# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed **engineering** sprint is **Sprint162**.

- Canonical engineering commit: `332bcff11b40307d350c7ce5b3a6c08913c4251c`
- Engineering PR: #742 — `Sprint162: add guarded POS operations hub`
- Final engineering head: `2d75efbdb4b3eb2b98ad7973866fa6576b1ffd79`
- Complete surfaced exact-head PR-triggered qualification: successful
- Sprint162 regression run `34857221294`: successful
- M7.1 run `34857221166`: successful
- Governance Required Checks run `34857221221`: successful
- PHP Foundation Regression run `34857220990`: successful
- Repository-native `product-owner-merge-authority`: successful for the exact engineering head
- Sprint162 engineering envelope: 9 paths, SHA-256 `afadd8577d794fffc100b8ab98d77dfc55599ead9d32963c1ef84ee8a086afd2`
- Post-Sprint162 reconciliation envelope: six paths, SHA-256 `66500e314da09a14dbc35624dc0e0468c6ca3707bcf0a91261b4f850b207b734`
- Next engineering position: **Sprint163 bounded discovery**, with no preselected objective or source envelope

For the full current project state, use **[`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md)** as the canonical human-readable source of truth.

## Sprint162 — guarded POS operations hub

**Purpose / Why:** Sprint156–Sprint161 produced seven secure operational POS destinations, but the product had no shared frontend layout, POS home route, or common authorized navigation surface. Operational users otherwise had to know individual URLs.

**Objective / Gap:** `POS_OPERATIONS_HUB` — provide one read-only POS entry point without weakening or duplicating existing target authorization.

**What changed:** Sprint162 added a guarded `/pos` hub that preserves exact tenant/organization/outlet/device scope and composes existing permissions only. Each visible destination must pass both the current context's canonical permission requirement and `Route::has()`; target pages then authorize again independently. No hub-specific permission, mutation authority, persistence model, migration, or stock-adjustment engine was introduced.

The hub covers currently delivered Catalog & Opening Stock, Shift Start, Cashier, Sales Summary, Sale History, Sale Corrections, and Shift Close surfaces according to their existing permission semantics and feature-delivery gates.

**Evidence / Qualification:** PR #742; exact engineering head `2d75efbdb4b3eb2b98ad7973866fa6576b1ffd79`; complete surfaced exact-head matrix successful; Product Owner authority successful; engineering squash `332bcff11b40307d350c7ce5b3a6c08913c4251c`.

**Operational boundaries / NO-GO:** selected durable target remains `null`; migration #27 remains `NOT_EXECUTED`; permissions remain `NONE`; feature activation remains `INACTIVE`; deployment authority remains `NOT_GRANTED`; Technical Preview/Production remain `NOT_AUTHORIZED`; updater remains `INACTIVE`.

**Next position:** Sprint163 bounded discovery from canonical post-Sprint162; no objective, implementation, or source envelope is preselected.

## What the repository has reached

Material canonical progress includes:

- modular-monolith architecture, tenant isolation, authorization, API governance, CI/governance, exact-head merge controls, and historical-regression preservation;
- POS shift/register, sale/payment/receipt, catalog, inventory baseline, durable stock mutation, sale void/refund, cash variance/adjudication, operational reporting, cashier sale entry, shift start, sale correction, immutable sale history/receipt detail, catalog/opening-inventory setup, and guarded POS operations navigation;
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
