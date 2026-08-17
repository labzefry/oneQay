# Sprint 14 Entry Gate — Migration Planning Artifact Foundation

## Identity

- Product: `oneQay`
- Developer and Product Engineering Entity: **Lab | zefry**
- Repository: `labzefry/oneQay`
- Decision date: 2026-08-17
- Exact entry-gate base: `225fba522435480f5577f171d9cb1ff5c4be9a76`
- Exact entry-gate base tree: `75f25ff2fbcd4c991ce0f4ded5edd1e0d0e55097`
- Phase 0: **COMPLETE / EXIT APPROVED / PUBLISHED**
- M7 Technical Preview: **COMPLETE / ACCEPTED**
- Production readiness: **NO-GO**

GitHub is the Single Source of Truth. Fresh verification is required before every lifecycle mutation.

## Product Owner direction

After canonical Phase 0 Exit publication, the Product Owner directed the project to **continue to the next step**. The immediately preceding lifecycle checkpoint identified that next gate as **Sprint 14 Planning / Implementation Authority**.

This entry gate records that direction as authorization for the bounded Sprint 14 scope below, including its Local/Test/CI implementation and ordinary Ready/Merge lifecycle after exact-head required checks and the repository-native Product Owner merge-authority gate succeed.

Independent review is not an additional requirement for this authorized continuation model.

This authorization does **not** grant Release, GitHub Release, Production, Production/customer data, real payment, live cPanel/database mutation, migration execution, deployment, or updater installation authority.

## Sprint 14 name

**Migration Planning Artifact Foundation**

## Why this is the next bounded capability

Sprint 12 published deterministic `PhysicalSchemaPlan` representation and conservative schema-change classification.

Sprint 13 published `SchemaChangeReviewEnvelope`, whose strongest positive decision is exactly `APPROVED_FOR_MIGRATION_PLANNING`. Sprint 13 explicitly states that this decision is not migration execution authority and only permits a future separately authorized capability to produce non-executable migration-planning material.

That missing non-executable planning layer is therefore the narrowest direct successor to Sprint 13. It closes the architectural gap without jumping directly to durable application persistence, executable migrations, or live database mutation.

## Controlled outcome

Produce a deterministic, immutable, safe, **non-executable migration planning artifact** from an exact `PhysicalSchemaPlan` plus its matching Sprint 13 review envelope.

The artifact exists only to describe approved additive schema-change intent using safe identifiers, stable change identifiers, and fingerprints. It must never contain executable SQL or enough runtime authority to apply a schema change.

## Authorized implementation paths

Sprint 14 implementation is limited to exactly these paths:

1. `src/SchemaPlanning/Foundation.php`
2. `src/SchemaPlanning/MigrationPlanning.php` — new
3. `tests/schema-planning.php`
4. `docs/MIGRATION_PLANNING_ARTIFACT_FOUNDATION.md` — new

`composer.json` is intentionally excluded because `src/SchemaPlanning/Foundation.php` is already the autoload entry for this module and `tests/schema-planning.php` is already included in `composer test`.

Any additional changed path is blocking and requires a new Product Owner scope decision.

## Required semantics

### Review binding

The planning builder must verify that:

- the supplied review envelope fingerprints the exact supplied `PhysicalSchemaPlan`;
- the review source disposition matches the supplied plan disposition;
- the review source correlation ID matches the supplied plan correlation ID;
- only `APPROVED_FOR_MIGRATION_PLANNING` + `REVIEW_APPROVED` may produce a migration planning artifact;
- `NOT_REQUIRED`, `REJECTED`, and any `BLOCKED` state cannot produce an executable or actionable migration plan.

### Allowed change kinds

Only Sprint 12 additive `REVIEW_REQUIRED` kinds may be represented:

- `ENTITY_CREATED`;
- `ATTRIBUTE_ADDED`;
- `UNIQUE_INDEX_ADDED`;
- `REFERENCE_ADDED`.

Any unsupported, destructive, tenant-boundary, tenant-key, vendor, physical-mapping, primary-index, removal, or mutation kind must fail closed even if presented through malformed or inconsistent input.

### Planning artifact shape

The artifact may contain only:

- source plan fingerprint;
- source review correlation ID;
- planning correlation ID;
- reviewer reference;
- baseline and target manifest fingerprints;
- deterministic ordered planning steps;
- each step's stable source change ID;
- additive change kind;
- safe entity identifier;
- optional safe component identifier;
- before/after fingerprints where already present in the published schema plan.

It must not contain raw manifests, raw column definitions, SQL, database names, credentials, endpoints, filesystem paths, tenant records, production data, arbitrary exception text, or executable instructions.

## Determinism and immutability

Equivalent plan + review + planning correlation inputs must produce byte-equivalent JSON.

The planning artifact and each planning step must be immutable/read-only.

No current timestamp is embedded because it would break deterministic canonical output.

## Database and migration boundary

Sprint 14 must not:

- generate SQL, DDL, or DML;
- create framework migration files;
- open a database connection;
- inspect database metadata;
- execute a migration;
- mutate a schema;
- create or backfill tables;
- define online-schema-change execution;
- acquire a migration lock;
- perform rollback execution;
- access cPanel or a live target;
- convert a planning artifact into deployment authority.

A Sprint 14 planning artifact is evidence for a **future separately authorized migration-generation or persistence capability only**.

## Security and tenant-isolation boundary

- Deny by default.
- Sprint 12 `BLOCKED` semantics remain non-overridable.
- Tenant-scope and tenant-key changes remain prohibited.
- No tenant record or tenant-owned data may be read or emitted.
- No secret, credential, database name, endpoint, or private path may be accepted as planning payload.
- Safe output uses only existing bounded identifiers and fingerprints.

## Explicit non-scope

Sprint 14 does not include:

- durable POS persistence;
- final business schema;
- application database repositories;
- Laravel migration/seeder files;
- data backfill;
- live database qualification or mutation;
- deployment or release;
- updater runtime wiring;
- Production or pilot readiness;
- Production/customer data;
- payment-provider integration or real-money processing;
- ERP/CRM/HRM or other business-module expansion;
- GD-007 promotion;
- JRN-003 or JRN-013 resolution.

## Required test coverage

Exact-head tests must demonstrate:

1. an approved additive plan produces a planning artifact;
2. equivalent inputs produce byte-equivalent JSON;
3. artifact and steps are read-only;
4. source plan fingerprint is re-derived and must match the review envelope;
5. plan disposition and source correlation ID must match the review envelope;
6. rejected review cannot produce a planning artifact;
7. `NOT_REQUIRED` review cannot produce a planning artifact;
8. blocked plan cannot produce a planning artifact;
9. tenant-scope and tenant-key blocked changes cannot be planned;
10. only additive Sprint 12 change kinds are accepted;
11. stable source change identifiers are preserved;
12. deterministic ordering is preserved;
13. safe JSON contains no raw manifest, SQL, credential, endpoint, path, tenant record, or arbitrary sensitive payload;
14. source contains no database connection, network dependency, or filesystem side effect;
15. all Sprint 12 and Sprint 13 schema-planning regressions remain green;
16. full `composer test` remains green through repository CI.

## Required exact-head CI

Before merge, all applicable existing pull-request workflows must be successful on the exact Sprint 14 head, including:

- Governance Required Checks;
- PHP Foundation Regression;
- M7.1 Application Regression.

The application regression must continue to pass its deterministic dependency locks, Composer/npm High/Critical advisory gates, PHP syntax validation, Vue/TypeScript type-check, Vite build, and application regression.

## Acceptance criteria

Sprint 14 is technically acceptable only when:

- exactly the four authorized implementation paths changed;
- the planning artifact requires an exact matching approved Sprint 13 review;
- unsupported or blocked changes fail closed;
- tenant-boundary protections cannot be overridden;
- output is deterministic and immutable;
- no SQL, migration file, database access, schema mutation, network call, filesystem side effect, deployment, release, or Production behavior exists;
- bounded tests and repository regressions pass on the exact head;
- Product Owner exact-head merge authority succeeds.

## Lifecycle after Sprint 14

Successful Sprint 14 publication authorizes no automatic successor implementation.

The likely subsequent engineering decision is a separately bounded choice between:

- migration artifact generation for a selected framework/runtime;
- application durable persistence/migration-seeder foundation;
- another Phase 1 Platform Foundation gap identified by fresh canonical verification.

That decision must be made from the post-Sprint-14 canonical state.

## Preserved lifecycle boundaries

- Phase 0: **COMPLETE / EXIT APPROVED / PUBLISHED**
- Sprint 14: **AUTHORIZED by this Product Owner continuation, bounded to this entry gate**
- Release / GitHub Release: **NOT AUTHORIZED**
- Deployment/live target mutation: **NOT AUTHORIZED**
- Migration execution/live schema mutation: **NOT AUTHORIZED**
- Production: **NOT AUTHORIZED**
- Production readiness: **NO-GO**

Attribution: **Lab | zefry**
