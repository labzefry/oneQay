# oneQay Tasks

**Current engineering checkpoint:** post-Sprint151 reconciliation
**Canonical engineering commit:** `68b8362f326e56cfec478f0275b6d29ed0f54dec`
**Canonical status authority:** `PROJECT_MANIFEST.md`

This file separates completed source engineering, next bounded engineering, and operational actions that remain unauthorized.

## Completed through Sprint151

- [x] Core modular-monolith architecture and repository governance foundation
- [x] Tenant-context-first and deny-by-default authorization foundations
- [x] Versioned REST / correlation / idempotency governance foundations
- [x] POS shift/register, sale/payment/receipt, and catalog foundations
- [x] JRN-010 expected-cash, sale-to-shift, variance, explanation/adjudication, maker-checker, and reviewer-control chain
- [x] Final Shift Close migration #27 source materialization; execution remains unauthorized
- [x] Durable-runtime readiness and exact selected-target identity readiness — Sprint110–Sprint111
- [x] Source-only attestation producer/ingestion, target-selection persistence and binding readiness — Sprint113–Sprint117
- [x] Canonical runtime-control-plane and HTTP/throttle identity hardening through Sprint147
- [x] Target-bound durable-runtime capability-evidence identity qualification — Sprint148
- [x] Trusted target-bound capability-evidence producer source materialization — Sprint149
- [x] Full nine-component selected-runtime dependency-envelope qualification source — Sprint150
- [x] Target-bound dependency-envelope evidence construction source foundation — Sprint151
- [x] Sprint151 PR #720 squash merged
- [x] Sprint151 exact-head PR qualification — 36/36 successful
- [x] Sprint151 repository-native Product Owner merge-authority run `34764011476` successful
- [x] Sprint151 canonical engineering commit `68b8362f326e56cfec478f0275b6d29ed0f54dec`
- [x] Sprint151 post-merge engineering NO-GO verification completed
- [x] Sprint151 provider runtime allowlist preserved as `local/test/ci`

## Sprint151 closure evidence

- Objective: `DURABLE_RUNTIME_TARGET_BOUND_DEPENDENCY_ENVELOPE_EVIDENCE_SOURCE_FOUNDATION`
- Final engineering head: `f41e32bc44e0ee0346f5efd15d54f6af21b8a19a`
- Engineering envelope: five paths
- Engineering envelope SHA-256: `ef000ec9172dcee1f08a6c8ea9149fb957a307e7929eaaa42c5d4b08bad94c54`
- Reconciliation envelope: six paths
- Reconciliation envelope SHA-256: `82d72910aaa4409113cfbb8b2b7f326daa51c205d7a6bfefa90ad5624604fb25`
- Dependency-evidence source foundation: `MATERIALIZED_SOURCE_ONLY`
- Dispatchable dependency-evidence producer: `NOT_IMPLEMENTED`
- Real dependency-envelope evidence: `NONE`
- Runtime allowlist change: `NOT_IMPLEMENTED`

## Next bounded engineering

### Sprint152 — bounded discovery after Sprint151 closure

After post-Sprint151 reconciliation closes, the next engineering activity must:

- identify the smallest material non-duplicative production-readiness gap from canonical post-Sprint151;
- prove the invariant is not already owned by historical regressions, source contracts, or current foundations;
- prioritize genuine durable-runtime readiness prerequisites without operational mutation;
- freeze a bounded source envelope before implementation;
- remain fail-closed and deny-by-default;
- qualify exact head before merge;
- preserve the six-field sprint documentation rule.

No Sprint152 implementation is preselected.

## Operational blockers / separate authority required

- [ ] Qualify/select and persist a real non-synthetic durable activation target — current selection `null`
- [ ] Dispatch trusted capability-evidence producer — `NOT_PERFORMED`
- [ ] Produce real target-bound durable-runtime capability evidence — `NONE`
- [ ] Materialize a separately qualified dispatchable dependency-envelope evidence producer — `NOT_IMPLEMENTED`
- [ ] Produce real target-bound dependency-envelope evidence — `NONE`
- [ ] Execute migration #27 — `NOT_EXECUTED`
- [ ] Provision Final Shift Close permission — `NONE`
- [ ] Widen selected durable runtime class in runtime allowlist — `NOT_IMPLEMENTED`
- [ ] Activate Final Shift Close feature — `INACTIVE`
- [ ] Grant deployment authority — `NOT_GRANTED`
- [ ] Authorize Technical Preview activation — `NOT_AUTHORIZED`
- [ ] Authorize Production activation — `NOT_AUTHORIZED`
- [ ] Activate updater — `INACTIVE`

Machine-readable operational authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

## Maintenance rule

At every material sprint closure: reconcile `PROJECT_MANIFEST.md`, README, TASKS, ROADMAP, and CHANGELOG; preserve detailed evidence in per-sprint docs/contracts/workflows/Git history; never label source-published work as deployed or activated without operational evidence; and make the just-closed workflow successor-compatible.

Author by Lab | zefry
