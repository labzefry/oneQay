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
5. `apps/web/resources/js/pages/Platform/Control.vue`: platform owner control/SMTP styling migrated to approved tokens, readable form controls, responsive owner sidebar. Its owner write request now has bounded 30-second timeout, explicit queued and unknown outcomes, same-operation-only manual retry and session-expiry owner redirect. Platform backend authorization and SMTP health semantics unchanged.
6. `apps/web/scripts/check-ui-palette.mjs`, `apps/web/scripts/operation-feedback.test.mjs`, and existing integrated-business CI wiring: source guard and synthetic state-transition tests with build/browser checks in the workflow.

7. Seven-color style migration expanded to existing compiled Vue screens for POS Cashier, Shift Start, Shift Close, Business Access/Workspace, owner Login and public Registration, plus `apps/web/resources/css/website.css`; functional controllers, financial calculations, RBAC and server routing unchanged. Typography and focus tokens upgraded within migrated style blocks, with reduced-motion fallback on related cards. This is **style source adoption**, not qualified UX acceptance of every flow or new Windows-native release.

## Coverage NOT claimed / quality gates

- Full-site browser/visual contrast, focus, screen reader, 200% zoom, responsive matrix and owner visual approval remain **pending**. The earlier UI source head `4ed3311164766616704d0c14d725628bcd406525` passed Integrated Business Management run `39605669764` (including palette/operation tests, vue-tsc, compiled Vite/Playwright and relational SQLite/MySQL flows), Merchandising Reports Currency run `39605669412`, cPanel Windows POS Upgrade run `39605669179` and governance run `39605669806`. **These passing results are for the prior head only**; requalify the updated Platform source head in CI before any lifecycle promotion.
- Other custom styles including remaining Platform subpages, unmodified POS detail/report/stock components, native Windows POS source and installer: **not fully migrated in this increment**. Do not call the entire UI complete.
- Real HTTP mutation tests on hosting, SMTP inbox confirmation, cash/offline pilot and signed Windows updater: **not executed**.
- The status surface is intentionally nonmodal to avoid interrupting cashier operations; queued jobs without an authorized status endpoint stay queued/unknown rather than being marked complete.
- No database migrations, tenant/RBAC/SMTP/accounting logic changes, re-provisioning or host cron execution in this PR.
- Merge gate: exact head, required checks, review/risk acceptance and branch conflict checks. A merged source would still require separately certified cPanel release package and *fresh* host authorization. Do not reuse the prior 7ef operator/grant.

## Next implementation actions

1. Resolve any CI/build/UI browser failures for this exact head; verify 7-color palette/contrast and negative/timeout cases.
2. Migrate remaining platform subpages, public/auth/error, unmodified POS reports/stock/corrections and desktop-native UI in separately reviewable source increments. Distinguish truly queued jobs from completed operations.
3. Complete exact-source artifact build/manifest/checksum and successor operator qualified against predecessor `7efdda7648c2` with no historical migrations, then produce specific File Manager and `/usr/local/bin/php` Cron commands **only once real release/artifact/path/grant exist**.
4. After actual successful deployment receipt, verify active host/worker and archive only eligible old expanded releases privately with integrity verification; never touch active/shared/backup/database.

## Continued implementation — transactional feedback and bounded read operations

- POS Cashier now creates one sale operation ID per attempted checkout, blocks repeated writes while unresolved, closes spinner on a 30-second deadline, and treats 5xx, 202, offline, connection loss and late data as unconfirmed (not a successful receipt). Tracked/unlimited stock deduction and the authoritative backend sale calculations remain unchanged. Sale history must be checked by authorized staff before proceeding if the outcome is uncertain.
- Shift Start and opening-cash evidence use a bounded request with fixed operation IDs, no automatic retries, cleanup on navigation, distinct server rejection and unknown/queued responses. Final Shift Close and counted-cash commands gain Axios deadline, explicit queued-not-complete detection and uncertainty-based write protection. Original two-person approval and shift identity remain unchanged.
- Business Access and business command workspace now use a bounded authenticated request, preserve an unknown operation identity for diagnosis and avoid silently replacing it with a fresh operation; dedicated nonmodal status overlay is shown.
- Platform owner report/DNS and MFA checks, as well as mail refresh, now have bounded reads or verification requests; the owner POST write flow does not claim SMTP inbox delivery from queue acceptance.
- Report exports prepare actual PDF/XLSX blobs with download-feedback terminology that distinguishes browser download initiation from verified local file save. On 202, show a queued outcome; on 401/419, reenter authorized business sign-in; on timeout stop loading. These are read-only report requests.
- Operation feedback provides a manual dismiss action; confirmed success/explicit rejection notifications automatically recede after 6.5 seconds; unknown and queued states remain reviewable. Realtime **progress percentages are deliberately absent** because server progress is not measurable from these endpoints.

### Source/CI and host boundaries

All statements here are **branch source implementation**, not a live production acceptance. CI for a new commit must be fetched for that exact Git SHA; passing workflow runs from the earlier `4ed3311` head do not certify newer changes. Additional visual/accessibility/manual data acceptance and remaining untouched POS/Platform/native Windows surfaces are still outstanding. GitHub source is not the hosting release. No new migration was executed. No new successor operator/deploy ZIP/hash or real Cron value exists at this milestone; inventing one would risk overwriting the active `production-7efdda7648c2` host.

