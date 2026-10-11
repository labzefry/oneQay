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
