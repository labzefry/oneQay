# Sprint 17 Entry Gate — Governed Migration Materialization & Validation Foundation

## Identity

- Product: `oneQay`
- Developer and Product Engineering Entity: **Lab | zefry**
- Repository: `labzefry/oneQay`
- Decision date: 2026-08-17
- Exact entry-gate base: `ccfb5d0a79c55c3cfbc1c59f7826d9f00344c437`
- Exact entry-gate base tree: `dfabbea47d75f8d9ca072a8bf847b908638e5c2a`
- Sprint 16 Laravel Migration Generation Foundation: **COMPLETE / IMPLEMENTED / PUBLISHED**
- Sprint 16 framework target: Laravel `12.64.0`
- Production readiness: **NO-GO**

GitHub is the Single Source of Truth. Fresh verification is required before every lifecycle mutation.

Attribution: **Lab | zefry**

## Product Owner continuation

The Product Owner directed the project to continue immediately to the next bounded engineering stage after Sprint 16.

This entry gate records that authority for **Sprint 17 — Governed Migration Materialization & Validation Foundation**, including Local/Test/CI implementation and the ordinary Ready/Merge lifecycle after all exact-head required checks and the repository-native Product Owner merge-authority gate succeed.

Independent review is not an additional requirement under the current Product Owner continuation model.

This authority does **not** grant database connection, database metadata inspection, migration execution, schema mutation, data backfill, durable persistence, deployment, cPanel/live-target action, Release/GitHub Release, updater activation, Production/customer data, or Production readiness.

## Why Sprint 17 is the next bounded capability

Sprint 16 can produce deterministic immutable Laravel migration file artifacts entirely in memory.

Each file already carries:

- one governed migration identifier;
- one stable source-change identifier;
- deterministic relative path;
- exact generated source text;
- SHA-256 source fingerprint;
- generation correlation ID.

The next safe boundary is not database execution.

The next safe boundary is controlled filesystem staging plus exact validation so that the project can prove that generated migration bytes survive materialization without drift, tampering, path escape, unexpected files, framework-target mismatch, or syntax corruption.

## Controlled outcome

Provide a Local/Test/CI-only boundary:

`LaravelMigrationGenerationArtifact`

+ pinned application Composer manifest content

+ existing caller-supplied staging parent directory

+ bounded materialization correlation ID

→ generation-artifact integrity validation

→ pinned Laravel target validation

→ PHP syntax validation in memory

→ isolated deterministic staging workspace creation

→ exact migration file materialization

→ post-write fingerprint verification

→ exact expected-file-set verification

→ immutable materialization validation report.

The result is filesystem staging evidence only.

It is not application migration installation, migration execution authority, database state, deployment authority, Release authority, or Production authority.

## Authorized implementation paths

Sprint 17 source implementation is limited to exactly:

1. `src/SchemaPlanning/Foundation.php`
2. `src/SchemaPlanning/LaravelMigrationMaterialization.php` — new
3. `tests/migration.php`
4. `docs/GOVERNED_MIGRATION_MATERIALIZATION_VALIDATION_FOUNDATION.md` — new

No other repository path is authorized by this entry gate.

Specifically excluded from source mutation:

- `apps/web/database/**`;
- `apps/web/composer.json`;
- `apps/web/composer.lock`;
- application routes/controllers/providers;
- `src/Migration/Foundation.php`;
- workflow files;
- deployment files;
- updater files;
- release files.

## Materialization target boundary

Sprint 17 must never materialize directly into the Laravel application tree.

The materializer receives one existing caller-supplied parent directory and creates only an isolated child staging hierarchy under that parent.

The staging hierarchy must be deterministic from the exact generation artifact fingerprint and must remain separate from any application checkout path supplied by the caller.

Directional shape:

`<caller staging parent>/.oneqay-migration-materialization/<artifact-prefix>/database/migrations/...`

The returned report must expose only bounded relative workspace identity and artifact metadata. It must not require logging or serializing an absolute private filesystem path.

## Parent-directory requirements

Before any write, the materializer must fail closed unless:

- the supplied parent exists;
- the supplied parent is a directory;
- the supplied parent is not a symbolic link;
- its canonical path can be resolved;
- the materializer can construct all output strictly below that canonical parent;
- no generated relative path contains traversal or absolute-path semantics.

The materializer must not create or mutate files outside the isolated staging hierarchy.

## Deterministic workspace identity

The exact Sprint 16 generation artifact must receive a deterministic SHA-256 fingerprint derived from its canonical JSON representation.

The staging child identity must be derived only from that artifact fingerprint.

No current time, wall-clock time, random UUID, environment secret, database state, network state, or customer data may participate in the final workspace identity.

Repeated materialization of the same exact artifact to the same staging parent must target the same isolated workspace and be idempotent when all existing bytes remain exact.

## Generation-artifact integrity checks

Before filesystem mutation, Sprint 17 must independently verify every `LaravelMigrationFileArtifact`:

- relative path matches the published Sprint 16 path grammar;
- path remains below `database/migrations/`;
- source begins with valid PHP opening syntax;
- SHA-256 of the in-memory source equals `sourceFingerprint`;
- generation correlation ID is consistent with the parent generation artifact;
- migration identifiers are unique;
- source-change identifiers are unique;
- file paths are unique and lexically ordered;
- framework identity remains `LARAVEL`;
- framework version remains exactly `12.64.0` for this stage.

Any mismatch fails closed before a migration file is written.

## Composer framework-target binding

Sprint 17 must accept the exact application Composer manifest **content** as validation input rather than reading arbitrary application paths itself.

It must decode the supplied JSON and fail closed unless:

- `require.php` is present and compatible with the currently published PHP runtime boundary;
- `require["laravel/framework"]` exists;
- the exact Laravel requirement equals the Sprint 16 framework version target `12.64.0`;
- malformed or ambiguous Composer input is rejected.

This binds staging validation to the exact framework target while keeping the filesystem writer isolated from the application tree.

Sprint 17 does not change the Composer manifest or dependency lock.

## PHP syntax validation

Every generated source must be syntax-validated before write and again after readback.

Sprint 17 should use PHP-native in-process parsing where possible, for example `token_get_all(..., TOKEN_PARSE)`, so syntax validation does not require shell execution, Artisan, a database, network access, or Laravel bootstrap.

A syntax failure must prevent or invalidate materialization.

## Laravel source-shape validation

Sprint 17 must verify the generated source still represents the published Laravel migration surface, including bounded requirements such as:

- `Illuminate\Database\Migrations\Migration`;
- `Illuminate\Database\Schema\Blueprint`;
- `Illuminate\Support\Facades\Schema`;
- anonymous class extending `Migration`;
- `up(): void`;
- `down(): void`;
- only the bounded generated schema APIs established by Sprint 16.

It must reject source containing disallowed execution surfaces such as raw SQL, database facade statements, Artisan migration commands, shell/process invocation, network calls, or destructive rollback primitives.

This validation is defense in depth. Exact source fingerprints remain the primary byte-integrity authority.

## Filesystem write policy

Writes are authorized only inside the isolated staging workspace.

Required behavior:

- create only the bounded staging directory hierarchy;
- materialize exactly the artifact-declared migration files;
- do not create application config or environment files;
- do not create database credentials;
- do not create migration journals;
- do not create lock records representing database locks;
- use bounded local file APIs only;
- verify the byte count of each write;
- re-read each file after write;
- verify exact SHA-256 after write;
- reject symbolic links at controlled output path components;
- reject an existing target file whose bytes differ from the expected artifact;
- permit an existing exact target file as idempotent success.

## Unexpected-file policy

The staged `database/migrations/` directory must contain exactly the expected file set for the generation artifact.

Sprint 17 must fail closed if it detects:

- an unexpected regular file;
- an unexpected subdirectory;
- a symbolic link;
- an expected path with different bytes;
- a missing expected file after materialization;
- a duplicate or path-alias condition.

This gives Sprint 17 stale-file and tamper detection rather than merely proving that expected files exist.

## Tamper-validation operation

The materializer must provide a validation operation that can re-check an already materialized isolated workspace against the exact Sprint 16 generation artifact.

The operation must not repair silently.

If a file changes after materialization, validation must fail with a stable bounded error code.

Repair/replacement policy remains an explicit caller action through a new clean staging workspace or separately authorized remediation path.

## Materialization report

Successful materialization and successful re-validation must produce an immutable report with bounded evidence such as:

- generation-artifact fingerprint;
- framework and exact framework version;
- generation correlation ID;
- materialization correlation ID;
- deterministic relative workspace identity;
- expected file count;
- validated file results;
- per-file relative path;
- expected source fingerprint;
- persisted source fingerprint;
- persisted byte count;
- whether the operation observed an already-identical file or newly wrote it, if that distinction is exposed.

The report must not serialize arbitrary file contents, secrets, environment values, database records, or absolute private paths.

## Stable error boundary

Sprint 17 implementation should expose stable bounded codes for conditions including:

- invalid generation artifact;
- framework target mismatch;
- invalid staging parent;
- symlink denial;
- path escape or invalid relative path;
- syntax invalid;
- source-shape invalid;
- source fingerprint mismatch;
- staging workspace conflict;
- existing target content mismatch;
- filesystem write failure;
- post-write fingerprint mismatch;
- unexpected staged file;
- missing staged file;
- persisted validation mismatch.

Error messages must remain bounded and must not include credentials, environment secrets, customer records, or arbitrary file contents.

## Idempotency requirement

Given the same:

- Sprint 16 generation artifact;
- Composer target manifest content;
- staging parent;

repeated materialization must either:

1. confirm the existing isolated workspace is byte-identical and succeed; or
2. fail closed if any staged content differs.

It must not silently overwrite tampered content.

## Local/Test/CI boundary

Sprint 17 materialization is intended only for isolated Local/Test/CI staging workspaces.

It does not grant authority to install files into:

- a developer application checkout migration directory;
- shared hosting application files;
- cPanel deployment trees;
- Release packages;
- Production servers.

A later gate must explicitly authorize any transition from validated staging artifacts into application-tree installation or migration execution.

## Database execution boundary

Sprint 17 introduces no:

- PDO connection;
- database connection string;
- database metadata query;
- `artisan migrate`;
- `migrate:fresh`;
- `migrate:rollback`;
- `DB::statement`;
- `DB::unprepared`;
- SQL execution;
- migration batch journal;
- real database lock;
- schema creation or alteration by the Sprint 17 runtime;
- data backfill;
- migration execution service invocation.

Generated source text may continue to represent Laravel `Schema::create` or `Schema::table` statements because that is the Sprint 16 artifact being staged; Sprint 17 itself does not execute those statements.

## Security boundary

Sprint 17 must remain deny-by-default and must not introduce:

- credential handling;
- `.env` parsing;
- secrets persistence;
- network access;
- remote filesystem access;
- HTTP endpoints;
- user-controlled arbitrary destination relative paths;
- shell command construction;
- package installation;
- deployment hooks;
- updater activation.

## Regression expectations

`tests/migration.php` must preserve all existing Migration Foundation, Sprint 15, and Sprint 16 regression assertions and add Sprint 17 coverage for at least:

- deterministic generation-artifact fingerprint;
- deterministic isolated workspace identity;
- Composer/Laravel version binding;
- successful materialization into an isolated temporary staging parent;
- exact expected file count;
- exact post-write SHA-256;
- PHP syntax parsing before and after materialization;
- source-shape validation;
- idempotent second materialization;
- tampered existing target denial;
- post-materialization validation failure after deliberate byte tamper;
- unexpected-file denial;
- symlink/path escape denial where supported deterministically;
- malformed or wrong Laravel Composer target denial;
- no database/process/network/execution coupling;
- test cleanup of the temporary staging workspace.

The full root `composer test` remains the canonical regression path.

## Exact changed-file envelope

The future Sprint 17 implementation PR must contain exactly these paths and no others:

1. `src/SchemaPlanning/Foundation.php`
2. `src/SchemaPlanning/LaravelMigrationMaterialization.php`
3. `tests/migration.php`
4. `docs/GOVERNED_MIGRATION_MATERIALIZATION_VALIDATION_FOUNDATION.md`

If a design discovery requires another path, Sprint 17 must stop and publish a bounded supplement before source mutation outside this envelope.

## Explicit non-scope

Sprint 17 does not authorize:

- direct `apps/web/database/migrations/` source publication;
- framework migration execution;
- database access;
- database-state validation;
- schema mutation;
- rollback execution;
- data migration/backfill;
- persistent migration journal;
- real migration lock;
- durable application persistence;
- POS/business schema publication;
- deployment;
- cPanel/live-target changes;
- Release/GitHub Release;
- updater activation;
- Production/customer data;
- Production readiness.

Production readiness remains **NO-GO**.

## Likely successor

After successful Sprint 17 publication, the next candidate must still be separately gated.

The likely successor is a **Governed Migration Execution Foundation** for Local/Test/CI only, with explicit preflight, execution lock, migration-state journal, exact artifact validation, execution result verification, and fail-closed recovery semantics.

No migration execution authority is created by this document.

Attribution: **Lab | zefry**
