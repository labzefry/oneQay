# JRN-010 Prerequisite — Non-Zero Cash Variance Explanation and Tolerance Policy Gate

Author by Lab | zefry

## Status

`SPRINT66 POLICY DECISION GATE ONLY / AUTOMATIC TOLERANCE EXACTLY ZERO ATOMIC UNITS / EXPLANATION REQUIRED FOR NON-ZERO VARIANCE / NO REVIEWER POLICY / NO CLOSE AUTHORITY / NO NEW SCHEMA / MIGRATION #25 NOT SELECTED / JRN-010 SHIFT CLOSE NOT SELECTED`

Sprint66 selects only the minimum deterministic explanation/tolerance policy for canonical Sprint64 cash-variance evidence after the Sprint65 adjudication entry gate.

It does not publish application source implementation, explanation persistence, reviewer/approval workflow, permissions, privileged step-up, close authority, concurrency/idempotency, final shift transition, runtime delivery, deployment, release, updater activation, Technical Preview activation, Production activation, migration execution, rollback, or destructive database authority.

## Canonical basis

The canonical baseline is `b592b10300829310423285461b6425f0561531e6`.

Canonical Sprint64 publishes immutable `CashVarianceResult` evidence with:

- exact tenant, organization, outlet, shift, opening evidence, closing evidence, and cutoff identity;
- expected atomic cash;
- observed closing atomic cash;
- signed atomic variance;
- exact direction `MATCH`, `OVER`, or `SHORT`;
- currency and scale.

Canonical Sprint65 classifies exact `MATCH` as mechanically reconciled evidence only and `OVER`/`SHORT` as unresolved variance evidence. None grants shift-close authority.

Sprint66 does not alter that arithmetic or evidence model.

## Selected automatic tolerance policy

The only selected automatic tolerance is:

`automatic_tolerance_atomic = 0`

This means:

- only an exact zero signed variance may qualify as mechanically reconciled without explanation;
- no positive variance is automatically tolerated;
- no negative variance is automatically tolerated;
- no percentage tolerance exists;
- no relative tolerance exists;
- no currency-specific tolerance exists;
- no role-specific tolerance exists;
- no outlet-specific tolerance exists;
- no tenant-specific tolerance exists;
- no time-dependent tolerance exists;
- no floating-point epsilon exists;
- no implicit rounding tolerance exists.

This is an automatic-adjudication policy only.

It does **not** mean every non-zero variance is permanently forbidden from any later governed resolution. It means a non-zero variance can never become automatically reconciled merely because its magnitude is small.

## Exact-match behavior

For a canonical `MATCH` result:

- variance must be exactly `0` atomic units;
- direction must be exactly `MATCH`;
- no variance explanation is required by Sprint66;
- the result remains mechanically reconciled evidence only.

A `MATCH` result still does not:

- authorize final shift close;
- grant permission;
- satisfy privileged MFA or step-up;
- prove one-time close concurrency/idempotency;
- mutate shift state;
- authorize deployment/runtime delivery;
- override final evidence freshness rules.

## Non-zero explanation requirement

For any canonical `OVER` or `SHORT` result, explanation evidence is mandatory before any future policy may consider the variance resolved, reviewed, approved, rejected, written off, remediated, or eligible for final-close evaluation.

The explanation requirement applies to both directions equally.

The explanation must be bound to the exact canonical variance evidence and must not be accepted against:

- another shift;
- another tenant;
- another organization;
- another outlet;
- another opening evidence identity;
- another closing evidence identity;
- another cutoff;
- another expected amount;
- another observed amount;
- another signed variance;
- another direction;
- another currency or scale.

## Explanation content policy

Sprint66 selects a minimal human-readable explanation requirement only.

A future explanation contract must contain a non-empty explanation supplied intentionally for the exact non-zero variance evidence.

Sprint66 does not yet select:

- a reason-code catalog;
- reason-code identifiers;
- free-text maximum length;
- localization rules;
- attachment support;
- evidence-upload support;
- structured metadata;
- reviewer comments;
- approval comments;
- correction categories;
- accounting treatment codes.

Those remain separately bounded design decisions.

The explanation must never be used as a source for recalculating or rewriting the canonical variance.

## Explanation integrity rules

Explanation evidence must be append-only or immutable after it becomes authoritative under a future durability policy.

At minimum, later source design must preserve the following invariants:

- explanation cannot change expected cash;
- explanation cannot change observed closing cash;
- explanation cannot change signed variance;
- explanation cannot change `MATCH`/`OVER`/`SHORT`;
- explanation cannot change evidence identity;
- explanation cannot change the canonical cutoff;
- explanation cannot change currency or scale;
- explanation cannot manufacture tolerance;
- explanation cannot itself grant reviewer approval;
- explanation cannot itself grant close authority.

If correction of explanation text is later required, that correction must use a separately governed supersession/amendment model rather than mutating canonical variance evidence.

Sprint66 does not select that amendment model.

## Direction and sign consistency

Any later explanation policy implementation must reject malformed variance evidence before explanation can be accepted.

Required consistency remains:

- `MATCH` iff variance atomic equals zero;
- `OVER` iff variance atomic is greater than zero;
- `SHORT` iff variance atomic is less than zero.

An explanation cannot normalize or relabel an inconsistent result.

## No magnitude-based bypass

No branch may treat an `OVER` or `SHORT` as equivalent to `MATCH` based on magnitude.

Examples of forbidden implicit behavior include:

- "within one atomic unit";
- "below a percentage";
- "below an outlet threshold";
- "below a manager threshold";
- "rounding difference";
- "small enough";
- "historically acceptable";
- "operator-approved";

Any future tolerance other than exact zero would require a new explicit policy decision and must not be inferred from this gate.

## Reviewer and approval separation

Explanation completeness is not approval.

Sprint66 selects no:

- reviewer role;
- supervisor role;
- maker-checker policy;
- approval quorum;
- permission;
- default grant;
- privileged step-up;
- MFA requirement;
- approval persistence;
- rejection persistence;
- escalation hierarchy.

A future reviewer/approval gate must consume exact variance evidence plus exact explanation evidence without modifying either.

## Persistence posture

Sprint66 does not select a durable explanation table or repository.

No schema is added.

No explanation identifier format is selected.

No retention period is selected.

No migration #25 is selected.

A later source-readiness gate must determine whether explanation evidence must be durable before reviewer/close policy can safely exist. If durability is required, its schema must be separately justified and bounded.

## Fail-closed requirements

Any future explanation/tolerance implementation must fail closed when:

- canonical cash-variance evidence is absent;
- variance evidence is malformed;
- direction/sign consistency fails;
- a caller supplies a non-zero automatic tolerance;
- a caller attempts percentage or implicit tolerance;
- an `OVER` or `SHORT` proceeds without explanation;
- explanation is empty after normalization;
- explanation is bound to a different variance identity;
- explanation attempts to override expected/observed/variance/direction;
- explanation attempts to assert reviewer approval or close authority;
- a `MATCH` is converted into non-zero variance through explanation;
- a non-zero variance is converted into `MATCH` through explanation.

Unknown policy states remain denied by default.

## Determinism

Tolerance evaluation is deterministic:

`variance_atomic === 0`

No wall-clock time, mutable configuration, UI state, current role state, locale, floating point, percentage computation, or outlet/tenant override participates in the Sprint66 automatic tolerance decision.

Explanation requirement is deterministic:

- `MATCH` -> explanation not required by this gate;
- `OVER` -> explanation required;
- `SHORT` -> explanation required.

## Explicit non-scope

Sprint66 does not select or implement:

- non-zero tolerance;
- percentage tolerance;
- reason-code catalog;
- explanation storage;
- explanation amendment/supersession;
- attachments;
- reviewer/supervisor policy;
- approval/rejection policy;
- maker-checker;
- permissions/default grants;
- privileged MFA/step-up;
- close authority;
- one-time close concurrency/idempotency;
- final evidence freshness window;
- final shift-state transition;
- controlled reopen;
- late-event remediation;
- arbitrary cash-in/cash-out movement;
- denomination counting;
- settlement/provider reconciliation;
- accounting/general ledger;
- endpoint/controller/route/API resource;
- UI/reporting;
- runtime feature flag;
- provider binding;
- deployment/release;
- updater activation;
- Technical Preview activation;
- Production activation;
- migration execution/application;
- rollback/destructive database operations.

## Next bounded gate

After Sprint66 is canonical, the next bounded Sprint should determine **explanation source-readiness and durability boundary only**.

That next gate should decide whether mandatory non-zero explanation evidence can remain transient at this stage or must become durable/auditable before any reviewer or close-authority policy is considered.

It must not combine explanation persistence, reviewer approval, permissions, privileged step-up, close concurrency, final shift transition, and runtime delivery into one change.

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

Attribution: **Lab | zefry**
