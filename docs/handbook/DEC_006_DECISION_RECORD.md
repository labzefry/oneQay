# DEC-006 — Authentication / MFA / Session Architecture Decision Record

> **Status:** Approved — substantive Product Owner decision
> **Phase:** 0 — Governance & Discovery
> **Canonical product:** oneQay
> **Developer & Product Engineering Entity:** Lab | zefry
> **Repository:** `labzefry/oneQay`
> **Product Owner:** `labzefry`

## Decision provenance

- Decision: **DEC-006 — Authentication / MFA / Session Architecture**.
- Decision result: **APPROVED / DECISION COMPLETE**.
- Product Owner decision baseline: `c495f1b1f6e4e624bf3669ee94a786a1d7e865ce`.
- Verified decision baseline tree: `795d53f326e6ee2ee52474f79b284dea1ce744da`.
- Decision date: 2026-08-09.
- Decision authority type: **Substantive authentication / identity architecture decision only**.
- Publication-preparation authority: **SEPARATELY GATED**.
- ADR-004 repository acceptance: **SEPARATELY GATED THROUGH PUBLICATION**.
- Authentication implementation authority: **NOT GRANTED**.
- Package / dependency authority: **NOT GRANTED**.
- User/account/membership/session schema authority: **NOT GRANTED**.
- SQL / DDL / migration authority: **NOT GRANTED**.
- Sprint 14 authority: **NOT GRANTED**.
- Deployment authority: **NOT GRANTED**.
- Release authority: **NOT GRANTED**.
- Production authority: **NOT GRANTED**.
- Production readiness: **NO-GO**.

The baseline SHA and tree above are stable substantive-decision provenance. They are not a claim that those identifiers remain permanently current. GitHub must be freshly verified before every later lifecycle mutation.

## Enterprise product boundary

oneQay remains an **Enterprise Intelligent Business Management Platform**. DEC-001 first bounded MVP slice remains **POS CORE TRANSACTION & OUTLET OPERATIONS**.

DEC-006 establishes identity ownership, Web/PWA browser-session architecture, Android/API authentication boundary, rotation/revocation principles, privileged MFA direction, passkey compatibility, credential-security principles, recovery boundaries, tenant-aware identity, controlled support impersonation, and future federation direction.

DEC-006 does not authorize source implementation, package selection, physical identity schema, database migration, deployment, release, or production changes.

## D-006-01 — Canonical identity ownership

**Approved: FIRST-PARTY ONEQAY PLATFORM IDENTITY.**

oneQay owns its canonical platform identity model.

A platform identity is logically separate from:

- tenant membership;
- role/capability;
- session or token;
- active tenant context.

External OIDC/SAML identity providers are not the mandatory canonical identity source at this stage. Future federation must map external identity into controlled oneQay identity and membership boundaries rather than bypass them.

## D-006-02 — Browser authentication / session model

**Approved: SERVER-SIDE SESSION AUTHENTICATION FOR FIRST-PARTY WEB/PWA.**

Architectural requirements:

- server-authoritative session state;
- opaque browser session identifier;
- `Secure` cookie;
- `HttpOnly` cookie;
- appropriate `SameSite` policy;
- CSRF protection;
- session rotation on successful authentication;
- rotation or re-evaluation after material privilege/security changes;
- server-enforced idle expiry;
- server-enforced absolute expiry;
- logout and explicit revocation;
- privileged session/device inventory direction;
- revoke-one / revoke-all direction;
- risk-based reauthentication / step-up for sensitive operations.

Authentication/session/JWT/refresh-token secrets must not use browser `localStorage` or `sessionStorage` as the canonical design.

Exact timeout values, cookie domain, session driver, and session-storage technology remain separately gated.

## D-006-03 — Android / API authentication model

**Approved: EXPLICIT TOKEN-BASED AUTHENTICATION BOUNDARY FOR ANDROID AND API CLIENTS.**

Android and future API clients must not permanently depend on Inertia or browser-session protocol.

Architectural direction:

- short-lived access credential;
- separately revocable renewable credential where continued login is supported;
- rotation and replay resistance for renewable credentials;
- device/session association;
- server-side revocation;
- bounded audience/privilege;
- secure device storage;
- compromised-device revocation;
- explicit logout/revoke semantics.

If OAuth/OIDC authorization is later introduced for native clients, the architecture direction is standards-aligned Authorization Code + PKCE through an external user-agent/browser.

Deferred implementation details include opaque versus JWT representation, exact token formats, signing algorithms, token storage/schema, Laravel package, OAuth server implementation, API endpoints, and Android storage library.

## D-006-04 — Session/token rotation and revocation

**Approved: SERVER-AUTHORITATIVE ROTATION, REVOCATION, AND RE-EVALUATION.**

Authentication state must be capable of invalidation after material security events including, where applicable:

- logout;
- user suspension;
- membership suspension;
- privilege, role, or capability change;
- password change or reset;
- MFA factor change or recovery;
- account recovery;
- suspected compromise;
- device compromise;
- administrator/security revocation.

Client possession of an old session or token does not override server-side revocation or current authorization state.

## D-006-05 — Privileged MFA baseline

**Approved: TOTP AS THE BASELINE DEPLOYABLE MFA FOR PRIVILEGED ROLES.**

MFA is mandatory in principle for privileged actors including platform admin, tenant owner, finance-privileged role, support impersonation capability, release access, and secret access.

Sensitive operations may require step-up or recent reauthentication.

SMS and email are not canonical high-assurance privileged MFA factors. Exact TOTP implementation, library, enrollment, and lifecycle details remain separately gated.

## D-006-06 — WebAuthn / passkey direction

**Approved: WEBAUTHN / PASSKEY COMPATIBILITY AS A PREFERRED PHISHING-RESISTANT EVOLUTION DIRECTION.**

oneQay architecture must remain compatible with WebAuthn/passkeys. Passkeys or security keys may become optional stronger authentication, privileged phishing-resistant authentication/MFA, or future passwordless authentication.

They are not mandated as the sole canonical authentication baseline by DEC-006.

Exact WebAuthn library, credential policy, attestation policy, passkey synchronization policy, enrollment, and recovery remain separately gated.

## D-006-07 — Password / credential security principle

**Approved: MODERN ADAPTIVE HASHING AND EVOLVABLE REHASH POLICY.**

Architectural direction:

- supported strong adaptive password hashing;
- rehash capability as runtime/security requirements evolve;
- password-capable accounts that may authenticate without MFA use a 15-character minimum baseline;
- maximum supported password length permits long passphrases and is at least 64 characters subject to safe implementation limits;
- common and known-compromised passwords must be rejectable;
- arbitrary composition rules are not the primary strength mechanism;
- arbitrary periodic password rotation is not required absent compromise or material security reason;
- throttling and automated-attack resistance are required;
- credential transport requires TLS;
- public authentication responses must not disclose account existence, disabled status, or equivalent enumeration information.

Exact hash algorithm, exact cost/memory settings, breached-password source, and rate-limit values remain implementation/runtime gated.

Historical bounded authentication implementation may differ and must only be reconciled under later separate implementation authority.

## D-006-08 — Recovery architecture / JRN-003 boundary

**Approved: IDENTITY/MFA RECOVERY IS A HIGH-RISK SECURITY FLOW.**

Architectural direction includes:

- single-use recovery-code capability where used;
- non-reversible/hashed recovery-code storage;
- rigorous factor-replacement flow;
- stronger verification for privileged-account recovery;
- support-assisted recovery must not be an informal bypass;
- recovery creates security audit evidence;
- material recovery triggers session/token revocation or mandatory re-evaluation;
- user/security notification for material factor/recovery changes where appropriate;
- cooling period or additional approval may be used for high-value accounts;
- recovery must not create tenant or platform authority not already authorized.

**JRN-003 remains UNRESOLVED.**

DEC-006 does not finalize all JRN-003 journey or recovery semantics. JRN-003 requires separate Product Owner resolution.

## D-006-09 — Tenant-aware identity / membership boundary

**Approved: GLOBAL PLATFORM IDENTITY + SEPARATE TENANT MEMBERSHIPS.**

One immutable oneQay identity may have zero, one, or multiple tenant memberships.

Authentication proves identity. Tenant membership determines which tenant contexts that identity may enter. Authorization determines what actions are permitted inside that context.

Server-side logic must verify selected tenant membership and context.

The following alone are not authorization:

- client-supplied tenant ID;
- hostname/subdomain;
- role label;
- possession of a session/token;
- client-side state.

Tenant switching must re-evaluate membership and authorization context. DEC-005 server-authoritative tenant isolation remains binding.

## D-006-10 — Support impersonation / break-glass

**Approved: CONTROLLED, PRIVILEGED, AUDITED SUPPORT IMPERSONATION ONLY.**

Required architectural guardrails:

- explicit reason;
- appropriately authorized support capability;
- privileged MFA / step-up authentication;
- approval policy based on risk;
- short bounded duration;
- visible impersonation indication;
- immutable original-actor + impersonated-actor audit;
- restricted capabilities;
- no silent privilege elevation;
- capability to prohibit impersonation of selected high-risk roles;
- tenant notification where policy/risk requires.

Break-glass access is a distinct exceptional control and must not be implemented as ordinary support impersonation.

Exact approval workflow and operational policy remain separately gated.

## D-006-11 — Future enterprise federation

**Approved: ENTERPRISE FEDERATION IS A FUTURE-COMPATIBLE EXTENSION, NOT A REQUIRED CURRENT IDENTITY DEPENDENCY.**

Direction:

- OIDC is the preferred modern federation direction;
- SAML may be supported where enterprise/customer requirements justify it;
- SCIM/directory lifecycle may be considered separately in future;
- external federation maps into oneQay identity, tenant-membership, and authorization boundaries;
- federation must not bypass tenant isolation or deny-by-default authorization.

No external identity provider is selected by DEC-006.

## D-006-12 — ADR-004 disposition

**Approved: MATERIALLY REVISE ADR-004 THEN PUBLISH.**

ADR-004 must preserve historical Technical Preview / Issue #23 / A1 provenance while representing current substantive DEC-006 architecture after governed publication.

The reconciliation covers first-party identity ownership, Web/PWA server-side sessions, Android/API authentication, rotation/revocation, privileged MFA, passkey evolution, password security, recovery/JRN-003, tenant-aware membership, impersonation/break-glass, and future federation.

## Alternatives considered

### A1 — First-party identity + server-side browser session only

Retains a strong browser-session basis and historical Technical Preview provenance, but is incomplete for dedicated Android/API authentication and future federation.

### A2 — External OIDC / IdP-first

Offers strong federation capability but introduces premature provider, outage, callback, operational, and contractual dependencies for the bounded first delivery stage.

### A3 — Token/JWT-centric authentication for every client

Provides apparent protocol uniformity but adds unnecessary browser token-storage, revocation, and refresh lifecycle complexity for same-origin first-party Web/PWA.

### A4 — Hybrid architecture

**Selected direction.**

Use first-party server-side session authentication for same-origin first-party Web/PWA, an explicit token authentication boundary for Android/API, and future separately bounded federation.

## Security architecture principles

- unique identity;
- shared accounts prohibited;
- authentication separate from tenant membership and authorization;
- authorization deny-by-default;
- server-authoritative session/token revocation;
- MFA for privileged roles;
- sensitive recovery treated as high-risk;
- tenant membership verified server-side;
- authentication and recovery secrets excluded from logs;
- TLS for credential transport;
- auditability for privileged, recovery, impersonation, and security events;
- threat modeling for critical authentication flows.

`SECURITY.md` remains the binding security-governance baseline.

## Published decision boundaries preserved

DEC-001 retains ownership of bounded MVP scope.

DEC-002 retains PHP, Laravel, Modular Monolith First, Clean Architecture, and framework-independent Domain/Application boundaries.

DEC-003 retains Web/PWA frontend architecture.

DEC-004 retains Android delivery approach.

DEC-005 retains database engine, physical tenancy, and server-authoritative tenant-isolation direction.

DEC-007 retains payment-provider and payment-compliance ownership.

DEC-008 exclusively retains offline POS transaction, synchronization, replay, conflict-resolution, and reconciliation semantics.

DEC-009 retains Stage 1 runtime/hosting requirements.

DEC-010 retains product licensing and third-party notice policy.

DEC-011 retains data retention, privacy, and jurisdiction.

DEC-012 retains final RPO/RTO/support objectives.

## Existing Authentication Foundation

Published bounded Authentication Foundation source through earlier Technical Preview work remains repository history.

DEC-006 does not retroactively make that source the final architecture and does not grant authority to modify it. Any implementation mismatch—including password-length policy, MFA, token, recovery, membership, or federation behavior—requires a later separate implementation lifecycle.

## Explicit deferments

DEC-006 does not choose or implement:

- exact Laravel authentication/API package;
- exact session driver/store;
- exact timeout values or cookie domain;
- exact password hashing algorithm/work factor;
- exact TOTP or WebAuthn library;
- exact OAuth/OIDC server/client package;
- opaque versus JWT token representation;
- exact token signing algorithm/key implementation;
- refresh-token schema;
- user/account/membership/session physical schema;
- SQL/DDL/migrations/seeders;
- API endpoints/controllers/middleware;
- login/MFA/recovery UI;
- email/SMS provider;
- exact enterprise IdP;
- SAML/OIDC provider configuration;
- production secrets;
- deployment;
- release;
- production.

## Authority boundary

This decision record represents substantive DEC-006 architecture only.

It does **not** grant source implementation, package/dependency changes, schema/SQL/migration, Authentication Foundation modification, JRN-003 resolution, Sprint 14, deployment, release, production, or production-readiness authority.

Attribution: Lab | zefry
