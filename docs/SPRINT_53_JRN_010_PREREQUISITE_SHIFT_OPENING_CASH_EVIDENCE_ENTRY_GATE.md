# Sprint53 JRN-010 Prerequisite Shift Opening Cash Evidence Entry Gate

Author by Lab | zefry

## Gate classification

`BUSINESS MVP RECONCILIATION PREREQUISITE ENTRY GATE ONLY / SOURCE NOT AUTHORIZED / SCHEMA DECISION DEFERRED / JRN-010 NOT SELECTED`

Canonical base: `8a091b7d049d423436da3865bb6a7dc698b25667`.

This gate selects one minimum prerequisite for future JRN-010 reasoning. It creates no source, schema, migration #22, runtime activation, deployment, release, rollback, or Production authority.

## Canonical predecessor

Canonical POS source already publishes bounded JRN-004 catalog preparation, JRN-005 shift opening, JRN-006 active-shift sale/payment/receipt, JRN-007 full-sale void and full CASH refund evidence, and JRN-008 inventory baseline.

Sprint48 JRN-005 intentionally excluded opening-cash amount policy. The current `oneqay_pos_shifts` evidence stores shift/context/opening identity and time but no money amount, currency, or scale.

DEC-001 approves JRN-010 Shift Close and Operational Reconciliation as an MVP journey. DEC-007 requires cash reconciliation to remain evidence-based and distinct from payment acceptance.

A future variance model cannot silently assume unknown physical opening cash is zero.

## Selected concern

Sprint53 selects exactly:

**JRN-010 prerequisite — Bounded Shift Opening Cash Observation Evidence**

The future concern may record one immutable operator-observed opening-cash fact for the exact current active shift in verified server context.

It is not Shift Close, a drawer ledger, settlement, or accounting.

## Minimum business objective

A future operation may:

1. resolve the exact current active shift from server-derived tenant/outlet/device context;
2. accept one explicit operator observation of opening cash;
3. bind the observation immutably to the exact shift and authorized actor/context;
4. preserve atomic amount, currency, and scale;
5. return deterministic durable evidence;
6. replay exactly without another write;
7. reject conflicting or duplicate establishment for the same shift.

Zero opening cash is valid only when explicitly observed and recorded. Unknown opening cash must never be silently represented as zero.

## Current money-profile limitation

Canonical source has no tenant/outlet/shift default money profile. Catalog and sale evidence carry their own currency and scale; shift evidence does not.

Therefore this gate forbids silently deriving opening-cash currency or scale from an arbitrary catalog row, sale history, environment setting, locale, or hidden platform default.

The future record is operator-observed money evidence. Currency and scale are part of that observation unless a separately governed canonical money-profile concern is later selected.

## Fresh active-shift target

Fresh establishment must fail closed unless the server resolves exactly one active canonical shift in the current verified tenant/outlet/device-backed register context.

The caller must not select an arbitrary shift id.

Cross-tenant, cross-organization, cross-outlet, wrong-device, missing-shift, inactive-shift, and ambiguous-shift states fail closed.

## Money observation

The bounded observation consists of:

- non-negative opening cash atomic units;
- a canonical three-letter currency code;
- currency scale from zero through six.

Validation must remain compatible with canonical `Money` invariants.

Negative values, floating-point input, hidden rounding, currency conversion, and implicit defaults are excluded.

## Caller-input boundary

The future delivery boundary may accept only:

- stable `operation_id`;
- explicit non-negative `opening_cash_atomic`;
- explicit `currency`;
- explicit `currency_scale`.

The caller must not supply tenant, organization, outlet, device/register authority, shift id, actor, role, permission, session authority, active-state value, correlation identity, event time, closing count, variance, or arbitrary metadata.

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
- correlation identity;
- event time.

## Authorization posture

Opening-cash observation requires dedicated deny-by-default authority.

This entry gate does not freeze an exact permission identifier and does not grant any permission to any role.

Existing POS permissions must not implicitly create this authority. The later schema/source-envelope gate must freeze one exact identifier with no default grant.

## Idempotency and replay

Durable idempotency must be at least `tenant_id + operation_id`.

The semantic fingerprint must bind actor, server-derived organizational/device context, exact shift, amount, currency, and scale.

Exact replay must resolve before fresh mutable active-shift validation and return original evidence even if the shift is no longer active later. Replay must not create a second record or mutate shift/sale/refund/inventory state.

Conflicting operation reuse fails closed. A different operation attempting a second opening-cash observation for the same shift also fails closed. Database uniqueness remains the final concurrency arbiter.

## Concurrency and immutability

Fresh target resolution and first evidence creation must be transactional. The later schema/source gate must choose locking and uniqueness strong enough to prevent two concurrent first observations for one shift.

Opening-cash evidence is additive and immutable. It must not rewrite shift opening evidence, shift active state, sales, payments, refunds, catalog, inventory baseline, or stock.

## Evidence meaning

The record means an authorized operator observed and recorded an opening physical-cash amount for the exact shift.

It does not independently prove denomination composition, hardware state, physical custody, external settlement, or accounting balance.

## Relationship to sale/refund evidence

Canonical CASH sale and CASH refund evidence remain independently immutable. This gate changes neither JRN-006 nor JRN-007 and does not require opening-cash evidence before sale completion.

A later reconciliation gate must define how compatible currency/scale evidence contributes to expected-versus-observed cash and must fail closed on incompatible or ambiguous money evidence.

## JRN-010 selection lock

JRN-010 remains **NOT SELECTED**.

Before JRN-010 may be selected, later bounded gates must still define at least:

- closing cash observation/count semantics;
- currency/scale compatibility across opening, CASH sales, CASH refunds, and closing observations;
- expected-cash derivation;
- variance semantics;
- close authority;
- one-time close/idempotency/concurrency behavior;
- treatment of excluded arbitrary cash movements;
- settlement boundary without premature accounting.

## Explicit non-scope

This gate excludes shift close, closing count, denominations, expected drawer balance, variance, arbitrary cash movement, drawer administration, settlement, accounting, purchasing, suppliers, stocktake, transfer, partial refund/return, provider integration, offline mutation, deployment, release, updater activation, Technical Preview activation, Production activation, migration execution/application, rollback, and destructive database operations.

## Schema/source decision deferred

This gate does not select migration #22, a table, columns, endpoint, controller, service, repository, exact permission, runtime flag, regression path, workflow path, or application source envelope.

Those decisions require a separately bounded schema/source-envelope gate after this entry gate is qualified.

## Lifecycle posture

Migration #21 remains **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**.

Migration #22 remains **NOT SELECTED**.

Technical Preview remains **INACTIVE**. Production remains **NO-GO**. Updater remains **INACTIVE**. Deployment, release, migration execution, and rollback remain **NOT AUTHORIZED**.

## Exact entry-gate envelope

Exact path:

`docs/SPRINT_53_JRN_010_PREREQUISITE_SHIFT_OPENING_CASH_EVIDENCE_ENTRY_GATE.md`

Sorted newline-terminated path SHA-256:

`02b4f93ee238e6b545870db51dd84036698f179f2f12054445c5c9785a713e0a`

No application source, schema, workflow, dependency, environment, runtime activation, deployment, release, rollback, Production, or migration-execution authority is created.

Attribution: **Lab | zefry**
