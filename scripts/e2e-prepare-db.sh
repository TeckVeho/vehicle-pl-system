#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT_DIR/backend"

DATABASE_URL="${E2E_DATABASE_URL:-mysql://admin:admin123@localhost:3306/vehicle_pl_e2e}"

export DATABASE_URL
npx prisma generate
npx prisma db push
npx prisma db seed
