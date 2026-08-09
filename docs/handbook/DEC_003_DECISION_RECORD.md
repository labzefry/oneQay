# DEC-003 — Frontend / PWA Stack Decision Record

> **Status:** Approved — substantive Product Owner decision
> **Phase:** 0 — Governance & Discovery
> **Canonical product:** oneQay
> **Developer & Product Engineering Entity:** Lab | zefry
> **Repository:** `labzefry/oneQay`
> **Product Owner:** `labzefry`

## Decision provenance

- Decision: **DEC-003 — Frontend / PWA Stack**.
- Decision result: **APPROVED**.
- Product Owner decision baseline: `dcb7e3f8de890530a00a0dd4fd310bc10762c72f`.
- Verified decision baseline tree: `b78d1f1452469a8ba856092e647bef92410f2517`.
- Decision date: 2026-08-09.
- Decision authority type: **Substantive technology decision only**.
- Package/dependency installation authority: **NOT GRANTED**.
- Frontend/backend implementation authority: **NOT GRANTED**.
- Sprint 14 authority: **NOT GRANTED**.
- Deployment authority: **NOT GRANTED**.
- Release authority: **NOT GRANTED**.
- Production readiness: **NO-GO**.

The baseline SHA and tree above are stable decision provenance. They are not a claim that those identifiers remain the permanently current live GitHub state. GitHub must be queried again before later lifecycle mutations.

## Approved decision

The Product Owner approved the following bounded DEC-003 direction:

- **Frontend Framework:** Vue 3.
- **Primary Frontend Composition Direction:** Vue Composition API.
- **Frontend Language Direction:** TypeScript-first for new oneQay frontend application code.
- **Laravel / Web Integration Model:** Inertia as the primary delivery integration for the first-party authenticated oneQay Web Application and PWA.
- **Architecture Model:** Modern Monolith Web Delivery + Explicit API Boundaries.
- **Build Tool:** Vite.
- **Frontend State Direction:** local-first; component state, Vue reactivity, and composables by default; Pinia only where genuinely shared client-side state justifies centralized management.
- **UI / Design-System Direction:** token-first, component-based, accessible, responsive, locale-aware, and tenant-aware.
- **PWA Direction:** installability and safe PWA foundation with explicit service-worker/cache boundaries.
- **ADR-002 Disposition:** reframe/reconcile the historical Technical Preview-only proposal, then represent ADR-002 as Accepted only within the exact DEC-003 boundary.

## Enterprise product boundary

oneQay remains an:

**Enterprise Intelligent Business Management Platform**.

DEC-003 selects a frontend/Web-PWA technology direction. It does not redefine oneQay as:

- POS-only;
- single-outlet-only;
- single-company-only;
- browser-only;
- Web/PWA-only;
- industry-specific.

The approved frontend direction must support the bounded MVP while remaining compatible with future Android/mobile, Public API, partner integration, and broader enterprise capability evolution without premature distributed architecture.

## Architecture boundary

The approved architecture remains:

**Modular Monolith First + Clean Architecture.**

The Web/PWA integration model is:

**Modern Monolith Web Delivery + Explicit API Boundaries.**

### Domain and Application

Business invariants, authoritative calculations, tenant authorization, financial rules, inventory rules, transaction correctness, and other Domain/Application behavior remain server-side and framework-independent.

Vue, Inertia, Vite, Pinia, browser APIs, service workers, and UI libraries must not become dependencies of Domain or Application layers.

### Inertia role

Inertia is approved only as a delivery/presentation integration mechanism for the first-party authenticated Web Application and PWA.

Inertia must not become:

- the Domain layer;
- the Application layer;
- the owner of business rules;
- the permanent Android/mobile contract;
- the Public API contract;
- the partner/integration contract.

Application use cases must remain reusable across delivery adapters.

### Explicit API and channel boundary

The first-party Web/PWA channel is not required to route every application operation through a public-style REST request solely to manufacture frontend/backend separation.

Where an API boundary exists, explicit and versioned API contracts remain mandatory.

Android/mobile, Public API, and partner integrations must remain independent of Inertia and capable of using explicit application/API contracts.

## Frontend framework and language

Vue 3 is the approved primary Web/PWA frontend framework family.

Vue Composition API is the approved primary composition direction.

TypeScript-first is approved for new oneQay frontend application code.

DEC-003 does not pin an exact Vue or TypeScript toolchain version and does not authorize creation or modification of `package.json`, a frontend lockfile, or any package installation.

## Build tool

Vite is the approved frontend build-tool family.

The exact Vite version, configuration, plugins, package manager, lockfile, build environment, and deployment integration remain separately gated.

## Frontend state-management direction

The approved direction is **local-first**.

Default client state mechanisms are:

- component state;
- Vue reactivity;
- composables.

Pinia is directionally approved only for genuinely shared client-side state where centralized state management is justified.

Pinia must not become a duplicate source of truth for authoritative server/domain state.

Server-authoritative state remains delivered through Inertia page data for the first-party Web/PWA channel or explicit application/API contracts where appropriate.

Tenant/session-scoped frontend state must be invalidated or reset when the active tenant, authorization context, or authenticated session changes.

## UI and design-system direction

The approved UI architecture is:

- token-first;
- component-based;
- accessible;
- responsive;
- locale-aware;
- tenant-aware.

The design-system direction must support:

- reusable primitives;
- consistent interaction states;
- keyboard accessibility;
- responsive Web/PWA behavior;
- optimized POS desktop/tablet workspace where appropriate;
- localization;
- tenant timezone and currency;
- loading, empty, error, and degraded states;
- safe correlation/reference IDs;
- performance budgets.

DEC-003 does not select Tailwind CSS, shadcn-vue, another CSS framework, or a specific UI component vendor.

## PWA direction

DEC-003 approves a Progressive Web Application foundation for the first-party oneQay Web channel.

Approved direction includes:

- Web App Manifest / installability direction;
- secure HTTPS delivery requirement;
- service-worker boundary;
- safe static asset/application-shell caching;
- explicit cache version/update lifecycle;
- responsive installed-app experience.

The service worker remains a frontend infrastructure mechanism only.

Sensitive tenant, authentication, authorization, transaction, payment, financial, or other protected business data must not be persistently cached by default merely for PWA convenience.

DEC-003 does not select an exact service-worker implementation or PWA package/plugin.

## Offline boundary

DEC-003 does **not** approve offline transactional semantics.

The following remain explicitly deferred to DEC-008:

- offline sale mutation;
- offline payment mutation;
- queued business mutation;
- local transaction authority;
- synchronization semantics;
- replay semantics;
- conflict detection;
- conflict resolution;
- server acknowledgement rules;
- offline recovery semantics.

Background Synchronization may only be considered as progressive enhancement. Core transactional correctness must not depend on browser Background Sync availability.

## Push-notification direction

Push-notification capability is directionally allowed for the PWA channel.

DEC-003 does not select:

- push provider;
- notification provider;
- notification backend;
- subscription-storage implementation;
- messaging package;
- user-consent implementation.

Push notifications must not become a source of truth for business or transaction state.

## Historical F1 provenance

Historical Technical Preview material proposed F1:

**Vue 3 + Inertia + Vite.**

That historical proposal is provenance only and was not the source of long-term substantive authority.

The Product Owner substantive DEC-003 decision is the authority for the approved frontend/Web-PWA direction recorded here.

## ADR-002 reconciliation

ADR-002 began as a **Proposed** Technical Preview v0.0.1 frontend/PWA candidate.

Because ADR-002 remained Proposed, it may be reconciled in place without rewriting an immutable Accepted decision.

The reconciled ADR-002 must:

- preserve the historical F1 Technical Preview provenance;
- identify DEC-003 as substantive authority;
- expand scope from Technical Preview-only wording to the bounded frontend/Web-PWA decision;
- record Vue 3 and Vue Composition API;
- record TypeScript-first direction;
- record Inertia as first-party Web/PWA delivery integration;
- record Vite;
- preserve explicit API/mobile boundaries;
- preserve Domain/Application framework independence;
- preserve tenant/cache/security guardrails;
- preserve the DEC-008 offline boundary;
- preserve implementation-authority separation.

ADR-002 becomes **Accepted** only as the repository representation of this exact DEC-003 boundary.

## Decisions deliberately deferred

DEC-003 does not decide or authorize:

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

Those decisions remain independently gated where applicable.

## Implementation and lifecycle boundary

This substantive decision does **not** authorize:

- package installation;
- `npm install`;
- Composer dependency changes;
- `package.json` creation or modification;
- lockfile creation or modification;
- Vue implementation;
- Inertia implementation;
- Vite implementation;
- Pinia implementation;
- PWA implementation;
- service-worker implementation;
- frontend application implementation;
- backend application implementation;
- business source implementation;
- Sprint 14;
- SQL/schema/migration implementation or execution;
- production database modification;
- DEC-004;
- deployment;
- release;
- production-readiness promotion.

Substantive Decision Authority, Independent Review, READY Authority, MERGE Authority, Implementation Authority, Deployment Authority, and Release Authority remain separate lifecycle authorities.

## Supersession

A future substantive change to the approved frontend framework, language direction, first-party Web/PWA integration model, build-tool family, PWA boundary, state-management direction, UI/design-system direction, or explicit API/channel boundary requires a new explicit Product Owner decision and the appropriate ADR supersession lifecycle.

Attribution: Lab | zefry
