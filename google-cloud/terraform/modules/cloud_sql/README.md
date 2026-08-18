# cloud sql

Cloud SQL for MySQL 8.0. Private IP, HA (prod). See infrastructure.md §8.

## Modes

| `create_sql_instance` | Behavior |
|----------------------|----------|
| `true` (default) | Create instance + database + user + `DATABASE_URL` secret |
| `false` | Attach database + user to `sql_shared_instance_name` (stg on prod instance) or external hub |

Stg/prod merge: [docs/sql-stg-prod-shared-instance.md](../../docs/sql-stg-prod-shared-instance.md).
Dev hub: [docs/sql-dev-hub-migration.md](../../docs/sql-dev-hub-migration.md).

See: `docs/architecture/infrastructure.md`
