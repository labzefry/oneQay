# Windows POS 0.2 delivery checkpoint

Requested 19 October 2026: embedded production origin; individual cashier login; mandatory opening cash; offline reopen and sequential shift synchronization; printable/email shift reports; web-managed receipt logo/footer/PIN/auto-lock; category-routed multiple kitchen printers; table/order references; transaction dashboard; signed patch updates without uninstalling.

Production remains `production-45509005b2f9` on `oneqaydev.n07.my.id`. Do not renew expired deployment grants before a new certified artifact and exact request exist. The permanent existing worker remains installed. Staging is outside this change.

Implementation branch: `pos-enterprise-20261019`, PR #966, baseline `85a602a6eb27acab533ac4c30d97e12ea0a07c9e`. Implementation is committed incrementally to preserve progress across interrupted sessions. No feature in this document is an assertion of production deployment or physical hardware acceptance.

## Contracts

- Native origin `https://oneqaydev.n07.my.id`; no user-entered URL or pairing for the new client. Owner-created cashier directory maps to canonical identities, organizational access, initial password enrollment and cashier RBAC. Never make platform owner accounts POS accounts.
- First login online; same-account cached password/PIN verifiers protected using Windows DPAPI. Offline authorization expires with the server-issued eight-hour lease; an online rejection never falls back to cached authorization. Account data remains in separate SQLite profiles and is not discarded on switching users.
- Stable UUID, fingerprint and FIFO ordering for opening cash, sale, closing cash, subsequent shift. Server composes existing canonical use cases. Every shift requires finalization by a different authorized actor in the owner's web console. Nonzero variance additionally requires the existing independent review. Dependent events wait, preserving evidence. Offline reports are provisional until accepted. Provisional and final email reports use the platform outbox with separate deduplication keys, not cashier SMTP settings.
- Monochrome customer bill logo bounded to 256×96 pixels, optional; cashier name, timestamp, unique reference, table/order and web footer. Kitchen and shift prints omit logos. Category routing can target multiple kitchens. Durable jobs distinguish queued, printing, done and uncertain. Retrying uncertain output requires explicit copy.
- Update discovery is automatic; installation is explicit when shifts are closed and unsynchronized records are settled. Embedded signature verification, fixed release origin, no downgrade, backup of every cached SQLite profile before replacement, additive database migrations. Never delete transaction databases as part of installation.

## Implemented and verified locally

- Version 0.2.0 uses the same application identifier and a fixed production origin. The web business settings page manages cashier enrollment, PINs, auto-lock, customer receipt logo/footer, report recipients, kitchen categories, and independent closure finalization.
- Canonical lifecycle test: owner enrollment → cashier online login → opening cash → sale → closing cash → independent owner finalization → acknowledgement → account disablement/session revocation. SQLite: 84 assertions passed. The existing v1 endpoint remains available for 0.1 clients.
- Rust offline store: 19 tests passed, including restart, ordered synchronization, malformed acknowledgements, kitchen print uncertainty, bounded receipt logo, and backup before update.
- Desktop Vue production build passed. Headless UI passed with screenshots for login, required shift opening, checkout, receipt, device configuration, transaction overview, kitchen routing, lock/unlock, language choice and 1024-pixel layout.
- The signed release workflow builds only merged, version-matched stable tags. It publishes immutable installers before replacing the stable manifest. The client requires a matching version-specific HTTPS release URL, a valid embedded-key signature and a newer stable version. All local profiles are backed up; installation is refused while shifts or transaction synchronization remain unsettled. Uncertain print jobs are retained for explicit operator resolution.
- Historical source-freeze workflows accept only the exact additive successor file set and hashes. Canonical policy, historical schema, deployment authority and executable regression tests are preserved.

## Operator handoff and remaining evidence

1. Before moving from 0.1 to 0.2, close and synchronize the old client. Its legacy database is preserved, but records in that legacy profile are not automatically reassigned to a new cashier account. An unresolved legacy queue blocks future in-app updates rather than being discarded.
2. Install the hosting successor before first use of the 0.2 cashier login. Create cashiers in the owner's web settings. The first login requires connectivity; cached login remains limited to the server's eight-hour offline lease. An account suspension is enforced as soon as the server can be contacted.
3. Configure repository Actions secret `TAURI_SIGNING_PRIVATE_KEY` using the privately delivered owner key; do not put it in source control or public hosting. The stable update channel is unavailable until a signed release is published. No Authenticode certificate is provisioned by this change.
4. Final-head Windows build/native tests, MySQL lifecycle checks, all required PR checks, merge, source certification and hosting packaging remain release gates. Passing source tests does not prove a hosting deployment.
5. Physical Windows 10, real cash, Bluetooth/USB/LAN printers, cash drawers, barcode scanners and delivery to actual email recipients require operator evidence. Pending outbox records are not proof of successful SMTP delivery.
