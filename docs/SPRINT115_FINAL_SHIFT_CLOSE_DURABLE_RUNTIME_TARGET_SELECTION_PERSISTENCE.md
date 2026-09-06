# Sprint115 — Final Shift Close Durable Runtime Target Selection Persistence

Author by Lab | zefry

## Purpose

Sprint115 materializes the source-only persistence entry gate between a trusted Sprint114 `ACCEPTED_NOT_SELECTED` ingestion artifact and the canonical repository selection state.

The gate does **not** select a target during Sprint115. It only defines how a future target PR may be qualified before repository persistence.

## Canonical starting state

- canonical main entering Sprint115: `6d84f4fd5c948488970492fe41802bf5945f861c`;
- `DURABLE_ACTIVATION_TARGET_SELECTION.json.selection_state = BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- `selected_target = null`;
- Sprint113 producer dispatch has not occurred;
- no real durable runtime attestation exists;
- Sprint114 ingestion dispatch has not occurred;
- no accepted real ingestion candidate exists;
- feature remains inactive.

## Materialized source

Sprint115 adds:

1. `.github/workflows/final-shift-close-durable-runtime-target-selection-persistence.yml`;
2. `.github/workflows/sprint115-final-shift-close-durable-runtime-target-selection-persistence.yml`;
3. `ops/final-shift-close/DURABLE_RUNTIME_TARGET_SELECTION_PERSISTENCE_CONTRACT.json`;
4. this document.

Exact sorted newline path fingerprint:

`b60f059137e8a1893d773d9067378ce0f83c9c76d8e5197a464e9bc36854add1`

## Future executor boundary

The future executor is manual `workflow_dispatch` only and must run from current canonical `main`.

The only accepted dispatch identities are:

- target PR number;
- exact target PR head SHA;
- exact Sprint114 ingestion run ID;
- exact Sprint114 ingestion run attempt.

The caller cannot provide environment ID, runtime class, running source commit, running artifact SHA-256, selection fingerprint, ingestion fingerprint, activation state, or feature state.

## Exact target PR envelope

A future target PR must be:

- open and non-draft;
- same repository;
- based on current canonical `main` at the executor source SHA;
- exact-head qualified;
- exactly one changed file: `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`;
- covered by exact-head `product-owner-merge-authority` success.

The base selection must remain:

- `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- `selected_target = null`.

The target selection may change only:

- `selection_state` to `SELECTED_NOT_AUTHORIZED`;
- `selected_target` from null to the exact trusted evidence-bound target record.

All candidate evaluations, future target requirements, and canonical operational boundaries must remain byte-semantically equivalent after JSON normalization.

## Trusted ingestion binding

The gate resolves the exact Sprint114 ingestion workflow run and requires:

- fixed workflow path `.github/workflows/final-shift-close-durable-runtime-attestation-ingestion.yml`;
- event `workflow_dispatch`;
- branch `main`;
- completed success;
- exact requested run attempt;
- run head still in canonical-main history;
- exactly one non-expired canonical ingestion artifact;
- exact bundle entries `attestation.json`, `provenance.json`, `ingestion.json`, `execution.json`;
- bounded artifact and JSON sizes;
- `ingestion_state = ACCEPTED_NOT_SELECTED`;
- `selected_target = null`;
- `activation_authority_state = NOT_GRANTED`;
- `feature_activation_state = INACTIVE`;
- `persistence_state = NOT_PERFORMED`;
- execution record bound to exact ingestion run ID, attempt, source SHA, and ingestion fingerprint;
- exact success status context `final-shift-close-durable-runtime-attestation-ingestion-evidence` with target URL identifying the same ingestion run.

## Selection derivation

Sprint115 reuses canonical source instead of reimplementing selection semantics:

- Sprint110 `FinalShiftCloseDurableRuntimeReadiness`;
- Sprint112 provenance/ingestion classes;
- Sprint111 `FinalShiftCloseDurableRuntimeTargetSelection`.

The ingestion artifact is recomputed from attestation + provenance and must match exactly.

The selected target is then derived from the Sprint111 selector and persisted only as a future target-PR proposal with these evidence bindings:

- exact environment ID;
- exact runtime class;
- exact running source commit;
- exact running artifact SHA-256;
- readiness attestation SHA-256;
- selection fingerprint SHA-256;
- trusted ingestion run ID;
- trusted ingestion run attempt;
- trusted ingestion fingerprint SHA-256.

The state remains `SELECTED_NOT_AUTHORIZED`. Selection is not activation authority.

## Evidence lifecycle

A future executor publishes:

1. target-head selection persistence evidence status `pending`;
2. a secret-free evidence artifact containing the exact target selection candidate and qualification record;
3. target-head selection persistence evidence status `success` only after artifact publication succeeds.

The executor never edits the target PR and never merges it.

## Explicit no-go boundaries

`DURABLE_RUNTIME_TARGET_SELECTION_PERSISTENCE_EXECUTOR_SOURCE = MATERIALIZED_NOT_DISPATCHED`

`SPRINT113_PRODUCER_DISPATCH = NOT_PERFORMED`

`REAL_DURABLE_RUNTIME_ATTESTATION = NONE`

`SPRINT114_INGESTION_DISPATCH = NOT_PERFORMED`

`ACCEPTED_REAL_INGESTION_CANDIDATE = NONE`

`TARGET_SELECTION_PERSISTENCE_EXECUTION = NOT_PERFORMED`

`SELECTED_TARGET = NONE`

`ACTIVATION_AUTHORITY = NOT_GRANTED`

`RUNTIME_ALLOWLIST_CHANGE = NOT_IMPLEMENTED`

`ENVIRONMENT_DEPLOYMENT = NOT_PERFORMED`

`MIGRATION_27_EXECUTION = NOT_PERFORMED`

`PERMISSION_PROVISIONING = NONE`

`FEATURE_ACTIVATION = INACTIVE`

`TECHNICAL_PREVIEW_ACTIVATION = NOT_AUTHORIZED`

`PRODUCTION_ACTIVATION = NOT_AUTHORIZED`

`UPDATER_ACTIVATION = INACTIVE`

Merging Sprint115 source does not create or select a runtime target and does not authorize any operational action.
