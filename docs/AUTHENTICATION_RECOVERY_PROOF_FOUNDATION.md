# Authentication Recovery Proof Foundation

> Status: Sprint 32 bounded source foundation
> Attribution: Lab | zefry

## Purpose

Sprint 32 implements only the first bounded Authentication Recovery / JRN-003 access-recovery slice:

**user-held single-use recovery code -> restricted recovery session**.

It does not implement password reset, password change, password overwrite, automatic login, full-session establishment from recovery proof, MFA/TOTP recovery, factor replacement, protected-control recovery, support bypass, passkeys, federation, API-token authentication, deployment, or release activation.

## Runtime and feature boundary

Recovery delivery is source-default disabled with `ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=false` and is available only when explicitly armed in Local/Test/CI.

The fixed restricted-session lifetime is 600 seconds. It is not environment-configurable.

Technical Preview remains NO_SCHEMA_CHANGE and does not expose the recovery routes or apply migration #10.

Production remains NO-GO / NOT AUTHORIZED and does not expose the recovery routes or apply migration #10.

The updater remains disabled/unwired.

## Recovery-code contract

A successful authenticated rotation returns exactly eight new codes. Each code has the exact form:

`rq1.<22-character-selector>.<43-character-secret>`

The selector is generated from 16 random bytes and is an opaque non-secret lookup locator. The secret is generated from 32 random bytes and is Restricted authentication material. Both are encoded as unpadded base64url.

Only `hash('sha256', <secret>)` is stored as durable verification material. Verification recomputes that digest and uses `hash_equals`. Plaintext complete codes and plaintext secret components are never persisted.

Recovery codes do not have a wall-clock expiry in Sprint 32. A code remains valid only until it is consumed or revoked by a later successful rotation.

## Rotation

`POST /auth/recovery/codes/rotate` requires:

- Local/Test/CI runtime;
- the dedicated recovery feature arm;
- a current full first-party session;
- no pending-MFA or restricted-recovery session state;
- server-derived identity, tenant, organization, outlet and device context;
- exactly one request field after CSRF handling: `password`;
- fresh password verification through the published first-party credential verifier;
- 5/minute and 20/hour throttling.

Inside one persistence transaction the repository locks the identity, re-checks recovery eligibility, revokes prior unused codes, inserts exactly eight fresh code rows, and writes one secret-free `codes_rotated` audit record.

Rotation does not invalidate, upgrade, or broaden the current full session.

## Proof

`POST /auth/recovery/proof` accepts exactly one request field after CSRF handling: `recovery_code`.

Proof is allowed only from a clean anonymous browser session. The selector locates one candidate row, that row is locked, the secret digest is verified, durable eligibility is re-checked, exactly one active code is atomically consumed, and one secret-free `proof_succeeded` audit record is written in the same transaction.

The conditional single-row consume combined with row locking makes replay and concurrent same-code proof fail closed with at most one winner.

Failures are generic and do not disclose selector existence, identity existence, tenant, password-credential state, protected-control state, or TOTP state.

## Eligibility

The non-privileged Sprint 32 recovery slice requires all of the following durable facts:

1. the tenant-scoped identity exists;
2. an existing first-party password credential exists;
3. there is no assignment to `authorization-policy-administrator`;
4. there is no TOTP factor row with non-null `confirmed_at_unix`.

The recovery repository performs the narrow TOTP-state query directly. It never reads, decrypts, copies, replaces, or returns the TOTP secret.

## Restricted recovery session

Successful proof invalidates/rotates the browser session, regenerates the CSRF token, and writes only:

- `oneqay.auth.recovery.tenant_id`;
- `oneqay.auth.recovery.identity_id`;
- `oneqay.auth.recovery.state` = `password_reset_required`;
- `oneqay.auth.recovery.proved_at`;
- `oneqay.auth.recovery.expires_at` = proved-at + 600.

It writes none of the five Sprint 27 full-session keys, pending-MFA keys, `mfa_verified_at`, or Sprint 31 step-up evidence.

No Sprint 32 route consumes `password_reset_required` to mutate a password. The restricted session is intentionally inert until a separately governed password-reset execution gate is authorized.

## Migration #10

Sprint 32 adds the forward-only source migration `0000_00_00_000010_create_identity_recovery_codes.php`.

It creates only:

- `oneqay_identity_recovery_codes`, containing opaque selector, non-reversible secret digest, single-use/revocation timestamps and identity binding; and
- `oneqay_identity_recovery_audit`, containing only secret-free successful transition evidence.

Canonical migrations #1 through #9 remain unchanged. Migration #10 is a Local/Test/CI source qualification artifact only and is not authorized for Technical Preview or Production application.

## Security preservation

Sprint 32 introduces no Composer/npm dependency, no email/SMS recovery provider, no custom password/TOTP cryptography, no credential mutation, no factor mutation, no support master code, and no hidden recovery bypass.

Historical executable regression workflows remain active through Sprint 31, while exact Sprint 32 successor recognition is bounded to the published 31-path fingerprint.

JRN-003 remains partially resolved: proof foundation is implemented, while password-reset execution, privileged recovery, MFA recovery, post-recovery session revocation policy, and other recovery channels remain separately governed.
