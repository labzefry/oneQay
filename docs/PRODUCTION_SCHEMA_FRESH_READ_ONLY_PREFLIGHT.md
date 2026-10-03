# oneQay — Fresh Production Schema Inventory and Locked Plan

Author by Lab | zefry

## Scope

This successor performs a read-only direct PDO MySQL inspection of the designated Production database, using the unchanged Sprint267 source lock for the original 27 migrations. It does NOT perform Production schema provisioning, execute migrations, grant authority, deploy, or activate traffic.

Historical migration source: 505518f79e8a70f789b94f5074a040eae785aeb0. A newer application release SHA does not change the historical migration identity.

## cPanel workflow

The dedicated GitHub workflow builds a secret-free preflight kit containing this guide, the inspector, the existing Sprint267 planning foundation, the immutable source lock, 27 original migration files and placeholder-only JSON templates.

Download the ZIP from a successful workflow run on canonical main. Extract it under a PRIVATE operator root outside every public document root. Copy the target and database-binding templates to private JSON files. Enter only actual cPanel-observed paths, real database name, host and port. Keep real database credentials exclusively in a private file with mode 0600. Do not place sensitive material in GitHub, chat, public_html or the release archive.

The configured environment must be oneqay-production-01, runtime_class production, and migration_source_commit 505518f79e8a70f789b94f5074a040eae785aeb0.

Run ONE absolute command through one-shot cPanel Cron Jobs. Replace the placeholders with verified paths, never guessed paths:

```text
<PHP_CLI> <PRIVATE_OPERATOR_ROOT>/tools/production/cpanel/inspect-governed-production-schema-bootstrap-target.php <PRIVATE_OPERATOR_ROOT>/apps/web/database/migrations <PRIVATE_TARGET_JSON> <PRIVATE_DB_CREDENTIALS_JSON> <PRIVATE_OUTPUT_INVENTORY_JSON> <PRIVATE_OUTPUT_PLAN_JSON> > <PRIVATE_OUTPUT_LOG> 2>&1
```

Use nonexistent inventory and plan output filenames under an existing private directory. Delete the one-shot Cron entry after execution.

Success output:

```text
PRODUCTION_SCHEMA_READ_ONLY_PREFLIGHT=PASS
PRODUCTION_SCHEMA_BOOTSTRAP_PLAN=PREPARED_NOT_AUTHORIZED
PRODUCTION_DATABASE_MUTATION=NOT_PERFORMED
```

The inventory and plan are owner-only secret-free JSON outputs. Do not post the secret database-binding input.

## Fail-closed limitations

The tool validates all 27 original migration blobs, rejects additional or missing migration files, checks direct SELECT DATABASE() readback against the declared target, and requires zero visible tables/views, routines, triggers and events. It reads information_schema in a read-only transaction. It never executes migration code or writes to the database.

Unexpected objects, missing privileges, input drift, dangerous paths or incorrect credentials result in failure and no successful plan. Do not remove objects merely to force a pass. The direct inventory represents a dated historical observation and is not operational authority. It cannot guarantee the schema is still empty later.

The future Production executor MUST independently perform fresh direct emptiness verification immediately before any write, verify exact release/source and target bindings, use distinct short-lived owner-issued provisioning authority, retain durable evidence, and have an explicit backup and partial-failure recovery plan. MySQL DDL cannot generally be rolled back as a single transaction.

Deployment authority, Production business traffic, Technical Preview, updater, permission reprovisioning and feature reactivation remain unchanged and unauthorized.
