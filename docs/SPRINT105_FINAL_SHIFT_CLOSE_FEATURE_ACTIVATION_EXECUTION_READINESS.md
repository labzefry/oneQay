# Sprint105 Final Shift Close Feature Activation Execution Readiness

Author by Lab | zefry

## Purpose

Sprint105 defines the fail-closed execution-readiness boundary for the third Final Shift Close operational transition: feature activation.

Sprint105 does **not** activate `ONEQAY_POS_SHIFT_CLOSE_ENABLED`, execute migration #27, provision `pos.shift.close`, mutate `ops/final-shift-close/STATE.json`, deploy, release, activate Technical Preview, activate Production, or activate the updater.

The goal is to prevent a runtime flag write, a readiness result, or a repository state edit from being misrepresented as trusted feature-activation execution evidence.

## Canonical sequencing prerequisite

Sprint102 permits the feature state transition only as:

`INACTIVE -> ACTIVE`

and only when the canonical base state already records:

1. migration #27 as `EXECUTED`;
2. scoped `pos.shift.close` provisioning as `PROVISIONED`;
3. exact-head `final-shift-close-feature-activation-authority = success`;
4. exact-head `final-shift-close-feature-activation-evidence = success`.

Current canonical operational state remains pre-operational:

- migration #27: `NOT_EXECUTED`;
- permission provisioning: `NONE`;
- feature activation: `INACTIVE`;
- permission default grant: `NONE`.

Therefore no feature activation is currently executable.

## Canonical runtime findings

### 1. Final Shift Close delivery is not Preview-capable

`FinalShiftCloseServiceProvider` reads `ONEQAY_POS_SHIFT_CLOSE_ENABLED` fail-closed with default `false`, but route delivery additionally requires all of the following:

- runtime class is one of `local`, `test`, or `ci`;
- durable persistence is enabled;
- session control is enabled;
- POS sale completion is enabled;
- Final Shift Close feature flag is enabled.

The canonical Technical Preview environment envelope uses runtime class `preview`.

Therefore setting `ONEQAY_POS_SHIFT_CLOSE_ENABLED=true` in a Technical Preview runtime would **not** activate Final Shift Close delivery under the current provider contract.

### 2. Upstream durable POS dependencies are also Local/Test/CI-only

The canonical durable sale repository rejects runtime classes outside `local`, `test`, and `ci`.

Final Shift Close depends on the durable POS lifecycle, session/context controls, expected-cash derivation, and durable authorization chain. Expanding only `FinalShiftCloseServiceProvider` to include another runtime class would create a partially enabled runtime and is not a valid activation strategy.

A future runtime-class expansion, if selected, requires its own bounded source qualification across the full dependency chain. Sprint105 does not select or implement such an expansion.

### 3. Operational state is not target-runtime scoped

`ops/final-shift-close/STATE.json` records a single feature state and runtime-flag identity. It does not identify:

- a target environment;
- a deployed runtime instance;
- a deployment/build identifier;
- a canonical source SHA running on that target;
- a configuration revision;
- a runtime mutation channel.

The actual `ONEQAY_POS_SHIFT_CLOSE_ENABLED` value is environment-local. A success status cannot safely represent feature activation unless the exact target and exact running source are independently attested.

### 4. Trusted activation mutation/provenance channel is absent

No canonical Final Shift Close feature-activation executor exists after Sprint104.

There is currently no bounded trusted mechanism that can all-at-once:

1. identify one exact existing runtime target;
2. prove the exact canonical source/build running on that target;
3. read the current feature flag as `false`;
4. mutate only that flag to `true` without deploying source;
5. read the flag back from the same target;
6. prove Final Shift Close is actually reachable under the selected runtime contract;
7. restore the flag to `false` and verify rollback on any failed activation proof;
8. publish exact-head activation evidence only after all prior checks succeed.

Without that mechanism, publishing `final-shift-close-feature-activation-evidence = success` would be fake-green evidence.

## Required future activation target contract

Before a trusted feature-activation evidence producer can be materialized, a bounded successor must define an exact activation target contract containing at least:

- one explicit runtime class;
- one stable runtime/environment identity;
- one canonical source or build SHA identity;
- one authenticated configuration-control channel;
- one read-before/write/read-after flag primitive scoped only to `ONEQAY_POS_SHIFT_CLOSE_ENABLED`;
- one non-mutating runtime health/route attestation primitive;
- one rollback primitive that restores `false` and verifies the restoration;
- one audit/evidence identity that binds the target, source/build, target PR, and exact target head SHA.

No target runtime class is selected by Sprint105. In particular, Technical Preview and Production are not inferred or authorized.

## Required future executor semantics

A future executor must be manual and fail closed. It must refuse execution unless all of the following are true on the exact target PR head:

1. the target PR changes only `ops/final-shift-close/STATE.json`;
2. the target PR is based on the current canonical `main`;
3. base migration state is `EXECUTED`;
4. base permission provisioning state is `PROVISIONED`;
5. base feature state is `INACTIVE`;
6. target feature state is exactly `ACTIVE` and all other canonical boundaries are unchanged;
7. `final-shift-close-feature-activation-authority = success`;
8. `product-owner-merge-authority = success`;
9. all required source/regression qualification workflows are terminal-success;
10. the selected runtime target contract is exact and immutable for that execution.

Execution must then prove, in order:

1. exact target identity;
2. exact running canonical source/build identity;
3. feature flag read-before equals `false`;
4. prerequisites required by Final Shift Close delivery are present on the same target;
5. a single bounded configuration mutation changes only `ONEQAY_POS_SHIFT_CLOSE_ENABLED` to `true`;
6. read-after equals `true` on the same target;
7. a non-mutating health/route attestation proves the feature is reachable under the selected runtime contract;
8. only then may `final-shift-close-feature-activation-evidence = success` be published on the exact target PR head.

If any post-write check fails, the executor must restore the feature flag to `false`, verify the restoration, publish activation evidence as failure, and leave canonical feature state `INACTIVE`.

## Authority separation

Feature activation authority remains independent from:

- migration #27 execution authority;
- permission provisioning authority;
- merge authority;
- deployment authority;
- Technical Preview activation authority;
- Production authority;
- DNS/cutover authority;
- release authority;
- updater activation authority.

A feature-activation executor must not deploy source, change application code, run migrations, grant roles/permissions, create releases, alter DNS, or activate the updater.

## Sprint105 disposition

`FEATURE_ACTIVATION_EXECUTION_READINESS = BLOCKED`

`BLOCKER_RUNTIME_SCOPE = UNRESOLVED`

`BLOCKER_RUNTIME_MUTATION_CHANNEL = NOT_IMPLEMENTED`

`BLOCKER_PREVIEW_RUNTIME_COMPATIBILITY = NOT_QUALIFIED`

`FEATURE_ACTIVATION_EVIDENCE_PRODUCER = NOT_IMPLEMENTED`

`FEATURE_ACTIVATION_EXECUTION = NOT_PERFORMED`

`MIGRATION_27_LIVE_EXECUTION = NOT_AUTHORIZED`

`FINAL_SHIFT_CLOSE_PERMISSION_PROVISIONING = NONE`

`FINAL_SHIFT_CLOSE_FEATURE_ACTIVATION = INACTIVE`

`DEPLOYMENT_AUTHORITY = NOT_GRANTED`

`TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY = NOT_GRANTED`

`PRODUCTION_AUTHORITY = NOT_GRANTED`

`UPDATER_ACTIVATION_AUTHORITY = NOT_GRANTED`
