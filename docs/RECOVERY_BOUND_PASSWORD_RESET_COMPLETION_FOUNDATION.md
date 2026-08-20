# Sprint 33 Recovery-Bound Password Reset Completion Foundation

Attribution: Lab | zefry

## Status

Sprint 33 implements the bounded password-reset completion that follows the Sprint 32 authentication-recovery proof foundation. The completion remains fail closed, tenant scoped, identity scoped, recovery-code scoped, and restricted-session scoped.

This source completion does not authorize deployment, activation in Technical Preview, activation in Production, or updater wiring.

## Canonical implementation boundary

The Sprint 33 source candidate is based on canonical commit `c89baa55318dca230cd0ef792df80e3d54b8165d` and canonical tree `64ca0cffc6067ccd03632b15af1786d21d00e463`.

No migration is added or changed. Migrations #1 through #10 remain immutable. Migration #11 is not introduced or authorized.

The existing source defaults remain unchanged:

- `ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=false` remains fail closed by default.
- `ONEQAY_PERSISTENCE_ENABLED=false` remains fail closed by default.
- the restricted recovery-session TTL remains exactly 600 seconds and is not environment configurable.

## Server-bound recovery proof

A successful recovery proof carries the server-owned durable `code_id` of the exact consumed recovery code into the restricted recovery session.

The restricted evidence includes:

- tenant id;
- identity id;
- recovery `code_id`;
- recovery state `password_reset_required`;
- proof time; and
- exact expiration time equal to proof time plus 600 seconds.

`code_id` is exactly 32 lowercase hexadecimal characters. It is never accepted from reset request input, never returned as reset authority to the caller, and never added to the canonical Sprint 27 full-context session-key list.

The password-reset request accepts exactly one business field: `password`.

## Credential epoch

Sprint 33 introduces separate session evidence named `oneqay.auth.credential_epoch`. It is not one of the five canonical Sprint 27 full-context keys.

The durable epoch is derived without schema change as the count of `password_reset_completed` audit rows for the exact tenant and identity in `oneqay_identity_recovery_audit`.

A normal successful first-party login captures the current durable epoch. A missing legacy epoch is accepted only while the durable epoch is zero. A malformed, negative, stale, or invented future epoch fails closed. Once a reset advances the durable epoch, an older authenticated session cannot regain authority merely because the recovery feature is later disabled.

## Password input contract

The replacement password is opaque byte input. Sprint 33 does not trim, normalize, case-fold, or otherwise transform it before hashing.

The accepted byte length is inclusive from 12 through 4096 bytes. Eleven bytes fail. Twelve bytes succeed when all other authorization conditions hold. 4096 bytes succeed when all other authorization conditions hold. 4097 bytes fail.

The replacement credential is hashed with PHP `PASSWORD_DEFAULT`.

## Atomic completion transaction

Password-reset completion is performed inside the existing persistence transaction boundary and revalidates durable server-owned state before mutation.

The transaction requires all of the following:

- the exact tenant exists;
- the exact identity exists inside that tenant;
- the exact recovery `code_id` belongs to that tenant and identity;
- that recovery code has already been consumed by the proof path;
- a matching secret-free `proof_succeeded` audit row exists;
- no prior `password_reset_completed` audit row exists for that exact proof;
- an existing password credential row exists for the exact tenant and identity;
- the identity is not a protected-control principal; and
- the identity does not have a confirmed privileged TOTP factor.

The credential mutation is update only. Sprint 33 does not recreate a missing credential row and does not use insert, upsert, update-or-insert, or delete as a substitute for the existing credential.

On success the transaction:

1. hashes and updates the existing password credential;
2. revokes all other unused, unconsumed recovery codes for the same tenant and identity; and
3. appends exactly one secret-free `password_reset_completed` audit event bound to the consumed `code_id` and correlation id.

The password, password hash, recovery-code secret, TOTP secret, session id, CSRF token, and other authentication secrets are not written to the recovery audit.

Replay of an already completed proof fails. Concurrent use of the same proof has at most one successful completion.

## Restricted-session collision boundary

The reset endpoint accepts only the restricted recovery session. It fails closed if the session also contains any canonical full-authentication evidence, pending-MFA evidence, privileged MFA verification evidence, privileged step-up verification evidence, step-up scope, step-up context, or credential-epoch evidence.

Failure does not extend the restricted-session expiration time and does not consume reset authority by fabricating a completion event.

Expired recovery sessions fail closed.

## Successful reset disposition

A successful reset invalidates the restricted session and regenerates the CSRF token.

A successful reset does **not** automatically authenticate the identity. It creates none of the canonical five full-context keys, no privileged MFA evidence, no step-up evidence, and no credential-epoch evidence.

The identity must perform a fresh normal login with the replacement password. That fresh normal login captures the newly advanced durable credential epoch.

## Explicit exclusions

Sprint 33 does not implement or authorize:

- email reset delivery;
- SMS reset delivery;
- password-reset links or tokens separate from the existing recovery-code proof;
- TOTP recovery, replacement, disablement, secret read, secret decryption, or mutation;
- protected-control-principal recovery bypass;
- automatic login after password reset;
- caller-selected tenant, identity, recovery code id, role, MFA state, step-up state, or epoch;
- migration #11;
- dependency changes;
- environment-file changes;
- Technical Preview schema activation;
- Production authentication activation; or
- updater wiring.

## Deployment and release boundaries

Technical Preview remains **NO_SCHEMA_CHANGE**. Sprint 33 does not place recovery password-reset capability into Technical Preview delivery.

Production remains **NO-GO / NOT AUTHORIZED**. Sprint 33 source qualification does not authorize Production activation.

The updater remains **DISABLED / UNWIRED** for this capability.

## Qualification contract

The dedicated Sprint 33 repository-native regression must prove the exact authorized 39-path source envelope and exact sorted-path SHA-256 fingerprint `04a1177c12712183a7dda4ae81be1356c0e41294533336c9f999d376c224712a`.

Qualification includes executable coverage for proof binding, password byte boundaries, update-only credentials, replay rejection, remaining-code revocation, secret-free audit, restricted-session collision denial, exact TTL behavior, protected-control denial, confirmed-TOTP denial, stale/missing/negative/future credential epoch behavior, repeated reset epoch advancement, fresh-login requirement, and preservation of historical Sprint and M7 regressions.

Source qualification does not imply Ready or Merge authority.
