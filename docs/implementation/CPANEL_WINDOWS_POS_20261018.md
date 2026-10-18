# Windows POS and browser experience hosting upgrade

Author by Lab | zefry.

Application source: `45509005b2f9ed4e5ffddd1e8c7db100a50e52fe` (squashed PR #963). The Windows x64 installer was compiled and tested from the equivalent PR source `424853ea072273c1a48df10f92b86372991180a9`, Actions run `39145827153`. Its application source is identical to the merged release. It is unsigned; physical Windows 10, receipt printer, drawer, scanner and real-cash acceptance are still required.

The cPanel successor accepts only the evidenced `production-78202bb5fe59` host, its private runtime, bridge, source-file manifest, public asset union and immutable successful installation receipt. Retention maintenance may update the receipt's housekeeping fields; it cannot change the immutable acceptance fields or independently verified deployed bytes.

The candidate application comes exclusively from the trusted main-branch Production Business Release Certification artifact. The operator does not create a substitute certification. The request binds the archive, manifest, complete extracted file manifest, operator, migration helper and retention helper. Native owner authority remains exact-request and one hour; it must be issued only once the final package is ready. Staging, account reprovisioning, historical migrations and external payments remain outside scope.

The migration requires precisely 33 prior receipts and 77 tables. It preserves existing schema, credentials, permissions, settings and SMTP data and adds only one receipt and four desktop POS tables, ending at 34 receipts and 81 tables. Partial or unknown schema states stop without automatic retry. Failed public promotion restores the previous BUSINESS_ON bridge and assets; additive tables and transaction journals are retained.

After successful HTTPS verification and a 120-second grace period, the recurring worker archives expanded old releases and installer payloads into verified private ZIP files. It keeps the active release, shared sessions/configuration, staging and operator helpers. A verified archived predecessor can be restored for rollback. The new `oneqaydev-pos-update-*` payload prefix is explicitly scoped in the same retention policy. Update the permanent worker cron to the new package's `control-worker.php` after success.

Public verification includes authenticated-route denial for JSON/API clients, separate owner and business login redirects for expired browser sessions, and an illustrated 404 with `PAGE_HTTP_404` and an NOC correlation header. No deployment result is treated as real-cash pilot acceptance.

Validation: migration preservation fixture (19 assertions on SQLite and MySQL in CI); request, authority, immutable predecessor and BUSINESS_ON rollback fixture (56); HTTPS browser/login/error fixture (11); verified ZIP round-trip and recovery fixture (29); fixed public bridge with both link capabilities disabled (20). The original regression fixtures remain unchanged.

Offline POS requires a catalog and open cashier shift prepared online, pairing by the current authorized cashier and a valid bounded lease. Committed local receipts survive restart and pending operations sync idempotently. Conflicts and stock-count submissions require web review; they do not overwrite authoritative inventory. Business administration and shift administration remain on the web. Offline card payments and unattended stock adjustments are not implemented.
