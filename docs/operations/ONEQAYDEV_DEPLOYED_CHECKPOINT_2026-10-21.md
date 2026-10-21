# oneQayDev — verified hosting checkpoint, 21 October 2026

Author by Lab | zefry. This checkpoint records actual hosting execution and supersedes older current-facing c2e/pre-deployment wording for **oneqaydev.n07.my.id only**. It does not change the staging target or imply financial acceptance. GitHub evidence remains authoritative.

## Read first and distinguish the three versions

| Surface | Verified baseline |
| --- | --- |
| Repository main before this documentation handoff | `afda997f79a866dfd151cc50de46275dd250cd3c`, operator PR #976 squash |
| Actual application running on hosting | `7efdda7648c2e89bf0100b1ac8d76763ec73dd94`, application PR #975 squash |
| Hosting release | `production-7efdda7648c2`, published **21 October 2026, 18:17:29 WIB** |
| Source certification | Actions run `39532367140`, SUCCESS; artifact `12315947592` |
| Actual state | `BUSINESS_WORKSPACE_UPDATED_ACCEPTANCE_PENDING`; traffic `ON_PRESERVED`; native gate `NATIVE_PRODUCTION_PASS` |
| Certified files | 6,395 |
| Database | 38 receipts / 88 tables, upgraded from 36 / 87 |

Documentation-only successor commits do not automatically become deployed application releases. Read current main when starting a new task; do not redeploy afda or an older release merely because its SHA appears here.

Canonical hosting evidence: [PR #975 deployment comment 6314727770](https://github.com/labzefry/oneQay/pull/975#issuecomment-6314727770). Fresh owner authority used for this execution: comment `6314556542`, request SHA `571e555b50bd14c1ec929042f9822c29454443d5f1e7ffe12bc934cd391371d2`. This was an agent-recorded execution of the owner's explicit instruction, not independent human review. Its one-hour grant must **never be reused** for a successor deployment.

## Target and preserved boundaries

- Production business target: `https://oneqaydev.n07.my.id/`.
- Private root: `/home/pekd7254/oneqay-production-test`.
- Fixed public root: `/home/pekd7254/public_html/oneqaydev.n07.my.id`.
- Current operator: `oneqaydev-pos-update-7efdda7648c2` below the private root.
- cPanel canonical login: `https://tambora.iixcp.rumahweb.net:2083/`. Session URLs and credentials are not recorded.
- Preserve occupied staging `oneqay.n07.my.id`, every unrelated domain/cron, merchant identity, RBAC, TOTP, session settings, SMTP secrets, quantities, sales, shifts and offline queues.
- No generic permission grants, blanket migration, migration #27 replay, reseeding, merchant reprovisioning, shift reset, payment or financial test is implied.

## Published application behavior

Application PR #975 delivers independent mail/health/campaign worker phases, safe SMTP attempt/error reports with live refresh, 72-hour mail-event retention with active queue/dedupe/suppression preservation, scoped category creation and tracked/unlimited stock across canonical sale/void, import/export, web and desktop source. Desktop source/build is 0.2.1; existing installers do not acquire it from a hosting update. Signed Windows patch promotion remains separate.

The two bounded migrations create `oneqay_business_product_categories` and add default-true `tracks_stock`/`stock_deducted` flags. All historical migrations remain intact. Accounts, sessions, SMTP configuration, original physical quantities and replenishment were preserved.

Before migration, a private gzip SQL backup of **87 tables / 4,273 rows** was verified, mode `0600`, under the current operator's `rollback/database.sql.gz`. Compressed SHA: `a435f10c74120b9759c80a936ae4d3eced34d58592ca217edbabea6e2d707c95`; logical SHA: `744a6a19e1bc6792e14ee68f3a08e6d2b07cf65ee3848c31ecef79593482f159`. Keep its contents private. Never import over the live database as a routine rollback.

## Operational bindings

| Binding | SHA-256 |
| --- | --- |
| Private runtime | `4d0449b3a681953af3beb4eb19c626ce1a60b107761ce10531143f6cdf8fc3fb` |
| Business bridge | `c5b479315fd71d0847148a42379d055662ec1893652a85aa0937fabb2e5ab262` |
| Routing | `0f3b080e0911feb8416ef8efd45f302e90591805a7af65f4e7d03522b00d668c` |
| Asset union | `791eb8e116f6a3e1a164e0e5aeb05ae4ab90292857f787be5d700e7fc95313cf` |
| Certified tar | `fe82c89fddfb7c20cac4bd8a5f2151e82f123a4a66ceb0be931537e6e6b19f22` |
| Worker | `416604cc00f2fba9910fec7b05fca21076f5c7cea6bd197a262412bf10a06567` |

Public home, registration, merchant entry, protected-route denial, expired-session browser login redirects and friendly correlation-coded 404 checks passed. Public CMS copy was preserved rather than republished. This does not prove authenticated merchant acceptance.

## Cron and retention — actual final state

The updater/transport cron was removed after success. Existing oneQay worker was replaced with the following **every-minute** command; unrelated cron entries were untouched:

```text
/usr/local/bin/php /home/pekd7254/oneqay-production-test/oneqaydev-pos-update-7efdda7648c2/control-worker.php > /home/pekd7254/oneqay-production-test/oneqaydev-pos-update-7efdda7648c2/worker-result.json 2>&1
```

Worker sample `1792581624`: `failed=false`, mail/health/campaign phases `completed`, mail processed `0`, campaign recipients `0`. There was no queued message in this sample. Actual SMTP acceptance, inbox delivery and spam placement are **NOT VERIFIED**. Do not describe a healthy worker or a queued acknowledgment as delivered mail. A future real test needs an explicitly chosen recipient and actual resulting report.

Retention completed after its 120-second grace, `warnings=[]`. At 18:20 WIB the verified archive receipts existed:

- Old expanded c2e release: `0852823d7547ad3b012ea6fc739054516e8dff229a1657cedf81356dabbe0d88.json`.
- Duplicate new candidate payload: `bc8eef443bbbe733cb8fac63dfb551d90e5c4b7119183f11f88e407c2e810dac.json`.

Only `production-7efdda7648c2` remained expanded in `releases`. Later maintenance reported COMPLETE with `freed_inodes=0` and `archived=[]` because it was a no-op; those values are not the original aggregate savings. Do not delete active releases, private runtime, shared storage, journals or backups to save inodes. Keep verified private ZIP recovery evidence.

## Transport experience and next-chat deployment

The last deployment used manual cPanel login and browser interaction. Ordinary File Manager ZIP upload returned generic 0% failure; this did **not** prove an inode or quota limit. A private PHP CLI transfer through the ordinary cPanel editor/cron fetched immutable source/operator files and the certified artifact over verified HTTPS, checked hashes and reproduced the exact GNU-tar source inventory/request. It returned `TRANSPORT_READY_NO_ACTIVATION` before activation. No application bytes or checks were bypassed.

Hosting PHP was **8.3.35**, with curl/zip/phar/zlib; `/usr/bin/python3` was unavailable. Do not require Python, Composer, Node, SSH, symlinks, disabled TLS or browser session extraction on this hosting. GNU long tar member names need compatible extraction; a source inventory must still be verified.

The owner now requires **ordinary-chat deployment without Cloud Browser**. For the next release, prepare a certified installable packet and short Indonesian steps; the owner uses their own cPanel File Manager/Cron Jobs. Any direct automated transport requires a separately available, explicitly authorized connector; do not assume cPanel access from chat. Do not request passwords/OTP in chat. Never publish short-lived artifact tokens, hardcode expiry bypasses, or issue a grant before upload is ready. Give a durable packet route and fresh authority when the owner is ready, rather than an already-expiring link during development.

The old operator is pinned to predecessor c2e and **cannot deploy a new successor**. Reuse engineering patterns, not its authority/source/predecessor constants. A successor must bind this observed 7ef runtime, current receipt and actual new certified source. UI-only work should not need database migrations; if a real schema change is needed, plan and qualify it separately.

**Forward recovery required:** once the stock schema exists, old stock-incompatible business-on code must not be restored. Preserve schema/financial evidence and prepare a compatible forward fix. New deployment recovery must also honor this boundary.

## Outstanding work, not missing deployment

1. Next requested scope: [modern UI/UX, exact palette and resilient progress](../design/ONEQAY_UI_MODERNIZATION_2026-10-21.md).
2. Actual SMTP acceptance/inbox test with an explicitly chosen recipient.
3. Authenticated business acceptance and owner-run real cash/offline pilot.
4. Separately qualified Windows artifact distribution/signed updater promotion.

Do not restart closed historical sprints or present these unverified acceptance items as proof the release was never deployed. Update this checkpoint with real successor evidence, not a fictitious 100% completion claim.
