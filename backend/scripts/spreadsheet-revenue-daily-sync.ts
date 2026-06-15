/**
 * Daily batch: sync Google Drive 売上 parse results into DB snapshot for all locations.
 *
 * Intended to run at 06:00 Asia/Tokyo (external cron / systemd / k8s CronJob).
 * Default: syncs current JST month + previous month (override with env).
 *
 * From backend/:
 *   npx tsx --env-file=.env scripts/spreadsheet-revenue-daily-sync.ts
 *
 * Optional:
 *   SPREADSHEET_REVENUE_SYNC_YEAR_MONTHS=2026-05,2026-04
 */
import { prisma } from "../src/lib/prisma.js";
import {
  defaultSpreadsheetRevenueSyncYearMonthsJst,
  syncSpreadsheetRevenueForLocationYear,
} from "../src/lib/spreadsheet-revenue.js";

async function main(): Promise<void> {
  try {
    const envMonths = process.env.SPREADSHEET_REVENUE_SYNC_YEAR_MONTHS?.split(",")
      .map((s) => s.trim())
      .filter((s) => /^\d{4}-\d{2}$/.test(s));
    const yearMonths =
      envMonths && envMonths.length > 0
        ? envMonths
        : defaultSpreadsheetRevenueSyncYearMonthsJst();

    const locations = await prisma.location.findMany({
      orderBy: { code: "asc" },
      select: { id: true, code: true, name: true },
    });

    let failures = 0;
    for (const ym of yearMonths) {
      for (const loc of locations) {
        const r = await syncSpreadsheetRevenueForLocationYear(loc.id, ym);
        if (!r.ok) {
          failures += 1;
          console.error(
            `[spreadsheet-revenue-daily-sync] ${loc.code} ${ym}: ${r.error}`
          );
        }
      }
    }

    if (failures > 0) {
      process.exitCode = 1;
    }
  } finally {
    await prisma.$disconnect().catch(() => {});
  }
}

main().catch((e) => {
  console.error(e);
  process.exitCode = 1;
});
