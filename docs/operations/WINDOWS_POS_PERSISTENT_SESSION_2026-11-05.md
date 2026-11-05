# Windows POS 0.2.8 candidate — persistent cashier sign-in and local screen lock

Status: implementation candidate; not merged, deployed or published. Initial exact-head CI on96f51ef passed Windows compilation/DPAPI tests, web/desktop/service UI, SQLite and MySQL persistent-session regressions (98 assertions each), release certification and security checks. This continuation repairs newly triggered historical CI envelopes and fixtures; final repaired-head CI and independent review remain required. Never advertise an installer until the signed release and compatible deployed API are verified.

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


## PR996 CI compatibility repair

The first exact-head run (`96f51efcecb6219221e31902a3d51224f443f80b`) passed Windows compilation, DPAPI/native tests, desktop/shared UI and persistent-session SQLite/MySQL (98 assertions each). The candidate remains unpublished pending the repaired complete gate set.

The native auth path newly triggers historical Sprint21–29/M7 checks whose migration counts and whole-PR path lists predate the current main baseline. `verify-windows-pos-successor.py` binds PR996 to its internal repository, branch, exact5ab base, exact path/status/blob/mode manifest and worktree; canonical27 root migrations, prior validators, historical harnesses, web dependencies and operational state remain frozen. Existing executable browser/auth regressions continue against current application source in disposable migration horizons. The original Sprint26/Sprint29 fixtures stay unchanged; the current Sprint29 variant adds the real epoch/session schema and retains all original security assertions. No production or updater authority comes from source classification.

The extracted Sprint253 script preserves its original checks and receives GitHub expressions through explicit environment values, keeping workflow run blocks within GitHub's size limit. Merchandise and integrated-business fixtures now create canonical session/sale foundations before all POS module migrations; missing download fixtures return an explicit500 before headers rather than crashing the browser harness. The newly exercised bootstrap regression exposed hidden input whitespace normalization; the console now uses hidden, nontrimming input with plaintext fallback disabled and removes only the terminal Enter delimiter. Password policy, confirmation and hash verification remain unchanged.

Continuation: finish local workflow rehearsals and independent review, push one repair commit, then stop while Actions queues. Do not merge, deploy or publishv0.2.8 until required checks pass. Deploy the matching backend/capabilities first, then run the signed Windows release and verify its stable updater manifest before reporting availability.

Local continuation evidence: canonical catalog/stock fixture73 assertions PASS; integrated business fixture73 PASS; persistent-session SQLite98 PASS; original Sprint26 credential verification PASS; current Sprint29 bootstrap and normal login PASS; existing isolated Sprint28 enrollment, Sprint36 inventory/revocation, Sprint37 all-session termination and Sprint38 absolute lifetime PASS. Browser rehearsals: merchandising6 scenarios and integrated-business4 scenarios PASS, including real PDF/Excel downloads and mobile layouts. Sprint27 establishment, Preview interaction (under the workflow's explicit CI Preview environment) and Preview database qualification PASS. Exact source classifier positive case PASS and12 negative event/path/status/mode/blob/worktree/historical-mutation cases fail closed. Parsed595 changed-workflow shell blocks with no shell syntax errors; all workflow run blocks remain within21,000characters. A local static-step rehearsal is recorded only after its complete result; it is not a substitute for GitHub Actions.
