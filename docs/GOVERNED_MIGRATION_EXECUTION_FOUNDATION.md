# Governed Migration Execution Foundation

## Status

Sprint 18 implements the bounded **Governed Migration Execution Foundation** authorized by `docs/SPRINT_18_GOVERNED_MIGRATION_EXECUTION_ENTRY_GATE.md`.

Status after publication: **IMPLEMENTED / DISPOSABLE-SQLITE-TEST-ONLY / LOCAL-TEST-CI EXECUTING**.

Production readiness remains **NO-GO**.

Attribution: **Lab | zefry**

## Purpose

Sprint 17 established deterministic, tamper-evident filesystem staging for exact Sprint 16 Laravel migration artifacts.

Sprint 18 establishes the next controlled boundary:

`LaravelMigrationGenerationArtifact`

+ exact Sprint 17 `LaravelMigrationMaterializationReport`

+ immediate Sprint 17 re-validation

+ isolated staging parent

+ `DISPOSABLE_SQLITE_TEST` execution adapter

+ execution correlation ID

→ deterministic execution workspace

→ exclusive local execution lock

→ bounded execution journal

→ disposable-target preflight witness

→ exact ordered migration execution

→ per-step applied journal progression

→ final target verification witness

→ immutable execution report.

Sprint 18 is the first oneQay migration-governance stage that permits generated Laravel migration `up()` execution.

That authority exists only inside Local/Test/CI disposable-target proof.

It is not Preview, cPanel, live, customer-data, deployment, Release, or Production authority.

## Exact implementation envelope

Sprint 18 changes exactly:

1. `src/SchemaPlanning/Foundation.php`;
2. `src/SchemaPlanning/LaravelMigrationExecution.php`;
3. `tests/migration.php`;
4. `apps/web/tests/run.php`;
5. `docs/GOVERNED_MIGRATION_EXECUTION_FOUNDATION.md`.

Sprint 18 does not modify:

- `src/Migration/Foundation.php`;
- `apps/web/config/database.php`;
- `apps/web/composer.json`;
- `apps/web/composer.lock`;
- `apps/web/database/**`;
- routes, controllers, providers, or application services;
- workflow files;
- deployment files;
- updater files;
- release files.

## Existing generic Migration Foundation remains unchanged

The generic `src/Migration/Foundation.php` retains its existing dry-run contract.

Its `MigrationPlan` remains explicitly dry-run and its existing `MigrationExecutionService` remains the previously published synthetic coordination foundation.

Sprint 18 does not weaken, reinterpret, or replace that contract.

The Sprint 18 executor belongs to the governed SchemaPlanning → Laravel artifact pipeline and operates directly on exact Sprint 16/Sprint 17 artifacts.

## Execution target contract

`LaravelMigrationExecutionTargetAdapter` defines the framework-specific execution target boundary.

For Sprint 18, the only accepted target kind is:

`DISPOSABLE_SQLITE_TEST`

Any other target kind fails closed before target preflight or migration execution.

The interface exposes only:

- `targetKind()`;
- `preflight()`;
- `execute()`;
- `verify()`.

It does not accept:

- a hostname;
- a port;
- a DSN;
- a username;
- a password;
- a cPanel database identifier;
- a Preview or Production target reference.

## Immediate Sprint 17 re-validation

`GovernedLaravelMigrationExecutor` never trusts the caller-supplied materialization report alone.

Before execution it calls the Sprint 17 `GovernedLaravelMigrationMaterializer::validate()` boundary against:

- the exact Laravel generation artifact;
- exact application Composer manifest content;
- the same isolated staging parent.

A failure is converted into the bounded Sprint 18 error:

`LARAVEL_MIGRATION_EXECUTION_MATERIALIZATION_VALIDATION_FAILED`.

No target preflight and no migration execution occurs if re-validation fails.

This provides a final byte-integrity barrier immediately before execution.

## Materialization-report binding

After re-validation, Sprint 18 verifies that the supplied Sprint 17 report and the fresh validation result both bind to the same exact generation artifact.

The binding covers:

- generation-artifact fingerprint;
- framework identity;
- exact framework version;
- generation correlation ID;
- deterministic materialization workspace;
- file count;
- file ordering;
- per-file relative path;
- expected SHA-256;
- persisted SHA-256;
- persisted byte count.

A stale or substituted materialization report fails closed.

## Deterministic execution workspace

Execution governance metadata is stored under:

`.oneqay-migration-execution/<first-24-hex-of-generation-artifact-fingerprint>/`

inside the same caller-owned isolated staging parent.

The workspace identity uses no:

- wall-clock time;
- random UUID;
- environment secret;
- database state;
- network response;
- tenant/customer data.

Only two governed metadata files are allowed in this execution workspace:

- `execution.lock`;
- `journal.json`.

Unexpected entries fail closed.

## Execution lock

Sprint 18 uses an exclusive local file lock on `execution.lock`.

The lock is:

- opened only inside the isolated execution workspace;
- acquired with non-blocking exclusive semantics;
- denied if already held;
- associated with a bounded SHA-256 owner derived from the execution correlation ID;
- released in the executor's `finally` path.

This lock is only a Local/Test/CI process-coordination mechanism.

It is not a database advisory lock and creates no Production locking authority.

## Execution journal

Sprint 18 maintains `journal.json` as a bounded local governance journal.

The journal contains only metadata including:

- generation-artifact fingerprint;
- canonical target-manifest fingerprint;
- framework/framework version;
- execution correlation ID;
- target kind;
- baseline witness;
- ordered migration identifiers;
- applied migration identifiers;
- state;
- bounded error code when failed;
- final target witness when complete.

The journal does not contain:

- migration source;
- SQL text;
- credentials;
- DSNs;
- database paths;
- absolute staging paths;
- environment secrets;
- row data;
- customer records.

Journal writes use a bounded temporary file followed by rename and exact readback SHA-256 verification.

## Journal state machine

Sprint 18 implements:

`PREPARED`

→ `RUNNING`

→ ordered applied identifiers

→ `COMPLETE`

or

→ `FAILED`.

A `PREPARED`, `RUNNING`, or `FAILED` prior journal is not resumed.

Because Sprint 16 migrations are forward-only, automatic crash recovery or partial resume would create an unsafe semantic claim.

Sprint 18 therefore fails closed and requires disposal of the contaminated target/workspace before another fresh execution scenario.

## Ordered execution

Execution order is exactly the `LaravelMigrationGenerationArtifact::files()` order.

For every file, Sprint 18 re-checks:

- exact staged relative path;
- regular-file identity;
- no symlink;
- exact SHA-256 against the generation artifact.

Only then is the file passed to the target adapter.

The adapter is not given a directory to scan and cannot choose another migration file through the executor contract.

After every successful migration step, its governed migration identifier is appended to the journal before the next step is attempted.

## Fail-stop behavior

If one migration execution throws:

- no later migration is attempted;
- the current applied-prefix remains recorded;
- journal state becomes `FAILED`;
- error code becomes `LARAVEL_MIGRATION_EXECUTION_FAILED`;
- exception text is not persisted;
- the execution lock is released;
- no `down()` method is invoked;
- no automatic schema repair is attempted.

The disposable target is then considered contaminated and must be discarded.

## Completed-run idempotency

A `COMPLETE` journal does not cause migrations to run again.

For an idempotent repeat, Sprint 18:

1. repeats Sprint 17 byte re-validation;
2. confirms exact journal/artifact identity;
3. calls target `verify()` only;
4. requires the current target witness to equal the stored final witness;
5. requires the complete applied list to equal the exact governed migration order.

If those conditions hold, the executor returns an immutable report with `already_complete = true`.

If the target witness differs, the execution fails closed.

## Runtime witnesses

The target adapter supplies two SHA-256 runtime witnesses:

- **baseline witness** from disposable-target preflight;
- **target witness** after final target verification.

Both must be exact lowercase SHA-256 values.

Sprint 18 requires the final witness to differ from the baseline witness for a newly executed migration set.

These are runtime evidence and are kept separate from the canonical `targetManifestFingerprint` carried by the Sprint 16 generation artifact.

Sprint 18 does not claim that a runtime SQLite metadata hash is the same semantic object as the canonical physical-planning fingerprint.

## Immutable execution report

Successful execution returns `LaravelMigrationExecutionReport`.

The report includes:

- generation-artifact fingerprint;
- canonical target-manifest fingerprint;
- framework/framework version;
- generation correlation ID;
- materialization correlation ID;
- execution correlation ID;
- deterministic relative execution-workspace identity;
- target kind;
- baseline witness;
- target witness;
- exact executed migration identifiers;
- executed file count;
- final state `COMPLETE`;
- completed-run idempotency indicator.

It excludes:

- migration source;
- SQL;
- credentials;
- DSNs;
- database file paths;
- absolute staging paths;
- customer data;
- exception details.

## Stable error boundary

Sprint 18 publishes bounded execution error codes covering:

- artifact binding invalid;
- materialization validation failure;
- materialization report mismatch;
- execution parent invalid;
- symlink denial;
- unsupported target;
- target preflight failure;
- baseline witness invalid;
- lock unavailable;
- journal invalid;
- journal state invalid;
- journal I/O failure;
- migration file mismatch;
- migration object invalid boundary;
- migration execution failure;
- target verification failure;
- target witness mismatch;
- completed state mismatch.

The runtime messages remain bounded and do not serialize the underlying exception or sensitive target material.

## Root governance regression

`tests/migration.php` preserves all previous Migration Foundation and Sprint 15–17 regression coverage.

Sprint 18 adds framework-independent lifecycle coverage using a synthetic execution adapter for:

- deterministic execution workspace identity;
- exact artifact/materialization binding;
- mandatory Sprint 17 re-validation;
- exact four-file governed order;
- journal `PREPARED/RUNNING/COMPLETE/FAILED` semantics;
- per-step applied progression;
- immutable execution report;
- safe journal/report serialization;
- completed-run idempotency with no re-execution;
- completed target-witness mismatch denial;
- unsupported target denial;
- staged-byte tamper denial before execution;
- deliberate step-two failure and fail-stop behavior;
- FAILED-journal non-resumability;
- exclusive lock contention denial;
- no shell/process/network coupling;
- no application migration publication coupling.

The root `composer test` remains the canonical foundation regression path.

## Actual Laravel + SQLite execution proof

`apps/web/tests/run.php` extends the canonical M7.1 application regression with the first actual Sprint 18 migration execution proof.

The proof does not publish application DB configuration.

Instead, the test process:

1. requires the already-published SchemaPlanning foundation;
2. constructs one bounded synthetic additive Laravel migration artifact;
3. materializes it through Sprint 17 into a temporary staging parent;
4. creates one temporary SQLite file under that test parent;
5. configures a named `s18_sqlite` connection programmatically on the already-booted test application;
6. sets that connection as the test-process default only;
7. verifies a clean synthetic baseline;
8. invokes Sprint 18;
9. loads the exact revalidated staged PHP migration file;
10. requires that it returns an `Illuminate\Database\Migrations\Migration` object;
11. invokes only `up()`;
12. verifies the resulting synthetic table/columns through Laravel schema APIs;
13. produces the final target witness;
14. proves completed-run idempotency without re-execution;
15. deliberately tampers with the staged file and proves execution is denied before migration loading;
16. restores and revalidates the governed file;
17. disconnects/purges the temporary SQLite connection;
18. removes the SQLite file and all temporary Sprint 18 workspaces.

The test also asserts before and after execution that `apps/web/config/database.php` is absent.

## Synthetic application proof schema

The actual SQLite proof uses only a synthetic probe table:

`s18_execution_probe`

with synthetic columns:

- `id`;
- `name`.

This table exists only in the disposable CI database.

It is not a POS/customer schema, is not persisted in repository migration files, and carries no real user or customer data.

## No application runtime database enablement

Sprint 18 does not create `apps/web/config/database.php`.

It does not change `environment.example`, application readiness, controllers, providers, routes, or service configuration to require a database.

The SQLite connection exists only in `apps/web/tests/run.php` and only for the bounded execution proof.

The ordinary oneQay application skeleton therefore remains unchanged with respect to persistent runtime database enablement.

## No Artisan or subprocess execution

Sprint 18 does not call:

- `artisan migrate`;
- `migrate:fresh`;
- `migrate:rollback`;
- `PHP_BINARY` child processes;
- `exec()`;
- `shell_exec()`;
- `system()`;
- `passthru()`;
- `proc_open()`;
- `popen()`;
- SSH;
- cPanel APIs.

The M7.1 proof executes migration objects in-process through the already installed and locked Laravel framework.

## Network and remote-database boundary

Sprint 18 execution source contains no network transport or remote database target configuration.

The framework-independent executor receives only an adapter object and the fixed target-kind label.

The actual M7.1 adapter receives an already configured disposable local SQLite connection.

There is no MySQL/MariaDB hostname, TCP port, remote DSN, credential, or Production database identifier in the execution contract.

## Forward-only recovery boundary

Sprint 18 never invokes generated `down()` methods.

A failed execution is not rolled back in place.

The correct Sprint 18 recovery operation is disposal of the isolated SQLite target and its execution workspace.

Crash-safe resume, automated rollback, database journal reconciliation, and live-target recovery require separate future authority.

## Privacy boundary

Sprint 18 uses only synthetic schema fixtures and metadata witnesses.

No real tenant, participant, employee, payment, or customer data is required.

No UUPDP-protected personal data is introduced by this stage.

## Explicit non-scope

Sprint 18 does not authorize or implement:

- `apps/web/config/database.php`;
- durable application DB configuration;
- `apps/web/database/migrations/` publication;
- Preview database execution;
- cPanel/live database execution;
- MariaDB/MySQL execution;
- remote database execution;
- real tenant/customer data migration;
- automated rollback;
- partial/crash-safe resume;
- durable application persistence;
- final POS/business schema;
- deployment;
- Release/GitHub Release;
- updater activation;
- Production readiness.

Production readiness remains **NO-GO**.

## Successor boundary

After Sprint 18 is successfully published, the narrowest candidate successor is **Durable Application Persistence Foundation**.

That future gate should separately define:

- application database configuration and secret boundaries;
- Local/Test/CI first durable connection policy;
- stable persisted platform identities;
- tenant ownership and isolation at persistence boundaries;
- repository/data-access adapters;
- transaction semantics;
- schema rollout sequencing;
- data lifecycle and audit requirements.

Sprint 18 does not automatically grant that authority and does not grant Production database authority.

Attribution: **Lab | zefry**
