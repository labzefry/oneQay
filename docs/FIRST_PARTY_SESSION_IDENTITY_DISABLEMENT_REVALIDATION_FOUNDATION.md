# First-Party Session Identity Disablement Revalidation Foundation

## Status

**SPRINT40 BOUNDED SOURCE IMPLEMENTATION / LOCAL-TEST-CI ONLY**

Date: **2026-08-25**

Attribution: **Lab | zefry**

## Purpose

Sprint40 adds one independent request-time requirement to the published first-party logical session authority chain: the exact tenant-scoped identity represented by an otherwise-valid current logical authority must still be eligible for first-party authenticated use according to canonical server-owned durable state.

This foundation consumes eligibility. It does not provide an identity-disable/enable producer, administration route, UI, public API, recovery mechanism, or external lifecycle synchronization.

## Schema

Sprint40 adds exactly migration #14:

`apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php`

The migration alters only `oneqay_identities` and adds:

- `first_party_authentication_enabled`;
- boolean;
- `NOT NULL`;
- default `true` for compatibility with existing canonical identity rows;
- no index, timestamp, reason, actor, enum, journal, foreign key, trigger, generated column, or auxiliary table.

Migrations #1 through #13 remain immutable. Migration #14 is forward-only and its `down()` path throws exactly:

`LogicException('Forward-only generated migration; rollback is not authorized.')`

The default is migration compatibility only. Missing schema, missing row, query failure, malformed value, disabled persistence, unauthorized runtime, or disabled session-control capability never becomes an implicit enabled state.

## Application contract

`App\Application\Identity\FirstPartyIdentityEligibilityVerifier` exposes exactly one read-only method:

`isEligible(TenantId $tenantId, PlatformIdentityId $identityId): bool`

It exposes no mutation, reason, timestamp, lifecycle administration, session mutation, organizational mutation, or credential mutation surface.

## Durable verifier

`App\Infrastructure\Identity\LaravelFirstPartyIdentityEligibilityVerifier`:

- receives the existing database connection, persistence-enabled state, runtime class, and existing session-control availability;
- operates only when persistence and session control are enabled and runtime is `local`, `test`, or `ci`;
- queries only `oneqay_identities` by exact `tenant_id` plus `id`;
- reads only `first_party_authentication_enabled`;
- returns `true` only for canonical enabled evidence;
- returns `false` for disabled, missing, malformed, contradictory, query-error, storage-unavailable, schema-missing, or operationally unavailable evidence;
- performs no write, repair, backfill, cache, audit mutation, lifecycle mutation, or session mutation;
- re-reads durable eligibility on every protected request through the request-time middleware path.

No request-external eligibility cache is introduced.

## Request-time ordering

`EnforceActiveFirstPartySessionAuthorityMiddleware` preserves the published Sprint36-Sprint39 chain and composes Sprint40 in this order:

1. reject restricted or malformed framework-session state;
2. derive tenant, identity, logical authority, organization, optional outlet, and optional device exclusively from server-side session state;
3. parse and canonicalize tenant and identity;
4. call existing `FirstPartySessionAuthorityService::assertActiveCurrent(...)` first;
5. call `FirstPartyIdentityEligibilityVerifier::isEligible(...)` immediately on those exact validated tenant/identity coordinates;
6. fail closed through the existing generic denial path if current identity eligibility is not canonical enabled evidence;
7. preserve Sprint39 organization/outlet/device parsing and device-without-outlet denial;
8. preserve current tenant-membership verification;
9. preserve exact organization/outlet/device relationship verification;
10. continue only after every required layer succeeds.

The public denial contract remains `SESSION_AUTHORITY_DENIED`. A denial may invalidate the local Laravel session and regenerate the CSRF token as already canonical. Sprint40 creates no new durable revocation transition solely because eligibility failed.

## Independent authority layers preserved

Identity eligibility is additive. It does not replace or weaken:

- exact tenant + identity logical authority ownership;
- credential epoch;
- privileged factor epoch where applicable;
- revocation state;
- Sprint36 inventory/revoke-one/revoke-others/logout behavior;
- Sprint37 tenant-scoped revoke-all behavior;
- Sprint38 idle lifetime of exactly **7200 seconds**;
- Sprint38 absolute lifetime of exactly **43200 seconds / 12 hours**;
- current tenant membership;
- exact organization/outlet/device authorization from Sprint39.

Current credentials, factor evidence, membership, or organizational grants cannot override a disabled identity. Conversely, an eligible identity does not restore removed membership or organizational access.

## Isolation and anti-grandfathering behavior

The bounded regression proves that:

- a currently enabled exact identity can continue only while all existing authority invariants also remain valid;
- a direct test-only durable transition from enabled to disabled is observed on the next protected request;
- a previously authorized request does not grandfather later access;
- missing or malformed eligibility evidence fails closed;
- caller query/body/header/session selectors do not become eligibility authority;
- privileged step-up, retry, or framework-session rotation cannot resurrect disabled access;
- disabling one identity does not disable another identity in the same tenant;
- another tenant remains isolated;
- eligible identities with removed membership or organizational access continue to fail through Sprint39 semantics.

Direct database updates in the dedicated regression fixture are test setup only. They do not create or authorize an application identity-lifecycle producer.

## HTTP, audit, configuration, and dependency boundary

Sprint40 adds:

**NO NEW ROUTE / NO NEW PUBLIC API / NO NEW REQUEST PAYLOAD**

Sprint40 adds:

**NO NEW AUDIT EVENT**

Sprint40 adds:

**NO NEW FEATURE ARM / NO NEW ENVIRONMENT VARIABLE**

Existing source-default-disabled session-control configuration remains authoritative:

`ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false`

No dependency manifest or lockfile changes are part of Sprint40.

## Qualification evidence model

The Sprint40 source PR is valid only on its exact final head when all workflows actually triggered for that head complete successfully without bypass. The dedicated Sprint40 workflow enforces the exact eight-path source fingerprint, migration #14 shape, migration #1-#13 immutability, no-new-route/audit/config/dependency boundaries, dedicated identity-disablement regression, preserved Sprint36-Sprint39 regressions, and tracked-source cleanliness.

Repository-native Product Owner merge authority remains exact-head bound. Any source-head mutation invalidates earlier exact-head merge authority and requires fresh qualification plus a new matching authority record before merge.

## Runtime boundary

Sprint40 source execution is **Local / Test / CI only**.

Technical Preview remains **NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED**.

Production remains **NO-GO / NOT AUTHORIZED**.

Updater remains **DISABLED / UNWIRED**.

Deployment and release remain **NOT AUTHORIZED**.

Migration #14 is not authorized for Technical Preview or Production by this foundation.

## Explicit non-authority

This Sprint40 source implementation does not authorize:

- identity disable/enable administration routes or UI;
- bulk or timed identity lifecycle mutation;
- self-service disablement;
- external directory/IdP lifecycle synchronization;
- automatic reactivation or repair;
- new public session routes;
- new audit vocabulary;
- automatic organizational grant restoration;
- tenant/identity/organization/outlet/device switching;
- API/mobile token authority;
- support impersonation or break-glass access;
- passkeys/WebAuthn/federation;
- Technical Preview schema or runtime activation;
- Production schema or runtime activation;
- updater wiring;
- deployment;
- release;
- phase-exit authority.
