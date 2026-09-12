# Changelog

This changelog records **material canonical progress**, not every intermediate compatibility or CI-only commit. Detailed immutable provenance remains available in merged pull requests, Git history, per-sprint documents under `docs/`, workflows, and machine-readable contracts.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

## 2026-09-12 — Sprint140 closed

**Sprint140: canonical control-plane auth rejection response hardening regression**

- PR #697 squash merged.
- Canonical engineering commit: `446f9ff80f646d2e77d0885b887da58a37994d28`.
- Parent canonical documentation checkpoint: `0d08996238d7aec0db3476d0dedf6d69ef3f0669`.
- Hardened both Final Shift Close authentication rejection surfaces without changing token policy, route registration, middleware ordering, throttle limits, controllers, or application-service semantics.
- Materialization preserves HTTP `503` for invalid expected-token configuration and HTTP `401` for missing, malformed, or mismatched bearer credentials.
- DB-binding attestation preserves cloaked HTTP `404` for invalid expected-token configuration and missing, malformed, or mismatched bearer credentials.
- All auth rejection responses are empty-body, `no-store`/`private`, `no-cache`, `nosniff`, robot-excluded, and non-reflective.
- Matching bearer credentials continue to a synthetic HTTP `204` continuation in the Sprint140 regression; production manifest-writer, database-identity-reader, and both controllers remain unresolved there.
- Updated historical Sprint126, Sprint130, and Sprint131 static representation assertions to recognize hardened rejection responses while preserving their executable regression ownership.
- Exact Sprint140 engineering envelope: nine paths.
- Frozen envelope SHA-256: `58f0fe3105a12fc3c6cac98e736743349237a5221a63376f668358c5c09fe1b3`.
- Final exact-head engineering SHA before merge: `3ef7024368d8b68c98974535d6aa44f925e4ced5`.
- Exact-head pull-request qualification: 25/25 workflow runs successful.
- Product Owner merge-authority workflow run `34707917274` completed successfully.
- Operational NO-GO state remained unchanged.

## 2026-09-12 — Sprint139 closed

**Sprint139: canonical control-plane auth-before-throttle regression**

- PR #694 squash merged at engineering commit `4028e05485589be649eb437c800b70c2990decf2`.
- Corrected both control-plane providers so canonical bearer authentication executes before unchanged `throttle:1,1` / `throttle:2,1` accounting.
- Proved structurally valid wrong-bearer traffic cannot consume authenticated request budget from the same source IP.
- Exact engineering envelope: six paths; frozen SHA-256 `be9546afdf82465da5f9d160845edc4cf6599f306445f941226e12288a7b2286`.
- Exact-head qualification: 24/24 successful; Product Owner merge-authority run `34705209701` successful.
- Operational NO-GO state remained unchanged.

## 2026-09-12 — Sprint138 closed

**Sprint138: canonical control-plane authenticated HTTP throttle regression**

- PR #692 squash merged at engineering commit `7f562ea48a0255b7e9803f6b268bd00a7e3b3dcf`.
- Proved materialization `1/min` and DB-attestation `2/min` authenticated throttle enforcement with excess requests rejected as HTTP `429` before repeated synthetic side effects.
- Exact-head qualification: 22/22 successful; Product Owner merge-authority run `34701268872` successful.
- Operational NO-GO state remained unchanged.

## 2026-09-12 — Sprint137 closed

**Sprint137: canonical control-plane authenticated HTTP fail-closed regression**

- PR #690 squash merged at engineering commit `62196919fb2c2a172bc0a290159aa26a045d4ed9`.
- Proved authenticated HTTP failures traverse registered route, real token middleware, and real controller while returning exact fail-closed HTTP `503` contracts without sensitive leakage.
- Production filesystem/database adapters remained unresolved.

## 2026-09-12 — Sprint136 closed

**Sprint136: canonical control-plane authenticated HTTP positive path regression**

- PR #688 squash merged at engineering commit `2a92a870d9c8388bdb9ac4f516e9d671237ce116`.
- Proved canonical-valid bearer credentials traverse registered routes, real token middleware, and real controllers with synthetic side-effect application services.
- Production filesystem/database adapters remained isolated.

## 2026-09-12 — Sprint134–Sprint135 canonical control-plane registration qualification

- Sprint134 added cross-provider delivery-gate route-absence regression for unqualified token configuration and established canonical root documentation governance.
- Sprint135 added canonical-valid-token cross-provider registration metadata/inertness qualification.
- Both retained operational NO-GO boundaries.

## 2026-09-12 — Sprint130–Sprint133 canonical control-plane hardening

- **Sprint130** — canonical token validity/bearer policy.
- **Sprint131** — CI-only synthetic middleware positive path.
- **Sprint132** — CI-only synthetic direct-controller positive path.
- **Sprint133** — direct-controller fail-closed translation and leakage regression.

## 2026-09-05 onward — Final Shift Close readiness and runtime-control-plane chain

Material milestones include Sprint88 source-only migration #27, Sprint89 application-readiness, later application/provider/runtime-binding/DB-attestation/authorization/target-readiness qualification, and the Sprint130–Sprint140 canonical control-plane hardening chain.

Throughout this sequence, migration execution, permission provisioning, deployment, Technical Preview activation, Production activation, and updater activation remained separately gated.

## 2026-09-03 to 2026-09-04 — JRN-010 cash-variance and reviewer-control chain

Representative milestones include Sprint55 expected-cash derivation, Sprint64 cash-variance source foundation, Sprint70 durable cash-variance explanation, and Sprint80 scoped reviewer authorization. The broader chain established immutable sale-to-shift binding, explanation/adjudication, maker-checker, and reviewer authorization foundations.

## Earlier canonical engineering history

Earlier repository work established the architecture/governance platform, authentication/session hardening, bounded POS sale/payment/receipt foundations, catalog preparation, shift/register opening, CI/governance controls, updater/deployment safety foundations, and historical regression preservation. Detailed provenance remains in Git history and per-sprint evidence.

## Current lifecycle boundary

As of the post-Sprint140 engineering checkpoint:

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
