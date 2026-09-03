# JRN-010 Prerequisite — Expected Cash Source Envelope Gate

Author by Lab | zefry

## Status

`SPRINT59 SOURCE-ENVELOPE GATE ONLY / NO SOURCE IMPLEMENTATION / NO NEW SCHEMA / MIGRATION #25 NOT SELECTED / JRN-010 SHIFT CLOSE NOT SELECTED`

This Sprint59 gate freezes only the minimum future source envelope for the first bounded read-only/stateless expected-cash derivation selected by canonical Sprint58 readiness.

It does not publish application implementation, activate runtime delivery, create an endpoint or UI, select permission or feature-flag semantics, create expected-cash persistence, select migration #25, calculate variance, transition shift state, deploy, release, activate Technical Preview or Production, execute migrations, roll back, or perform destructive database actions.

## Canonical basis

The canonical baseline is `fd7ab27e3ee44bad12a8a57570676312a04238d8`.

Sprint55 froze deterministic expected-cash semantics. Sprint57 published immutable server-derived sale-to-shift binding. Sprint58 confirmed that the first expected-cash implementation can be a read-only/stateless derivation over existing canonical durable evidence, while historical NULL sale bindings, same-timestamp arithmetic-changing ambiguity, late arithmetic-changing evidence, mixed snapshots, money incompatibility, overflow, and negative derived results remain fail-closed.

## Frozen future source envelope

The next bounded source implementation, if separately published, is restricted to exactly these seven paths:

1. `.github/workflows/sprint59-jrn010-prerequisite-expected-cash-source-regression.yml`
2. `apps/web/app/Application/Pos/DeriveExpectedCash.php`
3. `apps/web/app/Application/Pos/ExpectedCashRepository.php`
4. `apps/web/app/Application/Pos/ExpectedCashResult.php`
5. `apps/web/app/Infrastructure/Pos/LaravelExpectedCashRepository.php`
6. `apps/web/tests/pos-expected-cash-derivation.php`
7. `docs/JRN_010_PREREQUISITE_EXPECTED_CASH_DERIVATION_SOURCE_FOUNDATION.md`

The sorted newline-terminated path SHA-256 fingerprint is:

`ae57a65e64fa71509141c224012036c0d69920a48f6f323412d952f9234ba789`

Unknown path count, unknown path, or fingerprint mismatch must fail closed.

## Application boundary

`DeriveExpectedCash.php` may orchestrate one pure read-only derivation request over server-selected canonical evidence. It must not accept caller-supplied expected amount, evidence list, shift id, cutoff timestamp, currency, scale, or ordering key.

`ExpectedCashRepository.php` is the inward-facing read contract. It may expose only the minimum operation needed by the application service to derive one expected-cash result from the canonical closing observation/evidence relationship.

`ExpectedCashResult.php` is immutable derived output. At minimum it must preserve tenant, organization, outlet, shift, opening evidence identity, closing evidence identity, cutoff, expected atomic amount, currency, and currency scale. It is derived evidence and must not represent mutable financial state.

No command that mutates expected cash is selected.

## Infrastructure boundary

`LaravelExpectedCashRepository.php` is the only selected infrastructure query adapter.

It must derive from existing canonical tables only and must not create or modify rows. It must use a stable transactional/evidence snapshot appropriate to the supported database runtime so opening, closing, sale, void, and refund facts are not mixed across materially different concurrent states.

The adapter must derive the target from canonical closing-cash evidence and its linked opening-cash evidence. Caller-selected shift/cutoff/evidence authority is forbidden.

A sale contributes only when its durable `shift_id` is non-null, exact, and context-compatible with the target shift. Historical NULL/malformed/conflicting/cross-context binding fails closed; no outlet-plus-time, nearest-shift, current-active-shift, or inferred backfill is permitted.

Refund shift membership is inherited only through its exact canonical sale relationship. The refund must preserve exact canonical sale/void/refund identity and money invariants before subtraction.

## Arithmetic and cutoff lock

The derivation remains exactly:

`opening cash + eligible completed CASH sale applied amounts - eligible full CASH refund amounts`

`FULL_SALE_VOID` contributes zero arithmetic subtraction.

All arithmetic uses integer atomic money only with exact currency and scale compatibility. No float, conversion, implicit rounding, coercion, hidden default, or locale-dependent arithmetic is permitted.

The immutable closing-cash `recorded_at_unix` remains the cutoff. Arithmetic-changing sale/refund evidence strictly before the cutoff may be evaluated when all invariants pass.

Because no canonical total-order key exists for arithmetic-changing sale/refund evidence at the exact same second as the closing observation, same-timestamp ambiguity remains fail closed. The implementation may not manufacture order from ids, insertion order, row order, operation id, or query order.

Post-cutoff otherwise eligible evidence that would alter expected cash is late evidence and makes reconciliation-authoritative derivation fail closed. It must not silently rewrite the cutoff or produce a stale authoritative result.

Overflow, unsupported range, or negative expected cash fails closed.

## Dedicated regression requirements

`apps/web/tests/pos-expected-cash-derivation.php` must cover at least:

- opening-only derivation;
- exact same-shift CASH sale contribution once;
- canonical void contributes zero subtraction;
- exact full CASH refund subtracts once and does not double-subtract void;
- non-CASH sale exclusion without widening tender semantics;
- historical NULL sale shift binding fails closed when relevant;
- cross-tenant, cross-organization, cross-outlet, and incompatible device/shift evidence fail closed;
- opening/closing/sale/refund currency or scale mismatch fails closed;
- same-timestamp arithmetic-changing ambiguity fails closed;
- post-cutoff arithmetic-changing late evidence fails closed;
- malformed sale/void/refund relationship fails closed;
- arithmetic overflow and negative derived result fail closed;
- repeated read over unchanged canonical evidence is deterministic and creates no rows;
- stable-snapshot protection prevents mixed-state authoritative output.

The test must not migrate or activate Production state.

## Dedicated workflow requirements

`.github/workflows/sprint59-jrn010-prerequisite-expected-cash-source-regression.yml` must:

- enforce exact seven-path count and fingerprint `ae57a65e64fa71509141c224012036c0d69920a48f6f323412d952f9234ba789`;
- preserve all migrations #1–#24 byte-identically;
- reject migration #25 in this bounded source publication;
- validate syntax and locked dependencies;
- reject material dependency advisories according to current repository governance;
- run the dedicated expected-cash derivation regression;
- preserve relevant historical POS/Preview regression horizons rather than bypass them;
- keep unknown source shapes fail closed.

Historical compatibility corrections, if fresh qualification later proves them necessary, must be handled separately as bounded workflow-only predecessors. Business semantics must not be changed merely to satisfy a stale historical oracle.

## Deliberately excluded paths

The source envelope intentionally excludes:

- `AppServiceProvider.php` binding changes;
- controller, route, endpoint, resource, or UI files;
- authorization/permission files;
- configuration or runtime feature-flag files;
- database migrations;
- expected-cash tables/materialized aggregates;
- shift-state transition code;
- variance/tolerance/reviewer code;
- deployment/release/updater files.

Because no runtime delivery surface is selected, dependency injection registration may be deferred. The source foundation may remain directly constructible/testable until a later bounded delivery gate selects runtime wiring. A later gate must not silently widen this source envelope retroactively.

## Migration lock

Migrations #1–#24 must remain unchanged in the bounded source implementation.

Migration #22: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #23: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #24: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #25: **NOT SELECTED**

## Explicit non-scope

Sprint59 does not select or authorize:

- source implementation publication itself;
- endpoint/controller/route/UI;
- runtime provider binding;
- permission/default grant;
- runtime feature flag;
- expected-cash persistence/schema;
- migration #25;
- expected-versus-observed variance;
- variance tolerance/explanation;
- reviewer approval or privileged step-up;
- final shift close/state transition;
- close authority or one-time close concurrency/idempotency;
- controlled reopen;
- arbitrary cash-in/cash-out movement;
- denomination counting;
- settlement/provider reconciliation;
- accounting/general ledger;
- deployment/release/updater activation;
- Technical Preview or Production activation;
- migration execution/application;
- rollback or destructive database operations.

## JRN-010 dependency lock

JRN-010 Shift Close and Operational Reconciliation remains **NOT SELECTED**.

After this gate, separately bounded decisions still remain for source publication/qualification, expected-versus-observed variance semantics, any tolerance/explanation policy, close authority and step-up, one-time close concurrency/idempotency, reviewer policy, late-event remediation, controlled reopen if any, arbitrary cash movement if introduced, and settlement/reconciliation boundaries.

## Lifecycle posture

Technical Preview: **INACTIVE**

Production: **NO-GO**

Updater: **INACTIVE**

Deployment/release: **NOT AUTHORIZED**

Migration execution/application: **NOT AUTHORIZED**

Rollback/destructive database operations: **NOT AUTHORIZED**

Attribution: **Lab | zefry**
