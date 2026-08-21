# Sprint 34 — Authenticated In-Session Password Change Foundation

Attribution: **Lab | zefry**

## Scope

This foundation implements the bounded Sprint 34 source contract published by the schema/source-envelope gate. It is restricted to Local/Test/CI and does not authorize Technical Preview or Production schema mutation, deployment, release, or updater activation.

## Credential epoch

Migration #11 adds `oneqay_identity_password_credentials.credential_epoch` as the generic durable password-credential version. The migration is additive and forward-only, preserves migrations #1 through #10 unchanged, and backfills each exact tenant/identity credential from the historical count of `password_reset_completed` recovery events.

After migration #11 the password-credential row is the sole current credential-epoch authority. Normal login captures that value. Legacy session state with no epoch remains valid only while the durable epoch is zero. Stale, malformed, negative, future, missing-credential, or otherwise unrepresentable state fails closed.

## Authenticated password change

The Local/Test/CI route is exactly `POST /auth/password/change` with normal CSRF plus `throttle:5,1` and `throttle:20,60`.

The closed request payload contains only:

- `current_password` — required opaque bytes, non-empty, maximum 4096 bytes;
- `new_password` — required opaque bytes, 12 through 4096 bytes;
- `totp_code` — optional and accepted only when the existing server-side privileged TOTP policy requires a fresh challenge.

Tenant, identity, organizational context, credential epoch, privilege selection, recovery proof, and credential selectors are server-owned and cannot be supplied by the caller.

## Transaction boundary

A successful change requires:

1. a valid full first-party session with an exact current credential epoch;
2. early generic current-password verification;
3. when the existing privileged TOTP arm is active, a fresh confirmed TOTP challenge whenever protected-control policy requires it;
4. a locked exact credential row inside `PersistenceTransaction`;
5. exact epoch equality after the lock;
6. a second `password_verify` of the current password against the locked hash;
7. same-password denial;
8. replacement hashing with `PASSWORD_DEFAULT`;
9. update of exactly one existing credential row and increment of `credential_epoch` by exactly one;
10. revocation of every unused and unrevoked pre-change recovery code for the same tenant and identity.

The operation never inserts, upserts, bootstraps, deletes, truncates, administratively sets, or otherwise recreates a password credential.

## Recovery preservation

Sprint 33 recovery-bound password reset remains recovery-specific. A successful reset now updates the password hash and increments the generic credential epoch in the same locked credential mutation while retaining exactly one `password_reset_completed` audit event and all existing recovery replay, protected-control, confirmed-TOTP, code-binding, and session-invalidation rules.

Normal authenticated password change never fabricates a recovery audit event.

## Session disposition

After a successful authenticated password change, the current session is invalidated and its CSRF token is regenerated. The old session is never rewritten with the new epoch and no automatic login, MFA evidence, step-up evidence, recovery state, or organizational context is synthesized. A fresh normal login with the replacement password is required and captures the new durable epoch.

## Security properties

Passwords and TOTP codes are sensitive parameters and are never written to logs, responses, sessions, audit rows, telemetry, or exception messages. Success and failure responses are secret-free and carry the canonical correlation ID. Password-change failures collapse into one generic failure family.

## Preserved boundaries

- migrations #1 through #10: immutable;
- migration #11: Sprint34 Local/Test/CI source only;
- no migration #12;
- no new environment variable or Sprint34 feature flag;
- Composer/npm manifests and lockfiles unchanged;
- Technical Preview: `NO_SCHEMA_CHANGE`;
- Production: `NO-GO / NOT AUTHORIZED`;
- updater: `DISABLED / UNWIRED`;
- deployment and release: not authorized.
