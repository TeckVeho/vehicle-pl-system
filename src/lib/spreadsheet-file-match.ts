/** Marker in production workbook filenames (損益計算資料 templates). */
export const DRIVE_PL_FILENAME_MARKER = "損益計算資料";

export interface SpreadsheetFileRef {
  id: string;
  name: string;
}

/**
 * True when fileName contains YYYY.MM / YYYY-MM for the given yearMonth.
 * Uses boundary checks so e.g. 2026.02 does not match 2026.20.
 */
export function fileNameIncludesYearMonth(
  fileName: string,
  yearMonth: string
): boolean {
  const m = /^(\d{4})-(\d{2})$/.exec(yearMonth.trim());
  if (!m) return false;
  const y = m[1];
  const mo = m[2];
  const moNoPad = String(parseInt(mo, 10));
  const patterns = [
    `${y}\\.${mo}(?![0-9])`,
    `${y}-${mo}(?![0-9])`,
    `${y}\\.${moNoPad}(?![0-9])`,
    `${y}-${moNoPad}(?![0-9])`,
  ];
  return patterns.some((p) => new RegExp(p).test(fileName));
}

export function fileMatchesLocationPlTemplate(
  fileName: string,
  locationName: string,
  yearMonth: string
): boolean {
  const loc = locationName.trim();
  if (!loc) return false;
  if (!fileName.includes(DRIVE_PL_FILENAME_MARKER)) return false;
  if (!fileName.includes(loc)) return false;
  return fileNameIncludesYearMonth(fileName, yearMonth);
}

/** Best match for one location; first after ja sort when multiple. */
export function findSpreadsheetForLocation(
  spreadsheets: SpreadsheetFileRef[],
  locationName: string,
  yearMonth: string
): SpreadsheetFileRef | undefined {
  const matches = spreadsheets.filter((s) =>
    fileMatchesLocationPlTemplate(s.name, locationName, yearMonth)
  );
  if (matches.length === 0) return undefined;
  matches.sort((a, b) => a.name.localeCompare(b.name, "ja"));
  return matches[0];
}
