# Sprint118 Final Shift Close Migration27 Selected-Target DB Binding Producer

Author by Lab | zefry

## Purpose

Sprint118 materializes the trusted selected-target database-binding producer required by Sprint117 before any future migration #27 execution can become eligible.

The producer is source-only in this sprint. Sprint118 does **not** dispatch the producer, does not create real binding evidence, does not select a durable runtime target, does not execute migration #27, does not modify `STATE.json`, does not provision `pos.shift.close`, does not widen runtime allowlists, does not enable Final Shift Close, does not deploy, release, activate Technical Preview, activate Production, or activate the updater.

## Why a separate producer is required

Sprint117 already hardened the migration executor so its own database credentials must be cryptographically bound to the exact durable runtime selected by the Sprint110–Sprint115 chain.

The missing source was a producer capable of proving both sides of that relationship:

1. the selected serving runtime says which database identity its active application connection uses; and
2. an independent database connection established from protected migration-binding credentials resolves to the same database identity.

Neither a GitHub Environment name nor a database secret name is accepted as runtime identity evidence.

## Exact Sprint118 source envelope

Sprint118 changes exactly:

1. `.github/workflows/final-shift-close-migration27-selected-target-db-binding.yml`;
2. `.github/workflows/sprint103-final-shift-close-migration27-execution-evidence.yml`;
3. `.github/workflows/sprint117-final-shift-close-migration27-selected-target-binding.yml`;
4. `.github/workflows/sprint118-final-shift-close-migration27-selected-target-db-binding.yml`;
5. `docs/SPRINT117_FINAL_SHIFT_CLOSE_MIGRATION27_SELECTED_TARGET_BINDING.md`;
6. `docs/SPRINT118_FINAL_SHIFT_CLOSE_MIGRATION27_SELECTED_TARGET_DB_BINDING.md`;
7. `ops/final-shift-close/MIGRATION27_SELECTED_TARGET_BINDING_CONTRACT.json`.

Sorted-newline envelope SHA-256:

`1b8a8a9851a5cac94389c6939f4c4f08dd479c108296c3959c996884612ee13c`

No application source, provider, route, UI, bootstrap, config, migration source, operational `STATE.json`, durable target-selection state, or migration executor changes are part of Sprint118. The Sprint103 and Sprint117 workflow changes are successor-compatibility qualification only.

## Producer trigger and caller boundary

Producer workflow:

`.github/workflows/final-shift-close-migration27-selected-target-db-binding.yml`

The workflow is manual `workflow_dispatch` only and accepts **no dispatch inputs**.

The caller therefore cannot supply:

- environment ID;
- runtime class;
- running source SHA;
- running artifact SHA-256;
- readiness attestation SHA-256;
- selection fingerprint;
- ingestion run identity;
- ingestion fingerprint;
- database-binding fingerprint.

All target identity comes from canonical:

`ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`

A future run must also originate from the current canonical `main` SHA.

## Fixed protected environment

The producer uses the fixed GitHub Environment:

`final-shift-close-migration27-selected-target-db-binding`

The workflow requires a protection assertion value:

`REVIEW_GATED_V1`

Sprint118 source does not claim that repository environment reviewers, secrets, or variables are already configured. Environment configuration is a separate operational prerequisite and is not created by this PR.

Required future environment secrets:

- `ONEQAY_MIGRATION27_BINDING_ATTESTATION_URL`;
- `ONEQAY_MIGRATION27_BINDING_ATTESTATION_TOKEN`;
- `ONEQAY_MIGRATION27_BINDING_PROTECTION_ASSERTION`;
- `ONEQAY_MIGRATION27_BINDING_DB_HOST`;
- `ONEQAY_MIGRATION27_BINDING_DB_PORT`;
- `ONEQAY_MIGRATION27_BINDING_DB_DATABASE`;
- `ONEQAY_MIGRATION27_BINDING_DB_USERNAME`;
- `ONEQAY_MIGRATION27_BINDING_DB_PASSWORD`.

The producer only receives `contents: read` and `statuses: write`; it has no repository content-write or deployment permission.

## Canonical target prerequisite

Before any protected network or database evidence can be accepted, the workflow requires:

- `selection_state = SELECTED_NOT_AUTHORIZED`;
- `selected_target` is an object;
- explicit stable environment ID;
- eligible isolated non-synthetic non-production runtime class;
- exact running source SHA;
- exact running artifact SHA-256;
- readiness attestation SHA-256;
- selection fingerprint SHA-256;
- trusted ingestion run ID and run attempt;
- trusted ingestion fingerprint SHA-256;
- selected running source remains in canonical-main history.

It also requires canonical operational state to remain:

- migration #27 `NOT_EXECUTED`;
- permission provisioning `NONE`;
- feature activation `INACTIVE`.

The current repository does not satisfy the first prerequisite because selected target is still `null`. Therefore merely merging Sprint118 source cannot produce evidence.

## Authenticated runtime-side binding attestation

The protected runtime control channel is HTTPS with bearer authentication.

The response is bounded to 32 KiB and must be an exact JSON object. It must contain exactly:

- `schema_version`;
- `feature`;
- `binding_state`;
- `environment_id`;
- `runtime_class`;
- `exact_running_source_commit`;
- `exact_running_artifact_sha256`;
- `readiness_attestation_sha256`;
- `selection_fingerprint_sha256`;
- `trusted_ingestion`;
- `migration27_state`;
- `database_binding_algorithm`;
- `database_binding_sha256`;
- `database_identity_source`;
- `attestation_mode`;
- `secrets_embedded`.

Required semantic values include:

- `binding_state = VERIFIED_SELECTED_TARGET_DATABASE`;
- `database_identity_source = ACTIVE_APPLICATION_DATABASE_CONNECTION`;
- `attestation_mode = READ_ONLY`;
- `migration27_state = NOT_EXECUTED`;
- `secrets_embedded = false`.

All selected-target provenance fields must exactly match canonical target selection.

The runtime-side attestation is not accepted on its own.

## Independent database identity readback

The producer independently connects to the database using protected binding credentials through `pdo_mysql`.

It executes only read operations before evidence publication.

Identity query:

```sql
SELECT DATABASE() AS database_name, @@hostname AS server_hostname, @@port AS server_port
```

It also verifies:

- the canonical `migrations` table exists;
- `oneqay_pos_shift_close_evidence` does not exist;
- migration `0000_00_00_000027_create_pos_shift_close_evidence_foundation` is not recorded.

If migration #27 is already present, the producer refuses to create retrospective evidence.

## Database-binding fingerprint

Algorithm:

`SHA256_CANONICAL_JSON_DATABASE_HOSTNAME_PORT_V1`

The producer creates the canonical object:

```json
{
  "database_name": "<DATABASE()>",
  "server_hostname": "<@@hostname>",
  "server_port": 3306
}
```

Keys are sorted with `SORT_STRING`, JSON is encoded with unescaped slash/unicode semantics, then SHA-256 hashed.

The independently calculated fingerprint must exactly equal the authenticated runtime control-channel fingerprint using:

`hash_equals($runtimeBinding, $actualBinding)`

A mismatch fails closed before artifact or success evidence publication.

This dual-read design is the core trust property of Sprint118: the runtime-side control channel and the database credentials independently resolve to the same database identity.

## Secret-free evidence artifact

Only after both selected-target qualification and dual database identity verification succeed may the workflow materialize:

`final-shift-close-migration27-selected-target-db-binding`

The artifact contains exactly two JSON documents:

- `binding.json`;
- `execution.json`.

`binding.json` contains the exact selected-target provenance, `migration27_state = NOT_EXECUTED`, the database-binding SHA-256, and `secrets_embedded = false`.

`execution.json` binds that evidence to:

- workflow path;
- repository;
- `refs/heads/main`;
- exact producer source commit;
- exact run ID;
- exact run attempt;
- database-binding fingerprint;
- selection fingerprint;
- evidence context;
- success state;
- `secrets_embedded = false`.

The artifact intentionally does **not** publish database hostname, database name, database port, token, username, or password. Only the one-way database-binding fingerprint is published.

## Evidence status lifecycle

Status context:

`final-shift-close-migration27-selected-target-db-binding-evidence`

The status is attached to the selected runtime's exact running source SHA.

Ordering is locked:

1. canonical target and dual DB binding verified;
2. secret-free bundle materialized;
3. evidence status set to `pending` with target URL pointing to exact producer run;
4. artifact uploaded;
5. evidence status set to `success` with the same exact run URL.

If any earlier step fails, no success evidence exists. If artifact publication fails after pending, the migration executor still rejects the run because it requires an exact success status and exact artifact.

## Consumption by migration executor

Sprint117 migration executor already expects this exact workflow path, artifact name, status context, and two-file payload.

A future migration dispatch provides only:

- target state-transition PR number/head;
- binding producer run ID/attempt.

The executor independently retrieves the exact producer run and evidence, confirms all selected-target fields, and then independently re-reads its own migration database identity immediately before `php artisan migrate`.

Therefore the Sprint118 producer does not grant migration execution authority and does not make a state transition by itself.

## Historical compatibility

The Sprint103 and Sprint117 qualification workflows are updated as successor compatibility gates.

They preserve:

- canonical migration #27 blob/source;
- Sprint117 executor selected-target prerequisites;
- Sprint117 database-binding evidence requirements;
- pre-mutation database fingerprint verification;
- forward-only migration execution;
- migration authority and merge authority boundaries;
- absence of deployment/feature/updater actions.

Historical Sprint117 source envelope remains recorded separately:

`0aab6969f2e6fe3e7f23aae2af99bc8fb9d70ac10e6d620bf20bc30a58ba4e25`

## Current canonical boundaries

`SPRINT118_DB_BINDING_PRODUCER_SOURCE = MATERIALIZED_NOT_DISPATCHED`

`SPRINT118_DB_BINDING_PRODUCER_TRIGGER = MANUAL_MAIN_ONLY`

`SPRINT118_DB_BINDING_PRODUCER_CALLER_TARGET_INPUTS = NONE`

`SPRINT118_DB_BINDING_EVIDENCE_CONTEXT = final-shift-close-migration27-selected-target-db-binding-evidence`

`CURRENT_SELECTION_STATE = BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`

`SELECTED_TARGET = NONE`

`MIGRATION27_SELECTED_TARGET_BINDING_EVIDENCE = NONE`

`MIGRATION27_EXECUTION_AUTHORITY = NOT_GRANTED`

`MIGRATION_27_EXECUTION = NOT_PERFORMED`

`PERMISSION_PROVISIONING = NONE`

`FEATURE_ACTIVATION = INACTIVE`

`RUNTIME_ALLOWLIST_CHANGE = NOT_IMPLEMENTED`

`ENVIRONMENT_DEPLOYMENT = NOT_PERFORMED`

`TECHNICAL_PREVIEW_ACTIVATION = NOT_AUTHORIZED`

`PRODUCTION_ACTIVATION = NOT_AUTHORIZED`

`UPDATER_ACTIVATION = INACTIVE`

Sprint118 does not dispatch any operational workflow. Source readiness is not execution authority, and producer evidence is not migration execution evidence.
