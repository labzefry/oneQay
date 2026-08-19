# Privileged Reauthentication / Step-Up Session Freshness Foundation

Status: Sprint 31 source candidate.

Attribution: Lab | zefry

## Boundary

Sprint 31 adds a source-default-disabled Local/Test/CI-only privileged step-up boundary for the existing policy-administration mutation surface. It does not activate Preview or Production authentication.

The feature arm is `ONEQAY_PRIVILEGED_STEP_UP_ENABLED=false` by default. Freshness is fixed in source at 300 seconds. Migration #10 is not introduced; migrations #1-#9 remain unchanged.

## Verification

Successful reauthentication requires both the existing tenant-scoped first-party password verifier and the existing Sprint 30 replay-safe confirmed TOTP challenge. No password hashing, TOTP cryptography, secret storage, recovery, or factor lifecycle is duplicated.

The endpoint is `POST /auth/reauthenticate/privileged`. It accepts exactly `password` and six-digit `code` input after CSRF removal and never accepts identity, tenant, organization, outlet, device, role, permission, or scope selectors.

## Session evidence

A successful step-up invalidates/rotates the browser session, regenerates CSRF state, rewrites the re-verified canonical five full-session context facts, preserves the existing login-level `mfa_verified_at`, and writes separate step-up evidence:

- `oneqay.auth.step_up_verified_at`
- `oneqay.auth.step_up_scope` = `policy_administration`
- `oneqay.auth.step_up_context` with server-derived identity, tenant, organization, outlet and device values

`FirstPartySessionKeys::all()` remains the Sprint 27 canonical five-key context contract.

## Enforcement

`RequirePolicyAdministrationSessionContextMiddleware` is the only Sprint 31 enforcement point. When step-up is disabled it preserves the Sprint 30 behavior. When enabled it additionally requires the TOTP arm, a currently confirmed protected-control factor, exact scope/context binding, a positive server-issued timestamp, no future-clock evidence, and age less than or equal to 300 seconds. Durable policy authorization continues to run on every mutation.

## Explicit exclusions

No migration #10, schema change, dependency change, recovery, recovery code, factor reset/replacement/deletion, multiple factor, passkey, federation, API token, support impersonation, updater activation, Preview activation, Production activation, deployment authority, or Release authority is included. JRN-003 remains unresolved.
