# First-Party Initial Password Enrollment Foundation

## Status

Sprint 28 establishes a governed **Local/Test/CI-only** first-party initial password enrollment foundation for existing tenant identities.

Attribution: **Lab | zefry**

## Security objective

The foundation closes one bounded gap between Sprint 26 credential verification and Sprint 27 login/session establishment: an existing identity with no password credential can now obtain its first password through an authorized application lifecycle.

The flow deliberately avoids an administrator choosing or learning the target identity's final password.

## Two-step flow

### 1. Tenant-control administrator issues an enrollment challenge

`POST /administration/identity/password-enrollments`

The issuer must already have a valid Sprint 27 first-party session and must pass `RequirePolicyAdministrationSessionContextMiddleware`. The durable repository then independently verifies tenant-control authority before creating any enrollment.

The request accepts only:

- `enrollment_id`;
- `target_identity_id`.

The target must:

- exist in the exact issuer tenant;
- be different from the issuer;
- have no existing password credential;
- have no other active initial-password enrollment.

A successful response returns a generated one-time token exactly once and sets `Cache-Control: no-store, private` plus `Pragma: no-cache`.

### 2. Target redeems the enrollment challenge

`POST /auth/password-enrollment`

The request accepts only:

- `tenant_id`;
- `identity_id`;
- `enrollment_id`;
- `enrollment_token`;
- `password`.

Redemption requires normal Laravel web CSRF protection and is throttled. It does not require an existing authenticated first-party session.

A successful redemption writes exactly one credential and marks the exact enrollment consumed in the same durable transaction.

Successful enrollment does **not** authenticate the user automatically. The target must subsequently use Sprint 27 `POST /auth/login` and pass the normal Sprint 26 credential verification plus durable organizational verification.

## Enrollment token

The Infrastructure repository generates:

- 32 cryptographically secure random bytes via `random_bytes(32)`;
- URL-safe base64 without padding;
- a 43-character transport token.

Only `hash('sha256', $token)` is persisted.

The plaintext token is never persisted, never reversibly encrypted, never stored in session, and never written into the enrollment lifecycle table.

Token comparison uses `hash_equals` against the stored digest.

The token lifetime is exactly 900 seconds.

## Enrollment persistence

Migration #8 adds only:

`oneqay_initial_password_enrollments`

Columns:

- `tenant_id`;
- `enrollment_id`;
- `actor_identity_id`;
- `target_identity_id`;
- `token_digest`;
- `issued_at_unix`;
- `expires_at_unix`;
- `consumed_at_unix`;
- `active_marker`.

The primary key is `(tenant_id, enrollment_id)`.

Actor and target are independently protected by same-tenant composite foreign keys to `oneqay_identities`.

A unique `(tenant_id, target_identity_id, active_marker)` invariant makes the database the final race-condition boundary for at-most-one active enrollment per target. Active rows use marker `1`; consumed or retired expired rows use `NULL`.

No plaintext token, password, password hash, role, permission, session identifier, CSRF token, arbitrary metadata, email address, or phone number is stored in this table.

## Credential persistence

Sprint 28 does not modify the Sprint 26 credential table.

On valid redemption, the repository hashes the password with:

`password_hash($password, PASSWORD_DEFAULT)`

and performs a single insert into:

`oneqay_identity_password_credentials`

for the exact `(tenant_id, identity_id)`.

Sprint 28 contains no credential update, upsert, or delete lifecycle. If a credential already exists, initial enrollment fails closed.

Password change/rotation/reset/recovery remains separately governed.

## Password handling

Initial password input is treated as opaque, case-sensitive material.

Policy:

- minimum 12 bytes;
- maximum 4096 bytes;
- no trimming;
- no lowercasing;
- no application Unicode normalization;
- no character-class composition rule;
- no logging;
- no session persistence;
- no journal persistence;
- no response echo.

The disposable regression proves leading/trailing spaces remain part of the password rather than being silently removed.

## Issuance authority

Issuance requires all of:

- Local/Test/CI runtime;
- persistence explicitly enabled;
- a durably re-verified Sprint 27 session context;
- exact tenant-control authorization compatible with the protected `authorization.policy.manage` state;
- exact same-tenant target identity;
- target differs from actor;
- no existing target credential;
- no active target enrollment.

Request payload cannot supply role, permission, control, updater, runtime, persistence, session, table, SQL, or platform authority.

## Self-enrollment

Administrator issuance to the current actor is denied.

Sprint 28 therefore does not create public self-enrollment and does not solve first-control-principal bootstrap credential creation.

That bootstrap concern remains separate because Sprint 28 requires an already authenticated tenant-control issuer.

## Expiration and stale challenge retirement

An expired token cannot redeem.

When a tenant-control administrator later issues a new challenge for the same target, the repository may retire an expired active marker before inserting the new challenge. The expired token remains non-recoverable and unusable.

## Replay behavior

### Issuance

Issuance is intentionally not replayable as a token-returning operation. The plaintext token is not stored, so it cannot be reconstructed on duplicate `enrollment_id` requests.

A duplicate enrollment identifier fails generically and creates no replacement token.

### Redemption

After successful redemption, exact replay with the same tenant, identity, enrollment id, and token returns deterministic `applied` without replacing the credential.

The replay password is ignored for mutation purposes after the exact enrollment has already been consumed. The stored password hash remains unchanged.

## Generic failure behavior

Issuance returns the bounded code:

`INITIAL_PASSWORD_ENROLLMENT_ISSUE_REJECTED`

Redemption collapses business-level failures into:

`INITIAL_PASSWORD_ENROLLMENT_FAILED`

Wrong token, absent enrollment, expired enrollment, tenant mismatch, identity mismatch, malformed state, existing credential, persistence denial, and invalid password never expose token digests or credential hashes.

Framework CSRF rejection remains HTTP 419 and throttling may remain HTTP 429.

## CSRF and throttling

Both Sprint 28 routes remain under Laravel's normal `web` middleware stack.

No CSRF exclusion is introduced.

Both routes use existing throttle middleware with no dependency change.

The disposable regression proves missing CSRF prevents issuance/redemption writes.

## Canonical migrations

After Sprint 28 publication, canonical source contains exactly migrations #1–#8.

Migrations #1–#7 remain immutable. Migration #8 is additive and forward-only.

The existence of migration #8 in canonical source does not authorize schema application to Technical Preview or Production.

## Technical Preview

Technical Preview remains:

**NO_SCHEMA_CHANGE**

Sprint 28 enrollment classes and routes are not wired into Technical Preview Application/Delivery source.

Both Sprint 28 routes are absent when runtime class is Preview.

The deterministic Technical Preview release artifact continues excluding the migrations directory.

## Production

Production remains:

**NO-GO / NOT AUTHORIZED**

Both Sprint 28 routes are absent when runtime class is Production, and the Infrastructure repository independently denies persistence outside Local/Test/CI.

Sprint 28 does not imply Production readiness.

## Updater

Updater remains:

**DISABLED / UNWIRED**

Enrollment authority grants no update, deployment, release, rollback, host, infrastructure, or platform authority.

## Persistence default

`ONEQAY_PERSISTENCE_ENABLED=false` remains the repository default.

Sprint 28 does not change the environment default.

## Dependency boundary

Sprint 28 adds no Composer/npm package and changes no dependency manifest or lockfile.

The locked runtime provides all required cryptographic primitives.

## Qualification matrix

The Sprint 28 disposable regression proves:

- exact migration #1–#8 application in disposable SQLite;
- migration #8 schema and unique active-target invariant;
- same-tenant actor/target foreign keys;
- authenticated control-admin issuance;
- CSRF enforcement;
- no-store token response;
- plaintext token absent from persistence;
- SHA-256 digest persistence;
- self-issuance denial;
- non-control issuer denial;
- foreign-tenant target denial;
- existing-credential denial;
- one-active-enrollment denial;
- duplicate enrollment-id denial;
- generic wrong-token/foreign-tenant/wrong-identity failure;
- bounded password length policy;
- one-time credential creation;
- exact enrollment consumption;
- no authenticated session creation during redemption;
- deterministic exact redemption replay;
- no password overwrite on replay;
- Sprint 26 verification of the newly enrolled credential;
- Sprint 27 login using the newly enrolled credential;
- expired-token denial;
- safe stale-active retirement on later issuance;
- exact preservation of password whitespace;
- cross-tenant credential isolation;
- absence of sensitive material from enrollment persistence.

## Explicitly unresolved

Sprint 28 does not authorize or solve:

- first-control-principal bootstrap credential creation;
- password change/rotation;
- forgot-password/reset/recovery;
- credential revocation/deletion;
- MFA/TOTP;
- passkeys/WebAuthn;
- recovery codes;
- OAuth/OIDC/SAML;
- email/SMS delivery integration;
- remember-me;
- API/bearer tokens;
- emergency protected-control recovery;
- Production authentication.

These require later, separately governed entry gates.

Attribution: **Lab | zefry**
