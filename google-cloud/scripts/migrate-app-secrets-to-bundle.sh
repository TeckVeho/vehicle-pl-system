#!/usr/bin/env bash
# Migrate three legacy per-key app secrets into one bundled .env secret.
# Does NOT delete old secrets — run cleanup-legacy-app-secrets.sh after smoke test.
#
# Usage:
#   bash google-cloud/scripts/migrate-app-secrets-to-bundle.sh dev

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
# shellcheck source=/dev/null
source "${ROOT}/google-cloud/scripts/load-env.sh"

ENV_SUFFIX="${1:-}"
shift || true

PROJECT_ID="${PROJECT_ID:-${GCP_PROJECT_ID}}"

usage() {
  echo "Usage: $0 {dev|stg|prod} [--project PROJECT_ID]" >&2
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
    *)
      echo "Unknown argument: $1" >&2
      usage
      ;;
  esac
done

TMP_FILE="$(mktemp)"
trap 'rm -f "${TMP_FILE}"' EXIT

python3 - "${ENV_SUFFIX}" "${PROJECT_ID}" "${TMP_FILE}" <<'PY'
import subprocess
import sys

env_suffix, project_id, out_path = sys.argv[1:4]
legacy = [
    ("JWT_SECRET", f"izumi-vpl-jwt-secret-{env_suffix}"),
    ("GOOGLE_SERVICE_ACCOUNT_JSON", f"izumi-vpl-google-sa-json-{env_suffix}"),
    ("GOOGLE_DRIVE_FOLDER_ID", f"izumi-vpl-google-drive-folder-{env_suffix}"),
]

def read_secret(secret_id: str) -> str:
    return subprocess.check_output(
        [
            "gcloud", "secrets", "versions", "access", "latest",
            "--secret", secret_id,
            "--project", project_id,
        ],
        text=True,
    )

def dotenv_line(key: str, value: str) -> str:
    if any(c in value for c in "\n\r\""):
        escaped = value.replace("\\", "\\\\").replace('"', '\\"').replace("\n", "\\n").replace("\r", "")
        return f'{key}="{escaped}"\n'
    return f"{key}={value}\n"

lines: list[str] = []
for key, secret_id in legacy:
    subprocess.run(
        ["gcloud", "secrets", "describe", secret_id, "--project", project_id],
        check=True,
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )
    lines.append(dotenv_line(key, read_secret(secret_id).rstrip("\n")))

with open(out_path, "w", encoding="utf-8") as f:
    f.writelines(lines)
PY

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
bash "${SCRIPT_DIR}/create-app-secrets-bundle.sh" "${ENV_SUFFIX}" \
  --project "${PROJECT_ID}" \
  --data-file "${TMP_FILE}"

cat <<EOF

Bundle created for ${ENV_SUFFIX}.

Next steps:
  1. Deploy API image with loadAppSecrets (backend/src/config/loadAppSecrets.ts).
  2. Update environments/${ENV_SUFFIX}/app/terraform.tfvars:
       app_secrets_bundle_secret_id = "izumi-vpl-app-secrets-${ENV_SUFFIX}"
       api_secret_env_from_sm = []
  3. terragrunt apply in live/${ENV_SUFFIX}/app
  4. Smoke test API (health + auth)
  5. bash google-cloud/scripts/cleanup-legacy-app-secrets.sh ${ENV_SUFFIX}
EOF
