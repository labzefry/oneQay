# oneQay Tasks

**Current canonical engineering checkpoint:** Sprint165 closed
**Canonical engineering commit:** `dc6340c04ac710bd38966d897b27e23fd92c0a41`
**Latest engineering PR:** #748 — `Sprint165: add POS inventory accountability workspace`
**Canonical status authority:** `PROJECT_MANIFEST.md`

## Completed Sprint165 state

Sprint165 materialized `POS_INVENTORY_ACCOUNTABILITY_WORKSPACE` as a read-only accountability layer over existing immutable stock evidence.

Engineering PR #748 squash merged at `dc6340c04ac710bd38966d897b27e23fd92c0a41` after complete exact-head qualification and repository-native Product Owner merge authorization.

Engineering envelope: exactly 14 paths with sorted newline SHA-256 `d83945325461acdd9db7f1ab02bb908a1e0e263a6f680e2f0acf1f3256978786`.

Canonical reconciliation envelope: exactly six paths with sorted newline SHA-256 `2f10216f9f2c86a188a924c66a8473c17cd5310da47bd768a8ab1bb0f4a17533`.

## Completed accountability invariants

- Current stock is accepted only when `opening + replenishment + full-sale-void restoration - completed sale quantity` equals canonical persisted stock.
- Reconciliation is aggregate/commutative; timestamps do not determine accounting truth.
- CASH refunds do not restore inventory.
- Inactive but baselined products remain visible for historical accountability.
- Foreign tenant/outlet evidence, orphan evidence, malformed quantities, overflow, and current-stock mismatch fail closed.
- Workspace is read-only; no insert/update/delete/increment/decrement path exists in its repository.
- Access reuses existing `pos.inventory.baseline` OR `pos.inventory.replenish`; no new permission or provisioning exists.
- Delivery remains default-off and Local/Test/CI only with persistence and exact session controls.
- No migration, arbitrary stock adjustment, stocktake mutation, purchasing/supplier flow, transfer flow, negative adjustment, global route/provider edit, shared permission-registry edit, or Final Shift Close owner change was introduced.

## Lifecycle state

Machine-readable operational state under `ops/final-shift-close/` remains authoritative and unchanged: target selection remains blocked, selected target `null`, migration #27 `NOT_EXECUTED`, permission provisioning `NONE`, feature activation `INACTIVE`, deployment authority `NOT_GRANTED`, Technical Preview/Production `NOT_AUTHORIZED`, updater `INACTIVE`.

## Next engineering position

Begin **Sprint166 bounded discovery** from the fully reconciled post-Sprint165 checkpoint. Do not preselect the objective. Select the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap from current source/contracts/regressions.

Do not infer stock-adjustment, stocktake, purchasing, supplier, transfer, or negative-inventory authority from Sprint165; each requires separate bounded justification if later selected.

Author by Lab | zefry
