# oneQay Tasks

**Current engineering checkpoint:** Sprint160 closed
**Canonical engineering commit:** `e6ef6e77d8a2d0dea16d7c17dde78bece904b78b`
**Canonical status authority:** `PROJECT_MANIFEST.md`

This file separates completed source engineering, next bounded engineering, and operational actions that remain separately gated.

## Completed through Sprint160

- [x] Core modular-monolith architecture and repository governance foundation
- [x] Tenant-context-first and deny-by-default authorization foundations
- [x] Versioned REST / correlation / idempotency governance foundations
- [x] POS shift/register, sale/payment/receipt, catalog, inventory, full-sale void/refund, cash-variance, adjudication, and reviewer-control foundations
- [x] Final Shift Close source/readiness chain through Sprint155 without operational activation
- [x] Read-only POS operational sales reporting — Sprint156
- [x] Operational POS cashier sale-entry workspace — Sprint157
- [x] Operational POS shift-start workspace — Sprint158
- [x] Operational POS sale correction workspace — Sprint159
- [x] Immutable POS sale-history and receipt-detail workspace — Sprint160
- [x] Latest-50 sale history bounded in exact tenant + organization + outlet scope
- [x] Exact canonical `sale-<24 hex>` lookup without foreign-scope receipt disclosure
- [x] Existing deny-by-default `pos.reporting.sales-summary.view` permission reused
- [x] Existing `PosOperationalReportingServiceProvider` extended; no global-provider duplication
- [x] Immutable receipt lines read from canonical `oneqay_pos_sale_lines`
- [x] No mutable current catalog display-name reconstruction in historical receipts
- [x] Receipt line sequence / quantity / multiplication / sum-to-total validation
- [x] Currency and currency-scale integrity validation
- [x] Void/refund amount, tender, sequence, organization, and outlet evidence validation
- [x] Legitimate legacy nullable shift evidence preserved
- [x] Atomic monetary values delivered as strings for browser precision safety
- [x] Guarded Local/Test/CI history delivery with explicit default-off feature arming
- [x] Vue/Inertia sale-history/detail workspace
- [x] Executable SQLite scope/exact-lookup/receipt-integrity/correction-evidence/fail-closed regression
- [x] Sprint160 engineering PR #738 squash merged
- [x] Sprint160 complete surfaced exact-head PR-triggered matrix successful
- [x] Sprint160 regression run `34847797273` successful
- [x] M7.1 run `34847797253` successful
- [x] Governance run `34847797404` successful
- [x] PHP Foundation run `34847797433` successful
- [x] Sprint126/Sprint148/Sprint156/Sprint157/Sprint158/Sprint159 preservation regressions successful on exact head
- [x] Repository-native exact-head Product Owner merge authority successful
- [x] Sprint160 engineering squash `e6ef6e77d8a2d0dea16d7c17dde78bece904b78b`
- [x] Engineering merge verified as exactly one commit / 10 paths over post-Sprint159 canonical main
- [x] Post-engineering machine-readable operational NO-GO verification completed
- [x] Sprint160 workflow reconciled to successor-compatible historical regression ownership
- [x] Sprint160 canonical six-path reconciliation materialized

## Sprint160 engineering evidence

- Objective: `POS_SALE_HISTORY_DETAIL_WORKSPACE`
- Parent canonical checkpoint: `d4931eb7822d844cf74b3a60e593c35b00b1cfce`
- Final engineering head: `d159aa26c748c5a624f0f71fe6c135f0d4f36be9`
- Engineering PR: #738
- Engineering envelope: 10 paths
- Engineering envelope SHA-256: `f60bd3698cbc28cfccdf8b79c446e5e138203246afdb16aaea1c5637aa327181`
- Engineering squash: `e6ef6e77d8a2d0dea16d7c17dde78bece904b78b`
- Reconciliation envelope: six paths
- Reconciliation envelope SHA-256: `388c671587b1d0e21206260c5f0003fb215d606df494eaa528b1e6d839de7847`
- Operational mutation: `NOT_PERFORMED`
- Final Shift Close runtime allowlist: Local/Test/CI only
- Migration #27: `NOT_EXECUTED`
- Permission provisioning: `NONE`
- Feature activation: `INACTIVE`

## Next bounded engineering

### Sprint161 — bounded discovery after Sprint160 closure

The next engineering activity must:

- identify the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap from canonical post-Sprint160;
- inspect existing source/contracts/regressions before creating a new invariant owner;
- prefer product value over additional abstraction where external operational prerequisites remain the true blocker;
- preserve canonical shift/sale/catalog/stock/reporting/history/void/refund/authorization owners rather than duplicating them;
- consider genuine remaining operational product gaps such as inventory visibility, navigation/workspace integration, or other missing business-completeness surfaces only after live source confirms absence and non-duplication;
- freeze the smallest meaningful bounded source envelope before implementation;
- remain fail-closed, deny-by-default, tenant-isolated, and exact-context scoped;
- qualify exact head before merge;
- preserve the six-section sprint documentation rule.

No Sprint161 implementation is preselected.

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
