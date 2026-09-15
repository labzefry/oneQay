# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, module-owned schema, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed **engineering** sprint is **Sprint167**.

- Canonical engineering commit: `dbdf0aa90a6d6127cf10113ec8c1092b8780504f`
- Engineering PR: #752 — `Sprint167: add POS active shift performance workspace`
- Final engineering head: `05047a7f04650b52c4035e4ca40171609fa8db10`
- Complete surfaced exact-head PR-triggered qualification: successful
- Sprint167 regression run `34919045197`: successful
- M7.1 run `34919045660`: successful
- Governance Required Checks run `34919045650`: successful
- PHP Foundation Regression run `34919045018`: successful
- Repository-native Product Owner merge authorization: `success` on the exact engineering head
- Sprint167 engineering envelope: 12 paths, SHA-256 `e9ca990e36ce896428bc749d19cf47025ce0525fd707f5e330d5d1cfbbfc8b48`
- Post-Sprint167 reconciliation envelope: six paths, SHA-256 `2fd60a53d8996d41452cb10036d350a14a4d95a2943e897c233d29f3eac32b14`
- Next engineering position: **Sprint168 bounded discovery**, with no preselected objective

For the full current project state, use **[`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md)** as the canonical human-readable source of truth.

## Sprint167 — POS active shift performance workspace

**Purpose / Why:** Outlet reporting did not explain the live exact-device shift, while cash-variance reconciliation focused only on closing-cash control. Sprint167 adds read-only current-shift visibility without introducing any new mutation or permission owner.

**Objective / Gap:** `POS_ACTIVE_SHIFT_PERFORMANCE_WORKSPACE`.

The workspace reads only the current exact-device `active_slot=1` shift and derives tender/currency performance from immutable shift-bound sales. It exposes completed, voided, active, and cash-refunded counts plus gross, full-void, active-net, and refunded-cash values. Active net equals gross minus full-sale void; CASH refund remains separate and is not deducted twice.

A missing active shift is a valid empty state. Legacy null-shift sales at/after shift opening, cross-scope evidence, malformed tender/evidence modes, inconsistent void/refund evidence, overflow, or more than 64 buckets fail closed.

Access reuses `pos.reporting.sales-summary.view`. Delivery remains default-false through `ONEQAY_POS_ACTIVE_SHIFT_PERFORMANCE_ENABLED`, Local/Test/CI + persistence + operational-reporting + exact-session gated, and discoverable from the Operations Hub only when `Route::has()` confirms delivery.

No migration, schema change, sale/shift/stock mutation, new permission, permission provisioning, global route/provider change, Final Shift Close operational execution, deployment, or updater activation was introduced.

## Product state through Sprint167

Material canonical progress includes tenant isolation, deny-by-default authorization, session/authentication foundations, API governance, exact-head CI/governance, repository-native Product Owner merge authorization, POS shift/register operations, durable sale/payment/receipt evidence, catalog/opening inventory, positive replenishment, inventory accountability, void/refund controls, cash variance/adjudication, operational reporting, cashier, shift start, sale corrections, immutable sale history, guarded POS navigation, product-level sales performance, and live active-shift performance.

## Operational status remains intentionally gated

| Gate | State |
| --- | --- |
| Migration #27 execution | `NOT_EXECUTED` |
| Permission provisioning | `NONE` |
| Durable activation target | `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET` |
| Selected target | `null` |
| Real capability evidence | absent |
| Real dependency-envelope evidence | absent |
| Final Shift Close runtime allowlist | Local/Test/CI only |
| Feature activation | `INACTIVE` |
| Deployment authority | `NOT_GRANTED` |
| Technical Preview activation | `NOT_AUTHORIZED` |
| Production activation | `NOT_AUTHORIZED` |
| Updater activation | `INACTIVE` |

Machine-readable operational authority remains in `ops/final-shift-close/*.json`.

## Technology baseline

- Backend: Laravel / PHP
- Frontend: Vue 3 + Inertia + Vite
- Database: MySQL-compatible
- Architecture: Modular Monolith First, Clean Architecture, DDD, module-owned schema
- Authorization: tenant-context first, deny-by-default
- API governance: versioned REST, stable error envelope, correlation ID, tenant context, idempotency, cursor pagination, signed webhooks, replay protection

## Next position

Sprint168 starts with bounded discovery from the fully reconciled Sprint167 checkpoint. Do not preselect an objective or infer new mutation/operational authority.

Author by Lab | zefry
