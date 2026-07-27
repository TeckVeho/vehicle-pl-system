/**
 * Per-location spreadsheet revenue via Google Drive workbook + XLSX parse.
 *
 * Supports three layouts:
 *
 * **A — canonical (`YYYY-MM` sheet)** — Issue baseline format  
 * Row 1: `vehicleNo` | AccountItem.name × …  
 * Row 2+: vehicle numbers × amounts (comma ok).
 *
 * **B — 「車両別損益」 (Izumi Nagoya-style pivot)**  
 * Row above headers (`車両No`): Excel identifies columns (`1816`, `18-32`, …).  
 * Header row: `車両No` | alternating `[科目コード]|（％）|` × …  
 * Data rows: column `A` = AccountItem.name (or synonym row revenue-only filters permit).
 *
 * **C — 「売上明細」 (損益計算資料 / PM 指定)**  
 * Header row: blocks like `山パン` | `運賃` | … | `月額` | … `エコー` | … | `月額`.  
 * Data: column `A` = vehicle key (`18-16` style → resolved with `Location.code`).  
 * Amount per revenue account: cell under each block’s `月額` column.  
 * **`#ERROR!` / `#DIV/0!` cells** are read as **0** until fixed in the workbook (`parseAmount`).  
 * **Tab choice:** When `spreadsheetRevenueSheet` is unset: **`売上明細`**, else **`車両別損益`**. Set `spreadsheetRevenueSheet` to a tab name to override (e.g. canonical **`YYYY-MM`** / **`YYYY.MM`** sheet).
 *
 * Sheet picking (`spreadsheetRevenueSheet`): explicit tab name when set; otherwise PM order above. Caller-supplied `yearMonth` stays canonical internally (`YYYY-MM`).
 *
 * Never throws on failures → warns/logs → empty Map → callers render zeros.
 *
 * **Drive file:** Names contain `損益計算資料`. Folder ID (`GOOGLE_DRIVE_FOLDER_ID` or `google_drive_folder_id`) is required for auto-discovery (direct children); no folder → no Drive list fallback. If `Location.spreadsheetId` is empty, match by marker + **`Location.name`** + **`yearMonth`**, cache ~5 min. Default sheet: **`売上明細`** then **`車両別損益`** when both exist (`spreadsheetRevenueSheet` required to read a **`YYYY-MM`** / **`YYYY.MM`**-named tab automatically).
 */

import readXlsxFile, { readSheetNames } from "read-excel-file/node";
import { prisma } from "./prisma.js";
import { accountItemEffectiveWhere } from "./account-item-filter.js";
import { REVENUE_CATEGORY } from "./calc.js";
import { downloadDriveFileAsXlsxBuffer, listSharedPlSpreadsheetFileRefs } from "./google-drive-client.js";
import { logSpreadsheetRevenue } from "./spreadsheet-revenue-log.js";
import { getPreviousYearMonth } from "./salary-daily-proration.js";
import { runCourseAllocationScope } from "./course-allocation-trigger.js";

/** Current calendar month in Asia/Tokyo as YYYY-MM */
export function yearMonthNowAsiaTokyo(d: Date = new Date()): string {
  const parts = new Intl.DateTimeFormat("en-CA", {
    timeZone: "Asia/Tokyo",
    year: "numeric",
    month: "2-digit",
  }).formatToParts(d);
  const y = parts.find((p) => p.type === "year")?.value;
  const mo = parts.find((p) => p.type === "month")?.value;
  if (y && mo) return `${y}-${mo.padStart(2, "0")}`;
  return d.toISOString().slice(0, 7);
}

/** Default months for daily cron: current JST month + previous (dashboard often views two months). */
export function defaultSpreadsheetRevenueSyncYearMonthsJst(d: Date = new Date()): string[] {
  const cur = yearMonthNowAsiaTokyo(d);
  return [cur, getPreviousYearMonth(cur)];
}

/** Stored in DataSyncLog.syncType — keep in sync with FE sync-logs labels. */
export const SPREADSHEET_REVENUE_SYNC_TYPE = "spreadsheet_revenue";

/** Sync stores the same revenue rows dashboard/PL read (manual-entry-only names excluded). */
const SPREADSHEET_SYNC_EXCLUDED_MANUAL_REVENUE_NAMES = [
  "その他",
  "不動産収入",
  "人材派遣収入",
] as const;

/** 売上明細: A列グループ見出し（山パン・エコー等）→ DB `AccountItem.name` */
const URIMEISAI_GROUP_HEADER_TO_ACCOUNT_NAME: Record<string, string> = {
  山パン: "山崎製パン",
  エコー: "富士エコー",
  富士エコー: "富士エコー",
  その他: "その他",
};

const URIMEISAI_SUBHEADER_LABELS = new Set([
  "運賃",
  "日額損益用",
  "高速",
  "日数",
  "高速代",
  "プラン運賃",
  "時間加算",
  "距離加算",
]);

const URIMEISAI_ROW0_SKIP_LABELS = new Set([
  "デコ配送",
  "空番転送",
  "派遣負担額",
  "ログ",
  "合計",
]);

/** Sheet 「車両別損益」の行ラベル（A列）と DB AccountItem.name の揺らぎ対応（シードと一致済みのみ） */
const IZUMI_REVENUE_ROW_LABEL_TO_ACCOUNT_ITEM_NAME: Record<string, string> = {
  サンロジ: "サンロジスティックス",
  "ロジ・ネット": "ロジスティクス・ネットワーク",
  ダイセーログ: "ダイセーロジスティクス",
};

function yearMonthFilenameFragments(yearMonth: string): string[] {
  const ym = yearMonth.trim();
  const out = new Set<string>();
  const m = /^(\d{4})-(\d{2})$/.exec(ym);
  if (m) {
    const y = m[1];
    const mo = m[2];
    out.add(`${y}.${mo}`);
    out.add(`${y}-${mo}`);
    out.add(`${y}${mo}`);
    const moNoPad = String(parseInt(mo, 10));
    if (moNoPad !== mo) out.add(`${y}.${moNoPad}`);
  }
  if (ym) out.add(ym);
  return [...out].filter((s) => s.length >= 6);
}

function fileMatchesLocationPlTemplate(
  fileName: string,
  locationName: string,
  yearMonth: string
): boolean {
  const loc = locationName.trim();
  if (!loc) return false;
  if (!fileName.includes(loc)) return false;
  const frags = yearMonthFilenameFragments(yearMonth);
  return frags.some((f) => fileName.includes(f));
}

const drivePlFileCache = new Map<
  string,
  { id: string; name: string; expiry: number }
>();
const DRIVE_PL_FILE_CACHE_MS = 5 * 60 * 1000;

async function resolvePlSpreadsheetFileFromSharedDrive(
  locationId: string,
  locationName: string,
  yearMonth: string
): Promise<{ id: string; name: string } | null> {
  const cacheKey = `${locationId}:${yearMonth}`;
  const now = Date.now();
  const cached = drivePlFileCache.get(cacheKey);
  if (cached && cached.expiry > now) {
    return { id: cached.id, name: cached.name };
  }

  let files: { id: string; name: string }[];
  try {
    files = await listSharedPlSpreadsheetFileRefs();
  } catch (err) {
    console.warn(
      `[spreadsheet-revenue] Drive list (損益計算資料) failed for auto-resolve:`,
      err
    );
    return null;
  }

  const matches = files.filter((f) =>
    fileMatchesLocationPlTemplate(f.name, locationName, yearMonth)
  );
  if (matches.length === 0) return null;

  matches.sort((a, b) => a.name.localeCompare(b.name, "ja"));
  if (matches.length > 1) {
    console.warn(
      `[spreadsheet-revenue] multiple Drive files match 拠点「${locationName}」 ${yearMonth}: ${matches
        .map((x) => x.name)
        .join(" | ")} — using ${matches[0].name}`
    );
  }

  const chosen = matches[0]!;
  drivePlFileCache.set(cacheKey, {
    id: chosen.id,
    name: chosen.name,
    expiry: now + DRIVE_PL_FILE_CACHE_MS,
  });
  return chosen;
}

function pickRevenueWorkbookSheetName(
  sheetNames: string[],
  _yearMonth: string,
  configuredSheet: string | undefined
): string {
  const cfg = configuredSheet?.trim();
  if (cfg) {
    return pickSheetNameExact(sheetNames, cfg) ?? "";
  }
  /** PM: 損益計算資料ブックの既定読み取りシートは「売上明細」。無い場合のみ「車両別損益」。 */
  const urimei = pickSheetNameExact(sheetNames, "売上明細");
  if (urimei) return urimei;
  return pickSheetNameExact(sheetNames, "車両別損益") ?? "";
}

function locDigitsFromLocationCode(
  locationCode: string | null | undefined
): string | undefined {
  if (!locationCode?.trim()) return undefined;
  const m = /^LOC(\d+)$/i.exec(locationCode.trim());
  if (!m) return undefined;
  return m[1].padStart(3, "0");
}

/**
 * Excel 列ヘッダーの `1816` / `18-16` / `18-34` をシードの車両番号 `{loc}-NNN` と照合する。
 * 例: LOC015 + 列 `18-34` → DB `015-034`
 */
function inferIzumiVehicleNoFromColumnKey(
  locationCode: string | null | undefined,
  excelKey: unknown
): string | undefined {
  const loc = locDigitsFromLocationCode(locationCode);
  if (!loc) return undefined;

  let pair = "";
  if (typeof excelKey === "number" && Number.isFinite(excelKey)) {
    const n = Math.abs(Math.trunc(excelKey));
    if (n < 1000 || n > 9999) return undefined;
    const s = String(n).padStart(4, "0");
    pair = `${s.slice(0, 2)}-${s.slice(2)}`;
  } else {
    pair = cellString(excelKey);
    if (!pair) return undefined;
  }

  const m = /^(\d{1,3})-(\d{1,3})$/.exec(pair.replace(/\s/g, ""));
  if (!m) return undefined;
  const trailing = parseInt(m[2], 10);
  if (!Number.isFinite(trailing) || trailing < 1 || trailing > 999) return undefined;

  return `${loc}-${String(trailing).padStart(3, "0")}`;
}

function normalizeForFuzzyComparison(s: string): string {
  return s.replace(/[\s\u3000・/／]/g, "").toLowerCase();
}

function resolveVehicleByCourseLikeLabel(
  label: unknown,
  vehicles: Array<{
    id: string;
    vehicleNo: string;
    course?: { name: string | null } | null;
  }>,
  vehicleAllowed: Set<string>
): string | undefined {
  const raw = cellString(label);
  if (!raw || raw === "予備") return undefined;
  if (/^[0-9]/.test(raw) && raw.includes("-")) return undefined;

  const n = normalizeForFuzzyComparison(raw);
  if (n.length < 3) return undefined;

  for (const v of vehicles) {
    if (!vehicleAllowed.has(v.id)) continue;
    const cn = normalizeForFuzzyComparison(v.course?.name ?? "");
    if (cn.length >= 5 && (cn.includes(n) || n.includes(cn))) return v.id;
    const vn = normalizeForFuzzyComparison(v.vehicleNo);
    if (vn.length >= 6 && (vn.includes(n) || n.includes(vn))) return v.id;
  }
  return undefined;
}

function resolveIzumiColumnVehicle(
  vehicleKeyCell: unknown,
  opts: {
    vehicleAllowed: Set<string>;
    vehicleNoToId: Map<string, string>;
    vehicleLookup: Map<string, string>;
    vehicles: Array<{
      id: string;
      vehicleNo: string;
      course?: { name: string | null } | null;
    }>;
    locationCode: string | null | undefined;
  }
): string | undefined {
  const direct = resolveVehicleIdFromExcelKey(
    vehicleKeyCell,
    opts.vehicleLookup,
    opts.vehicleAllowed,
    opts.vehicleNoToId
  );
  if (direct) return direct;

  const inferred = inferIzumiVehicleNoFromColumnKey(
    opts.locationCode,
    vehicleKeyCell
  );
  if (inferred) {
    const id = opts.vehicleNoToId.get(inferred);
    if (id && opts.vehicleAllowed.has(id)) return id;
  }

  return resolveVehicleByCourseLikeLabel(
    vehicleKeyCell,
    opts.vehicles,
    opts.vehicleAllowed
  );
}

export interface GetRevenueFromSpreadsheetsParams {
  locationId: string;
  yearMonth: string;
  vehicleIds: string[];
  revenueAccountItemIds: string[];
}

function parseAmount(cell: unknown): number {
  if (cell == null || cell === "") return 0;
  if (typeof cell === "number") return Number.isFinite(cell) ? cell : 0;
  const cleaned = String(cell).replace(/,/g, "").trim();
  const lower = cleaned.toLowerCase();
  if (
    lower.startsWith("#error") ||
    lower.startsWith("#ref") ||
    lower.startsWith("#div") ||
    lower.includes(".infinity") ||
    lower === "-" ||
    lower.includes("(infinity)") ||
    lower.includes("(infinity")
  ) {
    return 0;
  }
  const n = Number(cleaned);
  return Number.isFinite(n) ? n : 0;
}

function cellString(cell: unknown): string {
  if (cell == null) return "";
  return String(cell).trim();
}

function pickSheetNameExact(workbookSheets: string[], sheetName: string): string | null {
  const trimmed = sheetName.trim();
  if (!trimmed) return null;
  const hit = workbookSheets.find((s) => s === trimmed);
  return hit ?? null;
}

function rowHasVehicleNoHeader(row: unknown[] | undefined): boolean {
  if (!row?.length) return false;
  return cellString(row[0]).toLowerCase() === "vehicleno";
}

/** Izumi template: first column `車両No` with percentage columns to the right. */
function isIzumiVehicleProfitHeaderRow(row: unknown[] | undefined): boolean {
  if (!row?.length) return false;
  const a = cellString(row[0]);
  return a === "車両No" || a === "車両NO";
}

/** Build vehicleNo → id plus common Excel variants (e.g. 1816 ⇄ 18-16). */
function vehicleLookupMaps(
  vehicles: { id: string; vehicleNo: string }[],
  vehicleAllowed: Set<string>
): Map<string, string> {
  const map = new Map<string, string>();

  function addKey(key: string, id: string): void {
    const k = key.trim();
    if (!k) return;
    if (!map.has(k)) map.set(k, id);
    const noHyphen = k.replace(/-/g, "");
    if (noHyphen !== k && !map.has(noHyphen)) map.set(noHyphen, id);
  }

  for (const v of vehicles) {
    if (!vehicleAllowed.has(v.id)) continue;
    const no = v.vehicleNo.trim();
    addKey(no, v.id);
    addKey(no.replace(/-/g, ""), v.id);

    if (/^\d{4}-\d{2}$/.test(no)) {
      const digits = no.replace(/\D/g, "");
      if (digits.length >= 3) addKey(digits.slice(-4).padStart(4, "0").slice(-4), v.id);
    }

    if (/^\d+$/.test(no) && no.length === 4 && !no.includes("-")) {
      addKey(`${no.slice(0, 2)}-${no.slice(2)}`, v.id);
    }
  }

  return map;
}

function resolveVehicleIdFromExcelKey(
  excelKey: unknown,
  vehicleLookup: Map<string, string>,
  vehicleAllowed: Set<string>,
  vehicleNoToId: Map<string, string>
): string | undefined {
  const rawOriginal = cellString(excelKey);
  if (!rawOriginal || rawOriginal === "予備") return undefined;

  let normalized = rawOriginal;
  if (!rawOriginal.includes("-")) {
    const onlyDigits = rawOriginal.replace(/\D/g, "");
    if (
      onlyDigits.length === 4 &&
      /^\d{4}$/.test(onlyDigits) &&
      /^(\d+[a-z]*)?$/i.test(rawOriginal.replace(/\s/g, ""))
    ) {
      normalized = `${onlyDigits.slice(0, 2)}-${onlyDigits.slice(2)}`;
    }
  }
  if (typeof excelKey === "number" && Number.isFinite(excelKey)) {
    const n = Math.abs(Math.trunc(excelKey));
    if (n >= 1000 && n <= 9999) {
      const s = String(n).padStart(4, "0").slice(-4);
      normalized = `${s.slice(0, 2)}-${s.slice(2)}`;
    }
  }

  const candidates = new Set<string>();
  candidates.add(rawOriginal);
  candidates.add(normalized);
  candidates.add(rawOriginal.replace(/-/g, ""));
  candidates.add(normalized.replace(/-/g, ""));

  for (const c of candidates) {
    const id = vehicleLookup.get(c) ?? vehicleNoToId.get(c);
    if (id && vehicleAllowed.has(id)) return id;
  }

  for (const [no, id] of vehicleNoToId) {
    if (!vehicleAllowed.has(id)) continue;
    if (no.replace(/-/g, "") === rawOriginal.replace(/-/g, "")) return id;
  }

  return undefined;
}

function findUrimeisaiHeaderRow(rows: unknown[][]): number {
  const max = Math.min(rows.length, 40);
  for (let r = 0; r < max; r++) {
    const row = rows[r];
    if (!row?.length) continue;
    let hasUnchin = false;
    let hasGetsudo = false;
    for (const cell of row) {
      const s = cellString(cell);
      if (s === "運賃") hasUnchin = true;
      if (s === "月額") hasGetsudo = true;
    }
    if (hasUnchin && hasGetsudo) return r;
  }
  return -1;
}

function urimeisaiGroupLabelForMonthColumn(
  headerRow: unknown[],
  monthColIdx: number
): string {
  let j = monthColIdx - 1;
  while (j >= 0) {
    const s = cellString(headerRow[j]);
    if (!s) {
      j--;
      continue;
    }
    if (URIMEISAI_SUBHEADER_LABELS.has(s)) {
      j--;
      continue;
    }
    return s;
  }
  return "";
}

function parseUrimeisaiDetailSheet(
  rows: unknown[][],
  sheetLabel: string,
  accountNameToId: Map<string, string>,
  revenueAllowed: Set<string>,
  vehicleNoToId: Map<string, string>,
  vehicleLookup: Map<string, string>,
  vehicleAllowed: Set<string>,
  locationCode: string | null | undefined
): Map<string, number> {
  const hdr = findUrimeisaiHeaderRow(rows);
  if (hdr < 0) {
    console.warn(
      `[spreadsheet-revenue] sheet "${sheetLabel}" 売上明細: 運賃・月額のヘッダ行が見つかりません`
    );
    return new Map();
  }

  const headerRow = rows[hdr] ?? [];
  const accountCol = new Map<string, number>();
  const dupMonthColWarned = new Set<string>();

  for (let c = 0; c < headerRow.length; c++) {
    if (cellString(headerRow[c]) !== "月額") continue;
    const groupLabel = urimeisaiGroupLabelForMonthColumn(headerRow, c);
    if (!groupLabel) continue;
    const accountName = URIMEISAI_GROUP_HEADER_TO_ACCOUNT_NAME[groupLabel];
    if (!accountName) {
      console.warn(
        `[spreadsheet-revenue] sheet "${sheetLabel}" 売上明細: グループ「${groupLabel}」に対応する勘定がありません（スキップ）`
      );
      continue;
    }
    const accountItemId = accountNameToId.get(accountName);
    if (!accountItemId || !revenueAllowed.has(accountItemId)) continue;
    if (accountCol.has(accountItemId) && !dupMonthColWarned.has(accountItemId)) {
      dupMonthColWarned.add(accountItemId);
      console.warn(
        `[spreadsheet-revenue] sheet "${sheetLabel}" 売上明細: 科目 ${accountName} に複数の月額列（最後を採用）`
      );
    }
    accountCol.set(accountItemId, c);
  }

  if (accountCol.size === 0) {
    console.warn(
      `[spreadsheet-revenue] sheet "${sheetLabel}" 売上明細: 月額列から売上科目を解決できませんでした`
    );
    return new Map();
  }

  const result = new Map<string, number>();
  const dupKeyWarned = new Set<string>();

  for (let r = hdr + 1; r < rows.length; r++) {
    const row = rows[r];
    if (!row?.length) continue;
    const label0 = cellString(row[0]);
    if (!label0) continue;
    if (label0 === "合計") break;
    if (URIMEISAI_ROW0_SKIP_LABELS.has(label0)) continue;

    let vid = resolveVehicleIdFromExcelKey(
      row[0],
      vehicleLookup,
      vehicleAllowed,
      vehicleNoToId
    );
    if (!vid) {
      const inferred = inferIzumiVehicleNoFromColumnKey(locationCode, row[0]);
      if (inferred) {
        const id = vehicleNoToId.get(inferred);
        if (id && vehicleAllowed.has(id)) vid = id;
      }
    }
    if (!vid) continue;

    for (const [accountItemId, colIdx] of accountCol) {
      if (colIdx >= row.length) continue;
      const amount = parseAmount(row[colIdx]);
      if (amount === 0) continue;
      const key = `${vid}-${accountItemId}`;
      if (result.has(key) && !dupKeyWarned.has(key)) {
        dupKeyWarned.add(key);
        console.warn(
          `[spreadsheet-revenue] sheet "${sheetLabel}" 売上明細: duplicate ${key}; last wins`
        );
      }
      result.set(key, amount);
    }
  }

  return result;
}

function parseCanonicalVehicleRowSheet(
  rows: unknown[][],
  sheetLabel: string,
  accountNameToId: Map<string, string>,
  revenueAllowed: Set<string>,
  vehicleNoToId: Map<string, string>,
  vehicleAllowed: Set<string>
): Map<string, number> {
  const headerRow = rows[0]!;
  let vehicleCol = -1;
  for (let c = 0; c < headerRow.length; c++) {
    if (cellString(headerRow[c]).toLowerCase() === "vehicleno") {
      vehicleCol = c;
      break;
    }
  }

  if (vehicleCol < 0) {
    console.warn(
      `[spreadsheet-revenue] sheet "${sheetLabel}" has no vehicleNo column`
    );
    return new Map();
  }

  const columnAccountIds: (string | undefined)[] = headerRow.map(
    (_h, colIdx) => {
      if (colIdx === vehicleCol) return undefined;
      const raw = cellString(headerRow[colIdx]);
      if (!raw) return undefined;
      const id = accountNameToId.get(raw);
      if (!id || !revenueAllowed.has(id)) return undefined;
      return id;
    }
  );

  const result = new Map<string, number>();
  const dupWarned = new Set<string>();

  for (let r = 1; r < rows.length; r++) {
    const row = rows[r];
    if (!row || row.length <= vehicleCol) continue;

    const vehicleNoKey = cellString(row[vehicleCol]);
    if (!vehicleNoKey) continue;

    const vehicleId = vehicleNoToId.get(vehicleNoKey);
    if (!vehicleId || !vehicleAllowed.has(vehicleId)) continue;

    for (let c = 0; c < columnAccountIds.length; c++) {
      const accountItemId = columnAccountIds[c];
      if (!accountItemId || c >= row.length) continue;

      const key = `${vehicleId}-${accountItemId}`;
      const amount = parseAmount(row[c]);
      if (amount === 0) continue;

      if (result.has(key) && !dupWarned.has(key)) {
        dupWarned.add(key);
        console.warn(
          `[spreadsheet-revenue] duplicate sheet entries for ${key}; last wins`
        );
      }
      result.set(key, amount);
    }
  }

  return result;
}

/** Izumi 「車両別損益」 pivot: alternating amount / （％） columns. */
function parseIzumiVehicleProfitSheet(
  rows: unknown[][],
  sheetLabel: string,
  accountNameToId: Map<string, string>,
  revenueAllowed: Set<string>,
  vehicleLookup: Map<string, string>,
  vehicleNoToId: Map<string, string>,
  vehicleAllowed: Set<string>,
  vehiclesDetailed: Array<{
    id: string;
    vehicleNo: string;
    course?: { name: string | null } | null;
  }>,
  locationCode: string | null | undefined,
  courseCodeToId: Map<string, string>
): { vehicle: Map<string, number>; course: Map<string, number> } {
  let hdr = -1;
  for (let r = 0; r < Math.min(rows.length, 80); r++) {
    if (isIzumiVehicleProfitHeaderRow(rows[r])) {
      hdr = r;
      break;
    }
  }
  if (hdr < 1) {
    console.warn(
      `[spreadsheet-revenue] sheet "${sheetLabel}" pivot: 「車両No」行が見つかりません`
    );
    return { vehicle: new Map(), course: new Map() };
  }

  const vehicleRowIdx = hdr - 1;
  const headerRow = rows[hdr]!;
  const vehicleLabels = rows[vehicleRowIdx] ?? [];
  const vehicleResult = new Map<string, number>();
  const courseResult = new Map<string, number>();
  const dupWarned = new Set<string>();

  for (
    let c = 1;
    c + 1 < headerRow.length;
  ) {
    const pctMarker = cellString(headerRow[c + 1]);
    const isPctPair =
      pctMarker === "（％）" || pctMarker === "(%)" || pctMarker === "%";
    if (!isPctPair) {
      c += 1;
      continue;
    }

    const amountCol = c;
    const vehicleKeyCell = vehicleLabels[amountCol];
    const courseCodeKey = cellString(vehicleKeyCell).trim();
    const courseId = courseCodeToId.get(courseCodeKey);

    if (courseId) {
      for (let r = hdr + 1; r < rows.length; r++) {
        const row = rows[r];
        if (!row) continue;
        const label = cellString(row[0]);
        if (!label) continue;
        const accountItemId = accountNameToId.get(label);
        if (!accountItemId || !revenueAllowed.has(accountItemId)) continue;

        const key = `${courseId}-${accountItemId}`;
        const amount = parseAmount(row[amountCol]);
        if (amount === 0) continue;

        if (courseResult.has(key) && !dupWarned.has(key)) {
          dupWarned.add(key);
          console.warn(
            `[spreadsheet-revenue] duplicate pivot course entries for ${key}; last wins`
          );
        }
        courseResult.set(key, amount);
      }
      c += 2;
      continue;
    }

    const vehicleId = resolveIzumiColumnVehicle(vehicleKeyCell, {
      vehicleAllowed,
      vehicleNoToId,
      vehicleLookup,
      vehicles: vehiclesDetailed,
      locationCode,
    });
    if (!vehicleId) continue;

    for (let r = hdr + 1; r < rows.length; r++) {
      const row = rows[r];
      if (!row) continue;
      const label = cellString(row[0]);
      if (!label) continue;
      const accountItemId = accountNameToId.get(label);
      if (!accountItemId || !revenueAllowed.has(accountItemId)) continue;

      const key = `${vehicleId}-${accountItemId}`;
      const amount = parseAmount(row[amountCol]);
      if (amount === 0) continue;

      if (vehicleResult.has(key) && !dupWarned.has(key)) {
        dupWarned.add(key);
        console.warn(
          `[spreadsheet-revenue] duplicate pivot entries for ${key}; last wins`
        );
      }
      vehicleResult.set(key, amount);
    }

    c += 2;
  }

  return { vehicle: vehicleResult, course: courseResult };
}

async function loadRevenueFromDbSnapshotIfSuccess(
  locationId: string,
  yearMonth: string,
  vehicleIds: string[],
  revenueAccountItemIds: string[]
): Promise<Map<string, number> | null> {
  const meta = await prisma.locationDriveSyncMeta.findUnique({
    where: {
      locationId_yearMonth: { locationId, yearMonth },
    },
  });
  if (!meta || meta.status !== "success") return null;

  const lines = await prisma.driveSpreadsheetRevenueLine.findMany({
    where: {
      locationId,
      yearMonth,
      vehicleId: { in: vehicleIds },
      accountItemId: { in: revenueAccountItemIds },
    },
  });
  const map = new Map<string, number>();
  for (const line of lines) {
    map.set(`${line.vehicleId}-${line.accountItemId}`, line.amount);
  }
  return map;
}

type DriveRevenueParseContext = {
  locationId: string;
  yearMonth: string;
  spreadsheetId: string;
  autoResolvedDriveFile: boolean;
  location: {
    spreadsheetRevenueSheet: string | null;
    code: string | null;
    name: string | null;
  } | null;
  vehicleIds: string[];
  revenueAccountItemIds: string[];
};

async function downloadAndParseRevenueMapFromDrive(
  ctx: DriveRevenueParseContext
): Promise<
  | { ok: true; map: Map<string, number>; courseMap: Map<string, number>; sheetName: string }
  | { ok: false; error: string }
> {
  const {
    locationId,
    yearMonth,
    spreadsheetId,
    autoResolvedDriveFile,
    location,
    vehicleIds,
    revenueAccountItemIds,
  } = ctx;

  const vehicleAllowed = new Set(vehicleIds);
  const revenueAllowed = new Set(revenueAccountItemIds);

  const [vehicles, accountItems, coursesAtLoc] = await Promise.all([
    prisma.vehicle.findMany({
      where: { id: { in: vehicleIds } },
      select: {
        id: true,
        vehicleNo: true,
        course: { select: { name: true } },
      },
    }),
    prisma.accountItem.findMany({
      where: { id: { in: revenueAccountItemIds } },
      select: { id: true, name: true },
    }),
    prisma.course.findMany({
      where: { locationId },
      select: { id: true, code: true },
    }),
  ]);

  const courseCodeToId = new Map<string, string>();
  for (const c of coursesAtLoc ?? []) {
    courseCodeToId.set(String(c.code).trim(), c.id);
  }

  const vehicleNoToId = new Map<string, string>();
  for (const v of vehicles) {
    vehicleNoToId.set(v.vehicleNo.trim(), v.id);
  }

  const accountNameToId = new Map<string, string>();
  for (const a of accountItems) {
    accountNameToId.set(a.name.trim(), a.id);
  }
  for (const [alias, canonical] of Object.entries(
    IZUMI_REVENUE_ROW_LABEL_TO_ACCOUNT_ITEM_NAME
  )) {
    const canonId = accountNameToId.get(canonical.trim());
    if (canonId) accountNameToId.set(alias, canonId);
  }

  const vehicleLookup = vehicleLookupMaps(vehicles, vehicleAllowed);

  try {
    void logSpreadsheetRevenue("info", "drive download start", {
      locationId,
      yearMonth,
      spreadsheetId,
      configuredSheet: location?.spreadsheetRevenueSheet?.trim() || null,
      autoResolvedDriveFile,
    });

    const buffer = await downloadDriveFileAsXlsxBuffer(spreadsheetId);
    const sheetNames = await readSheetNames(buffer);
    const sheetName = pickRevenueWorkbookSheetName(
      sheetNames,
      yearMonth,
      location?.spreadsheetRevenueSheet ?? undefined
    );

    if (!sheetName) {
      const cfg = location?.spreadsheetRevenueSheet?.trim();
      const triedDesc = cfg ? `configured="${cfg}"` : "売上明細, 車両別損益";
      console.warn(
        `[spreadsheet-revenue] no sheet resolved for "${yearMonth}" (tried: ${triedDesc}) in ${spreadsheetId}. Tabs: ${sheetNames.slice(0, 15).join(", ")}…`
      );
      void logSpreadsheetRevenue("warn", "no sheet tab matched yearMonth / configured tab", {
        locationId,
        yearMonth,
        spreadsheetId,
        tabCandidates: cfg ? cfg : "売上明細|車両別損益",
        configuredSheet: cfg || null,
        autoResolvedDriveFile,
        tabSample: sheetNames.slice(0, 20).join("|"),
        tabCount: sheetNames.length,
      });
      return { ok: false, error: "no_matching_sheet_tab" };
    }

    void logSpreadsheetRevenue("info", "sheet selected", {
      locationId,
      yearMonth,
      spreadsheetId,
      sheetName,
      tabCount: sheetNames.length,
    });

    const rows = (await readXlsxFile(buffer, {
      sheet: sheetName,
    })) as unknown[][];

    if (!rows.length) {
      void logSpreadsheetRevenue("warn", "selected sheet has zero rows", {
        locationId,
        yearMonth,
        sheetName,
      });
      return { ok: true, map: new Map(), courseMap: new Map(), sheetName };
    }

    let result: Map<string, number>;
    let courseMap = new Map<string, number>();
    if (sheetName.trim() === "売上明細") {
      result = parseUrimeisaiDetailSheet(
        rows,
        sheetName,
        accountNameToId,
        revenueAllowed,
        vehicleNoToId,
        vehicleLookup,
        vehicleAllowed,
        location?.code ?? undefined
      );
    } else if (rowHasVehicleNoHeader(rows[0])) {
      result = parseCanonicalVehicleRowSheet(
        rows,
        sheetName,
        accountNameToId,
        revenueAllowed,
        vehicleNoToId,
        vehicleAllowed
      );
    } else {
      const pivIdx = rows.findIndex(isIzumiVehicleProfitHeaderRow);
      if (pivIdx >= 1) {
        const parsedPivot = parseIzumiVehicleProfitSheet(
          rows,
          sheetName,
          accountNameToId,
          revenueAllowed,
          vehicleLookup,
          vehicleNoToId,
          vehicleAllowed,
          vehicles,
          location?.code ?? undefined,
          courseCodeToId
        );
        result = parsedPivot.vehicle;
        courseMap = parsedPivot.course;
      } else {
        console.warn(
          `[spreadsheet-revenue] sheet "${sheetName}" は 売上明細・vehicleNo 形式・Izumi 「車両No」ピボットのいずれでも読み取れませんでした。Location.spreadsheetRevenueSheet（例: 売上明細 / 車両別損益）と Drive のファイル構造を確認してください。`
        );
        void logSpreadsheetRevenue("warn", "sheet layout not recognized", {
          locationId,
          yearMonth,
          sheetName,
        });
        return { ok: false, error: "sheet_layout_not_recognized" };
      }
    }

    void logSpreadsheetRevenue("info", "parse complete", {
      locationId,
      yearMonth,
      sheetName,
      revenueCellCount: result.size,
      courseRevenueCellCount: courseMap.size,
    });
    return { ok: true, map: result, courseMap, sheetName };
  } catch (err) {
    console.error(
      `[spreadsheet-revenue] failed to read ${spreadsheetId} for ${yearMonth}:`,
      err
    );
    void logSpreadsheetRevenue("error", "read or parse failed", {
      locationId,
      yearMonth,
      spreadsheetId,
      error: err instanceof Error ? err.message : String(err),
    });
    return {
      ok: false,
      error: err instanceof Error ? err.message : String(err),
    };
  }
}

async function persistSyncFailureMeta(
  locationId: string,
  yearMonth: string,
  driveFileId: string | null,
  revenueSheetTab: string | null,
  errorMessage: string
): Promise<void> {
  const fid = driveFileId?.trim() ?? "";
  await prisma.locationDriveSyncMeta.upsert({
    where: {
      locationId_yearMonth: { locationId, yearMonth },
    },
    create: {
      locationId,
      yearMonth,
      driveFileId: fid,
      revenueSheetTab,
      status: "failed",
      errorMessage,
      syncedAt: new Date(),
      recordCount: 0,
    },
    update: {
      ...(fid ? { driveFileId: fid } : {}),
      revenueSheetTab,
      status: "failed",
      errorMessage,
      syncedAt: new Date(),
    },
  });
}

export type SyncSpreadsheetRevenueResult =
  | { ok: true; recordCount: number; driveFileId: string; sheetName: string | null }
  | { ok: false; error: string; driveFileId?: string };

/**
 * Download + parse Drive 損益 workbook for one 拠点/年月 and replace DB snapshot (lines + meta).
 * Uses all vehicles at the location and all spreadsheet-backed revenue account items effective for the month.
 */
export async function syncSpreadsheetRevenueForLocationYear(
  locationId: string,
  yearMonth: string
): Promise<SyncSpreadsheetRevenueResult> {
  const ym = yearMonth.trim();
  if (!/^\d{4}-\d{2}$/.test(ym)) {
    return { ok: false, error: "Invalid yearMonth (expected YYYY-MM)" };
  }

  const location = await prisma.location.findUnique({
    where: { id: locationId },
    select: {
      spreadsheetId: true,
      spreadsheetRevenueSheet: true,
      code: true,
      name: true,
    },
  });

  if (!location) {
    return { ok: false, error: "Location not found" };
  }

  let spreadsheetId = location.spreadsheetId?.trim() ?? "";
  let autoResolvedDriveFile = false;
  if (!spreadsheetId) {
    const resolved = await resolvePlSpreadsheetFileFromSharedDrive(
      locationId,
      location.name ?? "",
      ym
    );
    if (resolved) {
      spreadsheetId = resolved.id;
      autoResolvedDriveFile = true;
      void logSpreadsheetRevenue("info", "sync: auto-resolved Drive file", {
        locationId,
        yearMonth: ym,
        spreadsheetId,
        fileName: resolved.name,
      });
    }
  }

  if (!spreadsheetId) {
    const msg = `no spreadsheetId and no auto-matched 損益計算資料 for 拠点「${location.name ?? ""}」 ${ym}`;
    await persistSyncFailureMeta(locationId, ym, null, null, msg);
    return { ok: false, error: msg };
  }

  const [vehicles, revenueAccountItems] = await Promise.all([
    prisma.vehicle.findMany({
      where: { locationId },
      select: {
        id: true,
        vehicleNo: true,
        course: { select: { name: true } },
      },
    }),
    prisma.accountItem.findMany({
      where: {
        category: REVENUE_CATEGORY,
        name: { notIn: [...SPREADSHEET_SYNC_EXCLUDED_MANUAL_REVENUE_NAMES] },
        ...accountItemEffectiveWhere(ym),
      },
      select: { id: true, name: true },
    }),
  ]);

  const vehicleIds = vehicles.map((v) => v.id);
  const revenueAccountItemIds = revenueAccountItems.map((a) => a.id);

  const locSlice = {
    spreadsheetRevenueSheet: location.spreadsheetRevenueSheet,
    code: location.code,
    name: location.name,
  };

  if (vehicleIds.length === 0 || revenueAccountItemIds.length === 0) {
    const syncedAt = new Date();
    await prisma.$transaction(async (tx) => {
      await tx.driveSpreadsheetRevenueLine.deleteMany({
        where: { locationId, yearMonth: ym },
      });
      await tx.driveSpreadsheetRevenueCourseLine.deleteMany({
        where: { locationId, yearMonth: ym },
      });
      await tx.locationDriveSyncMeta.upsert({
        where: {
          locationId_yearMonth: { locationId, yearMonth: ym },
        },
        create: {
          locationId,
          yearMonth: ym,
          driveFileId: spreadsheetId,
          revenueSheetTab: null,
          status: "success",
          errorMessage: null,
          syncedAt,
          recordCount: 0,
        },
        update: {
          driveFileId: spreadsheetId,
          revenueSheetTab: null,
          status: "success",
          errorMessage: null,
          syncedAt,
          recordCount: 0,
        },
      });
    });
    await prisma.dataSyncLog.create({
      data: {
        source: "Google Drive",
        syncType: SPREADSHEET_REVENUE_SYNC_TYPE,
        recordCount: 0,
        yearMonth: ym,
        locationId,
      },
    });
    return {
      ok: true,
      recordCount: 0,
      driveFileId: spreadsheetId,
      sheetName: null,
    };
  }

  const parsed = await downloadAndParseRevenueMapFromDrive({
    locationId,
    yearMonth: ym,
    spreadsheetId,
    autoResolvedDriveFile,
    location: locSlice,
    vehicleIds,
    revenueAccountItemIds,
  });

  if (!parsed.ok) {
    await persistSyncFailureMeta(
      locationId,
      ym,
      spreadsheetId,
      null,
      parsed.error
    );
    return { ok: false, error: parsed.error, driveFileId: spreadsheetId };
  }

  const syncedAt = new Date();
  const lineRows: {
    locationId: string;
    yearMonth: string;
    vehicleId: string;
    accountItemId: string;
    amount: number;
  }[] = [];

  for (const [key, amount] of parsed.map) {
    if (amount === 0) continue;
    const dash = key.indexOf("-");
    if (dash < 1) continue;
    const vehicleId = key.slice(0, dash);
    const accountItemId = key.slice(dash + 1);
    if (!vehicleId || !accountItemId) continue;
    lineRows.push({
      locationId,
      yearMonth: ym,
      vehicleId,
      accountItemId,
      amount,
    });
  }

  const courseLineRows: {
    locationId: string;
    yearMonth: string;
    courseId: string;
    accountItemId: string;
    amount: number;
  }[] = [];

  for (const [key, amount] of parsed.courseMap) {
    if (amount === 0) continue;
    const dash = key.indexOf("-");
    if (dash < 1) continue;
    const courseId = key.slice(0, dash);
    const accountItemId = key.slice(dash + 1);
    if (!courseId || !accountItemId) continue;
    courseLineRows.push({
      locationId,
      yearMonth: ym,
      courseId,
      accountItemId,
      amount,
    });
  }

  await prisma.$transaction(async (tx) => {
    await tx.driveSpreadsheetRevenueLine.deleteMany({
      where: { locationId, yearMonth: ym },
    });
    await tx.driveSpreadsheetRevenueCourseLine.deleteMany({
      where: { locationId, yearMonth: ym },
    });
    if (lineRows.length > 0) {
      await tx.driveSpreadsheetRevenueLine.createMany({ data: lineRows });
    }
    if (courseLineRows.length > 0) {
      await tx.driveSpreadsheetRevenueCourseLine.createMany({
        data: courseLineRows,
      });
    }
    await tx.locationDriveSyncMeta.upsert({
      where: {
        locationId_yearMonth: { locationId, yearMonth: ym },
      },
      create: {
        locationId,
        yearMonth: ym,
        driveFileId: spreadsheetId,
        revenueSheetTab: parsed.sheetName,
        status: "success",
        errorMessage: null,
        syncedAt,
        recordCount: lineRows.length + courseLineRows.length,
      },
      update: {
        driveFileId: spreadsheetId,
        revenueSheetTab: parsed.sheetName,
        status: "success",
        errorMessage: null,
        syncedAt,
        recordCount: lineRows.length + courseLineRows.length,
      },
    });
  });

  await runCourseAllocationScope(ym, locationId);

  await prisma.dataSyncLog.create({
    data: {
      source: "Google Drive",
      syncType: SPREADSHEET_REVENUE_SYNC_TYPE,
      recordCount: lineRows.length + courseLineRows.length,
      yearMonth: ym,
      locationId,
    },
  });

  return {
    ok: true,
    recordCount: lineRows.length,
    driveFileId: spreadsheetId,
    sheetName: parsed.sheetName,
  };
}

export async function getRevenueFromSpreadsheets(
  params: GetRevenueFromSpreadsheetsParams
): Promise<Map<string, number>> {
  const { locationId, yearMonth, vehicleIds, revenueAccountItemIds } = params;

  void logSpreadsheetRevenue("info", "getRevenueFromSpreadsheets start", {
    locationId,
    yearMonth,
    vehicleCount: vehicleIds.length,
    revenueAccountItemCount: revenueAccountItemIds.length,
  });

  if (revenueAccountItemIds.length === 0 || vehicleIds.length === 0) {
    void logSpreadsheetRevenue("info", "skip: no vehicles or no revenue account items", {
      locationId,
      yearMonth,
    });
    return new Map();
  }

  const fromSnapshot = await loadRevenueFromDbSnapshotIfSuccess(
    locationId,
    yearMonth,
    vehicleIds,
    revenueAccountItemIds
  );
  if (fromSnapshot !== null) {
    void logSpreadsheetRevenue("info", "revenue from DB snapshot", {
      locationId,
      yearMonth,
      cellCount: fromSnapshot.size,
    });
    return fromSnapshot;
  }

  const snapshotOnly = process.env.SPREADSHEET_REVENUE_SNAPSHOT_ONLY === "true";
  if (snapshotOnly) {
    void logSpreadsheetRevenue("warn", "SPREADSHEET_REVENUE_SNAPSHOT_ONLY: no success snapshot", {
      locationId,
      yearMonth,
    });
    return new Map();
  }

  const location = await prisma.location.findUnique({
    where: { id: locationId },
    select: {
      spreadsheetId: true,
      spreadsheetRevenueSheet: true,
      code: true,
      name: true,
    },
  });

  let spreadsheetId = location?.spreadsheetId?.trim() ?? "";
  let autoResolvedDriveFile = false;
  if (!spreadsheetId) {
    const resolved = await resolvePlSpreadsheetFileFromSharedDrive(
      locationId,
      location?.name ?? "",
      yearMonth
    );
    if (resolved) {
      spreadsheetId = resolved.id;
      autoResolvedDriveFile = true;
      void logSpreadsheetRevenue("info", "auto-resolved Drive file from name pattern", {
        locationId,
        yearMonth,
        spreadsheetId,
        fileName: resolved.name,
      });
    }
  }

  if (!spreadsheetId) {
    console.warn(
      `[spreadsheet-revenue] location ${locationId}: no spreadsheetId and no shared 損益計算資料 file matching 拠点名「${location?.name ?? ""}」 and ${yearMonth}; returning empty revenue map`
    );
    void logSpreadsheetRevenue("warn", "no spreadsheetId and no auto-matched Drive file", {
      locationId,
      yearMonth,
      locationName: location?.name ?? null,
      locationCode: location?.code ?? null,
    });
    return new Map();
  }

  const parsed = await downloadAndParseRevenueMapFromDrive({
    locationId,
    yearMonth,
    spreadsheetId,
    autoResolvedDriveFile,
    location: location
      ? {
          spreadsheetRevenueSheet: location.spreadsheetRevenueSheet,
          code: location.code,
          name: location.name,
        }
      : null,
    vehicleIds,
    revenueAccountItemIds,
  });

  if (!parsed.ok) {
    return new Map();
  }

  return parsed.map;
}
