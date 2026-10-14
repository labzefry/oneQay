# oneQayDev catalog/report/currency cPanel successor

Author by Lab | zefry. Application artifact source: ec48e3fffdbb4cdaa79a7c65b16fe38425939dcf. Verified predecessor: f5a037ce60aa05c61e0b9c197f2e171c4d01fa8c, actual host result SHA256 73f0176fb4e07713ffe6aa619e03964ef5d351db44961ede2286bab9cc9a7cf3.

## Qualification and scope

The application PR #955 head 5d04d659af94646d71c0d2e2cae7fd5e4bad6718 passed all 137 pull_request workflows. The dedicated run 38546405641 passed 53 merchandising checks on SQLite and 53 on MySQL, 73 existing integrated-business checks, 20 role-administration checks, five merchandising browser groups and four existing business UI groups. The immutable artifact 11884812353 contains actual compiled application screenshots and PDF/XLSX samples; SHA256 33bca607ae3117e441975a33f4e909bf7b901042ec8763aca61bdfa38f9a7893. Seven historical push-only workflow definition failures are not included in this passing PR count.

The successor operator validates the exact deployed 30 migration receipts and pre-existing 62 table definitions, then adds only the merchandising migration and its two tables. It retains every old receipt/DDL, merchant credentials, session paths, SMTP and platform configuration. Its intent checkpoint is durable before DDL; partial schema states fail closed and cannot be retried automatically. Canonical owner role administration remains permission checked. Isolated operator tests cover 39 authority/integrity/secret-continuity/rollback boundaries; database upgrade tests cover 20 assertions per engine.

The operator fixes the previous release/artifact/env/entry/routing/report/file-manifest hashes from the supplied successful hosting JSON. It verifies the certified archive, manifest, expanded payload and all source file hashes; DOM and zlib are required for XLSX imports before promotion. Existing 27 historical and three module files must be byte-identical. No full `artisan migrate` is allowed. New release and private runtime files are separate; the old business-on bridge/build/routing are backed up and verified for rollback. Staging is excluded.

## Manual hosting procedure

Download the newly assembled package, upload into `/home/pekd7254/oneqay-production-test`, and extract it once. The exact directory must be `oneqaydev-catalog-update-<application-source-short>` with update.php, install-modules.php, authority-request.json, release-files.sha256, the certified native archive/manifest and payload together. Do not move payload manually into releases; the operator verifies and installs it.

Use only the newly issued exact-request owner grant and new one-shot Cron guard. The accepted CLI is `update.php update <native-comment-id>`; the user-facing command invokes it through quoted PHP `-r`, appends stdout/stderr to update-console.log and refuses to repeat the same guard. It includes no `%` so cPanel Cron does not split it. Remove the Cron after completion. Do not reuse the expired receipt-recovery command or grant, delete guards, reset reports, remove rollback directories or rerun prior module migrations.

A completed operator reports BUSINESS_WORKSPACE_UPDATED_ACCEPTANCE_PENDING and preserves business traffic. The public login and protected route checks are unauthenticated; actual owner browser use remains a separate host acceptance step. Supply the newly generated update-result.json and update-console.log after running. SMTP/worker owner setup and external commercial integrations remain separate pending items; a successful source certification is not a claim that those services are configured.
