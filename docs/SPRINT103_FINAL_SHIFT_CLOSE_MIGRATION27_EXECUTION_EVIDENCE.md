# Sprint103 Final Shift Close Migration27 Execution Evidence

Author by Lab | zefry

## Purpose

Sprint103 materializes the first trusted execution-evidence producer required by the Sprint102 Final Shift Close operational state machine.

The producer is deliberately limited to migration #27. Sprint103 does **not** dispatch the executor, execute migration #27, change the canonical operational state, provision `pos.shift.close`, enable Final Shift Close, deploy, release, activate Technical Preview, activate Production, or activate the updater.

## Trust boundary

The executor is stored at:

`.github/workflows/final-shift-close-migration27-execution.yml`

It is manual `workflow_dispatch` only. It is not triggered by `push` or `pull_request`.

A future execution must be dispatched from current canonical `main`. The workflow verifies that its own `github.sha` still equals the current `main` branch SHA before it evaluates a target transition. It checks out canonical dispatch source only and never checks out the target PR head for execution.

The canonical migration source is pinned to:

`apps/web/database/migrations/0000_00_00_000027_create_pos_shift_close_evidence_foundation.php`

Canonical Git blob:

`a412560c2f340f4783a385aef729dbd074389a4c`

If that migration source changes, this executor fails closed until a separately reviewed successor updates the pin.

## Exact future target PR contract

A future migration execution transition PR must:

1. target `main`;
2. be based on the same current canonical `main` SHA used to dispatch the executor;
3. come from the same repository;
4. change exactly one path: `ops/final-shift-close/STATE.json`;
5. change only `migration27.state` from `NOT_EXECUTED` to `EXECUTED`;
6. preserve permission provisioning as `NONE`;
7. preserve feature activation as `INACTIVE`;
8. preserve every other state field exactly.

The executor requires the caller to supply both the exact PR number and exact 40-character PR head SHA. It resolves the live PR through GitHub before any database access.

## Required exact-head prerequisites

Before database execution, the target exact head must have current success for:

- `final-shift-close-migration27-execution-authority`;
- `product-owner-merge-authority`;
- `Governance Required Checks`;
- `PHP Foundation Regression`;
- `M7.1 Application Regression`.

The Sprint102 sequencing workflow is intentionally not required to be green before execution because that workflow consumes the execution-evidence status produced here. Before evidence exists, the migration state-transition check is expected to remain fail-closed. After verified execution evidence is produced, the Sprint102 gate must be rerun/reevaluated and succeed before merge.

## Environment and secret boundary

The executor uses the fixed GitHub Environment name:

`final-shift-close-migration27-execution`

Sprint103 does not claim that this environment already has reviewer protection or secrets configured. A future authorized operator must configure the repository/environment controls separately before execution.

The workflow requires these environment secrets and fails closed when any is missing:

- `ONEQAY_MIGRATION27_DB_HOST`;
- `ONEQAY_MIGRATION27_DB_PORT`;
- `ONEQAY_MIGRATION27_DB_DATABASE`;
- `ONEQAY_MIGRATION27_DB_USERNAME`;
- `ONEQAY_MIGRATION27_DB_PASSWORD`.

The live executor forces `ONEQAY_DB_DRIVER=mysql`. SQLite remains isolated CI-only qualification and is not accepted by this operational executor.

## Database preflight

Before migration execution, the workflow verifies:

- the canonical `migrations` table exists;
- `oneqay_pos_shift_close_evidence` does not already exist;
- migration #27 is not already recorded;
- required predecessor tables exist;
- required predecessor migration records for shift opening, opening cash, closing cash, and variance review decision evidence exist.

If the migration table or migration record already exists, the executor refuses to manufacture retrospective evidence. Operational drift must be handled by a separately bounded reconciliation process.

## Execution

Only the pinned canonical migration is executed:

```text
php artisan migrate --path=database/migrations/0000_00_00_000027_create_pos_shift_close_evidence_foundation.php --force --no-interaction
```

No rollback, reset, refresh, fresh migration, destructive table command, permission provisioning, feature toggle, deployment, release, or updater action is present in this executor.

## Post-execution verification

Evidence cannot become `success` merely because the migration command exits zero.

The workflow additionally verifies:

- `oneqay_pos_shift_close_evidence` exists;
- migration #27 is recorded exactly once;
- the new close evidence table is empty immediately after schema creation;
- unique indexes `uq_pos_shift_close_operation` and `uq_pos_shift_close_shift` exist;
- index `ix_pos_shift_close_outlet_time` exists;
- CHECK constraint `chk_pos_shift_close_variance_review` is actually exposed as an enforced `CHECK` constraint by the MySQL-compatible target;
- required durable close evidence columns exist.

Only after those checks succeed is the target exact-head status set to:

`final-shift-close-migration27-execution-evidence = success`

The workflow first publishes `pending`. If execution or verification fails after the target has been validated, the final evidence status is `failure`. Sprint102 consumes the latest exact-head status, preventing an older success from overriding a later failure.

## Separation from authorization and state merge

Execution evidence is not authorization. The executor requires Sprint101 migration authority before it can touch the database.

Execution evidence is also not a state merge. The executor never updates `ops/final-shift-close/STATE.json` and never merges the target PR. After successful evidence production, the target state PR still must pass the Sprint102 sequencing gate and all merge requirements.

## Canonical boundaries after Sprint103

`FINAL_SHIFT_CLOSE_MIGRATION27_EXECUTION_EVIDENCE_PRODUCER = SOURCE_MATERIALIZED`

`FINAL_SHIFT_CLOSE_MIGRATION27_EXECUTOR_TRIGGER = MANUAL_MAIN_ONLY`

`FINAL_SHIFT_CLOSE_MIGRATION27_EVIDENCE_CONTEXT = final-shift-close-migration27-execution-evidence`

`SPRINT103_LIVE_EXECUTION = NOT_PERFORMED`

`MIGRATION_27_LIVE_EXECUTION = NOT_AUTHORIZED`

`FINAL_SHIFT_CLOSE_PERMISSION_PROVISIONING = NONE`

`FINAL_SHIFT_CLOSE_FEATURE_ACTIVATION = INACTIVE`

`TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY = NOT_GRANTED`

`PRODUCTION_AUTHORITY = NOT_GRANTED`

`UPDATER_ACTIVATION_AUTHORITY = NOT_GRANTED`

Sprint103 changes no application, infrastructure, provider, route, UI, bootstrap, config, migration, operational state, deployment, release, Technical Preview, Production, or updater source/state beyond the new executor/qualification workflow and this documentation.
