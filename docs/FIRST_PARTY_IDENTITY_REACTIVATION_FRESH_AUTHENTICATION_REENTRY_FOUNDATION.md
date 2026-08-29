# First-Party Identity Reactivation Fresh Authentication Re-entry Foundation

Author by Lab | zefry

## Status

`SPRINT44 SOURCE IMPLEMENTED / LOCAL-TEST-CI ONLY / NOT ACTIVATED`

Sprint44 closes the fresh-authentication re-entry gap after an ordinary first-party identity has been validly reactivated under Sprint43. Reactivation restores only eligibility to attempt authentication. It does not create, restore, resume, regenerate, or otherwise resurrect framework or logical session authority.

## Bounded source decision

The canonical first-party login controller now composes the existing `FirstPartyIdentityEligibilityVerifier` before organizational-context entry, MFA pending-state establishment, credential-epoch capture, logical authority issuance, and framework-session establishment whenever canonical session control is enabled.

The check is fail closed: if current tenant+identity eligibility cannot be proven true, login returns the existing safe `AUTHENTICATION_FAILED` envelope and no new logical or framework authority is established.

No new route, request field, repository contract, provider binding, configuration, schema, migration, authority type, public handle format, or restoration operation is introduced.

## Canonical authentication path

The existing named first-party login route remains authoritative. Its closed payload remains:

- `tenant_id`;
- `identity_id`;
- `password`;
- `organization_id`;
- optional `outlet_id`;
- optional `device_id`.

Caller-provided reactivation state, restore/resume operations, logical authority IDs, public handles, roles, permissions, grants, or session selectors remain rejected.

## Pre-issuance eligibility boundary

After current credential verification and before any session state may be established, the controller requires the existing server-side eligibility verifier to prove `first_party_authentication_enabled = true` for the exact tenant+identity whenever the existing canonical session-control boundary is active.

Unknown identity, missing row, duplicate/ambiguous state, disabled state, malformed persisted value, cross-tenant lookup, persistence failure, disabled persistence, unauthorized runtime, or otherwise unverifiable eligibility therefore fails closed.

This preserves the existing feature/runtime boundary while preventing a disabled identity from obtaining a new logical authority merely because its password remains valid.

## Non-resurrection invariant

Sprint43 reactivation does not mutate historical session rows. Sprint44 fresh login does not clear historical `revoked_at_unix`, does not reuse historical authority IDs, and does not reuse historical public handles.

A successful fresh login continues to use `FirstPartySessionAuthorityService::issue()` and the canonical persistence transaction. The resulting session carries current tenant+identity ownership, current organizational context, current credential epoch, current factor epoch when applicable, fresh timestamps, a newly generated authority ID, a newly generated public handle, and the existing `session_issued` audit evidence.

Historical authorities invalidated by revocation, absolute lifetime, idle lifetime, credential epoch, factor epoch, membership, organizational access, outlet/device access, or identity eligibility remain invalid.

## Disable-reactivate-login-disable convergence

The bounded regression proves the security cycle:

1. a historical target authority is already revoked;
2. while identity eligibility is false, current credentials cannot create a new logical or framework authority;
3. the already-qualified Sprint43 eligibility transition to true creates no session authority by itself;
4. wrong credentials, cross-tenant credential borrowing, and invalid organization context create no authority;
5. valid fresh authentication creates a different authority ID and public handle and records `session_issued`;
6. the old revoked authority remains unusable and its revocation evidence remains unchanged;
7. a later disablement terminates exactly the newly active target authority;
8. unrelated identity sessions remain untouched.

Materially applicable Sprint36, Sprint37, Sprint38, Sprint39, Sprint40, Sprint41, Sprint42, and Sprint43 regressions remain independently preserved by CI.

## Schema and migration boundary

`NO_SCHEMA_CHANGE`.

Canonical migrations remain exactly **#1 through #15**.

Migration #16 remains **NOT SELECTED**.

Sprint44 does not create, modify, apply, roll back, or activate a migration.

## Security properties

Sprint44 preserves:

- deny-by-default and fail-closed authentication;
- exact tenant isolation;
- server-derived authorization context;
- no caller-selected session authority;
- current credential/factor epoch enforcement;
- current membership and organizational access enforcement;
- immutable historical revocation evidence;
- no self-service or administrative authentication bypass;
- no protected-control lifecycle widening;
- deterministic bounded source qualification.

## Exact source envelope

The Sprint44 implementation is limited to exactly these sorted paths:

```text
.github/workflows/sprint44-first-party-identity-reactivation-fresh-authentication-reentry-regression.yml
apps/web/app/Delivery/Http/Identity/FirstPartySessionController.php
apps/web/tests/first-party-identity-reactivation-fresh-authentication-reentry.php
docs/FIRST_PARTY_IDENTITY_REACTIVATION_FRESH_AUTHENTICATION_REENTRY_FOUNDATION.md
```

Sorted newline-terminated SHA-256:

`ff01f1355de6c7fdfd28c2d359eb70787dd8448f0b1fc6d9cb73c1a0fb76580a`

No other path is authorized by this Sprint44 source implementation.

## Lifecycle locks

Technical Preview remains **NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED**.

Sprint41 migration #15 remains **NOT ACTIVATED / NOT APPLIED in Technical Preview**.

Sprint42 source remains **NOT ACTIVATED in Technical Preview**.

Sprint43 source remains **NOT ACTIVATED in Technical Preview**.

Sprint44 source remains **NOT ACTIVATED in Technical Preview**.

Production remains **NO-GO / NOT AUTHORIZED**.

Updater remains **DISABLED / UNWIRED**.

Deployment and release remain **NOT AUTHORIZED**.

Rollback remains **NOT AUTHORIZED**.

## Result

Sprint44 source implementation establishes a bounded, fail-closed fresh-authentication re-entry foundation. Reactivation remains eligibility-only; only the existing canonical fresh-authentication path may establish new authority, and no historical authority can be resurrected by reactivation or re-entry.
