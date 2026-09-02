# JRN-010 Prerequisite — Immutable Sale-to-Shift Binding Entry Gate

Author by Lab | zefry

## Status

`SPRINT56 ENTRY-GATE PLANNING ONLY / NO SCHEMA SELECTED / MIGRATION #24 NOT SELECTED / NO APPLICATION SOURCE / EXPECTED-CASH IMPLEMENTATION NOT SELECTED / JRN-010 SHIFT CLOSE NOT SELECTED`

## Canonical reason for this gate

Sprint55 froze expected cash as a deterministic derivation from immutable opening cash, eligible completed CASH sale evidence, eligible full CASH refund evidence, and the immutable closing-cash cutoff.

Canonical sale completion already requires a server-derived active shift before a new durable sale is inserted. However, the durable sale row currently records tenant, actor, organization, outlet, device, money, tender, correlation, and completion time without persisting the exact active `shift_id` that authorized that completion.

Therefore historical expected-cash replay cannot safely prove exact sale-to-shift membership from the durable sale row alone.

Outlet + device + completion timestamp is not an authorized substitute for immutable shift identity.

Sprint56 selects only the semantic/schema-readiness requirement needed to close that gap. It does not select the schema shape or implementation yet.

## Required invariant

Every future canonical durable completed sale that can participate in shift reconciliation must carry an immutable server-derived binding to the exact canonical shift that authorized completion.

The binding must satisfy all of the following:

1. `shift_id` originates only from the exact active shift resolved and locked by the server during sale completion;
2. caller input cannot provide, override, or select `shift_id`;
3. tenant, organization, outlet, and device ownership of the shift must match the verified execution context and sale evidence;
4. the binding is written atomically with first durable sale completion;
5. replay of the same canonical sale operation returns the existing binding and never rebinds the sale to a current or later shift;
6. the binding is immutable after first completion;
7. void/refund/reconciliation logic may consume the sale's immutable binding but may not rewrite it;
8. unknown, missing, conflicting, cross-tenant, cross-outlet, or cross-device binding fails closed for reconciliation-authoritative use.

## Historical evidence rule

Sprint56 does not authorize guessing or backfilling historical `shift_id` from mutable/current state.

For any pre-binding durable sale, historical membership may be accepted only if a later separately canonical rule can prove one exact shift from immutable evidence without ambiguity.

The following are insufficient by themselves:

- outlet plus completion timestamp;
- device plus completion timestamp;
- whichever shift is currently active;
- nearest opening/closing observation;
- caller-provided shift identity;
- mutable audit presentation state.

If exact historical proof is unavailable, that sale remains ineligible for reconciliation-authoritative expected-cash derivation and the derivation fails closed rather than silently excluding or assigning it.

Any migration/backfill policy for existing durable sales requires separate explicit authority and is not selected here.

## Atomicity and concurrency

The sale-to-shift binding must be captured inside the same authoritative sale-completion transaction that verifies and locks the active shift and persists the first canonical sale evidence.

A shift change racing with sale completion must not allow one sale to be committed under an ambiguous or different shift identity.

The later implementation gate must demonstrate transaction/locking semantics that preserve this invariant without widening lifecycle authority.

## Replay semantics

Operation replay must never resolve the current active shift to determine historical membership.

For an existing sale operation:

- its immutable stored binding is authoritative;
- same operation + same canonical facts returns the same binding;
- conflicting operation reuse fails closed;
- a missing binding cannot be repaired implicitly during replay;
- replay/audit evidence does not create a new shift relationship.

## Relationship propagation

Canonical full-sale void and full CASH refund evidence remain relationships to the original completed sale.

For expected-cash eligibility, their shift membership is derived through the original sale's immutable shift binding plus their own canonical tenant/organization/outlet relationship and cutoff semantics.

Sprint56 does not select duplicate `shift_id` columns on void/refund evidence and does not forbid them either; that exact schema choice remains for a later bounded schema/source-envelope gate.

No void/refund event may move a completed sale to another shift.

## Money and cutoff preservation

This gate does not change Sprint55 money or cutoff semantics.

- arithmetic remains integer atomic amounts;
- currency and scale must match exactly;
- closing-cash immutable server time remains the expected-cash cutoff;
- late-event handling remains fail closed when post-cutoff evidence would alter reconciliation;
- full-sale void contributes no independent cash subtraction;
- eligible full CASH refund subtracts exactly once.

## Minimal later schema requirements

A later schema/source-envelope gate, if selected, must prove at minimum:

- exact immutable sale-to-shift field/relationship shape;
- tenant-safe and shift-safe referential/uniqueness constraints appropriate to the existing canonical schema;
- no caller-controlled shift field at delivery/application boundaries;
- atomic capture from the already locked active shift during first sale completion;
- deterministic hydration/replay preservation;
- regression coverage for cross-shift, replay, race, missing-binding, and tenant/outlet/device mismatch cases;
- preservation of existing JRN-006/JRN-007 behavior;
- explicit treatment of historical rows without the binding;
- exact migration/source envelope and compatibility fingerprint if a migration is selected.

This entry gate does **not** select migration #24. The number remains reserved only by sequence, not authorized by chronology.

## Explicit non-scope

Sprint56 does not select or implement:

- migration #24;
- any database column, table, index, foreign key, or backfill;
- sale source modification;
- void/refund source modification;
- expected-cash application service or repository;
- expected-cash persistence or stored aggregate;
- endpoint, permission, route, controller, or runtime feature flag;
- caller-provided shift identity;
- historical heuristic shift assignment;
- variance calculation;
- variance tolerance/explanation;
- final shift close or shift-state transition;
- close authority, reviewer policy, controlled reopen, or settlement;
- arbitrary cash movement or denomination count;
- deployment, release, updater activation;
- Technical Preview or Production activation;
- migration execution/application;
- rollback or destructive database operation.

## Next-gate rule

After this entry gate is canonical, a fresh bounded reconciliation must determine whether the smallest safe next slice is a schema/source-envelope gate for immutable sale-to-shift binding.

Only that later gate may explicitly select migration #24 and an exact source envelope, and only if the required constraints can be frozen without broadening into expected-cash implementation.

Expected-cash source implementation remains unselected until exact historical/current shift membership can be proven from canonical durable evidence.

## JRN-010 dependency lock

JRN-010 Shift Close and Operational Reconciliation remains **NOT SELECTED**.

Still separately required are deterministic expected-cash source readiness, expected-versus-observed variance semantics, any tolerance/explanation policy, close authority, one-time close concurrency/idempotency, late-event remediation, reviewer/reopen policy if required, and settlement/reconciliation boundaries.

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
