# JRN-004 POS Catalog Price / Sellability Preparation Foundation

Author by Lab | zefry

## Status

BOUNDED SOURCE IMPLEMENTATION / LOCAL-TEST-CI ONLY / SOURCE-DEFAULT DISABLED

## Scope

This foundation implements only Sprint47 JRN-004 tenant/outlet-scoped preparation of current catalog display name, current unit price, and sellability for the already-published JRN-006 sale-completion path.

It is not broad catalog CRUD, a pricing engine, inventory administration, tax/fiscal policy, promotion management, purchasing, supplier management, or external API publication.

## Security boundary

Execution authority is reconstructed from the current first-party server session and canonical organizational context.

The caller cannot select tenant, organization, outlet, actor identity, device, role, permission, framework session authority, stock quantity, mutation identity, event time, or correlation identity.

The permission `pos.catalog.prepare` is required through the canonical durable scoped authorization policy. No default grant is created.

## Current catalog state

Canonical migration #16 remains the owner of current sale catalog state.

JRN-004 may change only:

- display name;
- current unit price atomic units;
- currency;
- currency scale;
- sellability through the existing `active` field.

The existing `available_quantity` remains server-owned stock state.

Existing-item preparation preserves `available_quantity` exactly.

New-item preparation initializes `available_quantity` to zero. The caller cannot override this value.

A sellable item with zero stock remains unavailable to JRN-006 until a separately authorized inventory concern changes stock.

## Durable journal and idempotency

Migration #17 adds only `oneqay_pos_catalog_preparation_journal`.

The journal records exact verified context, operation identity, semantic fingerprint, product reference, immutable before/after catalog state, correlation identity, and server event time.

Exact `tenant_id + operation_id` is the durable idempotency key.

An exact replay:

- returns the originally applied after-state;
- does not rewrite current catalog state;
- does not create a second journal row;
- does not alter stock.

A conflicting replay fails closed.

The semantic fingerprint binds actor/context plus the exact business mutation fields. Correlation identity is evidence, not part of mutation semantics.

## Transaction boundary

Catalog mutation runs inside the canonical `PersistenceTransaction`.

For a first operation, the repository locks the exact tenant+outlet+product current row when present, snapshots before-state, applies only the bounded catalog fields, and writes one journal row in the same transaction.

Historical completed JRN-006 sale-line price snapshots are never recalculated or rewritten.

## Delivery boundary

`ONEQAY_POS_CATALOG_PREPARATION_ENABLED` defaults to false.

The route is created only when:

- runtime is Local/Test/CI;
- session control is enabled;
- JRN-006 sale completion is explicitly enabled, as required by the unchanged shared POS context middleware boundary;
- JRN-004 catalog preparation is explicitly enabled.

The route requires `session.active`, bounded throttling, and `RequirePosSessionContextMiddleware`.

Technical Preview and Production remain unactivated.

## Lifecycle locks

Migration #16 remains source-published but unexecuted.

Migration #17 is source-published by this implementation only if this source PR becomes canonical; it is not executed, applied, or activated by source publication.

Technical Preview remains **NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED**.

Production remains **NO-GO / NOT AUTHORIZED**.

Updater remains **DISABLED / UNWIRED**.

Deployment, release, migration execution, and rollback remain **NOT AUTHORIZED**.
