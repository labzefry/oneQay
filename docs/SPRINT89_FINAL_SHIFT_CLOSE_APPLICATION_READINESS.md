# Sprint89 — Final Shift Close Application Readiness

Author by Lab | zefry

## 1. Purpose

Sprint89 selects the bounded application/repository contract required before durable Final Shift Close source may be materialized.

Sprint89 is readiness and contract selection only. It does **not** create `CloseShift`, `CloseShiftCommand`, `CloseShiftRepository`, `CloseShiftResult`, `LaravelCloseShiftRepository`, a Final Shift Close permission, provider/runtime bindings, routes/UI, migration execution, deployment, or activation.

## 2. Canonical starting point

Sprint88 published qualified migration #27 source for append-only `oneqay_pos_shift_close_evidence`. Migration #27 is source-published only and remains **NOT EXECUTED / NOT APPLIED**.

Canonical source already provides:

- durable shift opening with active-slot ownership;
- immutable opening-cash and closing-cash evidence;
- durable expected-cash reconstruction from shift-bound sales, voids, and full CASH refunds;
- canonical cash-variance derivation;
- immutable nonzero-variance explanation evidence;
- maker-checker review-decision evidence;
- `PersistenceTransaction` for bounded durable writes.

No Final Shift Close application source or permission exists.

## 3. Selected future application contract

A later source sprint may materialize these application types only after the blockers in this decision are closed:

- `CloseShift.php`;
- `CloseShiftCommand.php`;
- `CloseShiftRepository.php`;
- `CloseShiftResult.php`;
- `LaravelCloseShiftRepository.php`.

The selected future repository method shape is conceptually:

`CloseShiftRepository::close(PosExecutionContext, CloseShiftCommand, correlationId, closedAtUnix): CloseShiftResult`

The future `CloseShiftCommand` carries only a stable operation identifier and a constant semantic fingerprint part such as `CLOSE_SHIFT`. The request/caller must not supply authoritative monetary values, variance direction, evidence identifiers, review outcome, actor scope, or close timestamp.

The future application service must obtain verified organizational context, derive `PosExecutionContext`, require a separately selected dedicated Final Shift Close permission, obtain server-owned close time, and invoke the repository inside one `PersistenceTransaction`.

Because the dedicated permission and actor policy are not selected yet, `CloseShift` application source remains blocked.

## 4. Selected atomic repository responsibility

`LaravelCloseShiftRepository` must own the entire finalization write boundary. It must not split final evidence insertion and active-slot release across independently callable repositories.

Inside the same outer `PersistenceTransaction`, the future repository must:

1. resolve an existing close row by (`tenant_id`, `operation_id`) under lock before requiring an active shift, so an exact successful replay remains possible after `active_slot` became `NULL`;
2. reject the replay if the stored payload fingerprint differs;
3. lock the exact shift for current tenant/organization/outlet/device and require `active_slot = 1` for a first close;
4. reject an already-finalized shift under a competing operation identifier;
5. lock all sales bound to the shift in deterministic `sale_id` order before deriving reconciliation state;
6. resolve and lock exactly one canonical opening-cash evidence row and exactly one canonical closing-cash evidence row for the shift;
7. re-derive authoritative expected cash from the locked/stable durable snapshot;
8. derive canonical variance using the same arithmetic contract as `DeriveCashVariance`;
9. for `MATCH`, require zero variance and persist no review reference/outcome;
10. for `OVER` or `SHORT`, resolve the unique explanation for the closing-cash evidence, resolve the unique review decision for that explanation, require `REVIEW_ACCEPTED`, and prove every tenant/organization/outlet/shift/opening/closing/cutoff/expected/observed/variance/direction/currency/scale binding exactly matches the final close candidate;
11. insert exactly one immutable `oneqay_pos_shift_close_evidence` row;
12. update exactly one `oneqay_pos_shifts` row from `active_slot = 1` to `active_slot = NULL`;
13. fail closed if the affected active-slot row count is not exactly one;
14. roll back both effects on any failure.

The final close evidence fingerprint must bind at minimum the closer actor, tenant, organization, outlet, device, shift, opening evidence, closing evidence, cutoff, expected cash, observed cash, variance amount/direction, currency/scale, optional accepted review evidence, operation semantic, and immutable review/explanation fingerprints where applicable.

## 5. Concurrency lock contract

Canonical sale completion locks the active shift row before creating a shift-bound sale. Canonical void and CASH-refund paths lock the affected sale row, and refund additionally locks its void row.

Therefore the selected Final Shift Close lock order begins with the exact shift row and then all shift-bound sale rows in deterministic order before reconciliation reads. This serializes a close candidate against:

- a concurrent new sale through the shared shift lock;
- a concurrent void through the shared sale-row lock;
- a concurrent full CASH refund through the shared sale-row lock.

The implementation must keep a deterministic lock order and must treat any row-set inconsistency as `PosTransactionViolation` / transaction failure.

This lock strategy protects the close transaction itself, but it does **not** solve mutations attempted after the close has already committed. That is Blocker A below.

## 6. Blocker A — post-close sale mutation freeze

Current durable sale completion requires an active shift, but current void and full CASH refund repositories resolve the original sale directly and do not require that the sale's bound shift still has `active_slot = 1`.

That means a future Final Shift Close could become historically inconsistent if a void or CASH refund were accepted after the shift had already been finalized.

Before `CloseShift` source is materialized, a bounded prerequisite source change must make shift-bound void/refund mutation fail closed when the sale's canonical shift is no longer active.

Selected prerequisite semantics:

- resolve the sale's non-null canonical `shift_id`;
- lock the matching tenant/organization/outlet shift row;
- require `active_slot = 1` before first void/refund mutation;
- exact operation replay may still return already persisted immutable void/refund evidence after the shift closes, but must not create a new mutation;
- cross-tenant/outlet/device/shift mismatches fail closed;
- a closed shift cannot receive a new void or refund under a new operation ID.

This prerequisite is `FINAL_SHIFT_CLOSE_MUTATION_FREEZE = REQUIRED_BEFORE_CLOSE_SOURCE`.

## 7. Blocker B — transaction-aware expected-cash snapshot

`LaravelExpectedCashRepository::deriveFrom()` currently rejects an existing transaction and opens its own stable transaction. That is correct for standalone expected-cash derivation, but it cannot be called from inside the outer atomic Final Shift Close `PersistenceTransaction`.

Duplicating expected-cash arithmetic inside `LaravelCloseShiftRepository` is not selected because duplicate arithmetic would create reconciliation drift risk.

Before Final Shift Close source is materialized, a bounded prerequisite refactor must extract the current stable-snapshot arithmetic into one reusable infrastructure component, conceptually `LaravelExpectedCashSnapshotReader`, with these rules:

- the snapshot reader requires an already-active transaction;
- it performs the canonical durable opening/sale/void/refund/closing validation and returns `ExpectedCashResult` without opening or committing a transaction;
- existing `LaravelExpectedCashRepository::deriveFrom()` remains the standalone wrapper that opens its governed transaction and delegates to the same reader;
- future `LaravelCloseShiftRepository` uses the same reader inside the outer Final Shift Close transaction;
- no arithmetic semantics, event window, currency/scale rule, or legacy-data denial may be weakened by the refactor.

This prerequisite is `FINAL_SHIFT_CLOSE_TRANSACTION_AWARE_SNAPSHOT = REQUIRED_BEFORE_CLOSE_SOURCE`.

## 8. Idempotency and final-state contract

Required future behavior:

- exact replay of an already successful close returns the same immutable result without another active-slot update;
- same operation ID with a different fingerprint fails closed;
- a competing operation ID for an already closed shift fails closed;
- concurrent close attempts cannot both succeed;
- failed finalization leaves `active_slot = 1` and no partial close evidence;
- successful finalization leaves exactly one close evidence row and `active_slot = NULL`;
- after success, no new sale, void, refund, closing-cash, or second close mutation may alter the finalized shift lifecycle.

## 9. Permission and actor policy remain unselected

Sprint89 does not define `pos.shift.close` and does not reuse any existing permission as implicit close authority.

In particular:

- `pos.shift.closing-cash.record` is not Final Shift Close authority;
- `pos.shift.cash-variance-review-decision.record` is review-only and is not Final Shift Close authority;
- reviewer acceptance is evidence precondition for nonzero variance, not permission to close;
- no default role grant is selected;
- closer-vs-opener, closer-vs-explanation-author, and closer-vs-reviewer separation policies remain for a later explicit authorization decision.

## 10. Sprint89 decision

Subject to successful exact-head Sprint89 qualification:

- `FINAL_SHIFT_CLOSE_APPLICATION_CONTRACT = SELECTED`
- `FINAL_SHIFT_CLOSE_REPOSITORY_CONTRACT = SINGLE_ATOMIC_REPOSITORY`
- `FINAL_SHIFT_CLOSE_COMMAND_AUTHORITY_INPUT = OPERATION_ID_ONLY`
- `FINAL_SHIFT_CLOSE_MUTATION_FREEZE = REQUIRED_BEFORE_CLOSE_SOURCE`
- `FINAL_SHIFT_CLOSE_TRANSACTION_AWARE_SNAPSHOT = REQUIRED_BEFORE_CLOSE_SOURCE`
- `FINAL_SHIFT_CLOSE_APPLICATION_READINESS = BLOCKED_PENDING_PREREQUISITE_HARDENING`
- `FINAL_SHIFT_CLOSE_APPLICATION_SOURCE = NOT_IMPLEMENTED`
- `FINAL_SHIFT_CLOSE_PERMISSION = NOT_DEFINED`
- `FINAL_SHIFT_CLOSE_AUTHORITY = NOT_SELECTED`
- `MIGRATION_27_SOURCE = QUALIFIED_SOURCE_ONLY`
- `MIGRATION_27_LIVE_EXECUTION = NOT_AUTHORIZED`
- `TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY = NOT_GRANTED`
- `DEPLOYMENT_EXECUTION_AUTHORITY = NOT_GRANTED`
- `PRODUCTION_AUTHORITY = NOT_GRANTED`
- `UPDATER_ACTIVATION_AUTHORITY = NOT_GRANTED`

`BLOCKED_PENDING_PREREQUISITE_HARDENING` is a fail-closed readiness outcome. It means the selected Final Shift Close contract is sufficiently specified, but implementation must not begin until post-close mutation freeze and transaction-aware expected-cash snapshot prerequisites are source-qualified and canonical.

## 11. Explicitly absent Sprint89 source

Sprint89 must not add or modify:

- `CloseShift.php`;
- `CloseShiftCommand.php`;
- `CloseShiftRepository.php`;
- `CloseShiftResult.php`;
- `LaravelCloseShiftRepository.php`;
- `PosPermission.php`;
- provider bindings;
- routes/controllers/UI;
- migration files;
- current void/refund/expected-cash runtime source;
- deployment/release/activation logic.

## 12. Current NO-GO boundaries

Until separately selected, source-qualified, and authorized:

- migration #27 live execution remains **NOT AUTHORIZED**;
- Final durable Shift Close authority remains **NOT SELECTED**;
- Final Shift Close application/runtime remains **NOT IMPLEMENTED**;
- post-close mutation freeze remains **NOT IMPLEMENTED**;
- transaction-aware close snapshot remains **NOT IMPLEMENTED**;
- Technical Preview remains **NOT ACTIVATED / NO-GO**;
- Production remains **NO-GO**;
- updater remains **INACTIVE**.
