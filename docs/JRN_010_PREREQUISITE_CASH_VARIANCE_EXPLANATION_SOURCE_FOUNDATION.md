# JRN-010 Prerequisite — Cash Variance Explanation Source Foundation

Author by Lab | zefry

## Status

`SPRINT70 SOURCE-PUBLISHED FOUNDATION / DURABLE APPEND-ONLY NON-ZERO VARIANCE EXPLANATION / MIGRATION #25 SOURCE-PUBLISHED ONLY / NO RUNTIME WIRING / NO REVIEWER POLICY / NO CLOSE AUTHORITY / JRN-010 SHIFT CLOSE NOT SELECTED`

Sprint70 publishes exactly the nine-path durable cash-variance explanation source envelope frozen by canonical Sprint69.

It does not publish provider/config wiring, permission/default grants, controller, route, API resource, UI, reviewer/approval policy, privileged step-up, close authority, final shift-state transition, deployment, release, updater activation, Technical Preview activation, Production activation, migration execution/application, rollback, or destructive database authority.

## Exact source envelope

The source publication is restricted to exactly nine paths:

1. `.github/workflows/sprint69-jrn010-prerequisite-cash-variance-explanation-source-regression.yml`
2. `apps/web/app/Application/Pos/CashVarianceExplanationCommand.php`
3. `apps/web/app/Application/Pos/CashVarianceExplanationRepository.php`
4. `apps/web/app/Application/Pos/CashVarianceExplanationResult.php`
5. `apps/web/app/Application/Pos/RecordCashVarianceExplanation.php`
6. `apps/web/app/Infrastructure/Pos/LaravelCashVarianceExplanationRepository.php`
7. `apps/web/database/migrations/0000_00_00_000025_create_pos_cash_variance_explanation_evidence_foundation.php`
8. `apps/web/tests/pos-cash-variance-explanation-durable.php`
9. `docs/JRN_010_PREREQUISITE_CASH_VARIANCE_EXPLANATION_SOURCE_FOUNDATION.md`

Sorted newline-terminated path SHA-256:

`c2a575ec728249a8a4b26c173229b26455eee92c7bd4c59026a1d4c064e2c442`

Unknown path count, unknown path, rename, provider/config widening, permission/runtime widening, or fingerprint mismatch fails closed.

## Canonical policy inherited

Sprint70 preserves all prior canonical policy decisions:

- automatic cash-variance tolerance remains exactly `0` atomic units;
- exact `MATCH` does not require explanation under this foundation;
- every `OVER` requires explanation before any later governed adjudication may consider it;
- every `SHORT` requires explanation before any later governed adjudication may consider it;
- explanation does not change expected cash;
- explanation does not change observed closing cash;
- explanation does not change signed variance;
- explanation does not change variance direction;
- explanation is not reviewer approval;
- explanation is not close authority.

## Caller-intent command

`CashVarianceExplanationCommand` accepts only:

- stable operation identity;
- explanation text.

It does not accept caller-supplied variance identity, tenant, organization, outlet, shift, evidence identity, cutoff, amounts, currency, scale, actor identity, approval state, or close state.

Operation identity uses the canonical bounded stable-operation identifier pattern.

Explanation text is canonicalized by:

- normalizing CRLF/CR line endings to LF;
- trimming leading/trailing whitespace;
- requiring non-empty content;
- requiring valid UTF-8;
- rejecting NUL content;
- rejecting content greater than **4096 bytes**.

The 4096-byte bound is an implementation storage/input safety limit, not silent truncation. Oversized content fails closed.

The command semantic fingerprint includes a SHA-256 digest of the canonical explanation text.

## Application service

`RecordCashVarianceExplanation` accepts:

- one canonical `CashVarianceResult`;
- one immutable `CashVarianceExplanationCommand`;
- one bounded correlation identity.

The service:

1. validates the correlation identifier;
2. validates canonical non-zero variance shape;
3. obtains the current verified organizational context;
4. derives `PosExecutionContext`;
5. proves exact tenant/organization/outlet agreement with the variance result;
6. obtains one authoritative positive timestamp from the existing `ShiftOpeningClock` boundary;
7. executes durable recording under the existing `PersistenceTransaction` boundary.

No authorization permission is invented or checked in Sprint70 because no runtime invocation surface is published.

The absence of a permission is not permission to invoke the service in Production.

## Non-zero variance lock

The source accepts only:

- `OVER` when `variance_atomic > 0`;
- `SHORT` when `variance_atomic < 0`.

It rejects:

- `MATCH`;
- zero variance;
- positive variance labeled `SHORT`;
- negative variance labeled `OVER`;
- unknown direction;
- malformed currency;
- unsupported scale;
- negative expected cash;
- negative observed closing cash;
- missing canonical identity;
- non-positive cutoff.

No tolerance, epsilon, percentage, rounding, or magnitude bypass exists.

## Durable repository port

`CashVarianceExplanationRepository` accepts only trusted/canonical persistence inputs:

- verified `PosExecutionContext`;
- canonical `CashVarianceResult`;
- immutable explanation command;
- correlation identity;
- authoritative recorded timestamp.

It does not accept separate caller-provided variance snapshot fields.

It returns one immutable `CashVarianceExplanationResult`.

## Infrastructure adapter

`LaravelCashVarianceExplanationRepository` is explicitly constructible for source-foundation regression and is not registered in `AppServiceProvider`.

The adapter is fail-closed unless:

- persistence is enabled;
- its source-foundation feature boolean is enabled;
- runtime class is one of `local`, `test`, or `ci`.

Production runtime is rejected.

No environment variable or application config key is published by Sprint70.

## Exact context and durable evidence validation

Before insert, the adapter proves exact agreement between the canonical variance and durable storage where independently verifiable.

It validates:

- tenant identity;
- organization identity;
- outlet identity;
- exact shift identity;
- exact opening cash evidence identity;
- exact closing cash evidence identity;
- closing-to-opening relationship;
- closing-to-shift relationship;
- opening-to-shift relationship;
- canonical cutoff equals closing evidence recorded timestamp;
- canonical observed closing atomic value equals durable closing evidence;
- currency agreement;
- scale agreement.

The adapter does not query or substitute the current active shift.

The adapter does not recompute expected cash from caller input.

The expected amount and signed variance remain canonical fields copied from `CashVarianceResult`.

## Durable evidence table

Migration #25 creates exactly one table:

`oneqay_pos_cash_variance_explanation_evidence`

Columns are exactly the minimum selected evidence foundation:

- `tenant_id`;
- `evidence_id`;
- `operation_id`;
- `payload_fingerprint`;
- `shift_id`;
- `opening_cash_evidence_id`;
- `closing_cash_evidence_id`;
- `actor_identity_id`;
- `organization_id`;
- `outlet_id`;
- `cutoff_at_unix`;
- `expected_cash_atomic`;
- `observed_closing_cash_atomic`;
- `variance_atomic`;
- `variance_direction`;
- `currency`;
- `currency_scale`;
- `explanation_text`;
- `correlation_id`;
- `recorded_at_unix`.

There are no reviewer, approval, rejection, waiver, write-off, close-state, settlement, accounting, or final-shift-state columns.

## Signed money snapshot

Expected and observed cash atomic values are stored as non-negative unsigned big integers.

Signed variance is stored as a signed big integer.

The adapter supports signed variance magnitude only within the canonical PHP integer range established by Sprint64.

It rejects unsupported stored signed/unsigned integer representations fail-closed.

No float or double arithmetic is used.

## Stable evidence identity

Explanation evidence identity is deterministically generated from tenant identity and stable operation identity.

The evidence identity identifies one durable explanation evidence row and grants no permission or lifecycle authority.

## Replay fingerprint

The durable payload fingerprint covers the canonical persistence meaning of the request, including:

- authoritative actor identity;
- tenant;
- organization;
- outlet;
- exact shift;
- opening evidence identity;
- closing evidence identity;
- cutoff;
- expected atomic cash;
- observed atomic cash;
- signed variance;
- direction;
- currency;
- scale;
- canonical explanation semantic fingerprint.

Correlation identity and recorded time are deliberately not replay overrides.

## Exact replay behavior

For the same tenant and operation identity:

- exact canonical payload replay returns the original durable evidence;
- conflicting payload reuse fails closed.

Exact replay preserves the original:

- explanation evidence identity;
- variance snapshot;
- explanation text;
- actor attribution;
- correlation identity;
- recorded timestamp.

Retry correlation and retry clock time do not overwrite authoritative evidence.

Cross-tenant use of the same operation identity remains isolated.

## One authoritative explanation rule

The initial foundation allows exactly one authoritative explanation for one canonical closing/variance context.

Migration #25 enforces this using tenant-scoped uniqueness on the closing cash evidence identity.

A second independent operation attempting to explain the same canonical closing evidence fails closed.

Sprint70 does not publish explanation amendment or supersession.

It does not update existing authoritative explanation text.

It does not delete authoritative explanation evidence.

## Tenant-scoped relationships

Migration #25 uses restrictive tenant-scoped relationships to:

- exact POS shift;
- exact opening cash evidence;
- exact closing cash evidence;
- authoritative actor identity;
- exact organization;
- exact outlet.

Delete and update cascades are not selected.

The table uses:

- primary key on `tenant_id + evidence_id`;
- unique replay key on `tenant_id + operation_id`;
- unique one-explanation key on `tenant_id + closing_cash_evidence_id`;
- tenant/outlet/recorded-time index for audit lookup posture.

## Immutable result

`CashVarianceExplanationResult` is a final readonly value carrying only durable explanation evidence.

It exposes no lifecycle mutation method and no approval/close state.

The result is not financial state.

The result is not permission evidence.

The result is not a final reconciliation decision.

## No source mutation of other POS records

Recording explanation evidence does not update or delete:

- shift records;
- opening cash evidence;
- closing cash evidence;
- sales;
- refunds;
- catalog/inventory state.

The dedicated adapter contains no update/delete operation for explanation recording.

## Migration #25 rollback posture

Migration #25 is forward-only.

Its `down()` throws because rollback/destructive database work is not authorized.

Source publication does not execute the migration.

## Dedicated regression evidence

The dedicated regression proves at least:

- durable `OVER` explanation;
- durable `SHORT` explanation;
- `MATCH` denial;
- sign/direction mismatch denial;
- empty explanation denial;
- oversized explanation denial;
- canonical explanation normalization;
- exact variance snapshot persistence;
- actor attribution from verified context;
- tenant isolation;
- context mismatch denial;
- opening/closing/shift exact binding;
- cutoff preservation;
- currency/scale preservation;
- deterministic exact replay;
- original correlation/time preserved on replay;
- conflicting operation reuse denial;
- second authoritative explanation denial;
- cross-tenant operation reuse isolation;
- non-positive clock denial;
- persistence-disabled denial;
- source-feature-disabled denial;
- Production-runtime denial;
- opening/closing evidence unchanged;
- no sale mutation;
- no refund mutation;
- readonly result;
- migration #25 rollback denial;
- absence of runtime repository binding.

## Dedicated workflow evidence

The dedicated Sprint70 workflow:

- enforces exact nine-path count and frozen fingerprint;
- requires exactly migration #25 as the only migration delta;
- proves an exact migration set through #25;
- rejects migration #26;
- validates bounded source locks;
- rejects permission/provider/runtime widening;
- rejects update/delete operations in the adapter;
- validates PHP syntax;
- installs locked Composer dependencies;
- rejects High or Critical Composer advisories;
- runs the dedicated durable explanation regression;
- preserves Sprint64 cash-variance regression;
- preserves Sprint60 expected-cash regression at migration #24 horizon;
- preserves Sprint57 sale-to-shift binding at migration #24 horizon;
- preserves Sprint54 closing-cash regression at migration #23 horizon;
- preserves Sprint53 opening-cash regression at migration #22 horizon;
- asserts lifecycle locks.

No executable regression is skipped to manufacture green status.

## Historical compatibility rule

Fresh qualification may expose historical workflows whose source-envelope or migration-count oracle stops at migration #24.

Such failure must be classified before altering source.

If a failure is historical workflow/oracle incompatibility rather than a Sprint70 business-source defect:

1. the source PR must not merge;
2. all nine Sprint70 source blob identities must be frozen;
3. the smallest workflow-only compatibility predecessor must be published;
4. executable historical business regressions must remain preserved;
5. the compatibility predecessor must fresh-qualify and merge;
6. the exact nine frozen source blobs must be replayed byte-identically from new canonical main;
7. fresh exact-head qualification must be repeated.

Business semantics, migration #25, fail-closed behavior, or dedicated tests must not be weakened to satisfy stale CI.

## Deliberately absent runtime wiring

Sprint70 publishes no:

- `AppServiceProvider` binding;
- config/feature flag;
- environment variable;
- permission;
- default grant;
- controller;
- route;
- API resource;
- UI;
- queue;
- scheduled job;
- webhook.

The source foundation is not a delivered Product feature.

A later runtime-enablement gate, if selected, remains separate.

## Explicit non-scope

Sprint70 does not select or implement:

- reason-code catalog;
- attachments;
- explanation amendment/supersession;
- reviewer/supervisor policy;
- approval/rejection policy;
- maker-checker;
- permission/default grant;
- privileged MFA/step-up;
- close authority;
- final close concurrency/idempotency;
- final evidence freshness window;
- final shift-state transition;
- controlled reopen;
- late-event remediation;
- arbitrary cash movement;
- denomination counting;
- settlement/provider reconciliation;
- accounting/general ledger;
- controller/route/API/UI;
- runtime feature activation;
- Technical Preview activation;
- Production activation;
- deployment/release;
- updater activation;
- migration execution/application;
- rollback/destructive database operations.

## Migration and lifecycle lock

Migration #22: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #23: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #24: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #25: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #26: **NOT SELECTED**

Technical Preview: **INACTIVE**

Production: **NO-GO**

Updater: **INACTIVE**

Deployment/release: **NOT AUTHORIZED**

Migration execution/application: **NOT AUTHORIZED**

Rollback/destructive database operations: **NOT AUTHORIZED**

JRN-010 Shift Close: **NOT SELECTED**

Attribution: **Lab | zefry**
