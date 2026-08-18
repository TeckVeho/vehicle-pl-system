#!/usr/bin/env bash
# Import vehicle-pl SQL dump into Cloud SQL (dev hub or legacy instance).
#
# Dev hub (after issue #99 cutover):
#   SQL_INSTANCE=dev-sql-hub SQL_PROJECT=gcp-dev-sql-hub SQL_DATABASE=izumi-vpl-dev \
#     bash google-cloud/scripts/import-vehicle-pl-db-dev.sh /path/to/dump.sql
#
# Legacy dev instance:
#   bash google-cloud/scripts/import-vehicle-pl-db-dev.sh /path/to/dump.sql
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=load-env.sh
source "${SCRIPT_DIR}/load-env.sh"

DUMP_PATH="${1:-}"
PROJECT="${SQL_PROJECT:-${GCP_PROJECT_ID}}"
INSTANCE="${SQL_INSTANCE:-izumi-vpl-mysql-dev}"
DATABASE="${SQL_DATABASE:-izumi-vehicle-pl-system}"
RECREATE_DATABASE="${RECREATE_DATABASE:-false}"
GCS_BUCKET="${GCS_IMPORT_BUCKET:-${GCP_STATE_BUCKET}}"
GCS_OBJECT="db-imports/$(basename "${DUMP_PATH:-izumi-vehicle-pl-stage.sql}")"

if [[ -z "${DUMP_PATH}" || ! -f "${DUMP_PATH}" ]]; then
  echo "Usage: $0 /path/to/izumi-vehicle-pl-stage.sql" >&2
  exit 1
fi

echo "==> Project: ${PROJECT}"
echo "==> Instance: ${INSTANCE}"
echo "==> Database: ${DATABASE}"
echo "==> Dump: ${DUMP_PATH}"

WORK="$(mktemp -d)"
trap 'rm -rf "${WORK}"' EXIT
PREPARED="${WORK}/import.sql"

echo "==> Preparing dump (strip GTID / DEFINER for Cloud SQL)..."
sed -E \
  -e '/^SET @@GLOBAL.GTID_PURGED=/d' \
  -e '/^SET @@SESSION.SQL_LOG_BIN=/d' \
  -e 's/DEFINER=`[^`]+`@`[^`]+`/DEFINER=CURRENT_USER/g' \
  "${DUMP_PATH}" > "${PREPARED}"

if [[ "${RECREATE_DATABASE}" == "true" ]]; then
  echo "==> Recreating database ${DATABASE}..."
  gcloud sql databases delete "${DATABASE}" \
    --instance="${INSTANCE}" \
    --project="${PROJECT}" \
    --quiet 2>/dev/null || true
  gcloud sql databases create "${DATABASE}" \
    --instance="${INSTANCE}" \
    --project="${PROJECT}" \
    --charset=utf8mb4 \
    --collation=utf8mb4_0900_ai_ci
fi

echo "==> Ensuring Cloud SQL instance is RUNNABLE..."
STATE="$(gcloud sql instances describe "${INSTANCE}" --project="${PROJECT}" --format='value(state)')"
if [[ "${STATE}" != "RUNNABLE" ]]; then
  gcloud sql instances patch "${INSTANCE}" --project="${PROJECT}" --activation-policy=ALWAYS --quiet
  for _ in $(seq 1 60); do
    STATE="$(gcloud sql instances describe "${INSTANCE}" --project="${PROJECT}" --format='value(state)')"
    [[ "${STATE}" == "RUNNABLE" ]] && break
    sleep 10
  done
fi

echo "==> Uploading to gs://${GCS_BUCKET}/${GCS_OBJECT}..."
gsutil cp "${PREPARED}" "gs://${GCS_BUCKET}/${GCS_OBJECT}"

SQL_SA="$(gcloud sql instances describe "${INSTANCE}" --project="${PROJECT}" --format='value(serviceAccountEmailAddress)')"
gsutil iam ch "serviceAccount:${SQL_SA}:objectViewer" "gs://${GCS_BUCKET}" 2>/dev/null || true

echo "==> Importing into ${DATABASE} (may take several minutes)..."
gcloud sql import sql "${INSTANCE}" \
  "gs://${GCS_BUCKET}/${GCS_OBJECT}" \
  --project="${PROJECT}" \
  --database="${DATABASE}" \
  --quiet

echo "==> Done."
