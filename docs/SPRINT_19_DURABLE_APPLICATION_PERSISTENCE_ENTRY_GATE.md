# Sprint 19 Entry Gate — Durable Application Persistence Foundation

## Identity

- Product: `oneQay`
- Developer and Product Engineering Entity: **Lab | zefry**
- Repository: `labzefry/oneQay`
- Decision date: 2026-08-17
- Exact entry-gate base: `b60e2d881b5e3a2679067f30ea2601fde0a0dd5d`
- Exact entry-gate base tree: `27740a19445484be95e9b2b560fa54da044f840b`
- Sprint 18 Governed Migration Execution Foundation: **COMPLETE / IMPLEMENTED / PUBLISHED**
- Production readiness: **NO-GO**

GitHub remains the Single Source of Truth. Fresh verification is required before every lifecycle mutation.

Attribution: **Lab | zefry**

## Product Owner continuation

The Product Owner directed the project to continue immediately to the next bounded engineering stage after Sprint 18.

This entry gate records authority for **Sprint 19 — Durable Application Persistence Foundation**, including Local/Test/CI implementation and ordinary Ready/Merge lifecycle after exact-head required checks and the repository-native Product Owner merge-authority gate succeed.

Independent review is not an additional requirement under the current Product Owner continuation model.

This authority does **not** grant Preview/cPanel/live database mutation, Production migration execution, customer-data persistence, deployment, Release/GitHub Release, updater activation, or Production readiness.

## Why Sprint 19 is the next bounded capability

Sprint 18 proves that exact governed Laravel migration source can be revalidated and executed against a disposable test database under deterministic lock/journal control.

The application still does not have permanent business persistence authority. Existing application tenant, identity, organization, outlet, and device context is framework-independent and primarily server/synthetic in-memory infrastructure.

Sprint 19 therefore establishes the first **durable application persistence contract and implementation** while preserving the existing architectural boundaries:

- Clean Architecture / DDD inward dependency;
- tenant context first-class;
- deny-by-default authorization;
- tenant-owned query scoping;
- portable relational persistence direction;
- framework/vendor code confined to Infrastructure;
- transaction boundary explicit;
- no POS business persistence yet.

## Discovery constraints carried forward

Fresh canonical inspection confirms:

1. `src/Persistence/Foundation.php` already provides a fail-closed MySQL/MariaDB connection boundary and secret-aware configuration primitives. Sprint 19 must not duplicate or replace that qualification work.
2. `apps/web/app/Domain/**` and `apps/web/app/Application/**` are protected by framework-independence regression checks.
3. tenant isolation currently allows the same resource identifier to exist under different tenants; persistence must preserve that property and must not make tenant-owned identifiers globally unique by accident.
4. `apps/web/config/database.php` does not yet exist.
5. `apps/web/database/migrations/` does not yet exist as canonical application authority.
6. the M7.2 regression currently encodes a historical `no database/migrations` assumption that predates Sprint 16–18 and must be reconciled rather than silently bypassed.
7. no dependency or lockfile change is required for the bounded implementation because Laravel `12.64.0` and database support are already locked.

## Controlled outcome

Sprint 19 may establish this Local/Test/CI-only application persistence chain:

`VerifiedTenantContext`

+ durable context graph

+ application repository contract

+ explicit transaction boundary

→ persistence runtime gate

→ tenant-scoped Laravel query-builder repository

→ canonical forward-only foundational migration

→ deterministic durable relationship graph

→ exact tenant-scoped readback verification

→ cross-tenant read/write denial

→ idempotent same-graph persistence

→ conflicting relationship fail-closed behavior.

The bounded durable graph is:

`Tenant → Identity → Organization → Outlet → Device`

with identity-to-organization membership represented explicitly.

No Catalog, Sale, Payment Recording, Inventory, Shift/Register, customer profile, payroll, financial transaction, or other POS business persistence is authorized by Sprint 19.

## Exact authorized implementation paths

Sprint 19 source implementation is limited to exactly these paths:

1. `apps/web/config/database.php` — new;
2. `apps/web/environment.example`;
3. `apps/web/app/Application/Persistence/DurablePersistenceViolation.php` — new;
4. `apps/web/app/Application/Persistence/DurableContextGraph.php` — new;
5. `apps/web/app/Application/Persistence/DurableContextGraphRepository.php` — new;
6. `apps/web/app/Application/Persistence/PersistenceTransaction.php` — new;
7. `apps/web/app/Application/Persistence/DurableContextGraphService.php` — new;
8. `apps/web/app/Infrastructure/Persistence/LaravelDurableContextGraphRepository.php` — new;
9. `apps/web/app/Infrastructure/Persistence/LaravelPersistenceTransaction.php` — new;
10. `apps/web/app/Providers/AppServiceProvider.php`;
11. `apps/web/database/migrations/0000_00_00_000001_create_foundational_context_graph.php` — new;
12. `apps/web/tests/persistence.php` — new;
13. `apps/web/tests/run.php`;
14. `apps/web/tests/tenant-isolation.php`;
15. `.github/workflows/m7-2-tenant-isolation-regression.yml`;
16. `docs/DURABLE_APPLICATION_PERSISTENCE_FOUNDATION.md` — new.

No other repository path is authorized by this entry gate.

If implementation discovery requires another path, Sprint 19 must stop and publish a bounded supplement before source mutation outside this envelope.

## Explicit non-scope

Sprint 19 must not modify:

- `src/Persistence/Foundation.php`;
- `src/Migration/Foundation.php`;
- Sprint 16–18 SchemaPlanning implementation;
- `apps/web/composer.json`;
- `apps/web/composer.lock`;
- `apps/web/package.json`;
- `apps/web/package-lock.json`;
- routes/controllers/UI;
- updater/deployment/release source;
- Preview/cPanel qualification source;
- Production environment files;
- POS domain persistence.

## Runtime enablement policy

Canonical application persistence must be **disabled by default**.

The repository may publish placeholder configuration keys only. No real database credential may be committed.

The minimum environment switch is:

`ONEQAY_PERSISTENCE_ENABLED=false`

Sprint 19 may permit persistence only when the runtime class is one of:

- `local`;
- `test`;
- `ci`.

`preview`, `production`, blank, unknown, or malformed runtime classes must fail closed for Sprint 19 persistence operations even if the persistence-enabled flag is set.

This is intentionally stricter than the broader application runtime-class allowlist because Preview/live database persistence is not authorized by this gate.

## Database configuration and secret boundary

`apps/web/config/database.php` may define a canonical `oneqay` relational connection using dedicated environment variables.

Configuration must:

- remain lazy; application boot must not require a database connection;
- use environment-sourced host, port, database, username, and password;
- keep real credentials outside Git;
- default persistence enablement to false;
- use `utf8mb4` for the relational profile;
- preserve non-persistent connections and framework defaults compatible with the locked Laravel version;
- not make readiness endpoints depend on durable persistence in Sprint 19.

`apps/web/environment.example` must contain placeholders only and must state that persistence remains disabled until an explicitly authorized Local/Test/CI scenario enables it.

## Foundational schema only

The canonical forward-only migration may create only these tables:

1. `oneqay_tenants`;
2. `oneqay_identities`;
3. `oneqay_organizations`;
4. `oneqay_identity_organizations`;
5. `oneqay_outlets`;
6. `oneqay_devices`.

No other permanent application table is authorized.

The migration must not seed data.

The migration `down()` path must remain forward-only and throw the published authorization boundary rather than silently performing destructive rollback.

## Identifier and tenant-key policy

Existing domain identifier formats remain authoritative.

The persistence schema must not force UUID semantics where the existing domain uses bounded canonical string identifiers.

For every tenant-owned table, `tenant_id` is part of the primary or unique identity required for safe relationship enforcement.

Tenant-owned identifiers must support the same identifier value existing under more than one tenant.

Therefore a tenant-owned `id` must not become a globally unique database key unless the domain already guarantees global identity.

## Database-level tenant relationship enforcement

Sprint 19 must implement defense in depth at the relational layer.

At minimum:

- identities reference the owning tenant;
- organizations reference the owning tenant;
- identity/organization membership references both parents under the same tenant;
- outlets reference their organization under the same tenant;
- devices reference their organization and outlet under the same tenant.

Composite tenant-aware keys/foreign keys must prevent constructing a valid relationship from tenant A to a parent row owned by tenant B.

Database constraints supplement application authorization; they do not replace it.

## Application-layer architecture

Domain remains framework-independent.

Application persistence contracts must contain no `Illuminate\`, `Laravel\`, PDO, SQL, Schema facade, or query-builder dependency.

Application types may depend on existing Domain identifiers and existing Application tenancy contracts.

Infrastructure is the only layer authorized to depend on Laravel database abstractions.

## Durable context graph

`DurableContextGraph` represents only the stable identifiers necessary to prove the foundational relationship chain:

- tenant ID;
- platform identity ID;
- organization ID;
- outlet ID;
- device ID.

It contains no password, token, personal profile, customer information, financial value, secret, or POS transaction data.

## Repository contract

The application repository contract must provide bounded operations for:

- persisting one exact context graph;
- reading one exact graph under an explicit tenant scope.

The contract must never expose an unscoped `findById()` style API for tenant-owned records.

Read operations must require tenant identity explicitly.

Infrastructure implementation must include `tenant_id` in every tenant-owned lookup predicate.

## Transaction boundary

Graph persistence must occur inside one explicit application transaction boundary.

The transaction abstraction belongs in Application; Laravel transaction mechanics belong in Infrastructure.

A failed step must roll back the entire graph write in Local/Test/CI regression.

No partial Tenant/Identity/Organization/Outlet/Device graph may remain after a failed transaction.

## Idempotency and relationship conflict policy

Persisting the same exact graph more than once must be idempotent.

Persistence must not silently mutate an existing relationship.

Examples that must fail closed:

- an existing outlet identifier under tenant A is already bound to organization X and a request tries to bind it to organization Y;
- an existing device identifier under tenant A is already bound to a different outlet;
- a membership or parent relationship points across tenants.

The implementation may use insert-if-absent semantics plus exact readback validation; it must not use an unrestricted upsert that silently rewrites parent ownership.

## Verified tenant context requirement

Application persistence service operations require one verified tenant context.

The verified context tenant must equal the graph tenant.

Missing, malformed, or foreign tenant context must fail closed before repository mutation.

A raw request header, query string, route value, cookie, correlation ID, or arbitrary caller string remains non-authoritative.

## Persistence runtime gate

Infrastructure repository and transaction implementations must both fail closed unless:

- persistence is explicitly enabled; and
- runtime class is `local`, `test`, or `ci`.

This check must occur before database mutation.

This prevents a caller from bypassing the application service and directly resolving an Infrastructure persistence component in an unauthorized runtime.

## M7.2 regression reconciliation

The historical M7.2 control currently rejects any migration directory and any database implementation because M7.2 predated the governed migration program.

Sprint 19 is authorized to evolve that check, but not to weaken tenant isolation.

The updated workflow/test must:

- continue to enforce framework independence in Domain/Application;
- continue to enforce request-context isolation and no-default-tenant;
- continue to prove same global identifier values cannot bypass tenant scope;
- remove the obsolete global `no migration directory` assertion;
- permit only the exact Sprint 19 persistence implementation paths;
- inspect the canonical foundational migration for mandatory tenant-aware keys/relationships;
- inspect the Infrastructure repository for explicit tenant-scoped predicates;
- continue to reject database/framework dependencies from Domain and Application;
- run the durable persistence regression in addition to existing M7.1 and M7.2 regressions.

The workflow must not be changed to broadly allow arbitrary `apps/web/**` paths.

## Local/Test/CI regression proof

`apps/web/tests/persistence.php` must prove the bounded capability using a temporary SQLite database file under the test workspace.

The test must:

1. prove persistence is disabled by default;
2. create and configure one disposable SQLite Local/Test/CI connection programmatically;
3. execute the exact canonical Sprint 19 migration in-process;
4. persist one tenant-alpha foundational graph;
5. read it back only through tenant-alpha scope;
6. prove same identifiers can exist independently under tenant-beta;
7. prove tenant-alpha cannot read tenant-beta graph;
8. prove a verified tenant-alpha context cannot persist a tenant-beta graph;
9. prove same-graph persistence is idempotent;
10. prove conflicting parent relationship mutation fails closed;
11. prove a deliberate transactional failure leaves no partial graph;
12. prove tables/keys required by the foundational migration exist;
13. prove journal/credentials/absolute paths are not emitted because Sprint 19 persistence evidence contains no such serialized runtime object;
14. disconnect and delete the temporary database/workspace.

The regression uses synthetic identifiers and contains no customer data.

## No silent Preview/Production promotion

Even after Sprint 19 passes:

- Preview database persistence remains unauthorized;
- cPanel/live database persistence remains unauthorized;
- Production remains unauthorized;
- Production readiness remains **NO-GO**.

A later explicit gate is required before any live or durable Preview target may use the persistence foundation.

## Publication lifecycle

Sprint 19 implementation lifecycle is:

1. create bounded implementation branch from the exact merged entry-gate canonical main;
2. modify only the authorized path envelope;
3. run all triggered CI checks;
4. repair exact implementation defects without widening scope;
5. issue exact-head Product Owner merge authorization for the final head;
6. merge only after repository-required checks succeed;
7. verify canonical post-merge SHA, tree, parent, signature, and final envelope.

If the source head changes, exact Product Owner authorization must be reissued for the new exact head.

Independent human review is not required.

## Exit criteria

Sprint 19 may be declared COMPLETE only if:

- entry gate is published;
- implementation remains inside the exact authorized envelope;
- application database config is disabled by default;
- foundational schema is tenant-keyed and forward-only;
- Domain/Application framework independence remains intact;
- repository APIs are tenant-scoped;
- verified tenant context is required for service operations;
- transaction rollback, idempotency, relationship-conflict denial, and cross-tenant denial regressions pass;
- no dependency/lockfile changes occur;
- no Preview/Production/live authority is introduced;
- required exact-head checks and Product Owner merge-authority status succeed;
- merged canonical main is freshly verified.

Production readiness remains **NO-GO**.
