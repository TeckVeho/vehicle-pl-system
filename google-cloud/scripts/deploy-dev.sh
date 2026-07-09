#!/usr/bin/env bash
# Full dev deploy for izumi-vpl (run after billing is enabled on the project).
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

# shellcheck source=/dev/null
source "${ROOT}/google-cloud/scripts/load-env.sh"

export GOOGLE_OAUTH_ACCESS_TOKEN
GOOGLE_OAUTH_ACCESS_TOKEN="$(gcloud auth print-access-token)"

echo "==> Checking billing..."
if ! gcloud billing projects describe "${GCP_PROJECT_ID}" --format='value(billingEnabled)' | grep -q true; then
  echo "ERROR: Billing is not enabled on ${GCP_PROJECT_ID}." >&2
  echo "Enable billing in Console: https://console.cloud.google.com/billing/linkedaccount?project=${GCP_PROJECT_ID}" >&2
  echo "Use the same billing account as izumi-mt: 0127E3-359F8F-3BF2C5" >&2
  exit 1
fi

echo "==> Terraform bootstrap"
cd "${ROOT}/google-cloud/terraform/live/bootstrap"
terragrunt apply -auto-approve

echo "==> Terraform network/dev"
cd "${ROOT}/google-cloud/terraform/live/dev/network"
terragrunt apply -auto-approve

echo "==> Create secrets"
bash "${ROOT}/google-cloud/scripts/create-dev-secrets.sh"

echo "==> Build images only (first pass)"
export NEXT_PUBLIC_API_URL="${NEXT_PUBLIC_API_URL:-https://izumi-vpl-api-dev-placeholder.run.app}"
export NEXT_PUBLIC_BASE_URL="${NEXT_PUBLIC_BASE_URL:-https://izumi-vpl-web-dev-placeholder.run.app}"
bash "${ROOT}/google-cloud/scripts/cloud-build-submit.sh" google-cloud/cloudbuild/cloudbuild.dev.images.yaml

echo "==> Terraform app/dev"
cd "${ROOT}/google-cloud/terraform/live/dev/app"
terragrunt apply -auto-approve

API_URL="$(gcloud run services describe izumi-vpl-api-dev --region="${GCP_REGION}" --format='value(status.url)' 2>/dev/null || true)"
WEB_URL="$(gcloud run services describe izumi-vpl-web-dev --region="${GCP_REGION}" --format='value(status.url)' 2>/dev/null || true)"

if [[ -n "${API_URL}" && -n "${WEB_URL}" ]]; then
  echo "==> Cloud Run URLs: API=${API_URL} WEB=${WEB_URL}"
  export NEXT_PUBLIC_API_URL="${API_URL}"
  export NEXT_PUBLIC_BASE_URL="${WEB_URL}"
  echo "Update terraform.tfvars CORS_ORIGIN and NEXT_PUBLIC_* with these URLs, then re-apply if needed."
fi

echo "==> Full deploy (build + migrate + deploy)"
bash "${ROOT}/google-cloud/scripts/cloud-build-submit.sh" google-cloud/cloudbuild/cloudbuild.dev.yaml

echo "==> Image URLs"
gcloud artifacts docker images list \
  "asia-northeast1-docker.pkg.dev/${GCP_PROJECT_ID}/${GCP_PROJECT_ID}-docker" \
  --include-tags --format='table(package,tags,version)' 2>/dev/null || true

echo "==> Service URLs"
gcloud run services describe izumi-vpl-api-dev --region="${GCP_REGION}" --format='value(status.url)'
gcloud run services describe izumi-vpl-web-dev --region="${GCP_REGION}" --format='value(status.url)'

echo "Done."
