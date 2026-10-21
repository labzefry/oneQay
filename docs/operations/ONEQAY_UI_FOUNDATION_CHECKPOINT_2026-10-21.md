# oneQay UI/UX modernization — incremental source checkpoint, 21 October 2026

Author by Lab | zefry.

## Canonical separation

- Parent GitHub main was `cefd6724218fcef1de112836cfb514b9abc1c7b6` (PR #977 already merged).
- Current implementation branch: `agent/ui-enterprise-palette-feedback-20261021`. Review its actual head/CI before merge. Until an independently verified merge, **main is not this source**.
- Hosting oneQayDev remains **`production-7efdda7648c2`**, with 38 migration receipts and 88 database tables; no UI release is deployed.
- Windows POS desktop **source**, installed Windows **installer**, hosting candidate **artifact** and operator are separate products. No signed patch/installer/update kit is implied.

## Implemented source in this milestone

1. Canonical seven-color CSS: `apps/web/resources/css/app.css`, replacing historical workspace CSS colors (including alpha shadows). Normal text/control sizes, consistent focus, white cards, pale-blue selection, restrained depth and reduced motion. Color literals in the migrated stylesheet are guarded; site-wide legacy styles are not yet certified.
2. `apps/web/resources/js/pages/Business/DesktopSettings.vue` and `apps/web/resources/js/layouts/LocalizedShell.vue`: standard colors, readable settings controls, responsive kitchen/tab layout and shadow simplification. Desktop settings POST has bounded timeout, session-expired redirect, explicit unknown/queued state, and blocks subsequent writes pending status verification.
3. `apps/web/resources/js/use-business-mutation.ts` and `apps/web/resources/js/components/OperationFeedback.vue`: operation-scoped nonmodal feedback, indeterminate-only loading, cleanup on unmount, defensive duplicate-click prevention, same-ID manual retry when server state is unknown, queued (202) versus saved state, safe errors, session expiry handling. CSS defines presentation centrally.
4. `apps/web/resources/js/pages/Business/Settings.vue` and `apps/web/resources/js/pages/Business/Products.vue`: integrated shared feedback on settings/product/category/import-commit actions. Existing backend and amount/stock rules not modified.
5. `apps/web/scripts/check-ui-palette.mjs`, `apps/web/scripts/operation-feedback.test.mjs`, and existing integrated-business CI wiring: source guard and synthetic state-transition tests with build/browser checks in the workflow.

## Coverage NOT claimed / quality gates

- Browser end-to-end, dynamic color/contrast, focus, screen reader, 200% zoom, responsive matrix, owner visual approval and MySQL integrated business tests: **pending CI results and/or actual browser evidence** until run and checked.
- Other custom styles including `website.css`, `Platform/Control.vue`, cash drawer/shift/other POS pages, desktop native source and installer: **not fully migrated in this increment**. Do not call the entire UI complete.
- Real HTTP mutation tests on hosting, SMTP inbox confirmation, cash/offline pilot and signed Windows updater: **not executed**.
- The status surface is intentionally nonmodal to avoid interrupting cashier operations; queued jobs without an authorized status endpoint stay queued/unknown rather than being marked complete.
- No database migrations, tenant/RBAC/SMTP/accounting logic changes, re-provisioning or host cron execution in this PR.
- Merge gate: exact head, required checks, review/risk acceptance and branch conflict checks. A merged source would still require separately certified cPanel release package and *fresh* host authorization. Do not reuse the prior 7ef operator/grant.

## Next implementation actions

1. Resolve any CI/build/UI browser failures for this exact head; verify 7-color palette/contrast and negative/timeout cases.
2. Migrate remaining Platform Control, public/auth/error, cashier/shift and desktop-native UI in separately reviewable source increments. Distinguish truly queued jobs from completed operations.
3. Complete exact-source artifact build/manifest/checksum and successor operator qualified against predecessor `7efdda7648c2` with no historical migrations, then produce specific File Manager and `/usr/local/bin/php` Cron commands **only once real release/artifact/path/grant exist**.
4. After actual successful deployment receipt, verify active host/worker and archive only eligible old expanded releases privately with integrity verification; never touch active/shared/backup/database.
