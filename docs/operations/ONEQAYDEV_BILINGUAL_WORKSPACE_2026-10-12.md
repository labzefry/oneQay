# oneQayDev bilingual workspace

Author by Lab | zefry

The owner requested Indonesian and English on 12 October 2026, Indonesian by default, and direct deployment to the existing cPanel hosting. cPanel login succeeded through the secure credential handoff. The active production source is `091312accfd0a0e3f3b7d9e45941852fca7335bc`; the successful recovery report SHA256 is `9f3c684d6de2a1b59bb80b52e0d55ba5d222c420c2768664e1a5d7442f5bb36f`.

## Interface

The application uses an explicit Vue translation catalog for Indonesian and English. The default is Indonesian even with an English browser locale. Only `oneqay.locale` (`id` or `en`) is stored locally. Invalid values and denied storage fall back to Indonesian; switching still works in memory. The native select is accessible by keyboard. Switching changes rendered text and document language without reloading, navigating, or clearing cart/form state. Known parameterized errors retain their variable values as Vue-escaped text.

Translation applies to fixed interface text, navigation, headings, accessibility labels, statuses, notices, login/MFA/recovery, all merchant POS pages, reports, preview pages and updater status. Merchant data, product names, IDs, recovery codes, provisioning material, financial computation, idempotency, transport, API enum values, permissions, sessions and feature gates are preserved. Backend files and dependency locks are unchanged. The only server-template edit is the exact HTML `lang="en"` to `lang="id"` change; all embedded PHP remains unchanged.

## Verification and deployment

The compiled browser suite retains the existing 19 financial and navigation checks and adds default language, switching and persistence, translated search, cart/payment continuity across language changes, translated checkout with unchanged request contracts, login form continuity, invalid/blocked storage, and mobile layout checks. These are isolated fixtures, never production transactions.

The new deployment operator pins the successful 091312 release, environment, entry, routing, recovery report, full source manifest and current public asset union. It verifies the immutable certified successor, preserves all existing runtime configuration except source/artifact bindings, uses guarded hardlink-first binding, verifies the native production gate and HTTPS, and retains hashed assets for open sessions. Any changed backend, lock, or server template beyond the HTML language tag is refused. Rollback restores the current 091312 business-on release including its full union assets, not the older 192 release or health-only state. It performs no migration, provisioning, credential change, staging change, updater activation or external payment.

Native certification and host deployment evidence must be recorded when complete. This interface task does not establish real MFA, sale or shift-closing business acceptance.
