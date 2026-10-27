> Historical deployment evidence: superseded for current hosting by the d63cf4dd44af checkpoint and PR #987. Commands and Windows versions below describe 22 October only; do not reuse them for a new deployment.

# POS sync deployment result — 22 October 2026

Author by Lab | zefry.

## Actual hosting state

Target: https://oneqaydev.n07.my.id/ (not staging oneqay.n07.my.id).
Application PR981: source `938d9c32d8782c8b54fde8ba9a5143e87c01463a`, release `production-938d9c32d878`.
Operator PR982: reviewed head `8ee407db27a296d52bc915e5c6e49e3c4f355033`, squash `4e5d3ab55b616c7424459e0229d9cf6ded78f937`. 130 successful check runs and one event-specific skip; dedicated SQLite/MySQL operator run39706646098 succeeded. Exact-head native owner merge authority comment6331459662 records existing explicit chat authorization, not independent review.

Certified application run39661099371, artifact12374336570. Certified tar SHA256 `fbd6c8d41182ec9810894ae655f890a552d8fb93eef2b3699cc762c403c19024`. Operator package ZIP SHA256 `19fbb1626234506bd19889c3a74df478f83eccdfeb4f4d659ff873ecae37a1c5`; request SHA256 `d5fb5f5acbdd3629f346ca225db4050adbc21357b19c3a9364d8087bd5f86177`. 6404 certified source files verified on hosting.

Fresh source/package-bound owner deployment comment6331573718 on PR981, authorized1792637845, expires1792641445; historical grant only, never reuse. Read-only preflight `READY_NO_ACTIVATION` at1792637765. Deployment receipt `POS_SYNC_RELEASE_PUBLISHED_ACCEPTANCE_PENDING` at1792637948 (22 October2026 09:59:08 Asia/Jakarta).

Private operator directory: `/home/pekd7254/oneqay-production-test/oneqaydev-pos-sync-938d9c32d878`.
Shared runtime: `shared/production-business-runtime-938d9c32d878-pos.env`. Credentials preserved; source/artifact binding lines only changed. No runtime secret was copied to repository.

Runtime SHA256: `41cc19d0fa1df0953ff63047dccb35586050ddc3c56fe2cfb06ad17db92da51f`.
Public entry SHA256: `fa3773c72bfc0460ef029722caf5b5340de6983d2c33efa71d29fa9aa4dd25e8`.
Routing SHA256: `af3eb6591a871efaa8ab78a026a92b2f331db1a8fc585a926d4c402debe5040d`.

## Observed verification

- Database advanced38 migration receipts/88 tables to39/89, adding only `oneqay_desktop_sync_diagnostics`; all previous DDL fingerprints and migration receipts unchanged.
- Private consistent SQL backup completed before migration; `database-backup.json` exists283 bytes/mode0600. Backup contents were not read or exported.
- Actual HTTPS: homepage200, owner-login200, unauthenticated POS401; hashed JS/CSS200 and byte digests equal published disk assets.
- New JS `/build/assets/app-CsYc4Orn.js`: `5e0d7d02f749a0efb9099cc8e7f2bb6a3d7e2a588122a218f2c173e7ae9fc8df`.
- New CSS `/build/assets/app-Dy0f7CkN.css`: `28b8d2e51a534eeee815c40cafed8ac75c16ea6ba07dda7a67f5427d9f69ba9f`.
- Fresh browser verified the rendered Indonesian homepage and protected owner-login form; no authenticated owner UI acceptance or real business reset was performed.
- Only existing oneQay permanent worker cron replaced. Temporary deployment cron removed; exact deploy command row count0 observed. Unrelated app crons untouched.
- New private worker-result.json at1792638122 (10:02:02 WIB): failed=false, mail/health/campaigns completed, mail_processed0 and campaign_recipients_processed0. This is worker health, not SMTP delivery/inbox proof.

Permanent every-minute worker:

```sh
/usr/local/bin/php /home/pekd7254/oneqay-production-test/oneqaydev-pos-sync-938d9c32d878/update.php work > /home/pekd7254/oneqay-production-test/oneqaydev-pos-sync-938d9c32d878/worker.log 2>&1
```

## Windows and remaining acceptance

Windows0.2.2 installer/native tests succeeded in run39659759487; artifact12373737027 was downloaded and both ZIP and installer digests verified. Installer SHA256 `e0e33c34d8e0dbd0e38a79b242b70a5b50ea7f50bbfc722b07485cb04de2bf35`,4245172 bytes. CI source synthetic merge9ab8bfd312d9cbbc883b09b1ad7cd66280cfebf0 shares exact treec73fe3d613815b17a32580e848e756f904204fc2 with reviewed/main application source.

This installer is not a signed automatic-update publication. Install over the current per-user installation; do not uninstall, delete AppData/local database, clear rejected operations or generate replacement cash receipts. Close POS before installing. After installing0.2.2 and reconnecting, use **Pulihkan & sinkronkan antrean** on the synchronization page; canonical operation IDs/payloads/reservations remain unchanged. Only generic POS_INVALID_OPERATION and missing-opening dependent rejections are explicitly requeued; specific authority/stock/cash conflicts require reconciliation, not forged leases or shifts.

The original four Windows local operations have not been observed from this device and are **not yet acceptance verified**. Server diagnostic rows appear after an actual item submission and retain three days; an empty monitor before resubmission is not successful sync evidence. Owner-only business reset is deployed under business configuration but was never executed. Initial platform-owner tenant remains protected; suspend/drain/close shifts/reconcile offline devices/preview/typed target/private backup safeguards apply.

The previous stock-compatible7e release, prior build, shared files and backups remain preserved pending separate verified retention; no inode cleanup is claimed. No real cash transaction, business reset, credential change, SMTP test recipient or marketing send occurred during deployment.
