# oneQay Tasks

**Current engineering checkpoint:** post-Sprint152 reconciliation
**Canonical engineering commit:** `c6abc9356ad329c1a2273a71a4a8ca0e50822825`
**Canonical status authority:** `PROJECT_MANIFEST.md`

This file separates completed source engineering, next bounded engineering, and operational actions that remain unauthorized.

## Completed through Sprint152

- [x] Core modular-monolith architecture and repository governance foundation
- [x] Tenant-context-first and deny-by-default authorization foundations
- [x] Versioned REST / correlation / idempotency governance foundations
- [x] POS shift/register, sale/payment/receipt, catalog, cash-variance, adjudication, and reviewer-control foundations
- [x] Final Shift Close migration #27 source materialization; execution remains unauthorized
- [x] Durable-runtime readiness and exact selected-target identity readiness — Sprint110–Sprint111
- [x] Source-only attestation producer/ingestion, target-selection persistence and migration DB-binding readiness — Sprint113–Sprint118
- [x] Runtime DB-binding attestation/materialization control-plane and successor compatibility — Sprint119–Sprint129
- [x] Canonical runtime-control-plane and HTTP/throttle identity hardening through Sprint147
- [x] Target-bound durable-runtime capability-evidence identity qualification — Sprint148
- [x] Trusted target-bound capability-evidence producer source materialization — Sprint149
- [x] Full nine-component selected-runtime dependency-envelope qualification source — Sprint150
- [x] Target-bound dependency-envelope evidence construction source foundation — Sprint151
- [x] Permission provisioning selected-target database-binding source hardening — Sprint152
- [x] Sprint152 PR #722 squash merged
- [x] Sprint152 exact-head PR qualification — 38/38 successful
- [x] Sprint152 repository-native Product Owner merge-authority run `34765603014` successful
- [x] Sprint152 canonical engineering commit `c6abc9356ad329c1a2273a71a4a8ca0e50822825`
- [x] Sprint152 post-merge engineering NO-GO verification completed

## Sprint152 closure evidence

- Objective: `PERMISSION_PROVISIONING_SELECTED_TARGET_DATABASE_BINDING`
- Final engineering head: `70c30e407271c0e78439143230159f7b1c63f291`
- Engineering envelope: seven paths
- Engineering envelope SHA-256: `7ed9c7cc6da5f03f73fdbd3ef18f4315896b95832331c2c2d5e2fa6bb2151590`
- Reconciliation envelope: six paths
- Reconciliation envelope SHA-256: `bd3613c19baad6d74925a78dd72dcaf8121fbec842ace3e9279beaf8ecf8b971`
- Permission selected-target binding source: `MATERIALIZED_NOT_DISPATCHED`
- Permission selected-target binding evidence: `NONE`
- Permission provisioning: `NONE`
- Runtime allowlist change: `NOT_IMPLEMENTED`
- Sprint104 and Sprint116 historical regressions are successor-compatible while preserving owned security and NO-GO invariants.

## Next bounded engineering

### Sprint153 — bounded discovery after Sprint152 closure

After post-Sprint152 reconciliation closes, the next engineering activity must:

- identify the smallest material non-duplicative production-readiness gap from canonical post-Sprint152;
- prove the invariant is not already owned by historical regressions, source contracts, or current foundations;
- prioritize genuine durable-runtime readiness prerequisites without operational mutation;
- freeze a bounded source envelope before implementation;
- remain fail-closed and deny-by-default;
- qualify exact head before merge;
- preserve the six-field sprint documentation rule.

No Sprint153 implementation is preselected.

## Operational blockers / separate authority required

- [ ] Qualify/select and persist a real non-synthetic durable activation target — current selection `null`
- [ ] Produce real selected-target DB-binding evidence — `NONE`
- [ ] Execute migration #27 — `NOT_EXECUTED`
- [ ] Execute selected-target-bound Final Shift Close permission provisioning — `NONE`
- [ ] Dispatch trusted capability-evidence producer — `NOT_PERFORMED`
- [ ] Produce real target-bound durable-runtime capability evidence — `NONE`
- [ ] Materialize a separately qualified dispatchable dependency-envelope evidence producer — `NOT_IMPLEMENTED`
- [ ] Produce real target-bound dependency-envelope evidence — `NONE`
- [ ] Widen selected durable runtime class in runtime allowlist — `NOT_IMPLEMENTED`
- [ ] Materialize/authorize Final Shift Close feature activation — `INACTIVE`
- [ ] Grant deployment authority — `NOT_GRANTED`
- [ ] Authorize Technical Preview activation — `NOT_AUTHORIZED`
- [ ] Authorize Production activation — `NOT_AUTHORIZED`
- [ ] Activate updater — `INACTIVE`

Machine-readable operational authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

## Maintenance rule

At every material sprint closure: reconcile `PROJECT_MANIFEST.md`, README, TASKS, ROADMAP, and CHANGELOG; preserve detailed evidence in per-sprint docs/contracts/workflows/Git history; never label source-published work as deployed or activated without operational evidence; and make the just-closed workflow successor-compatible.

Author by Lab | zefry
