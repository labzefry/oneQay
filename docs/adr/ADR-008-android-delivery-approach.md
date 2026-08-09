# ADR-008 — Android Delivery Approach

- Status: Accepted — representation of substantive DEC-004 after publication
- Date: 2026-08-09
- Decision owner: Product Owner `labzefry`
- Substantive authority: DEC-004 — Android Approach
- Decision baseline: `97b2e5066118af2b3e9467afc71e84dce228eb38`
- Decision baseline tree: `2f979948184f475b52cf87b2d105c56364ebe883`
- Scope: bounded Android delivery architecture direction only

## Context

oneQay is an **Enterprise Intelligent Business Management Platform** with an approved first bounded MVP slice of **POS CORE TRANSACTION & OUTLET OPERATIONS**. DEC-003 already approves Vue 3, Inertia, Vite, TypeScript-first Web/PWA delivery, Modern Monolith Web Delivery + Explicit API Boundaries, and Android/mobile independence from Inertia.

The Product Owner has now approved DEC-004 to define how a future dedicated Android channel relates to PWA, device-intensive POS workflows, server-side application contracts, tenant isolation, security, and later offline requirements without granting Android implementation authority.

This ADR represents the Product Owner substantive DEC-004 decision. It does not independently create that authority.

## Decision drivers

- Preserve PWA as the approved general mobile-capable channel.
- Provide a first-class native path when validated device/POS requirements materially require it.
- Keep Domain/Application independent from Android, mobile UI frameworks, and device vendors.
- Preserve server-side authoritative business invariants and tenant authorization.
- Maintain explicit API/mobile boundaries independent from Inertia.
- Remain capable of supporting future DEC-008 offline semantics without architectural replacement.
- Avoid premature duplicate mobile implementation where PWA is sufficient.
- Preserve secure local-state and distribution boundaries.

## Decision

### Delivery strategy

Adopt a **Hybrid Staged Approach**.

PWA remains the preferred general mobile-capable first-party web experience. Dedicated Android is a complementary bounded delivery channel introduced for workflows where validated device, POS, lifecycle, security, performance, or operational requirements materially justify native capability.

DEC-004 does not require dedicated Android implementation immediately.

### Primary Android technology

Use **Native Android with Kotlin** as the language/platform direction for future dedicated oneQay Android application code.

Exact Kotlin, Android SDK, Gradle, Android Gradle Plugin, and package/dependency versions remain deferred.

### Android UI technology

Use **Jetpack Compose** as the primary UI technology direction for future dedicated oneQay Android UI.

Exact Compose, Material, navigation, lifecycle, testing, and related dependency versions remain deferred.

### Web/PWA relationship

- PWA remains first-class and is not replaced by Android.
- Use PWA where browser/platform capabilities are sufficient.
- Use dedicated Android where validated native/device requirements are materially required.
- oneQay is not redefined as Android-only or mobile-first.

### API / delivery boundary

Any dedicated Android application must consume explicit application/API contracts.

Android must not:

- depend on Inertia as its permanent protocol;
- access oneQay database tables directly;
- own server-side business invariants;
- bypass server-side tenant authorization;
- become authoritative for transaction, inventory, payment, financial, or other server-owned correctness.

Application use cases remain reusable across delivery adapters. Versioned contract evolution is mandatory wherever the Android/API boundary exists.

### Device adapter boundary

Dedicated Android may provide native adapters for capabilities including:

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

These remain Interface/Infrastructure concerns. Device adapters must not become owners of Domain or Application business rules. No hardware or printer vendor is selected.

### Authentication boundary

DEC-006 remains the owner of authentication, MFA, session, token, recovery, revocation, and related identity/security architecture.

DEC-004 requires only that Android be compatible with the future DEC-006 contract. Android possession or local state cannot independently establish tenant membership or authorization.

### Offline boundary

DEC-008 remains the exclusive substantive owner of offline POS transactional semantics and conflict-resolution policy.

DEC-004 does not define offline sale/payment authority, stock mutation, queueing, synchronization, replay, sequence allocation, conflict resolution, server acknowledgement, reconciliation, or disconnected transaction ownership.

The Android architecture must be capable of supporting the future DEC-008 decision without architectural replacement.

### Local state / security principle

Android local state must be minimal, explicitly classified, tenant scoped, session/auth-context scoped where applicable, isolated or invalidated on relevant tenant/user/auth/session changes, and non-authoritative for server-owned invariants.

Sensitive credential/key material requiring device persistence must follow an appropriate Android platform secure-storage/keystore principle. Restricted secrets must not be embedded in source, assets, logs, analytics, or distributable client bundles.

Exact local database, storage, encryption, and secure-storage libraries remain deferred.

### Distribution direction

The dedicated Android architecture must remain compatible with:

- Google Play distribution;
- controlled enterprise/internal distribution where appropriate.

DEC-004 does not select Play Console configuration, package/application ID, signing keys or procedures, MDM provider, private store, release channels, deployment, or production publication.

## Alternatives considered

### PWA-only

**Benefits:** one client stack, lowest duplicated delivery effort, direct continuity with DEC-003.

**Tradeoff:** may be insufficient for validated hardware/device workflows requiring stronger native platform integration.

**Disposition:** not selected as the permanent exclusive Android/mobile approach; retained where PWA capability is sufficient.

### Full native Android immediately

**Benefits:** direct access to Android platform/device capabilities from the start.

**Tradeoff:** duplicates client delivery effort before native requirements are sufficiently validated and conflicts with the principle of future-compatible, not future-overengineered.

**Disposition:** not selected; native Android is staged and bounded.

### Flutter

**Benefits:** cross-platform mobile UI and potential future multi-platform reuse.

**Tradeoff:** introduces another application framework/language and still requires native integration for some device/vendor capabilities.

**Disposition:** not selected for DEC-004.

### React Native

**Benefits:** TypeScript ecosystem familiarity and cross-platform direction.

**Tradeoff:** does not reuse Vue UI architecture directly and may still require native modules for device-heavy integration.

**Disposition:** not selected for DEC-004.

## Consequences

### Positive effects

- Preserves PWA investment and avoids immediate duplicate mobile implementation.
- Provides a direct native path for validated POS/device requirements.
- Keeps Domain/Application and authoritative business rules server-side and framework-independent.
- Makes Android an explicit delivery adapter rather than a parallel business system.
- Keeps DEC-006 and DEC-008 authority boundaries intact.
- Supports future distribution through Play and controlled enterprise channels.

### Negative effects and tradeoffs

- A future dedicated Android application creates another first-party delivery surface to design, test, version, secure, and release.
- Kotlin/Compose capability must coexist with Vue/TypeScript Web/PWA capability.
- Device-vendor integrations may introduce additional maintenance and supply-chain obligations when later approved.
- Explicit API compatibility becomes a stronger long-term cross-channel contract requirement.

## Security, privacy, and tenant impact

- Tenant membership and authorization remain server-enforced.
- Client-provided tenant identity is not sufficient authorization.
- Local Android state must not leak across tenant/user/session boundaries.
- Restricted data and secret handling follow oneQay security classification and no-secret-in-client principles.
- Exact authentication/session mechanisms are deferred to DEC-006.
- Offline transaction/data security is deferred to DEC-008.
- Future hardware/vendor dependencies require separate security, licensing, maintenance, and exit-strategy review.

## Migration and rollout principles

No migration or rollout is authorized by this ADR publication.

When separate implementation authority is later granted:

1. keep PWA operational for workflows that do not require native capability;
2. introduce Android only behind explicit application/API contracts;
3. validate native capability triggers with bounded workflows;
4. isolate device integration behind adapters;
5. stage distribution and operational controls separately from technology selection;
6. preserve server-side business authority throughout rollout.

## Rollback and supersession principles

Because DEC-004 is an architecture direction rather than an implementation, rollback means not activating or continuing the dedicated Android delivery path until later authority or requirements justify it. PWA remains a valid approved channel.

Any substantive replacement of Kotlin, Jetpack Compose, or the Hybrid Staged Approach requires a later explicit Product Owner decision and an ADR supersession path. Accepted historical evidence must not be rewritten.

## Validation and fitness functions

Future authorized implementation should demonstrate:

- Android depends on explicit API/application contracts, not Inertia;
- no direct Android database access;
- Domain/Application have no Android SDK, Compose, or vendor-SDK dependency;
- server-side tenant authorization remains mandatory;
- tenant/user/session changes isolate or invalidate relevant local state;
- local state does not become authoritative for server-owned invariants;
- PWA remains viable for non-native workflows;
- offline transactional mutation remains blocked until DEC-008 grants separate authority;
- device/vendor dependencies pass security and dependency governance;
- production distribution occurs only under separate deployment/release authority.

## Follow-up decisions

Separately gated follow-up decisions include:

- exact Kotlin/Compose/Gradle/SDK versions;
- Android project/package structure;
- API endpoint and authentication implementation;
- DEC-006 authentication/MFA/session architecture;
- DEC-008 offline POS semantics;
- local persistence technology;
- hardware/printer vendors and SDKs;
- push, analytics, crash reporting, MDM, signing, and Play configuration;
- implementation/Sprint authority;
- deployment, release, and production authority.

## Implementation authority boundary

This ADR publication records DEC-004 only. It does **not** authorize Android project files, Kotlin/Java/Compose source, Gradle configuration, dependencies, Android resources, API implementation, database/schema/migration, Sprint 14, deployment, release, or production publication.

Attribution: Lab | zefry
