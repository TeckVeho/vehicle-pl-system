#!/usr/bin/env bash
# GCP defaults for local scripts. Usage: source google-cloud/scripts/load-env.sh
set -euo pipefail

export GCP_PROJECT_ID="izumi-vpl"
export GCP_REGION="asia-northeast1"
export GCP_STATE_BUCKET="izumi-vpl-terraform-state"

export PROJECT_ID="${PROJECT_ID:-${GCP_PROJECT_ID}}"
