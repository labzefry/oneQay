# oneQay Tasks

**Current engineering checkpoint:** Sprint158 closed
**Canonical engineering commit:** `d6eb8f7f359584130deea9ec0ed3572add3c05aa`
**Canonical status authority:** `PROJECT_MANIFEST.md`

This file separates completed source engineering, next bounded engineering, and operational actions that remain separately gated.

## Completed through Sprint158

- [x] Core modular-monolith architecture and repository governance foundation
- [x] Tenant-context-first and deny-by-default authorization foundations
- [x] Versioned REST / correlation / idempotency governance foundations
- [x] Final Shift Close source/readiness chain through Sprint155 without operational activation
- [x] Read-only POS operational sales reporting — Sprint156
- [x] Operational POS cashier sale-entry workspace — Sprint157
- [x] Operational POS shift-start workspace — Sprint158
- [x] Exact tenant + organization + outlet + device shift-start scope
- [x] Existing `pos.shift.open` permission reuse
- [x] Existing `pos.shift.opening-cash.record` permission reuse
- [x] Canonical `/pos/shifts/open` mutation owner preserved
- [x] Canonical `/pos/shifts/opening-cash` mutation owner preserved
- [x] Resumable shift-open → opening-cash → cashier-ready flow
- [x] Partial-completion state preserved without opening a second shift
- [x] No hidden automatic mutation retry
- [x] Currency/scale-aware opening-cash input with server Money authority preserved
- [x] Guarded Local/Test/CI delivery with persistence, session, capability, and explicit feature gates
- [x] Vue/Inertia shift-start workspace
- [x] Executable SQLite exact-scope/resumability/fail-closed regression
- [x] Sprint158 engineering PR #734 squash merged
- [x] Sprint158 complete exact-head PR-triggered matrix successful
- [x] Sprint158 regression run `34816888058` successful
- [x] M7.1 run `34816888381` successful
- [x] Governance run `34816888368` successful
- [x] PHP Foundation run `34816887952` successful
- [x] Sprint96/Sprint97/Sprint126/Sprint148/Sprint156/Sprint157 regressions successful on the exact head
- [x] Repository-native exact-head Product Owner merge authority successful
- [x] Sprint158 engineering squash `d6eb8f7f359584130deea9ec0ed3572add3c05aa`
- [x] Engineering merge verified as exactly one commit / 11 paths over post-Sprint157 canonical main
- [x] Post-engineering machine-readable operational NO-GO verification completed
- [x] Sprint158 workflow reconciled to successor-compatible historical regression ownership
- [x] Sprint158 canonical six-path reconciliation materialized

## Sprint158 engineering evidence

- Objective: `POS_SHIFT_START_WORKSPACE`
- Parent canonical checkpoint: `700f2133032047d213d38e2e0317641599831a9f`
- Final engineering head: `1e2a0ae959a88870ae4728f46f07b08ae0c08a2c`
- Engineering PR: #734
- Engineering envelope: 11 paths
- Engineering envelope SHA-256: `3828b5914b64b4862ce4d39ff037261b796c232e5ac7c6fb001913a096ac1666`
- Engineering squash: `d6eb8f7f359584130deea9ec0ed3572add3c05aa`
- Reconciliation envelope: six paths
- Reconciliation envelope SHA-256: `0257dde337eee65e156c49f59b54a61506b47866babb2c68a8bcc3a1a2e3321f`
- Operational mutation: `NOT_PERFORMED`
- Final Shift Close runtime allowlist: Local/Test/CI only
- Migration #27: `NOT_EXECUTED`
- Permission provisioning: `NONE`
- Feature activation: `INACTIVE`

## Next bounded engineering

### Sprint159 — bounded discovery after Sprint158 closure

The next engineering activity must:

- identify the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap from canonical post-Sprint158;
- inspect existing source/contracts/regressions before creating a new invariant owner;
- prefer product value over additional abstraction where external operational prerequisites remain the true blocker;
- reuse canonical shift, cashier, sale, catalog, stock, and authorization owners rather than duplicating them;
- freeze the smallest meaningful bounded source envelope before implementation;
- remain fail-closed, deny-by-default, tenant-isolated, and exact-context scoped;
- qualify exact head before merge;
- preserve the six-section sprint documentation rule.

No Sprint159 implementation is preselected.

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
