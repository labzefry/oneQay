# Changelog

This changelog records **material canonical progress**, not every intermediate compatibility or CI-only commit. Detailed provenance remains in merged pull requests, Git history, workflows, tests, and machine-readable contracts.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

## 2026-09-14 — Sprint161 closed

**Sprint161: operational POS catalog and opening-inventory setup workspace**

- **Purpose / Why:** canonical POS catalog preparation and one-time inventory baseline already existed as secure APIs, but operational users had no guarded UI to make a product sale-ready.
- **Objective / Gap:** `POS_CATALOG_INVENTORY_SETUP_WORKSPACE`.
- Added tenant/outlet-scoped catalog, sellable state, price, available quantity, prior-baseline state, sale-history state, and baseline eligibility visibility.
- Reused existing deny-by-default `pos.catalog.prepare` and `pos.inventory.baseline` permissions and named mutation endpoints.
- Preserved explicit catalog preparation → opening inventory sequence; no composite mutation or second stock engine was added.
- Added manual authoritative-refresh lock after mutation success or network ambiguity; no automatic retry.
- Added Local/Test/CI-only guarded delivery with persistence/session/capability gates and explicit workspace feature arming.
- Added Vue/Inertia operational setup UI and executable SQLite regression.
- Initial exact head `6cbb218d204e7e84ff6701328f6d884ccf5699ec` failed Sprint161 regression because malformed lower-case persisted currency was normalized into uppercase instead of rejected.
- Corrected final exact head `fbe8e91df756855d38b8c6656b17f27b4cc32585` requires persisted currency to already be canonical uppercase and fails closed otherwise.
- Complete surfaced final exact-head PR-triggered matrix: successful.
- Sprint161 regression run `34853239912`: successful.
- M7.1, Governance Required Checks, PHP Foundation, Sprint156–Sprint160, Sprint126/Sprint148, and all other surfaced historical runs: successful.
- Repository-native exact-head Product Owner merge authority: successful.
- Engineering envelope: exactly 11 paths; SHA-256 `66d7c616fbe8ae0e6c3c262fc8054db26bcbaa67e07ed77000363041b056f613`.
- Engineering PR #740 squash merged at `33080c0b5f5c6f66e9994ad7f05ba78dea241294`.
- Post-merge verification proved exactly one squash commit over post-Sprint160 canonical main and exactly the qualified 11-path delta.
- Post-Sprint161 reconciliation envelope: six paths; SHA-256 `fd24a20017a13eca06d93ade217b6c0a68db07c205b218b8d9c201122c6e7ccc`.
- **Operational boundaries / NO-GO:** selected target remains `null`; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; feature activation remains `INACTIVE`; deployment authority remains `NOT_GRANTED`; Technical Preview/Production remain `NOT_AUTHORIZED`; updater remains `INACTIVE`.
- **Next position:** Sprint162 bounded discovery from canonical post-Sprint161; no objective or source envelope is preselected.

## 2026-09-14 — Sprint160 closed

**Sprint160: immutable POS sale history and receipt detail workspace**

- Added read-only latest-50 sale history and exact sale receipt lookup over canonical persistence.
- Added immutable line-level receipt validation, correction evidence validation, foreign-scope non-disclosure, and precision-safe atomic money strings.
- Reused existing reporting authority; no mutation engine or migration was introduced.
- Engineering PR #738 squash merged at `e6ef6e77d8a2d0dea16d7c17dde78bece904b78b`.

## 2026-09-14 — Sprint159 closed

- Added operational sale correction workspace over canonical void and CASH-refund authorities.
- Engineering PR #736 squash merged at `e4e6a0f55bbb3b6fd4126df66f4815e281ad0dc4`.

## 2026-09-14 — Sprint158 closed

- Added resumable exact-device shift-start workspace over canonical shift-opening and opening-cash authorities.
- Engineering PR #734 squash merged at `d6eb8f7f359584130deea9ec0ed3572add3c05aa`.

## 2026-09-14 — Sprint157 closed

- Added operational POS cashier sale-entry workspace over canonical sale completion authority.
- Engineering PR #732 squash merged at `b4d21b208a0580f4b40565b028dd6aed9bb190b8`.

## 2026-09-14 — Sprint156 closed

- Added read-only tenant + organization + outlet scoped operational sales reporting.
- Engineering PR #730 squash merged at `259cc00037ee0d3fb909cbcf2c87d39ffb26f9b9`.

## Earlier material engineering history

Sprint88–Sprint155 established the Final Shift Close source/readiness chain through migration source materialization, runtime dependency/readiness, selected-target identity, attestation/selection binding, DB binding/control-plane hardening, capability/dependency evidence foundations, permission-provisioning binding, and source-only activation foundations. Earlier work established architecture/governance, authentication/session hardening, POS sale/payment/receipt/catalog/inventory foundations, full-sale void/refund, and cash/variance evidence.

## Current lifecycle boundary

Machine-readable authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

Current values remain migration #27 `NOT_EXECUTED`, permission provisioning `NONE`, target selection blocked with selected target `null`, real capability evidence `NONE`, real dependency-envelope evidence `NONE`, feature activation `INACTIVE`, deployment authority `NOT_GRANTED`, Technical Preview/Production `NOT_AUTHORIZED`, and updater `INACTIVE`.

## Sprint description standard

Each material sprint records: **Purpose / Why; Objective / Gap; What changed; Evidence / Qualification; Operational boundaries / NO-GO; Next position**.

Author by Lab | zefry
