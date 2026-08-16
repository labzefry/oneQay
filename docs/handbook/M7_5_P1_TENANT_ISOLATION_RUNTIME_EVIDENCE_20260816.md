# M7.5 P1 Tenant Isolation Runtime Evidence — 2026-08-16

Attribution: **Lab | zefry**

## Purpose

This record reconciles sanitized bounded non-Production Technical Preview evidence for database-backed two-tenant negative isolation on the published oneQay release `m75-preview-dab951519e67`.

It records only qualification facts required for M7.5 runtime evidence. It intentionally excludes raw screenshots, database names, database usernames, passwords, raw `.env` content, APP_KEY, cPanel account identifiers or paths, cookies/session values, correlation IDs, tokens, customer data, BPJS data, Production data, and other credential material.

This record does not authorize permanent schema, migration execution, durable business persistence, restore, cleanup, M7.6, M7.7, Phase 0 Exit, Release, Production, or `oneqay.com`.

## Published source and runtime identity

Published source used by the active qualification release:

`dab951519e6747ac43653abfdd9fc24762f125b0`

Published source tree:

`3f388b527553012e0f43343db36232a76887165f`

Active bounded Technical Preview release:

`m75-preview-dab951519e67`

Technical Preview hostname:

`oneqay.n07.my.id`

The release was built by the governed M7.5 Technical Preview artifact workflow from exact `main` and deployed into the existing private versioned release boundary. The hosting-managed live `.htaccess` was not overwritten.

## Runtime preparation and fail-closed behavior

The newly activated release initially failed readiness because the versioned artifact correctly did not contain runtime `.env` material. Provisioning the existing private Preview `.env` into the new private release restored `/health/ready` to `status = ready`.

The database qualification feature remained fail-closed until separately enabled. After enablement it first returned `configuration = blocked`, which was expected because the earlier dedicated relational qualification database/user had previously been securely retired and its qualification identity/username/password had been cleared from the private runtime environment.

Under fresh Product Owner authority, a new dedicated empty non-Production qualification database and dedicated qualification user were created. No permanent table was created through the control panel.

## Least-privilege qualification boundary

The dedicated qualification user was attached to the dedicated qualification database with only:

- `SELECT`;
- `INSERT`;
- `UPDATE`;
- `DELETE`;
- `CREATE TEMPORARY TABLES`.

The following remained ungranted:

- `ALL PRIVILEGES`;
- permanent `CREATE`;
- `ALTER`;
- `DROP`;
- `INDEX`;
- `LOCK TABLES`;
- trigger/event administration;
- routine/view administration;
- comparable broad database administration privileges.

The qualification configuration was provided only in the private runtime `.env`; credential values are intentionally absent from repository evidence.

## Verified application context chain

The live browser path established the synthetic Technical Preview context through:

1. Demo Alpha synthetic identity;
2. server-verifiable membership for `tenant-alpha`;
3. synthetic organizational/outlet/device context;
4. successful bounded POS surface access under `tenant-alpha`;
5. invocation of the protected database qualification endpoint in that same Preview session.

PR #126 changed the qualification execution path so the database probe executes while the server-verified `VerifiedTenantContext` is active. Tenant scope for application operations is therefore derived from verified context rather than a client-supplied tenant parameter.

## Sanitized live qualification result

The protected endpoint returned:

- `status = qualified`;
- `scope = technical-preview-relational-probe`;
- `production_ready = false`;
- `persistent_schema_created = false`;
- engine profile `mariadb`;
- engine version `11.4.8`.

All bounded qualification checks returned `verified`:

- configuration;
- verified tenant context;
- PDO MySQL driver;
- application connection;
- MariaDB engine family;
- connection-scoped temporary table;
- transaction rollback;
- tenant-owned insert;
- tenant-scoped query;
- cross-tenant read isolation;
- same business-ID tenant collision isolation;
- tenant-only enumeration;
- cross-tenant update isolation;
- cross-tenant delete isolation.

No host, database identity, username, password, SQL exception, foreign payload, or credential value was returned in the sanitized qualification result.

## Negative tenant-isolation interpretation

The observed result closes the source-level gap that existed after the earlier PR #111 probe.

The earlier probe had demonstrated a tenant predicate against temporary data, but the tenant value used by the relational probe was not part of one continuous verified application-context-to-repository chain.

The current bounded qualification instead proves the following chain on the live MariaDB Preview profile:

`server-verified identity -> server-verified tenant membership/context -> tenant-bound relational operation -> MariaDB temporary qualification data`.

Within that bounded chain:

- same-tenant operations succeed;
- foreign-tenant reads return no accessible foreign record;
- foreign-tenant updates affect no foreign row;
- foreign-tenant deletes affect no foreign row;
- enumeration remains current-tenant scoped;
- the same business identifier can exist in different tenants without collapsing tenant scope;
- qualification write ownership derives from verified tenant context rather than caller-provided ownership;
- temporary data is bounded by transaction/connection lifecycle rather than durable business persistence.

## Persistence and schema boundary

The qualification used a connection-scoped temporary table only.

Observed and governed facts:

- `persistent_schema_created = false`;
- no migration executed;
- no permanent business table created;
- no business repository schema established;
- no Production/customer/BPJS data used;
- no Production authority created.

This evidence is sufficient for the bounded M7.5 tenant-isolation control because the control under test is the verified context-to-relational isolation boundary. It must not be reinterpreted as authorization or proof of a final durable POS schema.

## Post-qualification health

After the qualified relational probe completed, `/health/ready` returned:

- `status = ready`;
- `service = oneqay-web`.

This confirms the bounded qualification did not leave the Technical Preview runtime unhealthy.

## Control decision

**`ENGINE:TENANT_ISOLATION = VERIFIED` for the bounded non-Production Technical Preview relational qualification scope.**

The verified scope is specifically:

- oneQay published source `dab951519e6747ac43653abfdd9fc24762f125b0`;
- release `m75-preview-dab951519e67`;
- MariaDB `11.4.8`;
- synthetic two-tenant qualification fixtures;
- server-verified tenant context;
- temporary relational qualification state;
- negative read/update/delete/enumeration and identity-collision checks.

It does not assert:

- Production tenant isolation;
- permanent business-schema readiness;
- MySQL/PostgreSQL runtime equivalence;
- DBME qualification;
- successful backup restore;
- M7.6/M7.7 readiness;
- Release or Production authority.

## Evaluator reconciliation

Before this evidence:

- **26 VERIFIED**;
- **3 BLOCKED**;
- `ENGINE:TENANT_ISOLATION:PARTIAL`.

After this evidence is governed and published:

- **27 VERIFIED**;
- **2 BLOCKED**;
- outcome: **BLOCKED**;
- `lifecycle_authority_created=false`.

Remaining blockers:

- `ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED`;
- `RUNTIME:BACKUP_RESTORE:PARTIAL`.

Production readiness remains **NO-GO**.

## Cleanup boundary

The dedicated qualification database/user and enabled qualification switch remain operational artifacts until separately authorized cleanup. This evidence publication does not authorize their deletion, detachment, credential clearing, or switch disablement.

Attribution: **Lab | zefry**
