#!/usr/bin/env bash
# Create dev Secret Manager secrets for izumi-vpl (idempotent).
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
# shellcheck source=/dev/null
source "${ROOT}/google-cloud/scripts/load-env.sh"

PROJECT="${GCP_PROJECT_ID}"
SUFFIX="${SUFFIX:-dev}"

create_secret_if_missing() {
  local secret_id="$1"
  local payload="$2"
  if gcloud secrets describe "$secret_id" --project="$PROJECT" &>/dev/null; then
    echo "exists: $secret_id"
    return 0
  fi
  printf '%s' "$payload" | gcloud secrets create "$secret_id" \
    --project="$PROJECT" \
    --replication-policy=automatic \
    --data-file=-
  echo "created: $secret_id"
}

JWT_SECRET="${JWT_SECRET:-$(openssl rand -base64 48)}"
create_secret_if_missing "izumi-vpl-jwt-secret-${SUFFIX}" "$JWT_SECRET"

if ! gcloud secrets describe "izumi-vpl-google-sa-json-${SUFFIX}" --project="$PROJECT" &>/dev/null; then
  if [[ -n "${GOOGLE_SERVICE_ACCOUNT_JSON:-}" ]]; then
    create_secret_if_missing "izumi-vpl-google-sa-json-${SUFFIX}" "$GOOGLE_SERVICE_ACCOUNT_JSON"
  else
  PLACEHOLDER='{"type":"service_account","project_id":"REPLACE","private_key_id":"REPLACE","private_key":"REPLACE","client_email":"REPLACE","client_id":"REPLACE"}'
  create_secret_if_missing "izumi-vpl-google-sa-json-${SUFFIX}" "$PLACEHOLDER"
  echo "WARN: update izumi-vpl-google-sa-json-${SUFFIX} with real service account JSON before Drive sync works." >&2
  fi
fi

if ! gcloud secrets describe "izumi-vpl-google-drive-folder-${SUFFIX}" --project="$PROJECT" &>/dev/null; then
  FOLDER_ID="${GOOGLE_DRIVE_FOLDER_ID:-REPLACE_WITH_DRIVE_FOLDER_ID}"
  create_secret_if_missing "izumi-vpl-google-drive-folder-${SUFFIX}" "$FOLDER_ID"
  if [[ "$FOLDER_ID" == REPLACE_WITH_DRIVE_FOLDER_ID ]]; then
    echo "WARN: update izumi-vpl-google-drive-folder-${SUFFIX} with real Google Drive folder ID." >&2
  fi
fi

echo "Done. JWT for dev (save if new): ${JWT_SECRET}"
