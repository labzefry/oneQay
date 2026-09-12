# Changelog

This changelog records **material canonical progress**, not every intermediate compatibility or CI-only commit. Detailed immutable provenance remains available in merged pull requests, Git history, per-sprint documents under `docs/`, workflows, and machine-readable contracts.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

## 2026-09-12 — Post-Sprint134 canonical documentation reconciliation

### Documentation governance

- Reconciled root project-state documents that were still reporting post-Sprint48/post-Sprint54 state.
- Established `PROJECT_MANIFEST.md` as the single canonical human-readable current-state authority.
- Defined README as concise entry-point status, TASKS as current workboard, ROADMAP as forward sequencing, and CHANGELOG as material chronology.
- Preserved the distinction between source engineering readiness and operational activation.
- No application source, schema, runtime, migration execution, permission provisioning, activation, deployment, release, updater, or production authority is created by this documentation reconciliation.

## 2026-09-12 — Sprint134 closed

**Sprint134: canonical control-plane delivery gate registration regression**

- PR #684 squash merged.
- Canonical engineering commit: `185cbe9ddd4346b9d8c9e7ac7af283a20b9617bd`.
- Added CI-only delivery-gate registration regression proving both Final Shift Close control-plane routes remain absent for a canonical-length token containing a disallowed character.
- Preserved Sprint133 historical successor compatibility and transferred full active-envelope ownership to Sprint134 workflow.
- Exact Sprint134 source envelope: five paths.
- Frozen path SHA-256: `73b87b460ef349a05ab801d10fc6dd4a19d056625f4a042ff621ab96a12d2c0e`.
- Exact-head pull-request qualification: 18/18 workflow runs successful.
- Product Owner merge-authority workflow completed successfully.
- Operational NO-GO state remained unchanged.

## 2026-09-12 — Sprint130–Sprint133 canonical control-plane hardening

### Sprint133

- Added direct-controller fail-closed regression for Final Shift Close materialization and DB-binding attestation failure paths.
- Preserved synthetic isolation, no internal leakage, no real filesystem/database operational access, and NO-GO lifecycle boundaries.

### Sprint132

- Added CI-only synthetic direct-controller positive-path regression.
- Qualified synthetic HTTP success behavior without turning the canonical operational routes or production adapters into an activated runtime path.

### Sprint131

- Added CI-only synthetic middleware positive-path regression for canonical bearer handling.
- Preserved no-real-route/no-operational-target boundary.

### Sprint130

- Established canonical runtime control-plane token policy.
- Canonical token length: minimum 32, maximum 512.
- Canonical allowed-character policy: `[A-Za-z0-9._~+=/-]`.
- Preserved exact bearer semantics, fail-closed invalid token handling, and constant-time comparison behavior where applicable.

## 2026-09-05 onward — Final Shift Close readiness and runtime-control-plane chain

The repository transitioned from cash-variance/JRN readiness into a bounded Final Shift Close engineering sequence.

Material milestones include:

- **Sprint88** — materialized Final Shift Close migration #27 as **source-only**, explicitly `NOT_EXECUTED` and not operationally activated.
- **Sprint89** — selected the Final Shift Close application-readiness contract and fail-closed prerequisites.
- Subsequent bounded sprints established and hardened application/service boundaries, provider behavior, runtime-binding manifest foundations, DB-binding attestation, runtime authorization controls, historical compatibility, token policy, controller qualification, and delivery-registration gates.
- The chain culminated in Sprint130–Sprint134 canonical runtime-control-plane regression coverage.

Throughout this sequence, migration execution, permission provisioning, deployment, Technical Preview activation, Production activation, and updater activation remained separately gated.

## 2026-09-03 to 2026-09-04 — JRN-010 cash-variance and reviewer-control chain

Material post-Sprint54 progress includes:

- **Sprint55** — bounded expected-cash derivation entry gate.
- **Sprint56+** — immutable sale-to-shift binding and related prerequisite qualification.
- **Sprint61–Sprint63** — cash-variance entry/source-readiness and frozen source-envelope work.
- **Sprint64** — replayed/published cash-variance source foundation.
- **Sprint65+** — cash-variance adjudication readiness.
- **Sprint69–Sprint70** — variance-explanation source envelope and durable cash-variance explanation foundation.
- **Sprint71+** — explanation-author authorization and subsequent review-decision/maker-checker controls.
- **Sprint79–Sprint80** — review-decision source foundation followed by dedicated scoped cash-variance reviewer authorization policy and implementation qualification.

These changes materially advanced POS/JRN evidence and authorization controls while preserving fail-closed lifecycle boundaries.

## Earlier canonical engineering history

Before Sprint55, the repository had already established the main architecture/governance platform and bounded POS foundations, including:

- tenant-aware modular architecture and deny-by-default authorization;
- authentication/MFA/session hardening chains;
- POS sale completion/payment/receipt evidence foundation;
- tenant/outlet-scoped catalog preparation;
- shift/register opening foundation;
- governance, CI, historical regression, updater/deployment control, and exact-head merge controls.

The previous root changelog contained very granular per-sprint reconciliation text through post-Sprint48/post-Sprint54. Those entries remain recoverable from Git history and corresponding `docs/SPRINT*.md` evidence. This reconciled changelog intentionally removes the false impression that those old headings are the current state.

## Current lifecycle boundary

As of the post-Sprint134 engineering checkpoint:

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
