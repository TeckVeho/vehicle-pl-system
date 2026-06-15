import { Router, Request, Response } from "express";
import { z } from "zod";
import { requireRole, ROLES } from "../lib/auth.js";
import { prisma } from "../lib/prisma.js";
import { syncSpreadsheetRevenueForLocationYear, yearMonthNowAsiaTokyo } from "../lib/spreadsheet-revenue.js";

const syncBodySchema = z.object({
  yearMonth: z.string().regex(/^\d{4}-\d{2}$/).optional(),
  locationId: z.string().optional(),
});

export const spreadsheetRevenueRouter = Router();

spreadsheetRevenueRouter.post(
  "/sync",
  requireRole(ROLES.MASTER),
  async (req: Request, res: Response) => {
    const parsed = syncBodySchema.safeParse(req.body ?? {});
    if (!parsed.success) {
      res.status(400).json({
        error: "Invalid body",
        details: parsed.error.flatten(),
      });
      return;
    }

    const yearMonth = parsed.data.yearMonth ?? yearMonthNowAsiaTokyo();

    const locations = parsed.data.locationId
      ? await prisma.location.findMany({
          where: { id: parsed.data.locationId },
          select: { id: true, code: true, name: true },
        })
      : await prisma.location.findMany({
          orderBy: { code: "asc" },
          select: { id: true, code: true, name: true },
        });

    if (parsed.data.locationId && locations.length === 0) {
      res.status(404).json({ error: "location not found" });
      return;
    }

    const results: Array<{
      locationId: string;
      locationCode: string;
      ok: boolean;
      recordCount?: number;
      driveFileId?: string;
      sheetName?: string | null;
      error?: string;
    }> = [];

    for (const loc of locations) {
      const r = await syncSpreadsheetRevenueForLocationYear(loc.id, yearMonth);
      if (r.ok) {
        results.push({
          locationId: loc.id,
          locationCode: loc.code,
          ok: true,
          recordCount: r.recordCount,
          driveFileId: r.driveFileId,
          sheetName: r.sheetName,
        });
      } else {
        results.push({
          locationId: loc.id,
          locationCode: loc.code,
          ok: false,
          error: r.error,
          driveFileId: r.driveFileId,
        });
      }
    }

    const okCount = results.filter((x) => x.ok).length;
    res.json({
      yearMonth,
      locationCount: locations.length,
      okCount,
      failCount: results.length - okCount,
      results,
    });
  }
);
