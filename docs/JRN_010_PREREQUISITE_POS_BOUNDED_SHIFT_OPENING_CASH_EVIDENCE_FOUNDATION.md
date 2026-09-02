# JRN-010 Prerequisite POS Bounded Shift Opening Cash Evidence Foundation

Author by Lab | zefry

## Status

`SOURCE-PUBLISHED CONTRACT / RUNTIME ACTIVATION NOT AUTHORIZED / JRN-010 NOT SELECTED`

Sprint53 adds one narrowly bounded prerequisite for future shift reconciliation: immutable operator-observed opening-cash evidence for an already-active canonical shift.

This source contract does not authorize migration execution, shift close, cash count, variance, settlement, Technical Preview activation, Production activation, deployment, release, updater activation, rollback, or destructive database operations.

## Purpose

Canonical JRN-005 shift opening intentionally did not include opening-cash amount policy.

The Sprint53 foundation records one explicit opening-cash observation without mutating the original shift-opening evidence.

Unknown physical opening cash is never silently represented as zero.

Explicit zero is a valid observation and is distinguished from missing evidence by the durable evidence row.

## Permission

Exact permission:

`pos.shift.opening-cash.record`

The permission has no default grant.

Existing shift, sale, void, refund, catalog, and inventory permissions do not imply opening-cash authority.

## Input boundary

Exact caller-owned fields:

- `operation_id`;
- `opening_cash_atomic`;
- `currency`;
- `currency_scale`.

The caller cannot provide:

- tenant;
- organization;
- outlet;
- device/register authority;
- shift id;
- actor;
- role;
- permission;
- session authority;
- active state;
- correlation identity;
- event time;
- closing count;
- expected cash;
- variance;
- settlement state;
- arbitrary metadata.

Unknown HTTP keys fail closed.

## Money boundary

The delivery layer constructs canonical `Money` from the explicit observation.

Requirements:

- atomic amount is an integer and non-negative;
- currency is a canonical three-letter code;
- scale is from zero through six;
- floating-point amount is not accepted;
- hidden rounding and conversion are not performed.

Canonical source has no tenant/outlet/shift default money profile. Currency and scale are therefore explicit parts of this operator observation and are never silently inferred from catalog, sale history, environment, locale, or another hidden default.

The record is operational evidence, not external verification of physical cash.

## Server-owned execution context

The service derives from verified server state:

- actor identity;
- tenant;
- organization;
- outlet;
- device-backed register context;
- session authority;
- current active shift for a fresh establishment;
- correlation identity;
- server time.

A caller cannot select an arbitrary shift.

Cross-tenant, cross-organization, cross-outlet, wrong-device, missing-shift, inactive-shift, and ambiguous fresh state fail closed.

## Durable evidence

Migration #22 source creates exactly:

`oneqay_pos_shift_opening_cash_evidence`

Each first success stores:

- tenant/evidence identity;
- operation id;
- semantic payload fingerprint;
- exact server-resolved shift id;
- current authorized actor;
- organization;
- outlet;
- device;
- opening cash atomic amount;
- currency;
- currency scale;
- evidence mode `OPERATOR_OBSERVED_OPENING_CASH`;
- server correlation identity;
- server recorded time.

Uniqueness:

- `tenant_id + operation_id`;
- `tenant_id + shift_id`.

Migration #22 is forward-only.

## Deterministic evidence identity

Evidence id is:

`cashopen-` + first 23 hexadecimal characters of SHA-256 over `tenant_id|operation_id`.

It is exactly 32 characters and is not caller-controlled.

## Idempotency and replay

Replay lookup occurs first by:

`tenant_id + operation_id`

The fingerprint binds current authorized actor/context plus the exact opening money observation.

Exact fingerprint match returns original durable evidence.

Replay does not:

- require the historical shift to remain active;
- create another evidence row;
- rewrite correlation/time evidence;
- mutate shift state;
- mutate sale, refund, catalog, or inventory state.

Conflicting operation reuse fails closed.

A different operation id targeting a shift that already has opening-cash evidence fails closed.

## Fresh establishment ordering

For a fresh operation:

1. operational feature/runtime guard passes;
2. exact replay lookup finds no prior operation;
3. exact active shift is locked by tenant/outlet/device/active slot;
4. organization binding is verified;
5. existing evidence for that exact shift is locked/checked;
6. exact operator money observation is preserved;
7. one immutable evidence row is inserted.

The active shift lock serializes competing first attempts, while database uniqueness is the final concurrency boundary.

## Shift immutability

Opening-cash recording does not modify:

- shift id;
- shift opening operation;
- shift actor/context;
- shift opening time;
- `active_slot`;
- any future close state.

The original JRN-005 shift evidence remains immutable.

## POS isolation

Opening-cash recording performs zero mutation against:

- completed sales;
- sale lines;
- sale events;
- void evidence;
- CASH refund evidence;
- catalog;
- inventory baseline;
- stock quantity.

It does not create a sale, refund, stock movement, or close event.

## Delivery boundary

Endpoint:

`POST /pos/shifts/opening-cash`

The route exists only when:

- runtime class is Local/Test/CI;
- canonical session control is enabled;
- `ONEQAY_POS_SHIFT_OPENING_CASH_EVIDENCE_ENABLED=true`.

The feature defaults to false.

Required middleware includes active session, bounded throttling, and canonical POS session/context verification.

Successful responses are no-store/private and return the original durable correlation/time on replay.

## Regression contract

Dedicated Sprint53 regression proves:

- deny-by-default permission behavior;
- missing active shift denial;
- positive opening-cash observation;
- explicit zero observation;
- exact amount/currency/scale persistence;
- server-resolved shift binding;
- exact replay;
- replay after historical shift deactivation;
- conflicting operation denial;
- second observation for same shift denial;
- tenant isolation;
- disabled-feature denial;
- Production-runtime denial;
- strict request-field boundary;
- invalid money denial;
- route/middleware boundary;
- no shift mutation;
- no sale/refund/catalog mutation;
- exact migrations #1 through #22;
- migration #22 forward-only behavior.

Historical POS regressions remain preservation requirements.

## JRN-010 separation

JRN-010 Shift Close and Operational Reconciliation remains **NOT SELECTED**.

Sprint53 does not define:

- closing cash observation/count;
- denomination policy;
- expected cash calculation;
- currency/scale compatibility across all shift evidence;
- cash variance;
- close authority;
- close state transition;
- settlement;
- accounting.

Opening-cash evidence alone is insufficient to authorize JRN-010.

## Explicit non-scope

This foundation excludes:

- shift close;
- cash count;
- denominations;
- expected drawer balance;
- variance;
- arbitrary cash-in/cash-out;
- drawer administration;
- settlement;
- accounting;
- purchasing/suppliers;
- stocktake;
- transfer;
- arbitrary stock adjustment;
- partial return/refund;
- provider integration;
- offline mutation;
- deployment;
- release;
- updater activation;
- Technical Preview activation;
- Production activation;
- migration execution/application;
- rollback;
- destructive database operations.

## Lifecycle posture

Migration #22:

**SOURCE-PUBLISHED ONLY — NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #21 and earlier migrations remain source-published only.

Technical Preview:

**INACTIVE**

Production:

**NO-GO / NOT ACTIVATED**

Updater:

**INACTIVE / NOT ACTIVATED**

Deployment / release / migration execution / rollback:

**NOT AUTHORIZED**

Source publication never implies runtime activation.

Attribution: **Lab | zefry**
