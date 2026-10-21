# oneQayDev — blank-page and worker recovery, 22 October 2026

Author by Lab | zefry. Incident work explicitly authorized by the owner after a new manual cPanel login. This is a source-bound operator repair, not a new application release or database migration. The owner specifically permitted Cloud Browser for this incident; ordinary-chat deployment remains the default for subsequent development.

## Actual deployed UI source

Hosting receipt now proves UI application `7e4197b443225e2022a26af9fd53a966d06c304f`, release `production-7e4197b44322`, published at unix1792603387, **22 October 2026, 00:23:07 WIB**. Previous application was7ef. Main before this recovery was operator-handoff docs `acbeda6f6f64d3fc498ed179b942674b8357ac01`; it is not the running application SHA. Certification run39619254282/artifact12353588540; tar SHA `03670693b16f3f1ba0c3b6d64b89445d251780e1bdacc81de523927caec3c49e`; 6,398 files.

The actual receipt state is `UI_SOURCE_PUBLISHED_ACCEPTANCE_PENDING`, traffic `ON_PRESERVED`, database38receipts/88tables, `migrations: NONE_EXECUTED`. Original grant6322116018 is historical and must not authorize a successor. Runtime SHA `05aca10c97be6cb3cbaeaac2fef2e5d0c4502b14e050292822fc7e7ff00e7299`; bridge `4d53c3759441ac552055f47ef253c72cd6ccbebf2c23dfab8a42ba71a8fbc861`; routing `3b668015b8fd4c75c391a2eae06518885b41e4f904c88ecdcee62f790f179295`; original native worker `fe96490b4a27ad9e589b5ae69a7ff943139903e2e227f450d828e9cd0fe1aab9`.

These actual hosting observations supersede older main documentation calling PR#978 draft/not deployed or claiming hosting still7ef. They do not prove complete UI coverage, authenticated business acceptance, real SMTP inbox delivery, real cash/offline pilot or signed Windows update distribution.

## Blank page: root cause and bounded repair

The browser had a genuinely empty Vue root. Backend HTML returned200 with `Website/Page` props, while its own Vite JavaScript/CSS URLs returned404 JSON. The two files existed on disk and matched the certified release exactly:

| Asset | SHA-256 |
| --- | --- |
| `assets/app-Cy47fj4C.js` | `cd1eb8923a4ee667ea228415b4c4844d63bf883c6f757e4bbc8ca744617cdb84` |
| `assets/app-DgPj8UAj.css` | `71f123335ae43c356c11ebeb42401a00e49f9afee72384af27093d9214a6a906` |

cPanel showed public `build` mode0750. The custom UI deployment package used private directory permissions when staging its public build. Apache could not traverse it, so existing static URLs reached Laravel's fallback and returned404. A backend HTTP200-only deployment check missed the broken client boot.

The repair validates exact current receipt, original worker hash, unchanged private runtime bindings and certified JS/CSS digests. It refuses links/special/credential filenames, records original modes/hashes privately, and restores only public build directories to0755 and static files to0644. All asset bytes are rehashed unchanged afterwards. Private release/runtime/shared/backup files, routing, security headers and database are untouched. Actual report at unix1792606563 (01:16:03 WIB): `PUBLIC_BUILD_PERMISSIONS_REPAIRED`, changed_entries2, checked_entries29, asset_bytesUNCHANGED. Original mode evidence: `public-build-permissions-before.json` in the private operator directory.

Post-repair inspection at unix1792606746 (01:19:06 WIB) returned200 for home, JavaScript (`text/javascript`) and CSS (`text/css`); both HTTP asset digests exactly matched disk/certification. Browser reload then rendered the homepage, navigation, feature sections and copyright oneQay normally; the business login form also rendered correctly. Future release operators must use appropriate public modes before promotion and verify actual HTTP200/mime/digest for the manifest's JS/CSS plus a rendered browser mount. Never disable nosniff/CSP or weaken private directory permissions as a workaround.

## Worker: root cause and in-process replacement

The user's logs explicitly reported `WORKER_PROCESS_EXEC_UNAVAILABLE`. The attempted atomic wrapper depended on `proc_open`, unavailable on this hosting. Its earlier `ob_start` variant could not intercept Symfony direct STDOUT output either. Enabling process-execution functions is unnecessary.

`tools/production/cpanel/recover-oneqaydev-ui-20261022.php` is installed privately as `recovery-20261022.php` under `/home/pekd7254/oneqay-production-test/oneqaydev-ui-update-7e4197b44322`. Mode `work` bootstraps the same certified app and runtime, then calls the real Laravel Console Kernel with a Symfony `BufferedOutput`. It preserves the original worker file/hash, production gate, source binding, existing command behavior and application lock. A private wrapper lock prevents overlap. It validates exit/JSON/time, atomically writes mode0600 `worker-result.json`, preserves actual failed phase reports and avoids inventing healthy reports for already-running work.

The replacement every-minute oneQay cron is:

```text
/usr/local/bin/php /home/pekd7254/oneqay-production-test/oneqaydev-ui-update-7e4197b44322/recovery-20261022.php work > /home/pekd7254/oneqay-production-test/oneqaydev-ui-update-7e4197b44322/worker-recovery.log 2>&1
```

Actual first recovered sample unix1792606624 (01:17:04 WIB): failed=false; mail/health/campaign phases completed; mail_processed0 and campaign_recipients_processed0. This proves worker execution/output capture, not a real SMTP delivery. No recipient, shift reset or financial test was selected implicitly. Unrelated cron entries remain unchanged. The temporary inspect/assets cron must be removed after final verification; permanent job is only mode `work`.

## Evidence and continuation

Private safe reports: `web-recovery-inspect.json`, `public-assets-recovery.json`, `worker-result.json`, `worker-recovery.log`. The original `update-result.json` remains unchanged. No runtime secrets, SQL data, session URL or sign-in values belong in GitHub. Last old-release archive attempt is a separate activity; do not infer archive success from worker recovery or delete expanded releases blindly.

Eleven local checks passed using the exact certified vendor: restricted modes restored, bytes unchanged, private directory unchanged, replay retains original evidence, symlinks/tampering denied, private atomic JSON, and a real Illuminate command run through inherited Kernel::call + BufferedOutput without leaked output or external processes. Dedicated CI repeats this against the immutable certified archive. Repository checks and exact-head review/merge evidence belong in the recovery PR.

The helper is deliberately bound to this one hosting source/runtime. Do not reuse its pinned constants for a different release. Future deploy kits must incorporate public static permission/HTTP checks and in-process console output capture as qualified behavior, rather than reintroducing the failed private-mode staging or proc_open-only wrapper.
