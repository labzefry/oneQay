# AI Project State

## Project identity

- Project: oneQay
- Tagline: The Future of Intelligent Business Management
- Developer and Product Engineering Entity: Lab | zefry
- Repository: `labzefry/oneQay`
- GitHub repository role: Single Source of Truth
- Product attribution: Lab | zefry
- Canonical product name: `oneQay`

## Canonical checkpoint semantics

This file records stable program state, not a permanently current GitHub SHA. Fresh Minimal Delta Verification against GitHub is mandatory before lifecycle or implementation mutation.

## Current architecture decision state

- DEC-005: **APPROVED HISTORICAL DECISION / PARTIALLY SUPERSEDED BY DEC-005R**.
- DEC-005R: **APPROVED / DECISION COMPLETE — Portable Relational Persistence Architecture**.
- DEC-009: **APPROVED / DECISION COMPLETE — database dependency reconciled to DEC-005R qualified relational engine profiles**.
- ADR-003: **ACCEPTED — current representation reconciled to DEC-005R; historical D1 and DEC-005 provenance preserved**.
- ADR-007: **ACCEPTED — Stage-1 database dependency reconciled to DEC-005R**.

DEC-005R current direction is database-engine-neutral Domain/Application, zero vendor dependency in business rules, **ZERO BUSINESS-CODE CHANGE** target between officially qualified relational engine profiles, canonical logical schema/contract, MariaDB/MySQL/PostgreSQL profile direction, MariaDB 11.4 Stage-1 direction subject to runtime qualification, Database Portability Contract, cross-engine qualification direction, and oneQay DBME architecture direction.

DEC-005 shared database/shared schema default, bounded stronger-isolation evolution, Application-authoritative tenant isolation, and recoverability principles remain preserved.

## Current implementation state

Current POS/business runtime persistence remains synthetic/in-memory. No durable relational business persistence, physical business schema, executable SQL/migration, engine adapter, cross-engine CI, or DBME implementation is authorized by DEC-005R publication.

Bounded database/vendor coupling remains an Infrastructure concern. Future portability implementation requires separate Product Owner authority.

## Stage-1 runtime state

- P1 Shared Hosting/cPanel: **CONDITIONAL / NOT SELECTED**.
- P2 Managed/Hardened VPS or Server: **FALLBACK EXECUTION CLASS**.
- MariaDB 11.4 family: **ENGINE-FAMILY / VERSION EVIDENCE; NOT YET RUNTIME QUALIFIED**.
- Stage-1 selected engine must be an authorized **and runtime-qualified** relational profile under DEC-005R.

## Canonical M7 lifecycle

- M7.0 — DONE / PUBLISHED.
- M7.1 — DONE / PUBLISHED through PR #92.
- M7.2 — DONE / PUBLISHED through PR #93.
- M7.3 — DONE / PUBLISHED through PR #94.
- M7.4 — DONE / PUBLISHED through PR #96.
- M7.4A — DONE / PUBLISHED through PR #98.
- M7.5 — BLOCKED / NOT AUTHORIZED.
- M7.6 — BLOCKED / NOT AUTHORIZED.
- M7.7 — BLOCKED / NOT AUTHORIZED.

## Program authority state

- Phase 0: **IN PROGRESS**.
- Phase 0 Exit: **NOT APPROVED**.
- Sprint 14: **NOT AUTHORIZED**.
- Deployment: **NOT AUTHORIZED**.
- Release: **NOT AUTHORIZED**.
- Production: **NOT AUTHORIZED**.
- Production readiness: **NO-GO**.

DEC-005R does not promote any lifecycle state.

## Next governed gate

Next roadmap gate remains **M7.5 — Preview Runtime Qualification**, but it is **BLOCKED / NOT AUTHORIZED** until actual sanitized target evidence, DEC-009 capability verification including selected relational engine-profile qualification, fresh GitHub verification, and separate Product Owner M7.5 authority exist.

Attribution: Lab | zefry
