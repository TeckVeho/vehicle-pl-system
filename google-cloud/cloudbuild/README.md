# Cloud Build configs for vehicle-pl-system (izumi-vpl)

| File | Purpose |
|------|---------|
| `cloudbuild.dev.yaml` | Full: API + web + migrate + deploy |
| `cloudbuild.dev.api.yaml` | API only (+ migrate) |
| `cloudbuild.dev.web.yaml` | Web only |
| `cloudbuild.dev.images.yaml` | Images only (before first Terraform apply) |

Substitutions: `_REGION=asia-northeast1`, `_REPO=izumi-vpl-docker`, `_TAG=dev`.

See [GITHUB_ACTIONS_WIF.md](GITHUB_ACTIONS_WIF.md) for CI setup.
