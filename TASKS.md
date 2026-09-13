# oneQay Tasks

**Current engineering checkpoint:** Sprint155 engineering merged; post-Sprint155 reconciliation  
**Canonical engineering commit:** `1e84e1b3e07915a1d20b56fe768b0d1454f901d2`  
**Canonical status authority:** `PROJECT_MANIFEST.md`

This file separates completed source engineering, next bounded engineering, and operational actions that remain separately gated.

## Completed through Sprint155 engineering

- [x] Core modular-monolith architecture and repository governance foundation
- [x] Tenant-context-first and deny-by-default authorization foundations
- [x] Versioned REST / correlation / idempotency governance foundations
- [x] POS shift/register, sale/payment/receipt, catalog, cash-variance, adjudication, and reviewer-control foundations
- [x] Final Shift Close migration #27 source materialization; execution remains `NOT_EXECUTED`
- [x] Durable-runtime readiness and exact selected-target identity readiness — Sprint110–Sprint111
- [x] Attestation/ingestion, target-selection persistence and migration DB-binding readiness — Sprint113–Sprint118
- [x] Runtime DB-binding/materialization control plane and successor compatibility — Sprint119–Sprint129
- [x] Canonical runtime-control-plane and HTTP/throttle identity hardening — Sprint130–Sprint147
- [x] Target-bound durable-runtime capability-evidence identity qualification — Sprint148
- [x] Trusted target-bound capability-evidence producer source materialization — Sprint149
- [x] Full nine-component selected-runtime dependency-envelope qualification source — Sprint150
- [x] Target-bound dependency-envelope evidence deterministic construction source foundation — Sprint151
- [x] Permission provisioning selected-target database-binding source hardening — Sprint152
- [x] Trusted dependency-envelope evidence producer source materialization — Sprint153
- [x] Final Shift Close feature-activation executor source foundation — Sprint154
- [x] Final Shift Close deterministic source-only activation transport handoff envelope — Sprint155
- [x] Sprint155 regression registered in existing M7.1 application harness
- [x] Sprint155 engineering PR #728 squash merged
- [x] Sprint155 exact-head qualification — 36/36 successful
- [x] Sprint155 PHP Foundation run `34774606244` successful
- [x] Sprint155 M7.1 run `34774606266` successful and explicitly executed the new regression
- [x] Sprint155 Product Owner authority run `34775351008` successful
- [x] Sprint155 engineering squash `1e84e1b3e07915a1d20b56fe768b0d1454f901d2`
- [x] Sprint155 post-engineering merge delta verified as exactly one commit / three paths
- [x] Sprint155 post-engineering machine-readable NO-GO verification completed

## Sprint155 engineering evidence

- Objective: `FINAL_SHIFT_CLOSE_FEATURE_ACTIVATION_TRANSPORT_SOURCE_FOUNDATION`
- Parent canonical checkpoint: `056d0300af925c9e8adf04a11a107cc4f5fde196`
- Final engineering head: `4871e0ade60e8ac5e6f44b2bc27f0319ca150e63`
- Engineering envelope: three paths
- Engineering envelope SHA-256: `29619b928a422615647184c5316d9679dd4c4d582d759e89e8704e335ed982cb`
- Reconciliation envelope: six paths
- Reconciliation envelope SHA-256: `323efb8b04badda3874aa7542285499b7be7b8df139cc86fd43b294aac7f8a38`
- Activation executor source foundation: `MATERIALIZED_SOURCE_ONLY`
- Source-only activation transport handoff envelope: materialized
- Concrete configuration-mutation transport: `NOT_IMPLEMENTED`
- Dispatchable feature-activation executor: `NOT_IMPLEMENTED`
- Network / executor dispatch: `NOT_PERFORMED`
- Runtime allowlist change: `NOT_IMPLEMENTED`
- Feature activation: `INACTIVE`

## Current reconciliation work

- [x] Create reconciliation branch from exact engineering squash `1e84e1b3e07915a1d20b56fe768b0d1454f901d2`
- [x] Add non-operational Sprint155 source-contract preservation workflow
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
- [ ] Verify manifest still references engineering SHA, not reconciliation SHA
- [ ] Verify machine-readable operational NO-GO remains unchanged
- [ ] Declare Sprint155 CLOSED

## Next bounded engineering

### Sprint156 — bounded discovery after Sprint155 closure

After post-Sprint155 reconciliation closes, the next engineering activity must:

- identify the smallest material non-duplicative production-readiness gap from canonical post-Sprint155;
- prove the invariant is not already owned by historical regressions, source contracts, or current foundations;
- prioritize genuine durable-runtime prerequisites without operational mutation;
- freeze the smallest meaningful bounded source envelope before implementation;
- remain fail-closed and deny-by-default;
- qualify exact head before merge;
- preserve the six-section sprint documentation rule.

No Sprint156 implementation is preselected.

## Operational blockers / separate authority required

- [ ] Qualify/select and persist a real non-synthetic durable activation target — current selection `null`
- [ ] Produce real selected-target DB-binding evidence — `NONE`
- [ ] Execute migration #27 — `NOT_EXECUTED`
- [ ] Execute selected-target-bound permission provisioning — `NONE`
- [ ] Dispatch trusted capability-evidence producer — `NOT_PERFORMED`
- [ ] Produce real target-bound durable-runtime capability evidence — `NONE`
- [ ] Dispatch trusted dependency-envelope evidence producer — `NOT_PERFORMED`
- [ ] Produce real target-bound dependency-envelope evidence — `NONE`
- [ ] Materialize a dispatchable selected-target-bound feature-activation executor — `NOT_IMPLEMENTED`
- [ ] Materialize and qualify concrete configuration-mutation transport — `NOT_IMPLEMENTED`
- [ ] Widen selected durable runtime class in runtime allowlist — `NOT_IMPLEMENTED`
- [ ] Execute Final Shift Close feature activation — `INACTIVE`
- [ ] Grant deployment authority — `NOT_GRANTED`
- [ ] Authorize Technical Preview activation — `NOT_AUTHORIZED`
- [ ] Authorize Production activation — `NOT_AUTHORIZED`
- [ ] Activate updater — `INACTIVE`

Machine-readable operational authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

## Maintenance rule

At every material sprint closure: reconcile `PROJECT_MANIFEST.md`, README, TASKS, ROADMAP, and CHANGELOG; preserve detailed evidence in per-sprint evidence/contracts/workflows/Git history where materialized; never label source-published work as deployed or activated without operational evidence; and keep preservation workflows successor-compatible.

Author by Lab | zefry
