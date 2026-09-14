# Changelog

## 2026-09-14 — Sprint163 closed

**Sprint163: operational cash variance reconciliation workspace**

- **Purpose / Why:** Final Shift Close already required explanation plus independent accepted review for non-zero cash variance, but operational POS delivery did not expose those existing durable authorities.
- **Objective / Gap:** `POS_CASH_VARIANCE_RECONCILIATION_WORKSPACE`.
- Added tenant + organization + outlet scoped, same-outlet cross-device reconciliation workspace.
- Rebuilt selected variance subjects server-side from canonical closing evidence using the existing expected-cash reader and `DeriveCashVariance`; browser requests cannot supply authoritative variance inputs.
- Reused existing explanation and reviewer permissions; no new permission identifier was created.
- Preserved maker-checker separation and terminal `REVIEW_REJECTED` semantics.
- Added guarded workspace/explanation/review routes, Operations Hub discovery, Vue/Inertia UI, and executable reconciliation regression.
- Exact engineering head `61a506f44315e7ac18ec08672bd9cb2aee81c838` completed the surfaced PR-triggered matrix successfully; Sprint163 run `34862683840` succeeded.
- Engineering envelope: 18 paths; SHA-256 `61077fd95f392e588a99d394f0ba3a0fc4d5b3187da850a1f4a796fa61eb5dbb`.
- Engineering PR #744 squash merged at `a920e63c1a1d2623664b416422c3d5a471e389a6`.
- Reconciliation envelope: six paths; SHA-256 `dd1e8acc008bbe3ca8491cff12b0327f204d63e0820286af0da786acc5f8d4f2`.
- **Operational boundaries / NO-GO:** selected target `null`; migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; feature activation `INACTIVE`; deployment authority `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`.
- **Next position:** Sprint164 bounded discovery; no objective or source envelope preselected.

---

This changelog records **material canonical progress**, not every intermediate compatibility or CI-only commit. Detailed provenance remains in merged pull requests, Git history, workflows, tests, and machine-readable contracts.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

## 2026-09-14 — Sprint162 closed

**Sprint162: guarded POS operations hub**

- **Purpose / Why:** Sprint156–Sprint161 delivered seven independently guarded POS pages, but there was no common POS home route, frontend layout, or authorized navigation owner.
- **Objective / Gap:** `POS_OPERATIONS_HUB`.
- Added exact tenant/organization/outlet/device operations-hub snapshot and application query.
- Reused only existing permissions; no hub-specific permission was introduced.
- Preserved canonical access composition: Shift Start requires open-shift + opening-cash; Cashier requires complete-sale; reporting/history reuse reporting view; corrections use void OR refund; catalog/opening-stock requires catalog + baseline; Shift Close reuses `pos.shift.close`.
- Added fail-closed named-route filtering with `Route::has()` so disabled or unregistered feature surfaces never become links.
- Target workspaces retain and re-run their own authorization gates.
- Added `/pos` / `pos.operations.hub` through a child provider; global provider registry remained unchanged.
- Added `ONEQAY_POS_OPERATIONS_HUB_ENABLED`, default false, with Local/Test/CI + persistence + exact-session delivery gates.
- Added responsive Vue/Inertia read-only operations UI and executable permission-composition regression.
- Discovery confirmed no canonical restock/stock-adjustment application authority; Sprint162 deliberately did not invent one.
- Complete surfaced exact-head PR-triggered matrix: successful on `2d75efbdb4b3eb2b98ad7973866fa6576b1ffd79`.
- Sprint162 regression run `34857221294`: successful.
- M7.1 `34857221166`, Governance `34857221221`, PHP Foundation `34857220990`, Sprint156–Sprint161, and all other surfaced historical runs: successful.
- Repository-native exact-head Product Owner merge authority: successful.
- Engineering envelope: exactly 9 paths; SHA-256 `afadd8577d794fffc100b8ab98d77dfc55599ead9d32963c1ef84ee8a086afd2`.
- Engineering PR #742 squash merged at `332bcff11b40307d350c7ce5b3a6c08913c4251c`.
- Post-merge verification proved exactly one squash commit over post-Sprint161 canonical main and exactly the qualified 9-path delta.
- Post-Sprint162 reconciliation envelope: six paths; SHA-256 `66500e314da09a14dbc35624dc0e0468c6ca3707bcf0a91261b4f850b207b734`.
- **Operational boundaries / NO-GO:** selected target remains `null`; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; feature activation remains `INACTIVE`; deployment authority remains `NOT_GRANTED`; Technical Preview/Production remain `NOT_AUTHORIZED`; updater remains `INACTIVE`.
- **Next position:** Sprint163 bounded discovery from canonical post-Sprint162; no objective or source envelope is preselected.

## 2026-09-14 — Sprint161 closed

**Sprint161: operational POS catalog and opening-inventory setup workspace**

- Added guarded catalog/opening-stock operational workspace over existing canonical catalog-preparation and one-time inventory-baseline authorities.
- Reused `pos.catalog.prepare` and `pos.inventory.baseline`; preserved explicit two-step setup and authoritative refresh after mutation outcomes.
- Initial exact-head CI caught malformed persisted-currency normalization; corrected final head fails closed instead.
- Engineering PR #740 squash merged at `33080c0b5f5c6f66e9994ad7f05ba78dea241294`.

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
