# JRN-007 POS Bounded Full CASH Refund Evidence Foundation

Author by Lab | zefry

## Status

`SOURCE-PUBLISHED CONTRACT / RUNTIME ACTIVATION NOT AUTHORIZED`

Sprint52 extends the already-published JRN-007 full completed-sale void foundation with one narrowly bounded full CASH refund evidence operation.

This source contract does not authorize migration execution, Technical Preview activation, Production activation, deployment, release, updater activation, rollback, or destructive database operations.

## Purpose

The foundation records one immutable operator-authorized fact that the exact applied CASH amount of an eligible canonical sale has been refunded after that sale already has canonical `FULL_SALE_VOID` evidence.

The refund is distinct from the void:

- JRN-007 void restores stock exactly once from immutable original sale lines.
- Sprint52 refund records financial-operational evidence only.
- Refund never restores stock a second time.
- Original sale, receipt, payment, line, and void evidence remain immutable.

## Permission

Exact permission:

`pos.sale.refund`

The permission has no default grant.

Existing sale, void, catalog, shift, or inventory permissions do not imply refund authority.

## Input boundary

Exact caller-owned input:

- `operation_id`;
- `sale_id`.

The caller cannot supply:

- refund amount;
- tender category;
- currency or scale;
- void id;
- tenant;
- organization;
- outlet;
- device;
- actor;
- role/permission;
- shift/register;
- stock quantity;
- item/return quantity;
- reason;
- provider/external reference;
- event time;
- correlation identity;
- arbitrary metadata.

Unknown HTTP keys fail closed.

## Server-owned execution context

The service derives from verified server context:

- actor identity;
- tenant;
- organization;
- outlet;
- device;
- session authority;
- correlation identity;
- server time.

Cross-tenant, cross-organization, and cross-outlet targets fail closed.

The correction device may differ from the original sale/void device when the current actor is independently authorized in the same tenant/organization/outlet.

## Eligibility

A fresh refund succeeds only when:

1. the canonical sale exists in the exact tenant;
2. sale organization/outlet match current server context;
3. original sale tender category is exactly `CASH`;
4. exact canonical JRN-007 void evidence exists for the sale;
5. void organization/outlet match the sale;
6. void tender category is exactly `CASH`;
7. void evidence mode is exactly `FULL_SALE_VOID`;
8. void reversed amount, currency, and scale match original sale applied evidence;
9. no prior refund evidence exists for the sale.

A non-voided sale, MANUAL_EXTERNAL sale, provider/electronic sale, corrupt/inconsistent void, duplicate refund, or ambiguous target fails closed.

## Amount derivation

Refund amount is never caller-controlled.

The exact invariant is:

`refunded_atomic == sale.applied_atomic == void.reversed_atomic`

The original sale currency and scale are preserved.

For CASH, `change_atomic` is original tender-change evidence and is never included in refund amount.

## Durable evidence

Migration #21 source creates exactly:

`oneqay_pos_sale_cash_refunds`

Each successful first refund stores:

- tenant/refund identity;
- operation id;
- semantic payload fingerprint;
- sale id;
- exact void id;
- current authorized actor;
- organization;
- outlet;
- current device;
- derived refunded atomic amount;
- currency/scale;
- CASH tender classification;
- `FULL_CASH_REFUND` evidence mode;
- server correlation identity;
- server occurrence time.

Uniqueness:

- `tenant_id + operation_id`;
- `tenant_id + sale_id`;
- `tenant_id + void_id`.

Migration #21 is forward-only.

## Idempotency and replay

Replay lookup occurs first by:

`tenant_id + operation_id`

Exact fingerprint match returns the original durable refund result.

Exact replay does not:

- create another refund;
- create another event;
- restore stock;
- mutate sale/void evidence;
- depend on current historical device identity.

Conflicting operation-id reuse fails closed.

A different operation id for an already-refunded sale fails closed.

## Concurrency

Fresh refund processing locks the exact target sale and exact void evidence before the one-refund check.

Competing operations serialize on the target sale, while database unique constraints remain the final defensive boundary.

Application-only duplicate checks are not relied upon as the sole concurrency control.

## Audit event

First successful refund records exactly one immutable sale event:

`REFUNDED`

The event uses current authorized actor, refund operation id, server correlation identity, and server occurrence time.

Exact replay never duplicates the event.

## Inventory invariants

Refund performs zero catalog or stock mutation.

JRN-007 remains the only current full-void stock restoration path.

Refund never:

- increments/decrements `available_quantity`;
- rewrites inventory baseline evidence;
- changes catalog price/currency/scale/sellability;
- creates another void;
- changes sale lines.

## Shift and cash-drawer separation

Refund evidence does not require an active shift.

It does not create or mutate:

- shift;
- register;
- opening cash;
- drawer balance;
- denomination count;
- cash count;
- variance;
- settlement;
- payout.

The source records an authorized refund evidence fact; it does not claim independent proof of physical cash custody movement.

JRN-010 remains separately gated.

## Delivery boundary

Endpoint:

`POST /pos/sales/cash-refund`

The route exists only when:

- runtime class is Local/Test/CI;
- canonical session control is enabled;
- `ONEQAY_POS_SALE_CASH_REFUND_ENABLED=true`.

The feature defaults to false.

Required request middleware includes active session, bounded throttling, and canonical POS session/context verification.

Successful response is no-store/private and identifies the durable refund evidence.

Authorization denial returns bounded safe 403.

Invalid/conflicting refund state returns bounded safe 422.

## Regression requirements

Dedicated regression proves:

- permission denied by default;
- full eligible CASH refund success;
- applied amount derivation and cash-change exclusion;
- exact sale/void binding;
- non-voided denial;
- MANUAL_EXTERNAL denial;
- cross-tenant/organization/outlet denial;
- inconsistent void denial;
- exact replay;
- conflicting idempotency denial;
- second-refund denial;
- one REFUNDED event;
- immutable sale/void evidence;
- zero stock mutation;
- no shift mutation;
- feature-disabled and Production runtime denial;
- strict unknown-field rejection;
- route middleware presence;
- migration #21 forward-only behavior.

Historical JRN-004/JRN-005/JRN-006/JRN-007/JRN-008 regressions remain preservation requirements.

## Explicit non-scope

This foundation does not add:

- partial refund;
- item return;
- partial return;
- exchange;
- MANUAL_EXTERNAL refund;
- provider refund/reversal;
- chargeback/dispute;
- asynchronous provider outcome;
- refund fee;
- restocking fee;
- refund reason workflow;
- stock adjustment;
- cash drawer automation;
- cash counting;
- variance;
- settlement;
- accounting;
- purchasing/supplier lifecycle;
- transfer/stocktake;
- JRN-010;
- offline mutation.

## Lifecycle posture

Migration #21 is:

**SOURCE-PUBLISHED ONLY — NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #20 remains source-published only.

Technical Preview:

**NOT ACTIVATED**

Production:

**NO-GO / NOT ACTIVATED**

Deployment:

**NOT EXECUTED**

Updater:

**UNCHANGED / NOT ACTIVATED**

Source publication never implies runtime activation.

Attribution: **Lab | zefry**
