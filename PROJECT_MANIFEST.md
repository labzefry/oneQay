# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Canonical engineering checkpoint:** Sprint160
**Canonical engineering commit:** `e6ef6e77d8a2d0dea16d7c17dde78bece904b78b`
**Latest engineering PR:** #738 — `Sprint160: add immutable POS sale history detail workspace`
**Status date:** 2026-09-14

> This file is the canonical human-readable source of truth for current oneQay project status. Machine-readable operational state remains authoritative for operational gates.

## 1. Current project state

oneQay remains an actively engineered enterprise business-management platform using Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

Sprint160 closed the bounded P1 product-usability gap `POS_SALE_HISTORY_DETAIL_WORKSPACE`. Canonical POS already persisted immutable sale headers, line items, shift binding, tender evidence, void evidence, and CASH-refund evidence, but the product exposed only aggregate/recent sale summaries and correction operations. Sprint160 added a read-only operational history and exact receipt drill-down over those existing records rather than adding a second transaction engine or new persistence model.

The workspace lists at most the latest 50 sales in exact tenant + organization + outlet scope and supports exact canonical `sale-<24 hex>` lookup. Selected receipts validate line numbering, quantity, unit-price multiplication, line totals, currency/scale consistency, sum-to-sale-total, correction evidence ordering, correction amount/tender consistency, and correction organization/outlet scope. Atomic monetary values are delivered to the browser as strings to avoid JavaScript integer precision loss. Mutable current catalog names are intentionally not joined into historical receipts because they are not immutable sale evidence.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest completed engineering sprint | Sprint160 |
| Canonical engineering commit | `e6ef6e77d8a2d0dea16d7c17dde78bece904b78b` |
| Latest engineering PR | #738, squash merged |
| Sprint160 final engineering head | `d159aa26c748c5a624f0f71fe6c135f0d4f36be9` |
| Sprint160 exact-head CI | Complete surfaced PR-triggered matrix successful |
| Sprint160 Product Owner authority | `product-owner-merge-authority=success` for exact engineering head |
| Sprint160 engineering envelope | 10 paths; SHA-256 `f60bd3698cbc28cfccdf8b79c446e5e138203246afdb16aaea1c5637aa327181` |
| Post-Sprint160 reconciliation envelope | 6 paths; SHA-256 `388c671587b1d0e21206260c5f0003fb215d606df494eaa528b1e6d839de7847` |
| POS operational sales reporting | Materialized through Sprint156 |
| POS cashier workspace | Materialized through Sprint157 |
| POS shift-start workspace | Materialized through Sprint158 |
| POS sale correction workspace | Materialized through Sprint159 |
| POS sale history/detail workspace | Materialized through Sprint160 |
| History/detail scope | Tenant + organization + outlet; exact sale lookup does not disclose foreign scope |
| History/detail authorization | Existing `pos.reporting.sales-summary.view`, deny-by-default |
| Historical line source | Canonical `oneqay_pos_sale_lines`; no mutable catalog-name reconstruction |
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

### Platform, governance, and POS foundation

The repository has established modular-monolith architecture, tenant isolation, deny-by-default authorization, first-party session and privileged-authentication foundations, versioned REST governance, deterministic CI/governance controls, exact-head qualification, and repository-native Product Owner merge authorization.

Bounded POS work includes shift/register opening, sale completion/payment/receipt evidence, catalog preparation, inventory baseline, durable idempotency, full-sale void, full CASH refund evidence, cash variance/adjudication, Final Shift Close source/readiness controls, operational sales reporting, cashier sale entry, shift-start operations, sale correction operations, and immutable sale-history/receipt drill-down.

### Final Shift Close engineering chain

Sprint88 through Sprint155 established source/readiness controls through migration #27 materialization, runtime dependency/readiness, selected-target identity, DB binding/control-plane hardening, capability/dependency evidence foundations, permission-provisioning binding, and source-only feature-activation planning/handoff. Operational execution remains separately gated and has not occurred.

### Product-readiness pivot

- Sprint156 — read-only tenant + organization + outlet scoped POS operational sales reporting.
- Sprint157 — operational POS cashier sale-entry workspace over existing sale authority.
- Sprint158 — resumable exact-device shift-start workspace over existing shift-opening/opening-cash authorities.
- Sprint159 — operational sale correction workspace over existing void/refund authorities.
- Sprint160 — immutable sale-history and exact receipt-detail workspace under existing reporting authority.

## 3. Sprint160 description and closure evidence

### Purpose / Why

Post-Sprint159 oneQay could open a shift, sell, summarize sales, correct eligible sales, and close shifts, but operational users still lacked line-level historical receipt inspection. Existing reporting exposed recent headers only, while canonical persistence already contained the necessary immutable transaction records.

### Objective / Gap

Bounded objective: `POS_SALE_HISTORY_DETAIL_WORKSPACE`.

### What changed

- Added `PosSaleHistoryWorkspaceRepository`, validated snapshot, and authorized view service.
- Added bounded latest-50 sale history and exact canonical sale lookup in exact tenant + organization + outlet scope.
- Added line-level receipt reconstruction exclusively from immutable `oneqay_pos_sale_lines` records.
- Preserved legitimate legacy `shift_id=null` records rather than inventing historical binding data.
- Added fail-closed validation for line sequence, quantity, multiplication, currency/scale, receipt sum, sale amount, void evidence, CASH-refund evidence, correction sequence, and correction scope.
- Reused existing deny-by-default reporting permission `pos.reporting.sales-summary.view`; no new permission provisioning dependency was introduced.
- Extended existing `PosOperationalReportingServiceProvider`; global provider registry remains unchanged.
- Added guarded GET `/pos/reporting/sales-history/{sale_id?}` and explicit `ONEQAY_POS_SALE_HISTORY_WORKSPACE_ENABLED`, default false.
- Added Vue/Inertia sale-history and receipt UI with exact lookup, state filters, line items, immutable evidence display, and precision-safe string atomic amounts.
- Intentionally did not join current catalog `display_name`, preventing mutable catalog data from being presented as historical receipt evidence.
- Added disposable SQLite regression covering scope isolation, exact lookup, foreign-scope non-disclosure, state precedence, legacy shift evidence, receipt arithmetic/integrity, runtime/feature fail-closed gates, refund-without-void rejection, and cross-scope correction-evidence rejection.
- Left sale/void/refund mutation authorities, `routes/web.php`, global provider registry, Composer manifest, migrations, and operational state unchanged.

### Evidence / Qualification

- Engineering PR: #738, squash merged.
- Parent canonical post-Sprint159 checkpoint: `d4931eb7822d844cf74b3a60e593c35b00b1cfce`.
- Final exact engineering head: `d159aa26c748c5a624f0f71fe6c135f0d4f36be9`.
- Complete surfaced exact-head PR-triggered matrix: **successful**.
- Sprint160 regression run `34847797273`: successful.
- M7.1 Application Regression run `34847797253`: successful.
- Governance Required Checks run `34847797404`: successful.
- PHP Foundation Regression run `34847797433`: successful.
- Sprint156 run `34847797352`, Sprint157 run `34847797227`, Sprint158 run `34847797400`, Sprint159 run `34847797361`, Sprint126 run `34847797292`, and Sprint148 run `34847797341`: successful.
- Repository-native Product Owner authorization: exact PR #738 and exact engineering head.
- `product-owner-merge-authority`: successful — exact-head authority verified.
- Engineering envelope: 10 paths; SHA-256 `f60bd3698cbc28cfccdf8b79c446e5e138203246afdb16aaea1c5637aa327181`.
- Canonical engineering squash commit: `e6ef6e77d8a2d0dea16d7c17dde78bece904b78b`.
- Post-merge verification: exactly one squash commit above `d4931eb7822d844cf74b3a60e593c35b00b1cfce`, with exactly the 10 qualified engineering paths.
- Post-Sprint160 reconciliation envelope: six paths; SHA-256 `388c671587b1d0e21206260c5f0003fb215d606df494eaa528b1e6d839de7847`.

### Operational boundaries / NO-GO

Sprint160 does not select or persist a durable target, dispatch capability/dependency producers, execute migration #27, provision permissions, produce real target-bound capability/dependency evidence, widen the Final Shift Close runtime allowlist, activate Final Shift Close, grant deployment authority, activate Technical Preview/Production, or activate the updater.

Machine-readable state remains target selection blocked with `selected_target=null`, migration #27 `NOT_EXECUTED`, permission provisioning `NONE`, feature activation `INACTIVE`, deployment authority `NOT_GRANTED`, Technical Preview and Production `NOT_AUTHORIZED`, and updater `INACTIVE`.

### Next position

The next engineering position is **Sprint161 bounded discovery from canonical post-Sprint160**. No Sprint161 objective, implementation, or source envelope is preselected.

## 4. Operational truth — NO-GO remains authoritative

- migration #27: `NOT_EXECUTED`;
- permission provisioning: `NONE`;
- selected target: `null`;
- target selection: `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- capability-evidence producer dispatch: `NOT_PERFORMED`; real capability evidence: `NONE`;
- dependency-evidence producer dispatch: `NOT_PERFORMED`; real dependency evidence: `NONE`;
- Final Shift Close runtime allowlist remains Local/Test/CI only;
- feature activation: `INACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview / Production activation: `NOT_AUTHORIZED`;
- updater activation: `INACTIVE`.

Machine-readable operational authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

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
