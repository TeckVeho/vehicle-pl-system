import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";

export const dailyOperatingRouter = Router();

/**
 * 外部システム連携用: 日次稼働・走行データの一括同期（upsert）
 *
 * POST /api/daily-operating/sync
 * Body: {
 *   yearMonth: "2026-03",
 *   locationId?: string,  // 指定時は該当拠点の車両のみ
 *   records: [
 *     {
 *       vehicleId?: string,      // 内部ID（vehicleId または vehicleExternalId のいずれか必須）
 *       vehicleExternalId?: string,
 *       date: "2026-03-05",
 *       runCount: 2,
 *       isOperating: true
 *     },
 *     ...
 *   ]
 * }
 */
dailyOperatingRouter.post("/sync", async (req: Request, res: Response) => {
  try {
    const { yearMonth, locationId, departmentId, records } = req.body;

    let locId: string | null = locationId ? String(locationId) : null;
    if (!locId && departmentId) {
      const loc = await prisma.location.findUnique({
        where: { code: String(departmentId) },
        select: { id: true },
      });
      locId = loc?.id ?? null;
    }

    if (!yearMonth || !Array.isArray(records)) {
      res.status(400).json({
        error: "yearMonth and records array are required",
      });
      return;
    }

    const yearMonthStr = String(yearMonth).trim();
    if (!/^\d{4}-\d{2}$/.test(yearMonthStr)) {
      res.status(400).json({ error: "yearMonth must be YYYY-MM format" });
      return;
    }

    let upserted = 0;
    const errors: string[] = [];

    for (const r of records) {
      const { vehicleId, vehicleExternalId, date, runCount, isOperating } = r;

      if (!date || !/^\d{4}-\d{2}-\d{2}$/.test(String(date).trim())) {
        errors.push(`Invalid date: ${date}`);
        continue;
      }

      // date が yearMonth に属するか検証
      const recordYearMonth = String(date).slice(0, 7);
      if (recordYearMonth !== yearMonthStr) {
        errors.push(`Date ${date} is not in yearMonth ${yearMonthStr}`);
        continue;
      }

      let vid: string | null = null;
      if (vehicleId) {
        const v = await prisma.vehicle.findUnique({
          where: { id: String(vehicleId) },
          select: { id: true, locationId: true },
        });
        if (v && (!locId || v.locationId === locId)) {
          vid = v.id;
        }
      }
      if (!vid && vehicleExternalId) {
        const v = await prisma.vehicle.findFirst({
          where: {
            externalId: String(vehicleExternalId),
            ...(locId ? { locationId: locId } : {}),
          },
          select: { id: true },
        });
        if (v) vid = v.id;
      }

      if (!vid) {
        errors.push(`Vehicle not found for record: date=${date}`);
        continue;
      }

      const runCountVal = Math.max(0, parseInt(String(runCount), 10) || 0);
      const isOperatingVal = Boolean(isOperating ?? false);

      await prisma.dailyOperatingRecord.upsert({
        where: {
          vehicleId_date: { vehicleId: vid, date: String(date).trim() },
        },
        create: {
          vehicleId: vid,
          date: String(date).trim(),
          runCount: runCountVal,
          isOperating: isOperatingVal,
          yearMonth: yearMonthStr,
        },
        update: {
          runCount: runCountVal,
          isOperating: isOperatingVal,
        },
      });
      upserted++;
    }

    // 連携ログ記録
    await prisma.dataSyncLog.create({
      data: {
        source: "日次稼働連携",
        syncType: "daily_operating",
        recordCount: upserted,
        yearMonth: yearMonthStr,
        locationId: locId ? String(locId) : null,
      },
    });

    res.status(200).json({
      success: true,
      upserted,
      ...(errors.length > 0 && { errors }),
    });
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Failed to sync daily operating records" });
  }
});
