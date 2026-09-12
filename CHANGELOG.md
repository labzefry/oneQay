# Changelog

This changelog records **material canonical progress**, not every intermediate compatibility or CI-only commit. Detailed immutable provenance remains available in merged pull requests, Git history, per-sprint documents under `docs/`, workflows, and machine-readable contracts.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

## 2026-09-12 — Sprint136 closed

**Sprint136: canonical control-plane authenticated HTTP positive path regression**

- PR #688 squash merged.
- Canonical engineering commit: `2a92a870d9c8388bdb9ac4f516e9d671237ce116`.
- Parent canonical documentation checkpoint: `8e839881d7022df46516cd5a8ce83cdc58ee7a1b`.
- Added CI-only canonical-valid-token authenticated HTTP positive-path qualification for both Final Shift Close control planes.
- Proved a canonical minimum-length synthetic bearer credential traverses the registered route, real token middleware, and real controller for materialization and DB-binding attestation.
- Kept side-effect services synthetic/test-owned: in-memory manifest writer and database identity reader were used, production filesystem/database adapter bindings were bomb-isolated, and no Laravel `oneqay` connection was opened.
- Preserved HTTP 200 response contracts, no-store/private headers, migration #27 `NOT_EXECUTED`, DB attestation `READ_ONLY`, and secret-free deterministic evidence.
- Exact Sprint136 engineering envelope: four paths.
- Frozen envelope SHA-256: `b459daf7b7f52fd2b028452b821dfa13760f8b050116bdf2fdb5db4d8485fca8`.
- Final exact-head engineering SHA before merge: `93c447566efbd75e3e206ab0756cf49716231b11`.
- Exact-head pull-request qualification: 20/20 workflow runs successful.
- Product Owner merge-authority workflow run `34685054761` completed successfully.
- Operational NO-GO state remained unchanged.

## 2026-09-12 — Post-Sprint135 canonical documentation reconciliation

- Advanced all root current-state documents from Sprint134 to Sprint135 immediately after engineering closure.
- Kept `PROJECT_MANIFEST.md` as the canonical human-readable status authority.
- Moved the next bounded engineering position to Sprint136 discovery without preselecting an implementation.
- Converted the Sprint135 active full-envelope workflow into successor-compatible historical ownership while preserving executable regression and operational NO-GO assertions.

## 2026-09-12 — Sprint135 closed

**Sprint135: canonical control-plane positive registration metadata regression**

- PR #686 squash merged.
- Canonical engineering commit: `c13ea1e468154fc1997a48ad5ce7c0132ddbc1c9`.
- Added CI-only cross-provider positive registration metadata qualification using one canonical-minimum valid synthetic token.
- Proved both control-plane routes register with intended URI, controller action, methods, throttle, and token middleware while registration remains inert.
- Exact engineering envelope: four paths; frozen SHA-256 `628d99da2ce7f65938dc12df387c829748e36c4a8d8cab18a08eb5402fcf78fc`.
- Exact-head qualification: 19/19 successful; Product Owner merge-authority run `34683875845` successful.
- Operational NO-GO state remained unchanged.

## 2026-09-12 — Sprint134 closed and documentation governance established

- Sprint134 PR #684 squash merged at engineering commit `185cbe9ddd4346b9d8c9e7ac7af283a20b9617bd`.
- Added cross-provider delivery-gate route-absence regression for unqualified token configuration.
- Post-Sprint134 reconciliation established `PROJECT_MANIFEST.md` as the canonical human-readable project-state authority and defined README, TASKS, ROADMAP, and CHANGELOG responsibilities.

## 2026-09-12 — Sprint130–Sprint133 canonical control-plane hardening

- **Sprint130** — canonical token validity/bearer policy.
- **Sprint131** — CI-only synthetic middleware positive path.
- **Sprint132** — CI-only synthetic direct-controller positive path.
- **Sprint133** — direct-controller fail-closed translation and leakage regression.

## 2026-09-05 onward — Final Shift Close readiness and runtime-control-plane chain

Material milestones include Sprint88 source-only migration #27, Sprint89 application-readiness, later application/provider/runtime-binding/DB-attestation/authorization/target-readiness qualification, and the Sprint130–Sprint136 canonical control-plane hardening chain.

Throughout this sequence, migration execution, permission provisioning, deployment, Technical Preview activation, Production activation, and updater activation remained separately gated.

## 2026-09-03 to 2026-09-04 — JRN-010 cash-variance and reviewer-control chain

Representative milestones include Sprint55 expected-cash derivation, Sprint64 cash-variance source foundation, Sprint70 durable cash-variance explanation, and Sprint80 scoped reviewer authorization. The broader chain established immutable sale-to-shift binding, explanation/adjudication, maker-checker, and reviewer authorization foundations.

## Earlier canonical engineering history

Earlier repository work established the architecture/governance platform, authentication/session hardening, bounded POS sale/payment/receipt foundations, catalog preparation, shift/register opening, CI/governance controls, updater/deployment safety foundations, and historical regression preservation. Detailed provenance remains in Git history and per-sprint evidence.

## Current lifecycle boundary

As of the post-Sprint136 engineering checkpoint:

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
