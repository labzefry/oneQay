# Sprint117 Final Shift Close Migration27 Selected-Target Binding

Author by Lab | zefry

## Purpose

Sprint117 hardens the existing Final Shift Close migration #27 execution source so a future operational execution cannot rely only on a GitHub Environment name plus database secrets.

The new requirement is fail-closed: migration #27 may become operationally eligible only after a durable runtime has actually been persisted as `SELECTED_NOT_AUTHORIZED` and a separate trusted database-binding evidence producer proves that the DB credentials used by the migration executor belong to that exact selected target.

Sprint117 is source-only. It does not select a target, implement or dispatch the binding producer, dispatch migration execution, execute migration #27, modify `STATE.json`, provision `pos.shift.close`, widen runtime allowlists, enable Final Shift Close, deploy, release, activate Technical Preview, activate Production, or activate the updater.

## Why Sprint117 is required

Sprint116 proved the historical Sprint103 migration executor was not yet bound to the durable target selected by Sprint110–Sprint115.

The historical executor used the fixed GitHub Environment `final-shift-close-migration27-execution` and `ONEQAY_MIGRATION27_DB_*` secrets. Those controls are necessary, but neither proves that the resulting DB connection belongs to the exact selected runtime/build.

Sprint117 therefore preserves the Sprint103 migration semantics while adding a selected-target and DB-identity prerequisite before any pending execution status or migration command.

## Exact source changes

Sprint117 changes only:

1. `.github/workflows/final-shift-close-migration27-execution.yml`;
2. `.github/workflows/sprint103-final-shift-close-migration27-execution-evidence.yml`;
3. `.github/workflows/sprint117-final-shift-close-migration27-selected-target-binding.yml`;
4. `docs/SPRINT103_FINAL_SHIFT_CLOSE_MIGRATION27_EXECUTION_EVIDENCE.md`;
5. `docs/SPRINT117_FINAL_SHIFT_CLOSE_MIGRATION27_SELECTED_TARGET_BINDING.md`;
6. `ops/final-shift-close/MIGRATION27_SELECTED_TARGET_BINDING_CONTRACT.json`.

Sorted-newline envelope SHA-256:

`0aab6969f2e6fe3e7f23aae2af99bc8fb9d70ac10e6d620bf20bc30a58ba4e25`

No application/runtime/provider/route/UI/bootstrap/config/migration source or operational state file changes are allowed by the Sprint117 qualification gate.

## Canonical selected-target prerequisite

The migration executor now reads:

`ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`

A future dispatch is rejected unless:

- `selection_state == SELECTED_NOT_AUTHORIZED`;
- `selected_target` is an object;
- environment ID is stable and explicit;
- runtime class is an eligible non-synthetic non-production class;
- exact running source commit is a 40-character lowercase Git SHA;
- exact running artifact SHA-256 is present;
- readiness attestation SHA-256 is present;
- selection fingerprint SHA-256 is present;
- trusted ingestion run ID/attempt are present;
- trusted ingestion fingerprint SHA-256 is present;
- the selected running source remains in canonical-main history.

The current canonical state remains blocked with `selected_target = null`, so the hardened executor cannot pass this prerequisite today.

## Exact database-binding evidence prerequisite

A future migration dispatch must additionally provide only:

- `binding_run_id`;
- `binding_run_attempt`.

These values identify an exact trusted evidence run. They do not define the runtime target.

Reserved producer workflow:

`.github/workflows/final-shift-close-migration27-selected-target-db-binding.yml`

Reserved artifact:

`final-shift-close-migration27-selected-target-db-binding`

Reserved status context:

`final-shift-close-migration27-selected-target-db-binding-evidence`

Sprint117 does not implement this producer. That is deliberate: the existing repository has no canonical DB/runtime identity endpoint or durable installation identity anchor that would let Sprint117 manufacture a valid binding without additional source work.

The migration executor therefore remains non-dispatchable in practice until a separately bounded successor implements and qualifies that producer.

## Required future binding artifact

The exact producer artifact must contain only:

- `binding.json`;
- `execution.json`.

The run must be successful `workflow_dispatch` from `main`, its head must remain in canonical-main history, the exact artifact must be single/non-expired/bounded, and the success status must point to the same exact run.

`binding.json` must carry:

- `schema_version = 1`;
- `feature = final-shift-close`;
- `binding_state = VERIFIED_SELECTED_TARGET_DATABASE`;
- exact selected environment ID;
- exact selected runtime class;
- exact selected running source commit;
- exact selected running artifact SHA-256;
- exact selected readiness attestation SHA-256;
- exact selected selection fingerprint SHA-256;
- exact trusted ingestion run ID/attempt;
- exact trusted ingestion fingerprint SHA-256;
- `migration27_state = NOT_EXECUTED`;
- lowercase 64-character `database_binding_sha256`;
- `secrets_embedded = false`.

`execution.json` must bind the artifact to the exact workflow path, repository, main ref, source commit, run ID/attempt, selection fingerprint, database-binding fingerprint, evidence context, and success state.

## Database identity algorithm

The canonical algorithm is:

`SHA256_CANONICAL_JSON_DATABASE_HOSTNAME_PORT_V1`

Immediately before any migration command, the executor connects using `ONEQAY_MIGRATION27_DB_*` and reads:

```sql
SELECT DATABASE() AS database_name, @@hostname AS server_hostname, @@port AS server_port
```

The canonical payload is:

```json
{
  "database_name": "<DATABASE()>",
  "server_hostname": "<@@hostname>",
  "server_port": 3306
}
```

Keys are sorted and encoded as unescaped JSON, then SHA-256 hashed. The result must equal the trusted `database_binding_sha256` using constant-time `hash_equals()`.

A mismatch aborts before `php artisan migrate`.

This binds the actual database connection used by migration execution to a separately trusted selected-target binding proof. It prevents the following from being treated as sufficient identity evidence:

- GitHub Environment name alone;
- database secret names alone;
- caller-supplied runtime target values;
- `STATE.json` alone;
- `DURABLE_ACTIVATION_TARGET_SELECTION.json` alone.

## Preserved migration semantics

Sprint117 does not change the migration source or its Git blob pin.

The executor still:

- requires exact migration execution authority and exact-head merge authority;
- requires Governance, PHP Foundation, and M7.1 success;
- accepts exactly one `STATE.json` transition PR;
- permits only `NOT_EXECUTED → EXECUTED` for migration #27;
- preserves permission `NONE` and feature `INACTIVE`;
- rejects already-created close evidence table or already-recorded migration #27;
- requires predecessor tables/migrations;
- executes only the pinned migration #27 with `--force --no-interaction`;
- verifies the new table, indexes, CHECK constraint, columns, and exact migration record;
- does not merge the target PR.

## Evidence lifecycle

The migration execution evidence context remains:

`final-shift-close-migration27-execution-evidence`

The executor cannot publish `pending` until both:

1. exact target/state/authority qualification succeeds; and
2. exact selected-target DB binding evidence succeeds.

A successful migration command is still insufficient by itself. Post-execution schema verification must also succeed before the final status can be `success`.

## Current no-go disposition

`MIGRATION27_SELECTED_TARGET_BINDING_CONTRACT = MATERIALIZED_SOURCE_ONLY`

`MIGRATION27_EXECUTOR_SOURCE = HARDENED_SELECTED_TARGET_BINDING_REQUIRED_NOT_DISPATCHED`

`CURRENT_SELECTION_STATE = BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`

`SELECTED_TARGET = NONE`

`MIGRATION27_SELECTED_TARGET_BINDING_PRODUCER = NOT_IMPLEMENTED`

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

The next bounded successor is the trusted selected-target database-binding producer/endpoint contract. That successor must not create retrospective evidence and must not execute migration #27 merely because producer source exists.
