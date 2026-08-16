# AI M7.5 Observability Live Runtime State

Attribution: **Lab | zefry**

## Purpose

This additive state overlay records bounded non-Production live runtime verification of the observability and safe structured logging capability published by PR #120 and deployed to the Technical Preview under separate Product Owner authority.

This evidence-only state creates no deployment, M7.6, M7.7, Release, Phase 0 Exit, restore, rollback, database, migration, schema, queue, or Production authority.

## Governed baseline

Published `main` used for the deployment and this evidence branch:

`c7159770381e5ade7d88a00d57bd4346c9e73637`

Published tree:

`ec506371f6b5454fc4987f8a4c35f41641356d9b`

Published observability implementation source head:

`543d8f60559b7769ad597bdc045a4fb7a99505f9`

Canonical M7.5 before this reconciliation:

- **21 VERIFIED**;
- **8 BLOCKED**;
- outcome: **BLOCKED**;
- lifecycle authority created: **false**.

## Deployed Technical Preview release

The bounded non-Production release under verification is:

`m75-preview-c7159770381e`

The private runtime `.env` was copied from the previous active Preview release without opening or recording its contents. Permission `0600` was observed on the copied file.

A release-specific Artisan preflight returned:

`Laravel Framework 12.64.0`

The temporary preflight cron was removed after execution.

Activation changed only the live release pointer to `m75-preview-c7159770381e`. The hosting-managed live `.htaccess` was preserved and the previous release was retained.

The Technical Preview displayed normally after activation.

## Runtime-class proof

A bounded CLI bootstrap check returned:

`ONEQAY_RUNTIME_CLASS=preview`

The temporary runtime-class cron was removed.

This is material because `SafeRequestObservationMiddleware` records only when the governed runtime class is `preview`.

## Correlation and persistence proof

A synthetic browser request to `/technical-preview` used the non-secret correlation identifier:

`oneqay-observe-c715-20260816-0253`

Observed browser result:

`OBS_VERIFY 200 oneqay-observe-c715-20260816-0253`

The private application `storage/logs` directory contained rotated oneQay observation logs.

A filename-only search across the rotated files located the same synthetic correlation identifier in:

`oneqay-observation-2026-08-15.log`

The earlier lookup against the assumed `2026-08-16` file was empty and is not treated as proof of absence. No timezone conclusion is inferred from the rotation filename alone.

A bounded single-line extraction confirmed the live request context:

- event: `http.request`;
- correlation ID: `oneqay-observe-c715-20260816-0253`;
- method: `GET`;
- route: `preview.index`;
- status: `200`;
- duration: `48.541` ms;
- exception class: `null`.

The live matching context contained only the governed allowlisted observation fields.

All temporary lookup crons were removed after verification.

## Sensitive logging boundary

The live browser request deliberately omitted browser credentials and did not inject sensitive markers. Its purpose was live persistence and correlation lookup.

The published regression in `apps/web/tests/run.php` separately proves that synthetic query, body, cookie, `Authorization`, exception-message, and `APP_KEY` markers are not copied into the observation log while correlation ID and named route remain searchable.

No real secret, cookie value, session ID, password, token, raw `.env`, database identity, or hosting account identifier is used as evidence.

## Observability reconciliation

The combined published-source regression and live runtime proof supports the bounded candidate decision:

`RUNTIME:OBSERVABILITY_LOGGING = VERIFIED`

This is a Technical Preview qualification claim only. It does not claim centralized log aggregation, external alerting, Production log retention governance, SLO monitoring, queue-worker observability, background-worker qualification, or Production readiness.

## Proposed evaluator

This reconciliation promotes only:

`RUNTIME:OBSERVABILITY_LOGGING: PARTIAL -> VERIFIED`

Proposed deterministic snapshot after governed publication:

- **22 VERIFIED**;
- **7 BLOCKED**;
- outcome: **BLOCKED**;
- lifecycle authority created: **false**.

## Remaining blockers

- `ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED`;
- `ENGINE:TENANT_ISOLATION:PARTIAL`;
- `RUNTIME:BACKGROUND_EXECUTION:PARTIAL`;
- `RUNTIME:BACKUP_RESTORE:PARTIAL`;
- `RUNTIME:DEPLOYMENT_RECOVERY:PARTIAL`;
- `RUNTIME:QUEUE_EXECUTION:UNVERIFIED`;
- `RUNTIME:ROLLBACK:NOT_SUPPLIED`.

## Safety boundary

No raw browser/cPanel capture, cPanel session URL, hosting account identifier, cookie value, session identifier, authorization value, password, token, raw `.env`, runtime `APP_KEY`, database identity, customer data, or Production data is committed.

The synthetic correlation identifier is bounded non-secret operational metadata.

This Draft PR is evidence-only and does not authorize Ready, merge, deployment, cleanup, restore, rollback, database/migration/schema work, queue execution, Release, M7.6, M7.7, Phase 0 Exit, or Production.

Attribution: **Lab | zefry**
