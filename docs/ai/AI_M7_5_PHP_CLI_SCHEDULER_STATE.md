# AI M7.5 PHP CLI + Scheduler State

Attribution: **Lab | zefry**

## Purpose

This additive state overlay records the bounded non-Production PHP CLI and Scheduler Cron qualification observed on the active oneQay Technical Preview after the relational qualification probe had already been retired fail-closed.

It does not replace historical M7.5 evidence. It only reconciles the two runtime controls directly supported by the new cPanel Cron observations.

## Governed baseline

Repository `main` at the start of this reconciliation:

`3e2a310144fd73504b662cabae6a32a0073c592d`

Tree:

`70de762f254950abdaa6ee519ecd4d88869337eb`

Active non-Production Preview release:

`m75-preview-0edea8cdcc0c`

Previous deterministic M7.5 snapshot:

- verified mandatory controls: **13**;
- blocking mandatory controls: **16**;
- outcome: **BLOCKED**.

## New bounded live evidence

The Product Owner explicitly authorized a non-Production M7.5 PHP CLI + Scheduler Cron qualification through cPanel, one manual step at a time, without database, migration, or Production authority.

Sanitized observations:

- Technical Preview PHP assignment: PHP 8.3 / `alt-php83`;
- generic PHP CLI execution succeeded;
- observed PHP CLI version: `8.3.26`;
- observed SAPI: `cli`;
- active oneQay Artisan boot succeeded;
- observed framework version: `Laravel Framework 12.64.0`;
- exact active-release `routes/console.php` defines no scheduled or business commands;
- active oneQay `artisan schedule:run` executed successfully through cPanel Cron;
- observed scheduler result: `No scheduled commands are ready to run.`;
- all temporary qualification Cron entries were removed after observation.

No screenshot, account identifier, home-directory identity, credential, password, token, raw `.env`, database identity, BPJS data, customer data, or Production data is committed by this reconciliation.

## Control reconciliation

The new evidence directly supports:

- `RUNTIME:PHP_CLI`: **VERIFIED**;
- `RUNTIME:SCHEDULER_CRON`: **VERIFIED**.

The following remains deliberately unchanged:

- `RUNTIME:BACKGROUND_EXECUTION`: **PARTIAL**.

No other runtime or engine-profile control is promoted by inference.

Therefore the reconciled deterministic snapshot is:

- verified mandatory controls: **15**;
- blocking mandatory controls: **14**;
- outcome: **BLOCKED**;
- lifecycle authority created: **false**.

## Current lifecycle

- M7.5: **BLOCKED / INCOMPLETE**;
- historical relational probe: **QUALIFIED / VERIFIED**;
- current relational probe runtime lifecycle: **RETIRED / FAIL-CLOSED**;
- M7.6: **BLOCKED / NOT AUTHORIZED**;
- M7.7: **BLOCKED / NOT AUTHORIZED**;
- Phase 0 Exit: **NOT APPROVED**;
- Release: **NOT AUTHORIZED**;
- Production: **NOT AUTHORIZED**;
- Production readiness: **NO-GO**.

This evidence reconciliation creates no standing source, deployment, database, migration, recovery, release, or Production authority.

## Evidence references

- `docs/evidence/runtime/p1-cpanel-php-cli-scheduler-20260815.json`
- `docs/evidence/runtime/p1-cpanel-live-runtime-reconciled-20260815.json`
- `docs/evidence/runtime/p1-cpanel-live-runtime-reconciled-20260815.report.json`
- `docs/handbook/M7_5_P1_PHP_CLI_SCHEDULER_EVIDENCE_20260815.md`

Attribution: **Lab | zefry**
