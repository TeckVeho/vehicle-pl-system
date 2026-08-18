# Production: backup enabled + 17:00 UTC start are fixed in module locals (dev/stg configure via tfvars).

check "prod_sql_backup_start_time_not_overridden" {
  assert {
    condition = var.env_suffix != "prod" || !var.enable_cloud_sql || var.sql_backup_start_time == "17:00"
    error_message = "Production: sql_backup_start_time is fixed at 17:00 UTC. Remove sql_backup_start_time from prod tfvars."
  }
}

check "sql_shared_with_env_suffix_not_self" {
  assert {
    condition     = var.sql_shared_with_env_suffix == "" || var.sql_shared_with_env_suffix != var.env_suffix
    error_message = "sql_shared_with_env_suffix must differ from env_suffix (cannot share instance with yourself)."
  }
}

check "sql_schedule_incompatible_with_shared_instance" {
  assert {
    condition     = var.sql_shared_with_env_suffix == "" || !var.enable_sql_night_weekend_schedule
    error_message = "enable_sql_night_weekend_schedule cannot be true when sql_shared_with_env_suffix is set (would stop the shared instance)."
  }
}

check "sql_hub_and_stg_share_mutually_exclusive" {
  assert {
    condition     = var.sql_shared_with_env_suffix == "" || trimspace(var.external_cloud_sql_connection_name) == ""
    error_message = "sql_shared_with_env_suffix and external_cloud_sql_connection_name cannot both be set."
  }
}

check "sql_schedule_incompatible_with_external_hub" {
  assert {
    condition     = trimspace(var.external_cloud_sql_connection_name) == "" || !var.enable_sql_night_weekend_schedule
    error_message = "enable_sql_night_weekend_schedule cannot be true when using external SQL hub (would affect shared hub instance)."
  }
}
