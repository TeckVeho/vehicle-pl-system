/**
 * Smoke-test Google Drive access for a folder.
 *
 * Prerequisites:
 * - GCP: Google Drive API enabled for the service account's project.
 * - Share the folder (or files) with the SA client_email as Viewer.
 * - GOOGLE_SERVICE_ACCOUNT_JSON set (same as backend).
 *
 * The first file in the folder is downloaded as `.xlsx` via the shared
 * `downloadDriveFileAsXlsxBuffer` helper (native Google Sheets are exported,
 * uploaded `.xlsx` files use `alt=media`). Non-Sheets / non-`.xlsx` files
 * still pass through `alt=media`, but the resulting buffer may not be a
 * valid spreadsheet.
 *
 * Run from backend/:
 *   npx tsx --env-file=.env scripts/drive-folder-smoke.ts
 *   npx tsx --env-file=.env scripts/drive-folder-smoke.ts <OTHER_FOLDER_ID>
 *   npx tsx --env-file=.env scripts/drive-folder-smoke.ts --spreadsheets-only <FOLDER_ID>
 *   SMOKE_DRIVE_FOLDER_ID=<id> npx tsx --env-file=.env scripts/drive-folder-smoke.ts
 *
 * If `--env-file` is unsupported, export GOOGLE_SERVICE_ACCOUNT_JSON in the shell first.
 */
import {
  downloadDriveFileAsXlsxBuffer,
  getDriveClient,
  listSpreadsheetsInFolder,
} from "../src/lib/google-drive-client.js";

/** Test folder used by the smoke script. Override with CLI arg or SMOKE_DRIVE_FOLDER_ID. */
const DEFAULT_FOLDER = "1FFhlsFB90y-PrCVQCr3-ROLi5bcinTt5";

function parseArgs(argv: string[]): { folderId: string; spreadsheetsOnly: boolean } {
  const spreadsheetsOnly = argv.includes("--spreadsheets-only");
  const positional = argv.filter((arg) => arg !== "--spreadsheets-only");
  const folderId =
    process.env.SMOKE_DRIVE_FOLDER_ID?.trim() ||
    positional[2]?.trim() ||
    DEFAULT_FOLDER;
  return { folderId, spreadsheetsOnly };
}

async function main(): Promise<void> {
  const { folderId, spreadsheetsOnly } = parseArgs(process.argv);

  if (spreadsheetsOnly) {
    const spreadsheets = await listSpreadsheetsInFolder(folderId);
    console.log(
      `Found ${spreadsheets.length} spreadsheet(s) in folder ${folderId}:\n`
    );
    for (const s of spreadsheets) {
      console.log(`- ${s.name}\n  id=${s.id}`);
    }
    return;
  }

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
    console.log(
      `- ${f.name}\n  id=${f.id} mime=${f.mimeType} size=${f.size ?? "n/a"}`
    );
  }

  const firstWithId = files.find((f) => f.id);
  if (!firstWithId?.id) {
    console.log("\nNo files to download (empty folder or no permission).");
    return;
  }

  console.log(`\nDownloading first file (${firstWithId.name}) as .xlsx…`);
  const buf = await downloadDriveFileAsXlsxBuffer(firstWithId.id);
  console.log(`OK: ${buf.byteLength} bytes received (xlsx buffer).`);
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
