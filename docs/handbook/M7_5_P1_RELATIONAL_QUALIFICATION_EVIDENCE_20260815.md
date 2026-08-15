# M7.5 P1 Relational Qualification Evidence — 2026-08-15

Attribution: **Lab | zefry**

## Purpose

This additive record reconciles the Product Owner-authorized, sanitized relational qualification evidence observed on the non-Production oneQay Technical Preview after PR #111 was published and the exact-main Preview artifact was activated.

It records only bounded qualification facts. It does not contain database names, database usernames, passwords, raw `.env` content, account identifiers, tokens, private keys, screenshots, customer data, BPJS data, Production data, or other credentials.

This record does not authorize permanent schema, migration execution, business persistence, M7.6, M7.7, Phase 0 Exit, Release, Production, or `oneqay.com`.

## Published source and runtime identity

Canonical published source used by the active relational-probe release:

`0edea8cdcc0cb7f16c8e8758aa626e79b4096cf8`

Published source tree:

`effa878587f0175b928f92f91dc9612411c4f24c`

Active non-Production Preview release ID:

`m75-preview-0edea8cdcc0c`

Technical Preview hostname:

`oneqay.n07.my.id`

PR #111 introduced the bounded Preview relational qualification probe. The probe remains opt-in and is available only inside the enabled Technical Preview runtime after synthetic principal and verified context establishment.

## Sanitized live probe result

**Bounded relational probe: QUALIFIED**

The protected Technical Preview qualification endpoint returned the following sanitized facts:

- `status = qualified`;
- `scope = technical-preview-relational-probe`;
- `production_ready = false`;
- `persistent_schema_created = false`;
- `engine_profile = mariadb`;
- `engine_version = 11.4.8`;
- configuration = verified;
- PDO MySQL driver = verified;
- connection = verified;
- engine family = verified;
- temporary table = verified;
- transaction rollback = verified;
- tenant-scoped query = verified.

No exception message, host, database identity, username, password, or credential is part of the recorded response evidence.

The endpoint-level `qualified` result applies only to the bounded PR #111 relational probe. It is not equivalent to `EVIDENCE_COMPLETE` for the broader 29-control M7.5 runtime qualification evaluator.

## Database connectivity

**Status: VERIFIED for the bounded non-Production Preview relational probe**

The active Laravel Technical Preview successfully established a PDO connection to the dedicated non-Production MariaDB Preview target and verified the engine family/version through the protected qualification path.

This closes the previously missing live application database-connectivity evidence for the bounded Preview probe.

It does not establish durable business persistence because no permanent business schema exists and no migration was executed.

## Application connectivity

**Status: VERIFIED**

The relational probe executed from the oneQay Laravel runtime, not from a detached control-panel-only database tool. The application path therefore directly verified application-to-engine connectivity for the selected MariaDB profile.

## Least privilege

**Status: VERIFIED for the bounded qualification account**

The dedicated Preview qualification account was verified with only these required privileges:

- `SELECT`;
- `INSERT`;
- `UPDATE`;
- `DELETE`;
- `CREATE TEMPORARY TABLES`.

Permanent-schema and administration privileges were not granted. In particular, the qualification account does not carry `ALL PRIVILEGES`, permanent `CREATE`, `ALTER`, `DROP`, `INDEX`, trigger/event/routine/view administration, `LOCK TABLES`, or comparable broad DDL/administrative authority.

Credential values are intentionally excluded from this repository record.

## Connection limit visibility

**Status: UNVERIFIED**

The successful bounded probe proves connectivity, but no sanitized evidence in this reconciliation establishes the effective database connection ceiling, per-account connection limit, saturation behavior, or application pool/concurrency envelope.

This remains a fail-closed engine-profile blocker in the deterministic M7.5 evaluator.

## Transaction semantics

**Status: VERIFIED for the bounded probe**

The PR #111 probe exercised an InnoDB transaction, inserted synthetic qualification data, rolled the transaction back, and verified rollback semantics without creating durable business records.

This closes the bounded transaction-semantics check for the selected MariaDB Preview profile.

## Tenant isolation

**Status: PARTIAL**

The relational probe verified a tenant-scoped query against connection-scoped temporary qualification data. That is material evidence that tenant scope is carried into the relational query boundary.

It is deliberately not promoted to full `ENGINE:TENANT_ISOLATION = VERIFIED` because the current project still has no permanent relational business schema and this probe did not perform a database-backed two-tenant negative-isolation exercise across durable business repositories.

The bounded probe result `tenant_scoped_query = verified` is therefore preserved without over-claiming the broader DEC-005R tenant-isolation control.

## Migration boundary

**Status: VERIFIED as a deny-by-capability boundary for this qualification scope**

The qualification account does not have permanent `CREATE`, `ALTER`, or `DROP` privileges. The probe creates only a connection-scoped temporary table and reports `persistent_schema_created = false`.

No migration, permanent table, schema mutation, seeder, or business persistence was executed.

This verification means the current qualification path is technically constrained away from permanent schema mutation. It does not authorize or prove any future migration implementation or migration rehearsal.

## Portability contract

**Status: UNVERIFIED**

This live qualification is for the selected MariaDB 11.4.8 Preview profile only. It does not qualify MySQL, PostgreSQL, cross-engine behavior, DBME, data movement, or the complete DEC-005R portability contract.

`ENGINE:PORTABILITY_CONTRACT` therefore remains blocking.

## Release and recovery boundary

**Status: PARTIAL**

The active Preview uses a versioned exact-main release layout and an earlier governed release remains available as a rollback point. That proves a concrete release/recovery boundary exists.

No deliberate rollback or recovery rehearsal is recorded by this reconciliation. Therefore `RUNTIME:DEPLOYMENT_RECOVERY` remains `PARTIAL` and `RUNTIME:ROLLBACK` remains `NOT_SUPPLIED`.

M7.6 remains a separate blocked milestone and is not implied by the relational probe success.

## Security boundary

**Status: PARTIAL / materially improved**

The qualification route is bounded to the Technical Preview runtime, requires a synthetic principal session plus verified context, emits a sanitized response, and does not expose credentials. The runtime also preserves previously verified public/private filesystem separation and tested non-disclosure of sensitive paths.

The broader M7.5 security-boundary control remains `PARTIAL` because this reconciliation does not complete the full threat-model/security verification envelope for all remaining runtime capabilities.

## Deterministic M7.5 evaluator reconciliation

The sanitized machine-readable package is:

`docs/evidence/runtime/p1-cpanel-live-relational-20260815.json`

Its deterministic evaluator-shaped report is:

`docs/evidence/runtime/p1-cpanel-live-relational-20260815.report.json`

Current result:

- outcome: **BLOCKED**;
- verified mandatory controls: **13**;
- blocking mandatory controls: **16**;
- lifecycle authority created: **false**.

The 13 controls currently reconciled as `VERIFIED` are:

- `ENGINE:APPLICATION_CONNECTIVITY`;
- `ENGINE:BACKUP_EXPORT`;
- `ENGINE:LEAST_PRIVILEGE`;
- `ENGINE:MIGRATION_BOUNDARY`;
- `ENGINE:TRANSACTION_SEMANTICS`;
- `RUNTIME:DATABASE_CONNECTIVITY`;
- `RUNTIME:FILESYSTEM_STORAGE`;
- `RUNTIME:PHP_RUNTIME`;
- `RUNTIME:PREVIEW_ISOLATION`;
- `RUNTIME:SAFE_DOCUMENT_ROOT`;
- `RUNTIME:TLS_HTTPS`;
- `RUNTIME:URL_REWRITE`;
- `RUNTIME:WEB_SERVER_REQUEST_RUNTIME`.

## Remaining blocking controls

The deterministic fail-closed evidence model still has 16 mandatory blockers:

- `ENGINE:CONNECTION_LIMIT_VISIBILITY:UNVERIFIED`;
- `ENGINE:PORTABILITY_CONTRACT:UNVERIFIED`;
- `ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED`;
- `ENGINE:TENANT_ISOLATION:PARTIAL`;
- `RUNTIME:BACKGROUND_EXECUTION:PARTIAL`;
- `RUNTIME:BACKUP_RESTORE:PARTIAL`;
- `RUNTIME:DEPLOYMENT_RECOVERY:PARTIAL`;
- `RUNTIME:ENVIRONMENT_SECRETS:PARTIAL`;
- `RUNTIME:OBSERVABILITY_LOGGING:PARTIAL`;
- `RUNTIME:OUTBOUND_DNS_HTTPS:PARTIAL`;
- `RUNTIME:PHP_CLI:PARTIAL`;
- `RUNTIME:QUEUE_EXECUTION:UNVERIFIED`;
- `RUNTIME:RESOURCE_LIMITS:PARTIAL`;
- `RUNTIME:ROLLBACK:NOT_SUPPLIED`;
- `RUNTIME:SCHEDULER_CRON:PARTIAL`;
- `RUNTIME:SECURITY_BOUNDARY:PARTIAL`.

These blockers are the reason the broader M7.5 evaluator remains `BLOCKED` even though the bounded live relational probe itself reports `qualified`.

## Current M7.5 interpretation

The correct current distinction is:

- M7.5 preparation: **DONE / PUBLISHED**;
- M7.5 live web-runtime evidence: **VERIFIED / MATERIAL PROGRESS**;
- M7.5 bounded MariaDB relational probe: **QUALIFIED / VERIFIED**;
- M7.5 complete 29-control evidence package: **BLOCKED / INCOMPLETE — 16 blockers remain**;
- M7.6: **BLOCKED / NOT AUTHORIZED**;
- M7.7: **BLOCKED / NOT AUTHORIZED**;
- Production readiness: **NO-GO**;
- Production authority: **NONE**.

The bounded relational probe success does not authorize a permanent schema, migration, durable business persistence, real payment provider, Production database/data, `oneqay.com`, Release, or Production.

## Probe lifecycle after evidence reconciliation

The live qualification switch may be returned to its fail-closed disabled state only after this evidence reconciliation is safely published/governed and the Product Owner performs the bounded private-runtime action. The runtime credential must remain private and must never be pasted into GitHub, chat, screenshots, logs, or source.

## Security and privacy statement

No raw screenshot, cPanel account identifier, home-directory path, database name, database username, password, token, private key, runtime `APP_KEY`, customer data, BPJS data, personal data, or Production data is committed by this record.

Attribution: **Lab | zefry**
