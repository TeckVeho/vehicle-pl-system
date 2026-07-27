import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";
import { syncDailyOperatingRecordsFromRows, type OperatingRow } from "../lib/daily-operating-records-sync.js";
import { runCourseAllocationScope } from "../lib/course-allocation-trigger.js";

export const atmtcTransactionsRouter = Router();

/** Stored in DataSyncLog.syncType — keep in sync with FE sync-logs labels. */
export const ATMTC_TRANSACTIONS_SYNC_TYPE = "atmtc_transactions";

async function resolveCourseId(
  locId: string,
  fields: {
    courseId?: unknown;
    courseExternalId?: unknown;
    courseCode?: unknown;
  }
): Promise<{ courseId: string | null; courseExternalId: string | null }> {
  const { courseId, courseExternalId, courseCode } = fields;
  if (courseId) {
    const c = await prisma.course.findFirst({
      where: { id: String(courseId), locationId: locId },
      select: { id: true, externalId: true },
    });
    if (c) return { courseId: c.id, courseExternalId: c.externalId };
  }
  if (courseExternalId) {
    const c = await prisma.course.findFirst({
      where: { externalId: String(courseExternalId), locationId: locId },
      select: { id: true, externalId: true },
    });
    if (c) return { courseId: c.id, courseExternalId: c.externalId };
  }
  if (courseCode) {
    const codeStr = String(courseCode).trim();
    const c = await prisma.course.findUnique({
      where: { locationId_code: { locationId: locId, code: codeStr } },
      select: { id: true, externalId: true },
    });
    if (c) return { courseId: c.id, courseExternalId: c.externalId };
  }
  return { courseId: null, courseExternalId: courseExternalId ? String(courseExternalId) : null };
}

/**
 * ATMTC / IC: DailyAtmtcRun + assignment + operating records.
 * 給与配賦はタイムシート経路のみ。ここではコース別集計を再計算する。
 */
atmtcTransactionsRouter.post("/sync", async (req: Request, res: Response) => {
  try {
    const { yearMonth, locationId, departmentId, records, replaceExisting } = req.body;

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
    let atmtcRunsUpserted = 0;
    const runCountByVehicleDate = new Map<string, number>();

    const shouldReplace = replaceExisting !== false;
    if (locId && shouldReplace) {
      await prisma.dailyAtmtcRun.deleteMany({
        where: { locationId: locId, yearMonth: yearMonthStr },
      });
    }

    for (const r of records) {
      const {
        driverId,
        driverExternalId,
        vehicleId,
        vehicleExternalId,
        courseId,
        courseExternalId,
        courseCode,
        date,
        weight: weightRaw,
        sourceTxnId,
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
      const dateStr = String(date).trim();

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
      let vehicleLocId = locId;
      if (vehicleId) {
        const v = await prisma.vehicle.findUnique({
          where: { id: String(vehicleId) },
          select: { id: true, locationId: true },
        });
        if (v && (!locId || v.locationId === locId)) {
          vid = v.id;
          if (!vehicleLocId) vehicleLocId = v.locationId;
        }
      }
      if (!vid && vehicleExternalId) {
        const v = await prisma.vehicle.findFirst({
          where: {
            externalId: String(vehicleExternalId),
            ...(locId ? { locationId: locId } : {}),
          },
          select: { id: true, locationId: true },
        });
        if (v) {
          vid = v.id;
          if (!vehicleLocId) vehicleLocId = v.locationId;
        }
      }

      if (!vid) {
        errors.push(`Vehicle not found for record: date=${date}`);
        continue;
      }

      if (!vehicleLocId) {
        vehicleLocId = (
          await prisma.vehicle.findUnique({
            where: { id: vid },
            select: { locationId: true },
          })
        )?.locationId ?? null;
      }
      if (!vehicleLocId) {
        errors.push(`Location could not be resolved for vehicle on date=${date}`);
        continue;
      }

      const courseResolved = await resolveCourseId(vehicleLocId, {
        courseId,
        courseExternalId,
        courseCode,
      });

      const runData = {
        locationId: vehicleLocId,
        date: dateStr,
        yearMonth: yearMonthStr,
        vehicleId: vid,
        driverId: did,
        courseId: courseResolved.courseId,
        courseExternalId: courseResolved.courseExternalId,
        weight,
      };

      if (sourceTxnId) {
        await prisma.dailyAtmtcRun.upsert({
          where: { sourceTxnId: String(sourceTxnId) },
          create: { ...runData, sourceTxnId: String(sourceTxnId) },
          update: runData,
        });
      } else {
        await prisma.dailyAtmtcRun.create({
          data: runData,
        });
      }
      atmtcRunsUpserted++;

      if (did) {
        await prisma.dailyDriverAssignment.upsert({
          where: {
            driverId_vehicleId_date: {
              driverId: did,
              vehicleId: vid,
              date: dateStr,
            },
          },
          create: {
            driverId: did,
            vehicleId: vid,
            date: dateStr,
            yearMonth: yearMonthStr,
          },
          update: {},
        });
        assignmentsUpserted++;
      }

      const aggKey = `${vid}|${dateStr}`;
      runCountByVehicleDate.set(aggKey, (runCountByVehicleDate.get(aggKey) ?? 0) + weight);
    }

    const operatingRows: OperatingRow[] = [];
    for (const [key, runCount] of Array.from(runCountByVehicleDate.entries())) {
      const [vehicleIdKey, dateKey] = key.split("|");
      operatingRows.push({
        vehicleId: vehicleIdKey,
        date: dateKey,
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

    const courseAllocation = await runCourseAllocationScope(yearMonthStr, locId);

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
      atmtcRunsUpserted,
      operatingUpserted: operatingResult.upserted,
      courseAllocation,
      ...(errors.length > 0 && { errors }),
    });
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Failed to sync ATMTC transactions" });
  }
});
