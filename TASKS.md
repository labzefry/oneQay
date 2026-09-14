# oneQay Tasks

**Current engineering checkpoint:** Sprint161 closed
**Canonical engineering commit:** `33080c0b5f5c6f66e9994ad7f05ba78dea241294`
**Canonical status authority:** `PROJECT_MANIFEST.md`

This file separates completed source engineering, next bounded engineering, and operational actions that remain separately gated.

## Completed through Sprint161

- [x] Core modular-monolith architecture and repository governance foundation
- [x] Tenant-context-first and deny-by-default authorization foundations
- [x] Versioned REST / correlation / idempotency governance foundations
- [x] POS shift/register, sale/payment/receipt, catalog, inventory, void/refund, cash-variance, adjudication, and reviewer-control foundations
- [x] Final Shift Close source/readiness chain through Sprint155 without operational activation
- [x] Read-only POS operational sales reporting — Sprint156
- [x] Operational POS cashier sale-entry workspace — Sprint157
- [x] Operational POS shift-start workspace — Sprint158
- [x] Operational POS sale correction workspace — Sprint159
- [x] Immutable POS sale-history and receipt-detail workspace — Sprint160
- [x] Operational POS catalog and opening-inventory setup workspace — Sprint161
- [x] Existing `pos.catalog.prepare` and `pos.inventory.baseline` permission reuse
- [x] Existing catalog-preparation and inventory-baseline mutation endpoint reuse
- [x] Tenant/outlet catalog and stock visibility with maximum 250 rows
- [x] Baseline eligibility aligned to canonical mutation conditions
- [x] Explicit two-step catalog → opening-inventory flow; no composite mutation
- [x] Manual authoritative refresh after mutation success or network ambiguity
- [x] Strict persisted-currency evidence validation; no read-side normalization of malformed currency
- [x] Local/Test/CI guarded delivery with persistence/session/capability/feature gates
- [x] Vue/Inertia setup workspace and executable SQLite regression
- [x] Initial exact-head integrity defect discovered by CI and corrected before merge
- [x] Final exact engineering head `fbe8e91df756855d38b8c6656b17f27b4cc32585` fully qualified
- [x] Sprint161 regression run `34853239912` successful
- [x] Complete surfaced final exact-head PR-triggered matrix successful
- [x] Repository-native exact-head Product Owner merge authority successful
- [x] Sprint161 engineering PR #740 squash merged
- [x] Sprint161 engineering squash `33080c0b5f5c6f66e9994ad7f05ba78dea241294`
- [x] Engineering merge verified as exactly one commit / 11 paths over post-Sprint160 canonical main
- [x] Post-engineering machine-readable operational NO-GO verification completed
- [x] Sprint161 workflow reconciled to successor-compatible historical regression ownership
- [x] Sprint161 canonical six-path reconciliation materialized

## Sprint161 engineering evidence

- Objective: `POS_CATALOG_INVENTORY_SETUP_WORKSPACE`
- Parent canonical checkpoint: `92d932020ef95bdc26460a4944841d141d6fad5b`
- Initial disqualified head: `6cbb218d204e7e84ff6701328f6d884ccf5699ec`
- Final engineering head: `fbe8e91df756855d38b8c6656b17f27b4cc32585`
- Engineering PR: #740
- Engineering envelope: 11 paths
- Engineering envelope SHA-256: `66d7c616fbe8ae0e6c3c262fc8054db26bcbaa67e07ed77000363041b056f613`
- Engineering squash: `33080c0b5f5c6f66e9994ad7f05ba78dea241294`
- Reconciliation envelope: six paths
- Reconciliation envelope SHA-256: `fd24a20017a13eca06d93ade217b6c0a68db07c205b218b8d9c201122c6e7ccc`
- Operational mutation: `NOT_PERFORMED`
- Final Shift Close runtime allowlist: Local/Test/CI only
- Migration #27: `NOT_EXECUTED`
- Permission provisioning: `NONE`
- Feature activation: `INACTIVE`

## Next bounded engineering

### Sprint162 — bounded discovery after Sprint161 closure

The next engineering activity must:

- identify the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap from canonical post-Sprint161;
- inspect current source/contracts/regressions before creating a new invariant owner;
- prefer product value over additional abstraction where external operational prerequisites remain the true blocker;
- preserve canonical shift/sale/catalog/inventory/void/refund/authorization owners rather than duplicating them;
- distinguish one-time opening inventory from any future stock-adjustment/restock requirement and never infer new mutation authority from Sprint161;
- freeze the smallest meaningful bounded source envelope before implementation;
- remain fail-closed, deny-by-default, tenant-isolated, and exact-context scoped;
- qualify exact head before merge;
- preserve the six-section sprint documentation rule.

No Sprint162 implementation is preselected.

## Operational blockers / separate authority required

- [ ] Qualify/select and persist a real non-synthetic durable activation target — current selection `null`
- [ ] Produce real selected-target DB-binding evidence — `NONE`
- [ ] Execute migration #27 — `NOT_EXECUTED`
- [ ] Execute selected-target-bound permission provisioning — `NONE`
- [ ] Dispatch trusted capability-evidence producer — `NOT_PERFORMED`
- [ ] Produce real target-bound durable-runtime capability evidence — `NONE`
- [ ] Dispatch trusted dependency-envelope evidence producer — `NOT_PERFORMED`
- [ ] Produce real target-bound dependency-envelope evidence — `NONE`
- [ ] Widen selected durable runtime class in Final Shift Close runtime allowlist — not authorized
- [ ] Execute Final Shift Close feature activation — `INACTIVE`
- [ ] Grant deployment authority — `NOT_GRANTED`
- [ ] Authorize Technical Preview activation — `NOT_AUTHORIZED`
- [ ] Authorize Production activation — `NOT_AUTHORIZED`
- [ ] Activate updater — `INACTIVE`

Machine-readable operational authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

## Maintenance rule

At every material sprint closure: reconcile `PROJECT_MANIFEST.md`, README, TASKS, ROADMAP, and CHANGELOG; preserve detailed evidence in workflows/contracts/Git history; never label source-published work as deployed or activated without operational evidence; and keep preservation workflows successor-compatible.

Author by Lab | zefry
