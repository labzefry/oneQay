# DEC-005 — Database Engine and Physical Tenancy Model Decision Record

> **Status:** Approved Historical Decision — Partially Superseded by DEC-005R
> **Phase:** 0 — Governance & Discovery
> **Canonical product:** oneQay
> **Developer & Product Engineering Entity:** Lab | zefry
> **Repository:** `labzefry/oneQay`
> **Product Owner:** `labzefry`

## Current precedence after DEC-005R

DEC-005 remains the approved historical record of the database decision made on 2026-08-09. The later substantive **DEC-005R — Portable Relational Persistence Architecture** partially supersedes only the applicable engine-selection and portability portions. Historical DEC-005 wording below is intentionally preserved as evidence of what was approved at that time.

- D-005-01: **SUPERSEDED**.
- D-005-02: **SUPERSEDED**.
- D-005-03: **PRESERVED**.
- D-005-04: **PRESERVED**.
- D-005-05: **PRESERVED**.
- D-005-06: **PRESERVED AND EXPANDED**.
- D-005-07: **PRESERVED AND EXPANDED**.
- D-005-08: **PRESERVED**.
- D-005-09: **PARTIALLY SUPERSEDED / MATERIALLY EXPANDED**.
- D-005-10: **PRESERVED AS HISTORICAL FACT**.

For current-facing relational-engine architecture, `docs/handbook/DEC_005R_DECISION_RECORD.md` governs the superseded portions. This supersession notice does not grant source, schema, SQL, migration, DBME, M7.5, deployment, release, or Production authority.

## Decision provenance

- Decision: **DEC-005 — Database Engine and Physical Tenancy Model**.
- Decision result: **APPROVED**.
- Product Owner decision baseline: `63646e1cccc611a1911c452397059983030dfe66`.
- Verified decision baseline tree: `80cd3bbf1a0c1d454e73c89f17d8896941f369cd`.
- Decision date: 2026-08-09.
- Decision authority type: **Substantive technology / architecture decision only**.
- Publication-preparation authority: **SEPARATELY GATED**.
- ADR-003 repository acceptance: **SEPARATELY GATED THROUGH PUBLICATION**.
- Database implementation authority: **NOT GRANTED**.
- Schema authority: **NOT GRANTED**.
- SQL / DDL authority: **NOT GRANTED**.
- Migration authority: **NOT GRANTED**.
- Package / dependency authority: **NOT GRANTED**.
- Sprint 14 authority: **NOT GRANTED**.
- Deployment authority: **NOT GRANTED**.
- Release authority: **NOT GRANTED**.
- Production authority: **NOT GRANTED**.
- Production readiness: **NO-GO**.

The baseline SHA and tree above are stable substantive-decision provenance. They are not a claim that those identifiers remain permanently current. GitHub must be freshly verified before every later lifecycle mutation.

## Enterprise product boundary

oneQay remains an **Enterprise Intelligent Business Management Platform**.

DEC-005 establishes only the canonical relational database-engine family, physical tenancy direction, tenant-isolation enforcement principle, database/application authority boundary, schema-evolution principle, recoverability principle, and portability/scale direction needed to guide later authorized implementation.

DEC-005 does not redefine oneQay as a database product, does not expand the first MVP slice, and does not grant application, database, deployment, release, or production implementation authority.

The approved first bounded MVP delivery slice from DEC-001 remains:

**POS CORE TRANSACTION & OUTLET OPERATIONS**.

## Decision summary

| ID | Decision | Disposition |
| --- | --- | --- |
| D-005-01 | Canonical relational database engine family | **APPROVED — MySQL Server** |
| D-005-02 | Exact-version boundary | **APPROVED — supported MySQL LTS family; exact series/minor/patch deferred** |
| D-005-03 | Default physical tenancy model | **APPROVED — shared database + shared schema + immutable tenant isolation key** |
| D-005-04 | Future dedicated / hybrid isolation direction | **APPROVED — bounded hybrid evolution path** |
| D-005-05 | Tenant-isolation enforcement principle | **APPROVED — Application-authoritative with database defense-in-depth** |
| D-005-06 | Database / Application authority boundary | **APPROVED — database/vendor behavior is Infrastructure concern** |
| D-005-07 | Migration and schema-evolution principle | **APPROVED** |
| D-005-08 | Backup / restore / recoverability principle | **APPROVED** |
| D-005-09 | Portability and scale-evolution principle | **APPROVED** |
| D-005-10 | ADR-003 disposition | **APPROVED — materially revise ADR-003 and publish through governed lifecycle** |

## D-005-01 — Canonical relational database engine family

**APPROVED: MySQL Server.**

oneQay uses **MySQL Server** as its canonical relational database engine family.

The historical Technical Preview phrase **“MySQL-compatible engine”** is preserved only as provenance. It is not the current canonical technology decision.

MariaDB and PostgreSQL remain technically viable alternatives only if a future separately authorized architecture decision supersedes DEC-005 on material evidence. DEC-005 does not approve either as an interchangeable canonical production engine.

## D-005-02 — Exact-version boundary

**APPROVED: supported MySQL LTS family.**

The exact MySQL major/LTS series, minor version, and patch version remain **DEFERRED**.

At later implementation/runtime selection time, the chosen version must:

- belong to a supported MySQL LTS family;
- remain under applicable active maintenance;
- be supported by the authorized runtime;
- remain compatible with the authorized PHP/Laravel runtime;
- satisfy required security maintenance;
- satisfy required backup, restore, and recovery capability.

Innovation-track selection is not authorized by DEC-005.

DEC-009 retains ownership of Stage 1 runtime requirements. DEC-005 does not select a hosting provider, managed database provider, exact server package, or exact runtime topology.

## D-005-03 — Default physical tenancy model

**APPROVED: shared database + shared schema + mandatory immutable tenant isolation key.**

This is the default physical tenancy model for oneQay.

Tenant isolation must never depend only on:

- subdomain;
- URL;
- request header;
- client input;
- frontend state;
- mobile state;
- unguessable identifiers.

Every tenant-owned relational resource must be governed by validated tenant context and tenant-aware integrity rules according to later authorized physical design.

This decision establishes architecture direction only. It does not define tables, columns, keys, foreign keys, indexes, SQL, DDL, or migrations.

## D-005-04 — Future dedicated / hybrid isolation direction

**APPROVED: bounded hybrid evolution path.**

Shared schema remains the default.

A later separately authorized architecture/implementation may introduce dedicated database or stronger physical storage isolation for specifically justified tenants, including where material evidence establishes:

- regulatory requirements;
- jurisdiction requirements;
- contractual enterprise isolation;
- materially different scale;
- noisy-neighbor control needs;
- recovery requirements;
- security-classification requirements.

DEC-005 does not authorize tenant database routing, database-per-tenant provisioning, dedicated tenant databases, tenant migration between isolation tiers, automatic isolation-tier promotion, shard routing, or any related implementation.

## D-005-05 — Tenant-isolation enforcement principle

**APPROVED: Application-authoritative tenant authorization with database defense-in-depth.**

Required architecture principles:

1. Tenant context is established and validated server-side.
2. Missing, invalid, or inconsistent tenant context fails closed.
3. Client-supplied tenant identity alone is never sufficient authorization.
4. Tenant identity is immutable as the isolation identity.
5. Tenant-owned data is explicitly classified.
6. Relevant uniqueness and referential integrity remain tenant-aware.
7. Repository/data-access boundaries enforce tenant scope.
8. Global identifiers do not bypass tenant authorization.
9. Privileged cross-tenant operations require explicit application contracts and audit.
10. Web, PWA, and Android clients never directly access database tables.

Database-native security mechanisms may provide additional defense-in-depth. They do not silently replace Application ownership of tenant authorization.

## D-005-06 — Database / Application authority boundary

**APPROVED: database engine and database-specific behavior are Infrastructure concerns.**

DEC-005 preserves DEC-002:

- PHP;
- Laravel;
- Modular Monolith First;
- Clean Architecture;
- framework-independent Domain/Application.

Domain and Application must not depend on:

- MySQL-specific client APIs;
- MySQL server objects;
- vendor-specific storage-engine concepts;
- database-administration topology;
- hosting-provider APIs.

Bounded engine-specific optimizations may later exist inside authorized Infrastructure implementations where justified.

Database portability means preserving clean architecture boundaries. It does not require intentionally reducing every future database operation to the weakest feature set shared by every relational engine.

## D-005-07 — Migration and schema-evolution principle

**APPROVED:**

- VERSIONED;
- DETERMINISTIC;
- COMPATIBLE;
- RECOVERABLE;
- RECONCILABLE.

Schema evolution follows:

**EXPAND → MIGRATE → VERIFY → CONTRACT**

where destructive or mixed-version evolution requires staged compatibility.

Later authorized implementation must account for:

- compatibility windows;
- lock impact;
- migration duration;
- rollback or recovery strategy;
- backup/recovery prerequisites;
- representative-data rehearsal;
- resumability where applicable;
- idempotency where applicable;
- post-migration reconciliation;
- observability.

DEC-005 does not authorize an actual schema, migration, SQL statement, DDL operation, seeder, or database change.

## D-005-08 — Backup / restore / recoverability principle

**APPROVED: backup success is not recoverability.**

Recoverability requires verified restoration.

The database architecture must remain compatible with:

- protected full backup;
- incremental / point-in-time recovery direction where supported by the authorized runtime;
- controlled backup access;
- backup-integrity monitoring;
- isolated restore rehearsal;
- post-restore verification;
- platform-level disaster recovery;
- future tenant-scoped recovery procedures.

Under shared-schema tenancy, tenant-specific recovery must not be assumed solved merely because a physical database backup exists.

Final numerical RPO, RTO, and support objectives remain owned by DEC-012 and are not approved here.

## D-005-09 — Portability and scale evolution

**APPROVED: FUTURE-COMPATIBLE, NOT FUTURE-OVERENGINEERED.**

The database architecture must support bounded evolution from a simple Stage 1 runtime toward, when separately justified:

- managed relational database operation;
- read replicas;
- high availability;
- reporting/read-model separation;
- larger tenants;
- stronger tenant physical isolation;
- container/Kubernetes runtime;
- partitioning or sharding only after evidence.

DEC-005 does not require or authorize HA, replicas, Kubernetes, partitioning, sharding, or dedicated tenant databases for initial implementation.

## D-005-10 — ADR-003 disposition

**APPROVED: materially revise ADR-003, then publish through the separate governed publication lifecycle.**

`docs/adr/ADR-003-technical-preview-database-tenancy.md` remains the correct ADR number because it is currently Proposed and already owns the historical database/physical-tenancy provenance.

ADR-003 must preserve its historical Technical Preview / D1 provenance while being reframed as the bounded representation of substantive DEC-005.

The substantive decision does not make ADR-003 repository-canonical or Accepted by itself. Repository representation and ADR acceptance become canonical only through the separately authorized publication lifecycle.

## Historical Technical Preview provenance

Before substantive DEC-005, ADR-003 recorded a Proposed Technical Preview v0.0.1 candidate from Issue #23:

- D1 “MySQL-compatible” engine;
- one database / shared schema;
- mandatory tenant identity;
- engine/version dependent on hosting evidence;
- PostgreSQL shared-schema/RLS considered as an alternative;
- database/schema-per-tenant considered operationally too complex for the accelerated preview.

That historical candidate is retained for provenance. DEC-005 supersedes its ambiguous current-facing technology wording without rewriting the fact that D1 existed as a Proposed Technical Preview candidate.

## Alternatives considered

### MySQL Server

Selected as the canonical engine family because it provides the required relational/transactional foundation while fitting oneQay's staged operational portability and approved PHP/Laravel direction. Exact version and runtime remain separately gated.

### MariaDB

Considered a viable relational alternative and historically close to the “MySQL-compatible” candidate language. It was not selected as the canonical interchangeable engine because DEC-005 requires an explicit engine family rather than an ambiguous compatibility category.

### PostgreSQL

Considered a strong relational alternative, including database-native isolation capabilities. It was not selected as the canonical engine for DEC-005. Database-native authorization mechanisms remain defense-in-depth rather than a transfer of authoritative tenant authorization from Application.

### T1 — shared database + shared schema

Selected as the default because it minimizes initial provisioning, migration fan-out, connection/topology complexity, and operational overhead while retaining strict tenant isolation through validated context and tenant-aware integrity controls.

### T2 — schema-per-tenant

Not selected as the canonical default because it increases provisioning and migration fan-out and is not the intended portable default across the selected engine direction.

### T3 — database-per-tenant

Not selected as the canonical default because it materially increases provisioning, connection, migration, backup, restore, and operations complexity for the bounded initial delivery stage.

### T4 — hybrid / tiered isolation

Approved only as a bounded future evolution path. It is not an instruction to implement multiple tenancy tiers now.

## Preserved decision boundaries

### DEC-001

DEC-001 remains owner of MVP scope/non-scope. DEC-005 does not expand the approved first delivery slice.

### DEC-002

DEC-002 remains owner of PHP/Laravel and Modular Monolith First + Clean Architecture. Database/vendor specifics remain outside Domain/Application.

### DEC-003

DEC-003 remains owner of first-party Web/PWA delivery. Frontend state never becomes physical tenant-isolation authority.

### DEC-004

DEC-004 remains owner of Android delivery direction. Android never accesses database tables directly and never becomes authoritative for server-side tenant isolation.

### DEC-006

DEC-006 remains owner of authentication, MFA, session architecture, token/session lifecycle, and identity recovery. DEC-005 defines only database-side isolation/integrity expectations needed to coexist with later authentication/authorization architecture.

### DEC-007

DEC-007 remains owner of payment-provider and compliance boundaries. DEC-005 does not select or implement a payment provider.

### DEC-008

DEC-008 remains the exclusive owner of offline POS transaction semantics, synchronization, replay, conflict resolution, reconciliation, and disconnected transaction authority. DEC-005 does not define offline numbering, local database semantics, or sync conflict policy.

### DEC-009

DEC-009 remains owner of Stage 1 deployment runtime requirements. DEC-005 does not choose a hosting vendor, managed database service, container topology, or exact MySQL package/version.

### DEC-010

DEC-010 remains owner of product license and third-party notice policy. DEC-005 does not resolve the full product licensing/notice decision.

### DEC-011

DEC-011 remains owner of data retention, privacy policy, and jurisdiction. DEC-005 allows physical-isolation implications to be evaluated later without setting retention periods or jurisdiction policy.

### DEC-012

DEC-012 remains owner of final RPO, RTO, and support objectives. DEC-005 establishes recoverability principles only.

## Explicit deferments

The following remain not approved by DEC-005:

- exact MySQL LTS series;
- exact MySQL minor/patch version;
- database server configuration;
- charset/collation;
- connection-pool values;
- database timeout values;
- database memory/buffer settings;
- physical schema;
- table names;
- columns;
- physical primary keys;
- physical foreign keys;
- actual indexes;
- SQL;
- DDL;
- Laravel migrations;
- seeders;
- exact UUID/ULID strategy;
- database credentials;
- production connection strings;
- database/hosting provider;
- managed database product;
- replication topology;
- HA topology;
- Kubernetes database topology;
- shard keys;
- partition definitions;
- tenant-provisioning implementation;
- dedicated-tenant database implementation;
- isolation-tier routing;
- tenant-migration implementation;
- tenant-restore implementation;
- exact backup product;
- exact encryption/key-management implementation;
- authentication/MFA/session implementation;
- offline transaction/synchronization implementation;
- payment implementation;
- retention periods;
- final RPO/RTO;
- deployment;
- release;
- production migration.

## Fitness and validation direction

Later implementation authority should require evidence that the selected runtime and design can satisfy at minimum:

- transaction correctness for POS and inventory operations;
- tenant-isolation negative tests;
- tenant-aware integrity constraints;
- failure-closed tenant context;
- bounded privileged cross-tenant operations and audit;
- migration compatibility and recovery rehearsal;
- backup integrity and successful restore rehearsal;
- representative query-plan/performance review;
- supported MySQL LTS runtime verification;
- no database/vendor dependency leakage into Domain/Application.

These are validation directions, not implementation authorization.

## Authority boundary

DEC-005 grants **substantive database architecture decision authority only**.

It does not grant:

- repository mutation beyond separately granted publication preparation;
- schema design;
- SQL / DDL;
- migrations;
- seeders;
- database installation/configuration;
- package/dependency changes;
- Laravel database implementation;
- tenant-provisioning implementation;
- dedicated-tenant implementation;
- backup/restore implementation;
- Sprint 14;
- deployment;
- release;
- production database modification;
- production-readiness promotion.

## Program state after substantive DEC-005

- DEC-005 substantive decision: **APPROVED / DECISION COMPLETE**.
- ADR-003 repository representation: requires separately governed publication.
- Phase 0: **IN PROGRESS**.
- Final/business/production application implementation: **BLOCKED without separate authority**.
- Sprint 14: **NOT AUTHORIZED**.
- Production readiness: **NO-GO**.

Attribution: Lab | zefry
