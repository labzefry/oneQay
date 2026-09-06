# Sprint116 — Final Shift Close Post-Selection Downstream Readiness

Author by Lab | zefry

## Purpose

Sprint116 defines the fail-closed boundary that applies **after** a future durable runtime target is persisted as `SELECTED_NOT_AUTHORIZED`.

This sprint does not select a target and does not execute any operational workflow. It answers a narrower question:

> Once a durable runtime target has been selected, which downstream Final Shift Close actions are actually eligible to run against that exact target?

The answer at the Sprint116 checkpoint is: **none yet**.

## Canonical checkpoint

Sprint116 starts from canonical main:

`7877d693bb89a427a776975559f3efe40c00362b`

At this checkpoint:

- `DURABLE_ACTIVATION_TARGET_SELECTION.json` remains `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- `selected_target` remains `null`;
- migration #27 remains `NOT_EXECUTED`;
- permission provisioning remains `NONE`;
- Final Shift Close feature remains `INACTIVE`;
- no deployment, Technical Preview activation, Production activation, or updater activation is authorized.

## Finding: selected target identity is not yet bound to the operational DB ceremonies

The existing migration #27 executor and permission-provisioning executor were intentionally materialized before durable target selection existed.

They already have strong exact-head, authority, state-transition, and evidence controls. However, their operational database endpoints are supplied through their fixed GitHub Environment secrets and they do **not** currently consume or verify the canonical durable target-selection record.

That means the following is not yet machine-proven:

- the database reached by migration #27 secrets belongs to the selected `environment_id`;
- the permission-provisioning database is the same selected environment used for migration #27;
- the operational database is running the exact selected source commit and artifact digest;
- the operational endpoint/control channel corresponds to the same readiness attestation and trusted ingestion evidence that caused target selection.

GitHub Environment naming alone is not sufficient evidence of runtime identity.

Therefore Sprint116 classifies both existing executors as:

`NOT_ELIGIBLE_SELECTED_TARGET_BINDING_MISSING`

This is a readiness disposition only. It does not invalidate the source quality of Sprint103 or Sprint104.

## Required target binding

Before any durable operational action becomes eligible, downstream execution must be bound to the exact canonical selected target through all of these fields:

- `environment_id`;
- `runtime_class`;
- `exact_running_source_commit`;
- `exact_running_artifact_sha256`;
- `readiness_attestation_sha256`;
- `selection_fingerprint_sha256`;
- trusted ingestion run ID;
- trusted ingestion run attempt;
- trusted ingestion fingerprint SHA-256.

The operational control channel or database endpoint must then provide a fail-closed pre-mutation readback proving it belongs to that same selected environment and exact build provenance.

## Migration #27 downstream eligibility

Current source:

`.github/workflows/final-shift-close-migration27-execution.yml`

Current disposition:

`NOT_ELIGIBLE_SELECTED_TARGET_BINDING_MISSING`

Before it may become eligible, a successor must add a bounded binding mechanism proving:

1. canonical selection is `SELECTED_NOT_AUTHORIZED`;
2. the selected target record is exact and trusted-ingestion-bound;
3. the migration database/control channel belongs to that selected `environment_id`;
4. pre-mutation runtime identity readback matches the selected runtime class;
5. running source SHA and artifact SHA-256 match the selected target;
6. existing migration authority, merge authority, exact target state PR, and regression gates remain intact.

Sprint116 does not implement or run that successor.

## Permission provisioning downstream eligibility

Current source:

`.github/workflows/final-shift-close-permission-provisioning.yml`

Current disposition:

`NOT_ELIGIBLE_SELECTED_TARGET_BINDING_MISSING`

Before it may become eligible, a successor must prove all migration binding requirements **and**:

1. migration #27 was executed on this exact same selected target;
2. permission provisioning reaches the same selected target database/control plane;
3. `NO_DEFAULT_GRANT` remains preserved;
4. the exact tenant/control-principal/role transaction boundary remains unchanged.

Sprint116 does not implement or run that successor.

## Runtime allowlist downstream eligibility

Canonical `FinalShiftCloseServiceProvider` still enables delivery only for:

- `local`;
- `test`;
- `ci`.

Current disposition:

`NOT_ELIGIBLE_SELECTED_RUNTIME_NOT_QUALIFIED`

A future selected non-synthetic durable runtime class must not be added to the allowlist until the complete durable dependency envelope is qualified for that exact class. This preserves the Sprint107/Sprint108 rule against one-line allowlist widening and synthetic/durable mixing.

## Feature activation downstream eligibility

Canonical feature-activation evidence context exists in `STATE.json`, but there is still no source file:

`.github/workflows/final-shift-close-feature-activation.yml`

Current disposition:

`NOT_ELIGIBLE_EXECUTOR_NOT_IMPLEMENTED`

A future executor may only be materialized after:

1. migration #27 execution is proven on the selected target;
2. permission provisioning is proven on that same target;
3. the full durable runtime dependency envelope is qualified for the selected runtime class;
4. an authenticated config mutation channel is bound to the selected environment;
5. read-before-write/read-after flag verification is available;
6. non-mutating health attestation is available;
7. rollback to feature flag `false` is verified;
8. separate exact feature-activation authority is obtained.

Selection itself is never activation authority.

## Required ordering

The canonical downstream order is now:

1. persist a trusted `SELECTED_NOT_AUTHORIZED` target;
2. bind and qualify migration #27 execution to that selected target;
3. execute and prove migration #27 on that target;
4. bind and qualify permission provisioning to the same selected target;
5. provision and prove `pos.shift.close` on that target;
6. qualify the complete durable runtime envelope for the selected runtime class;
7. materialize a selected-target-bound feature-activation executor;
8. obtain exact feature-activation authority;
9. activate only with readback, health, and rollback evidence.

No later step may be used to bypass an earlier one.

## Forbidden shortcuts

Sprint116 explicitly rejects:

- running migration #27 against database secrets that are not bound to the selected target;
- running permission provisioning against database secrets that are not bound to the selected target;
- treating a GitHub Environment name as proof of serving runtime identity;
- widening Final Shift Close runtime allowlists before the full dependency envelope is qualified;
- treating `SELECTED_NOT_AUTHORIZED` as activation authority;
- treating repository state JSON as proof that a live runtime mutation occurred.

## Sprint116 source boundary

`POST_SELECTION_DOWNSTREAM_READINESS_CONTRACT = MATERIALIZED_SOURCE_ONLY`

`CURRENT_SELECTION_STATE = BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`

`SELECTED_TARGET = NONE`

`MIGRATION27_SELECTED_TARGET_BINDING = MISSING`

`PERMISSION_PROVISIONING_SELECTED_TARGET_BINDING = MISSING`

`FEATURE_ACTIVATION_EXECUTOR = NOT_IMPLEMENTED`

`RUNTIME_ALLOWLIST_CHANGE = NOT_IMPLEMENTED`

`SPRINT113_PRODUCER_DISPATCH = NOT_PERFORMED`

`REAL_DURABLE_RUNTIME_ATTESTATION = NONE`

`SPRINT114_INGESTION_DISPATCH = NOT_PERFORMED`

`ACCEPTED_REAL_INGESTION_CANDIDATE = NONE`

`SPRINT115_PERSISTENCE_EXECUTOR_DISPATCH = NOT_PERFORMED`

`TARGET_SELECTION_PERSISTENCE_EXECUTION = NOT_PERFORMED`

`MIGRATION_27_EXECUTION = NOT_PERFORMED`

`PERMISSION_PROVISIONING = NONE`

`FEATURE_ACTIVATION = INACTIVE`

`DEPLOYMENT_AUTHORITY = NOT_GRANTED`

`TECHNICAL_PREVIEW_ACTIVATION = NOT_AUTHORIZED`

`PRODUCTION_ACTIVATION = NOT_AUTHORIZED`

`UPDATER_ACTIVATION = INACTIVE`

## No operational authority consumed

Merging Sprint116 does not grant or consume migration, permission-provisioning, deployment, feature-activation, Technical Preview, Production, release, DNS, or updater authority.
