# Scripts for local GCP deploy. See ../DEPLOY_GUIDE.md.

| Script | Purpose |
|--------|---------|
| `load-env.sh` | Export `GCP_PROJECT_ID`, `GCP_REGION`, `GCP_STATE_BUCKET` |
| `cloud-build-submit.sh` | Submit Cloud Build from repo root |
| `create-dev-secrets.sh` | Legacy: per-key dev secrets (prefer `migrate-app-secrets-to-bundle.sh`) |
| `create-app-secrets-bundle.sh` | Create/update bundled app secrets `.env` in Secret Manager |
| `migrate-app-secrets-to-bundle.sh` | Copy legacy JWT/SA/Drive secrets into bundle |
| `cleanup-legacy-app-secrets.sh` | Delete legacy per-key secrets after smoke test |
| `run-migrate-job.sh` | Execute Prisma migrate Cloud Run Job |
| `import-vehicle-pl-db-dev.sh` | Import SQL dump into Cloud SQL dev (hub or legacy instance) |
| `export-vpl-sql-dumps.sh` | Export dev/stg/prod Cloud SQL dumps before migration (issue #99) |
| `apply-shared-sql-stg-prod.sh` | Terragrunt apply network + app (prod instance, stg shared DB); optional `--migrate-stg-data` |
| `grant-github-actions-iam.sh` | WIF + IAM for GitHub Actions CI |
| `setup-github-actions-cicd.sh` | One-shot: WIF/IAM + GitHub repository secrets/variables (like izumi-maintenance-v2) |

**Cloud SQL topology (issue #99):** see [`terraform/SQL_TOPOLOGY.md`](../terraform/SQL_TOPOLOGY.md), [`docs/sql-dev-hub-migration.md`](../docs/sql-dev-hub-migration.md), [`docs/sql-stg-prod-shared-instance.md`](../docs/sql-stg-prod-shared-instance.md).
