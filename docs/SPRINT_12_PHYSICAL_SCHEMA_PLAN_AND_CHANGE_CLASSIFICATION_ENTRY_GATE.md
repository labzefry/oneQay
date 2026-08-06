# Sprint 12 Physical Schema Plan and Change Classification Entry Gate

## Identity

- Project: OneQay
- Developer and Product Engineering Entity: Lab | zefry
- Repository: `labzefry/oneQay`
- Canonical repository role: Single Source of Truth
- Decision date: 2026-08-06
- Entry-gate base: `dcb60b6879f4427032d2df528f2a2dde17e5a537`
- Entry-gate base tree: `501d2f56c8899259679bc79c4923bc5dfdd4bc48`
- Planning branch: `agent/sprint12-entry-gate-schema-plan-change-classification`

## Product Owner direction

The Product Owner directed the project to continue to the next stage after PR #51 was squash-merged. This direction authorizes preparation and review of the Sprint 12 entry gate.

It does not silently accept an ADR, approve Phase 0 exit, establish a final tenant or business schema, authorize executable SQL, authorize a production migration, or authorize deployment.

Source implementation for Sprint 12 requires a separate exact-head implementation decision after this entry-gate document is reviewed.

## Current publication checkpoint

- Sprint 11 technical capability was published through PR #50.
- Sprint 11 source head: `58e12195e0ca2a5138c33e7bedf29719dcf5c54e`.
- Sprint 11 published commit: `2ffe14e8fef09e0c31105d98cb6ad47ae543ec17`.
- Sprint 11 published tree: `b029a9c81bda60b742c79cc4173218c2d7b5933a`.
- Sprint 11 state reconciliation was published through PR #51.
- PR #51 source head: `9c40a34bade7bbe6cf64ea9a0308faf3e7c84cf5`.
- PR #51 published commit: `dcb60b6879f4427032d2df528f2a2dde17e5a537`.
- PR #51 published tree: `501d2f56c8899259679bc79c4923bc5dfdd4bc48`.

## Delivery-state distinction

The canonical delivery phase remains Phase 0 — Governance and Discovery as recorded in `PROJECT_MANIFEST.md` and `TASKS.md`.

The repository also contains explicitly authorized, framework-agnostic foundation capabilities. These artifacts are treated as a bounded Platform Foundation Capability workstream. They do not by themselves approve Phase 0 exit, accept Proposed ADRs, start the final application, or make the system production-ready.

Sprint 12 planning must preserve this distinction.

## Proposed Sprint 12

**Name:** Physical Schema Plan Representation and Change Classification Foundation

**Outcome:** provide a deterministic and immutable representation of differences between two validated `PhysicalMappingManifest` instances, classify the operational risk of every detected change, and produce a safe review artifact without generating or executing SQL.

## In scope

1. Immutable physical schema plan representation.
2. Deterministic baseline and target fingerprints.
3. Stable change identifiers and stable ordering.
4. Change classification for:
   - entity creation;
   - entity removal;
   - physical entity identifier change;
   - attribute addition;
   - attribute removal;
   - attribute physical or scalar mapping change;
   - primary-index change;
   - unique-index addition, removal, or change;
   - reference addition, removal, or change;
   - tenant-scope or tenant-key policy change;
   - vendor change.
5. Conservative risk classification.
6. Plan disposition of `NO_CHANGES`, `REVIEW_REQUIRED`, or `BLOCKED`.
7. Correlation ID and safe JSON-serializable review report.
8. Deterministic tests using synthetic manifests only.
9. Regression execution for all previously published foundation suites.
10. Documentation and AI checkpoint updates.

## Required risk policy

| Change category | Required classification | Reason |
| --- | --- | --- |
| No change | `NO_CHANGES` | Identical canonical physical intent |
| Entity creation | `REVIEW_REQUIRED` | Requires capacity, ownership, and migration review |
| Attribute addition | `REVIEW_REQUIRED` | Nullability, default, and backfill are not represented here |
| Unique index addition | `REVIEW_REQUIRED` | Existing data compatibility is unknown |
| Reference addition | `REVIEW_REQUIRED` | Existing data and lock behavior are unknown |
| Entity removal | `BLOCKED` | Destructive operation |
| Attribute removal | `BLOCKED` | Destructive operation |
| Physical or scalar mapping change | `BLOCKED` | Conversion and data-loss risk are unknown |
| Primary-index change | `BLOCKED` | Identity, reference, and lock impact |
| Unique index removal or mutation | `BLOCKED` | Integrity may be weakened |
| Reference removal or mutation | `BLOCKED` | Referential integrity may be weakened |
| Tenant-scope or tenant-key change | `BLOCKED` | Cross-tenant exposure risk |
| Vendor change | `BLOCKED` | Compatibility and migration semantics are unknown |

A `REVIEW_REQUIRED` classification is not migration approval. A `BLOCKED` classification must not be bypassed by the planner.

## Explicit exclusions

Sprint 12 must not:

- generate executable SQL;
- generate a production migration artifact;
- connect to a database;
- inspect production metadata or data;
- execute DDL or DML;
- infer rename operations;
- define data backfill behavior;
- define online-schema-change behavior;
- define rollback execution;
- select or accept a database ADR;
- establish the final tenant data model;
- establish the final business schema;
- create production tables;
- start POS or any business module;
- deploy or release;
- begin Sprint 13.

## Proposed implementation boundary

Expected new module:

- `src/SchemaPlanning/Foundation.php`;
- `src/SchemaPlanning/ValueObjects.php`;
- `src/SchemaPlanning/Contracts.php`;
- `src/SchemaPlanning/Planning.php`.

Expected test entry point:

- `tests/schema-planning.php`.

Expected supporting changes:

- `composer.json` only for foundation loading and test execution;
- one Sprint 12 capability document;
- three AI checkpoint documents.

Any additional file requires an explicit explanation in the implementation PR.

## Acceptance criteria

1. The same validated input manifests always produce the same plan, fingerprints, ordering, disposition, and JSON output.
2. Input manifests are validated through the published vendor compatibility boundary before comparison.
3. Invalid or incompatible manifests cannot produce a ready plan.
4. Destructive, tenant-boundary, primary-index, and vendor changes are classified `BLOCKED`.
5. Additive changes are never represented as automatically executable or automatically safe.
6. No SQL string, database credential, production path, production identifier, or tenant data is emitted.
7. Empty correlation IDs are rejected.
8. Plan output contains safe identifiers and fingerprints only.
9. No network or database dependency is used by the tests.
10. PHP syntax validation passes for every changed PHP file.
11. Authentication, Tenant Context, Authorization, Configuration, Runtime, Bootstrap, Persistence, Migration, Data Definition, and Physical Mapping regressions pass on the final exact head.
12. Required GitHub checks pass on the final exact head.
13. Independent review is APPROVED on the final exact head.
14. No unresolved review thread remains.

## Entry gate

Sprint 12 implementation may begin only after all of the following are true:

1. This entry-gate scope is published in a Draft PR.
2. The exact head and exact tree are recorded.
3. Required checks succeed.
4. Independent review by `zefriansyah` is recorded on the exact head.
5. The Product Owner explicitly approves this exact-head scope and separately authorizes Sprint 12 source implementation.

## Stop condition

For the current task:

- create one documentation-only commit;
- open one Draft PR;
- request independent review;
- report exact base, head, tree, changed files, checks, and review status;
- do not mark Ready;
- do not merge;
- do not create Sprint 12 source code.

Attribution: Lab | zefry
