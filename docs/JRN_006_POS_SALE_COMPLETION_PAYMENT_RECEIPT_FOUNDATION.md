<!-- Sprint47 JRN-004 JRN-006 compatibility preservation anchor. -->
# JRN-006 POS Sale Completion / Payment Recording / Receipt Foundation

Author by Lab | zefry

## Sprint49 active-shift sale-completion precondition extension

Sprint49 adds only the bounded fail-closed active-shift precondition required by the canonical JRN-006 journey.

For a **fresh** sale-completion operation:

- the existing server-derived tenant, identity, organization, outlet, and device-backed register execution context remains authoritative;
- `pos.sale.complete` remains mandatory and no new permission is introduced;
- the existing durable `tenant_id + operation_id` lookup and semantic-fingerprint conflict check remain first;
- when no completed operation exists, the same persistence transaction must resolve an active `oneqay_pos_shifts` row for the exact `tenant_id + outlet_id + device_id` context with server-owned `active_slot = 1`;
- missing, cross-tenant, cross-outlet, or cross-device active-shift evidence fails closed before catalog, stock, sale, payment, line, or completion-event mutation;
- exact replay of an already-completed sale remains deterministic and does not require the shift to still be active;
- sale completion does not create, close, move, reopen, or otherwise mutate shift opening evidence.

Sprint49 is **NO_SCHEMA_CHANGE**. Migration #19 is not selected. No `shift_id` column is added to completed sales, and migrations #1 through #18 remain unchanged.

This extension does not authorize shift close, JRN-010 reconciliation, cash count/variance, register administration, provider reconciliation, deployment, release, updater activation, or migration execution.

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

<!-- Sprint48 JRN-005 Sprint46 compatibility preservation anchor. -->
