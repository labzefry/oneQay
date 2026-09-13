# Changelog

This changelog records **material canonical progress**, not every intermediate compatibility or CI-only commit. Detailed provenance remains in merged pull requests, Git history, per-sprint documents, workflows, and machine-readable contracts.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

## 2026-09-13 — Sprint149 closed

**Sprint149: durable runtime target-bound capability evidence producer**

- **Purpose / Why:** Sprint148 established exact target-bound capability-evidence identity qualification but intentionally left real capability evidence absent and the trusted producer unimplemented.
- **Objective / Gap:** materialize a trusted protected-environment producer source that can obtain capability-specific observation digests, recompute Sprint110/Sprint111 identity, bind all four Sprint148 evidence kinds to the exact target, and require final Sprint148 qualification in a future separately authorized execution.
- **What changed:** added `FinalShiftCloseDurableRuntimeCapabilityEvidenceProducer`, dedicated executable regression coverage, protected producer workflow source, `DURABLE_RUNTIME_CAPABILITY_EVIDENCE_PRODUCER_CONTRACT.json`, post-selection readiness integration, active exact-head workflow, and the required six-section Sprint149 document.
- Initial seven-path exact-head CI proved historical Sprint148 still froze successor-owned producer materialization; Sprint149 therefore expanded to eight paths only to make that historical workflow successor-compatible while preserving its owned evidence-binding and NO-GO invariants.
- Engineering PR #716 squash merged.
- Canonical engineering commit: `662a892c3269d945579594da03121bce960c9074`.
- Parent canonical post-Sprint148 reconciliation checkpoint: `c595da17fd023a9eb2ebd3f046e6f17940430a03`.
- Final exact engineering head: `449832afd18117c58fb034ad23c9d4217bd3cd1e`.
- Exact-head pull-request qualification: **34/34 successful**.
- Repository-native Product Owner merge-authority run `34759693893` successful.
- Final engineering envelope: eight paths; SHA-256 `8acea0b1cc826dedbe2dd55f38b4aa24fd4854a96ee32dde9f7552d22d239a91`.
- Post-Sprint149 canonical reconciliation envelope: six paths; SHA-256 `f97c59e253a6d3d47ff84f026690e700c3c85c2b9e8baf9ec00bfb57c1663c7e`.
- **Operational boundaries / NO-GO:** producer source materialized but not dispatched; no real capability evidence, target persistence/activation, migration execution, permission provisioning, runtime allowlist change, feature activation, deployment/release, Technical Preview activation, Production activation, or updater activation.
- **Next position:** Sprint150 bounded discovery from canonical post-Sprint149 after reconciliation is squash merged and verified; no objective or source envelope is preselected.

## 2026-09-13 — Sprint148 closed

- PR #714 squash merged at engineering commit `7a07a3163842e60332ccd3e3780d4e970280d46c`.
- Added exact target-bound durable-runtime capability-evidence identity qualification.
- Final exact-head qualification: 33/33 successful; Product Owner authority run `34756294306` successful.
- Engineering envelope SHA-256: `f7d9cfb173b54ac863cc70f10b9ae3df2f6715a4abdae0c7c32cbdb399bda5a6`.

## 2026-09-13 — Sprint147 closed

- PR #711 squash merged at engineering commit `50a3ba99b8f5628381d9df63f4f6a0e1020d550a`.
- Added canonical throttle-rejection metadata identity response hardening.
- Exact-head qualification: 31/31 successful; Product Owner authority run `34752002084` successful.

## 2026-09-13 — Sprint146 closed

- PR #709 squash merged at engineering commit `a5e4aec8c142e7478a0e58d2d732dbf106393b06`.
- Added exact canonical per-route throttle-budget identity to response ownership.
- Exact-head qualification: 30/30 successful; Product Owner authority run `34750648988` successful.

## 2026-09-13 — Sprint145 closed

- PR #707 squash merged at engineering commit `6d4fc06ac1166d15d8598d2a6d39d594f2493767`.
- Added canonical controller-action identity to throttle-response ownership.
- Exact-head qualification: 29/29 successful; Product Owner authority run `34749677796` successful.

## 2026-09-13 — Sprint144 closed

- PR #705 squash merged at engineering commit `98840c29c21bcf6b1d81cb2afe21d07eb720e120`.
- Tightened throttle-response ownership to canonical route name + exact method + exact path.
- Exact-head qualification: 28/28 successful; Product Owner authority run `34748118905` successful.

## 2026-09-13 — Sprint143 closed

- PR #703 squash merged at engineering commit `b307d925400e9707c137f75bcfa3823a182fb84f`.
- Added DB-attestation HEAD throttle-rejection parity.
- Exact-head qualification: 27/27 successful; Product Owner authority run `34746609129` successful.

## 2026-09-13 — Sprint142 closed

- PR #701 squash merged at engineering commit `61a6d68b45303a796c5eb7c2afa78b16e740da53`.
- Hardened authenticated throttle rejections while preserving framework rate-limit metadata.
- Exact-head qualification: 26/26 successful; Product Owner authority run `34744364161` successful.

## Sprint130–Sprint141 canonical control-plane qualification

Sprint130 established the canonical runtime-control-plane token policy. Sprint131–Sprint138 qualified middleware/controller, delivery-gate/registration metadata, and authenticated HTTP positive/fail-closed/throttle behavior. Sprint139–Sprint141 hardened authentication-before-throttle and rejection propagation.

## Earlier material engineering history

Final Shift Close milestones include Sprint88 source-only migration #27, Sprint89 application-readiness, Sprint110 durable-runtime readiness, Sprint111 selected-target identity, and Sprint113–Sprint117 source-only attestation/selection/binding readiness. JRN-010 milestones include Sprint55 expected-cash derivation, Sprint64 cash-variance source foundation, Sprint70 durable cash-variance explanation, and Sprint80 scoped reviewer authorization. Earlier work established architecture/governance, bounded POS foundations, authentication/session hardening, and regression-preservation controls.

## Current lifecycle boundary

Machine-readable authority remains in `ops/final-shift-close/STATE.json` and `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`.

Current high-level values remain migration #27 `NOT_EXECUTED`, permission provisioning `NONE`, feature `INACTIVE`, deployment `NOT_GRANTED`, preview/production `NOT_AUTHORIZED`, updater `INACTIVE`, durable target selection blocked, selected target `null`, runtime allowlist change `NOT_IMPLEMENTED`, real target-bound capability evidence `NONE`, trusted capability-evidence producer `MATERIALIZED_NOT_DISPATCHED`, and producer dispatch `NOT_PERFORMED`.

## Sprint description standard

Each material sprint records: **Purpose / Why; Objective / Gap; What changed; Evidence / Qualification; Operational boundaries / NO-GO; Next position**.

Author by Lab | zefry
