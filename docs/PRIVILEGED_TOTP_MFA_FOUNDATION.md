# Privileged TOTP MFA Foundation — Sprint 30

Attribution: **Lab | zefry**

## Status

`IMPLEMENTED CANDIDATE / NOT YET PUBLISHED`

This document describes the bounded Sprint 30 source candidate authorized by the published Sprint 30 selection and source-envelope gates. Publication requires exact-head Product Owner merge authority after all governed checks succeed.

## Purpose

Sprint 30 closes the mandatory privileged-MFA gap for the protected first-party control principal without activating Production authentication, Technical Preview authentication, recovery, or updater behavior.

The protected-control identity remains the existing canonical role/permission pair:

- role: `authorization-policy-administrator`
- permission: `authorization.policy.manage`

When the Sprint 30 feature arm is enabled in an authorized runtime, password verification alone is no longer sufficient to establish a full protected-control session.

## Runtime and feature boundary

The source feature arm is:

`ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED`

The source default is `false`.

The foundation is callable only when both conditions are true:

1. runtime class is exactly one of `local`, `test`, or `ci`;
2. `ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED=true`.

The established program boundaries remain unchanged:

- Technical Preview: **NO_SCHEMA_CHANGE**
- Production: **NO-GO / NOT AUTHORIZED**
- updater: **DISABLED / UNWIRED**
- persistence source default: `ONEQAY_PERSISTENCE_ENABLED=false`

No `.env*` file is changed or published by Sprint 30.

## Dependency boundary

Sprint 30 adds exactly one direct Composer dependency:

`spomky-labs/otphp` = `11.5.0`

The generated lock resolves the required constant-time encoding dependency to:

`paragonie/constant_time_encoding` = `v3.1.3`

The source candidate does not authorize unrelated Composer dependency upgrades and does not modify Node/npm dependency manifests.

OTPHP awareness is restricted to the Infrastructure adapter:

`apps/web/app/Infrastructure/Identity/OtphpPrivilegedTotpEngine.php`

Application services depend only on the `PrivilegedTotpEngine` port. Application code does not implement HMAC, Base32, dynamic truncation, provisioning-URI construction, or OTP comparison.

## Fixed TOTP profile

The governed TOTP profile is fixed to:

- algorithm: SHA-1
- digits: 6
- period: 30 seconds
- generated secret entropy input: 20 bytes
- accepted clock window: current time step plus exactly one adjacent step on either side
- issuer: `oneQay`
- account label: exact tenant identifier and exact identity identifier

The input code must be exactly six ASCII digits. Whitespace trimming, alternate numerals, normalization, and additional request fields are rejected.

The adapter verifies candidate timestamps one at a time using the provider verification primitive with no provider-internal leeway. It returns the exact matched integer time step to the Application service so durable replay protection can operate on the accepted step rather than only on the submitted digits.

## Migration 9

Sprint 30 authorizes exactly one additive migration:

`0000_00_00_000009_create_identity_totp_factors.php`

It creates exactly one table:

`oneqay_identity_totp_factors`

Columns:

- `tenant_id` — string(64), non-null
- `identity_id` — string(96), non-null
- `secret_ciphertext` — text, non-null
- `created_at_unix` — unsigned big integer, non-null
- `confirmed_at_unix` — unsigned big integer, nullable
- `last_accepted_time_step` — unsigned big integer, nullable

Primary key:

`(tenant_id, identity_id)`

Foreign key:

`(tenant_id, identity_id)` → `oneqay_identities(tenant_id, id)` with restrict-on-delete and restrict-on-update behavior.

The table intentionally contains no factor identifier, recovery material, backup code, reset token, factor history, factor replacement metadata, or multi-factor collection structure.

Migrations #1 through #8 remain immutable. The post-Sprint30 source candidate contains exactly migrations #1 through #9 and no migration #10.

Rollback remains forward-only denied.

## Durable factor state

Factor state is derived from the durable row:

- `absent`: no row exists;
- `pending`: row exists, `confirmed_at_unix` is null, and `last_accepted_time_step` is null;
- `confirmed`: row exists, `confirmed_at_unix` is positive, and `last_accepted_time_step` is a non-negative integer.

Any other shape is invalid and fails closed.

A pending enrollment is insert-only. Repeating enrollment start for the same protected identity reuses the same pending secret and does not replace the factor.

Once confirmed, Sprint 30 does not provide factor replacement, factor deletion, factor reset, or recovery.

## Secret protection

The raw TOTP secret is classified as Restricted authentication material.

The database stores only authenticated ciphertext produced by the Laravel Encrypter backed by the application key. Before encryption, the payload binds:

- payload version;
- exact tenant identifier;
- exact identity identifier;
- TOTP secret.

Decrypt requires all fields to match the requested tenant and identity exactly. Copying ciphertext to another tenant or identity therefore fails closed even if the ciphertext itself is otherwise valid.

The raw secret and provisioning URI are returned only to the same restricted pre-auth enrollment session that already passed password verification and protected-control derivation.

Sprint 30 does not authorize external QR-code services or any network delivery of provisioning material.

The raw secret, provisioning URI, submitted TOTP code, and decrypted payload must not be written to logs, errors, CI output, screenshots, or persisted test fixtures.

## Replay and concurrency protection

Enrollment confirmation consumes the successfully verified time step by writing both:

- `confirmed_at_unix`;
- `last_accepted_time_step`.

A subsequent challenge is accepted only when its matched step is strictly greater than the durable `last_accepted_time_step`.

Critical checks are repeated inside the existing `PersistenceTransaction` immediately before mutation. Factor rows are acquired with a row lock when mutating, and the final update includes a monotonic condition on `last_accepted_time_step`.

Consequences:

- an already accepted time step cannot be accepted again;
- an older time step cannot be accepted;
- two requests racing with the same accepted time step cannot both advance the durable marker;
- stale state fails closed with the same bounded MFA verification surface.

Sprint 30 does not modify the shared `PersistenceTransaction` contract.

## Session-state model

Sprint 27 full authenticated context keys remain unchanged:

- `oneqay.auth.identity_id`
- `oneqay.auth.tenant_id`
- `oneqay.auth.organization_id`
- `oneqay.auth.outlet_id`
- `oneqay.auth.device_id`

Sprint 30 adds restricted pre-auth keys:

- `oneqay.auth.pending.identity_id`
- `oneqay.auth.pending.tenant_id`
- `oneqay.auth.pending.organization_id`
- `oneqay.auth.pending.outlet_id`
- `oneqay.auth.pending.device_id`
- `oneqay.auth.pending.mfa_state`

Allowed pending states are exactly:

- `enrollment_required`
- `challenge_required`

Successful privileged verification adds full-session evidence:

`oneqay.auth.mfa_verified_at`

A pending session must not contain any full authenticated context key or MFA evidence.

## Login transition

With the Sprint 30 feature arm disabled, Sprint 27 login behavior remains unchanged.

With the feature arm enabled:

1. password verification runs first;
2. organizational context is revalidated using the existing server-verified path;
3. the canonical protected-control requirement is derived from durable tenant-scoped role/permission state;
4. a non-protected identity receives the existing Sprint 27 full session;
5. a protected identity with no factor or a pending factor receives only restricted `enrollment_required` state;
6. a protected identity with a confirmed factor receives only restricted `challenge_required` state.

The response dispositions are:

- `MFA_ENROLLMENT_REQUIRED`
- `MFA_CHALLENGE_REQUIRED`

Password success alone never creates a full protected-control session while Sprint 30 enforcement is armed.

## Enrollment delivery

Exact routes:

- `POST /auth/mfa/totp/enrollment/start`
- `POST /auth/mfa/totp/enrollment/confirm`

Both are runtime-scoped, feature-scoped, CSRF-protected by the existing web stack, and rate limited.

Enrollment start accepts no application payload fields. It returns the restricted provisioning material with no-store cache headers.

Enrollment confirmation accepts exactly one application field:

`code`

A successful confirmation invalidates the pending session and regenerates session/CSRF state. It does **not** establish a full authenticated session. The principal must perform a fresh password login before moving to the challenge state.

## Challenge delivery

Exact route:

`POST /auth/mfa/totp/challenge`

The route accepts exactly one application field:

`code`

A successful challenge:

1. verifies a candidate time step through the provider adapter;
2. atomically consumes a strictly newer step;
3. reuses the already server-verified pending organizational context;
4. invalidates pending session state;
5. regenerates session/CSRF state;
6. writes the existing five full context keys;
7. writes `oneqay.auth.mfa_verified_at`.

No alternate challenge or bypass route exists.

## Policy-administration enforcement

`RequirePolicyAdministrationSessionContextMiddleware` retains its existing tenant/identity/organization/outlet/device revalidation.

When the Sprint 30 feature arm is enabled, the middleware additionally requires positive integer `oneqay.auth.mfa_verified_at` evidence.

The marker is necessary but not sufficient: the existing organizational context still has to revalidate successfully.

Legacy full sessions without MFA evidence therefore fail closed for policy-administration access once Sprint 30 enforcement is armed.

## Error privacy

HTTP MFA verification failures use the generic error code:

`MFA_VERIFICATION_FAILED`

The delivery layer does not distinguish externally between incorrect OTP, replay, stale factor state, cross-tenant ciphertext mismatch, or authorization-state failure.

Internal domain violations remain bounded and secret-free.

## Regression contract

The dedicated workflow:

`.github/workflows/sprint30-privileged-totp-mfa-regression.yml`

must enforce:

- exact 46-path source envelope;
- sorted-path SHA-256 fingerprint `95daaf86ba93ae797fccf3825d65d27acd4f71ee58916898a16fbc83d432a5ce`;
- migration #9 additive-only contract and immutable migrations #1–#8;
- exact Composer dependency/lock references;
- Composer high/critical advisory blocking;
- Application framework independence;
- provider-only OTPHP awareness;
- fixed TOTP profile;
- encrypted tenant/identity binding;
- row-lock/monotonic replay controls;
- exact session vocabulary and routes;
- secret-leakage denial;
- Preview/Production/updater separation;
- executable Sprint30 end-to-end regression;
- preserved Sprint21–Sprint29 and M7 tenant/identity regressions.

The 13 historical workflows modified by this candidate recognize only the exact Sprint30 successor fingerprint for shape compatibility. Legacy shape-only guards may defer to the dedicated Sprint30 workflow only for that exact fingerprint; their executable historical regressions remain enabled.

## Explicit non-authority

Sprint 30 does not authorize:

- password change or rotation;
- password reset, forgot-password, or password recovery;
- TOTP recovery or backup codes;
- factor reset, replacement, deletion, or revocation;
- more than one TOTP factor per identity;
- emergency protected-control recovery;
- arbitrary administrator password setting;
- passkeys or WebAuthn;
- OAuth, OIDC, SAML, or federation;
- bearer/API tokens;
- Production authentication activation;
- Technical Preview credential/session/MFA activation;
- migration #10;
- updater activation;
- Production deployment or release.

## Publication condition

This source remains a candidate until all exact-head workflows succeed and the Product Owner grants merge authority for that exact candidate head.

No independent review is required after exact Product Owner authority under the current repository operating instruction; race-safe exact-head qualification and merge checks remain mandatory.
