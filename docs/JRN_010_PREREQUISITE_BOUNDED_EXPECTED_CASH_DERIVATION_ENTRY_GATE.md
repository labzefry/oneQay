# JRN-010 Prerequisite — Bounded Expected Cash Derivation Entry Gate

Author by Lab | zefry

## Status

`ENTRY-GATE PLANNING ONLY / NO SCHEMA / NO APPLICATION SOURCE / MIGRATION #24 NOT SELECTED / JRN-010 SHIFT CLOSE NOT SELECTED`

This Sprint55 entry gate freezes only the minimum deterministic semantics required to derive system expected cash from canonical immutable evidence before variance or final shift close may be selected.

It creates no expected-cash persistence, endpoint, permission, runtime feature flag, application implementation, migration, lifecycle activation, or shift-state transition.

## Purpose

Canonical evidence now provides:

- one immutable operator-observed opening-cash fact for a canonical shift;
- completed CASH sale/payment evidence;
- full completed-sale void evidence;
- full CASH refund evidence after canonical void;
- one immutable operator-observed closing-cash fact for the same canonical shift.

Expected cash must be reproducible from those durable facts. It must never be accepted from the caller and must never be read as an unbounded mutable drawer balance.

## Selected derivation semantic

For one canonical shift and one canonical closing-cash observation, expected cash is derived as:

`opening cash + eligible completed CASH sale applied amounts - eligible full CASH refund amounts`

A canonical full-sale void is a correction relationship marker, not a second independent cash subtraction.

Therefore:

- an eligible completed CASH sale contributes its immutable `applied_atomic` exactly once;
- a CASH sale with canonical `FULL_SALE_VOID` evidence but no canonical full CASH refund still contributes its original applied amount because the void foundation does not claim physical cash movement;
- when the same voided CASH sale has canonical `FULL_CASH_REFUND` evidence, that refund contributes one subtraction equal to the immutable original applied amount;
- the void row itself contributes zero arithmetic amount;
- the refund and void must never both subtract the sale amount;
- replay/audit events do not create additional arithmetic contributions.

This preserves the canonical distinction between stock/correction evidence and financial-operational refund evidence.

## Opening basis

The derivation requires exactly one canonical same-shift opening-cash observation.

Opening cash is never implicit zero.

Explicit observed zero is valid only when represented by the canonical immutable opening-cash evidence row.

Missing, duplicate, ambiguous, cross-shift, or incompatible opening evidence fails closed.

## Canonical shift ownership

The derivation target is the exact shift referenced by canonical closing-cash evidence and its linked opening-cash evidence.

Tenant, organization, outlet, device/register, shift, opening evidence, closing evidence, cutoff, and evidence set are server-owned facts.

The caller may not select or override them.

Every contributing sale/refund relationship must belong to the exact tenant and outlet of the shift. Cross-tenant or cross-outlet evidence fails closed.

## Sale eligibility and shift binding

A completed CASH sale is eligible only when canonical durable evidence proves all of the following:

1. tender category is exactly `CASH`;
2. the sale belongs to the exact tenant, organization, and outlet of the target shift;
3. the sale completion was established under the exact device-backed active shift being reconciled;
4. its immutable completion evidence is within the selected cutoff boundary;
5. its applied amount, currency, and scale are valid and compatible with the opening/closing money basis.

Sprint55 does not authorize inference of shift membership from outlet plus wall-clock time alone.

If current canonical durable sale evidence cannot independently and unambiguously prove which exact shift authorized a historical sale, expected-cash source implementation remains blocked until a separately bounded source/schema decision provides that durable relationship or another exact proof.

No mutable current-state query may guess historical shift membership.

## Void relationship

Canonical `FULL_SALE_VOID` evidence is eligible for relationship evaluation only when it is the exact tenant-bound void for an eligible canonical sale and its derived reversed amount/currency/scale matches that sale.

Void does not itself prove physical cash left the drawer and therefore contributes zero arithmetic amount.

A malformed, duplicate, cross-tenant, cross-outlet, money-inconsistent, or ambiguous void relationship fails closed.

## Refund eligibility

A canonical full CASH refund contributes one subtraction only when all of the following hold:

1. it references an eligible canonical CASH sale;
2. it references that sale's exact canonical `FULL_SALE_VOID` evidence;
3. evidence mode is exactly `FULL_CASH_REFUND`;
4. tender classification is exactly `CASH`;
5. `refunded_atomic == sale.applied_atomic == void.reversed_atomic`;
6. currency and scale exactly match the sale and target shift money basis;
7. tenant, organization, and outlet ownership are exact;
8. the refund occurrence is within the selected cutoff boundary.

A refund outside the cutoff does not silently rewrite the historical expected-cash result.

## Deterministic observation cutoff

The canonical closing-cash observation supplies the cutoff.

The exact cutoff is its immutable server-owned `recorded_at` time together with the closing evidence identity.

Eligibility uses a half-open temporal boundary:

`shift opening evidence time <= eligible event occurrence time <= closing cash recorded_at`

The closing boundary is inclusive so a canonical eligible event whose immutable server occurrence time equals the closing observation time is not discarded by an arbitrary exclusive comparison.

Timestamp equality alone must not be used to manufacture ordering between two events. If canonical evidence with the same timestamp creates an ordering ambiguity that changes arithmetic eligibility, derivation fails closed unless a separately canonical deterministic ordering key resolves it.

The cutoff is never `now()` and is never caller supplied.

## Late-event semantics

Any otherwise eligible sale/refund evidence with immutable occurrence time after the canonical closing-cash cutoff is a late event for that historical observation.

Late evidence is excluded from that historical arithmetic result, but its existence must not be silently ignored when the system is asked to treat the closing observation as reconciliation-ready.

The derivation must surface a fail-closed late-event condition whenever post-cutoff evidence belongs to the exact shift/evidence relationship and would alter expected cash.

Sprint55 does not authorize mutation of the historical closing observation, moving the cutoff, reopening a shift, or automatically recalculating a previously accepted reconciliation state.

A later policy for late-event remediation requires separate authority.

## Money compatibility

All arithmetic uses integer atomic amounts only.

Every contributing amount must have exactly the same canonical currency and currency scale as the opening-cash and closing-cash observations.

No floating point, conversion, coercion, implicit rounding, locale inference, or hidden currency default is allowed.

Unknown or mismatched currency/scale fails closed.

Integer overflow or an unsupported arithmetic range fails closed.

Expected cash may not become negative under the currently selected evidence model. A negative result indicates missing/unsupported cash-movement semantics or inconsistent evidence and fails closed rather than being normalized to zero.

## Replay, duplicates, and evidence identity

Expected cash is a pure derivation over canonical durable evidence identities at the frozen cutoff.

- exact operation replays return existing evidence and do not add arithmetic contributions;
- audit `REPLAYED` events contribute zero;
- database uniqueness remains authoritative for one void/refund/opening/closing evidence relationship;
- duplicate or conflicting durable evidence that violates canonical uniqueness or relationship invariants fails closed;
- no caller-provided aggregate or evidence list is accepted.

## Deterministic reproducibility

Given the same canonical target shift, opening evidence, closing evidence/cutoff, and same eligible immutable evidence set, the result must be byte-for-byte reproducible as the same atomic amount, currency, and scale.

A historical replay must not depend on:

- current active-shift state;
- current wall-clock time;
- current catalog price;
- current stock quantity;
- current role/grant state;
- current mutable balance;
- caller ordering;
- locale or floating-point behavior.

Any future evidence type that can move physical cash is excluded until separately selected and assigned explicit arithmetic/cutoff semantics.

## Fail-closed conditions

Derivation is not valid when any material condition is unknown or ambiguous, including:

- missing or duplicate opening/closing evidence;
- inability to prove exact historical shift membership for a candidate sale;
- cross-tenant, cross-organization, or cross-outlet evidence;
- malformed sale/void/refund relationships;
- unsupported tender category;
- currency/scale mismatch;
- timestamp/order ambiguity that changes eligibility;
- post-cutoff late evidence that would alter expected cash;
- duplicate/conflicting durable evidence;
- arithmetic overflow or negative derived result;
- any required evidence represented only by mutable current state.

Fail closed means no expected-cash value may be presented as reconciliation-authoritative.

## Explicit non-scope

Sprint55 does not select or implement:

- expected-cash persistence table;
- stored aggregate or materialized balance;
- expected-cash endpoint;
- expected-cash permission;
- runtime feature flag;
- application service/repository/controller;
- exact application source envelope;
- migration #24;
- expected-versus-observed variance;
- variance tolerance or explanation;
- reviewer approval or step-up;
- final shift close or state transition;
- close authority;
- controlled reopen;
- arbitrary cash-in/cash-out movement;
- denomination count;
- drawer administration;
- settlement or provider reconciliation;
- accounting/general ledger;
- purchasing or supplier lifecycle;
- deployment, release, updater activation;
- Technical Preview or Production activation;
- migration execution/application;
- rollback or destructive database action.

## Source-readiness gate for the next sprint

A later source/schema gate may be selected only after it proves that canonical durable evidence can satisfy the semantics above without guessing historical shift membership or mutating historical facts.

In particular, the current JRN-006 active-shift precondition is not by itself permission to assume that every historical sale can be deterministically rebound to an exact shift during replay.

If an explicit immutable sale-to-shift binding is required, its schema, migration number, source envelope, preservation rules, and compatibility impact must be selected separately. Migration #24 remains **NOT SELECTED** by Sprint55.

## JRN-010 dependency lock

JRN-010 Shift Close and Operational Reconciliation remains **NOT SELECTED**.

After Sprint55, separately bounded decisions are still required for at least:

- source/schema support for deterministic expected-cash derivation if needed;
- expected-versus-observed variance semantics;
- tolerance/explanation policy if required;
- close authority and any privileged step-up;
- one-time close concurrency and idempotency;
- reviewer policy if required;
- late-event remediation policy;
- controlled reopen policy if any;
- arbitrary cash movement policy if introduced;
- settlement/reconciliation boundary.

## Lifecycle posture

Migration #22: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #23: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #24: **NOT SELECTED**

Technical Preview: **INACTIVE**

Production: **NO-GO**

Updater: **INACTIVE**

Deployment/release: **NOT AUTHORIZED**

Migration execution/application: **NOT AUTHORIZED**

Rollback/destructive database operations: **NOT AUTHORIZED**

Attribution: **Lab | zefry**
