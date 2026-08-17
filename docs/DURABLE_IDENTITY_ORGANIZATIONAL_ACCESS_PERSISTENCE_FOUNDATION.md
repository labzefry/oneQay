# Durable Identity & Organizational Access Persistence Foundation

## Status

Sprint 20 implements the bounded **Durable Identity & Organizational Access Persistence Foundation** authorized by `docs/SPRINT_20_DURABLE_IDENTITY_ORGANIZATIONAL_ACCESS_PERSISTENCE_ENTRY_GATE.md`.

Status after publication: **IMPLEMENTED / LOCAL-TEST-CI DURABLE ACCESS FOUNDATION / PREVIEW-PRODUCTION DISABLED**.

Production readiness remains **NO-GO**.

Attribution: **Lab | zefry**

## Purpose

Sprint 19 established durable persistence for the foundational context graph:

`Tenant → Identity → Organization → Outlet → Device`

and stored identity-to-organization membership in `oneqay_identity_organizations`.

Sprint 20 adds durable lower-scope organizational access evidence without inventing a new business authorization model.

The durable hierarchy is:

- tenant membership;
- organization membership;
- outlet access grant;
- device access grant.

Organization membership remains canonical in Sprint 19. Sprint 20 adds only outlet and device grant persistence.

## No RBAC catalog in Sprint 20

Sprint 20 does not create role names, permission strings, role inheritance, administrator grants, cashier/manager/owner semantics, deny overrides, expiration, or grant/revoke administration.

Those capabilities require a later explicit gate.

## Trust boundary

Durable access recording accepts only an already verified `VerifiedOrganizationalContext`.

Raw tenant, organization, outlet, or device hints are never accepted as grant authority.

The recording chain is:

`VerifiedOrganizationalContext`

→ `DurableOrganizationalAccessGrant`

→ `PersistenceTransaction`

→ `DurableOrganizationalAccessRepository`

→ exact durable readback.

## Application contracts

Sprint 20 adds framework-independent Application types:

- `DurableOrganizationalAccessGrant`;
- `DurableOrganizationalAccessRepository`;
- `DurableOrganizationalAccessService`;
- `DurableOrganizationalAccessViolation`.

They contain no Laravel, Illuminate database, PDO, SQL, Schema facade, or query-builder dependency.

`DurableOrganizationalAccessGrant` contains only existing domain identifiers:

- `TenantId`;
- `PlatformIdentityId`;
- `OrganizationId`;
- optional `OutletId`;
- optional `DeviceId`.

A device grant requires an outlet scope.

## Durable access repository

`LaravelDurableOrganizationalAccessRepository` is the Infrastructure implementation.

It provides three bounded capabilities:

1. record one already verified access grant;
2. prove tenant membership for an explicit tenant + identity;
3. verify one explicit organization/outlet/device grant scope.

All lookups are tenant scoped.

The repository independently enforces the Sprint 19 runtime boundary:

- enabled flag required;
- runtime must be `local`, `test`, or `ci`.

`preview`, `production`, blank, and unknown runtime classes fail closed.

## Organization membership remains canonical

Sprint 20 does not duplicate organization membership.

The existing Sprint 19 relation:

`oneqay_identity_organizations`

remains the organization-level access fact.

An organization-only durable authorization succeeds only when the exact tuple exists:

`tenant_id + identity_id + organization_id`

## New access tables

Sprint 20 publishes one forward-only migration:

`apps/web/database/migrations/0000_00_00_000002_create_organizational_access_grants.php`

It adds exactly two access-grant tables:

1. `oneqay_outlet_access_grants`;
2. `oneqay_device_access_grants`.

The migration also adds only the minimum composite unique key to `oneqay_devices` required for a tenant + organization + outlet + device foreign key.

No existing column is removed or renamed.

No data is seeded.

## Outlet access grant

Primary relationship identity:

`tenant_id + identity_id + organization_id + outlet_id`

Database constraints require:

- exact identity/organization membership in the same tenant;
- exact outlet under the same tenant + organization.

The presence of an outlet row alone does not authorize an identity to use it.

## Device access grant

Primary relationship identity:

`tenant_id + identity_id + organization_id + outlet_id + device_id`

Database constraints require:

- an existing corresponding outlet access grant;
- the device to belong to the same tenant + organization + outlet.

A device cannot receive durable access without its outlet grant.

## Idempotency

Grant recording uses insert-if-absent semantics and exact readback.

It does not use unrestricted `upsert()` or `updateOrInsert()` behavior.

Recording the same verified access context repeatedly therefore remains idempotent without silently changing ownership.

## Durable tenant membership verifier

`LaravelTenantMembershipVerifier` implements the existing `TenantMembershipVerifier` contract using durable evidence.

It canonicalizes the principal and tenant identifiers through existing Domain values, performs an explicitly tenant-scoped durable membership check, and returns `ServerVerifiedTenantContext` only after durable proof.

Malformed, absent, foreign, disabled-runtime, or denied membership returns `null` without exposing foreign row data.

## Durable organizational relationship verifier

`LaravelOrganizationalRelationshipVerifier` implements the existing `OrganizationalRelationshipVerifier` contract.

Its hierarchy is intentionally identical to M7.3 semantics:

- organization-only requires exact durable membership;
- outlet scope additionally requires the exact outlet grant;
- device scope additionally requires the exact device grant;
- device without outlet is denied;
- another tenant cannot satisfy the same identifiers;
- another outlet or device is not authorized merely because it exists in the tenant.

The verifier returns only boolean authorization evidence.

## No global Technical Preview switch

Sprint 20 does not globally replace Technical Preview membership or organizational verifiers.

Technical Preview remains synthetic/non-durable, and durable persistence continues to deny runtime class `preview`.

The new durable verifier adapters are qualified for Local/Test/CI and reserved for a later explicit runtime-transition gate.

## Regression proof

`apps/web/tests/access-persistence.php` runs in the existing M7.1 application harness with a disposable SQLite database.

It proves:

- disabled durable access fails closed;
- Preview runtime fails closed;
- Sprint 19 migration is applied first;
- Sprint 20 migration is applied second;
- both new access tables exist;
- Sprint 19 graph persistence creates canonical identity/organization membership and organizational resources;
- a context verified by existing synthetic authorization can be recorded durably;
- repeated recording is idempotent;
- organization-only durable verification succeeds from canonical membership;
- explicitly granted outlet access succeeds;
- explicitly granted device access succeeds;
- an ungranted outlet that exists in the same organization remains denied;
- an ungranted device that exists in the same organization remains denied;
- device-without-outlet remains denied;
- another tenant can independently reuse the same organization/outlet/device identifiers;
- cross-tenant membership and relationship checks fail closed;
- a grant without canonical organization membership is rejected;
- runtime/source boundaries remain intact;
- the disposable database and workspace are removed.

Only synthetic identifiers are used.

## M7.2 and M7.3 preservation

Sprint 20 evolves the canonical migration expectation from one migration to exactly two:

1. Sprint 19 foundational context graph;
2. Sprint 20 organizational access grants.

M7.2 continues to protect tenant isolation and Application framework independence.

M7.3 continues to protect identity/organizational authorization semantics and now additionally requires the durable access regression to pass.

No client-supplied tenant or organizational hint becomes authorization authority.

## Preview database qualification preservation

M7.5 Preview Database Qualification remains non-mutating.

The workflow syntax-checks the Sprint 20 migration and durable access adapters but does not execute canonical migrations against a Preview target.

Technical Preview packaging continues to exclude the entire `apps/web/database/migrations/**` directory, preserving `NO_SCHEMA_CHANGE` release semantics.

## Forward-only boundary

The Sprint 20 migration `down()` path throws:

`Forward-only generated migration; rollback is not authorized.`

No destructive rollback authority is introduced.

## No dependency changes

Sprint 20 uses the already locked Laravel/database dependency set.

It does not modify Composer or npm manifests/locks.

## Explicit non-scope

Sprint 20 does not authorize:

- business role catalogs;
- permissions/capabilities;
- grant/revoke administration UI/API;
- access expiry;
- authorization audit history;
- Preview durable access;
- cPanel/live persistence;
- Production persistence;
- Production migration execution;
- POS business persistence;
- deployment;
- GitHub Release;
- updater activation;
- Production readiness.

## Next candidate stage

After Sprint 20 publication, the next bounded candidate should define **Durable Role & Permission Policy Foundation** only if canonical business roles and permission semantics are first explicitly specified and gated.

Until that occurs, durable access remains relationship-based and deny-by-default.

Production readiness remains **NO-GO**.
