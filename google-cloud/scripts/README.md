# Scripts for local GCP deploy. See ../DEPLOY_GUIDE.md.

| Script | Purpose |
|--------|---------|
| `load-env.sh` | Export `GCP_PROJECT_ID`, `GCP_REGION`, `GCP_STATE_BUCKET` |
| `cloud-build-submit.sh` | Submit Cloud Build from repo root |
| `create-dev-secrets.sh` | Create JWT + Google Drive secrets in Secret Manager |
| `run-migrate-job.sh` | Execute Prisma migrate Cloud Run Job |
| `grant-github-actions-iam.sh` | WIF + IAM for GitHub Actions CI |
| `setup-github-actions-cicd.sh` | One-shot: WIF/IAM + GitHub repository secrets/variables (like izumi-maintenance-v2) |
