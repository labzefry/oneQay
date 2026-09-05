# Sprint90 — Post-Close Sale Mutation Freeze

Author by Lab | zefry

## Decision

Sprint90 hardens the durable completed-sale void and full CASH refund repositories so a **new** mutation may proceed only while the sale-bound shift remains active.

This is a prerequisite hardening selected by Sprint89. It is not Final Shift Close implementation and does not select Final Shift Close authority.

## Required semantics

1. Exact operation replay remains the first repository check.
2. For a new mutation, the sale row is locked before its exact bound shift is resolved.
3. The bound shift must match tenant, shift id, organization and outlet, must retain `active_slot = 1`, and is locked with `lockForUpdate()` before mutation side effects.
4. A missing or inactive bound shift fails closed with `PosTransactionViolation`.
5. A denied new void must not restore inventory or create void evidence/events.
6. A denied new full CASH refund must not create refund evidence/events.
7. An exact replay of evidence successfully recorded while the shift was active remains idempotently readable after the shift becomes inactive.

## Explicit boundaries

- no migration #28;
- no migration execution;
- no `CloseShift` application service;
- no `pos.shift.close` permission;
- no route, controller, provider, UI, deployment or updater delta;
- Final Shift Close authority remains `NOT_SELECTED`;
- Technical Preview activation remains `NOT_AUTHORIZED`;
- Production activation remains `NOT_AUTHORIZED`.

## Next prerequisite

After this hardening is canonical, Sprint89 still requires authoritative expected-cash/reconciliation to be derived from raw persisted sale/refund facts before Final Shift Close source implementation may be selected.
