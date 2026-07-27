#!/usr/bin/env sh
# Baseline staging DB when schema exists but _prisma_migrations is empty (Prisma P3005).
# Marks migrations 1–3 as applied, then runs migrate deploy (ATMTC / migration 4+).
set -eu

for migration in \
  20250507000000_baseline \
  20260507160000_add_location_spreadsheet_revenue_sheet \
  20260508075544_add_drive_spreadsheet_revenue_snapshot
do
  echo "==> migrate resolve --applied ${migration}"
  npx prisma migrate resolve --applied "${migration}"
done

echo "==> migrate deploy"
exec npx prisma migrate deploy
