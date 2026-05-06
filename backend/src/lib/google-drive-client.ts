import { google } from "googleapis";

const DRIVE_READONLY = "https://www.googleapis.com/auth/drive.readonly";

let driveClient: ReturnType<typeof google.drive> | null = null;

export function getDriveClient(): ReturnType<typeof google.drive> {
  if (driveClient) return driveClient;

  const credentialsJson = process.env.GOOGLE_SERVICE_ACCOUNT_JSON;
  if (!credentialsJson?.trim()) {
    throw new Error("[google-drive] GOOGLE_SERVICE_ACCOUNT_JSON is not set");
  }

  const credentials = JSON.parse(credentialsJson) as Record<string, unknown>;
  const auth = new google.auth.GoogleAuth({
    credentials,
    scopes: [DRIVE_READONLY],
  });

  driveClient = google.drive({ version: "v3", auth });
  return driveClient;
}

export function resetGoogleDriveClientForTests(): void {
  driveClient = null;
}
