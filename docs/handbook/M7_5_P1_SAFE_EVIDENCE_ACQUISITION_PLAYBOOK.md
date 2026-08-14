# M7.5 P1 Safe Evidence Acquisition Playbook

Attribution: **Lab | zefry**

## Purpose

This playbook defines the safest non-secret, non-destructive evidence collection path for the remaining P1 Shared Hosting/cPanel M7.5 controls.

It complements:

- `docs/evidence/runtime/p1-cpanel-historical.json`;
- `docs/evidence/runtime/p1-cpanel-historical.report.json`;
- `docs/handbook/M7_5_P1_RUNTIME_GAP_REPORT.md`;
- `docs/handbook/M7_5_P2_EVIDENCE_ACQUISITION_CHECKLIST.md`.

It does **not** authorize deployment, infrastructure mutation, DNS/TLS mutation, package installation, database migration, Production access, credentials capture, M7.6, M7.7, Release, or Production.

## Current classification

Current deterministic P1 outcome remains:

**P1-BLOCKED-INCOMPLETE-EVIDENCE**

Current machine-verifiable report records:

- `RUNTIME:PHP_RUNTIME` as the only fully `VERIFIED` mandatory runtime control;
- 28 blocking runtime/engine controls;
- MariaDB 11.4.8 as engine-family/version evidence only;
- `lifecycle_authority_created=false`.

No mandatory control is reclassified by this playbook.

## Evidence safety rules

Evidence collection must be read-only and sanitized.

Never capture or commit:

- passwords;
- database passwords;
- cPanel passwords;
- SSH/private keys;
- API tokens;
- raw `.env` files;
- Laravel `APP_KEY`;
- session secrets;
- private keys;
- customer or Production data.

Before saving a screenshot, hide or redact:

- usernames where not required for the control;
- account/domain identifiers where a generic target label is sufficient;
- IP addresses where not required;
- filesystem home-directory account names;
- email addresses;
- credential fields;
- database usernames where the value itself is unnecessary.

Evidence should record a sanitized target identifier and observation time.

## Existing negative evidence

Account-level SSH is historically observed as unavailable.

SSH absence alone is not a mandatory M7.5 failure because an equivalent controlled cPanel mechanism may qualify. However, evidence collection must not assume shell access, root access, Supervisor, systemd, package installation, or server configuration access.

## Safe collection matrix

| Control | Current state | Safe evidence path | Sufficient evidence direction |
| --- | --- | --- | --- |
| `PHP_CLI` | UNVERIFIED | cPanel Terminal/SSH availability screen if present, hosting plan documentation tied to the account, or a provider-confirmed PHP CLI capability statement | Direct proof that PHP CLI can execute for this target without exposing credentials. Absence of SSH alone does not prove CLI unavailable. |
| `WEB_SERVER_REQUEST_RUNTIME` | PARTIAL | cPanel domain/subdomain configuration plus an already-existing non-sensitive HTTPS endpoint response | Evidence that the selected Preview hostname is actually served through the expected PHP/web runtime. No new deployment is authorized. |
| `SAFE_DOCUMENT_ROOT` | UNVERIFIED | cPanel Domains/Subdomains document-root screen with account-specific path portions redacted | Direct proof that the web root can be mapped only to the public application surface rather than repository/private paths. |
| `URL_REWRITE` | UNVERIFIED | provider/cPanel Apache rewrite capability evidence tied to the target; actual front-controller proof must wait for an authorized non-production application runtime | UI/module capability alone remains PARTIAL until effective routing is proven. |
| `BACKGROUND_EXECUTION` | UNVERIFIED | cPanel process/application capability documentation tied to the hosting plan | Direct proof of a safe bounded background execution model. Do not assume persistent workers from generic cPanel features. |
| `QUEUE_EXECUTION` | UNVERIFIED | provider documentation for persistent process support or a bounded scheduled queue alternative that satisfies oneQay semantics | Must demonstrate retry/restart/concurrency behavior; otherwise remain UNVERIFIED. |
| `SCHEDULER_CRON` | PARTIAL | cPanel Cron Jobs minimum interval screen and, if already available, sanitized historical execution evidence | Minimum cadence capability can be verified separately; actual oneQay scheduled execution requires later authorized runtime evidence. |
| `FILESYSTEM_STORAGE` | PARTIAL | File Manager path/permission screenshots with private account paths redacted | Must prove private writable storage is outside public serving paths and permissions are controlled. |
| `ENVIRONMENT_SECRETS` | UNVERIFIED | provider/cPanel documentation showing non-public environment/configuration storage capability; never display values | Must prove secrets can remain outside public files, logs, and repository. |
| `TLS_HTTPS` | PARTIAL | cPanel SSL/TLS status, certificate validity metadata, and HTTPS behavior of an already-existing non-sensitive hostname | Effective redirect and secure-cookie suitability remain separate from certificate-tool availability. |
| `DATABASE_CONNECTIVITY` | UNVERIFIED | database engine/account-management capability screenshots without password values; actual oneQay connection proof requires authorized non-production runtime | PDO driver or database UI does not prove application connectivity. |
| `BACKUP_RESTORE` | PARTIAL | backup configuration/coverage/retention screenshots and provider documentation; successful restore must be separately demonstrated | Backup capability without an isolated successful restore cannot be VERIFIED. |
| `OBSERVABILITY_LOGGING` | PARTIAL | cPanel Errors/Raw Access/Metrics capability plus sanitized log-retention visibility | Application correlation-ID lookup requires an authorized running oneQay Preview target. |
| `RESOURCE_LIMITS` | PARTIAL | cPanel Resource Usage, Disk Usage, process/account limits, and PHP limit screens | Evidence should cover CPU, memory, storage, process limits, request/upload limits, and relevant database limits. |
| `DEPLOYMENT_RECOVERY` | UNVERIFIED | cPanel Git Version Control/release capability and provider documentation | Must show a versioned/recoverable publication boundary; direct destructive overwrite is insufficient. |
| `ROLLBACK` | NOT_SUPPLIED | documented cPanel release rollback/version-retention capability if available | Actual rollback rehearsal requires separately authorized non-production runtime/recovery work. |
| `SECURITY_BOUNDARY` | PARTIAL | public/private path controls, secret-storage capability, TLS status, account isolation and permission evidence | Must prove effective target behavior; architecture documentation alone is not runtime proof. |
| `PREVIEW_ISOLATION` | UNVERIFIED | sanitized evidence of a dedicated Preview hostname/account/environment boundary without Production/customer data | Actual oneQay tenant/data isolation runtime proof remains separately required. |
| `OUTBOUND_DNS_HTTPS` | NOT_SUPPLIED | hosting-plan/provider capability statement tied to the target | Actual outbound request proof should use only an authorized non-sensitive endpoint and must not leak tokens or credentials. |

## MariaDB profile evidence matrix

MariaDB 11.4.8 is already recorded as engine-family/version evidence. The following controls remain runtime qualification requirements.

| Engine control | Current state | Safe evidence path |
| --- | --- | --- |
| `APPLICATION_CONNECTIVITY` | UNVERIFIED | actual sanitized oneQay non-production connection evidence after runtime use is separately authorized |
| `LEAST_PRIVILEGE` | UNVERIFIED | database-user privilege screen or sanitized privilege summary without password/secret values; application-level behavior still needs runtime proof |
| `CONNECTION_LIMIT_VISIBILITY` | UNVERIFIED | cPanel/database limits or hosting-plan documentation tied to the account |
| `TRANSACTION_SEMANTICS` | UNVERIFIED | actual bounded synthetic transaction test on an authorized non-production target |
| `TENANT_ISOLATION` | UNVERIFIED | actual synthetic cross-tenant negative tests on the selected runtime/database profile |
| `BACKUP_EXPORT` | PARTIAL | database backup/export coverage and retention evidence |
| `RESTORE_VERIFIED` | NOT_SUPPLIED | successful isolated restore evidence; never restore into Production |
| `MIGRATION_BOUNDARY` | UNVERIFIED | evidence that schema evolution can be controlled/recoverable; no migration execution is authorized here |
| `PORTABILITY_CONTRACT` | UNVERIFIED | selected-profile evidence against DEC-005R portability invariants; no business-code vendor leakage |

## Evidence that cannot be completed under this prompt alone

The current authority explicitly prohibits deployment and infrastructure provisioning. Therefore several end-to-end controls cannot legitimately become `VERIFIED` only through cPanel capability screenshots.

At minimum, full verification of the following requires a separately authorized non-production runtime/rehearsal context or an already-running authorized Preview target:

- effective front-controller routing;
- actual oneQay database connectivity;
- transaction semantics;
- tenant-isolation semantics;
- application correlation logging;
- scheduler/worker execution semantics;
- successful isolated restore;
- release/rollback rehearsal;
- effective Preview-only application isolation.

This is an authority/evidence boundary, not a reason to fabricate capability.

## Recommended screenshot package

For the next P1 evidence intake, a sanitized screenshot bundle should cover, where available:

1. cPanel Domains/Subdomains document-root configuration.
2. Cron Jobs page showing minimum selectable cadence.
3. Resource Usage / Limits and Disk Usage.
4. SSL/TLS status for a non-sensitive Preview-capable hostname.
5. File Manager showing public/private path separation without exposing private files.
6. Backup configuration/coverage/retention capability.
7. Database engine/version and account/privilege capability with credential values hidden.
8. Git Version Control/release/version-retention capability.
9. Errors/Raw Access/Metrics/logging capability.
10. Any provider/account page proving outbound HTTPS/DNS, background process, queue/worker, PHP CLI, or storage limits.

Do not include secret values merely to make a screenshot look complete.

## P1 decision rule

Until new evidence is supplied and evaluated:

**P1-BLOCKED-INCOMPLETE-EVIDENCE**

No currently available evidence proves a mandatory control is `UNAVAILABLE` except SSH itself, and SSH is not independently mandatory if an equivalent safe operational model exists.

P1 must not be labeled unsuitable solely because SSH is unavailable. It must also not be labeled conditionally qualified while 28 mandatory runtime/engine controls remain non-VERIFIED.

## P2 direction

P2 Managed/Hardened VPS or Server remains the provider-neutral fallback evidence path.

P2 evidence acquisition is required to keep qualification moving in parallel unless new P1 evidence closes the blockers. P2 is not selected as a deployment target by this playbook.

Use `docs/handbook/M7_5_P2_EVIDENCE_ACQUISITION_CHECKLIST.md` for P2 evidence collection.

## Lifecycle

This playbook creates no lifecycle authority.

- M7.5 PREPARATION: **DONE / PUBLISHED**.
- M7.5 P1 HISTORICAL EVIDENCE CLASSIFICATION: **DONE / PUBLISHED**.
- M7.5 QUALIFICATION: **BLOCKED / WAITING FOR REQUIRED EXTERNAL RUNTIME EVIDENCE**.
- M7.6 / M7.7: **BLOCKED / NOT AUTHORIZED**.
- Deployment / Release / Production: **NOT AUTHORIZED**.
- Phase 0: **IN PROGRESS**.
- Production readiness: **NO-GO**.
