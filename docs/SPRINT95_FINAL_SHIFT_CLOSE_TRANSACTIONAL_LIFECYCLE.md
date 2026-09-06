# Sprint95 — Final Shift Close Transactional Lifecycle

Author by Lab | zefry

## Purpose

Sprint95 materializes the bounded Final Shift Close application lifecycle on top of the canonical Sprint94 authorization contract and the source-published migration #27 schema.

This sprint does not execute migration #27, expose a route/UI, bind or activate a production runtime path, deploy, release, activate the updater, or grant Technical Preview / Production authority.

## Transaction contract

`CloseShift` requires the dedicated `pos.shift.close` permission through durable scoped authorization before entering the persistence transaction. `CloseShiftCommand` contains only a stable operation identifier; the caller cannot supply authoritative shift identity, reconciliation amounts, variance direction, accepted-review outcome, closer identity, or close timestamp.

Inside one persistence transaction `LaravelCloseShiftRepository`:

1. resolves an exact operation replay first and returns durable close evidence without repeating lifecycle mutation;
2. locks the active tenant/outlet/device shift and derives its opener identity;
3. rejects any already-closed shift shape;
4. locks authoritative closing-cash evidence;
5. derives expected cash using `LaravelExpectedCashSnapshotReader` while the transaction is active;
6. derives canonical MATCH / OVER / SHORT variance with `DeriveCashVariance`;
7. rejects a close timestamp earlier than the durable closing-cash cutoff;
8. for nonzero variance, resolves exactly one durable `REVIEW_ACCEPTED` decision bound to the complete reconciliation snapshot;
9. enforces the Sprint94 actor separation policy;
10. inserts append-only Final Shift Close evidence;
11. atomically changes the locked shift `active_slot` from `1` to `NULL`;
12. returns the durable close result.

## Authorization and separation

The canonical Sprint94 posture remains unchanged:

- permission: `pos.shift.close`;
- default role grant: `NO_DEFAULT_GRANT`;
- closer must differ from opener;
- for nonzero variance, closer must differ from explanation author;
- for nonzero variance, closer must differ from reviewer;
- review acceptance is reconciliation evidence only, never close authority.

## Replay and fail-closed behavior

The operation fingerprint binds the verified closer identity and verified tenant / organization / outlet / device context. An exact operation replay returns the original durable close evidence, including the original correlation identifier and close timestamp, and does not release the active slot a second time.

A different operation cannot close the same shift again. Malformed durable values, unsupported runtime state, execution outside a transaction, missing closing evidence, ambiguous/missing accepted review, actor-separation violation, impossible timestamp ordering, or lifecycle persistence failure all fail closed with `PosTransactionViolation`.

## Qualification

The executable Sprint95 regression proves:

- MATCH closes without review evidence;
- close evidence is persisted and `active_slot` becomes `NULL`;
- exact operation replay returns the same evidence and original close timestamp without duplicate mutation;
- a different operation cannot close an already-closed shift;
- closer equal to opener is denied and leaves the shift active;
- rejected review cannot satisfy nonzero variance;
- accepted nonzero variance is derived from durable evidence and can close only under selected separation-of-duties rules;
- closer equal to reviewer is denied;
- close time before the authoritative cutoff is denied.

## Boundary status

- `FINAL_SHIFT_CLOSE_PERMISSION = DEFINED_AS_pos.shift.close`
- `FINAL_SHIFT_CLOSE_DEFAULT_ROLE_GRANT = NO_DEFAULT_GRANT`
- `FINAL_SHIFT_CLOSE_AUTHORITY = SELECTED`
- `FINAL_SHIFT_CLOSE_TRANSACTIONAL_APPLICATION_SOURCE = MATERIALIZED`
- `FINAL_SHIFT_CLOSE_ROUTE = NOT_IMPLEMENTED`
- `FINAL_SHIFT_CLOSE_UI = NOT_IMPLEMENTED`
- `MIGRATION_27_SOURCE = PUBLISHED`
- `MIGRATION_27_LIVE_EXECUTION = NOT_AUTHORIZED`
- `TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY = NOT_GRANTED`
- `PRODUCTION_AUTHORITY = NOT_GRANTED`
- `UPDATER_ACTIVATION_AUTHORITY = NOT_GRANTED`
