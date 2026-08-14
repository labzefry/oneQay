# AI Next Task

## Stable checkpoint

- Project: oneQay
- Repository: `labzefry/oneQay`
- Developer and Product Engineering Entity: Lab | zefry
- Canonical product attribution: Lab | zefry
- Canonical product name: `oneQay`
- Canonical checkpoint path: `docs/ai/`
- Canonical Phase 0 status: In Progress
- Current engineering workstream: M7 — Technical Preview Implementation Enablement
- M5.1: PUBLISHED / COMPLETE through PR #66
- M5.2: PUBLISHED / ENFORCEMENT COMPLETE through PR #67
- M5.3: PUBLISHED / COMPLETE through PR #68
- M6 Enterprise Vision Canonicalization: PUBLISHED / PUBLICATION COMPLETE through PR #69
- M6 Post-Publication State Reconciliation: PUBLISHED through PR #70
- M6 Closure — Checkpoint Semantics Correction: PUBLISHED / COMPLETE through PR #71
- GOV-051 Enterprise Vision substantive decision: APPROVED / DECISION COMPLETE
- Enterprise Vision decision status: Approved
- M7.0 Controlled Implementation Bridge: DONE / PUBLISHED
- M7.1 Application Skeleton & Configuration Boundary: DONE / PUBLISHED through PR #92
- M7.2 Tenant Kernel & Isolation Foundation: DONE / PUBLISHED through PR #93
- M7.3 Identity / Organization / Outlet / Device Minimum: DONE / PUBLISHED through PR #94
- M7.4 POS Core Synthetic Vertical Slice: DONE / PUBLISHED through PR #96
- M7.4A Technical Preview Interaction Layer: DONE / PUBLISHED through PR #98
- DEC-005R Portable Relational Persistence Architecture: APPROVED / DECISION COMPLETE / PUBLISHED through PR #100
- Canonical next gated roadmap milestone: M7.5 — Preview Runtime Qualification
- M7.5: BLOCKED / NOT AUTHORIZED; actual sanitized P2 target evidence, DEC-009 capability verification, and selected relational engine-profile qualification under DEC-005R required
- M7.6: BLOCKED / NOT AUTHORIZED
- M7.7: BLOCKED / NOT AUTHORIZED
- Sprint 12: Published
- Sprint 13: Published
- Sprint 14: Not Authorized
- Production readiness: NO-GO

## Live GitHub state rule

This tracked checkpoint MUST NOT claim that a hard-coded commit SHA is permanently the current live GitHub `main` or live tree.

Any SHA recorded below is publication provenance or the verified baseline that existed before the bounded work began.

Before any new branch, lifecycle mutation, Ready, Merge, implementation decision, or milestone transition, perform Minimal Delta Verification against GitHub because GitHub is the Single Source of Truth.

Do not create repetitive state-reconciliation commits merely to replace a stored `current main` SHA after publication. Publishing that replacement creates another SHA and causes an infinite self-referential cycle.

## Current next-work authority

**NO STANDING IMPLEMENTATION OR MILESTONE AUTHORITY.**

The canonical next gated roadmap micro-milestone is:

**M7.5 — Preview Runtime Qualification**

Its authority state is:

**BLOCKED / NOT AUTHORIZED.**

M7.5 may begin only after actual sanitized P2 target evidence is supplied, the target is verified against DEC-009 mandatory capability requirements, the selected relational engine profile is qualified under DEC-005R, fresh GitHub Minimal Delta Verification is performed, and separate explicit Product Owner M7.5 authority is granted. This checkpoint does not create evidence, qualification, or authority.

The next governed direction is therefore **M7.5 evidence/authority preparation**, not automatic M7.5 implementation, not Preview deployment, and not another application-source milestone.

Track A Controlled Application Engineering has published M7.4 and M7.4A. Track B Preview Runtime Qualification remains blocked until its external target evidence, DEC-009 capability verification, DEC-005R selected-engine-profile qualification, and separate authority gates are satisfied. Both tracks converge before Preview deployment/acceptance.

Substantive decision authority, preparation authority, independent exact-head review, Product Owner READY authority, and Product Owner MERGE authority remain separate whenever applicable.

## Current canonical M7 state

- M7.0 — Controlled Implementation Bridge: **DONE / PUBLISHED**.
- M7.1 — Application Skeleton & Configuration Boundary: **DONE / PUBLISHED** through PR #92.
- M7.2 — Tenant Kernel & Isolation Foundation: **DONE / PUBLISHED** through PR #93.
- M7.3 — Identity / Organization / Outlet / Device Minimum: **DONE / PUBLISHED** through PR #94.
- M7.4 — POS Core Synthetic Vertical Slice: **DONE / PUBLISHED** through PR #96.
- M7.4A — Technical Preview Interaction Layer: **DONE / PUBLISHED** through PR #98.
- M7.5 — Preview Runtime Qualification: **BLOCKED / NOT AUTHORIZED — ACTUAL SANITIZED P2 TARGET EVIDENCE, DEC-009 CAPABILITY VERIFICATION, AND SELECTED RELATIONAL ENGINE-PROFILE QUALIFICATION UNDER DEC-005R REQUIRED**.
- M7.6 — Preview Deployment / Recovery Rehearsal: **BLOCKED / NOT AUTHORIZED**.
- M7.7 — Technical Preview Acceptance: **BLOCKED / NOT AUTHORIZED**.

M7.1 preserves the application/configuration foundation. M7.2 preserves tenant-context and isolation controls. M7.3 preserves first-party identity separation, server-verified tenant membership, and server-controlled organization/outlet/device authority. M7.4 preserves server-authoritative synthetic POS transaction behavior. M7.4A adds the bounded synthetic interaction layer while reusing the existing M7.4 `CompleteSyntheticSale` authority. None of those completion facts grants M7.5 runtime qualification, deployment, release, Phase 0 Exit, Sprint 14, or Production authority.

## M7 publication provenance

The following values are historical publication provenance only and must not be interpreted as permanently current live repository state:

- M7.1 PR #92: CLOSED / MERGED; resulting main `82b2bffb3b087aa818c2a229d2b7e0c07ea158ec`.
- M7.2 PR #93: CLOSED / MERGED; resulting main `ba95f745869092d251230fb5a3db2c08e42f4941`.
- M7.3 PR #94: CLOSED / MERGED; source head `67d7b890fe95db9c32d4e2dbc432be193bb064a9`; source tree `3cb925e9234bc28b64aec3a1f6efd1a03756221c`; resulting main `9b43f6be520b64e47bfa9a66be577dab20f69bd9`.
- M7.4 PR #96: CLOSED / MERGED; source head `0659e0e3c2ab7f8ec9f12653b773aaa4391e931b`; source tree `f67f9b75a11b2710b58a9928f5b73f876cba2cef`; resulting main `4981fca92e7de028ca55e746b36af6afe0d3e7f2`.
- M7.4A PR #98: CLOSED / MERGED; source head `893b73b8f20b2ede0d3a8896b3a015df5370dbed`; source tree `cdc140e5061481bec4b6b691b02b2b234181c2fb`; published commit `c0bdf8ad7539a5c83de2e5183fbf2eda9f17f02b`; published tree `cdc140e5061481bec4b6b691b02b2b234181c2fb`; source tree equals published tree: Yes.

Before using any of those values for a new action, fetch live GitHub state again.

## DEC-005R publication provenance

DEC-005R publication is stable historical provenance, not a permanently current live-head claim:

- publication PR: #100 CLOSED / MERGED;
- source head: `8ec7069b08c9127e402fa80e5e79ca26be2b63d6`;
- source tree: `0862c851d30c11c37c39d13aa5660d042da91989`;
- published squash commit: `b5cbdeb6ea45d4f159f3d1cd39cadc561605c5ff`;
- published tree: `0862c851d30c11c37c39d13aa5660d042da91989`;
- source tree equals published tree: Yes;
- decision: **DEC-005R — Portable Relational Persistence Architecture — APPROVED / DECISION COMPLETE**;
- no source, schema, SQL, migration, cross-engine CI, DBME implementation, M7.5, deployment, release, or Production authority was created.

## Current canonical decision state

- DEC-000 Product Vision and Decision Rights: **APPROVED / DECISION COMPLETE**; GD-003 is Approved through DEC-000.
- DEC-001 MVP Scope and Non-Scope: **APPROVED / DECISION COMPLETE**; first bounded MVP delivery slice is **POS CORE TRANSACTION & OUTLET OPERATIONS**.
- DEC-002, DEC-003, and DEC-004: **APPROVED / DECISION COMPLETE** according to their respective bounded records; ADR-001, ADR-002, and ADR-008 retain their governed Accepted state.
- DEC-005 Database Engine and Physical Tenancy Model: **APPROVED HISTORICAL DECISION / PARTIALLY SUPERSEDED BY DEC-005R**; historical MySQL Server selection remains preserved and applicable shared-tenancy/isolation/recovery principles remain preserved according to DEC-005R dispositions.
- DEC-005R Portable Relational Persistence Architecture: **APPROVED / DECISION COMPLETE / PUBLISHED through PR #100**; current relational architecture is engine-neutral at Domain/Application level with qualified MariaDB/MySQL/PostgreSQL engine-profile direction, Database Portability Contract direction, and DBME/cross-engine qualification directions not yet implemented.
- DEC-006, DEC-007, and DEC-008: **APPROVED / DECISION COMPLETE**; ADR-004, ADR-005, and ADR-006 retain their separately governed Accepted state.
- DEC-009 Deployment Stage 1 Runtime Requirements: **APPROVED / DECISION COMPLETE**; ADR-007 remains Accepted and the database dependency is reconciled to require an authorized and runtime-qualified relational engine profile under DEC-005R rather than sole canonical MySQL Server.
- DEC-010 Product License and Third-Party Notice Policy: **APPROVED / DECISION COMPLETE**; oneQay remains **PROPRIETARY / ALL RIGHTS RESERVED**.
- DEC-011 Data Retention, Privacy, and Jurisdiction: **APPROVED / DECISION COMPLETE**; initial jurisdiction remains not yet canonically selected.
- DEC-012 RPO/RTO and Support Objectives: **APPROVED / DECISION COMPLETE**; final numerical Production RPO/RTO/SLO and customer-contractual SLA remain deferred.
- DEC-010 Supplement: **APPROVED / DECISION COMPLETE / PUBLISHED through PR #87**; Apache ECharts remains a default Web/PWA visualization technology candidate / approved technology direction only; package/dependency adoption and implementation remain separately gated.
- GD-007 remains Proposed.
- JRN-003 and JRN-013 remain Unresolved.
- Phase 0 remains In Progress.
- Phase 0 Exit remains Not Approved.
- Actual P2 target remains Pending External Input unless fresh evidence proves otherwise.
- Sprint 14 remains Not Authorized.
- Deployment remains Not Authorized.
- Release remains Not Authorized.
- Production remains Not Authorized.
- Production readiness remains NO-GO.

## Issue #23 semantic drift

Issue #23 contains historical pre-M7.0 planning language, including older assumptions around Phase 0 Exit and source preparation. The later governed Phase 0 Controlled Implementation Bridge supersedes that older wording for bounded Local/Test/CI source preparation.

Issue #23 remains **KNOWN OPEN ISSUE SEMANTIC DRIFT / HISTORICAL PLANNING LANGUAGE**. Its historical wording must not override later canonical M7.0 bridge authority. This checkpoint does not authorize editing, commenting on, or closing Issue #23.

## M7.5 qualification gate

M7.5 is **not authorized** by publication of M7.4, M7.4A, or DEC-005R.

Before any future M7.5 activity, the required gate includes at minimum:

- fresh GitHub Minimal Delta Verification;
- actual sanitized P2 target evidence supplied from the real target rather than invented or assumed capability;
- DEC-009 mandatory capability verification against that target;
- selected relational engine-profile qualification under DEC-005R, including Database Portability Contract evidence appropriate to the authorized scope;
- explicit separation of runtime qualification from deployment/release authority;
- preservation of M7.1–M7.4A source and security boundaries;
- synthetic-only Technical Preview data unless a separately approved masked-data process exists;
- no Production/customer data;
- no real payment provider processing;
- no Production deployment or release;
- separate explicit Product Owner M7.5 authority before qualification work begins.

A future M7.5 authority must not silently introduce application source changes, database schema, SQL, migrations, seeders, dependencies, cross-engine CI implementation, DBME implementation, deployment, release, or Production work unless separately and explicitly authorized.

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

Historical decisions and M7.0–M7.4A completion do not authorize:

- Sprint 14 implementation;
- final/business/production application implementation beyond separately bounded authority;
- M7.5 runtime qualification;
- database/schema implementation;
- executable SQL;
- migration execution;
- production database modification;
- cross-engine CI implementation;
- DBME implementation;
- deployment;
- release;
- production-readiness promotion;
- GD-007 promotion;
- JRN resolution;
- provider selection or Production payment processing.

Capability-map or roadmap presence does not imply implementation authority. Later separately governed decisions remain authoritative for their own bounded scopes.

## Governance preservation

- Phase 0: In Progress
- Phase 0 Exit: Not Approved
- Enterprise Vision decision status: Approved through GOV-051
- ADR-001 through ADR-007: Accepted through their separately governed DEC reconciliations
- ADR-008: Accepted representation of DEC-004
- GD-003: Approved through DEC-000
- GD-007: Proposed
- JRN-003 and JRN-013: Unresolved
- Actual P2 target: Pending external input unless fresh evidence proves otherwise
- Final tenant data model: Not Started
- Final business schema: Not Started
- Production migration: Not Performed
- Production database usage: None
- Production table: None
- Sprint 14: Not Authorized
- Deployment: Not Authorized
- Release: Not Authorized
- Production: Not Authorized
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
