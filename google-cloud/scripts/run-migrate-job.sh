#!/usr/bin/env bash
# Execute Prisma migrate Cloud Run Job and wait for completion.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
# shellcheck source=/dev/null
source "${ROOT}/google-cloud/scripts/load-env.sh"

REGION="${REGION:-${GCP_REGION}}"
MIGRATE_JOB="${MIGRATE_JOB_NAME:-${GCP_PROJECT_ID}-migrate-dev}"

gcloud run jobs execute "$MIGRATE_JOB" \
  --project="${GCP_PROJECT_ID}" \
  --region="$REGION" \
  --wait
