# GCP Deployment Guide — vehicle-pl-system (izumi-vpl)

End-to-end guide to deploy **vehicle-pl-system** on GCP project **`izumi-vpl`**.

Pattern matches **kumu** and **izumi-maintenance-express**: single GCP project, env suffixes (`dev` / `stg` / `prod`), Cloud Run + Cloud SQL + Terraform/Terragrunt.

---

## 1. Prerequisites

| Tool | Notes |
|------|-------|
| [gcloud CLI](https://cloud.google.com/sdk/docs/install) | Authenticated with deploy permissions |
| [Terraform](https://developer.hashicorp.com/terraform/downloads) | `>= 1.5` |
| [Terragrunt](https://terragrunt.gruntwork.io/) | Recommended |

GCP project **`izumi-vpl`** must exist with billing enabled.

---

## 2. Bootstrap (one-time)

### 2.1 Set project number in bootstrap tfvars

```bash
gcloud config set project izumi-vpl
PROJECT_NUMBER=$(gcloud projects describe izumi-vpl --format='value(projectNumber)')
echo "Project number: $PROJECT_NUMBER"
```

Edit `google-cloud/terraform/environments/bootstrap/terraform.tfvars` — replace `REPLACE_WITH_PROJECT_NUMBER` with the value above.

### 2.2 Apply bootstrap

```bash
cd google-cloud/terraform/live/bootstrap
terragrunt apply
```

Creates: Terraform state bucket, Artifact Registry (`izumi-vpl-docker`), Cloud Build source cleanup.

---

## 3. Network (dev)

```bash
cd google-cloud/terraform/live/dev/network
terragrunt apply
```

Creates VPC `izumi-vpl-vpc-dev` with Private Service Access for Cloud SQL.

---

## 4. App stack (dev)

Review `google-cloud/terraform/environments/dev/app/terraform.tfvars`:

- `container_port = 8080` (Cloud Run injects `PORT`)
- `enable_gcs = false` (no file uploads)
- `sql_database_name = "vehicle_pl_system"`
- Update `CORS_ORIGIN`, `NEXT_PUBLIC_API_URL`, `NEXT_PUBLIC_BASE_URL` after first deploy with real `*.run.app` URLs

```bash
cd google-cloud/terraform/live/dev/app
terragrunt apply
```

Creates: Cloud Run API/Web/Migrate, Cloud SQL MySQL, Secret Manager bindings.

---

## 5. Secrets (required)

```bash
# Optional: pass real values via env before running
export GOOGLE_SERVICE_ACCOUNT_JSON='{"type":"service_account",...}'
export GOOGLE_DRIVE_FOLDER_ID='your-folder-id'

bash google-cloud/scripts/create-dev-secrets.sh
```

| Secret ID | Env var |
|-----------|---------|
| `izumi-vpl-database-url-dev` | `DATABASE_URL` (auto-created by Terraform) |
| `izumi-vpl-jwt-secret-dev` | `JWT_SECRET` |
| `izumi-vpl-google-sa-json-dev` | `GOOGLE_SERVICE_ACCOUNT_JSON` |
| `izumi-vpl-google-drive-folder-dev` | `GOOGLE_DRIVE_FOLDER_ID` |

---

## 6. Build & deploy

### 6.1 Images only (first time, before Cloud Run URLs are known)

```bash
source google-cloud/scripts/load-env.sh
export NEXT_PUBLIC_API_URL='https://placeholder.run.app/api'
export NEXT_PUBLIC_BASE_URL='https://placeholder.run.app'
bash google-cloud/scripts/cloud-build-submit.sh google-cloud/cloudbuild/cloudbuild.dev.images.yaml
```

Re-run `terragrunt apply` in `live/dev/app` if images were not available during first apply.

### 6.2 Get Cloud Run URLs

```bash
gcloud run services describe izumi-vpl-api-dev --region=asia-northeast1 --format='value(status.url)'
gcloud run services describe izumi-vpl-web-dev --region=asia-northeast1 --format='value(status.url)'
```

Update `terraform.tfvars` with real URLs in `CORS_ORIGIN`, `NEXT_PUBLIC_API_URL`, `NEXT_PUBLIC_BASE_URL`, then `terragrunt apply`.

### 6.3 Full deploy (build + migrate + deploy)

```bash
source google-cloud/scripts/load-env.sh
export NEXT_PUBLIC_API_URL='https://izumi-vpl-api-dev-XXXX.asia-northeast1.run.app/api'
export NEXT_PUBLIC_BASE_URL='https://izumi-vpl-web-dev-XXXX.asia-northeast1.run.app'
bash google-cloud/scripts/cloud-build-submit.sh google-cloud/cloudbuild/cloudbuild.dev.yaml
```

### 6.4 Seed database (first time)

```bash
# Connect via Cloud SQL Auth Proxy or run seed via one-off job
cd backend && npx prisma db seed
```

Change default admin password after seed (`admin@example.com` / `password`).

---

## 7. GitHub Actions CI/CD

1. **One-shot setup** (after `gcloud auth login` and `gh auth login`):

   ```bash
   bash google-cloud/scripts/setup-github-actions-cicd.sh
   ```

2. Or manual WIF: [cloudbuild/GITHUB_ACTIONS_WIF.md](cloudbuild/GITHUB_ACTIONS_WIF.md)

**Repository secrets:** `GCP_PROJECT_ID`, `GCP_WORKLOAD_IDENTITY_PROVIDER`, `GCP_SERVICE_ACCOUNT`

**Repository variables:**

   - `GCP_NEXT_PUBLIC_API_URL_DEVELOP`
   - `GCP_NEXT_PUBLIC_BASE_URL_DEVELOP`
   - (and `_STAGING` / `_PRODUCTION` when those branches deploy)

Push to `develop` triggers deploy via `.github/workflows/cd-gcp.yml`.

---

## 8. Resource naming (dev)

| Resource | Name |
|----------|------|
| API | `izumi-vpl-api-dev` |
| Web | `izumi-vpl-web-dev` |
| Migrate job | `izumi-vpl-migrate-dev` |
| Cloud SQL | `izumi-vpl-mysql-dev` |
| Database | `vehicle_pl_system` |
| Images | `izumi-vpl-api:dev`, `izumi-vpl-web:dev` |

---

## 9. Troubleshooting

| Issue | Fix |
|-------|-----|
| `gcloud auth` expired | `gcloud auth login` |
| Migrate job fails | Check Cloud SQL is running; verify `DATABASE_URL` secret |
| CORS errors | Update `CORS_ORIGIN` in tfvars to match web URL exactly |
| Drive sync fails | Update `izumi-vpl-google-sa-json-dev` and folder secret; share Drive folder with SA email |
| Auth cookies fail | Ensure both services use HTTPS (`*.run.app`); `NODE_ENV=production` |

---

## 10. Custom domains (optional)

Uncomment `api_custom_domain` / `web_custom_domain` in `terraform.tfvars` and configure DNS per Terraform outputs (`api_domain_mapping_status`).

| Env | Web domain | API domain (optional) |
|-----|------------|------------------------|
| dev | `izumi-vpl-v2.vw-dev.com` | `izumi-vpl-v2-api.vw-dev.com` |
| stg | `vpl-stage.izumilogi.com` | `vpl-stage-api.izumilogi.com` |
| prod | `vpl.izumilogi.com` | `vpl-api.izumilogi.com` |

**Staging:** set GitHub Environment variable `GCP_NEXT_PUBLIC_BASE_URL_STAGING=https://vpl-stage.izumilogi.com` (and `GCP_NEXT_PUBLIC_API_URL_STAGING` to the API URL). Then run:

```bash
bash google-cloud/scripts/setup-stg-custom-domains.sh
```

**Production:** set `GCP_NEXT_PUBLIC_BASE_URL_PRODUCTION` / `GCP_NEXT_PUBLIC_API_URL_PRODUCTION`, then:

```bash
bash google-cloud/scripts/setup-prod-custom-domains.sh
```
