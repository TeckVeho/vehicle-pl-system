import fs from "node:fs";
import dotenv from "dotenv";

const DEFAULT_SECRETS_FILE = "/secrets/app.env";

/**
 * Load bundled app secrets from a .env file mounted by Cloud Run (Secret Manager volume).
 * No-op when the file is missing (local dev uses --env-file=.env or backend/.env).
 */
export function loadAppSecrets(): void {
  const secretsFile = process.env.APP_SECRETS_FILE?.trim() || DEFAULT_SECRETS_FILE;
  if (!fs.existsSync(secretsFile)) {
    return;
  }
  dotenv.config({ path: secretsFile, override: false });
}
