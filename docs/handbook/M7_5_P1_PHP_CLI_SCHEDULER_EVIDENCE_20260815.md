# M7.5 P1 PHP CLI + Scheduler Cron Evidence — 2026-08-15

Attribution: **Lab | zefry**

## Purpose

This additive record documents the Product Owner-authorized, bounded non-Production M7.5 PHP CLI and Scheduler Cron qualification performed through the cPanel UI for the active oneQay Technical Preview release.

The evidence is intentionally sanitized. It does not contain cPanel account identifiers, home-directory account names, credentials, passwords, raw `.env` content, tokens, private keys, screenshots, customer data, BPJS data, Production data, or other secret material.

This work does not authorize database creation, migration execution, permanent schema, durable business persistence, queue workers, M7.6, M7.7, Phase 0 Exit, Release, or Production.

## Governed runtime identity

Published source used by the active Technical Preview release:

`0edea8cdcc0cb7f16c8e8758aa626e79b4096cf8`

Active release ID:

`m75-preview-0edea8cdcc0c`

Repository `main` at the start of this evidence reconciliation:

`3e2a310144fd73504b662cabae6a32a0073c592d`

with tree:

`70de762f254950abdaa6ee519ecd4d88869337eb`

Technical Preview hostname:

`oneqay.n07.my.id`

## PHP runtime alignment

The cPanel MultiPHP Manager showed the Technical Preview hostname assigned to:

`PHP 8.3 (alt-php83)`

The bounded CLI probe therefore used the hosting provider's Alt-PHP 8.3 CLI binary rather than changing the domain PHP version.

No PHP version configuration was mutated by this qualification.

## Generic PHP CLI execution

**Status: VERIFIED**

A temporary cPanel Cron entry executed a harmless PHP CLI expression and wrote a sanitized result into the private `oneqay-preview` area outside the public web surface.

Observed result:

- PHP version: `8.3.26`;
- SAPI: `cli`;
- command execution through cPanel Cron: successful.

The temporary Cron entry was removed after the observation.

This directly verifies that the Technical Preview hosting account can execute the selected PHP 8.3 runtime through CLI under cPanel Cron.

## oneQay Artisan CLI boot

**Status: VERIFIED**

A second temporary cPanel Cron entry invoked the active release's `artisan --version` command through the same PHP 8.3 CLI runtime.

Observed result:

`Laravel Framework 12.64.0`

This proves the active oneQay Laravel application can boot successfully through CLI on the target, rather than merely proving that generic PHP CLI exists.

The temporary Artisan qualification Cron entry was removed after the observation.

No database, migration, queue worker, schema mutation, or business command was exercised.

## Scheduler source safety check

Before `artisan schedule:run` was exercised, the exact active release source was inspected read-only.

`apps/web/routes/console.php` contains only:

`// M7.1 intentionally defines no scheduled or business commands.`

Therefore the active release defines no scheduled or business commands through the current console route file.

This read-only source check was used to prevent accidental execution of business or destructive scheduled work during the qualification.

## Laravel Scheduler through cPanel Cron

**Status: VERIFIED for the current bounded M7.5 scheduler control**

A temporary cPanel Cron entry invoked the active release's:

`artisan schedule:run`

through PHP 8.3 CLI.

Observed result:

`INFO  No scheduled commands are ready to run.`

The result is consistent with the exact active-release source, which currently defines no scheduled or business commands.

This is direct target evidence that:

- cPanel Cron executes the oneQay Laravel CLI entry point;
- Laravel scheduler boot succeeds on the target;
- the selected PHP CLI runtime is compatible with the active application release;
- a scheduled invocation can reach Laravel's scheduler end-to-end;
- no scheduled business action was executed during this qualification.

The temporary Scheduler qualification Cron entry was removed after the observation.

## Background execution distinction

`RUNTIME:BACKGROUND_EXECUTION` remains **PARTIAL**.

The Cron evidence materially strengthens the bounded background-execution evidence because a non-interactive scheduled process successfully launched PHP and Laravel.

It is deliberately not promoted to `VERIFIED` because this test does not establish:

- persistent worker lifecycle;
- restart/recovery behavior;
- concurrency semantics;
- long-running process limits;
- queue worker behavior;
- broader asynchronous background-execution requirements.

## Control reconciliation

The new live evidence supports:

- `RUNTIME:PHP_CLI`: **VERIFIED**;
- `RUNTIME:SCHEDULER_CRON`: **VERIFIED**;
- `RUNTIME:BACKGROUND_EXECUTION`: **PARTIAL**.

The previous governed M7.5 snapshot was:

- **13 VERIFIED**;
- **16 BLOCKED**;
- outcome: **BLOCKED**.

With only `PHP_CLI` and `SCHEDULER_CRON` promoted from `PARTIAL` to `VERIFIED`, the projected deterministic snapshot becomes:

- **15 VERIFIED**;
- **14 BLOCKED**;
- outcome: **BLOCKED**;
- lifecycle authority created: **false**.

No other mandatory control is promoted by inference.

## Remaining blocker boundary

This evidence does not change the status of, among others:

- `RUNTIME:BACKGROUND_EXECUTION`;
- `RUNTIME:QUEUE_EXECUTION`;
- `RUNTIME:BACKUP_RESTORE`;
- `RUNTIME:DEPLOYMENT_RECOVERY`;
- `RUNTIME:ENVIRONMENT_SECRETS`;
- `RUNTIME:OBSERVABILITY_LOGGING`;
- `RUNTIME:OUTBOUND_DNS_HTTPS`;
- `RUNTIME:RESOURCE_LIMITS`;
- `RUNTIME:ROLLBACK`;
- `RUNTIME:SECURITY_BOUNDARY`;
- `ENGINE:CONNECTION_LIMIT_VISIBILITY`;
- `ENGINE:PORTABILITY_CONTRACT`;
- `ENGINE:RESTORE_VERIFIED`;
- `ENGINE:TENANT_ISOLATION`.

The complete M7.5 evaluator therefore remains fail-closed `BLOCKED / INCOMPLETE`.

## Machine-readable evidence

The sanitized machine-readable evidence record is:

`docs/evidence/runtime/p1-cpanel-php-cli-scheduler-20260815.json`

No runtime screenshot or raw qualification output file is committed. Only the sanitized observed facts required for the control reconciliation are recorded.

## Lifecycle preservation

Current lifecycle after this evidence remains:

- M7.5: **BLOCKED / INCOMPLETE**;
- historical relational probe: **QUALIFIED / VERIFIED**;
- current relational probe runtime lifecycle: **RETIRED / FAIL-CLOSED**;
- M7.6: **BLOCKED / NOT AUTHORIZED**;
- M7.7: **BLOCKED / NOT AUTHORIZED**;
- Phase 0 Exit: **NOT APPROVED**;
- Release: **NOT AUTHORIZED**;
- Production: **NOT AUTHORIZED**;
- Production readiness: **NO-GO**.

No lifecycle authority is created by successful PHP CLI or Scheduler Cron qualification.

Attribution: **Lab | zefry**
