# AI Post-Sprint 30 Canonical State

## Purpose

This document records the factual canonical oneQay program state immediately after publication of Sprint 30 — Privileged TOTP MFA Foundation. It is stable publication provenance, not a permanently-current live-head substitute.

Attribution: **Lab | zefry**

## Publication chain

| Publication | Result |
| --- | --- |
| Sprint 29 source PR #195 | `9f6e9529aa9fe87e74de9da962a5533e04922e7f` |
| Post-Sprint29 state PR #196 | `3a675d54b2addd949edfb3e8e3562296575d48ec` |
| Sprint 30 selection gate PR #197 | `fee400de29ae5875da89035e8e63406b225b3620` |
| Sprint 30 source-envelope gate PR #198 | `3be0b093f3351b59c6cdc4cce1bd9d5ac82ddbf2` |
| Sprint 30 authorized exact source head | `29b5a2e7194e21e9a8734a5d1a06ea08a1d6be01` |
| Sprint 30 source publication PR #199 | `6d41755eba4030c2b0b7c4f3b7a5806b761b0ad7` |
| Sprint 30 publication tree | `bf1d56af5524e77919833bd64b585cdca84af55d` |
| Sprint 30 exact 46-path fingerprint | `95daaf86ba93ae797fccf3825d65d27acd4f71ee58916898a16fbc83d432a5ce` |
| Sprint 30 qualification | **22/22 workflows SUCCESS** |

## Published identity/control progression

The bounded publication chain now includes Sprint 21 durable role/permission policy, Sprint 22 policy administration, Sprint 23 initial tenant-admin provisioning, Sprint 24 protected-control administrator lifecycle, Sprint 25 policy-administration delivery, Sprint 26 password credential verification, Sprint 27 first-party login/session, Sprint 28 initial password enrollment, Sprint 29 first-control-principal bootstrap credential foundation, and Sprint 30 privileged TOTP MFA foundation.

These are bounded repository facts and do not imply Production readiness.

## Sprint 29 preserved boundary

Sprint 29 provides `oneqay:identity:first-control-credential-bootstrap {tenant_id}` under its Local/Test/CI feature gate. The target is derived from immutable successful Sprint 23 provisioning evidence and must retain the exact protected-control role/permission. The bootstrap inserts exactly one password credential, creates no session, and does not implement credential overwrite, reset, recovery, deletion, Preview activation, or Production activation.

## Sprint 30 dependency and TOTP profile

Sprint 30 directly pins `spomky-labs/otphp` **11.5.0**. oneQay uses the library adapter rather than implementing custom TOTP/HMAC/Base32/provisioning cryptography.

Published profile: RFC 6238 TOTP, SHA-1, six ASCII decimal digits, 30-second period, 20-byte generated secret before Base32 representation, ±1 verification step, issuer `oneQay`, and deterministic tenant+identity user-visible label.

## Migration #9 and factor durability

Canonical migrations are exactly **#1 through #9**. Migration #9 creates `oneqay_identity_totp_factors` with exact `(tenant_id, identity_id)` ownership, encrypted `secret_ciphertext`, creation time, nullable confirmation time, and nullable monotonic `last_accepted_time_step`.

Published factor states are absent, pending, and confirmed. Allowed mutations are one pending insert, pending→confirmed transition that consumes the matched step, and confirmed challenge that advances the accepted step monotonically. Secret overwrite/replacement, factor deletion/reset, multiple factor rows, recovery-code tables, and destructive down behavior are not published.

## Secret and replay protection

TOTP secret material is Restricted. The database stores encrypted ciphertext only. Encryption is implemented behind the Application port, with payload context binding to version + tenant + identity + secret and key material remaining outside database ciphertext.

Confirmation and challenge consume the matched TOTP time step. A later challenge must advance strictly beyond the stored accepted step; transactional row locking/conditional mutation prevents concurrent same-step double acceptance.

## Session state machine

Existing Sprint 27 full authenticated identity/context keys remain the full-session baseline. Sprint 30 adds restricted pre-authentication identity/context plus `enrollment_required` or `challenge_required` MFA state. Restricted state must not contain the full authenticated session identity/context keys.

Protected-control full-session evidence additionally includes `oneqay.auth.mfa_verified_at`, but that marker is evidence only and does not replace durable tenant, organization, role, permission, or protected-control authorization checks.

With the feature arm enabled, password success for a protected-control principal yields restricted enrollment/challenge state rather than a full privileged session. Enrollment confirmation requires fresh login. Successful challenge rotates to full session only after atomic step consumption. Logout clears full, pending, and MFA evidence state.

## Runtime and release boundaries

- Sprint 29–30 identity delivery: **Local/Test/CI only**.
- `ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED=false` by source default.
- Technical Preview: **`NO_SCHEMA_CHANGE`** and does not execute migration #9.
- Production: **`NO-GO / NOT AUTHORIZED`**.
- Updater: **`DISABLED / UNWIRED`**.
- Durable persistence source default: `ONEQAY_PERSISTENCE_ENABLED=false`.

## Explicit non-authority

Publication does not authorize password change/reset/recovery/rotation/revocation, MFA recovery, TOTP replacement/deletion/reset, recovery codes, multiple factors, WebAuthn/passkeys, federation, Android/API token implementation, Technical Preview auth/MFA activation, Production auth/MFA activation, Production schema execution, updater activation, deployment, Release, or Production.

**JRN-003 remains UNRESOLVED.**

## Next governed concern

**Privileged Reauthentication / Step-Up Session Freshness Foundation** is the next logical candidate and is **CANDIDATE / NOT AUTHORIZED**.

DEC-006 requires risk-based reauthentication/step-up for sensitive operations and states that sensitive operations may require recent reauthentication. Sprint 30 establishes baseline privileged MFA but does not define operation-specific recent-authentication semantics.

Before implementation, a separate gate must freeze protected operation scope, required factor combination by risk class, freshness evidence/expiry, relation to `mfa_verified_at`, session rotation/re-evaluation, durable authorization re-verification, throttling/generic failure/replay behavior, Local/Test/CI route scope, exact source/schema/workflow envelope, and historical regression preservation. Migration #10 is not assumed.

## Live GitHub rule

SHAs in this file are publication provenance. Fresh GitHub Minimal Delta Verification remains mandatory before every future branch, implementation mutation, Ready transition, or Merge transition.
