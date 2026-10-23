# oneQayDev account-flow source-only deployment successor

Author by Lab | zefry. Prepared 23 October 2026. This is an operator qualification record, not a hosting deployment receipt.

Application: PR #984, final source head `edea194ec05175b7ce57080e9e733a8b43797717`. Deployment must use the subsequent merged main source and its own completed Production Business Release Certification artifact; never relabel a PR artifact with a squash SHA.

Current hosting predecessor: `production-938d9c32d878` / source `938d9c32d8782c8b54fde8ba9a5143e87c01463a`, certified tar SHA-256 `fbd6c8d41182ec9810894ae655f890a552d8fb93eef2b3699cc762c403c19024`, 6,404 source files, 39 migration receipts / 89 tables. Exact predecessor inventory SHA-256: `2fbe13714957fd52625339af6ed589b9ebd64653c08269aacd256a6ef97e98bd` (recomputed from verified artifact12374336570, ZIP SHA-256 `388da3ff400c5e6f50dec85d14ef4927bfa0c20c6e6a7d4f7e4fea56d100e07b`).

Target is only `https://oneqaydev.n07.my.id`; private deployment root `/home/pekd7254/oneqay-production-test`; public root `/home/pekd7254/public_html/oneqaydev.n07.my.id`. Staging and unrelated cron entries are excluded.

## Operator behavior

The operator pins predecessor environment, bridge, routing, public index and certified source inventory. Candidate inventory and manifest bind the exact source, artifact and certification run. All migration source files must remain byte-identical. It never runs a migration or resets business data. A fresh native repository-owner comment on PR #984 must bind the concrete request SHA, source/artifact and one-hour window; the previous grant is not reusable.

Before promotion it creates a verified private logical database backup and an old-runtime/routing backup. The public build stays traversable under restrictive umask. Current and immediate-predecessor hashed assets remain available during the switch. Atomic promotion verifies public pages, login, registration, denied unauthenticated POS, actual JavaScript/CSS bytes and unchanged schema; failed verification restores the old business-on build/routing.

Replace only the prior oneQay worker cron after receipt verification. The worker runs Laravel in-process with captured output, a lock and atomic private health JSON. It checks the current source and environment before running SMTP/NOC work.

After a healthy current-source worker and a 180-second request-drain window, the exact preceding expanded application is archived to a private ZIP. Every archived file is read back and hashed against the unchanged source before any expanded file is removed. ZIP is mode0600 in a mode0700 private archive directory. Current release, SQL data, users, offline receipts and unrelated releases are not removed. Archive failure is reported separately from mail-worker health; it never falls back to deleting an unverified version.

## Validation and remaining work

Local disposable SQLite/filesystem test: `ACCOUNT_FLOW_OPERATOR_sqlite_PASS assertions=83`. Checks include exact inventory and injected-file rejection, public file modes, failed-probe rollback, successful promotion, old-asset grace/collision protection, complete private ZIP preservation and link refusal, complete 89-table database backup and unchanged 39-receipt schema. MySQL CI and actual package/hosting qualification are still required.

Use `python tools/production/package-oneqaydev-account-flow.py --certified-zip <CI ZIP> --source <MERGED MAIN SHA> --run <CERTIFICATION RUN> --output <PRIVATE OUTPUT>`. The emitted `PETUNJUK.txt` contains the exact cPanel-compatible PHP cron commands; JSON is written atomically by the operator, with stdout redirected to `.log`.

Before deployment, open the canonical cPanel address in a fresh tab and let the user complete the private login form. Do not reuse an expired cpsess URL or place credentials in chat. Do not create the one-hour deployment comment until the package and login are ready.

Windows signed-channel publication is separate: readiness reported the original signing private key missing. No unsigned patch, replacement trust key or successful installed-device upgrade is claimed. No real SMTP recipient or financial transaction is selected by these tests.
