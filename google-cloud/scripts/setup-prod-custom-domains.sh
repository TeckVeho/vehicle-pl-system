#!/usr/bin/env bash
# Apply prod custom domains (Terraform) and rebuild/deploy API + web with matching URLs.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
# shellcheck source=/dev/null
source "${ROOT}/google-cloud/scripts/load-env.sh"

API_DOMAIN="${API_DOMAIN:-vpl-api.izumilogi.com}"
WEB_DOMAIN="${WEB_DOMAIN:-vpl.izumilogi.com}"
TAG="${TAG:-prod}"
SERVICE_SUFFIX="${SERVICE_SUFFIX:-prod}"
IMAGE_API="${GCP_REGION}-docker.pkg.dev/${GCP_PROJECT_ID}/${GCP_PROJECT_ID}-docker/izumi-vpl-api:${TAG}"
IMAGE_WEB="${GCP_REGION}-docker.pkg.dev/${GCP_PROJECT_ID}/${GCP_PROJECT_ID}-docker/izumi-vpl-web:${TAG}"

export NEXT_PUBLIC_API_URL="https://${API_DOMAIN}"
export NEXT_PUBLIC_BASE_URL="https://${WEB_DOMAIN}/"

if token="$(gcloud auth print-access-token 2>/dev/null)"; then
  export GOOGLE_OAUTH_ACCESS_TOKEN="${token}"
fi

echo "==> Checking gcloud auth..."
gcloud auth print-access-token >/dev/null

echo "==> Terraform apply (domain mappings + env)..."
cd "${ROOT}/google-cloud/terraform/live/prod/app"
terragrunt apply -auto-approve

echo "==> Domain mapping DNS records:"
terragrunt output api_domain_mapping_status web_domain_mapping_status 2>/dev/null || true

echo "==> Build + push images..."
cd "${ROOT}"
TAG="${TAG}" SERVICE_SUFFIX="${SERVICE_SUFFIX}" \
  bash "${ROOT}/google-cloud/scripts/cloud-build-submit.sh" \
  google-cloud/cloudbuild/cloudbuild.dev.images.yaml

echo "==> Deploy API + web services..."
gcloud run services update "izumi-vpl-api-${SERVICE_SUFFIX}" \
  --project="${GCP_PROJECT_ID}" \
  --region="${GCP_REGION}" \
  --image="${IMAGE_API}"

gcloud run services update "izumi-vpl-web-${SERVICE_SUFFIX}" \
  --project="${GCP_PROJECT_ID}" \
  --region="${GCP_REGION}" \
  --image="${IMAGE_WEB}" \
  --update-env-vars="NEXT_PUBLIC_API_URL=${NEXT_PUBLIC_API_URL},NEXT_PUBLIC_BASE_URL=${NEXT_PUBLIC_BASE_URL}"

echo "==> Done."
echo "Web : https://${WEB_DOMAIN}"
echo "API : https://${API_DOMAIN}"
