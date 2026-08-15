# M7.5 P1 Actual cPanel Evidence — 2026-08-15

Attribution: **Lab | zefry**

## Scope

This record classifies the sanitized facts that can be supported by the Product Owner supplied cPanel screenshots captured on 2026-08-14 and 2026-08-15.

The raw screenshots are **not committed** because they contain account-identifying information and, in the Cron Jobs view, secret-shaped query values associated with existing applications. Those values are intentionally omitted and must never become oneQay repository evidence.

This record does not deploy oneQay, create a domain, mutate DNS, install a certificate, create a database, run a migration, provision infrastructure, or authorize M7.6/M7.7/Release/Production.

## Product Owner target direction

For the Technical Preview/test stage, the Product Owner selected:

`oneqay.n07.my.id`

For a future Production stage, the intended canonical public domain is:

`oneqay.com`

The Production-domain intent is planning information only. It does **not** authorize Production or a domain/DNS migration.

At the evidence observation time, `oneqay.n07.my.id` is not shown as an existing domain entry in the supplied cPanel Domains screen. Therefore the Preview hostname is selected but not yet runtime-proven.

## Sanitized evidence sources

The supplied evidence package includes screenshots of:

- cPanel Domains and document-root controls;
- Cron Jobs;
- Disk Usage and account/resource summaries;
- File Manager;
- Backup / partial restore controls;
- Manage My Databases;
- Git Version Control;
- SSL/TLS Certificates / AutoSSL status;
- Server Information;
- PHP Selector version, extensions, and options.

No username, shared IP address, home-directory account name, database username, secret value, or existing application token is reproduced in this record.

## PHP runtime

**Status: VERIFIED**

The supplied PHP Selector evidence confirms PHP 8.3 as the active selected runtime and shows the relevant runtime options and extensions. Published repository evidence already records the exact observed PHP 8.3.26 runtime. The supplied screenshots also show configured memory, execution-time, upload, and POST limits.

## PHP CLI

**Status: PARTIAL**

The Cron Jobs interface provides PHP command examples using a CLI executable path, and existing scheduled entries are configured to invoke PHP scripts through a CLI path. This is strong target capability evidence, but the screenshots do not prove successful execution of a oneQay CLI command. Therefore the strict M7.5 status remains `PARTIAL`.

## Web runtime

**Status: PARTIAL**

Server Information shows Apache 2.4.63 on Linux x86_64 and the cPanel account currently serves multiple domains. The evidence proves the hosting web-runtime class exists, but `oneqay.n07.my.id` has not yet been created and no oneQay request/health response has been observed.

## Document root

**Status: PARTIAL**

The Domains interface demonstrates that individual domains/subdomains can be assigned distinct document-root paths rather than all being forced to one common web root. File Manager also shows a structural separation between the account home and `public_html`.

The selected Preview domain is not yet provisioned, so there is not yet direct proof that `oneqay.n07.my.id` resolves exactly to oneQay's public application surface. `SAFE_DOCUMENT_ROOT` therefore remains `PARTIAL`.

## URL rewrite

**Status: UNVERIFIED**

No supplied screenshot demonstrates an effective oneQay front-controller rewrite or an application route being resolved through the selected Preview target. Generic Apache/cPanel presence is insufficient.

## Background execution

**Status: PARTIAL**

The cPanel account supports scheduled command execution and currently contains scheduled PHP/HTTPS command entries. This supports a bounded background-execution capability direction. It does not prove persistent worker behavior, restart semantics, concurrency, or oneQay background execution.

## Queue execution

**Status: UNVERIFIED**

No persistent queue worker, Supervisor-like process model, or proven bounded oneQay queue alternative is evidenced.

## Scheduler cron

**Status: PARTIAL**

The Cron Jobs screenshot proves the interface supports one-minute (`*`) and five-minute (`*/5`) cadence expressions and contains current scheduled entries. This materially strengthens the scheduler evidence.

However, the screenshot does not include a sanitized execution log proving successful oneQay scheduler execution. The mandatory end-to-end control therefore remains `PARTIAL`.

## Filesystem storage

**Status: PARTIAL**

File Manager and Disk Usage show directories outside `public_html`, including account-level log, temporary, PHP/configuration, and other private paths. This proves the platform can structurally separate public and non-public paths.

Actual oneQay private persistent storage, ownership, write permissions, and public-serving denial remain unproven.

## Environment secrets

**Status: PARTIAL**

The account has non-public filesystem paths outside `public_html`, which provides a plausible target boundary for environment-specific configuration. No oneQay secret-storage implementation is present or inspected.

The supplied Cron Jobs screenshot contains secret-shaped query values for existing applications. Those values are deliberately excluded from repository evidence. oneQay must not use URL/query-string secrets as its operational secret pattern.

## TLS HTTPS

**Status: PARTIAL**

The SSL/TLS evidence shows AutoSSL domain-validated certificates are active for existing account domains and can renew through AutoSSL. The Domains interface also exposes per-domain Force HTTPS Redirect capability, with that control enabled on multiple existing domains.

`oneqay.n07.my.id` is not yet present in the certificate/domain evidence, so target-specific certificate coverage, HTTPS redirect, secure-cookie behavior, and certificate lifecycle are not yet verified.

## Database connectivity

**Status: PARTIAL**

The database management interface proves the account can create/manage relational databases and database users and can associate users with databases. Server Information identifies MariaDB 11.4.8.

No oneQay database, credential, live connection, TLS/topology behavior, or application connection test is evidenced. PDO/driver or database UI availability alone remains insufficient for `VERIFIED` application connectivity.

## Backup restore

**Status: PARTIAL**

The Backup interface provides:

- downloadable full-account backup capability;
- downloadable home-directory backup capability;
- downloadable individual database backup capability;
- upload-based home-directory restore control;
- upload-based database restore control.

The same interface states that automatically generated account backups are not currently available unless enabled by the server administrator. No successful isolated restore rehearsal is supplied. Therefore the combined `BACKUP_RESTORE` runtime control remains `PARTIAL`.

## Observability logging

**Status: PARTIAL**

The cPanel toolset exposes Errors, Raw Access, Awstats, Resource Usage, and related metrics/logging surfaces. File Manager also shows a non-public logs directory. No running oneQay application exists on the selected Preview hostname, so correlation-ID lookup and oneQay health/runtime log behavior remain unverified.

## Resource limits

**Status: PARTIAL**

The supplied cPanel account summary and PHP options expose concrete CPU/memory/process/PHP request-limit information, and Disk Usage provides actual account storage consumption. This is stronger than the historical partial evidence.

A complete oneQay M7.5 resource qualification still requires the relevant storage quota/threshold interpretation, database connection limits, process behavior under the selected execution model, and target-specific capacity evidence.

## Deployment recovery

**Status: PARTIAL**

Git Version Control is available and can create or clone repositories into a chosen account path. Manual full/home/database backups are also available. These prove useful deployment/recovery primitives.

No versioned oneQay release layout, artifact identity, atomic/recoverable publication method, previous-release retention, or tested recovery sequence has been supplied.

## Rollback

**Status: NOT_SUPPLIED**

No tested oneQay rollback or equivalent version-retention/recovery rehearsal is supplied.

## Security boundary

**Status: PARTIAL**

Evidence supports account-level public/private filesystem separation capability, SSL/TLS tooling, AutoSSL, authentication/security tools, backup controls, and metrics/log surfaces.

Target-specific oneQay secret handling, public-path denial, secure session/cookie behavior, Preview isolation, and application authorization behavior remain unproven.

## Preview isolation

**Status: UNVERIFIED**

The Product Owner has selected `oneqay.n07.my.id` as the temporary Technical Preview/test hostname and has stated that `oneqay.com` is reserved for a future Production stage.

This is a useful environment-separation decision, but the Preview hostname is not yet provisioned in the supplied evidence and no oneQay runtime exists there. Runtime Preview isolation therefore remains `UNVERIFIED`.

## Outbound DNS HTTPS

**Status: PARTIAL**

Existing Cron Jobs are configured with HTTPS/cURL-style scheduled commands, which demonstrates that the account is intended to make outbound HTTPS requests. Secret-shaped query values visible in the raw screenshot are not reproduced.

No sanitized execution result proves successful outbound DNS resolution and HTTPS connectivity for a oneQay process, so the status remains `PARTIAL`.

# MariaDB profile evidence

## Application connectivity

**Status: PARTIAL**

MariaDB 11.4.8 and database/user management are evidenced, but actual oneQay connectivity is not.

## Least privilege

**Status: PARTIAL**

The cPanel database interface supports separate database users and user-to-database association. The supplied evidence does not show the exact oneQay privilege grant set or prove deny-by-default behavior for the application account.

## Connection limit visibility

**Status: UNVERIFIED**

No database connection-limit value tied to the account/oneQay profile is supplied.

## Transaction semantics

**Status: UNVERIFIED**

No synthetic oneQay transaction test has run against this target.

## Tenant isolation

**Status: UNVERIFIED**

No two-tenant negative runtime test has run against this target/profile.

## Backup export

**Status: VERIFIED**

The Backup interface directly exposes downloadable per-database backup artifacts for databases in the account. This is direct sufficient evidence of target database backup/export capability.

This does **not** imply recoverability because restore success is a separate mandatory control.

## Restore verified

**Status: NOT_SUPPLIED**

The UI exposes a database restore upload control, but no successful isolated restore has been demonstrated.

## Migration boundary

**Status: UNVERIFIED**

No controlled oneQay schema-evolution/recovery evidence is supplied, and no migration is authorized by this work item.

## Portability contract

**Status: UNVERIFIED**

The selected evidence family remains MariaDB 11.4.8. No target execution has yet qualified the DEC-005R portability invariants or demonstrated zero business-code vendor leakage.

## Evaluator conclusion

The sanitized actual evidence package is:

`docs/evidence/runtime/p1-cpanel-actual-20260815.json`

Its deterministic report is:

`docs/evidence/runtime/p1-cpanel-actual-20260815.report.json`

Expected strict result:

- outcome: **BLOCKED**;
- verified controls: **2**;
- blockers: **27**;
- verified controls: `ENGINE:BACKUP_EXPORT`, `RUNTIME:PHP_RUNTIME`;
- `lifecycle_authority_created=false`.

The evidence materially improves confidence in P1 capabilities but does not support M7.5 technical qualification.

## P1 decision

Current result:

**P1-BLOCKED-INCOMPLETE-EVIDENCE**

P1 remains a legitimate candidate because the screenshots prove several important cPanel capabilities. It cannot yet be selected for M7.5 because end-to-end oneQay runtime, tenant isolation, database behavior, restore, rollback, routing, and security evidence remain incomplete.

## Security hygiene finding

The raw Cron Jobs screenshot visibly contains secret-shaped query parameters associated with existing applications. Their values are not copied into this repository.

If those values are still active credentials/tokens, they should be rotated outside this repository workflow. For oneQay, operational secrets must be externalized and must not be embedded in URLs, query strings, repository files, screenshots, or logs.

## Lifecycle

No lifecycle authority is created.

- M7.5 PREPARATION: **DONE / PUBLISHED**.
- M7.5 P1 HISTORICAL EVIDENCE CLASSIFICATION: **DONE / PUBLISHED**.
- M7.5 ACTUAL P1 EVIDENCE CLASSIFICATION: **BLOCKED / EVIDENCE IMPROVED**.
- M7.5 QUALIFICATION: **BLOCKED / WAITING FOR REMAINING EXTERNAL RUNTIME EVIDENCE**.
- M7.6 / M7.7: **BLOCKED / NOT AUTHORIZED**.
- Phase 0: **IN PROGRESS**.
- Deployment / Release / Production: **NOT AUTHORIZED**.
- Production readiness: **NO-GO**.
