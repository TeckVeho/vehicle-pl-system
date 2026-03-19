import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";
import { requireRole, ROLES } from "../lib/auth.js";
import { runDriverAllocation } from "../lib/driver-allocation.js";
import { runSalaryRunCountAllocation } from "../lib/salary-run-count-allocation.js";

export const driverAssignmentsRouter = Router();

/**
 * 外部システム連携用: 日次乗務記録の一括同期（upsert）
 * 連携後、ドライバー配賦を再計算して MonthlyRecord を更新する。
 *
 * POST /api/driver-assignments/sync
 * Body: {
 *   yearMonth: "2026-03",
 *   locationId?: string,
 *   records: [
 *     {
 *       driverId?: string,
 *       driverExternalId?: string,
 *       vehicleId?: string,
 *       vehicleExternalId?: string,
 *       date: "2026-03-05"
 *     },
 *     ...
 *   ]
 * }
 */
driverAssignmentsRouter.post("/sync", requireRole(ROLES.MASTER), async (req: Request, res: Response) => {
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
      const { driverId, driverExternalId, vehicleId, vehicleExternalId, date } = r;

      if (!date || !/^\d{4}-\d{2}-\d{2}$/.test(String(date).trim())) {
        errors.push(`Invalid date: ${date}`);
        continue;
      }

      const recordYearMonth = String(date).slice(0, 7);
      if (recordYearMonth !== yearMonthStr) {
        errors.push(`Date ${date} is not in yearMonth ${yearMonthStr}`);
        continue;
      }

      let did: string | null = null;
      if (driverId) {
        const d = await prisma.driver.findUnique({
          where: { id: String(driverId) },
          select: { id: true, locationId: true },
        });
        if (d && (!locId || d.locationId === locId)) {
          did = d.id;
        }
      }
      if (!did && driverExternalId) {
        const d = await prisma.driver.findFirst({
          where: {
            externalId: String(driverExternalId),
            ...(locId ? { locationId: locId } : {}),
          },
          select: { id: true },
        });
        if (d) did = d.id;
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

    if (!did) {
        errors.push(`Driver not found for record: date=${date}`);
        continue;
      }
      if (!vid) {
        errors.push(`Vehicle not found for record: date=${date}`);
        continue;
      }

      await prisma.dailyDriverAssignment.upsert({
        where: {
          driverId_vehicleId_date: {
            driverId: did,
            vehicleId: vid,
            date: String(date).trim(),
          },
        },
        create: {
          driverId: did,
          vehicleId: vid,
          date: String(date).trim(),
          yearMonth: yearMonthStr,
        },
        update: {},
      });
      upserted++;
    }

    // 配賦計算を実行（対象拠点の車両のみ）
    const [allocationResult, salaryAllocationResult] = await Promise.all([
      runDriverAllocation(yearMonthStr, locId),
      runSalaryRunCountAllocation(yearMonthStr, locId),
    ]);

    await prisma.dataSyncLog.create({
      data: {
        source: "タイムシート連携",
        syncType: "driver_assignments",
        recordCount: upserted,
        yearMonth: yearMonthStr,
        locationId: locId ? String(locId) : null,
      },
    });

    res.status(200).json({
      success: true,
      upserted,
      allocation: allocationResult,
      salaryAllocation: salaryAllocationResult,
      ...(errors.length > 0 && { errors }),
    });
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Failed to sync driver assignments" });
  }
});
