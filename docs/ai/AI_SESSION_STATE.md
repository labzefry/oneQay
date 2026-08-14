# AI Session State

## Identity

- Project: oneQay
- Developer and Product Engineering Entity: Lab | zefry
- Repository: `labzefry/oneQay`
- Repository role: Single Source of Truth
- Checkpoint date: 2026-08-14
- Canonical product attribution: Lab | zefry
- Canonical product name: `oneQay`

## Checkpoint semantics

This file is a stable session checkpoint, not a substitute for live GitHub verification. Hard-coded SHAs are provenance only. Perform Minimal Delta Verification before every branch, lifecycle transition, implementation decision, Ready, or Merge.

## DEC-005R session result

Product Owner substantively approved:

**DEC-005R — OPTION C — PORTABLE RELATIONAL PERSISTENCE ARCHITECTURE**.

Canonical architecture after this decision:

- Domain and Application are database-engine-neutral;
- business rules have zero database-vendor dependency;
- target between officially qualified relational engine profiles is **ZERO BUSINESS-CODE CHANGE**;
- engine-specific behavior remains Infrastructure/Configuration concern;
- canonical logical schema/contract is vendor-neutral;
- MariaDB, MySQL, and PostgreSQL are authorized engine-profile directions;
- MariaDB 11.4 family is Stage-1 profile direction subject to runtime qualification;
- formal Database Portability Contract is approved direction;
- cross-engine qualification/CI is approved direction, **not implemented**;
- oneQay Database Mobility & Migration Engine — DBME is approved architecture direction, **not implemented**;
- unsafe/lossy/ambiguous conversion fails closed;
- future mobility uses controlled preflight, reconciliation, cutover, source retention, and rollback only when safe.

DEC-005 is preserved as an **APPROVED HISTORICAL DECISION / PARTIALLY SUPERSEDED BY DEC-005R**. DEC-009 remains owner of Stage-1 runtime requirements and now requires an authorized and runtime-qualified relational engine profile under DEC-005R.

## Current Stage-1 evidence state

Observed MariaDB 11.4 family is **engine-family/version evidence only**. It does not prove oneQay runtime qualification. P1 Shared Hosting/cPanel remains **CONDITIONAL / NOT SELECTED**; P2 Managed/Hardened VPS or Server remains fallback execution class.

## Implementation boundary

DEC-005R publication does not authorize source refactoring, durable relational business persistence, physical schema, SQL/DDL, migrations, seeders, live database connectivity, credentials, engine adapters, cross-engine CI implementation, DBME implementation, data movement, infrastructure provisioning, deployment, release, or Production.

## Lifecycle preserved

- M7.0 — DONE / PUBLISHED
- M7.1 — DONE / PUBLISHED
- M7.2 — DONE / PUBLISHED
- M7.3 — DONE / PUBLISHED
- M7.4 — DONE / PUBLISHED
- M7.4A — DONE / PUBLISHED
- M7.5 — BLOCKED / NOT AUTHORIZED
- M7.6 — BLOCKED / NOT AUTHORIZED
- M7.7 — BLOCKED / NOT AUTHORIZED
- Phase 0 — IN PROGRESS
- Phase 0 Exit — NOT APPROVED
- Sprint 14 — NOT AUTHORIZED
- Deployment — NOT AUTHORIZED
- Release — NOT AUTHORIZED
- Production — NOT AUTHORIZED
- Production readiness — NO-GO

## Exact next gate

The next roadmap gate remains **M7.5 — Preview Runtime Qualification**, but it cannot begin without actual sanitized target evidence, DEC-009 capability verification including relational engine-profile qualification, fresh GitHub verification, and separate explicit Product Owner M7.5 authority.

Attribution: Lab | zefry
