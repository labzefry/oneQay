# JRN-010 Prerequisite — Cash Variance Source Envelope Gate

Author by Lab | zefry

## Status

`SPRINT63 SOURCE-ENVELOPE GATE ONLY / NO SOURCE IMPLEMENTATION / READ-ONLY STATELESS VARIANCE / NO NEW SCHEMA / MIGRATION #25 NOT SELECTED / JRN-010 SHIFT CLOSE NOT SELECTED`

This Sprint63 gate freezes only the minimum future source envelope for the read-only/stateless expected-versus-observed cash variance derivation selected by canonical Sprint61 semantics and confirmed source-ready by Sprint62.

It does not publish application source implementation, create persistence or schema, select migration #25, add a repository or infrastructure adapter, create an endpoint/UI/permission/runtime feature flag, introduce tolerance/explanation/approval/close authority, transition shift state, deploy, release, activate the updater, activate Technical Preview, activate Production, execute migrations, roll back, or perform destructive database actions.

## Canonical basis

The canonical baseline is `91d12e48dd2753646306c16f61eef45c57fdb5df`.

Canonical Sprint60 already publishes immutable `ExpectedCashResult` over a stable evidence snapshot. Sprint61 freezes the variance formula and direction semantics. Sprint62 confirms the variance source can remain pure, read-only, and stateless over exactly one canonical `ExpectedCashResult` plus the exact canonical `ShiftClosingCashResult` represented by that expected result.

No additional database read is required after expected cash exists.

## Collision and dependency conclusion

At this gate, canonical source has no existing `CashVariance` or `DeriveCashVariance` class collision.

The canonical `Money` value object represents non-negative money only and rejects subtraction that would become negative. Therefore signed variance must not be modeled as a `Money` instance.

The future bounded implementation must use the two canonical `Money` inputs only to validate exact currency/scale compatibility and obtain their non-negative atomic units. It may then compute a signed integer atomic delta using overflow-safe integer arithmetic.

No repository or infrastructure adapter is selected.

## Frozen future source envelope

The next bounded source implementation, if separately published, is restricted to exactly these five paths:

1. `.github/workflows/sprint63-jrn010-prerequisite-cash-variance-source-regression.yml`
2. `apps/web/app/Application/Pos/CashVarianceResult.php`
3. `apps/web/app/Application/Pos/DeriveCashVariance.php`
4. `apps/web/tests/pos-cash-variance-derivation.php`
5. `docs/JRN_010_PREREQUISITE_CASH_VARIANCE_SOURCE_FOUNDATION.md`

The sorted newline-terminated path SHA-256 fingerprint is:

`f668cbbe0bd82b75171a34b410138d1c0c8c8c4196d14f964617e513f2aa59e3`

Unknown path count, unknown path, or fingerprint mismatch must fail closed.

The envelope intentionally contains no repository interface, infrastructure adapter, database migration, provider binding, controller, route, API resource, UI, permission, or runtime feature flag.

## Application boundary

`DeriveCashVariance.php` may accept only:

- one canonical `ExpectedCashResult`; and
- the exact canonical `ShiftClosingCashResult` from which that expected result was derived.

It must not accept caller-supplied tenant, organization, outlet, shift, opening evidence identity, closing evidence identity, cutoff, expected amount, observed amount, variance, direction, currency, scale, tolerance, explanation, approval, or close-state authority.

The service must perform no database read and no database write.

`CashVarianceResult.php` must be immutable derived output. It must contain only the minimum canonical evidence needed to make the result self-describing and deterministic:

- tenant identity;
- organization identity inherited from the expected result;
- outlet identity;
- shift identity;
- opening cash evidence identity;
- closing cash evidence identity;
- cutoff timestamp;
- expected atomic amount;
- observed atomic amount;
- signed variance atomic amount;
- direction;
- currency;
- currency scale.

The result is derived reconciliation evidence only. It is not mutable financial state and does not authorize shift close.

## Exact identity lock

Before arithmetic, the implementation must prove exact equality between the expected and closing result for every identity exposed by both contracts:

- tenant identity;
- outlet identity;
- shift identity;
- opening cash evidence identity;
- closing cash evidence identity;
- cutoff timestamp, where `ExpectedCashResult::cutoffAtUnix()` must exactly equal `ShiftClosingCashResult::recordedAtUnix()`.

The expected result's organization identity remains authoritative from Sprint60. The current closing result does not expose organization identity, so the implementation must not invent a caller-supplied organization comparison and must not modify `ShiftClosingCashResult` merely to widen this source envelope.

Any mismatch, missing canonical result, malformed contract, or attempt to combine an expected result with a different closing result fails closed.

## Money and signed arithmetic lock

Expected and observed cash must have exact currency and exact scale compatibility before arithmetic.

The only selected formula remains:

`variance_atomic = observed_closing_atomic - expected_cash_atomic`

Direction remains:

- zero: `MATCH`;
- positive: `OVER`;
- negative: `SHORT`.

Because both canonical `Money` atomic values are non-negative integers bounded by the runtime integer range, the future implementation must avoid an unchecked subtraction that could coerce or overflow.

A bounded safe strategy is:

- when observed is greater than or equal to expected, compute `observed - expected`;
- when observed is less than expected, compute the non-negative magnitude `expected - observed` first and negate that magnitude;
- reject any unsupported runtime integer representation or arithmetic state.

No floating point, decimal string coercion, currency conversion, implicit rounding, tolerance, epsilon, or locale-dependent arithmetic is selected.

Signed variance is valid evidence and must remain signed. It must not be converted into non-negative `Money`.

## Determinism and immutability

Repeated derivation from unchanged exact canonical inputs must return equivalent output.

The result must not depend on:

- current wall-clock time;
- current active shift;
- mutable database state;
- caller ordering;
- current permission/grant state;
- UI state;
- tolerance configuration;
- reviewer state.

No input object may be mutated. No source evidence may be re-queried or rewritten.

Sprint60 remains solely responsible for the stable database snapshot and the opening/closing/shift/sale/void/refund, cutoff, same-timestamp, late-event, historical NULL-binding, money, and overflow guards required to produce the canonical expected result.

## Dedicated regression requirements

`apps/web/tests/pos-cash-variance-derivation.php` must cover at least:

- exact MATCH;
- positive OVER;
- negative SHORT;
- exact expected/observed atomic evidence preservation;
- exact tenant mismatch denial;
- exact outlet mismatch denial;
- exact shift mismatch denial;
- exact opening evidence identity mismatch denial;
- exact closing evidence identity mismatch denial;
- cutoff mismatch denial;
- currency mismatch denial;
- scale mismatch denial;
- deterministic repeated derivation;
- immutable result behavior;
- no database reads;
- no database writes;
- no shift mutation;
- boundary arithmetic using zero and `PHP_INT_MAX` canonical money values without floating-point coercion or overflow;
- no tolerance, explanation, approval, or close authority.

The regression must operate on canonical immutable result objects and must not require a new database migration.

## Dedicated workflow requirements

`.github/workflows/sprint63-jrn010-prerequisite-cash-variance-source-regression.yml` must:

- enforce exact five-path count and fingerprint `f668cbbe0bd82b75171a34b410138d1c0c8c8c4196d14f964617e513f2aa59e3`;
- reject any database migration path and preserve migrations #1–#24 unchanged;
- reject migration #25;
- validate PHP syntax and locked dependencies;
- reject material dependency advisories according to current repository governance;
- run the dedicated cash-variance regression;
- preserve the canonical Sprint60 expected-cash derivation regression;
- preserve relevant historical POS regression horizons without generic bypass;
- keep unknown source shapes fail closed.

Historical workflow/oracle incompatibility discovered by fresh qualification must be classified before source changes. If it is not a business-source defect, correction must be a separate bounded workflow-only predecessor and the source envelope must later be replayed byte-identically.

## Deliberately excluded paths and semantics

The frozen source envelope excludes:

- repository interfaces and infrastructure adapters;
- database schema or variance persistence;
- migration #25;
- `AppServiceProvider.php` binding changes;
- controller, route, endpoint, API resource, or UI files;
- permission/default-grant changes;
- runtime feature flags;
- tolerance thresholds;
- explanation or reason codes;
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
- deployment/release/updater files.

A later gate must not silently widen this exact envelope retroactively.

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

## Next bounded gate

After Sprint63 is canonical, the next bounded Sprint may publish the exact five-path read-only/stateless cash-variance source foundation and fresh-qualify it against current and historical material regression horizons.

It must not expand into tolerance, explanation, approval, close authority, final shift transition, runtime delivery, or lifecycle activation.

Attribution: **Lab | zefry**
