# Terragrunt Live Layout

Single GCP project (`izumi-vpl`); environments separated by `env_suffix` in tfvars.

## Stack mapping

| Terragrunt unit | Terraform source |
|---|---|
| `live/bootstrap` | `environments/bootstrap` (state bucket + Artifact Registry + Cloud Build cleanup) |
| `live/dev/network` | `environments/_shared/network` + `environments/dev/network/terraform.tfvars` |
| `live/dev/app` | `environments/_shared/app` + `environments/dev/app/terraform.tfvars` |
| `live/stg/network` | `environments/_shared/network` + `environments/stg/network/terraform.tfvars` |
| `live/stg/app` | `environments/_shared/app` + `environments/stg/app/terraform.tfvars` |
| `live/prod/network` | `environments/_shared/network` + `environments/prod/network/terraform.tfvars` |
| `live/prod/app` | `environments/_shared/app` + `environments/prod/app/terraform.tfvars` |

## Usage

```bash
cd google-cloud/terraform/live

# bootstrap → network → app (per env)
terragrunt run-all plan
```

Run a single stack:

```bash
cd google-cloud/terraform/live/stg/network
terragrunt plan
```

## Notes

- Remote state bucket: `izumi-vpl-terraform-state` (in `live/*/terragrunt.hcl`).
- Keep `project_id` in `terraform.tfvars` aligned with `GCP_PROJECT_ID` in `load-env.sh` (`izumi-vpl`).
- `env_suffix` in tfvars: `dev` | `stg` | `prod` (Terraform resource names).
- CI image tags: `dev` | `stage` | `prod` (Git branch `develop` | `staging` | `production`).
- Terragrunt cache is ignored via `**/.terragrunt-cache/`.
