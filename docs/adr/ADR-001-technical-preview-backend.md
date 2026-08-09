# ADR-001: Backend Language and Application Framework

- Status: Accepted
- Original Technical Preview date: 2026-08-03
- Accepted via DEC-002: 2026-08-09
- Decision owner: Product Owner `labzefry`
- Substantive authority: `docs/handbook/DEC_002_DECISION_RECORD.md`
- Decision baseline: `504b10be44d45dfcfec9b6cfed4f72ed5748b564`
- Decision baseline tree: `e4622a45f9f298b95358b3d662be3cd48607e4d9`
- Scope: Backend language and application framework boundary for oneQay

## Context

ADR-001 originally recorded B1 Laravel/PHP as a **Proposed** Technical Preview
v0.0.1 candidate. That historical proposal optimized for a bounded sandbox and
did not constitute long-term substantive technology approval.

The Product Owner subsequently completed DEC-002 evidence closure and issued an
explicit substantive DEC-002 decision after Minimal Delta Verification against
the decision baseline recorded above.

oneQay remains an **Enterprise Intelligent Business Management Platform**. The
backend choice must support the approved bounded MVP while remaining compatible
with future enterprise capability expansion without making premature distributed
architecture choices.

## Accepted decision

The accepted DEC-002 backend/application-framework direction is:

- **Backend Language:** PHP.
- **Application Framework:** Laravel.
- **Architecture Direction:** Modular Monolith First + Clean Architecture.
- **Domain/Application Framework Independence:** framework-independent.
- **Framework Role:** delivery/composition/infrastructure.

The Product Owner substantive DEC-002 decision is the authority for these
choices. Historical B1 Technical Preview material is provenance only.

## Framework boundary

Laravel is the approved application framework for delivery, composition, and
infrastructure mechanisms. It is not the owner of oneQay business rules.

### Domain

Domain code must remain independent of Laravel and must not depend on HTTP,
Eloquent/ORM, queues, filesystem, cache, service providers, the Laravel
container, or external providers.

### Application

Application use cases, orchestration, ports, and transaction boundaries must
remain framework-independent and depend inward on Domain contracts.

### Delivery / composition / infrastructure

Laravel may be used, when separately authorized for implementation, for:

- HTTP and CLI delivery;
- dependency composition and bootstrap;
- validation adapters at trust boundaries;
- persistence adapters;
- queues and scheduling;
- cache and filesystem adapters;
- configuration and infrastructure integration.

These mechanisms must remain behind the applicable Domain/Application
boundaries and ports.

## Architecture guardrails

- Preserve **Modular Monolith First**; do not introduce microservices or a
  distributed architecture without separate evidence and authority.
- Preserve **Clean Architecture** dependency direction.
- Preserve framework-independent Domain and Application layers.
- Do not redefine oneQay as POS-only.
- Do not introduce permanent single-outlet-only, single-company-only, or
  industry-specific assumptions through the framework choice.
- Preserve compatibility with multi-outlet and future enterprise capability
  evolution.
- Keep tenant isolation, authorization, idempotency, transaction, audit, and
  recovery rules as application/domain concerns where appropriate rather than
  delegating their business semantics to framework convenience.
- Do not assume persistent workers, Redis, a queue vendor, a cache vendor, or a
  specific cloud/container runtime until the relevant decision is approved.

## Historical Technical Preview provenance

The original Proposed ADR-001 selected B1 Laravel/PHP for Technical Preview
v0.0.1 and considered:

- Symfony/PHP as B2;
- NestJS/Node.js as B3.

The historical proposal emphasized cPanel compatibility and time-to-preview.
It also recorded that exact PHP/framework versions, extensions, worker
capability, and hosting support remained unverified.

That historical evidence is preserved. It is not treated as the substantive
approval source and does not broaden DEC-002.

## Consequences

Positive consequences:

- published framework-agnostic PHP foundation code and tests can be reused;
- the backend language does not require a cross-language rewrite;
- Laravel can be introduced later as an outer delivery/composition/
  infrastructure mechanism;
- the architecture can evolve from shared hosting toward VPS, containers, cloud,
  or other approved stages without changing Domain/Application business rules.

Material risks:

- framework convenience can create accidental coupling if Laravel-specific
  objects leak into Domain/Application;
- exact runtime/framework version and support lifecycle still require bounded
  dependency/runtime verification;
- background-worker assumptions can fail on constrained hosting.

These risks are controlled through architecture fitness tests, dependency
review, and the later deployment/runtime gate rather than by weakening the
framework-independence rule.

## Deferred decisions

This ADR does not select:

- exact PHP version;
- exact Laravel version;
- database engine or physical tenancy;
- authentication/session architecture;
- payment provider;
- offline semantics;
- queue/cache vendor;
- Redis or message broker;
- deployment runtime;
- cloud/container/orchestration platform.

Those remain governed by their respective DEC/ADR lifecycle.

## Authority boundary

Acceptance of ADR-001 records the already-approved DEC-002 technology decision.
It does **not** authorize framework installation, dependency changes, Laravel
bootstrap, application/business source implementation, database/schema/SQL/
migration execution, Sprint 14, deployment, release, or production-readiness
promotion.

Attribution: Lab | zefry
