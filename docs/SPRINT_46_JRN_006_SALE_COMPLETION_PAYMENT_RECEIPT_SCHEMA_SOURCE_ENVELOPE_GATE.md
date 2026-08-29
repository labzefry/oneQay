# Sprint46 JRN-006 Sale Completion / Payment Recording / Receipt Schema & Source Envelope Gate

Author by Lab | zefry

## Status

`SCHEMA / SOURCE ENVELOPE GATE ONLY / SOURCE NOT YET IMPLEMENTED / MIGRATION_16_SELECTED_NOT_EXECUTED`

## Canonical predecessor

This bounded gate starts from canonical main `b9fce4699eb1fc9e40e6117b741ce2004138b440`, tree `4ff63547a9bfd3dd570b4cd375d9c2ba10be04f0`, after the qualified Sprint46 Business MVP transition entry gate and the exact historical-workflow compatibility predecessor for this schema/source gate.

The selected first Business MVP source vertical remains exactly:

**JRN-006 — Tenant/Outlet-Scoped Sale Completion, Payment Recording, and Receipt Foundation**

No other business domain is selected by this gate.

## Live source finding

Canonical POS source before this gate is still the bounded M7.4 synthetic foundation:

- domain money, cart, catalog-item, tender, sale-line, and receipt primitives exist;
- `CompleteSyntheticSale` derives execution authority from verified organizational context;
- `InMemorySyntheticPosStore` proves deterministic cart/money validation, tenant+outlet catalog lookup, stock sufficiency, payment evidence, idempotent operation replay, and atomic in-memory rollback behavior;
- the repository has no durable POS repository implementation and no persistent POS table;
- canonical migrations are exactly #1 through #15.

Therefore a durable JRN-006 implementation cannot remain `NO_SCHEMA_CHANGE`. Accepting price, stock, tenant, outlet, actor, or other authority-bearing business state from the caller would violate server-authoritative and deny-by-default requirements.

## Schema decision

This dedicated gate selects exactly one new source migration:

```text
apps/web/database/migrations/0000_00_00_000016_create_pos_sale_completion_foundation.php
```

Migration #16 is **SELECTED FOR THE FUTURE BOUNDED SOURCE IMPLEMENTATION ONLY**.

It is **NOT EXECUTED** by this gate.

It does not authorize Technical Preview schema application, Production schema application, deployment, release, updater wiring, rollback, or any direct database mutation.

Migrations #1 through #15 remain immutable.

## Bounded migration #16 schema

Migration #16 may create only the minimum durable structures required by JRN-006:

1. a tenant+outlet scoped server-owned POS sale-catalog/availability table sufficient to provide canonical product identity, display evidence, unit price, currency/scale, active state, and bounded available quantity;
2. a tenant-scoped completed-sale table containing exact actor, organization, outlet, device, operation ID, deterministic payload fingerprint, monetary totals, tender/evidence mode, correlation ID, and completion time;
3. immutable sale-line snapshots for the exact completed sale;
4. minimal sale-event/audit evidence for completed and exact replay outcomes.

The schema must enforce tenant-scoped keys and foreign-key relationships to canonical tenant/identity/organization/outlet/device context where materially applicable.

The unique idempotency boundary must be at least exact `tenant_id + operation_id`. Sale-line and audit evidence must be subordinate to the exact tenant+sale identity.

No schema for catalog administration, purchasing, supplier settlement, shift/register lifecycle, refund/void/return, customer CRM, accounting posting, external payment providers, offline synchronization, or broad reporting is authorized.

## Authorization decision

JRN-006 must remain deny-by-default.

The source implementation may add one application permission identifier:

`pos.sale.complete`

The permission must be evaluated through the existing durable scoped role/permission policy against the verified tenant/organization/outlet/device context.

No default grant, implicit cashier role, caller-selected role, permission bypass, protected-control bypass, or cross-tenant grant is authorized.

## Context and delivery boundary

The source implementation may expose one Local/Test/CI-only sale-completion HTTP boundary.

The request may contain only bounded transaction data required to form the sale command, such as operation ID, product IDs with quantities, tender category/amount, and non-authority monetary format evidence where required.

The request must not accept tenant ID, organization ID, outlet ID, device ID, actor identity, role, permission, session authority ID, price, stock quantity, or sale ID as caller authority.

The canonical correlation ID must remain server-derived.

The delivery path must compose current first-party session authority and rebuild exact verified organizational context before the application sale service is called.

The route must be source-default disabled and restricted to Local/Test/CI. Technical Preview and Production remain unarmed.

## Transaction and idempotency invariants

The durable source implementation must:

- derive exact actor/tenant/organization/outlet/device execution context from server-verified state;
- require `pos.sale.complete`;
- resolve canonical product/price/availability from server-owned storage for the exact tenant+outlet;
- reject missing, inactive, cross-tenant, malformed, currency-incompatible, or insufficient-stock catalog evidence;
- perform integer-safe money arithmetic using existing Domain POS primitives;
- preserve the existing CASH and MANUAL_EXTERNAL tender semantics without external provider integration;
- bind idempotency to exact tenant + operation ID + deterministic semantic fingerprint;
- return the same canonical receipt for exact replay;
- fail closed on same operation ID with a different semantic fingerprint;
- atomically decrement only the exact tenant+outlet stock rows and persist sale, line, payment/receipt evidence, and completion audit;
- prevent partial sale/stock/payment persistence on failure or contention;
- produce no authority from caller-selected tenant/outlet/device/session fields.

## Frozen future source envelope

The eventual Sprint46 JRN-006 source implementation is bounded to exactly these 14 paths:

```text
.github/workflows/sprint46-jrn006-durable-sale-completion-regression.yml
apps/web/app/Application/Authorization/PosPermission.php
apps/web/app/Application/Pos/CompleteSale.php
apps/web/app/Application/Pos/DurablePosSaleRepository.php
apps/web/app/Application/Pos/PosSaleClock.php
apps/web/app/Delivery/Http/Middleware/RequirePosSessionContextMiddleware.php
apps/web/app/Delivery/Http/Pos/PosSaleController.php
apps/web/app/Infrastructure/Pos/LaravelDurablePosSaleRepository.php
apps/web/app/Providers/AppServiceProvider.php
apps/web/config/oneqay.php
apps/web/database/migrations/0000_00_00_000016_create_pos_sale_completion_foundation.php
apps/web/routes/web.php
apps/web/tests/pos-sale-completion-durable.php
docs/JRN_006_POS_SALE_COMPLETION_PAYMENT_RECEIPT_FOUNDATION.md
```

Sorted newline-terminated SHA-256:

`ed29b6128c193f0efd6359748e220a37aefaec856acc4bc3b90f445ce3ccb674`

No source path outside this exact envelope is authorized by this gate except a separately qualified compatibility predecessor if repository-native historical workflows require exact successor recognition.

Existing M7.4 POS Domain primitives, `SaleCommand`, `PosExecutionContext`, `SaleReceipt`, `InMemorySyntheticPosStore`, Preview controllers/pages, identity/session services, role-permission repository implementation, and previous migration files must remain unchanged.

## Required regression proof

The dedicated source regression must prove at minimum:

- exact 14-path source envelope and fingerprint;
- canonical migrations #1–#15 unchanged and exactly one migration #16 added;
- migration #16 is forward-only and not executed in Technical Preview or Production by repository source;
- source-default JRN-006 delivery is disabled;
- runtime authorization is Local/Test/CI only;
- exact active first-party session and exact verified tenant/organization/outlet/device context are mandatory;
- `pos.sale.complete` is deny-by-default and required;
- request tenant/outlet/device/actor/role/permission/session authority cannot be selected by caller input;
- canonical product price and availability come only from server-owned durable storage;
- cross-tenant and cross-outlet product borrowing is denied;
- exact replay returns the original sale result without a second stock decrement;
- conflicting replay fails closed;
- insufficient stock and concurrent stock contention have at most one valid effect;
- sale, line, payment/receipt evidence, stock decrement, and completion audit are atomic;
- malformed money/cart/tender/currency evidence fails closed;
- M7.4 synthetic regression remains green;
- current Sprint40–Sprint45 identity eligibility, session revocation/lifetime, organizational-access, reactivation, fresh-authentication, and pending-MFA controls remain preserved;
- full application regression remains green under canonical default feature flags;
- lifecycle locks remain unchanged.

## Explicit non-goals

This gate does not authorize:

- JRN-004 catalog administration;
- JRN-005 shift/register opening;
- JRN-007 cancellation, void, return, or refund;
- JRN-010 shift close/reconciliation;
- broad inventory administration;
- purchasing or supplier lifecycle;
- customer/CRM expansion;
- accounting or ERP posting;
- external payment-provider integration;
- offline POS;
- public API or mobile-native delivery;
- Technical Preview activation;
- Production activation;
- migration execution;
- updater wiring;
- deployment, release, or rollback.

## Gate envelope

This gate itself changes exactly one path:

```text
docs/SPRINT_46_JRN_006_SALE_COMPLETION_PAYMENT_RECEIPT_SCHEMA_SOURCE_ENVELOPE_GATE.md
```

Sorted newline-terminated SHA-256:

`33959c9b56089ae6d8272acebdac93a03b82c5498bc689d1d7542ab4297e10cd`

## Lifecycle locks

Technical Preview remains **NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED**.

Migration #15 remains **NOT ACTIVATED / NOT APPLIED in Technical Preview**.

Migration #16 is **SELECTED IN SOURCE DESIGN ONLY / NOT CREATED BY THIS GATE / NOT EXECUTED**.

Sprint42, Sprint43, Sprint44, and Sprint45 source remain **NOT ACTIVATED in Technical Preview**.

Production remains **NO-GO / NOT AUTHORIZED**.

Updater remains **DISABLED / UNWIRED**.

Deployment, release, migration execution, and rollback remain **NOT AUTHORIZED**.

## Result

This gate selects migration #16 for the bounded JRN-006 durable source stage and freezes an exact 14-path source envelope. It creates no runtime, Preview, Production, deployment, release, updater, migration-execution, or rollback authority.
