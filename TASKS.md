# oneQay Tasks

**Current engineering checkpoint:** post-Sprint150 reconciliation
**Canonical engineering commit:** `d743a054092231729fa0e33cd34538f9d1e81787`
**Canonical status authority:** `PROJECT_MANIFEST.md`

This file separates completed source engineering, next bounded engineering, and operational actions that remain unauthorized.

## Completed through Sprint150

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
- [x] Sprint150 PR #718 squash merged
- [x] Sprint150 exact-head PR qualification — 35/35 successful
- [x] Sprint150 repository-native Product Owner merge-authority run `34761524119` successful
- [x] Sprint150 canonical engineering commit `d743a054092231729fa0e33cd34538f9d1e81787`
- [x] Sprint150 post-merge engineering NO-GO verification completed
- [x] Sprint150 provider runtime allowlist preserved as `local/test/ci`

## Sprint150 closure evidence

- Objective: `DURABLE_RUNTIME_SELECTED_CLASS_DEPENDENCY_ENVELOPE_QUALIFICATION`
- Final engineering head: `7102733080735f4591bb17df76bdec928975e826`
- Engineering envelope: six paths
- Engineering envelope SHA-256: `9f261895ab0373af5d6d385c3db3f93e5e510061b8e843e4110ecce992d9a0e6`
- Reconciliation envelope: six paths
- Reconciliation envelope SHA-256: `578765b03de34048670791017fcffe8680b65e6bede9f57d22affb255f5ee43f`
- Dependency-envelope qualifier: `MATERIALIZED_SOURCE_ONLY`
- Real dependency-envelope evidence: `NONE`
- Dependency-evidence producer: `NOT_IMPLEMENTED`
- Runtime allowlist change: `NOT_IMPLEMENTED`

## Next bounded engineering

### Sprint151 — bounded discovery after Sprint150 closure

After post-Sprint150 reconciliation closes, the next engineering activity must:

- identify the smallest material non-duplicative production-readiness gap from canonical post-Sprint150;
- prove the invariant is not already owned by historical regressions or source contracts;
- prioritize genuine durable-runtime readiness prerequisites without operational mutation;
- freeze a bounded source envelope before implementation;
- remain fail-closed and deny-by-default;
- qualify exact head before merge;
- preserve the six-field sprint documentation rule.

No Sprint151 implementation is preselected.

## Operational blockers / separate authority required

- [ ] Qualify/select and persist a real non-synthetic durable activation target — current selection `null`
- [ ] Dispatch trusted capability-evidence producer — `NOT_PERFORMED`
- [ ] Produce real target-bound durable-runtime capability evidence — `NONE`
- [ ] Materialize/dispatch trusted dependency-envelope evidence producer — `NOT_IMPLEMENTED`
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
