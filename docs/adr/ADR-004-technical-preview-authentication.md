# ADR-004: Authentication, MFA, Session, and Client Credential Architecture

- Status: Accepted — representation of substantive DEC-006 after publication
- Date: 2026-08-03
- Reconciled decision date: 2026-08-09
- Decision owner: Product Owner oneQay
- Historical evidence: Issue #23
- Substantive decision: DEC-006
- Decision baseline: `c495f1b1f6e4e624bf3669ee94a786a1d7e865ce`
- Decision baseline tree: `795d53f326e6ee2ee52474f79b284dea1ce744da`
- Publication note: this Accepted representation becomes repository-canonical only after the governed DEC-006 publication PR is successfully merged.

## Historical Technical Preview provenance

The original Technical Preview v0.0.1 candidate recorded A1: first-party server-side session authentication with secure browser cookies, CSRF protection, supported-runtime password hashing, session rotation, login throttling, TOTP MFA for privileged roles, hashed single-use recovery codes, idle/absolute expiry, and audited recovery.

That A1 selection remains historical provenance. It was a Proposed Technical Preview input, not final authority for Android/API authentication, enterprise federation, final recovery semantics, or source implementation.

## Context

oneQay requires revocable first-party authentication for Web/PWA while also supporting a dedicated Android channel and future APIs without making Inertia/browser-session mechanics a permanent mobile protocol.

The platform must keep authentication, tenant membership, active tenant context, and authorization distinct. It must also preserve server-authoritative tenant isolation from DEC-005 and future compatibility with stronger phishing-resistant authentication and enterprise federation.

## Decision

Adopt the bounded hybrid authentication architecture approved by substantive DEC-006.

### Identity ownership

oneQay owns a canonical first-party platform identity model.

Identity is logically separate from tenant membership, role/capability, session/token, and active tenant context. One immutable identity may hold zero, one, or multiple tenant memberships.

External IdPs are future federation inputs and do not bypass oneQay identity, membership, or authorization boundaries.

### First-party Web/PWA

Use server-side session authentication for first-party Web/PWA with:

- server-authoritative session state;
- opaque browser session identifier;
- `Secure`, `HttpOnly`, and appropriate `SameSite` cookie policy;
- CSRF protection;
- rotation after successful authentication;
- rotation or re-evaluation after material privilege/security change;
- server-enforced idle and absolute expiry;
- explicit logout/revocation;
- privileged session/device inventory direction;
- revoke-one / revoke-all direction;
- risk-based reauthentication or step-up for sensitive actions.

Browser `localStorage` or `sessionStorage` is not the canonical location for authentication/session/JWT/refresh-token secrets.

### Android / API clients

Android and future API clients use an explicit token authentication boundary independent of Inertia/browser-session protocol.

Architectural properties:

- short-lived access credential;
- revocable renewable credential where continued login is supported;
- rotation/replay resistance for renewable credentials;
- device/session association;
- server-side revocation;
- bounded audience/privilege;
- secure device storage;
- compromised-device revocation;
- explicit logout/revoke semantics.

If OAuth/OIDC authorization is later introduced for native clients, use standards-aligned Authorization Code + PKCE through an external user-agent/browser.

Opaque versus JWT representation, token formats, signing algorithms, persistence schema, package selection, and endpoint implementation remain deferred.

### Rotation, revocation, and re-evaluation

Authentication state must be invalidatable after logout, user/membership suspension, privilege changes, password changes/reset, MFA-factor changes/recovery, account recovery, compromise, device compromise, or explicit administrator/security revocation.

Possession of stale client credentials does not override server revocation or current authorization.

### Privileged MFA

TOTP is the baseline deployable MFA for privileged roles.

MFA is mandatory in principle for platform admin, tenant owner, finance-privileged role, support impersonation capability, release access, and secret access.

Sensitive actions may require step-up or recent reauthentication.

SMS and email are not canonical high-assurance privileged MFA factors.

### WebAuthn / passkeys

Architecture remains compatible with WebAuthn/passkeys as the preferred phishing-resistant evolution direction.

Passkeys/security keys may become stronger optional authentication, privileged phishing-resistant authentication/MFA, or future passwordless authentication. They are not the sole mandatory baseline under DEC-006.

### Password / credential security

Use strong adaptive hashing with an evolvable rehash policy.

Password-capable accounts that may authenticate without MFA use a 15-character minimum baseline. Maximum supported length permits long passphrases and is at least 64 characters subject to safe implementation limits.

Common/known-compromised passwords must be rejectable. Arbitrary composition rules and arbitrary periodic rotation are not primary security mechanisms. Authentication requires throttling/automated-attack resistance, TLS, and public responses that do not expose account existence or disabled state.

Exact hashing algorithm, work factors, breached-password source, and throttling values remain implementation/runtime gated.

### Recovery and JRN-003

Identity/MFA recovery is a high-risk flow.

Where recovery codes are used they are single-use and stored non-reversibly/hashed. Privileged recovery receives stronger verification. Support-assisted recovery cannot be an informal bypass. Material recovery creates audit evidence, security notification where appropriate, and session/token revocation or mandatory re-evaluation.

**JRN-003 remains UNRESOLVED.**

DEC-006 and this ADR do not finalize all JRN-003 journey or recovery semantics.

### Tenant-aware identity and authorization

Authentication proves identity.

Tenant membership determines which tenant contexts that identity may enter.

Authorization determines what actions may occur inside the verified context.

Client tenant ID, hostname/subdomain, role label, possession of session/token, or client-side state alone are not authorization.

Tenant switching must re-evaluate membership and authorization. DEC-005 server-authoritative tenant isolation remains binding.

### Support impersonation and break-glass

Support impersonation may exist only as a separately privileged, controlled, audited capability with explicit reason, authorized support capability, privileged MFA/step-up, risk-based approval, short duration, visible indication, immutable actor/impersonated-actor audit, restricted capabilities, and high-risk role exclusions.

Break-glass access is a separate exceptional control and must not be implemented as ordinary support impersonation.

### Enterprise federation

Enterprise federation is a future-compatible extension, not a required current identity dependency.

OIDC is the preferred modern federation direction. SAML may be supported when enterprise/customer requirements justify it. SCIM/directory lifecycle remains a future separately bounded consideration.

No external identity provider is selected by DEC-006.

## Security and architecture guardrails

- Unique identity; shared account prohibited.
- Authorization remains deny-by-default.
- Tenant membership is verified server-side.
- Client identity/session/token possession is never sufficient authorization by itself.
- Authentication, session/token, membership, and authorization remain separate concepts.
- Authentication/recovery secrets must not enter public logs or issue content.
- Privileged, recovery, impersonation, and security events must be auditable.
- `SECURITY.md` remains binding security governance.
- DEC-008 exclusively owns offline POS transaction/sync/replay/conflict/reconciliation semantics.
- DEC-009 owns Stage 1 runtime/hosting.
- DEC-011 owns retention/privacy/jurisdiction.
- DEC-012 owns final RPO/RTO/support objectives.

## Alternatives considered

### A1 — First-party browser session only

Preserved as historical Technical Preview provenance. Strong fit for same-origin Web/PWA but incomplete for Android/API and future federation.

### A2 — External IdP / OIDC-first

Federation-capable but introduces premature provider, outage, callback, operational, and contractual dependencies.

### A3 — Token/JWT-centric for every client

Creates unnecessary browser token-storage and revocation/refresh complexity for same-origin first-party Web/PWA.

### A4 — Hybrid

Selected by substantive DEC-006: server-side Web/PWA sessions + explicit Android/API token boundary + future separately bounded federation.

## Consequences

The architecture uses channel-appropriate authentication mechanisms while retaining one server-authoritative identity/membership/authorization model.

Historical bounded Authentication Foundation source remains a repository fact but is not modified or promoted to final implementation by this ADR. Any mismatch with DEC-006 requires later separate implementation authority.

Exact packages, stores, schemas, endpoints, token representation, MFA/passkey libraries, recovery implementation, provider configuration, and deployment remain deferred.

## Acceptance and lifecycle boundary

This ADR represents substantive DEC-006 after governed publication.

It does not authorize:

- PHP/Laravel authentication implementation;
- Authentication Foundation modification;
- package/dependency installation;
- user/account/membership/session schema;
- SQL/DDL/migrations;
- MFA, token, recovery, impersonation, or federation implementation;
- Android authentication implementation;
- JRN-003 resolution;
- Sprint 14;
- deployment;
- release;
- production.

Attribution: Lab | zefry
