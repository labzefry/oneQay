# Sprint225 Historical Selected-Target Successor Compatibility

Author by Lab | zefry

## Objective

Sprint225 closes one cross-cutting historical CI compatibility gap revealed by PR #880 after the canonical durable target was selected by PR #879.

The current canonical selected-target state is now:

- selection_state: SELECTED_NOT_AUTHORIZED
- environment_id: oneqay-durable-staging-01
- runtime_class: durable-staging
- exact running source commit: 5be28a3c001738373588b58e9d29832c46402de1
- exact running artifact SHA-256: 66be23792478fb191f912571b35076c42783b9733cdb3fb514b549d22ec90dd7
- selection fingerprint SHA-256: 858280ea3575317e8d88eed7009c770530097e261c391b2869fa81ad7c8546ce

PR #880 proved that historical regressions from Sprint125 through Sprint223 still contained current-state assertions that required the superseded pre-selection state:

- BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET
- selected_target = null

Those assertions are stale because PR #879 intentionally persisted the selected durable-staging target while preserving all activation and migration NO-GO boundaries.

## Scope

Sprint225 updates only the 87 historical workflow files that actually failed on PR #880, plus this note and the Sprint225 regression workflow.

Path count: 89

Sorted newline path-set SHA-256:

b183716512de26c08f1416d5fb62e94d01cbd4e722b93c09668db2fd42f19dd3

No application source, database migration, runtime configuration, operational state file, target-selection file, deployment source, installer source, updater source, or documentation outside this Sprint225 note is changed.

## Successor compatibility rules

Historical workflow contracts remain historical provenance. Contract assertions such as canonical_boundaries.selected_target == null are not rewritten when they describe the state owned by that historical Sprint.

Only current operational-state assertions are advanced to the canonical post-PR879 state.

Current selected-target checks now require:

- SELECTED_NOT_AUTHORIZED
- exact environment ID oneqay-durable-staging-01
- exact runtime class durable-staging
- exact selection fingerprint 858280ea3575317e8d88eed7009c770530097e261c391b2869fa81ad7c8546ce

Historical regression workflows that previously required the migration27 selected-target DB-binding producer workflow to remain byte-unchanged no longer treat that producer as immutable. They continue to preserve application source, migration source, STATE.json, target-selection state, migration execution, permission provisioning, feature activation, deployment, Preview, Production, and updater boundaries.

This allows a separately governed successor producer correction, such as Sprint224, without disabling the historical invariant regressions.

## Evidence source

PR #880 exact head used for failure classification:

4986af4a9974f01b906e936fee3476ebda1d65dc

Observed classification:

- 87 historical workflow failures were mapped one-to-one to their workflow files.
- Failure steps were operational NO-GO/lifecycle preservation steps or historical producer immutability checks.
- Sprint224 regression, Sprint118, Sprint117, Sprint103, Governance, and PHP Foundation were independently successful on that head.
- The Sprint225 correction is therefore a CI successor-compatibility closure, not a product/runtime behavior change.

## NO-GO preserved

MIGRATION27_EXECUTION = NOT_PERFORMED

MIGRATION27_EXECUTION_AUTHORITY = NOT_GRANTED

PERMISSION_PROVISIONING = NONE

FINAL_SHIFT_CLOSE = INACTIVE

TECHNICAL_PREVIEW = NOT_AUTHORIZED

PRODUCTION = NOT_AUTHORIZED

UPDATER = INACTIVE

DATABASE_MUTATION = NO

APPLICATION_RUNTIME_MUTATION = NO

TARGET_RESELECTION = NO

After Sprint225 is merged, PR #880 must be rebased/reverified against the new main. Sprint225 itself does not authorize migration #27 and does not produce migration27 DB-binding evidence.

Author by Lab | zefry
