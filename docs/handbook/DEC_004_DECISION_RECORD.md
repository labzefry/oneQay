# DEC-004 — Android Approach Decision Record

> **Status:** Approved — substantive Product Owner decision
> **Phase:** 0 — Governance & Discovery
> **Canonical product:** oneQay
> **Developer & Product Engineering Entity:** Lab | zefry
> **Repository:** `labzefry/oneQay`
> **Product Owner:** `labzefry`

## Decision provenance

- Decision: **DEC-004 — Android Approach**.
- Decision result: **APPROVED**.
- Product Owner decision baseline: `97b2e5066118af2b3e9467afc71e84dce228eb38`.
- Verified decision baseline tree: `2f979948184f475b52cf87b2d105c56364ebe883`.
- Decision date: 2026-08-09.
- Decision authority type: **Substantive technology / architecture decision only**.
- Android project creation authority: **NOT GRANTED**.
- Kotlin / Jetpack Compose implementation authority: **NOT GRANTED**.
- Package/dependency authority: **NOT GRANTED**.
- Backend/API implementation authority: **NOT GRANTED**.
- Sprint 14 authority: **NOT GRANTED**.
- Deployment authority: **NOT GRANTED**.
- Release authority: **NOT GRANTED**.
- Production authority: **NOT GRANTED**.
- Production readiness: **NO-GO**.

The baseline SHA and tree above are stable decision provenance. They are not a claim that those identifiers remain permanently current. GitHub must be freshly verified before later lifecycle mutations.

## Enterprise product boundary

oneQay remains an:

**Enterprise Intelligent Business Management Platform**.

DEC-004 selects a bounded Android delivery direction. It does not redefine oneQay as:

- Android-only;
- mobile-first;
- POS-only;
- single-device;
- single-outlet;
- single-company;
- industry-specific.

The first bounded MVP delivery slice remains **POS CORE TRANSACTION & OUTLET OPERATIONS** under DEC-001. DEC-004 does not expand that product scope or create implementation authority.

## Approved decision

### D-004-01 — Android Delivery Strategy

**APPROVED: HYBRID STAGED APPROACH.**

- Preserve PWA as the approved general mobile-capable channel.
- Dedicated Android is a complementary bounded delivery channel.
- Native Android is introduced where validated POS, hardware, lifecycle, security, performance, or operational requirements materially justify native capability.
- Dedicated Android implementation is not required immediately by this decision.

### D-004-02 — Primary Android Technology

**APPROVED: Native Android using Kotlin.**

Kotlin is the approved language direction for future dedicated oneQay Android application code.

DEC-004 does not select an exact Kotlin version, Android SDK version, Gradle version, Android Gradle Plugin version, plugin version, or dependency set.

### D-004-03 — Android UI Technology

**APPROVED: Jetpack Compose.**

Jetpack Compose is the approved primary UI technology direction for future dedicated oneQay Android application UI.

Exact Compose, Material, navigation, lifecycle, testing, or other UI dependency versions remain separately gated.

### D-004-04 — Web/PWA and Android Relationship

**APPROVED:**

- PWA remains the preferred general mobile-capable first-party web experience established by DEC-003.
- Dedicated Android complements rather than replaces PWA.
- Use PWA where browser/platform capability is sufficient.
- Use dedicated Android where validated native/device capability is materially required, including bounded POS/device workflows.
- oneQay is not redefined as Android-only or mobile-first.

### D-004-05 — API / Delivery Boundary

**APPROVED:**

Any separate Android application must consume explicit application/API contracts.

Android must not:

- depend on Inertia as its permanent application protocol;
- directly access oneQay database tables;
- own server-side business invariants;
- bypass server-side tenant authorization;
- become authoritative for transaction, inventory, payment, financial, or other server-owned business correctness.

Application use cases must remain reusable across delivery adapters. Versioned API contract evolution remains mandatory wherever an Android/API boundary exists.

Exact API endpoints, wire schemas, transport details, and authentication mechanisms remain separately gated.

### D-004-06 — Device Capability Boundary

**APPROVED:**

Dedicated Android may provide native delivery adapters for capabilities such as:

- camera;
- barcode/QR scanning;
- receipt printers;
- Bluetooth;
- USB;
- NFC;
- device identity;
- device/outlet registration;
- notifications;
- Android lifecycle integration;
- bounded background processing.

Device integration remains an Interface/Infrastructure concern. Device adapters must not become owners of Domain or Application business rules.

No hardware, printer, terminal, OEM, or device-SDK vendor is selected by DEC-004.

### D-004-07 — Offline Ownership Boundary

**APPROVED:**

DEC-008 remains the exclusive substantive owner of offline POS transactional semantics and conflict-resolution policy.

DEC-004 approves only an Android architecture direction capable of supporting future DEC-008 outcomes without architectural replacement.

DEC-004 does not decide:

- offline sale authority;
- offline payment authority;
- offline stock mutation;
- queued business mutations;
- synchronization semantics;
- replay semantics;
- sequence allocation;
- conflict detection;
- conflict resolution;
- server acknowledgement;
- inventory reconciliation;
- disconnected transaction ownership.

### D-004-08 — Local State / Security Principle

**APPROVED:**

Android local state must remain:

- minimal;
- explicitly classified;
- tenant scoped;
- session/auth-context scoped where applicable;
- invalidated or isolated when active tenant, authenticated user, authorization context, or session changes;
- non-authoritative for server-owned business invariants.

Sensitive credential/key material requiring device persistence must follow an appropriate Android platform secure-storage/keystore principle.

Restricted secrets must not be embedded in Android source, assets, logs, analytics, or distributable client bundles.

Exact authentication/session architecture remains DEC-006. Exact local database, storage, encryption, or secure-storage libraries are not selected by DEC-004.

### D-004-09 — Distribution Direction

**APPROVED:**

The future dedicated Android architecture must remain compatible with:

- Google Play distribution;
- controlled enterprise/internal distribution where appropriate.

This is distribution-direction approval only. DEC-004 does not select or authorize:

- Play Console account/configuration;
- package/application ID;
- production signing keys;
- signing operational procedure;
- MDM provider;
- private application store;
- release-channel configuration;
- deployment;
- production publication.

### D-004-10 — ADR Disposition

**APPROVED:**

DEC-004 requires a new Android architecture decision record:

**ADR-008 — Android Delivery Approach**

Canonical repository path:

`docs/adr/ADR-008-android-delivery-approach.md`

ADR-001 through ADR-007 must not be renumbered or rewritten to manufacture an Android ADR. ADR-008 represents this bounded substantive decision and may become Accepted only through the authorized DEC-004 publication lifecycle.

## Relationship with DEC-002

DEC-004 preserves DEC-002:

- PHP as backend language direction;
- Laravel as application framework;
- Modular Monolith First + Clean Architecture;
- framework-independent Domain/Application;
- framework role bounded to delivery/composition/infrastructure.

Android is another delivery adapter. Domain/Application must not depend on Android SDK, Kotlin Android framework APIs, Jetpack Compose, mobile UI frameworks, or device-vendor SDKs.

## Relationship with DEC-003

DEC-004 preserves DEC-003:

- Vue 3;
- Vue Composition API;
- TypeScript-first Web/PWA direction;
- Inertia for first-party authenticated Web/PWA delivery;
- Vite;
- Modern Monolith Web Delivery + Explicit API Boundaries;
- PWA foundation;
- Android/mobile independence from Inertia.

PWA remains a first-class approved channel. Dedicated Android is complementary and must not force the Web/PWA channel to become a wrapper around native assumptions.

## Relationship with DEC-006

DEC-004 does not finalize authentication, MFA, token, session, refresh, device-trust, recovery, or revocation architecture.

Those remain owned by DEC-006. Android must be able to implement the future DEC-006 contract without becoming the authority for identity, tenant membership, authorization, or session correctness.

## Relationship with DEC-008

DEC-004 does not finalize offline POS behavior. DEC-008 remains the owner of offline transaction authority, sync/replay, conflict resolution, reconciliation, sequence allocation, and disconnected-operation semantics.

Android architecture must remain capable of supporting a future DEC-008 decision without requiring replacement of the approved delivery boundary.

## API and application boundary

The intended dependency direction remains:

```text
Android UI / device adapters
        |
        v
Android delivery/application adapter
        |
        v
Explicit versioned oneQay API/application contract
        |
        v
Server Application use cases
        |
        v
Domain
```

The following are prohibited by DEC-004:

```text
Android -> database tables
Android -> Inertia as permanent mobile protocol
Android -> server business-rule ownership
Domain/Application -> Android SDK or Jetpack Compose
```

Tenant membership, authorization, transaction correctness, inventory authority, payment correctness, and financial correctness remain server-enforced.

## Device-capability boundary

Native Android may encapsulate device/platform integration required by validated workflows. A device adapter may translate platform events and hardware interaction into application-facing inputs, but must not silently create new business invariants or cross tenant boundaries.

Device capability presence is not hardware-vendor approval. Every future vendor SDK remains subject to dependency, security, licensing, maintenance, and exit-strategy review.

## Local-state and tenant isolation principles

Any future Android local persistence must follow data classification and tenant isolation controls. At minimum:

- authenticated or tenant-sensitive data is minimized;
- tenant/session scoped state cannot leak across tenant or user switches;
- local data is not treated as authoritative merely because it exists on-device;
- logout/revocation/tenant-switch handling must invalidate or isolate relevant state;
- credentials and restricted secrets are never embedded in distributable artifacts;
- device-side logs and analytics must not disclose secrets or restricted tenant data.

Exact storage implementation remains deferred.

## Distribution direction

DEC-004 requires architecture compatibility with Google Play and controlled enterprise/internal distribution. This does not select a store account, release track, package identity, signing key, MDM solution, or production distribution procedure.

## Deliberately deferred

DEC-004 does not select or authorize:

- exact Kotlin version;
- exact Jetpack Compose version;
- exact Gradle version/configuration;
- exact Android Gradle Plugin version;
- exact Android SDK;
- `minSdk`;
- `targetSdk`;
- package/application ID;
- Android project directory structure;
- exact Android dependencies;
- exact REST endpoints or payloads;
- exact authentication/token/MFA mechanism;
- exact local database;
- exact secure-storage library;
- database engine;
- physical tenancy model;
- payment provider;
- offline transactional semantics;
- synchronization/conflict implementation;
- push provider;
- analytics provider;
- crash-reporting provider;
- device-management/MDM provider;
- hardware/printer vendor;
- application-signing operational procedure;
- Play Console configuration;
- iOS stack;
- Apache ECharts implementation;
- deployment runtime.

These remain separately gated.

## Consequences

### Positive

- Preserves the already-approved PWA investment for general mobile workflows.
- Provides a native Android path when POS/device capabilities require it.
- Keeps server-side application and domain authority independent from mobile frameworks.
- Avoids forcing a second full client implementation before validated native requirements exist.
- Leaves future offline semantics open to DEC-008 without architectural replacement.

### Tradeoffs

- A hybrid strategy can eventually create two first-party UI delivery surfaces requiring coordinated UX, testing, versioning, and release practices.
- Kotlin/Jetpack Compose introduces Android-specific engineering capability alongside Vue/TypeScript Web/PWA capability.
- Vendor-specific hardware integrations may create additional lifecycle and security obligations when later authorized.
- API compatibility becomes a stronger cross-channel governance requirement.

## Security, privacy, and tenant impact

DEC-004 preserves deny-by-default tenant authorization and server authority. Android device possession, local cache state, host identity, or client-supplied tenant identifiers must never independently authorize tenant access.

Sensitive device persistence follows the platform secure-storage/keystore principle, with exact library and key-management design deferred. Secret material must not be stored in source control or distributed client assets.

Security-critical authentication/session choices remain DEC-006. Offline data and transaction security remain DEC-008.

## Validation and fitness functions

Future implementation authority should require evidence that:

- Android consumes an explicit application/API boundary and does not depend on Inertia;
- Domain/Application have no Android/Compose/vendor-SDK dependency;
- tenant/session state is isolated across tenant, user, auth-context, and session transitions;
- local state is non-authoritative for server-owned invariants;
- device adapters do not bypass server authorization;
- PWA remains functional for workflows that do not require native capability;
- any offline transactional implementation is blocked until DEC-008 grants its own authority;
- package/dependency choices pass security, license, maintenance, and exit-strategy review;
- distribution implementation is separately authorized before production publication.

## Implementation authority boundary

DEC-004 is a substantive technology/architecture decision only.

It does **not** authorize:

- creation of an Android project;
- `AndroidManifest.xml`;
- Gradle files or wrapper;
- Kotlin source;
- Java source;
- Jetpack Compose source;
- Android resources;
- dependency installation;
- Android API client implementation;
- backend/API implementation;
- frontend/business application implementation;
- SQL/schema/migration;
- production database modification;
- Sprint 14;
- deployment;
- release;
- production publication or production-readiness promotion.

## Supersession path

DEC-004 remains binding within its stated scope until an explicit later Product Owner decision supersedes or replaces it. A later change must preserve decision provenance and use a new or superseding ADR where the repository ADR lifecycle requires it; accepted historical evidence must not be retroactively rewritten.

Attribution: Lab | zefry
