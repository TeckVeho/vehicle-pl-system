import { prisma } from "./prisma.js";

/** One row of operating data for upsert (same shape as POST /api/daily-operating/sync body.records[]). */
export type OperatingRow = {
  vehicleId?: string;
  vehicleExternalId?: string;
  date: string;
  runCount: number | string;
  isOperating?: boolean;
};

export type SyncDailyOperatingRecordsParams = {
  yearMonth: string;
  locationId: string | null;
  records: OperatingRow[];
};

/**
 * Upsert DailyOperatingRecord rows (vehicle resolve, date vs yearMonth check).
 * Does not run salary allocation or DataSyncLog — callers handle post-steps.
 */
export async function syncDailyOperatingRecordsFromRows(
  params: SyncDailyOperatingRecordsParams
): Promise<{ upserted: number; errors: string[] }> {
  const { yearMonth, locationId: locId, records } = params;
  const yearMonthStr = String(yearMonth).trim();
  let upserted = 0;
  const errors: string[] = [];

  for (const r of records) {
    const { vehicleId, vehicleExternalId, date, runCount, isOperating } = r;

    if (!date || !/^\d{4}-\d{2}-\d{2}$/.test(String(date).trim())) {
      errors.push(`Invalid date: ${date}`);
      continue;
    }

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
      if (v) {
        vid = v.id;
      }
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

  return { upserted, errors };
}
