# Changelog

This changelog records **material canonical progress**, not every intermediate compatibility or CI-only commit. Detailed immutable provenance remains available in merged pull requests, Git history, per-sprint documents under `docs/`, workflows, and machine-readable contracts.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

## 2026-09-12 — Post-Sprint135 canonical documentation reconciliation

- Advanced all root current-state documents from Sprint134 to Sprint135 immediately after engineering closure.
- Kept `PROJECT_MANIFEST.md` as the canonical human-readable status authority.
- Moved the next bounded engineering position to Sprint136 discovery without preselecting a Sprint136 implementation.
- Converted the Sprint135 active full-envelope workflow into successor-compatible historical ownership while preserving its executable regression, contract invariants, Sprint134 regression preservation, and operational NO-GO assertions.
- No application runtime, schema execution, migration execution, permission provisioning, activation, deployment, release, Production, Technical Preview, or updater authority is created by this documentation reconciliation.

## 2026-09-12 — Sprint135 closed

**Sprint135: canonical control-plane positive registration metadata regression**

- PR #686 squash merged.
- Canonical engineering commit: `c13ea1e468154fc1997a48ad5ce7c0132ddbc1c9`.
- Parent canonical checkpoint: `3bc22b9f31b9855ea15b750cd67af1d13537a93c`.
- Added CI-only cross-provider positive registration metadata qualification using one canonical-minimum valid synthetic token.
- Proved both Final Shift Close control-plane routes are simultaneously registered with the intended URI, controller action, HTTP methods, throttle, and token middleware.
- Proved positive route registration itself remains inert: production writer, materializer, DB identity reader, DB attestation service, and controllers remain unresolved; no control-plane request is sent.
- Exact Sprint135 engineering envelope: four paths.
- Frozen envelope SHA-256: `628d99da2ce7f65938dc12df387c829748e36c4a8d8cab18a08eb5402fcf78fc`.
- Exact-head pull-request qualification: 19/19 workflow runs successful.
- Product Owner merge-authority workflow run `34683875845` completed successfully.
- Operational NO-GO state remained unchanged.

## 2026-09-12 — Post-Sprint134 canonical documentation reconciliation

- Reconciled root project-state documents that had still reported post-Sprint48/post-Sprint54 state.
- Established `PROJECT_MANIFEST.md` as the single canonical human-readable current-state authority.
- Defined README as concise entry-point status, TASKS as current workboard, ROADMAP as forward sequencing, and CHANGELOG as material chronology.
- Made the Sprint134 workflow successor-compatible after its canonical merge.
- Preserved the distinction between source engineering readiness and operational activation.

## 2026-09-12 — Sprint134 closed

**Sprint134: canonical control-plane delivery gate registration regression**

- PR #684 squash merged.
- Canonical engineering commit: `185cbe9ddd4346b9d8c9e7ac7af283a20b9617bd`.
- Added CI-only delivery-gate registration regression proving both control-plane routes remain absent for a canonical-length token containing a disallowed character.
- Exact Sprint134 source envelope: five paths.
- Frozen path SHA-256: `73b87b460ef349a05ab801d10fc6dd4a19d056625f4a042ff621ab96a12d2c0e`.
- Exact-head qualification: 18/18 workflow runs successful.
- Operational NO-GO state remained unchanged.

## 2026-09-12 — Sprint130–Sprint133 canonical control-plane hardening

### Sprint133

- Added direct-controller fail-closed regression for Final Shift Close materialization and DB-binding attestation failure paths.
- Preserved synthetic isolation, no internal leakage, no real filesystem/database operational access, and NO-GO lifecycle boundaries.

### Sprint132

- Added CI-only synthetic direct-controller positive-path regression without activating operational routes or production adapters.

### Sprint131

- Added CI-only synthetic middleware positive-path regression for canonical bearer handling.

### Sprint130

- Established the canonical runtime control-plane token policy.
- Canonical token length: minimum 32, maximum 512.
- Canonical allowed-character policy: `[A-Za-z0-9._~+=/-]`.
- Preserved exact bearer semantics and fail-closed invalid-token handling.

## 2026-09-05 onward — Final Shift Close readiness and runtime-control-plane chain

Material milestones include:

- **Sprint88** — migration #27 materialized as source-only and explicitly `NOT_EXECUTED`.
- **Sprint89** — Final Shift Close application-readiness contract and fail-closed prerequisites.
- Later bounded sprints established and hardened application/service boundaries, provider behavior, runtime-binding manifest foundations, DB-binding attestation, authorization, target-readiness, historical compatibility, token policy, controller qualification, and route-registration gates.
- The canonical control-plane hardening chain currently reaches Sprint135.

Throughout this sequence, migration execution, permission provisioning, deployment, Technical Preview activation, Production activation, and updater activation remained separately gated.

## 2026-09-03 to 2026-09-04 — JRN-010 cash-variance and reviewer-control chain

Representative material milestones:

- **Sprint55** — expected-cash derivation entry gate;
- **Sprint64** — cash-variance source foundation;
- **Sprint70** — durable cash-variance explanation foundation;
- **Sprint80** — scoped cash-variance reviewer authorization policy and implementation chain.

The broader chain established immutable sale-to-shift binding, cash-variance evidence, explanation/adjudication, maker-checker, and reviewer authorization foundations while preserving fail-closed lifecycle controls.

## Earlier canonical engineering history

Earlier repository work established the architecture/governance platform, authentication/session hardening, bounded POS sale/payment/receipt foundations, catalog preparation, shift/register opening, CI/governance controls, updater/deployment safety foundations, and historical regression preservation.

Detailed historical provenance remains in Git history and per-sprint evidence rather than being duplicated into this current changelog.

## Current lifecycle boundary

As of the post-Sprint135 engineering checkpoint:

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
