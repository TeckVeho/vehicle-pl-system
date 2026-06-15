import fs from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";

const defaultLogPath = path.join(
  path.dirname(fileURLToPath(import.meta.url)),
  "../../logs/spreadsheet-revenue.log"
);

/**
 * Append-only audit log for Google Drive / XLSX revenue reads.
 * Path: `SPREADSHEET_REVENUE_LOG_PATH` or `backend/logs/spreadsheet-revenue.log`.
 */
export async function logSpreadsheetRevenue(
  level: "info" | "warn" | "error",
  message: string,
  fields?: Record<string, string | number | boolean | null | undefined>
): Promise<void> {
  const logPath =
    process.env.SPREADSHEET_REVENUE_LOG_PATH?.trim() || defaultLogPath;
  try {
    await fs.mkdir(path.dirname(logPath), { recursive: true });
    const ts = new Date().toISOString();
    const extra = fields && Object.keys(fields).length ? ` ${JSON.stringify(fields)}` : "";
    const line = `${ts} [${level.toUpperCase()}] ${message}${extra}\n`;
    await fs.appendFile(logPath, line, "utf8");
  } catch (e) {
    console.error("[spreadsheet-revenue-log] failed to write:", e);
  }
}
