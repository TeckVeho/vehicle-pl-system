data "terraform_remote_state" "network" {
  count = (
    var.enable_cloud_sql && var.vpc_network_override == ""
  ) ? 1 : 0
  backend = "gcs"
  config = {
    bucket = var.network_remote_state_bucket
    prefix = var.network_remote_state_prefix
  }
}

# Stg sharing prod Cloud SQL: instance private IP lives on prod VPC — Cloud Run must attach there.
data "terraform_remote_state" "network_sql_host" {
  count   = var.enable_cloud_sql && var.sql_shared_with_env_suffix != "" ? 1 : 0
  backend = "gcs"
  config = {
    bucket = var.network_remote_state_bucket
    prefix = "network/${var.sql_shared_with_env_suffix}"
  }
}
