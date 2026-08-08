# AI Next Task

## Stable checkpoint

- Project: oneQay
- Repository: `labzefry/oneQay`
- Developer and Product Engineering Entity: Lab | zefry
- Canonical product attribution: Lab | zefry
- Canonical product name: `oneQay`
- Canonical checkpoint path: `docs/ai/`
- Canonical Phase 0 status: In Progress
- M5.1: PUBLISHED / COMPLETE through PR #66
- M5.2: PUBLISHED / ENFORCEMENT COMPLETE through PR #67
- M5.3: PUBLISHED / COMPLETE through PR #68
- M6 Enterprise Vision Canonicalization: PUBLISHED / PUBLICATION COMPLETE through PR #69
- M6 Post-Publication State Reconciliation: PUBLISHED through PR #70
- Enterprise Vision decision status: Proposed
- Sprint 12: Published
- Sprint 13: Published
- Sprint 14: Not Authorized
- Production readiness: NO-GO

## Live GitHub state rule

This tracked checkpoint MUST NOT claim that a hard-coded commit SHA is permanently the current live GitHub `main` or live tree.

Any SHA recorded below is publication provenance or the verified baseline that existed before the bounded work began.

Before any new branch, lifecycle mutation, Ready, Merge, implementation decision, or milestone transition, perform Minimal Delta Verification against GitHub because GitHub is the Single Source of Truth.

Do not create repetitive state-reconciliation commits merely to replace a stored `current main` SHA after publication. Publishing that replacement creates another SHA and causes an infinite self-referential cycle.

## Verified publication baseline before this closure work

GitHub Delta Verification before starting the authorized closure correction confirmed:

- PR #70: CLOSED / MERGED;
- PR #70 source exact head: `e7eded8d6c661cb5485527d0f1937fb839a3617f`;
- PR #70 source tree: `58e84138173b1e6e5ca2dc7649dbeb89d79e9af0`;
- PR #70 published commit: `b26c4690d68db61118ee1c4cecbb87e9418d791f`;
- PR #70 published tree: `58e84138173b1e6e5ca2dc7649dbeb89d79e9af0`;
- PR #70 published parent: `0b7b28028966ac38af0f32960054210c3a083916`;
- source tree equals published tree: Yes;
- independent reviewer: `zefriansyah`;
- exact-head review: APPROVED;
- required checks: SUCCESS;
- Product Owner READY authority: GRANTED / EXECUTED;
- Product Owner MERGE authority: GRANTED / EXECUTED;
- `product-owner-merge-authority`: SUCCESS before squash merge.

These values are stable publication evidence, not permanently current live-head declarations.

## Immediate authorized task

Complete only:

**M6 Closure — Checkpoint Semantics Correction**

Product Owner START authority:

**START M6 CLOSURE — CHECKPOINT SEMANTICS CORRECTION**

Authorized scope:

1. eliminate self-referential `current main/current tree` semantics from tracked canonical AI checkpoints;
2. record PR #70 publication facts as stable provenance;
3. make live repository state explicitly dependent on GitHub Minimal Delta Verification;
4. remove completed PR #70 reconciliation work from the future/immediate task description;
5. close M6 reconciliation checkpoint semantics cleanly;
6. preserve Enterprise Vision substantive status as Proposed;
7. preserve A-09 representation/publication disposition;
8. preserve A-10 canonical `oneQay` naming disposition;
9. preserve Phase 0 In Progress;
10. preserve Sprint 14 Not Authorized;
11. preserve production readiness NO-GO;
12. preserve ADR/GD/JRN existing states;
13. create only a bounded Draft PR for this closure correction;
14. run required checks;
15. obtain independent review from `zefriansyah` on the final exact head;
16. stop before Ready or Merge unless separate exact-head Product Owner lifecycle authority is supplied.

This is M6 closure work only. It is not Sprint 14 and is not a new implementation milestone.

## M6 publication facts to preserve

### PR #69 — Enterprise Vision Canonicalization

- state: CLOSED / MERGED;
- source head: `e6a3345b09a6b270ac7e09abd78c6356f426e363`;
- source tree: `567df997bae70090b19465c75e4cc3b1e23b6579`;
- published commit: `0b7b28028966ac38af0f32960054210c3a083916`;
- published tree: `567df997bae70090b19465c75e4cc3b1e23b6579`;
- source tree equals published tree: Yes.

Publication canonicalizes the Enterprise Vision representation. It does **not** promote the Enterprise Vision decision status from Proposed to Approved.

### PR #70 — Post-Publication State Reconciliation

- state: CLOSED / MERGED;
- source head: `e7eded8d6c661cb5485527d0f1937fb839a3617f`;
- source tree: `58e84138173b1e6e5ca2dc7649dbeb89d79e9af0`;
- published commit: `b26c4690d68db61118ee1c4cecbb87e9418d791f`;
- published tree: `58e84138173b1e6e5ca2dc7649dbeb89d79e9af0`;
- published parent: `0b7b28028966ac38af0f32960054210c3a083916`;
- source tree equals published tree: Yes;
- independent reviewer: `zefriansyah`;
- exact-head review: APPROVED;
- Product Owner READY: GRANTED / EXECUTED;
- Product Owner MERGE: GRANTED / EXECUTED;
- `product-owner-merge-authority`: SUCCESS before squash merge.

PR #70 completed the post-publication reconciliation. It must not be described as future work after its publication.

## M6 anomaly disposition

- A-09 — Enterprise Vision canonicalization: Resolved at canonical representation/publication level through PR #69; Enterprise Vision decision status remains Proposed until separately approved.
- A-10 — product-name capitalization inconsistency: Resolved for current/future-facing canonical material through PR #69; immutable historical evidence remains preserved.

## Canonical naming rule

The canonical product name is **oneQay**.

Current/future product identity must not use `OneQay`, `ONEQAY`, `Oneqay`, or `oneqay` as the brand name. Repository identifier `labzefry/oneQay`, immutable GitHub URLs, SHAs, branch names, commit messages, and historical quoted evidence are not rewritten merely for brand normalization.

## M5.2 enforcement facts to preserve

Required protected contexts:

- `governance-validation`;
- `markdown-lint`;
- `secret-scan`;
- `php-foundation-regression`;
- `product-owner-merge-authority`.

Ruleset protections remain strict according to the published M5.2 verification record.

## Product boundary

M6 publication, PR #70 reconciliation, and this closure correction do not authorize:

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

## Governance preservation

- Phase 0: In Progress
- Enterprise Vision decision status: Proposed
- ADR-001 through ADR-007: Proposed
- GD-007: Proposed
- JRN-003 and JRN-013: Unresolved
- Final tenant data model: Not Started
- Final business schema: Not Started
- Production migration: Not Performed
- Production database usage: None
- Production table: None
- Sprint 14: Not Authorized
- Production readiness: NO-GO

## Root checkpoint rule

The root files:

- `AI_SESSION_STATE.md`
- `AI_PROJECT_STATE.md`
- `AI_NEXT_TASK.md`

are deprecated pointer stubs only. They are not authoritative and must not be used as active checkpoints.

Canonical state lives under `docs/ai/`.

## Closure stop condition

Prepare the bounded closure correction as a Draft PR and obtain independent review from `zefriansyah` on the final exact head.

Do not mark the closure PR Ready without separate:

`PRODUCT OWNER READY AUTHORIZATION`

bound to the PR number and exact final head.

Do not merge without separate:

`PRODUCT OWNER MERGE AUTHORIZATION`

bound to the PR number and exact final head, recorded repository-native so `product-owner-merge-authority` can pass.

After this M6 closure correction is published, do not infer or start Sprint 14 or any new milestone. The next program action must come from separate explicit Product Owner authority after live GitHub Delta Verification.

Attribution: Lab | zefry
