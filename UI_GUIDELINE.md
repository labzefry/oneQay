## 2026-10-21 — Cashier/shift and owner operation feedback expansion (branch only)

Author by Lab | zefry. PR #978 draft adds bounded POS checkout with single immutable sale operation ID and no blind retry, shift cash/open/close timeout handling, queued-versus-confirmed responses, bounded RBAC and business commands, owner SMTP/DNS/report/MFA request ceilings, report download progress, and dismissible localized feedback. Seven-color styling was also extended across cashier, shifts, owner login, business workspace and public website. Business PHP, DB schema/migrations, payment semantics, stock tracked/unlimited, SMTP backend and offline queues are unchanged. The change is **not deployed**; complete CI, approval, missing UI surfaces and certified successor operator remain required. [Branch checkpoint](docs/operations/ONEQAY_UI_FOUNDATION_CHECKPOINT_2026-10-21.md).

**UI source coverage follow-on:** Seven-color and proportional typography styling now also touches cashier, open/close shift, business management/access, public registration, platform login, and shared website CSS; the source-level palette guard enumerates these migrated stylesheets. No claim of full app accessibility, client-operation completeness or native Windows coverage; new exact-head CI and interactive review still required. Hosting release remains unchanged.

## 2026-10-21 — Applied foundation on feature branch, not on hosting

**UI foundation follow-on — Platform Control:** Seven-color, readable owner/SMTP configuration and bounded owner operation feedback added to PR #978 source. Keep full-site modernization pending until remaining page/Windows coverage and exact-head CI/reviews are verified. Source not deployed.

The new shared web workspace foundation includes palette-locked CSS tokens, 16px body/inputs, 14px operational labels/tables, balanced morph depth, accessible focus and reduced-motion fallback. Business Settings/Products use a nonmodal status surface with no made-up numeric progress, timeout/unknown/queued/session-expired outcomes and same-operation manual verification. POS desktop settings now use bounded fetch and block re-submission of unknown/queued writes pending owner verification. This **does not yet** certify all scoped views: platform control, other POS flows, Windows native installer, public website styles and other custom scoped page CSS still require migration and browser validation. Automated palette guard includes only the named migrated stylesheets; do not treat unchanged legacy files as approved. Branch source is not deployed. See [checkpoint](docs/operations/ONEQAY_UI_FOUNDATION_CHECKPOINT_2026-10-21.md).

# oneQay UI/UX Guideline

## Owner-required successor design — 21 October 2026

For the next UI implementation, [ONEQAY_UI_PALETTE_2026-10-21.json](docs/design/ONEQAY_UI_PALETTE_2026-10-21.json) and [modernization/progress contract](docs/design/ONEQAY_UI_MODERNIZATION_2026-10-21.md) govern all owned surfaces. Exactly: Navy `#1B1B39`, Primary Blue `#3965FA`, Soft Blue `#99B7FC`, Pale Blue `#E9EBFF`, White `#FFFFFF`, Light Gray `#EDEDED`, Dark Gray `#202020`. These replace the historical teal workspace direction for future redesigned UI. The requirement is recorded; this docs-only handoff does not change current deployed styling.

Use shared tokens, readable 16px body/form and 14–16px operational tables, accessible restrained morph/depth, consistent Iconify, reduced motion and complete component states. Every user-triggered process has truthful operation-scoped progress and a terminal outcome; queued/timeout/unknown/offline are distinct, cleanup is bounded and mutations are never blindly retried. Refer to the full contract for acceptance cases, palette guard, contrast and ordinary-chat cPanel release handoff. Preserve existing tenant/security/business behavior.

## Experience principles

- Cepat dipahami pada lingkungan operasional sibuk.
- Konsisten lintas web, PWA, Android, admin, dan landing.
- Accessible, responsive, locale-aware, dan resilient.
- Kesalahan dicegah sebelum dijelaskan.
- Status transaksi, sinkronisasi, tenant/outlet, dan konsekuensi aksi selalu jelas.
- Visual polish tidak boleh mengurangi keterbacaan atau performa.

## Information architecture

Navigasi mengikuti pekerjaan pengguna, bukan struktur database. Platform admin, tenant admin, manager, kasir, inventory, finance, dan customer-facing experience memiliki surface serta permission berbeda.

Context aktif—tenant, company, outlet, register, business date—ditampilkan jelas. Perpindahan context berisiko memerlukan confirmation dan audit.

## Design tokens

Token kanonis mencakup color, typography, spacing, radius, elevation, motion, breakpoint, z-index, dan icon sizing. Token web merchant light ditetapkan melalui pembaruan workspace tanggal 11 Oktober 2026 sesuai permintaan pemilik; surface lain mengikuti coverage masing-masing. Hardcoded style berulang dilarang.

## Color and status

- Warna bukan satu-satunya pembeda.
- Success, warning, error, info, neutral memiliki icon/label.
- Contrast memenuhi baseline accessibility yang dipilih.
- Financial negative/positive harus mempertimbangkan konteks dan locale.
- Dark/light theme hanya dijanjikan setelah coverage component dan chart lengkap.

## Typography

Gunakan hierarchy terbatas dan konsisten. Angka transaksi menggunakan numeral yang mudah dibandingkan. Currency, quantity, unit, date, dan timezone tidak boleh ambigu. Text dapat membesar/reflow tanpa kehilangan fungsi.

## Components

Component memiliki states: default, hover, focus, active, selected, disabled, loading, error, dan read-only sesuai relevansi. Focus ring tidak boleh dihapus. Icon-only action membutuhkan accessible name dan tooltip bila perlu.

## Forms

- Label persisten; placeholder bukan label.
- Required/optional jelas.
- Validasi sedekat mungkin dengan field dan ringkasan error tersedia.
- Format tidak mengubah input pengguna secara mengejutkan.
- Save memiliki progress, success/error, dan duplicate-submit protection.
- Destructive/financial action menampilkan object, consequence, amount, dan recovery.

## Tables and data density

Gunakan table untuk perbandingan data exact; card untuk ringkasan/aksi. Table memiliki responsive strategy, sort/filter state, pagination bounded, empty/error/loading, keyboard support, dan column visibility yang konsisten. Horizontal scroll hanya bila struktur data memang membutuhkan.

## POS-specific UX

- Critical action dapat dilakukan dengan sedikit langkah dan keyboard/touch.
- Cart, price, tax, discount, total, payment, change, dan transaction state selalu terlihat.
- Duplicate submission dan accidental navigation dicegah.
- Offline/degraded state terlihat jelas dan tidak memberi false success.
- Void/refund memerlukan permission, reason, confirmation, dan receipt/audit outcome.

## Feedback states

Loading tidak menghilangkan context. Empty state menjelaskan sebab dan next action. Error memiliki safe message, retry bila aman, dan reference/correlation ID. Partial/stale data diberi timestamp dan scope. Optimistic UI hanya digunakan bila rollback visual/data aman.

## Accessibility

- Semantic structure dan accessible names.
- Full keyboard navigation dan logical focus order.
- Focus management pada dialog, route, error, dan async update.
- Sufficient contrast, touch target, zoom/reflow.
- Screen reader announcement untuk status penting.
- Reduced motion support.
- Caption/transcript untuk media bila digunakan.

## Responsive strategy

Mobile-first untuk flow yang sesuai, tetapi POS desktop/tablet dapat memiliki optimized workspace. Breakpoint berasal dari kebutuhan content, bukan device populer. Feature parity dan permission harus dijaga; mobile tidak boleh menjadi jalan memotong kontrol.

## Localization

UI tidak menggabungkan string secara manual. Gunakan translation key, pluralization, locale-aware number/currency/date, timezone tenant, dan layout yang tahan text expansion. Bahasa default dan supported locale ditetapkan Product Owner.

## PWA and offline

Offline capability ditentukan per use case. UI membedakan online, offline, queued, syncing, conflict, failed, dan synced. Tidak ada transaksi dianggap selesai hanya karena tersimpan lokal jika server acknowledgement dibutuhkan.

## Charts and dashboards

Chart digunakan bila memperjelas hubungan; table/data label tersedia untuk nilai exact. Axis, unit, period, timezone, filter, source, freshness, dan empty state wajib. Warna series accessible dan tidak menyesatkan. Dashboard tidak boleh menampilkan data lintas tenant akibat cache/filter.

## Security and privacy UX

Sensitive value masked by default. Copy/reveal/export membutuhkan permission dan audit sesuai risk. Authentication error tidak membocorkan account existence. Session expiry menjaga draft bila aman. Consent dan privacy notice harus spesifik serta dapat dipahami.

## Performance budgets

Tetapkan budget untuk first load, interaction, bundle, image/font, API payload, dan chart rendering per platform. Skeleton digunakan hanya bila membantu orientasi. Third-party script harus direview privacy, security, dan performance.

## UX Definition of Done

Flow dan role tervalidasi, component states lengkap, responsive/accessibility/localization diuji, loading-empty-error-offline tersedia, security consequence jelas, analytics/event tidak membocorkan data, serta UI_GUIDELINE/TASKS/CHANGELOG diperbarui.

## Merchant workspace implementation — 2026-10-11

- Light canvas `#f4f7f8`, ink `#152a35`, navy navigation `#112c35`, teal action `#087f6f`, line `#e2e9ed`, base card radius 16px. Canonical variables live in `apps/web/resources/css/app.css`.
- Shared shell covers authenticated `Pos/*` pages; anonymous foundation/login keeps its existing layout. Desktop navigation becomes a keyboard-accessible drawer at 800px.
- Navigation uses the current server-delivered destinations. Deep pages request only the same-origin read-only overview and discard stale, mismatched-scope, redirected, or denied responses. No client-invented grants or browser-stored context.
- Overview displays actual catalog, sellable, shift, and opening-cash readiness. Unknown values remain unverified. No fabricated revenue, charts, stock, outlet names, product photos, or SKU.
- The cashier preserves its existing transaction computation and transport. A pending cart or submission triggers confirmation before leaving. Print hides application navigation while retaining the receipt.
- Icons are bundled Lucide SVG data; ISC attribution/license is retained. No external font or icon service is required. This release covers the light theme only.

## 2026-10-21 — Mail delivery, category creation and stock policy

Stage 1 source changes: independent SMTP/NOC worker phases; live owner delivery reports and safe SMTP error guidance; automatic 72-hour email log retention protecting active queues/dedupe; tenant/outlet category creation; explicit tracked/unlimited stock through canonical sale/void, web and offline desktop 0.2.1. Two additive module migrations require a separately qualified deployment. Hosting remains c2e (PR #974); deployment is deferred per Product Owner. [Implementation, schema, compatibility and validation](docs/implementation/MAIL_CATALOG_STOCK_20261021.md). Author by Lab | zefry.

## 2026-10-23 — Activation, email and native POS palette scope

Activation now uses readable 16px forms, a restrained morph card, accessible focus, reduced motion and the exact owner seven-color palette. Native POS stylesheet is migrated to that same palette, with readable body/input size, solid button contrast and a login-screen update area. Email uses inline table layout, 16px text, no external images and an escaped first-party activation button with a plain-text fallback. The palette guard now includes these named activation and POS stylesheets; it does not certify every unrelated legacy UI.

## Owner surface/text rule — 28 October 2026
White backgrounds require Black Onyx (`#202020`) for all text. Colored backgrounds require white (`#FFFFFF`) text. Use dark/primary approved fills when white text is required; pale text-bearing fills normalize to white to retain contrast. The shared web/Windows build theme in `packages/ui-theme/surface-colors.mjs` enforces the rule. Keep decorative borders/charts within the existing seven-color palette.

## 2026-10-29 — Menu cover editor (source draft)

Distinguish upload selection, saved draft and explicit publication. Use contained image preview, proportional labels, localized errors, bounded 30-second progress and reduced-motion support. White surfaces use black onyx; navy/primary fills use white. Mobile cover preserves aspect ratio. No unfinished table-order or photo controls are advertised as complete. See [menu contract](docs/operations/MENU_MEMBERSHIP_ROADMAP_2026-10-29.md).

### 29 October continuation

Cover gallery displays usage, 12-image pages, saved choices and unused-image deletion with confirmation. Cleanup is disabled while publication fields are unsaved. Public menu page changes use a brief transform with reduced-motion fallback; no fake numeric progress. Browser visual verification remains pending.
