# First-Party All-Session Termination Foundation

Attribution: Lab | zefry

## Purpose

Sprint37 adds a bounded tenant-scoped **revoke-all** capability to the durable first-party logical session authority introduced by Sprint36. The capability is intentionally limited to the exact current tenant and identity derived from authenticated server-owned session context.

The implementation is Local / Test / CI only. It does not grant Technical Preview, Production, updater, deployment, or release authority.

## Security contract

`POST /auth/sessions/revoke-all` is named `auth.sessions.revoke-all` and accepts no caller-controlled tenant, identity, authority identifier, public handle, target list, filter, device selector, organization selector, outlet selector, or arbitrary scope as authority. Framework CSRF input remains permitted.

The request executes under the existing first-party session-control boundary:

- `session.active` proves the current durable logical authority is still active;
- `session.control-mutation` applies the existing privileged mutation context;
- existing `throttle:5,1` and `throttle:20,60` limits remain in force;
- the existing `ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false` feature arm is reused;
- `oneqay.session_control.enabled` remains the canonical configuration switch;
- idle TTL remains exactly 7200 seconds;
- execution is restricted to Local / Test / CI with durable persistence available.

## Exact owner semantics

The application service first validates the current durable logical authority against the server-derived tenant, identity, organization, outlet, device, credential epoch, factor epoch, revocation state, and idle expiry.

Only after current authority validation succeeds does the repository execute revoke-all for the exact tenant + identity owner. The durable update:

- includes the current logical authority;
- includes every other active, unrevoked logical authority for the same tenant + identity;
- excludes already revoked authorities;
- excludes expired authorities from the active target set;
- never crosses into another tenant;
- never crosses into another identity, including a same-text identity identifier in another tenant.

The mutation is monotonic. Revoked authorities are never returned to active state. A repeated repository transition after the owner set is already revoked affects zero rows and does not create a duplicate transition audit.

## Durable-before-local terminal ordering

The HTTP controller invalidates the current Laravel session only **after** the durable revoke-all operation returns successfully.

After durable success the controller:

1. invalidates the current Laravel session;
2. regenerates the CSRF token;
3. returns `204 No Content` with private no-store caching.

A failure before durable success does not perform successful terminal handling and returns the existing generic session-authority denial behavior.

## Privileged step-up preservation

Sprint37 does not introduce another MFA mechanism or another privilege scope.

For a protected privileged identity, the existing `session_control` step-up scope remains mandatory and freshness remains exactly **300 seconds**. Step-up context remains bound to server-derived identity, tenant, organization, outlet, and device context.

For an ordinary identity for which protected-control MFA is not required, Sprint37 does not invent a privileged challenge. The existing session-control mutation middleware permits the request to continue after its normal feature/runtime/context checks.

Credential epoch and TOTP factor epoch are validation evidence only. Revoke-all does not mutate either epoch.

## Audit contract

Sprint37 adds one bounded audit event to the existing migration #13 first-party session audit table:

`all_sessions_revoked`

The event is written in the same durable transaction as the owner-scoped revocation and only when at least one active authority makes a durable transition to revoked state.

Audit content remains server-derived and secret-free. It must not contain passwords, password hashes, TOTP secrets, recovery codes, session secrets, cookies, CSRF tokens, bearer tokens, public session handles, or other authentication secret material.

The existing audit events remain preserved:

- `session_issued`
- `session_revoked`
- `other_sessions_revoked`
- `session_logout`

## Preserved Sprint36 behavior

Sprint37 preserves the existing session-control capabilities:

- active session inventory;
- revoke one remote session by opaque public handle;
- revoke all other sessions while preserving current authority;
- canonical current-session logout;
- credential epoch invalidation;
- factor epoch invalidation;
- 7200-second idle authority lifetime;
- tenant + identity ownership isolation;
- Local / Test / CI runtime restrictions;
- disabled-by-default session-control activation.

The historical Sprint36 regression is evolved only to recognize the newly governed revoke-all route and `all_sessions_revoked` audit event. Its prior inventory, revoke-one, revoke-others, logout, ownership, epoch, migration, runtime, and persistence assertions remain active.

## Schema decision

Sprint37 remains **NO_SCHEMA_CHANGE**.

Migration #13 already contains the durable session authority and audit storage required by revoke-all. Therefore:

- migrations #1 through #13 remain immutable;
- migration #14 is not required;
- migration #14 is not selected;
- migration #14 is not authorized;
- no table, column, index, foreign key, trigger, or schema rewrite is introduced by Sprint37.

## Regression evidence

The dedicated Sprint37 regression proves:

- exact tenant + identity owner derivation;
- current authority included in revoke-all;
- every active exact-owner authority revoked;
- another identity untouched;
- another tenant untouched even with the same identity string;
- caller-supplied owner selectors denied before durable mutation;
- durable transition completed before local framework session invalidation;
- CSRF token regenerated after durable success;
- replay converges without resurrection or duplicate transition audit;
- ordinary identity receives no invented privileged MFA requirement;
- protected privileged identity requires fresh `session_control` step-up;
- stale privileged step-up is denied;
- disabled feature fails closed;
- disallowed runtime fails closed;
- credential epoch is not mutated;
- factor epoch is not mutated;
- audit remains secret-free;
- route middleware retains active-session, session-control mutation, and throttle boundaries;
- migration set remains exactly #1 through #13;
- Sprint36 regression remains green;
- full application regression remains green.

## Activation boundary

Technical Preview remains `NO_SCHEMA_CHANGE`.

Production remains `NO-GO / NOT AUTHORIZED`.

Updater remains `DISABLED / UNWIRED`.

Deployment remains not authorized.

Release remains not authorized.

No authority is granted here for API/mobile token lifecycle, WebAuthn/passkeys, federation/SSO, cross-tenant identity-wide logout, administrator revocation of another identity, account suspension, trusted-device state, IP/browser fingerprint authority, risk scoring, support impersonation, or break-glass administration.
