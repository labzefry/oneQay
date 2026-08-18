# First-Party Login / Session Establishment Foundation

## Status

This document describes the bounded Sprint 27 implementation authorized by the published Sprint 27 entry gate.

Attribution: **Lab | zefry**

## Purpose

Sprint 27 connects the already-published Sprint 26 first-party credential verifier to a narrowly bounded first-party Laravel session-establishment flow for Local/Test/CI qualification only.

Sprint 27 does not broaden credential lifecycle, authorization, Production, Technical Preview, updater, release, deployment, or platform authority.

## Authorized HTTP surface

Sprint 27 adds exactly two first-party routes:

- `POST /auth/login` — `auth.first-party.login`
- `POST /auth/logout` — `auth.first-party.logout`

Both remain on Laravel's normal `web` middleware stack. CSRF remains mandatory. Login is additionally bounded by existing Laravel throttle middleware.

The routes are registered only for runtime classes `local`, `test`, or `ci`. The controller independently repeats the same runtime allowlist and fails closed with HTTP 404 outside it.

Technical Preview and Production receive no Sprint 27 route authority.

## Closed login payload

Ignoring Laravel `_token` as framework CSRF transport metadata, the only accepted business fields are:

- `tenant_id`
- `identity_id`
- `password`
- `organization_id`
- optional `outlet_id`
- optional `device_id`

Unknown fields fail closed. A `device_id` without a valid outlet relationship is rejected by the existing organizational verification boundary.

The password is never trimmed, normalized, lowercased, logged, echoed, returned, or stored in session.

## Credential verification

`FirstPartySessionController` delegates password verification to the published Sprint 26 `VerifyFirstPartyIdentityCredential` Application service.

The controller does not call `password_verify()` or `password_hash()` and does not query the credential table directly.

The authoritative credential identity remains exact `(tenant_id, identity_id)` ownership.

All credential failures remain generic through the Sprint 26 boolean verifier contract.

## Durable context verification before session write

Credential success alone is insufficient to establish authenticated session authority.

Before session state is written, Sprint 27 reuses the existing durable context path:

- `ServerVerifiedPlatformIdentity`
- `ServerVerifiedTenantContext`
- `TenantContextStore`
- `EnterOrganizationalContext`
- `TenantMembershipVerifier`
- `OrganizationalRelationshipVerifier`
- `OrganizationalContextStore`

The requested organization, optional outlet, and optional device must be durably valid for the exact authenticated tenant identity.

A correct password with invalid membership or organizational context is denied using the same generic authentication failure envelope and creates no authenticated session authority.

Request-scoped tenant and organizational contexts are cleared after every login/logout request.

## Exact first-party session authority

The shared `FirstPartySessionKeys` definition contains only:

- `oneqay.auth.identity_id`
- `oneqay.auth.tenant_id`
- `oneqay.auth.organization_id`
- `oneqay.auth.outlet_id`
- `oneqay.auth.device_id`

`RequirePolicyAdministrationSessionContextMiddleware` keeps its published public constants as aliases to this shared vocabulary so Sprint 25 compatibility remains intact.

On successful login, session values are written only from the verified canonical identity and organizational context objects.

No password, password hash, role, permission, protected-control claim, updater claim, token, arbitrary request field, or raw session identifier becomes authentication authority.

## Session fixation and CSRF rotation

Successful login invalidates the previous Laravel session before authenticated facts are committed and regenerates the CSRF token.

The disposable regression proves the pre-login anonymous session cookie is not reused as the authenticated cookie and that the CSRF token changes during the transition.

Logout invalidates the current session and regenerates the CSRF token. The disposable regression proves the previous authenticated state is no longer authoritative afterward.

No remember-me mechanism or client-supplied session identifier is introduced.

## Generic failure boundary

Business-level login failures return one generic `AUTHENTICATION_FAILED` envelope with HTTP 401.

The same outcome covers, among other cases:

- wrong password
- absent identity
- missing credential
- wrong tenant
- invalid tenant/identity identifier
- invalid organization/outlet/device relationship
- missing durable membership
- unknown login business field
- persistence unavailable

Framework-level CSRF rejection remains HTTP 419. Framework throttle rejection may remain HTTP 429.

The response does not expose password/hash material or distinguish credential enumeration facts.

## Sprint 25 compatibility

Sprint 27 does not grant policy administration authority.

The existing Sprint 25 policy-administration middleware consumes Sprint 27 session state and independently performs its existing durable tenant/organizational verification.

The disposable regression proves:

- an authenticated tenant administrator with existing policy authority can reach the Sprint 25 policy route through the real Sprint 27 session;
- a correctly authenticated identity without policy authority remains denied by the existing authorization boundary;
- logout removes the authenticated session authority required by Sprint 25 delivery.

## Tenant isolation

The disposable regression includes the same textual identity ID under two different tenants with independent password credentials.

Each tenant authenticates only with its own credential and organizational context. A password valid in one tenant cannot authenticate that textual identity in another tenant.

## No credential mutation

Sprint 27 performs no credential insert, update, replace, rotate, revoke, delete, reset, or enrollment behavior.

Synthetic `password_hash()` calls exist only in the disposable regression to create isolated SQLite fixtures.

The regression verifies the stored credential hash remains unchanged after login and logout.

## No schema or dependency change

Sprint 27 adds zero migrations.

Canonical migrations remain exactly #1–#7 and are immutable during Sprint 27.

No migration #8 is authorized.

Sprint 27 changes no Composer or npm manifest or lockfile and adds no authentication, token, OAuth, MFA, or session package.

`ONEQAY_PERSISTENCE_ENABLED=false` remains the repository default.

## Technical Preview boundary

Technical Preview remains **NO_SCHEMA_CHANGE**.

The existing synthetic Technical Preview `/technical-preview/sign-in` interaction remains separate from Sprint 27 and is not backed by Sprint 26 credentials.

Sprint 27 login/session classes are not wired into Preview Application or Delivery source.

The governed deterministic Technical Preview release artifact and its manifest/checksum/no-schema-change proofs remain required.

## Production boundary

Production remains **NO-GO / NOT AUTHORIZED**.

Sprint 27 does not authorize Production login, Production credential verification, Production session establishment, Production database activation, deployment, or readiness inference.

## Updater and protected-control boundary

Updater remains **DISABLED / UNWIRED**.

Authentication success does not grant:

- `authorization.policy.manage`
- Sprint 23 bootstrap authority
- Sprint 24 protected-control delegation/revocation authority
- emergency recovery authority
- platform-superadmin authority
- updater, release, deployment, rollback, host, or infrastructure authority

Those remain separately governed concerns.

## Qualification

The disposable Sprint 27 regression uses synthetic data and a temporary SQLite database. It exercises the actual Laravel HTTP kernel, web session middleware, CSRF middleware, Sprint 26 credential verifier, durable tenant/organization verification, Sprint 25 policy-administration middleware, and real session cookies.

Qualification covers:

- exact migration set #1–#7
- missing-login-CSRF rejection
- successful credential-backed login
- session identifier rotation
- CSRF-token rotation
- exact five-key session vocabulary
- generic wrong-password/absent-identity/missing-credential/cross-tenant/context failures
- persistence-disabled denial
- Preview/Production controller denial
- tenant-independent same-text identity credentials
- optional verified outlet/device session facts
- Sprint 25 compatibility
- authenticated-without-policy-authority denial
- logout CSRF protection
- logout invalidation and token rotation
- post-logout authorization denial
- stored credential immutability
- request-context cleanup

## Excluded future concerns

Sprint 27 does not resolve or authorize:

- registration
- password enrollment/change/reset/recovery
- remember-me
- API/bearer tokens
- MFA/TOTP/recovery codes
- passkeys/WebAuthn
- OAuth/OIDC/SAML/social login
- email verification
- Production activation
- emergency protected-control recovery

Each remains subject to a separately published governance decision.

GitHub remains the Single Source of Truth.

Attribution: **Lab | zefry**
