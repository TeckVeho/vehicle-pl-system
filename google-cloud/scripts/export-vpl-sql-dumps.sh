#!/usr/bin/env bash
# Export Cloud SQL dumps for izumi-vpl migration (issue #99).
#
# Usage:
#   bash google-cloud/scripts/export-vpl-sql-dumps.sh dev
#   bash google-cloud/scripts/export-vpl-sql-dumps.sh stg
#   bash google-cloud/scripts/export-vpl-sql-dumps.sh prod
#   bash google-cloud/scripts/export-vpl-sql-dumps.sh all
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
# shellcheck source=/dev/null
source "${ROOT}/google-cloud/scripts/load-env.sh"

TARGET="${1:-}"
PROJECT="${GCP_PROJECT_ID}"
STATE_BUCKET="${GCP_STATE_BUCKET}"
DUMP_DIR="${DUMP_DIR:-${ROOT}/db-dumps}"
DATE_TAG="$(date +%Y%m%d-%H%M)"

declare -A INSTANCES=(
  [dev]=izumi-vpl-mysql-dev
  [stg]=izumi-vpl-mysql-stg
  [prod]=izumi-vpl-mysql-prod
)
declare -A DATABASES=(
  [dev]=vehicle_pl_system
  [stg]=izumi_vehicle_pl_system
  [prod]=izumi_vehicle_pl_system
)

usage() {
  echo "Usage: $0 dev|stg|prod|all" >&2
  exit 1
}

export_env() {
  local env="$1"
  local instance="${INSTANCES[$env]}"
  local database="${DATABASES[$env]}"
  local gcs_uri="gs://${STATE_BUCKET}/db-migration/${env}-export-${DATE_TAG}.sql"
  local local_path="${DUMP_DIR}/vpl-${env}-${DATE_TAG}.sql"

  if ! gcloud sql instances describe "$instance" --project="$PROJECT" &>/dev/null; then
    echo "==> Skip ${env}: instance ${instance} not found"
    return 0
  fi

  mkdir -p "$DUMP_DIR"
  echo "==> Export ${env}: ${instance} / ${database}"
  gcloud sql export sql "$instance" "$gcs_uri" \
    --database="$database" \
    --project="$PROJECT" \
    --offload || gcloud sql export sql "$instance" "$gcs_uri" \
    --database="$database" \
    --project="$PROJECT"

  echo "==> Download to ${local_path}"
  gcloud storage cp "$gcs_uri" "$local_path" --project="$PROJECT"

  local size
  size="$(wc -c < "$local_path" | tr -d ' ')"
  if [[ "$size" -le 0 ]]; then
    echo "ERROR: dump size is 0 for ${env}" >&2
    exit 1
  fi
  echo "    OK: ${size} bytes → ${local_path}"
}

case "$TARGET" in
  dev|stg|prod) export_env "$TARGET" ;;
  all)
    for env in dev stg prod; do
      export_env "$env"
    done
    ;;
  -h|--help|"") usage ;;
  *) echo "Unknown target: $TARGET" >&2; usage ;;
esac

echo "Done. Keep local dumps until cutover is verified."
