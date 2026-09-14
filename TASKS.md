# oneQay Tasks

**Current engineering checkpoint:** Sprint157 closed
**Canonical engineering commit:** `b4d21b208a0580f4b40565b028dd6aed9bb190b8`
**Canonical status authority:** `PROJECT_MANIFEST.md`

This file separates completed source engineering, next bounded engineering, and operational actions that remain separately gated.

## Completed through Sprint157

- [x] Core modular-monolith architecture and repository governance foundation
- [x] Tenant-context-first and deny-by-default authorization foundations
- [x] Versioned REST / correlation / idempotency governance foundations
- [x] POS shift/register, sale/payment/receipt, catalog, inventory, cash-variance, adjudication, and reviewer-control foundations
- [x] Final Shift Close source/readiness chain through Sprint155 without operational activation
- [x] Read-only POS operational sales reporting — Sprint156
- [x] Operational POS cashier sale-entry workspace — Sprint157
- [x] Cashier tenant + organization + outlet + device scope preservation
- [x] Exact-device active-shift readiness
- [x] Active positive-stock catalog filtering
- [x] Existing deny-by-default `pos.sale.complete` permission reuse
- [x] Canonical `/pos/sales` mutation path reuse; no second transaction engine
- [x] Currency + currency-scale safe cart boundary
- [x] CASH and MANUAL_EXTERNAL tender UI validation with server authority preserved
- [x] Explicit no-auto-retry behavior for network failure
- [x] Guarded Local/Test/CI cashier delivery with explicit feature arming
- [x] Vue/Inertia cashier workspace
- [x] Executable SQLite cashier isolation/readiness/fail-closed regression
- [x] Sprint157 engineering PR #732 squash merged
- [x] Sprint157 complete exact-head PR-triggered matrix successful
- [x] Sprint157 regression run `34815027880` successful
- [x] M7.1 run `34815027865` successful
- [x] Governance run `34815027920` successful
- [x] PHP Foundation run `34815027969` successful
- [x] Sprint96/Sprint97/Sprint126/Sprint148/Sprint156 preservation regressions successful on the exact head
- [x] Repository-native exact-head Product Owner merge authority successful
- [x] Sprint157 engineering squash `b4d21b208a0580f4b40565b028dd6aed9bb190b8`
- [x] Engineering merge verified as exactly one commit / 12 paths over post-Sprint156 canonical main
- [x] Post-engineering machine-readable operational NO-GO verification completed
- [x] Sprint157 workflow reconciled to successor-compatible historical regression ownership
- [x] Sprint157 canonical six-path reconciliation materialized

## Sprint157 engineering evidence

- Objective: `POS_CASHIER_SALE_ENTRY_WORKSPACE`
- Parent canonical checkpoint: `1255fd1a310792c50e174465aa91417af23bd47e`
- Final engineering head: `0ff14cf95aa54cd798fe5d1b5611c2890757e5b3`
- Engineering PR: #732
- Engineering envelope: 12 paths
- Engineering envelope SHA-256: `f363bbfff9b1a52479c0f6d76e7cefe4b14ac89c597b2cd7894713e34bcc2f5b`
- Engineering squash: `b4d21b208a0580f4b40565b028dd6aed9bb190b8`
- Reconciliation envelope: six paths
- Reconciliation envelope SHA-256: `4393c47067856f6d426cc2ce3f976bda78a72c13adaedb47ff53cc93f2c4ca1c`
- Operational mutation: `NOT_PERFORMED`
- Final Shift Close runtime allowlist: Local/Test/CI only
- Migration #27: `NOT_EXECUTED`
- Permission provisioning: `NONE`
- Feature activation: `INACTIVE`

## Next bounded engineering

### Sprint158 — bounded discovery after Sprint157 closure

The next engineering activity must:

- identify the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap from canonical post-Sprint157;
- inspect existing source/contracts/regressions before creating a new invariant owner;
- prefer product value over additional abstraction where external operational prerequisites remain the true blocker;
- preserve canonical sale/catalog/stock/authorization owners rather than duplicating them;
- freeze the smallest meaningful bounded source envelope before implementation;
- remain fail-closed, deny-by-default, tenant-isolated, and exact-context scoped;
- qualify exact head before merge;
- preserve the six-section sprint documentation rule.

No Sprint158 implementation is preselected.

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
