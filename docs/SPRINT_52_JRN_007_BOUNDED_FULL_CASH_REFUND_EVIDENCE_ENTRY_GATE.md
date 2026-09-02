# Sprint52 JRN-007 Bounded Full CASH Refund Evidence Entry Gate

Author by Lab | zefry

## 1. Gate classification

`BUSINESS MVP CORRECTION ENTRY GATE ONLY / SOURCE NOT AUTHORIZED / SCHEMA DECISION DEFERRED / LIFECYCLE NOT AUTHORIZED`

This bounded Sprint52 entry gate starts only from canonical main:

`ec9badb831385f4726d2be0513436d0150cc881e`

It selects one minimum continuation of the approved JRN-007 correction journey. It does not authorize application source, schema, migration #21, migration execution/application, Technical Preview activation, Production, updater activation, deployment, release, rollback, or destructive database operations.

## 2. Canonical predecessor evidence

The canonical POS path already source-publishes:

- JRN-004 tenant/outlet catalog preparation;
- JRN-005 shift/register opening;
- JRN-006 active-shift sale completion with durable payment and receipt evidence;
- JRN-007 one controlled full completed-sale void with exact stock restoration;
- JRN-008 one-time outlet inventory baseline.

Sprint50 JRN-007 deliberately stops at `FULL_SALE_VOID`. It preserves the completed sale, derives stock restoration only from immutable sale lines, derives the reversal amount from the original sale `applied_atomic`, and explicitly does not implement refund semantics or cash movement.

DEC-001 includes controlled cancellation/void/return/refund in the approved POS MVP correction journey.

DEC-007 keeps refund distinct from void/reversal and requires refund/reversal operations to remain authorization-controlled, idempotent, auditable, and bound to original eligible payment evidence and tenant/context.

Therefore the next bounded correction concern is not another stock mutation. It is one auditable CASH refund evidence fact for an already-voided eligible CASH sale.

## 3. Selected Sprint52 concern

Sprint52 selects exactly:

**JRN-007 continuation — Bounded Full CASH Refund Evidence Foundation**

The future operation may record one full CASH refund evidence fact for one canonical sale only when the exact sale has already been successfully corrected by the canonical JRN-007 full-sale void foundation.

This is an operational evidence record. It does not claim that oneQay physically moved bank/provider funds, controlled a cash drawer, or proved a cash denomination/count event.

## 4. Business objective

The minimum objective is to close the currently missing CASH correction evidence between:

1. immutable completed-sale/payment evidence;
2. successful full-sale void and stock restoration; and
3. one operator-authorized record that the exact applied CASH amount is refunded.

The future operation must not infer or accept a different refund amount.

## 5. Exact target eligibility

A fresh future refund operation must fail closed unless all minimum eligibility conditions are proven transactionally:

- the target canonical sale exists in the exact server-derived tenant;
- the sale belongs to the exact current server-derived organization and outlet;
- the sale tender category is exactly `CASH`;
- the exact canonical JRN-007 void evidence exists for that sale;
- the void evidence is internally consistent with the original sale;
- no prior durable CASH refund evidence exists for that sale;
- state is otherwise unambiguous.

A sale that has not been voided is not eligible.

A `MANUAL_EXTERNAL` sale is not eligible.

A future `PROVIDER_ELECTRONIC` sale is not eligible.

## 6. Refund amount derivation

The caller must never provide a refund amount.

The exact full refund amount must be derived from immutable canonical sale/payment evidence.

The current JRN-006 durable sale keeps:

- `total_atomic`;
- `applied_atomic`;
- `change_atomic`;
- currency;
- currency scale;
- tender category.

For CASH, customer change is not part of the sale payment obligation. The bounded refund amount therefore derives from the exact original `applied_atomic`, not from tendered cash and not from `change_atomic`.

The future gate must also prove that the JRN-007 void reversal amount is consistent with that same original applied amount before a fresh refund record is accepted.

No caller-selected rounding, fee, deduction, surcharge, restocking fee, alternate currency, or alternate amount is allowed.

## 7. Original evidence immutability

The future refund operation must not update or delete original rows representing:

- completed sale;
- sale lines;
- original payment/receipt evidence;
- JRN-007 void evidence;
- original completed/void event evidence.

The refund is additive immutable correction evidence only.

A later exact JRN-006 replay must still return the original completed-sale receipt.

A later exact JRN-007 replay must still return the original full-void evidence without another stock restoration.

## 8. Inventory separation

The JRN-007 full void already performs the exact stock restoration from immutable sale lines.

Sprint52 CASH refund evidence must never:

- increment stock;
- decrement stock;
- recalculate original sale-line quantity;
- rewrite inventory baseline evidence;
- mutate catalog price, currency, scale, sellability, or quantity;
- create a second void;
- create any stock adjustment.

Any refund implementation that performs another stock restoration is incorrect and must fail regression qualification.

## 9. CASH evidence boundary

The selected semantic is exactly full CASH refund evidence.

It does not implement:

- cash denomination counting;
- cash drawer opening/closing;
- drawer balance mutation;
- opening cash;
- cash-in/cash-out ledger;
- physical cash verification;
- manager cash handoff;
- bank deposit;
- settlement;
- payout;
- accounting posting.

The evidence means an authorized operator records the bounded refund fact in oneQay. It does not independently prove physical custody movement beyond that authorized operational record.

## 10. Tenant, organization, outlet, device, and session boundary

Authority remains server-owned.

The future operation must derive from canonical verified server context:

- tenant;
- identity / actor;
- organization;
- outlet;
- device;
- current session authority;
- correlation identity;
- event time.

The caller cannot provide or override those values.

The sale identifier is only a target selector. It is not authority.

Cross-tenant, cross-organization, and cross-outlet target resolution must fail closed.

The current correction device does not need to equal the original sale or void device, provided the current operator is independently authorized in the exact same tenant/organization/outlet boundary.

## 11. Authorization posture

The future refund operation requires a dedicated deny-by-default permission.

Candidate vocabulary:

`pos.sale.refund`

This entry gate does not add that permission to source and does not grant it to any role.

No existing `pos.sale.complete`, `pos.sale.void`, `pos.catalog.prepare`, `pos.shift.open`, or `pos.inventory.baseline` permission may implicitly grant refund authority.

The next schema/source-envelope gate must inspect the canonical authorization model and either freeze this exact identifier or reject/replace it before source implementation.

## 12. Caller-input boundary

The future delivery boundary may accept only:

- stable idempotency `operation_id`;
- exact canonical `sale_id`.

It must not accept caller-provided:

- refund amount;
- tender category;
- currency or scale;
- void id;
- tenant;
- organization;
- outlet;
- device;
- register;
- shift;
- actor;
- role;
- permission;
- session authority;
- stock quantity;
- return quantity;
- item list;
- reason text;
- fee;
- external reference;
- provider state;
- timestamp;
- correlation identity;
- arbitrary metadata.

Unknown request keys must fail closed.

## 13. Idempotency and replay

The future operation must use durable idempotency at least as strict as:

`tenant_id + operation_id`

The semantic fingerprint must bind the relevant server-derived current execution context plus the exact target sale.

Exact replay must resolve before current mutable-state validation and return the original durable refund result without:

- creating a second refund row;
- creating duplicate event evidence;
- rewriting sale/void evidence;
- changing stock;
- requiring the current correction device to equal the historical device.

Conflicting reuse of the same operation id must fail closed.

A different operation id targeting a sale that already has refund evidence must fail closed.

Database uniqueness must remain the final concurrency arbiter.

## 14. Concurrency and transaction posture

Fresh refund eligibility and durable evidence insertion must be atomic.

The next gate must select locking/uniqueness behavior that prevents two concurrent operations from both recording a full refund for the same tenant/sale.

An application-only pre-check is insufficient.

The future transaction must serialize the exact target sale/void/refund evidence or otherwise provide an equally strong database-enforced one-refund boundary.

Unknown, missing, inconsistent, duplicate, or conflicting state fails closed.

## 15. Durable evidence requirement

A successful future CASH refund requires immutable durable evidence sufficient to prove at least:

- refund identity;
- original sale identity;
- canonical full-void identity or unambiguous binding to that void;
- tenant;
- organization;
- outlet;
- current authorized actor;
- current authorized device;
- operation identity;
- semantic request fingerprint;
- exact server-derived refunded amount;
- currency and scale;
- CASH tender classification;
- bounded refund evidence mode;
- correlation identity;
- server-owned occurrence time.

The exact table representation is not selected by this entry gate.

## 16. Shift/register relationship

The selected concern is refund evidence, not shift close or drawer movement.

This entry gate does not require an active shift and does not authorize mutation of a shift/register record.

The future operation must not:

- create a shift;
- close a shift;
- reopen a shift;
- reassign a shift;
- mutate opening cash;
- synthesize shift reconciliation evidence.

JRN-010 remains separately gated.

## 17. Event evidence posture

A durable refund fact must be auditable.

Whether the source design also adds one immutable sale event such as `REFUNDED` is deferred to the next schema/source-envelope gate.

If selected later, exact replay must never duplicate that event and the event must not replace the dedicated durable refund evidence required for idempotency and uniqueness.

## 18. Schema decision

**SCHEMA DECISION DEFERRED**

Migration #21 is **NOT SELECTED**.

The next separately bounded schema/source-envelope gate must inspect exact canonical sale, void, event, transaction, and authorization source and decide between:

- `NO_SCHEMA_CHANGE`, only if immutable refund evidence, one-refund uniqueness, replay identity, concurrency safety, and auditability are all unambiguous with existing schema; or
- one exact bounded source-only migration #21 proposal if dedicated refund evidence is required.

This entry gate creates no migration and grants no migration execution, application, activation, or rollback authority.

## 19. Explicit non-scope

Sprint52 entry-gate scope excludes:

- partial refund;
- partial return;
- item-level return;
- exchange;
- credit note;
- store credit;
- gift card;
- MANUAL_EXTERNAL refund;
- provider refund/reversal;
- chargeback/dispute;
- asynchronous provider outcome;
- arbitrary refund amount;
- refund fee;
- restocking fee;
- refund reason workflow;
- stock adjustment;
- second stock restoration;
- cash drawer automation;
- cash counting;
- cash variance;
- settlement;
- payout;
- accounting/general ledger;
- purchasing;
- supplier lifecycle;
- inventory transfer;
- stocktake;
- JRN-010 shift close/reconciliation;
- external payment integration;
- offline mutation;
- reporting/BI expansion;
- Technical Preview activation;
- Production activation;
- updater activation;
- deployment;
- release;
- migration execution/application;
- rollback;
- destructive database operation.

## 20. Required evidence for the next gate

The next schema/source-envelope gate must inspect at least:

- canonical JRN-006 sale schema and durable sale/payment/receipt source;
- exact CASH `applied_atomic` and `change_atomic` semantics;
- canonical JRN-007 void schema, repository/service, and regression;
- exact JRN-007 replay-before-current-state behavior;
- exact JRN-007 stock restoration behavior;
- current sale-event schema and event insertion behavior;
- canonical persistence transaction abstraction;
- current POS permission vocabulary;
- current Local/Test/CI feature-gating conventions;
- current migration horizon #1 through #20;
- tenant/organization/outlet/device verified-context boundaries.

It must freeze:

- exact permission identifier;
- exact command/input shape;
- exact refund eligibility;
- exact amount derivation;
- exact durable replay semantics;
- exact concurrency mechanism;
- exact evidence model;
- exact event decision;
- exact schema decision;
- exact feature flag;
- exact endpoint;
- exact changed-file source envelope;
- exact regression properties.

## 21. Required future regression properties

Before any future Sprint52 refund source publication, exact-head regression evidence must prove at least:

- permission denied by default;
- eligible CASH sale + exact canonical void can receive one full refund record;
- refund amount equals original applied CASH amount exactly;
- original cash change is never included in refund amount;
- non-voided sale is denied;
- MANUAL_EXTERNAL sale is denied;
- cross-tenant target is denied;
- cross-organization target is denied;
- cross-outlet target is denied;
- exact replay returns original refund evidence;
- exact replay does not require a second stock mutation;
- conflicting operation-id reuse is denied;
- second distinct refund operation for the same sale is denied;
- concurrent double-refund safety is deterministic;
- original sale/line/payment/receipt evidence remains unchanged;
- JRN-007 void evidence remains unchanged;
- exact JRN-007 replay still does not restore stock twice;
- refund produces zero catalog/inventory mutation;
- no shift state is required or mutated;
- unknown HTTP field is rejected;
- caller cannot inject amount/tender/context/time;
- server-owned correlation/time evidence is retained;
- runtime outside Local/Test/CI fails closed;
- feature defaults false;
- lifecycle locks remain intact;
- tracked-source cleanliness remains enforced.

No fake-green or skipped material regression may qualify source publication.

## 22. Historical compatibility posture

Any later Sprint52 workflow compatibility correction must be derived from fresh exact-head failure evidence.

Allowed compatibility work must remain deterministic and bounded to exact historical predecessor/successor shapes.

Unknown shapes remain fail-closed.

Stale PR/head CI evidence cannot qualify a changed head.

## 23. JRN-010 posture

JRN-010 Shift Close and Operational Reconciliation remains:

**NOT SELECTED**

A future bounded CASH refund source publication alone does not satisfy the separate cash-count, variance, and settlement/reconciliation evidence needed before JRN-010 may be selected.

No shift-close authority is created here.

## 24. Lifecycle locks

Current canonical source migration horizon is exactly migrations #1 through #20.

- Migration #20 remains **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**.
- Migration #21: **NOT SELECTED**.
- Technical Preview: **NOT ACTIVATED** for these Business MVP source additions.
- Production: **NO-GO / NOT AUTHORIZED**.
- Updater activation: **NOT AUTHORIZED**.
- Deployment: **NOT AUTHORIZED**.
- Release: **NOT AUTHORIZED**.
- Migration execution/application: **NOT AUTHORIZED**.
- Rollback: **NOT AUTHORIZED**.
- Destructive database operations: **NOT AUTHORIZED**.

Source or documentation publication never implies lifecycle activation.

## 25. Exact entry-gate envelope

This entry gate changes exactly one path:

`docs/SPRINT_52_JRN_007_BOUNDED_FULL_CASH_REFUND_EVIDENCE_ENTRY_GATE.md`

Sorted newline-terminated path SHA-256:

`75fec412ac7999f78e54353d10c94c91e2f429a60b6baedd686212266c2fd90e`

No application source, workflow, migration, configuration, dependency, deployment, release, updater, or runtime file belongs to this entry-gate envelope.

Unknown changed-file shapes must fail closed.

## 26. Required next bounded gate

If this entry gate is published, the next logical Sprint52 task is:

**Sprint52 JRN-007 Bounded Full CASH Refund Evidence Schema & Source Envelope Gate**

That gate must start from the exact then-current canonical `main`, inspect only the bounded evidence listed above, and qualify its own exact head independently.

It must not treat this entry gate as schema, source, runtime, deployment, release, Production, or migration-execution authority.

## 27. Entry-gate decision

Sprint52 formally selects only:

**one auditable, exactly-once, fail-closed full CASH refund evidence operation for one canonical CASH sale that already has exact JRN-007 full-void evidence.**

The full refund amount is server-derived from immutable original applied payment evidence.

No second stock restoration, no partial refund, no MANUAL_EXTERNAL/provider behavior, no shift close, and no lifecycle activation are selected.

**Migration #21 remains NOT SELECTED.**

**Technical Preview remains NOT ACTIVATED.**

**Production remains NO-GO / NOT AUTHORIZED.**

Attribution: **Lab | zefry**
