# Slim variables for secrets — only inputs used by this module.
# Full interface: modules/app_compose/variables.tf

variable "api_secret_env_from_sm" {
  type = list(object({
    env_name  = string
    secret_id = string
    version   = optional(string, "latest")
  }))
  default     = []
  description = <<-EOT
    Inject env vars from Secret Manager (create secrets in GCP first). Runtime SA gets secretAccessor on each secret_id.
    Do not use env names that already exist in env_vars.
  EOT
}

variable "project_id" {
  type        = string
  description = "GCP project ID (e.g. kumu-dev)."
}

variable "web_secret_env_from_sm" {
  type = list(object({
    env_name  = string
    secret_id = string
    version   = optional(string, "latest")
  }))
  default     = []
  description = "Same as api_secret_env_from_sm for the web Cloud Run service when enable_web = true."
}

variable "app_secrets_bundle_secret_id" {
  type        = string
  default     = ""
  description = "Bundled app secrets .env secret id (see google-cloud/scripts/create-app-secrets-bundle.sh)."
}
