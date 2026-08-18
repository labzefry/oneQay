# Sprint 27 Entry Gate — Governed Interactive First-Party Login / Session Establishment Foundation

## Identity and authority

- Product: `oneQay`
- Engineering entity: **Lab | zefry**
- Repository: `labzefry/oneQay`
- Canonical branch: `main`
- Exact base: `c5796086ba9b664d39bd8439692391befa713c13`
- Exact base tree: `7cf1c4dfa0d05a5308a7bc49dd84272c67b5b87e`
- Sprint 26: **COMPLETE / IMPLEMENTED / PUBLISHED**
- Post-Sprint 26 canonical reconciliation: **PUBLISHED**
- Canonical migrations: exactly **#1–#7**
- Technical Preview: **NO_SCHEMA_CHANGE**
- Production: **NO-GO / NOT AUTHORIZED**
- Updater: **DISABLED / UNWIRED**

GitHub remains the Single Source of Truth.

This document authorizes **Sprint 27 — Governed Interactive First-Party Login / Session Establishment Foundation** for bounded Local/Test/CI implementation after this documentation-only entry gate is published.

Independent review is not an additional mandatory gate under the current Product Owner continuation model unless explicitly reactivated. Exact-head Product Owner authority, exact changed-file scope, required CI, tenant isolation, CSRF/session security, fail-closed runtime controls, and repository protection remain mandatory.

Attribution: **Lab | zefry**

## Why Sprint 27

Sprint 25 introduced an ordinary policy-administration HTTP delivery surface that consumes server-owned `oneqay.auth.*` session state, but deliberately did not establish that state.

Sprint 26 introduced a tenant-bound read-only first-party password credential verifier for exact `(tenant_id, identity_id)` ownership, but deliberately added no login route and no session writer.

The next safe step is therefore not broad authentication, registration, password lifecycle, MFA, external identity providers, Production activation, or a privileged bypass. The next bounded concern is a first-party Local/Test/CI login/session-establishment path that composes the already-published Sprint 26 verifier with the already-published durable tenant and organizational relationship verification primitives before any authenticated session state is written.

## Scope decision

Sprint 27 authorizes exactly one first-party interactive authentication mode:

**password-backed first-party login into server-owned Laravel session state**

Sprint 27 also authorizes one bounded first-party logout operation that invalidates that session state.

Sprint 27 does not authorize:

- registration;
- password enrollment;
- password creation or replacement;
- password change;
- password reset or recovery;
- forgot-password flows;
- email verification;
- remember-me tokens;
- API tokens;
- bearer tokens;
- OAuth/OIDC/SAML;
- social login;
- passkeys/WebAuthn;
- MFA/TOTP;
- recovery codes;
- device trust tokens;
- Production login;
- Technical Preview login;
- platform-superadmin shortcuts;
- Sprint 23 bootstrap reuse;
- Sprint 24 protected-control delegation/revocation through login;
- updater/release/deployment authority;
- migration #8.

## Exact authorized HTTP surface

Sprint 27 authorizes exactly two first-party HTTP routes and no others:

1. `POST /auth/login`
2. `POST /auth/logout`

Authorized route names are:

- `auth.first-party.login`
- `auth.first-party.logout`

No GET login page, registration page, password-management page, public API, GraphQL resolver, CLI login command, background login job, webhook, or external identity-provider callback is authorized.

Both routes remain on Laravel's normal `web` middleware stack. CSRF protection must remain active. Sprint 27 must not add CSRF exclusions or a custom CSRF bypass header.

The login route must retain bounded rate limiting using the already-available Laravel throttle middleware. No dependency change is required.

## Runtime and persistence boundary

The two Sprint 27 routes are authorized only for runtime classes:

- `local`;
- `test`;
- `ci`.

Route registration must fail closed outside that allowlist, and the controller must independently fail closed if invoked outside that allowlist.

Technical Preview and Production must have no active Sprint 27 login/session-establishment authority.

Successful login additionally requires persistence to be explicitly enabled because Sprint 26 credential verification and durable membership/relationship verification are persistence-backed.

`ONEQAY_PERSISTENCE_ENABLED=false` remains the repository default.

Logout may clear a Local/Test/CI session without requiring persistence access.

## Closed login request vocabulary

Ignoring Laravel `_token` as framework CSRF transport metadata, the login business payload is closed to exactly:

- `tenant_id`;
- `identity_id`;
- `password`;
- `organization_id`;
- optional `outlet_id`;
- optional `device_id`.

Unknown business fields fail closed.

`device_id` is invalid without `outlet_id`.

The request may not supply:

- role;
- permission;
- authorization facts;
- protected-control status;
- platform authority;
- updater authority;
- repository/table/model names;
- SQL;
- arbitrary command names;
- session key names;
- session IDs;
- CSRF bypass flags;
- runtime overrides;
- persistence overrides.

## Password handling

Sprint 27 must delegate password verification to the published Sprint 26 `VerifyFirstPartyIdentityCredential` service.

Sprint 27 must not duplicate `password_verify()` logic in the controller or delivery layer.

Sprint 27 must not call `password_hash()` in production application source.

The supplied password must not be:

- trimmed;
- lowercased;
- normalized;
- logged;
- echoed;
- serialized into diagnostics;
- returned in responses;
- stored in session.

Malformed or oversized password input remains subject to the Sprint 26 fail-closed verifier boundary.

## Credential and tenant identity ownership

Authentication identity remains exactly:

`(tenant_id, identity_id)`

`identity_id` alone is never sufficient.

Sprint 27 must call the Sprint 26 verifier with canonical `TenantId` and `PlatformIdentityId` values. Cross-tenant reuse of the same textual identity ID must remain isolated.

A correct password for one tenant must never authenticate the same textual identity under another tenant.

## Durable organizational re-verification before session write

A successful password verification is necessary but not sufficient to establish the final Sprint 27 session context.

Before writing `oneqay.auth.*` session state, Sprint 27 must reuse the existing durable verification path represented by:

- `ServerVerifiedPlatformIdentity`;
- `ServerVerifiedTenantContext`;
- `TenantContextStore`;
- `EnterOrganizationalContext`;
- `TenantMembershipVerifier`;
- `OrganizationalRelationshipVerifier`;
- `OrganizationalContextStore`.

The requested organization/outlet/device context must be durably verified for the authenticated tenant identity before session state is committed.

Password success followed by missing tenant membership, foreign organization, foreign outlet, foreign device, or otherwise invalid organizational context must still fail login generically and write no authenticated session state.

Request-scoped verified tenant and organizational contexts must be cleared in `finally` semantics after processing.

## Exact server-owned session state

Sprint 27 may establish only the already-published five first-party session facts:

- `oneqay.auth.identity_id`;
- `oneqay.auth.tenant_id`;
- `oneqay.auth.organization_id`;
- `oneqay.auth.outlet_id`;
- `oneqay.auth.device_id`.

`outlet_id` and `device_id` may be absent when the verified organizational context does not contain them.

No role, permission, protected-control claim, updater claim, password, password hash, API token, remember-me token, CSRF bypass value, or arbitrary request data may be stored as authentication authority.

The session values must be written from the verified canonical identity/context objects, not blindly copied from raw request data.

## Shared session-key contract

Sprint 27 authorizes one small first-party session-key definition class so login writing and Sprint 25 session consumption share the same exact key vocabulary.

The existing `RequirePolicyAdministrationSessionContextMiddleware` may be modified only to consume that shared key definition while preserving its public constants and every existing runtime/durable re-verification assertion.

Sprint 27 must not weaken Sprint 25 middleware authorization behavior.

## Session fixation and CSRF rotation

On successful login, Sprint 27 must invalidate prior session contents and rotate the framework session identifier before authenticated state becomes authoritative.

A new CSRF token must be generated after the successful login session transition.

Qualification must prove that a pre-login anonymous session cookie is not reused as the authenticated session identifier.

On logout, Sprint 27 must invalidate the session and regenerate the CSRF token.

Logout must not leave any of the five `oneqay.auth.*` keys authoritative.

No custom session ID, client-supplied session key, or remember-me mechanism is authorized.

## Generic authentication failure semantics

All business-level login failures must use one generic authentication failure outcome.

The response contract must not disclose whether failure was caused by:

- unknown identity;
- wrong tenant;
- missing credential;
- wrong password;
- malformed identity value;
- malformed context value;
- missing tenant membership;
- foreign organization;
- foreign outlet;
- foreign device;
- persistence unavailable.

Framework-level CSRF rejection may remain HTTP 419 and rate-limit rejection may remain HTTP 429.

The login failure body must not include submitted tenant, identity, password, organization, outlet, device, password hash, or database facts.

## Login success response

Successful login may return only a minimal success envelope and correlation identifier.

It must not return:

- password/hash material;
- role/permission authority;
- protected-control state;
- updater authority;
- raw session identifier;
- internal database details.

The authenticated server-owned cookie is the framework-managed session transport.

## Logout semantics

`POST /auth/logout` is authorized only to:

- invalidate the current Laravel session;
- clear authenticated first-party state;
- regenerate the CSRF token;
- return a minimal success/no-content result.

Logout must not mutate credentials, roles, permissions, tenant membership, organizational relationships, protected-control state, updater state, or database schema.

## Sprint 25 compatibility requirement

A successful Sprint 27 login must produce session state compatible with the published Sprint 25 `RequirePolicyAdministrationSessionContextMiddleware` contract.

Qualification must prove that the authenticated session can reach the existing Sprint 25 policy-administration route only after Sprint 25 independently re-verifies durable tenant and organizational context.

Sprint 27 does not grant policy-administration permission. Existing Sprint 21/Sprint 22 authorization evaluation remains authoritative after session establishment.

A correctly authenticated identity that lacks policy authority must still be denied by the existing policy-administration boundary.

## Protected-control separation

Login success proves only first-party identity/session establishment for the exact verified tenant/context.

It does not prove or grant:

- `authorization.policy.manage`;
- Sprint 23 bootstrap authority;
- Sprint 24 protected-control delegation/revocation authority;
- emergency recovery authority;
- platform-superadmin authority;
- updater/release/deployment authority.

Those remain separate governed concerns.

## No schema change

Sprint 27 authorizes **zero migrations**.

Canonical migrations remain exactly #1–#7.

Migrations #1–#7 are immutable during Sprint 27 implementation.

Migration #8 does not exist and is not authorized.

No table, column, index, foreign key, session database table, token table, audit table, or authentication metadata table may be introduced under Sprint 27.

The existing session driver configuration is not authorized for schema changes.

## No dependency or configuration expansion

Sprint 27 authorizes no Composer/npm manifest or lockfile changes.

No authentication package, token package, MFA package, OAuth package, or external identity SDK may be added.

Existing Laravel session, CSRF, throttle, request, response, database, and dependency-injection capabilities are sufficient.

Existing environment defaults, including `ONEQAY_PERSISTENCE_ENABLED=false`, must remain unchanged.

## Technical Preview preservation

Technical Preview remains:

**NO_SCHEMA_CHANGE**.

Sprint 27 must not wire first-party credential login into `Application/Preview` or `Delivery/Preview`.

Technical Preview's synthetic `/technical-preview/sign-in` flow remains a separate synthetic Preview-only interaction and must not be treated as Sprint 27 credential authentication.

Sprint 27 must not reuse Technical Preview synthetic identities as production-style first-party credential authority.

The deterministic Technical Preview release artifact and its manifest/checksum/no-schema-change proofs remain mandatory.

## Production preservation

Production remains:

**NO-GO / NOT AUTHORIZED**.

Sprint 27 does not authorize:

- Production login;
- Production credential verification;
- Production session establishment;
- Production database activation;
- cPanel deployment;
- release activation;
- Production readiness inference.

The route and controller runtime boundary must fail closed for Production.

## Updater preservation

Updater remains:

**DISABLED / UNWIRED**.

First-party login/session establishment grants no update, release, deployment, rollback, host, infrastructure, or platform authority.

Updater source and updater authority are outside the Sprint 27 source envelope.

## Exact authorized implementation envelope

After this entry gate is published, Sprint 27 implementation is authorized to change **exactly 17 paths** and no others:

1. `apps/web/app/Delivery/Http/Identity/FirstPartySessionKeys.php`
2. `apps/web/app/Delivery/Http/Identity/FirstPartySessionController.php`
3. `apps/web/app/Delivery/Http/Middleware/RequirePolicyAdministrationSessionContextMiddleware.php`
4. `apps/web/routes/web.php`
5. `apps/web/tests/first-party-session-establishment.php`
6. `.github/workflows/sprint27-first-party-session-establishment-regression.yml`
7. `docs/FIRST_PARTY_LOGIN_SESSION_ESTABLISHMENT_FOUNDATION.md`
8. `.github/workflows/m7-2-tenant-isolation-regression.yml`
9. `.github/workflows/m7-3-identity-org-context-regression.yml`
10. `.github/workflows/m7-4a-technical-preview-interaction-regression.yml`
11. `.github/workflows/m7-5-preview-db-qualification-regression.yml`
12. `.github/workflows/sprint21-role-permission-policy-regression.yml`
13. `.github/workflows/sprint22-policy-administration-regression.yml`
14. `.github/workflows/sprint23-initial-tenant-admin-provisioning-regression.yml`
15. `.github/workflows/sprint24-protected-control-admin-lifecycle-regression.yml`
16. `.github/workflows/sprint25-policy-administration-delivery-regression.yml`
17. `.github/workflows/sprint26-identity-credential-verification-regression.yml`

Any newly discovered preservation dependency outside this exact envelope requires a separately published documentation-only preservation supplement before that path may be modified.

## Source-specific authority

### `FirstPartySessionKeys.php`

May define only the exact five published `oneqay.auth.*` session key constants and a bounded helper to return the key set.

It may not read requests, databases, credentials, roles, permissions, updater state, or environment secrets.

### `FirstPartySessionController.php`

May implement only the two authorized HTTP operations: login and logout.

Login must compose Sprint 26 credential verification plus existing durable tenant/organizational verification before session write.

Logout may only invalidate session state.

The controller may not mutate credential or authorization tables.

### `RequirePolicyAdministrationSessionContextMiddleware.php`

May change only as required to use the shared Sprint 27 session-key definitions while preserving all existing Sprint 25 behavior and public key constants.

### `apps/web/routes/web.php`

May add only the two authorized Sprint 27 routes with Local/Test/CI fail-closed registration and bounded login throttling.

No existing route may be deleted, renamed, broadened, or moved into Sprint 27 authority.

## Disposable qualification requirements

The Sprint 27 disposable regression must use synthetic data only and a temporary SQLite database.

It must run canonical migrations #1–#7 without modifying them.

It must prove at minimum:

1. canonical migration set remains exactly #1–#7;
2. missing CSRF on login is rejected by Laravel web middleware;
3. correct tenant + identity + password + valid organization context succeeds;
4. pre-login anonymous session identifier is rotated on successful login;
5. successful login writes only the exact five allowed authentication session facts, with optional outlet/device absent when not verified;
6. session values are canonical verified values;
7. correct credential plus foreign organization is denied generically and writes no authenticated session;
8. wrong password is denied generically;
9. absent identity is denied generically;
10. missing credential is denied generically;
11. same textual identity in another tenant remains isolated;
12. unknown login business fields are denied generically;
13. device-without-outlet is denied generically;
14. persistence-disabled login is fail-closed;
15. Preview runtime has no active Sprint 27 route/authority;
16. Production runtime has no active Sprint 27 route/authority;
17. successful login session is accepted by the existing Sprint 25 session-context middleware only after durable re-verification;
18. authenticated identity without policy authority remains denied by existing policy administration;
19. logout requires normal CSRF protection;
20. successful logout invalidates authenticated session state;
21. old authenticated session state cannot remain authoritative after logout;
22. credential hash data is unchanged by login and logout;
23. no password or password hash appears in response bodies;
24. request-scoped tenant/organizational contexts are cleared after each login attempt;
25. Sprint 21–26 regressions remain passing.

## Dedicated Sprint 27 workflow requirements

The dedicated workflow must enforce:

- exact 17-path Sprint 27 envelope;
- zero migration diff and exact canonical migration set #1–#7;
- zero dependency manifest/lock changes;
- exact two-route authority only;
- Local/Test/CI route/runtime restriction;
- mandatory CSRF/web stack preservation;
- login throttling presence;
- Sprint 26 verifier reuse and no duplicated password verification logic;
- no credential writer behavior;
- exact five session key vocabulary;
- session invalidation/CSRF rotation behavior;
- generic login failure semantics;
- no Preview/Production wiring;
- no updater/protected-control authority expansion;
- disposable Sprint 27 HTTP/session regression;
- preservation of Sprint 21 through Sprint 26;
- tracked-source cleanliness.

Security assertions must remain blocking. They may not be weakened into warnings to obtain green CI.

## Preservation-workflow rules

The ten existing workflows inside the authorized envelope may change only as needed to:

- recognize the exact Sprint 27 17-path envelope;
- preserve canonical migration set #1–#7 with zero Sprint 27 migration changes;
- invoke/preserve the Sprint 27 disposable session-establishment regression where appropriate;
- preserve their previous Sprint 21–26 security assertions;
- preserve Technical Preview, Production, updater, tenant-isolation, and protected-control boundaries.

They must not remove, skip, weaken, or convert prior assertions into non-blocking warnings.

## Exact-head qualification and merge authority

Sprint 27 source publication requires:

1. implementation only inside the published exact 17-path envelope;
2. final candidate head locked;
3. every triggered required workflow passing on that exact head;
4. no stale CI result reused after any head mutation;
5. Product Owner merge authorization comment bound to the exact final head;
6. `product-owner-merge-authority` status **SUCCESS** for that exact head;
7. final race-check confirming canonical base relation and exact diff;
8. expected-head protected squash merge;
9. post-merge canonical verification;
10. post-Sprint27 canonical reconciliation if the established lifecycle pattern still applies.

No independent review is additionally required under the current Product Owner continuation model.

## Completion definition

Sprint 27 is complete only when the governed source implementation has been published to canonical `main` and post-merge verification proves:

- first-party Local/Test/CI login/session establishment exists;
- login composes Sprint 26 credential verification rather than duplicating it;
- durable tenant/organization verification occurs before session write;
- session fixation and CSRF rotation protections are proven;
- exactly five server-owned `oneqay.auth.*` facts form the session authority;
- logout invalidates that authority;
- canonical migrations remain exactly #1–#7;
- no dependency changes occurred;
- Sprint 21–26 remain preserved;
- Technical Preview remains `NO_SCHEMA_CHANGE`;
- Production remains `NO-GO / NOT AUTHORIZED`;
- updater remains `DISABLED / UNWIRED`;
- password lifecycle, registration, MFA, remember-me, tokens, and external identity providers remain unresolved and separately governed.

GitHub remains the Single Source of Truth.

Attribution: **Lab | zefry**
