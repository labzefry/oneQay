# AI Next Task

## Current checkpoint

- Sprint 08 Persistence Capability and Database Connection Boundary Foundation: Published at `5e620f7e1975450d7538e2d04c0b098c2ead962f`.
- Sprint 09 Database Schema Governance and Migration Safety Foundation: Implemented on branch.
- Migration bounded tests: Passed — 47 assertions.
- Production SQL and production migration: None.
- Business schema and tenant data model final: Not Started.
- POS: Not Started.
- Deployment: None.
- Release: None.

## Remaining Sprint 09 lifecycle

1. Verify exact head after final content commit.
2. Run PHP syntax validation on the final exact tree.
3. Run Authentication, Tenant Context, Authorization, Configuration, Runtime, Bootstrap, Persistence, and Migration regressions.
4. Run duplicate, checksum mismatch, dependency ordering, destructive rejection, and rollback classification negative tests.
5. Run secret, path, SQL, and credential leakage-negative tests.
6. Verify no business schema, no POS behavior, no production SQL, and no production database connection.
7. Wait for required checks on the Draft PR.
8. Obtain independent approval from `zefriansyah` on the latest exact head.
9. Do not mark Ready or merge without separate Product Owner authority.

## Production migration dependency

Production migration remains NO-GO until a least-privilege migration account, approved grants, lock strategy, transaction behavior, backup and restore evidence, RTO/RPO, migration window, connection limits, deployment method, and rollback authority are verified outside the repository. Do not provide credentials through chat or GitHub.

## Sprint 10 candidate

Sprint 10 is not authorized. A bounded candidate may be Generic Data Definition Contract and Tenant Isolation Schema Policy Foundation only after Sprint 09 is published, all exact-head regressions pass, independent approval is recorded, and Product Owner provides separate authorization.

Sprint 10 must not automatically create production tables, final tenant data model, POS schema, production SQL, deployment, or migration execution.

Attribution: Lab | zefry
