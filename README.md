# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed **engineering** sprint is **Sprint164**.

- Canonical engineering commit: `d37ecdfa16d3f024de840871aa202af4fb5ee7d1`
- Engineering PR: #746 — `Sprint164: add POS inventory replenishment foundation workspace`
- Final engineering head: `45cf298b799b134d0e17bdf19cc751814fe3ae2a`
- Complete latest reopened exact-head PR-triggered qualification: successful
- Sprint164 regression run `34912504801`: successful
- M7.1 run `34912504799`: successful
- Governance Required Checks run `34912504817`: successful
- PHP Foundation Regression run `34912504836`: successful
- Repository-native Product Owner merge authorization: `success` on the exact engineering head
- Sprint164 engineering envelope: 22 paths, SHA-256 `b826b746e18bc39025735e10fe645ad559eceab992801190a654624462811f8e`
- Post-Sprint164 reconciliation envelope: six paths, SHA-256 `19c035b85a4698f60a78cbb70ccd0c1b82835c47073f5dcb2e2443f49c88f0fa`
- Next engineering position: **Sprint165 bounded discovery**, with no preselected objective or source envelope

For the full current project state, use **[`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md)** as the canonical human-readable source of truth.

## Sprint164 — POS inventory replenishment foundation workspace

**Purpose / Why:** Opening inventory baseline is intentionally one-time. After normal sales reduce stock, there was no canonical authority to receive additional stock, even though sale void already restored sold quantities correctly. Without an ongoing replenishment path, normal product operations would eventually stall or require direct database manipulation.

**Objective / Gap:** `POS_INVENTORY_REPLENISHMENT_FOUNDATION_WORKSPACE`.

**What changed:** Sprint164 added positive-only receiving/replenishment after an existing canonical opening baseline. The authority requires an active exact tenant/outlet product, stable operation identity, immutable before/received/after evidence, overflow-safe locked stock transition, and dedicated deny-by-default `pos.inventory.replenish` permission. No automatic grant or provisioning is introduced. The guarded workspace exposes authoritative stock plus recent immutable receiving evidence and requires authoritative refresh after successful or ambiguous mutation outcomes; there is no automatic retry.

Migration #28 is module-owned under `apps/web/database/module-migrations/pos/` and is discovered by the bounded replenishment provider before delivery activation checks. The canonical global migration directory remains unchanged through migration #27. Existing sale completion, full-sale void, cash refund, and opening baseline owners remain intact.

**Evidence / Qualification:** PR #746; exact engineering head `45cf298b799b134d0e17bdf19cc751814fe3ae2a`; latest reopened surfaced matrix successful; Product Owner merge authorization verified; engineering squash `d37ecdfa16d3f024de840871aa202af4fb5ee7d1`.

**Operational boundaries / NO-GO:** selected durable target remains `null`; migration #27 remains `NOT_EXECUTED`; permissions remain `NONE`; Final Shift Close feature activation remains `INACTIVE`; deployment authority remains `NOT_GRANTED`; Technical Preview/Production remain `NOT_AUTHORIZED`; updater remains `INACTIVE`.

**Next position:** Sprint165 bounded discovery from canonical post-Sprint164. Do not preselect the objective. Any future stock correction, decrement, transfer, or count-adjustment authority requires separate bounded justification; Sprint164 authorizes positive replenishment only.

## Sprint163 — operational cash variance reconciliation workspace

Sprint163 materialized `POS_CASH_VARIANCE_RECONCILIATION_WORKSPACE` over existing explanation/reviewer authorities while keeping variance subjects server-authoritative, preserving maker-checker separation, and retaining terminal rejection semantics. Engineering PR #744 squash merged at `a920e63c1a1d2623664b416422c3d5a471e389a6`.

## Sprint162 — guarded POS operations hub

Sprint162 added one guarded read-only POS entry point that exposes only currently delivered routes for which the verified context has the existing target permission. Engineering PR #742 squash merged at `332bcff11b40307d350c7ce5b3a6c08913c4251c`.

## What the repository has reached

Material canonical progress includes:

- modular-monolith architecture, tenant isolation, authorization, API governance, CI/governance, exact-head merge controls, and historical-regression preservation;
- POS shift/register, sale/payment/receipt, catalog, one-time opening inventory baseline, positive inventory replenishment, durable stock mutation, sale void/refund, cash variance/adjudication, operational reporting, cashier sale entry, shift start, sale correction, immutable sale history/receipt detail, catalog/opening-inventory setup, guarded POS operations navigation, and cash-variance reconciliation;
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
- Architecture: Modular Monolith First, Clean Architecture, DDD, module-owned schema
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
