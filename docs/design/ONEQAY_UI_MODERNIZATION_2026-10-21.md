# oneQay — modern UI/UX and reliable progress contract

Author by Lab | zefry. Owner requirements received 21 October 2026. **Requirements recorded; UI implementation pending.** Continue existing application architecture and features. This document is the visual/interaction brief, not a claim that a redesign is already deployed.

## Exact palette is a lasting product constraint

Read [machine-readable palette](ONEQAY_UI_PALETTE_2026-10-21.json) before any visual work. The uploaded reference contains printed HEX labels; their literal values take precedence over sampled PNG pixels, which differ slightly. Preserve its filename/SHA in the JSON. The reference's third-party brand/copy must not appear as oneQay branding.

| Token | Exact color | Intended use |
| --- | --- | --- |
| Navy Blue | `#1B1B39` | Navigation, headings, strong text |
| Primary Blue | `#3965FA` | Primary buttons, links on white, focus |
| Soft Blue | `#99B7FC` | Secondary accents and data surfaces with dark labels |
| Pale Blue | `#E9EBFF` | Selected rows, support panels, soft canvas |
| Base White | `#FFFFFF` | Cards and primary content canvas |
| Light Gray | `#EDEDED` | Neutral surfaces and restrained separators |
| Dark Gray | `#202020` | Body text and key numeric content |

Exactly seven base colors. No independent teal, green, red, amber, purple, new white/gray shades or dark theme. Define semantic tokens in the existing design system; avoid duplicate per-page palettes and arbitrary CSS/Tailwind color values. Replace legacy hues in the redesigned UI only through a coordinated change. Do not recolor customer-owned uploaded logos/photos or alter fidelity of printed business assets merely to satisfy a CSS color rule; those are content assets, not new theme tokens.

Depth uses white/pale/neutral surfaces and restrained shadows/overlays derived only from these base colors with alpha. Evaluate composited contrast. No unapproved solid tints or decorative rainbow gradients. Charts use palette colors plus patterns/dashes/direct labels when more series are needed. Errors/success/warnings remain unambiguous through explicit Indonesian/English labels, suitable icons and shape; do not add unauthorized semantic colors.

Normal text contrast >=4.5:1; large text and meaningful non-text controls >=3:1 where the criterion applies. Palette calculations: blue/white about4.76:1, navy/white16.63:1, dark/light-gray13.92:1, navy/soft-blue8.34:1; blue/pale-blue only4.03:1, so use navy/dark for normal text there. Light-gray boundaries alone do not define a visible input/focus boundary. Test actual disabled, hover, selected, focus and composited states, not only token pairs.

## Visual direction and scope

Modern enterprise interface: clear hierarchy, generous but economical spacing, consistent navigation, purposeful cards/tables, precise alignment, readable data density, restrained depth and responsive composition. Apply a consistent identity across public site, registration/login, control plane/NOC/SMTP/CMS, business workspace/configuration/catalog/people/reports, cashier/shift views, error pages and desktop POS source. Preserve server-driven navigation and permission distinctions; modernizing a menu does not implement missing business logic.

Morph means subtle soft depth and fluid container/state transitions: raised cards where useful, gentle inset read-only wells, dialog/container transforms and small state changes. It does not mean low-contrast soft buttons, translucent financial text, excessive blur, long animations or unclear input borders. Use a solid, accessible fallback where shadow/blur is unsupported. Prioritize operational speed on Windows10-class low-spec hardware; avoid permanent GPU-heavy effects. No new animation library without a concrete need and reviewed cost.

Use existing Iconify-backed assets/components consistently, with offline-safe bundled icons for Windows POS. Avoid introducing a different icon family per screen. Icon-only actions need accessible names; preserve attribution/license in source. UI footer is copyright oneQay. `Author by Lab | zefry` remains source/docs attribution.

Typography is proportional: body/forms16px (1rem), secondary labels14px minimum for operational content, tables14–16px, section headings20–24px, page headings24–32px, restrained display headings where appropriate. Use rem/clamp, line-height around1.45–1.6, system/existing locally packaged font, clear weights and tabular numerals for money/quantity. No shrink-to-fit tiny controls, fixed text zoom or unreadable metadata. Verify 200% zoom and language expansion. Buttons/input hit area target40–44px, with POS touch actions44px or more as a product target.

Use consistent spacing/radii/elevation/motion tokens. Initial implementation values may be adjusted for content, accessibility and low-spec performance without changing approved colors. Micro-interactions generally120–200ms; dialogs/container transitions180–280ms. These are product implementation starting points, not a claim that a standard mandates those durations. Respect prefers-reduced-motion and provide static status text.

## Progress popup contract — every user-triggered process

Every meaningful user action gives clear progress feedback, success or actionable error: save/edit, upload/import, report/PDF/Excel generation/download preparation, SMTP test, business registration, shift open/close, print preparation and desktop sync/update. Reuse one operation-status model and presentation component rather than scattered booleans/timers. Delay popup appearance around200–300ms to avoid flash for instant work; instant work still gives final feedback. Background polling/autosave uses a quiet status indicator rather than repeatedly stealing focus. Avoid queuing a popup for every scan or keypress in fast POS work; show the relevant operation's status in its existing flow.

Track states explicitly: idle, pending, running, succeeded, failed, cancelled, timed-out, unknown/pending-verification, and offline-queued where appropriate. State is scoped to a unique operation ID and tenant/outlet/session context. Keep authoritative backend/offline acknowledgments distinct.

Required behaviors:

- Stop spinner on every terminal outcome; close active overlay, unlock valid controls, clear timers/listeners and restore focus. Use finally/onFinish for cleanup without turning a failure into success.
- A backend HTTP202/queued response is not completion. Track its job/operation ID until an authoritative terminal status. SMTP accepted is not inbox delivery, print spool accepted is not paper printed, and browser download initiation is not OS-level download completion.
- Show a numeric percentage only when total/progress is measured. Otherwise show an indeterminate animation with stage labels. Never fabricate100% or a fake time estimate.
- Timeout policy is per operation, with a bounded network wait and a visible safe next action. On timeout of a mutation, show unknown/pending verification and look up the same operation before considering retry; do not claim failure or resubmit blindly. A bounded animation alone cannot certify backend completion.
- Prevent duplicate submit; retries preserve canonical idempotency requirements. Never retry money, shift close or import commit as a new operation merely to dismiss a stuck loader.
- Route/unmount/session expiry cancels appropriate reads and stale UI listeners. AbortController does not undo a committed server mutation. Ignore late responses from superseded requests and mismatched tenant/session context. No cross-tab/global loader may block an unrelated operation forever.
- Long jobs survive popup dismissal where the server supports it; allow reopening status safely. Offer cancel only for truly cancellable work. Give background operations a non-blocking progress center/status surface. Server-side bounded expiry/status is needed if the client disappears.
- Network loss, offline queued transaction, conflict and failure get different messages. Stop spinners when work becomes offline-queued; preserve durable POS local data. Reconnect does not mean synced until server acknowledgement.
- SMTP polling is visibility-aware, bounded, cleaned up and preserves unsaved fields. Resetting a polling interval cannot overwrite a completed SMTP test with stale status.
- Display safe correlation/error code for NOC; do not expose JSON, stack traces, recipient/body or SMTP secrets to unauthorized users.
- Focus management, dialog semantics, aria-busy, aria-live/status/progressbar and keyboard interactions must match the actual operation. Do not disable Escape or trap focus in non-modal background feedback.

## Coverage and implementation sequence

1. Read current checkpoint/brief and map existing shared shells/components once. Reuse current business functions; avoid a new audit program.
2. Install shared palette/typography/elevation/motion tokens and operation-feedback primitives in the existing web/desktop stack. Keep app architecture and API contracts intact.
3. Complete control-plane/business shells and the visibly cluttered business/POS configuration forms; apply the same controls to SMTP/categories/stock. All button text remains visible in every state.
4. Cover cashier/shift/receipt configuration, people/job descriptions, reporting/import, public/auth/error pages and desktop POS source. Maintain actual functional coverage; do not replace working screens with decorative mockups.
5. Test the critical journey and async edge cases below, publish exact-source evidence, squash only through applicable gates, then prepare the ordinary-chat deployment packet. Web release, Windows installer/build and signed updater promotion remain distinct deliverables.

## Focused acceptance evidence

- Palette token allowlist/lint catches unapproved theme literals in changed owned UI sources. Existing third-party/vendor/user content is excluded intentionally and documented. A scanner must not accidentally bless the unchanged legacy UI as migrated.
- Contrast, keyboard, focus, 200% zoom, reduced motion and Indonesian/English states work at representative mobile/tablet/desktop widths320/768/1366/1920px. Do not claim WCAG conformance solely from an automated scan.
- Meaningful compiled UI tests: immediate success, delayed success, validation422, denied403, expired401/419, server500, disconnect, aborted read, timeout/unknown mutation, queued job then completion, stale late response, duplicate click, route/unmount and concurrent operations. Verify spinner removal, focus restoration, control re-enabling and zero duplicate commits. Use fake timers where useful rather than flaky wall-clock sleeps.
- Preserve business RBAC, tenant/outlet scope, stock tracked/unlimited sale+void behavior, numeric/currency validation, PDF/Excel/import, SMTP reporting retention, shift flows and offline sync. Do not rewrite backend accounting for a cosmetic task.
- Capture before/after screenshots using synthetic/redacted data, route/component coverage and honest build/test results. No UI screenshot exposes real tenant data or credentials.
- Update UI_GUIDELINE, palette contract, TASKS, CHANGELOG, PROJECT_MANIFEST and next handoff with completed/pending evidence each coherent milestone. No placeholder menu or passing build is called full feature acceptance.

## Design references and interpretation

Primary references checked21October2026: [W3C WCAG2.2](https://www.w3.org/TR/WCAG22/) for contrast/status/accessibility and [Microsoft Fluent2 motion](https://fluent2.microsoft.design/motion) for purposeful, quick transitions/container relationships. These inform interaction quality, not a requirement to import their colors, brand, framework or components. The palette, font targets, timing defaults and operation model above are oneQay product requirements/engineering proposals, not copied standards text. Favor mature, accessible patterns over an unsupported claim that a particular trend guarantees quality.
