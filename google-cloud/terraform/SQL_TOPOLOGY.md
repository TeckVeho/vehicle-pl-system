# SQL topology (vehicle-pl-system / izumi-vpl)

## Target

| Env | Cloud SQL |
|-----|-----------|
| **dev** | External hub `gcp-dev-sql-hub:asia-northeast1:dev-sql-hub` (Shared VPC + Direct VPC) |
| **stg** | Shared instance `izumi-vpl-mysql-prod` + DB `izumi-vpl-stg` (attach-only) |
| **prod** | Owns instance `izumi-vpl-mysql-prod` + DB `izumi-vpl-prod` |

Data export/remove of old instances is done manually in Console (not Terraform).

## Module flags

| Flag | Meaning |
|------|---------|
| `enable_cloud_sql` | Attach SQL to Cloud Run (VPC/volume/jobs/secret) |
| `create_sql_instance` | Create/manage `google_sql_database_instance` in this stack (derived from tfvars) |
| `sql_shared_with_env_suffix` | Stg attaches to prod instance instead of creating its own |
| `external_cloud_sql_connection_name` | Dev attaches to hub instance |

## Apply order (when you choose to apply)

1. Hub (`gcp-dev-sql-hub`) already grants `izumi-vpl`
2. Export dumps for `izumi-vpl-mysql-dev`, `izumi-vpl-mysql-stg`, `izumi-vpl-mysql-prod` (see `docs/sql-*.md`)
3. Copy `environments/*/app/terraform.tfvars.example` → `terraform.tfvars`
4. Dev: `terragrunt plan` in `live/dev/app` (after hub Shared VPC attach)
5. Prod: unchanged ownership of instance (tfvars documents shared use)
6. Stg: switch to attach-only + `network/prod`; create stg DB/user on shared instance
7. Console: export/remove old `izumi-vpl-mysql-dev` / `izumi-vpl-mysql-stg` when ready

## Notes

- Stg must use **prod VPC** for Direct VPC (Private IP is non-transitive across VPCs).
- Never enable `enable_sql_night_weekend_schedule` on shared/hub instances from consumer stacks.
- Stg SQL user is `izumi-vpl-stg` (distinct from prod `izumi-vpl-prod`) to avoid two Terraform states fighting over one user.
