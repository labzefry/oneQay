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
- Preserved target authorization composition:
  - Shift Start = `pos.shift.open` AND `pos.shift.opening-cash.record`;
  - Cashier = `pos.sale.complete`;
  - Sales Summary / Sale History = `pos.reporting.sales-summary.view`;
  - Corrections = `pos.sale.void` OR `pos.sale.refund`;
  - Catalog & Opening Stock = `pos.catalog.prepare` AND `pos.inventory.baseline`;
  - Shift Close = `pos.shift.close`.
- Denied hub access when the current verified context has no qualifying POS authority.
- Added a second fail-closed filter requiring every visible destination to satisfy `Route::has()`; disabled/unregistered feature surfaces are never emitted as links.
- Added `/pos` named route `pos.operations.hub` through a dedicated child provider, leaving the global provider registry unchanged.
- Added fail-closed `ONEQAY_POS_OPERATIONS_HUB_ENABLED`, default false.
- Restricted delivery to Local/Test/CI, persistence enabled, exact session controls, and explicit hub arming.
- Added responsive Vue/Inertia read-only navigation UI.
- Added executable permission-composition regression proving AND/OR semantics, no permission broadening, exact scope preservation, no-access denial, and canonical route identities.
- Left all existing POS mutation owners, target workspace route owners, `PosPermission.php`, migrations, Composer metadata, and operational state unchanged.

### Evidence / Qualification

- Parent canonical post-Sprint161 checkpoint: `e0330761a325a5f9e5faa4c5c0089b6868f979c8`.
- Engineering PR: #742, squash merged.
- Final exact engineering head: `2d75efbdb4b3eb2b98ad7973866fa6576b1ffd79`.
- Sprint162 regression run `34857221294`: successful on the exact head.
- M7.1 Application Regression run `34857221166`: successful.
- Governance Required Checks run `34857221221`: successful.
- PHP Foundation Regression run `34857220990`: successful.
- Sprint156–Sprint161 and all other surfaced PR-triggered historical runs: successful.
- Repository-native Product Owner merge authority: successful for PR #742 and the exact head.
- Engineering envelope: exactly 9 paths; SHA-256 `afadd8577d794fffc100b8ab98d77dfc55599ead9d32963c1ef84ee8a086afd2`.
- Canonical engineering squash: `332bcff11b40307d350c7ce5b3a6c08913c4251c`.
- Post-merge verification: exactly one squash commit above post-Sprint161 canonical main and exactly the qualified 9 engineering paths.
- Post-Sprint162 reconciliation envelope: exactly six canonical paths; SHA-256 `66500e314da09a14dbc35624dc0e0468c6ca3707bcf0a91261b4f850b207b734`.

### Operational boundaries / NO-GO

Sprint162 does not select or persist a durable target, dispatch capability/dependency producers, execute migration #27, provision permissions, create real target-bound evidence, widen the runtime allowlist, activate Final Shift Close, grant deployment authority, activate Technical Preview/Production, or activate the updater.

Machine-readable state remains target selection blocked with `selected_target=null`, migration #27 `NOT_EXECUTED`, permission provisioning `NONE`, feature activation `INACTIVE`, deployment authority `NOT_GRANTED`, Technical Preview and Production `NOT_AUTHORIZED`, and updater `INACTIVE`.

### Next position

The next engineering position is **Sprint163 bounded discovery from canonical post-Sprint162**. No Sprint163 objective, implementation, or source envelope is preselected.

## 4. Operational truth — NO-GO remains authoritative

Machine-readable operational authority remains in:

- `ops/final-shift-close/STATE.json`;
- `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`;
- `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

No source-only engineering or reconciliation text constitutes operational authorization.

## 5. Documentation responsibility model

- `PROJECT_MANIFEST.md` — canonical human-readable current project/lifecycle state;
- `README.md` — concise entry-point summary;
- `CHANGELOG.md` — material chronology;
- `TASKS.md` — current completed/pending workboard;
- `ROADMAP.md` — forward sequencing and lifecycle gates;
- `docs/SPRINT*.md` — detailed historical bounded-sprint evidence where materialized;
- `ops/final-shift-close/*.json` — machine-readable operational authority;
- merged PRs and Git history — immutable implementation provenance.

## 6. Mandatory sprint description and update rule

Every material sprint records **Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position**. Every material closed sprint reconciles this manifest and the four root summary documents while the just-closed sprint preservation workflow remains successor-compatible.

Author by Lab | zefry
