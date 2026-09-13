# oneQay Tasks

**Current engineering checkpoint:** post-Sprint153 reconciliation
**Canonical engineering commit:** `e42ea89fca2b3974e7fa9b0a95fa31a901e9840c`
**Canonical status authority:** `PROJECT_MANIFEST.md`

This file separates completed source engineering, next bounded engineering, and operational actions that remain unauthorized.

## Completed through Sprint153

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
- [x] Target-bound dependency-envelope evidence deterministic construction source foundation — Sprint151
- [x] Permission provisioning selected-target database-binding source hardening — Sprint152
- [x] Trusted protected-environment dependency-envelope evidence producer source materialization — Sprint153
- [x] Sprint153 PR #724 squash merged
- [x] Sprint153 exact-head PR qualification — 38/38 successful
- [x] Sprint153 repository-native Product Owner merge-authority run `34767471668` successful
- [x] Sprint153 canonical engineering commit `e42ea89fca2b3974e7fa9b0a95fa31a901e9840c`
- [x] Sprint153 post-merge engineering NO-GO verification completed

## Sprint153 closure evidence

- Objective: `DURABLE_RUNTIME_TARGET_BOUND_DEPENDENCY_ENVELOPE_EVIDENCE_PRODUCER`
- Final engineering head: `44a17e4590df58472114aad547dd1cd3087f8e96`
- Initial engineering envelope: five paths, SHA-256 `4d184a18b6d9814474233bda0bf4761e7045ebfb0a8cda5ea56b6b296cef627b`
- CI-proven Sprint151 compatibility conflict: run `34767104436`
- Final engineering envelope: six paths
- Final engineering envelope SHA-256: `7269a0e5f8927c2763411d0700b0f456cdcb0781f1d198179966a97a7b934a79`
- Reconciliation envelope: six paths
- Reconciliation envelope SHA-256: `6a0fd4267f940c02d23e95ce4085e89cdf25a52186d95619e1b1da89488dfc46`
- Dependency-evidence source foundation: `MATERIALIZED_SOURCE_ONLY`
- Dependency-evidence producer source: `MATERIALIZED_NOT_DISPATCHED`
- Dependency-evidence producer dispatch: `NOT_PERFORMED`
- Real dependency-envelope evidence: `NONE`
- Runtime allowlist change: `NOT_IMPLEMENTED`

## Next bounded engineering

### Sprint154 — bounded discovery after Sprint153 closure

After post-Sprint153 reconciliation closes, the next engineering activity must:

- identify the smallest material non-duplicative production-readiness gap from canonical post-Sprint153;
- prove the invariant is not already owned by historical regressions, source contracts, or current foundations;
- prioritize genuine durable-runtime readiness prerequisites without operational mutation;
- freeze the smallest meaningful bounded source envelope before implementation;
- remain fail-closed and deny-by-default;
- qualify exact head before merge;
- preserve the six-field sprint documentation rule.

No Sprint154 implementation is preselected.

## Operational blockers / separate authority required

- [ ] Qualify/select and persist a real non-synthetic durable activation target — current selection `null`
- [ ] Produce real selected-target DB-binding evidence — `NONE`
- [ ] Execute migration #27 — `NOT_EXECUTED`
- [ ] Execute selected-target-bound Final Shift Close permission provisioning — `NONE`
- [ ] Dispatch trusted capability-evidence producer — `NOT_PERFORMED`
- [ ] Produce real target-bound durable-runtime capability evidence — `NONE`
- [ ] Dispatch trusted dependency-envelope evidence producer — `NOT_PERFORMED`
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
