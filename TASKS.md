# oneQay Tasks

**Current engineering checkpoint:** Sprint156 engineering merged; post-Sprint156 canonical reconciliation
**Canonical engineering commit:** `259cc00037ee0d3fb909cbcf2c87d39ffb26f9b9`
**Canonical status authority:** `PROJECT_MANIFEST.md`

This file separates completed source engineering, closure reconciliation, next bounded engineering, and operational actions that remain separately gated.

## Completed through Sprint156 engineering

- [x] Core modular-monolith architecture and repository governance foundation
- [x] Tenant-context-first and deny-by-default authorization foundations
- [x] Versioned REST / correlation / idempotency governance foundations
- [x] POS shift/register, sale/payment/receipt, catalog, cash-variance, adjudication, and reviewer-control foundations
- [x] Final Shift Close source/readiness chain through Sprint155 without operational activation
- [x] Read-only POS operational sales reporting — Sprint156
- [x] Tenant + organization + outlet reporting isolation
- [x] Currency + currency-scale boundary preservation
- [x] Deny-by-default reporting permission `pos.reporting.sales-summary.view`
- [x] Guarded Local/Test/CI reporting delivery with explicit feature arming
- [x] Vue/Inertia operational sales-summary surface
- [x] Executable SQLite reporting regression
- [x] Global provider registry restored to canonical state
- [x] Reporting composed through existing POS composition root
- [x] Stale historical regression ownership narrowed while substantive regressions remain active
- [x] Sprint96 runtime and Sprint97 HTTP regression successor compatibility
- [x] Sprint148 exact-head concurrency isolation and substantive evidence-binding regression preservation
- [x] Sprint156 engineering PR #730 squash merged
- [x] Sprint156 exact-head PR-triggered matrix successful
- [x] Sprint156 reporting regression run `34813484159` successful
- [x] M7.1 run `34813484204` successful
- [x] Governance run `34813484278` successful
- [x] PHP Foundation run `34813484276` successful
- [x] Sprint96 run `34813484081` successful
- [x] Sprint97 run `34813484181` successful
- [x] Sprint126 run `34813484356` successful
- [x] Sprint148 run `34813484102` successful
- [x] Repository-native exact-head Product Owner merge authority successful
- [x] Sprint156 engineering squash `259cc00037ee0d3fb909cbcf2c87d39ffb26f9b9`
- [x] Engineering merge verified as exactly one commit / 24 paths over post-Sprint155 canonical main
- [x] Post-engineering machine-readable operational NO-GO verification completed

## Sprint156 engineering evidence

- Objective: `POS_OPERATIONAL_SALES_REPORTING`
- Parent canonical checkpoint: `4f939acd6cbcc3c49a45cba549a082c789cabd44`
- Final engineering head: `5e460d1c7c5174cc831106ade3e3fa6309acba4d`
- Engineering PR: #730
- Engineering envelope: 24 paths
- Engineering envelope SHA-256: `34c6dab2c898ddd9133aaa6d5413ca7b345127020d8f04fe54f861a4d1a5e79c`
- Engineering squash: `259cc00037ee0d3fb909cbcf2c87d39ffb26f9b9`
- Reconciliation envelope: six paths
- Reconciliation envelope SHA-256: `adba5b23ef33aeb360ebb4090b3f848fc2a3704807a60026c1344b2e0d1a54f4`
- Operational mutation: `NOT_PERFORMED`
- Runtime allowlist: Local/Test/CI only
- Migration #27: `NOT_EXECUTED`
- Permission provisioning: `NONE`
- Feature activation: `INACTIVE`

## Current reconciliation work

- [x] Create reconciliation branch from exact engineering squash `259cc00037ee0d3fb909cbcf2c87d39ffb26f9b9`
- [x] Convert Sprint156 workflow from active full-envelope lock to historical successor-compatible regression owner
- [x] Preserve reporting executable regression, UI build, scoped authorization, read-only semantics, and operational NO-GO in the historical workflow
- [x] Freeze six-path reconciliation envelope and hash
- [x] Reconcile `PROJECT_MANIFEST.md`
- [x] Reconcile `README.md`
- [x] Reconcile `CHANGELOG.md`
- [x] Reconcile `TASKS.md`
- [x] Reconcile `ROADMAP.md`
- [ ] Open exact six-path reconciliation PR
- [ ] Require all exact-head reconciliation CI successful
- [ ] Obtain repository-native Product Owner exact-head authority
- [ ] Complete final race and squash merge reconciliation PR
- [ ] Verify engineering-to-reconciliation delta is exactly one commit / six paths
- [ ] Verify `PROJECT_MANIFEST.md` references engineering squash `259cc00037ee0d3fb909cbcf2c87d39ffb26f9b9`, not reconciliation SHA
- [ ] Verify machine-readable operational NO-GO remains unchanged
- [ ] Declare Sprint156 CLOSED

## Next bounded engineering

### Sprint157 — bounded discovery after Sprint156 closure

After post-Sprint156 reconciliation closes, the next engineering activity must:

- identify the smallest material non-duplicative production-readiness or business-completeness gap from canonical post-Sprint156;
- inspect existing source/contracts/regressions before creating a new invariant owner;
- prefer product value and P0/P1 readiness work over further abstraction where external operational prerequisites remain the true blocker;
- freeze the smallest meaningful bounded source envelope before implementation;
- remain fail-closed, deny-by-default, and tenant-isolated;
- qualify exact head before merge;
- preserve the six-section sprint documentation rule.

No Sprint157 implementation is preselected.

## Operational blockers / separate authority required

- [ ] Qualify/select and persist a real non-synthetic durable activation target — current selection `null`
- [ ] Produce real selected-target DB-binding evidence — `NONE`
- [ ] Execute migration #27 — `NOT_EXECUTED`
- [ ] Execute selected-target-bound permission provisioning — `NONE`
- [ ] Dispatch trusted capability-evidence producer — `NOT_PERFORMED`
- [ ] Produce real target-bound durable-runtime capability evidence — `NONE`
- [ ] Dispatch trusted dependency-envelope evidence producer — `NOT_PERFORMED`
- [ ] Produce real target-bound dependency-envelope evidence — `NONE`
- [ ] Materialize/qualify any still-required operational activation transport under separate authority
- [ ] Widen selected durable runtime class in runtime allowlist — not authorized
- [ ] Execute Final Shift Close feature activation — `INACTIVE`
- [ ] Grant deployment authority — `NOT_GRANTED`
- [ ] Authorize Technical Preview activation — `NOT_AUTHORIZED`
- [ ] Authorize Production activation — `NOT_AUTHORIZED`
- [ ] Activate updater — `INACTIVE`

Machine-readable operational authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

## Maintenance rule

At every material sprint closure: reconcile `PROJECT_MANIFEST.md`, README, TASKS, ROADMAP, and CHANGELOG; preserve detailed evidence in workflows/contracts/Git history; never label source-published work as deployed or activated without operational evidence; and keep preservation workflows successor-compatible.

Author by Lab | zefry
