# GCP IAM cleanup — issue #154 (`izumi-vpl`)

Operator runbook after [#153](https://github.com/TeckVeho/vehicle-pl-system/issues/153) is merged and `setup-github-actions-wif.sh` has been applied.

## Preconditions

- [ ] `google-cloud/cloudbuild/*.yaml` use `izumi-vpl-cd-build@`
- [ ] `bash google-cloud/scripts/setup-github-actions-wif.sh` completed (not dry-run)
- [ ] At least one CD build **SUCCESS** with execution SA `izumi-vpl-cd-build@izumi-vpl.iam.gserviceaccount.com`
- [ ] Cloud Run services + migrate jobs use `izumi-vpl-run-{dev,stg,prod}@` (Terraform `app_compose` runtime SA)

## Verify identity

```bash
export PROJECT=izumi-vpl
gcloud builds list --project="$PROJECT" --limit=5 --format='table(id,status,serviceAccount)'
gcloud run services list --project="$PROJECT" --format='table(name,serviceAccount)' --region=asia-northeast1
gcloud run jobs list --project="$PROJECT" --format='table(name,serviceAccount)' --region=asia-northeast1
```

## Remove Compute default SA project roles

Compute SA: `160208442010-compute@developer.gserviceaccount.com` (confirm project number).

After workloads moved, remove bindings on **`izumi-vpl`** for that member (Console IAM or `gcloud projects remove-iam-policy-binding`), including:

- `roles/run.admin`, `roles/run.developer`
- `roles/storage.objectAdmin`, `roles/storage.admin`
- `roles/iam.serviceAccountUser`
- `roles/cloudbuild.builds.builder`
- `roles/artifactregistry.writer`
- `roles/logging.logWriter`

Also remove **actAs** bindings where compute@ is principal or target, once no resource uses it.

## Cloud Build default SA

If builds no longer use `160208442010@cloudbuild.gserviceaccount.com`, remove leftover project-wide roles there too (keep Google-managed Cloud Build service agent unchanged).

## Dev SQL hub (`gcp-dev-sql-hub`)

Do **not** delete `roles/cloudsql.client` in Console only — it is managed by hub Terraform.

1. Ensure dev Cloud Run uses `izumi-vpl-run-dev@` with SQL client on runtime SA (app stack).
2. In hub repo `consumers.tf`, remove `izumi-vpl` from `consumer_projects` when compute@ no longer needs hub access.
3. `terraform apply` on hub.

## Bootstrap Terraform (repo)

In `google-cloud/terraform/environments/bootstrap/terraform.tfvars`:

```hcl
grant_default_compute_reader = false
additional_artifact_registry_writer_members = [
  "serviceAccount:izumi-vpl-cd-build@izumi-vpl.iam.gserviceaccount.com",
]
```

Apply bootstrap after CD is green so Artifact Registry pulls are not tied to compute@.

## Acceptance (close #154)

- [ ] One successful deploy after IAM change
- [ ] compute@ has **no** project roles on `izumi-vpl`
- [ ] CD builds use `izumi-vpl-cd-build@` only
- [ ] Hub consumer list updated if applicable