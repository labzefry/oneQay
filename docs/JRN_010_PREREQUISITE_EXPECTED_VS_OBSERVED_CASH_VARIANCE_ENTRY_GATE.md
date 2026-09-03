# JRN-010 Prerequisite — Expected-versus-Observed Cash Variance Entry Gate

Author by Lab | zefry

## Status

`SPRINT61 ENTRY-GATE PLANNING ONLY / VARIANCE SEMANTICS SELECTED / NO SOURCE ENVELOPE / NO NEW SCHEMA / MIGRATION #25 NOT SELECTED / JRN-010 SHIFT CLOSE NOT SELECTED`

Sprint61 selects only the minimum deterministic expected-versus-observed cash variance semantics that may follow the canonical Sprint60 read-only expected-cash derivation foundation.

This gate does not publish application implementation, schema, persistence, endpoint, controller, route, UI, permission, runtime feature flag, migration #25, tolerance, explanation workflow, approval, close authority, privileged step-up, final shift transition, deployment, release, updater activation, Technical Preview activation, Production activation, migration execution, rollback, or destructive database authority.

## Canonical prerequisite

Variance is eligible to exist only after the canonical Sprint60 expected-cash derivation succeeds for one canonical closing-cash evidence target.

The expected amount must come from the canonical read-only `ExpectedCashResult` derived from that closing evidence. The observed amount must come from the exact same canonical closing-cash evidence identity already bound into that result.

Caller-supplied expected cash, observed cash, variance, sign, shift identity, cutoff, evidence identity, currency, scale, or evidence list is forbidden.

If expected-cash derivation fails closed for any reason, variance derivation must also fail closed. Sprint61 does not weaken or bypass any Sprint60 evidence, snapshot, ordering, late-event, membership, relationship, or money guard.

## Exact identity lock

A variance result may be derived only when the expected result and durable closing-cash evidence agree exactly on:

- tenant identity;
- organization identity;
- outlet identity;
- shift identity;
- closing evidence identity;
- opening evidence identity where represented by the expected result;
- canonical cutoff timestamp;
- currency;
- currency scale.

Any missing, duplicate, malformed, cross-context, stale, or conflicting identity fails closed.

The implementation must not resolve a current active shift, infer shift membership from outlet plus wall-clock time, substitute another closing observation, or recompute a different cutoff.

## Selected variance formula

Sprint61 freezes one directional formula:

`variance_atomic = observed_closing_atomic - expected_cash_atomic`

The sign has canonical meaning:

- `variance_atomic == 0`: observed cash exactly matches expected cash;
- `variance_atomic > 0`: cash **OVER** — observed cash is greater than expected cash;
- `variance_atomic < 0`: cash **SHORT** — observed cash is less than expected cash.

The result must preserve the signed atomic amount. Implementations must not replace the signed result with an absolute value, invert the sign convention, silently normalize negative values, or infer direction from presentation text.

This formula is evidence comparison only. It does not itself authorize accepting, explaining, tolerating, approving, writing off, settling, or closing any variance.

## Money and arithmetic lock

Expected and observed cash must use integer atomic money only and must have exact currency and exact currency-scale compatibility.

No floating point, currency conversion, coercion, implicit rounding, locale inference, hidden currency default, or presentation-layer arithmetic is permitted.

Signed subtraction overflow or unsupported numeric range fails closed.

A negative variance is valid directional evidence and must not be confused with the Sprint60 prohibition on a negative **expected cash** result.

## Deterministic result contract

A valid bounded variance result must be reproducible from unchanged canonical evidence as the same:

- tenant identity;
- organization identity;
- outlet identity;
- shift identity;
- opening evidence identity;
- closing evidence identity;
- cutoff timestamp;
- expected atomic amount;
- observed atomic amount;
- signed variance atomic amount;
- variance direction (`MATCH`, `OVER`, or `SHORT`);
- currency;
- currency scale.

The variance result is derived evidence only. Sprint61 selects no persistence row, mutable balance, materialized reconciliation state, or independent source of financial truth.

Repeated derivation from unchanged canonical evidence must not insert, update, upsert, delete, increment, decrement, backfill, or otherwise mutate source evidence.

## Inherited cutoff, ambiguity, and late-event posture

Sprint61 inherits the exact Sprint60 cutoff and fail-closed behavior.

The immutable closing-cash `recorded_at_unix` remains the canonical cutoff. Same-timestamp arithmetic-changing sale/refund ambiguity and post-cutoff arithmetic-changing late evidence remain reasons that authoritative expected cash cannot be produced; therefore variance cannot be produced either.

Sprint61 does not authorize moving the cutoff, rewriting closing evidence, manufacturing event order, reopening a shift, deleting late evidence, or accepting a stale expected amount.

## No tolerance semantics selected

Sprint61 deliberately does **not** select a tolerance threshold.

Zero variance has only the mathematical meaning of exact equality. Non-zero variance remains non-zero evidence regardless of magnitude.

No absolute-value threshold, percentage threshold, currency-specific allowance, outlet policy, role-based allowance, rounding band, or automatic pass/fail tolerance is canonical under this gate.

A future bounded gate may select tolerance policy only after defining its authority, scope, exact money semantics, and audit behavior. Until then, no component may silently treat a non-zero variance as equivalent to zero.

## No explanation, approval, or close authority selected

Sprint61 does not select:

- mandatory or optional variance explanation;
- reason codes;
- reviewer or supervisor approval;
- privileged step-up or MFA;
- close permission identifiers;
- automatic acceptance;
- write-off or adjustment;
- drawer correction;
- settlement;
- accounting/general-ledger posting;
- final shift close/state transition;
- controlled reopen.

The existence of a valid variance result must not change shift state and must not create close authority.

## Fail-closed conditions

No reconciliation-authoritative variance may be returned when any material condition is unknown or ambiguous, including:

- expected-cash derivation failure;
- missing or conflicting expected result;
- missing, duplicate, malformed, or conflicting closing evidence;
- expected result and closing evidence identity mismatch;
- cross-tenant, cross-organization, cross-outlet, or cross-shift mismatch;
- closing evidence/cutoff mismatch;
- currency or scale mismatch;
- unsupported money representation;
- signed subtraction overflow;
- same-timestamp arithmetic ambiguity inherited from Sprint60;
- arithmetic-changing late evidence inherited from Sprint60;
- unstable or mixed evidence snapshot inherited from Sprint60;
- caller attempt to override expected, observed, variance, sign, identity, cutoff, currency, or scale.

Unknown evidence or unknown source shape must fail closed rather than be silently ignored or coerced.

## Next bounded gate

Sprint61 selects variance semantics only. It does not freeze an application source envelope.

The next bounded gate may evaluate whether a minimal read-only/stateless variance source implementation can be added without schema or migration #25, using the canonical Sprint60 `ExpectedCashResult` and exact closing-cash evidence as its only authoritative inputs.

That future gate must preserve:

- Sprint60 expected-cash semantics unchanged;
- migrations #1–#24 unchanged;
- migration #25 unselected unless separately justified;
- no persistence unless separately selected;
- no endpoint/UI/permission/runtime activation unless separately selected;
- no tolerance or explanation policy unless separately selected;
- no final close/state transition;
- deterministic exact evidence identity;
- fail-closed unknown shapes and evidence;
- historical compatibility regression horizons.

## Explicit non-scope

Sprint61 does not select or implement:

- variance application source paths;
- variance persistence/schema;
- migration #25;
- endpoint/controller/route/API resource;
- UI or reporting surface;
- permission or default grant;
- runtime feature flag;
- tolerance threshold;
- explanation or reason-code policy;
- reviewer/supervisor approval;
- privileged step-up;
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
- rollback or destructive database operations.

## JRN-010 dependency lock

JRN-010 Shift Close and Operational Reconciliation remains **NOT SELECTED**.

After Sprint61, separately bounded decisions are still required for at least:

- exact variance source readiness and source envelope;
- tolerance/explanation policy if required;
- close authority and any privileged step-up;
- one-time close concurrency/idempotency;
- reviewer policy if required;
- late-event remediation policy;
- controlled reopen policy if any;
- arbitrary cash movement policy if introduced;
- settlement/reconciliation boundary;
- final shift-state transition.

## Migration and lifecycle posture

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

## Exact planning envelope

This Sprint61 entry gate changes exactly one path:

`docs/JRN_010_PREREQUISITE_EXPECTED_VS_OBSERVED_CASH_VARIANCE_ENTRY_GATE.md`

Sorted newline-terminated path SHA-256:

`ad9b4871c6e988a0a1c755a998d13fab88fa0ad98ee57eb26ea3cd596e5a3cbb`

Attribution: **Lab | zefry**
