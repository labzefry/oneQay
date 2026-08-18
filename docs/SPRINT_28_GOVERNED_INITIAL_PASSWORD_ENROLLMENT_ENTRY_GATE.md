# Sprint 28 Entry Gate — Governed First-Party Initial Password Enrollment Foundation

## Identity and authority

- Product: `oneQay`
- Engineering entity: **Lab | zefry**
- Repository: `labzefry/oneQay`
- Canonical branch: `main`
- Exact base: `9a88e5ddaa36f52ab5b9105af8f57a6d99bdf8ba`
- Exact base tree: `6c0cf8b0d605ddd2d935a5878eac12d7569c8900`
- Sprint 27: **COMPLETE / IMPLEMENTED / PUBLISHED**
- Post-Sprint 27 canonical reconciliation: **PUBLISHED**
- Production readiness: **NO-GO / NOT AUTHORIZED**

GitHub remains the Single Source of Truth.

This document authorizes **Sprint 28 — Governed First-Party Initial Password Enrollment Foundation** for bounded Local/Test/CI implementation after this documentation-only entry gate is published.

Independent review is not an additional mandatory gate under the current Product Owner continuation model unless explicitly reactivated. Exact-head Product Owner authority, exact changed-file scope, required CI, tenant isolation, protected-control authorization, fail-closed runtime controls, privacy boundaries, and repository protection remain mandatory.

Attribution: **Lab | zefry**

## Why Sprint 28

Sprint 26 created read-only password credential verification. Sprint 27 created Local/Test/CI first-party login/session establishment. Neither sprint created an authorized application lifecycle for an existing tenant identity to obtain its first password credential.

A tenant administrator directly assigning a user's final password would unnecessarily disclose that password to the administrator. A public self-enrollment endpoint without an independently issued enrollment secret would permit unauthorized credential creation. Persisting a plaintext or reversibly encrypted enrollment secret would create an avoidable credential-recovery risk.

Sprint 28 therefore establishes a bounded two-step enrollment pattern:

1. an already authenticated tenant-control administrator issues a short-lived, one-time enrollment challenge for a different existing identity in the same tenant;
2. the target identity redeems the enrollment challenge and chooses its own initial password.

The administrator never receives or supplies the target identity's final password.

## Scope decision

Sprint 28 authorizes exactly one credential lifecycle operation:

**initial password enrollment for an existing tenant identity that currently has no password credential.**

Sprint 28 does not authorize:

- user/identity registration;
- self-enrollment without administrator issuance;
- administrator assignment of the target's final password;
- password change or rotation;
- forgot-password or password reset;
- password recovery;
- credential revocation;
- credential deletion;
- credential overwrite;
- remember-me tokens;
- API/bearer tokens;
- passkeys/WebAuthn;
- TOTP/MFA enrollment or verification;
- recovery codes;
- OAuth/OIDC/SAML/external identity providers;
- social login;
- email verification;
- SMS delivery;
- email delivery integration;
- emergency administrator recovery;
- Production activation.

## Authority to issue enrollment

Enrollment issuance is not a public or unauthenticated operation.

The issuer must be an already authenticated first-party session that:

- passes the published Sprint 27 server-owned session boundary;
- passes the existing `RequirePolicyAdministrationSessionContextMiddleware` durable tenant/organizational re-verification;
- holds tenant-scoped control authority equivalent to the existing `authorization.policy.manage` protected-control authority in the exact tenant;
- targets a different identity in the same tenant;
- targets an identity that already exists in canonical `oneqay_identities`;
- targets an identity that does not already have a password credential.

The target identity must not be derived from another tenant, a request-supplied role/permission claim, a platform-superadmin shortcut, updater authority, or Technical Preview synthetic identity.

Self-enrollment through the administrator issuance route is explicitly denied.

This Sprint does not solve first-control-principal bootstrap credential creation. That remains separately governed because an authenticated tenant-control issuer is required.

## Enrollment token model

Sprint 28 uses a generated one-time enrollment token.

The token must:

- be generated from at least 32 cryptographically secure random bytes using the locked PHP runtime;
- use URL-safe base64 encoding without padding for transport;
- contain at least 256 bits of randomness before encoding;
- be returned only from a successful issuance response;
- never be placed in a URL, query string, route parameter, log, exception message, session value, cookie, telemetry field, audit row, or repository document;
- never be stored in plaintext;
- never be reversibly encrypted for later recovery.

Only a SHA-256 digest of the token may be persisted.

The plaintext token is intentionally **non-recoverable** after the successful issuance response. If the response is lost, the exact token cannot be replayed from storage. A later challenge lifecycle/reissue concern may be governed separately.

## Token response privacy

A successful issuance response may return:

- `status`;
- `enrollment_id`;
- `target_identity_id`;
- `expires_at_unix`;
- `enrollment_token`;
- `correlation_id`.

The response must set cache-prevention headers equivalent to:

- `Cache-Control: no-store, private`;
- `Pragma: no-cache`.

No password, password hash, token digest, role, permission, session identifier, database fact, or foreign-tenant fact may be returned.

## Enrollment lifetime

The initial enrollment token lifetime is exactly **900 seconds / 15 minutes**.

The persisted enrollment record binds:

- tenant;
- enrollment identifier;
- issuer identity;
- target identity;
- token digest;
- issued time;
- expiration time;
- consumed time when redeemed;
- a bounded active marker used only to enforce at most one active enrollment per target.

An expired token cannot create a credential.

Expired active rows may be made inactive during a later issuance transaction for the same target. This cleanup does not revive or reveal the expired token.

## One-active-enrollment invariant

At most one active initial password enrollment may exist for the same `(tenant_id, target_identity_id)`.

Migration #8 must enforce this with a database-level unique invariant using an active marker that is `1` for active enrollment and `NULL` after consumption/expiration cleanup.

The database must remain the final race-condition boundary.

## Issuance replay semantics

Issuance is intentionally **not token-replayable**, because the plaintext enrollment token is never persisted and therefore cannot be safely reconstructed.

The caller supplies an `enrollment_id` used as a durable request identity.

If the same `(tenant_id, enrollment_id)` already exists:

- no new token may be generated;
- no new enrollment row may be written;
- the stored token digest may not be returned;
- the old plaintext token may not be reconstructed;
- the request fails with a generic issuance conflict.

This is an explicit security tradeoff in favor of non-recoverable token storage.

## Redemption replay semantics

Redemption is one-time for authority but safe for exact network replay.

When a valid token was already consumed and the exact bound credential now exists, a repeat redemption using the same tenant, identity, enrollment id, and token may return the deterministic `applied` outcome without writing or replacing the credential.

A consumed enrollment must never create a second credential, rotate a password, or authorize another identity.

## Password policy

Sprint 28 initial password input must:

- be a string;
- contain at least 12 bytes;
- contain at most 4096 bytes;
- be treated as opaque case-sensitive material;
- not be trimmed;
- not be lowercased;
- not be Unicode-normalized by application code;
- not be logged, serialized into journal evidence, returned, or stored in session.

Sprint 28 intentionally does not impose brittle character-class composition rules.

The credential is hashed with PHP's locked `password_hash(..., PASSWORD_DEFAULT)` primitive before insertion into `oneqay_identity_password_credentials`.

A hash-generation failure fails closed.

No reversible encryption routine is authorized for passwords.

## Credential-write boundary

Sprint 28 may insert exactly one password credential row only when all enrollment checks succeed.

Credential ownership remains:

`(tenant_id, identity_id)`

The existing Sprint 26 credential table remains canonical and is not altered.

Sprint 28 may:

- insert a credential when none exists;
- reject attempts when a credential already exists.

Sprint 28 must not:

- update an existing `password_hash`;
- delete a credential;
- upsert a credential;
- overwrite a credential;
- create a credential for another tenant;
- create a credential for a non-existent identity.

Password change/rotation remains separately governed.

## Additive migration #8

Sprint 28 authorizes exactly one additive forward-only migration:

`0000_00_00_000008_create_initial_password_enrollments.php`

It may create exactly one table:

`oneqay_initial_password_enrollments`

The table must contain only bounded lifecycle facts:

- `tenant_id` — string length 64;
- `enrollment_id` — string length 64;
- `actor_identity_id` — string length 96;
- `target_identity_id` — string length 96;
- `token_digest` — fixed-length SHA-256 hexadecimal digest;
- `issued_at_unix` — positive integer timestamp;
- `expires_at_unix` — positive integer timestamp;
- `consumed_at_unix` — nullable positive integer timestamp;
- `active_marker` — nullable tiny integer, `1` only while active.

Primary key:

`(tenant_id, enrollment_id)`

Composite foreign keys must bind actor and target independently to canonical `oneqay_identities(tenant_id, id)` with restrict-on-update/delete behavior.

A unique invariant must enforce at most one active enrollment per `(tenant_id, target_identity_id)`.

No plaintext token, password, password hash, password hint, session token, CSRF token, role set, permission set, request body, email address, phone number, arbitrary metadata JSON, or real customer payload may be stored in this table.

Migration `down()` remains forward-only and throws the canonical rollback-not-authorized exception.

Migrations #1–#7 are immutable.

## Canonical migration set after successful implementation

A successful Sprint 28 source publication makes the canonical migration set exactly #1–#8:

1. `0000_00_00_000001_create_foundational_context_graph.php`;
2. `0000_00_00_000002_create_organizational_access_grants.php`;
3. `0000_00_00_000003_create_scoped_role_permission_policy.php`;
4. `0000_00_00_000004_create_policy_mutation_journal.php`;
5. `0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php`;
6. `0000_00_00_000006_create_protected_control_administrator_mutation_journal.php`;
7. `0000_00_00_000007_create_identity_password_credentials.php`;
8. `0000_00_00_000008_create_initial_password_enrollments.php`.

## Application boundary

Sprint 28 introduces bounded Application-layer primitives for:

- enrollment identifier validation;
- issued-enrollment result representation;
- repository contract;
- issuance/redemption orchestration;
- classified lifecycle violations.

Application source must remain independent from Illuminate database/query mechanics.

Password and enrollment-token parameters should use PHP `SensitiveParameter` where applicable.

## Infrastructure repository

The Infrastructure repository may:

- verify exact tenant-control issuer authority;
- verify exact same-tenant target identity existence;
- verify credential absence;
- expire stale active challenge state during a later issuance for the exact target;
- generate a secure random enrollment token;
- store only its SHA-256 digest;
- verify token digest with `hash_equals`;
- hash the target's initial password using `PASSWORD_DEFAULT`;
- insert exactly one initial credential;
- mark the exact enrollment consumed;
- recognize exact post-consumption redemption replay.

All durable writes must occur within the existing `PersistenceTransaction` boundary.

The repository must permit lifecycle persistence only when both are true:

1. `database.oneqay_persistence_enabled` is true;
2. runtime class is one of `local`, `test`, `ci`.

Preview and Production are denied.

## HTTP issuance route

Sprint 28 authorizes exactly one administrator issuance route:

`POST /administration/identity/password-enrollments`

Route name:

`identity.initial-password-enrollment.issue`

It must:

- be registered only in Local/Test/CI;
- remain in Laravel `web` middleware and therefore CSRF protected;
- use existing `RequirePolicyAdministrationSessionContextMiddleware`;
- use existing throttle middleware;
- obtain actor authority only from the durably re-verified server-owned Sprint 27 session context;
- accept only `enrollment_id` and `target_identity_id` as business payload;
- return the generated plaintext token only on successful issuance;
- use no-store response headers.

No password is accepted by the issuance route.

## HTTP redemption route

Sprint 28 authorizes exactly one redemption route:

`POST /auth/password-enrollment`

Route name:

`auth.initial-password-enrollment.redeem`

It must:

- be registered only in Local/Test/CI;
- remain in Laravel `web` middleware and therefore CSRF protected;
- use existing throttle middleware;
- require no authenticated first-party session;
- accept only `tenant_id`, `identity_id`, `enrollment_id`, `enrollment_token`, and `password` as business payload;
- never establish a login session automatically after password enrollment;
- never grant role, permission, protected-control, updater, or platform authority.

After successful redemption, the user must use the separately published Sprint 27 login route to establish a session.

## Generic failure boundaries

Administrator issuance failures must not reveal foreign-tenant target facts or password-credential state beyond what an authorized tenant-control issuer already owns.

The issuance controller returns a bounded generic error code:

`INITIAL_PASSWORD_ENROLLMENT_ISSUE_REJECTED`

The redemption controller collapses all business-level failures—including absent enrollment, wrong token, expired token, tenant mismatch, identity mismatch, credential already present, malformed lifecycle state, persistence denied, and replay conflict—into a bounded generic envelope:

`INITIAL_PASSWORD_ENROLLMENT_FAILED`

Framework CSRF rejection remains HTTP 419. Throttling may remain HTTP 429.

No token digest, password hash, account-existence oracle, foreign-tenant detail, or internal database error may be returned.

## No automatic session establishment

Sprint 28 does not modify Sprint 27 login semantics.

Successful password enrollment writes a credential only. It does not:

- authenticate the target;
- issue or rotate a login session;
- write any `oneqay.auth.*` session fact;
- bypass password verification;
- bypass Sprint 27 durable organizational verification.

## Technical Preview preservation

Technical Preview remains:

**NO_SCHEMA_CHANGE**.

The governed Technical Preview release artifact must continue excluding the migration directory.

Sprint 28 initial password enrollment routes/classes must not be wired into Technical Preview Application or Delivery source.

The existence of canonical migration #8 in source does not authorize applying migration #8 to Technical Preview.

M7.4A and M7.5 qualification must recognize canonical migration #8 while preserving their no-schema-change and Preview-separation contracts.

## Production and updater preservation

Production remains:

**NO-GO / NOT AUTHORIZED**.

Updater remains:

**DISABLED / UNWIRED**.

Sprint 28 enrollment authority grants no deployment, update, rollback, release, host, infrastructure, platform-superadmin, emergency-recovery, or Production authority.

## Persistence default

`ONEQAY_PERSISTENCE_ENABLED=false` remains the repository default.

No environment example may default credential enrollment persistence to true.

## No dependency changes

Sprint 28 authorizes no Composer/npm manifest or lockfile changes.

The locked PHP runtime provides all required cryptographic primitives:

- `random_bytes`;
- SHA-256 `hash`;
- `hash_equals`;
- `password_hash`;
- `PASSWORD_DEFAULT`.

## Exact authorized implementation envelope

After this entry gate is published, Sprint 28 implementation is authorized to change **exactly 33 paths** and no others:

1. `apps/web/app/Application/Identity/InitialPasswordEnrollmentId.php`
2. `apps/web/app/Application/Identity/IssuedInitialPasswordEnrollment.php`
3. `apps/web/app/Application/Identity/InitialPasswordEnrollmentRepository.php`
4. `apps/web/app/Application/Identity/InitialPasswordEnrollmentService.php`
5. `apps/web/app/Application/Identity/InitialPasswordEnrollmentViolation.php`
6. `apps/web/app/Infrastructure/Identity/LaravelInitialPasswordEnrollmentRepository.php`
7. `apps/web/app/Providers/AppServiceProvider.php`
8. `apps/web/app/Delivery/Http/Identity/InitialPasswordEnrollmentController.php`
9. `apps/web/routes/web.php`
10. `apps/web/database/migrations/0000_00_00_000008_create_initial_password_enrollments.php`
11. `apps/web/tests/initial-password-enrollment.php`
12. `.github/workflows/sprint28-initial-password-enrollment-regression.yml`
13. `docs/FIRST_PARTY_INITIAL_PASSWORD_ENROLLMENT_FOUNDATION.md`
14. `apps/web/tests/authorization-persistence.php`
15. `apps/web/tests/authorization-administration-persistence.php`
16. `apps/web/tests/initial-tenant-administrator-provisioning.php`
17. `apps/web/tests/protected-control-administrator-lifecycle.php`
18. `apps/web/tests/tenant-isolation.php`
19. `apps/web/tests/identity-org-context.php`
20. `apps/web/tests/policy-administration-delivery.php`
21. `apps/web/tests/identity-credential-verification.php`
22. `apps/web/tests/first-party-session-establishment.php`
23. `.github/workflows/m7-2-tenant-isolation-regression.yml`
24. `.github/workflows/m7-3-identity-org-context-regression.yml`
25. `.github/workflows/m7-4a-technical-preview-interaction-regression.yml`
26. `.github/workflows/m7-5-preview-db-qualification-regression.yml`
27. `.github/workflows/sprint21-role-permission-policy-regression.yml`
28. `.github/workflows/sprint22-policy-administration-regression.yml`
29. `.github/workflows/sprint23-initial-tenant-admin-provisioning-regression.yml`
30. `.github/workflows/sprint24-protected-control-admin-lifecycle-regression.yml`
31. `.github/workflows/sprint25-policy-administration-delivery-regression.yml`
32. `.github/workflows/sprint26-identity-credential-verification-regression.yml`
33. `.github/workflows/sprint27-first-party-session-establishment-regression.yml`

Any newly discovered preservation dependency outside this exact envelope requires a separately published documentation-only preservation supplement before that path may be modified.

## Preservation-test rules

The nine existing tests in the authorized preservation envelope may change only to:

- update exact canonical migration expectations from #1–#7 to #1–#8;
- materialize migration #8 where each test intentionally applies the full canonical migration set;
- assert the new enrollment table's tenant-bound foreign-key/secret-storage boundary where directly relevant;
- preserve every prior Sprint 21–27 assertion.

They must not remove, skip, weaken, or convert prior assertions into non-blocking warnings.

## Preservation-workflow rules

The eleven existing workflows in the authorized envelope may change only to:

- recognize the exact Sprint 28 33-file envelope where applicable;
- update canonical migration expectations to #1–#8;
- enforce migration #1–#7 immutability and migration #8 additive-only semantics;
- execute the Sprint 28 disposable regression where appropriate;
- preserve all Sprint 21–27, M7.x, Technical Preview, Production, and updater assertions.

No prior security gate may be removed merely to make Sprint 28 pass.

## Dedicated Sprint 28 regression requirements

The new Sprint 28 workflow must prove at minimum:

- exact 33-path diff envelope;
- zero dependency changes;
- migrations #1–#7 immutable;
- migration #8 is the only migration diff;
- exact canonical migration set #1–#8;
- Application source remains framework/database independent;
- password/token sensitive parameters are not logged;
- issuer must have exact tenant-control authority;
- target must exist in the same tenant;
- self-enrollment issuance is denied;
- existing credential issuance is denied;
- at most one active enrollment per target;
- plaintext enrollment token is never persisted;
- persisted token digest is SHA-256 and never returned;
- issuance response has no-store headers;
- wrong/expired/foreign token redemption fails generically;
- cross-tenant enrollment replay fails;
- password policy is 12–4096 bytes without trimming;
- credential insert is one-time and never update/upsert/delete;
- successful redemption creates exactly one credential for the exact tenant/identity;
- successful redemption marks the exact enrollment consumed;
- repeat exact redemption is deterministic and does not change the credential;
- enrollment does not establish a Sprint 27 login session;
- the newly created credential subsequently passes Sprint 26 verification and can be used by Sprint 27 login only through the normal login route;
- CSRF remains mandatory for both issuance and redemption;
- both new routes are absent in Preview and Production runtime registration;
- Technical Preview remains `NO_SCHEMA_CHANGE`;
- Production remains `NO-GO / NOT AUTHORIZED`;
- updater remains disabled/unwired;
- persistence default remains false;
- Sprint 21–27 regressions remain passing;
- tracked source remains clean.

## Source publication authority

After the entry gate is merged, Sprint 28 implementation may proceed only from that exact canonical gate publication commit.

Before source merge:

1. exact implementation head must contain exactly the 33 authorized changed paths;
2. required CI for that exact head must be successful;
3. Product Owner merge authorization must bind to that exact PR and exact head SHA;
4. final race-check must prove canonical `main` has not moved unexpectedly and the implementation branch remains behind-by-zero relative to its authorized base;
5. squash merge must use expected-head protection.

## Post-Sprint 28 reconciliation

After successful source publication, publish a separate documentation-only post-Sprint28 canonical reconciliation if the established governance pattern still requires it.

That reconciliation should record source publication SHA/tree, exact source head, CI matrix, migration #8 publication, enrollment security boundaries, unresolved bootstrap credential concern, Preview/Production/updater state, and the next separately governed engineering concern.

## Canonical declaration at entry

At this gate:

- Sprint 27 remains **COMPLETE / IMPLEMENTED / PUBLISHED**;
- canonical main is `9a88e5ddaa36f52ab5b9105af8f57a6d99bdf8ba`;
- canonical tree is `6c0cf8b0d605ddd2d935a5878eac12d7569c8900`;
- canonical migrations are exactly #1–#7;
- first-party password verification exists Local/Test/CI only;
- first-party login/session establishment exists Local/Test/CI only;
- Sprint 28 initial password enrollment is authorized only after publication of this gate;
- first-control-principal bootstrap credential creation remains unresolved;
- password change/reset/recovery remains unresolved;
- MFA/TOTP/passkeys remain unresolved;
- Technical Preview remains `NO_SCHEMA_CHANGE`;
- Production remains `NO-GO / NOT AUTHORIZED`;
- updater remains `DISABLED / UNWIRED`.

GitHub remains the Single Source of Truth.

Attribution: **Lab | zefry**
