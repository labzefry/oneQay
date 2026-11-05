# Windows POS 0.2.8 candidate — persistent cashier sign-in and local screen lock

Status: implementation candidate; not merged, deployed or published. Local validation on continuation: PHP syntax and SQLite persistent-session regression PASS (98 assertions including existing harness); Vue type-check/Vite build PASS; desktop cashier/QR/member/lock UI PASS; service UI PASS for laundry, boarding and homestay; shared palette tests PASS (4) and operation-timeout tests PASS (4). Native Windows compilation/DPAPI tests, MySQL, exact-head CI and final independent implementation review remain pending. Never advertise an installer until the signed release and compatible deployed API are verified.

## User outcome and scope

Windows retains the same cashier until explicit logout, including closing/reopening the application. Automatic screen lock is independent: off, 1, 2, 5, 10, 15 or 30 minutes, configured in **Kunci & akun / Lock & account** per cashier profile on the current computer. Manual lock survives restart even when automatic lock is off. A lock uses the existing owner-managed PIN; it does not create a new password or log out the cashier.

The candidate also packages the already-merged shared web/Windows member register/search and QR lookup, edit/quote, confirmation, cancellation and settlement workspace. Retail/F&B and generic commercial checkout remain separate from laundry, boarding and homestay workflows. Shift opening/closing, offline operation journal, stock counting, receipts, kitchen routing, printer/drawer setup, sync, and signed in-app updates remain present. Business administration stays in the web portal.

Member self-service login, loyalty points/redemption, ordinary offline sale member attribution and member email flows are not claimed as newly delivered.

## Authentication and API contract

Existing browser sessions retain 7200-second idle and 43200-second absolute TTL. Existing v2 native and pairing grants retain their previous behavior. Only the new authenticated native v3 login issues a persistent authority.

- `POST /api/pos/desktop/v3/login`: same bounded login/password/optional-TOTP fields as v2. Requires the new schema; returns the same opaque bearer and protocol-2 cashier snapshot. No client-supplied scope, authority kind or lifetime is accepted.
- `POST /api/pos/desktop/v3/logout`: self-revokes the bearer and its linked canonical authority transactionally. Possession of the token is required; it works even if that cashier is disabled or its old authority is no longer active. It is idempotent and does not accept target IDs.
- `GET /api/pos/desktop/v3/capabilities`: no-store, public readiness signal for the persistent native session schema and running source commit; no tenant/user/session data.
- Existing v2 snapshot/sync and v3 service/QR routes validate the new marked authority through the shared desktop authentication boundary.

An additive module migration adds `authority_kind`, default `browser`, to canonical session rows. Persistent native rows must be explicitly marked `desktop_persistent` AND have the 64-bit non-expiring sentinel `PHP_INT_MAX`. An inflated expiry on an unmarked browser row never grants persistent authority. Unknown kinds fail closed. Missing schema prevents native v3 login; no old session is promoted.

Canonical session inventory/revoke-one/revoke-others/revoke-all continue including native grants; their expiry remains a compatible integer field and additive `session_kind=desktop_persistent`, `persistent_until_logout=true` tell consumers to display **Hingga logout / Until logout** rather than formatting the sentinel as a date. Credential/factor epochs, TOTP state, tenant status, enabled cashier, exact organization/outlet/device and current POS RBAC are checked online. Browser activation explicitly rejects native authorities.

## Local storage and lifecycle

The bearer, remembered profile/identity/scope, lock/activity record and pending logout revocations are protected using Windows DPAPI in SQLite metadata. Only an allowlisted profile basename derived from normalized login may be opened. Passwords are not stored in plaintext; the existing encrypted password verifier/PIN verifier remain native-only. No bearer/password/PIN verifier is returned to the webview or stored in browser localStorage.

On startup the native resume command first validates the saved bearer with the server. Only a genuine unavailable/interrupted transport permits cached resume within the existing offline lease. TLS/auth/permission failures are not treated as offline. Expired offline authority requires reconnection and offers resume without a new password; the remembered account is retained. Offline transaction leases remain bounded to 8 hours and are independent of remembered sign-in.

Explicit logout atomically clears the remembered active profile, persists a per-profile signed-out tombstone and queues the encrypted revocation before network I/O; memory is signed out before any fallible profile write. Known server authentication/permission denial uses the same tombstone-before-profile-write ordering at the common native request boundary. A failed profile write cannot resurrect the account through PIN unlock or cached offline resume. Profile receipts and pending sales are preserved. Logout marks that profile signed out, so it cannot be silently resumed or reused offline. The next explicit sign-in after logout requires the server. Pending logout requests are flushed separately from the active profile, one bounded request per attempt. Native operations serialize profile changes with the existing store/security locks so an earlier request cannot resurrect a logged-out account.

## UI and accessibility

The bilingual security workspace uses the existing seven-color palette, white surfaces with #202020 text and dark/primary surfaces with white text. Labels distinguish locking from logout and offline transaction permission from login lifetime. Duration options have explicit text; save/restore loading state clears in finally blocks. Existing 1024px layout and reduced-motion behavior must remain usable. Existing shared QR/member components retain their validation, pending-payment/retry and online-only settlement guards.

## Verification required

- Windows native tests: DPAPI remembered-state round trip, logout tombstone/revocation retention, invalid profile rejection, automatic lock on/off and manual lock persistence, online/offline/401/403/TLS/expired-lease resume, and injected logout/denial profile-write failures; existing transport, updater signature, offline core and installer recovery suites.
- SQLite and MySQL: dedicated desktop-persistent-session regression covers real login, marked authority, persistence beyond browser TTL, browser/native separation, context mismatch, unknown kind, inventory visibility, revoke-others, and disabled-cashier idempotent self-logout. Existing browser idle/absolute, session-control, desktop, service and QR tests remain required.
- Desktop UI: duration off/save, manual lock after off, editable account switch, QR/member busy gate and exact payment retries, reconnect, bilingual and minimum-width cases.
- Physical Windows pilot after signed build: upgrade v0.2.7 in place; retained accounts/receipts; first native v3 sign-in; close/reopen; manual/idle PIN lock; disabled auto-lock; online/offline/reconnect; owner suspension and revocation; per-vertical workflow; printer/scanner/drawer.

## Deployment, release and rollback

Deploy compatible backend and execute only the new additive migration with the normal certified cPanel process before publishing the Windows update. No database reset, existing tenant edit or source key rotation is needed. The signed publication workflow checks live capabilities and exact source before publishing versioned assets/stable manifest. Preserve existing updater trust and app identifier; upgrade in place without uninstalling or deleting local data.

Never overwrite the existing v0.2.7 release. New package/Cargo/Tauri/lock metadata is 0.2.8. Publish only after exact-head checks, independent review, merge and compatible hosted API evidence. If Actions is queued, pause and wait for the user to report completion.

Before rollback to an older backend, revoke all `desktop_persistent` authorities and matching bearer credentials; retain their audit and transaction evidence. Retain the additive discriminator column and all business/receipt data. Restore prior web routing through the normal source-bound operator, and keep v0.2.7 assets intact. Do not lower updater signature requirements or erase local profiles.
