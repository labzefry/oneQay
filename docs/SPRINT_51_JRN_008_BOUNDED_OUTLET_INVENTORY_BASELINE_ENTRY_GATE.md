# Sprint51 JRN-008 Bounded Outlet Inventory Baseline Entry Gate

Author by Lab | zefry

## 1. Gate classification

`BUSINESS MVP DEPENDENCY ENTRY GATE ONLY / SOURCE NOT AUTHORIZED / SCHEMA DECISION DEFERRED`

This bounded Sprint51 entry gate starts only from canonical main
`a03d6e3833bff48ffb6ad77012848da421e10023`.

It selects one minimum JRN-008 concern only. It does not authorize application
source, schema, migration #20, migration execution/application, Technical
Preview activation, Production, updater activation, deployment, release, or
rollback.

## 2. Canonical predecessor evidence

The canonical Business MVP POS path already source-publishes:

- JRN-004 tenant/outlet catalog preparation for display name, current price,
  currency/scale, and sellability;
- JRN-005 accountable shift/register opening;
- JRN-006 active-shift sale completion with immutable sale-line snapshots,
  bounded payment/receipt evidence, and stock decrement;
- JRN-007 controlled full completed-sale void with exact stock restoration from
  immutable original sale lines.

DEC-001 separately identifies **JRN-008 — Bounded Outlet Inventory Baseline**
as an MVP dependency.

The current durable catalog row already contains
`available_quantity`. JRN-004 deliberately never accepts caller-controlled
stock quantity: an existing row preserves its quantity and a newly prepared row
receives server-owned zero. JRN-006 decrements that quantity only for a
successful sale. JRN-007 restores only the exact original sold quantity.

Therefore the missing bounded concern is not generic inventory administration.
It is the controlled establishment of an opening inventory quantity before
ordinary sale/void movement.

## 3. Selected Sprint51 concern

Sprint51 selects exactly:

**JRN-008 — Tenant/Outlet/Product Opening Inventory Baseline Foundation**

The future concern may establish one opening quantity for one already-prepared
catalog product in the exact current outlet.

It is intentionally one-time baseline establishment, not an ongoing stock
adjustment feature.

## 4. Business objective

The minimum objective is to make the existing server-owned
`available_quantity` usable through a governed business path without allowing
arbitrary inventory rewriting.

A future bounded operation may:

1. resolve one existing catalog product inside the exact server-derived
   tenant/outlet context;
2. prove the product is still eligible for first baseline establishment;
3. record one non-negative integer opening quantity;
4. persist durable immutable baseline evidence;
5. update only the current `available_quantity`;
6. return deterministic durable result evidence;
7. make exact retry safe;
8. reject every attempt to use the baseline operation as later stock
   adjustment.

## 5. Exact baseline eligibility

A future baseline operation must fail closed unless all minimum eligibility
conditions are proven transactionally.

The intended bounded eligibility is:

- the exact tenant/outlet/product catalog row already exists;
- current `available_quantity` is exactly zero;
- no earlier durable inventory-baseline evidence exists for that exact
  tenant/outlet/product;
- no canonical completed-sale line history exists for that product in that
  outlet;
- state is otherwise unambiguous.

A zero opening quantity is a valid baseline value. Durable baseline evidence,
not a non-zero quantity, distinguishes an established zero baseline from a
never-baselined product.

This prevents a product sold down to zero from being re-baselined as though it
were new.

## 6. Single-product boundary

One operation addresses exactly one product.

This entry gate does not select:

- bulk inventory import;
- spreadsheet upload;
- batch adjustment;
- multi-product atomic stocktake;
- warehouse initialization;
- cross-outlet baseline copy.

Repeated independently idempotent single-product operations may later be used
by a higher-level process only if separately governed.

## 7. Quantity boundary

The only future caller-owned business value beyond stable identifiers may be
one opening quantity.

The quantity must be:

- an integer;
- non-negative;
- representable safely by the canonical application integer boundary;
- compatible with the existing unsigned durable quantity representation.

Negative quantity, decimal/fractional quantity, unit conversion, alternate unit
of measure, reserved quantity, damaged quantity, in-transit quantity, and
back-order quantity are excluded.

No implicit negative-stock policy is created.

## 8. Catalog-state separation

JRN-008 must not become catalog administration.

The future baseline operation must never change:

- display name;
- unit price;
- currency;
- currency scale;
- sellability / `active` state;
- product identity;
- tenant/outlet ownership.

Baseline establishment may be allowed for a currently non-sellable prepared
catalog row because inventory existence and sellability are separate concerns.

JRN-004 remains the sole currently governed catalog-preparation path and must
continue preserving `available_quantity`.

## 9. Sale and void separation

JRN-006 remains the only currently governed fresh-sale stock decrement path.

JRN-007 remains the only currently governed completed-sale stock restoration
path.

JRN-008 must not:

- decrement stock for a sale;
- restore stock for a void;
- recalculate historical sale lines;
- rewrite sale/void evidence;
- alter receipt/payment evidence;
- create sale events.

After a baseline is established, subsequent quantity movement remains governed
by the independently published sale/void contracts until later inventory
journeys are separately selected.

## 10. Tenant, outlet, device, and session boundary

Authority remains server-owned.

The future operation must derive from canonical verified session/context:

- tenant;
- identity / actor;
- organization;
- outlet;
- device;
- session authority;
- correlation identity;
- event time.

The caller cannot provide or override any of those authority values.

A product identifier is only a target selector. It is not authority and must
resolve inside the exact current tenant/outlet boundary.

Cross-tenant and cross-outlet target resolution always fails closed.

## 11. Authorization posture

The intended permission meaning is a separately governed inventory-baseline
authority, with candidate vocabulary:

`pos.inventory.baseline`

This entry gate does not add that permission to source and does not grant it to
any role.

No default grant is authorized.

The next schema/source-envelope gate must inspect the canonical authorization
model and either freeze this exact identifier or reject/replace it before any
source implementation.

Role mapping, manager approval, separation-of-duties, and step-up policy are not
invented by this gate.

## 12. Caller-input boundary

The future delivery boundary may accept only:

- stable idempotency `operation_id`;
- exact product identifier;
- non-negative opening quantity.

It must not accept caller-provided:

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
- current stock value;
- before-state override;
- price/currency;
- sellability;
- adjustment reason;
- free-text reason;
- sale/void identifiers;
- timestamp;
- correlation identity.

No arbitrary metadata bag is selected.

## 13. Idempotency and replay

The future operation must use durable idempotency at least as strict as:

`tenant_id + operation_id`

The semantic fingerprint must bind the relevant server-derived execution
context plus product and opening quantity.

Exact replay must return the original durable baseline result without:

- rewriting current quantity;
- creating a second baseline record;
- duplicating audit/event evidence.

Conflicting reuse of the same operation id must fail closed.

A different operation id targeting a product whose baseline is already
established must fail closed even when the requested quantity is identical.

## 14. Concurrency and transaction posture

Baseline eligibility and application must be atomic.

The next gate must prove a design that prevents two concurrent requests from
both establishing a baseline for the same tenant/outlet/product.

An application-only pre-check is insufficient if two writers can pass it
simultaneously.

The future transaction must lock or otherwise serialize the relevant durable
state and must either apply all baseline evidence plus quantity state or apply
nothing.

Unknown, missing, conflicting, or overflow state fails closed.

## 15. Durable evidence requirement

A successful baseline requires immutable durable evidence sufficient to prove:

- baseline identity;
- tenant/outlet/product;
- authorized actor;
- operation identity;
- semantic request fingerprint;
- before quantity;
- after opening quantity;
- correlation identity;
- server-owned occurrence time.

The exact table/journal representation is not selected here.

The evidence must survive later JRN-004 repricing/sellability changes and later
JRN-006/JRN-007 stock movement without being rewritten.

## 16. Shift/register relationship

JRN-008 is an outlet inventory-preparation dependency, not a cashier
shift-lifecycle operation.

This entry gate does not require or mutate:

- an active shift;
- register ownership;
- opening/closing cash;
- JRN-010 close/reconciliation state.

A future source gate must not create, close, reopen, or reassign a shift as a
side effect of inventory baseline establishment.

## 17. Schema decision

**SCHEMA DECISION DEFERRED**

Migration #20 is **NOT SELECTED**.

The next separately bounded schema/source-envelope gate must inspect exact
canonical source and decide between:

- `NO_SCHEMA_CHANGE`, only if one-time baseline establishment, durable
  idempotency, immutable evidence, and concurrency safety are unambiguous with
  existing schema; or
- one exact bounded source-only migration #20 proposal if dedicated baseline
  journal/state is required.

This entry gate creates no migration and grants no migration execution,
application, activation, or rollback authority.

## 18. Explicit non-scope

Sprint51 JRN-008 entry-gate scope excludes:

- ongoing stock adjustment;
- increase/decrease correction after baseline;
- stock receiving;
- purchasing;
- supplier lifecycle;
- warehouse;
- inter-outlet or inter-warehouse transfer;
- stock reservation;
- damaged/expired stock;
- shrinkage;
- stocktake;
- cycle count;
- physical-count reconciliation;
- variance posting;
- negative-stock policy expansion;
- reorder point;
- replenishment;
- costing / COGS;
- lot/batch/serial tracking;
- unit conversion;
- inventory valuation;
- broad catalog CRUD;
- repricing;
- tax/fiscal policy;
- sale modification;
- return/refund;
- external payment/provider behavior;
- JRN-010 shift close/reconciliation;
- reporting/BI expansion;
- offline inventory mutation;
- Technical Preview activation;
- Production activation;
- updater activation;
- deployment;
- release;
- migration execution/application;
- rollback.

## 19. Required evidence for the next gate

The next schema/source-envelope gate must inspect at least:

- `oneqay_pos_sale_catalog_items` current schema;
- JRN-004 catalog preparation repository/service and regression;
- server-owned zero quantity behavior for newly prepared products;
- JRN-006 sale quantity validation/decrement and replay behavior;
- JRN-007 void quantity restoration and replay behavior;
- durable tenant/outlet authorization primitives;
- exact transaction/locking primitives;
- current POS permission vocabulary;
- current migration horizon #1 through #19;
- Local/Test/CI feature-gating conventions.

It must freeze:

- exact permission identifier;
- exact command/input shape;
- exact baseline eligibility;
- exact durable replay semantics;
- exact concurrency mechanism;
- exact evidence model;
- exact schema decision;
- exact changed-file envelope;
- exact regression properties.

## 20. Required future regression properties

Before any future JRN-008 source publication, exact-head regression evidence
must prove at least:

- correct tenant/outlet/product resolution;
- cross-tenant target denial;
- cross-outlet target denial;
- missing catalog row denial;
- non-zero pre-baseline quantity denial;
- first positive baseline success;
- first zero baseline success with durable established state;
- exact replay without duplicate mutation;
- conflicting operation-id reuse denial;
- second operation against already-baselined product denial;
- prior-sale-history baseline denial;
- concurrent double-baseline safety;
- quantity overflow/invalid-input denial;
- no price/currency/sellability mutation;
- JRN-004 later preparation preserves baselined quantity;
- JRN-006 sale decrement remains unchanged;
- JRN-006 exact replay remains no-second-decrement;
- JRN-007 exact restoration remains unchanged;
- no shift mutation;
- tracked-source cleanliness;
- Local/Test/CI-only runtime posture unless later separately authorized;
- lifecycle locks remain intact.

No fake-green or skipped material runner may qualify source publication.

## 21. Historical compatibility posture

Any later Sprint51 workflow change must preserve the existing materially
applicable regressions and fail-closed unknown-envelope behavior.

Compatibility changes are allowed only when derived from exact fresh failure
evidence and must remain bounded to the affected historical workflow surfaces.

Stale PR/head evidence cannot qualify a changed head.

## 22. Lifecycle locks

Current canonical source migration horizon remains exactly migrations #1 through
#19.

- Migrations #16 through #19 remain **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**.
- Migration #20: **NOT SELECTED**.
- Technical Preview: **NOT ACTIVATED** for these Business MVP source additions.
- Production: **NO-GO / NOT AUTHORIZED**.
- Updater activation: **NOT AUTHORIZED**.
- Deployment: **NOT AUTHORIZED**.
- Release: **NOT AUTHORIZED**.
- Migration execution/application: **NOT AUTHORIZED**.
- Rollback: **NOT AUTHORIZED**.

Source or documentation publication never implies lifecycle activation.

## 23. Exact entry-gate envelope

This entry gate changes exactly one path:

`docs/SPRINT_51_JRN_008_BOUNDED_OUTLET_INVENTORY_BASELINE_ENTRY_GATE.md`

Sorted newline-terminated path SHA-256:

`eaf7f0e7ba823b84bb99b7d1e34b11189863aa0e0c4a07a8e70ee94dcd308abf`

No application source, workflow, migration, configuration, dependency,
deployment, release, or runtime file belongs to this entry-gate envelope.

Unknown changed-file shapes must fail closed.

## 24. Required next bounded gate

If this entry gate is published, the next logical Sprint51 task is:

**Sprint51 JRN-008 Bounded Outlet Inventory Baseline Schema & Source Envelope Gate**

That gate must start from the exact then-current canonical `main`, inspect the
bounded evidence listed above, and may not reuse stale qualification.

It must not treat this entry gate as schema, source, runtime, deployment,
release, Production, or migration-execution authority.

## 25. Entry-gate decision

Sprint51 formally selects only:

**JRN-008 — Tenant/Outlet/Product Opening Inventory Baseline Foundation**

The bounded concern is one auditable, exactly-once, fail-closed opening quantity
establishment for one already-prepared catalog product in one server-derived
outlet, before ordinary inventory movement, while preserving all current
catalog, sale, void, tenant-isolation, and lifecycle contracts.

No ongoing stock-adjustment authority is created.

**Migration #20 remains NOT SELECTED.**

**Technical Preview remains NOT ACTIVATED.**

**Production remains NO-GO / NOT AUTHORIZED.**

Attribution: **Lab | zefry**
