# DEC-001 — MVP Scope and Non-Scope Decision Record

> **Status:** Approved — substantive Product Owner decision
> **Phase:** 0 — Governance & Discovery
> **Canonical product:** oneQay
> **Developer & Product Engineering Entity:** Lab | zefry
> **Repository:** `labzefry/oneQay`
> **Product Owner:** `labzefry`

## Decision provenance

- Decision: **DEC-001 — MVP Scope and Non-Scope**.
- Decision result: **APPROVED**.
- Product Owner decision baseline: `17f156b9861972b4924a5ed01bfabd5a1a79461a`.
- Verified decision baseline tree: `33241c18a1b7da2efc7dd2889c13c25c6e8526d5`.
- Decision authority type: **Substantive product decision only**.
- Implementation authority: **NOT GRANTED**.
- Sprint 14 authority: **NOT GRANTED**.
- Deployment authority: **NOT GRANTED**.
- Release authority: **NOT GRANTED**.
- Production readiness: **NO-GO**.

This record represents the explicit Product Owner substantive DEC-001 decision.
It does not expand authority beyond the bounded product decision below.

## Product-direction boundary

oneQay remains an:

**Enterprise Intelligent Business Management Platform**.

DEC-001 does not redefine oneQay as a POS-only product.

DEC-001 defines only the first bounded, testable, value-producing delivery
slice.

Approved initial MVP delivery slice:

**POS CORE TRANSACTION & OUTLET OPERATIONS**

Long-term enterprise direction remains binding, but future enterprise
capabilities are not implemented through this decision.

The design principle for this slice is:

**future-compatible, not future-overengineered.**

## Initial customer hypothesis

Approved as a Product Owner working hypothesis:

Businesses requiring:

- reliable POS transactions;
- basic outlet inventory control;
- daily operational reconciliation.

This is not completed market validation, completed industry validation, or
product-market-fit evidence.

Initial industry validation status remains:

**NOT YET NARROWED**.

DEC-001 remains industry-neutral where practical. Industry-specific behavior
must not be introduced without evidence and separate Product Owner authority
where required.

## Operating profile

The approved MVP direction must remain compatible with:

- single outlet;
- multi outlet.

DEC-001 must not introduce a permanent single-outlet-only business assumption.
Multi-outlet complexity must not be expanded beyond what the approved MVP
journeys require.

## Approved primary actors

Primary actors:

- Business Owner;
- Outlet / Operations Manager;
- Cashier.

Supporting actor:

- Inventory Operator.

Finance, Tenant Administration, Platform Support, and other roles remain
supporting or dependency roles unless separately brought into MVP scope.

Actor approval does not approve technical permissions, technical role matrices,
authentication architecture, or authorization implementation.

## Approved MVP journeys

The following journeys are approved as the MVP product slice:

- **JRN-004** — Catalog, price, and availability preparation;
- **JRN-005** — Shift/register opening;
- **JRN-006** — Sale, payment recording, and receipt;
- **JRN-007** — Basic controlled cancellation/void/return/refund;
- **JRN-010** — Shift close and operational reconciliation.

## Approved bounded MVP dependencies

The following are approved only as bounded MVP dependencies:

- **JRN-001** — Minimum tenant onboarding;
- **JRN-002** — Minimum outlet/register setup;
- **JRN-003** — Minimum authorized user access;
- **JRN-008** — Bounded outlet inventory baseline only;
- **JRN-011** — Minimum operational reporting;
- **JRN-012** — Minimum supportability boundary.

Dependency status does not promote these journeys into full-feature MVP
modules.

### JRN-003 preservation

JRN-003 remains:

**UNRESOLVED**.

DEC-001 does not silently resolve:

- authentication architecture;
- MFA;
- session architecture;
- recovery architecture;
- final authorization model;
- final permission design.

## Approved core MVP capabilities

Core:

- Catalog and pricing;
- Cart and sale;
- Payment recording;
- Receipt;
- Shift/register lifecycle;
- Controlled void/refund/correction;
- Daily operational reconciliation.

Supporting / MVP dependency:

- Minimum tenant onboarding;
- Minimum outlet/register setup;
- Minimum authorized user access;
- Bounded outlet inventory baseline;
- Minimum operational reporting;
- Minimum supportability.

## Explicit non-scope

The following are deferred and are not part of the initial MVP implementation:

- JRN-009 full purchasing and supplier settlement;
- JRN-013 tenant suspension/export/restore/termination;
- full purchasing/procurement;
- full supplier lifecycle;
- full ERP accounting;
- accounts receivable;
- accounts payable;
- external payment-provider integration;
- offline POS implementation;
- Android Native;
- Public API;
- CMS;
- Marketplace;
- Plugin System;
- AI Assistant / AI Platform;
- full CRM;
- full HRM;
- full Business Intelligence platform.

Deferred means:

**NOT REQUIRED FOR THE FIRST BOUNDED MVP SLICE.**

Deferred does not mean permanently rejected.

### JRN-013 preservation

JRN-013 remains:

**UNRESOLVED**.

## Payment boundary

Payment recording is part of the approved MVP.

External payment-provider integration is not approved through DEC-001.

Payment-provider selection, provider-specific semantics, and applicable payment
compliance remain:

**DEFER TO DEC-007**.

DEC-001 does not approve a payment provider.

## Offline boundary

Offline POS implementation is not approved through DEC-001.

Offline semantics, conflict handling, synchronization behavior, and recovery
semantics remain:

**DEFER TO DEC-008**.

## Approved MVP outcomes

Primary MVP outcomes:

1. **Transaction Trust**;
2. **Operational Efficiency**;
3. **Inventory Accuracy**.

Mandatory product guardrail:

**Secure Tenant Isolation**.

Operational release/reliability gate:

**Recoverability**.

No numerical target is approved through DEC-001.

Metric definitions, metric owners, data sources, baseline acquisition, target
periods, target values, and anti-gaming guardrails must be prepared separately
from evidence.

Unsupported numerical targets must not be invented.

## Unresolved items accepted as open

The following remain intentionally unresolved and are not silently approved or
waived by DEC-001:

- initial industry narrowing;
- completed market validation;
- legal/fiscal jurisdiction;
- tax/receipt compliance;
- payment-provider compliance;
- privacy/data-retention requirements;
- inventory concurrency/reservation semantics;
- support/break-glass controls;
- RPO/RTO;
- backup/restore operational requirements;
- release responsibility;
- incident responsibility;
- detailed authorization semantics;
- JRN-003;
- JRN-013.

These items may become blocking dependencies at their appropriate
implementation, security, architecture, compliance, operational, or release
gates. They do not block the approved DEC-001 product boundary unless later
repository evidence proves otherwise.

## Long-term compatibility

Subsequent implementation decisions must remain compatible with oneQay's
approved long-term direction toward reusable enterprise capabilities,
including future:

- Business Network;
- multi-company / multi-unit operation;
- Inventory and Warehouse capabilities;
- ERP operations;
- CRM;
- workflow;
- reporting / BI;
- integration;
- marketplace / extensions;
- AI capabilities;
- additional channels;
- additional industry verticals.

These capabilities are not implemented through DEC-001.

## Authority boundary

This DEC-001 substantive approval does not:

- approve DEC-002 through DEC-012;
- accept ADR-001 through ADR-007;
- promote GD-005;
- promote GD-006;
- promote GD-007;
- resolve JRN-003;
- resolve JRN-013;
- start Sprint 14;
- authorize application/business implementation;
- authorize new business source code;
- authorize SQL/schema/migration;
- authorize production database changes;
- authorize deployment;
- authorize release;
- promote production readiness;
- approve a framework;
- approve a database engine;
- approve a physical tenancy model;
- approve authentication architecture;
- approve a payment provider;
- approve offline architecture;
- approve a deployment runtime.

## Preserved program state

- GD-005: **PROPOSED**.
- GD-006: **PROPOSED**.
- GD-007: **PROPOSED**.
- JRN-003: **UNRESOLVED**.
- JRN-013: **UNRESOLVED**.
- Phase 0: **IN PROGRESS**.
- Sprint 14: **NOT AUTHORIZED**.
- Production readiness: **NO-GO**.

## Publication lifecycle boundary

This decision record may be prepared and reviewed through a bounded Draft PR.
The Draft PR must remain Draft until separate Product Owner READY authority is
provided for the PR number and exact final head.

Merge remains separately prohibited until separate Product Owner MERGE
authority is provided for the PR number and exact final head and all applicable
repository gates are satisfied.

Publication of this decision record does not itself create implementation,
Sprint 14, deployment, release, or production-readiness authority.

## Supersession

This decision remains binding within its scope until explicitly superseded by a
later Product Owner decision recorded in GitHub. A later decision must identify
what is superseded and preserve immutable decision history.

Attribution: Lab | zefry
