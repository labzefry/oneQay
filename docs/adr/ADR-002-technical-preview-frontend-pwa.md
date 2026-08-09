# ADR-002: Frontend and PWA Stack

- Status: Accepted
- Original Technical Preview date: 2026-08-03
- Accepted via DEC-003: 2026-08-09
- Decision owner: Product Owner `labzefry`
- Substantive authority: `docs/handbook/DEC_003_DECISION_RECORD.md`
- Decision baseline: `dcb7e3f8de890530a00a0dd4fd310bc10762c72f`
- Decision baseline tree: `b78d1f1452469a8ba856092e647bef92410f2517`
- Scope: Frontend and first-party Web/PWA technology boundary for oneQay

## Context

ADR-002 originally recorded F1 Vue 3 + Inertia + Vite as a **Proposed** Technical Preview v0.0.1 candidate. That historical proposal optimized a bounded preview and did not constitute long-term substantive technology approval.

The Product Owner subsequently issued the explicit substantive **DEC-003 — Frontend / PWA Stack** decision on the baseline recorded above.

oneQay remains an **Enterprise Intelligent Business Management Platform**. The frontend decision must support the approved bounded MVP while remaining compatible with future Android/mobile, Public API, partner integration, and enterprise capability expansion without introducing unnecessary deployment or distributed-architecture complexity.

## Accepted decision

The accepted DEC-003 frontend/Web-PWA direction is:

- **Frontend Framework:** Vue 3.
- **Primary Composition Direction:** Vue Composition API.
- **Frontend Language Direction:** TypeScript-first for new oneQay frontend application code.
- **First-party Web/PWA Integration:** Inertia.
- **Architecture Model:** Modern Monolith Web Delivery + Explicit API Boundaries.
- **Build Tool:** Vite.
- **Frontend State Direction:** local-first; component state, Vue reactivity, and composables by default; Pinia only for genuinely shared client-side state where centralized state management is justified.
- **UI / Design-System Direction:** token-first, component-based, accessible, responsive, locale-aware, and tenant-aware.
- **PWA Direction:** installability and safe PWA foundation with explicit service-worker/cache boundaries.

The Product Owner substantive DEC-003 decision is the authority for these choices. Historical F1 Technical Preview material is provenance only.

## Architecture boundary

The overall architecture remains **Modular Monolith First + Clean Architecture**.

The first-party Web/PWA delivery model is:

**Modern Monolith Web Delivery + Explicit API Boundaries.**

### Domain and Application

Domain and Application layers remain framework-independent.

Business invariants, authoritative calculations, tenant authorization, financial rules, inventory rules, transaction correctness, and other Domain/Application behavior remain server-side and must not depend on Vue, Inertia, Vite, Pinia, browser APIs, service workers, or UI libraries.

### Inertia boundary

Inertia is a delivery/presentation integration mechanism for the first-party authenticated oneQay Web Application and PWA.

Inertia must not become:

- Domain;
- Application;
- owner of business rules;
- Android/mobile contract;
- Public API contract;
- partner/integration contract.

Application use cases must remain reusable across delivery adapters.

### API and channel boundary

The first-party Web/PWA channel is not required to use a public-style REST request for every application operation solely to manufacture separation.

Explicit and versioned API contracts remain mandatory where an API boundary exists.

Android/mobile, Public API, and partner integrations must not depend on Inertia.

## Frontend state-management guardrails

The default client-state strategy is local-first:

- component state;
- Vue reactivity;
- composables.

Pinia is directionally approved only for genuinely shared client-side state where centralized management is justified.

Pinia must not become a duplicate source of truth for authoritative server/domain state.

Tenant/session-scoped frontend state must be invalidated or reset when the active tenant, authorization context, or authenticated session changes.

## UI and design-system guardrails

The UI architecture is:

- token-first;
- component-based;
- accessible;
- responsive;
- locale-aware;
- tenant-aware.

It must support reusable primitives, keyboard accessibility, responsive Web/PWA behavior, optimized POS desktop/tablet workspace where appropriate, localization, tenant timezone/currency, loading/empty/error/degraded states, safe correlation/reference IDs, and performance budgets.

No specific UI component vendor or CSS framework is selected by DEC-003.

## PWA boundary

The approved PWA direction includes:

- Web App Manifest / installability direction;
- HTTPS requirement;
- service-worker boundary;
- safe static asset/application-shell caching;
- explicit cache version/update lifecycle;
- responsive installed-app experience.

The service worker remains a frontend infrastructure mechanism only.

Sensitive tenant, authentication, authorization, transaction, payment, financial, or other protected business data must not be persistently cached by default merely for PWA convenience.

## Offline boundary

DEC-003 does **not** approve offline transactional semantics.

The following remain deferred to DEC-008:

- offline sale mutation;
- offline payment mutation;
- queued business mutation;
- local transaction authority;
- synchronization semantics;
- replay semantics;
- conflict detection and resolution;
- server acknowledgement rules;
- offline recovery semantics.

Background Synchronization is progressive enhancement only. Core transactional correctness must not depend on browser Background Sync availability.

## Push-notification direction

Push notifications are directionally allowed for the PWA channel, but DEC-003 does not select a provider, backend, subscription-storage implementation, messaging package, or consent implementation.

Push notifications must not become a source of truth for business or transaction state.

## Historical Technical Preview provenance

The original Proposed ADR used F1 Vue 3 + Inertia + Vite for a Technical Preview deployment unit, with installability and safe static-asset caching while offline transaction semantics were deferred.

That historical proposal remains preserved as provenance. The current Accepted status derives only from the later substantive DEC-003 decision and this reconciliation.

## Alternatives considered

Historical alternatives remain relevant context:

- React/Next.js or another API-separated frontend could provide stronger physical client/API separation but would introduce additional routing, runtime, deployment, and operational complexity not required by the current Modern Monolith direction.
- Blade/Livewire/Alpine could simplify a Laravel-centric delivery path but provides a less direct fit for the approved rich Vue-based Web/PWA direction.
- A fully API-separated Vue SPA remains a possible future evolution if product/channel evidence justifies a different deployment or integration boundary.

No alternative is rejected permanently merely because DEC-003 selects the current direction.

## Decisions deliberately deferred

DEC-003 does not select or authorize:

- exact Vue version;
- exact Inertia version;
- exact Vite version;
- exact Pinia version;
- package manager;
- `package.json` contents;
- frontend lockfile;
- dependency installation;
- Tailwind CSS;
- shadcn-vue;
- another UI/component library;
- PWA implementation package/plugin;
- exact service-worker implementation;
- offline local database;
- offline business mutation;
- conflict-resolution implementation;
- push provider/backend;
- SSR implementation;
- exact browser support matrix;
- authentication/MFA/session architecture;
- Android technology stack;
- database engine or physical tenancy model;
- payment provider;
- deployment runtime.

## Implementation and lifecycle boundary

This Accepted ADR represents a substantive technology decision only. It does not authorize:

- package installation or `npm install`;
- Composer dependency changes;
- `package.json` or lockfile creation/modification;
- Vue, Inertia, Vite, Pinia, PWA, or service-worker implementation;
- frontend or backend application implementation;
- business source implementation;
- Sprint 14;
- SQL/schema/migration implementation or execution;
- production database modification;
- deployment;
- release;
- production-readiness promotion.

Substantive Decision Authority, Independent Review, READY Authority, MERGE Authority, Implementation Authority, Deployment Authority, and Release Authority remain separate.

## Supersession

A future substantive change to the approved frontend framework, language direction, first-party Web/PWA integration model, build-tool family, PWA boundary, frontend state-management direction, UI/design-system direction, or API/channel boundary requires a new explicit Product Owner decision and an ADR supersession path.

Attribution: Lab | zefry
