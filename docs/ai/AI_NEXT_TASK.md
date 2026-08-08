# AI Next Task

## Stable checkpoint

- Project: oneQay
- Repository: `labzefry/oneQay`
- Developer and Product Engineering Entity: Lab | zefry
- Canonical product attribution: Lab | zefry
- Canonical product name: `oneQay`
- Canonical checkpoint path: `docs/ai/`
- Canonical Phase 0 status: In Progress
- Active program: M6 — Post-Publication State Reconciliation
- Active micro-milestone: M6 — Publication State Reconciliation
- Current `main`: `0b7b28028966ac38af0f32960054210c3a083916`
- Current `main` tree: `567df997bae70090b19465c75e4cc3b1e23b6579`
- M5.1: PUBLISHED / COMPLETE through PR #66
- M5.2: PUBLISHED / ENFORCEMENT COMPLETE through PR #67
- M5.3: PUBLISHED / COMPLETE through PR #68
- M6 lifecycle: PUBLISHED / PUBLICATION COMPLETE through PR #69
- M6 source head: `e6a3345b09a6b270ac7e09abd78c6356f426e363`
- M6 source/published tree: `567df997bae70090b19465c75e4cc3b1e23b6579`
- M6 published commit: `0b7b28028966ac38af0f32960054210c3a083916`
- Enterprise Vision decision status: Proposed
- Sprint 12: Published
- Sprint 13: Published
- Sprint 14: Not Authorized
- Production readiness: NO-GO

## Immediate authorized task

Complete only M6 post-publication state reconciliation for the verified PR #69 publication.

Verified publication baseline:

- base branch: `main`;
- exact current `main`: `0b7b28028966ac38af0f32960054210c3a083916`;
- exact current `main` tree: `567df997bae70090b19465c75e4cc3b1e23b6579`;
- reconciliation branch: `agent/m6-post-publication-state-reconciliation`.

Authorized reconciliation work:

1. record PR #69 as PUBLISHED / publication-complete;
2. record exact M6 source and published identity;
3. synchronize mutable canonical state that still references pre-publication M5.3 `main`;
4. classify A-09 as resolved at canonical-representation/publication level while preserving Enterprise Vision decision status as Proposed;
5. classify A-10 as resolved for current/future-facing canonical product naming while preserving immutable historical evidence;
6. preserve Phase 0 In Progress, Sprint 14 Not Authorized, production readiness NO-GO, and final/business/production application implementation Blocked;
7. keep ADR/GD/JRN statuses unchanged unless separately authorized;
8. create a bounded Draft PR for reconciliation;
9. obtain independent review on the final exact head;
10. stop before Ready or Merge unless separate exact-head Product Owner lifecycle authority is supplied.

## Canonical naming rule

The canonical product name is **oneQay**.

Current/future product identity must not use `OneQay`, `ONEQAY`, `Oneqay`, or `oneqay` as the brand name. Repository identifier `labzefry/oneQay`, immutable GitHub URLs, SHAs, branch names, commit messages, and historical quoted evidence are not rewritten merely for brand normalization.

## M6 publication facts to preserve

- PR #69: CLOSED / MERGED.
- Source head: `e6a3345b09a6b270ac7e09abd78c6356f426e363`.
- Source tree: `567df997bae70090b19465c75e4cc3b1e23b6579`.
- Published commit: `0b7b28028966ac38af0f32960054210c3a083916`.
- Published tree: `567df997bae70090b19465c75e4cc3b1e23b6579`.
- Source tree equals published tree: Yes.
- Independent reviewer: `zefriansyah`.
- Exact-head review state: APPROVED.
- Required technical checks: SUCCESS.
- Product Owner READY authority: separately recorded and executed.
- Product Owner MERGE authority: separately recorded and executed.
- `product-owner-merge-authority`: SUCCESS before squash merge.

Publication canonicalizes the Enterprise Vision representation. It does **not** promote the Enterprise Vision decision status from Proposed to Approved.

## M5 publication facts to preserve

- M5.1 — Canonical State Reconciliation: PUBLISHED / COMPLETE through PR #66.
- M5.2 — CI & Lifecycle Control Hardening: PUBLISHED / ENFORCEMENT COMPLETE through PR #67.
- M5.3 — Governance & Program State Synchronization: PUBLISHED / COMPLETE through PR #68.
- A-03 — Lifecycle Authority Not Enforced: Resolved.
- A-05 — PHP Regression Not in GitHub CI: Resolved.
- A-06 — Phase 0 semantic ambiguity: Resolved through M5.3 publication.
- A-07 — ROADMAP / TASKS synchronization: Resolved through M5.3 publication.
- A-08 — product metadata / attribution reconciliation: Resolved through M5.3 publication.

## M6 anomaly disposition

- A-09 — Enterprise Vision canonicalization: Resolved at canonical representation/publication level through PR #69; Enterprise Vision decision status remains Proposed until separately approved.
- A-10 — product-name capitalization inconsistency: Resolved for current/future-facing canonical material through PR #69; immutable historical evidence remains preserved.

## M5.2 enforcement facts to preserve

Required protected contexts:

- `governance-validation`;
- `markdown-lint`;
- `secret-scan`;
- `php-foundation-regression`;
- `product-owner-merge-authority`.

Ruleset protections remain strict, require one approving review and latest-push approval, dismiss stale reviews, require review-thread resolution, allow squash-only, block deletion/non-fast-forward updates, and have an empty bypass list according to the published M5.2 verification record.

## Product boundary

M6 publication and reconciliation do not authorize:

- Enterprise Vision promotion from Proposed to Approved;
- Sprint 14 implementation;
- final/business/production application implementation;
- new application source-code implementation;
- database/schema implementation;
- executable SQL;
- migration execution;
- production database modification;
- deployment;
- release;
- production-readiness promotion;
- ADR/GD promotion;
- JRN resolution.

Capability-map presence does not imply implementation authority.

## Root checkpoint rule

The root files:

- `AI_SESSION_STATE.md`
- `AI_PROJECT_STATE.md`
- `AI_NEXT_TASK.md`

are deprecated pointer stubs only. They are not authoritative and must not be used as active checkpoints.

Canonical state lives under `docs/ai/`.

## Historical facts that must not be rewritten

- Published Sprint 12 and Sprint 13 repository facts remain intact.
- PR #64 and PR #65 retain their historical lifecycle discrepancies.
- Sprint 13 Product Owner local 402-assertion Composer PASS remains post-publication evidence.
- Historical alternate-head review references remain contamination only and do not replace canonical reviewer evidence.
- Historical lifecycle discrepancies are not retroactively normalized by M6.

## Lifecycle stop condition

The reconciliation branch may be prepared as a Draft PR and independently reviewed.

Do not mark the reconciliation PR Ready without separate:

`PRODUCT OWNER READY AUTHORIZATION`

bound to the PR number and exact current head.

Do not merge without separate:

`PRODUCT OWNER MERGE AUTHORIZATION`

bound to the PR number and exact current head, recorded repository-native so `product-owner-merge-authority` can pass.

Attribution: Lab | zefry
