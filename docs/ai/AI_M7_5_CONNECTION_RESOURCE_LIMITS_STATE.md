# AI M7.5 Connection + Resource Limits State

Attribution: **Lab | zefry**

## Purpose

This additive state overlay records the bounded read-only M7.5 evidence reconciliation for database connection-limit visibility and runtime resource-limit visibility on the non-Production Technical Preview hosting target.

It does not replace historical M7.5 evidence and does not alter the secure-retirement state of the historical relational qualification probe.

## Governed baseline

Published `main` before this reconciled PR state:

`62ff17969a13dfc74ae0b0d790a354559bccc653`

Published tree:

`0d32336f37fdadd50ade8a49bdedcf3567041572`

Current canonical M7.5 snapshot after published PR #116:

- **16 VERIFIED**;
- **13 BLOCKED**;
- `RUNTIME:OUTBOUND_DNS_HTTPS`: **VERIFIED**;
- outcome: **BLOCKED**;
- lifecycle authority created: **false**.

The connection/resource observations themselves were captured earlier against `c25760a832d265ac30e8b0bbecdb59f44837bcc3`; that observation provenance remains historical and unchanged.

## New bounded evidence

Read-only database server evidence established:

- MariaDB server connection ceiling: `1024`;
- global `max_user_connections` reported as `0`;
- `extra_max_connections` reported as `1`;
- historical maximum concurrent connections observed: `481`.

No account-specific override is inferred, and no database/database user was created for this observation.

Read-only cPanel Resource Usage evidence established a visible Technical Preview hosting-account resource envelope with:

- CPU / SPEED limit: `100%`;
- physical memory limit: `1 GiB`;
- NPROC limit: `30`;
- Entry Processes limit: `15`;
- inode limit: `250000`;
- historical hourly CPU average around `1–2%`;
- historical hourly physical-memory average around `17.91–23.57 MiB`;
- historical Entry Processes average `0`;
- historical NPROC average around `1–2`;
- no recorded resource faults in the observed period.

No load, saturation, Production capacity, or p95 performance claim is created by this evidence.

## Control reconciliation

Relative to current canonical `main`, only these controls are promoted by this PR:

- `ENGINE:CONNECTION_LIMIT_VISIBILITY`: **VERIFIED**;
- `RUNTIME:RESOURCE_LIMITS`: **VERIFIED**.

Published `RUNTIME:OUTBOUND_DNS_HTTPS = VERIFIED` from PR #116 is preserved as baseline and is not re-promoted by this PR.

Reconciled deterministic snapshot:

- **18 VERIFIED**;
- **11 BLOCKED**;
- outcome: **BLOCKED**;
- lifecycle authority created: **false**.

## Current lifecycle

- M7.5: **BLOCKED / INCOMPLETE**;
- historical relational probe: **QUALIFIED / VERIFIED**;
- current relational probe lifecycle: **RETIRED / FAIL-CLOSED**;
- M7.6: **BLOCKED / NOT AUTHORIZED**;
- M7.7: **BLOCKED / NOT AUTHORIZED**;
- Phase 0 Exit: **NOT APPROVED**;
- Release: **NOT AUTHORIZED**;
- Production: **NOT AUTHORIZED**;
- Production readiness: **NO-GO**.

This reconciliation creates no standing authority for database creation, database users, migration, permanent schema, load testing, recovery rehearsal, rollback, restore, Release, or Production.

## Evidence references

- `docs/evidence/runtime/p1-cpanel-connection-resource-limits-20260815.json`
- `docs/evidence/runtime/p1-cpanel-live-runtime-reconciled-20260815-v2.json`
- `docs/evidence/runtime/p1-cpanel-live-runtime-reconciled-20260815-v2.report.json`
- `docs/handbook/M7_5_P1_CONNECTION_RESOURCE_LIMITS_EVIDENCE_20260815.md`
- published outbound baseline: `docs/evidence/runtime/p1-cpanel-live-runtime-reconciled-20260815-outbound.report.json`

Attribution: **Lab | zefry**
