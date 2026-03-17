import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";
import { requireRole, ROLES } from "../lib/auth.js";

export const driversRouter = Router();

driversRouter.get("/", async (req: Request, res: Response) => {
  const locationId = req.query.locationId as string | undefined;

  const drivers = await prisma.driver.findMany({
    where: locationId ? { locationId } : undefined,
    select: {
      id: true,
      locationId: true,
      code: true,
      name: true,
      externalId: true,
      createdAt: true,
      updatedAt: true,
      location: { select: { id: true, code: true, name: true } },
    },
    orderBy: [{ locationId: "asc" }, { code: "asc" }],
  });
  res.json(drivers);
});

/**
 * 外部システム連携用: ドライバーマスタ一括同期（upsert）
 *
 * POST /api/drivers/sync
 * Body: {
 *   drivers: [
 *     {
 *       locationId?: string,
 *       locationCode?: string,
 *       code: "EMP001",
 *       name: "山田太郎",
 *       externalId?: "ext-driver-123"
 *     },
 *     ...
 *   ]
 * }
 */
driversRouter.post("/sync", requireRole(ROLES.MASTER), async (req: Request, res: Response) => {
  try {
    const { drivers } = req.body;
    if (!Array.isArray(drivers)) {
      res.status(400).json({ error: "drivers array is required" });
      return;
    }

    const results: { code: string; status: "created" | "updated"; id: string }[] = [];

    for (const d of drivers) {
      const { locationId, locationCode, departmentId, code, name, externalId } = d;
      if (!code) continue;

      const locCode = locationCode ?? departmentId;
      let locId = locationId;
      if (!locId && locCode) {
        const loc = await prisma.location.findUnique({
          where: { code: String(locCode) },
        });
        if (!loc) {
          console.warn(`Location not found for code: ${locCode}, skipping driver ${code}`);
          continue;
        }
        locId = loc.id;
      }
      if (!locId) continue;

      const codeStr = String(code).trim();
      const nameStr = name ? String(name).trim() : codeStr;

      const data = {
        locationId: locId,
        code: codeStr,
        name: nameStr,
        externalId: externalId ? String(externalId).trim() : null,
      };

      const existing = externalId
        ? await prisma.driver.findFirst({ where: { externalId: String(externalId) } })
        : await prisma.driver.findUnique({
            where: {
              locationId_code: { locationId: locId, code: codeStr },
            },
          });

      if (existing) {
        const updated = await prisma.driver.update({
          where: { id: existing.id },
          data,
        });
        results.push({ code: codeStr, status: "updated", id: updated.id });
      } else {
        const created = await prisma.driver.create({
          data,
        });
        results.push({ code: codeStr, status: "created", id: created.id });
      }
    }

    res.status(200).json({
      success: true,
      results,
    });
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Failed to sync drivers" });
  }
});
