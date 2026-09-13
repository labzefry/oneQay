# Changelog

This changelog records **material canonical progress**, not every intermediate compatibility or CI-only commit. Detailed provenance remains in merged pull requests, Git history, per-sprint documents, workflows, and machine-readable contracts.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

## 2026-09-13 — Sprint151 closed

**Sprint151: durable runtime dependency-envelope evidence source foundation**

- **Purpose / Why:** Sprint150 qualified the complete nine-component durable-runtime dependency envelope but intentionally left the source responsible for deterministic evidence construction unimplemented.
- **Objective / Gap:** materialize a source-only application foundation that consumes only exact canonical dependency observations, binds them to exact selected-target/runtime/source/artifact and Sprint148 capability-evidence identity, and requires Sprint150 qualification before accepting output.
- **What changed:** added `FinalShiftCloseDurableRuntimeDependencyEnvelopeEvidenceProducer`, `DURABLE_RUNTIME_DEPENDENCY_ENVELOPE_SOURCE_FOUNDATION_CONTRACT.json`, downstream-readiness source-foundation state, exact-head Sprint151 workflow, and required six-section documentation.
- The published scope deliberately keeps the dispatchable dependency-evidence producer `NOT_IMPLEMENTED`; no operational transport or dispatch workflow is introduced.
- Engineering PR #720 squash merged.
- Canonical engineering commit: `68b8362f326e56cfec478f0275b6d29ed0f54dec`.
- Parent canonical post-Sprint150 reconciliation checkpoint: `ea328965af0ff15c5431450f3d9888f43cd36b63`.
- Final exact engineering head: `f41e32bc44e0ee0346f5efd15d54f6af21b8a19a`.
- Exact-head pull-request qualification: **36/36 successful**.
- Repository-native Product Owner merge-authority run `34764011476` successful.
- Final engineering envelope: five paths; SHA-256 `ef000ec9172dcee1f08a6c8ea9149fb957a307e7929eaaa42c5d4b08bad94c54`.
- Post-Sprint151 canonical reconciliation envelope: six paths; SHA-256 `82d72910aaa4409113cfbb8b2b7f326daa51c205d7a6bfefa90ad5624604fb25`.
- **Operational boundaries / NO-GO:** no selected target persistence, capability/dependency producer dispatch, real capability/dependency evidence, runtime allowlist widening, migration execution, permission provisioning, feature activation, deployment/release, Technical Preview activation, Production activation, or updater activation.
- **Next position:** Sprint152 bounded discovery from canonical post-Sprint151 after reconciliation is squash merged and verified; no objective or source envelope is preselected.

## 2026-09-13 — Sprint150 closed

- PR #718 squash merged at engineering commit `d743a054092231729fa0e33cd34538f9d1e81787`.
- Added exact selected-runtime-class full nine-component dependency-envelope qualification.
- Exact-head qualification: 35/35 successful; Product Owner authority run `34761524119` successful.
- Engineering envelope SHA-256: `9f261895ab0373af5d6d385c3db3f93e5e510061b8e843e4110ecce992d9a0e6`.

## 2026-09-13 — Sprint149 closed

- PR #716 squash merged at engineering commit `662a892c3269d945579594da03121bce960c9074`.
- Added trusted protected-environment target-bound capability-evidence producer source readiness without dispatch.
- Exact-head qualification: 34/34 successful; Product Owner authority run `34759693893` successful.
- Engineering envelope SHA-256: `8acea0b1cc826dedbe2dd55f38b4aa24fd4854a96ee32dde9f7552d22d239a91`.

## 2026-09-13 — Sprint148 closed

- PR #714 squash merged at engineering commit `7a07a3163842e60332ccd3e3780d4e970280d46c`.
- Added exact target-bound durable-runtime capability-evidence identity qualification.
- Final exact-head qualification: 33/33 successful; Product Owner authority run `34756294306` successful.
- Engineering envelope SHA-256: `f7d9cfb173b54ac863cc70f10b9ae3df2f6715a4abdae0c7c32cbdb399bda5a6`.

## Sprint130–Sprint147 canonical control-plane qualification

Sprint130 established canonical runtime-control-plane token policy. Sprint131–Sprint138 qualified middleware/controller, delivery-gate/registration metadata, and authenticated HTTP positive/fail-closed/throttle behavior. Sprint139–Sprint147 hardened authentication-before-throttle, rejection propagation, route/action identity, exact throttle-budget identity, and canonical throttle-rejection metadata identity.

## Earlier material engineering history

Final Shift Close milestones include Sprint88 source-only migration #27, Sprint89 application-readiness, Sprint107 runtime dependency inventory, Sprint110 durable-runtime readiness, Sprint111 selected-target identity, and Sprint113–Sprint117 source-only attestation/selection/binding readiness. JRN-010 milestones include Sprint55 expected-cash derivation, Sprint64 cash-variance source foundation, Sprint70 durable cash-variance explanation, and Sprint80 scoped reviewer authorization. Earlier work established architecture/governance, bounded POS foundations, authentication/session hardening, and regression-preservation controls.

## Current lifecycle boundary

Machine-readable authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

Current high-level values remain migration #27 `NOT_EXECUTED`, permission provisioning `NONE`, feature `INACTIVE`, deployment `NOT_GRANTED`, preview/production `NOT_AUTHORIZED`, updater `INACTIVE`, durable target selection blocked, selected target `null`, capability-evidence producer `MATERIALIZED_NOT_DISPATCHED`, real capability evidence `NONE`, dependency-envelope qualifier `MATERIALIZED_SOURCE_ONLY`, dependency-evidence source foundation `MATERIALIZED_SOURCE_ONLY`, dispatchable dependency-evidence producer `NOT_IMPLEMENTED`, real dependency-envelope evidence `NONE`, and runtime allowlist change `NOT_IMPLEMENTED`.

## Sprint description standard

Each material sprint records: **Purpose / Why; Objective / Gap; What changed; Evidence / Qualification; Operational boundaries / NO-GO; Next position**.

Author by Lab | zefry
