#!/usr/bin/env bash
# Create or update the bundled app secrets secret (JWT, Google SA JSON, Drive folder) in Secret Manager.
#
# Usage:
#   bash google-cloud/scripts/create-app-secrets-bundle.sh dev --data-file /path/to/app-secrets.dev.env
#
# Expected keys in the data file:
#   JWT_SECRET
#   GOOGLE_SERVICE_ACCOUNT_JSON
#   GOOGLE_DRIVE_FOLDER_ID

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
# shellcheck source=/dev/null
source "${ROOT}/google-cloud/scripts/load-env.sh"

ENV_SUFFIX="${1:-}"
shift || true

PROJECT_ID="${PROJECT_ID:-${GCP_PROJECT_ID}}"
REGION="${REGION:-${GCP_REGION}}"
DATA_FILE=""

usage() {
  echo "Usage: $0 {dev|stg|prod} [--project PROJECT_ID] [--data-file PATH]" >&2
  exit 1
}

if [[ -z "${ENV_SUFFIX}" || ! "${ENV_SUFFIX}" =~ ^(dev|stg|prod)$ ]]; then
  usage
fi

while [[ $# -gt 0 ]]; do
  case "$1" in
    --project)
      PROJECT_ID="${2:-}"
      shift 2
      ;;
    --data-file)
      DATA_FILE="${2:-}"
      shift 2
      ;;
    *)
      echo "Unknown argument: $1" >&2
      usage
      ;;
  esac
done

SECRET_ID="izumi-vpl-app-secrets-${ENV_SUFFIX}"

if [[ -z "${DATA_FILE}" || ! -f "${DATA_FILE}" ]]; then
  echo "ERROR: --data-file is required and must exist." >&2
  echo "Example file contents:" >&2
  cat >&2 <<'EOF'
JWT_SECRET=your-long-random-jwt-secret
GOOGLE_SERVICE_ACCOUNT_JSON={"type":"service_account",...}
GOOGLE_DRIVE_FOLDER_ID=your-folder-id
EOF
  exit 1
fi

required_keys=(JWT_SECRET GOOGLE_SERVICE_ACCOUNT_JSON GOOGLE_DRIVE_FOLDER_ID)
for key in "${required_keys[@]}"; do
  if ! grep -q "^${key}=" "${DATA_FILE}"; then
    echo "ERROR: ${DATA_FILE} is missing required key: ${key}" >&2
    exit 1
  fi
done

if ! gcloud secrets describe "${SECRET_ID}" --project="${PROJECT_ID}" >/dev/null 2>&1; then
  echo "Creating secret ${SECRET_ID} in ${PROJECT_ID} (user-managed, ${REGION})..."
  gcloud secrets create "${SECRET_ID}" \
    --project="${PROJECT_ID}" \
    --replication-policy=user-managed \
    --locations="${REGION}"
else
  echo "Secret ${SECRET_ID} already exists in ${PROJECT_ID}."
fi

echo "Adding secret version to ${SECRET_ID}..."
gcloud secrets versions add "${SECRET_ID}" \
  --project="${PROJECT_ID}" \
  --data-file="${DATA_FILE}"

echo "Done. Set in terraform.tfvars:"
echo "  app_secrets_bundle_secret_id = \"${SECRET_ID}\""
echo "  api_secret_env_from_sm = []"
echo ""
echo "Then: terragrunt apply in live/${ENV_SUFFIX}/app (API image must call loadAppSecrets)."
