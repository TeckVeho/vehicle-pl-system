/**
 * Smoke-test Google Drive access for a folder.
 *
 * Prerequisites:
 * - GCP: Google Drive API enabled for the service account's project.
 * - Share the folder (or files) with the SA client_email as Viewer.
 * - Native Google Docs/Sheets/Slides are exercised via `files.export`, not `alt=media`.
 * - GOOGLE_SERVICE_ACCOUNT_JSON set (same as backend).
 *
 * Run from backend/:
 *   npx tsx --env-file=.env scripts/drive-folder-smoke.ts
 *   npx tsx --env-file=.env scripts/drive-folder-smoke.ts <OTHER_FOLDER_ID>
 *
 * If `--env-file` is unsupported, export GOOGLE_SERVICE_ACCOUNT_JSON in the shell first.
 */
import { getDriveClient } from "../src/lib/google-drive-client.js";

const DEFAULT_FOLDER = "1FFhlsFB90y-PrCVQCr3-ROLi5bcinTt5";

const MIME_SHEETS = "application/vnd.google-apps.spreadsheet";
const MIME_DOCS = "application/vnd.google-apps.document";
const MIME_SLIDES = "application/vnd.google-apps.presentation";

/** Google-native files cannot use `alt=media`; use `files.export` with a concrete MIME. */
function exportMimeForGoogleWorkspace(
  mimeType: string | null | undefined
): string | null {
  switch (mimeType) {
    case MIME_SHEETS:
      return "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
    case MIME_DOCS:
      return "application/pdf";
    case MIME_SLIDES:
      return "application/pdf";
    default:
      return null;
  }
}

async function main(): Promise<void> {
  const folderId = process.argv[2]?.trim() || DEFAULT_FOLDER;
  const drive = getDriveClient();

  const list = await drive.files.list({
    q: `'${folderId}' in parents and trashed = false`,
    fields: "files(id, name, mimeType, size)",
    pageSize: 25,
    supportsAllDrives: true,
    includeItemsFromAllDrives: true,
  });

  const files = list.data.files ?? [];
  console.log(`Found ${files.length} item(s) in folder ${folderId}:\n`);
  for (const f of files) {
    console.log(`- ${f.name}\n  id=${f.id} mime=${f.mimeType} size=${f.size ?? "n/a"}`);
  }

  const firstWithId = files.find((f) => f.id);
  if (!firstWithId?.id) {
    console.log("\nNo files to download (empty folder or no permission).");
    return;
  }

  const exportMime = exportMimeForGoogleWorkspace(firstWithId.mimeType);
  const label = exportMime ? "Exporting" : "Downloading";
  console.log(`\n${label} first file (${firstWithId.name})…`);

  const media = exportMime
    ? await drive.files.export(
        { fileId: firstWithId.id, mimeType: exportMime },
        { responseType: "arraybuffer" }
      )
    : await drive.files.get(
        {
          fileId: firstWithId.id,
          alt: "media",
          supportsAllDrives: true,
        },
        { responseType: "arraybuffer" }
      );

  const buf = media.data as ArrayBuffer;
  console.log(`OK: ${buf.byteLength} bytes received (binary payload).`);
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
