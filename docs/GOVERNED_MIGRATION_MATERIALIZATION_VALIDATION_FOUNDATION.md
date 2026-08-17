# Governed Migration Materialization & Validation Foundation

## Status

Sprint 17 implements the bounded **Governed Migration Materialization & Validation Foundation** authorized by `docs/SPRINT_17_GOVERNED_MIGRATION_MATERIALIZATION_VALIDATION_ENTRY_GATE.md`.

Status after publication: **IMPLEMENTED / ISOLATED-STAGING-ONLY / NON-EXECUTING**.

Production readiness remains **NO-GO**.

Attribution: **Lab | zefry**

## Purpose

Sprint 16 established deterministic, immutable Laravel migration file artifacts entirely in memory.

Sprint 17 adds the next controlled representation boundary:

`LaravelMigrationGenerationArtifact`

+ exact application Composer manifest content

+ existing caller-supplied Local/Test/CI staging parent

+ materialization correlation ID

→ generation-artifact verification

→ framework-target verification

→ PHP syntax and Laravel source-shape verification

→ deterministic isolated staging workspace

→ exact file materialization

→ post-write SHA-256 validation

→ exact staged-file-set validation

→ immutable materialization report.

Sprint 17 does not install migration files into the Laravel application tree and does not execute any migration.

## Exact implementation envelope

Sprint 17 changes exactly:

1. `src/SchemaPlanning/Foundation.php`;
2. `src/SchemaPlanning/LaravelMigrationMaterialization.php`;
3. `tests/migration.php`;
4. `docs/GOVERNED_MIGRATION_MATERIALIZATION_VALIDATION_FOUNDATION.md`.

No application migration directory, dependency manifest, workflow, runtime application source, deployment file, updater file, release file, or Migration Foundation execution path is modified.

## Materializer contract

`GovernedLaravelMigrationMaterializer` exposes two bounded operations:

- `materialize()` creates or confirms an exact isolated staging workspace;
- `validate()` re-validates an already staged workspace without repairing it.

Both operations accept:

1. one exact Sprint 16 `LaravelMigrationGenerationArtifact`;
2. the exact application `composer.json` content as a string;
3. one existing caller-supplied staging parent directory;
4. one bounded materialization or validation correlation ID.

The materializer does not accept a destination migration path from the caller.

## Isolated staging boundary

Sprint 17 always derives its child workspace from the exact Sprint 16 generation artifact fingerprint.

The bounded relative shape is:

`.oneqay-migration-materialization/<first-24-hex-of-artifact-fingerprint>`

Migration files are then staged below:

`database/migrations/`

inside that isolated child workspace.

The output report contains only the relative workspace identity. It does not serialize an arbitrary absolute private filesystem path.

## Application-tree denial

Sprint 17 deliberately rejects a staging parent that appears to be an application or repository root.

A parent containing application-root markers such as:

- `artisan`;
- `composer.json`;

is rejected before any staged migration write.

The implementation therefore cannot be pointed directly at the current `apps/web` root through its normal API.

Sprint 17 also contains no hard-coded `apps/web/database/migrations/` destination.

## Deterministic generation-artifact fingerprint

The materializer derives a SHA-256 fingerprint from the exact canonical JSON representation of the supplied Sprint 16 generation artifact using the same deterministic JSON flags used throughout the schema-governance foundations.

The fingerprint controls the staging workspace identity.

No wall-clock time, random value, generated UUID, database state, network response, environment secret, tenant record, or customer data contributes to workspace naming.

The same exact generation artifact therefore resolves to the same relative isolated workspace under the same caller staging parent.

## Sprint 16 artifact integrity verification

Before filesystem mutation, Sprint 17 independently verifies every generated migration file artifact.

Checks include:

- the published `database/migrations/0000_00_00_...php` path grammar;
- `database/migrations/` containment;
- generation correlation consistency;
- unique migration identifiers;
- unique source-change identifiers;
- unique generated paths;
- lexical path ordering;
- exact SHA-256 of in-memory source;
- valid PHP syntax;
- bounded Laravel migration source shape.

Any mismatch fails closed before an expected migration file is written.

## Composer and framework binding

Sprint 17 does not inspect an arbitrary application path to discover framework metadata.

Instead, the caller supplies exact Composer manifest content.

The implementation decodes that content and currently requires:

- PHP requirement: `^8.2`;
- `laravel/framework`: exactly `12.64.0`.

This matches the Sprint 16 framework generation target.

Malformed Composer JSON, missing `require` data, or a different Laravel target fails closed with a framework-target error.

Sprint 17 never changes `apps/web/composer.json` or `apps/web/composer.lock`.

## PHP syntax validation

Generated source is parsed in-process with:

`token_get_all($source, TOKEN_PARSE)`

before write and after readback.

The same parser is also used when validating a previously materialized workspace.

This keeps syntax validation independent of:

- shell execution;
- Artisan;
- Laravel bootstrap;
- database connectivity;
- package installation;
- network access.

A parse failure is represented by the stable bounded `LARAVEL_MIGRATION_MATERIALIZATION_SYNTAX_INVALID` error.

## Laravel source-shape validation

Sprint 17 verifies that generated source retains the published Sprint 16 Laravel migration structure.

Required structural markers include:

- `Illuminate\Database\Migrations\Migration`;
- `Illuminate\Database\Schema\Blueprint`;
- `Illuminate\Support\Facades\Schema`;
- anonymous class extending `Migration`;
- `up(): void`;
- `down(): void`;
- the published forward-only `LogicException` boundary;
- at least one `Schema::create(...)` or `Schema::table(...)` representation.

The allowed generated fluent schema-method vocabulary is bounded to the methods required by Sprint 16:

- `string`;
- `bigInteger`;
- `decimal`;
- `boolean`;
- `char`;
- `date`;
- `dateTime`;
- `json`;
- `charset`;
- `collation`;
- `nullable`;
- `default`;
- `primary`;
- `unique`;
- `foreign`;
- `references`;
- `on`.

Unexpected fluent APIs fail closed.

## Forbidden generated execution surfaces

Defense-in-depth source validation rejects migration text containing execution or destructive surfaces outside the published Sprint 16 representation boundary, including markers for:

- `DB::...`;
- `Artisan::...`;
- PDO construction;
- raw SQL statements;
- destructive schema drops;
- migration command invocation;
- process execution;
- network socket/cURL execution.

This does not replace SHA-256 authority. It adds a second validation layer around the exact generated bytes.

## Staging-parent validation

The staging parent must:

- already exist;
- be a directory;
- not be a symbolic link;
- resolve through `realpath()`;
- not present application-root markers.

Sprint 17 does not create arbitrary caller parent directories.

Only its own bounded child hierarchy is created.

## Path containment and symbolic-link defense

Every created workspace path is checked against a resolved parent boundary.

Every migration destination is checked against the resolved isolated workspace.

Controlled workspace directories and staged files reject symbolic links.

A path that cannot be proven to remain inside its authorized boundary fails closed.

Generated relative paths are also independently constrained by the Sprint 16 grammar, so traversal sequences and absolute paths are not accepted artifact input.

## Filesystem write policy

Sprint 17 authorizes only bounded local writes inside the isolated staging workspace.

It may create:

- `.oneqay-migration-materialization/`;
- the deterministic artifact child directory;
- `database/`;
- `database/migrations/`;
- exactly the migration files declared by the generation artifact.

It does not create:

- application configuration;
- `.env` files;
- credentials;
- migration database journals;
- database lock files;
- deployment metadata;
- release packages.

A migration file is written with an exclusive local file lock and exact byte-count verification.

The file is then immediately read back, syntax-parsed, source-shape validated, and SHA-256 checked against the Sprint 16 artifact.

## Idempotency

If an expected target file already exists and its SHA-256 exactly matches the Sprint 16 artifact, the file is accepted as `already_identical` and is not replaced.

If an expected target file already exists with different bytes, materialization fails closed.

There is no silent overwrite or repair of altered migration bytes.

Repeated materialization of the same artifact into the same clean staging parent is therefore deterministic and idempotent.

## Exact staged-file-set validation

Sprint 17 verifies that the isolated `database/migrations/` directory contains only the exact migration files declared by the Sprint 16 artifact.

It rejects:

- unexpected regular files;
- unexpected subdirectories;
- symbolic links;
- missing expected files after materialization;
- persisted files whose bytes no longer match.

This prevents stale migration artifacts from being silently mixed with the current governed generation artifact.

## Tamper detection

`validate()` re-checks a materialized workspace without repairing it.

For each expected file it verifies:

- regular-file identity;
- exact SHA-256;
- PHP syntax;
- Laravel source shape.

It also re-checks the exact staged-file set.

A deliberate byte change after materialization therefore fails validation rather than being normalized or replaced.

The caller must explicitly create or clean a staging workspace before remediation; Sprint 17 does not silently restore tampered content.

## Materialization evidence

Successful operations return an immutable `LaravelMigrationMaterializationReport`.

The report includes bounded evidence:

- generation-artifact fingerprint;
- framework identity;
- exact framework version;
- generation correlation ID;
- materialization/validation correlation ID;
- relative deterministic workspace identity;
- validated file count;
- per-file relative path;
- expected source fingerprint;
- persisted source fingerprint;
- persisted byte count;
- idempotent/existing-exact indicator.

The report does not contain migration source text, arbitrary filesystem contents, credentials, tenant records, customer data, or absolute staging paths.

## Stable error boundary

Sprint 17 provides bounded error codes including:

- `LARAVEL_MIGRATION_MATERIALIZATION_ARTIFACT_INVALID`;
- `LARAVEL_MIGRATION_MATERIALIZATION_FRAMEWORK_TARGET_MISMATCH`;
- `LARAVEL_MIGRATION_MATERIALIZATION_STAGING_PARENT_INVALID`;
- `LARAVEL_MIGRATION_MATERIALIZATION_SYMLINK_DENIED`;
- `LARAVEL_MIGRATION_MATERIALIZATION_PATH_INVALID`;
- `LARAVEL_MIGRATION_MATERIALIZATION_SYNTAX_INVALID`;
- `LARAVEL_MIGRATION_MATERIALIZATION_SOURCE_SHAPE_INVALID`;
- `LARAVEL_MIGRATION_MATERIALIZATION_SOURCE_FINGERPRINT_MISMATCH`;
- `LARAVEL_MIGRATION_MATERIALIZATION_WORKSPACE_CONFLICT`;
- `LARAVEL_MIGRATION_MATERIALIZATION_EXISTING_CONTENT_MISMATCH`;
- `LARAVEL_MIGRATION_MATERIALIZATION_WRITE_FAILED`;
- `LARAVEL_MIGRATION_MATERIALIZATION_POST_WRITE_FINGERPRINT_MISMATCH`;
- `LARAVEL_MIGRATION_MATERIALIZATION_UNEXPECTED_FILE`;
- `LARAVEL_MIGRATION_MATERIALIZATION_MISSING_FILE`;
- `LARAVEL_MIGRATION_MATERIALIZATION_PERSISTED_VALIDATION_MISMATCH`.

Messages are bounded and do not include arbitrary source content, credentials, environment secrets, customer records, or database values.

## Database and execution boundary

Sprint 17 introduces no:

- PDO database connection;
- database connection string;
- database metadata query;
- SQL execution;
- `artisan migrate` execution;
- migration rollback execution;
- migration batch persistence;
- real database migration lock;
- schema mutation by the Sprint 17 runtime;
- data backfill;
- invocation of `MigrationExecutionService`.

The migration PHP files being staged naturally contain Sprint 16 `Schema::create(...)` or `Schema::table(...)` source representations. Sprint 17 never loads or executes those migration classes.

## Process and network boundary

Sprint 17 uses PHP-native filesystem and token-parser functionality only.

It does not invoke:

- `PHP_BINARY` subprocesses;
- `exec`;
- `shell_exec`;
- `system`;
- `passthru`;
- `proc_open`;
- `popen`;
- network sockets;
- cURL;
- HTTP clients;
- package installation.

## Regression coverage

`tests/migration.php` preserves the previous Migration Foundation, Sprint 15, and Sprint 16 assertions and extends them with Sprint 17 validation for:

- deterministic generation-artifact fingerprint;
- deterministic isolated workspace identity;
- exact Composer/Laravel binding;
- isolated staging materialization;
- immutable report/file evidence;
- exact persisted file count;
- post-write SHA-256 integrity;
- PHP syntax validation;
- source-shape validation;
- idempotent repeated materialization;
- validation of already materialized bytes;
- wrong/malformed framework target denial;
- application-root staging denial;
- unexpected-file denial;
- deliberate byte-tamper denial;
- missing-file detection and governed re-materialization;
- symlink denial where supported by the test runtime;
- syntax-invalid artifact denial;
- source-shape-invalid artifact denial;
- absence of process, database-execution, and application-tree coupling;
- cleanup of the isolated temporary test staging parent.

The root `composer test` remains the canonical regression path.

## Explicit non-scope

Sprint 17 does not authorize or implement:

- publication of migration files into `apps/web/database/migrations/`;
- application-tree migration installation;
- Laravel migration execution;
- database connectivity;
- database-state validation;
- schema mutation;
- rollback execution;
- data migration or backfill;
- durable migration journal;
- real migration execution lock;
- durable application persistence;
- final POS/business schema;
- cPanel/live-target change;
- deployment;
- Release/GitHub Release;
- updater activation;
- Production/customer data;
- Production-readiness promotion.

Production readiness remains **NO-GO**.

## Likely next gate

After successful Sprint 17 publication, the narrowest candidate successor is a separately governed **Governed Migration Execution Foundation** for Local/Test/CI.

That future gate would need to define exact execution-target provisioning, preflight, lock semantics, migration-state journal, validated staged-artifact installation/execution, outcome verification, failure handling, and recovery boundaries.

Sprint 17 itself creates no migration-execution authority.

Attribution: **Lab | zefry**
