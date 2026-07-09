# Terraform state migration snapshots

Captured during layout migration from `infra/terraform/` to `infra/{modules,environments}/`.

## Bootstrap (local state)

```
google_project_service.storage
google_storage_bucket.terraform_state
```

If the state bucket already exists (typical after first apply), import into
`google-cloud/terraform/environments/bootstrap/` before expecting a zero-diff plan:

```bash
cd google-cloud/terraform/environments/bootstrap
terraform import -var-file=terraform.tfvars google_project_service.storage PROJECT_ID/storage.googleapis.com
terraform import -var-file=terraform.tfvars google_storage_bucket.terraform_state BUCKET_NAME
```

## App stack state addresses

Production GCS state for `app/{env}` was created from **flat** `Save/infra/terraform/*.tf`
(root module). `environments/*/app/moved.tf` maps those addresses into template modules.

## Remote stacks (GCS: `dx-kumu-common-terraform-state`)

Re-run after `gcloud auth application-default login` if credentials expire:

```bash
# common
cd infra/terraform/common && terraform state list > ../../.migration-snapshots/common-state-list.txt

# app dev (backend prefix app/dev)
cd infra/terraform && terraform init -reconfigure && terraform state list > ../.migration-snapshots/app-dev-state-list.txt

# network dev
cd infra/terraform/network && terraform init -reconfigure -backend-config=backend.tf.dev && terraform state list > ../../.migration-snapshots/network-dev-state-list.txt
```

Lock files copied from each stack at migration time.
