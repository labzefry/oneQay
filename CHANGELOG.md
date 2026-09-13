# Changelog

This changelog records **material canonical progress**, not every intermediate compatibility or CI-only commit. Detailed provenance remains in merged pull requests, Git history, per-sprint documents, workflows, and machine-readable contracts.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

## 2026-09-13 — Sprint150 closed

**Sprint150: qualify selected runtime durable dependency envelope**

- **Purpose / Why:** Sprint107 established the nine-component runtime dependency inventory and explicitly blocked runtime-allowlist widening, while Sprint148/Sprint149 established target-bound capability evidence and producer source readiness. The complete dependency envelope still lacked qualification against exact Sprint111 selected-target identity.
- **Objective / Gap:** qualify all nine canonical Sprint107 dependency components as independently verified, exact-source-path, exact-selected-runtime, exact-target-bound, lowercase-SHA-256, secret-free, and non-synthetic before any future runtime-allowlist eligibility.
- **What changed:** added `FinalShiftCloseDurableRuntimeDependencyEnvelope`, dedicated executable regression coverage, `DURABLE_RUNTIME_DEPENDENCY_ENVELOPE_QUALIFICATION_CONTRACT.json`, downstream-readiness ordering, an exact-head Sprint150 workflow, and the required six-section Sprint150 document.
- The qualifier requires exact Sprint148 capability evidence to qualify first and binds the resulting capability-evidence bundle identity into the dependency envelope.
- No historical compatibility expansion was required; all 35 PR-triggered exact-head workflows passed on the frozen six-path envelope.
- Engineering PR #718 squash merged.
- Canonical engineering commit: `d743a054092231729fa0e33cd34538f9d1e81787`.
- Parent canonical post-Sprint149 reconciliation checkpoint: `0113ee31db38dcd8f7c1378b109371ad900c6f69`.
- Final exact engineering head: `7102733080735f4591bb17df76bdec928975e826`.
- Exact-head pull-request qualification: **35/35 successful**.
- Repository-native Product Owner merge-authority run `34761524119` successful.
- Final engineering envelope: six paths; SHA-256 `9f261895ab0373af5d6d385c3db3f93e5e510061b8e843e4110ecce992d9a0e6`.
- Post-Sprint150 canonical reconciliation envelope: six paths; SHA-256 `578765b03de34048670791017fcffe8680b65e6bede9f57d22affb255f5ee43f`.
- **Operational boundaries / NO-GO:** no selected target persistence, capability/dependency producer dispatch, real dependency evidence, runtime allowlist widening, migration execution, permission provisioning, feature activation, deployment/release, Technical Preview activation, Production activation, or updater activation.
- **Next position:** Sprint151 bounded discovery from canonical post-Sprint150 after this reconciliation is squash merged and verified; no objective or source envelope is preselected.

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

## 2026-09-13 — Sprint147 closed

- PR #711 squash merged at engineering commit `50a3ba99b8f5628381d9df63f4f6a0e1020d550a`.
- Added canonical throttle-rejection metadata identity response hardening.
- Exact-head qualification: 31/31 successful; Product Owner authority run `34752002084` successful.

## Sprint130–Sprint146 canonical control-plane qualification

Sprint130 established canonical runtime-control-plane token policy. Sprint131–Sprint138 qualified middleware/controller, delivery-gate/registration metadata, and authenticated HTTP positive/fail-closed/throttle behavior. Sprint139–Sprint146 hardened authentication-before-throttle, rejection propagation, route/action identity, and exact throttle-budget identity.

## Earlier material engineering history

Final Shift Close milestones include Sprint88 source-only migration #27, Sprint89 application-readiness, Sprint107 runtime dependency inventory, Sprint110 durable-runtime readiness, Sprint111 selected-target identity, and Sprint113–Sprint117 source-only attestation/selection/binding readiness. JRN-010 milestones include Sprint55 expected-cash derivation, Sprint64 cash-variance source foundation, Sprint70 durable cash-variance explanation, and Sprint80 scoped reviewer authorization. Earlier work established architecture/governance, bounded POS foundations, authentication/session hardening, and regression-preservation controls.

## Current lifecycle boundary

Machine-readable authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

Current high-level values remain migration #27 `NOT_EXECUTED`, permission provisioning `NONE`, feature `INACTIVE`, deployment `NOT_GRANTED`, preview/production `NOT_AUTHORIZED`, updater `INACTIVE`, durable target selection blocked, selected target `null`, capability-evidence producer `MATERIALIZED_NOT_DISPATCHED`, real capability evidence `NONE`, dependency-envelope qualifier `MATERIALIZED_SOURCE_ONLY`, real dependency-envelope evidence `NONE`, dependency-evidence producer `NOT_IMPLEMENTED`, and runtime allowlist change `NOT_IMPLEMENTED`.

## Sprint description standard

Each material sprint records: **Purpose / Why; Objective / Gap; What changed; Evidence / Qualification; Operational boundaries / NO-GO; Next position**.

Author by Lab | zefry
