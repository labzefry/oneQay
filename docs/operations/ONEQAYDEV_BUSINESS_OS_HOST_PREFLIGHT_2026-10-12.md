# oneQayDev Business OS Host Readiness — Read-only Gate

Author by Lab | zefry · 12 October 2026

## Scope

The backend-changing Business Command Center release `production-d15f2fc3f9aa` is source-certified (run `38236323278`), **not yet host deployed**. The last documented successful host update is the bilingual `production-e93b7fef42cb`, whose operator prohibits backend/source modifications. Do NOT edit or skip its `uPresentationOnly` backend integrity gate; do NOT point it at the new release.

A new **read-only inspection** has been added in `tools/production/cpanel/inspect-oneqaydev-business-os-upgrade.php`. It checks the private/public fixed path is the expected isolated oneQayDev target, reads only bounded JSON metadata from the previous successful update plus `RELEASE.json`, verifies hashed public bridge and routing match the recorded update, and refuses to treat a partially existing candidate release as a pristine update. No runtime environment contents, private credentials, database, HTTP, log, session or payment data are read or modified. Source/host paths and secrets are not echoed. It emits sanitized JSON and deliberately reports `live_https_verified=false` and `business_acceptance_verified=false`.

## Owner-controlled one-shot cPanel CLI probe (NO SSH)

Only the owner should execute after downloading the exact merged tool source. Place the PHP file in a **private** directory under `/home/pekd7254/oneqay-production-test/`, e.g. `business-os-readiness/`; do **not** put it under `public_html`. Confirm PHP 8.2+ and source file hash before scheduling. In cPanel Cron Jobs schedule a one-off temporary job using this command (change the source file location only if the private directory differs):

```sh
/usr/local/bin/php /home/pekd7254/oneqay-production-test/business-os-readiness/inspect-oneqaydev-business-os-upgrade.php > /home/pekd7254/oneqay-production-test/business-os-readiness/host-preflight.json 2> /home/pekd7254/oneqay-production-test/business-os-readiness/host-preflight-error.log
```

Remove the Cron entry as soon as its single run completes. The returned JSON is not a deployment grant or evidence of successful HTTP acceptance. Do not rerun if it fails: return only the sanitized JSON to reconcile the mismatch. The script itself makes no host changes, and this command is **not** an instruction to migrate/provision, deploy or replay a merchant command. No upload of the entire newer release yet.

## Sufficient output for the next engineering gate

A state of `HOST_FILE_BINDINGS_CONSISTENT_PREDEPLOYMENT` means recorded file evidence is consistent only; it does **not** prove current live browser route, runtime authorization, POS transaction readiness, rollback success, or d15 deployment. The following step is to build and test a backend-capable **exact-source** successor operator with an immutable certified archive; a fresh host-specific release/rollback authorization; a no-symlink-compatible atomic bridge; active sessions/asset preservation; and fail-closed recovery. A `STOPPED_FAIL_CLOSED_HOST_STATE_UNQUALIFIED` must be reconciled before any write attempt. No grants in historical PR #948/#949 can be repurposed as host update authority.
