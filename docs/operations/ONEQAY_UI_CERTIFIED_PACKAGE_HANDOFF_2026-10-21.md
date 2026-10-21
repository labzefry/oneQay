# oneQayDev UI enterprise — certified source and private cPanel package handoff, 21 October 2026

Author by Lab | zefry.

## Actual decisions and immutable identities

- Application PR [#978](https://github.com/labzefry/oneQay/pull/978) **squash-merged** onto `main`: `7e4197b443225e2022a26af9fd53a966d06c304f`. Its Product Owner merge authorization was bound to the final PR head, not to hosting.
- GitHub Actions **Production Business Release Certification**, run [39619254282](https://github.com/labzefry/oneQay/actions/runs/39619254282), **SUCCESS** on main; source artifact **12353588540**, `oneqay-certified-business-production-7e4197b44322`.
- Certified source archive: `production-7e4197b44322.tar.gz` SHA-256 `03670693b16f3f1ba0c3b6d64b89445d251780e1bdacc81de523927caec3c49e`; 6,398 regular files with locked Composer/vendor and compiled frontend assets.
- Local private-host operator package prepared: `oneqaydev-ui-update-7e4197b44322.zip`, **15,491,138 bytes**, SHA-256 **`b7b1738fc91fcb8aaddaacab0c302e7c2d0741c424818683b13068e60b2eb4b8`**.
- Source package `update.php` SHA-256 `87707cb4bff23daf0355847f919c0e9288b06e3627b03dbbbd2317cf45a135b6`; `authority-request.json` SHA-256 **`cf66ca3806e42e366e17ec4a86982a4657ba61dd80a1fa5041b651db0acc2e9e`**. Any ZIP repackaging changes the outer digest and requires a fresh verified handoff.
- This ZIP was created and validated in the assistant's isolated build container, not on GitHub Actions or on the user's host. Local PHP syntax, 6,398 per-file digests, ZIP CRC, manifest identity and tamper-denial checks passed. **Actual cPanel preflight, publication, HTTPS acceptance and retention remain NOT_EXECUTED**. An individual upload does not activate production.
- Hosting predecessor last independently recorded: `production-7efdda7648c2`, 38 migration receipts/88 tables, business traffic `ON_PRESERVED` on `oneqaydev.n07.my.id`. Never substitute staging `oneqay.n07.my.id`, reuse the old deployment grant, reprovision a merchant, replay migrations or send emails/transactions as proof.
- Web release, source on `main`, cPanel deploy ZIP, native Windows installer, and Windows updater/signed patch remain **separate artifacts**.

## Exact owner-operated cPanel preparation

The owner uses a personal cPanel File Manager and Cron Jobs. Do not ask for a password/OTP or use Cloud Browser. Upload and extract the ZIP privately under `/home/pekd7254/oneqay-production-test` so the PHP operator is
`/home/pekd7254/oneqay-production-test/oneqaydev-ui-update-7e4197b44322/update.php`.
**Never** upload operator, release metadata or runtime credentials into `public_html`.

Run this **one-time read-only preflight** from cPanel Cron Jobs:

```text
/usr/local/bin/php /home/pekd7254/oneqay-production-test/oneqaydev-ui-update-7e4197b44322/update.php preflight > /home/pekd7254/oneqay-production-test/oneqaydev-ui-update-7e4197b44322/preflight.log 2>&1
```

Remove that one-time cron and inspect `preflight-result.json` and `preflight.log`. Only `READY_NO_ACTIVATION` with predecessor source `7efdda7648c2...`, preserved bindings, **38/88**, all file hashes and unchanged application PHP is an acceptable candidate.

**No new hosting authorization has been granted.** Once upload and preflight are truly confirmed, issue a fresh one-hour **native GitHub Product Owner grant on PR #978** using the exact `AUTHORIZATION-EXACT-BODY-ONLY-AFTER-UPLOAD.txt` bundled in this package. The operator validates the real owner, PR identity, issue comment body, request SHA, and grant age over verified TLS. A grant/comment number cannot be fabricated or pre-issued. After its real comment number is obtained, the one-time command becomes:

```text
/usr/local/bin/php /home/pekd7254/oneqay-production-test/oneqaydev-ui-update-7e4197b44322/update.php update <NEW_REAL_GITHUB_OWNER_COMMENT_ID> > /home/pekd7254/oneqay-production-test/oneqaydev-ui-update-7e4197b44322/cron-output.log 2>&1
```

**Do not execute this placeholder command.** The human owner must receive the specific fresh comment ID for the exact uploaded source package first. Any unknown/failed outcome: STOP and inspect receipts before retry; do not regenerate a transaction or run an obsolete operator.

Only after the actual `update-result.json` reports `UI_SOURCE_PUBLISHED_ACCEPTANCE_PENDING`, `business_traffic: ON_PRESERVED`, expected source SHA, 38/88 and verified HTTPS, retire only the old oneQayDev worker cron and replace it, once per minute, with:

```text
/usr/local/bin/php /home/pekd7254/oneqay-production-test/oneqaydev-ui-update-7e4197b44322/control-worker.php > /home/pekd7254/oneqay-production-test/oneqaydev-ui-update-7e4197b44322/worker-result.json 2>&1
```

Do not change unrelated cron entries. Keep predecessor worker until the new one is actually verified. After the successful deployment, at least 120 seconds elapsed, and `worker-result.json` reports `failed=false`, run once:

```text
/usr/local/bin/php /home/pekd7254/oneqay-production-test/oneqaydev-ui-update-7e4197b44322/update.php archive > /home/pekd7254/oneqay-production-test/oneqaydev-ui-update-7e4197b44322/archive.log 2>&1
```

Require `archive-result.json` with `PRIVATE_OLD_RELEASE_ZIP_VERIFIED`. The private ZIP is SHA-verified against old expanded files, while the former expanded release remains untouched for compatibility and recovery. Never delete active release, merchant data, database, credentials, shared storage, journals or backups.

## Scope, gate and evidence limits

UI-only application differences mean **zero migrations authorized**. Guard RBAC, tenant isolation, real stock tracked/unlimited, sessions/TOTP, SMTP queue, shift evidence, offline queue and financial/merchant records; no automatic blind retries after unknown results. Frontend source and host package are **not a native Windows installer or signed updater promotion**. GitHub certification does not prove real SMTP inbox placement, live cash pilot, desktop offline queue acceptance or newly installed host.

The package ZIP is a conversation artifact, not a persisted GitHub release asset. This document records its exact SHA and rebuild identity; owner should retain the ZIP privately. Actual host command values cannot be issued for `update` until its fresh owner-comment ID exists. Upon real execution, append the sanitized final host receipts, new worker digest/health and archive hashes in a separate repository milestone; never commit private credentials, database snapshots, or MFA.
