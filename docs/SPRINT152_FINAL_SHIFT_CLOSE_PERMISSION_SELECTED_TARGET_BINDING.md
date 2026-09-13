# Sprint152 Final Shift Close Permission Selected-Target Binding

Author by Lab | zefry

## Purpose / Why

The Final Shift Close permission-provisioning executor already performs a bounded tenant-scoped `pos.shift.close` grant, but its database credentials are not yet proven to belong to the exact durable runtime selected by the canonical target-selection chain. Sprint118 already materialized trusted selected-target database-binding evidence for migration #27, so Sprint152 must reuse that evidence instead of creating another producer.

## Objective / Gap

Bounded objective: `PERMISSION_PROVISIONING_SELECTED_TARGET_DATABASE_BINDING`.

The permission executor must fail closed unless it can resolve one exact successful Sprint118 binding run, prove the binding artifact matches the canonical `SELECTED_NOT_AUTHORIZED` target, and independently read the database identity used for provisioning immediately before mutation so its SHA-256 binding equals the trusted selected-target database fingerprint.

## What changed

Sprint152 hardens `.github/workflows/final-shift-close-permission-provisioning.yml` to require exact database-binding run identity and canonical selected-target identity before provisioning can proceed. It reuses the Sprint118 `binding.json` and `execution.json` artifact, requires exact evidence status identity, and requires an immediate readback of `DATABASE()`, `@@hostname`, and `@@port` from the actual provisioning connection before the existing transaction is allowed to mutate authorization state.

No new database-binding producer is introduced. The existing permission semantics remain bounded to one existing same-tenant non-protected role, preserve the canonical migration #27 prerequisite, and preserve `NO_DEFAULT_GRANT` assignment boundaries.

Frozen engineering envelope: five paths. Sorted newline-terminated SHA-256: `10b79aec054c69df9208dbabf014ac2ed885701a0046d6d950a0623783007fac`.

## Evidence / Qualification

The exact-head Sprint152 regression validates the five-path envelope, machine-readable contract, required workflow inputs and binding artifact identity, selected-target prerequisite, pre-mutation database fingerprint readback, `hash_equals()` comparison, migration #27 prerequisite, downstream fail-closed state, and unchanged operational NO-GO values.

Sprint152 does not dispatch the provisioning workflow or the Sprint118 binding producer. Real selected-target database-binding evidence remains `NONE` because canonical target selection is still blocked and no operational producer run is performed.

## Operational boundaries / NO-GO

Durable target selection remains `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET` with selected target `null`. Migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; feature activation remains `INACTIVE`; runtime allowlist change remains `NOT_IMPLEMENTED`; deployment authority remains `NOT_GRANTED`; Technical Preview and Production remain `NOT_AUTHORIZED`; updater remains `INACTIVE`.

No permission mutation, database mutation, target persistence, producer dispatch, migration execution, deployment, release, activation, or updater operation occurs in Sprint152.

## Next position

If exact-head qualification, repository-native Product Owner authority, final race, squash merge, post-merge verification, and canonical reconciliation all succeed, Sprint152 may close. The next sprint begins with bounded discovery from the new canonical main; no feature-activation executor, dependency-evidence producer, runtime allowlist change, or operational execution is pre-authorized here.
