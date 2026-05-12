/**
 * Scheduled sync for spreadsheet revenue data.
 *
 * Downloads and parses P&L workbooks from Google Drive,
 * then persists revenue data into DB snapshots (DriveSpreadsheetRevenueLine).
 *
 * Dashboard and income statement read from DB snapshots only,
 * so this scheduler (or manual sync) is needed to keep snapshots up to date.
 */

import cron from "node-cron";
import { prisma } from "./prisma.js";
import {
  defaultSpreadsheetRevenueSyncYearMonthsJst,
  syncSpreadsheetRevenueForLocationYear,
} from "./spreadsheet-revenue.js";
import { logSpreadsheetRevenue } from "./spreadsheet-revenue-log.js";

// ============================================================
// [SCHEDULE CONFIGURATION] Change CRON_EXPRESSION below:
//
// Daily at 06:00 JST:           '0 6 * * *'     <-- default
// Weekly on Monday 06:00 JST:   '0 6 * * 1'
// Monthly on 1st at 06:00 JST:  '0 6 1 * *'
// Twice daily 06:00 & 18:00:    '0 6,18 * * *'
// Weekdays only at 06:00:       '0 6 * * 1-5'
//
// Cron format: minute hour day month weekday
// ============================================================
const CRON_EXPRESSION = "0 6 * * *"; // <-- change this
const CRON_TIMEZONE = "Asia/Tokyo";

/**
 * Sync spreadsheet revenue for all locations (current month + previous month).
 * Shared logic called by both the cron scheduler and the manual sync endpoint.
 */
export async function runSpreadsheetRevenueSync(
  yearMonths?: string[]
): Promise<{
  yearMonths: string[];
  totalLocations: number;
  okCount: number;
  failCount: number;
  results: Array<{
    locationId: string;
    locationCode: string;
    locationName: string;
    yearMonth: string;
    ok: boolean;
    recordCount?: number;
    driveFileId?: string;
    sheetName?: string | null;
    error?: string;
  }>;
}> {
  const months =
    yearMonths && yearMonths.length > 0
      ? yearMonths
      : defaultSpreadsheetRevenueSyncYearMonthsJst();

  const locations = await prisma.location.findMany({
    orderBy: { code: "asc" },
    select: { id: true, code: true, name: true },
  });

  const results: Array<{
    locationId: string;
    locationCode: string;
    locationName: string;
    yearMonth: string;
    ok: boolean;
    recordCount?: number;
    driveFileId?: string;
    sheetName?: string | null;
    error?: string;
  }> = [];

  let okCount = 0;
  let failCount = 0;

  for (const ym of months) {
    for (const loc of locations) {
      const r = await syncSpreadsheetRevenueForLocationYear(loc.id, ym);
      if (r.ok) {
        okCount++;
        results.push({
          locationId: loc.id,
          locationCode: loc.code,
          locationName: loc.name,
          yearMonth: ym,
          ok: true,
          recordCount: r.recordCount,
          driveFileId: r.driveFileId,
          sheetName: r.sheetName,
        });
      } else {
        failCount++;
        results.push({
          locationId: loc.id,
          locationCode: loc.code,
          locationName: loc.name,
          yearMonth: ym,
          ok: false,
          error: r.error,
          driveFileId: r.driveFileId,
        });
      }
    }
  }

  // --- Structured sync summary logging ---
  const successEntries = results.filter((r) => r.ok);
  const failEntries = results.filter((r) => !r.ok);

  const successSummary = successEntries
    .map(
      (r) =>
        `  ✓ ${r.locationCode} (${r.locationName}) [${r.yearMonth}] → file=${r.driveFileId ?? "N/A"}, sheet=${r.sheetName ?? "N/A"}, records=${r.recordCount ?? 0}`
    )
    .join("\n");

  const failSummary = failEntries
    .map(
      (r) =>
        `  ✗ ${r.locationCode} (${r.locationName}) [${r.yearMonth}] → reason: ${r.error ?? "unknown"}`
    )
    .join("\n");

  const logMessage = [
    `[scheduler] spreadsheet-revenue sync complete: ${okCount} ok, ${failCount} skipped (${months.join(", ")})`,
    successEntries.length > 0 ? `Synced locations:\n${successSummary}` : null,
    failEntries.length > 0 ? `Skipped locations (no matching file/sheet):\n${failSummary}` : null,
  ]
    .filter(Boolean)
    .join("\n");

  console.log(logMessage);

  // Persist structured log to file
  void logSpreadsheetRevenue("info", "sync summary", {
    yearMonths: months.join(","),
    totalLocations: locations.length,
    okCount,
    failCount,
  });
  for (const r of successEntries) {
    void logSpreadsheetRevenue("info", "sync success", {
      locationCode: r.locationCode,
      locationName: r.locationName,
      yearMonth: r.yearMonth,
      driveFileId: r.driveFileId ?? null,
      sheetName: r.sheetName ?? null,
      recordCount: r.recordCount ?? 0,
    });
  }
  for (const r of failEntries) {
    void logSpreadsheetRevenue("warn", "sync skipped", {
      locationCode: r.locationCode,
      locationName: r.locationName,
      yearMonth: r.yearMonth,
      reason: r.error ?? "unknown",
      driveFileId: r.driveFileId ?? null,
    });
  }

  return {
    yearMonths: months,
    totalLocations: locations.length,
    okCount,
    failCount,
    results,
  };
}

let schedulerStarted = false;

/**
 * cron スケジューラを起動する（サーバー起動時に 1 回だけ呼び出す）。
 */
export function startSpreadsheetRevenueScheduler(): void {
  if (schedulerStarted) return;
  schedulerStarted = true;

  console.log(
    `[scheduler] spreadsheet-revenue cron registered: "${CRON_EXPRESSION}" (${CRON_TIMEZONE})`
  );

  cron.schedule(
    CRON_EXPRESSION,
    async () => {
      const startedAt = new Date().toISOString();
      console.log(
        `[scheduler] spreadsheet-revenue sync started at ${startedAt}`
      );

      try {
        const result = await runSpreadsheetRevenueSync();
        console.log(
          `[scheduler] spreadsheet-revenue sync completed: ${result.okCount} ok, ${result.failCount} failed (${result.yearMonths.join(", ")})`
        );
      } catch (err) {
        console.error("[scheduler] spreadsheet-revenue sync error:", err);
      }
    },
    {
      timezone: CRON_TIMEZONE,
    }
  );
}
