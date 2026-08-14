# M7.5 P1 Runtime Gap Report

Attribution: **Lab | zefry**

## Scope

This report converts existing repository-published P1/cPanel observations into the M7.5 fail-closed evidence model published by PR #102.

It does not add new hosting observations, credentials, network probes, database connections, deployment activity, or runtime qualification claims.

The evidence package is:

`docs/evidence/runtime/p1-cpanel-historical.json`

The deterministic evaluator result is:

`docs/evidence/runtime/p1-cpanel-historical.report.json`

The package assessment timestamp `2026-08-15T01:47:00+07:00` records when repository evidence was classified into the M7.5 schema. It is not represented as the original timestamp of every historical hosting observation.

## Result

**Outcome: BLOCKED**

M7.5 qualification is not complete.

P1 Shared Hosting/cPanel remains:

**CONDITIONAL / NOT SELECTED**

MariaDB 11.4.8 remains:

**VERIFIED ENGINE-FAMILY / VERSION EVIDENCE — NOT RUNTIME QUALIFIED**

## Verified mandatory runtime control

Only one mandatory runtime control has sufficient repository evidence to be classified `VERIFIED` under the strict M7.5 evaluator:

- `PHP_RUNTIME` — PHP 8.3.26 was observed in the published shared-hosting evidence.

The presence of Apache, cPanel UI tools, PDO/PDO MySQL, backup UI, SSL/TLS UI, cron UI, File Manager, logs/metrics UI, and MariaDB 11.4.8 is useful evidence, but it is not enough to mark the corresponding end-to-end application controls `VERIFIED`.

## Partial runtime controls

The following have meaningful evidence but remain incomplete:

- `WEB_SERVER_REQUEST_RUNTIME` — Apache/runtime evidence exists, but effective oneQay request routing and target behavior are not fully proven.
- `SCHEDULER_CRON` — Cron Jobs UI is observed, but required cadence and actual scheduled execution are not proven.
- `FILESYSTEM_STORAGE` — File Manager capability exists, but required private-path isolation and application permissions are not proven.
- `TLS_HTTPS` — SSL/TLS tooling is observed, but effective redirect, secure-cookie behavior, and target certificate lifecycle are not fully proven.
- `BACKUP_RESTORE` — backup capability is partially evidenced, but successful isolated restore is not supplied.
- `OBSERVABILITY_LOGGING` — Errors/Raw Access/Metrics tooling is observed, but application correlation lookup is not proven.
- `RESOURCE_LIMITS` — PHP memory/execution/request limits are known, but CPU/process/storage/quota visibility is incomplete.
- `SECURITY_BOUNDARY` — architecture and platform controls exist, but effective target-side secrets/private-storage/Preview security boundaries are incomplete.

## Unverified runtime controls

The following remain `UNVERIFIED`:

- `PHP_CLI`;
- `SAFE_DOCUMENT_ROOT`;
- `URL_REWRITE`;
- `BACKGROUND_EXECUTION`;
- `QUEUE_EXECUTION`;
- `ENVIRONMENT_SECRETS`;
- `DATABASE_CONNECTIVITY`;
- `DEPLOYMENT_RECOVERY`;
- `PREVIEW_ISOLATION`.

## Not-supplied runtime controls

The following remain `NOT_SUPPLIED`:

- `ROLLBACK`;
- `OUTBOUND_DNS_HTTPS`.

## Relational profile result

Selected evidence family: **MariaDB 11.4.8**.

No DEC-005R relational profile control is yet `VERIFIED` by actual oneQay runtime evidence.

### Partial engine-profile control

- `BACKUP_EXPORT` — backup UI/capability evidence exists but coverage, security, export behavior, and recovery integration are incomplete.

### Unverified engine-profile controls

- `APPLICATION_CONNECTIVITY`;
- `LEAST_PRIVILEGE`;
- `CONNECTION_LIMIT_VISIBILITY`;
- `TRANSACTION_SEMANTICS`;
- `TENANT_ISOLATION`;
- `MIGRATION_BOUNDARY`;
- `PORTABILITY_CONTRACT`.

### Not-supplied engine-profile control

- `RESTORE_VERIFIED`.

## Evidence integrity conclusion

The deterministic BLOCKED result is the correct outcome for the repository evidence currently available.

No unknown capability was inferred as available. No control-panel UI presence was converted into end-to-end application proof. No MariaDB profile support claim was inferred merely from PDO/PDO MySQL or engine-family/version availability.

## Exact external evidence still required for P1

To reconsider P1, obtain sanitized non-secret evidence for at least:

1. PHP CLI execution on the target.
2. Document root resolving exactly to the oneQay public application surface.
3. Effective front-controller rewrite/routing.
4. Required cron cadence and actual scheduler execution.
5. Safe bounded background/queue execution model.
6. Environment-secret isolation outside public paths and logs.
7. Private persistent storage isolation and permissions.
8. Effective HTTPS redirect and secure-cookie behavior.
9. Actual oneQay database connectivity using least privilege.
10. Database connection-limit visibility.
11. Transaction behavior required by the bounded POS flow.
12. Tenant-isolation behavior against the actual selected database profile.
13. Backup scope/retention and export behavior.
14. Successful isolated restore evidence.
15. Application log/correlation lookup.
16. Complete CPU/process/storage/quota visibility.
17. Versioned/recoverable publication mechanism.
18. Rollback rehearsal capability.
19. Outbound DNS/HTTPS capability required by the execution model.
20. Explicit Preview-only isolation/security evidence.
21. DEC-005R Database Portability Contract qualification evidence for the selected MariaDB profile.

## Lifecycle

This report creates no lifecycle authority.

- M7.5 PREPARATION: **DONE / PUBLISHED**.
- M7.5 QUALIFICATION: **BLOCKED / WAITING FOR REQUIRED EXTERNAL RUNTIME EVIDENCE**.
- M7.6 / M7.7: **BLOCKED / NOT AUTHORIZED**.
- Deployment / Release / Production: **NOT AUTHORIZED**.
- Phase 0: **IN PROGRESS**.
- Production readiness: **NO-GO**.
