# AI Next Task

## Current checkpoint

- Sprint 09 Database Schema Governance and Migration Safety Foundation: Published at `227290c10b26d7f310f669526f3722c82489050e`.
- Sprint 10 Generic Data Definition Contract and Tenant Isolation Schema Policy Foundation: Implemented on branch.
- Legacy foundation regressions: Passed during Sprint 10 against verified exact-base blobs.
- Data Definition and Tenant Isolation Policy tests: Passed — 70 assertions.
- Executable SQL and production table: None.
- Final tenant data model: Not Started.
- Production migration: Not Performed.
- POS: Not Started.
- Deployment: None.
- Release: None.

## Historical residual risk

Authentication, Tenant Context, Authorization, Configuration, Runtime, Bootstrap, dan Persistence regressions tidak dieksekusi ulang sebelum Sprint 09 merge. Sprint 10 execution tidak mengubah fakta historis tersebut menjadi pre-merge evidence.

## Remaining Sprint 10 lifecycle

1. Verify exact head after final content commit.
2. Run PHP syntax validation on the final exact tree.
3. Run Authentication, Tenant Context, Authorization, Configuration, Runtime, Bootstrap, Persistence, Migration, dan Data Definition regressions.
4. Run invalid identifier, invalid scalar type, invalid constraint, missing tenant key, cross-tenant reference, dan reserved namespace negative tests.
5. Run secret, path, SQL, credential, dan data leakage-negative tests.
6. Verify no production table, no business schema, no POS behavior, no executable SQL, and no production database connection.
7. Wait for required checks on the Draft PR.
8. Obtain independent approval from `zefriansyah` on the latest exact head.
9. Do not mark Ready or merge without separate Product Owner authority.

## Production schema dependency

Physical schema and production migration remain NO-GO until final tenant data model, physical naming policy, MariaDB type mapping, collation and index constraints, foreign-key policy, least-privilege migration grants, lock strategy, transaction behavior, backup and restore evidence, RTO/RPO, migration window, connection limits, deployment method, and rollback authority are verified outside the repository.

## Sprint 11 candidate

Sprint 11 is not authorized. A bounded candidate may be Physical Schema Mapping Capability and Vendor Compatibility Policy Foundation only after Sprint 10 is published, all exact-head regressions pass, independent approval is recorded, and Product Owner provides separate authorization.

Sprint 11 must not automatically create production tables, executable migration SQL, final business schema, POS schema, deployment, or production migration execution.

Attribution: Lab | zefry
