# AI Project State

## Project identity

- Project: oneQay
- Tagline: The Future of Intelligent Business Management
- Developer and Product Engineering Entity: Lab | zefry
- Repository: `labzefry/oneQay`
- GitHub repository role: Single Source of Truth
- Product attribution: Lab | zefry
- Canonical product name: `oneQay`

Engineering AI/tooling identity is governance metadata only and is not product authorship attribution.

## Canonical checkpoint semantics

This tracked checkpoint does **not** store a hard-coded SHA as the permanently current live GitHub `main` or live tree.

Stable provenance fields in this document describe published milestone identity or the verified baseline that existed before the checkpoint work began. They are historical evidence, not a claim that the referenced SHA remains the live repository head forever.

Before any lifecycle mutation, branch creation, implementation decision, Ready transition, or Merge transition, the live repository state MUST be obtained by Minimal Delta Verification from GitHub because GitHub is the Single Source of Truth.

The required live verification includes, as applicable:

- default-branch head;
- default-branch tree;
- active PR state;
- active PR exact head and tree;
- required checks;
- reviewer state;
- unresolved review threads.

A checkpoint update must not be created merely to replace a stored `current main` SHA after every publication.

## Canonical delivery state

- Canonical delivery phase: Phase 0 — Governance and Discovery
- Canonical Phase 0 status: In Progress
- M5.1: PUBLISHED / COMPLETE through PR #66
- M5.2: PUBLISHED / ENFORCEMENT COMPLETE through PR #67
- M5.3: PUBLISHED / COMPLETE through PR #68
- M6 Enterprise Vision Canonicalization: PUBLISHED / PUBLICATION COMPLETE through PR #69
- M6 Post-Publication State Reconciliation: PUBLISHED through PR #70
- M6 Closure — Checkpoint Semantics Correction: authorized bounded closure work; no later milestone implied
- Enterprise Vision decision status: Proposed; publication does not promote it to Approved
- Latest published technical capability sprint: Sprint 13
- Sprint 12: Published
- Sprint 13: Published
- Sprint 14: Not Authorized
- Production readiness: NO-GO
- Deployment: None / Not Authorized
- Release: None / Not Authorized
- Production migration: Not Performed

## Verified baseline before M6 closure semantics correction

The bounded closure branch was authorized and created only after GitHub Delta Verification confirmed this published baseline:

- published PR: #70;
- PR #70 state: CLOSED / MERGED;
- PR #70 source head: `e7eded8d6c661cb5485527d0f1937fb839a3617f`;
- PR #70 source tree: `58e84138173b1e6e5ca2dc7649dbeb89d79e9af0`;
- PR #70 published commit: `b26c4690d68db61118ee1c4cecbb87e9418d791f`;
- PR #70 published tree: `58e84138173b1e6e5ca2dc7649dbeb89d79e9af0`;
- PR #70 published parent: `0b7b28028966ac38af0f32960054210c3a083916`;
- source tree equals published tree: Yes;
- independent reviewer: `zefriansyah`;
- exact-head review: APPROVED;
- required technical checks: SUCCESS;
- Product Owner READY authority: separately recorded and executed;
- Product Owner MERGE authority: separately recorded and executed;
- `product-owner-merge-authority`: SUCCESS before squash merge.

This is a **publication baseline**, not a permanently current-live-head declaration.

## Canonical naming

The canonical product name is **oneQay**.

Current and future canonical product identity must use exactly `oneQay`. Non-canonical active variants include `OneQay`, `ONEQAY`, `Oneqay`, and `oneqay`.

Repository identifier `labzefry/oneQay`, immutable GitHub URLs, SHAs, historical commit messages, historical branch names, and quoted historical evidence are preserved as recorded.

## M6 Enterprise Vision publication

Product Owner authorized M6 with:

**START M6 — ENTERPRISE VISION CANONICALIZATION**

M6 publication lifecycle completed through PR #69.

Publication identity:

- base: `e45f5b4c0f143abc6e255e4e8550bf3504348aae`;
- base tree: `e2bc0505f5abd98a7283b3cd3cd2c4c02ef23ece`;
- source branch: `agent/m6-enterprise-vision-canonicalization`;
- source head: `e6a3345b09a6b270ac7e09abd78c6356f426e363`;
- source tree: `567df997bae70090b19465c75e4cc3b1e23b6579`;
- published commit: `0b7b28028966ac38af0f32960054210c3a083916`;
- published tree: `567df997bae70090b19465c75e4cc3b1e23b6579`;
- source tree equals published tree: Yes.

The published canonical Enterprise Vision representation defines oneQay as:

**Enterprise Intelligent Business Management Platform**

The canonical M6 document is:

`docs/handbook/ENTERPRISE_VISION.md`

Publication canonicalizes the representation and location of the Enterprise Vision. It does **not** promote the Enterprise Vision decision status from Proposed to Approved, does not authorize implementation, and does not imply production readiness.

## M6 post-publication reconciliation publication

PR #70 published the bounded reconciliation of mutable program-state documentation after PR #69.

PR #70 publication establishes that:

- M6 Enterprise Vision publication is complete;
- A-09 is resolved at canonical representation/publication level only;
- A-10 is resolved for current/future-facing canonical product naming;
- Enterprise Vision substantive decision remains Proposed;
- GOV-047 through GOV-050 represent completed publication/reconciliation work;
- GOV-051 remains the separate substantive Enterprise Vision Product Owner decision;
- Phase 0 remains In Progress;
- Sprint 14 remains Not Authorized;
- production readiness remains NO-GO.

## Enterprise capability direction

M6 groups the directional capability map into:

1. Core Business Platform — Tenant & Organization, Identity & Access, Master Data, POS / Commerce, Inventory, Procurement, Finance / Accounting, CRM, HRM, Reporting & Business Intelligence.
2. Platform Capabilities — Workflow, Notification, Audit, File / Document, Search, API, Integration, Webhook / Event Integration, Configuration, Localization, Observability, Recovery & Operational Control.
3. Extensibility — Marketplace, Plugin / Extension, Public API, Partner Integration, Developer / Integration Experience.
4. AI Platform — AI Assistant, AI Insight, AI Automation, AI Recommendation, AI Analytics, AI Gateway / Policy Boundary.
5. Channels — Web Application, PWA, Mobile / Android, Admin Platform, Public / Customer-facing surfaces, and API/partner consumers.

Capability-map presence is not implementation authority.

## Staged product evolution

M6 defines conceptual stages only:

- E0 — Foundation
- E1 — Core Transaction Platform
- E2 — Business Management
- E3 — Enterprise Management
- E4 — Intelligence
- E5 — Ecosystem

These stages are not release commitments and do not start without separate Product Owner authority and applicable gates.

## Canonical Phase 0 semantics

Phase 0 **In Progress** describes the governance/discovery program state. It does not mean that the repository contains no technical source code.

Bounded Platform Foundation implementation has been published through Sprint 13. This publication is separate from Phase 0 exit and must not be interpreted as final/business application approval, automatic Phase 1 completion, Sprint 14 authority, production readiness, deployment authority, release authority, or migration authority.

The canonical blocked boundary remains:

**Final/business/production application implementation: Blocked.**

## Published Platform Foundation through Sprint 13

Canonical Sprint 13 capability:

**Schema Change Review and Approval Envelope Foundation**

Canonical identity:

- PR: #64
- Base: `de3c8c73c0002915c735dc1dfa29828e1781e71d`
- Source head: `4a2e44cc31361954b126e8857de65fcccca30445`
- Source tree: `5a0adb0d2ce80338f9f9d782f0871fb2115afd5d`
- Published commit: `ebe6abcf77263bf644565ca2fbe2b2844416d49b`
- Published tree: `5a0adb0d2ce80338f9f9d782f0871fb2115afd5d`
- Source tree equals published tree: Yes

PR #65 reconciled the canonical Sprint 13 publication state and was published as `7a9def560466fc8bf81529c2b5125c6ac19a96b5`.

## M5 publication facts

### M5.1

M5.1 — Canonical State Reconciliation is PUBLISHED / COMPLETE through PR #66 and published commit `153a33a4a2b5edb4a31285eca7d3491f9589b778`.

### M5.2

M5.2 — CI & Lifecycle Control Hardening is PUBLISHED / ENFORCEMENT COMPLETE through PR #67.

Published identity:

- commit: `512344d0497787c729242cb1fd2d7d02ecfc40c2`;
- tree: `0f0af1c1acab208c704fbdf05b19014127abddbb`.

Required protected contexts:

1. `governance-validation`
2. `markdown-lint`
3. `secret-scan`
4. `php-foundation-regression`
5. `product-owner-merge-authority`

### M5.3

M5.3 — Governance & Program State Synchronization is PUBLISHED / COMPLETE through PR #68.

Published identity:

- source head: `aa799e657070a7d3283110a73a411f54a73b972c`;
- source tree: `e2bc0505f5abd98a7283b3cd3cd2c4c02ef23ece`;
- published commit: `e45f5b4c0f143abc6e255e4e8550bf3504348aae`;
- published tree: `e2bc0505f5abd98a7283b3cd3cd2c4c02ef23ece`;
- source tree equals published tree: Yes.

M5.3 resolved A-06, A-07, and A-08 for current canonical program state.

## Canonical checkpoint authority

Canonical mutable AI checkpoint files are located only under `docs/ai/`:

1. `docs/ai/AI_SESSION_STATE.md`
2. `docs/ai/AI_PROJECT_STATE.md`
3. `docs/ai/AI_NEXT_TASK.md`

Root files with matching names remain deprecated pointer stubs only.

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

## M5/M6 anomaly status

- A-01 stale canonical AI checkpoint: Resolved by M5.1 and subsequent material checkpoint updates.
- A-02 duplicate root AI checkpoint: Resolved by M5.1 using pointer stubs.
- A-03 lifecycle authority not technically enforced: Resolved by M5.2 enforcement.
- A-04 review history contamination: canonical disposition recorded; historical evidence preserved.
- A-05 PHP regression not in GitHub CI: Resolved by M5.2 enforcement.
- A-06 Phase 0 semantic ambiguity: Resolved through M5.3 publication.
- A-07 ROADMAP / TASKS synchronization: Resolved through M5.3 publication.
- A-08 attribution/collaboration metadata supersession: Resolved through M5.3 publication; canonical product attribution is Lab | zefry.
- A-09 Enterprise Vision canonicalization: Resolved at representation/publication level through PR #69; Enterprise Vision decision status remains Proposed until separately approved.
- A-10 product-name capitalization inconsistency: Resolved for current/future-facing canonical material through PR #69; immutable historical evidence remains preserved.

## M6 closure boundary

M6 Enterprise Vision publication and M6 post-publication reconciliation are already published through PR #69 and PR #70 respectively.

The bounded M6 Closure — Checkpoint Semantics Correction exists only to remove self-referential live-head semantics and to prevent an infinite reconciliation cycle.

It does not authorize:

- Sprint 14 implementation;
- final/business application implementation;
- database/schema implementation;
- executable SQL;
- migration execution;
- production database modification;
- deployment;
- release;
- Enterprise Vision promotion from Proposed to Approved;
- ADR/GD promotion;
- JRN resolution;
- production readiness promotion.

Any Ready or Merge transition for the closure PR requires separate exact-head Product Owner authority.

Attribution: Lab | zefry
