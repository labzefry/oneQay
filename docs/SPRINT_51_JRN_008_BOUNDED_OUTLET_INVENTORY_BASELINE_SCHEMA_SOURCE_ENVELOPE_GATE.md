# Sprint51 JRN-008 Bounded Outlet Inventory Baseline Schema & Source Envelope Gate

Author by Lab | zefry

## 1. Gate classification

`SCHEMA + SOURCE ENVELOPE SELECTED / SOURCE IMPLEMENTATION NOT YET AUTHORIZED BY THIS DOCUMENT ALONE / LIFECYCLE NOT AUTHORIZED`

This bounded Sprint51 gate starts from canonical main:

`d53cdbe197b56eeac620e83cac3b8fe266131f44`

The canonical predecessor is the published Sprint51 JRN-008 entry gate in PR
#510.

This gate freezes the minimum schema/source design required to implement the
selected one-time outlet inventory baseline foundation. It does not execute or
apply migration #20 and creates no Technical Preview, Production, deployment,
release, updater, rollback, or destructive lifecycle authority.

## 2. Entry-gate semantic retained

The selected concern remains exactly:

**JRN-008 — Tenant/Outlet/Product Opening Inventory Baseline Foundation**

The future operation establishes one opening quantity for one already-prepared
catalog product in the exact server-derived outlet.

It is not an ongoing stock-adjustment surface.

The entry-gate invariants remain unchanged:

- catalog row exists;
- current `available_quantity` is exactly zero for first application;
- no previous baseline exists for the exact tenant/outlet/product;
- no prior canonical sale-line movement exists for that product/outlet;
- zero opening quantity is valid when durable baseline evidence records that the
  baseline has already been established;
- exact replay is safe;
- a second distinct baseline fails closed;
- price/currency/sellability are not mutated;
- shift state is not required or mutated.

## 3. Why schema is required

`NO_SCHEMA_CHANGE` is rejected.

The current `oneqay_pos_sale_catalog_items.available_quantity` value cannot by
itself distinguish:

- never-baselined zero quantity; from
- an intentionally established zero baseline.

It also cannot durably prove:

- one-time baseline uniqueness;
- `tenant_id + operation_id` replay identity;
- request semantic fingerprint;
- actor/context evidence;
- immutable before/after baseline evidence;
- original correlation identity and occurrence time.

Therefore one new durable source-only migration is required.

## 4. Selected migration

Sprint51 selects exactly:

`apps/web/database/migrations/0000_00_00_000020_create_pos_inventory_baseline_foundation.php`

Migration #20 is:

`SOURCE DESIGN SELECTED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED`

No migration #21 or additional schema change is selected.

Migration #20 must remain forward-only and its `down()` path must throw:

`LogicException('Forward-only generated migration; rollback is not authorized.')`

## 5. Selected durable table

Migration #20 must create exactly one table:

`oneqay_pos_inventory_baselines`

The table is immutable baseline evidence and the durable replay/uniqueness
boundary.

It is not a stock ledger, adjustment ledger, receiving ledger, transfer ledger,
or stocktake table.

## 6. Exact migration #20 columns

The selected columns are:

- `tenant_id VARCHAR(64)`;
- `baseline_id CHAR(32)`;
- `operation_id VARCHAR(128)`;
- `payload_fingerprint CHAR(64)`;
- `actor_identity_id VARCHAR(96)`;
- `organization_id VARCHAR(64)`;
- `outlet_id VARCHAR(64)`;
- `device_id VARCHAR(64)`;
- `product_id VARCHAR(64)`;
- `before_available_quantity UNSIGNED BIGINT`;
- `opening_quantity UNSIGNED BIGINT`;
- `correlation_id VARCHAR(128)`;
- `occurred_at_unix UNSIGNED BIGINT`.

For every first successful baseline,
`before_available_quantity` must be exactly zero by application invariant.

## 7. Exact migration #20 keys and constraints

The selected relational constraints are:

Primary key:

`tenant_id + baseline_id`

Unique replay key:

`tenant_id + operation_id`

Unique one-time baseline key:

`tenant_id + outlet_id + product_id`

Required foreign keys:

- `tenant_id + actor_identity_id` -> canonical identities;
- `tenant_id + organization_id` -> canonical organizations;
- `tenant_id + outlet_id` -> canonical outlets;
- `tenant_id + device_id` -> canonical devices;
- `tenant_id + outlet_id + product_id` ->
  `oneqay_pos_sale_catalog_items(tenant_id, outlet_id, product_id)`.

All selected foreign-key updates/deletes remain restrictive.

No nullable business-state column is required.

## 8. Exact permission

The exact future permission identifier is frozen as:

`pos.inventory.baseline`

`PosPermission` must expose a dedicated constant and typed helper.

No existing permission may be reused as implicit baseline authority.

No default role grant is included in the source envelope.

## 9. Exact command shape

The Application command is:

`InventoryBaselineCommand`

Its exact constructor business inputs are:

1. `operationId`;
2. `ProductId productId`;
3. `int openingQuantity`.

The command must:

- validate the same stable operation-id vocabulary used by current POS mutation
  commands;
- reject negative quantity;
- preserve zero as valid;
- expose a deterministic semantic fingerprint part containing product and
  opening quantity only.

Tenant, organization, outlet, device, actor, role, permission, correlation, and
time are not command inputs.

## 10. Exact result shape

The Application result is:

`InventoryBaselineResult`

The bounded result must expose:

- baseline id;
- operation id;
- tenant id;
- outlet id;
- product id;
- opening quantity.

HTTP correlation identity remains response metadata from the server-owned
request correlation boundary.

No price/sellability/payment/shift data belongs in this result.

## 11. Exact Application service

The selected service is:

`EstablishInventoryBaseline`

It must depend only on Application abstractions plus canonical security/context
abstractions:

- `InventoryBaselineRepository`;
- `OrganizationalContextStore`;
- `DurableScopedAuthorizationPolicy`;
- `PersistenceTransaction`;
- `InventoryBaselineClock`.

The service must:

1. validate server-owned correlation identity;
2. derive `PosExecutionContext` from current verified organizational context;
3. require `PosPermission::inventoryBaseline()`;
4. obtain positive server time from the dedicated clock;
5. run the repository mutation inside the canonical persistence transaction.

The Application layer must remain framework independent.

## 12. Exact clock abstraction

The selected clock is:

`InventoryBaselineClock`

It contains only:

`public function nowUnix(): int;`

The provider may bind it to the existing bounded anonymous system-time pattern
used by current POS clocks.

## 13. Exact repository interface

The selected repository is:

`InventoryBaselineRepository`

It exposes one mutation method equivalent to:

`establish(PosExecutionContext, InventoryBaselineCommand, correlationId, occurredAtUnix): InventoryBaselineResult`

No broad inventory CRUD methods are selected.

## 14. Exact persistence ordering

The Laravel adapter must preserve this fail-closed transactional ordering.

### A. Operational guard

Deny unless:

- persistence is enabled;
- JRN-008 feature flag is enabled;
- runtime class is exactly one of Local/Test/CI.

### B. Deterministic fingerprint

Compute the semantic payload fingerprint from:

- actor id;
- tenant id;
- organization id;
- outlet id;
- device id;
- command product id;
- command opening quantity.

### C. Exact operation replay lookup

First lock/read `oneqay_pos_inventory_baselines` by exact:

`tenant_id + operation_id`.

If found:

- payload fingerprint must match exactly;
- return the original durable baseline result;
- do not inspect or rewrite current catalog quantity;
- do not create a second baseline row.

This permits a safe exact replay even after later JRN-006/JRN-007 quantity
movement.

### D. Catalog row lock

For a fresh operation, lock the exact
`tenant_id + outlet_id + product_id` catalog row.

Missing row fails closed.

### E. One-time product baseline guard

Check for existing baseline evidence for exact
`tenant_id + outlet_id + product_id`.

Any existing record under another operation fails closed.

### F. Zero-state guard

Current catalog `available_quantity` must be exactly zero.

Non-zero current state fails closed.

### G. Prior-sale-history guard

The adapter must prove no canonical JRN-006 sale-line history exists for this
product in this outlet.

The bounded lookup must join canonical sale lines to their sale header so outlet
ownership is derived from `oneqay_pos_sales.outlet_id`, while retaining exact
tenant/product scoping.

Any matching sale history fails closed.

JRN-007 history cannot exist without JRN-006 sale history, so no separate void
query is required for the baseline eligibility invariant.

### H. Atomic update + evidence

Update only the exact catalog row
`available_quantity` from zero to the requested opening quantity and insert
one immutable baseline evidence row in the same canonical transaction.

The catalog update must retain a zero-state compare condition so unexpected
concurrent state change fails closed.

If the opening quantity is zero, the row value remains zero but durable
baseline evidence is still inserted.

## 15. Concurrency proof

The catalog row is the serialization point for fresh baseline versus current
sale quantity mutation.

JRN-006 already locks the exact catalog row before quantity availability and
decrement.

A fresh baseline must also lock that row before eligibility and application.

Two different baseline operations against the same product cannot both succeed:

- the first lock holder inserts the unique product baseline evidence;
- the second, after acquiring the row lock, observes existing baseline evidence
  and fails closed.

Database uniqueness remains the final defensive boundary.

## 16. Prior-sale-history rationale

Current canonical source has no governed application path that raises a newly
prepared catalog product above server-owned zero before JRN-008.

The explicit sale-history guard is still selected because baseline is an
opening-state operation, not a reset operation.

It prevents accidental re-baselining if a legacy/directly seeded quantity was
sold down to zero before JRN-008 evidence existed.

No sale or sale-line row is modified by this check.

## 17. Exact feature flag

The selected source-default feature key is:

Environment:

`ONEQAY_POS_INVENTORY_BASELINE_ENABLED`

Config:

`oneqay.pos_inventory_baseline.enabled`

Default:

`false`

The adapter and route must independently remain fail-closed when the feature is
not armed.

## 18. Exact delivery boundary

The selected future endpoint is:

`POST /pos/inventory/baseline`

Controller:

`PosInventoryBaselineController`

The route is created only when:

- runtime class is Local/Test/CI;
- canonical session control is enabled;
- `oneqay.pos_inventory_baseline.enabled` is true.

The route must be independent from whether sale completion, catalog preparation,
shift opening, or sale void is enabled.

Required middleware includes:

- `session.active`;
- existing bounded throttling;
- `RequirePosSessionContextMiddleware`.

No Technical Preview or Production route activation is selected.

## 19. Exact closed HTTP payload

The controller accepts exactly:

- `operation_id`;
- `product_id`;
- `opening_quantity`.

Type requirements:

- operation id: string;
- product id: string;
- opening quantity: integer and non-negative.

Unknown or additional request keys fail closed.

The controller must never accept tenant/outlet/device/actor/role/permission,
before quantity, current quantity, price, sellability, sale/void state, or
timestamp input.

## 20. Exact HTTP response posture

First success and exact replay may return a bounded response containing:

- status indicating baseline established;
- baseline id;
- operation id;
- tenant id;
- outlet id;
- product id;
- opening quantity;
- server-owned correlation id.

Response caching remains:

`Cache-Control: no-store, private`

Authorization denial remains a 403 safe error envelope.

Invalid/conflicting baseline state remains a bounded 422 safe error envelope.

No raw internal exception or database detail is exposed.

## 21. Catalog compatibility contract

JRN-004 must remain unchanged semantically:

- repricing/sellability preparation never rewrites
  `available_quantity`;
- a newly created catalog row still begins at server-owned zero;
- a later JRN-004 mutation after baseline preserves the baselined/current
  quantity;
- JRN-004 journal history remains unchanged.

JRN-008 does not alter display name, price, currency, scale, or active state.

## 22. Sale compatibility contract

JRN-006 sale completion remains unchanged:

- exact tenant/outlet/product catalog resolution;
- active/sellable requirement;
- sufficient quantity requirement;
- row lock before decrement;
- atomic decrement;
- exact replay does not decrement twice.

JRN-008 introduces no sale input or sale replay behavior.

## 23. Void compatibility contract

JRN-007 remains unchanged:

- stock restoration is derived only from immutable sale lines;
- inactive catalog row restoration remains allowed;
- missing catalog state fails the whole void transaction;
- overflow fails closed;
- exact void replay does not restore twice.

JRN-008 baseline evidence is never rewritten by JRN-007.

## 24. Shift compatibility contract

JRN-008 does not require active shift state.

JRN-005/Sprint49 shift semantics are not part of baseline eligibility.

Baseline establishment must never create, close, reopen, transfer, or mutate a
shift/register record.

## 25. Selected migration #20 table does not become a ledger

`oneqay_pos_inventory_baselines` records only one opening-state fact per
tenant/outlet/product.

It does not record:

- sales;
- voids;
- purchases;
- receipts;
- transfers;
- adjustments;
- counts;
- shrinkage;
- reconciliation.

Those concerns require later explicit journeys and schema decisions.

## 26. Exact future source envelope

The future JRN-008 source implementation is frozen to exactly these 15 paths:

1. `.github/workflows/sprint51-jrn008-bounded-outlet-inventory-baseline-regression.yml`
2. `apps/web/app/Application/Authorization/PosPermission.php`
3. `apps/web/app/Application/Pos/EstablishInventoryBaseline.php`
4. `apps/web/app/Application/Pos/InventoryBaselineClock.php`
5. `apps/web/app/Application/Pos/InventoryBaselineCommand.php`
6. `apps/web/app/Application/Pos/InventoryBaselineRepository.php`
7. `apps/web/app/Application/Pos/InventoryBaselineResult.php`
8. `apps/web/app/Delivery/Http/Pos/PosInventoryBaselineController.php`
9. `apps/web/app/Infrastructure/Pos/LaravelInventoryBaselineRepository.php`
10. `apps/web/app/Providers/AppServiceProvider.php`
11. `apps/web/config/oneqay.php`
12. `apps/web/database/migrations/0000_00_00_000020_create_pos_inventory_baseline_foundation.php`
13. `apps/web/routes/web.php`
14. `apps/web/tests/pos-inventory-baseline-durable.php`
15. `docs/JRN_008_POS_BOUNDED_OUTLET_INVENTORY_BASELINE_FOUNDATION.md`

Sorted newline-terminated path SHA-256:

`325559599c79ba68340eac34569f8e5513aa4957b1ddf2515007b98b2cc1f15f`

Any other changed-file shape fails closed.

The entry-gate and schema/source-envelope gate documents themselves are not part
of the future source envelope because they are already canonical predecessor
evidence.

## 27. Dedicated workflow contract

The new Sprint51 workflow must at minimum enforce:

- exact 15-path source envelope and fingerprint;
- exactly one migration delta and exact migration #20 path;
- migrations #1 through #19 preserved byte-for-byte relative to base;
- migration #20 exact selected schema shape;
- no dependency lock or environment-file changes;
- Application framework independence;
- permission/feature/default-false route locks;
- PHP syntax;
- locked Composer install;
- High/Critical Composer advisory rejection;
- dedicated JRN-008 durable regression;
- JRN-004 catalog preparation regression preservation;
- JRN-006 sale regression preservation;
- Sprint49 active-shift sale regression preservation where materially required;
- JRN-007 void regression preservation;
- M7.4 synthetic POS regression preservation;
- tracked-source cleanliness;
- lifecycle/runtime locks.

Historical compatibility changes, if exact fresh failures require them, remain
separate bounded workflow-only predecessors.

## 28. Required dedicated JRN-008 regression

The dedicated regression must prove all of the following:

- permission denied by default;
- authorized first positive baseline succeeds;
- authorized zero baseline succeeds and becomes durably established;
- exact replay returns original baseline evidence;
- exact replay after later quantity movement does not rewrite quantity;
- conflicting operation-id reuse fails;
- second operation for an already-baselined product fails;
- missing catalog row fails;
- non-zero pre-baseline current quantity fails;
- prior-sale-history baseline attempt fails even when current quantity is zero;
- cross-tenant product target fails;
- cross-outlet target fails;
- negative quantity is rejected before persistence;
- unknown HTTP field is rejected;
- caller cannot inject tenant/outlet/device/actor/current quantity;
- price/currency/scale/sellability remain unchanged;
- inactive catalog row may receive a baseline without being activated;
- concurrent/double baseline safety is deterministically covered to the extent
  supported by the current test adapter;
- baseline evidence includes immutable server-owned context/correlation/time;
- JRN-004 later preparation preserves quantity and baseline evidence;
- JRN-006 sale consumes established quantity exactly once;
- JRN-006 replay does not consume again;
- JRN-007 void restores exact sold quantity and leaves baseline evidence
  unchanged;
- no shift state is created or mutated;
- migration #20 is forward-only;
- feature defaults false;
- runtime outside Local/Test/CI fails closed.

## 29. Explicit non-scope

No future source outside the exact envelope may add:

- general stock adjustment;
- stock receiving;
- purchase order/supplier behavior;
- stock transfer;
- stocktake/cycle count;
- shrinkage/damage/expiry;
- inventory reconciliation;
- valuation/COGS;
- reservation;
- reorder/replenishment;
- negative-stock expansion;
- warehouse lifecycle;
- batch/lot/serial tracking;
- broad catalog CRUD;
- refund/return;
- JRN-010 close/reconciliation;
- accounting;
- external providers;
- offline inventory mutation.

## 30. Lifecycle locks

After this gate, canonical migration horizon remains #1 through #19 until source
publication.

Migration #20 is only **SELECTED IN SOURCE DESIGN**.

The following remain unchanged:

- migration #16 through #19: SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT
  ACTIVATED;
- migration #20: NOT CREATED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED at
  this gate;
- Technical Preview: NOT ACTIVATED;
- Production: NO-GO / NOT AUTHORIZED;
- updater activation: NOT AUTHORIZED;
- deployment: NOT AUTHORIZED;
- release: NOT AUTHORIZED;
- rollback: NOT AUTHORIZED.

## 31. Exact schema/source-envelope gate path

This gate changes exactly:

`docs/SPRINT_51_JRN_008_BOUNDED_OUTLET_INVENTORY_BASELINE_SCHEMA_SOURCE_ENVELOPE_GATE.md`

Sorted newline-terminated path SHA-256:

`4b7b82341172725684683b21bc4014fc2aaf68fc64dc8bf9549da835d456376e`

No source/workflow/config/migration/dependency/runtime file belongs to this gate
PR.

## 32. Required next bounded task

If this gate is published, the next Sprint51 task is:

**Sprint51 JRN-008 Bounded Outlet Inventory Baseline Source Implementation**

That source task must begin from the exact then-current canonical main, create
only the frozen 15-path envelope, and qualify the exact head independently.

Migration #20 may be created as source in that future task but must not be
executed/applied/activated.

## 33. Gate decision

Sprint51 selects:

- exact semantic: one-time tenant/outlet/product opening inventory baseline;
- exact permission: `pos.inventory.baseline`;
- exact closed payload: operation id + product id + opening quantity;
- exact Local/Test/CI default-false feature boundary;
- exact durable baseline evidence table;
- exact migration #20 source design;
- exact 15-path future source envelope.

This gate grants no lifecycle activation.

Attribution: **Lab | zefry**
