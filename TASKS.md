# oneQay Tasks

**Current engineering checkpoint:** post-Sprint154 reconciliation
**Canonical engineering commit:** `5349acbffd1c90087d37a1a0f74ce0ffd7e87773`
**Canonical status authority:** `PROJECT_MANIFEST.md`

This file separates completed source engineering, next bounded engineering, and operational actions that remain separately gated.

## Completed through Sprint154

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
- [x] Sprint154 CI-proven Sprint116 successor compatibility correction
- [x] Sprint154 engineering PR #726 squash merged
- [x] Sprint154 exact-head qualification — 39/39 successful
- [x] Sprint154 Product Owner authority run `34772516177` successful
- [x] Sprint154 engineering squash `5349acbffd1c90087d37a1a0f74ce0ffd7e87773`
- [x] Sprint154 post-merge engineering NO-GO verification completed

## Sprint154 closure evidence

- Objective: `FINAL_SHIFT_CLOSE_FEATURE_ACTIVATION_EXECUTOR_SOURCE_FOUNDATION`
- Final engineering head: `132dbd0048ad40248efe093e22f276b487890487`
- Initial six-path envelope SHA-256: `35499fbb12404b3ab5f25de924d4528060f4cf4eb362091b116e5faf3c3766c3`
- CI-proven Sprint116 compatibility conflict: run `34772115090`
- Final engineering envelope: seven paths
- Final engineering envelope SHA-256: `5dcf1fcd0b638ed9ec3d311947055a2b2c96c74d8e8fb5b74ff1f1b96bd1296f`
- Reconciliation envelope: six paths
- Reconciliation envelope SHA-256: `ba0208b79fb9790435dcc968fe85e05aece4a980cb891e4c2e946efcbc65650f`
- Activation executor source foundation: `MATERIALIZED_SOURCE_ONLY`
- Dispatchable feature-activation executor: `NOT_IMPLEMENTED`
- Configuration-mutation transport: `NOT_IMPLEMENTED`
- Executor dispatch: `NOT_PERFORMED`
- Runtime allowlist change: `NOT_IMPLEMENTED`
- Feature activation: `INACTIVE`

## Next bounded engineering

### Sprint155 — bounded discovery after Sprint154 closure

After post-Sprint154 reconciliation closes, the next engineering activity must:

- identify the smallest material non-duplicative production-readiness gap from canonical post-Sprint154;
- prove the invariant is not already owned by historical regressions, source contracts, or current foundations;
- prioritize genuine durable-runtime prerequisites without operational mutation;
- freeze the smallest meaningful bounded source envelope before implementation;
- remain fail-closed and deny-by-default;
- qualify exact head before merge;
- preserve the six-section sprint documentation rule.

No Sprint155 implementation is preselected.

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

At every material sprint closure: reconcile `PROJECT_MANIFEST.md`, README, TASKS, ROADMAP, and CHANGELOG; preserve detailed evidence in per-sprint docs/contracts/workflows/Git history; never label source-published work as deployed or activated without operational evidence; and make the just-closed workflow successor-compatible.

Author by Lab | zefry
