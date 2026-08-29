# Sprint46 Business MVP POS Core Transaction & Outlet Operations Entry Gate

Author by Lab | zefry

## Status

`BUSINESS MVP TRANSITION ENTRY GATE ONLY / BUSINESS SOURCE NOT AUTHORIZED / NO_SCHEMA_CHANGE`

## Canonical predecessor

This bounded Sprint46 entry gate starts from canonical main `4f8067b9fa537a73020d72c65f43b1e218e0a8d3`, tree `9d95bf2cefd3899048b2d68f757593cd995e00cf`, after completed Sprint45 source publication/reconciliation and the exact historical-workflow compatibility predecessor for this one-path Sprint46 gate.

Sprint45 closed the pending-MFA identity-eligibility revalidation foundation while preserving deny-by-default tenant isolation, fresh-authentication re-entry, session no-resurrection, and exact-head lifecycle controls.

## Sprint46 classification

Sprint46 is classified as:

**BUSINESS MVP ENTRY / FIRST BUSINESS VERTICAL SLICE**

This classification does not declare oneQay Business MVP complete, does not approve Production readiness, and does not approve Phase 0 Exit by itself.

## Product evidence for Business MVP transition

Canonical DEC-001 already approved the first bounded MVP delivery direction as:

**POS CORE TRANSACTION & OUTLET OPERATIONS**

DEC-001 preserves oneQay as an Enterprise Intelligent Business Management Platform and defines this only as the first bounded, testable, value-producing delivery slice.

The approved MVP journeys include catalog/price/availability preparation, shift/register opening, sale/payment/receipt, controlled cancellation/void/return/refund, and shift close/reconciliation, with bounded tenant/outlet/access/inventory/reporting dependencies.

The current repository also already contains a bounded M7.4 synthetic POS foundation. The canonical application path derives POS execution context from verified server-owned organizational context, including exact tenant, organization, outlet, device, and actor identity. The synthetic sale command already uses stable operation and correlation identifiers, and the synthetic regression proves exact money/cart/tenant/outlet transaction behavior without granting durable Business MVP persistence authority.

Those facts are sufficient to open a formal Business MVP transition gate. They are not sufficient to mutate broad business source without a separate schema/source-envelope gate.

## Exactly one first bounded source vertical

The first Business MVP source vertical selected by this gate is exactly:

**JRN-006 — Tenant/Outlet-Scoped Sale Completion, Payment Recording, and Receipt Foundation**

The future bounded source stage may implement only the minimum end-to-end business path needed to:

1. accept a server-authorized sale command for the exact authenticated tenant + organization + outlet + device context;
2. validate the cart and exact monetary semantics;
3. record one bounded payment category without external payment-provider integration;
4. produce a deterministic receipt/result;
5. preserve idempotent or replay-safe operation semantics where required;
6. preserve tenant/outlet isolation and deny cross-tenant or caller-selected authority;
7. preserve transactional integrity between sale completion and any bounded stock effect that the schema/source gate explicitly authorizes;
8. emit only the minimum auditable business evidence required by the bounded source design.

The existing synthetic M7.4 source is evidence and a compatibility predecessor only. It must not be relabeled as durable Business MVP implementation without a separately qualified source stage.

## Explicitly not selected in this Sprint46 source vertical

The following remain outside the selected first source vertical:

- JRN-004 catalog administration beyond the minimum read-side product evidence required by the selected sale path;
- JRN-005 shift/register opening;
- JRN-007 cancellation, void, return, or refund;
- JRN-010 shift close and operational reconciliation;
- broad inventory management or stock-adjustment administration;
- purchasing and supplier lifecycle;
- customer/CRM expansion;
- accounting/ERP posting;
- external payment-provider integration;
- offline POS;
- public API;
- mobile-native implementation;
- broad reporting or Business Intelligence;
- multi-domain implementation in one Sprint.

No catalog + inventory + sales + purchasing + payments + accounting big bang is authorized.

## Required security and transaction invariants

Any later bounded source implementation must preserve:

- exact server-derived tenant, identity, organization, outlet, and device context;
- deny-by-default authorization;
- no caller-selected tenant, role, permission, session, outlet, or device authority;
- cross-tenant denial;
- deterministic money semantics;
- stable operation/correlation identity;
- replay-safe/idempotent behavior where materially applicable;
- transactional integrity;
- auditable, secret-free business evidence;
- current authentication/session eligibility controls from Sprint40–Sprint45;
- no resurrection of revoked or historical authority;
- no weakening of protected-control or privileged-MFA boundaries.

## Schema decision

This entry gate remains:

**NO_SCHEMA_CHANGE**

Migration #16 remains:

**NOT SELECTED**

A later schema/source-envelope gate must determine from the exact current source whether the selected JRN-006 durable business vertical can remain schema-free or requires one separately bounded migration. No migration file may be added or executed by this entry gate.

## Required next bounded gate

Before any Sprint46 business source mutation, publish and qualify a separately bounded:

**Sprint46 JRN-006 Sale Completion / Payment Recording / Receipt Schema & Source Envelope Gate**

That gate must:

- identify the exact source paths;
- freeze the sorted newline-terminated changed-path envelope and SHA-256;
- determine the schema requirement without assuming migration #16;
- preserve M7.4 POS transaction invariants;
- preserve current tenant/organization/outlet/device and authentication/session controls;
- define negative and cross-tenant tests;
- identify all materially triggered historical workflows;
- keep Preview/Production lifecycle locks unchanged.

## Entry-gate envelope

This entry gate changes exactly one path:

```text
docs/SPRINT_46_BUSINESS_MVP_POS_CORE_TRANSACTION_OUTLET_OPERATIONS_ENTRY_GATE.md
```

Sorted newline-terminated SHA-256:

`88f7f342b07619929e82a5f7f057c8354471e8a02347d4d9d66331cae9f123c0`

No other path is authorized by this entry gate.

## Lifecycle locks

Technical Preview remains **NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED**.

Migration #15 remains **NOT ACTIVATED / NOT APPLIED in Technical Preview**.

Sprint42, Sprint43, Sprint44, and Sprint45 source remain **NOT ACTIVATED in Technical Preview**.

Production remains **NO-GO / NOT AUTHORIZED**.

Updater remains **DISABLED / UNWIRED**.

Deployment, release, migration execution, and rollback remain **NOT AUTHORIZED**.

## Result

Sprint46 formally opens the bounded Business MVP transition and selects exactly one first business source vertical: **JRN-006 Tenant/Outlet-Scoped Sale Completion, Payment Recording, and Receipt Foundation**.

This gate creates no business source, schema, runtime, deployment, release, updater, Preview, Production, or migration-execution authority.
