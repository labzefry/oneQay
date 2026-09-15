# oneQay Tasks

**Current canonical engineering checkpoint:** Sprint168 closed
**Canonical engineering commit:** `d3703a6b18f478acd812e7892c3871ce3aaf7bfa`
**Latest engineering PR:** #754 — `Sprint168: add POS shift history performance workspace`
**Canonical status authority:** `PROJECT_MANIFEST.md`

## Completed Sprint168 state

Sprint168 materialized `POS_SHIFT_HISTORY_PERFORMANCE_WORKSPACE` as a bounded read-only closed-shift reporting surface over canonical Final Shift Close and immutable shift-bound sale evidence.

- [x] Exact tenant + organization + outlet scope.
- [x] Same-outlet cross-device historical visibility.
- [x] Closed shifts only (`active_slot IS NULL`).
- [x] Exactly one canonical Final Shift Close evidence row required per eligible shift.
- [x] Newest 50 closed shifts bounded list.
- [x] Optional selected shift defaults to newest eligible shift.
- [x] Opener/closer/device/open-close/cutoff identity validation.
- [x] Expected/observed/variance/review arithmetic validation.
- [x] Immutable completed-sale, full-sale-void, and CASH-refund performance by tender + currency + scale.
- [x] Active net = gross minus full-sale void; CASH refund not subtracted twice.
- [x] Legacy null-shift sales inside the selected shift window fail closed.
- [x] Foreign-scope/outside-window/corrupt/inconsistent evidence and overflow fail closed.
- [x] Performance bucket count bounded to 64.
- [x] Existing `pos.reporting.sales-summary.view` reused; no new permission/provisioning.
- [x] Default-false guarded route and feature flag.
- [x] Existing `ONEQAY_POS_SHIFT_CLOSE_ENABLED` source/runtime dependency preserved.
- [x] POS Operations Hub discoverability remains permission + `Route::has()` guarded.
- [x] No schema/migration/mutation/global-owner changes.
- [x] Exact engineering head qualification complete and successful.
- [x] PR #754 squash merged at `d3703a6b18f478acd812e7892c3871ce3aaf7bfa`.

Engineering envelope: 12 paths; SHA-256 `d42a5a7d8568766e524ff662107ffdb35452d4f9ac00f26cdc8e419d36bc3c25`.

Canonical reconciliation envelope: six paths; SHA-256 `350fd16d111bb398c34a63e1519cc02206bcc373378a9366768c198ccf8146b5`.

## Preserved lifecycle state

Machine-readable operational state under `ops/final-shift-close/` remains authoritative and unchanged: selected target `null`; migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; real target-bound capability/dependency evidence absent; producer dispatch not performed; feature activation `INACTIVE`; deployment authority `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`.

## Next engineering position

Begin **Sprint169 bounded discovery** only from the fully reconciled Sprint168 canonical checkpoint. Do not preselect the objective. Select the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap from live source/contracts/regressions and reuse existing canonical owners wherever possible. Do not infer new mutation or operational authority.

Author by Lab | zefry
