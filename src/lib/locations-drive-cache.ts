import type { SpreadsheetFileRef } from "@/lib/spreadsheet-file-match";

const STORAGE_KEY = "vpl-locations-drive-cache";

export interface LocationsDriveCache {
  folderId: string;
  spreadsheets: SpreadsheetFileRef[];
}

export function saveLocationsDriveCache(cache: LocationsDriveCache): void {
  try {
    sessionStorage.setItem(STORAGE_KEY, JSON.stringify(cache));
  } catch {
    /* quota / private mode */
  }
}

export function loadLocationsDriveCache(): LocationsDriveCache | null {
  try {
    const raw = sessionStorage.getItem(STORAGE_KEY);
    if (!raw) return null;
    const parsed = JSON.parse(raw) as LocationsDriveCache;
    if (
      typeof parsed.folderId !== "string" ||
      !Array.isArray(parsed.spreadsheets)
    ) {
      return null;
    }
    const spreadsheets = parsed.spreadsheets.filter(
      (s): s is SpreadsheetFileRef =>
        typeof s?.id === "string" && typeof s?.name === "string"
    );
    return { folderId: parsed.folderId, spreadsheets };
  } catch {
    return null;
  }
}
