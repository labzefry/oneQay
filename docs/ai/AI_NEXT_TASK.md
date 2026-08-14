# AI Next Task

## Stable checkpoint

- Project: oneQay
- Repository: `labzefry/oneQay`
- Developer and Product Engineering Entity: Lab | zefry
- Canonical product attribution: Lab | zefry
- Canonical Phase 0 status: **IN PROGRESS**
- M7.0: **DONE / PUBLISHED**
- M7.1: **DONE / PUBLISHED** through PR #92
- M7.2: **DONE / PUBLISHED** through PR #93
- M7.3: **DONE / PUBLISHED** through PR #94
- M7.4: **DONE / PUBLISHED** through PR #96
- M7.4A: **DONE / PUBLISHED** through PR #98
- M7.5: **BLOCKED / NOT AUTHORIZED**
- M7.6: **BLOCKED / NOT AUTHORIZED**
- M7.7: **BLOCKED / NOT AUTHORIZED**
- Sprint 14: **NOT AUTHORIZED**
- Deployment: **NOT AUTHORIZED**
- Release: **NOT AUTHORIZED**
- Production: **NOT AUTHORIZED**
- Production readiness: **NO-GO**

## Current database architecture decision

DEC-005R — **Portable Relational Persistence Architecture** is **APPROVED / DECISION COMPLETE**.

Current canonical direction:

- Domain/Application database-engine-neutral;
- zero database-vendor dependency in business rules;
- **ZERO BUSINESS-CODE CHANGE** target across officially qualified relational engine profiles;
- engine-specific behavior confined to Infrastructure/Configuration;
- canonical logical schema/contract;
- MariaDB, MySQL, and PostgreSQL engine-profile directions;
- MariaDB 11.4 family as Stage-1 profile direction, subject to runtime qualification;
- formal Database Portability Contract direction;
- cross-engine qualification/CI direction;
- oneQay Database Mobility & Migration Engine — DBME direction;
- fail-closed unsafe/lossy conversion;
- controlled preflight, reconciliation, cutover, source retention, and safe rollback direction.

DEC-005 remains **APPROVED HISTORICAL DECISION / PARTIALLY SUPERSEDED BY DEC-005R**. Shared database/shared schema, bounded hybrid isolation, Application-authoritative tenant isolation, and recoverability principles remain preserved.

DEC-009 remains the Stage-1 runtime owner. Its database requirement is now an **authorized and runtime-qualified relational engine profile under DEC-005R**, not sole canonical MySQL Server.

Observed MariaDB 11.4 family evidence is **engine-family/version evidence only**, not runtime qualification. P1 Shared Hosting/cPanel remains **CONDITIONAL / NOT SELECTED** and P2 Managed/Hardened VPS or Server remains the fallback execution class.

## Live GitHub state rule

GitHub is the Single Source of Truth. Hard-coded SHAs in tracked checkpoints are provenance, not permanently current live state. Fresh Minimal Delta Verification is required before every new branch, lifecycle mutation, implementation decision, or milestone transition.

## Exact next governed engineering gate

The canonical next roadmap milestone remains:

**M7.5 — Preview Runtime Qualification**

Current authority:

**BLOCKED / NOT AUTHORIZED**.

M7.5 may begin only after:

1. actual sanitized target evidence is supplied;
2. DEC-009 mandatory capability verification is performed, including the selected DEC-005R relational engine-profile qualification;
3. fresh GitHub Minimal Delta Verification succeeds; and
4. separate explicit Product Owner M7.5 authority is granted.

DEC-005R publication does not grant database adapter implementation, cross-engine CI implementation, DBME implementation, schema/SQL/migration, live database connectivity, data movement, deployment, release, Sprint 14, Phase 0 Exit, or Production authority.

## Current implementation facts

- `apps/web` POS/business persistence remains synthetic/in-memory.
- No durable relational POS/business persistence is authorized by DEC-005R publication.
- Bounded database coupling remains inside current Infrastructure foundation.
- Existing logical DataDefinition vocabulary already points toward portable logical types.
- Existing Migration foundation remains governance/planning/dry-run oriented and is not live DBME.

Future portability source work requires separate Product Owner implementation authority.

Attribution: Lab | zefry
