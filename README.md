# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, module-owned schema, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed **engineering** sprint is **Sprint165**.

- Canonical engineering commit: `dc6340c04ac710bd38966d897b27e23fd92c0a41`
- Engineering PR: #748 — `Sprint165: add POS inventory accountability workspace`
- Final engineering head: `553b7b07b6b30ae37ebd36a041e2cf85ffa09c03`
- Complete surfaced exact-head PR-triggered qualification: successful
- Sprint165 regression run `34915204475`: successful
- M7.1 run `34915204297`: successful
- Governance Required Checks run `34915204324`: successful
- PHP Foundation Regression run `34915204303`: successful
- Repository-native Product Owner merge authorization: `success` on the exact engineering head
- Sprint165 engineering envelope: 14 paths, SHA-256 `d83945325461acdd9db7f1ab02bb908a1e0e263a6f680e2f0acf1f3256978786`
- Post-Sprint165 reconciliation envelope: six paths, SHA-256 `2f10216f9f2c86a188a924c66a8473c17cd5310da47bd768a8ab1bb0f4a17533`
- Next engineering position: **Sprint166 bounded discovery**, with no preselected objective or source envelope

For the full current project state, use **[`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md)** as the canonical human-readable source of truth.

## Sprint165 — POS inventory accountability workspace

**Purpose / Why:** oneQay already had canonical stock-affecting evidence for opening baseline, positive replenishment, completed sale decrement, and full-sale void restoration, but no single operational surface proved that current stock reconciled to those immutable sources.

**Objective / Gap:** `POS_INVENTORY_ACCOUNTABILITY_WORKSPACE`.

**What changed:** Sprint165 added a read-only tenant + organization + outlet accountability view. For every baselined product the server computes `opening + replenishment + void restoration - sold` and requires the result to match canonical current catalog stock. The calculation is aggregate and independent of timestamp ordering; timestamps are presentation-only. CASH refunds never create a second stock restoration. Inactive baselined products remain visible for historical accountability.

The workspace reuses existing inventory authorities only: `pos.inventory.baseline` OR `pos.inventory.replenish`. It introduces no new permission, migration, schema, adjustment engine, purchasing flow, transfer flow, or negative stock mutation. Delivery remains default-off and Local/Test/CI only with persistence and exact session controls. The POS Operations Hub shows the destination only when authorization is satisfied and the route is actually delivered.

**Evidence / Qualification:** PR #748; exact engineering head `553b7b07b6b30ae37ebd36a041e2cf85ffa09c03`; complete surfaced exact-head matrix successful; Product Owner merge authorization verified; engineering squash `dc6340c04ac710bd38966d897b27e23fd92c0a41`.

**Operational boundaries / NO-GO:** selected durable target remains `null`; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; Final Shift Close remains `INACTIVE`; deployment authority remains `NOT_GRANTED`; Technical Preview/Production remain `NOT_AUTHORIZED`; updater remains `INACTIVE`.

**Next position:** Sprint166 bounded discovery from canonical post-Sprint165. No objective, permission, mutation authority, migration, or source envelope is preselected.

## Recent POS engineering progression

- **Sprint164:** positive-only inventory replenishment after canonical opening baseline; engineering squash `d37ecdfa16d3f024de840871aa202af4fb5ee7d1`.
- **Sprint163:** operational cash-variance reconciliation using existing explanation/reviewer authorities; engineering squash `a920e63c1a1d2623664b416422c3d5a471e389a6`.
- **Sprint162:** guarded POS operations hub using existing target permissions and delivered-route discovery; engineering squash `332bcff11b40307d350c7ce5b3a6c08913c4251c`.
- **Sprint156–Sprint161:** operational reporting, cashier, shift start, sale correction, immutable sale history/receipt detail, and catalog/opening-inventory setup.

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
- Schema ownership: module-owned where bounded evolution requires it
- API governance: versioned REST, stable error envelope, correlation ID, tenant context, idempotency, cursor pagination, signed webhooks, replay protection

## Documentation map

- [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md) — current project/lifecycle source of truth
- [`CHANGELOG.md`](CHANGELOG.md) — material chronological progress
- [`TASKS.md`](TASKS.md) — current completed/pending workboard
- [`ROADMAP.md`](ROADMAP.md) — future sequencing and gates
- `docs/SPRINT*.md` — detailed historical sprint evidence where materialized
- `ops/final-shift-close/*.json` — machine-readable operational state

Every material sprint records **Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position**.

Author by Lab | zefry
