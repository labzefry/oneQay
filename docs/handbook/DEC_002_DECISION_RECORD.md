# DEC-002 — Backend Language and Application Framework Decision Record

> **Status:** Approved — substantive Product Owner decision
> **Phase:** 0 — Governance & Discovery
> **Canonical product:** oneQay
> **Developer & Product Engineering Entity:** Lab | zefry
> **Repository:** `labzefry/oneQay`
> **Product Owner:** `labzefry`

## Decision provenance

- Decision: **DEC-002 — Backend Language / Application Framework**.
- Decision result: **APPROVED**.
- Product Owner decision baseline: `504b10be44d45dfcfec9b6cfed4f72ed5748b564`.
- Verified decision baseline tree: `e4622a45f9f298b95358b3d662be3cd48607e4d9`.
- Decision date: 2026-08-09.
- Decision authority type: **Substantive technology decision only**.
- Implementation authority: **NOT GRANTED**.
- Sprint 14 authority: **NOT GRANTED**.
- Deployment authority: **NOT GRANTED**.
- Release authority: **NOT GRANTED**.
- Production readiness: **NO-GO**.

The baseline SHA and tree above are stable decision provenance. They are not a
claim that those identifiers remain the permanently current live GitHub state.
GitHub must be queried again before later lifecycle mutations.

## Approved decision

The Product Owner approved the following exact DEC-002 choices:

- **Backend Language:** PHP.
- **Application Framework:** Laravel.
- **Architecture Direction:** Modular Monolith First + Clean Architecture.
- **Domain/Application Framework Independence:** framework-independent.
- **Framework Role:** delivery/composition/infrastructure.
- **ADR-001 Scope Disposition:** reframe/reconcile after approval; do not Accept
  current Technical Preview wording as-is.

This decision selects the long-term backend language and application framework
family for oneQay. It does not install Laravel, pin a Laravel version, change a
runtime, or authorize application implementation.

## Enterprise product boundary

oneQay remains an:

**Enterprise Intelligent Business Management Platform**.

DEC-002 does not redefine oneQay as a POS-only product and does not bind the
product to:

- one industry;
- a single outlet;
- a single company;
- a single deployment stage;
- a distributed or microservice architecture.

The approved backend/framework direction must remain compatible with the
approved bounded MVP and with future enterprise capability evolution without
premature abstraction.

## Architecture boundary

The approved architecture direction is:

**Modular Monolith First + Clean Architecture.**

Dependency direction remains inward.

### Domain layer

The Domain layer owns entities, value objects, invariants, domain services, and
business rules. It must not depend on Laravel HTTP, ORM, queue, filesystem,
cache, service-provider, container, or other framework internals.

### Application layer

The Application layer owns use cases, orchestration, ports, and transaction
boundaries. It must remain framework-independent and depend on Domain contracts,
not on Laravel delivery or infrastructure mechanisms.

### Delivery and infrastructure

Laravel is approved as the application framework for delivery, composition, and
infrastructure concerns. Laravel may provide adapters and mechanisms for HTTP,
CLI, scheduling, queues, persistence integration, caching, filesystem access,
configuration, and other authorized infrastructure concerns, provided those
mechanisms remain outside Domain/Application business-rule ownership.

Framework convenience must not become an implicit permission to move business
invariants into controllers, ORM models, queue jobs, service providers, or other
framework-specific classes.

## Existing PHP Platform Foundation

The published PHP Platform Foundation through Sprint 13 is preserved as an
existing reusable engineering asset.

DEC-002 does not require destructive rewrite of framework-agnostic PHP
foundation contracts or tests merely to introduce Laravel later. Any future
adapter, bootstrap, dependency, or refactor work requires separate implementation
authority.

## ADR-001 reconciliation

Historical ADR-001 began as a **Proposed** Technical Preview v0.0.1 backend
candidate based on B1 Laravel/PHP.

DEC-002 does not treat that historical proposal as the source of substantive
authority. The Product Owner substantive DEC-002 decision is the authority.

ADR-001 is reconciled in place because it remained Proposed and therefore was
not immutable under the repository ADR lifecycle. The reconciled ADR:

- preserves Technical Preview provenance;
- represents the approved PHP/Laravel decision;
- expands the scope from Technical Preview-only wording to the bounded long-term
  backend language/application framework decision;
- records the framework-independence guardrails;
- becomes **Accepted** only after that reframing.

## Decisions explicitly outside DEC-002

DEC-002 does not decide or authorize:

- exact PHP version or support pin;
- exact Laravel version or package set;
- Composer dependency installation or update;
- frontend/PWA stack — DEC-003;
- Android approach — DEC-004;
- database engine or physical tenancy — DEC-005;
- authentication/MFA/session architecture — DEC-006;
- payment provider/compliance — DEC-007;
- offline POS semantics — DEC-008;
- deployment runtime and final Stage-1 capability — DEC-009;
- product/third-party license policy — DEC-010;
- privacy/retention/jurisdiction — DEC-011;
- RPO/RTO/support objectives — DEC-012;
- cache, queue, Redis, broker, search, cloud, container, or Kubernetes vendor;
- marketplace/plugin execution model;
- AI provider.

Those decisions remain independently gated.

## Implementation and lifecycle boundary

This decision does **not** authorize:

- `composer require` or framework installation;
- Laravel bootstrap;
- dependency or lockfile changes;
- application/business source implementation;
- refactor of the existing PHP foundation;
- database/schema implementation;
- SQL or migration execution;
- production database modification;
- Sprint 14;
- deployment;
- release;
- production-readiness promotion.

Substantive Decision Authority, READY Authority, MERGE Authority, Implementation
Authority, Deployment Authority, and Release Authority remain separate.

## Supersession

A future substantive change to the approved backend language, application
framework, architecture direction, Domain/Application framework-independence
boundary, or framework role requires a new explicit Product Owner decision and
the appropriate ADR supersession lifecycle.

Attribution: Lab | zefry
