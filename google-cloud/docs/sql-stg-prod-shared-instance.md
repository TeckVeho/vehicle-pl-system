# Cloud SQL: gộp instance Stg + Prod (chỉ tách DB)

Stg và Prod dùng **một** Cloud SQL instance (`izumi-vpl-mysql-prod`), mỗi môi trường có database + MySQL user riêng.

| Môi trường | Terraform stack | Instance | Database | Secret `DATABASE_URL` |
|------------|-----------------|----------|----------|------------------------|
| **prod** | `live/prod/app` | Tạo + quản lý | `izumi-vpl-prod` | `izumi-vpl-database-url-prod` |
| **stg** | `live/stg/app` | Dùng instance prod (`sql_shared_with_env_suffix = "prod"`) | `izumi-vpl-stg` | `izumi-vpl-database-url-stg` |

## Terraform (stg)

Trong `environments/stg/app/terraform.tfvars`:

```hcl
enable_cloud_sql = true
sql_shared_with_env_suffix = "prod"

network_remote_state_bucket = "izumi-vpl-terraform-state"
network_remote_state_prefix = "network/stg"

# Bắt buộc false — schedule tắt SQL sẽ ảnh hưởng cả prod
enable_sql_night_weekend_schedule = false
```

Prod giữ nguyên `enable_cloud_sql = true` (không set `sql_shared_with_env_suffix`).

## Migration từ instance stg riêng

Thực hiện **sau khi prod instance đã RUNNABLE** và đã backup.

### 1. Export dữ liệu stg cũ

```bash
bash google-cloud/scripts/export-vpl-sql-dumps.sh stg
```

### 2. Apply prod (nếu chưa có instance)

```bash
cd google-cloud/terraform/live/prod/app
terragrunt apply
```

### 3. Cập nhật tfvars stg + plan

Thêm `sql_shared_with_env_suffix = "prod"` và `enable_sql_night_weekend_schedule = false`.

```bash
cd google-cloud/terraform/live/stg/app
terragrunt plan
```

Plan sẽ:
- **Tạo** `google_sql_database` + `google_sql_user` trên instance prod
- **Cập nhật** Secret `izumi-vpl-database-url-stg` (connection name prod)
- **Xóa** `google_sql_database_instance` stg cũ (nếu vẫn trong state)

### 4. Import dữ liệu vào DB stg mới (trên instance prod)

```bash
PROD_INSTANCE=izumi-vpl-mysql-prod

gcloud sql import sql "${PROD_INSTANCE}" \
  "gs://izumi-vpl-terraform-state/db-migration/stg-export-YYYYMMDD.sql" \
  --database=izumi-vpl-stg \
  --project=izumi-vpl
```

### 5. Apply stg + verify

```bash
terragrunt apply
```

- Smoke test API stg (`/health` hoặc login)
- Chạy migrate job: `gcloud run jobs execute izumi-vpl-migrate-stg --region=asia-northeast1`

### 6. Xóa instance stg cũ (nếu plan chưa destroy)

```bash
gcloud sql instances delete izumi-vpl-mysql-stg --project=izumi-vpl
```

(Tắt deletion protection trước nếu bật.)

## Cloud Run memory (tier3 = 256Mi)

Giới hạn heap Node:

- `env_vars.NODE_OPTIONS = "--max-old-space-size=192"`
- `backend/Dockerfile.prod` runner: `ENV NODE_OPTIONS=--max-old-space-size=192`

## VPC khi stg dùng instance prod

Instance Cloud SQL private IP nằm trên **prod VPC** (`izumi-vpl-vpc-prod`). Terraform tự gắn stg Cloud Run (API + migrate job) vào **prod subnet** khi `sql_shared_with_env_suffix = "prod"` — không dùng `izumi-vpl-vpc-stg` cho kết nối SQL.

`network_remote_state_prefix = "network/stg"` vẫn giữ cho stack stg (nếu cần tham chiếu VPC stg sau này).

## Lưu ý vận hành

- **Không** bật `enable_sql_night_weekend_schedule` cho stg khi dùng shared instance.
- Stg deployer IAM scope gồm instance prod — cần thiết cho `cloudsql.client`; database vẫn tách user.
- Backup/PITR theo policy **prod** (áp dụng cho toàn instance).
- Dev dùng hub (`gcp-dev-sql-hub`), không dùng instance riêng.

## Rollback

1. Khôi phục tfvars stg (bỏ `sql_shared_with_env_suffix`).
2. `terragrunt apply` tạo lại instance stg (trống hoặc restore từ export).
3. Import lại dump nếu cần.
