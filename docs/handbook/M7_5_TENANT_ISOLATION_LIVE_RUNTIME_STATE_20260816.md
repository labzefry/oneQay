# M7.5 Tenant Isolation Live Runtime State — 2026-08-16

Attribution: **Lab | zefry**

## Purpose

This additive state overlay records sanitized bounded non-Production Technical Preview evidence for database-backed two-tenant negative isolation on the published oneQay Preview release.

It creates no permanent schema, migration, restore, cleanup, M7.6, M7.7, Phase 0 Exit, Release, or Production authority.

## Governed baseline

Evidence branch base and published source:

`dab951519e6747ac43653abfdd9fc24762f125b0`

Published source tree:

`3f388b527553012e0f43343db36232a76887165f`

Technical Preview release:

`m75-preview-dab951519e67`

Canonical evaluator before this reconciliation:

- **26 VERIFIED**;
- **3 BLOCKED**;
- outcome: **BLOCKED**;
- `lifecycle_authority_created=false`.

## Live qualification result

The bounded protected qualification path was exercised after server-verified synthetic Demo Alpha context establishment.

Observed sanitized result:

- `status = qualified`;
- `scope = technical-preview-relational-probe`;
- `production_ready = false`;
- `persistent_schema_created = false`;
- engine profile: MariaDB `11.4.8`;
- configuration: verified;
- tenant context: verified;
- PDO MySQL driver: verified;
- connection: verified;
- engine family: verified;
- temporary table: verified;
- transaction rollback: verified;
- tenant-owned insert: verified;
- tenant-scoped query: verified;
- cross-tenant read isolation: verified;
- same business-ID tenant collision isolation: verified;
- tenant enumeration isolation: verified;
- cross-tenant update isolation: verified;
- cross-tenant delete isolation: verified.

A post-qualification `/health/ready` request returned `status = ready` and `service = oneqay-web`.

## Qualification database boundary

The dedicated non-Production qualification database and dedicated qualification user were recreated under separate Product Owner authority because the earlier qualification boundary had been securely retired.

The qualification user was granted only:

- `SELECT`;
- `INSERT`;
- `UPDATE`;
- `DELETE`;
- `CREATE TEMPORARY TABLES`.

`ALL PRIVILEGES`, permanent `CREATE`, `ALTER`, `DROP`, `INDEX`, routine/view administration, trigger/event administration, and comparable broad privileges were not granted.

The probe used synthetic data and connection-scoped temporary relational state only. No permanent table, migration, business persistence, customer data, BPJS data, or Production data was introduced.

## Sanitization

Raw browser/cPanel screenshots are not committed. Database identity, database username, password, cPanel account prefix/path, raw `.env`, APP_KEY, cookies/session values, correlation IDs, tokens, customer data, BPJS data, and Production data are intentionally excluded.

## Candidate control reconciliation

The live evidence supports:

`ENGINE:TENANT_ISOLATION: PARTIAL -> VERIFIED`

This verification is scoped to the bounded oneQay Technical Preview relational qualification chain:

`server-verified identity -> verified tenant context -> tenant-bound relational operations -> MariaDB temporary qualification data`.

It does not claim a permanent business schema, Production persistence, cross-engine runtime equivalence, or restore capability.

## Proposed evaluator

After governed publication of this evidence:

- **27 VERIFIED**;
- **2 BLOCKED**;
- outcome: **BLOCKED**;
- `lifecycle_authority_created=false`.

Remaining blockers:

- `ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED`;
- `RUNTIME:BACKUP_RESTORE:PARTIAL`.

Production readiness remains **NO-GO**.

Attribution: **Lab | zefry**
