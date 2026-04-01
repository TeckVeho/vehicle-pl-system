import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";
import { requireRole, ROLES } from "../lib/auth.js";

export const vehiclesRouter = Router();

vehiclesRouter.get("/", async (req: Request, res: Response) => {
  const locationId = req.query.locationId as string | undefined;

  const vehicles = await prisma.vehicle.findMany({
    where: locationId ? { locationId } : undefined,
    select: {
      id: true,
      locationId: true,
      vehicleNo: true,
      serviceType: true,
      tonnage: true,
      externalId: true,
      createdAt: true,
      updatedAt: true,
      location: true,
      course: { select: { id: true, name: true, code: true, externalId: true } },
    },
    orderBy: [{ locationId: "asc" }, { vehicleNo: "asc" }],
  });
  res.json(vehicles);
});

// 外部システム連携用: 一括同期（upsert）
vehiclesRouter.post("/sync", requireRole(ROLES.MASTER), async (req: Request, res: Response) => {
  try {
    const { vehicles } = req.body;
    if (!Array.isArray(vehicles)) {
      res.status(400).json({ error: "vehicles array is required" });
      return;
    }

    const results: { vehicleNo: string; status: "created" | "updated" | "skipped"; id?: string; reason?: string }[] = [];

    // Pre-fetch all locations to avoid N+1 queries
    const locations = await prisma.location.findMany();
    const locationByCode = new Map(locations.map((l) => [l.code, l]));

    for (const v of vehicles) {
      const { locationId, locationCode, departmentId, vehicleNo, serviceType, tonnage, externalId, courseId, courseExternalId } = v;
      if (!vehicleNo) continue;

      try {
        const locCode = locationCode ?? departmentId;
        let locId = locationId;
        if (!locId && locCode) {
          const loc = locationByCode.get(String(locCode));
          if (!loc) {
            results.push({ vehicleNo: String(vehicleNo), status: "skipped", reason: `Location not found: ${locCode}` });
            continue;
          }
          locId = loc.id;
        }
        if (!locId) continue;

        const vehicleNoStr = String(vehicleNo).trim();
        let courseIdVal: string | null = courseId ?? null;
        if (!courseIdVal && courseExternalId) {
          const course = await prisma.course.findFirst({
            where: { externalId: String(courseExternalId) },
          });
          courseIdVal = course?.id ?? null;
        }

        const updateData = {
          serviceType: serviceType ? String(serviceType) : null,
          externalId: externalId ? String(externalId).trim() : null,
          courseId: courseIdVal,
          ...(tonnage !== undefined && { tonnage: tonnage === null ? null : Number(tonnage) }),
        };

        // Use upsert to handle duplicate vehicleNo entries in the same batch
        // and race conditions gracefully
        if (externalId) {
          // If we have an externalId, first try to find by externalId
          const existingByExtId = await prisma.vehicle.findFirst({ where: { externalId: String(externalId) } });
          if (existingByExtId) {
            const updated = await prisma.vehicle.update({
              where: { id: existingByExtId.id },
              data: { locationId: locId, vehicleNo: vehicleNoStr, ...updateData },
            });
            results.push({ vehicleNo: vehicleNoStr, status: "updated", id: updated.id });
            continue;
          }
        }

        // Upsert by locationId + vehicleNo (handles duplicates within the same batch)
        const upserted = await prisma.vehicle.upsert({
          where: {
            locationId_vehicleNo: { locationId: locId, vehicleNo: vehicleNoStr },
          },
          update: updateData,
          create: {
            locationId: locId,
            vehicleNo: vehicleNoStr,
            ...updateData,
          },
        });

        // Determine if it was created or updated based on timestamps
        const isNew = upserted.createdAt.getTime() === upserted.updatedAt.getTime();
        results.push({ vehicleNo: vehicleNoStr, status: isNew ? "created" : "updated", id: upserted.id });
      } catch (innerErr: unknown) {
        const errMsg = innerErr instanceof Error ? innerErr.message : String(innerErr);
        console.warn(`Failed to sync vehicle ${vehicleNo}: ${errMsg}`);
        results.push({ vehicleNo: String(vehicleNo), status: "skipped", reason: errMsg });
      }
    }

    const synced = results.filter((r) => r.status !== "skipped").length;
    const skipped = results.filter((r) => r.status === "skipped");
    res.json({ synced, skippedCount: skipped.length, results });
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Failed to sync vehicles" });
  }
});
