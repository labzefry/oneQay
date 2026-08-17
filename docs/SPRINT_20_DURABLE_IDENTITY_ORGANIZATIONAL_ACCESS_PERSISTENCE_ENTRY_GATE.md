# Sprint 20 Entry Gate — Durable Identity & Organizational Access Persistence Foundation

## Identity

- Product: `oneQay`
- Developer and Product Engineering Entity: **Lab | zefry**
- Repository: `labzefry/oneQay`
- Decision date: 2026-08-17
- Exact entry-gate base: `d3ab5ebed9687593ca8c5a1126f8c980d35ec0b1`
- Exact entry-gate base tree: `21634b680d9c7455bf4a0d84aced801881061c09`
- Sprint 19 Durable Application Persistence Foundation: **COMPLETE / IMPLEMENTED / PUBLISHED**
- Production readiness: **NO-GO**

GitHub remains the Single Source of Truth.

Attribution: **Lab | zefry**

## Product Owner continuation

The Product Owner directed the project to continue immediately to the next bounded engineering stage after Sprint 19.

This entry gate authorizes **Sprint 20 — Durable Identity & Organizational Access Persistence Foundation**, including Local/Test/CI implementation and normal Ready/Merge lifecycle after exact-head required checks and repository-native Product Owner merge-authority status succeed.

Independent review is not an additional requirement under the current Product Owner continuation model.

This authority does **not** grant Preview/cPanel/live/Production persistence, customer-data persistence, Production schema execution, deployment, Release/GitHub Release, updater activation, or Production readiness.

## Why Sprint 20 is the next bounded capability

Sprint 19 established durable foundational persistence for:

`Tenant → Identity → Organization → Outlet → Device`

and explicit identity/organization membership through `oneqay_identity_organizations`.

M7.3 organizational authorization currently depends on two Application abstractions:

- `TenantMembershipVerifier`;
- `OrganizationalRelationshipVerifier`.

Their qualified implementation remains synthetic/in-memory for current Technical Preview flows.

Sprint 20 establishes a durable Local/Test/CI source for those membership/access relationships without changing the policy model, without inventing a business RBAC catalog, and without enabling durable Preview access.

## Design rule: persist existing authorization facts, do not invent policy

Sprint 20 must not introduce arbitrary role names, permission catalogs, ACL strings, superadmin semantics, cashier roles, manager roles, owner roles, or other business authorization vocabulary that is not yet canonical.

The persisted authorization facts are limited to the hierarchy already encoded by M7.3:

1. Tenant membership;
2. Organization membership;
3. Outlet access under an organization;
4. Device access under an outlet.

Organization membership continues to use the Sprint 19 canonical table:

`oneqay_identity_organizations`

Sprint 20 adds only the missing lower-scope grant persistence necessary to distinguish:

- organization-only access;
- organization + outlet access;
- organization + outlet + device access.

## Trust model

Sprint 20 does not expose a user-facing grant-management API.

New durable access facts may be recorded only from an already verified `VerifiedOrganizationalContext` produced by the existing authorization chain.

Therefore the progression is:

`existing verified identity + verified tenant + verified organizational relationship`

→ immutable/canonical access-grant value

→ durable access repository

→ exact persisted readback

→ later durable verification.

The persistence operation must never treat a raw tenant, organization, outlet, or device hint as grant authority.

## Exact authorized implementation paths

Sprint 20 source implementation is limited to exactly these paths:

1. `apps/web/app/Application/Access/DurableOrganizationalAccessGrant.php` — new;
2. `apps/web/app/Application/Access/DurableOrganizationalAccessRepository.php` — new;
3. `apps/web/app/Application/Access/DurableOrganizationalAccessService.php` — new;
4. `apps/web/app/Application/Access/DurableOrganizationalAccessViolation.php` — new;
5. `apps/web/app/Infrastructure/Access/LaravelDurableOrganizationalAccessRepository.php` — new;
6. `apps/web/app/Infrastructure/Tenancy/LaravelTenantMembershipVerifier.php` — new;
7. `apps/web/app/Infrastructure/Organization/LaravelOrganizationalRelationshipVerifier.php` — new;
8. `apps/web/app/Providers/AppServiceProvider.php`;
9. `apps/web/database/migrations/0000_00_00_000002_create_organizational_access_grants.php` — new;
10. `apps/web/tests/access-persistence.php` — new;
11. `apps/web/tests/run.php`;
12. `apps/web/tests/tenant-isolation.php`;
13. `apps/web/tests/identity-org-context.php`;
14. `.github/workflows/m7-2-tenant-isolation-regression.yml`;
15. `.github/workflows/m7-3-identity-org-context-regression.yml`;
16. `.github/workflows/m7-5-preview-db-qualification-regression.yml`;
17. `docs/DURABLE_IDENTITY_ORGANIZATIONAL_ACCESS_PERSISTENCE_FOUNDATION.md` — new.

No other repository path is authorized by this entry gate.

If implementation discovery requires another path, Sprint 20 must stop and publish a bounded supplement before mutating outside this envelope.

## Explicit non-scope

Sprint 20 must not modify:

- `src/Auth/Foundation.php`;
- `src/Persistence/Foundation.php`;
- `src/Migration/Foundation.php`;
- Sprint 16–18 SchemaPlanning implementation;
- Sprint 19 foundational migration;
- `apps/web/composer.json`;
- `apps/web/composer.lock`;
- `apps/web/package.json`;
- `apps/web/package-lock.json`;
- routes/controllers/UI;
- Technical Preview fixture source;
- deployment/updater/release source;
- Preview/cPanel/live runtime evidence;
- POS domain persistence.

## Runtime boundary

Sprint 20 reuses the Sprint 19 durable persistence runtime contract.

Durable access repository and durable verifier adapters must fail closed unless:

- `ONEQAY_PERSISTENCE_ENABLED` is true; and
- runtime class is one of `local`, `test`, or `ci`.

They must deny:

- `preview`;
- `production`;
- blank runtime class;
- unknown runtime class.

Sprint 20 must not broaden the runtime allowlist.

## Application access model

`DurableOrganizationalAccessGrant` must be framework-independent and represent exactly:

- `TenantId`;
- `PlatformIdentityId`;
- `OrganizationId`;
- optional `OutletId`;
- optional `DeviceId`.

A device grant requires an outlet grant scope.

The value object must not contain:

- password;
- token;
- email;
- personal profile;
- customer data;
- POS transaction data;
- arbitrary permission strings.

## Durable recording service

`DurableOrganizationalAccessService` may record access only from `VerifiedOrganizationalContext`.

It must not accept raw string hints as trust authority.

It must create a canonical `DurableOrganizationalAccessGrant` from the verified context and persist it inside the existing Sprint 19 `PersistenceTransaction` boundary.

The service must be idempotent for an identical verified context.

## Repository contract

The Application repository contract must provide bounded operations sufficient for:

- recording an already verified organizational access grant;
- checking durable tenant membership for one tenant + identity;
- checking one exact organizational/outlet/device scope.

Every operation must be explicitly tenant scoped.

The contract must not expose a global unscoped identity lookup.

## Organization-level authority

Sprint 20 does not duplicate organization membership into another table.

`oneqay_identity_organizations` remains the canonical organization-level membership relation.

A durable organization-only verification succeeds only when the exact tuple exists:

`tenant_id + identity_id + organization_id`

## New relational access tables

Sprint 20 may create exactly two new tables:

1. `oneqay_outlet_access_grants`;
2. `oneqay_device_access_grants`.

No other permanent table is authorized.

The migration must not seed data.

### Outlet access grant

Required logical key:

`tenant_id + identity_id + organization_id + outlet_id`

Required defense-in-depth relationships:

- membership tuple must exist in `oneqay_identity_organizations`;
- outlet must belong to the same tenant + organization.

### Device access grant

Required logical key:

`tenant_id + identity_id + organization_id + outlet_id + device_id`

Required defense-in-depth relationships:

- corresponding outlet access grant must already exist;
- device must belong to the same tenant + organization + outlet.

The migration may add the minimum non-destructive unique key to `oneqay_devices` necessary to support that composite foreign key.

No existing column or table may be dropped, renamed, or rewritten.

## Forward-only migration boundary

The Sprint 20 migration must remain forward-only.

`down()` must throw the existing published boundary:

`Forward-only generated migration; rollback is not authorized.`

No automatic destructive rollback authority is introduced.

## Idempotency and no silent escalation

Recording the same exact verified access context repeatedly must be idempotent.

The implementation must not use unrestricted ownership-rewriting upsert semantics.

An outlet or device access grant must never silently change tenant, organization, outlet, or identity ownership.

Because the grant keys are relationship identities, conflicting relationship input must fail closed through validation or relational constraints.

## Durable tenant membership verifier

`LaravelTenantMembershipVerifier` may implement the existing `TenantMembershipVerifier` interface.

Verification must:

- canonicalize principal and tenant identifiers through existing Domain values;
- query durable membership using explicit tenant scope;
- succeed only when at least one exact `oneqay_identity_organizations` row exists for that tenant + identity;
- return `ServerVerifiedTenantContext` only after durable proof;
- return `null` for absent, malformed, foreign, disabled-runtime, or unauthorized membership without leaking foreign data.

Runtime denial may use a bounded exception internally, but callers must not receive foreign payload details.

## Durable organizational relationship verifier

`LaravelOrganizationalRelationshipVerifier` may implement the existing `OrganizationalRelationshipVerifier` interface.

Verification semantics must preserve M7.3 hierarchy:

- organization-only request requires exact durable organization membership;
- outlet request requires exact organization membership plus exact outlet access grant;
- device request requires exact organization membership + outlet grant + device grant;
- device without outlet remains denied;
- same identifiers under another tenant must not satisfy access;
- an outlet belonging to another organization must not satisfy access;
- a device belonging to another outlet must not satisfy access.

The verifier returns boolean authorization evidence only; it must not return database rows or sensitive metadata.

## Wiring policy

Sprint 20 may bind `DurableOrganizationalAccessRepository` in `AppServiceProvider`.

Sprint 20 must **not** globally replace current Technical Preview `TenantMembershipVerifier` or `OrganizationalRelationshipVerifier` bindings with durable adapters.

Technical Preview remains synthetic/non-durable and `preview` runtime remains denied by the durable persistence boundary.

The durable verifier adapters are qualified through Local/Test/CI regression and remain available for a later explicitly gated runtime transition.

## M7.2 and M7.3 preservation

Existing tenant and organizational authorization behavior remains authoritative.

M7.2/M7.3 regressions must evolve from an exact one-migration Sprint 19 set to an exact two-migration Sprint 19+20 set.

They must continue to enforce:

- Domain/Application framework independence;
- no default tenant;
- client hints are non-authoritative;
- cross-tenant read/write denial;
- same global-looking IDs cannot bypass tenant scope;
- identity/organization layers remain free of physical persistence mechanics;
- explicit tenant predicates in Infrastructure persistence/verifier code;
- no unrestricted ownership-rewriting upsert.

M7.3 must additionally prove that durable verifiers reproduce the same hierarchy decisions as existing synthetic qualification fixtures for Local/Test/CI data.

## Preview qualification preservation

M7.5 Preview Database Qualification must remain non-mutating.

Its workflow may be updated only to recognize and syntax-check the second canonical migration and confirm that durable access adapters remain `local/test/ci` only.

It must not execute either canonical application migration against a Preview target.

Technical Preview release packaging already excludes `apps/web/database/migrations/**`; Sprint 20 must preserve that boundary without modifying release packaging source.

## Local/Test/CI regression proof

`apps/web/tests/access-persistence.php` must use a disposable SQLite database and prove at minimum:

1. durable access persistence is denied when persistence is disabled;
2. Preview runtime is denied;
3. Sprint 19 migration executes into a clean disposable database;
4. Sprint 20 migration executes after Sprint 19;
5. exactly the two new access tables exist;
6. a synthetic tenant/identity/organization/outlet/device graph is persisted through the Sprint 19 repository;
7. an already verified organizational context is recorded through the Sprint 20 service;
8. organization-only durable verification succeeds;
9. outlet durable verification succeeds only for the granted outlet;
10. device durable verification succeeds only for the granted device beneath the granted outlet;
11. same grant recording is idempotent;
12. another tenant may reuse the same organization/outlet/device identifiers independently;
13. tenant-alpha cannot satisfy tenant-beta membership/access;
14. wrong-organization outlet access is denied;
15. wrong-outlet device access is denied;
16. device scope without outlet is denied;
17. a durable tenant verifier returns verified context only for an actual durable membership;
18. a durable organizational verifier reproduces the expected M7.3 allow/deny hierarchy;
19. no secret, DSN, absolute DB path, or row dump is emitted as authorization evidence;
20. the temporary database/workspace is removed.

Only synthetic identifiers may be used.

## No role/permission catalog yet

Sprint 20 deliberately stops before business RBAC/ABAC semantics.

A later explicit gate is required before introducing:

- named business roles;
- permissions/capabilities;
- deny overrides;
- role inheritance;
- user-facing grant/revoke administration;
- authorization audit history;
- access expiration;
- Production access control migration.

## Publication lifecycle

Sprint 20 lifecycle is:

1. merge this documentation-only entry gate;
2. create a fresh implementation branch from exact merged canonical main;
3. mutate only the authorized implementation paths;
4. run all triggered CI;
5. repair bounded implementation defects without scope expansion;
6. reissue exact-head Product Owner merge authority whenever head changes;
7. merge only after required checks succeed;
8. verify canonical post-merge SHA, tree, parent, signature, and file envelope.

Independent human review is not required.

## Exit criteria

Sprint 20 may be declared COMPLETE only when:

- this entry gate is published;
- implementation stays inside the exact path envelope;
- exactly two lower-scope access-grant tables are added;
- Sprint 19 organization membership remains canonical rather than duplicated;
- durable recording accepts only already verified organizational context;
- durable tenant and organizational verifiers are tenant scoped and fail closed;
- Local/Test/CI regression proves hierarchy, idempotency, and cross-tenant denial;
- existing M7.2/M7.3 authorization behavior remains green;
- Preview qualification remains non-mutating;
- no dependency/lockfile changes occur;
- no Preview/live/Production durable access authority is introduced;
- exact-head CI and Product Owner merge authority succeed;
- merged canonical main is freshly verified.

Production readiness remains **NO-GO**.
