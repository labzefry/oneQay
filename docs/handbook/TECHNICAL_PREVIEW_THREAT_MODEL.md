# Technical Preview Threat Model

- Status: Proposed
- Owner: Security Owner
- Scope: OneQay Technical Preview v0.0.1
- Source: Issue #23

## Assets and trust boundaries

Assets include tenant context, synthetic business data, account/session/MFA material, money and stock state, audit records, backups, deployment credentials, and release artifacts.

Trust boundaries exist between browser and application, request and tenant resolver, application and database/cache/job/filesystem, privileged and tenant roles, build and runtime, runtime and backup storage, and support/operator access.

## Priority threats and controls

| Threat | Severity | Required preventive controls | Verification |
| --- | --- | --- | --- |
| Cross-tenant data access | Critical | Validated tenant context, deny-by-default, scoped repository/query layer, composite integrity | Negative isolation suite |
| Broken authorization | Critical | Server-side policy, least privilege, role matrix | Authorization matrix tests |
| Session theft/fixation | High | Secure cookie, rotation, expiry, CSRF, TLS, revocation | Session lifecycle tests |
| MFA/recovery abuse | High | TOTP, hashed single-use recovery, throttling, audit | Recovery abuse tests; JRN-003 remains open |
| Double sale/stock effect | High | Idempotency, transaction boundary, stable movement causation | Retry/concurrency tests |
| Money precision/currency mix | High | Integer minor units, one sale currency, invariant checks | Money property tests |
| Secret exposure | Critical | Environment boundary, redaction, secret scan, least privilege | Repository/log scan |
| Malicious upload or path traversal | High | Preview upload disabled unless separately approved | Route and file-boundary review |
| Backup disclosure or failed restore | High | Encryption, restricted access, expiry, restore rehearsal | Backup/restore evidence |
| Supply-chain compromise | High | Lockfiles, provenance, dependency/license scan | CI evidence |
| Deployment overwrite/no rollback | High | Versioned release, atomic switch or equivalent, retained rollback | Deployment rehearsal |
| Offline replay/stale mutation | High | Online-only mutation, explicit failure offline | Offline/reconnect tests |

## Abuse cases

1. A user changes tenant identifiers in URL/body/header.
2. A global object ID from Tenant A is requested under Tenant B.
3. A cached response survives tenant switching.
4. A sale request is retried concurrently.
5. A privileged session is reused after revocation.
6. A backup for one tenant is restored over another tenant.
7. A deployment partially replaces files and leaves incompatible migration state.

## Recovery and audit requirements

Security-relevant events record tenant, actor, action, target, timestamp, correlation, causation/idempotency reference, outcome, and safe error code. Logs do not contain secret or raw authentication material. Recovery procedures preserve audit evidence.

## Exit conditions

- Every Critical/High threat maps to a test or explicit blocking task.
- No Critical unresolved threat required for skeleton authorization.
- Hosting capability and recovery plan support the listed controls.
- Product Owner and Security reviewer approve exact head.
