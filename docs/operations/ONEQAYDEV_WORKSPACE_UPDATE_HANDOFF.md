# oneQayDev certified merchant workspace update

Author by Lab | zefry

The owner reports working merchant sign-in and requests a more complete authenticated production interface. Deploy the newly certified presentation release as a complete private immutable release. Never overlay source into `production-192c8a3f7190`, relabel its archive, replay provisioning/migrations, or run its health-off upgrade/activation operators against the working business-on installation.

## Preconditions and packet

`tools/production/cpanel/update-oneqaydev-certified-workspace.php` is the bounded successor. The generated wrapper contains this exact operator as `update.php`, the certified official archive and manifest, an exact file-integrity manifest and extracted official payload, and `authority-request.json` binding the source, archive, source-certification run, operator digest, target, existing runtime/entry/routing hashes, merchant receipt, and business-on rollback. The wrapper is extracted under `/home/pekd7254/oneqay-production-test/oneqaydev-ui-upgrade-<source-prefix>/`.

The current working baseline is source `192c8a3f719006ecdc5f8a4216d09c285b747e59`. Its native final activation operator SHA256 is `bd529156ec6327841290643c075063ede635a1288bc6cb1075a42db8ec1a7a49`, host-result SHA256 is `ae75314557a4aa2b885bdbbf5df56f04837a690a72385e99c9f08dad778e2356`, and business-on environment SHA256 is `647e48338c5519036b3a7867bf984cd6eeec559235816621ffdf5cf4a124ed70`. These are immutable preconditions. A changed baseline stops before switching; do not suppress the check.

The operator obtains a new unedited owner comment from native GitHub issue #946 over verified HTTPS. The exact request is included in the grant; engineering merge authorization and the older release's activation grant are insufficient. Its one-hour window authorizes execution; it does not automatically turn off a successfully updated business. Record it only after the certified packet is ready.

## Behavior

The helper verifies the old business-on routing, immutable source, current assets, merchant receipt, read-only database scope/password check, old HTTPS gate/cookie/denial behavior, and new certified payload. It rejects any change to backend PHP, views, schema, routes, dependency locks, or entry templates.

The new private environment changes only `ONEQAY_RUNNING_SOURCE_COMMIT` and `ONEQAY_RUNNING_ARTIFACT_SHA256`; all existing merchant, APP_KEY, DB credentials, session path/cookie, MFA and business flags remain byte-for-byte unchanged. The new release binds that environment and passes the native production gate and secure-session probe before switching.

Rollback snapshots the currently working business-on bridge/routing, untouched health index/placeholder, and old assets. New hashed assets are added alongside old hashed assets; the official new manifest becomes current. Existing browser tabs can continue to request old assets. Hash collisions fail closed. Protected staging remains untouched.

The switch installs a fresh environment-bound PHP entry and updates routing with the existing cPanel PHP handler retained. It then verifies live/ready HTTPS, fresh native business entry, secure session cookie, denied unauthenticated POS, actual new JS/CSS bytes, source/config/merchant preservation, and asset readback. A failed switch restores the previous business-on installation; it never rolls back to health-only business-off mode. A successful repeated cron verifies existing bindings without replaying the mutation. Stopped attempts require explicit correction rather than automatic retry.

## Owner-host command and result

The final handoff supplies one exact command in the hosting-compatible form:

```text
/usr/local/bin/php /home/pekd7254/oneqay-production-test/oneqaydev-ui-upgrade-<source-prefix>/update.php update <native-owner-comment-id> > /home/pekd7254/oneqay-production-test/oneqaydev-ui-upgrade-<source-prefix>/cron-result.log 2>&1
```

The helper writes secret-free `update-result.json`. Success is `BUSINESS_WORKSPACE_UPDATED_ACCEPTANCE_PENDING`, business traffic `ON_PRESERVED`, with native gate, HTTPS assets, secure session and merchant preservation verified. This does not assert a real owner CASH sale, MFA challenge, receipt, shift close, reconciliation, or reporting acceptance. No direct host execution is claimed when the agent's browser/host access is unavailable.

Manual rollback, only when requested or required to recover a failed update, uses the same exact private operator with the `rollback` argument. It restores the previous source and working business-on routing/assets, preserving existing environment and database.

## Validation

The existing application/deployment regressions and dependency audits remain enabled. The update fixture verifies exact native grants, expiry/edited/foreign grants, source integrity and links, path bounds, unchanged backend/schema/locks, preservation of all runtime fields except source/artifact, retained hashed assets, collision rejection, and actual local restoration to the previous business-on assets/routing. HTTP, DB and credential fixtures are isolated and do not establish production acceptance.
