import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";
import { requireRole, ROLES } from "../lib/auth.js";

export const coursesRouter = Router();

coursesRouter.get("/", async (req: Request, res: Response) => {
  const locationId = req.query.locationId as string | undefined;

  const courses = await prisma.course.findMany({
    where: locationId ? { locationId } : undefined,
    include: {
      location: { select: { id: true, code: true, name: true } },
      _count: { select: { vehicles: true } },
    },
    orderBy: [{ locationId: "asc" }, { sortOrder: "asc" }, { code: "asc" }],
  });
  res.json(courses);
});

// 外部システム連携用: 一括同期（upsert）
coursesRouter.post("/sync", requireRole(ROLES.MASTER), async (req: Request, res: Response) => {
  try {
    const { courses: coursesPayload } = req.body;
    if (!Array.isArray(coursesPayload)) {
      res.status(400).json({ error: "courses array is required" });
      return;
    }

    const results: { code: string; status: "created" | "updated"; id: string }[] = [];

    for (const c of coursesPayload) {
      const { locationId, locationCode, departmentId, name, code, sortOrder, externalId } = c;
      if (!name || !code) continue;

      const locCode = locationCode ?? departmentId;
      let locId = locationId;
      if (!locId && locCode) {
        const loc = await prisma.location.findUnique({
          where: { code: String(locCode) },
        });
        if (!loc) {
          console.warn(`Location not found for code: ${locCode}, skipping course ${code}`);
          continue;
        }
        locId = loc.id;
      }
      if (!locId) continue;

      const codeStr = String(code).trim();
      const data = {
        locationId: locId,
        name: String(name).trim(),
        code: codeStr,
        sortOrder: sortOrder !== undefined ? Number(sortOrder) : 0,
        externalId: externalId ? String(externalId).trim() : null,
      };

      // Prefer externalId; if missing in VPL but (locationId, code) exists (e.g. re-sync / IC id change), match by composite key to avoid P2002 on create.
      let existing = null;
      if (externalId) {
        existing = await prisma.course.findFirst({
          where: { externalId: String(externalId).trim() },
        });
      }
      if (!existing) {
        existing = await prisma.course.findUnique({
          where: {
            locationId_code: { locationId: locId, code: codeStr },
          },
        });
      }

      if (existing) {
        const updated = await prisma.course.update({
          where: { id: existing.id },
          data,
        });
        results.push({ code: codeStr, status: "updated", id: updated.id });
      } else {
        const maxSort = await prisma.course.aggregate({
          where: { locationId: locId },
          _max: { sortOrder: true },
        });
        const nextSort =
          sortOrder !== undefined && sortOrder !== null
            ? Number(sortOrder)
            : (maxSort._max.sortOrder ?? 0) + 1;
        const created = await prisma.course.create({
          data: { ...data, sortOrder: nextSort },
        });
        results.push({ code: codeStr, status: "created", id: created.id });
      }
    }

    res.json({ synced: results.length, results });
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Failed to sync courses" });
  }
});

coursesRouter.post("/", requireRole(ROLES.MASTER), async (req: Request, res: Response) => {
  try {
    const { locationId, name, code, sortOrder } = req.body;

    if (!locationId || !name || !code) {
      res.status(400).json({ error: "locationId, name, code are required" });
      return;
    }

    const codeStr = String(code).trim();

    const existing = await prisma.course.findUnique({
      where: {
        locationId_code: { locationId: String(locationId), code: codeStr },
      },
    });
    if (existing) {
      res.status(400).json({ error: "この拠点に同じコードのコースが既に存在します" });
      return;
    }

    const existingVehicle = await prisma.vehicle.findUnique({
      where: {
        locationId_vehicleNo: {
          locationId: String(locationId),
          vehicleNo: codeStr,
        },
      },
    });
    if (existingVehicle) {
      res.status(400).json({
        error: "この拠点に同じ車両番号の車両が既に存在します。既存の車両にコースを紐づけるには、コースマスタで該当コースの名前を編集してください。",
      });
      return;
    }

    const maxSort = await prisma.course.aggregate({
      where: { locationId },
      _max: { sortOrder: true },
    });
    const nextSort = sortOrder ?? (maxSort._max.sortOrder ?? 0) + 1;

    const course = await prisma.course.create({
      data: {
        locationId: String(locationId),
        name: String(name),
        code: codeStr,
        sortOrder: Number(nextSort),
      },
      include: {
        location: { select: { id: true, code: true, name: true } },
      },
    });

    await prisma.vehicle.create({
      data: {
        locationId: String(locationId),
        vehicleNo: codeStr,
        courseId: course.id,
      },
    });

    const created = await prisma.course.findUnique({
      where: { id: course.id },
      include: {
        location: { select: { id: true, code: true, name: true } },
        _count: { select: { vehicles: true } },
      },
    });
    res.json(created);
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Failed to create course" });
  }
});

coursesRouter.put("/:id", requireRole(ROLES.MASTER), async (req: Request, res: Response) => {
  try {
    const { id } = req.params;
    const { name, code, sortOrder } = req.body;

    const course = await prisma.course.update({
      where: { id },
      data: {
        ...(name !== undefined && { name: String(name) }),
        ...(code !== undefined && { code: String(code).trim() }),
        ...(sortOrder !== undefined && { sortOrder: Number(sortOrder) }),
      },
      include: {
        location: { select: { id: true, code: true, name: true } },
      },
    });
    res.json(course);
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Failed to update course" });
  }
});

coursesRouter.delete("/:id", requireRole(ROLES.MASTER), async (req: Request, res: Response) => {
  try {
    const { id } = req.params;

    const count = await prisma.vehicle.count({
      where: { courseId: id },
    });
    if (count > 0) {
      res.status(400).json({
        error: `このコースには ${count} 台の車両が紐づいています。先に車両の紐づけを解除してください。`,
      });
      return;
    }

    await prisma.course.delete({
      where: { id },
    });
    res.json({ success: true });
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Failed to delete course" });
  }
});
