# Changelog

This changelog records **material canonical progress**, not every intermediate compatibility or CI-only commit. Detailed provenance remains in merged pull requests, Git history, per-sprint documents, workflows, and machine-readable contracts.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

## 2026-09-13 — Sprint145 closed

**Sprint145: canonical action identity throttle response hardening**

- **Purpose / Why:** close the remaining ownership gap after Sprint144, where route name + method + path were canonical but controller action identity was not yet required.
- **Objective / Gap:** require canonical route name + canonical controller action + exact method + exact path before throttle-response rewriting is owned by the Final Shift Close hardener.
- **What changed:** added action-identity matching, a Sprint145 executable regression, and a successor-compatible Sprint144 fixture carrying canonical Laravel controller metadata.
- Engineering PR #707 squash merged.
- Canonical engineering commit: `6d4fc06ac1166d15d8598d2a6d39d594f2493767`.
- Parent canonical documentation checkpoint: `a3ab64bffae0f1322eb731908b9ca1bf9dddf9b6`.
- Final exact-head SHA before merge: `eeb93032fb8611e031d207ce95c1825dea7e2f2d`.
- Exact-head qualification: 29/29 workflow runs successful.
- Product Owner merge-authority run `34749677796` successful.
- Engineering envelope: six paths; SHA-256 `ed1a67c7a7b89e26cd4c3ade350132b8eca7c4e2142f76d9b69495ac0ba2fad2`.
- **Operational boundary:** no migration execution, permission provisioning, runtime-token provisioning, deployment/release, target activation, Technical Preview activation, Production activation, or updater activation.
- **Next position:** Sprint146 bounded discovery from canonical post-Sprint145.

## 2026-09-13 — Sprint144 closed

- PR #705 squash merged at engineering commit `98840c29c21bcf6b1d81cb2afe21d07eb720e120`.
- Tightened throttle-response ownership to canonical route name + exact method + exact path.
- Same-path/method noncanonical routes remain framework-owned.
- Exact-head qualification: 28/28 successful; Product Owner authority run `34748118905` successful.

## 2026-09-13 — Sprint143 closed

- PR #703 squash merged at engineering commit `b307d925400e9707c137f75bcfa3823a182fb84f`.
- Added DB-attestation HEAD throttle-rejection parity.
- Exact-head qualification: 27/27 successful; Product Owner authority run `34746609129` successful.

## 2026-09-13 — Sprint142 closed

- PR #701 squash merged at engineering commit `61a6d68b45303a796c5eb7c2afa78b16e740da53`.
- Hardened authenticated throttle rejections while preserving framework rate-limit metadata.
- Exact-head qualification: 26/26 successful; Product Owner authority run `34744364161` successful.

## Sprint139–Sprint141 control-plane hardening

- Sprint139 — authentication-before-throttle hardening and budget-isolation regression.
- Sprint140 — direct middleware authentication-rejection response hardening.
- Sprint141 — registered-route HTTP-kernel propagation of the hardened rejection contract.

## Sprint130–Sprint138 canonical control-plane qualification

- Sprint130 — canonical runtime control-plane token policy.
- Sprint131–Sprint133 — middleware/controller positive and fail-closed regressions.
- Sprint134–Sprint135 — delivery-gate and registration metadata qualification.
- Sprint136–Sprint138 — authenticated HTTP positive, fail-closed, and throttle qualification.

## Earlier material engineering history

Final Shift Close milestones include Sprint88 source-only migration #27 and Sprint89 application-readiness. JRN-010 milestones include Sprint55 expected-cash derivation, Sprint64 cash-variance source foundation, Sprint70 durable cash-variance explanation, and Sprint80 scoped reviewer authorization. Earlier work established architecture/governance, bounded POS foundations, authentication/session hardening, and regression-preservation controls.

## Current lifecycle boundary

Machine-readable authority remains in:

- `ops/final-shift-close/STATE.json`
- `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`

Current high-level values remain migration #27 `NOT_EXECUTED`, permission provisioning `NONE`, feature `INACTIVE`, deployment `NOT_GRANTED`, preview/production `NOT_AUTHORIZED`, updater `INACTIVE`, durable target selection blocked, and selected target `null`.

## Sprint description standard

Each material sprint must record: **Purpose / Why; Objective / Gap; What changed; Evidence / Qualification; Operational boundaries / NO-GO; Next position**.

Author by Lab | zefry
