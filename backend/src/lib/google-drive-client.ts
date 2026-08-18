import { GoogleAuth, OAuth2Client } from "google-auth-library";

const DRIVE_API = "https://www.googleapis.com/drive/v3";
const DRIVE_READONLY = "https://www.googleapis.com/auth/drive.readonly";

/**
 * Google Workspace MIME for native Google Sheets. These cannot be downloaded
 * via `alt=media` and must be exported (`files.export`) to a concrete format.
 */
export const MIME_GOOGLE_SHEETS = "application/vnd.google-apps.spreadsheet";

/** OOXML `.xlsx` MIME — both upload-on-Drive and Sheets-export target. */
export const MIME_XLSX =
  "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";

let authClient: OAuth2Client | null = null;

function loadServiceAccountCredentials(): Record<string, unknown> {
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

  return credentials;
}

async function getAuthClient(): Promise<OAuth2Client> {
  if (authClient) return authClient;

  const credentials = loadServiceAccountCredentials();
  const googleAuth = new GoogleAuth({
    credentials,
    scopes: [DRIVE_READONLY],
  });
  authClient = (await googleAuth.getClient()) as OAuth2Client;
  return authClient;
}

async function driveRequest<T>(opts: {
  path: string;
  params?: Record<string, string | number | boolean | undefined>;
  responseType?: "json" | "arraybuffer";
}): Promise<T> {
  const auth = await getAuthClient();
  const res = await auth.request<T>({
    url: `${DRIVE_API}${opts.path}`,
    method: "GET",
    params: opts.params,
    responseType: opts.responseType ?? "json",
  });
  return res.data;
}

/**
 * Validates Drive credentials and returns the auth client (REST, no googleapis).
 * Share each target file or parent folder with the service account `client_email`.
 */
export async function getDriveClient(): Promise<OAuth2Client> {
  return getAuthClient();
}

interface DriveFileListResponse {
  files?: Array<{ id?: string; name?: string; mimeType?: string; size?: string }>;
  nextPageToken?: string;
}

interface DriveFileMetaResponse {
  id?: string;
  name?: string;
  mimeType?: string;
}

/**
 * Download a Drive file as an `.xlsx` Buffer.
 *
 * - Native Google Sheets (`MIME_GOOGLE_SHEETS`): fetched via `files.export` to
 *   `MIME_XLSX` since `alt=media` is not allowed for Workspace docs.
 * - Anything else (including already-`.xlsx` uploads): fetched via
 *   `files.get` with `alt=media`.
 */
export async function downloadDriveFileAsXlsxBuffer(
  fileId: string
): Promise<Buffer> {
  if (!fileId?.trim()) {
    throw new Error("[google-drive] downloadDriveFileAsXlsxBuffer: empty fileId");
  }

  const meta = await driveRequest<DriveFileMetaResponse>({
    path: `/files/${encodeURIComponent(fileId)}`,
    params: {
      fields: "mimeType, name",
      supportsAllDrives: true,
    },
  });
  const mimeType = meta.mimeType ?? "";

  const payload =
    mimeType === MIME_GOOGLE_SHEETS
      ? await driveRequest<ArrayBuffer>({
          path: `/files/${encodeURIComponent(fileId)}/export`,
          params: { mimeType: MIME_XLSX },
          responseType: "arraybuffer",
        })
      : await driveRequest<ArrayBuffer>({
          path: `/files/${encodeURIComponent(fileId)}`,
          params: { alt: "media", supportsAllDrives: true },
          responseType: "arraybuffer",
        });

  return Buffer.from(payload);
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

async function listDriveFilesByQuery(q: string): Promise<DriveFileRef[]> {
  const out: DriveFileRef[] = [];
  let pageToken: string | undefined;
  do {
    const res = await driveRequest<DriveFileListResponse>({
      path: "/files",
      params: {
        q,
        fields: "nextPageToken, files(id, name)",
        pageSize: 100,
        pageToken,
        supportsAllDrives: true,
        includeItemsFromAllDrives: true,
      },
    });
    for (const f of res.files ?? []) {
      if (f.id && f.name) out.push({ id: f.id, name: f.name });
    }
    pageToken = res.nextPageToken;
  } while (pageToken);
  return out;
}

/**
 * Folder ID for 損益計算資料 workbook discovery. First non-whitespace wins:
 * `GOOGLE_DRIVE_FOLDER_ID`, then `google_drive_folder_id`.
 */
export function getGoogleDriveFolderIdFromEnv(): string | undefined {
  const upper = process.env.GOOGLE_DRIVE_FOLDER_ID?.trim();
  const lower = process.env.google_drive_folder_id?.trim();
  const v = upper || lower;
  return v ? v : undefined;
}

/**
 * Lists workbook files whose name contains {@link DRIVE_PL_FILENAME_MARKER}.
 * Requires folder ID env; returns [] when unset.
 */
export async function listSharedPlSpreadsheetFileRefs(): Promise<DriveFileRef[]> {
  const folderId = getGoogleDriveFolderIdFromEnv();
  if (!folderId) return [];

  const marker = driveQueryLiteral(DRIVE_PL_FILENAME_MARKER);
  const q = `'${driveQueryLiteral(folderId)}' in parents and trashed = false and name contains '${marker}'`;
  return listDriveFilesByQuery(q);
}

/**
 * Lists spreadsheet files that are direct children of `folderId`.
 */
export async function listSpreadsheetsInFolder(
  folderId: string
): Promise<DriveFileRef[]> {
  if (!folderId?.trim()) {
    throw new Error("[google-drive] listSpreadsheetsInFolder: empty folderId");
  }

  const parent = driveQueryLiteral(folderId.trim());
  const q = `'${parent}' in parents and trashed = false and (mimeType = '${MIME_GOOGLE_SHEETS}' or mimeType = '${MIME_XLSX}')`;
  const refs = await listDriveFilesByQuery(q);
  refs.sort((a, b) => a.name.localeCompare(b.name, "ja"));
  return refs;
}

/** Lists direct children of a Drive folder (metadata only). */
export async function listFilesInFolder(
  folderId: string,
  opts?: { pageSize?: number }
): Promise<Array<{ id?: string; name?: string; mimeType?: string; size?: string }>> {
  if (!folderId?.trim()) {
    throw new Error("[google-drive] listFilesInFolder: empty folderId");
  }

  const res = await driveRequest<DriveFileListResponse>({
    path: "/files",
    params: {
      q: `'${driveQueryLiteral(folderId.trim())}' in parents and trashed = false`,
      fields: "files(id, name, mimeType, size)",
      pageSize: opts?.pageSize ?? 25,
      supportsAllDrives: true,
      includeItemsFromAllDrives: true,
    },
  });
  return res.files ?? [];
}

export async function getDriveFileMeta(fileId: string): Promise<DriveFileRef> {
  if (!fileId?.trim()) {
    throw new Error("[google-drive] getDriveFileMeta: empty fileId");
  }

  const res = await driveRequest<DriveFileMetaResponse>({
    path: `/files/${encodeURIComponent(fileId.trim())}`,
    params: {
      fields: "id, name",
      supportsAllDrives: true,
    },
  });

  const id = res.id;
  const name = res.name;
  if (!id || !name) {
    throw new Error(`[google-drive] getDriveFileMeta: missing id/name for ${fileId}`);
  }

  return { id, name };
}

/** Test-only: clear the singleton between cases. */
export function resetGoogleDriveClientForTests(): void {
  authClient = null;
}
