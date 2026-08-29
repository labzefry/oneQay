# JRN-006 POS Sale Completion / Payment Recording / Receipt Foundation

Author by Lab | zefry

## Status

BOUNDED SOURCE IMPLEMENTATION / LOCAL-TEST-CI ONLY / SOURCE-DEFAULT DISABLED

## Scope

This foundation implements only JRN-006 tenant/outlet-scoped sale completion, bounded payment recording, and deterministic receipt evidence.

It is not the complete POS MVP and does not implement catalog administration, shift/register lifecycle, void/refund/return, purchasing, supplier lifecycle, CRM, accounting, external payment providers, offline POS, or broad reporting.

## Security boundary

Execution authority is derived from the current first-party server session and rebuilt as an exact verified tenant, identity, organization, outlet, and device context.

The caller cannot select tenant, organization, outlet, device, actor, role, permission, session authority, product price, stock quantity, or sale identity.

The permission pos.sale.complete is required through the existing durable scoped authorization policy. No default grant is created.

## Durable transaction boundary

Migration #16 adds only the minimal server-owned sale catalog/availability state plus completed sale, immutable line snapshot, payment/receipt evidence, and sale-event evidence.

Exact tenant_id + operation_id is the durable idempotency key. The semantic fingerprint binds actor/context, cart, tender category, and tendered amount. Exact replay returns the original receipt and does not decrement stock again; conflicting replay fails closed.

Catalog lookup and stock mutation are always tenant+outlet scoped. Price and availability are read from durable server-owned state. Sale, lines, stock decrement, and completion event execute under the canonical persistence transaction.

## Runtime boundary

ONEQAY_POS_SALE_COMPLETION_ENABLED defaults to false.

The HTTP route is created only for Local/Test/CI when session control is enabled and the JRN-006 feature is explicitly armed. Technical Preview and Production remain unactivated.

Migration #16 is source-published only. Migration execution remains separately governed and unauthorized by this foundation.

## Lifecycle locks

Technical Preview remains NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED.

Production remains NO-GO / NOT AUTHORIZED.

Updater remains DISABLED / UNWIRED.

Deployment, release, migration execution, and rollback remain NOT AUTHORIZED.
