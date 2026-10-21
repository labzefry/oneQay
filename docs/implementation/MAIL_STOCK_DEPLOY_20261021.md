# SMTP, categories and stock deployment — 21 October 2026

Author by Lab | zefry. Stage 2 is explicitly authorized by the owner after manual cPanel login. Application PR #975 is already squash-published as `7efdda7648c2e89bf0100b1ac8d76763ec73dd94`; main certification run `39532367140` succeeded. Deploy that immutable application artifact, not an operator-only successor commit.

The observed active predecessor is `c2e3e3b5a05e9729a478f432c9751d4b4297bcfe`, with 6,392 certified files, 36 migration receipts and 87 tables. Runtime/bridge/routing/asset bindings come from PR #974 comment 6297183155 and are enforced again on hosting. Prior source inventory SHA is `db9d341bcf0481827249f3c8abad6a63471f5862342c9370419c23b42c5165c0`. The candidate contains 6,395 certified files.

The new operator accepts only this predecessor and source, copies a private regular-file runtime with unchanged credentials/session settings, verifies a private consistent gzip SQL backup, then applies the category table migration and the two default-true stock flags. It requires exactly 38 receipts and 88 tables afterwards. All other DDL, old columns/indexes/foreign keys, previous receipt identities, credentials, permissions, sessions, SMTP configuration and existing physical quantities are preserved. No homepage copy is republished. No merchant is reprovisioned and no shift is reset.

The backup is `rollback/database.sql.gz` under the private operator directory, mode 0600, with separately verified compressed and logical hashes. It includes private database contents and must never be exposed through the web root or GitHub. Restore into an isolated empty database during a separately reviewed recovery; never import it over an active database. MySQL uses a consistent snapshot. Backup failure prevents migration.

Exact native owner authorization is recorded on PR #975 only after upload, binding `authority-request.json` and the new operator hashes for one hour. This records the owner's current deployment instruction through the connected owner account; it is not independent human review. No source, expiry or protection bypass is allowed.

The unchanged retention helper archives inactive expanded releases and duplicate payload into verified private ZIPs after the 120-second grace, preserving active files and recoverable backup evidence. Only this application's worker cron is replaced and the one-time updater cron removed after actual success. Other domains and staging are outside scope.

Rollback to the predecessor is refused if any unlimited sale effect exists. Such a condition requires a compatible forward fix; no older business-on code may restock unlimited sales incorrectly. Partial schema checkpoints are retained without automatic DDL retry or destructive down migrations.

Local checks cover exact authority, runtime preservation, source integrity, rollback, HTTPS/TLS retry and worker locks; the SQL test exercises the real 36/87 → 38/88 upgrade, full backup round-trip on SQLite, unchanged account/SMTP/offline records and guard rejection. CI additionally executes disposable MySQL 8.4 and validates the actual certified package bytes. Source and synthetic qualification do not prove production inbox delivery. No external test recipient is implicitly selected.

Deployment receipt, new runtime/bridge/routing/asset/worker hashes, actual timestamp, cron state and retention result must be recorded on PR #975 after execution. Do not claim hosting success from a queued cron or uploaded ZIP.
