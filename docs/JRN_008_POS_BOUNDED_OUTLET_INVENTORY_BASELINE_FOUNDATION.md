# JRN-008 POS Bounded Outlet Inventory Baseline Foundation

Author by Lab | zefry

## Status

Sprint51 implements the bounded JRN-008 **Tenant/Outlet/Product Opening Inventory
Baseline Foundation** selected by the canonical entry gate and schema/source
envelope gate.

This is source publication only.

Migration #20 is **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT
ACTIVATED**.

Technical Preview remains unactivated. Production remains
**NO-GO / NOT AUTHORIZED**.

## Business boundary

JRN-008 establishes exactly one opening inventory quantity for one already
prepared product in the exact current server-derived tenant/outlet context.

It is not general inventory adjustment.

A first baseline is eligible only when:

- the exact catalog row exists;
- current `available_quantity` is zero;
- no durable JRN-008 baseline already exists for the exact
  tenant/outlet/product;
- no canonical JRN-006 sale-line history exists for the product in that outlet.

A zero opening quantity is valid. The durable baseline record, rather than a
non-zero quantity, proves that the baseline was established.

## Authorization

The dedicated permission is:

`pos.inventory.baseline`

No default role grant is created.

Tenant, organization, outlet, device, actor, role, permission, session
authority, correlation identity, and event time remain server-derived.

The caller provides only:

- `operation_id`;
- `product_id`;
- `opening_quantity`.

Unknown payload fields fail closed.

## Durable replay

Durable idempotency is scoped by:

`tenant_id + operation_id`

The semantic fingerprint binds current verified actor/context, product, and
opening quantity.

Exact replay is resolved from immutable JRN-008 evidence before current catalog
quantity is evaluated. This means an exact replay after a later sale or void
returns the original baseline result without rewriting the current quantity.

Conflicting operation-id reuse fails closed.

A different operation targeting an already-baselined product also fails closed.

## Durable evidence

Migration #20 creates only:

`oneqay_pos_inventory_baselines`

The table records immutable opening-state evidence including actor/context,
operation identity, semantic fingerprint, before quantity, opening quantity,
correlation identity, and server-owned occurrence time.

Uniqueness is enforced for:

- `tenant_id + operation_id`;
- `tenant_id + outlet_id + product_id`.

The baseline table is not a stock ledger.

## Transaction and concurrency boundary

For a fresh baseline, the adapter:

1. checks exact operation replay;
2. locks the exact catalog row;
3. rejects any prior product baseline;
4. requires current quantity zero;
5. rejects prior sale history for the product/outlet;
6. updates only `available_quantity` when the requested opening quantity is
   positive;
7. inserts immutable baseline evidence in the same canonical transaction.

A zero baseline inserts evidence while leaving the already-zero current quantity
unchanged.

Database uniqueness is the final defensive boundary against concurrent duplicate
baseline establishment.

## Relationship to JRN-004

JRN-004 remains responsible for catalog display name, price, currency/scale, and
sellability.

JRN-008 never changes those fields.

Later JRN-004 preparation preserves current `available_quantity` and does not
rewrite JRN-008 baseline evidence.

## Relationship to JRN-006

JRN-006 remains the governed sale stock-decrement path.

A sale consumes the current quantity established by JRN-008 exactly as it
consumes any other canonical available quantity.

JRN-006 exact replay continues to avoid a second decrement.

Prior canonical sale history blocks first-time JRN-008 baseline establishment,
preventing a sold-down-to-zero product from being treated as never initialized.

## Relationship to JRN-007

JRN-007 remains the governed completed-sale stock-restoration path.

Void restoration continues to derive quantity only from immutable original sale
lines. It does not rewrite JRN-008 baseline evidence.

## Shift boundary

JRN-008 does not require an active shift and never creates, closes, reopens,
reassigns, or mutates a shift/register record.

JRN-005 and Sprint49 sale-shift rules remain independent.

## Runtime boundary

The feature flag is:

`ONEQAY_POS_INVENTORY_BASELINE_ENABLED`

The configuration key is:

`oneqay.pos_inventory_baseline.enabled`

Default is false.

The endpoint is exposed only in Local/Test/CI with canonical session control
enabled and the JRN-008 feature explicitly armed:

`POST /pos/inventory/baseline`

The route requires active first-party session authority and the canonical
verified POS session context middleware.

The persistence adapter independently rejects:

- persistence disabled;
- feature disabled;
- runtime outside Local/Test/CI.

## Migration boundary

Migration #20 is:

`0000_00_00_000020_create_pos_inventory_baseline_foundation.php`

It is forward-only. Rollback remains unauthorized.

Migrations #1 through #19 remain preserved.

No migration is executed or applied by source publication.

## Explicit non-goals

JRN-008 does not add:

- general stock adjustment;
- receiving or purchasing;
- supplier lifecycle;
- transfers;
- stocktake/cycle count;
- shrinkage/damage/expiry;
- reservations;
- negative-stock policy;
- warehouse lifecycle;
- valuation/COGS;
- batch/lot/serial tracking;
- broad catalog CRUD;
- refund/return;
- JRN-010 close/reconciliation;
- accounting;
- external provider behavior;
- offline inventory mutation.

## Lifecycle locks

The following remain unauthorized:

- migration execution/application/activation;
- Technical Preview activation;
- Production activation;
- deployment;
- release;
- updater activation;
- rollback.

Source publication never implies lifecycle activation.

Attribution: **Lab | zefry**
