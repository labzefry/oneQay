# AI Next Task

## Current checkpoint

- Canonical delivery phase: Phase 0 — Governance and Discovery
- Canonical Phase 0 status: In Progress
- Active bounded workstream: Platform Foundation Capability
- Sprint 12 Entry Gate: Published through PR #52
- Sprint 12 publication closure: Published through PR #53
- Implementation base: `15999b34fa223fe8e7fcc33cab7427de316f76c2`
- Sprint 12 source implementation: Authorized and implemented as a candidate
- Branch: `agent/sprint12-schema-plan-change-classification`
- Production readiness: NO-GO

## Current task

Complete the Draft PR review lifecycle for:

**Sprint 12 — Physical Schema Plan Representation and Change Classification Foundation**

## Expected changed files

1. `src/SchemaPlanning/Foundation.php`;
2. `src/SchemaPlanning/ValueObjects.php`;
3. `src/SchemaPlanning/Contracts.php`;
4. `src/SchemaPlanning/Planning.php`;
5. `tests/schema-planning.php`;
6. `composer.json`;
7. `docs/PHYSICAL_SCHEMA_PLAN_REPRESENTATION_AND_CHANGE_CLASSIFICATION_FOUNDATION.md`;
8. `docs/ai/AI_SESSION_STATE.md`;
9. `docs/ai/AI_PROJECT_STATE.md`;
10. `docs/ai/AI_NEXT_TASK.md`.

Any additional file is blocking unless separately authorized and explained.

## Candidate evidence

- PHP syntax validation: Passed for all changed PHP files.
- Sprint 12 synthetic tests: Passed, 55 assertions.
- Full historical `composer test`: Pending; no pass claim is permitted without execution evidence.

## Required Draft PR lifecycle

1. Verify the candidate commit parent is exactly `15999b34fa223fe8e7fcc33cab7427de316f76c2`.
2. Verify exactly ten changed files and no out-of-scope path.
3. Open one Draft PR targeting `main`.
4. Wait for `governance-validation`, `markdown-lint`, and `secret-scan`.
5. Request independent review from `zefriansyah` on the final exact head.
6. Verify no unresolved review thread.
7. Obtain complete historical regression evidence before recommending Ready.
8. Stop and report exact base, head, tree, changed files, tests, checks, review state, and open risks.

## Reviewer focus

The reviewer must verify:

- canonicalization is deterministic and respects semantic index ordering;
- fingerprints and stable change IDs do not depend on runtime state;
- additive changes remain `REVIEW_REQUIRED`;
- destructive, physical/scalar, primary-index, tenant-boundary, and vendor changes are `BLOCKED`;
- invalid vendor compatibility reports cannot produce a plan;
- output contains safe identifiers and fingerprints only;
- no executable SQL, migration, network, database, production path, tenant data, POS, or business behavior exists;
- all governance states remain preserved;
- missing full historical regression evidence remains an explicit pre-Ready risk.

## Prohibited

Do not mark Ready, merge, generate SQL, create a migration artifact, connect to a database, create a production table, establish a final schema, deploy, release, start POS or a business module, or begin Sprint 13 without separate Product Owner authority.

Attribution: Lab | zefry
