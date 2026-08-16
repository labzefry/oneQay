# M7.5 P1 Tenant Isolation Qualification Cleanup — 2026-08-16

Attribution: **Lab | zefry**

## Purpose

This record documents the secure retirement of the bounded non-Production Technical Preview tenant-isolation qualification capability after the live qualification evidence was published through PR #127.

It records only sanitized operational closure facts. It intentionally excludes database names, database usernames, passwords, raw `.env` content, APP_KEY, cPanel account identifiers or paths, cookies/session values, correlation IDs, screenshots, tokens, private keys, customer data, BPJS data, Production data, and other credential material.

This closure does not authorize restore, M7.6, M7.7, Phase 0 Exit, Release, Production, permanent schema, migration execution, or durable business persistence.

## Governed baseline

PR #127 published the tenant-isolation evidence to canonical `main`:

`390a0a446f392ca364aae81684edbc90d69a8249`

Published tree:

`afe2d1b602a9df066225332f1915026aab1a1b25`

Active non-Production Technical Preview application release during cleanup:

`m75-preview-dab951519e67`

The published evaluator state before cleanup was:

- **27 VERIFIED**;
- **2 BLOCKED**;
- outcome: **BLOCKED**;
- `lifecycle_authority_created=false`.

The cleanup is lifecycle-neutral and does not change those counts.

## Historical qualification preserved

Before retirement, the protected qualification endpoint had produced a governed bounded result with:

- `status = qualified`;
- engine family MariaDB;
- engine version `11.4.8`;
- server-verified tenant context;
- temporary-table-only relational state;
- transaction rollback verified;
- tenant-owned insert verified;
- tenant-scoped query verified;
- cross-tenant read isolation verified;
- cross-tenant update isolation verified;
- cross-tenant delete isolation verified;
- tenant-only enumeration verified;
- tenant identity-collision isolation verified;
- `persistent_schema_created = false`;
- `production_ready = false`.

That published observation remains valid after cleanup. Retirement of the temporary capability does not downgrade `ENGINE:TENANT_ISOLATION = VERIFIED` for the governed bounded Technical Preview qualification scope.

## Cleanup sequence and observations

Under fresh explicit Product Owner authority, the following bounded cleanup sequence was manually observed:

1. **Qualification switch disabled.** The private runtime configuration was changed so the qualification capability was no longer enabled.
2. **Fail-closed verified.** The protected database-qualification endpoint returned `404 NOT FOUND` before any database credential retirement action.
3. **Qualification user detached.** The dedicated qualification user's access to the dedicated qualification database was revoked.
4. **Runtime health preserved after detach.** `/health/ready` returned `status = ready` and `service = oneqay-web` after privilege revocation.
5. **Qualification user deleted.** The dedicated temporary qualification database user was removed.
6. **Database inspected before deletion.** The dedicated qualification database reported no permanent tables.
7. **Qualification database deleted.** The dedicated empty qualification database was removed.
8. **Private qualification identity/credential values cleared.** The qualification database identity, qualification username, and qualification password were cleared from the active release's private runtime environment.
9. **Fail-closed reverified after credential clearing.** The database-qualification endpoint again returned `404 NOT FOUND`.
10. **Final health verified.** `/health/ready` again returned `status = ready` and `service = oneqay-web` after full cleanup.

No other database was intentionally modified by the cleanup.

## Permanent-schema boundary

Before the dedicated qualification database was removed, it was directly inspected and reported no tables.

This is consistent with the governed qualification contract:

- connection-scoped temporary table only;
- no migration;
- no permanent business table;
- no durable business persistence;
- no customer/BPJS/Production data;
- `persistent_schema_created = false`.

The cleanup therefore retired an empty qualification database boundary rather than deleting a business schema.

## Final secure runtime state

After cleanup:

- qualification feature switch: **DISABLED**;
- qualification endpoint: **404 / FAIL-CLOSED**;
- dedicated qualification user: **REMOVED**;
- dedicated qualification database: **REMOVED**;
- qualification database identity in private runtime environment: **CLEARED**;
- qualification username in private runtime environment: **CLEARED**;
- qualification password in private runtime environment: **CLEARED**;
- Technical Preview health: **READY**;
- service: `oneqay-web`.

The active application release remains the governed Technical Preview release used for the tenant-isolation qualification. No additional deployment was required for cleanup.

## Security and sanitization

Repository evidence intentionally records only closure facts. It does not contain:

- database identity/name;
- database username;
- password;
- raw `.env` material;
- APP_KEY;
- cPanel account prefix or home path;
- correlation values;
- session/cookie values;
- raw screenshots;
- tokens/private keys;
- unrelated database identities;
- customer, BPJS, or Production data.

## Evaluator reconciliation

This cleanup **does not promote or regress any evaluator control**.

Canonical evaluator remains:

- **27 VERIFIED**;
- **2 BLOCKED**;
- outcome: **BLOCKED**;
- `lifecycle_authority_created=false`.

Remaining blockers remain exactly:

- `ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED`;
- `RUNTIME:BACKUP_RESTORE:PARTIAL`.

`ENGINE:TENANT_ISOLATION` remains **VERIFIED** because the historical live qualification evidence was governed and published before secure retirement.

## Lifecycle boundary

Current lifecycle remains:

- M7.5: **IN PROGRESS / BLOCKED / INCOMPLETE**;
- M7.6: **NOT AUTHORIZED**;
- M7.7: **NOT AUTHORIZED**;
- Phase 0 Exit: **NOT APPROVED**;
- Release: **NOT AUTHORIZED**;
- Production: **NOT AUTHORIZED**;
- Production readiness: **NO-GO**.

The next remaining evidence problem is restore/backup-restore qualification. This cleanup record itself does not authorize any restore action.

## Machine-readable evidence

Sanitized cleanup evidence is recorded in:

`docs/evidence/runtime/p1-cpanel-tenant-isolation-qualification-cleanup-20260816.json`

State overlay:

`docs/ai/AI_M7_5_TENANT_ISOLATION_CLEANUP_STATE.md`

Attribution: **Lab | zefry**
