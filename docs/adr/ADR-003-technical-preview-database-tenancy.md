# ADR-003 — Database Engine and Physical Tenancy

- Status: Accepted — representation of substantive DEC-005 after publication
- Date: 2026-08-09
- Decision owner: Product Owner `labzefry`
- Substantive authority: DEC-005 — Database Engine and Physical Tenancy Model
- Decision baseline: `63646e1cccc611a1911c452397059983030dfe66`
- Decision baseline tree: `80cd3bbf1a0c1d454e73c89f17d8896941f369cd`
- Historical evidence: Issue #23 / Technical Preview v0.0.1 D1 candidate
- Scope: bounded relational database-engine and physical-tenancy architecture direction only

## Context

oneQay is an **Enterprise Intelligent Business Management Platform** whose approved first bounded MVP delivery slice is **POS CORE TRANSACTION & OUTLET OPERATIONS**.

DEC-002 already establishes PHP + Laravel, Modular Monolith First + Clean Architecture, and framework-independent Domain/Application. DEC-005 therefore selects database technology and physical-tenancy direction without permitting database/vendor concerns to leak into Domain/Application.

This ADR originally existed as a **Proposed Technical Preview v0.0.1** decision under Issue #23. The historical D1 candidate used the phrase **“MySQL-compatible engine”**, selected a shared schema with mandatory tenant identity, and deferred actual engine/version pending hosting evidence.

The historical candidate is preserved below as provenance. The current binding architecture represented by this ADR comes from the later explicit Product Owner substantive **DEC-005** decision.

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

DEC-005 supersedes only the ambiguous current-facing technology/tenancy direction. It does not rewrite the fact that the D1 Technical Preview candidate was Proposed under Issue #23.

## Current decision

### Canonical relational database engine family

Use **MySQL Server** as the canonical relational database engine family for oneQay.

The phrase **“MySQL-compatible engine”** is historical provenance only and must not be used as the current canonical engine decision.

MariaDB and PostgreSQL are not approved as interchangeable canonical production engines by DEC-005. A future architecture decision may supersede MySQL Server only on separately authorized material evidence.

### Version boundary

Use a **supported MySQL LTS family**.

The following remain deferred:

- exact MySQL major/LTS series;
- exact minor version;
- exact patch version.

The future selected runtime version must be supported, maintained, compatible with the authorized PHP/Laravel runtime, and capable of meeting required security and backup/restore controls.

Innovation-track selection is not authorized by DEC-005.

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

- MySQL-specific client APIs;
- MySQL server objects;
- storage-engine concepts;
- database-administration topology;
- hosting-provider APIs.

Bounded engine-specific optimization may later exist inside authorized Infrastructure implementations where justified.

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

This ADR does not define or authorize actual migration files, SQL, DDL, tables, columns, keys, indexes, or seeders.

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

## Alternatives considered

### MariaDB

Viable relational alternative and historically adjacent to the “MySQL-compatible” language, but not selected as the canonical interchangeable engine. DEC-005 intentionally chooses an explicit engine family.

### PostgreSQL

Strong relational alternative, including database-native isolation capabilities. It is not selected as the DEC-005 canonical engine. Any database-native row/security mechanism would remain defense-in-depth unless a future substantive decision explicitly changes authorization ownership.

### Schema-per-tenant

Not selected as the default because it increases provisioning/migration fan-out and is not the intended portable default under the selected engine direction.

### Database-per-tenant

Not selected as the default because it materially increases provisioning, connection, migration, backup, restore, and operational complexity for the bounded initial stage.

### Hybrid / tiered tenancy

Approved only as a bounded future evolution path. It is not an instruction to implement multiple tenancy tiers now.

## Consequences

### Positive

- An explicit MySQL Server family replaces ambiguous “compatible” terminology.
- Shared-schema tenancy keeps the initial physical model operationally bounded.
- Mandatory tenant identity and tenant-aware integrity preserve Secure Tenant Isolation as a first-class guardrail.
- Application authorization remains authoritative while database integrity provides independent defense-in-depth.
- Domain/Application remain free of database-vendor dependencies.
- A future dedicated-tenant path exists without forcing current overengineering.
- Recoverability is tied to restoration evidence rather than backup-job success.

### Tradeoffs

- Shared schema creates a Critical dependency on correct tenant-context and tenant-aware data-access enforcement.
- Tenant-scoped recovery is more complex than whole-database restore and requires separately designed recovery procedures.
- MySQL-specific optimizations must be isolated to Infrastructure to avoid architecture leakage.
- Dedicated tenant isolation is not available merely by accepting this ADR; it requires separate design and implementation authority.

## Preserved decision boundaries

- **DEC-001** owns MVP scope/non-scope.
- **DEC-002** owns backend/application architecture.
- **DEC-003** owns Web/PWA delivery.
- **DEC-004** owns Android delivery direction.
- **DEC-006** owns authentication, MFA, session/token lifecycle, and identity recovery.
- **DEC-007** owns payment-provider/compliance boundaries.
- **DEC-008** exclusively owns offline POS transaction semantics, synchronization, replay, conflict resolution, reconciliation, and disconnected transaction authority.
- **DEC-009** owns Stage 1 deployment/runtime requirements.
- **DEC-010** owns product license and third-party notice policy.
- **DEC-011** owns retention, privacy, and jurisdiction.
- **DEC-012** owns final RPO/RTO/support objectives.

## Explicit deferments

This ADR does not select or authorize:

- exact MySQL LTS series/minor/patch;
- database server configuration;
- charset/collation;
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
- exact backup product;
- encryption/key-management implementation;
- authentication implementation;
- offline transaction/sync implementation;
- payment implementation;
- retention periods;
- final RPO/RTO;
- deployment;
- release;
- production migration.

## Validation direction

Later authorized implementation should produce evidence for:

- supported MySQL LTS runtime compatibility;
- transaction correctness for POS and inventory use cases;
- tenant-isolation negative tests;
- tenant-aware integrity;
- fail-closed tenant-context behavior;
- privileged cross-tenant auditability;
- migration compatibility and recovery rehearsal;
- backup integrity and successful restore rehearsal;
- representative query-plan/performance review;
- absence of database/vendor dependencies in Domain/Application.

## Authority boundary

Acceptance of this ADR represents substantive DEC-005 architecture only.

It does not grant database/schema/SQL/migration implementation, package/dependency changes, Sprint 14, deployment, release, production database modification, or production-readiness promotion.

Attribution: Lab | zefry
