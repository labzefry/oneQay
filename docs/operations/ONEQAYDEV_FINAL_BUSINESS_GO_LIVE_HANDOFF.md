# oneQayDev Final Business Go-Live Handoff

Author by Lab | zefry

## Scope and authority

Target: `https://oneqaydev.n07.my.id`, exclusively. Preserve the existing
database, merchant, APP_KEY, credentials, migrations and protected staging
`https://oneqay.n07.my.id`. Engineering authority through squash-and-merge
is carried forward from the Product Owner's continuation instructions.
Engineering merge authority does not grant host business activation.

Do not repeat migrations, provision another merchant, enable an updater or
use an external payment provider. Production acceptance must use authorized
merchant test data, with no live customer data or invented evidence.

## Live repository checkpoint at entry, 2026-10-11

- Verified main: `4439d08c75542c653ae919127991333c3432ea38`; PR #940 merged.
- PR #941: open, ready, mergeable; verified initial head
  `354018cb5e9be7fcc6396ebeb8f23285471441bd`.
- Native Product Owner merge status succeeded for that initial head;
  the owner comment is
  <https://github.com/labzefry/oneQay/pull/941#issuecomment-6101477789>.
- Exact-head checks: 108 succeeded, seven failed, one skipped.
  The skipped source qualification is the independent operational-authority
  workflow; it must not be represented as host acceptance.
- Secure-cookie behavioral test and Production business source regression
  succeeded on the initial head. No new certified archive exists at entry.
- All seven failures occurred in historical application-path constraints:
  Sprint211, Sprint212, Sprint213, Sprint214, Sprint218, Sprint222, Sprint223.
  Their functional tests ran before rejecting the newly changed session config.

## Bounded source correction in PR #941

Production always requires Secure session cookies, including when a stale
`SESSION_SECURE_COOKIE` binding is false. HttpOnly and SameSite=Lax remain
required; isolated HTTP tests retain configurable Secure behavior.

Seven historical workflows now accept the exact PR941 branch and number
only when its application diff consists of these two files:

- `apps/web/config/session.php`
- `apps/web/tests/production-session-cookie-policy.php`

Deletion is rejected. Extra application, dependency and migration paths
remain rejected. Existing functional, rollback, deny-by-default, schema,
runtime and source-cleanliness tests still execute. The focused session CI
also validates this application envelope with immutable head checkout.

Local validation passed 63 actual Git-diff boundary checks across the seven
workflow constraints: the authorized diff succeeds, while wrong branch,
wrong PR, extra application/test paths, dependency changes, migration
changes, deleted config and omitted behavioral regression are rejected.
Eight workflow YAML files parse successfully. PHP tests require GitHub CI
because this Work container has no PHP executable. Do not substitute local
Git-boundary results for PHP, MySQL or actual host acceptance.

The final head, latest CI and merge result must be read from
<https://github.com/labzefry/oneQay/pull/941>. A source file cannot embed
its own final commit SHA; the final execution comment records the immutable
head and outcome. Refresh native exact-head authority only under the actual
Product Owner instruction, without representing it as independent review.

## Operator-reported host checkpoint

The continuation attachment supersedes older repository handoffs which said
the 409ac6b upgrade or first merchant had not yet run. These are operator
reports, not new observations by this Work session:

- Installed release: `production-409ac6b2bdb8`.
- Certified source: `409ac6b2bdb80d31fbfb9425ddbb413a278b97e6`.
- Certification run: `38066218900`, reported success.
- Archive SHA-256:
  `8ad336c2396fb5d6e95bcea80c574a305040c6ba4f570c9a2edea911186f3f3e`.
- Upgrade: `HEALTH_READY_BUSINESS_OFF`; 6,252 integrity-checked files.
- Merchant: `ONEQAYDEV_FIRST_MERCHANT_APPLIED`; private credential `APPLIED`.
- MySQL SELECT passed; 43 tables and at least 27 migration receipts.
- HTTPS health passed; staging untouched; business traffic OFF.
- Latest preflight: `PREACTIVATION_BLOCKED`, nine required features missing,
  `session_configuration_verified=false`.
- No real-host CASH, receipt, stock, shift-close or reporting acceptance
  has been verified in this execution.

## Confirmed access limitation

Work Cloud Browser has no cPanel session. Opening the authorized target
failed with `net::ERR_BLOCKED_BY_CLIENT` before a host page was available.
No host configuration, bridge, source, database or credentials were modified.
Do not infer host access from a successful GitHub connector operation.

Private root: `/home/pekd7254/oneqay-production-test`.
Public root: `/home/pekd7254/public_html/oneqaydev.n07.my.id`.
Existing shared runtime: `/home/pekd7254/oneqay-production-test/shared`.
Confirm actual paths on host before any mutation; keep secrets out of logs.

## Continuation sequence

1. Resolve PR941's bounded CI correction; verify every latest check and
   native exact-head merge status. Squash only the verified head with
   `Author by Lab | zefry`, without bypassing required checks.
2. On merged main, await Production Business Release Certification. Use
   only its exact-source artifact; record source, run, release and archive
   SHA-256. Do not patch or relabel the previous certified archive.
3. Prepare a recoverable cPanel upgrade package from that archive. Preserve
   existing private bindings and the merchant; do not run migrations.
4. With authenticated cPanel access, verify isolated paths, snapshots,
   session persistence, feature dependencies and runtime-gate bindings.
   Keep the two independent traffic-authorization flags disabled.
5. Obtain the exact independent activation authority required by the
   repository and target. Apply a recoverable switch only after host gates
   qualify. On failure remain BUSINESS_OFF or execute qualified rollback.
6. Verify login/MFA, tenant/outlet/device boundaries, catalog/inventory,
   shift opening/cash evidence, authorized CASH sale, receipt persistence,
   idempotency, inventory decrement, closing cash, Final Shift Close and
   reporting on the host. Record sanitized actual evidence.

Final classification is BLOCKED while access or an essential gate is
unavailable, BUSINESS_OFF after a verified inactive deployment, and
GO_LIVE_SUCCESS only after authorized activation and actual acceptance.
