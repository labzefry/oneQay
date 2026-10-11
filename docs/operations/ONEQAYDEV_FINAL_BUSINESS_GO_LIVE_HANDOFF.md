# oneQayDev Final Business Go-Live Handoff

Author by Lab | zefry

The only target is `https://oneqaydev.n07.my.id`, private root
`/home/pekd7254/oneqay-production-test`. Preserve the existing database,
merchant, APP_KEY, credentials and protected staging `https://oneqay.n07.my.id`.
Standing owner instructions authorize engineering continuation through squash
merge and final isolated business deployment. The host operator still requires
a separately recorded, authenticated native owner grant for its exact request;
an engineering merge comment is insufficient.

## Certified application and actual host evidence

PR942 delivered the native Production POS web/session middleware correction.
Certified application source remains
`192c8a3f719006ecdc5f8a4216d09c285b747e59`, release
`production-192c8a3f7190`. Certification run `38122104639` succeeded.
Official archive SHA-256:
`ff1a4e92eba9d2e5c79c33acc8aa788d4561863541242d16674094704ac0325f`.
Tooling changes must never relabel or modify that archive.

Owner-supplied `upgrade-result (2).json` proved HEALTH_READY_BUSINESS_OFF:
6252 source files, 16 prepared features, preserved merchant, durable secure
file-session probe and verified rollback. Exact health bridge SHA-256:
`6870348b43644d61fd80d7dcef4bdc4b9158a1a314ce558d6752dda8ab1c2934`.
Exact private merchant receipt SHA-256:
`e1cddcd1f0334a85b073c0526ecbc6236ba530dbc3a671b00f4d5d18e4a3109a`.

PR943 introduced governed activation tooling, main squash
`5d8723d9de4731f1e52d4fd859528ee6f85ad9c4`. Separate business grant
<https://github.com/labzefry/oneQay/pull/942#issuecomment-6107114571>
was created at 2026-10-11T08:25:59Z and expired at 08:40:59Z.
The owner returned `activation-result.json` reporting
ACTIVATION_FAILED_ROLLED_BACK / MERCHANT_ENTRY_HTTPS_NOT_READY:
business OFF, FILES_AND_BUSINESS_OFF_VERIFIED, rollback HTTPS PASS and
database records PRESERVED_NOT_REVERTED. Execution record:
<https://github.com/labzefry/oneQay/pull/942#issuecomment-6107208021>.
Exact failed receipt SHA-256:
`620bf2311dada3cb950f83e390494ad639b6bcf3709537fe22b0cbd787170941`.

Owner-supplied `merchant-entry-diagnostic.json` subsequently proved that the
candidate login renders HTTP 200 in a private CLI process, with enabled entry
and login context; protected files stayed unchanged and business OFF.
HTTPS after rollback returned the expected old health-only 503 response,
Cloudflare DYNAMIC. No application or public PHP log was available. This
does not prove the original failed HTTP status, OPcache configuration or
root cause. CLI evidence is not successful live-host business acceptance.

## Bounded fresh-entry retry

The revised operator uses private folder
`oneqaydev-business-retry-192c8a3f7190`, a separate r2 candidate environment,
new public entry `oneqay-business-192c8a3f7190-r2.php`, and a new rollback
snapshot. The previous failed operator, receipt and snapshot remain intact.
It qualifies their hashes, the restored OFF state, the same certified source,
private merchant receipt, read-only database/schema/scope/password checks,
native runtime/MFA/session policy, and a fresh 900-second owner grant.

Root and routed requests explicitly use the new filename; the already
qualified cPanel PHP handler is retained. The original health index remains
unchanged. HTTPS success requires a fresh bridge identity, rendered login,
secure session cookie and unauthenticated POS denial. Status, safe entry
phase, capability names and boolean evidence are retained on success/failure;
HTML, cookies, passwords and actual merchant identifiers are never emitted.
The new compiled bridge checks its exact private binding file on every
request, so removing that binding closes it even with stale bytecode.
Rollback removes that binding first, restores the prepared OFF environment
and original public files, and removes the new entry. HTTP uncertainty is
reported honestly. Failed attempts are never retried automatically.

Upload the small retry package into the existing private root. After the
native owner comment has been issued and verified, execute once:

```sh
/usr/local/bin/php /home/pekd7254/oneqay-production-test/oneqaydev-business-retry-192c8a3f7190/activate.php activate OWNER_COMMENT_ID > /home/pekd7254/oneqay-production-test/oneqaydev-business-retry-192c8a3f7190/activation-run.log 2>&1
```

Remove the Cron after one execution. Return only its sanitized
`activation-result.json`. Do not redirect stdout into that atomic JSON file.
Explicit rollback is `php .../activate.php rollback`.

## Remaining acceptance and access limitations

Cloud Browser cPanel access failed with TLS/502; its session cannot reuse the
owner's personal Chrome login. No direct host execution is claimed here.
Only the owner's cPanel executes the supplied Cron.

No migrations, merchant reprovision, staging mutation, updater or external
payment is allowed. Real acceptance must use authorized merchant test data
and CASH. After activation, qualify actual merchant login/MFA/session,
tenant/outlet/device scope, catalog/inventory, opening shift/cash, CASH sale,
durable receipt/replay, inventory decrement, closing cash, Final Shift Close
and reports. Until those are verified, success is only
BUSINESS_ACTIVATED_ACCEPTANCE_PENDING and GO_LIVE_NOT_YET_PROVEN.
