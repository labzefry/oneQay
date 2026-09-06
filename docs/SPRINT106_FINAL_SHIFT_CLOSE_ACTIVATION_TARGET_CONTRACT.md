# Sprint106 Final Shift Close Activation Target Contract

Author by Lab | zefry

## Purpose

Sprint106 resolves the runtime-scope ambiguity identified by Sprint105 without activating Final Shift Close.

The selected logical activation target is the existing **Synthetic Technical Preview** runtime only. Sprint106 does not authorize or perform Technical Preview activation, Final Shift Close activation, migration #27 execution, permission provisioning, deployment, release, DNS change, Production activation, or updater activation.

The machine-readable target contract is:

`ops/final-shift-close/ACTIVATION_TARGET.json`

## Selected target scope

The target contract fixes the following logical identity:

- environment ID: `oneqay-synthetic-technical-preview`;
- environment class: `technical-preview`;
- runtime class: `preview`;
- selection state: `SELECTED_NOT_AUTHORIZED`.

Selection of a logical target is not activation authority. It only prevents a future feature-activation executor from choosing an arbitrary runtime, Production instance, or caller-supplied target.

Production is not an allowed target under this contract.

## Runtime provenance contract

A future trusted executor must derive the running build identity from the canonical System Update active-release pointer rather than accepting a caller-supplied SHA.

The required safe release identity fields are:

- `release_id`;
- `source_commit`;
- `artifact_sha256`.

The canonical `SystemUpdateReleaseIdentity` contract already requires a 40-character lowercase source commit, a 64-character artifact SHA-256, and a release ID bound to the source commit prefix. The canonical active-release pointer persists and rehydrates those values under pointer version 1.

Sprint106 names that provenance profile:

`SYSTEM_UPDATE_ACTIVE_RELEASE_POINTER_V1`

A future executor must read the active pointer from the selected target at execution time. It must never trust target-PR text, workflow inputs, or a repository state edit as proof of the build actually running on the target.

## Shared runtime configuration profile

The selected Technical Preview target uses the canonical private shared runtime environment profile:

`PRIVATE_SHARED_DOTENV_V1`

The profile is already recognized by the shared-runtime environment boundary and is intentionally secret-safe. Sprint106 does not read, copy, emit, or mutate raw environment values.

The feature flag remains:

`ONEQAY_POS_SHIFT_CLOSE_ENABLED`

with the required activation ceremony values:

- read-before: `false`;
- desired write: `true`;
- rollback: `false`.

Sprint106 defines the future safe feature configuration revision profile:

`FINAL_SHIFT_CLOSE_FEATURE_FLAG_REVISION_V1`

That revision must be produced by a future trusted configuration-control mechanism without exposing raw secrets. No such producer is implemented by Sprint106.

## Remaining runtime compatibility blocker

Target scope is now selected, but the selected target is **not yet runtime-compatible** with Final Shift Close.

Canonical Final Shift Close delivery currently permits only runtime classes:

- `local`;
- `test`;
- `ci`.

The selected target runtime class is `preview`.

The upstream durable POS sale repository also rejects runtime classes outside `local`, `test`, and `ci`.

Therefore a future successor must qualify the complete dependency chain for `preview`; widening only `FinalShiftCloseServiceProvider` would create a partial runtime and remains forbidden.

Sprint106 does not change runtime-class allowlists.

## Future activation evidence requirements

A future trusted feature-activation evidence producer may exist only after all blockers below are closed:

1. the selected `preview` target is qualified across the complete durable dependency chain;
2. an authenticated configuration-control channel for `PRIVATE_SHARED_DOTENV_V1` exists;
3. the channel can read `ONEQAY_POS_SHIFT_CLOSE_ENABLED=false` from the selected target;
4. it can mutate only that flag to `true` without deploying source or changing other configuration;
5. it can read the flag back as `true` from the same target;
6. it can independently read the active-release pointer and bind evidence to `release_id`, `source_commit`, and `artifact_sha256`;
7. it can produce a secret-safe configuration revision identity;
8. it can perform non-mutating health/route attestation on the same target;
9. it can restore the flag to `false` and verify that restoration if any post-write check fails;
10. only after all checks succeed may it publish `final-shift-close-feature-activation-evidence = success` on the exact target PR head.

## Canonical operational state remains unchanged

Sprint106 does not mutate `ops/final-shift-close/STATE.json`.

Current canonical operational state therefore remains:

- migration #27: `NOT_EXECUTED`;
- `pos.shift.close` provisioning: `NONE`;
- feature activation: `INACTIVE`;
- default grant: `NONE`.

No execution evidence is produced by target selection.

## Authority separation

The target contract does not grant or imply:

- `final-shift-close-feature-activation-authority`;
- migration #27 execution authority;
- permission provisioning authority;
- deployment authority;
- Technical Preview activation authority;
- Production authority;
- release authority;
- DNS/cutover authority;
- updater activation authority.

Any future operational action still requires its separately defined exact-head authority and trusted execution evidence.

## Sprint106 disposition

`FINAL_SHIFT_CLOSE_ACTIVATION_TARGET = ONEQAY_SYNTHETIC_TECHNICAL_PREVIEW`

`BLOCKER_RUNTIME_SCOPE = CLOSED_BY_TARGET_CONTRACT`

`TARGET_RUNTIME_CLASS = preview`

`TARGET_SHARED_ENVIRONMENT_PROFILE = PRIVATE_SHARED_DOTENV_V1`

`TARGET_RELEASE_PROVENANCE = SYSTEM_UPDATE_ACTIVE_RELEASE_POINTER_V1`

`BLOCKER_PREVIEW_RUNTIME_COMPATIBILITY = NOT_QUALIFIED`

`BLOCKER_RUNTIME_MUTATION_CHANNEL = NOT_IMPLEMENTED`

`FEATURE_ACTIVATION_EVIDENCE_PRODUCER = NOT_IMPLEMENTED`

`FEATURE_ACTIVATION_EXECUTION = NOT_PERFORMED`

`MIGRATION_27_LIVE_EXECUTION = NOT_AUTHORIZED`

`FINAL_SHIFT_CLOSE_PERMISSION_PROVISIONING = NONE`

`FINAL_SHIFT_CLOSE_FEATURE_ACTIVATION = INACTIVE`

`DEPLOYMENT_AUTHORITY = NOT_GRANTED`

`TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY = NOT_GRANTED`

`PRODUCTION_AUTHORITY = NOT_GRANTED`

`UPDATER_ACTIVATION_AUTHORITY = NOT_GRANTED`
