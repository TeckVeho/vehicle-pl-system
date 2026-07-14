#!/usr/bin/env bash
# Full production deploy for izumi-vpl (network → secrets → terraform → build → migrate → deploy).
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

# shellcheck source=/dev/null
source "${ROOT}/google-cloud/scripts/load-env.sh"

API_DOMAIN="${API_DOMAIN:-vpl-api.izumilogi.com}"
WEB_DOMAIN="${WEB_DOMAIN:-vpl.izumilogi.com}"
TAG="${TAG:-prod}"
SERVICE_SUFFIX="${SERVICE_SUFFIX:-prod}"

export NEXT_PUBLIC_API_URL="https://${API_DOMAIN}"
export NEXT_PUBLIC_BASE_URL="https://${WEB_DOMAIN}"
export TAG SERVICE_SUFFIX

if token="$(gcloud auth print-access-token 2>/dev/null)"; then
  export GOOGLE_OAUTH_ACCESS_TOKEN="${token}"
fi

echo "==> Checking gcloud auth..."
gcloud auth print-access-token >/dev/null
if ! gcloud billing projects describe "${GCP_PROJECT_ID}" --format='value(billingEnabled)' | grep -qi true; then
  echo "ERROR: Billing is not enabled on ${GCP_PROJECT_ID}." >&2
  exit 1
fi

echo "==> Terraform network/prod"
cd "${ROOT}/google-cloud/terraform/live/prod/network"
terragrunt apply -auto-approve

echo "==> Create production secrets"
SUFFIX="${SERVICE_SUFFIX}" bash "${ROOT}/google-cloud/scripts/create-dev-secrets.sh"

echo "==> Build images (first pass)"
bash "${ROOT}/google-cloud/scripts/cloud-build-submit.sh" google-cloud/cloudbuild/cloudbuild.dev.images.yaml

echo "==> Terraform app/prod (Cloud Run + domain mappings)"
cd "${ROOT}/google-cloud/terraform/live/prod/app"
terragrunt apply -auto-approve

echo "==> Domain mapping DNS records:"
terragrunt output api_domain_mapping_status web_domain_mapping_status 2>/dev/null || true

echo "==> Full deploy (build + migrate + deploy)"
bash "${ROOT}/google-cloud/scripts/cloud-build-submit.sh" google-cloud/cloudbuild/cloudbuild.dev.yaml

echo "==> Service URLs"
gcloud run services describe "izumi-vpl-api-${SERVICE_SUFFIX}" --region="${GCP_REGION}" --format='value(status.url)' || true
gcloud run services describe "izumi-vpl-web-${SERVICE_SUFFIX}" --region="${GCP_REGION}" --format='value(status.url)' || true

echo "==> Custom domains"
echo "Web : https://${WEB_DOMAIN}"
echo "API : https://${API_DOMAIN}"
echo "Done."
