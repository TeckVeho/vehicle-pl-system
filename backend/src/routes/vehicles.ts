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

    const results: { vehicleNo: string; status: "created" | "updated"; id: string }[] = [];

    for (const v of vehicles) {
      const { locationId, locationCode, departmentId, vehicleNo, serviceType, tonnage, externalId, courseId, courseExternalId } = v;
      if (!vehicleNo) continue;

      const locCode = locationCode ?? departmentId;
      let locId = locationId;
      if (!locId && locCode) {
        const loc = await prisma.location.findUnique({
          where: { code: String(locCode) },
        });
        if (!loc) {
          console.warn(`Location not found for code: ${locCode}, skipping vehicle ${vehicleNo}`);
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

      const baseData = {
        locationId: locId,
        vehicleNo: vehicleNoStr,
        serviceType: serviceType ? String(serviceType) : null,
        externalId: externalId ? String(externalId).trim() : null,
        courseId: courseIdVal,
        ...(tonnage !== undefined && tonnage !== null && { tonnage: Number(tonnage) }),
      };

      const existing = externalId
        ? await prisma.vehicle.findFirst({ where: { externalId: String(externalId) } })
        : await prisma.vehicle.findUnique({
            where: {
              locationId_vehicleNo: { locationId: locId, vehicleNo: vehicleNoStr },
            },
          });

      const updateData: { tonnage?: number | null } = {};
      if (tonnage !== undefined) {
        updateData.tonnage = tonnage === null ? null : Number(tonnage);
      }

      if (existing) {
        const updated = await prisma.vehicle.update({
          where: { id: existing.id },
          data: { ...baseData, ...updateData },
        });
        results.push({ vehicleNo: vehicleNoStr, status: "updated", id: updated.id });
      } else {
        const created = await prisma.vehicle.create({
          data: baseData,
        });
        results.push({ vehicleNo: vehicleNoStr, status: "created", id: created.id });
      }
    }

    res.json({ synced: results.length, results });
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Failed to sync vehicles" });
  }
});
