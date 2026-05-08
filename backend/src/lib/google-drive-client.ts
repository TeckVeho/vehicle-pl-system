import { drive_v3, google } from "googleapis";

const DRIVE_READONLY = "https://www.googleapis.com/auth/drive.readonly";

/**
 * Google Workspace MIME for native Google Sheets. These cannot be downloaded
 * via `alt=media` and must be exported (`files.export`) to a concrete format.
 */
export const MIME_GOOGLE_SHEETS = "application/vnd.google-apps.spreadsheet";

/** OOXML `.xlsx` MIME — both upload-on-Drive and Sheets-export target. */
export const MIME_XLSX =
  "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";

let driveClient: drive_v3.Drive | null = null;

/**
 * Returns a singleton, read-only Drive v3 client built from the service account
 * JSON in `GOOGLE_SERVICE_ACCOUNT_JSON`.
 *
 * Scope is `drive.readonly` — share each target file or parent folder with the
 * service account `client_email` as Viewer. The SA only sees what you share.
 */
export function getDriveClient(): drive_v3.Drive {
  if (driveClient) return driveClient;

  const credentialsJson = process.env.GOOGLE_SERVICE_ACCOUNT_JSON;
  if (!credentialsJson?.trim()) {
    throw new Error("[google-drive] GOOGLE_SERVICE_ACCOUNT_JSON is not set");
  }

  let credentials: Record<string, unknown>;
  try {
    credentials = JSON.parse(credentialsJson) as Record<string, unknown>;
  } catch (err) {
    throw new Error(
      `[google-drive] GOOGLE_SERVICE_ACCOUNT_JSON is not valid JSON: ${
        (err as Error).message
      }`
    );
  }

  const requiredFields = ["type", "client_email", "private_key"] as const;
  for (const field of requiredFields) {
    const value = credentials[field];
    if (typeof value !== "string" || !value.trim()) {
      throw new Error(
        `[google-drive] service account JSON missing field: ${field}`
      );
    }
  }
  if (credentials.type !== "service_account") {
    throw new Error(
      '[google-drive] credentials.type must be "service_account"'
    );
  }

  const auth = new google.auth.GoogleAuth({
    credentials,
    scopes: [DRIVE_READONLY],
  });

  driveClient = google.drive({ version: "v3", auth });
  return driveClient;
}

/**
 * Download a Drive file as an `.xlsx` Buffer.
 *
 * - Native Google Sheets (`MIME_GOOGLE_SHEETS`): fetched via `files.export` to
 *   `MIME_XLSX` since `alt=media` is not allowed for Workspace docs.
 * - Anything else (including already-`.xlsx` uploads): fetched via
 *   `files.get` with `alt=media`.
 *
 * Throws on Drive errors (auth, quota, not found, no permission, etc.).
 * Callers that want a soft-fail should wrap in try/catch and log.
 */
export async function downloadDriveFileAsXlsxBuffer(
  fileId: string
): Promise<Buffer> {
  if (!fileId?.trim()) {
    throw new Error("[google-drive] downloadDriveFileAsXlsxBuffer: empty fileId");
  }

  const drive = getDriveClient();

  const meta = await drive.files.get({
    fileId,
    fields: "mimeType, name",
    supportsAllDrives: true,
  });
  const mimeType = meta.data.mimeType ?? "";

  const response =
    mimeType === MIME_GOOGLE_SHEETS
      ? await drive.files.export(
          { fileId, mimeType: MIME_XLSX },
          { responseType: "arraybuffer" }
        )
      : await drive.files.get(
          { fileId, alt: "media", supportsAllDrives: true },
          { responseType: "arraybuffer" }
        );

  return Buffer.from(response.data as ArrayBuffer);
}

/** Marker in production workbook filenames (損益計算資料 templates). */
export const DRIVE_PL_FILENAME_MARKER = "損益計算資料";

export interface DriveFileRef {
  id: string;
  name: string;
}

/** Drive query string escape for values wrapped in single quotes. */
function driveQueryLiteral(value: string): string {
  return value.replace(/\\/g, "\\\\").replace(/'/g, "\\'");
}

async function listDriveFilesByQuery(
  drive: drive_v3.Drive,
  q: string
): Promise<DriveFileRef[]> {
  const out: DriveFileRef[] = [];
  let pageToken: string | undefined;
  do {
    const res = await drive.files.list({
      q,
      fields: "nextPageToken, files(id, name)",
      pageSize: 100,
      pageToken,
      supportsAllDrives: true,
      includeItemsFromAllDrives: true,
    });
    for (const f of res.data.files ?? []) {
      if (f.id && f.name) out.push({ id: f.id, name: f.name });
    }
    pageToken = res.data.nextPageToken ?? undefined;
  } while (pageToken);
  return out;
}

/**
 * Folder ID for 損益計算資料 workbook discovery. First non-whitespace wins:
 * `GOOGLE_DRIVE_FOLDER_ID`, then `google_drive_folder_id`.
 * Share that folder with the service account `client_email` as Viewer.
 */
export function getGoogleDriveFolderIdFromEnv(): string | undefined {
  const upper = process.env.GOOGLE_DRIVE_FOLDER_ID?.trim();
  const lower = process.env.google_drive_folder_id?.trim();
  const v = upper || lower;
  return v ? v : undefined;
}

/**
 * Lists workbook files whose name contains {@link DRIVE_PL_FILENAME_MARKER}.
 *
 * **Requires** {@link getGoogleDriveFolderIdFromEnv} to be set: lists direct
 * children of that folder only. Without a folder ID, returns an empty array
 * (no Drive calls, no `sharedWithMe` fallback).
 *
 * Paginates when listing; never throws solely for “no folder”.
 */
export async function listSharedPlSpreadsheetFileRefs(): Promise<DriveFileRef[]> {
  const folderId = getGoogleDriveFolderIdFromEnv();
  if (!folderId) return [];

  const drive = getDriveClient();
  const marker = driveQueryLiteral(DRIVE_PL_FILENAME_MARKER);
  const q = `'${driveQueryLiteral(folderId)}' in parents and trashed = false and name contains '${marker}'`;
  return listDriveFilesByQuery(drive, q);
}

/** Test-only: clear the singleton between cases. */
export function resetGoogleDriveClientForTests(): void {
  driveClient = null;
}
