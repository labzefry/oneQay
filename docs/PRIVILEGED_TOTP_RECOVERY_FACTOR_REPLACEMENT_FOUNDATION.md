# Sprint 35 — Privileged TOTP Recovery & Factor Replacement Foundation

Attribution: **Lab | zefry**

## Published source contract

Sprint35 implements the source envelope selected by the published schema/source-envelope gate. The implementation remains bounded to Local/Test/CI and does not authorize Technical Preview, Production, updater activation, deployment, or release.

The trust roots remain deliberately separate:

- password recovery continues to use the Sprint32 `rq1` namespace and password `credential_epoch`;
- privileged TOTP recovery uses the dedicated `mq1` namespace and TOTP `factor_epoch`;
- neither authority accepts the other authority's recovery codes;
- password credential epoch is not advanced solely because a TOTP factor is replaced.

## Migration #12

Migration #12 is forward-only and performs only the selected schema changes:

1. adds non-null unsigned `factor_epoch` with default `0` to `oneqay_identity_totp_factors`;
2. creates `oneqay_identity_totp_recovery_codes` for tenant + identity + factor-epoch-bound single-use recovery codes;
3. creates `oneqay_identity_totp_recovery_audit` for secret-free transition evidence.

Migrations #1–#11 remain immutable.

## Recovery-code lifecycle

Dedicated privileged-TOTP recovery codes are generated as high-entropy `mq1.<selector>.<secret>` values. Only selector and SHA-256 secret digest are durable. Plaintext complete codes are returned only by issuance/rotation.

Rotation requires a current full session, current password proof, current canonical TOTP proof, current password credential epoch, and a confirmed privileged TOTP factor. Rotation revokes all previously unused dedicated TOTP recovery codes before issuing exactly eight fresh codes.

Proof is accepted only from a clean anonymous browser session. The exact code row is locked, digest comparison is constant-time, factor epoch is revalidated, and at most one concurrent consumer can win. Successful proof consumes exactly one code and creates a restricted TOTP-recovery session for exactly 600 seconds.

## Restricted recovery session

The restricted session is bound to tenant, identity, consumed dedicated recovery code id, exact TOTP factor epoch, proof timestamp, and fixed expiration timestamp. It contains no full-session organizational authority, no password credential epoch, no pending-login MFA state, no privileged MFA verification, and no step-up evidence. Failure does not extend expiry.

## Factor replacement

Replacement is update-only. The existing factor row is never deleted to simulate recovery.

`replace/start` creates a fresh secret through the canonical `PrivilegedTotpEngine`. The plaintext secret and provisioning URI are returned only for enrollment. Server continuation state contains an encrypted opaque replacement token bound to tenant, identity, recovery code id, factor epoch, issued timestamp, and new secret.

`replace/confirm` accepts only the new TOTP code. The server opens its own opaque replacement state, validates exact recovery proof and current factor epoch, verifies the new TOTP through the canonical engine, locks the existing factor row, and atomically replaces factor ciphertext, refreshes confirmation state, advances `factor_epoch` exactly once, revokes remaining unused dedicated TOTP recovery codes, and appends one secret-free `factor_replaced` audit event.

Concurrent replacement from the same starting epoch has at most one winner because the durable update is guarded by the exact prior epoch. After success the restricted session is invalidated and CSRF is regenerated. No full session, privileged MFA evidence, or step-up authority is synthesized. A fresh normal password login and a new canonical TOTP challenge are required.

## HTTP surface

Only these Local/Test/CI POST routes are introduced:

- `POST /auth/mfa/recovery/codes/rotate`
- `POST /auth/mfa/recovery/proof`
- `POST /auth/mfa/recovery/totp/replace/start`
- `POST /auth/mfa/recovery/totp/replace/confirm`

Each route uses normal Laravel CSRF semantics plus `5/minute` and `20/hour` throttling. Failures return a generic secret-free error envelope.

## Qualification and preservation

The published frozen envelope still contains 19 allowed paths with fingerprint `aaf7fb11490250d29c68dc7b46b62d2ee2239707ca53e004f9c0652878928e3f`. This implementation changes the 17 semantic implementation paths and deliberately preserves the two historical regression files as exact canonical blobs:

- `apps/web/tests/privileged-totp-mfa.php` = `ce0d0cde5031191dc0fdc383ad71d1e20bacf869`;
- `apps/web/tests/run.php` = `8f74a9fc7db0efce06bf03bc7e5aead644d80948`.

The dedicated Sprint35 workflow fails on any path outside the frozen envelope, requires every semantic implementation path, verifies both historical regression blob SHAs exactly, preserves migration #1–#11 immutability, validates PHP syntax, runs the new Sprint35 recovery regression, then executes both preserved historical regression suites.

Technical Preview remains **NO_SCHEMA_CHANGE**. Production remains **NO-GO / NOT AUTHORIZED**. Updater remains **DISABLED / UNWIRED**. Persistence and authentication recovery remain disabled by default.
