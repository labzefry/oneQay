# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, module-owned schema, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed **engineering** sprint is **Sprint168**.

- Canonical engineering commit: `d3703a6b18f478acd812e7892c3871ce3aaf7bfa`
- Engineering PR: #754 — `Sprint168: add POS shift history performance workspace`
- Final engineering head: `05652fc5b11fdad7bb11e9c992f79d6f5436e29d`
- Complete surfaced exact-head PR-triggered qualification: successful
- Sprint168 regression run `34921162121`: successful
- M7.1 run `34921161920`: successful
- Governance Required Checks run `34921162056`: successful
- PHP Foundation Regression run `34921162098`: successful
- Repository-native Product Owner merge authorization: `success` on the exact engineering head
- Sprint168 engineering envelope: 12 paths, SHA-256 `d42a5a7d8568766e524ff662107ffdb35452d4f9ac00f26cdc8e419d36bc3c25`
- Post-Sprint168 reconciliation envelope: six paths, SHA-256 `350fd16d111bb398c34a63e1519cc02206bcc373378a9366768c198ccf8146b5`
- Next engineering position: **Sprint169 bounded discovery**, with no preselected objective

For the full current project state, use **[`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md)** as the canonical human-readable source of truth.

## Sprint168 — POS shift history performance workspace

Sprint168 adds a read-only historical workspace for closed outlet shifts. It deliberately supports same-outlet cross-device visibility, requires exactly one canonical Final Shift Close evidence row for every eligible closed shift, and derives selected-shift performance only from immutable shift-bound sale, full-sale-void, and CASH-refund evidence.

Active net value equals gross minus full-sale void; CASH refund remains separately reported and is not deducted twice. Missing close evidence, legacy null-shift sales inside the shift window, foreign-scope/outside-window evidence, malformed tender/evidence, inconsistent void/refund evidence, overflow, or excessive bucket count fail closed.

Access reuses `pos.reporting.sales-summary.view`. Delivery remains default-false through `ONEQAY_POS_SHIFT_HISTORY_PERFORMANCE_ENABLED`, Local/Test/CI + persistence + operational-reporting + exact-session gated, and depends on existing `ONEQAY_POS_SHIFT_CLOSE_ENABLED` source/runtime readiness. Operations Hub discovery remains guarded by reporting authority and `Route::has()`.

No migration, schema change, shift/sale/cash/stock mutation, new permission, provisioning, global route/provider change, Final Shift Close provider change, deployment, Technical Preview/Production activation, or updater activation was introduced.

## Product state through Sprint168

Material canonical progress includes tenant isolation, deny-by-default authorization, session/authentication foundations, API governance, exact-head CI/governance, repository-native Product Owner merge authorization, POS shift/register operations, durable sale/payment/receipt evidence, catalog/opening inventory, positive replenishment, inventory accountability, void/refund controls, cash variance/adjudication, operational reporting, cashier, shift start, sale corrections, immutable sale history, guarded POS navigation, product-level sales performance, live active-shift performance, and closed-shift historical performance.

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

Sprint169 starts with bounded discovery from the fully reconciled Sprint168 checkpoint. Do not preselect an objective or infer new mutation/operational authority.

Author by Lab | zefry
