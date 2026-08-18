# oneQay — Post-Sprint 27 Canonical Program State

## Purpose and authority

This document is the canonical current-state supersession record after Sprint 27.

Older repository documents remain valid historical provenance, but they are not current authority where they conflict with this record or newer GitHub publication evidence.

GitHub remains the Single Source of Truth.

Attribution: **Lab | zefry**

## Verified repository baseline

Fresh post-merge verification established:

- repository: `labzefry/oneQay`;
- canonical branch: `main`;
- Sprint 27 source publication commit: `8ee9146d93cbef560e63ceff16afff5413d8e94b`;
- verified Sprint 27 source publication tree: `e919aa6a8ac1b9737009133d0e0f1a2148fcb57b`;
- parent: `af9957b3de6b2db5d481322fbdf8778139c7ac63`;
- parent is the published Sprint 27 entry gate from PR #184;
- commit: `feat(sprint27): add governed first-party login session foundation (#185)`;
- GitHub signature: **VERIFIED / VALID**;
- PR #185: **CLOSED / MERGED**;
- final Sprint 27 source head: `dfe710142d0b2953fe463bcfdb4eeeea81ec9a35`;
- Sprint 27 source envelope: exactly **17 changed files**;
- final source relation before merge: **ahead 17 / behind 0**.

These values are publication provenance for this reconciliation baseline. Future lifecycle mutations still require fresh GitHub verification.

## Sprint 27 publication proof

The exact final Sprint 27 source head passed all eighteen pull-request-triggered workflows:

1. Governance Required Checks — **SUCCESS**;
2. PHP Foundation Regression — **SUCCESS**;
3. M7.1 Application Regression — **SUCCESS**;
4. M7.2 Tenant Isolation Regression — **SUCCESS**;
5. M7.3 Identity Organizational Context Regression — **SUCCESS**;
6. M7.4A Technical Preview Interaction Regression — **SUCCESS**;
7. M7.5 Preview Database Qualification Regression — **SUCCESS**;
8. M7.5 Technical Preview Release Artifact — **SUCCESS**;
9. Sprint 21 Role Permission Policy Regression — **SUCCESS**;
10. Sprint 22 Policy Administration Regression — **SUCCESS**;
11. Sprint 23 Initial Tenant Administrator Provisioning Regression — **SUCCESS**;
12. Sprint 24 Protected Control Administrator Lifecycle Regression — **SUCCESS**;
13. Sprint 25 Policy Administration Delivery Regression — **SUCCESS**;
14. Sprint 26 Identity Credential Verification Regression — **SUCCESS**;
15. Sprint 27 First-Party Session Establishment Regression — **SUCCESS**;
16. Privileged Update Security Regression — **SUCCESS**;
17. Backend Updater Control Plane Regression — **SUCCESS**;
18. Read-Only Update Deployment UI Regression — **SUCCESS**.

The dedicated Sprint 27 workflow proved the exact 17-file source envelope, zero migration changes, exact canonical migration set #1–#7, zero dependency changes, exact first-party session key vocabulary, bounded credential-to-session composition, exact two-route authority, CSRF preservation, login throttling, runtime-scoped route registration, Sprint 26 verifier reuse, credential immutability, Technical Preview separation, Production denial, updater separation, the disposable Sprint 27 HTTP/session regression, preservation of Sprint 21 through Sprint 26, and tracked-source cleanliness.

Exact-head `product-owner-merge-authority` was **SUCCESS** for `dfe710142d0b2953fe463bcfdb4eeeea81ec9a35`.

The valid authorization comment was authored by repository owner `labzefry` and bound to PR #185 and that exact head.

PR #185 was squash-merged using expected-head protection. No independent review was additionally required under the current Product Owner continuation model.

## Sprint 27 canonical state

**Sprint 27 — Governed Interactive First-Party Login / Session Establishment Foundation** is now:

**COMPLETE / IMPLEMENTED / PUBLISHED**.

Sprint 27 establishes a bounded password-backed first-party Laravel session for Local/Test/CI qualification by composing:

- the published Sprint 26 tenant-bound credential verifier;
- existing durable tenant membership verification;
- existing durable organizational relationship verification;
- existing server-owned session transport;
- existing Sprint 25 session-consumer boundary.

Sprint 27 does not create a general authentication platform or Production login authority.

## Published first-party HTTP surface

Sprint 27 adds exactly two first-party routes:

- `POST /auth/login` with route name `auth.first-party.login`;
- `POST /auth/logout` with route name `auth.first-party.logout`.

They are registered only for runtime classes:

- Local;
- Test;
- CI.

The controller repeats the runtime check as defense in depth and fails closed outside the allowlist.

The dedicated workflow separately proved that Sprint 27 auth routes are present in CI runtime and absent from Preview and Production route registration.

No GET login page, registration page, public authentication API, external identity callback, or background authentication surface is published.

## CSRF and throttle preservation

Both first-party routes remain in Laravel's normal `web` middleware stack.

CSRF protection is not excluded or replaced.

The login route is bounded by existing Laravel throttle middleware without a dependency change.

The disposable regression proved missing CSRF is rejected before login and logout processing.

Framework CSRF rejection remains distinct from business authentication failure.

## Closed login request vocabulary

Ignoring Laravel `_token` as framework transport metadata, Sprint 27 accepts only:

- `tenant_id`;
- `identity_id`;
- `password`;
- `organization_id`;
- optional `outlet_id`;
- optional `device_id`.

Unknown business fields fail closed.

Request data cannot provide roles, permissions, protected-control claims, updater authority, platform authority, session key names, session IDs, runtime overrides, persistence overrides, repository names, table names, SQL, or arbitrary command dispatch.

## Password handling

The password supplied to login is delegated to the published Sprint 26 `VerifyFirstPartyIdentityCredential` service.

Sprint 27 production application source does not duplicate `password_verify()` and does not call `password_hash()`.

The password is not:

- trimmed;
- lowercased;
- normalized;
- logged;
- returned;
- stored in session.

Synthetic `password_hash()` calls exist only inside the disposable Sprint 27 regression to construct isolated SQLite fixtures.

The regression proved stored credential hashes remain unchanged after login and logout.

## Tenant-bound credential ownership

First-party credential ownership remains exact:

`(tenant_id, identity_id)`

Sprint 27 preserves Sprint 26 tenant isolation.

The disposable regression contains the same textual identity ID in two separate tenants with independent password credentials and proves that each tenant authenticates only with its own credential.

A password valid for a textual identity in one tenant cannot authenticate that identity in another tenant.

## Durable organizational verification before session write

A valid password is necessary but not sufficient for session establishment.

Before authenticated session authority is written, Sprint 27 reuses the existing durable path represented by:

- `ServerVerifiedPlatformIdentity`;
- `ServerVerifiedTenantContext`;
- `TenantContextStore`;
- `EnterOrganizationalContext`;
- `TenantMembershipVerifier`;
- `OrganizationalRelationshipVerifier`;
- `OrganizationalContextStore`.

The requested organization and optional outlet/device must be durably valid for the exact authenticated tenant identity.

A correct credential combined with an invalid or foreign organizational context is denied generically and writes no authenticated session state.

Request-scoped tenant and organizational contexts are cleared after processing.

## Exact server-owned session authority

Sprint 27 introduces the shared `FirstPartySessionKeys` definition for exactly five existing server-owned facts:

- `oneqay.auth.identity_id`;
- `oneqay.auth.tenant_id`;
- `oneqay.auth.organization_id`;
- `oneqay.auth.outlet_id`;
- `oneqay.auth.device_id`.

The Sprint 25 `RequirePolicyAdministrationSessionContextMiddleware` retains its published public constants as aliases to this shared key definition.

Successful login writes session values only from verified canonical value objects.

Optional outlet/device values are written only after corresponding durable verification.

No password, password hash, role, permission, protected-control claim, updater claim, bearer token, remember-me token, arbitrary request value, or raw session identifier becomes authenticated authority.

## Session fixation and CSRF rotation

On successful login, Sprint 27 invalidates the previous Laravel session before authenticated session state becomes authoritative and regenerates the CSRF token.

The disposable HTTP/session regression proved:

- an anonymous pre-login session cookie exists;
- successful login does not reuse that cookie as the authenticated session identifier;
- the CSRF token rotates on successful login.

On successful logout, Sprint 27:

- invalidates the current session;
- rotates the session transport;
- regenerates the CSRF token;
- removes the five authenticated session facts from authority.

The regression proved the old authenticated state cannot continue to authorize Sprint 25 policy delivery after logout.

No remember-me mechanism or client-supplied session ID is introduced.

## Generic authentication failure boundary

All business-level first-party login failures collapse to one HTTP 401 `AUTHENTICATION_FAILED` envelope.

The disposable regression proved generic handling for cases including:

- wrong password;
- absent identity;
- missing credential;
- cross-tenant credential reuse;
- foreign organizational context;
- unknown login business fields;
- device context without the required valid outlet relationship;
- persistence disabled.

Those failures create no authenticated session authority.

The failure envelope does not disclose submitted credential material or distinguish account/credential enumeration facts.

Framework-level CSRF rejection remains HTTP 419 and rate limiting may remain HTTP 429.

## Sprint 25 compatibility and independent authorization

Sprint 27 establishes authentication/session context only.

It does not grant policy administration authority.

The disposable regression proved a valid Sprint 27 session can reach the existing Sprint 25 policy-administration route only through Sprint 25's own durable context and authorization boundary.

An authenticated identity that lacks existing policy authority remains denied by Sprint 25/Sprint 21/Sprint 22 authorization logic.

Authentication therefore does not collapse identity establishment and authorization into one trust decision.

## Protected-control preservation

Sprint 27 login success does not grant or infer:

- `authorization.policy.manage`;
- initial tenant administrator bootstrap authority;
- protected-control delegation/revocation authority;
- emergency protected-control recovery authority;
- platform-superadmin authority.

Sprint 23 and Sprint 24 remain separate governed control lifecycles.

## Canonical migration set

The canonical repository contains exactly seven forward-only migrations:

1. `0000_00_00_000001_create_foundational_context_graph.php`;
2. `0000_00_00_000002_create_organizational_access_grants.php`;
3. `0000_00_00_000003_create_scoped_role_permission_policy.php`;
4. `0000_00_00_000004_create_policy_mutation_journal.php`;
5. `0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php`;
6. `0000_00_00_000006_create_protected_control_administrator_mutation_journal.php`;
7. `0000_00_00_000007_create_identity_password_credentials.php`.

Sprint 27 modified **zero migration files** and added **zero migrations**.

Migration #8 does not exist and is not authorized by Sprint 27.

## Dependency and persistence boundary

Sprint 27 changed zero Composer/npm dependency manifests or lockfiles.

No authentication, OAuth, token, MFA, passkey, or external identity package was added.

`ONEQAY_PERSISTENCE_ENABLED=false` remains the repository default.

Persistence-backed login succeeds only when persistence is explicitly enabled in the allowed Local/Test/CI runtime boundary.

## Technical Preview preservation

Technical Preview remains:

**NO_SCHEMA_CHANGE**.

The synthetic Technical Preview `/technical-preview/sign-in` interaction remains separate from Sprint 27 first-party credential authentication.

Sprint 27 credential/session classes are not wired into Technical Preview Application or Delivery source.

M7.4A and M7.5 Preview database qualification passed on the final Sprint 27 head.

The deterministic Technical Preview Release Artifact also passed its manifest binding, checksum, public/private boundary, no-schema-change verification, deterministic archive reproduction, and tracked-source checks.

## Production preservation

Production remains:

**NO-GO / NOT AUTHORIZED**.

Sprint 27 does not authorize:

- Production login;
- Production credential verification;
- Production authenticated session establishment;
- Production database activation;
- deployment;
- release activation;
- Production readiness inference.

Both route registration and controller runtime enforcement fail closed outside Local/Test/CI.

## Updater preservation

Updater remains:

**DISABLED / UNWIRED**.

Authentication/session authority grants no update, release, deployment, rollback, host, infrastructure, or platform authority.

Privileged Update Security Regression, Backend Updater Control Plane Regression, and Read-Only Update Deployment UI Regression all passed on the final Sprint 27 head.

## Closure audit

Fresh bounded closure verification after PR #185 established:

- PR #185 is merged;
- source publication envelope is exactly 17 files;
- final source head passed 18/18 triggered workflows;
- final source relation was ahead 17 / behind 0;
- exact-head Product Owner merge authority was successful;
- canonical `main` is the Sprint 27 source publication commit recorded above;
- GitHub signature is verified/valid;
- canonical migration directory remains exactly #1–#7;
- zero open Sprint 27 pull requests remain;
- zero open Sprint 27 issues remain;
- canonical code search returned zero `TODO` findings;
- canonical code search returned zero `FIXME` findings;
- canonical code search returned zero `bypass` findings;
- Technical Preview remains `NO_SCHEMA_CHANGE`;
- Production remains `NO-GO / NOT AUTHORIZED`;
- updater remains disabled/unwired.

## Remaining first-party credential lifecycle gap

Sprint 27 can authenticate an identity only when a valid Sprint 26 password credential already exists.

Canonical application source still does not provide an authorized workflow to:

- provision an initial password credential for an existing tenant identity;
- rotate/change an existing password;
- recover/reset a lost password;
- revoke password authentication;
- enroll MFA or recovery factors.

The Sprint 27 disposable regression creates password hashes only as synthetic test fixtures. That is qualification evidence, not a credential-provisioning product flow.

Therefore a real first-party user credential cannot yet be created or managed through an authorized application lifecycle.

## Next bounded engineering concern

The next logical concern is a separately governed **first-party credential provisioning / initial password enrollment foundation** for existing, already-authorized tenant identities.

A future entry gate should determine, before source implementation:

- who is allowed to provision the first credential;
- whether self-enrollment is permitted at all;
- whether tenant protected-control authority is required;
- how initial-secret delivery is handled without plaintext persistence;
- password hashing and minimum input-policy boundaries;
- replay/idempotency semantics;
- durable audit/journal requirements;
- whether an additive forward-only migration is required for lifecycle evidence;
- separation from password reset/recovery and MFA;
- Local/Test/CI qualification before any Production consideration.

This post-Sprint27 reconciliation does **not** authorize that source work and does not authorize migration #8.

Password change/reset/recovery, MFA/TOTP, remember-me, API/bearer tokens, OAuth/OIDC/SAML, passkeys/WebAuthn, email verification, Production activation, and emergency protected-control recovery remain unresolved and separately governed.

## Canonical declaration

As of this reconciliation:

- Sprint 21 remains **COMPLETE / IMPLEMENTED / PUBLISHED**;
- Sprint 22 remains **COMPLETE / IMPLEMENTED / PUBLISHED**;
- Sprint 23 remains **COMPLETE / IMPLEMENTED / PUBLISHED**;
- Sprint 24 remains **COMPLETE / IMPLEMENTED / PUBLISHED**;
- Sprint 25 remains **COMPLETE / IMPLEMENTED / PUBLISHED**;
- Sprint 26 remains **COMPLETE / IMPLEMENTED / PUBLISHED**;
- Sprint 27 is **COMPLETE / IMPLEMENTED / PUBLISHED**;
- canonical migrations remain exactly #1–#7;
- first-party password credential verification exists for Local/Test/CI only;
- first-party login/session establishment exists for Local/Test/CI only;
- server-owned authenticated session authority is limited to the exact five `oneqay.auth.*` facts;
- credential provisioning/enrollment remains unresolved and unauthorized;
- password lifecycle/recovery remains unresolved and unauthorized;
- Technical Preview remains `NO_SCHEMA_CHANGE`;
- Production remains `NO-GO / NOT AUTHORIZED`;
- updater remains `DISABLED / UNWIRED`;
- emergency protected-control recovery remains unresolved and unauthorized;
- any next source work requires a separately published entry gate against the then-current canonical `main`.

GitHub remains the Single Source of Truth.

Attribution: **Lab | zefry**
