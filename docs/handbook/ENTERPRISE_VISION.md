# oneQay Enterprise Vision

> **Status:** Proposed — M6 canonicalization candidate; requires independent review and explicit Product Owner lifecycle approval before publication
> **Program:** M6 — Enterprise Vision Canonicalization
> **Developer & Product Engineering Entity:** Lab | zefry
> **Repository:** `labzefry/oneQay`

## Canonical product naming

The canonical product name is **oneQay**.

Allowed canonical form:

- `oneQay`

Non-canonical forms for current product identity include `OneQay`, `ONEQAY`, `Oneqay`, and `oneqay`.

Historical immutable GitHub evidence, commit messages, URLs, branch names, SHAs, review evidence, and quoted historical text are not rewritten merely to normalize branding. Current and future canonical product references must use **oneQay**.

## Enterprise vision

**oneQay is an Enterprise Intelligent Business Management Platform.**

The product is intended to evolve from a reliable multi-tenant business foundation into an integrated operating platform that can support transaction processing, business management, enterprise administration, intelligence, extensibility, and ecosystem participation without coupling business rules to a specific infrastructure stage, vendor, channel, or AI provider.

This vision is directional. It does not state that every capability is implemented, production-ready, approved for delivery, or authorized for the next sprint.

## Vision boundary

M6 separates five concepts that must not be conflated:

1. **Product Vision** — the long-term direction of oneQay.
2. **Product Capability Map** — the capability families that may be developed over time.
3. **Product Architecture Direction** — the constraints that keep those capabilities modular, secure, scalable, and evolvable.
4. **Delivery Roadmap** — the staged ordering used to reduce risk and sequence value.
5. **Implementation Authority** — explicit Product Owner authority for a bounded piece of work at a specific lifecycle stage.

Vision canonicalization does **not** create implementation authority.

## Enterprise design qualities

The canonical Enterprise Vision requires oneQay to be designed toward the following qualities:

- **enterprise-grade** — clear ownership, auditability, deterministic controls, recovery, and operational evidence;
- **modular** — capability boundaries minimize accidental coupling and duplicate business logic;
- **scalable** — scale decisions follow measured need while preserving domain contracts;
- **multi-tenant by design** — tenant identity and isolation remain explicit across data, cache, jobs, files, events, search, and audit;
- **API-first** — integration contracts are versioned, reviewable, and compatibility-aware;
- **integration-ready** — external systems are isolated behind controlled ports/adapters and explicit failure semantics;
- **extensible** — marketplace, plugin, partner, and public API capabilities may be added behind governance and trust boundaries;
- **security-conscious** — deny-by-default authorization, least privilege, data classification, audit, secret isolation, and recovery are first-class concerns;
- **AI-ready** — AI capabilities are bounded by tenant authorization, data policy, deterministic validation, human accountability, and safe fallback;
- **infrastructure-independent** — business rules do not change merely because deployment evolves from shared hosting to VPS, dedicated infrastructure, containers, cloud, or orchestration.

## Enterprise Capability Map

All capability families below are **directional capability groups**. This map does not promote Proposed domain hypotheses, does not accept ADRs, and does not authorize implementation.

### Core Business Platform

- Tenant & Organization
- Identity & Access
- Master Data
- POS / Commerce
- Inventory
- Procurement
- Finance / Accounting
- CRM
- HRM
- Reporting & Business Intelligence

### Platform Capabilities

- Workflow
- Notification
- Audit
- File / Document
- Search
- API
- Integration
- Webhook / Event Integration
- Configuration
- Localization
- Observability
- Recovery & Operational Control

### Extensibility

- Marketplace
- Plugin / Extension
- Public API
- Partner Integration
- Developer / Integration Experience

### AI Platform

- AI Assistant
- AI Insight
- AI Automation
- AI Recommendation
- AI Analytics
- AI Gateway / Policy Boundary

AI output must not become an unreviewed source of truth for financial mutation, authorization, tenant boundary decisions, inventory mutation, accounting posting, or irreversible administrative action.

### Channels

- Web Application
- Progressive Web App (PWA)
- Mobile / Android
- Admin Platform
- Public / Customer-facing surfaces
- API consumers and partner surfaces

## Staged Product Evolution

The following stages express conceptual product evolution rather than release commitment.

### Stage E0 — Foundation

Purpose: establish trustworthy platform, governance, tenancy, identity, configuration, audit, data safety, quality, and recovery foundations.

Existing bounded Platform Foundation publications through Sprint 12 and Sprint 13 remain repository facts. Their publication does not mean the full Foundation stage is complete.

### Stage E1 — Core Transaction Platform

Purpose: enable controlled business transactions and core operational records, including POS/commerce and the minimum supporting master/inventory capabilities required by an explicitly approved scope.

### Stage E2 — Business Management

Purpose: integrate operational management across inventory, procurement, customer relationships, finance/accounting foundations, reporting, and workflow where approved.

### Stage E3 — Enterprise Management

Purpose: support larger organizations, multi-unit governance, advanced administration, role separation, configurable processes, deeper controls, and enterprise operating requirements.

### Stage E4 — Intelligence

Purpose: provide trusted analytics, business intelligence, AI-assisted insight, recommendation, and bounded automation with human accountability and measurable evaluation.

### Stage E5 — Ecosystem

Purpose: provide governed public APIs, partner integrations, marketplace, plugin/extension capabilities, and ecosystem surfaces after their security, compatibility, and trust models are approved.

No stage starts merely because it is described here. Product Owner authority and the applicable decision/quality gates remain mandatory.

## Architecture direction

The Enterprise Vision is compatible with the existing Modular Monolith First, Clean Architecture, Domain-Driven Design, API-first, multi-tenant, event-ready, and infrastructure-independent direction.

M6 does not accept any Proposed bounded context, framework, database engine, authentication provider, payment provider, AI provider, physical tenancy model, or deployment migration merely by referencing this direction.

## Capability lifecycle semantics

A capability can appear in the Enterprise Capability Map while remaining one of the following:

- directional only;
- Proposed;
- Under Review;
- Deferred;
- Approved for product direction but not implementation;
- explicitly authorized for bounded implementation;
- published as a verified repository fact.

Capability-map presence is never sufficient evidence of implementation authority or production readiness.

## Current governance boundary

The following state remains in force during M6:

- Phase 0 — Governance and Discovery: **In Progress**.
- Sprint 12: **Published**.
- Sprint 13: **Published**.
- Sprint 14: **Not Authorized**.
- Final/business/production application implementation: **Blocked** unless separately authorized.
- Production readiness: **NO-GO**.
- Deployment: **Not Authorized**.
- Release: **Not Authorized**.
- SQL execution: **Not Authorized**.
- Migration execution: **Not Authorized**.
- Production database modification: **Not Authorized**.
- ADR-001 through ADR-007: remain **Proposed** unless separately approved.
- GD-007: remains **Proposed**.
- JRN-003 and JRN-013: remain unresolved.

## Canonical document relationship

- `docs/handbook/ENTERPRISE_VISION.md` — canonical Enterprise Vision and high-level capability/evolution map once published through the approved M6 lifecycle.
- `docs/handbook/PRODUCT_VISION_AND_DECISION_RIGHTS.md` — Phase 0 product/discovery and decision-rights artifact; its independent decision statuses are not automatically promoted by M6.
- `PROJECT_MANIFEST.md` — canonical product identity and decision-status register.
- `ARCHITECTURE.md` — architecture constraints and technical boundaries.
- `ROADMAP.md` — staged delivery sequencing.
- `TASKS.md` — operational backlog and lifecycle state.
- `docs/ai/` — current canonical AI checkpoint state.

If these documents conflict after M6 publication, explicit Approved decisions and later superseding lifecycle evidence take precedence; discrepancies must be reconciled through a bounded PR rather than silently interpreted.

## M6 exit criteria

M6 is complete only when:

1. this Enterprise Vision has completed independent review;
2. Product Owner content/lifecycle decisions are explicit for the exact head;
3. canonical program documents are synchronized;
4. brand identity is normalized to **oneQay** in current canonical material;
5. capability map and staged evolution are internally consistent;
6. vision remains separated from implementation authority;
7. required checks pass on the exact head;
8. unresolved review threads are zero;
9. publication to `main` is explicitly authorized and verified;
10. source tree and published tree are verified.

Until those conditions are satisfied, A-09 is **In Progress / addressed by M6 candidate**, not yet closed by publication.

Attribution: Lab | zefry
