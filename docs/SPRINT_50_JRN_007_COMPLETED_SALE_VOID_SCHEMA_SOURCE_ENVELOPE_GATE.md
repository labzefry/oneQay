# Sprint50 JRN-007 Completed-Sale Void Schema / Source Envelope Gate

Author by Lab | zefry

## Status

**SELECTED / SCHEMA-SOURCE ENVELOPE GATE / SOURCE NOT YET IMPLEMENTED / MIGRATION_19_SELECTED_NOT_EXECUTED**

## Canonical predecessor

This gate starts from canonical `main`:

- commit: `f983aa5b3eb96cfef0686799c02d8358b43c622a`;
- tree: `7fce4270cd8253417386224f3891cdb4ab6e1db6`;
- Sprint50 JRN-007 Controlled Full Completed-Sale Void entry gate is canonical through PR #497;
- the evidence-proven Sprint50 entry-gate historical compatibility predecessor is canonical through PR #496;
- Sprint49 JRN-006 active-shift fresh-sale precondition remains canonical;
- Sprint48 JRN-005 shift/register opening remains canonical;
- canonical source migrations are exactly #1 through #18.

Migrations #16, #17, and #18 remain **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**.

Migration #19 is not present on this canonical predecessor.

Technical Preview remains **NOT ACTIVATED**.

Production remains **NO-GO / NOT AUTHORIZED**.

Updater remains **NOT ACTIVATED**.

No deployment, release, migration execution/application/activation, rollback, force update, protected-main bypass, or Production authority is created here.

## Targeted canonical source findings

The bounded source inspection required by the entry gate establishes:

1. canonical JRN-006 persists immutable completed-sale identity, original sale-line snapshots, tender evidence, total/applied/change values, correlation identity, actor, tenant, organization, outlet, device, and completion time;
2. `oneqay_pos_sales` already has unique `tenant_id + operation_id`, but there is no durable correction/void row;
3. `oneqay_pos_sale_events` can preserve additional sale history, but it does not by itself provide a unique one-void-per-sale state or a durable correction result that can be hydrated for exact replay;
4. `oneqay_pos_sale_lines` preserves the exact sold product quantities required to derive stock compensation without caller-supplied quantity;
5. current catalog stock is tenant + outlet + product scoped;
6. canonical `PosExecutionContext` already derives actor, tenant, organization, outlet, and device exclusively from verified server-owned context;
7. canonical `CompleteSale` already uses `PersistenceTransaction`, and the same transaction primitive is sufficient for the bounded correction;
8. no canonical permission for sale void exists;
9. no canonical JRN-007 route, feature flag, controller, application command, result, or durable void method exists;
10. migration #18 shift state is not required to represent an already-completed sale correction.

Therefore **NO_SCHEMA_CHANGE is rejected** for the bounded JRN-007 full-void foundation.

## Schema decision

Sprint50 selects exactly one future source migration:

`apps/web/database/migrations/0000_00_00_000019_create_pos_sale_void_foundation.php`

Migration #19 is:

**SELECTED IN SOURCE DESIGN ONLY / NOT CREATED BY THIS GATE / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

The future migration may create exactly one table:

`oneqay_pos_sale_voids`

No existing migration #1 through #18 may be modified.

### Required columns

The selected table is frozen to:

- `tenant_id` string(64);
- `void_id` string(32), deterministic and server-owned;
- `operation_id` string(128);
- `payload_fingerprint` char(64);
- `sale_id` string(32);
- `actor_identity_id` string(96);
- `organization_id` string(64);
- `outlet_id` string(64);
- `device_id` string(64);
- `reversed_atomic` unsigned bigint;
- `currency` char(3);
- `currency_scale` unsigned tiny integer;
- `tender_category` string(32);
- `evidence_mode` string(32);
- `correlation_id` string(128);
- `voided_at_unix` unsigned bigint.

### Required constraints

Migration #19 must define:

- primary key: `tenant_id + void_id`;
- unique idempotency: `tenant_id + operation_id`;
- unique one-void-per-sale: `tenant_id + sale_id`;
- index: `tenant_id + outlet_id + voided_at_unix`;
- restrictive tenant-bound foreign key to `oneqay_pos_sales(tenant_id, sale_id)`;
- restrictive tenant-bound foreign key to canonical identity;
- restrictive tenant-bound foreign key to canonical organization;
- restrictive tenant-bound foreign key to canonical outlet;
- restrictive tenant-bound foreign key to canonical device;
- forward-only `down()` that fails closed.

No void-line table is selected.

The immutable canonical `oneqay_pos_sale_lines` rows are the source of truth for the exact stock-restoration vector. The correction row references the immutable original sale, and the sale retains its immutable line evidence.

No status column is added to `oneqay_pos_sales`. The original sale must not be rewritten to represent the correction.

## Permission decision

The future source adds exactly one permission identifier:

`pos.sale.void`

It is deny-by-default.

It receives no default role grant.

Existing permissions, including:

- `pos.sale.complete`;
- `pos.catalog.prepare`;
- `pos.shift.open`;

do not imply JRN-007 void authority.

The future application service must require `pos.sale.void` against the verified current organizational context before durable correction.

## Caller-input decision

The future HTTP payload is frozen to exactly:

- `operation_id`;
- `sale_id`.

No reason input is selected in this foundation because no canonical authoritative reason-code allow-list exists.

Arbitrary free text is not accepted.

The caller must not submit or override:

- actor;
- tenant;
- organization;
- outlet;
- device;
- register;
- shift;
- role;
- permission;
- session authority;
- target sale state;
- money;
- tender category;
- refund amount;
- stock quantity;
- line quantity;
- currency;
- currency scale;
- correlation identity;
- void timestamp;
- provider result;
- settlement evidence.

`operation_id` must use the canonical stable identifier grammar already used by JRN-006.

`sale_id` must match the canonical server-generated completed-sale identifier shape.

Correlation identity remains server derived from the request boundary and is not semantic caller authority.

## Target-sale eligibility

The durable target lookup is frozen to the current server-derived tenant boundary.

The target sale must additionally match the current verified:

- organization;
- outlet.

A sale from another tenant, organization, or outlet fails closed.

The current verified device is audited as the device performing the void, but **the target sale is not required to have been completed on the same device**.

This is deliberate:

- the immutable original sale retains its original device;
- the void record retains the current authorized correction device;
- a separately authorized operator at the same verified outlet may perform the correction from another verified device;
- caller-selected device or register authority remains impossible.

No cross-outlet correction is selected because stock compensation is outlet-scoped to the immutable original sale.

## Shift relationship

The bounded full-void foundation does **not** require:

- the original shift to remain active;
- a current active shift;
- the current device to own the original sale's shift;
- a shift close/reopen transition.

The void operation must never:

- create a shift;
- close a shift;
- reopen a shift;
- change `active_slot`;
- reassign a shift;
- rewrite JRN-005 opening evidence.

This is a post-completion correction boundary, not a register lifecycle mutation.

JRN-010 remains separately governed.

## Idempotency and replay semantics

The future JRN-007 operation uses:

`tenant_id + operation_id`

as its durable idempotency boundary.

The semantic fingerprint must bind at least:

- current actor;
- current tenant;
- current organization;
- current outlet;
- current device;
- target canonical sale id.

Correlation identity and server time must not be part of the semantic payload fingerprint.

The future ordering is frozen as:

1. enforce runtime/persistence/feature guards;
2. compute the JRN-007 semantic fingerprint;
3. lock and resolve existing `tenant_id + operation_id` in `oneqay_pos_sale_voids`;
4. if found:
   - fingerprint mismatch fails closed;
   - exact replay hydrates and returns the original durable void result;
   - replay performs no stock mutation and writes no second correction/event;
5. if no existing void operation exists:
   - lock target sale inside current tenant;
   - require exact current organization and outlet match;
   - lock/check any existing `tenant_id + sale_id` void;
   - a different operation targeting an already-void sale fails closed;
   - load immutable original sale lines;
   - derive stock and money correction exclusively from durable original sale evidence;
   - apply exact stock compensation;
   - insert one durable void row;
   - insert one additional `VOIDED` sale event;
   - return the new durable result.

Database uniqueness remains the final concurrency arbiter.

A stale, missing, cross-boundary, malformed, conflicting, already-void, or ambiguous target fails closed.

## Original sale immutability

The future source must not update or delete the original rows in:

- `oneqay_pos_sales`;
- `oneqay_pos_sale_lines`.

It must not rewrite:

- original actor;
- organization;
- outlet;
- device;
- original operation id;
- payload fingerprint;
- prices;
- quantities;
- tender category;
- evidence mode;
- total;
- applied amount;
- change;
- original correlation identity;
- completion time.

The original sale remains the immutable historical completion fact.

The void is additional durable correction evidence.

## Stock-compensation decision

A successful first-time full void must restore stock using only immutable original sale-line quantities.

For every original line:

- resolve the catalog row by the original sale's tenant + outlet + product id;
- lock the row before mutation;
- do not require the catalog row to remain `active`;
- require the row to exist;
- increment `available_quantity` by the exact original sold quantity;
- detect arithmetic overflow and fail closed.

No caller quantity participates.

If any required catalog row is missing or any increment cannot be represented safely, the entire correction fails and the transaction rolls back.

No partial restoration is allowed.

## Financial-correction evidence

The bounded foundation does not send money and does not call an external provider.

The durable correction row records the internal financial reversal evidence derived from the original completed sale:

- `reversed_atomic = original applied_atomic`;
- original currency;
- original currency scale;
- original tender category;
- fixed bounded evidence mode for full completed-sale void.

The source must not treat original `change_atomic` as a new refund amount.

Provider reversal, settlement reversal, payout, bank action, card-network action, cash movement, drawer mutation, or refund are outside this source envelope.

## Event decision

The existing `oneqay_pos_sale_events` table is reused.

A successful first-time void adds exactly one event with:

`event_type = VOIDED`

The event:

- references the immutable target sale;
- uses the JRN-007 operation id;
- records current authorized actor;
- records server-owned correlation identity;
- records server-owned occurrence time.

The event id must be deterministic for the JRN-007 correction identity.

Exact replay must not add a second event.

No existing JRN-006 `COMPLETED` or `REPLAYED` event is removed or rewritten.

## Application boundary

The future source introduces:

- `SaleVoidCommand`;
- `SaleVoidResult`;
- `VoidSale`.

`VoidSale` must:

1. obtain current verified organizational context;
2. create canonical `PosExecutionContext`;
3. require `pos.sale.void`;
4. obtain server-owned time from existing `PosSaleClock`;
5. execute the durable repository void method inside canonical `PersistenceTransaction`.

No new transaction primitive or clock is selected.

The existing `DurablePosSaleRepository` is extended with one bounded void method rather than creating a second overlapping sale aggregate repository.

## Infrastructure boundary

`LaravelDurablePosSaleRepository` remains the single durable sale aggregate adapter.

Its current completion behavior must remain unchanged.

The future constructor/provider wiring may add the independently disabled void feature flag while preserving sale-completion behavior.

Completion operational checks continue to use the sale-completion flag.

Void operational checks require both:

- canonical sale-completion foundation enabled;
- new JRN-007 void feature enabled.

No void operation is permitted in a runtime other than Local/Test/CI.

## Runtime and route decision

Future feature flag:

`ONEQAY_POS_SALE_VOID_ENABLED`

Canonical config key:

`oneqay.pos_sale_void.enabled`

It must default to `false`.

The future route is frozen to:

`POST /pos/sales/void`

The route must require:

- runtime class Local/Test/CI;
- canonical session control;
- `oneqay.pos_sale_completion.enabled`;
- `oneqay.pos_sale_void.enabled`;
- active first-party session;
- bounded mutation throttling;
- existing `RequirePosSessionContextMiddleware`.

Technical Preview routes are not changed.

Production remains unavailable by default and unauthorized.

## HTTP result and failure posture

A successful result may expose only bounded correction evidence equivalent to:

- status `voided`;
- void id;
- target sale id;
- operation id;
- tenant id;
- outlet id;
- reversed amount with currency/scale;
- original tender category;
- bounded evidence mode;
- server-owned correlation id;
- voided-at time.

Authorization denial retains the canonical safe POS authorization envelope.

Invalid, missing, cross-boundary, conflicting, already-void, stock-compensation, or durable correction failures return one bounded safe JRN-007 rejection envelope without leaking target existence across tenant boundaries.

## Frozen future source envelope

The next bounded Sprint50 source implementation is frozen to exactly these 14 paths:

1. `.github/workflows/sprint50-jrn007-completed-sale-void-regression.yml`
2. `apps/web/app/Application/Authorization/PosPermission.php`
3. `apps/web/app/Application/Pos/DurablePosSaleRepository.php`
4. `apps/web/app/Application/Pos/SaleVoidCommand.php`
5. `apps/web/app/Application/Pos/SaleVoidResult.php`
6. `apps/web/app/Application/Pos/VoidSale.php`
7. `apps/web/app/Delivery/Http/Pos/PosSaleVoidController.php`
8. `apps/web/app/Infrastructure/Pos/LaravelDurablePosSaleRepository.php`
9. `apps/web/app/Providers/AppServiceProvider.php`
10. `apps/web/config/oneqay.php`
11. `apps/web/database/migrations/0000_00_00_000019_create_pos_sale_void_foundation.php`
12. `apps/web/routes/web.php`
13. `apps/web/tests/pos-sale-void-durable.php`
14. `docs/JRN_007_POS_COMPLETED_SALE_VOID_FOUNDATION.md`

Sorted newline-terminated path SHA-256:

`78628976e4dbfea29ecab4b40ec6bfbdf697564668ef028a5cb77a3b53466451`

No application/source path outside this envelope is authorized by this gate.

Migration #1 through #18 must remain byte-preserved.

Historical compatibility corrections, if exact terminal CI evidence proves them necessary, must remain separate workflow-only predecessor PRs.

## Dedicated regression requirements

The future dedicated Sprint50 regression must prove at least:

- exact 14-path source envelope and fingerprint;
- canonical migrations #1 through #18 byte-preserved;
- exactly one migration #19 source file added;
- migration #19 defines only the bounded void table and remains forward-only;
- migration #19 is source-published only and never treated as executed/applied/activated;
- feature flag defaults false;
- Local/Test/CI-only source boundary;
- active first-party session and verified server-derived context;
- `pos.sale.void` required and deny-by-default;
- no default permission grant;
- closed caller payload of only operation id + sale id;
- cross-tenant target denied;
- cross-organization target denied;
- cross-outlet target denied;
- same-outlet different-current-device correction may succeed when independently authorized;
- no active-shift requirement;
- no shift mutation;
- missing sale denied;
- original completed sale row remains byte/field equivalent after void;
- original sale lines remain byte/field equivalent after void;
- full stock restoration uses exact original quantities;
- inactive-but-existing catalog rows may receive exact restoration;
- missing catalog row fails closed with zero partial correction;
- arithmetic overflow fails closed;
- exact financial reversal evidence equals original `applied_atomic`;
- no external provider/refund call;
- exact replay returns original void evidence;
- exact replay creates no second stock increment;
- exact replay creates no second void row;
- exact replay creates no second `VOIDED` event;
- conflicting operation reuse fails closed;
- different operation against already-void sale fails closed;
- concurrent one-sale correction is arbitrated by durable uniqueness/locking;
- original JRN-006 completion replay remains unchanged;
- fresh JRN-006 active-shift precondition remains unchanged;
- JRN-005 shift-opening evidence remains unchanged;
- JRN-004 catalog preparation remains independently governed;
- relevant historical regressions remain executable;
- tracked-source cleanliness;
- no workflow with `jobs=[]` qualifies as success.

## Dedicated workflow posture

The future workflow:

`.github/workflows/sprint50-jrn007-completed-sale-void-regression.yml`

must:

- trigger only for the exact frozen 14-path source envelope;
- enforce fingerprint `78628976e4dbfea29ecab4b40ec6bfbdf697564668ef028a5cb77a3b53466451`;
- reject any mutation to migration #1 through #18;
- require exactly one newly added migration #19;
- validate PHP syntax;
- install locked dependencies;
- reject High/Critical Composer advisories;
- run the dedicated JRN-007 regression;
- preserve JRN-004, JRN-005, JRN-006, Sprint49 active-shift, and M7.4 regressions where their canonical fixtures are compatible with the source horizon;
- enforce tracked-source cleanliness.

Unknown changed-file shapes remain fail closed.

## JRN-006 replay preservation

A JRN-006 exact replay after a sale has later been voided must continue to return the original immutable completed-sale receipt without creating another sale or stock decrement.

The JRN-007 correction does not retroactively rewrite JRN-006 idempotency semantics.

A fresh JRN-007 void is a separate operation with its own correction permission and durable idempotency boundary.

## JRN-010 boundary

JRN-010 remains **NOT SELECTED**.

Sprint50 does not implement:

- shift close;
- expected-vs-actual reconciliation;
- cash count;
- cash variance;
- reviewer approval;
- closing state;
- settlement import;
- payment-provider reconciliation;
- drawer movement.

A future JRN-010 gate may consume immutable `COMPLETED` and `VOIDED` evidence but must not be smuggled into this foundation.

## Explicit non-scope

This gate does not authorize:

- pre-completion cancellation;
- partial void;
- partial return;
- return reason lifecycle;
- item-condition lifecycle;
- exchange;
- refund;
- provider reversal;
- chargeback;
- external settlement;
- cash drawer movement;
- opening cash;
- closing cash;
- cash count;
- cash variance;
- JRN-010 shift close;
- shift reopen;
- shift handoff;
- register CRUD/administration;
- broad inventory adjustment administration;
- purchasing;
- supplier lifecycle;
- accounting general ledger posting;
- tax/fiscal policy;
- Technical Preview activation;
- Production activation;
- updater activation;
- deployment;
- release;
- migration execution;
- migration application;
- schema activation;
- rollback;
- force update;
- protected-main bypass.

## Gate envelope

This gate changes exactly:

`docs/SPRINT_50_JRN_007_COMPLETED_SALE_VOID_SCHEMA_SOURCE_ENVELOPE_GATE.md`

Sorted newline-terminated path SHA-256:

`42bf761a33ab61dc2110d9afae0a307ffcc63828c34c2dbd7d2b0f0da4e87f0f`

Unknown changed-file shapes remain fail closed.

## Lifecycle locks

- migration #16: **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**;
- migration #17: **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**;
- migration #18: **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**;
- migration #19: **SELECTED IN SOURCE DESIGN ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**;
- Technical Preview: **NOT ACTIVATED**;
- Production: **NO-GO / NOT AUTHORIZED**;
- updater: **NOT ACTIVATED**;
- deployment/release/migration execution/rollback: **NOT AUTHORIZED**.

After canonical publication of this gate, only the frozen 14-path JRN-007 source implementation may proceed under the existing bounded repository authority.

Attribution: **Lab | zefry**
