# Durable Application Persistence Foundation

## Status

Sprint 19 implements the bounded **Durable Application Persistence Foundation** authorized by `docs/SPRINT_19_DURABLE_APPLICATION_PERSISTENCE_ENTRY_GATE.md`.

Status after publication: **IMPLEMENTED / LOCAL-TEST-CI DURABLE PERSISTENCE FOUNDATION / PREVIEW-PRODUCTION DISABLED**.

Production readiness remains **NO-GO**.

Attribution: **Lab | zefry**

## Purpose

Sprint 18 established controlled execution of revalidated Laravel migration artifacts against a disposable test database.

Sprint 19 establishes the first permanent application persistence architecture for the foundational context graph while retaining a strict runtime gate:

`VerifiedTenantContext`

+ `DurableContextGraph`

+ framework-independent repository contract

+ framework-independent transaction contract

→ verified tenant equality check

→ Local/Test/CI persistence runtime gate

→ tenant-scoped Laravel Infrastructure repository

→ one explicit transaction

→ composite tenant-aware relational constraints

→ exact readback

→ idempotent same-graph persistence

→ relationship-conflict denial.

The bounded graph is:

`Tenant → Identity → Organization → Outlet → Device`

with explicit identity-to-organization membership.

Sprint 19 does not persist Catalog, Sale, Payment Recording, Inventory, Shift/Register, customer profiles, payroll, financial transactions, or other POS business data.

## Exact implementation envelope

Sprint 19 implementation changes only the paths authorized by its entry gate:

1. `apps/web/config/database.php`;
2. `apps/web/environment.example`;
3. `apps/web/app/Application/Persistence/DurablePersistenceViolation.php`;
4. `apps/web/app/Application/Persistence/DurableContextGraph.php`;
5. `apps/web/app/Application/Persistence/DurableContextGraphRepository.php`;
6. `apps/web/app/Application/Persistence/PersistenceTransaction.php`;
7. `apps/web/app/Application/Persistence/DurableContextGraphService.php`;
8. `apps/web/app/Infrastructure/Persistence/LaravelDurableContextGraphRepository.php`;
9. `apps/web/app/Infrastructure/Persistence/LaravelPersistenceTransaction.php`;
10. `apps/web/app/Providers/AppServiceProvider.php`;
11. `apps/web/database/migrations/0000_00_00_000001_create_foundational_context_graph.php`;
12. `apps/web/tests/persistence.php`;
13. `apps/web/tests/run.php`;
14. `apps/web/tests/tenant-isolation.php`;
15. `.github/workflows/m7-2-tenant-isolation-regression.yml`;
16. `docs/DURABLE_APPLICATION_PERSISTENCE_FOUNDATION.md`.

No dependency or lockfile is changed.

`src/Persistence/Foundation.php`, `src/Migration/Foundation.php`, and the Sprint 16–18 SchemaPlanning pipeline remain unchanged.

## Disabled-by-default runtime policy

Canonical durable persistence is disabled by default through:

`ONEQAY_PERSISTENCE_ENABLED=false`

Sprint 19 Infrastructure permits durable persistence only for these runtime classes:

- `local`;
- `test`;
- `ci`.

The following remain denied:

- `preview`;
- `production`;
- blank runtime class;
- unknown runtime class.

Both the repository and transaction adapter independently enforce the runtime gate before database mutation.

This means a caller cannot bypass the Application service simply by resolving an Infrastructure repository directly.

## Lazy database configuration

`apps/web/config/database.php` publishes the first canonical application relational configuration, but it does not open a connection during application boot.

Laravel resolves the connection lazily only when a persistence component is actually resolved and used.

The configuration uses dedicated environment keys:

- `ONEQAY_DB_DRIVER`;
- `ONEQAY_DB_HOST`;
- `ONEQAY_DB_PORT`;
- `ONEQAY_DB_DATABASE`;
- `ONEQAY_DB_USERNAME`;
- `ONEQAY_DB_PASSWORD`;
- `ONEQAY_DB_SOCKET`.

The repository contains placeholders only. Real credentials remain outside Git.

The canonical relational profile uses `utf8mb4` and strict mode.

Sprint 19 does not make liveness or readiness depend on the durable database.

## Secret boundary

`apps/web/environment.example` explicitly keeps durable persistence disabled and contains replacement placeholders rather than credentials.

No connection password, DSN, database path, or customer data is serialized by the Application persistence types.

The durable context graph contains identifiers only.

## Framework-independent Application boundary

Sprint 19 introduces these Application-layer types:

- `DurablePersistenceViolation`;
- `DurableContextGraph`;
- `DurableContextGraphRepository`;
- `PersistenceTransaction`;
- `DurableContextGraphService`.

They contain no dependency on:

- `Illuminate\`;
- Laravel database classes;
- PDO;
- SQL;
- Schema facade;
- query builder.

Infrastructure remains the only layer authorized to depend on Laravel database mechanics.

This preserves Clean Architecture inward dependency.

## Durable context graph

`DurableContextGraph` carries exactly five existing bounded domain identifiers:

- `TenantId`;
- `PlatformIdentityId`;
- `OrganizationId`;
- `OutletId`;
- `DeviceId`.

The implementation deliberately does not replace these identifiers with UUID assumptions.

It contains no password, token, email, name, address, personal profile, customer record, or financial information.

## Verified tenant context requirement

`DurableContextGraphService` requires an existing server-verified tenant context.

Before any transaction begins it:

1. requires a non-null valid `VerifiedTenantContext`;
2. canonicalizes the verified tenant through the existing `TenantId` value object;
3. compares the verified tenant with the graph tenant;
4. fails closed when they differ.

A raw header, route value, query string, cookie, correlation ID, or arbitrary caller-supplied tenant value is not accepted as persistence authority.

## Tenant-scoped repository API

The repository contract exposes:

- `persist(DurableContextGraph $graph)`;
- `findForTenant(TenantId, PlatformIdentityId, DeviceId)`.

There is no unscoped tenant-owned `findById()` API.

The Laravel implementation includes `tenant_id` in every tenant-owned read predicate required to reconstruct the graph.

The device, identity, organization, outlet, and membership reads are independently tenant constrained.

## Insert-if-absent and exact readback

The Infrastructure repository uses insert-if-absent semantics followed by exact persisted-row verification.

It does not use unrestricted `upsert()` or `updateOrInsert()` behavior.

This supports idempotent repeat persistence of the same graph without silently changing ownership.

When an existing tenant-owned identifier is already bound to different relationship data, the exact readback check raises:

`DURABLE_PERSISTENCE_RELATIONSHIP_CONFLICT`

The surrounding transaction then rolls back any earlier inserts from that request.

## Explicit transaction boundary

`PersistenceTransaction` is an Application contract.

`LaravelPersistenceTransaction` is the Infrastructure implementation.

The entire graph persistence call executes within one Laravel transaction.

A failure at any point prevents a partial context graph from becoming durable.

Sprint 19 regression deliberately writes a row and throws inside a transaction, then verifies that the row does not survive.

## Canonical foundational schema

Sprint 19 publishes exactly one canonical application migration:

`apps/web/database/migrations/0000_00_00_000001_create_foundational_context_graph.php`

It creates exactly these six tables:

1. `oneqay_tenants`;
2. `oneqay_identities`;
3. `oneqay_organizations`;
4. `oneqay_identity_organizations`;
5. `oneqay_outlets`;
6. `oneqay_devices`.

No data is seeded.

No POS business table is introduced.

## Tenant-aware keys

Tenant-owned identifiers are intentionally composite with `tenant_id`.

Examples include:

- identity primary key: `(tenant_id, id)`;
- organization primary key: `(tenant_id, id)`;
- outlet primary key: `(tenant_id, id)`;
- device primary key: `(tenant_id, id)`;
- membership primary key: `(tenant_id, identity_id, organization_id)`.

As a result, the same identity, organization, outlet, or device identifier may exist independently under different tenants.

This preserves the existing M7.2 invariant that a global-looking identifier does not bypass tenant scope.

## Tenant-aware foreign keys

Database constraints provide defense in depth in addition to Application authorization.

The migration includes tenant-aware relationships for:

- identity → tenant;
- organization → tenant;
- membership → tenant;
- membership → identity under the same tenant;
- membership → organization under the same tenant;
- outlet → organization under the same tenant;
- device → organization under the same tenant;
- device → outlet under the same tenant.

A database row owned by tenant A therefore cannot validly reference a parent key that exists only under tenant B.

Sprint 19 regression directly attempts such a cross-tenant relationship and requires the database to reject it.

## Forward-only migration boundary

The canonical migration is forward-only.

Its `down()` operation throws:

`Forward-only generated migration; rollback is not authorized.`

Sprint 19 does not introduce automatic destructive rollback authority.

## Application service and Infrastructure defense in depth

Tenant safety is deliberately enforced at multiple layers:

1. request/server verification establishes `VerifiedTenantContext`;
2. Application service checks context tenant equals graph tenant;
3. repository reads are tenant scoped;
4. transaction protects atomic graph creation;
5. exact readback rejects ownership conflicts;
6. relational composite keys prevent cross-tenant parent relationships.

No one layer is treated as a substitute for the others.

## M7.2 reconciliation

M7.2 originally prohibited all migration/database implementation because it predated the governed migration program.

Sprint 19 removes only that obsolete assumption.

The M7.2 regression still preserves:

- missing-context denial;
- malformed-context denial;
- client tenant hints are not authoritative;
- request-context cleanup;
- no default tenant;
- cross-tenant read denial;
- cross-tenant write denial;
- same global identifier cannot bypass tenant scope;
- generic non-leaking denial messages;
- Domain/Application framework independence;
- synthetic fixture controls.

It now additionally requires:

- exact one-file Sprint 19 migration set;
- composite tenant-aware migration markers;
- forward-only migration boundary;
- framework-independent Application persistence contracts;
- explicit tenant predicates in the Infrastructure repository;
- no unrestricted ownership-rewriting upsert behavior;
- Local/Test/CI-only Infrastructure runtime gate.

The workflow allowlist remains path-specific rather than allowing arbitrary application changes.

## Local/Test/CI durable persistence regression

`apps/web/tests/persistence.php` runs inside the existing M7.1 application test harness.

It uses a temporary SQLite database file and proves:

1. persistence is disabled by default;
2. Preview runtime is denied before schema/persistence mutation;
3. the exact canonical migration executes in-process;
4. all six foundational tables exist;
5. tenant-alpha graph persists and reads back exactly;
6. identical repeat persistence remains one row per tenant-owned key;
7. tenant-beta can independently use the same identity/organization/outlet/device identifiers;
8. tenant-alpha cannot read a beta-only graph;
9. alpha verified context cannot persist a gamma graph;
10. relationship-conflict attempts fail closed;
11. conflict transactions do not leave partial rows;
12. deliberate transaction failure rolls back completely;
13. a direct cross-tenant parent relationship is rejected by database constraints;
14. temporary database and workspace are removed.

Only synthetic identifiers are used.

## Preservation of Sprint 18 proof

`apps/web/tests/run.php` still executes the complete Sprint 18 disposable migration execution proof.

The obsolete assertion that `config/database.php` must not exist is superseded by the stronger Sprint 19 assertion that:

- guarded database configuration exists; and
- durable persistence remains disabled by default during ordinary application boot.

After Sprint 18 cleanup, the same application regression runs the Sprint 19 durable persistence proof.

This preserves historical capability while progressing the application architecture.

## No dependency changes

Sprint 19 uses the existing locked Laravel/database dependency set.

It does not modify:

- `apps/web/composer.json`;
- `apps/web/composer.lock`;
- `apps/web/package.json`;
- `apps/web/package-lock.json`.

## Explicit non-scope

Sprint 19 does not authorize:

- Preview durable persistence;
- cPanel/live database persistence;
- Production persistence;
- Production migration execution;
- customer data;
- Catalog persistence;
- Sale persistence;
- Payment Recording persistence;
- Inventory persistence;
- Shift/Register persistence;
- deployment;
- Release/GitHub Release;
- updater activation;
- Production readiness.

## Next candidate stage

After Sprint 19 is published and all regressions are green, the next candidate should build on this foundation rather than bypass it.

A bounded candidate is **Sprint 20 — Durable Identity & Organizational Access Persistence Foundation**, extending persisted membership/access semantics while retaining tenant-aware constraints and Local/Test/CI-first authority.

That future stage requires its own entry gate.

Production readiness remains **NO-GO**.
