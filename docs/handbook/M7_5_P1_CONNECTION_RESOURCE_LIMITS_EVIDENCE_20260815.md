# M7.5 P1 Connection + Resource Limits Evidence — 2026-08-15

Attribution: **Lab | zefry**

## Purpose

This additive evidence record documents the Product Owner-authorized, read-only M7.5 qualification of database connection-limit visibility and cPanel runtime resource-limit visibility on the non-Production Technical Preview hosting target.

The observations were performed through phpMyAdmin server variables/status and cPanel Resource Usage. No database, database user, SQL mutation, migration, permanent schema, runtime configuration, Production surface, or business persistence was created or modified.

Raw screenshots and the raw cPanel capture are intentionally not committed because they expose hosting-account/database identifiers and other control-panel context that is not required for repository evidence.

## Governed baseline

Published `main` at the start of this reconciliation:

`c25760a832d265ac30e8b0bbecdb59f44837bcc3`

Published tree:

`c96f78fd24087ffaad6e6f7ba46d82514e434447`

Active non-Production Preview release:

`m75-preview-0edea8cdcc0c`

Canonical evaluator before this reconciliation:

- verified mandatory controls: **15**;
- blocking mandatory controls: **14**;
- outcome: **BLOCKED**;
- lifecycle authority created: **false**.

## Database connection-limit visibility

**Status: VERIFIED**

Read-only phpMyAdmin server-variable evidence exposed the MariaDB connection ceiling and related server values:

- `max_connections = 1024`;
- `max_user_connections = 0`;
- `extra_max_connections = 1`.

Read-only phpMyAdmin server-status evidence also exposed historical connection statistics, including:

- maximum observed concurrent connections: **481**;
- server connection statistics were available for the running MariaDB service.

This is sufficient for the M7.5 `ENGINE:CONNECTION_LIMIT_VISIBILITY` control because the selected MariaDB profile has a visible server connection ceiling and a visible observed concurrency high-water mark.

The evidence does **not** assert that every database account lacks an account-specific connection override. The earlier temporary qualification database account had already been securely retired, and no new database user was created merely to obtain this evidence.

No SQL mutation was executed.

## Runtime resource limits

**Status: VERIFIED for the Technical Preview hosting-account resource envelope**

Read-only cPanel Resource Usage evidence exposed concrete hosting-account limits that govern the account containing the Technical Preview runtime:

- SPEED / CPU limit: **100%**;
- physical memory limit: **1 GiB**;
- NPROC limit: **30**;
- Entry Processes limit: **15**;
- inode limit: **250,000**.

The current-usage view reported low utilization with zero resource faults during the observation, including:

- NPROC usage: **1 / 30**;
- Entry Processes usage: **0 / 15**;
- physical memory observed around **17.16–17.63 MiB**;
- inode usage observed around **40,103–40,129**;
- resource fault count: **0**.

The historical hourly view for the observed day additionally showed:

- SPEED / CPU average around **1–2%**;
- physical memory average around **17.91–23.57 MiB**;
- Entry Processes average: **0**;
- NPROC average around **1–2**;
- recorded fault columns remained **0**.

The Resource Usage dashboard also reported no site resource issues in the preceding 24-hour window, while the detailed graphs showed no recorded faults in the displayed period.

Together these observations establish both the visible resource ceilings and bounded historical utilization/fault visibility required for `RUNTIME:RESOURCE_LIMITS`.

## I/O and IOPS interpretation boundary

The cPanel resource surface displayed platform values of `0` for the I/O and IOPS limit fields during the observation.

This reconciliation records those values only as platform-reported values. It does **not** reinterpret `0` as unlimited, unavailable, or a guaranteed capacity value.

No control promotion depends on such an interpretation.

## No load or saturation test

No synthetic load test, deliberate saturation test, stress test, or fault-induction exercise was performed.

`RUNTIME:RESOURCE_LIMITS = VERIFIED` here means that the relevant hosting resource ceilings and bounded historical usage/fault telemetry are directly visible for the Technical Preview hosting envelope. It does not claim a Production capacity benchmark, p95 load qualification, or saturation/recovery guarantee.

## Control reconciliation

Only these two mandatory controls are promoted by this evidence:

- `ENGINE:CONNECTION_LIMIT_VISIBILITY`: `UNVERIFIED -> VERIFIED`;
- `RUNTIME:RESOURCE_LIMITS`: `PARTIAL -> VERIFIED`.

No other control is promoted by inference.

The resulting evaluator-shaped snapshot is therefore:

- verified mandatory controls: **17**;
- blocking mandatory controls: **12**;
- outcome: **BLOCKED**;
- lifecycle authority created: **false**.

The remaining blockers are:

- `ENGINE:PORTABILITY_CONTRACT:UNVERIFIED`;
- `ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED`;
- `ENGINE:TENANT_ISOLATION:PARTIAL`;
- `RUNTIME:BACKGROUND_EXECUTION:PARTIAL`;
- `RUNTIME:BACKUP_RESTORE:PARTIAL`;
- `RUNTIME:DEPLOYMENT_RECOVERY:PARTIAL`;
- `RUNTIME:ENVIRONMENT_SECRETS:PARTIAL`;
- `RUNTIME:OBSERVABILITY_LOGGING:PARTIAL`;
- `RUNTIME:OUTBOUND_DNS_HTTPS:PARTIAL`;
- `RUNTIME:QUEUE_EXECUTION:UNVERIFIED`;
- `RUNTIME:ROLLBACK:NOT_SUPPLIED`;
- `RUNTIME:SECURITY_BOUNDARY:PARTIAL`.

## Machine-readable evidence

Sanitized bounded observation:

`docs/evidence/runtime/p1-cpanel-connection-resource-limits-20260815.json`

Reconciled complete runtime package:

`docs/evidence/runtime/p1-cpanel-live-runtime-reconciled-20260815-v2.json`

Evaluator-shaped report:

`docs/evidence/runtime/p1-cpanel-live-runtime-reconciled-20260815-v2.report.json`

## Security and privacy

The repository evidence does not contain:

- raw screenshots or the raw cPanel PDF capture;
- cPanel account identifiers;
- database names or database usernames;
- passwords, tokens, private keys, or raw `.env` content;
- runtime `APP_KEY`;
- customer, BPJS, personal, or Production data.

## Lifecycle

This evidence does not complete M7.5 and creates no new lifecycle authority.

- M7.5: **BLOCKED / INCOMPLETE — 17 VERIFIED / 12 BLOCKED**;
- historical relational probe: **QUALIFIED / VERIFIED**;
- current relational probe lifecycle: **RETIRED / FAIL-CLOSED**;
- M7.6: **BLOCKED / NOT AUTHORIZED**;
- M7.7: **BLOCKED / NOT AUTHORIZED**;
- Phase 0 Exit: **NOT APPROVED**;
- Release: **NOT AUTHORIZED**;
- Production: **NOT AUTHORIZED**;
- Production readiness: **NO-GO**.

Attribution: **Lab | zefry**
