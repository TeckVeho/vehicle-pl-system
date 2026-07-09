#!/usr/bin/env bash
# Submit Cloud Build from repo root. See google-cloud/scripts/README.md.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

# shellcheck source=/dev/null
source "${ROOT}/google-cloud/scripts/load-env.sh"

CONFIG="${1:-google-cloud/cloudbuild/cloudbuild.dev.yaml}"
TAG="${TAG:-dev}"
CONFIG_BASE=$(basename "$CONFIG")

CORE_SUBS="_TAG=${TAG},_AR_PROJECT_ID=${GCP_PROJECT_ID},_DEPLOY_PROJECT_ID=${GCP_PROJECT_ID},_REPO=${GCP_PROJECT_ID}-docker"

if [[ "${CONFIG_BASE}" == cloudbuild.dev.images.yaml ]]; then
  export NEXT_PUBLIC_API_URL="${NEXT_PUBLIC_API_URL:-https://izumi-vpl-api-dev-placeholder.run.app}"
  export NEXT_PUBLIC_BASE_URL="${NEXT_PUBLIC_BASE_URL:-https://izumi-vpl-web-dev-placeholder.run.app}"
  exec gcloud builds submit --project="${GCP_PROJECT_ID}" --config="${CONFIG}" \
    --substitutions="_TAG=${TAG},_AR_PROJECT_ID=${GCP_PROJECT_ID},_REPO=${GCP_PROJECT_ID}-docker,_NEXT_PUBLIC_API_URL=${NEXT_PUBLIC_API_URL},_NEXT_PUBLIC_BASE_URL=${NEXT_PUBLIC_BASE_URL}" .
fi

if [[ "${CONFIG_BASE}" == *.api.yaml ]]; then
  exec gcloud builds submit --project="${GCP_PROJECT_ID}" --config="${CONFIG}" \
    --substitutions="${CORE_SUBS},_API_SERVICE=${GCP_PROJECT_ID}-api-${TAG},_MIGRATE_JOB=${GCP_PROJECT_ID}-migrate-${TAG}" .
fi

if [[ "${CONFIG_BASE}" == *.web.yaml ]]; then
  if [[ -z "${NEXT_PUBLIC_API_URL:-}" ]] || [[ -z "${NEXT_PUBLIC_BASE_URL:-}" ]]; then
    echo "Set NEXT_PUBLIC_API_URL and NEXT_PUBLIC_BASE_URL (required for web build)." >&2
    exit 1
  fi
  exec gcloud builds submit --project="${GCP_PROJECT_ID}" --config="${CONFIG}" \
    --substitutions="${CORE_SUBS},_WEB_SERVICE=${GCP_PROJECT_ID}-web-${TAG},_NEXT_PUBLIC_API_URL=${NEXT_PUBLIC_API_URL},_NEXT_PUBLIC_BASE_URL=${NEXT_PUBLIC_BASE_URL}" .
fi

if [[ -z "${NEXT_PUBLIC_API_URL:-}" ]] || [[ -z "${NEXT_PUBLIC_BASE_URL:-}" ]]; then
  echo "Set NEXT_PUBLIC_API_URL and NEXT_PUBLIC_BASE_URL (required for this config)." >&2
  exit 1
fi

exec gcloud builds submit --project="${GCP_PROJECT_ID}" --config="${CONFIG}" \
  --substitutions="${CORE_SUBS},_API_SERVICE=${GCP_PROJECT_ID}-api-${TAG},_WEB_SERVICE=${GCP_PROJECT_ID}-web-${TAG},_MIGRATE_JOB=${GCP_PROJECT_ID}-migrate-${TAG},_NEXT_PUBLIC_API_URL=${NEXT_PUBLIC_API_URL},_NEXT_PUBLIC_BASE_URL=${NEXT_PUBLIC_BASE_URL}" .
