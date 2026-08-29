# Sprint47 JRN-004 Catalog, Price, and Availability Preparation Entry Gate

> Status: SELECTED / ENTRY GATE ONLY
>
> Product: oneQay — The Future of Intelligent Business Management
>
> Repository: `labzefry/oneQay`
>
> Product Owner: Lab | zefry
>
> Attribution: Lab | zefry

## Canonical predecessor

Sprint47 begins only after canonical Sprint46 JRN-006 source publication and
post-source documentation reconciliation.

Canonical predecessor at selection time:

- main: `f234a27060055fc2e54eb08d0a4e1c8ea6942826`;
- tree: `f18da557dc1b1bc214dfeb58cf07280246f48a1d`;
- Sprint46 JRN-006 source publication: PR #419 /
  `437e463d4e862b1a1ba26cd500ea7ec23e352878`;
- post-source migration-horizon compatibility: PR #421 /
  `f705cf21e391d96b1c147a4194569629254ff607`;
- canonical post-Sprint46 documentation reconciliation: PR #422 /
  `f234a27060055fc2e54eb08d0a4e1c8ea6942826`.

No closed, superseded, or unmerged probe is canonical authority.

## Product decision basis

DEC-001 approves the initial bounded MVP slice:

**POS CORE TRANSACTION & OUTLET OPERATIONS**.

Within that approved slice, JRN-004 is an approved primary MVP journey:

**Catalog, price, and availability preparation**.

Canonical product evidence also establishes:

- JRN-006 depends on a sellable item and server-authoritative price;
- JRN-006 now reads price and sellability from durable server-owned
  tenant+outlet catalog state;
- the current JRN-006 source intentionally provides no catalog-administration
  surface;
- catalog availability intent and physical/on-hand stock are distinct domain
  concepts and must not be silently conflated;
- tax/fiscal jurisdiction remains unresolved and must not be invented through a
  catalog implementation;
- inventory reservation, stock movement, stock adjustment, and concurrency
  policy remain separately governed.

## Sprint47 selected concern

Sprint47 selects the next bounded business-MVP concern as:

**JRN-004 — Tenant/Outlet-Scoped Catalog Sellability and Current Price
Preparation Foundation**.

This is selected because the already-published JRN-006 sale path consumes
server-owned catalog price and sellability evidence, while canonical source
does not yet provide an authorized business operation for preparing that
state.

The selected concern closes that immediate operational dependency without
expanding into the complete catalog, inventory, tax, promotion, or pricing
platform.

## Bounded business intent

A future source stage may prepare one tenant/outlet catalog item for use by the
already-published JRN-006 sale-completion path.

The bounded business intent is limited to:

- server-authorized creation or preparation of a tenant/outlet sellable item
  identity;
- server-authorized current display-name preparation;
- server-authorized current unit-price preparation with explicit currency and
  currency scale;
- server-authorized sellability/availability-intent state;
- deterministic mutation identity and replay behavior;
- immutable or append-only audit evidence sufficient to explain who changed
  catalog sellability or current price and when;
- exact tenant+outlet isolation;
- deny-by-default authorization;
- source-default-disabled Local/Test/CI delivery unless a later gate states
  otherwise.

## Mandatory semantic separation

Sprint47 must preserve these distinctions:

### Sellability is not stock quantity

The JRN-004 availability boundary selected here means whether an item is
currently eligible to be offered for sale in the exact tenant/outlet context.

It does not authorize arbitrary caller mutation of physical or logical
on-hand quantity.

The existing `available_quantity` used by JRN-006 remains server-owned stock
state. A future JRN-004 implementation must not expose that field as a generic
catalog administration input unless a separately bounded inventory gate
explicitly authorizes the stock semantics.

### Current price is not a complete pricing engine

This Sprint47 concern may prepare only the current authoritative unit price
required by the existing bounded sale flow.

It does not approve:

- scheduled/future price activation;
- overlapping price windows;
- promotions;
- discount policy;
- customer-specific pricing;
- price books;
- wholesale tiers;
- dynamic pricing;
- tax-inclusive/exclusive policy;
- fiscal price rules;
- cross-outlet price inheritance.

Those require separate evidence and authority if later selected.

### Catalog preparation is not inventory administration

This concern does not approve:

- receiving;
- stock movement;
- stock transfer;
- stock count;
- stock adjustment;
- reservation;
- negative-stock policy;
- warehouse management;
- purchase order or supplier behavior.

Those remain inside JRN-008/JRN-009 or another separately bounded successor.

## Security and authority invariants

Any future Sprint47 source must remain fail closed.

The caller must not be allowed to select or override:

- tenant authority;
- organization authority;
- outlet authority;
- actor identity;
- role;
- permission;
- framework or logical session authority;
- protected-control status;
- cross-tenant resource ownership.

Exact tenant, identity, organization, outlet, device, and relevant session
authority must be reconstructed from canonical server-side first-party
session evidence before the business mutation is attempted.

Unknown, missing, stale, malformed, cross-tenant, or unauthorized context must
be denied.

No default permission grant may be created.

## Data and audit boundary

Catalog mutation must not silently rewrite the meaning of historical completed
sales.

Completed JRN-006 sale lines retain their immutable price snapshots and must not
be recalculated when catalog state later changes.

Any future mutable catalog representation must preserve enough evidence to
determine:

- exact tenant;
- exact outlet;
- exact catalog item/product reference;
- actor identity;
- operation/mutation identity;
- before/after business-relevant state;
- correlation identity;
- event time;
- replay/conflict disposition.

Secrets, credentials, session handles, payment secrets, and unnecessary
personal data must not be stored in catalog audit evidence.

## Schema decision boundary

This entry gate does **not** select migration #17.

Canonical source already contains the migration #16
`oneqay_pos_sale_catalog_items` table, but the entry gate does not assume
that this representation is sufficient for secure durable catalog mutation,
history, idempotency, or auditability.

The next bounded schema/source-envelope gate must determine one of exactly
these outcomes from live canonical evidence:

1. **NO_SCHEMA_CHANGE** — existing canonical state is sufficient without
   weakening auditability, idempotency, tenant isolation, or historical sale
   integrity; or
2. **MIGRATION #17 SELECTED IN SOURCE DESIGN ONLY** — a minimal forward-only
   schema addition is required.

Even if migration #17 is selected later, migration execution remains
separately unauthorized.

## Explicit non-scope

Sprint47 entry authority does not authorize:

- JRN-005 shift/register opening;
- JRN-007 cancellation, void, return, or refund;
- JRN-008 inventory movement/count/adjustment;
- JRN-010 shift close or reconciliation;
- tax or fiscal rule implementation;
- payment-provider integration;
- offline POS;
- external API publication;
- broad catalog administration;
- broad inventory management;
- migration execution;
- Technical Preview activation;
- Production activation;
- deployment;
- release;
- updater activation or wiring;
- rollback.

## Lifecycle locks

The following remain unchanged:

- Technical Preview: **NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED**;
- migration #15: **NOT APPLIED / NOT ACTIVATED IN TECHNICAL PREVIEW**;
- migration #16: **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED /
  NOT ACTIVATED**;
- migration #17: **NOT SELECTED** by this entry gate;
- Production: **NO-GO / NOT AUTHORIZED**;
- updater: **DISABLED / UNWIRED**;
- deployment: **NOT AUTHORIZED**;
- release: **NOT AUTHORIZED**;
- migration execution: **NOT AUTHORIZED**;
- rollback: **NOT AUTHORIZED**.

## Entry-gate completion criterion

This entry gate is complete only when it is published through the normal
exact-head lifecycle.

After publication, the next permitted bounded stage is targeted live
inspection for the JRN-004 schema/source-envelope decision.

No application source implementation is authorized by this entry gate.

## Frozen entry-gate path

This gate changes exactly one path:

`docs/SPRINT_47_JRN_004_CATALOG_PRICE_AVAILABILITY_PREPARATION_ENTRY_GATE.md`

Sorted newline-terminated path SHA-256:

`745a9d58ec096946dd72c5ee951d88bc15014a7b7aa983fcd0fcf7301e75a40f`

Unknown changed-file shapes remain fail closed.

Attribution: **Lab | zefry**
