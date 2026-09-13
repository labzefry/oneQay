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

Initial exact-head CI proved the historical Sprint104 permission-provisioning regression still owned its original full three-path PR envelope. Sprint152 therefore expanded by exactly one path to make Sprint104 successor-compatible while preserving the original Sprint104 fingerprint and all transaction, evidence-lifecycle, runtime-boundary, and NO-GO assertions.

The next exact head proved two remaining compatibility details: Sprint104 was looking for its historical fingerprint in a document that never owned that fingerprint, and Sprint116 still required the pre-Sprint152 permission eligibility state plus the absence of selected-target source binding. Sprint104 now preserves its historical fingerprint in its workflow itself. Sprint116 now accepts the successor state only when selected-target binding source is materialized while binding evidence remains absent and execution remains `NOT_PERFORMED`.

Final frozen engineering envelope: seven paths. Sorted newline-terminated SHA-256: `7ed9c7cc6da5f03f73fdbd3ef18f4315896b95832331c2c2d5e2fa6bb2151590`.

## Evidence / Qualification

The exact-head Sprint152 regression validates the seven-path envelope, machine-readable contract, required workflow inputs and binding artifact identity, selected-target prerequisite, pre-mutation database fingerprint readback, `hash_equals()` comparison, migration #27 prerequisite, Sprint104 and Sprint116 successor compatibility, downstream fail-closed state, and unchanged operational NO-GO values.

The initial five-path exact head passed the active Sprint152 regression but produced a CI-proven Sprint104 historical envelope conflict in run `34765046079`. The bounded compatibility correction removed only historical full-envelope ownership.

The six-path correction head then produced a CI-proven Sprint104 fingerprint-location assertion issue and a Sprint116 successor-state conflict in run `34765261070`. The final bounded expansion adds only the Sprint116 historical workflow and preserves its original post-selection readiness fingerprint while recognizing the stronger source-bound-but-evidence-absent permission state.

Sprint152 does not dispatch the provisioning workflow or the Sprint118 binding producer. Real selected-target database-binding evidence remains `NONE` because canonical target selection is still blocked and no operational producer run is performed.

## Operational boundaries / NO-GO

Durable target selection remains `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET` with selected target `null`. Migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; feature activation remains `INACTIVE`; runtime allowlist change remains `NOT_IMPLEMENTED`; deployment authority remains `NOT_GRANTED`; Technical Preview and Production remain `NOT_AUTHORIZED`; updater remains `INACTIVE`.

No permission mutation, database mutation, target persistence, producer dispatch, migration execution, deployment, release, activation, or updater operation occurs in Sprint152.

## Next position

If exact-head qualification, repository-native Product Owner authority, final race, squash merge, post-merge verification, and canonical reconciliation all succeed, Sprint152 may close. The next sprint begins with bounded discovery from the new canonical main; no feature-activation executor, dependency-evidence producer, runtime allowlist change, or operational execution is pre-authorized here.
