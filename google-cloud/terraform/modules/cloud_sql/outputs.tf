output "cloud_sql_instance_name" {
  value = var.enable_cloud_sql ? (
    var.create_sql_instance
    ? google_sql_database_instance.main[0].name
    : (
      local.use_external_connection
      ? local.external_instance_id
      : data.google_sql_database_instance.shared[0].name
    )
  ) : null
}

output "cloud_sql_connection_name" {
  value = var.enable_cloud_sql ? local.sql_connection_name_effective : null
}

output "database_url_secret_name" {
  value = var.enable_cloud_sql ? google_secret_manager_secret.database_url[0].name : null
}

output "database_url_secret_version_name" {
  value = var.enable_cloud_sql ? google_secret_manager_secret_version.database_url[0].name : null
}

output "cloudsql_client_iam_member_id" {
  value = var.enable_cloud_sql && var.grant_cloudsql_client_iam ? google_project_iam_member.cloudrun_sql_client[0].id : "external-iam"
}

output "database_url_secret_id" {
  value = var.enable_cloud_sql ? google_secret_manager_secret.database_url[0].secret_id : null
}

output "sql_database_name" {
  value = var.enable_cloud_sql ? local.sql_logical_name_effective : null
}

output "create_sql_instance" {
  value = var.enable_cloud_sql ? var.create_sql_instance : null
}
