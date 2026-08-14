# ADR-003 — Database Engine and Physical Tenancy

- Status: Accepted — current representation reconciled to DEC-005R; historical D1 and DEC-005 provenance preserved
- Date: 2026-08-09
- Decision owner: Product Owner `labzefry`
- Substantive authority: DEC-005 historically; current superseding authority DEC-005R — Portable Relational Persistence Architecture
- Decision baseline: `63646e1cccc611a1911c452397059983030dfe66`
- Decision baseline tree: `80cd3bbf1a0c1d454e73c89f17d8896941f369cd`
- Historical evidence: Issue #23 / Technical Preview v0.0.1 D1 candidate
- Scope: bounded relational database-engine and physical-tenancy architecture direction only

## Context

oneQay is an **Enterprise Intelligent Business Management Platform** whose approved first bounded MVP delivery slice is **POS CORE TRANSACTION & OUTLET OPERATIONS**.

DEC-002 already establishes PHP + Laravel, Modular Monolith First + Clean Architecture, and framework-independent Domain/Application. Database technology and physical-tenancy direction therefore must not permit database/vendor concerns to leak into Domain/Application.

This ADR preserves three architecture provenance layers:

1. historical Proposed Technical Preview D1 using **“MySQL-compatible engine”**;
2. historical substantive DEC-005 selecting **MySQL Server** as the canonical engine family;
3. current substantive **DEC-005R — Portable Relational Persistence Architecture**, which partially supersedes DEC-005's sole-engine selection while preserving its shared-schema, tenant-isolation, Infrastructure-boundary, schema-evolution, and recoverability principles.

## Current DEC-005R decision

Current oneQay relational persistence architecture is **Portable Relational Persistence Architecture**.

- Domain and Application remain database-engine-neutral.
- Business rules must not depend on database vendor identity.
- The target across officially qualified relational engine profiles is **ZERO BUSINESS-CODE CHANGE**.
- Engine-specific behavior remains confined to Infrastructure/configuration boundaries.
- A canonical logical schema/contract is separated from engine-specific physical representation.
- MariaDB, MySQL, and PostgreSQL are authorized engine-profile directions; profile identity alone is not runtime qualification.
- MariaDB 11.4 family is the Stage-1 profile direction subject to DEC-009/M7.5 runtime qualification.
- A formal Database Portability Contract, cross-engine qualification, and oneQay Database Mobility & Migration Engine — DBME are approved architecture directions but are not implemented by this ADR publication.
- Unsafe, lossy, ambiguous, or unverified physical conversion must fail closed.

The default physical tenancy remains **shared database + shared schema + mandatory immutable tenant isolation key**. Tenant authorization remains **Application-authoritative with database defense-in-depth**. A bounded stronger physical-isolation path remains available through separate future authority.

## Historical Proposed Technical Preview D1 provenance

The original Proposed ADR-003 direction was:

- D1: a **MySQL-compatible engine**;
- one database / shared schema for the Technical Preview;
- mandatory immutable tenant identity on tenant-owned data;
- tenant-aware uniqueness, foreign-key strategy, indexes, queries, cache keys, jobs, files, audit records, and backup/restore selection;
- request rejection when validated tenant context is absent;
- tenant identity not derived only from subdomain or client input;
- cross-tenant relationships prohibited except explicitly audited platform-level models;
- global-ID lookup still requiring tenant predicate;
- deterministic synthetic tenants for preview testing;
- tenant-isolation negative tests across read, write, enumeration, cache, job, export, file, audit, and restore;
- versioned, deterministic, forward-compatible migrations with rollback/recovery planning.

Historical alternatives included:

- PostgreSQL shared schema with optional RLS defense;
- database/schema-per-tenant isolation.

At the Technical Preview point, the engine/version remained dependent on hosting evidence and no schema, migration, or source-code authority existed.

DEC-005 later superseded only the ambiguous current-facing technology/tenancy direction. DEC-005R subsequently supersedes DEC-005's sole-engine selection. Neither later decision rewrites the fact that the D1 Technical Preview candidate was Proposed under Issue #23.

## Historical DEC-005 representation

The following subsections preserve the Accepted representation of DEC-005 as it existed before DEC-005R. Where they refer to MySQL Server as the sole canonical engine, that status is historical and superseded by the current DEC-005R engine-profile model above.

### Canonical relational database engine family

DEC-005 selected **MySQL Server** as the canonical relational database engine family for oneQay.

The phrase **“MySQL-compatible engine”** was historical provenance only and was not used as DEC-005's canonical engine decision.

MariaDB and PostgreSQL were not approved as interchangeable canonical production engines by DEC-005. DEC-005R is the later authorized architecture decision that supersedes this sole-engine restriction.

### Version boundary

DEC-005 selected a **supported MySQL LTS family** while deferring exact major/LTS series, minor version, and patch version.

DEC-005R supersedes this as the sole global version boundary. Engine/version support is now profile-specific and requires qualification evidence.

DEC-009 retains ownership of Stage 1 runtime requirements and exact hosting/runtime selection.

### Default physical tenancy model

Use:

**shared database + shared schema + mandatory immutable tenant isolation key**

as the default oneQay physical tenancy direction.

This ADR does not define actual schema objects. It establishes the architecture invariant that tenant-owned relational resources remain explicitly tenant-scoped according to later authorized physical design.

### Bounded hybrid evolution path

Shared schema remains the default.

The architecture may later support a dedicated database or stronger physical storage boundary for specifically justified tenants where separately verified requirements establish material need, such as:

- regulatory requirements;
- jurisdiction requirements;
- enterprise contractual isolation;
- materially different scale;
- noisy-neighbor control;
- recovery requirements;
- security classification.

This direction does not authorize database-per-tenant provisioning, isolation-tier routing, tenant migration between tiers, shard routing, or any related implementation.

## Tenant-isolation invariants

Tenant authorization is **Application-authoritative with database defense-in-depth**.

Required principles:

- tenant context is established and validated server-side;
- missing, invalid, or inconsistent tenant context fails closed;
- client-supplied tenant identity alone is never authorization;
- tenant identity is immutable as an isolation identity;
- tenant-owned data is explicitly classified;
- relevant uniqueness and referential integrity remain tenant-aware;
- repository/data-access boundaries enforce tenant scope;
- global identifiers do not bypass tenant authorization;
- privileged cross-tenant operations use separate explicit application contracts and audit;
- Web, PWA, and Android clients never access database tables directly;
- database-native security features may add defense-in-depth but do not silently replace Application authorization ownership.

## Database / Application authority boundary

Database engine and database-specific behavior are **Infrastructure concerns**.

Preserve DEC-002:

- PHP;
- Laravel;
- Modular Monolith First;
- Clean Architecture;
- framework-independent Domain/Application.

Domain/Application must not depend on:

- vendor-specific client APIs;
- vendor-specific server objects;
- storage-engine concepts;
- database-administration topology;
- hosting-provider APIs.

Bounded engine-specific optimization may later exist inside authorized Infrastructure implementations where justified. DEC-005R expands this boundary into qualified relational engine profiles while preserving the zero-business-code-change target.

## Migration and schema-evolution principle

Schema evolution must be:

- versioned;
- deterministic;
- compatible;
- recoverable;
- reconcilable.

Use:

**expand → migrate → verify → contract**

where destructive or mixed-version evolution requires staging.

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

DEC-005R expands the future architecture direction with DBME preflight, compatibility analysis, fail-closed unsafe conversion, reconciliation, controlled cutover, source retention, and rollback only where genuinely safe. This ADR does not define or authorize actual migration files, SQL, DDL, tables, columns, keys, indexes, seeders, or DBME implementation.

## Backup / restore / recoverability principle

**Backup success is not recoverability.**

Recoverability requires verified restoration.

The architecture must remain compatible with:

- protected full backup;
- incremental / PITR direction where supported by the later authorized runtime;
- controlled backup access;
- backup-integrity monitoring;
- isolated restore rehearsal;
- post-restore verification;
- platform-level disaster recovery;
- future tenant-scoped recovery procedures.

Under shared-schema tenancy, tenant-specific recovery must not be assumed solved merely because a physical database backup exists.

Final numerical RPO, RTO, and support objectives remain owned by DEC-012.

## Portability and scale principle

Apply:

**FUTURE-COMPATIBLE, NOT FUTURE-OVERENGINEERED.**

DEC-005R materially expands portability through a canonical logical schema, qualified MariaDB/MySQL/PostgreSQL profiles, formal Database Portability Contract, cross-engine qualification direction, and DBME architecture direction.

The architecture may evolve, through later separate authority, toward:

- managed relational database operation;
- read replicas;
- high availability;
- reporting/read-model separation;
- larger tenant workloads;
- stronger tenant physical isolation;
- container/Kubernetes runtime;
- partitioning/sharding only after evidence.

None of those mechanisms is required or authorized merely by acceptance of this ADR.

## Alternatives and engine-profile history

### MariaDB

Under historical DEC-005, MariaDB was a viable relational alternative but not the canonical interchangeable engine. Under DEC-005R it is now an authorized engine-profile direction, with MariaDB 11.4 family as Stage-1 direction subject to runtime qualification.

### MySQL

Under DEC-005, MySQL Server was the sole canonical relational engine family. Under DEC-005R it remains an authorized engine-profile direction but no longer has sole-canonical status.

### PostgreSQL

Under historical DEC-005, PostgreSQL was a strong relational alternative but not selected as canonical. Under DEC-005R it is now an authorized engine-profile direction; no completed PostgreSQL runtime qualification is claimed.

### Schema-per-tenant

Not selected as the default because it increases provisioning/migration fan-out and is not the intended portable default.

### Database-per-tenant

Not selected as the default because it materially increases provisioning, connection, migration, backup, restore, and operational complexity for the bounded initial stage.

### Hybrid / tiered tenancy

Approved only as a bounded future evolution path. It is not an instruction to implement multiple tenancy tiers now.

## Consequences

### Positive

- Business rules and Domain/Application remain independent from relational engine vendor identity.
- The architecture can qualify MariaDB, MySQL, and PostgreSQL profiles without changing business code.
- Shared-schema tenancy keeps the initial physical model operationally bounded.
- Mandatory tenant identity and tenant-aware integrity preserve Secure Tenant Isolation as a first-class guardrail.
- Application authorization remains authoritative while database integrity provides independent defense-in-depth.
- A future dedicated-tenant path exists without forcing current overengineering.
- Recoverability is tied to restoration evidence rather than backup-job success.
- Future database mobility has an explicit fail-closed DBME architecture direction rather than ad-hoc conversion.

### Tradeoffs

- Each engine profile requires qualification and bounded Infrastructure implementation; portability does not mean zero Infrastructure differences.
- Shared schema creates a Critical dependency on correct tenant-context and tenant-aware data-access enforcement.
- Tenant-scoped recovery is more complex than whole-database restore and requires separately designed recovery procedures.
- Engine-specific optimizations must remain isolated to Infrastructure.
- DBME/cross-engine CI are architecture directions only until separately implemented and validated.

## Preserved decision boundaries

- **DEC-001** owns MVP scope/non-scope.
- **DEC-002** owns backend/application architecture.
- **DEC-003** owns Web/PWA delivery.
- **DEC-004** owns Android delivery direction.
- **DEC-006** owns authentication, MFA, session/token lifecycle, and identity recovery.
- **DEC-007** owns payment-provider/compliance boundaries.
- **DEC-008** exclusively owns offline POS transaction semantics, synchronization, replay, conflict resolution, reconciliation, and disconnected transaction authority.
- **DEC-009** owns Stage 1 deployment/runtime requirements and is reconciled only for DEC-005R's database-engine dependency.
- **DEC-010** owns product license and third-party notice policy.
- **DEC-011** owns retention, privacy, and jurisdiction.
- **DEC-012** owns final RPO/RTO/support objectives.

## Explicit deferments

This ADR does not select or authorize:

- completed runtime qualification for any engine profile;
- database server configuration;
- charset/collation physical mapping;
- connection-pool or timeout values;
- memory/buffer sizing;
- physical schema;
- table names;
- columns;
- primary keys;
- foreign keys;
- actual indexes;
- SQL / DDL;
- Laravel migrations;
- seeders;
- exact identifier implementation;
- credentials or connection strings;
- hosting/managed-database provider;
- replication or HA topology;
- Kubernetes database topology;
- shard/partition definitions;
- tenant provisioning;
- dedicated-tenant database implementation;
- isolation-tier routing;
- tenant migration/restore implementation;
- DBME implementation;
- cross-engine CI implementation;
- exact backup product;
- encryption/key-management implementation;
- authentication implementation;
- offline transaction/sync implementation;
- payment implementation;
- retention periods;
- final RPO/RTO;
- M7.5 execution;
- deployment;
- release;
- production migration.

## Validation direction

Later separately authorized implementation should produce evidence for each engine profile where applicable:

- relational engine/profile and supported version compatibility;
- transaction correctness for POS and inventory use cases;
- tenant-isolation negative tests;
- tenant-aware integrity;
- fail-closed tenant-context behavior;
- privileged cross-tenant auditability;
- migration compatibility and recovery rehearsal;
- backup integrity and successful restore rehearsal;
- representative query-plan/performance review;
- Database Portability Contract conformance;
- absence of database/vendor dependencies in Domain/Application.

## Authority boundary

Acceptance/reconciliation of this ADR represents substantive DEC-005R architecture only.

It does not grant database/schema/SQL/migration implementation, DBME implementation, cross-engine CI, package/dependency changes, M7.5, Sprint 14, deployment, release, production database modification, or production-readiness promotion.

Attribution: Lab | zefry
