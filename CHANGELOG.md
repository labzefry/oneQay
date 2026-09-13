# Changelog

This changelog records **material canonical progress**, not every intermediate compatibility or CI-only commit. Detailed provenance remains in merged pull requests, Git history, per-sprint documents, workflows, and machine-readable contracts.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

## 2026-09-13 — Sprint153 closed

**Sprint153: durable runtime dependency-envelope evidence producer**

- **Purpose / Why:** Sprint150 qualified the full target-bound nine-component dependency envelope and Sprint151 provided deterministic construction, but canonical post-Sprint152 still lacked a trusted protected-environment producer transport for authenticated dependency observations.
- **Objective / Gap:** materialize a source-only producer requiring canonical `SELECTED_NOT_AUTHORIZED`, exact trusted Sprint149 capability-producer provenance, authenticated HTTPS dependency observations, Sprint151 construction, and Sprint150 final qualification, with no caller-selected target identity.
- **What changed:** added `.github/workflows/final-shift-close-durable-runtime-dependency-envelope-evidence.yml`, `DURABLE_RUNTIME_DEPENDENCY_ENVELOPE_EVIDENCE_PRODUCER_CONTRACT.json`, downstream producer-source readiness, exact-head Sprint153 regression, and six-section documentation.
- The producer source is `MATERIALIZED_NOT_DISPATCHED`; producer dispatch remains `NOT_PERFORMED`; real dependency-envelope evidence remains `NONE`.
- Initial engineering envelope contained five paths, SHA-256 `4d184a18b6d9814474233bda0bf4761e7045ebfb0a8cda5ea56b6b296cef627b`.
- Exact-head CI run `34767104436` proved Sprint151 still locked the old canonical producer state. The final bounded envelope therefore added only the historical Sprint151 workflow and retained its original extension/provenance while making current canonical state successor-compatible.
- Engineering PR #724 squash merged.
- Canonical engineering commit: `e42ea89fca2b3974e7fa9b0a95fa31a901e9840c`.
- Parent canonical post-Sprint152 reconciliation checkpoint: `fc1efce072abdbb3e06d4ba22d6aaaa169cd447d`.
- Final exact engineering head: `44a17e4590df58472114aad547dd1cd3087f8e96`.
- Exact-head pull-request qualification: **38/38 successful**.
- Repository-native Product Owner merge-authority run `34767471668` successful.
- Final engineering envelope: six paths; SHA-256 `7269a0e5f8927c2763411d0700b0f456cdcb0781f1d198179966a97a7b934a79`.
- Post-Sprint153 canonical reconciliation envelope: six paths; SHA-256 `6a0fd4267f940c02d23e95ce4085e89cdf25a52186d95619e1b1da89488dfc46`.
- **Operational boundaries / NO-GO:** no target persistence, dependency/capability producer dispatch, real dependency/capability evidence, migration execution, permission provisioning, runtime allowlist widening, feature activation, deployment/release, Technical Preview activation, Production activation, or updater activation.
- **Next position:** Sprint154 bounded discovery from canonical post-Sprint153 after reconciliation is squash merged and verified; no objective or source envelope is preselected.

## 2026-09-13 — Sprint152 closed

- PR #722 squash merged at engineering commit `c6abc9356ad329c1a2273a71a4a8ca0e50822825`.
- Hardened permission provisioning with selected-target DB binding, Sprint118 evidence reuse, and immediate pre-mutation DB identity readback.
- Exact-head qualification: 38/38 successful; Product Owner authority run `34765603014` successful.
- Engineering envelope SHA-256: `7ed9c7cc6da5f03f73fdbd3ef18f4315896b95832331c2c2d5e2fa6bb2151590`.

## 2026-09-13 — Sprint151 closed

- PR #720 squash merged at engineering commit `68b8362f326e56cfec478f0275b6d29ed0f54dec`.
- Added target-bound dependency-envelope evidence deterministic construction source foundation.
- Exact-head qualification: 36/36 successful; Product Owner authority run `34764011476` successful.
- Engineering envelope SHA-256: `ef000ec9172dcee1f08a6c8ea9149fb957a307e7929eaaa42c5d4b08bad94c54`.

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

Final Shift Close milestones include Sprint88 source-only migration #27, Sprint89 application-readiness, Sprint107 runtime dependency inventory, Sprint110 durable-runtime readiness, Sprint111 selected-target identity, Sprint113–Sprint118 attestation/selection and migration selected-target binding readiness, and Sprint119–Sprint129 runtime binding/control-plane hardening. JRN-010 milestones include Sprint55 expected-cash derivation, Sprint64 cash-variance source foundation, Sprint70 durable cash-variance explanation, and Sprint80 scoped reviewer authorization. Earlier work established architecture/governance, bounded POS foundations, authentication/session hardening, and regression-preservation controls.

## Current lifecycle boundary

Machine-readable authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

Current high-level values remain migration #27 `NOT_EXECUTED`, permission selected-target binding source `MATERIALIZED_NOT_DISPATCHED`, real permission binding evidence `NONE`, permission provisioning `NONE`, feature `INACTIVE`, deployment `NOT_GRANTED`, preview/production `NOT_AUTHORIZED`, updater `INACTIVE`, durable target selection blocked, selected target `null`, capability-evidence producer `MATERIALIZED_NOT_DISPATCHED`, capability producer dispatch `NOT_PERFORMED`, real capability evidence `NONE`, dependency-evidence source foundation `MATERIALIZED_SOURCE_ONLY`, dependency-evidence producer `MATERIALIZED_NOT_DISPATCHED`, dependency producer dispatch `NOT_PERFORMED`, real dependency-envelope evidence `NONE`, and runtime allowlist change `NOT_IMPLEMENTED`.

## Sprint description standard

Each material sprint records: **Purpose / Why; Objective / Gap; What changed; Evidence / Qualification; Operational boundaries / NO-GO; Next position**.

Author by Lab | zefry
