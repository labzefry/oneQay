# JRN-010 Prerequisite — Cash Variance Source Foundation

Author by Lab | zefry

## Status

`SPRINT64 SOURCE-PUBLISHED FOUNDATION / PURE READ-ONLY STATELESS VARIANCE / NO NEW SCHEMA / MIGRATION #25 NOT SELECTED / JRN-010 SHIFT CLOSE NOT SELECTED`

This Sprint64 source foundation publishes only the exact five-path cash-variance derivation frozen by canonical Sprint63.

It does not create variance persistence, a repository, an infrastructure adapter, database reads, database writes, endpoint/UI/permission/runtime feature flag, tolerance, explanation, approval, close authority, final shift-state transition, deployment, release, updater activation, Technical Preview activation, Production activation, migration execution, rollback, or destructive database authority.

## Exact source envelope

The source publication is restricted to exactly five paths:

1. `.github/workflows/sprint63-jrn010-prerequisite-cash-variance-source-regression.yml`
2. `apps/web/app/Application/Pos/CashVarianceResult.php`
3. `apps/web/app/Application/Pos/DeriveCashVariance.php`
4. `apps/web/tests/pos-cash-variance-derivation.php`
5. `docs/JRN_010_PREREQUISITE_CASH_VARIANCE_SOURCE_FOUNDATION.md`

Sorted newline-terminated path SHA-256:

`f668cbbe0bd82b75171a34b410138d1c0c8c8c4196d14f964617e513f2aa59e3`

Unknown path count, unknown path, or fingerprint mismatch fails closed.

## Application contract

`DeriveCashVariance` accepts exactly:

- one canonical Sprint60 `ExpectedCashResult`; and
- the exact canonical `ShiftClosingCashResult` represented by that expected result.

No caller-supplied expected amount, observed amount, tenant, organization, outlet, shift, evidence id, cutoff, variance, direction, currency, scale, tolerance, explanation, approval, or close authority is accepted.

The service has no constructor dependency and performs no repository call, database query, database write, mutable evidence lookup, current-active-shift lookup, or runtime context lookup.

## Exact identity validation

Before arithmetic, the service requires exact agreement between expected and observed contracts for:

- tenant identity;
- outlet identity;
- shift identity;
- opening cash evidence identity;
- closing cash evidence identity;
- cutoff timestamp.

The expected result's organization identity remains authoritative from Sprint60's stable evidence snapshot. It must be non-empty, but Sprint64 does not invent an organization caller input or modify `ShiftClosingCashResult` only to add an organization comparison.

The expected result's tenant, organization, outlet, shift, opening evidence id, and closing evidence id must be non-empty. The canonical cutoff must be positive.

A mismatch, blank canonical identity, non-positive cutoff, or attempt to combine an expected result with a different closing observation fails closed with the existing POS transaction violation boundary.

## Money compatibility

Expected and observed values remain canonical non-negative `Money` instances.

Before arithmetic:

- currency must match exactly;
- currency scale must match exactly.

No conversion, float, decimal coercion, implicit rounding, tolerance, epsilon, locale conversion, or hidden currency default exists.

The result copies canonical currency and scale from expected cash after compatibility is proven.

## Signed variance arithmetic

The only formula is:

`variance_atomic = observed_closing_atomic - expected_cash_atomic`

Direction is exactly:

- zero: `MATCH`;
- positive: `OVER`;
- negative: `SHORT`.

Canonical `Money` cannot represent negative values, so signed variance is intentionally stored as a signed integer atomic value in `CashVarianceResult`, not as `Money`.

The implementation avoids unchecked signed subtraction:

- if observed is greater than or equal to expected, it computes `observed - expected`;
- otherwise it computes non-negative magnitude `expected - observed` first and then negates that magnitude.

Because both canonical input atomic values are non-negative runtime integers, each magnitude is bounded by `PHP_INT_MAX`. The regression explicitly qualifies the zero-to-`PHP_INT_MAX` positive and negative boundaries without floating-point coercion.

No arithmetic writeback occurs.

## Immutable result

`CashVarianceResult` is a final readonly value carrying only:

- tenant identity;
- organization identity;
- outlet identity;
- shift identity;
- opening cash evidence identity;
- closing cash evidence identity;
- cutoff timestamp;
- expected cash atomic amount;
- observed closing atomic amount;
- signed variance atomic amount;
- direction;
- currency;
- currency scale.

Direction values are restricted by the derivation service to canonical `MATCH`, `OVER`, or `SHORT`.

The result is derived reconciliation evidence only. It is not mutable financial state and grants no permission, approval, close authority, lifecycle authority, or shift-state mutation.

## Determinism and no-mutation posture

Repeated derivation from unchanged exact canonical inputs returns equivalent output.

Derivation does not depend on:

- current wall-clock time;
- current active shift;
- mutable database state;
- caller ordering;
- current permissions/grants;
- reviewer state;
- tolerance configuration;
- UI state.

The expected and closing result objects are not mutated. No shift object is read or mutated.

Sprint60 remains solely responsible for the stable evidence snapshot and its opening/closing/shift/sale/void/refund relationship, historical NULL binding, same-timestamp ambiguity, late arithmetic-changing evidence, currency/scale, and expected-cash overflow guards.

Sprint64 does not re-query those durable facts or weaken those guards.

## Dedicated regression evidence

The dedicated regression proves at least:

- exact MATCH;
- positive OVER;
- negative SHORT;
- canonical identity and evidence output;
- expected and observed atomic output preservation;
- tenant mismatch denial;
- outlet mismatch denial;
- shift mismatch denial;
- opening evidence identity mismatch denial;
- closing evidence identity mismatch denial;
- cutoff mismatch denial;
- currency mismatch denial;
- scale mismatch denial;
- blank canonical identity denial;
- non-positive cutoff denial;
- deterministic repeated derivation;
- readonly result contract;
- unchanged expected/closing inputs;
- unchanged shift identity;
- positive `PHP_INT_MAX` boundary;
- negative `PHP_INT_MAX` magnitude boundary;
- absence of database/infrastructure tokens from the application service;
- absence of tolerance/explanation/approval/close transition methods.

The test uses Composer autoload only and does not bootstrap Laravel or a database for the variance derivation itself.

## Dedicated workflow evidence

The dedicated workflow:

- enforces exact five-path count and fingerprint `f668cbbe0bd82b75171a34b410138d1c0c8c8c4196d14f964617e513f2aa59e3`;
- preserves migrations #1–#24 unchanged;
- rejects migration #25;
- validates the pure application boundary;
- rejects repository/infrastructure/database write tokens in the variance source;
- rejects float-based variance source;
- validates PHP syntax;
- installs locked dependencies;
- rejects High or Critical Composer advisories;
- runs the Sprint64 variance regression;
- preserves canonical Sprint60 expected-cash regression;
- preserves Sprint57 immutable sale-to-shift binding regression;
- preserves the Sprint54 closing-cash historical migration horizon;
- preserves the Sprint52 full CASH refund historical migration horizon;
- asserts lifecycle locks from this source-foundation document.

No executable regression is skipped merely to create a green result.

Any future historical workflow incompatibility must first be classified. If it is not a business-source defect, it must be corrected separately through a bounded workflow-only predecessor with exact path/fingerprint recognition, and this exact source envelope must then be replayed byte-identically on fresh canonical main.

## Deliberately absent delivery wiring

No `AppServiceProvider` binding is published because `DeriveCashVariance` has no dependency.

No controller, route, API resource, UI component, permission, default grant, or runtime feature flag is published.

No variance persistence table, repository, materialized aggregate, or migration is published.

The source is directly constructible and executable only as a pure application derivation. Runtime delivery remains a separately bounded future decision.

## Explicit non-scope

Sprint64 does not select or implement:

- variance persistence/schema;
- migration #25;
- variance repository/infrastructure adapter;
- endpoint/controller/route/API resource;
- UI/reporting;
- permission/default grant;
- runtime feature flag;
- tolerance threshold;
- explanation/reason code;
- reviewer/supervisor policy;
- privileged MFA/step-up;
- close authority;
- one-time close concurrency/idempotency;
- final shift-state transition;
- controlled reopen;
- arbitrary cash-in/cash-out movement;
- denomination counting;
- settlement/provider reconciliation;
- accounting/general ledger;
- deployment/release;
- updater activation;
- Technical Preview activation;
- Production activation;
- migration execution/application;
- rollback/destructive database operations.

## Migration and lifecycle lock

Migration #22: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #23: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #24: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #25: **NOT SELECTED**

Technical Preview: **INACTIVE**

Production: **NO-GO**

Updater: **INACTIVE**

Deployment/release: **NOT AUTHORIZED**

Migration execution/application: **NOT AUTHORIZED**

Rollback/destructive database operations: **NOT AUTHORIZED**

JRN-010 Shift Close: **NOT SELECTED**

## Remaining JRN-010 dependencies

Cash variance derivation alone does not authorize final shift close.

Separately bounded decisions remain required for any tolerance/explanation policy, close authority, privileged step-up, one-time close concurrency/idempotency, reviewer policy, late-event remediation, controlled reopen, arbitrary cash movement, settlement/reconciliation boundary, final shift-state transition, and runtime delivery surface.

Attribution: **Lab | zefry**
