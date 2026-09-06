# Sprint91 — Transaction-Aware Expected Cash Snapshot

Author by Lab | zefry

## Purpose

Sprint91 closes Final Shift Close Blocker B selected by Sprint89 without implementing Final Shift Close itself.

The canonical standalone expected-cash repository previously owned both transaction creation and stable-snapshot arithmetic. That prevented the same arithmetic from being reused inside a future outer atomic Final Shift Close transaction.

## Bounded source decision

Sprint91 extracts the canonical durable snapshot arithmetic into `LaravelExpectedCashSnapshotReader`.

The reader:

- requires an already-active database transaction;
- never opens, commits, or changes transaction isolation itself;
- validates the canonical closing-cash evidence and its opening evidence;
- validates tenant / organization / outlet / device / shift bindings;
- preserves the opening-inclusive / closing-exclusive arithmetic event window;
- preserves CASH versus MANUAL_EXTERNAL handling;
- preserves immutable shift-bound sale validation;
- preserves void/refund cardinality and amount validation;
- preserves legacy null-shift CASH denial;
- preserves currency and scale fail-closed rules;
- returns the existing `ExpectedCashResult` contract.

`LaravelExpectedCashRepository::deriveFrom()` remains the standalone wrapper. It still:

- rejects entry while a transaction is already active;
- selects REPEATABLE READ for MySQL before the standalone transaction;
- supports SQLite test execution;
- opens exactly one governed transaction;
- delegates arithmetic to the same `LaravelExpectedCashSnapshotReader`.

This eliminates duplicate reconciliation arithmetic for the future Final Shift Close repository while preserving the existing standalone contract.

## Exact source envelope

Exactly five paths:

1. `.github/workflows/sprint91-transaction-aware-expected-cash-snapshot-regression.yml`
2. `apps/web/app/Infrastructure/Pos/LaravelExpectedCashRepository.php`
3. `apps/web/app/Infrastructure/Pos/LaravelExpectedCashSnapshotReader.php`
4. `apps/web/tests/pos-expected-cash-transaction-aware-snapshot.php`
5. `docs/SPRINT91_TRANSACTION_AWARE_EXPECTED_CASH_SNAPSHOT.md`

Sorted newline-terminated SHA-256:

`c09d42e49f3ad4086591dbb7dc7cc964d4868de1228025223bd06933cef50bd6`

## Qualification requirements

The dedicated regression proves:

- reader invocation outside a transaction fails closed;
- reader invocation inside an existing transaction succeeds;
- the standalone wrapper remains deterministic and equivalent;
- the standalone wrapper still rejects nested transaction entry;
- CASH sale contribution remains unchanged;
- void-only treatment remains unchanged;
- full CASH refund treatment remains unchanged;
- repeat snapshot output remains deterministic;
- no migration source changes are included;
- no Final Shift Close source or permission is introduced.

Existing historical and application regressions remain required CI evidence. Any stale historical exact-envelope failure must be corrected separately without weakening executable regressions.

## Explicit boundaries

Sprint91 does **not** add or modify:

- `CloseShift` application source;
- `CloseShiftCommand`, `CloseShiftRepository`, or `CloseShiftResult`;
- `LaravelCloseShiftRepository`;
- Final Shift Close permission or actor policy;
- provider/runtime bindings;
- routes/controllers/UI;
- migrations;
- migration execution;
- deployment/release/updater logic.

Migration #27 remains SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED.

Final durable Shift Close authority remains NOT SELECTED.

Technical Preview remains NO-GO.
Production remains NO-GO.
Updater remains INACTIVE.

## Sprint91 decision

Subject to exact-head qualification and canonical merge:

- `FINAL_SHIFT_CLOSE_MUTATION_FREEZE = SOURCE_QUALIFIED`
- `FINAL_SHIFT_CLOSE_TRANSACTION_AWARE_SNAPSHOT = SOURCE_QUALIFIED`
- `EXPECTED_CASH_STANDALONE_TRANSACTION_WRAPPER = PRESERVED`
- `EXPECTED_CASH_REUSABLE_ACTIVE_TRANSACTION_READER = MATERIALIZED`
- `EXPECTED_CASH_ARITHMETIC_SEMANTICS = UNCHANGED`
- `FINAL_SHIFT_CLOSE_SOURCE = NOT_IMPLEMENTED`
- `FINAL_SHIFT_CLOSE_PERMISSION = NOT_DEFINED`
- `FINAL_SHIFT_CLOSE_AUTHORITY = NOT_SELECTED`
- `MIGRATION_27_LIVE_EXECUTION = NOT_AUTHORIZED`
