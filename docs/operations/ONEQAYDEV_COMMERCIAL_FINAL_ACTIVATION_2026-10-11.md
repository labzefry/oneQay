# oneQayDev Commercial Production — Final Activation Blocker and Repair (2026-10-11)

Author by Lab | zefry

## Operator evidence and purpose
- Operator-reported successful certified business release deployment: `409ac6b2bdb80d31fbfb9425ddbb413a278b97e6`, release `production-409ac6b2bdb8`; `HEALTH_READY_BUSINESS_OFF`, 6,252 files; staging and migrations untouched.
- Operator-reported first merchant provisioning: `ONEQAYDEV_FIRST_MERCHANT_APPLIED`, private credential `APPLIED`. Do not print, version-control or share credential.
- Live-host private preflight: `PREACTIVATION_BLOCKED`; database SELECT PASS, 43 tables, 27+ migration receipts, HTTPS PASS, business 503, nine essential feature flags OFF, session policy NOT READY.
- Explicit operator request: prioritize commercial Production Go-Live on **isolated oneQayDev only**. No authority for changing staging domain or executing migrations.

## Source-side correction
`apps/web/config/session.php` previously set `secure => null`, independent of `SESSION_SECURE_COOKIE` env. Therefore simply toggling env would not guarantee a secure session cookie. This PR requires Secure cookies **unconditionally when APP_ENV=production**, and preserves explicit config in Local/Test/CI. It adds tests and a focused CI workflow.

## Not a live activation
No host traffic switch, changes to `RELEASE.json`, schema change, merchant provisioning or shared runtime binding edits are performed by this PR. CI must certify a **new exact-source Production business release** after merge. Historical release `409ac6b2bdb8` must not be patched or relabelled; cPanel operator must deploy any new release side-by-side, with verified rollback.

Next (single combined host operation, NO SSH): prepare exact-source private candidate env; retain original 0600 runtime env, private merchant tuple, connected oneQayDev DB; activate durable session driver, Secure cookie and minimum CASH POS flags, while independent authority remains OFF. Verify Laravel route readiness, credential without disclosure, tenant scope, negative authorization cases and rollback. Only then bind separately authorized Production traffic flags and atomically activate private runtime + public bridge. Verify login/MFA, CASH sale, receipt/idempotency, stock, shift close, and reporting on the actual target. Record immutable sanitized evidence and keep staging untouched.

**Guardrail:** If the target cannot prove a secure cookie, persistent session, exact release and guarded rollback, remain `BUSINESS_OFF`. Never simply turn on environment switches on the old release.
