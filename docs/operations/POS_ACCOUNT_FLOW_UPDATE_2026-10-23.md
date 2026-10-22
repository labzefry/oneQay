# POS account and activation flow update — 23 October 2026

Author by Lab | zefry.

Owner request: simplify Windows cashier sign-in/account switching, automatic reconnect sync, professional email presentation, activation navigation, unique account email, owner POS access and in-app Windows patches.

This is a work-in-progress source checkpoint. It is not a deployment receipt, successful Windows patch publication or proof that a customer device has synchronized.

Base main: `4e5d3ab55b616c7424459e0229d9cf6ded78f937`. Hosting remains the previously verified `production-938d9c32d878` until a new certified release is installed. The old live checkpoint PR #983 remains separate.

## Implemented source

- Login no longer fails solely because a register shift exists. Account profiles remain separate SQLite databases; logout/switch preserves immutable transactions, receipts and outbox.
- Register ownership and closure review remain enforced before new financial operations. An existing unresolved shift is shown inside the workspace rather than preventing authentication.
- Reconnect triggers sync; batches continue promptly while progress is made. HTTP server errors remain distinct from network loss. Queued data is removed from the pending state only by validated server acknowledgment.
- Registration rejects normalized existing owner/cashier emails with `EMAIL_ALREADY_REGISTERED`. Owner POS enablement binds the existing identity and password, keeps required TOTP, and rejects creation of a separate cashier with the owner's email.
- Activation uses canonical login, redirects to the workspace or required MFA screen, and no longer returns a technical login-context download.
- Multipart email includes escaped, palette-compliant HTML and plain text; only configured first-party activation links become buttons. No external images or tracking pixels.
- Windows 0.2.3 reads the actual application version, exposes update checking at login, reports real download/verification/backup stages and keeps signature verification. Consistent integrity-checked backups include pending transactions and active shifts, so a sync-repair patch is not blocked by the queue it must repair.

## Remaining before release

Complete functional/security tests on SQLite/MySQL, Rust and Windows native compilation; interaction checks; exact-head PR CI; review and merge; signed updater publication; certified cPanel successor and actual deployment verification. Do not claim these complete from this source checkpoint.

GitHub releases were empty at the prior read. The signing private key corresponding to the embedded updater public key must be available to the signed-release workflow. Do not replace the trusted key or publish unsigned updates as a workaround. A preexisting binary may still restrict access to its update screen; no successful installed-device update is claimed.

If cPanel authentication is needed, open the canonical address in a new tab without reusing an expired cpsess URL. The user completes the private login form before deployment continues. Never place credentials in chat, source, logs or this document.

No production reset, customer-data inspection, real cash transaction, SMTP recipient selection or database migration is authorized by these source tests. This update requires no new database schema.
