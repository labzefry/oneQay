# JRN-010 Prerequisite — POS Bounded Shift Closing Cash Evidence Foundation

Author by Lab | zefry

## Status

`SOURCE-PUBLISHED DESIGN / LOCAL-TEST-CI ONLY / DEFAULT FALSE / MIGRATION #23 SOURCE ONLY / JRN-010 SHIFT CLOSE NOT SELECTED`

This document defines the bounded source semantics for one immutable operator-observed closing-cash fact per canonical shift.

It does not authorize migration execution, runtime activation outside Local/Test/CI, Technical Preview activation, Production activation, deployment, release, updater activation, rollback, settlement, or accounting.

## Purpose

The canonical POS path already has:

- bounded catalog preparation;
- shift opening;
- active-shift sale/payment/receipt;
- controlled full-sale void;
- full CASH refund evidence;
- bounded inventory baseline;
- immutable opening-cash observation evidence.

Future JRN-010 reasoning still needs an independently observed physical cash amount at the end of the shift before expected-versus-observed variance can be designed safely.

This foundation records only that operator observation.

## Selected operation

The operation is:

**Record one bounded shift closing-cash observation.**

Fresh establishment:

1. derives the exact current active shift from verified server context;
2. requires canonical opening-cash evidence for that same shift;
3. accepts explicit closing cash as atomic units + currency + scale;
4. requires exact opening/closing currency and scale compatibility;
5. records one immutable evidence row;
6. leaves the shift active;
7. performs no expected-cash or variance calculation.

## Explicit non-scope

This foundation does not implement:

- final shift close;
- shift-state transition;
- expected cash;
- expected-versus-actual variance;
- tolerance;
- variance explanation;
- reviewer approval;
- privileged close step-up;
- controlled reopen;
- arbitrary cash movement;
- denomination capture;
- drawer administration;
- settlement;
- provider reconciliation;
- accounting/general ledger;
- purchasing or supplier settlement;
- offline mutation;
- deployment or release.

JRN-010 Shift Close and Operational Reconciliation remains **NOT SELECTED**.

## Authority

Dedicated permission:

`pos.shift.closing-cash.record`

The permission is deny-by-default and receives no default role grant.

Existing permissions do not imply this authority.

## Request boundary

Exact caller business keys:

- `operation_id`;
- `closing_cash_atomic`;
- `currency`;
- `currency_scale`.

Unknown keys fail closed.

The caller cannot provide:

- tenant;
- organization;
- outlet;
- device/register;
- shift id;
- opening-cash evidence id;
- actor;
- role;
- permission;
- session authority;
- expected cash;
- variance;
- reviewer;
- settlement state;
- correlation id;
- event time.

## Money invariant

Closing cash is canonical `Money`:

- atomic units are non-negative integer values;
- currency is canonical three-letter evidence;
- scale follows canonical `Money` validation;
- no floating point;
- no hidden rounding;
- no currency conversion;
- no implicit default.

Zero is valid only when explicitly observed.

Missing physical closing cash is not zero.

The observation must never be synthesized from system expected cash.

## Trusted context

Server-derived evidence includes:

- tenant;
- actor identity;
- organization;
- outlet;
- device-backed register context;
- current active shift;
- same-shift opening-cash evidence;
- correlation identity;
- server time.

Cross-tenant, cross-organization, cross-outlet, wrong-device, missing shift, inactive shift, missing opening evidence, and incompatible money evidence fail closed for fresh establishment.

## Idempotency

Durable idempotency is tenant-scoped:

`tenant_id + operation_id`

The semantic fingerprint binds:

- actor;
- tenant;
- organization;
- outlet;
- device;
- `CLOSING_CASH|<currency>:<scale>:<atomic_units>`.

Exact operation replay is resolved before mutable active-shift and opening-evidence prerequisite validation.

Exact replay returns original immutable evidence and correlation/time facts without another write, even if the shift later becomes inactive.

Conflicting operation reuse fails closed.

## One observation per shift

The database enforces exactly one closing-cash observation per tenant + shift.

A different operation attempting a second observation for the same shift fails closed.

Database uniqueness remains the final concurrency arbiter.

## Opening-cash prerequisite

Fresh closing observation requires exactly one canonical same-shift row in:

`oneqay_pos_shift_opening_cash_evidence`

The closing record stores that immutable opening evidence identifier.

Opening and closing currency must match exactly.

Opening and closing currency scale must match exactly.

No conversion or fallback is permitted.

This prerequisite creates only comparable physical-cash units. It does not yet define how CASH sales/refunds contribute to expected cash.

## Durable evidence table

Migration #23 creates:

`oneqay_pos_shift_closing_cash_evidence`

The table stores:

- tenant;
- deterministic evidence id;
- operation id;
- payload fingerprint;
- shift id;
- opening-cash evidence id;
- authorized actor;
- organization;
- outlet;
- device;
- closing cash atomic amount;
- currency;
- currency scale;
- evidence mode;
- correlation identity;
- recorded time.

Exact evidence mode:

`OPERATOR_OBSERVED_CLOSING_CASH`

Evidence id is deterministic from tenant + operation id using prefix:

`cashclose-`

## Immutability

Closing-cash recording inserts only closing-cash evidence.

It does not mutate:

- `oneqay_pos_shifts.active_slot`;
- shift-opening evidence;
- opening-cash evidence;
- sales;
- payments;
- receipts;
- voids;
- refunds;
- catalog;
- inventory;
- stock;
- expected cash;
- variance;
- settlement;
- accounting state.

The evidence itself has no update/delete operation.

## Persistence ordering

Fresh record ordering is fail-closed:

1. operational feature/runtime guard;
2. deterministic fingerprint;
3. lock exact tenant + operation id and replay/conflict resolution;
4. resolve and lock exact active tenant/outlet/device shift;
5. verify organization context;
6. lock and verify same-shift opening-cash evidence;
7. verify exact currency/scale compatibility;
8. lock existing same-shift closing evidence;
9. insert immutable closing evidence.

The application service runs repository recording inside the canonical persistence transaction.

## Runtime posture

Feature configuration:

`oneqay.pos_shift_closing_cash_evidence.enabled`

Environment binding:

`ONEQAY_POS_SHIFT_CLOSING_CASH_EVIDENCE_ENABLED`

Default:

`false`

Even when armed, infrastructure permits only:

- Local;
- Test;
- CI.

Technical Preview and Production remain unavailable.

## Delivery

Exact route:

`POST /pos/shifts/closing-cash`

Route name:

`pos.shifts.closing-cash`

Required controls include:

- active first-party session;
- bounded throttling;
- canonical POS session context middleware.

Safe authorization failure:

`POS_SHIFT_CLOSING_CASH_AUTHORIZATION_DENIED`

Safe validation/state/storage failure:

`POS_SHIFT_CLOSING_CASH_REJECTED`

Internal database/storage details do not escape.

## Migration posture

Migration #23:

`SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED`

Migration #22 remains:

`SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED`

The migration is additive and forward-only.

Its rollback method throws:

`LogicException('Forward-only generated migration; rollback is not authorized.')`

No migration #24 is selected.

## JRN-010 dependency lock

After this foundation, JRN-010 still requires separately bounded decisions for at least:

- exact eligible CASH sale/refund evidence;
- expected-cash derivation;
- event cutoff and late-event behavior;
- money compatibility across all eligible evidence;
- variance semantics;
- tolerance/explanation policy;
- close authority;
- reviewer/step-up semantics if required;
- one-time close concurrency/idempotency;
- controlled reopen policy if any;
- treatment of arbitrary cash movements;
- settlement boundary without premature accounting.

Therefore publishing this source does not select Shift Close.

## Lifecycle locks

Technical Preview: **INACTIVE**

Production: **NO-GO**

Updater: **INACTIVE**

Migration execution: **NOT AUTHORIZED**

Deployment/release: **NOT AUTHORIZED**

Rollback/destructive database operations: **NOT AUTHORIZED**

Attribution: **Lab | zefry**
