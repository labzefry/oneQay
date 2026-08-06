# AI Next Task

## Current checkpoint

- Canonical delivery phase: Phase 0 — Governance and Discovery
- Canonical Phase 0 status: In Progress
- Active bounded workstream: Platform Foundation Capability
- Latest published technical sprint: Sprint 11
- Sprint 11 technical publication PR: #50
- Sprint 11 published commit: `2ffe14e8fef09e0c31105d98cb6ad47ae543ec17`
- Sprint 11 published tree: `b029a9c81bda60b742c79cc4173218c2d7b5933a`
- State reconciliation PR: #51
- Reconciliation published commit: `dcb60b6879f4427032d2df528f2a2dde17e5a537`
- Reconciliation published tree: `501d2f56c8899259679bc79c4923bc5dfdd4bc48`
- Sprint 12 implementation: Not Authorized
- Sprint 12 entry-gate planning: Authorized

## Current task

Prepare and publish a Draft PR containing the documentation-only entry gate for:

**Sprint 12 — Physical Schema Plan Representation and Change Classification Foundation**

Branch:

`agent/sprint12-entry-gate-schema-plan-change-classification`

Exact base:

`dcb60b6879f4427032d2df528f2a2dde17e5a537`

## Required changed files

The entry-gate commit must change exactly these files:

1. `docs/SPRINT_12_PHYSICAL_SCHEMA_PLAN_AND_CHANGE_CLASSIFICATION_ENTRY_GATE.md`;
2. `docs/ai/AI_SESSION_STATE.md`;
3. `docs/ai/AI_PROJECT_STATE.md`;
4. `docs/ai/AI_NEXT_TASK.md`.

## Required lifecycle

1. Create one atomic documentation-only commit.
2. Verify the exact parent is `dcb60b6879f4427032d2df528f2a2dde17e5a537`.
3. Verify exactly four changed files.
4. Verify no source code, tests, composer configuration, workflow, ruleset, schema, database, deployment, or release file changed.
5. Open one Draft PR targeting `main`.
6. Wait for:
   - `governance-validation`;
   - `markdown-lint`;
   - `secret-scan`.
7. Request independent review from `zefriansyah` on the final exact head.
8. Stop and report exact base, head, tree, changed files, checks, review status, and unresolved threads.

## Review focus

The reviewer must verify that:

- canonical Phase 0 status remains In Progress;
- published foundation evidence is preserved;
- Sprint 12 scope is deterministic and bounded;
- no ADR is promoted;
- no Phase 0 exit is approved;
- no final schema is established;
- no executable SQL or migration is authorized;
- destructive, tenant-boundary, primary-index, and vendor changes are classified `BLOCKED`;
- additive changes remain `REVIEW_REQUIRED`;
- Sprint 12 source implementation remains blocked pending separate Product Owner authority.

## Decision required after review

After required checks and independent approval succeed, the Product Owner must make a separate exact-head decision:

- approve or reject the Sprint 12 scope;
- authorize or withhold Sprint 12 source implementation;
- authorize or withhold Ready transition and merge of the entry-gate PR.

No implementation branch, PHP source file, test file, composer update, SQL, migration, database connection, deployment, or Sprint 13 work may be created before that decision.

## Production boundary

Production schema, production table, executable SQL, migration execution, deployment, release, POS, and all business modules remain NO-GO.

Attribution: Lab | zefry
