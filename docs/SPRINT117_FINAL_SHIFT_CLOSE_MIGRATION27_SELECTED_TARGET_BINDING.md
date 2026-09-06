# Sprint117 Final Shift Close Migration27 Selected-Target Binding

Author by Lab | zefry

## Historical Sprint117 purpose

Sprint117 hardened the Final Shift Close migration #27 executor so future operational execution cannot rely only on a GitHub Environment name plus database secrets.

At the Sprint117 merge checkpoint, migration #27 could become eligible only after a durable runtime was persisted as `SELECTED_NOT_AUTHORIZED` and a separately trusted database-binding evidence producer proved that the migration database belonged to that exact selected target.

Sprint117 itself did not implement or dispatch that producer, did not select a target, did not execute migration #27, did not modify `STATE.json`, did not provision `pos.shift.close`, did not widen runtime allowlists, did not enable Final Shift Close, and did not deploy or activate any environment.

## Sprint117 selected-target prerequisite retained

The migration executor reads:

`ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`

A future dispatch remains rejected unless:

- `selection_state == SELECTED_NOT_AUTHORIZED`;
- `selected_target` is an object;
- environment ID and runtime class are explicit and stable;
- the runtime class is an eligible isolated non-synthetic non-production class;
- exact running source SHA and artifact SHA-256 are present;
- readiness attestation and selection fingerprints are present;
- trusted ingestion run ID, run attempt, and ingestion fingerprint are present;
- the selected running source remains in canonical-main history.

The canonical repository remains blocked with `selected_target = null` until the earlier Sprint113–Sprint115 operational evidence chain is actually executed and merged through its own gates.

## Sprint117 database-binding contract retained

The migration executor accepts only evidence identity inputs in addition to the state-transition target:

- `binding_run_id`;
- `binding_run_attempt`.

The caller still cannot provide environment ID, runtime class, running source SHA, artifact SHA-256, selection fingerprint, or database fingerprint.

The reserved workflow remains:

`.github/workflows/final-shift-close-migration27-selected-target-db-binding.yml`

Reserved artifact:

`final-shift-close-migration27-selected-target-db-binding`

Reserved status context:

`final-shift-close-migration27-selected-target-db-binding-evidence`

The executor requires exact run path/event/ref/result/run attempt, exact single non-expired bounded artifact, exact selected-target field matches, exact database-binding fingerprint, and an exact success status whose target URL points to the same run.

## Database identity algorithm retained

Canonical algorithm:

`SHA256_CANONICAL_JSON_DATABASE_HOSTNAME_PORT_V1`

Immediately before migration, the executor reads from its actual migration database connection:

```sql
SELECT DATABASE() AS database_name, @@hostname AS server_hostname, @@port AS server_port
```

Canonical payload:

```json
{
  "database_name": "<DATABASE()>",
  "server_hostname": "<@@hostname>",
  "server_port": 3306
}
```

The keys are sorted, encoded as unescaped JSON, and SHA-256 hashed. The executor compares that result with trusted binding evidence using `hash_equals()` before any migration command.

A GitHub Environment name, secret names, target-selection state, or caller-supplied values are never sufficient by themselves.

## Preserved migration semantics

Sprint117 and its successors preserve:

- canonical migration #27 source and Git blob pin;
- exact migration-execution authority;
- exact-head Product Owner merge authority;
- Governance, PHP Foundation, and M7.1 prerequisites;
- exact one-file `STATE.json` transition;
- `NOT_EXECUTED → EXECUTED` only;
- permission state `NONE` during migration transition;
- feature state `INACTIVE` during migration transition;
- refusal of ambiguous retrospective migration evidence;
- forward-only `php artisan migrate --path=... --force --no-interaction` semantics;
- post-execution table/index/CHECK/column/migration-record verification;
- no automatic merge of the target transition PR.

## Sprint118 successor compatibility addendum

Sprint118 is the bounded successor that materializes the reserved selected-target database-binding producer source.

This successor does **not** weaken any Sprint117 prerequisite. It closes only the producer-source gap.

Sprint118 producer source:

`.github/workflows/final-shift-close-migration27-selected-target-db-binding.yml`

The producer is manual `workflow_dispatch` from current canonical `main`, has no caller-supplied target inputs, and is protected by the fixed GitHub Environment:

`final-shift-close-migration27-selected-target-db-binding`

A future producer run must simultaneously prove:

1. canonical selection is `SELECTED_NOT_AUTHORIZED`;
2. migration #27 canonical operational state is still `NOT_EXECUTED`;
3. authenticated protected runtime control-channel evidence matches the exact selected environment/runtime/source/artifact/readiness/selection/ingestion provenance;
4. the control channel states that its database identity comes from the active application database connection and the attestation is read-only;
5. an independent PDO MySQL connection reads `DATABASE()/@@hostname/@@port`;
6. migration #27 table and migration record are both absent;
7. the independent database fingerprint exactly equals the runtime-reported fingerprint using `hash_equals()`.

Only after those checks can the producer publish a secret-free two-file artifact:

- `binding.json`;
- `execution.json`.

The producer publishes `pending` only after binding verification, uploads the artifact, then publishes `success`. The success status targets the exact producer run and is attached to the selected running source SHA consumed by the migration executor.

## Historical and successor source envelopes

Sprint117 historical sorted-newline source envelope SHA-256:

`0aab6969f2e6fe3e7f23aae2af99bc8fb9d70ac10e6d620bf20bc30a58ba4e25`

Sprint118 successor sorted-newline source envelope SHA-256:

`a046e41aef37a4e6e2c6d66054517ffd7ed81be85b70b1a9199e926af5388b57`

The Sprint117 qualification workflow is upgraded only as a successor compatibility gate so the historical executor guarantees remain locked while the reserved producer becomes real source.

## Current canonical no-go disposition after Sprint118 source publication

`MIGRATION27_SELECTED_TARGET_BINDING_CONTRACT = MATERIALIZED_SOURCE_ONLY`

`MIGRATION27_EXECUTOR_SOURCE = HARDENED_SELECTED_TARGET_BINDING_REQUIRED_NOT_DISPATCHED`

`CURRENT_SELECTION_STATE = BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`

`SELECTED_TARGET = NONE`

`SPRINT117_HISTORICAL_BINDING_PRODUCER_STATE = NOT_IMPLEMENTED_AT_SPRINT117_CHECKPOINT`

`SPRINT118_SUCCESSOR_BINDING_PRODUCER_STATE = SOURCE_MATERIALIZED_NOT_DISPATCHED`

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

Source publication does not dispatch the Sprint118 producer or the migration executor, does not create binding evidence, and does not grant migration, deployment, Technical Preview, Production, release, or updater authority.
