# CURRENT CANONICAL OVERRIDE — Sprint163

**Canonical engineering checkpoint:** Sprint163  
**Canonical engineering commit:** `a920e63c1a1d2623664b416422c3d5a471e389a6`  
**Latest engineering PR:** #744 — `Sprint163: add operational cash variance reconciliation workspace`  
**Final engineering head:** `61a506f44315e7ac18ec08672bd9cb2aee81c838`  
**Sprint163 regression:** `34862683840` — successful  
**Engineering envelope:** 18 paths — `61077fd95f392e588a99d394f0ba3a0fc4d5b3187da850a1f4a796fa61eb5dbb`  
**Reconciliation envelope:** 6 paths — `dd1e8acc008bbe3ca8491cff12b0327f204d63e0820286af0da786acc5f8d4f2`  
**Next position:** Sprint164 bounded discovery; no objective preselected.

Sprint163 materialized `POS_CASH_VARIANCE_RECONCILIATION_WORKSPACE` over existing durable cash-variance explanation/reviewer authorities. Variance subjects remain server-authoritative, same-outlet review may cross devices, maker-checker separation remains enforced, and terminal `REVIEW_REJECTED` remains explicit. No new migration, permission identifier, adjudication engine, stock-adjustment authority, or Final Shift Close rule was introduced.

Operational NO-GO remains unchanged: selected target `null`; migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; feature activation `INACTIVE`; deployment authority `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`.

> The retained Sprint162 manifest snapshot below is historical context. This Sprint163 override is the current canonical human-readable checkpoint. Machine-readable operational state remains authoritative.

---

# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Canonical engineering checkpoint:** Sprint162
**Canonical engineering commit:** `332bcff11b40307d350c7ce5b3a6c08913c4251c`
**Latest engineering PR:** #742 — `Sprint162: add guarded POS operations hub`
**Status date:** 2026-09-14

> This file is the canonical human-readable source of truth for current oneQay project status. Machine-readable operational state remains authoritative for operational gates.

## 1. Current project state

oneQay remains an actively engineered enterprise business-management platform using Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

Sprint162 closed the bounded P1 gap `POS_OPERATIONS_HUB`. Sprint156–Sprint161 had produced seven independently guarded POS operational pages, but there was no shared frontend layout, POS home route, or authorized navigation owner. Sprint162 added one read-only `/pos` entry point that discovers only destinations whose named route is currently delivered and whose existing permission requirement is satisfied by the current verified context.

The hub creates no new mutation authority, persistence model, migration, or permission identifier. It composes the existing access rules: Shift Start requires shift-open and opening-cash permissions; Cashier reuses complete-sale; reporting/history reuse the canonical reporting view permission; corrections require void or refund authority; catalog/opening-stock setup requires both catalog and inventory-baseline permissions; Shift Close reuses `pos.shift.close`. Every target workspace still re-authorizes independently.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest completed engineering sprint | Sprint162 |
| Canonical engineering commit | `332bcff11b40307d350c7ce5b3a6c08913c4251c` |
| Latest engineering PR | #742, squash merged |
| Final engineering head | `2d75efbdb4b3eb2b98ad7973866fa6576b1ffd79` |
| Exact-head CI | Complete surfaced PR-triggered matrix successful |
| Sprint162 regression | Run `34857221294`, successful |
| Product Owner authority | `product-owner-merge-authority=success` on exact engineering head |
| Engineering envelope | 9 paths; SHA-256 `afadd8577d794fffc100b8ab98d77dfc55599ead9d32963c1ef84ee8a086afd2` |
| Post-Sprint162 reconciliation envelope | 6 paths; SHA-256 `66500e314da09a14dbc35624dc0e0468c6ca3707bcf0a91261b4f850b207b734` |
| POS operational reporting | Materialized through Sprint156 |
| POS cashier workspace | Materialized through Sprint157 |
| POS shift-start workspace | Materialized through Sprint158 |
| POS sale correction workspace | Materialized through Sprint159 |
| POS immutable history/receipt workspace | Materialized through Sprint160 |
| POS catalog/inventory setup workspace | Materialized through Sprint161 |
| POS operations hub | Materialized through Sprint162 |
| Runtime allowlist | `local`, `test`, `ci` only |
| Durable activation target | `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET` |
| Selected activation target | `null` |
| Final Shift Close migration #27 | `NOT_EXECUTED` |
| Permission provisioning | `NONE` |
| Real target-bound capability evidence | `NONE` |
| Real dependency-envelope evidence | `NONE` |
| Feature activation | `INACTIVE` |
| Deployment authority | `NOT_GRANTED` |
| Technical Preview activation | `NOT_AUTHORIZED` |
| Production activation | `NOT_AUTHORIZED` |
| Updater activation | `INACTIVE` |

## 2. Material engineering progress

Platform foundations include tenant isolation, deny-by-default authorization, session/authentication foundations, versioned REST governance, idempotency, exact-head CI/governance, repository-native Product Owner merge authorization, and successor-compatible historical regression ownership.

Bounded POS engineering includes shift/register opening, sale completion/payment/receipt evidence, catalog preparation, inventory baseline, durable stock mutation, full-sale void, full CASH refund, cash variance/adjudication, operational reporting, cashier sale entry, shift start, sale correction, immutable sale history/receipt detail, catalog/opening-inventory setup, and a guarded operations navigation hub.

Sprint88 through Sprint155 established the Final Shift Close source/readiness chain through migration #27 materialization, runtime dependency/readiness, target selection and binding controls, capability/dependency evidence foundations, permission-provisioning binding, and source-only activation planning. Operational execution remains separately gated and has not occurred.

## 3. Sprint162 description and closure evidence

### Purpose / Why

Post-Sprint161 bounded discovery proved the operational POS pages existed as independently guarded destinations but were fragmented. There was no `resources/js/layouts` or shared component owner, no POS index/home route, and no central authorized navigation surface. Shift Start linked only forward to Cashier; the other operational workspaces were not discoverable from a common POS entry point.

Discovery also confirmed only the one-time inventory-baseline mutation authority exists; no canonical restock/stock-adjustment application authority was present. Sprint162 therefore avoided inventing a new inventory mutation engine and selected the smaller material usability gap.

### Objective / Gap

`POS_OPERATIONS_HUB`.

### What changed

- Added `PosOperationsHubSnapshot` carrying exact tenant, organization, outlet, and device scope plus access booleans only.
- Added `ViewPosOperationsHub` using existing durable permissions; no hub-specific permission was introduced.
- Preserved target authorization composition.
- Denied hub access when the current verified context has no qualifying POS authority.
- Added fail-closed named-route discovery.
- Added `/pos` through a dedicated child provider, leaving the global provider registry unchanged.
- Added fail-closed `ONEQAY_POS_OPERATIONS_HUB_ENABLED`, default false.
- Restricted delivery to Local/Test/CI, persistence enabled, exact session controls, and explicit hub arming.
- Added responsive Vue/Inertia read-only navigation UI and executable permission-composition regression.

### Evidence / Qualification

- Parent canonical post-Sprint161 checkpoint: `e0330761a325a5f9e5faa4c5c0089b6868f979c8`.
- Engineering PR: #742, squash merged.
- Final exact engineering head: `2d75efbdb4b3eb2b98ad7973866fa6576b1ffd79`.
- Canonical engineering squash: `332bcff11b40307d350c7ce5b3a6c08913c4251c`.

### Operational boundaries / NO-GO

Sprint162 did not select a durable target, execute migration #27, provision permissions, activate Final Shift Close, grant deployment authority, activate Technical Preview/Production, or activate the updater.

### Next position

Historical Sprint162 next position was Sprint163 bounded discovery. Current next position is superseded by the Sprint163 override above.

## 4. Operational truth — NO-GO remains authoritative

Machine-readable operational authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

## 5. Documentation responsibility model

`PROJECT_MANIFEST.md` is the canonical human-readable state; README, CHANGELOG, TASKS, and ROADMAP are reconciled summaries; merged PRs and Git history preserve implementation provenance.

## 6. Mandatory sprint description and update rule

Every material sprint records **Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position**.

Author by Lab | zefry
