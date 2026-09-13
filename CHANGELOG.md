# Changelog

This changelog records **material canonical progress**, not every intermediate compatibility or CI-only commit. Detailed provenance remains in merged pull requests, Git history, per-sprint documents, workflows, and machine-readable contracts.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

## 2026-09-13 — Sprint147 closed

**Sprint147: canonical throttle rejection metadata identity response hardening**

- **Purpose / Why:** Sprint146 secured canonical request identity plus exact per-route throttle ceiling, but any response with the right ceiling could still be rewritten without proving the full canonical throttle-rejection metadata shape.
- **Objective / Gap:** require `X-RateLimit-Remaining: 0`, decimal non-empty `Retry-After`, and decimal non-empty `X-RateLimit-Reset` in addition to the previously qualified request identity and throttle ceiling.
- **What changed:** added canonical throttle-rejection metadata validation to the hardener, dedicated Sprint147 regression coverage, successor-compatible Sprint144 fixture metadata, machine-readable contract, active exact-head workflow, and the required six-section Sprint description.
- Missing/nonzero/numeric-alias remaining values and missing/empty/nondigit retry/reset metadata remain framework-owned.
- Engineering PR #711 squash merged.
- Canonical engineering commit: `50a3ba99b8f5628381d9df63f4f6a0e1020d550a`.
- Parent canonical documentation checkpoint: `f4984081a30c4251d57acd143ec090b17ca181ff`.
- Final exact-head SHA before merge: `513d95dbb4d5ab95a8f6c3282f8911cf339a9697`.
- Exact-head qualification: 31/31 workflow runs successful.
- Product Owner merge-authority run `34752002084` successful.
- Engineering envelope: six paths; SHA-256 `ffd176da808eda16fccdf0375fcae2fd5bcc1cfc4b931aca6492ca31eb9b1d40`.
- **Operational boundaries / NO-GO:** no migration execution, permission provisioning, feature activation, runtime-token provisioning, operational manifest/DB invocation, deployment/release, durable-target activation, Technical Preview activation, Production activation, or updater activation.
- **Next position:** Sprint148 bounded discovery from canonical post-Sprint147; no objective or envelope is preselected.

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
