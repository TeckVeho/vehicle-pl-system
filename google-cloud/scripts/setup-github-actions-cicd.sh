#!/usr/bin/env bash
# One-shot: GCP WIF + IAM for GitHub Actions, then repository Secrets/Variables on GitHub.
# Mirrors TeckVeho/izumi-maintenance-v2 (repository secrets, not Environments).
#
# Prerequisites:
#   - gcloud CLI authenticated (gcloud auth login)
#   - gh CLI authenticated with admin on TeckVeho/vehicle-pl-system
#
# Usage (from repo root):
#   bash google-cloud/scripts/setup-github-actions-cicd.sh
#   DRY_RUN=1 bash google-cloud/scripts/setup-github-actions-cicd.sh
#   SKIP_GCP=1 bash google-cloud/scripts/setup-github-actions-cicd.sh   # GitHub only (WIF already exists)
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
# shellcheck source=/dev/null
source "${ROOT}/google-cloud/scripts/load-env.sh"

GITHUB_REPO="${GITHUB_REPO:-TeckVeho/vehicle-pl-system}"
DRY_RUN="${DRY_RUN:-0}"
SKIP_GCP="${SKIP_GCP:-0}"
SETUP_WIF="${SETUP_WIF:-auto}" # auto | always | never

run() {
  if [[ "$DRY_RUN" == 1 ]]; then
    echo "[dry-run] $*"
  else
    echo "+ $*"
    "$@"
  fi
}

need_wif_setup() {
  gcloud iam workload-identity-pools providers describe github-provider \
    --project="${GCP_PROJECT_ID}" --location=global \
    --workload-identity-pool=github-pool >/dev/null 2>&1
  return $?
}

echo "==> GitHub repo: ${GITHUB_REPO}"
echo "==> GCP project: ${GCP_PROJECT_ID}"

if [[ "$SKIP_GCP" != 1 ]]; then
  echo "==> Checking gcloud auth..."
  gcloud auth print-access-token >/dev/null

  WIF_EXISTS=0
  if need_wif_setup; then WIF_EXISTS=1; fi

  if [[ "$SETUP_WIF" == "always" ]] || { [[ "$SETUP_WIF" == "auto" ]] && [[ "$WIF_EXISTS" == 0 ]]; }; then
    echo "==> Creating WIF pool, OIDC provider, and federated SA..."
    CREATE_SA=1 run bash "${ROOT}/google-cloud/scripts/grant-github-actions-iam.sh" --setup-wif
  fi

  echo "==> Granting Cloud Build / deploy IAM..."
  run bash "${ROOT}/google-cloud/scripts/grant-github-actions-iam.sh"
fi

if [[ "$SKIP_GCP" == 1 ]]; then
  PROJECT_NUMBER="${PROJECT_NUMBER:?Set PROJECT_NUMBER when SKIP_GCP=1}"
else
  PROJECT_NUMBER="$(gcloud projects describe "${GCP_PROJECT_ID}" --format='value(projectNumber)')"
fi
WIF_PROVIDER="projects/${PROJECT_NUMBER}/locations/global/workloadIdentityPools/github-pool/providers/github-provider"
SA_EMAIL="${SA_EMAIL:-github-actions-izumi-vpl@${GCP_PROJECT_ID}.iam.gserviceaccount.com}"

if [[ "$SKIP_GCP" != 1 ]]; then
  if ! gcloud iam service-accounts describe "${SA_EMAIL}" --project="${GCP_PROJECT_ID}" >/dev/null 2>&1; then
    echo "ERROR: Federated SA not found: ${SA_EMAIL}" >&2
    echo "Re-run without SKIP_GCP or set SA_EMAIL." >&2
    exit 1
  fi
fi

# URLs: custom domains from tfvars (same as setup-*-custom-domains.sh), else Cloud Run *.run.app
API_URL_DEVELOP="${API_URL_DEVELOP:-https://izumi-vpl-v2-api.vw-dev.com}"
BASE_URL_DEVELOP="${BASE_URL_DEVELOP:-https://izumi-vpl-v2.vw-dev.com}"
API_URL_STAGING="${API_URL_STAGING:-https://vpl-stage-api.izumilogi.com}"
BASE_URL_STAGING="${BASE_URL_STAGING:-https://vpl-stage.izumilogi.com}"
API_URL_PRODUCTION="${API_URL_PRODUCTION:-https://vpl-api.izumilogi.com}"
BASE_URL_PRODUCTION="${BASE_URL_PRODUCTION:-https://vpl.izumilogi.com}"

if [[ "$SKIP_GCP" != 1 ]]; then
  RUN_API="$(gcloud run services describe izumi-vpl-api-dev \
    --project="${GCP_PROJECT_ID}" --region="${GCP_REGION}" --format='value(status.url)' 2>/dev/null || true)"
  RUN_WEB="$(gcloud run services describe izumi-vpl-web-dev \
    --project="${GCP_PROJECT_ID}" --region="${GCP_REGION}" --format='value(status.url)' 2>/dev/null || true)"
  if [[ -z "${USE_CUSTOM_DOMAINS:-1}" ]] && [[ -n "${RUN_API}" ]] && [[ -n "${RUN_WEB}" ]]; then
    API_URL_DEVELOP="${RUN_API}"
    BASE_URL_DEVELOP="${RUN_WEB}"
  fi
fi

echo ""
echo "==> GitHub repository secrets:"
echo "  GCP_PROJECT_ID=${GCP_PROJECT_ID}"
echo "  GCP_SERVICE_ACCOUNT=${SA_EMAIL}"
echo "  GCP_WORKLOAD_IDENTITY_PROVIDER=${WIF_PROVIDER}"
echo ""
echo "==> GitHub repository variables:"
echo "  GCP_NEXT_PUBLIC_API_URL_DEVELOP=${API_URL_DEVELOP}"
echo "  GCP_NEXT_PUBLIC_BASE_URL_DEVELOP=${BASE_URL_DEVELOP}"
echo "  GCP_NEXT_PUBLIC_API_URL_STAGING=${API_URL_STAGING}"
echo "  GCP_NEXT_PUBLIC_BASE_URL_STAGING=${BASE_URL_STAGING}"
echo "  GCP_NEXT_PUBLIC_API_URL_PRODUCTION=${API_URL_PRODUCTION}"
echo "  GCP_NEXT_PUBLIC_BASE_URL_PRODUCTION=${BASE_URL_PRODUCTION}"
echo ""

if [[ "$DRY_RUN" == 1 ]]; then
  echo "[dry-run] Skipping gh secret/variable writes."
  exit 0
fi

echo "==> Writing GitHub secrets..."
gh secret set GCP_PROJECT_ID --body "${GCP_PROJECT_ID}" --repo "${GITHUB_REPO}"
gh secret set GCP_SERVICE_ACCOUNT --body "${SA_EMAIL}" --repo "${GITHUB_REPO}"
gh secret set GCP_WORKLOAD_IDENTITY_PROVIDER --body "${WIF_PROVIDER}" --repo "${GITHUB_REPO}"

echo "==> Writing GitHub variables..."
gh api --method POST \
  -H "Accept: application/vnd.github+json" \
  "/repos/${GITHUB_REPO}/actions/variables" \
  -f name='GCP_NEXT_PUBLIC_API_URL_DEVELOP' -f value="${API_URL_DEVELOP}" 2>/dev/null \
  || gh api --method PATCH \
    -H "Accept: application/vnd.github+json" \
    "/repos/${GITHUB_REPO}/actions/variables/GCP_NEXT_PUBLIC_API_URL_DEVELOP" \
    -f value="${API_URL_DEVELOP}"

gh api --method POST \
  -H "Accept: application/vnd.github+json" \
  "/repos/${GITHUB_REPO}/actions/variables" \
  -f name='GCP_NEXT_PUBLIC_BASE_URL_DEVELOP' -f value="${BASE_URL_DEVELOP}" 2>/dev/null \
  || gh api --method PATCH \
    -H "Accept: application/vnd.github+json" \
    "/repos/${GITHUB_REPO}/actions/variables/GCP_NEXT_PUBLIC_BASE_URL_DEVELOP" \
    -f value="${BASE_URL_DEVELOP}"

gh api --method POST \
  -H "Accept: application/vnd.github+json" \
  "/repos/${GITHUB_REPO}/actions/variables" \
  -f name='GCP_NEXT_PUBLIC_API_URL_STAGING' -f value="${API_URL_STAGING}" 2>/dev/null \
  || gh api --method PATCH \
    -H "Accept: application/vnd.github+json" \
    "/repos/${GITHUB_REPO}/actions/variables/GCP_NEXT_PUBLIC_API_URL_STAGING" \
    -f value="${API_URL_STAGING}"

gh api --method POST \
  -H "Accept: application/vnd.github+json" \
  "/repos/${GITHUB_REPO}/actions/variables" \
  -f name='GCP_NEXT_PUBLIC_BASE_URL_STAGING' -f value="${BASE_URL_STAGING}" 2>/dev/null \
  || gh api --method PATCH \
    -H "Accept: application/vnd.github+json" \
    "/repos/${GITHUB_REPO}/actions/variables/GCP_NEXT_PUBLIC_BASE_URL_STAGING" \
    -f value="${BASE_URL_STAGING}"

gh api --method POST \
  -H "Accept: application/vnd.github+json" \
  "/repos/${GITHUB_REPO}/actions/variables" \
  -f name='GCP_NEXT_PUBLIC_API_URL_PRODUCTION' -f value="${API_URL_PRODUCTION}" 2>/dev/null \
  || gh api --method PATCH \
    -H "Accept: application/vnd.github+json" \
    "/repos/${GITHUB_REPO}/actions/variables/GCP_NEXT_PUBLIC_API_URL_PRODUCTION" \
    -f value="${API_URL_PRODUCTION}"

gh api --method POST \
  -H "Accept: application/vnd.github+json" \
  "/repos/${GITHUB_REPO}/actions/variables" \
  -f name='GCP_NEXT_PUBLIC_BASE_URL_PRODUCTION' -f value="${BASE_URL_PRODUCTION}" 2>/dev/null \
  || gh api --method PATCH \
    -H "Accept: application/vnd.github+json" \
    "/repos/${GITHUB_REPO}/actions/variables/GCP_NEXT_PUBLIC_BASE_URL_PRODUCTION" \
    -f value="${BASE_URL_PRODUCTION}"

echo ""
echo "==> Done. Re-run CD GCP workflow on develop (Actions → CD GCP → Run workflow)."
echo "    gh workflow run cd-gcp.yml --ref develop -f build_mode=full --repo ${GITHUB_REPO}"
