# Changelog

This changelog records **material canonical progress**, not every intermediate compatibility or CI-only commit. Detailed immutable provenance remains available in merged pull requests, Git history, per-sprint documents under `docs/`, workflows, and machine-readable contracts.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

## 2026-09-13 — Sprint143 closed

**Sprint143: DB attestation HEAD throttle rejection response hardening**

- PR #703 squash merged.
- Canonical engineering commit: `b307d925400e9707c137f75bcfa3823a182fb84f`.
- Parent canonical documentation checkpoint: `cdda490be67ef11cac95cb1449e7de86f176bef6`.
- Closed the concrete method-parity gap where the canonical DB-attestation route exposes `GET,HEAD` but Sprint142 hardening owned only GET.
- Extended only the throttle-response hardener's DB-attestation method ownership from GET-only to GET-or-HEAD.
- Materialization remains POST-only; route registration, auth-before-throttle ordering, controllers, token policy, services, and exact DB-attestation `throttle:2,1` remain unchanged.
- Two authenticated same-IP HEAD requests return HTTP `200`; the third remains HTTP `429` before any third synthetic database identity read.
- The throttled HEAD response is empty-body, `no-store, private`, `Pragma: no-cache`, `nosniff`, robot-excluded, and preserves Laravel `Retry-After`, `X-RateLimit-Limit`, `X-RateLimit-Remaining`, and `X-RateLimit-Reset` metadata.
- Sprint135 route metadata and Sprint138–Sprint142 executable ownership remain preserved.
- Exact Sprint143 engineering envelope: five paths.
- Frozen envelope SHA-256: `30df6d628ffa67335dd51275137c022cd461264a137b28b5d9fa623a1d1298ed`.
- Final exact-head engineering SHA before merge: `440a345c99fb9df6e5de3ef717f6f2d967ea26cb`.
- Exact-head pull-request qualification: 27/27 workflow runs successful.
- Product Owner merge-authority workflow run `34746609129` completed successfully.
- Operational NO-GO state remained unchanged.

## 2026-09-13 — Sprint142 closed

**Sprint142: canonical control-plane throttle rejection response hardening**

- PR #701 squash merged at engineering commit `61a6d68b45303a796c5eb7c2afa78b16e740da53`.
- Hardened authenticated HTTP `429` throttle rejections for materialization POST and DB-attestation GET while preserving auth-before-throttle ordering and exact `1,1` / `2,1` limits.
- Preserved Laravel rate-limit metadata while standardizing empty-body privacy/security headers.
- Exact-head qualification: 26/26 successful; Product Owner merge-authority run `34744364161` successful.
- Operational NO-GO state remained unchanged.

## 2026-09-12 — Sprint141 closed

**Sprint141: canonical HTTP auth rejection response regression**

- PR #699 squash merged at engineering commit `81d4d19c61e99746495bec804c0e7d8a9778a257`.
- Proved Sprint140 auth-rejection response hardening survives real registered routes and the Laravel HTTP kernel for missing, malformed, and mismatched bearer credentials.
- Exact-head qualification: 25/25 successful; Product Owner merge-authority run `34709040922` successful.

## 2026-09-12 — Sprint140 closed

**Sprint140: canonical control-plane auth rejection response hardening regression**

- PR #697 squash merged at engineering commit `446f9ff80f646d2e77d0885b887da58a37994d28`.
- Hardened materialization `503/401` and DB-attestation cloaked `404` auth rejection surfaces with empty-body privacy/security metadata.
- Exact-head qualification: 25/25 successful; Product Owner merge-authority run `34707917274` successful.

## 2026-09-12 — Sprint139 closed

- PR #694 squash merged at engineering commit `4028e05485589be649eb437c800b70c2990decf2`.
- Corrected both control-plane providers so authentication executes before unchanged throttle accounting.
- Proved wrong-bearer traffic cannot consume authenticated request budget.

## 2026-09-12 — Sprint138 closed

- PR #692 squash merged at engineering commit `7f562ea48a0255b7e9803f6b268bd00a7e3b3dcf`.
- Proved materialization `1/min` and DB-attestation `2/min` authenticated throttle enforcement before repeated synthetic side effects.

## 2026-09-12 — Sprint130–Sprint137 canonical control-plane qualification

- Sprint130 — canonical runtime control-plane token policy.
- Sprint131 — middleware positive-path regression.
- Sprint132 — controller positive-path regression.
- Sprint133 — controller fail-closed regression.
- Sprint134 — cross-provider delivery-gate registration regression.
- Sprint135 — canonical-valid-token registration metadata/inertness regression, including DB-attestation `GET,HEAD` route ownership.
- Sprint136 — authenticated HTTP positive path through real route, middleware, and controller.
- Sprint137 — authenticated HTTP fail-closed translation and response non-disclosure.

## 2026-09-05 onward — Final Shift Close readiness chain

Material milestones include Sprint88 source-only migration #27, Sprint89 application-readiness, later application/provider/runtime-binding/DB-attestation/authorization/target-readiness qualification, and the Sprint130–Sprint143 canonical control-plane hardening chain.

Throughout this sequence, migration execution, permission provisioning, deployment, Technical Preview activation, Production activation, and updater activation remained separately gated.

## 2026-09-03 to 2026-09-04 — JRN-010 cash-variance and reviewer-control chain

Representative milestones include Sprint55 expected-cash derivation, Sprint64 cash-variance source foundation, Sprint70 durable cash-variance explanation, and Sprint80 scoped reviewer authorization. The broader chain established immutable sale-to-shift binding, explanation/adjudication, maker-checker, and reviewer authorization foundations.

## Earlier canonical engineering history

Earlier repository work established the architecture/governance platform, authentication/session hardening, bounded POS sale/payment/receipt foundations, catalog preparation, shift/register opening, CI/governance controls, updater/deployment safety foundations, and historical regression preservation. Detailed provenance remains in Git history and per-sprint evidence.

## Current lifecycle boundary

As of the post-Sprint143 engineering checkpoint:

- migration #27 execution: `NOT_EXECUTED`;
- permission provisioning: `NONE`;
- feature activation: `INACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview activation: `NOT_AUTHORIZED`;
- Production activation: `NOT_AUTHORIZED`;
- updater activation: `INACTIVE`;
- durable activation target: `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- selected target: `null`.

See `ops/final-shift-close/STATE.json` and `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json` for machine-readable operational authority.

Author by Lab | zefry
