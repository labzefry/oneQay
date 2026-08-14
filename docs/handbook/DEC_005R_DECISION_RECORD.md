# DEC-005R — Portable Relational Persistence Architecture Decision Record

> **Status:** Approved / Substantive Decision Complete
> **Decision result:** APPROVED — OPTION C
> **Phase:** 0 — Governance & Discovery
> **Canonical product:** oneQay
> **Developer & Product Engineering Entity:** Lab | zefry
> **Repository:** `labzefry/oneQay`
> **Product Owner:** `labzefry`
> **Decision date:** 2026-08-14

## Decision provenance

DEC-005R is a substantive Product Owner architecture decision that revises only the database-engine and portability portions of DEC-005 while preserving the historical fact that DEC-005 originally selected MySQL Server.

The substantive Product Owner authorization approved **OPTION C — PORTABLE RELATIONAL PERSISTENCE ARCHITECTURE**. Repository publication is governed separately and does not itself authorize implementation, database mutation, deployment, release, or Production activity.

## Decision

oneQay adopts a **Portable Relational Persistence Architecture**.

The architecture requires:

- database-engine-neutral Domain;
- database-engine-neutral Application;
- zero database-vendor dependency in business rules;
- **ZERO BUSINESS-CODE CHANGE** target when moving between officially qualified relational engine profiles;
- engine-specific behavior confined to Infrastructure boundaries;
- one canonical logical schema/contract independent from a specific physical engine representation;
- qualified engine profiles for MariaDB, MySQL, and PostgreSQL;
- MariaDB 11.4 family as the Stage-1 profile direction, subject to actual runtime qualification;
- a formal Database Portability Contract;
- cross-engine qualification and regression direction;
- oneQay Database Mobility & Migration Engine — **DBME** architecture direction;
- automatic physical-schema adaptation only when semantic equivalence is proven;
- fail-closed behavior for unsafe, lossy, or ambiguous conversion;
- canonical data-movement pipeline with dry-run/preflight, compatibility analysis, reconciliation, controlled cutover, safe rollback where technically supportable, source retention, privileged-operation security, and auditability.

This decision defines architecture and governance direction. It does **not** claim that the listed profiles, DBME, cross-engine CI, or physical migration mechanisms are already implemented or runtime-qualified.

## Database Portability Contract

The future formal Database Portability Contract must preserve the following principles across officially supported engine profiles:

1. Domain rules and Application use cases remain engine-neutral.
2. Business behavior must not branch on database vendor identity.
3. Logical data semantics are canonical; physical mappings are adapter concerns.
4. Tenant isolation semantics remain invariant across engines.
5. Transactional correctness, idempotency, money semantics, inventory correctness, and audit semantics remain invariant.
6. Engine-specific capabilities may be used inside Infrastructure only when a portable contract and fallback/equivalence strategy are explicit.
7. Unsupported semantic mappings fail closed rather than silently coercing data.
8. Qualification requires evidence, not vendor-name compatibility assumptions.
9. A database engine is not considered supported merely because a driver can connect to it.
10. The target is zero business-code change; Infrastructure/configuration changes may still be required when changing engine profiles.

## Engine-profile model

### MariaDB

MariaDB is an authorized relational engine profile direction. The observed MariaDB 11.4 family is the Stage-1 profile direction, but actual application connectivity, security posture, limits, backup/restore behavior, transaction semantics, migration behavior, and operational suitability remain subject to M7.5 runtime qualification and separate authority.

### MySQL

MySQL remains an authorized relational engine profile direction. DEC-005R supersedes its former status as the sole canonical relational engine family; it is not removed from oneQay architecture.

### PostgreSQL

PostgreSQL becomes an authorized relational engine profile direction. No current repository evidence is interpreted as completed PostgreSQL runtime qualification or implementation.

## Canonical logical schema

The canonical model is logical rather than vendor-physical. Logical data definitions should use portable domain/data concepts while physical representations, generated DDL, indexes, constraints, JSON/UUID/boolean/date-time mappings, identifier quoting, collation behavior, and other engine-specific mechanics belong to qualified Infrastructure engine profiles.

The existing logical DataDefinition foundation already demonstrates a portable vocabulary direction including `STRING`, `INTEGER`, `DECIMAL`, `BOOLEAN`, `UUID`, `DATE`, `DATETIME`, and `JSON`. DEC-005R does not modify that source foundation in this publication.

## DBME architecture direction

oneQay adopts the architecture direction for a first-party **oneQay Database Mobility & Migration Engine — DBME**.

A future separately authorized DBME implementation is expected to provide bounded capabilities for:

- source/target engine discovery;
- engine-profile compatibility checks;
- canonical logical-schema comparison;
- physical schema planning/adaptation;
- dry-run/preflight;
- unsupported/lossy-conversion detection;
- deterministic migration planning;
- controlled data extraction and loading;
- integrity/reconciliation verification;
- resumability where technically safe;
- source retention;
- controlled cutover;
- rollback only where genuinely safe and supportable;
- privileged-operation authorization;
- immutable/auditable migration evidence;
- secret minimization and least-privilege credentials.

DBME must fail closed when semantic equivalence, integrity, recoverability, or authorization cannot be established.

DEC-005R does **not** authorize DBME source code, executable schema conversion, database credentials, live database access, data movement, SQL, DDL, migrations, or Production operations.

## Cross-engine qualification direction

A future separately authorized qualification program should establish reproducible evidence for supported engine profiles, including where applicable:

- logical contract compatibility;
- tenant-isolation behavior;
- transaction semantics;
- exact-money behavior;
- inventory correctness;
- unique/referential-integrity semantics;
- UUID/identifier behavior;
- JSON behavior;
- date/time behavior;
- schema evolution and recovery;
- backup/restore compatibility;
- representative performance and query-plan behavior;
- failure/retry behavior;
- source/profile migration and reconciliation.

Cross-engine CI is an approved architecture direction but is **NOT IMPLEMENTED** by this decision publication.

## Relationship to DEC-005

DEC-005 remains an approved historical decision. DEC-005R partially supersedes only the following dispositions:

| DEC-005 disposition | DEC-005R relationship |
| --- | --- |
| D-005-01 — MySQL Server canonical engine | **SUPERSEDED** — replaced by qualified relational engine profiles |
| D-005-02 — supported MySQL LTS-family boundary | **SUPERSEDED** — exact support is profile-specific qualification |
| D-005-03 — shared database/shared schema default | **PRESERVED** |
| D-005-04 — bounded hybrid stronger isolation direction | **PRESERVED** |
| D-005-05 — Application-authoritative tenant isolation | **PRESERVED** |
| D-005-06 — database-specific behavior as Infrastructure | **PRESERVED AND EXPANDED** |
| D-005-07 — deterministic/recoverable schema evolution | **PRESERVED AND EXPANDED** by portability/DBME direction |
| D-005-08 — backup/restore/recoverability principle | **PRESERVED** |
| D-005-09 — portability and scale evolution | **PARTIALLY SUPERSEDED / MATERIALLY EXPANDED** |
| D-005-10 — historical ADR-003 reconciliation | **PRESERVED AS HISTORICAL FACT** |

Historical DEC-005 text and provenance remain valid evidence of the decision that existed before DEC-005R.

## Relationship to DEC-009

DEC-009 remains the owner of Stage-1 Preview runtime capability requirements.

D-009-05 is reconciled so Stage 1 requires an **authorized and qualified relational engine profile under DEC-005R** rather than sole canonical MySQL Server.

This does not automatically:

- qualify MariaDB;
- select P1 Shared Hosting/cPanel;
- start M7.5;
- provision infrastructure;
- deploy oneQay.

P1 Shared Hosting/cPanel remains **CONDITIONAL / NOT SELECTED**. P2 Managed/Hardened VPS or Server remains the fallback execution class.

## Relationship to ADR-003 and ADR-007

ADR-003 remains the canonical ADR for database engine and physical-tenancy architecture. It must preserve three provenance layers:

1. historical Technical Preview D1 — “MySQL-compatible”;
2. historical DEC-005 — canonical MySQL Server;
3. current DEC-005R — Portable Relational Persistence Architecture.

ADR-007 remains the Accepted representation of DEC-009 and is reconciled only where its Stage-1 database requirement depended on sole canonical MySQL Server.

## Historical foundation preservation

The following documents remain historical evidence and are not rewritten by DEC-005R publication merely to make earlier Sprint work appear portable:

- `docs/PERSISTENCE_CAPABILITY_AND_DATABASE_CONNECTION_BOUNDARY_FOUNDATION.md`;
- `docs/GENERIC_DATA_DEFINITION_CONTRACT_AND_TENANT_ISOLATION_SCHEMA_POLICY_FOUNDATION.md`;
- `docs/PHYSICAL_SCHEMA_MAPPING_CAPABILITY_AND_VENDOR_COMPATIBILITY_POLICY_FOUNDATION.md`.

Historical PDO MySQL, MariaDB 11, and Sprint-specific physical-mapping statements remain facts of their original work.

## Current implementation facts

DEC-005R does not rewrite current source reality:

- `apps/web` POS/business persistence remains synthetic/in-memory;
- durable relational POS/business persistence is not authorized by this publication;
- bounded Infrastructure coupling remains in `src/Persistence/Foundation.php`, including PDO MySQL/MariaDB-shaped capability and connection behavior;
- bounded physical-mapping coupling remains in `src/PhysicalMapping/ValueObjects.php`, including `VendorIdentifier::MARIADB_11`;
- current Migration foundation remains governance/planning/dry-run oriented and is not a live DBME implementation.

Therefore:

- Business/Domain database coupling: **effectively zero**;
- bounded Infrastructure database coupling: **present**.

Future source refactoring requires separate implementation authority.

## Explicit non-scope and authority boundary

DEC-005R and its publication do not authorize:

- application source modification;
- Domain/Application refactoring;
- Infrastructure source refactoring;
- database-driver implementation;
- MariaDB/MySQL/PostgreSQL adapter implementation;
- engine-profile runtime implementation;
- cross-engine CI implementation;
- DBME implementation;
- physical schema creation;
- SQL or DDL;
- executable migrations or seeders;
- database credentials or connection to live databases;
- data migration;
- cPanel/infrastructure changes;
- M7.5, M7.6, or M7.7 execution;
- Sprint 14;
- deployment;
- release;
- Production;
- Phase 0 Exit.

## Lifecycle preservation

DEC-005R publication does not change the established delivery lifecycle:

- M7.0 — DONE / PUBLISHED;
- M7.1 — DONE / PUBLISHED;
- M7.2 — DONE / PUBLISHED;
- M7.3 — DONE / PUBLISHED;
- M7.4 — DONE / PUBLISHED;
- M7.4A — DONE / PUBLISHED;
- M7.5 — BLOCKED / NOT AUTHORIZED;
- M7.6 — BLOCKED / NOT AUTHORIZED;
- M7.7 — BLOCKED / NOT AUTHORIZED;
- Phase 0 — IN PROGRESS;
- Phase 0 Exit — NOT APPROVED;
- Sprint 14 — NOT AUTHORIZED;
- Deployment — NOT AUTHORIZED;
- Release — NOT AUTHORIZED;
- Production — NOT AUTHORIZED;
- Production readiness — NO-GO.

Attribution: Lab | zefry
