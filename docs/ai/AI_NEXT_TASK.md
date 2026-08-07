# AI Next Task

## Stable checkpoint

- Project: OneQay
- Repository: `labzefry/oneQay`
- Developer and Product Engineering Entity: Lab | zefry
- Canonical checkpoint path: `docs/ai/`
- Canonical Phase 0 status: In Progress
- Active program: M5 — Engineering State, CI & Governance Stabilization
- Active micro-milestone: M5.1 — Canonical State Reconciliation
- Current `main`: `7a9def560466fc8bf81529c2b5125c6ac19a96b5`
- Current `main` tree: `d27dae3c8cc9be77a590187cabd49ac469a6943f`
- Sprint 13: Published
- Sprint 14: Not Authorized
- Production readiness: NO-GO

## Canonical Sprint 13 facts to preserve

- Canonical PR: #64
- Canonical source head: `4a2e44cc31361954b126e8857de65fcccca30445`
- Canonical source tree: `5a0adb0d2ce80338f9f9d782f0871fb2115afd5d`
- Canonical published commit: `ebe6abcf77263bf644565ca2fbe2b2844416d49b`
- Canonical published tree: `5a0adb0d2ce80338f9f9d782f0871fb2115afd5d`
- PR #65 reconciliation published commit: `7a9def560466fc8bf81529c2b5125c6ac19a96b5`
- Product Owner local `composer test`: PASS, 402 assertions, exit code `0`
- Regression evidence classification: POST-PUBLICATION
- Canonical reviewer: `zefriansyah`
- Canonical reviewed exact head: `4a2e44cc31361954b126e8857de65fcccca30445`
- Alternate head `ba312fa9095d434c204f01e3dac9870e9eaa4d6d`: NON-CANONICAL
- Historical alternate-head review references: preserved as historical contamination, not canonical evidence

## Immediate authorized task

Complete only M5.1 lifecycle processing for the bounded canonical-state correction:

1. maintain exactly one atomic M5.1 content commit;
2. keep the change set limited to the three canonical `docs/ai/` checkpoint files plus the three root deprecation/pointer stubs;
3. open and keep the pull request as Draft;
4. run proportionate documentation/governance validation;
5. verify GitHub checks on the exact head;
6. request independent review from `zefriansyah` only at the proper technical gate;
7. report exact base, head, tree, commit count, changed files, checks, review state, unresolved threads, and lifecycle boundary;
8. stop before Ready or Merge unless separate Product Owner authority is provided.

## Root checkpoint rule

The root files:

- `AI_SESSION_STATE.md`
- `AI_PROJECT_STATE.md`
- `AI_NEXT_TASK.md`

are deprecated pointer stubs only. They are not authoritative and must not be used as active checkpoints.

Canonical state lives under `docs/ai/`.

## Lifecycle facts that must not be rewritten

- PR #64 has a historical lifecycle sequencing discrepancy.
- PR #65 has a historical lifecycle discrepancy.
- The 402-assertion Composer PASS is post-publication evidence and does not retroactively change historical sequencing.
- GitHub review history must not be rewritten.

## Explicit exclusions

M5.1 does not authorize:

- Ready transition;
- merge or auto-merge;
- M5.2 implementation before M5.1 publication;
- Sprint 14 implementation;
- executable SQL;
- migration execution;
- production database modification;
- production table creation;
- deployment;
- release;
- POS, ERP, WMS, CRM, or other business-module implementation;
- Enterprise Vision implementation;
- ADR/GD promotion;
- JRN resolution;
- production readiness promotion.

## Next milestone after M5.1 publication

Only after M5.1 is properly published, the next bounded micro-milestone is:

**M5.2 — CI & Lifecycle Control Hardening**

M5.2 still requires the proper lifecycle start boundary. It is not started by this checkpoint.

Attribution: Lab | zefry
