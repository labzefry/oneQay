# First-Party Session Organizational Access Revalidation Foundation

## Status

**SPRINT39 SOURCE IMPLEMENTATION / LOCAL-TEST-CI ONLY**

Date: **2026-08-25**

Attribution: **Lab | zefry**

## Purpose

Sprint39 closes the bounded authorization gap in which a durable first-party logical session could remain technically active after its represented tenant membership or organization/outlet/device access had been removed.

The implementation adds request-time revalidation of current durable organizational access to the already-established Sprint36-Sprint38 logical session authority path.

It does not introduce a new session authority, a new access model, or a new grant-administration capability.

## Canonical governed basis

Sprint39 is implemented under the published governance chain:

- concern selection and entry gate: PR #258;
- schema/source-gate preservation predecessor: PR #259;
- schema/source-envelope gate: PR #260;
- source-preservation predecessor: PR #261;
- source-preservation publication commit: `5482ff5adfd5a5ca769eefcd4864ea4f16d6afd9`;
- source-preservation publication tree: `c9dca3cac3fc7ca42d08c2f5ab890fef6cdde5f7`.

The source implementation remains bounded to the exact eight-path envelope frozen by PR #260.

Sorted newline-terminated source-envelope SHA-256:

`2cfc92c34f46375b11bf3fe92f9094cefa598d234133847fdd6629be211f12c4`

## Schema decision

Sprint39 remains:

**NO_SCHEMA_CHANGE**

Canonical migrations remain exactly **#1 through #13**.

Migration #14 is:

- **NOT REQUIRED**;
- **NOT SELECTED**;
- **NOT AUTHORIZED**.

Sprint39 adds no table, column, index, foreign key, enum, trigger, generated field, migration artifact, rollback path, or schema rewrite.

The implementation reuses existing durable tenant membership and organizational-access grant state.

## Existing logical session authority remains primary

Before organizational access is revalidated, the existing `FirstPartySessionAuthorityService::assertActiveCurrent(...)` contract remains authoritative for:

- exact tenant + identity ownership;
- exact logical `authority_id`;
- exact organization/outlet/device context equality against the durable session row;
- credential epoch;
- privileged factor epoch when applicable;
- monotonic revocation state;
- sliding idle lifetime of **7200 seconds**;
- absolute lifetime of **43200 seconds / 12 hours**;
- source-default-disabled session-control configuration;
- persistence availability;
- Local/Test/CI runtime boundary.

Sprint39 does not move or duplicate those responsibilities into a replacement service.

## Request-time organizational access revalidation

After the existing logical session authority succeeds, the session middleware now revalidates current durable access using the existing authority contracts:

- `TenantMembershipVerifier`;
- `OrganizationalRelationshipVerifier`.

The exact sequence is:

1. reject restricted or malformed framework-session state;
2. read tenant, identity, authority ID, organization, optional outlet, and optional device only from server-side session state;
3. parse tenant and identity into existing domain IDs and require their raw values to already be canonical;
4. validate the current logical session through `FirstPartySessionAuthorityService::assertActiveCurrent(...)`;
5. parse organization, optional outlet, and optional device into existing domain IDs;
6. require every parsed identifier to equal the raw server-side session value exactly;
7. reject device-bound authority when outlet is absent;
8. revalidate current tenant membership for the exact identity + tenant;
9. require the server-verified membership tenant to equal the exact current tenant;
10. revalidate the exact organization/outlet/device relationship;
11. continue the protected request only if all evidence remains valid.

All failures use the existing generic fail-closed session-authority denial path.

## Canonical identifier preservation

Existing domain identifier factories normalize with lowercase/trim semantics. Sprint39 does not allow that normalization to silently transform framework-session state into a different authority coordinate.

The middleware therefore requires the raw server-side session identifier to equal the parsed canonical domain value exactly for:

- tenant;
- identity;
- organization;
- outlet when present;
- device when present.

A non-canonical, malformed, missing, contradictory, or structurally impossible session coordinate fails closed.

## Tenant membership semantics

The existing durable tenant-membership verifier remains unchanged.

A usable first-party logical authority now requires current durable tenant membership for its exact identity + tenant on every protected request using this middleware.

If that membership is removed after session issuance:

- the logical session row is not treated as continuing authorization;
- the protected request is denied;
- the local framework session is invalidated by the existing denial path;
- the removed membership is not recreated;
- no replacement logical authority is minted.

## Organization semantics

Organization access is revalidated through the existing organizational relationship verifier.

An organization-bound session remains usable only while the exact tenant + identity + organization relationship remains permitted.

If the represented organization relationship is removed while another tenant membership still exists, the selected stale organization does not fall back to another organization and the request is denied.

Sprint39 never auto-switches organizational context.

## Outlet semantics

An outlet-bound session authority requires the exact outlet grant represented by its already-validated session context.

Loss of that outlet grant denies the request.

The middleware does not fall back to organization-only permission merely because broader organization membership remains present.

## Device semantics

A device-bound session authority requires:

- a non-null outlet;
- the exact outlet grant;
- the exact device grant for the same tenant + identity + organization + outlet + device.

A device without outlet fails closed.

Loss of only the device grant denies the request even when the outlet grant remains valid.

The middleware does not fall back to outlet-only or organization-only permission.

## Caller-controlled selectors are not authority

Sprint39 does not read organizational authority from:

- query parameters;
- route parameters;
- request bodies;
- headers;
- caller-controlled cookies;
- client-supplied tenant or organizational selectors.

Revalidation uses only the server-side session coordinates that have already passed the durable logical-session context check.

A caller cannot restore stale access by submitting a currently valid organization, outlet, or device in the request.

## Retry, session rotation, step-up, and replay

Loss of durable organizational access remains authoritative across:

- a newly created framework session carrying the same logical authority;
- request retry;
- session rotation;
- privileged step-up evidence;
- caller selector changes;
- repeated evaluation of the same stale logical authority.

These mechanisms do not recreate grants and do not issue replacement logical authority.

Existing session inventory, revoke-one, revoke-others, revoke-all, canonical logout, and privileged session-control step-up semantics remain unchanged.

## Failure handling

Sprint39 reuses the existing middleware denial path.

When revalidation fails:

- the protected request does not continue;
- the local framework session is invalidated;
- the CSRF token is regenerated;
- the existing generic `SESSION_AUTHORITY_DENIED` response is used for protected requests;
- canonical logout retains its existing special no-content behavior.

Sprint39 does not add a public reason distinguishing tenant-membership loss from organization/outlet/device loss.

This avoids turning authorization state into a caller-visible probing oracle.

## No durable mutation on access loss

Organizational-access loss is evaluated as authorization evidence.

Sprint39 does not add a new durable mutation when that evidence fails.

In particular, it does not:

- revoke or rewrite the historical access grant as part of revalidation;
- recreate a removed grant;
- add a new durable session revocation reason;
- write a new audit event;
- mint a replacement session;
- choose an alternate organization/outlet/device.

Any future durable state transition specifically tied to access-loss detection requires separate Product Owner selection and governance.

## HTTP and audit contract

Sprint39 adds:

**NO NEW ROUTE / NO NEW PUBLIC API / NO NEW REQUEST PAYLOAD**

Existing routes remain unchanged, including:

- session inventory;
- revoke-one;
- revoke-others;
- revoke-all;
- privileged session-control reauthentication;
- canonical logout.

Sprint39 adds:

**NO NEW AUDIT EVENT**

Existing secret-free session audit vocabulary remains:

- `session_issued`;
- `session_revoked`;
- `other_sessions_revoked`;
- `all_sessions_revoked`;
- `session_logout`.

## Configuration and activation

Sprint39 adds:

**NO NEW FEATURE ARM / NO NEW ENVIRONMENT VARIABLE**

It remains under the existing session-control feature boundary:

`ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false`

The feature remains source-default-disabled.

Fixed session lifetimes remain:

- `idle_ttl_seconds = 7200`;
- `absolute_ttl_seconds = 43200`.

Execution authority remains limited to:

**Local / Test / CI**

Technical Preview remains **NO_SCHEMA_CHANGE** and receives no activation authority from Sprint39.

Production remains **NO-GO / NOT AUTHORIZED**.

Updater remains **DISABLED / UNWIRED**.

Deployment and release remain **NOT AUTHORIZED**.

## Dedicated regression evidence

`apps/web/tests/first-party-session-organizational-access-revalidation.php` uses actual SQLite persistence with canonical migrations #1-#13 plus the existing durable repository/verifier implementations.

It proves at minimum:

- exact valid durable access permits the protected request;
- removal of the only tenant membership denies the still-active logical authority;
- removal of the selected organization denies it even when another membership preserves tenant membership;
- removal of an outlet grant denies outlet-bound authority without organization fallback;
- removal of a device grant denies device-bound authority while the outlet grant remains;
- retry, framework-session rotation, caller selectors, and step-up evidence do not restore stale access;
- device without outlet fails closed;
- non-canonical tenant/identity authority coordinates fail closed rather than being normalized;
- another identity in the same tenant remains unaffected;
- another tenant remains unaffected;
- access denial does not recreate grants or mint a replacement logical authority;
- migrations remain exactly #1-#13;
- idle and absolute lifetime configuration remains 7200/43200;
- session audit vocabulary remains unchanged and secret-free.

The dedicated Sprint39 workflow also preserves Sprint36 inventory/revocation, Sprint37 revoke-all, Sprint38 absolute lifetime, and the full application regression.

## Exact source envelope

Sprint39 source implementation changes exactly these eight paths:

1. `.github/workflows/sprint35-privileged-totp-recovery-regression.yml`
2. `.github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml`
3. `.github/workflows/sprint37-first-party-all-session-termination-regression.yml`
4. `.github/workflows/sprint38-first-party-session-absolute-lifetime-regression.yml`
5. `.github/workflows/sprint39-first-party-session-organizational-access-revalidation-regression.yml`
6. `apps/web/app/Delivery/Http/Middleware/EnforceActiveFirstPartySessionAuthorityMiddleware.php`
7. `apps/web/tests/first-party-session-organizational-access-revalidation.php`
8. `docs/FIRST_PARTY_SESSION_ORGANIZATIONAL_ACCESS_REVALIDATION_FOUNDATION.md`

Sorted newline-terminated SHA-256:

`2cfc92c34f46375b11bf3fe92f9094cefa598d234133847fdd6629be211f12c4`

No other path belongs to Sprint39 source implementation authority.

## Explicit non-authority

This Sprint39 source implementation does not authorize or perform:

- schema mutation;
- migration #14;
- access-grant administration UI/API;
- account suspension or disablement;
- administrator revocation of another identity;
- global/cross-tenant identity logout;
- new session-control route or payload;
- new audit event;
- new feature arm;
- Technical Preview activation;
- Production activation;
- updater wiring;
- deployment;
- release.
