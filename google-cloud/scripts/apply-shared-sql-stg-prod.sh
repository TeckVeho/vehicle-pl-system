#!/usr/bin/env bash
# Apply shared Cloud SQL (prod instance + stg/prod databases) on izumi-vpl.
#
# Prerequisites:
#   gcloud auth login
#   gcloud auth application-default login
#   gcloud config set project izumi-vpl
#
# Usage (from repo root):
#   bash google-cloud/scripts/apply-shared-sql-stg-prod.sh
#   bash google-cloud/scripts/apply-shared-sql-stg-prod.sh --migrate-stg-data
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
# shellcheck source=/dev/null
source "${ROOT}/google-cloud/scripts/load-env.sh"

MIGRATE_STG_DATA=0
for arg in "$@"; do
  case "$arg" in
    --migrate-stg-data) MIGRATE_STG_DATA=1 ;;
    -h|--help)
      echo "Usage: $0 [--migrate-stg-data]"
      exit 0
      ;;
    *) echo "Unknown arg: $arg" >&2; exit 1 ;;
  esac
done

PROJECT="${GCP_PROJECT_ID}"
REGION="${GCP_REGION}"
STATE_BUCKET="${GCP_STATE_BUCKET}"
OLD_STG_INSTANCE="izumi-vpl-mysql-stg"
PROD_INSTANCE="izumi-vpl-mysql-prod"
STG_DB="izumi-vpl-stg"
EXPORT_URI="gs://${STATE_BUCKET}/db-migration/stg-export-$(date +%Y%m%d%H%M%S).sql"

require_auth() {
  if ! gcloud auth print-access-token --project="$PROJECT" &>/dev/null; then
    echo "ERROR: GCP auth expired. Run:" >&2
    echo "  gcloud auth login" >&2
    echo "  gcloud auth application-default login" >&2
    echo "  gcloud config set project ${PROJECT}" >&2
    exit 1
  fi
  export GOOGLE_OAUTH_ACCESS_TOKEN
  GOOGLE_OAUTH_ACCESS_TOKEN="$(gcloud auth print-access-token --project="$PROJECT")"
  export CLOUDSDK_CORE_PROJECT="$PROJECT"
}

require_auth

if [[ "$MIGRATE_STG_DATA" -eq 1 ]]; then
  if gcloud sql instances describe "$OLD_STG_INSTANCE" --project="$PROJECT" &>/dev/null; then
    echo "==> Exporting legacy stg instance ${OLD_STG_INSTANCE}..."
    gcloud sql export sql "$OLD_STG_INSTANCE" "$EXPORT_URI" \
      --database=izumi_vehicle_pl_system \
      --project="$PROJECT" \
      --offload || gcloud sql export sql "$OLD_STG_INSTANCE" "$EXPORT_URI" \
      --database=izumi_vehicle_pl_system \
      --project="$PROJECT"
    echo "    Export: $EXPORT_URI"
  else
    echo "==> No legacy instance ${OLD_STG_INSTANCE}; skip export."
  fi
fi

TG_ROOT="${ROOT}/google-cloud/terraform/live"

apply_stack() {
  local dir="$1"
  echo ""
  echo "==> terragrunt apply: ${dir}"
  (cd "$dir" && terragrunt apply -auto-approve)
}

echo "==> Network stacks..."
apply_stack "${TG_ROOT}/prod/network"
apply_stack "${TG_ROOT}/stg/network"

echo "==> App stacks (prod instance + stg shared DB)..."
apply_stack "${TG_ROOT}/prod/app"
apply_stack "${TG_ROOT}/stg/app"

if [[ "$MIGRATE_STG_DATA" -eq 1 && -n "${EXPORT_URI:-}" ]]; then
  echo "==> Import stg data into ${PROD_INSTANCE}/${STG_DB}..."
  gcloud sql import sql "$PROD_INSTANCE" "$EXPORT_URI" \
    --database="$STG_DB" \
    --project="$PROJECT"
fi

echo ""
echo "==> Cloud SQL instances:"
gcloud sql instances list --project="$PROJECT" --format='table(name,state,databaseVersion)'

echo ""
echo "==> Databases on ${PROD_INSTANCE}:"
gcloud sql databases list --instance="$PROD_INSTANCE" --project="$PROJECT" --format='table(name,charset)' 2>/dev/null || true
