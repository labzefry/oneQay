# oneQayDev cPanel No-SSH — EXACT NON-EXECUTED HEALTH-READY UPGRADE HANDOFF (2026-10-11)

**Author by Lab | zefry. READ THIS BEFORE ANY CRON.**

## Host state before upgrade
User has **NOT performed** this new upgrade. Only old isolated health-only bridge on `https://oneqaydev.n07.my.id` from `production-0400241bd83d` was verified: `/health/live` JSON `ok`; old `/health/ready` JSON `unavailable`. Earlier on-host readiness 20/20 PASS and bridge `HEALTH_ONLY_ACTIVE_NO_BUSINESS_TRAFFIC`. Existing database and staging must not be touched.

Exact target:
- oneQayDev private root: `/home/pekd7254/oneqay-production-test`
- oneQayDev public docroot: `/home/pekd7254/public_html/oneqaydev.n07.my.id`
- protected staging: `/home/pekd7254/public_html/oneqay.n07.my.id` and `/home/pekd7254/oneqay-staging`
- previous certified release `releases/production-0400241bd83d`, previous on-host operator `oneqaydev-health-activation-0400241bd83d/`
- private shared `shared/production-runtime.env` and `shared/private-bindings.json` with expected 0600 permissions.
- Separate dev MySQL database already schema-reconciled: 27 core + module #28 = 28 receipts, 43 tables; do not rerun migrations or secret prep.

## Trusted release vs standalone local upgrade kit: DIFFERENT ARTIFACTS
Official GitHub Actions `Production Business Release Certification` run **38066218900 SUCCESS** for application source **`409ac6b2bdb80d31fbfb9425ddbb413a278b97e6`**:
- Actions artifact id **11674473139**; name `oneqay-certified-business-production-409ac6b2bdb8`.
- Official ZIP SHA256 **4f6866003c598902d9b1a034bc59be8f43ed567e4807823c79d3c42d2bb83449**.
- Embedded release `production-409ac6b2bdb8.tar.gz` SHA256 **8ad336c2396fb5d6e95bcea80c574a305040c6ba4f570c9a2edea911186f3f3e**.

Distinct **standalone local operator ZIP**, not a GitHub Release asset: `oneqaydev-upgrade-409ac6b2bdb8.zip`.
- **Actual last local file SHA256 (verified 2026-10-11): `5b3c18bc6b72e3ddda0402035cce7e273cf0109b8cda4e5d0f0b32cfdfca1256`**.
- This supersedes stale hash claims **9cbe4f9b...** and **6cbcae...** in previous PR #938 comments; the prior comment also incorrectly said the kit attempts first merchant provisioning. Its current `README.txt` and `upgrade.php` clearly say **IT DOES NOT PROVISION MERCHANT, RUN DATABASE MIGRATIONS OR ENABLE BUSINESS TRANSACTIONS**.
- Last local `unzip -t` and `php -l upgrade.php` passed, embedded tar SHA matching Actions. One negative local run reported `OLD_HELPER_ATTESTATION_FAILED` as expected outside the exact cPanel host. This does not prove successful real host execution.
- ZIP members nested in `oneqaydev-upgrade-409ac6b2bdb8/`: `README.txt`, `upgrade.php`, `release-files.sha256`, official manifest, official `.tar.gz`, tar checksum. The kit remains a conversation artifact, not a repository file; verify exact bytes/obtain original file in new chat rather than constructing sandbox paths or downloading a mismatched archive.

## Manual cPanel procedure — USER MUST EXECUTE, not yet done
Follow **ONLY when exact ZIP hash is verified** and health-only old bridge/status and target paths are still unchanged. Never deploy to occupied staging.

### 1. Upload
In **cPanel File Manager** navigate to:
`/home/pekd7254/oneqay-production-test`

Upload `oneqaydev-upgrade-409ac6b2bdb8.zip` and choose **Extract**. Confirm resulting directory:
`/home/pekd7254/oneqay-production-test/oneqaydev-upgrade-409ac6b2bdb8/`

Do **not** upload the ZIP or private scripts to `public_html`.

### 2. Extract new release side-by-side
**COPY** `production-409ac6b2bdb8.tar.gz` from inside the extracted operator folder into
`/home/pekd7254/oneqay-production-test/releases/`
and cPanel **Extract** it in that directory. It should result in:
`/home/pekd7254/oneqay-production-test/releases/production-409ac6b2bdb8/`

Keep both the old release `production-0400241bd83d` and original tar inside operator folder. Preserve all private `shared/` files.

### 3. Temporary one-shot Cron (ONLY after 1 & 2 qualified)
In **cPanel > Cron Jobs** select **Once Per Minute temporarily**, paste the complete command:

```bash
/usr/local/bin/php /home/pekd7254/oneqay-production-test/oneqaydev-upgrade-409ac6b2bdb8/upgrade.php deploy > /home/pekd7254/oneqay-production-test/oneqaydev-upgrade-409ac6b2bdb8/upgrade.log 2>&1
```

**Remove the scheduled Cron after it fires ONCE**, independently of success or failure. Never leave a repeating installer. Do not trigger a second run to repair an error, remove lock, edit secrets, or start the old migration Cron.

### 4. Inspect private, sanitized result (DO NOT send secrets)
Open File Manager file:
`/home/pekd7254/oneqay-production-test/oneqaydev-upgrade-409ac6b2bdb8/upgrade-result.json`

Expected good report `state=HEALTH_READY_BUSINESS_OFF`; bad `STOPPED_FAIL_CLOSED`/specific safe error code. If failure, cease and share ONLY sanitized result and harmless error code. Do not expose `production-runtime.env`, `private-bindings.json`, merchant credential file or raw PHP error stack with secrets.

### 5. Verify public new application
Open:
- `https://oneqaydev.n07.my.id/health/live` — HTTP 200, `status=ok`, `service=oneqay-web`
- `https://oneqaydev.n07.my.id/health/ready` — HTTP 200, `status=ready`, `service=oneqay-web`

On success record report/URL evidence and exact new source. Do **not** mark Production business ready merely due to two HTTP 200 responses.

## Operator internal scope and restoration
The PHP operator is CLI-only, exact-target and exact source-bound, checks previous health-only operator/bridge state and hashes, new archive/manifest/6,252 extracted file checksums and private environment, maintains 0600 lock, preserves old placeholder, stages private rollback under its operator directory and attempts a guarded public bridge/assets swap. Rebinds only allowed source/artifact identity values to a new private 0600 runtime environment. In failure, rollback is **best effort**: inspect report and old health before any retry. It does not mutate schema or transactions.

Expected output ceiling **`HEALTH_READY_BUSINESS_OFF`**. This is **not merchant provisioning**, not final accepted POS, not authorized CASH traffic, and not Production cutover to staging.

## NEXT AFTER HEALTH-READY (separate, only when evidence is real)
1. Validate first-merchant exact CLI permission/source/host using merged `ProductionFirstMerchantProvisionCommand` on new source; run only ONCE with owner-only 0600 credential custody, never paste confidential values.
2. Qualify new **business traffic activation operator** with separately granted exact-host Production transaction/traffic authority and a verified rollback; do not edit released JSON or merely set `.env` flags.
3. End-to-end on-host genuine CASH sale with identity + privileged MFA/session, tenant/outlet/device authorization, inventory/stock, register/shift, durable idempotent receipt, closing cash, Final Shift Close, reporting and denial tests. No fake synthetic-only claim.
4. Record sanitized host acceptance and only then label `REAL_BUSINESS_PRODUCTION_GO_LIVE=VERIFIED`; any future `oneqay.n07.my.id` cutover requires separate approval.

## Failure and authority controls
- Source-certified `business_runtime_activation_ready=true` does not mean host-ready, merchant-ready, or approved payment traffic.
- No terminal/SSH prerequisite; operator actions by user via cPanel File Manager and temporarily scheduled PHP CLI Cron.
- Preserve staging root, independent dev DB, historical migration receipts and all `.lock`/`.started` markers.
- Engineering squash merge authority is not unbounded financial or host mutation authority.
