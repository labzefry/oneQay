# Sprint54 JRN-010 Prerequisite Shift Closing Cash Evidence Entry Gate

Author by Lab | zefry

## Gate classification

`BUSINESS MVP RECONCILIATION PREREQUISITE ENTRY GATE ONLY / SOURCE NOT AUTHORIZED / SCHEMA DECISION DEFERRED / JRN-010 NOT SELECTED`

Canonical base: `2b06c448deb2719785ebcc3b77ac8aa572942fdf`.

This gate selects one minimum prerequisite for future JRN-010 reasoning. It creates no source, schema, migration #23, runtime activation, deployment, release, rollback, or Production authority.

## Canonical predecessor

Canonical POS source already publishes bounded JRN-004 catalog preparation, JRN-005 shift opening, JRN-006 active-shift sale/payment/receipt, JRN-007 full-sale void and full CASH refund evidence, JRN-008 inventory baseline, and the Sprint53 JRN-010 prerequisite opening-cash observation foundation.

Sprint53 now provides one immutable operator-observed opening-cash fact per canonical shift. Migration #22 is source-published only and remains not executed/applied/activated.

DEC-001 approves JRN-010 Shift Close and Operational Reconciliation as an MVP journey. DEC-007 requires operational cash reconciliation to compare oneQay records against physical cash/register/shift evidence.

A future close/variance model still lacks an independent operator-observed physical cash amount at the end of the shift. That observation must not be synthesized from expected system cash.

## Selected concern

Sprint54 selects exactly:

**JRN-010 prerequisite — Bounded Shift Closing Cash Observation Evidence**

The future concern may record one immutable operator-observed closing-cash fact for the exact current active shift in verified server context.

It is not Shift Close, expected-cash derivation, variance approval, settlement, drawer administration, or accounting.

## Minimum business objective

A future operation may:

1. resolve the exact current active shift from server-derived tenant/outlet/device context;
2. require that the same shift already has canonical opening-cash observation evidence;
3. accept one explicit operator observation of physical closing cash;
4. bind that observation immutably to the exact shift and authorized actor/context;
5. preserve atomic amount, currency, and scale;
6. return deterministic durable evidence;
7. replay exactly without another write;
8. reject conflicting or duplicate establishment for the same shift.

Zero closing cash is valid only when explicitly observed and recorded. Missing closing-cash evidence must never be silently represented as zero.

The recorded value is an operator observation. It must never be replaced by, copied from, or defaulted to a calculated expected-cash amount.

## Opening-cash prerequisite and money compatibility

Fresh closing-cash observation must fail closed if canonical opening-cash observation evidence does not exist for the same shift.

For this bounded prerequisite, closing-cash currency and scale must match the existing opening-cash observation for the same shift exactly.

This establishes only a comparable physical-cash unit between opening and closing observations. It does not define expected cash, sale/refund aggregation, currency conversion, multi-currency treatment, or variance.

A later bounded gate must still define compatibility across opening cash, eligible CASH sale evidence, eligible CASH refund evidence, and closing cash before expected-versus-actual reconciliation may be selected.

## Fresh active-shift target

Fresh establishment must fail closed unless the server resolves exactly one active canonical shift in the current verified tenant/outlet/device-backed register context.

The caller must not select an arbitrary shift id.

Cross-tenant, cross-organization, cross-outlet, wrong-device, missing-shift, inactive-shift, missing-opening-cash, ambiguous-shift, and incompatible-money-profile states fail closed.

Recording closing-cash evidence does **not** transition the shift out of active state.

## Money observation

The bounded observation consists of:

- non-negative closing cash atomic units;
- a canonical three-letter currency code;
- currency scale from zero through six.

Validation must remain compatible with canonical `Money` invariants and the same-shift opening-cash currency/scale evidence.

Negative values, floating-point input, hidden rounding, currency conversion, implicit defaults, and system-derived observation amounts are excluded.

## Caller-input boundary

The future delivery boundary may accept only:

- stable `operation_id`;
- explicit non-negative `closing_cash_atomic`;
- explicit `currency`;
- explicit `currency_scale`.

The caller must not supply tenant, organization, outlet, device/register authority, shift id, actor, role, permission, session authority, active/closed state, opening cash, expected cash, variance, tolerance, reviewer, settlement state, correlation identity, event time, or arbitrary metadata.

Unknown request keys must fail closed.

## Server-derived authority

The future operation must derive from verified server context:

- tenant;
- actor identity;
- organization;
- outlet;
- device-backed register context;
- session authority;
- current active shift;
- canonical opening-cash observation for that shift;
- correlation identity;
- event time.

## Authorization posture

Closing-cash observation requires dedicated deny-by-default authority.

This entry gate does not freeze an exact permission identifier and does not grant any permission to any role.

Existing shift-open, opening-cash, sale, refund, catalog, inventory, or administrative permissions must not implicitly create this authority. A later schema/source-envelope gate must freeze one exact identifier with no default grant.

## Idempotency and replay

Durable idempotency must be at least `tenant_id + operation_id`.

The semantic fingerprint must bind actor, server-derived organizational/device context, exact shift, closing amount, currency, and scale.

Exact replay must resolve before fresh mutable active-shift and opening-cash prerequisite validation and return original evidence even if the shift is no longer active later. Replay must not create a second record or mutate shift/opening-cash/sale/refund/inventory state.

Conflicting operation reuse fails closed. A different operation attempting a second closing-cash observation for the same shift also fails closed. Database uniqueness remains the final concurrency arbiter.

## Concurrency and immutability

Fresh target resolution, opening-cash prerequisite verification, and first closing-cash evidence creation must be transactional. The later schema/source gate must choose locking and uniqueness strong enough to prevent two concurrent first closing observations for one shift.

Closing-cash evidence is additive and immutable. It must not rewrite shift opening evidence, opening-cash evidence, shift active state, sales, payments, refunds, catalog, inventory baseline, or stock.

## Evidence meaning

The record means an authorized operator observed and recorded a physical cash amount intended as the closing observation for the exact shift.

It does not independently prove denomination composition, physical custody, expected drawer balance, acceptable variance, reviewer approval, settlement, provider reconciliation, or accounting balance.

## Relationship to sale/refund evidence

Canonical CASH sale and CASH refund evidence remain independently immutable. This gate changes neither JRN-006 nor JRN-007.

This gate does not calculate:

`expected_cash = opening_cash + CASH sales - CASH refunds +/- other movements`

No arbitrary cash-movement semantic is selected, so no such movement may be silently assumed in expected-cash reasoning.

A later bounded reconciliation gate must define the exact eligible evidence set, money compatibility, event cutoff, expected-cash derivation, excluded movements, and fail-closed behavior for ambiguous or incompatible evidence.

## JRN-010 selection lock

JRN-010 remains **NOT SELECTED**.

Before JRN-010 may be selected, later bounded gates must still define at least:

- expected-cash derivation from canonical eligible evidence;
- currency/scale compatibility across opening, CASH sales, CASH refunds, and closing observations;
- variance semantics and tolerance;
- close authority and any reviewer/step-up requirement;
- one-time close/idempotency/concurrency behavior;
- event cutoff and treatment of late/refund-after-observation events;
- treatment of excluded arbitrary cash movements;
- settlement boundary without premature accounting;
- controlled reopen policy, if any.

## Explicit non-scope

This gate excludes shift close, shift-state mutation, denomination capture, expected drawer balance, expected-cash calculation, variance, tolerance, explanation, reviewer approval, controlled reopen, arbitrary cash movement, drawer administration, settlement, provider reconciliation, accounting, purchasing, suppliers, stocktake, transfer, partial refund/return, offline mutation, deployment, release, updater activation, Technical Preview activation, Production activation, migration execution/application, rollback, and destructive database operations.

## Schema/source decision deferred

This gate does not select migration #23, a table, columns, endpoint, controller, service, repository, exact permission, runtime flag, regression path, workflow path, or application source envelope.

Those decisions require a separately bounded schema/source-envelope gate after this entry gate is qualified.

## Lifecycle posture

Migration #22 remains **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**.

Migration #23 remains **NOT SELECTED**.

Technical Preview remains **INACTIVE**. Production remains **NO-GO**. Updater remains **INACTIVE**. Deployment, release, migration execution, and rollback remain **NOT AUTHORIZED**.

## Exact entry-gate envelope

Exact path:

`docs/SPRINT_54_JRN_010_PREREQUISITE_SHIFT_CLOSING_CASH_EVIDENCE_ENTRY_GATE.md`

Sorted newline-terminated path SHA-256:

`3c678089eff6fb018013ce1be57d0df0db06e39ab8c89b2523acb33d53ae4bce`

No application source, schema, workflow, dependency, environment, runtime activation, deployment, release, rollback, Production, or migration-execution authority is created.

Attribution: **Lab | zefry**
