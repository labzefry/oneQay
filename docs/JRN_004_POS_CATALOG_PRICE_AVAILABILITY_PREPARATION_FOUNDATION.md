# JRN-004 POS Catalog Price / Sellability Preparation Foundation

Author by Lab | zefry

## Status

BOUNDED SOURCE IMPLEMENTATION / LOCAL-TEST-CI ONLY / SOURCE-DEFAULT DISABLED

## Scope

This foundation implements only Sprint47 JRN-004 tenant/outlet-scoped catalog preparation for the bounded current sale catalog used by JRN-006.

Allowed mutation state is limited to:

- product identifier;
- display name;
- current unit price;
- currency and scale;
- current sellability.

This source does not implement stock receiving, stock movement, stock adjustment, inventory count, reservation, warehouse management, price books, promotions, discounts, broad catalog CRUD, item deletion, or external publication.

## Security boundary

Execution authority is reconstructed from the current first-party server session through the canonical verified POS organizational context.

The caller cannot select tenant, organization, outlet, device, actor, role, permission, session authority, stock quantity, mutation identity, correlation identity, or event time.

The permission `pos.catalog.prepare` is required through the canonical durable scoped authorization policy. No default grant is created and `pos.sale.complete` does not imply catalog preparation authority.

## Current catalog state

The implementation reuses canonical `oneqay_pos_sale_catalog_items` from migration #16.

Existing rows may update only:

- `display_name`;
- `unit_price_atomic`;
- `currency`;
- `currency_scale`;
- `active` as sellability.

Existing `available_quantity` is never changed by JRN-004.

A newly prepared catalog row receives server-owned `available_quantity = 0`. Sellability remains distinct from stock availability.

## Durable idempotency and journal evidence

Migration #17 adds only `oneqay_pos_catalog_preparation_journal`.

The durable idempotency key is `tenant_id + operation_id`. The semantic fingerprint binds the verified actor/tenant/organization/outlet/device context plus product id, display name, unit price, currency/scale, and sellability.

Exact replay returns the originally applied after-state without rewriting the current catalog row and without inserting a second journal record.

A conflicting replay fails closed.

The journal records immutable before/after state, verified execution context, server correlation identity, and server mutation time.

A replay of an older operation after a later catalog mutation returns the older operation's recorded result but never restores stale state.

## Historical sale integrity

JRN-004 never rewrites completed sales or sale lines. Existing JRN-006 line price snapshots remain immutable after later catalog preparation.

## Runtime boundary

`ONEQAY_POS_CATALOG_PREPARATION_ENABLED` defaults to false.

The route is created only for Local/Test/CI while canonical session control and the existing verified POS session middleware are active. The middleware remains coupled to the canonical POS sale-completion runtime guard, so the source route is never independently widened beyond the established POS runtime boundary.

Technical Preview and Production remain unactivated.

## Migration boundary

Migration #16 remains immutable and source-published but unexecuted.

Migration #17 is source-published by this bounded implementation only if the source PR becomes canonical. It is not executed, applied, or activated by source publication.

`down()` remains fail-closed; rollback is not authorized.

## Lifecycle locks

Technical Preview remains **NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED**.

Migration #15 remains **NOT APPLIED / NOT ACTIVATED IN TECHNICAL PREVIEW**.

Migration #16 remains **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**.

Migration #17 remains **SOURCE-ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**.

Production remains **NO-GO / NOT AUTHORIZED**.

Updater remains **DISABLED / UNWIRED**.

Deployment, release, migration execution, and rollback remain **NOT AUTHORIZED**.

<!-- Sprint48 JRN-005 Sprint47 JRN-006 compatibility preservation anchor. -->
