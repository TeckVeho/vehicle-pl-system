# Cloud SQL Dev → Hub (issue #99)

Dev dùng instance chung `gcp-dev-sql-hub:asia-northeast1:dev-sql-hub`, database logic `izumi_vpl_dev`.

Tham khảo: [drivee-link-2](https://github.com/TeckVeho/drivee-link-2) `google_cloud/terraform/environments/dev/app/terraform.tfvars.example`.

## Trước khi apply

1. **Export dump** instance cũ (bắt buộc):

```bash
bash google-cloud/scripts/export-vpl-sql-dumps.sh dev
```

Hoặc thủ công:

```bash
PROJECT=izumi-vpl
OLD_INSTANCE=izumi-vpl-mysql-dev
DUMP_DIR=/tmp/izumi-vpl-dev-sql-migration
mkdir -p "$DUMP_DIR"

gcloud sql export sql "$OLD_INSTANCE" \
  "gs://izumi-vpl-terraform-state/db-migration/dev-export-$(date +%Y%m%d).sql" \
  --database=vehicle_pl_system \
  --project="$PROJECT"

gcloud storage cp \
  "gs://izumi-vpl-terraform-state/db-migration/dev-export-*.sql" \
  "$DUMP_DIR/" --project="$PROJECT"
```

2. **Review plan** — dừng nếu có `destroy` / `replace` trên `google_sql_database_instance` mà bạn chưa backup.

```bash
cd google-cloud/terraform/live/dev/app
terragrunt plan
```

3. Hub prerequisites (platform): `izumi-vpl` trong hub `consumers.tf`, Shared VPC attach.

## Apply

```bash
cd google-cloud/terraform/live/dev/app
terragrunt apply
```

## Import dữ liệu vào hub DB

Sau khi Terraform tạo `izumi-vpl-dev` trên hub:

```bash
gcloud sql import sql dev-sql-hub \
  "gs://izumi-vpl-terraform-state/db-migration/dev-export-YYYYMMDD.sql" \
  --database=izumi-vpl-dev \
  --project=gcp-dev-sql-hub
```

(Lưu ý: Terraform chuẩn hóa `izumi_vpl_dev` → `izumi-vpl-dev` trong GCP.)

## Verify

```bash
gcloud run jobs execute izumi-vpl-migrate-dev --region=asia-northeast1 --project=izumi-vpl --wait
# Smoke test API dev
```

## Sau khi OK

- Tắt/xóa `izumi-vpl-mysql-dev` (Console hoặc `gcloud sql instances delete` sau khi tắt deletion protection).
- Nếu Terraform state còn instance cũ: `terragrunt state rm 'module.app_compose.module.cloud_sql.google_sql_database_instance.main[0]'` trước apply hub (tránh destroy không chủ ý).

## Rollback

1. Khôi phục tfvars dev (bỏ `external_cloud_sql_connection_name`, bật lại instance local).
2. Restore dump vào `izumi-vpl-mysql-dev` nếu cần.
