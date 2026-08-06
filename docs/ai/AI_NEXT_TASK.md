# AI Next Task

## Current checkpoint

- Canonical delivery phase: Phase 0 — Governance and Discovery
- Canonical Phase 0 status: In Progress
- Active bounded workstream: Platform Foundation Capability
- Latest published technical capability sprint: Sprint 11
- Sprint 12 Entry Gate: Published through PR #52
- Approved source head: `f9c74ce798ef1095e03164ad1424cefbdabc9474`
- Approved and published tree: `4f6d49c4dcf894f78f40764940da21b821ffb315`
- Published commit: `7d0ab5db991d75ba6a83bebc6a681988f3d8d26b`
- Published parent: `dcb60b6879f4427032d2df528f2a2dde17e5a537`
- Independent review: APPROVED by `zefriansyah` on the exact source head
- Governance Required Checks run #43: Success
- Unresolved review threads: None
- Push after approval: None identified
- Sprint 12 source implementation: Not Authorized
- Production readiness: NO-GO

## Current task

Complete the documentation-only publication closure for PR #52 and then obtain one explicit Product Owner decision on Sprint 12 source implementation.

Required decision statement:

> Product Owner mengotorisasi Sprint 12 source implementation pada scope yang telah dipublikasikan melalui PR #52.

Without that exact intent, do not create an implementation branch, PHP source file, test file, composer update, SQL, migration artifact, database connection, deployment artifact, or Sprint 13 work.

## Published Sprint 12 scope

**Physical Schema Plan Representation and Change Classification Foundation**

Expected implementation boundary after authorization:

1. `src/SchemaPlanning/Foundation.php`;
2. `src/SchemaPlanning/ValueObjects.php`;
3. `src/SchemaPlanning/Contracts.php`;
4. `src/SchemaPlanning/Planning.php`;
5. `tests/schema-planning.php`;
6. `composer.json` only for foundation loading and test execution;
7. one Sprint 12 capability document;
8. three AI checkpoint documents.

Any additional file requires an explicit explanation in the implementation PR.

## Required behavior after authorization

- deterministic baseline and target fingerprints;
- immutable physical schema plan representation;
- stable change identifiers and ordering;
- `NO_CHANGES` for identical manifests;
- `REVIEW_REQUIRED` for additive changes;
- `BLOCKED` for destructive changes;
- `BLOCKED` for tenant-boundary changes;
- `BLOCKED` for primary-index changes;
- `BLOCKED` for vendor changes;
- safe JSON review artifact;
- required correlation ID;
- synthetic test data only;
- no network dependency;
- no database connection;
- no executable SQL;
- no migration artifact.

## Acceptance criteria after authorization

1. Identical validated manifests always produce the same fingerprints, ordering, disposition, and JSON output.
2. Input manifests are validated through the published vendor compatibility boundary before comparison.
3. Invalid or incompatible manifests cannot produce a ready plan.
4. Destructive, tenant-boundary, primary-index, and vendor changes are always `BLOCKED`.
5. Additive changes are never represented as automatically executable or automatically safe.
6. Empty correlation IDs are rejected.
7. Plan output contains safe identifiers and fingerprints only.
8. No SQL string, database credential, production path, production identifier, or tenant data is emitted.
9. Tests use no network or database dependency.
10. PHP syntax validation passes for every changed PHP file.
11. All previously published foundation regressions pass on the final exact head.
12. Required GitHub checks pass on the final exact head.
13. Independent review is APPROVED on the final exact head.
14. No unresolved review thread remains.
15. Ready transition and merge require separate Product Owner authority.

## Governance state to preserve

- ADR-001 through ADR-007: Proposed.
- GD-007: Proposed.
- JRN-003 and JRN-013: Unresolved.
- Final tenant data model: Not Started.
- Final business schema: Not Started.
- Production migration: Not Performed.
- Production database usage: None.
- Production table: None.
- POS and business modules: Not Started.
- Deployment: None.
- Release: None.
- Sprint 13: Not Authorized.

## Publication-closure lifecycle

For the current documentation-only closure:

1. use exact base `7d0ab5db991d75ba6a83bebc6a681988f3d8d26b`;
2. change exactly the three AI checkpoint documents;
3. create one atomic documentation-only commit;
4. verify exact head, exact tree, and changed files;
5. open one Draft PR targeting `main`;
6. wait for `governance-validation`, `markdown-lint`, and `secret-scan`;
7. request independent review from `zefriansyah` on the final exact head;
8. do not mark Ready or merge without separate Product Owner authority;
9. do not create Sprint 12 source implementation during this lifecycle.

Attribution: Lab | zefry
