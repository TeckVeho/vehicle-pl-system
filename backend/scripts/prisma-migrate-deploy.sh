#!/usr/bin/env sh
# Cloud Run migrate job: Prisma migrate deploy against Cloud SQL via DATABASE_URL secret.
set -eu
exec npx prisma migrate deploy
