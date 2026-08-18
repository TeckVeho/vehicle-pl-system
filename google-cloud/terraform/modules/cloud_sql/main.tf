# Optional Cloud SQL (MySQL) + Secret Manager DATABASE_URL + Cloud Run unix socket.
# Enable with enable_cloud_sql = true after enabling billing and APIs.
# Set create_sql_instance = false + sql_shared_instance_name to share one instance across envs.

resource "google_project_service" "sqladmin" {
  count   = var.enable_cloud_sql ? 1 : 0
  service = "sqladmin.googleapis.com"
  # Single-project, many-env: destroying one env must not disable the API for others.
  disable_on_destroy = false
}

resource "google_project_service" "secretmanager" {
  count              = var.enable_cloud_sql ? 1 : 0
  service            = "secretmanager.googleapis.com"
  disable_on_destroy = false
}

resource "random_password" "sql_app" {
  count = var.enable_cloud_sql ? 1 : 0

  length = 24
  # Cloud SQL password_validation_policy (COMPLEXITY_DEFAULT) requires lower, upper, numeric, and special.
  lower       = true
  upper       = true
  numeric     = true
  special     = true
  min_lower   = 1
  min_upper   = 1
  min_numeric = 1
  min_special = 1
}

# Root password is required when password_validation_policy is enabled (not embedded in app DATABASE_URL).
resource "random_password" "sql_root" {
  count = var.enable_cloud_sql && var.create_sql_instance ? 1 : 0

  length      = 24
  lower       = true
  upper       = true
  numeric     = true
  special     = true
  min_lower   = 1
  min_upper   = 1
  min_numeric = 1
  min_special = 1
}

locals {
  name_prefix      = replace(var.project_id, "_", "-")
  sql_default_name = "${local.name_prefix}-mysql-${var.env_suffix}"
  sql_instance_name_effective = substr(
    replace(
      lower(var.sql_instance_name != "" ? var.sql_instance_name : local.sql_default_name),
      "_",
      "-",
    ),
    0,
    96,
  )
  sql_shared_instance_name_effective = substr(
    replace(lower(var.sql_shared_instance_name), "_", "-"),
    0,
    96,
  )
  sql_host_instance_name = var.create_sql_instance ? local.sql_instance_name_effective : (
    local.use_external_connection ? local.external_instance_id : local.sql_shared_instance_name_effective
  )
  sql_logical_name_effective = substr(
    replace(
      lower(var.sql_database_name != "" ? var.sql_database_name : local.sql_default_name),
      "_",
      "-",
    ),
    0,
    64,
  )
  sql_user_name_effective = substr(
    replace(
      lower(var.sql_user_name != "" ? var.sql_user_name : local.sql_logical_name_effective),
      "_",
      "-",
    ),
    0,
    32,
  )
  database_url_secret_id = substr("${local.name_prefix}-database-url-${var.env_suffix}", 0, 63)

  sql_instance_project_effective = var.sql_instance_project != "" ? var.sql_instance_project : var.project_id
  cloudsql_client_project_effective = (
    var.cloudsql_client_iam_project != "" ? var.cloudsql_client_iam_project : var.project_id
  )
  use_external_connection = trimspace(var.external_cloud_sql_connection_name) != ""
  use_shared_data_source  = var.enable_cloud_sql && !var.create_sql_instance && !local.use_external_connection
  external_instance_id = substr(
    replace(lower(var.sql_instance_name != "" ? var.sql_instance_name : "dev-sql-hub"), "_", "-"),
    0,
    96,
  )
  sql_connection_name_effective = var.enable_cloud_sql ? (
    var.create_sql_instance
    ? google_sql_database_instance.main[0].connection_name
    : (
      local.use_external_connection
      ? trimspace(var.external_cloud_sql_connection_name)
      : data.google_sql_database_instance.shared[0].connection_name
    )
  ) : ""
}

data "google_sql_database_instance" "shared" {
  count   = local.use_shared_data_source ? 1 : 0
  name    = local.sql_shared_instance_name_effective
  project = var.project_id
}

check "external_sql_target_required" {
  assert {
    condition = (
      !var.enable_cloud_sql
      || var.create_sql_instance
      || local.use_external_connection
      || var.sql_shared_instance_name != ""
    )
    error_message = "When create_sql_instance is false, set external_cloud_sql_connection_name (hub) or sql_shared_instance_name (same-project shared instance)."
  }
}

resource "google_sql_database_instance" "main" {
  count = var.enable_cloud_sql && var.create_sql_instance ? 1 : 0

  name             = local.sql_instance_name_effective
  database_version = "MYSQL_8_0"
  region           = var.region
  root_password    = random_password.sql_root[0].result

  settings {
    tier              = var.sql_tier_effective
    disk_size         = var.sql_disk_size_gb_effective
    disk_type         = var.sql_disk_type_effective
    activation_policy = "ALWAYS"
    # API-level protection (Console / gcloud). Root `deletion_protection` below is Terraform-only.
    deletion_protection_enabled = true
    ip_configuration {
      ipv4_enabled    = false
      private_network = var.network_self_link
      ssl_mode        = "ENCRYPTED_ONLY"
      # Org policy sql.restrictPublicIp: no public IPv4; Cloud Run uses unix socket + Direct VPC for private path.
    }

    password_validation_policy {
      enable_password_policy      = true
      min_length                  = 8
      complexity                  = "COMPLEXITY_DEFAULT"
      disallow_username_substring = true
    }

    dynamic "database_flags" {
      for_each = var.enable_sql_audit ? [1] : []
      content {
        name  = "cloudsql_mysql_audit"
        value = "ON"
      }
    }

    backup_configuration {
      enabled                        = var.sql_backup_enabled
      start_time                     = var.sql_backup_start_time
      point_in_time_recovery_enabled = var.sql_backup_enabled && var.sql_point_in_time_recovery_enabled
    }
  }

  deletion_protection = true

  # Cloud Scheduler may set NEVER overnight; do not revert on the next terraform apply.
  lifecycle {
    ignore_changes = [settings[0].activation_policy]
  }

  depends_on = [
    google_project_service.sqladmin,
  ]
}

resource "google_sql_database" "app" {
  count = var.enable_cloud_sql ? 1 : 0

  name     = local.sql_logical_name_effective
  project  = local.sql_instance_project_effective
  instance = local.sql_host_instance_name
}

resource "google_sql_user" "app" {
  count = var.enable_cloud_sql ? 1 : 0

  name     = local.sql_user_name_effective
  project  = local.sql_instance_project_effective
  instance = local.sql_host_instance_name
  password = random_password.sql_app[0].result
}

resource "google_secret_manager_secret" "database_url" {
  count = var.enable_cloud_sql ? 1 : 0

  secret_id = local.database_url_secret_id

  replication {
    auto {}
  }

  depends_on = [google_project_service.secretmanager]
}

resource "google_secret_manager_secret_version" "database_url" {
  count = var.enable_cloud_sql ? 1 : 0

  secret = google_secret_manager_secret.database_url[0].id
  secret_data = format(
    # socket= for Prisma migrate CLI + Cloud SQL; API maps to socketPath in db.service (mariadb adapter).
    "mysql://%s:%s@localhost/%s?socket=/cloudsql/%s",
    local.sql_user_name_effective,
    urlencode(random_password.sql_app[0].result),
    local.sql_logical_name_effective,
    local.sql_connection_name_effective,
  )
}

resource "google_secret_manager_secret_iam_member" "cloudrun_database_url" {
  count = var.enable_cloud_sql ? 1 : 0

  project   = var.project_id
  secret_id = google_secret_manager_secret.database_url[0].secret_id
  role      = "roles/secretmanager.secretAccessor"
  member    = "serviceAccount:${var.cloud_run_service_account}"
}

resource "google_project_iam_member" "cloudrun_sql_client" {
  count = var.enable_cloud_sql && var.grant_cloudsql_client_iam ? 1 : 0

  project = local.cloudsql_client_project_effective
  role    = "roles/cloudsql.client"
  member  = "serviceAccount:${var.cloud_run_service_account}"
}
