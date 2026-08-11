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
- M6 Closure — Checkpoint Semantics Correction: PUBLISHED / COMPLETE through PR #71
- GOV-051 Enterprise Vision substantive decision: APPROVED / DECISION COMPLETE
- Enterprise Vision decision status: Approved
- Sprint 12: Published
- Sprint 13: Published
- Sprint 14: Not Authorized
- Production readiness: NO-GO

## Live GitHub state rule

This tracked checkpoint MUST NOT claim that a hard-coded commit SHA is permanently the current live GitHub `main` or live tree.

Any SHA recorded below is publication provenance or the verified baseline that existed before the bounded work began.

Before any new branch, lifecycle mutation, Ready, Merge, implementation decision, or milestone transition, perform Minimal Delta Verification against GitHub because GitHub is the Single Source of Truth.

Do not create repetitive state-reconciliation commits merely to replace a stored `current main` SHA after publication. Publishing that replacement creates another SHA and causes an infinite self-referential cycle.

## Verified publication baseline before this historical decision-record work

Fresh GitHub Delta Verification before starting the authorized GOV-051 decision record confirmed:

- PR #71: CLOSED / MERGED;
- PR #71 source exact head: `19c723f32c62c982a80e1d8a520ab6ff5a189e2c`;
- PR #71 source tree: `4d16f322b1bc8f2b666eef87ce4a1caaa6755e4f`;
- PR #71 published commit / verified baseline: `762149757e4bc1fa79cc16bc4761f4147be0f7ea`;
- PR #71 published tree / verified baseline tree: `4d16f322b1bc8f2b666eef87ce4a1caaa6755e4f`;
- PR #71 published parent: `b26c4690d68db61118ee1c4cecbb87e9418d791f`;
- source tree equals published tree: Yes;
- canonical Enterprise Vision artifact: `docs/handbook/ENTERPRISE_VISION.md`;
- approved canonical artifact blob: `bb1cace72a6fdb359e15e22467443d9f3916c336`.

These values are stable publication and decision evidence, not permanently current live-head declarations.

## Current next-work authority

**NO STANDING IMPLEMENTATION OR MILESTONE AUTHORITY.**

The next governed work item must be explicitly authorized by the Product Owner after fresh GitHub Minimal Delta Verification.

This checkpoint does not automatically designate Phase 0 exit, Sprint 14, implementation, deployment, release, Production, or another milestone as the next authorized work item.

Substantive decision authority, preparation authority, independent exact-head review, Product Owner READY authority, and Product Owner MERGE authority remain separate whenever applicable.

## Current canonical decision state

- DEC-000 Product Vision and Decision Rights: **APPROVED / DECISION COMPLETE**; GD-003 is Approved through DEC-000.
- DEC-001 MVP Scope and Non-Scope: **APPROVED / DECISION COMPLETE**; first bounded MVP delivery slice is **POS CORE TRANSACTION & OUTLET OPERATIONS**.
- DEC-002 through DEC-009: **APPROVED / DECISION COMPLETE** according to their respective bounded records; ADR-001 through ADR-007 are Accepted through their separately governed DEC reconciliations.
- DEC-004 additionally owns ADR-008 as its Accepted Android-delivery representation.
- DEC-010 Product License and Third-Party Notice Policy: **APPROVED / DECISION COMPLETE**; oneQay remains **PROPRIETARY / ALL RIGHTS RESERVED**.
- DEC-011 Data Retention, Privacy, and Jurisdiction: **APPROVED / DECISION COMPLETE**; initial jurisdiction remains not yet canonically selected.
- DEC-012 RPO/RTO and Support Objectives: **APPROVED / DECISION COMPLETE**; final numerical Production RPO/RTO/SLO and customer-contractual SLA remain deferred.
- DEC-010 Supplement: **APPROVED / DECISION COMPLETE / PUBLISHED through PR #87**; Apache ECharts remains a default Web/PWA visualization technology candidate / approved technology direction only; package/dependency adoption and implementation remain separately gated.
- GD-007 remains Proposed.
- JRN-003 and JRN-013 remain Unresolved.
- Phase 0 remains In Progress.
- Sprint 14 remains Not Authorized.
- Production readiness remains NO-GO.

## Historical GOV-051 decision-record work

The GOV-051 decision-record lifecycle below is preserved as historical evidence and is not an active task.

Historical Product Owner START authority:

**START GOV-051 — ENTERPRISE VISION DECISION RECORD**

Historical authorized scope:

1. record the already-given GOV-051 Product Owner substantive decision as Approved;
2. record the Approved Enterprise Vision as **Enterprise Intelligent Business Management Platform**;
3. preserve the approved canonical artifact identity and decision provenance;
4. mark GOV-051 as completed in the operational task register;
5. correct stale wording that says PR #70 post-publication reconciliation remains pending;
6. record M6 Closure through PR #71 as already PUBLISHED / COMPLETE;
7. normalize stale `M6 candidate` wording only where it refers to the Enterprise Vision that is now published and substantively Approved;
8. keep `docs/handbook/PRODUCT_VISION_AND_DECISION_RIGHTS.md` and GD-003 Proposed as a separate decision at that historical point;
9. preserve Phase 0 In Progress;
10. preserve Sprint 14 Not Authorized;
11. preserve production readiness NO-GO;
12. preserve ADR/GD/JRN existing states outside GOV-051 at that historical point;
13. create only a bounded Draft PR for this decision record;
14. run required checks;
15. obtain independent exact-head review from `zefriansyah`;
16. stop before Ready or Merge unless separate exact-head Product Owner lifecycle authority is supplied.

This was decision record synchronization only. It was not Sprint 14 and was not a new implementation milestone.

## Historical Product Owner substantive decision recorded by GOV-051

Decision:

**APPROVED**

Approved Enterprise Vision:

**oneQay is an Enterprise Intelligent Business Management Platform.**

Decision provenance:

- verified repository baseline: `762149757e4bc1fa79cc16bc4761f4147be0f7ea`;
- verified baseline tree: `4d16f322b1bc8f2b666eef87ce4a1caaa6755e4f`;
- canonical artifact: `docs/handbook/ENTERPRISE_VISION.md`;
- approved artifact blob: `bb1cace72a6fdb359e15e22467443d9f3916c336`.

The approval establishes the Enterprise Vision boundary, enterprise design qualities, directional Enterprise Capability Map, and conceptual E0–E5 evolution as binding long-term product direction.

It does not constitute implementation authority.

## M6 publication facts to preserve

### PR #69 — Enterprise Vision Canonicalization

- state: CLOSED / MERGED;
- source head: `e6a3345b09a6b270ac7e09abd78c6356f426e363`;
- source tree: `567df997bae70090b19465c75e4cc3b1e23b6579`;
- published commit: `0b7b28028966ac38af0f32960054210c3a083916`;
- published tree: `567df997bae70090b19465c75e4cc3b1e23b6579`;
- source tree equals published tree: Yes.

PR #69 canonicalized and published the Enterprise Vision representation. It did **not** by itself promote the substantive Enterprise Vision decision; GOV-051 later approved that decision separately.

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

PR #70 completed the post-publication reconciliation and is not future work.

### PR #71 — M6 Closure — Checkpoint Semantics Correction

- state: CLOSED / MERGED;
- source head: `19c723f32c62c982a80e1d8a520ab6ff5a189e2c`;
- source tree: `4d16f322b1bc8f2b666eef87ce4a1caaa6755e4f`;
- published commit: `762149757e4bc1fa79cc16bc4761f4147be0f7ea`;
- published tree: `4d16f322b1bc8f2b666eef87ce4a1caaa6755e4f`;
- published parent: `b26c4690d68db61118ee1c4cecbb87e9418d791f`;
- source tree equals published tree: Yes.

PR #71 completed M6 closure and established stable provenance semantics for canonical tracked checkpoints.

## M6 anomaly disposition

- A-09 — Enterprise Vision canonicalization: Resolved at canonical representation/publication level through PR #69; separate substantive Enterprise Vision decision Approved through GOV-051.
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

GOV-051 Enterprise Vision approval and the historical decision-record work did not authorize:

- Sprint 14 implementation;
- final/business/production application implementation;
- MVP scope approval;
- new application source-code implementation;
- database/schema implementation;
- executable SQL;
- migration execution;
- production database modification;
- deployment;
- release;
- production-readiness promotion;
- GD-003 or GD-007 promotion by GOV-051 itself;
- ADR-001 through ADR-007 acceptance by GOV-051 itself;
- JRN resolution;
- framework, database, tenancy-model, authentication, payment, or AI-provider selection.

Capability-map presence does not imply implementation authority. Later separately governed decisions remain authoritative for their own bounded scopes.

## Governance preservation

- Phase 0: In Progress
- Enterprise Vision decision status: Approved through GOV-051
- ADR-001 through ADR-007: Accepted through their separately governed DEC reconciliations
- ADR-008: Accepted representation of DEC-004
- GD-003: Approved through DEC-000
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

## Lifecycle rule

No standing Ready or Merge authority is stored in this checkpoint.

For any future governed PR, applicable substantive authority, preparation authority, independent exact-head review, Product Owner READY authority, and Product Owner MERGE authority remain separate. A source-head change after review invalidates exact-head review binding and requires fresh verification/review before later lifecycle authority.

No future milestone, Phase 0 exit, Sprint 14, source implementation, dependency adoption, deployment, release, or Production action may be inferred from this file.

Attribution: Lab | zefry
