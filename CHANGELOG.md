# Changelog

This changelog records **material canonical progress**, not every intermediate compatibility or CI-only commit. Detailed provenance remains in merged pull requests, Git history, per-sprint documents, workflows, and machine-readable contracts.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

## 2026-09-13 — Sprint146 closed

**Sprint146: canonical throttle budget identity response hardening**

- **Purpose / Why:** Sprint145 secured canonical request identity for throttle-response ownership, but any present `X-RateLimit-Limit` value could still qualify the response for rewriting.
- **Objective / Gap:** require exact canonical per-route throttle ceiling in addition to route name + controller action + exact method + exact path.
- **What changed:** added exact budget resolution/matching to the throttle-response hardener, a dedicated Sprint146 executable regression, a machine-readable contract, active exact-head workflow, and the required six-section Sprint description.
- Materialization POST canonical ceiling: `1`.
- DB-attestation GET/HEAD canonical ceiling: `2`.
- Cross-budget, arbitrary, and numeric-alias ceilings remain framework-owned.
- Engineering PR #709 squash merged.
- Canonical engineering commit: `a5e4aec8c142e7478a0e58d2d732dbf106393b06`.
- Parent canonical documentation checkpoint: `ebaf16c64245c67e9ecf8cac613696e5661a02ac`.
- Final exact-head SHA before merge: `b520b8e8c565a96b4c41e7838a68492f4b836066`.
- Exact-head qualification: 30/30 workflow runs successful.
- Product Owner merge-authority run `34750648988` successful.
- Engineering envelope: five paths; SHA-256 `bbc0da27fe84ca8a1fafcf4b76bcf9e1f42e595c01d35544a94793c1a7fec161`.
- **Operational boundaries / NO-GO:** no migration execution, permission provisioning, feature activation, runtime-token provisioning, operational manifest/DB invocation, deployment/release, durable-target activation, Technical Preview activation, Production activation, or updater activation.
- **Next position:** Sprint147 bounded discovery from canonical post-Sprint146; no objective or envelope is preselected.

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
