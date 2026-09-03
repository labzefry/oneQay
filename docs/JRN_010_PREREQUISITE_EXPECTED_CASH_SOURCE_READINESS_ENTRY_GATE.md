# JRN-010 Prerequisite — Expected Cash Source Readiness Entry Gate

Author by Lab | zefry

## Status

`SPRINT58 ENTRY-GATE PLANNING ONLY / EXPECTED-CASH SOURCE READINESS CONFIRMED / NO NEW SCHEMA / MIGRATION #25 NOT SELECTED / JRN-010 SHIFT CLOSE NOT SELECTED`

This Sprint58 entry gate reconciles the now-canonical Sprint57 immutable sale-to-shift binding against the deterministic expected-cash semantics frozen by Sprint55.

It confirms only that a bounded read-only expected-cash derivation can be implemented from current canonical durable evidence without inventing historical shift membership and without adding persistence.

It does not publish application implementation, exact source-envelope paths, an endpoint, permission, runtime feature flag, stored aggregate, migration #25, variance, final close, deployment, release, updater activation, Technical Preview activation, Production activation, migration execution, rollback, or destructive database action.

## Canonical readiness evidence

Canonical durable evidence now provides the minimum identities required by Sprint55:

- opening-cash evidence is unique per tenant and shift and stores exact shift, organization, outlet, device, atomic amount, currency, scale, correlation identity, and immutable `recorded_at_unix`;
- closing-cash evidence is unique per tenant and shift, references the exact opening-cash evidence, and stores exact shift, organization, outlet, device, atomic amount, currency, scale, correlation identity, and immutable `recorded_at_unix`;
- completed durable sales now persist a server-derived immutable `shift_id` selected from the exact active tenant / organization / outlet / device shift at first completion;
- operation replay returns existing sale evidence before resolving a current shift and therefore cannot rebind a sale;
- historical sales with `shift_id = NULL` remain explicitly unknown and are not repaired by replay;
- full CASH refund evidence references the exact sale and void evidence and stores immutable refund amount, currency, scale, tenant, organization, outlet, device, correlation identity, and `refunded_at_unix`;
- canonical full-sale void evidence remains a relationship/correction marker and not an independent cash subtraction.

The historical-membership blocker identified by Sprint55 is therefore closed for newly bound canonical sales. Historical NULL sale bindings remain fail-closed for reconciliation-authoritative derivation.

## Selected implementation posture

The next bounded expected-cash implementation may be selected as a **read-only stateless derivation** over current canonical tables.

No expected-cash persistence table, materialized balance, mutable drawer balance, or migration #25 is required for the first bounded implementation.

The derivation remains:

`opening cash + eligible completed CASH sale applied amounts - eligible full CASH refund amounts`

The implementation must derive the target exclusively from canonical closing-cash evidence and its linked opening-cash evidence. Caller-supplied shift identity, cutoff, evidence list, expected amount, currency, or scale is forbidden.

## Exact sale membership rule

A sale may contribute only when its durable `shift_id` is non-null and exactly equals the target canonical shift.

The same sale must also exactly match the target tenant, organization, and outlet. Device context must remain consistent with the canonical bound shift context.

A historical sale with missing, malformed, conflicting, or cross-context shift binding fails closed. No outlet-plus-time inference, nearest-shift inference, current-active-shift inference, or backfill is authorized.

## Refund membership rule

Refund evidence does not require a separate shift column for this bounded derivation because its membership is inherited only through its exact canonical sale relationship.

A refund may subtract only when:

- its sale has an exact valid target-shift binding;
- its exact canonical void relationship exists;
- tender category is `CASH`;
- evidence mode is `FULL_CASH_REFUND`;
- `refunded_atomic == sale.applied_atomic == void.reversed_atomic`;
- tenant, organization, and outlet relationships are exact;
- currency and scale exactly match the target money basis;
- its immutable occurrence time satisfies the cutoff rules below.

Any broken or ambiguous relationship fails closed.

## Cutoff and same-timestamp rule

The immutable closing-cash `recorded_at_unix` remains the canonical cutoff.

Events strictly before the closing timestamp may be evaluated normally after all identity and money checks pass.

Because current durable occurrence timestamps are second-resolution and no separately canonical total-order key exists between a sale/refund event and the closing observation at the same timestamp, an otherwise arithmetic-changing sale or refund whose occurrence timestamp equals the closing-cash timestamp is **ordering-ambiguous and fails closed**.

Sprint58 does not manufacture ordering from row identity, insertion order, database physical order, operation id, lexical id order, or query return order.

A later deterministic ordering key may be selected only by a separate bounded decision.

## Late-event rule

An otherwise eligible target-shift CASH sale or full CASH refund with immutable occurrence time after the closing-cash cutoff is late evidence for that observation.

Such evidence is excluded from the historical arithmetic window, but if it would alter expected cash its existence makes the observation not reconciliation-ready and the derivation must fail closed rather than silently present an authoritative expected amount.

Sprint58 does not authorize moving the cutoff, rewriting the closing observation, automatically reopening a shift, deleting late evidence, or accepting a stale aggregate.

## Stable evidence snapshot

Expected-cash derivation must operate over one stable database evidence snapshot.

The source implementation must not combine opening, closing, sale, void, and refund rows read from materially different concurrent states.

The later exact source-envelope gate must prove a bounded transactional/snapshot strategy appropriate to the supported database runtime. If a stable evidence set cannot be guaranteed, derivation fails closed.

No lifecycle activation or long-lived mutable balance is selected as a workaround.

## Money and arithmetic lock

All arithmetic remains integer atomic only.

Opening cash, closing cash, contributing sale amounts, void relationship amounts, and refund amounts must have exact currency and exact currency scale compatibility.

No floating point, conversion, coercion, implicit rounding, locale inference, hidden currency default, negative normalization, or overflow is permitted.

Arithmetic overflow, unsupported range, or a derived negative expected cash value fails closed.

## Deterministic result contract

A valid bounded derivation result must be reproducible from the same canonical evidence snapshot as the same:

- tenant identity;
- organization identity;
- outlet identity;
- shift identity;
- opening evidence identity;
- closing evidence identity;
- cutoff timestamp;
- expected atomic amount;
- currency;
- currency scale.

The result is derived evidence, not an independently mutable financial record.

Replay or repeated read of unchanged canonical evidence must not create rows or alter source evidence.

## Fail-closed conditions

No reconciliation-authoritative expected cash may be returned when any material condition is unknown or ambiguous, including:

- missing or duplicate opening/closing evidence;
- missing historical sale shift binding;
- cross-tenant, cross-organization, cross-outlet, or incompatible device/shift evidence;
- malformed sale/void/refund relationship;
- unsupported tender/evidence mode;
- currency or scale mismatch;
- same-timestamp ordering ambiguity affecting arithmetic;
- post-cutoff late evidence that would alter expected cash;
- unstable/mixed database evidence snapshot;
- duplicate/conflicting evidence outside canonical uniqueness expectations;
- arithmetic overflow or negative derived expected cash.

## Next bounded source-envelope gate

Sprint58 confirms source readiness but does not freeze exact application paths.

The next bounded gate may select only the minimum source envelope required for a read-only expected-cash derivation service/result plus infrastructure query adapter and dedicated regression/workflow evidence.

That gate must preserve:

- current migrations #1–#24 unchanged;
- no migration #25 unless separately justified and selected;
- no expected-cash persistence;
- no endpoint or UI unless separately selected;
- no caller-controlled evidence/cutoff/shift authority;
- no variance or final shift state transition;
- unknown shapes and unknown evidence fail closed;
- historical compatibility regression horizons.

## Explicit non-scope

Sprint58 does not select or implement:

- exact expected-cash application source paths;
- expected-cash persistence/schema;
- migration #25;
- endpoint/controller/route;
- permission or default grant;
- runtime feature flag;
- expected-versus-observed variance;
- variance tolerance/explanation;
- close approval or privileged step-up;
- final shift close/state transition;
- one-time close idempotency/concurrency semantics;
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

After Sprint58, separately bounded decisions are still required for at least:

- exact expected-cash source envelope and implementation;
- expected-versus-observed variance semantics;
- tolerance/explanation policy if required;
- close authority and any privileged step-up;
- one-time close concurrency/idempotency;
- reviewer policy if required;
- late-event remediation policy;
- controlled reopen policy if any;
- arbitrary cash movement policy if introduced;
- settlement/reconciliation boundary.

## Lifecycle posture

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

Attribution: **Lab | zefry**
