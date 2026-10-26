#!/usr/bin/env bash
# Delete legacy per-key app secrets and disable extra ENABLED versions (keep latest only).
#
# Usage:
#   bash google-cloud/scripts/cleanup-legacy-app-secrets.sh dev
#   bash google-cloud/scripts/cleanup-legacy-app-secrets.sh dev --disable-versions-only

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
# shellcheck source=/dev/null
source "${ROOT}/google-cloud/scripts/load-env.sh"

ENV_SUFFIX="${1:-}"
shift || true

PROJECT_ID="${PROJECT_ID:-${GCP_PROJECT_ID}}"
DELETE_LEGACY=true

usage() {
  echo "Usage: $0 {dev|stg|prod} [--project PROJECT_ID] [--disable-versions-only]" >&2
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
    --disable-versions-only)
      DELETE_LEGACY=false
      shift
      ;;
    *)
      echo "Unknown argument: $1" >&2
      usage
      ;;
  esac
done

disable_extra_enabled_versions() {
  local secret_id="$1"
  mapfile -t enabled < <(gcloud secrets versions list "${secret_id}" \
    --project="${PROJECT_ID}" \
    --filter='state=ENABLED' \
    --format='value(name)' \
    --sort-by='~createTime')
  if ((${#enabled[@]} <= 1)); then
    return 0
  fi
  for version in "${enabled[@]:1}"; do
    echo "Disabling extra version ${version} on ${secret_id}"
    gcloud secrets versions disable "${version}" --secret="${secret_id}" --project="${PROJECT_ID}" --quiet
  done
}

legacy_secrets=(
  "izumi-vpl-jwt-secret-${ENV_SUFFIX}"
  "izumi-vpl-google-sa-json-${ENV_SUFFIX}"
  "izumi-vpl-google-drive-folder-${ENV_SUFFIX}"
)

keep_secrets=(
  "izumi-vpl-app-secrets-${ENV_SUFFIX}"
  "izumi-vpl-database-url-${ENV_SUFFIX}"
)

if [[ "${DELETE_LEGACY}" == true ]]; then
  for secret_id in "${legacy_secrets[@]}"; do
    if gcloud secrets describe "${secret_id}" --project="${PROJECT_ID}" >/dev/null 2>&1; then
      echo "Deleting legacy secret ${secret_id}"
      gcloud secrets delete "${secret_id}" --project="${PROJECT_ID}" --quiet
    fi
  done
fi

for secret_id in "${keep_secrets[@]}"; do
  if gcloud secrets describe "${secret_id}" --project="${PROJECT_ID}" >/dev/null 2>&1; then
    disable_extra_enabled_versions "${secret_id}"
  fi
done

echo "Cleanup done for ${ENV_SUFFIX}. Remaining secrets:"
gcloud secrets list --project="${PROJECT_ID}" --format='table(name)' | grep "izumi-vpl" || true
