# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed **engineering** sprint is **Sprint161**.

- Canonical engineering commit: `33080c0b5f5c6f66e9994ad7f05ba78dea241294`
- Engineering PR: #740 — `Sprint161: add operational catalog inventory setup workspace`
- Final engineering head: `fbe8e91df756855d38b8c6656b17f27b4cc32585`
- Complete surfaced exact-head PR-triggered qualification: successful
- Sprint161 regression run `34853239912`: successful
- Repository-native `product-owner-merge-authority`: successful for the exact engineering head
- Sprint161 engineering envelope: 11 paths, SHA-256 `66d7c616fbe8ae0e6c3c262fc8054db26bcbaa67e07ed77000363041b056f613`
- Post-Sprint161 reconciliation envelope: six paths, SHA-256 `fd24a20017a13eca06d93ade217b6c0a68db07c205b218b8d9c201122c6e7ccc`
- Next engineering position: **Sprint162 bounded discovery**, with no preselected objective or source envelope

For the full current project state, use **[`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md)** as the canonical human-readable source of truth.

## Sprint161 — operational catalog and opening-inventory setup

**Purpose / Why:** canonical oneQay already had catalog preparation and one-time inventory-baseline mutation engines, but both were API-only while cashier operations depended on products being prepared with opening stock.

**Objective / Gap:** `POS_CATALOG_INVENTORY_SETUP_WORKSPACE` — provide an operational path to make products sale-ready without duplicating canonical mutation authority.

**What changed:** Sprint161 added a tenant/outlet-scoped catalog and stock snapshot, exact baseline-eligibility state, reuse of existing `pos.catalog.prepare` and `pos.inventory.baseline` permissions/endpoints, an explicit two-step setup UI, manual authoritative-refresh lock after mutation success or network ambiguity, feature arming, child-provider delivery, and executable regression. No composite mutation, migration, stock-adjustment engine, or automatic retry was introduced.

The first exact-head candidate correctly failed CI because lower-case persisted currency was being normalized rather than rejected. The corrected final head requires persisted currency to already be canonical uppercase, preserving fail-closed evidence integrity.

**Evidence / Qualification:** PR #740; final engineering head `fbe8e91df756855d38b8c6656b17f27b4cc32585`; complete surfaced exact-head matrix successful; Product Owner authority successful; engineering squash `33080c0b5f5c6f66e9994ad7f05ba78dea241294`.

**Operational boundaries / NO-GO:** selected durable target remains `null`; migration #27 remains `NOT_EXECUTED`; permissions remain `NONE`; feature activation remains `INACTIVE`; deployment authority remains `NOT_GRANTED`; Technical Preview/Production remain `NOT_AUTHORIZED`; updater remains `INACTIVE`.

**Next position:** Sprint162 bounded discovery from canonical post-Sprint161; no objective, implementation, or source envelope is preselected.

## What the repository has reached

Material canonical progress includes:

- modular-monolith architecture, tenant isolation, authorization, API governance, CI/governance, exact-head merge controls, and historical-regression preservation;
- POS shift/register, sale/payment/receipt, catalog, inventory baseline, durable stock mutation, sale void/refund, cash variance/adjudication, operational reporting, cashier sale entry, shift start, sale correction, immutable sale history/receipt detail, and catalog/opening-inventory setup;
- Final Shift Close source/readiness controls through Sprint155 without operational activation.

## Operational status remains intentionally gated

| Gate | State |
| --- | --- |
| Migration #27 execution | `NOT_EXECUTED` |
| Permission provisioning | `NONE` |
| Durable activation target | `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET` |
| Selected target | `null` |
| Real capability evidence | `NONE` |
| Real dependency-envelope evidence | `NONE` |
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
- Architecture: Modular Monolith First, Clean Architecture, DDD
- Authorization: tenant-context first, deny-by-default
- API governance: versioned REST, stable error envelope, correlation ID, tenant context, idempotency, cursor pagination, signed webhooks, replay protection

## Documentation map

- [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md) — current project/lifecycle source of truth
- [`CHANGELOG.md`](CHANGELOG.md) — material chronological progress
- [`TASKS.md`](TASKS.md) — current completed/pending workboard
- [`ROADMAP.md`](ROADMAP.md) — future sequencing and gates
- `docs/SPRINT*.md` — detailed historical sprint evidence where materialized
- `ops/final-shift-close/*.json` — machine-readable operational state

## Sprint documentation rule

Every material sprint records **Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position**.

Author by Lab | zefry
