# Windows POS 0.2 delivery checkpoint

Requested 19 October 2026: embedded production origin; individual cashier login; mandatory opening cash; offline reopen and sequential shift synchronization; printable/email shift reports; web-managed receipt logo/footer/PIN/auto-lock; category-routed multiple kitchen printers; table/order references; transaction dashboard; signed patch updates without uninstalling.

Production remains `production-45509005b2f9` on `oneqaydev.n07.my.id`. Do not renew expired deployment grants before a new certified artifact and exact request exist. The permanent existing worker remains installed. Staging is outside this change.

Implementation branch: `pos-enterprise-20261019`, baseline `85a602a6eb27acab533ac4c30d97e12ea0a07c9e`. The interrupted local implementation is being recovered incrementally to this branch. No feature in this document is an assertion of production deployment or physical hardware acceptance.

## Contracts

- Native origin `https://oneqaydev.n07.my.id`; no user-entered URL or pairing for the new client. Owner-created cashier directory maps to canonical identities, organizational access, initial password enrollment and cashier RBAC. Never make platform owner accounts POS accounts.
- First login online; same-account cached password/PIN verifiers protected using Windows DPAPI. Offline authorization expires with the server-issued eight-hour lease; an online rejection never falls back to cached authorization. Account data remains in separate SQLite profiles and is not discarded on switching users.
- Stable UUID, fingerprint and FIFO ordering for opening cash, sale, closing cash, subsequent shift. Server composes existing canonical use cases. Independent cash variance review remains mandatory; dependent events wait, preserving evidence. Offline reports are provisional until accepted. Email uses the platform outbox, not cashier SMTP settings.
- Monochrome customer bill logo bounded to 256×96 pixels, optional; cashier name, timestamp, unique reference, table/order and web footer. Kitchen and shift prints omit logos. Category routing can target multiple kitchens. Durable jobs distinguish queued, printing, done and uncertain. Retrying uncertain output requires explicit copy.
- Update discovery is automatic; installation is explicit when shifts are closed and unsynchronized records are settled. Embedded signature verification, fixed release origin, no downgrade, backup of every cached SQLite profile before replacement, additive database migrations. Never delete transaction databases as part of installation.

## Acceptance remaining

Restore and implement code, run SQLite/MySQL canonical lifecycle tests and Rust crash/offline tests, validate native Windows build and visual UI, pass required final-head PR checks, merge, certify and package the hosting update and installer. Physical Windows 10, real cash, printers, drawers, scanners, and delivery to actual email recipients require operator evidence.
