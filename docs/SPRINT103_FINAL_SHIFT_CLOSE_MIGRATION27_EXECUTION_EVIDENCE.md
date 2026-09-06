# Sprint103 Final Shift Close Migration27 Execution Evidence

Author by Lab | zefry

## Purpose

Sprint103 materialized the first trusted execution-evidence producer required by the Sprint102 Final Shift Close operational state machine.

The producer is deliberately limited to migration #27. Source publication does **not** dispatch the executor, execute migration #27, change canonical operational state, provision `pos.shift.close`, enable Final Shift Close, deploy, release, activate Technical Preview, activate Production, or activate the updater.

The executor remains at:

`.github/workflows/final-shift-close-migration27-execution.yml`

It remains manual `workflow_dispatch` only, current-canonical-`main` only, and continues to execute only the pinned canonical migration #27 source.

## Canonical migration source

Path:

`apps/web/database/migrations/0000_00_00_000027_create_pos_shift_close_evidence_foundation.php`

Git blob:

`a412560c2f340f4783a385aef729dbd074389a4c`

If the migration source changes, the executor fails closed until a separately reviewed successor updates the pin.

## Exact state-transition PR contract

A future migration execution transition PR must:

1. target `main`;
2. be based on the same current canonical `main` SHA used to dispatch the executor;
3. come from the same repository;
4. change exactly `ops/final-shift-close/STATE.json`;
5. change only `migration27.state` from `NOT_EXECUTED` to `EXECUTED`;
6. preserve permission provisioning as `NONE`;
7. preserve feature activation as `INACTIVE`;
8. preserve every other state field exactly.

The target exact head must have current success for:

- `final-shift-close-migration27-execution-authority`;
- `product-owner-merge-authority`;
- `Governance Required Checks`;
- `PHP Foundation Regression`;
- `M7.1 Application Regression`.

Execution evidence remains distinct from authorization and remains distinct from state merge. The executor never merges the target PR.

## Database execution and verification baseline

The fixed GitHub Environment remains:

`final-shift-close-migration27-execution`

The operational DB connection still requires:

- `ONEQAY_MIGRATION27_DB_HOST`;
- `ONEQAY_MIGRATION27_DB_PORT`;
- `ONEQAY_MIGRATION27_DB_DATABASE`;
- `ONEQAY_MIGRATION27_DB_USERNAME`;
- `ONEQAY_MIGRATION27_DB_PASSWORD`.

The workflow still requires MySQL-compatible execution, rejects retrospective/ambiguous migration evidence, checks predecessor migrations and tables, executes only:

```text
php artisan migrate --path=database/migrations/0000_00_00_000027_create_pos_shift_close_evidence_foundation.php --force --no-interaction
```

and performs the existing post-execution schema/index/CHECK/column verification before `final-shift-close-migration27-execution-evidence` can become `success`.

No rollback, reset, refresh, fresh migration, destructive table command, permission provisioning, feature toggle, deployment, release, or updater action is introduced.

## Sprint117 selected-target binding hardening

Sprint116 established that a GitHub Environment name plus database secrets are not sufficient proof that the database being mutated belongs to the exact durable runtime selected by the Sprint110–Sprint115 chain.

Sprint117 therefore hardens the existing Sprint103 executor without performing a live migration.

A future dispatch now additionally requires exact `binding_run_id` and `binding_run_attempt` inputs. These are evidence identities only. The caller cannot supply environment ID, runtime class, running source SHA, artifact digest, selection fingerprint, ingestion fingerprint, or database binding fingerprint.

Before any pending migration execution status or database mutation, the executor now requires canonical:

`ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`

to be in:

`SELECTED_NOT_AUTHORIZED`

with a non-null `selected_target` carrying the exact trusted selection/ingestion bindings materialized by Sprint111–Sprint115.

The executor rejects the current blocked state and therefore cannot execute migration #27 while no durable target is selected.

## Trusted selected-target database-binding evidence

Sprint117 reserves the future binding producer path:

`.github/workflows/final-shift-close-migration27-selected-target-db-binding.yml`

Evidence artifact:

`final-shift-close-migration27-selected-target-db-binding`

Evidence context:

`final-shift-close-migration27-selected-target-db-binding-evidence`

The producer is intentionally **not implemented in Sprint117**. Consequently, source merge alone cannot satisfy the new prerequisite and cannot make migration execution operationally eligible.

A future exact binding run must be completed-success `workflow_dispatch` from `main`, remain in canonical-main history, provide one non-expired exact artifact, and publish success status whose target URL identifies that exact run.

Its `binding.json` must bind exactly to canonical selected-target:

- environment ID;
- runtime class;
- exact running source commit;
- exact running artifact SHA-256;
- readiness attestation SHA-256;
- selection fingerprint SHA-256;
- trusted ingestion run ID/attempt;
- trusted ingestion fingerprint SHA-256.

It must also state migration #27 is still `NOT_EXECUTED`, contain no secrets, and carry `database_binding_sha256`.

## Pre-mutation database readback

The binding algorithm is fixed as:

`SHA256_CANONICAL_JSON_DATABASE_HOSTNAME_PORT_V1`

The migration executor reads directly from the DB connection represented by `ONEQAY_MIGRATION27_DB_*`:

- `DATABASE()`;
- `@@hostname`;
- `@@port`.

It canonicalizes these as `database_name`, `server_hostname`, and integer `server_port`, hashes the canonical JSON with SHA-256, and compares that value using `hash_equals()` against the trusted selected-target database-binding evidence.

A mismatch fails before `php artisan migrate` is reached.

This prevents unbound DB secrets, a GitHub Environment label, or a state JSON file from being treated as sufficient live-runtime identity evidence.

## Evidence lifecycle

The migration execution context remains:

`final-shift-close-migration27-execution-evidence`

`pending` is not published until both the exact target/state/authority preflight and the exact selected-target database-binding preflight have succeeded.

Final `success` is published only if database binding, migration execution, and post-execution verification all succeed. A migration execution or post-verification failure after qualified binding publishes failure and fails closed.

## Canonical boundaries after Sprint117 source hardening

`FINAL_SHIFT_CLOSE_MIGRATION27_EXECUTION_EVIDENCE_PRODUCER = SOURCE_HARDENED_SELECTED_TARGET_BINDING_REQUIRED`

`FINAL_SHIFT_CLOSE_MIGRATION27_EXECUTOR_TRIGGER = MANUAL_MAIN_ONLY`

`FINAL_SHIFT_CLOSE_MIGRATION27_EVIDENCE_CONTEXT = final-shift-close-migration27-execution-evidence`

`MIGRATION27_SELECTED_TARGET_BINDING_PRODUCER = NOT_IMPLEMENTED`

`MIGRATION27_SELECTED_TARGET_BINDING_EVIDENCE = NONE`

`SELECTED_DURABLE_TARGET = NONE`

`SPRINT103_LIVE_EXECUTION = NOT_PERFORMED`

`MIGRATION_27_LIVE_EXECUTION = NOT_PERFORMED`

`MIGRATION_27_LIVE_AUTHORITY = NOT_GRANTED`

`FINAL_SHIFT_CLOSE_PERMISSION_PROVISIONING = NONE`

`FINAL_SHIFT_CLOSE_FEATURE_ACTIVATION = INACTIVE`

`TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY = NOT_GRANTED`

`PRODUCTION_AUTHORITY = NOT_GRANTED`

`UPDATER_ACTIVATION_AUTHORITY = NOT_GRANTED`

Sprint117 changes no application runtime, migration source, operational `STATE.json`, durable target selection state, deployment, release, Technical Preview, Production, or updater state. The strengthened executor is not dispatched by Sprint117.
