# AI Next Task

## Current checkpoint

- Sprint 10 Generic Data Definition Contract and Tenant Isolation Schema Policy Foundation: Published at `302c9957bcda55fe8265fc0a0449003d59f23620`.
- Sprint 11 Physical Schema Mapping Capability and Vendor Compatibility Policy Foundation: Implemented on branch.
- Legacy foundation regressions: Passed during Sprint 11 against verified exact-base blobs.
- Data Definition and Tenant Isolation Policy regression: Passed — 70 assertions.
- Physical Mapping and Vendor Compatibility tests: Passed — 88 assertions.
- Executable SQL and production table: None.
- Final tenant data model: Not Started.
- Final business schema: Not Started.
- Production migration: Not Performed.
- POS: Not Started.
- Deployment: None.
- Release: None.

## Historical residual risk

Authentication, Tenant Context, Authorization, Configuration, Runtime, Bootstrap, dan Persistence regressions tidak dieksekusi ulang sebelum Sprint 09 merge. Sprint 10 dan Sprint 11 execution tidak mengubah fakta historis tersebut menjadi pre-merge evidence.

## Remaining Sprint 11 lifecycle

1. Verify exact head after final content commit.
2. Run PHP syntax validation on the final exact tree.
3. Run Authentication, Tenant Context, Authorization, Configuration, Runtime, Bootstrap, Persistence, Migration, Data Definition, dan Physical Mapping regressions.
4. Run invalid physical identifier, reserved namespace, unsupported scalar, invalid charset/collation, invalid length/precision, index-budget overflow, incompatible foreign-key, dan missing tenant-key negative tests.
5. Run secret, path, SQL, credential, dan data leakage-negative tests.
6. Verify no executable SQL, no production table, no business schema, no POS behavior, and no production database connection.
7. Wait for required checks on the Draft PR.
8. Obtain independent approval from `zefriansyah` on the latest exact head.
9. Do not mark Ready or merge without separate Product Owner authority.

## Production schema dependency

Production table and production migration remain NO-GO until final tenant data model, final business schema, live MariaDB compatibility, SQL mode, storage-engine policy, actual index and collation constraints, physical foreign-key policy, least-privilege migration grants, lock strategy, transaction behavior, backup and restore evidence, RTO/RPO, migration window, connection limits, deployment method, and rollback authority are verified outside the repository.

## Sprint 12 candidate

Sprint 12 is not authorized. A bounded candidate may be Physical Schema Plan Representation and Change Classification Foundation only after Sprint 11 is published, all exact-head regressions pass, independent approval is recorded, and Product Owner provides separate authorization.

Sprint 12 must not automatically create production tables, render executable SQL, generate production migration artifacts, establish final business schema, start POS, deploy, or execute production migration.

Attribution: Lab | zefry
