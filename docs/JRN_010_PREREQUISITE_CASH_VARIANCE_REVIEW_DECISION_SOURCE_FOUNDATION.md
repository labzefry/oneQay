# JRN-010 Prerequisite — Cash Variance Review Decision Source Foundation

Author by Lab | zefry

## Status

`SPRINT79 DURABLE REVIEW-DECISION SOURCE FOUNDATION / EXACT NINE-PATH SOURCE PUBLICATION / MIGRATION #26 SOURCE-PUBLISHED ONLY / REVIEWER PERMISSION NOT SELECTED / NO RUNTIME WIRING / NO CLOSE AUTHORITY / JRN-010 SHIFT CLOSE NOT SELECTED`

Sprint79 publishes the bounded durable reviewer-decision source foundation frozen by canonical Sprint78.

The source foundation records authoritative reviewer evidence only for canonical non-zero cash variance that already has authoritative durable explanation evidence.

It does not publish reviewer permission, role/default grant, provider/config binding, controller, route, API, UI, privileged step-up, close eligibility, close authority, final evidence freshness, final shift mutation, deployment, release, updater activation, Technical Preview activation, Production activation, or shared-environment migration execution.

## Canonical base

Sprint79 begins from canonical main:

`1962f5b814ac2ac6fb9d08bc70a68e49b737a665`

Canonical Sprint75 selects the only reviewer outcomes:

- `REVIEW_ACCEPTED`;
- `REVIEW_REJECTED`.

Canonical Sprint76 requires authoritative durable reviewer evidence distinct from transient reviewer state.

Canonical Sprint77 selects dedicated append-only reviewer-decision persistence and migration #26 semantically.

Canonical Sprint78 freezes the exact nine-path future source envelope and fingerprint.

Sprint79 does not reopen those decisions.

## Exact source envelope

Sprint79 publishes exactly these nine paths:

1. `.github/workflows/sprint78-jrn010-prerequisite-cash-variance-review-decision-source-regression.yml`
2. `apps/web/app/Application/Pos/CashVarianceReviewDecisionCommand.php`
3. `apps/web/app/Application/Pos/CashVarianceReviewDecisionRepository.php`
4. `apps/web/app/Application/Pos/CashVarianceReviewDecisionResult.php`
5. `apps/web/app/Application/Pos/RecordCashVarianceReviewDecision.php`
6. `apps/web/app/Infrastructure/Pos/LaravelCashVarianceReviewDecisionRepository.php`
7. `apps/web/database/migrations/0000_00_00_000026_create_pos_cash_variance_review_decision_evidence_foundation.php`
8. `apps/web/tests/pos-cash-variance-review-decision-durable.php`
9. `docs/JRN_010_PREREQUISITE_CASH_VARIANCE_REVIEW_DECISION_SOURCE_FOUNDATION.md`

The sorted newline-terminated path SHA-256 fingerprint remains exactly:

`890cb08631de804bc2936882f635be11dea52c1e022c4f5112389fd76744ce10`

No tenth source path is authorized by Sprint79.

## Immutable caller intent

`CashVarianceReviewDecisionCommand` accepts only:

- stable operation identity;
- exact authoritative cash-variance explanation evidence identity;
- exact outcome `REVIEW_ACCEPTED` or `REVIEW_REJECTED`.

It does not accept tenant, organization, outlet, shift, variance values, reviewer identity, explanation author identity, explanation fingerprint, permission state, close state, tolerance, accounting treatment, or cash adjustment from the caller.

Alternate aliases such as `APPROVE` and `REJECT` are not canonical stored outcomes and fail closed.

## Authoritative explanation resolution

The reviewer repository family resolves the exact explanation evidence from canonical durable explanation persistence using trusted tenant-scoped context and the exact requested explanation evidence identity.

The source independently validates agreement for:

- tenant;
- organization;
- outlet;
- shift;
- opening cash evidence;
- closing cash evidence;
- cutoff;
- expected cash;
- observed closing cash;
- signed variance;
- variance direction;
- currency;
- currency scale;
- explanation author identity;
- non-empty explanation content;
- authoritative explanation payload fingerprint.

A missing, cross-tenant, malformed, or mismatched explanation fails closed.

The existing `CashVarianceExplanationRepository` interface is not widened or mutated.

## Maker-checker invariant

The authoritative reviewer actor is derived only from verified organizational execution context.

The authoritative explanation author is derived only from durable explanation evidence.

Reviewer identity must differ from explanation author identity.

Self-review is rejected:

- by the application service before reviewer clock/transaction/write side effects;
- independently by the infrastructure repository before insert;
- by migration #26 database-level maker-checker protection for canonical MySQL-compatible targets and isolated SQLite CI fixtures.

Role labels, display names, request payloads, or default administrator status cannot override this invariant.

## Canonical non-zero variance lock

Reviewer-decision source accepts only:

- `OVER` with positive signed variance exactly equal to observed minus expected; or
- `SHORT` with negative signed variance exactly equal to observed minus expected.

`MATCH`, zero variance, wrong sign/direction, inconsistent arithmetic, malformed money metadata, or non-positive cutoff fails closed.

Automatic tolerance remains exactly zero atomic units.

Review does not normalize a non-zero variance into `MATCH`.

## Durable reviewer evidence

Migration #26 creates exactly one dedicated table:

`oneqay_pos_cash_variance_review_decision_evidence`

The authoritative row contains the exact tenant-scoped review subject, explanation binding, maker-checker identities, exact canonical outcome, correlation identity, and authoritative reviewed timestamp selected by Sprint77/Sprint78.

The table is separate from explanation evidence and does not mutate opening cash evidence, closing cash evidence, POS shift state, sale state, or generic audit storage.

The initial foundation permits exactly one authoritative reviewer decision for one exact authoritative explanation evidence record.

No re-review, reversal, override, quorum, escalation, amendment, or supersession model is published.

## Deterministic replay and conflict handling

The reviewer repository preserves tenant-scoped stable operation replay:

- same tenant + same operation + same exact authoritative review payload returns the original evidence;
- same tenant + same operation + conflicting payload fails closed;
- same authoritative explanation + competing independent decision fails closed;
- the same operation identity may be isolated independently across different tenants.

Exact replay returns the original reviewer evidence identity, reviewer identity, explanation binding, outcome, correlation identity, and reviewed timestamp.

A retry cannot replace authoritative evidence from the original successful write.

## Append-only posture

The Sprint79 reviewer adapter publishes no update or delete method for reviewer evidence.

The source foundation performs insert-or-exact-replay only.

No outcome mutation, decision replacement, explanation mutation, or hidden shift-state transition is selected.

Any future correction or supersession requirement requires a separately bounded immutable design.

## Source-foundation runtime guard

The explicit infrastructure adapter requires all of the following:

- persistence enabled;
- source-foundation feature input enabled;
- runtime class limited to `local`, `test`, or `ci`.

Production runtime is rejected.

The source-foundation feature constructor value exists only for explicit regression/source qualification and is not a runtime delivery flag.

No provider or config binding is published by Sprint79.

`CashVarianceReviewDecisionRepository` and `RecordCashVarianceReviewDecision` remain absent from runtime provider binding.

## Reviewer authorization remains separate

Reviewer permission: **NOT SELECTED**

Sprint79 intentionally publishes no reviewer permission identifier and no default grant.

The existing explanation permission:

`pos.shift.cash-variance-explanation.record`

is not reused for reviewer decisions.

A later bounded authorization gate must select reviewer authorization before any delivery surface may invoke this source foundation.

That future authorization must remain tenant/organization/outlet scoped, deny-by-default, and must execute before authoritative reviewer clock, transaction, and persistence side effects.

## Dedicated regression evidence

`apps/web/tests/pos-cash-variance-review-decision-durable.php` exercises the source foundation against an isolated disposable SQLite database.

The regression proves the selected source responsibilities including:

- exact migration horizon through #26;
- runtime binding absence;
- canonical `OVER` acceptance;
- canonical `SHORT` acceptance;
- `MATCH` rejection;
- sign/direction rejection;
- missing explanation rejection;
- wrong explanation subject rejection;
- cross-tenant explanation rejection;
- cross-organization and cross-outlet context rejection;
- self-review rejection;
- exact `REVIEW_ACCEPTED` round-trip;
- exact `REVIEW_REJECTED` round-trip;
- unknown outcome alias rejection;
- exact replay;
- conflicting replay rejection;
- competing decision rejection;
- cross-tenant operation isolation;
- authoritative reviewer identity;
- authoritative explanation author identity;
- exact explanation fingerprint binding;
- malformed explanation fingerprint rejection;
- non-positive reviewed timestamp rejection;
- Production runtime rejection;
- persistence-disabled rejection;
- source-feature-disabled rejection;
- no adapter update/delete path;
- no shift-state mutation;
- no close-authority side effect.

CI migration execution occurs only inside the disposable test database and is not authority to execute migration #26 against a shared environment.

## Qualification workflow

`.github/workflows/sprint78-jrn010-prerequisite-cash-variance-review-decision-source-regression.yml` qualifies the exact Sprint79 nine-path source shape.

It verifies:

- exact path count and frozen path fingerprint;
- exact migration sequence through #26;
- bounded source locks;
- absence of runtime binding and reviewer permission/default-grant publication;
- PHP syntax;
- locked Composer dependency installation;
- blocking high/critical Composer advisory gate;
- dedicated durable reviewer-decision regression;
- preservation of canonical explanation/variance regressions at migration #25 horizon;
- lifecycle locks;
- no deployment or shared migration application authority.

If another canonical historical workflow rejects migration #26 only because it contains a stale exact migration/source-shape oracle, Sprint79 source must not be weakened. The source blobs must be frozen and the smallest workflow-only compatibility predecessor must be qualified and merged before byte-identical source replay.

## Explicit non-scope

Sprint79 does not publish or authorize:

- reviewer permission source;
- reviewer role/default grant;
- provider/config binding;
- controller/route/API/UI;
- reviewer comment;
- rejection reason;
- reason-code catalog;
- re-review/reversal/override/escalation/supersession;
- privileged MFA/step-up;
- close eligibility;
- close permission;
- close authority;
- final evidence freshness;
- close concurrency/idempotency;
- final shift-state transition;
- controlled reopen;
- late-event remediation;
- tolerance widening;
- waiver/write-off;
- arbitrary cash adjustment;
- accounting/general ledger treatment;
- settlement/provider reconciliation;
- shared migration execution;
- migration rollback/destructive operations;
- Technical Preview activation;
- Production activation;
- deployment/release;
- updater activation.

## Lifecycle lock

Migration #22: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #23: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #24: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #25: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #26: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Technical Preview: **INACTIVE**

Production: **NO-GO**

Updater: **INACTIVE**

Deployment/release: **NOT AUTHORIZED**

Migration execution/application: **NOT AUTHORIZED**

Rollback/destructive database operations: **NOT AUTHORIZED**

JRN-010 Shift Close: **NOT SELECTED**

## Next bounded gate

After Sprint79 becomes canonical, the next bounded decision is:

**Sprint80 — Non-Zero Variance Reviewer Authorization Policy Decision Gate**

Sprint80 must remain planning/docs-only unless a separately frozen source envelope is later selected.

It must not infer reviewer authority from role names and must not reuse the explanation-author permission.

## Sprint79 status

**SPRINT79 DURABLE REVIEW-DECISION SOURCE FOUNDATION = SOURCE-PUBLISHED FOR QUALIFICATION.**

**Exact source envelope = 9 paths.**

**Exact path fingerprint = `890cb08631de804bc2936882f635be11dea52c1e022c4f5112389fd76744ce10`.**

**Migration #26 = SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED.**

**Reviewer permission = NOT SELECTED.**

**JRN-010 Shift Close = NOT SELECTED.**

Author by Lab | zefry
