# JRN-007 POS Completed-Sale Void Foundation

Author by Lab | zefry

## Status

**SOURCE-PUBLISHED FOUNDATION / LOCAL-TEST-CI ONLY / MIGRATION #19 SOURCE ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

This document describes the bounded Sprint50 implementation of JRN-007 controlled full void for an already-completed canonical JRN-006 sale.

It does not activate Technical Preview, Production, deployment, release, updater, migration execution, schema application, or rollback.

## Selected semantic

Sprint50 implements exactly one correction semantic: **full void of one completed sale**.

The implementation does not combine cart cancellation, partial void, return, exchange, refund, provider reversal, chargeback, or settlement reversal.

The original completed sale remains immutable historical evidence.

## Authorization

The bounded permission is \`pos.sale.void\`.

It is deny-by-default and receives no default grant.

\`pos.sale.complete\`, \`pos.catalog.prepare\`, and \`pos.shift.open\` do not imply void authority.

The application service reconstructs the current POS execution context exclusively from verified server-owned organizational context and requires the void permission before durable mutation.

## Caller boundary

The HTTP request accepts exactly \`operation_id\` and \`sale_id\`.

The caller cannot provide or override actor, tenant, organization, outlet, device, register, shift, role, permission, session authority, money, tender category, stock quantity, currency, correction state, correlation identity, provider state, or settlement evidence.

No arbitrary reason text is accepted in this foundation.

## Target eligibility

The target sale is resolved inside the current server-derived tenant and must also match the current verified organization and outlet.

The current correction device does not need to equal the original sale device. This permits an independently authorized operator at the same outlet to correct a sale from another verified device while preserving both original sale device evidence and current correction device evidence.

Cross-tenant, cross-organization, and cross-outlet targets fail closed.

## Shift relationship

A completed-sale void does not require the original shift or a current shift to remain active.

The operation never creates, closes, reopens, reassigns, or mutates a shift.

JRN-005 opening evidence and Sprint49 active-shift sale-completion semantics remain independently governed.

## Migration #19 source

Sprint50 adds \`apps/web/database/migrations/0000_00_00_000019_create_pos_sale_void_foundation.php\`.

The migration creates exactly one durable correction table: \`oneqay_pos_sale_voids\`.

The table records server-owned correction identity, idempotency fingerprint, immutable target sale reference, current authorized actor/context, derived reversal evidence, correlation identity, and server-owned occurrence time.

Durable constraints include primary \`tenant_id + void_id\`, unique \`tenant_id + operation_id\`, unique \`tenant_id + sale_id\`, and tenant-bound restrictive foreign keys.

The migration is forward-only.

Migration #19 is source-published only. It is not executed, applied, or activated by source publication.

Migrations #1 through #18 remain byte-preserved.

## Idempotency

The durable idempotency boundary is \`tenant_id + operation_id\`.

The semantic fingerprint binds current actor, tenant, organization, outlet, device, and target sale id. Correlation identity and server time are non-semantic.

Exact replay returns the original durable void result and does not repeat stock restoration, correction-row insertion, or the \`VOIDED\` event.

Conflicting operation-id reuse fails closed. A different operation targeting an already-void sale also fails closed.

Database uniqueness is the final concurrency arbiter.

## Original evidence immutability

The implementation never updates or deletes the original rows in \`oneqay_pos_sales\` or \`oneqay_pos_sale_lines\`.

Original actor, organization, outlet, device, operation identity, payload fingerprint, price, quantity, tender category, total, applied amount, change, correlation identity, and completion time remain unchanged.

A later JRN-006 exact replay therefore still returns the original completed-sale receipt even when an additional JRN-007 void exists.

## Stock compensation

Stock restoration is derived only from immutable original sale lines. The caller never supplies quantity.

For each original product, the implementation aggregates the sold quantity, locks the tenant/outlet catalog row, permits restoration when that row is inactive, requires the row to exist, rejects unsupported integer overflow, and increments the exact original quantity.

Missing catalog evidence or overflow fails the whole transaction. Partial restoration is not allowed.

## Financial correction evidence

The internal reversal amount is derived from immutable original \`applied_atomic\`.

The correction preserves original currency, scale, and tender category. The bounded evidence mode is \`FULL_SALE_VOID\`.

This foundation does not send money, move a cash drawer, call a payment provider, reverse a bank/card settlement, or implement refund semantics.

## Event evidence

A successful first-time void adds exactly one immutable \`VOIDED\` sale event.

The event references the original sale and records the JRN-007 operation id, current authorized actor, server-owned correlation identity, and server-owned occurrence time.

Exact replay does not add another \`VOIDED\` event. Existing JRN-006 \`COMPLETED\` and \`REPLAYED\` events remain unchanged.

## Runtime boundary

The feature flag is \`ONEQAY_POS_SALE_VOID_ENABLED\` and canonical configuration is \`oneqay.pos_sale_void.enabled\`. The default is \`false\`.

The route is \`POST /pos/sales/void\`.

It remains available only when Local/Test/CI runtime, first-party session control, sale-completion foundation, and the void feature are explicitly armed. It requires active session middleware, bounded mutation throttling, and the canonical POS verified-context middleware.

Technical Preview and Production are not activated.

## Explicit non-scope

This foundation does not implement JRN-010, shift close, cash count, cash variance, drawer movement, provider settlement, refund, partial return, exchange, chargeback, purchasing, supplier lifecycle, accounting general-ledger posting, Technical Preview activation, Production activation, deployment, release, updater activation, migration execution/application, schema activation, or rollback.

Attribution: **Lab | zefry**
