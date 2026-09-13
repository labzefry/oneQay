# oneQay Tasks

**Current engineering checkpoint:** post-Sprint149 reconciliation
**Canonical engineering commit:** `662a892c3269d945579594da03121bce960c9074`
**Canonical status authority:** `PROJECT_MANIFEST.md`

This file separates completed source engineering, next bounded engineering, and operational actions that remain unauthorized.

## Completed through Sprint149

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
- [x] Sprint149 PR #716 squash merged
- [x] Sprint149 exact-head PR qualification — 34/34 successful
- [x] Sprint149 repository-native Product Owner merge-authority run `34759693893` successful
- [x] Sprint149 canonical engineering commit `662a892c3269d945579594da03121bce960c9074`
- [x] Sprint149 post-merge engineering NO-GO verification completed
- [x] Sprint148 historical workflow made successor-compatible after exact-head CI proved its producer-state conflict

## Sprint149 closure evidence

- Objective: `DURABLE_RUNTIME_TARGET_BOUND_CAPABILITY_EVIDENCE_PRODUCER`
- Final engineering head: `449832afd18117c58fb034ad23c9d4217bd3cd1e`
- Engineering envelope: eight paths
- Engineering envelope SHA-256: `8acea0b1cc826dedbe2dd55f38b4aa24fd4854a96ee32dde9f7552d22d239a91`
- Reconciliation envelope: six paths
- Reconciliation envelope SHA-256: `f97c59e253a6d3d47ff84f026690e700c3c85c2b9e8baf9ec00bfb57c1663c7e`
- Producer source state: `MATERIALIZED_NOT_DISPATCHED`
- Producer dispatch: `NOT_PERFORMED`
- Real target-bound capability evidence: `NONE`

## Next bounded engineering

### Sprint150 — bounded discovery after Sprint149 closure

After post-Sprint149 reconciliation closes, the next engineering activity must:

- identify the smallest material non-duplicative production-readiness gap from canonical post-Sprint149;
- prove the invariant is not already owned by historical regressions;
- prioritize genuine durable-runtime readiness prerequisites without operational mutation;
- freeze a bounded source envelope before implementation;
- remain fail-closed and deny-by-default;
- qualify exact head before merge;
- preserve the six-field sprint documentation rule.

No Sprint150 implementation is preselected.

## Operational blockers / separate authority required

- [ ] Qualify/select and persist a real non-synthetic durable activation target — current selection `null`
- [ ] Dispatch trusted capability-evidence producer — `NOT_PERFORMED`
- [ ] Produce real target-bound durable-runtime capability evidence — `NONE`
- [ ] Execute migration #27 — `NOT_EXECUTED`
- [ ] Provision Final Shift Close permission — `NONE`
- [ ] Widen selected durable runtime class in runtime allowlist — `NOT_IMPLEMENTED`
- [ ] Activate Final Shift Close feature — `INACTIVE`
- [ ] Grant deployment authority — `NOT_GRANTED`
- [ ] Authorize Technical Preview activation — `NOT_AUTHORIZED`
- [ ] Authorize Production activation — `NOT_AUTHORIZED`
- [ ] Activate updater — `INACTIVE`

Machine-readable operational authority remains in `ops/final-shift-close/STATE.json` and `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`.

## Maintenance rule

At every material sprint closure: reconcile `PROJECT_MANIFEST.md`, README, TASKS, ROADMAP, and CHANGELOG; preserve detailed evidence in per-sprint docs/contracts/workflows/Git history; never label source-published work as deployed or activated without operational evidence; and make the just-closed workflow successor-compatible.

Author by Lab | zefry
