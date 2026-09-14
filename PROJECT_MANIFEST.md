# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Canonical engineering checkpoint:** Sprint161
**Canonical engineering commit:** `33080c0b5f5c6f66e9994ad7f05ba78dea241294`
**Latest engineering PR:** #740 — `Sprint161: add operational catalog inventory setup workspace`
**Status date:** 2026-09-14

> This file is the canonical human-readable source of truth for current oneQay project status. Machine-readable operational state remains authoritative for operational gates.

## 1. Current project state

oneQay remains an actively engineered enterprise business-management platform using Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

Sprint161 closed the bounded P1 gap `POS_CATALOG_INVENTORY_SETUP_WORKSPACE`. Canonical POS already had durable catalog-preparation and one-time inventory-baseline mutation authorities, but both were API-only while the cashier required prepared catalog items with stock. Sprint161 added a guarded operational setup workspace over those existing authorities rather than introducing a second mutation engine, migration, or stock-adjustment contract.

The workspace preserves an explicit two-step flow: catalog preparation, then opening inventory baseline. It requires the existing `pos.catalog.prepare` and `pos.inventory.baseline` permissions, reuses the existing named POST endpoints, exposes current tenant/outlet catalog and stock state, and derives baseline eligibility from the same conditions owned by the canonical baseline mutation: current quantity zero, no prior baseline, and no sale history for that product in the tenant/outlet.

After successful mutation or network ambiguity, further mutation is blocked until authoritative refresh. Persisted monetary currency evidence must already be canonical uppercase; Sprint161 exact-head CI caught and corrected an initial read-side normalization defect that would otherwise have transformed malformed persisted currency into apparently valid evidence.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest completed engineering sprint | Sprint161 |
| Canonical engineering commit | `33080c0b5f5c6f66e9994ad7f05ba78dea241294` |
| Latest engineering PR | #740, squash merged |
| Final engineering head | `fbe8e91df756855d38b8c6656b17f27b4cc32585` |
| Exact-head CI | Complete surfaced PR-triggered matrix successful |
| Product Owner authority | `product-owner-merge-authority=success` on final exact head |
| Engineering envelope | 11 paths; SHA-256 `66d7c616fbe8ae0e6c3c262fc8054db26bcbaa67e07ed77000363041b056f613` |
| Post-Sprint161 reconciliation envelope | 6 paths; SHA-256 `fd24a20017a13eca06d93ade217b6c0a68db07c205b218b8d9c201122c6e7ccc` |
| POS operational reporting | Materialized through Sprint156 |
| POS cashier workspace | Materialized through Sprint157 |
| POS shift-start workspace | Materialized through Sprint158 |
| POS sale correction workspace | Materialized through Sprint159 |
| POS immutable history/receipt workspace | Materialized through Sprint160 |
| POS catalog/inventory setup workspace | Materialized through Sprint161 |
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

Bounded POS engineering includes shift/register opening, sale completion/payment/receipt evidence, catalog preparation, inventory baseline, durable stock mutation, full-sale void, full CASH refund, cash variance/adjudication, operational reporting, cashier sale entry, shift start, sale correction, immutable sale history/receipt detail, and catalog/opening-inventory setup.

Sprint88 through Sprint155 established the Final Shift Close source/readiness chain through migration #27 materialization, runtime dependency/readiness, target selection and binding controls, capability/dependency evidence foundations, permission-provisioning binding, and source-only activation planning. Operational execution remains separately gated and has not occurred.

## 3. Sprint161 description and closure evidence

### Purpose / Why

Post-Sprint160 discovery proved catalog preparation and inventory baseline mutation engines already existed, but no operational page allowed an authorized user to make a product sale-ready without calling APIs manually.

### Objective / Gap

`POS_CATALOG_INVENTORY_SETUP_WORKSPACE`.

### What changed

- Added validated catalog/inventory setup read contract and snapshot.
- Added tenant/outlet-scoped read repository with maximum 250 catalog rows.
- Exposed sellable state, precision-safe atomic price strings, available quantity, prior-baseline state, sale-history state, and baseline eligibility.
- Reused existing deny-by-default `pos.catalog.prepare` and `pos.inventory.baseline` permissions.
- Reused existing named POST endpoints `pos.catalog.prepare` and `pos.inventory.baseline`.
- Preserved explicit two-stage catalog preparation then opening inventory; no composite mutation was introduced.
- Added authoritative-refresh lock after success or uncertain network outcome; no automatic mutation retry.
- Added fail-closed `ONEQAY_POS_CATALOG_INVENTORY_SETUP_ENABLED`, default false.
- Added a child service provider registered outside the global provider registry.
- Restricted delivery to Local/Test/CI plus persistence, exact session controls, existing catalog/baseline capability flags, and explicit workspace arming.
- Added Vue/Inertia setup UI and disposable SQLite regression.
- Corrected strict persisted-currency validation after the first exact-head candidate exposed normalization of malformed lower-case evidence.
- Left canonical mutation owners, `routes/web.php`, global provider registry, Composer metadata, migrations, and operational state unchanged.

### Evidence / Qualification

- Parent canonical post-Sprint160 checkpoint: `92d932020ef95bdc26460a4944841d141d6fad5b`.
- Engineering PR: #740, squash merged.
- Initial head `6cbb218d204e7e84ff6701328f6d884ccf5699ec` was disqualified by the Sprint161 executable regression because malformed persisted currency was normalized instead of rejected.
- Final exact engineering head: `fbe8e91df756855d38b8c6656b17f27b4cc32585`.
- Sprint161 regression run `34853239912`: successful on the final exact head.
- M7.1, Governance Required Checks, PHP Foundation, Sprint156–Sprint160, Sprint126/Sprint148, and all other surfaced PR-triggered historical runs: successful on the final exact head.
- Repository-native Product Owner merge authority: successful for PR #740 and the final exact head.
- Engineering envelope: exactly 11 paths; SHA-256 `66d7c616fbe8ae0e6c3c262fc8054db26bcbaa67e07ed77000363041b056f613`.
- Canonical engineering squash: `33080c0b5f5c6f66e9994ad7f05ba78dea241294`.
- Post-merge verification: exactly one squash commit above post-Sprint160 canonical main and exactly the qualified 11 engineering paths.
- Post-Sprint161 reconciliation envelope: exactly six canonical paths; SHA-256 `fd24a20017a13eca06d93ade217b6c0a68db07c205b218b8d9c201122c6e7ccc`.

### Operational boundaries / NO-GO

Sprint161 does not select or persist a durable target, dispatch capability/dependency producers, execute migration #27, provision permissions, create real target-bound evidence, widen the runtime allowlist, activate Final Shift Close, grant deployment authority, activate Technical Preview/Production, or activate the updater.

Machine-readable state remains target selection blocked with `selected_target=null`, migration #27 `NOT_EXECUTED`, permission provisioning `NONE`, feature activation `INACTIVE`, deployment authority `NOT_GRANTED`, Technical Preview and Production `NOT_AUTHORIZED`, and updater `INACTIVE`.

### Next position

The next engineering position is **Sprint162 bounded discovery from canonical post-Sprint161**. No Sprint162 objective, implementation, or source envelope is preselected.

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
