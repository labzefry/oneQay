# JRN-010 Prerequisite — Expected Cash Derivation Source Foundation

Author by Lab | zefry

## Status

`SPRINT60 SOURCE-PUBLISHED FOUNDATION / READ-ONLY STATELESS DERIVATION / NO NEW SCHEMA / MIGRATION #25 NOT SELECTED / JRN-010 SHIFT CLOSE NOT SELECTED`

This source foundation publishes only the bounded read-only expected-cash derivation selected by Sprint58 and frozen by the Sprint59 exact source-envelope gate.

It does not create expected-cash persistence, an endpoint, UI, provider binding, permission, runtime feature flag, expected-versus-observed variance, final shift-state transition, deployment, release, updater activation, Technical Preview activation, Production activation, migration execution, rollback, or destructive database authority.

## Exact source envelope

The source publication is restricted to exactly seven paths:

1. `.github/workflows/sprint59-jrn010-prerequisite-expected-cash-source-regression.yml`
2. `apps/web/app/Application/Pos/DeriveExpectedCash.php`
3. `apps/web/app/Application/Pos/ExpectedCashRepository.php`
4. `apps/web/app/Application/Pos/ExpectedCashResult.php`
5. `apps/web/app/Infrastructure/Pos/LaravelExpectedCashRepository.php`
6. `apps/web/tests/pos-expected-cash-derivation.php`
7. `docs/JRN_010_PREREQUISITE_EXPECTED_CASH_DERIVATION_SOURCE_FOUNDATION.md`

Sorted newline-terminated path SHA-256:

`ae57a65e64fa71509141c224012036c0d69920a48f6f323412d952f9234ba789`

Unknown path count, unknown path, or fingerprint mismatch fails closed.

## Application contract

`DeriveExpectedCash` accepts only a canonical `ShiftClosingCashResult`, not raw caller-provided shift identity, cutoff, expected amount, currency, scale, evidence list, or ordering key.

`ExpectedCashRepository` exposes one read-only derivation operation from that canonical closing-cash evidence result.

`ExpectedCashResult` is immutable derived output containing tenant, organization, outlet, shift, opening evidence identity, closing evidence identity, cutoff, and expected atomic money. It is not mutable financial state and creates no database row.

## Stable snapshot boundary

`LaravelExpectedCashRepository` performs the derivation inside one database transaction and refuses an unknown supported driver or an already-open outer transaction whose isolation level cannot be proven by this bounded source.

For MySQL-compatible runtime it requests `REPEATABLE READ` for the derivation transaction. SQLite is retained only as the deterministic Local/Test/CI regression runtime and uses its transaction snapshot behavior.

Every opening, closing, shift, sale, void, and refund query used by one result executes inside that stable transaction. No insert, update, upsert, delete, increment, decrement, backfill, or aggregate persistence operation exists in the adapter.

## Canonical target and evidence authority

The target is derived from the canonical closing-cash evidence represented by `ShiftClosingCashResult` and then revalidated against the durable closing row.

The durable closing row must exactly preserve its tenant, evidence identity, opening evidence identity, shift identity, operation identity, outlet, device, evidence mode, correlation identity, amount, currency, scale, and cutoff time.

The linked opening evidence and durable shift must exactly agree with the closing row on tenant-owned organization, outlet, device, and shift context. Missing, duplicate, malformed, or cross-context evidence fails closed.

No current-active-shift lookup, outlet-plus-time inference, nearest-shift inference, mutable current state, or historical backfill is used.

## Sale membership and tender semantics

A sale contributes only when durable `shift_id` is non-null and exactly equals the canonical target shift. Its organization, outlet, and device must exactly match the durable bound shift context.

`CASH` remains the only arithmetic-contributing tender category. A canonical `MANUAL_EXTERNAL` sale is explicitly excluded from cash arithmetic and must preserve its canonical evidence mode. An unknown tender category fails closed.

A relevant historical same-context CASH sale with `shift_id = NULL` fails closed rather than being guessed into or out of the target shift.

The eligible CASH sale uses immutable `applied_atomic`. The durable `total_atomic` must match the applied amount under the currently selected completed-sale evidence model. Currency and scale must match the opening/closing basis exactly.

## Void and refund relationship

A canonical `FULL_SALE_VOID` is relationship/correction evidence and contributes zero independent subtraction.

If a void exists for an eligible target-shift CASH sale, its tenant-bound sale relationship, organization, outlet, CASH tender category, `FULL_SALE_VOID` mode, reversed atomic amount, currency, and scale must exactly match the sale.

A full CASH refund may neutralize that sale contribution only when its exact sale and void relationship is canonical and `refunded_atomic == sale.applied_atomic == void.reversed_atomic`, with exact currency/scale and tenant/organization/outlet compatibility.

A fully refunded sale therefore has zero net expected-cash contribution. This is mathematically identical to adding the sale once and subtracting the exact full refund once, while avoiding any ordering-dependent transient arithmetic. The void itself remains zero arithmetic.

Malformed, missing, ambiguous, cross-context, money-incompatible, or mismatched sale/void/refund relationships fail closed.

## Cutoff, same-timestamp, and late evidence

The immutable closing-cash `recorded_at_unix` is the only cutoff.

A target-shift arithmetic-changing CASH sale or refund must occur at or after opening-cash observation time and strictly before the closing-cash timestamp.

An arithmetic-changing sale/refund at the exact same second as closing cash fails closed because no separately canonical total-order key exists.

An otherwise eligible arithmetic-changing sale/refund after the cutoff is late evidence and also fails closed for reconciliation-authoritative derivation. The implementation never moves the cutoff, rewrites historical evidence, manufactures ordering from ids/query order, or silently returns a stale authoritative value.

## Money and arithmetic

All money is integer atomic only through the canonical `Money` value object.

Currency and scale must match exactly across opening basis, closing observation, contributing sales, void relationships, and refunds. No floating point, conversion, coercion, implicit rounding, locale inference, or hidden currency default is used.

Overflow fails closed. A negative expected-cash result is prohibited and remains guarded fail-closed; under the currently selected exact full-refund model a valid canonical relationship cannot legitimately create a negative result.

## Determinism and immutability

Repeated derivation from unchanged canonical evidence returns the same tenant, organization, outlet, shift, evidence identities, cutoff, expected atomic amount, currency, and scale.

The result does not depend on current active-shift state, current wall-clock time, caller ordering, row insertion order, lexical identifiers, mutable catalog price, mutable stock quantity, or current role/grant state.

Derivation creates no rows and mutates no source evidence.

## Dedicated regression evidence

The dedicated regression covers opening-only expected cash, one eligible same-shift CASH sale, zero-arithmetic void, exact full CASH refund neutralization without double subtraction, canonical non-CASH exclusion, relevant historical NULL binding denial, cross-context denial, money incompatibility, same-timestamp ambiguity, late evidence, malformed sale/void/refund relationship, overflow, deterministic repeated reads, no-row-mutation behavior, and stable-transaction query execution.

The dedicated workflow enforces the exact seven-path fingerprint, preserves migrations #1–#24 unchanged, rejects migration #25, validates syntax and locked dependencies, rejects material Composer advisories, runs the Sprint60 regression, and preserves relevant Sprint57, Sprint54, and Sprint52 historical POS horizons without changing their historical schema expectations.

Any additional historical workflow incompatibility discovered by fresh qualification must be corrected separately through bounded workflow-only compatibility work. Business semantics must not be weakened to satisfy a stale oracle.

## Deliberately absent delivery wiring

No `AppServiceProvider` binding is published. No controller, route, API resource, UI, permission, default grant, or runtime feature flag is published.

The source foundation remains directly constructible and testable only. Runtime delivery requires a separate bounded gate and cannot retroactively widen this exact source envelope.

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

This source foundation does not select expected-versus-observed variance, tolerance/explanation policy, close authority or step-up, one-time close concurrency/idempotency, reviewer policy, late-event remediation, controlled reopen, arbitrary cash movement, settlement/reconciliation, or final shift-state transition.

Those remain separately bounded future decisions.

Attribution: **Lab | zefry**
