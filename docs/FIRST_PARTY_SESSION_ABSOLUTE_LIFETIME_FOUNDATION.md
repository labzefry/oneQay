# First-Party Session Absolute Lifetime Foundation

Attribution: Lab | zefry

## Purpose

Sprint38 adds a fixed absolute lifetime to the existing durable first-party logical session authority without creating a second session authority, token family, public selector, route, audit event, persistence model, or schema revision.

The foundation remains restricted to **Local/Test/CI**. It does not activate Technical Preview or Production authentication and does not authorize deployment, release, or updater installation.

## Frozen lifetime contract

The established sliding idle lifetime remains exactly **7200 seconds**.

Sprint38 adds an absolute lifetime of exactly **43200 seconds (12 hours)** from the server-owned durable `issued_at_unix` value:

- `absolute_deadline = issued_at_unix + 43200`;
- initial effective expiry is `min(issued_at_unix + 7200, absolute_deadline)`;
- a later touch may set expiry only to `min(now + 7200, absolute_deadline)`;
- `issued_at_unix` is immutable for the lifetime of the logical authority;
- caller-supplied timestamps never become lifetime authority;
- framework-session rotation, request activity, inventory, revocation operations, privileged step-up evidence, or repeated touches cannot renew a logical authority beyond its original absolute deadline.

The existing expiry equality convention is preserved. An authority is still valid when `now == effective_expiry` and is denied when `now > effective_expiry`. Sprint38 applies the same convention to the absolute deadline.

## Configuration boundary

The existing `session_control` configuration owns both governed lifetime values:

- `idle_ttl_seconds => 7200`;
- `absolute_ttl_seconds => 43200`.

Both values are fixed in source and are not environment-configurable. The existing feature arm remains `ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false` by default.

The session-control capability is operational only when the existing runtime, persistence, feature-arm, idle-TTL, and absolute-TTL checks all pass. A lifetime mismatch fails closed.

## Durable authority behavior

The existing migration #13 row remains the sole durable first-party session authority record. Sprint38 reuses:

- `issued_at_unix` as the server-owned origin of the absolute deadline;
- `last_seen_at_unix` for bounded activity evidence;
- `expires_at_unix` for the current effective expiry.

The repository interface and Laravel repository remain unchanged. The application service computes the effective expiry before invoking the existing repository `issue` and `touch` operations.

For a pre-Sprint38-style durable row whose stored `expires_at_unix` extends beyond the new absolute deadline, the service treats `issued_at_unix + 43200` as authoritative. Inventory exposes only the effective capped expiry and excludes the row after the absolute deadline even if the stored expiry is later.

Clock movement before a durable authority's server-owned `issued_at_unix` is rejected fail-closed rather than being allowed to increase usable lifetime.

## Preserved authorization and revocation semantics

Sprint38 does not change:

- exact tenant + identity ownership;
- organization/outlet/device context matching;
- credential-epoch or TOTP factor-epoch validation;
- revoke-one, revoke-others, revoke-all, or canonical logout semantics;
- privileged `session_control` step-up scope and its 300-second freshness requirement;
- ordinary-identity behavior;
- revocation monotonicity and replay behavior;
- secret-minimal audit records.

An authority that is revoked, idle-expired, absolute-expired, context-invalid, epoch-stale, runtime-disabled, persistence-disabled, or otherwise invalid cannot be reactivated by touch, replay, concurrency, MFA evidence, step-up evidence, inventory, or framework-session activity.

## HTTP and audit contract

Sprint38 adds **no HTTP route**, route name, controller action, request payload, caller selector, or public session identifier.

Sprint38 adds **no audit event**. Existing first-party session audit vocabulary remains unchanged:

- `session_issued`;
- `session_revoked`;
- `other_sessions_revoked`;
- `all_sessions_revoked`;
- `session_logout`.

Absolute expiration is authority-validity behavior, not a new destructive mutation or audit-event family.

## Schema and migration boundary

Sprint38 classification is **NO_SCHEMA_CHANGE**.

Migration #13 already contains the durable fields required for the selected concern. Therefore:

- migration #14 is **NOT REQUIRED**;
- migration #14 is **NOT SELECTED**;
- migration #14 is **NOT AUTHORIZED**;
- migrations #1 through #13 remain immutable.

No table, column, index, foreign key, enum, trigger, or schema rewrite belongs to Sprint38.

## Regression evidence

The dedicated Sprint38 regression proves:

- initial effective expiry remains bounded by the 7200-second idle lifetime;
- continuous valid activity can slide the idle expiry but cannot exceed the fixed 43200-second absolute deadline;
- equality at the absolute deadline preserves the established expiry convention;
- one second beyond the absolute deadline is denied;
- `issued_at_unix` remains immutable through touch;
- inventory caps a durable expiry that exceeds the absolute deadline;
- an absolute-expired durable row is not an active inventory item;
- server clock rollback before `issued_at_unix` fails closed;
- idle- or absolute-TTL configuration mismatch fails closed;
- migration #14 does not exist and migrations #1-#13 remain the exact migration set;
- no new HTTP route or audit event is introduced;
- Sprint36 inventory/revocation and Sprint37 all-session termination behavior remain preserved.

## Deployment boundary

Technical Preview remains **NO_SCHEMA_CHANGE** and receives no Sprint38 authentication activation.

Production remains **NO-GO / NOT AUTHORIZED**.

Updater remains **DISABLED / UNWIRED**.

Deployment and release remain outside Sprint38 authority.
