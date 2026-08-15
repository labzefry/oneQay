# M7.5 P1 Outbound DNS + HTTPS Evidence — 2026-08-15

Attribution: **Lab | zefry**

## Purpose

This additive record reconciles a Product Owner-authorized, bounded, non-Production M7.5 qualification of outbound DNS resolution and HTTPS connectivity from the cPanel Technical Preview hosting environment.

The qualification was intentionally narrow. It did not create a database, database user, migration, permanent schema, runtime configuration change, Production connection, provider integration, API credential, or durable business data.

## Governed baseline

Published `main` before this reconciliation:

`c25760a832d265ac30e8b0bbecdb59f44837bcc3`

Published tree:

`c96f78fd24087ffaad6e6f7ba46d82514e434447`

Canonical evaluator before this reconciliation:

- **15 VERIFIED**;
- **14 BLOCKED**;
- outcome: **BLOCKED**;
- lifecycle authority created: **false**.

PR #115 remains a separate Draft PR and is not used as this PR's baseline.

## Bounded outbound DNS + HTTPS qualification

**Status: VERIFIED**

The qualification ran through PHP 8.3 CLI from a temporary cPanel Cron entry and performed only:

1. DNS resolution for the neutral public host `example.com`;
2. a bounded HTTPS HEAD request to `https://example.com/`;
3. TLS peer verification and hostname verification;
4. a sanitized single-line result written to a private non-public evidence file.

Observed sanitized result:

```text
ONEQAY_OUTBOUND_QUAL|DNS=OK|HTTPS=OK|HTTP=200|CURL_ERRNO=0|UTC=2026-08-15T15:59:01+00:00
```

The request body was not persisted, no credential/API key/token was used, and the maximum request timeout was bounded to 10 seconds.

This provides direct execution evidence that the target hosting environment can resolve a public DNS name and establish a certificate-verified outbound HTTPS connection from the PHP CLI execution model used for oneQay qualification.

## Cleanup

After evidence capture, the temporary outbound qualification Cron entry was removed.

The private sanitized result file was retained for manual audit continuity. Its hosting account path and account identifier are not recorded in this repository.

## Control reconciliation

Only this mandatory control changes:

- `RUNTIME:OUTBOUND_DNS_HTTPS`: `PARTIAL -> VERIFIED`.

No other engine/runtime control is promoted.

Proposed evaluator from the canonical `main` baseline therefore becomes:

- **16 VERIFIED**;
- **13 BLOCKED**;
- outcome: **BLOCKED**;
- lifecycle authority created: **false**.

The separate Draft PR #115 proposes connection/resource-limit evidence independently. Its unmerged changes are deliberately not folded into this PR's count.

## Security and privacy

No raw screenshot, control-panel account identifier, home-directory identity, password, token, API key, private key, runtime `APP_KEY`, database identity, database username, customer data, BPJS data, personal data, or Production data is committed.

The evidence does not authorize a real payment/service provider integration. `example.com` is used only as a neutral public HTTPS qualification target.

## Lifecycle

- M7.5: **BLOCKED / INCOMPLETE**;
- M7.6: **BLOCKED / NOT AUTHORIZED**;
- M7.7: **BLOCKED / NOT AUTHORIZED**;
- Phase 0 Exit: **NOT APPROVED**;
- Release: **NOT AUTHORIZED**;
- Production: **NOT AUTHORIZED**;
- Production readiness: **NO-GO**.

This record creates no authority for database creation, database users, migration execution, permanent schema, provider credentials, rollback, restore, Release, or Production.

Attribution: **Lab | zefry**
