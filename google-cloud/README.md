# GCP deployment — vehicle-pl-system

Deploy **vehicle-pl-system** (IZUMI 車両別損益計算) to GCP project **`izumi-vpl`**, following the same pattern as [kumu](https://github.com/TeckVeho/kumu) and [izumi-maintenance-express](https://github.com/TeckVeho/izumi-maintenance-v2).

| Item | Value |
|------|-------|
| GCP project | `izumi-vpl` (single project for dev / stg / prod) |
| Region | `asia-northeast1` |
| Artifact Registry | `izumi-vpl-docker` |
| Terraform state | `izumi-vpl-terraform-state` |

## Single-project naming (like kumu)

One GCP project; environments differ by `env_suffix` in Terraform and resource suffix in names.

| Git branch | Terraform `env_suffix` | Image tag (AR) | Cloud Run API | Cloud Run Web |
|------------|------------------------|----------------|---------------|---------------|
| `develop` | `dev` | `dev` | `izumi-vpl-api-dev` | `izumi-vpl-web-dev` |
| `staging` | `stg` | `stage` | `izumi-vpl-api-stg` | `izumi-vpl-web-stg` |
| `production` | `prod` | `prod` | `izumi-vpl-api-prod` | `izumi-vpl-web-prod` |

Shared bootstrap (one per project): state bucket, Artifact Registry, Cloud Build bucket.

Per env Terraform stacks: `live/{dev,stg,prod}/network` → `live/{dev,stg,prod}/app`.

Secrets pattern: `izumi-vpl-jwt-secret-{env_suffix}` (e.g. `izumi-vpl-jwt-secret-stg`).

## Architecture

- **Cloud Run**: API (Express/Prisma), Web (Next.js), Migrate Job (Prisma)
- **Cloud SQL**: MySQL (`vehicle_pl_system`)
- **Secret Manager**: `JWT_SECRET`, `GOOGLE_SERVICE_ACCOUNT_JSON`, `GOOGLE_DRIVE_FOLDER_ID`, `DATABASE_URL` (auto)
- **VPC**: Private Cloud SQL connectivity

## Quick start

See **[DEPLOY_GUIDE.md](DEPLOY_GUIDE.md)** for the full walkthrough.

```bash
# 1. Auth & project
gcloud auth login
gcloud config set project izumi-vpl

# 2. Update bootstrap tfvars with project number
PROJECT_NUMBER=$(gcloud projects describe izumi-vpl --format='value(projectNumber)')
# Edit google-cloud/terraform/environments/bootstrap/terraform.tfvars

# 3. Terraform (bootstrap → network → app)
cd google-cloud/terraform/live/bootstrap && terragrunt apply
cd ../dev/network && terragrunt apply
cd ../app && terragrunt apply

# 4. Secrets
bash google-cloud/scripts/create-dev-secrets.sh

# 5. Build images (before or after terraform — images-only config works before Cloud Run exists)
source google-cloud/scripts/load-env.sh
export NEXT_PUBLIC_API_URL='https://izumi-vpl-api-dev-XXXX.run.app/api'
export NEXT_PUBLIC_BASE_URL='https://izumi-vpl-web-dev-XXXX.run.app'
bash google-cloud/scripts/cloud-build-submit.sh google-cloud/cloudbuild/cloudbuild.dev.images.yaml

# 6. Full deploy (build + migrate + deploy)
bash google-cloud/scripts/cloud-build-submit.sh google-cloud/cloudbuild/cloudbuild.dev.yaml
```

## Layout

| Path | Purpose |
|------|---------|
| `backend/Dockerfile.prod` | Express API image |
| `Dockerfile.prod` | Next.js web image (repo root) |
| `google-cloud/cloudbuild/` | Cloud Build YAML |
| `google-cloud/terraform/` | Terraform modules + Terragrunt live |
| `google-cloud/scripts/` | Local deploy helpers |
| `.github/workflows/cd-gcp.yml` | CI/CD via GitHub Actions + WIF |

## CI/CD

Configure GitHub repository secrets and variables per [cloudbuild/GITHUB_ACTIONS_WIF.md](cloudbuild/GITHUB_ACTIONS_WIF.md).
