# Sprint 18 Entry Gate — Governed Migration Execution Foundation

## Identity

- Product: `oneQay`
- Developer and Product Engineering Entity: **Lab | zefry**
- Repository: `labzefry/oneQay`
- Decision date: 2026-08-17
- Exact entry-gate base: `5a41e4f19f024859a1ab6e60b2915dbffce897e6`
- Exact entry-gate base tree: `758f7069165616e312db4ed15271c0c1d6d5ab8d`
- Sprint 17 Governed Migration Materialization & Validation Foundation: **COMPLETE / IMPLEMENTED / PUBLISHED**
- Sprint 16 framework target: Laravel `12.64.0`
- Production readiness: **NO-GO**

GitHub is the Single Source of Truth. Fresh verification is required before every lifecycle mutation.

Attribution: **Lab | zefry**

## Product Owner continuation

The Product Owner directed the project to continue immediately to the next bounded engineering stage after Sprint 17.

This entry gate records that authority for **Sprint 18 — Governed Migration Execution Foundation**, including Local/Test/CI implementation and ordinary Ready/Merge lifecycle after all exact-head required checks and the repository-native Product Owner merge-authority gate succeed.

Independent review is not an additional requirement under the current Product Owner continuation model.

This authority does **not** grant Production, Preview/cPanel/live-target migration execution, customer-data mutation, application runtime database enablement, deployment, Release/GitHub Release, updater activation, or Production readiness.

## Why Sprint 18 is the next bounded capability

Sprint 17 can prove that exact Sprint 16 Laravel migration source survives deterministic isolated materialization without byte drift, stale files, path escape, or silent tampering.

The next controlled capability is to prove that an exact, revalidated staged artifact can execute against a **disposable Local/Test/CI database target** under a fail-closed lifecycle.

Sprint 18 is therefore the first stage that may invoke generated Laravel migration `up()` methods.

That execution authority is intentionally narrower than deployment or durable persistence.

## Repository discovery that constrains this gate

Fresh inspection of canonical Sprint 17 shows:

- `apps/web/composer.json` requires PHP `^8.2` and `laravel/framework` exactly `12.64.0`;
- `apps/web/bootstrap/app.php` provides the Laravel application bootstrap;
- `apps/web/tests/run.php` already creates an isolated CI application runtime and is the canonical application regression path;
- the current application skeleton has **no `apps/web/config/database.php`**;
- no canonical application migration directory is currently published as runtime authority;
- `src/Migration/Foundation.php` contains generic dry-run planning, synthetic lock/executor interfaces, and `MigrationExecutionService`, but its `MigrationPlan` is explicitly dry-run only.

Sprint 18 must respect those facts rather than silently turning the application skeleton into a database-enabled runtime.

## Controlled outcome

Provide a Local/Test/CI-only execution boundary:

`LaravelMigrationGenerationArtifact`

+ exact Sprint 17 `LaravelMigrationMaterializationReport`

+ exact application Composer manifest content

+ existing caller-supplied isolated staging parent

+ disposable execution-target adapter

+ bounded execution correlation ID

→ Sprint 17 re-validation immediately before execution

→ deterministic execution workspace identity

→ exclusive local execution lock

→ clean execution journal initialization

→ disposable-target preflight witness

→ ordered generated migration execution

→ per-step journal progression

→ final target verification witness

→ immutable execution report

→ disposable target cleanup by the caller/test harness.

The result is Local/Test/CI execution evidence only.

It is not Production migration authority, durable application persistence, deployment authority, Release authority, or Production readiness.

## Authorized future implementation paths

Sprint 18 source implementation is limited to exactly:

1. `src/SchemaPlanning/Foundation.php`
2. `src/SchemaPlanning/LaravelMigrationExecution.php` — new
3. `tests/migration.php`
4. `apps/web/tests/run.php`
5. `docs/GOVERNED_MIGRATION_EXECUTION_FOUNDATION.md` — new

No other repository path is authorized by this entry gate.

Specifically excluded from source mutation:

- `apps/web/config/database.php`;
- `apps/web/composer.json`;
- `apps/web/composer.lock`;
- `apps/web/database/**`;
- application routes/controllers/providers;
- `src/Migration/Foundation.php`;
- workflow files;
- deployment files;
- updater files;
- release files.

If implementation discovery requires another path, Sprint 18 must stop and publish a bounded supplement before source mutation outside this envelope.

## Separation from the existing generic Migration Foundation

`src/Migration/Foundation.php` remains unchanged in Sprint 18.

Its existing `MigrationPlan` is explicitly dry-run and its `MigrationExecutionService` currently coordinates synthetic execution only.

Sprint 18 must not weaken or reinterpret that contract.

The new Sprint 18 execution boundary belongs to the governed SchemaPlanning → Laravel artifact pipeline and must consume exact Sprint 16/Sprint 17 artifacts directly.

A later program stage may reconcile the generic Migration Foundation with durable execution semantics, but that is not required to prove the Sprint 18 framework-specific Local/Test/CI execution boundary.

## Disposable target policy

Sprint 18 may execute only against a target explicitly identified by an execution adapter as:

`DISPOSABLE_SQLITE_TEST`

for this stage.

The target must be:

- created for one Local/Test/CI execution scenario;
- isolated from application runtime databases;
- located under a caller-owned temporary test workspace or equivalent disposable test boundary;
- free of customer or Production data;
- disposable after verification;
- unavailable as an HTTP/runtime configuration surface.

Any other target kind must fail closed.

Sprint 18 must not accept:

- MariaDB/MySQL hostnames;
- TCP database endpoints;
- cPanel database credentials;
- Production DSNs;
- shared Preview databases;
- customer database identifiers;
- arbitrary connection strings.

## No application database enablement

Sprint 18 must not add `apps/web/config/database.php`.

It must not add database values to `apps/web/environment.example`.

It must not alter ordinary application readiness or runtime configuration to require a database.

The M7.1 application regression may configure a disposable SQLite connection **programmatically inside the test process only** after application bootstrap.

That test-only connection must not become canonical application configuration.

## Baseline provisioning boundary

A migration execution target must represent a known synthetic baseline before the governed migrations run.

For Sprint 18 regression, the M7.1 test harness may provision a bounded synthetic baseline schema directly in the disposable SQLite target using Laravel schema APIs.

The synthetic baseline exists only to exercise the approved additive migration sequence.

It must not be represented as Product/customer data or as the final POS schema.

The execution adapter must produce a bounded SHA-256 **baseline witness** after preflight.

The baseline witness is runtime evidence; it does not replace or claim equality with the canonical planning baseline fingerprint.

## Final target witness

After all governed migrations execute, the adapter must verify the expected synthetic target shape and produce a bounded SHA-256 **target witness**.

The final target witness must be derived from deterministic, non-sensitive schema metadata only.

It must not contain:

- row data;
- credentials;
- absolute private paths;
- connection secrets;
- source file contents;
- customer identifiers.

The execution report must preserve the canonical Sprint 16 target manifest fingerprint separately from the runtime target witness so the two concepts cannot be confused.

## Sprint 17 re-validation requirement

Sprint 18 must not trust an old materialization report by itself.

Immediately before execution it must invoke the Sprint 17 validation boundary against:

- the exact generation artifact;
- exact application Composer manifest content;
- the same isolated staging parent.

Execution must fail closed if Sprint 17 validation fails for any reason, including:

- staged byte tamper;
- unexpected file;
- missing file;
- framework-target mismatch;
- source-shape mismatch;
- path or symlink violation.

No migration object may be loaded before this re-validation succeeds.

## Materialization-report binding

The caller-supplied Sprint 17 materialization report must match the exact re-validation result for:

- generation-artifact fingerprint;
- framework identity;
- framework version;
- generation correlation ID;
- deterministic workspace identity;
- file count;
- per-file relative path;
- expected/persisted source fingerprint.

A stale or mismatched report must fail closed.

## Deterministic execution workspace

Sprint 18 must create execution metadata only below an isolated deterministic child of the same caller staging parent.

Directional shape:

`.oneqay-migration-execution/<artifact-prefix>/`

The execution workspace identity must derive only from the exact generation-artifact fingerprint.

No wall-clock time, random UUID, environment secret, database state, network response, or customer data may affect that identity.

The execution workspace must remain outside the Laravel application tree.

## Execution lock

Sprint 18 must acquire one exclusive local file lock before journal initialization or migration execution.

The lock must:

- live only inside the isolated execution workspace;
- be non-blocking/fail-closed for a concurrent owner;
- use a bounded owner derived from the execution correlation ID;
- be released in a `finally`-equivalent path;
- never represent a real Production/database advisory lock.

Lock contention must return a stable bounded error and no migration may execute.

## Execution journal

Sprint 18 must maintain a bounded local JSON execution journal inside the isolated execution workspace.

The journal may contain only governance metadata such as:

- generation-artifact fingerprint;
- framework/framework version;
- execution correlation ID;
- execution target kind;
- baseline witness;
- ordered migration identifiers;
- ordered applied migration identifiers;
- state;
- bounded error code when failed;
- final target witness when complete.

The journal must never contain:

- migration source text;
- SQL text;
- credentials;
- DSNs;
- application secrets;
- row/customer data;
- absolute private filesystem paths.

## Journal state machine

The minimum Sprint 18 state model is:

`PREPARED`

→ `RUNNING`

→ zero or more applied identifiers

→ `COMPLETE`

or

→ `FAILED`.

A partially applied or failed journal must not be silently resumed in Sprint 18.

If a prior journal is `RUNNING` or `FAILED`, execution must fail closed and require a fresh disposable target/workspace remediation path.

This is necessary because the generated migrations are forward-only and crash-safe resume semantics have not yet been authorized.

## Completed-run idempotency

A prior `COMPLETE` journal may be treated as idempotent success only if:

- exact artifact identity still matches;
- exact ordered migration identifiers still match;
- Sprint 17 re-validation still passes;
- the disposable target adapter independently verifies the final target and returns the same stored target witness.

Otherwise the completed journal must fail closed as stale or inconsistent.

No migration may execute a second time merely because the caller repeats the request.

## Migration loading boundary

Sprint 18 may load only exact migration files declared by the Sprint 16 artifact and revalidated by Sprint 17.

The runtime adapter must load each staged PHP migration file by its deterministic relative path and require that it returns an `Illuminate\Database\Migrations\Migration` object.

The adapter may invoke only:

`$migration->up()`

for Sprint 18.

It must not invoke `down()` because Sprint 16 migrations are intentionally forward-only.

It must not discover or execute arbitrary files from a directory scan.

## Ordered execution

Migration execution order must be exactly the Sprint 16 artifact file order and therefore exactly the governed migration order.

Before each step, the executor must verify:

- migration identifier identity;
- source-change identity;
- exact source fingerprint;
- staged relative path identity;
- previous journal state.

After each successful step, the identifier must be recorded in the journal before proceeding to the next step.

A failure stops execution immediately.

## Failure semantics

If any migration fails:

- no later migration executes;
- the journal transitions to `FAILED` with one stable bounded error code;
- the execution lock is released;
- the exception detail is not serialized into the journal/report;
- Sprint 18 does not call `down()`;
- Sprint 18 does not attempt automatic schema repair;
- the disposable target is considered contaminated and must be discarded by the test/caller.

This is fail-closed forward-only behavior.

## Execution report

A successful execution must return an immutable report containing bounded evidence including:

- generation-artifact fingerprint;
- canonical target manifest fingerprint;
- framework/framework version;
- generation correlation ID;
- materialization correlation ID or validated materialization identity;
- execution correlation ID;
- deterministic relative execution workspace identity;
- target kind `DISPOSABLE_SQLITE_TEST`;
- baseline witness;
- target witness;
- ordered executed migration identifiers;
- exact executed file count;
- final state `COMPLETE`;
- whether the result was an already-complete idempotent verification.

The report must not serialize migration source, SQL, credentials, database path, absolute staging path, row data, or exception details.

## Stable error boundary

Sprint 18 implementation should expose stable bounded codes for conditions including:

- invalid execution artifact binding;
- materialization validation failure;
- materialization report mismatch;
- invalid execution parent/workspace;
- symlink/path denial;
- unsupported target kind;
- target preflight failure;
- baseline witness invalid;
- lock unavailable;
- journal invalid;
- journal partial/failed state;
- journal write/readback failure;
- migration file mismatch;
- migration object invalid;
- migration execution failure;
- target verification failure;
- target witness mismatch;
- completed-run state mismatch.

Messages must remain bounded and must not expose secrets, database paths, raw migration source, SQL, or customer data.

## Actual Laravel/SQLite regression proof

`apps/web/tests/run.php` must extend the existing M7.1 application regression without weakening any existing assertions.

The Sprint 18 regression must use the already locked Laravel framework dependency and must:

1. create a unique temporary staging parent;
2. construct the same bounded synthetic planning/generation fixture used by the root migration governance tests, or an equivalent exact additive fixture;
3. materialize it through Sprint 17;
4. create a disposable SQLite database file below the temporary test workspace;
5. configure the Laravel test process programmatically to use only that SQLite file;
6. provision the synthetic baseline through Laravel schema APIs;
7. produce a baseline witness from bounded schema metadata;
8. execute the exact revalidated staged migrations through Sprint 18;
9. verify the synthetic target shape using Laravel schema metadata APIs;
10. produce a final target witness;
11. assert all expected migration identifiers executed in order;
12. assert completed-run idempotency does not re-execute migrations;
13. assert a deliberate staged-file tamper is denied before execution;
14. assert a deliberately failing runtime adapter stops after the failing step in root regression;
15. disconnect the disposable database and remove all temporary files.

No external database service is required.

## Root regression expectations

`tests/migration.php` must preserve all existing Migration Foundation, Sprint 15, Sprint 16, and Sprint 17 assertions and add framework-independent Sprint 18 tests for at least:

- deterministic execution workspace identity;
- exact materialization-report binding;
- mandatory Sprint 17 re-validation;
- unsupported target denial;
- exclusive local execution lock behavior;
- bounded journal lifecycle;
- ordered execution through a synthetic runtime adapter;
- per-step applied journal progression;
- fail-stop semantics;
- `FAILED` journal non-resumability;
- completed-run idempotency verification;
- target-witness mismatch denial;
- report immutability and safe serialization;
- no credential/DSN/customer-data leakage;
- no application-tree or cPanel/live-target coupling.

The root `composer test` remains the canonical foundation regression path.

## Security boundary

Sprint 18 must remain deny-by-default and must not introduce:

- `.env` parsing for database credentials;
- Production database configuration;
- remote database connections;
- arbitrary DSN acceptance;
- arbitrary migration path discovery;
- shell command construction;
- `artisan migrate` subprocess invocation;
- package installation;
- network access;
- HTTP execution endpoints;
- background execution workers;
- deployment hooks;
- updater activation.

## Explicitly forbidden execution mechanisms

Sprint 18 must not use:

- `exec()`;
- `shell_exec()`;
- `system()`;
- `passthru()`;
- `proc_open()`;
- `popen()`;
- child `artisan migrate` processes;
- remote SSH;
- cPanel API calls;
- network database hosts.

Actual execution must occur in-process through the already loaded Laravel framework in the M7.1 test runtime.

## Database metadata boundary

Metadata inspection is authorized only against the disposable SQLite test target and only for baseline/final verification.

Preferred verification uses Laravel schema-builder metadata APIs.

Sprint 18 does not authorize introspection of Preview, shared, cPanel, MariaDB/MySQL, or Production databases.

## No rollback claim

Sprint 18 does not introduce rollback execution.

The generated `down()` method remains a forward-only denial boundary.

Failure recovery in Sprint 18 is disposal of the isolated test target, not schema rollback.

A future stage must separately govern rollback/recovery or resumable execution semantics if required.

## Privacy and data boundary

Sprint 18 test data/schema fixtures must remain synthetic.

No real tenant, user, participant, payment, customer, or employee data may be introduced.

No UUPDP-protected personal data is required for this stage.

## Exact changed-file envelope

The future Sprint 18 implementation PR must contain exactly these paths and no others:

1. `src/SchemaPlanning/Foundation.php`
2. `src/SchemaPlanning/LaravelMigrationExecution.php`
3. `tests/migration.php`
4. `apps/web/tests/run.php`
5. `docs/GOVERNED_MIGRATION_EXECUTION_FOUNDATION.md`

## Explicit non-scope

Sprint 18 does not authorize:

- `apps/web/config/database.php` publication;
- ordinary application runtime DB enablement;
- persistent application database connection;
- direct publication into `apps/web/database/migrations/`;
- Preview database execution;
- cPanel/live database execution;
- MariaDB/MySQL execution;
- customer-data migration;
- production schema mutation;
- automated rollback;
- crash-safe resume;
- durable application persistence;
- final POS/business schema;
- deployment;
- Release/GitHub Release;
- updater activation;
- Production readiness.

Production readiness remains **NO-GO**.

## Likely successor

After successful Sprint 18 publication, the narrowest candidate successor is **Durable Application Persistence Foundation**.

That successor would separately govern application database configuration, stable persisted identities, tenant-owned schema contracts, repository adapters, transaction boundaries, and rollout sequencing.

It must not inherit Production authority automatically from Sprint 18.

Attribution: **Lab | zefry**
