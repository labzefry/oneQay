# Changelog

This changelog records **material canonical progress**, not every intermediate compatibility or CI-only commit. Detailed provenance remains in merged pull requests, Git history, per-sprint documents, workflows, and machine-readable contracts.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

## 2026-09-13 — Sprint144 closed

**Sprint144: canonical named-route identity throttle response hardening**

- PR #705 squash merged.
- Canonical engineering commit: `98840c29c21bcf6b1d81cb2afe21d07eb720e120`.
- Parent canonical documentation checkpoint: `7356081623524acb8218d6933a7104eecea36009`.
- Tightened throttle-response ownership from method+path only to **canonical route name + exact method + exact path**.
- Canonical materialization POST and DB-attestation GET/HEAD keep the established hardened HTTP `429` response contract.
- Same-path/method noncanonical routes and unresolved route identity remain framework-owned and are not rewritten.
- Exact engineering envelope: five paths; SHA-256 `a057418c27d32d2a3c0bb88bf694fb7389304c392d101e04a1d45955805c50ab`.
- Final exact-head SHA before merge: `66883b9c838b76fadaa5cd0c38c84a51a5176b7f`.
- Exact-head PR qualification: 28/28 workflow runs successful.
- Product Owner merge-authority run `34748118905` successful.
- Lifecycle gates remain unchanged; see the canonical machine-readable state files.

## 2026-09-13 — Sprint143 closed

- PR #703 squash merged at engineering commit `b307d925400e9707c137f75bcfa3823a182fb84f`.
- Added DB-attestation HEAD throttle-rejection parity to the existing GET hardening contract.
- Exact-head qualification: 27/27 successful; Product Owner authority run `34746609129` successful.

## 2026-09-13 — Sprint142 closed

- PR #701 squash merged at engineering commit `61a6d68b45303a796c5eb7c2afa78b16e740da53`.
- Hardened authenticated throttle rejections for materialization POST and DB-attestation GET while preserving framework rate-limit metadata.
- Exact-head qualification: 26/26 successful; Product Owner authority run `34744364161` successful.

## 2026-09-12 — Sprint139–Sprint141 control-plane hardening

- Sprint139 — authentication-before-throttle hardening and budget-isolation regression.
- Sprint140 — direct middleware authentication-rejection response hardening.
- Sprint141 — registered-route HTTP-kernel propagation of the hardened rejection contract.

## 2026-09-12 — Sprint130–Sprint138 canonical control-plane qualification

- Sprint130 — canonical runtime control-plane token policy.
- Sprint131 — middleware positive-path regression.
- Sprint132 — controller positive-path regression.
- Sprint133 — controller fail-closed regression.
- Sprint134 — cross-provider delivery-gate registration regression.
- Sprint135 — registration metadata/inertness regression including DB-attestation `GET,HEAD` ownership.
- Sprint136 — authenticated HTTP positive path through real route, middleware, and controller.
- Sprint137 — authenticated HTTP fail-closed translation and response non-disclosure.
- Sprint138 — authenticated HTTP throttle enforcement.

## Final Shift Close readiness chain

Material milestones include Sprint88 source-only migration #27, Sprint89 application-readiness, and the later runtime-binding, DB-attestation, authorization, and control-plane qualification chain through Sprint144.

## JRN-010 cash-variance and reviewer-control chain

Representative milestones include Sprint55 expected-cash derivation, Sprint64 cash-variance source foundation, Sprint70 durable cash-variance explanation, and Sprint80 scoped reviewer authorization.

## Earlier canonical engineering history

Earlier work established architecture/governance, session/auth hardening, bounded POS sale/payment/receipt foundations, catalog preparation, shift/register opening, CI/governance controls, and historical regression preservation.

## Current lifecycle boundary

The post-Sprint144 lifecycle state is unchanged. Machine-readable authority remains in:

- `ops/final-shift-close/STATE.json`
- `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`

Current high-level values remain migration #27 `NOT_EXECUTED`, permission provisioning `NONE`, feature `INACTIVE`, deployment `NOT_GRANTED`, preview/production `NOT_AUTHORIZED`, updater `INACTIVE`, durable target selection blocked, and selected target `null`.

Author by Lab | zefry
