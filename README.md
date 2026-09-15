# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, module-owned schema, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed **engineering** sprint is **Sprint166**.

- Canonical engineering commit: `e2758d0170a081953aaae11711ecc1ec3c0f8e78`
- Engineering PR: #750 — `Sprint166: add POS product sales performance workspace`
- Final engineering head: `7f8de2dc2639938a02f5413a1a1edbbb0b7e925f`
- Complete surfaced exact-head PR-triggered qualification: successful
- Sprint166 regression run `34917171015`: successful
- M7.1 run `34917171092`: successful
- Governance Required Checks run `34917171084`: successful
- PHP Foundation Regression run `34917171083`: successful
- Repository-native Product Owner merge authorization: `success` on the exact engineering head
- Sprint166 engineering envelope: 12 paths, SHA-256 `3687acbd962b494a354c705c43eda5a71c1de426b5691d9b0f13b1620af2e228`
- Post-Sprint166 reconciliation envelope: six paths, SHA-256 `d887f8bcf393ad56a74d481e64aa3963c8678012f87025dcbf93ef8bde5ea4c8`
- Next engineering position: **Sprint167 bounded discovery**, with no preselected objective

For the full current project state, use **[`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md)** as the canonical human-readable source of truth.

## Sprint166 — POS product sales performance workspace

**Purpose / Why:** Existing operational sales reporting was outlet-aggregate only. Sprint166 adds product-level visibility without introducing a second reporting authority or any new mutation path.

**Objective / Gap:** `POS_PRODUCT_SALES_PERFORMANCE_WORKSPACE`.

**What changed:** A guarded read-only workspace now derives product/currency performance from immutable completed sale lines and full-sale void evidence. It reports gross, voided, and net active quantity/value; preserves historical currency/scale boundaries; keeps inactive catalog products visible when historical evidence exists; fails closed on orphan/corrupt/inconsistent evidence; and bounds output to 250 buckets with explicit truncation.

CASH refund is not subtracted a second time because canonical CASH refund follows an already-recorded full-sale void. Access reuses existing `pos.reporting.sales-summary.view`; delivery is default-false through `ONEQAY_POS_PRODUCT_SALES_PERFORMANCE_ENABLED` and remains Local/Test/CI + persistence + existing reporting + exact-session gated.

No migration, schema change, sale or stock mutation, stocktake, arbitrary adjustment, supplier/purchasing, transfer, new permission, permission provisioning, global route/provider change, or existing Sales Summary ownership change was introduced.

## Product state through Sprint166

Material canonical progress includes tenant isolation, deny-by-default authorization, session/authentication foundations, API governance, exact-head CI/governance, repository-native Product Owner merge authorization, POS shift/register operations, durable sale/payment/receipt evidence, catalog and opening inventory, positive replenishment, inventory accountability, void/refund controls, cash variance/adjudication, operational reporting, cashier, shift start, sale corrections, immutable sale history, guarded POS navigation, and product-level sales performance.

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

Sprint167 starts with bounded discovery from the fully reconciled Sprint166 checkpoint. Do not preselect an objective or infer new operational authority.

Author by Lab | zefry
