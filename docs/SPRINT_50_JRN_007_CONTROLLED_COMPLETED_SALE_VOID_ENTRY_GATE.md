# Sprint50 JRN-007 Controlled Completed-Sale Void Entry Gate

Author by Lab | zefry

## 1. Gate classification

`BUSINESS MVP SUCCESSOR ENTRY GATE ONLY / SOURCE NOT AUTHORIZED / SCHEMA DECISION DEFERRED`

This bounded Sprint50 entry gate starts from canonical main
`3cd0131ca6dfbd3f87e5e2ceecd10f5d2b0b8131`, tree
`8b5ffd4e0d1ecae54b2415b3ec6d217ca5956728`.

The gate records one successor concern only. It does not activate any runtime,
migration, Technical Preview, Production, updater, deployment, release, or
rollback lifecycle.

## 2. Canonical predecessor evidence

The current Business MVP POS sequence already has these source-published
foundations:

- JRN-004 tenant/outlet catalog sellability and current-price preparation;
- JRN-005 accountable shift/register opening for the exact verified
  tenant/outlet/device-backed register execution context;
- JRN-006 durable sale completion, bounded payment recording, receipt evidence,
  stock decrement, and deterministic idempotency;
- Sprint49 integration requiring an exact current active shift before a fresh
  JRN-006 sale completion.

The current journey inventory classifies both JRN-007 and JRN-010 as critical
P0 journeys, but explicitly states that MVP order must follow value, frequency,
dependency, capacity, and evidence rather than numbering alone.

JRN-010 shift close/reconciliation depends on an active shift plus the available
transaction/payment-event set and includes sale/refund, cash movement, variance,
and settlement evidence. Those correction and reconciliation inputs are not yet
fully canonical.

JRN-007 directly consumes an already-completed sale and is therefore the smaller
coherent successor dependency before a full JRN-010 close/reconciliation path.

## 3. Selected Sprint50 concern

Sprint50 selects exactly one bounded JRN-007 semantic:

**JRN-007 — Controlled Full Completed-Sale Void Foundation**

This selection is intentionally narrower than the full JRN-007 journey.

It is limited to a future operation that can mark one already-completed sale as
fully void through an auditable, fail-closed, exactly-once correction boundary.

This entry gate does not combine void, cancellation, return, and refund
semantics.

## 4. Business objective

The bounded objective is to create a governable correction path for the case
where an already-completed sale must be invalidated in full without losing the
original durable sale, line, payment/receipt, stock, actor, context, or event
history.

A future source stage may compensate the original sale effects only if the next
schema/source-envelope gate proves the required state and evidence model.

The original completed sale must remain immutable historical evidence.

## 5. Why JRN-010 is not selected

JRN-010 is not selected merely because shift opening and sale completion are
already source-published.

The current journey evidence for JRN-010 requires, among other things:

- expected and actual amount;
- sale and refund evidence;
- cash movement;
- variance;
- settlement evidence;
- reviewer and final close state.

Selecting JRN-010 now would either widen Sprint50 into multiple unresolved money
semantics or create incomplete reconciliation evidence.

Sprint50 therefore selects the smaller completed-sale correction dependency
first.

## 6. Exact semantic boundary

The future bounded void operation must satisfy all of these properties:

1. the target sale already exists as a canonical completed JRN-006 sale;
2. the sale belongs to the exact current server-derived tenant boundary;
3. outlet/device/register authority is never caller-selected;
4. the original sale and sale-line snapshots are never deleted or rewritten;
5. the correction is full-sale only;
6. partial line return is not allowed;
7. partial monetary refund is not allowed;
8. external provider reversal is not allowed;
9. the operation is exactly-once under durable idempotency;
10. a second distinct correction attempting to void an already-void sale fails
    closed unless the next gate proves a stricter deterministic replay model;
11. cross-tenant target resolution always fails closed;
12. unknown or ambiguous current state fails closed.

## 7. Tenant, outlet, device, and register boundary

Authority remains server owned.

The future operation must derive current execution authority from the canonical
first-party verified session/context boundary.

The caller must not be able to provide or override:

- tenant identity;
- organization identity;
- outlet authority;
- device authority;
- register authority;
- shift authority;
- actor identity;
- role;
- permission;
- session state;
- active-state assertion;
- original sale monetary values;
- original sale line quantities;
- stock-restoration quantities;
- payment category;
- refund amount;
- settlement state.

The original sale reference must resolve inside the exact tenant boundary before
any correction decision is made.

The next gate must decide whether outlet/device matching is a mandatory target
eligibility rule for void execution or whether tenant-scoped authorized
management correction is required. No widening is implied here.

## 8. Authorization posture

The operation is deny-by-default and requires separately governed POS correction
authority.

This entry gate does not grant any role permission and does not reuse
`pos.sale.complete` as implicit void authority.

The next schema/source-envelope gate must inspect the canonical authorization
model and freeze the exact permission identifier before source implementation.

Any future permission remains ungranted by default.

Step-up, separation-of-duties, approval threshold, or manager-review policy must
not be invented by source. If one is required for this bounded foundation, the
next gate must freeze it from canonical security primitives and business
evidence.

## 9. Caller-input boundary

The future delivery boundary may expose only the minimum business identifiers
needed for an explicit correction request.

The currently frozen caller-input categories are:

- a stable idempotency operation identifier;
- the original canonical sale identifier returned by JRN-006;
- one bounded reason identifier or reason code only if the next gate proves an
  authoritative allow-list and durable audit requirement.

The caller must not submit:

- tenant, organization, outlet, device, register, shift, actor, role,
  permission, or session authority;
- monetary totals;
- refund amount;
- tender category;
- stock quantities;
- line prices;
- original sale timestamps;
- correction state;
- provider result;
- settlement evidence.

If the next gate cannot prove a safe bounded reason-code model, reason input must
remain excluded rather than accepted as arbitrary free text.

## 10. Replay and idempotency posture

The future void operation must use a durable idempotency boundary at least as
strict as:

`tenant_id + operation_id`

Exact replay of the same already-applied void request must return the original
durable correction evidence without duplicating:

- correction records;
- stock compensation;
- financial correction evidence;
- sale events;
- audit evidence.

Conflicting reuse of the same operation identifier must fail closed.

A different operation identifier targeting an already-void sale must not create
a second correction.

No stale exact-head workflow or historical PR evidence may qualify a changed
head.

## 11. Original sale immutability

The completed JRN-006 evidence remains historical truth.

A future void implementation must not delete or rewrite:

- `oneqay_pos_sales` identity and original completion evidence;
- original sale line quantity or price snapshots;
- original payment/receipt evidence;
- original completion correlation identity;
- original completion timestamp;
- original completed-sale events.

A void must be represented as additional durable correction evidence or another
separately proven integrity-preserving representation.

## 12. Stock and money posture

This entry gate does not authorize an implementation model for stock or money
compensation.

The next schema/source-envelope gate must inspect the exact canonical JRN-006
schema and transaction boundaries and decide whether a full void foundation can
safely:

- restore the exact original sold quantities;
- record bounded financial correction evidence for the recorded tender category;
- preserve deterministic totals and currency scale;
- maintain transactional all-or-nothing behavior.

No caller-selected quantity or amount is permitted.

External payment-provider reversal remains excluded.

## 13. Active-shift relationship

Sprint49 active-shift sale-completion enforcement remains unchanged.

This entry gate does not decide that a void requires:

- the original shift to still be active;
- the current device to own the original sale;
- a newly opened shift;
- a JRN-010 close/reconciliation state.

The next gate must inspect exact canonical context and decide the smallest
fail-closed execution rule without rewriting JRN-005 opening evidence.

A void must never create, close, reopen, reassign, or otherwise mutate a shift as
a side effect.

## 14. Schema decision

**SCHEMA DECISION DEFERRED**

Migration #19 is not selected by this entry gate.

The next separately bounded schema/source-envelope gate must inspect exact
canonical source and choose one of:

- `NO_SCHEMA_CHANGE`, only if durable exactly-once void state, immutable
  correction evidence, and compensation integrity can be proven using existing
  schema without semantic ambiguity; or
- one exact bounded migration #19 proposal if new durable correction state,
  journal, uniqueness, or financial/stock evidence is required.

No migration may be created, executed, applied, activated, or rolled back by
this entry gate.

## 15. Explicit non-scope

Sprint50 entry-gate scope excludes:

- pre-completion cart cancellation;
- partial void;
- partial return;
- item-condition processing;
- exchange;
- refund;
- external payment-provider reversal;
- provider callback;
- chargeback;
- settlement import;
- cash drawer movement;
- opening cash;
- closing cash;
- cash count;
- cash variance;
- JRN-010 shift close;
- payment reconciliation;
- shift reopen;
- shift transfer/handoff;
- broad register administration;
- tax/fiscal adjustment policy;
- inventory adjustment administration;
- purchasing;
- supplier lifecycle;
- accounting general ledger posting;
- Technical Preview activation;
- Production activation;
- updater activation;
- deployment;
- release publication;
- migration execution/application;
- rollback execution.

## 16. Required evidence for the next gate

The next bounded schema/source-envelope gate must inspect at least:

- canonical JRN-006 sale application boundary;
- durable JRN-006 repository interface and Laravel adapter;
- canonical completed-sale identifier and replay behavior;
- migration #16 sale, line, and event tables;
- Sprint49 active-shift ordering;
- current POS authorization identifiers;
- catalog quantity mutation behavior;
- transaction boundary;
- Local/Test/CI feature gating;
- dedicated JRN-006 and Sprint49 regressions.

The next gate must freeze:

- exact permission;
- exact caller payload;
- exact target-sale eligibility;
- exact replay behavior;
- exact stock compensation behavior;
- exact financial correction evidence;
- exact changed-file envelope;
- exact schema decision;
- exact regression properties.

## 17. Required regression properties for future source

Before any future source publication, exact-head regression evidence must prove
at least:

- target completed sale is tenant scoped;
- cross-tenant sale target is denied;
- missing sale is denied;
- original completed sale evidence remains unchanged;
- exact replay creates no duplicate correction;
- conflicting operation reuse fails closed;
- already-void sale cannot be corrected twice by different operations;
- caller cannot inject authority, money, quantity, or original-state values;
- correction transaction is all-or-nothing;
- JRN-006 sale completion replay remains unchanged;
- Sprint49 fresh-sale active-shift precondition remains unchanged;
- JRN-005 opening evidence remains unchanged;
- JRN-004 catalog preparation remains independently governed;
- runtime remains Local/Test/CI only unless separately authorized;
- lifecycle locks remain intact.

## 18. Lifecycle locks

Current canonical migration horizon remains exactly migrations #1 through #18.

- Migration #16: SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED.
- Migration #17: SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED.
- Migration #18: SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED.
- Migration #19: NOT SELECTED.
- Technical Preview: NOT ACTIVATED.
- Production: NO-GO / NOT AUTHORIZED.
- Updater: NOT ACTIVATED.
- Deployment: NOT AUTHORIZED.
- Release: NOT AUTHORIZED.
- Migration execution/application: NOT AUTHORIZED.
- Rollback: NOT AUTHORIZED.

Source publication must never be interpreted as lifecycle activation.

## 19. Exact entry-gate envelope

This entry gate changes exactly one path:

`docs/SPRINT_50_JRN_007_CONTROLLED_COMPLETED_SALE_VOID_ENTRY_GATE.md`

No application source, workflow, migration, configuration, deployment, release,
or runtime file belongs to this entry-gate envelope.

Unknown changed-file shapes must fail closed.

## 20. Required next bounded gate

If this entry gate is published, the next logical Sprint50 task is:

**Sprint50 JRN-007 Controlled Completed-Sale Void Schema & Source Envelope Gate**

That gate must start from the exact then-current canonical main and may not reuse
stale source qualification evidence.

## 21. Entry-gate decision

Sprint50 formally selects:

**JRN-007 — Controlled Full Completed-Sale Void Foundation**

The concern is bounded to one auditable, exactly-once, fail-closed full-void
correction path over a canonical completed JRN-006 sale while preserving
immutable original evidence and all current tenant/context/lifecycle controls.

No source implementation, schema change, migration #19, Technical Preview,
Production, updater, deployment, release, migration execution, or rollback
authority is created by this document.

Attribution: Lab | zefry
