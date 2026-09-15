# oneQay Tasks

**Current canonical engineering checkpoint:** Sprint167 closed
**Canonical engineering commit:** `dbdf0aa90a6d6127cf10113ec8c1092b8780504f`
**Latest engineering PR:** #752 — `Sprint167: add POS active shift performance workspace`
**Canonical status authority:** `PROJECT_MANIFEST.md`

## Completed Sprint167 state

Sprint167 materialized `POS_ACTIVE_SHIFT_PERFORMANCE_WORKSPACE` as a bounded read-only exact-device live-shift reporting surface over existing immutable sale, full-void, and cash-refund evidence.

- [x] Exact tenant + organization + outlet + device isolation.
- [x] Current active exact-device shift only.
- [x] Explicit valid empty state when no active shift exists.
- [x] Shift-bound immutable completed-sale evidence only.
- [x] Tender + currency + scale bucket separation.
- [x] Completed, voided, active, and cash-refund transaction counts.
- [x] Gross, full-void, active-net, and refunded-cash values.
- [x] CASH refund shown separately and not subtracted twice.
- [x] Legacy null-shift sale evidence at/after opening fails closed.
- [x] Cross-scope/corrupt/inconsistent evidence and overflow fail closed.
- [x] Bucket count bounded to 64.
- [x] Existing `pos.reporting.sales-summary.view` reused; no new permission/provisioning.
- [x] Default-false guarded route and feature flag.
- [x] POS Operations Hub discoverability remains permission + `Route::has()` guarded.
- [x] No schema/migration/mutation/global-owner changes.
- [x] Exact engineering head qualification complete and successful.
- [x] PR #752 squash merged at `dbdf0aa90a6d6127cf10113ec8c1092b8780504f`.

Engineering envelope: 12 paths; SHA-256 `e9ca990e36ce896428bc749d19cf47025ce0525fd707f5e330d5d1cfbbfc8b48`.

Canonical reconciliation envelope: six paths; SHA-256 `2fd60a53d8996d41452cb10036d350a14a4d95a2943e897c233d29f3eac32b14`.

## Preserved lifecycle state

Machine-readable operational state under `ops/final-shift-close/` remains authoritative and unchanged: selected target `null`; migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; real target-bound capability/dependency evidence absent; producer dispatch not performed; feature activation `INACTIVE`; deployment authority `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`.

## Next engineering position

Begin **Sprint168 bounded discovery** only from the fully reconciled Sprint167 canonical checkpoint. Do not preselect the objective. Select the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap from live source/contracts/regressions and reuse existing canonical owners wherever possible. Do not infer new mutation or operational authority.

Author by Lab | zefry
