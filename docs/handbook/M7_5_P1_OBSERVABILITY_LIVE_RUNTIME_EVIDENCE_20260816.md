# M7.5 P1 Observability Live Runtime Evidence — 2026-08-16

Attribution: **Lab | zefry**

## Scope

This handbook entry records bounded non-Production Technical Preview deployment and live runtime verification of the observability and safe structured logging capability published by PR #120.

The runtime work was performed under separate bounded deployment authority before this evidence-only Draft PR. This repository change itself performs no deployment.

The purpose is to determine whether `RUNTIME:OBSERVABILITY_LOGGING` can move from `PARTIAL` to `VERIFIED` inside the current M7.5 Technical Preview qualification envelope.

No Production readiness, Release, M7.6, M7.7, restore, rollback, queue, database, migration, or schema authority is created.

## Published source

- implementation PR: `#120`;
- implementation source exact head: `543d8f60559b7769ad597bdc045a4fb7a99505f9`;
- published `main`: `c7159770381e5ade7d88a00d57bd4346c9e73637`;
- published tree: `ec506371f6b5454fc4987f8a4c35f41641356d9b`;
- deployed Preview release: `m75-preview-c7159770381e`.

Canonical M7.5 before this evidence reconciliation remains:

- **21 VERIFIED**;
- **8 BLOCKED**;
- outcome: **BLOCKED**;
- lifecycle authority created: **false**.

## Release preflight and activation evidence

The published Technical Preview artifact for `c7159770381e5ade7d88a00d57bd4346c9e73637` was unpacked into a new private release directory.

The runtime `.env` was not contained in the artifact. The existing private Technical Preview `.env` was copied into the new private application release without opening or recording its contents. Permission `0600` was observed on the copied file.

A temporary cPanel Cron invoked the new release's Artisan CLI through the governed PHP 8.3 executable. Sanitized result:

`Laravel Framework 12.64.0`

The temporary preflight cron was removed after the one-time check.

Activation changed only the live release pointer from the prior Technical Preview release to `m75-preview-c7159770381e`. The hosting-managed live `.htaccess` was preserved and the previous release was not deleted.

After activation, `https://oneqay.n07.my.id/technical-preview` displayed normally.

## Observability implementation boundary

The published `SafeRequestObservationMiddleware` provides a bounded Preview-only request observation path:

- active only when `oneqay.runtime_class` equals `preview`;
- private log base path under application `storage/logs`;
- daily rotation;
- bounded 14-day retention;
- `info` level;
- logging failure does not alter request semantics;
- public-document-root log paths fail closed.

The request context is explicitly allowlisted to:

- `event`;
- `correlation_id`;
- `method`;
- `route`;
- `status`;
- `duration_ms`;
- `exception_class`.

`CorrelationIdMiddleware` is registered before `SafeRequestObservationMiddleware`, allowing the same governed correlation identifier to be returned to the client and recorded in the observation context.

## Runtime-class verification

A temporary bounded CLI check bootstrapped the deployed application without printing `.env` contents or unrelated configuration.

Observed sanitized result:

`ONEQAY_RUNTIME_CLASS=preview`

The temporary runtime-class cron was removed after verification.

This establishes that the Preview-only observability branch is active for the deployed release.

## Synthetic correlation request

A browser Console request was sent to `/technical-preview` with a synthetic, non-secret correlation identifier and with browser credentials omitted.

Observed sanitized result:

`OBS_VERIFY 200 oneqay-observe-c715-20260816-0253`

This proves that the live request completed with HTTP 200 and that the same correlation identifier was returned through the response header path.

## Private observation-log persistence

The deployed private application directory contained rotated `oneqay-observation-*.log` files under `storage/logs`.

The first targeted lookup assumed the `2026-08-16` daily filename and returned no matching line. That empty lookup is not treated as failure evidence or as proof of absence across rotation.

A follow-up filename-only search across the rotated observation logs found the synthetic correlation identifier in:

`oneqay-observation-2026-08-15.log`

No timezone interpretation is asserted from the daily filename alone.

A bounded `grep -m 1` then copied only the first matching synthetic line into a private proof file. Sanitized observed line:

```text
[2026-08-15 20:00:29] preview.INFO: oneqay.http.request {"event":"http.request","correlation_id":"oneqay-observe-c715-20260816-0253","method":"GET","route":"preview.index","status":200,"duration_ms":48.541,"exception_class":null}
```

The live line demonstrates:

- private observation persistence;
- correlation-ID lookup from response to log;
- named route capture;
- HTTP status capture;
- bounded duration metadata;
- null exception class on the healthy path;
- allowlisted structured context only for the observed synthetic request.

The temporary lookup crons were removed after each bounded check.

## Sensitive-surface distinction

The live synthetic browser request intentionally used `credentials: omit` and did not inject secret-shaped request data. Therefore the live line is evidence of persistence, lookup, and allowlisted shape; it is not by itself a negative test for every sensitive request surface.

The published first-party regression in `apps/web/tests/run.php` complements the live proof by sending synthetic query, body, cookie, `Authorization`, exception-message, and `APP_KEY` markers and asserting that those values and labels are absent from observation logs.

That regression also requires the synthetic request correlation ID and named route to remain searchable. This provides the negative sensitive-surface coverage without placing real credentials or private runtime values in evidence.

## Observability qualification

The combined evidence supports the bounded Technical Preview candidate decision:

`RUNTIME:OBSERVABILITY_LOGGING = VERIFIED`

The basis is limited to:

- deployed Preview runtime class confirmed;
- live Technical Preview remained healthy;
- correlation ID propagated through the live response;
- private rotated observation log persisted;
- the same synthetic correlation ID was searchable in the private log;
- the matching live context used the governed metadata allowlist;
- canonical regression rejects synthetic sensitive request surfaces from the observation log.

This decision does **not** claim centralized log aggregation, external alerting, SLO monitoring, Production log governance, queue-worker observability, background-worker qualification, or Production readiness.

## Deterministic evaluator reconciliation

The sanitized evaluator input added by this evidence PR is:

`docs/evidence/runtime/p1-cpanel-live-runtime-reconciled-20260816-observability.json`

It is shaped for the canonical command:

```text
php tools/runtime-qualification.php --evidence=docs/evidence/runtime/p1-cpanel-live-runtime-reconciled-20260816-observability.json
```

Because seven mandatory controls remain non-VERIFIED, the deterministic outcome remains `BLOCKED`.

Proposed snapshot after publication of this evidence:

- **22 VERIFIED**;
- **7 BLOCKED**;
- outcome: **BLOCKED**;
- lifecycle authority created: **false**.

Only `RUNTIME:OBSERVABILITY_LOGGING` is promoted by this evidence.

## Remaining blockers

1. `ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED`
2. `ENGINE:TENANT_ISOLATION:PARTIAL`
3. `RUNTIME:BACKGROUND_EXECUTION:PARTIAL`
4. `RUNTIME:BACKUP_RESTORE:PARTIAL`
5. `RUNTIME:DEPLOYMENT_RECOVERY:PARTIAL`
6. `RUNTIME:QUEUE_EXECUTION:UNVERIFIED`
7. `RUNTIME:ROLLBACK:NOT_SUPPLIED`

## Security and privacy handling

No raw browser screenshot, raw cPanel screenshot, cPanel session URL, hosting account identifier, cookie value, session identifier, authorization value, password, token, raw `.env`, runtime `APP_KEY`, database identity, customer data, or Production data is committed.

The exact correlation identifier recorded here is synthetic operational metadata created solely for this bounded verification.

This evidence-only Draft PR performs no deployment, database operation, migration, permanent schema change, queue execution, restore, rollback, Release, M7.6/M7.7, Phase 0 Exit, or Production action.

Attribution: **Lab | zefry**
