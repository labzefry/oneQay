# oneQay Tasks

**Current engineering checkpoint:** Sprint159 closed
**Canonical engineering commit:** `e4e6a0f55bbb3b6fd4126df66f4815e281ad0dc4`
**Canonical status authority:** `PROJECT_MANIFEST.md`

This file separates completed source engineering, next bounded engineering, and operational actions that remain separately gated.

## Completed through Sprint159

- [x] Core modular-monolith architecture and repository governance foundation
- [x] Tenant-context-first and deny-by-default authorization foundations
- [x] Versioned REST / correlation / idempotency governance foundations
- [x] POS shift/register, sale/payment/receipt, catalog, inventory, full-sale void/refund, cash-variance, adjudication, and reviewer-control foundations
- [x] Final Shift Close source/readiness chain through Sprint155 without operational activation
- [x] Read-only POS operational sales reporting — Sprint156
- [x] Operational POS cashier sale-entry workspace — Sprint157
- [x] Operational POS shift-start workspace — Sprint158
- [x] Operational POS sale correction workspace — Sprint159
- [x] Correction tenant + organization + outlet scope preservation
- [x] Original sale device visibility without artificial same-device correction restriction
- [x] Original-shift active-state eligibility preservation
- [x] Existing deny-by-default `pos.sale.void` and `pos.sale.refund` permission reuse
- [x] Existing canonical void and CASH-refund mutation path reuse; no second correction engine
- [x] Full-sale evidence amount/currency/scale/tender integrity validation
- [x] Explicit `COMPLETED → VOIDED → REFUNDED` CASH correction sequencing
- [x] MANUAL_EXTERNAL void → external-settlement boundary
- [x] Explicit no-auto-retry and uncertain-state refresh behavior
- [x] Guarded Local/Test/CI correction delivery with explicit feature arming
- [x] Vue/Inertia correction workspace
- [x] Executable SQLite scope/permission/state/evidence/fail-closed regression
- [x] Sprint159 engineering PR #736 squash merged
- [x] Sprint159 complete surfaced exact-head PR-triggered matrix successful
- [x] Sprint159 regression run `34818568216` successful
- [x] M7.1 run `34818568151` successful
- [x] Governance run `34818568451` successful
- [x] PHP Foundation run `34818568650` successful
- [x] Sprint96/Sprint97/Sprint126/Sprint148/Sprint156/Sprint157/Sprint158 preservation regressions successful on exact head
- [x] Repository-native exact-head Product Owner merge authority successful
- [x] Sprint159 engineering squash `e4e6a0f55bbb3b6fd4126df66f4815e281ad0dc4`
- [x] Engineering merge verified as exactly one commit / 11 paths over post-Sprint158 canonical main
- [x] Post-engineering machine-readable operational NO-GO verification completed
- [x] Sprint159 workflow reconciled to successor-compatible historical regression ownership
- [x] Sprint159 canonical six-path reconciliation materialized

## Sprint159 engineering evidence

- Objective: `POS_SALE_CORRECTION_WORKSPACE`
- Parent canonical checkpoint: `6a23eefcc10a60d306d969834c594c73a7b7d2bf`
- Final engineering head: `475c4d8fb9a1e17467e71c77fb837351d77e48e2`
- Engineering PR: #736
- Engineering envelope: 11 paths
- Engineering envelope SHA-256: `6a5f49136334f99c182220a7db89a7869611be8a5b65a6cd3fe4c73fcc40cf00`
- Engineering squash: `e4e6a0f55bbb3b6fd4126df66f4815e281ad0dc4`
- Reconciliation envelope: six paths
- Reconciliation envelope SHA-256: `e7ebee58804975f9d64bc3061c13dccd4c1212877fe73cb75072163e101070dc`
- Operational mutation: `NOT_PERFORMED`
- Final Shift Close runtime allowlist: Local/Test/CI only
- Migration #27: `NOT_EXECUTED`
- Permission provisioning: `NONE`
- Feature activation: `INACTIVE`

## Next bounded engineering

### Sprint160 — bounded discovery after Sprint159 closure

The next engineering activity must:

- identify the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap from canonical post-Sprint159;
- inspect existing source/contracts/regressions before creating a new invariant owner;
- prefer product value over additional abstraction where external operational prerequisites remain the true blocker;
- preserve canonical shift/sale/catalog/stock/void/refund/authorization owners rather than duplicating them;
- freeze the smallest meaningful bounded source envelope before implementation;
- remain fail-closed, deny-by-default, tenant-isolated, and exact-context scoped;
- qualify exact head before merge;
- preserve the six-section sprint documentation rule.

No Sprint160 implementation is preselected.

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
