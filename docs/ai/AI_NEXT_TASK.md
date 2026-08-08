# AI Next Task

## Stable checkpoint

- Project: OneQay
- Repository: `labzefry/oneQay`
- Developer and Product Engineering Entity: Lab | zefry
- Canonical product attribution: Lab | zefry
- Canonical checkpoint path: `docs/ai/`
- Canonical Phase 0 status: In Progress
- Active program: M5 — Engineering State, CI & Governance Stabilization
- Active micro-milestone: M5.3 — Governance & Program State Synchronization
- Current `main`: `512344d0497787c729242cb1fd2d7d02ecfc40c2`
- Current `main` tree: `0f0af1c1acab208c704fbdf05b19014127abddbb`
- M5.1: PUBLISHED / COMPLETE through PR #66
- M5.2: PUBLISHED / ENFORCEMENT COMPLETE through PR #67
- Sprint 12: Published
- Sprint 13: Published
- Sprint 14: Not Authorized
- Production readiness: NO-GO

## Immediate authorized task

Complete only M5.3 lifecycle processing for the bounded Governance & Program State Synchronization change:

1. preserve the verified exact base `512344d0497787c729242cb1fd2d7d02ecfc40c2` and base tree `0f0af1c1acab208c704fbdf05b19014127abddbb`;
2. use bounded branch `agent/m5-3-governance-program-state-synchronization`;
3. reconcile A-06 Phase 0 semantics, A-07 ROADMAP/TASKS synchronization, and A-08 product metadata/attribution only;
4. preserve Sprint 12 and Sprint 13 publication facts, M5.1 and M5.2 publication facts, and historical lifecycle discrepancies;
5. open and keep the pull request as Draft;
6. verify the final changed-file set and GitHub checks on the exact final head;
7. request independent review on the final exact head;
8. report exact base, head, tree, commit count, changed files, checks, review state, unresolved threads, and lifecycle boundary;
9. stop before Ready or Merge unless separate Product Owner lifecycle authority is provided for the exact current head.

## Canonical semantic boundary

Phase 0 remains **In Progress** as a governance/discovery program state.

Published bounded Platform Foundation source through Sprint 12 and Sprint 13 remains a repository fact. The canonical blocked boundary is **final/business/production application implementation**. This semantic reconciliation does not create new source-code authority.

## M5.2 enforcement facts to preserve

- A-03 — Lifecycle Authority Not Enforced: Resolved.
- A-05 — PHP Regression Not in GitHub CI: Resolved.
- Required protected contexts:
  - `governance-validation`;
  - `markdown-lint`;
  - `secret-scan`;
  - `php-foundation-regression`;
  - `product-owner-merge-authority`.
- Ruleset protections remain strict, require one approving review and latest-push approval, dismiss stale reviews, require review-thread resolution, allow squash-only, block deletion/non-fast-forward updates, and have an empty bypass list.

## M5.3 anomaly scope

- A-06 — Phase 0 semantic ambiguity: reconcile in M5.3.
- A-07 — ROADMAP / TASKS out of sync: reconcile in M5.3.
- A-08 — AI-specific product metadata / attribution: reconcile in M5.3 by keeping canonical product/development attribution as **Lab | zefry** and treating AI collaboration identity as engineering-tooling governance metadata rather than product authorship.
- A-09 — Enterprise Vision Not Yet Canonical: reserved for M6 and excluded from M5.3.

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
- Sprint 13 Product Owner local 402-assertion Composer PASS remains classified as post-publication evidence.
- Historical alternate-head review references remain contamination only and do not replace canonical reviewer evidence.
- M5.1 and M5.2 publication facts remain intact.

## Explicit exclusions

M5.3 does not authorize:

- Ready transition;
- merge or auto-merge;
- Sprint 14 implementation;
- Enterprise Vision implementation or canonicalization;
- final/business/production application implementation;
- executable SQL;
- migration execution;
- production database modification;
- production table creation;
- deployment;
- release;
- ADR/GD promotion;
- JRN resolution;
- production readiness promotion.

## Anti-recursive checkpoint rule

PR number assignment, checks, review, Ready transition, merge, or publication of a change already accurately represented by this checkpoint does not by itself require another checkpoint reconciliation. Update the canonical checkpoint again only when a material repository, program, lifecycle, or authority state changes and the existing checkpoint would otherwise become materially inaccurate.

Attribution: Lab | zefry
