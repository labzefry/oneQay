# oneQayDev Final Business Go-Live Handoff

Author by Lab | zefry

## Scope and authority

Only `https://oneqaydev.n07.my.id` and its isolated private deployment are
authorized. Preserve the existing database, merchant, APP_KEY, credentials,
migrations and protected staging `https://oneqay.n07.my.id`. The Product
Owner's continuation instruction supplies engineering squash-and-merge
authority. It does not independently authorize host business traffic.

No migration, merchant reprovision, updater activation, live customer data or
external payment is permitted. Real acceptance must use authorized merchant
test data and CASH. Never substitute CI or a local fixture for host proof.

## Completed source and certified upgrade

PR941 was squash-merged as `d78bede01d46c6cb4154148ce361809e5da8ab0b`.
The final PR head was `ccef4cef55ca85610d36a6c8e68c4b28f2cbcd9f`:
116 checks succeeded, one authority-conditioned qualification was skipped,
and none failed. Main's 15 checks succeeded. The native execution record is
<https://github.com/labzefry/oneQay/pull/941#issuecomment-6106279537>.

That source makes Production session cookies Secure even when a stale binding
requests false, retaining HttpOnly and SameSite=Lax. Seven historical
application envelopes and Sprint268's bounded dependency-audit envelope were
corrected without skipping functional, security or dependency checks.

Production Business Release Certification `38117125557` succeeded. Installed
release identity: `production-d78bede01d46`; artifact `11693586623`.
Official archive SHA-256:
`23c8759e53e3da942a400848ac5b6b6e43304b92b4b6d9183bb15dcf08a4870f`.
No historical archive was patched or relabelled.

## Actual operator-reported host checkpoint, 2026-10-11

The owner uploaded `upgrade-result (1).json`. It reports:

- `HEALTH_READY_BUSINESS_OFF`, installed source/release exactly as above.
- 6,252 source-integrity files checked; retained placeholder preserved.
- Existing first merchant `EXISTING_APPLIED_PRESERVED`; receipt unchanged.
- Secure session configuration verified; file write/read/delete probe passed.
- 16 required runtime features prepared; both independent traffic flags OFF.
- Migrations and staging `NOT_TOUCHED`; rollback snapshot verified.
- Previous release `production-409ac6b2bdb8`.
- Current health-only bridge SHA-256:
  `ff7b3888b72c163641baa95d41b2431dcdaee52a754ca0e17d6db2095e5df125`.
- Merchant receipt SHA-256:
  `e1cddcd1f0334a85b073c0526ecbc6236ba530dbc3a671b00f4d5d18e4a3109a`.
- Rollback directory:
  `/home/pekd7254/oneqay-production-test/oneqaydev-upgrade-d78bede01d46/rollback`.
- Business activation `NOT_AUTHORIZED`; actual business acceptance
  `NOT_VERIFIED`.

These are owner-supplied host results, not direct cPanel observations by this
Work session. There is still no verified real-host sign-in/MFA, CASH sale,
receipt persistence/replay, inventory decrement, cash reconciliation, Final
Shift Close or report acceptance. Final status remains BUSINESS_OFF.

## Newly reproduced native HTTP defect and bounded correction

Activation-operator qualification booted the actual certified Laravel app
through its HTTP kernel with native Production gates, durable file sessions
and existing-merchant-shaped local context. The unmodified d78 POS entry
returned HTTP 500: `Session store not set on request`. Provider-registered
POS routes used `session.active` without Laravel's web/session pipeline.
The prototype activation operator detected the failed unauthenticated-access
check and restored the exact local health bridge, environment and public
files. This was a local fixture; no host traffic was enabled.

The successor changes only these two application paths:

- `apps/web/app/Providers/PosOperationsHubServiceProvider.php`
- `apps/web/tests/production-session-web-pipeline.php`

For qualified native Production, the POS provider resolves `session.active`
as the existing `web` pipeline before the original authority middleware. This gives provider
routes the canonical cookie, session and CSRF controls. Laravel deduplicates
middleware when a route already belongs to `web`. The original authority,
tenant, MFA, throttling and native Production gate remain in force. Bootstrap
configuration, Local/Test/CI and staging registration are unchanged.

The regression exercises eight real HTTP-kernel requests using isolated
Production-shaped fixtures: unauthenticated GETs to POS, setup, shift start,
cashier, shift close and session inventory return 401; POST sale/login
without CSRF return 419; cookies remain Secure/HttpOnly/SameSite=Lax despite
a false Secure binding. Every registered `session.active` route must resolve
StartSession and CSRF before authority, exactly once. No database or host
acceptance is claimed. This Work session now has an isolated PHP 8.3 runtime
for local checks; the earlier no-local-PHP limitation is superseded.

Production Business Release Certification runs this regression before any
replacement archive is published. The same eight historical workflows
accept only the exact successor branch, base source and bounded source
paths. All existing functional and dependency checks still run. Dependencies
and migration sources are unchanged. Final head, CI, merge and certification
identities belong in the native PR execution record, because a source file
cannot embed its own final commit SHA.

## Access limitation and next host delivery

The owner is signed in through their own browser; its cookies are not shared
with Work Cloud Browser. The authorized cPanel login attempt returned a
network error. Manual takeover was unavailable. The single requested retry
returned `502 Bad Gateway / The remote server does not speak TLS`. Do not
repeat credential attempts, downgrade TLS or claim authenticated host access.

Private root: `/home/pekd7254/oneqay-production-test`.
Public root: `/home/pekd7254/public_html/oneqaydev.n07.my.id`.
Protected staging: `/home/pekd7254/public_html/oneqay.n07.my.id`.

After successor source CI, exact-head engineering authority and squash merge,
use only the newly certified official artifact. Deliver one preserved-binding
upgrade from installed d78 to that source, keeping BUSINESS_OFF. Never patch
the installed/certified d78 archive or run the obsolete 409-bound inspector.
Provide a direct `/usr/local/bin/php ... > ... 2>&1` Cron command, not a shell
`if` wrapper rejected by the owner's cPanel. Give one manual instruction at
a time, then continue from its sanitized result. Remove temporary Cron after
one execution. No private credentials, runtime env or TOTP material should
be returned in chat or committed.

## Remaining independent activation and real acceptance

The existing contract
`docs/operations/ONEQAYDEV_BUSINESS_ACTIVATION_READONLY_PREFLIGHT_2026-10-11.md`
requires independently bound short-lived Product Owner host business-traffic
authorization: exact domain, certified source/archive, private target,
health-only bridge and rollback snapshot, with expiry. Prepare and qualify
the concrete successor operator first; obtain that separate business grant
only when the operator and host gates are ready. A merge comment is not a
business grant. The operator must authenticate the native grant, preserve
bindings, recheck dependencies and make one recoverable switch. On failure
traffic stays OFF or verified rollback closes business access.

After the controlled switch, record actual sign-in/MFA, tenant/outlet/device
scope, catalog/inventory, opening shift/cash evidence, authorized CASH sale,
durable receipt and replay protection, inventory decrement, closing cash,
Final Shift Close and audit/report evidence. Activation alone is
`BUSINESS_ACTIVATED_ACCEPTANCE_PENDING`, never GO_LIVE_SUCCESS.

Only completed real-host acceptance permits GO_LIVE_SUCCESS. Any cutover to
the occupied staging domain needs fresh separate authority; updater stays
INACTIVE.


## Latest official successor and concrete activation operator

PR942 was squash-merged as `192c8a3f719006ecdc5f8a4216d09c285b747e59`.
Its exact head `6257ef2542d138fe780915493c9cf1f7e35073f4` qualified 116
SUCCESS, one authority-conditioned SKIP and zero failed checks. Native
engineering authority and protected-branch conditions passed without bypass.
Certification run `38122104639` succeeded; release
`production-192c8a3f7190`, artifact `11695427525`, 6,252 integrity files.
Official TAR SHA-256:
`ff1a4e92eba9d2e5c79c33acc8aa788d4561863541242d16674094704ac0325f`.
The native execution record is
<https://github.com/labzefry/oneQay/pull/942#issuecomment-6106806104>.
Executed main check runs: 14 SUCCESS. GitHub also reports seven failed
historical workflow records for unchanged files, already present on d78;
these are not counted or represented as passed. A sampled record has zero
jobs. They are outside this bounded correction.

The preserved-binding successor upgrade package is prepared from the exact
new official artifact. Local qualification passed successful preparation,
idempotent replay, forced health-failure rollback to d78, source/merchant
substitution rejection and refusal of premature traffic flags. HTTPS is
substituted only in isolated QA. Last actual host evidence still reports
installed d78 BUSINESS_OFF; the successor has not been installed by this
Work session. The owner must run its direct PHP preparation Cron and return
only the sanitized `upgrade-result.json`.

`tools/production/cpanel/activate-oneqaydev-certified-business.php` is a
separate CLI operator for that exact installed successor. Its source belongs
to this tooling governance change; the certified application remains the
unchanged official 192c source. No archive is relabelled after a tooling-only
merge. The operator imports only the pinned preparation helper, validates
all certified files, rejects cached config/routes, verifies prepared private
bindings, and preserves the historical 409-bound merchant receipt.

Before mutation it independently fetches an exact request-bound owner grant
from GitHub over verified HTTPS. It requires native `labzefry` User / OWNER
identity, a comment on PR942, an unedited creation timestamp, exact domain,
source/archive and closed scope, and a 900-second activation window. A local
locator does not grant authority. No grant is included in this delivery and
an engineering merge comment is rejected. The request pins the operator,
helper-derived host checks, expected health bridge, merchant receipt and
qualified rollback directory/target.

The operator performs only SELECTs to verify the existing database baseline,
merchant tenant/identity/organization/outlet/device graph and password hash.
It derives a new private business env while preserving all other bindings.
It enables the two independent traffic flags and existing merchant entry
context only after the independent grant qualifies; merchant bootstrap
remains disabled. Native ProductionBusinessRuntimeGate, MFA/step-up, session
policy and required routes must qualify before the public index/htaccess
switch. It checks actual HTTPS health, merchant entry metadata, secure cookie
and unauthenticated POS denial. Any switch/replay failure triggers rollback
and records its verified or unverified outcome truthfully; revoked owner
authority on replay closes public business access. Rollback preserves
committed database records and does not pretend to undo transactions.

Local integration used the exact official successor payload with fixture
substitutions for GitHub responses, external HTTPS and database SELECTs. The
real native Laravel gate, public HTTP merchant entry, secure session cookies,
unauthenticated POS denial and file-session persistence passed. Explicit
rollback, forced health/cookie failure, merge-authority-only refusal and
revoked-authority replay restoration passed. These are not actual host or
merchant CASH acceptance. The focused CI contract also checks valid native
owner authority, 15 denied mutations, expiry and private atomic output.

After source governance and the owner's successor preparation report, obtain
one explicit separate business-traffic approval for the reviewable exact
request. Then record its native owner comment and issue the private locator
for a single controlled Cron within 900 seconds. Give only one manual
copy-paste instruction at a time. Do not issue business authority in advance
of that owner approval. Activation reports
`BUSINESS_ACTIVATED_ACCEPTANCE_PENDING` / `host_business_acceptance=NOT_VERIFIED`;
actual login/MFA, authorized merchant CASH, receipt/replay, inventory, Final
Shift Close and reporting evidence is still required for GO_LIVE_SUCCESS.
