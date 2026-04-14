import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";
import { runSalaryRunCountAllocation } from "../lib/salary-run-count-allocation.js";
import { syncDailyOperatingRecordsFromRows, type OperatingRow } from "../lib/daily-operating-records-sync.js";

export const atmtcTransactionsRouter = Router();

/** Stored in DataSyncLog.syncType — keep in sync with FE sync-logs labels. */
export const ATMTC_TRANSACTIONS_SYNC_TYPE = "atmtc_transactions";

/**
 * ATMTC / IC: one request updates DailyDriverAssignment + aggregated DailyOperatingRecord
 * (1 input row = 1 run by default, summed per vehicle×day), then salary run-count allocation only
 * (no runDriverAllocation — PM B4.2).
 *
 * POST /api/atmtc-transactions/sync
 * Body: {
 *   yearMonth: "2026-03",
 *   locationId?: string,
 *   departmentId?: string,  // location code → Location.id
 *   records: [
 *     { driverExternalId?, vehicleExternalId?, driverId?, vehicleId?, date: "2026-03-05", weight?: number }
 *   ]
 * }
 */
atmtcTransactionsRouter.post("/sync", async (req: Request, res: Response) => {
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

    const errors: string[] = [];
    let assignmentsUpserted = 0;
    /** vehicleId|date -> summed weight (runs) */
    const runCountByVehicleDate = new Map<string, number>();

    for (const r of records) {
      const {
        driverId,
        driverExternalId,
        vehicleId,
        vehicleExternalId,
        date,
        weight: weightRaw,
      } = r;

      if (!date || !/^\d{4}-\d{2}-\d{2}$/.test(String(date).trim())) {
        errors.push(`Invalid date: ${date}`);
        continue;
      }

      const recordYearMonth = String(date).slice(0, 7);
      if (recordYearMonth !== yearMonthStr) {
        errors.push(`Date ${date} is not in yearMonth ${yearMonthStr}`);
        continue;
      }

      const w = Number(weightRaw);
      const weight = Number.isFinite(w) && w >= 0 ? w : 1;

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
        if (d) {
          did = d.id;
        }
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
        if (v) {
          vid = v.id;
        }
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
      assignmentsUpserted++;

      const aggKey = `${vid}|${String(date).trim()}`;
      runCountByVehicleDate.set(aggKey, (runCountByVehicleDate.get(aggKey) ?? 0) + weight);
    }

    const operatingRows: OperatingRow[] = [];
    for (const [key, runCount] of Array.from(runCountByVehicleDate.entries())) {
      const [vehicleId, date] = key.split("|");
      operatingRows.push({
        vehicleId,
        date,
        runCount,
        isOperating: true,
      });
    }

    const operatingResult = await syncDailyOperatingRecordsFromRows({
      yearMonth: yearMonthStr,
      locationId: locId,
      records: operatingRows,
    });
    errors.push(...operatingResult.errors);

    const salaryAllocationResult = await runSalaryRunCountAllocation(yearMonthStr, locId);

    await prisma.dataSyncLog.create({
      data: {
        source: "ATMTC",
        syncType: ATMTC_TRANSACTIONS_SYNC_TYPE,
        recordCount: records.length,
        yearMonth: yearMonthStr,
        locationId: locId ? String(locId) : null,
      },
    });

    res.status(200).json({
      success: true,
      assignmentsUpserted,
      operatingUpserted: operatingResult.upserted,
      salaryAllocation: salaryAllocationResult,
      ...(errors.length > 0 && { errors }),
    });
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Failed to sync ATMTC transactions" });
  }
});
