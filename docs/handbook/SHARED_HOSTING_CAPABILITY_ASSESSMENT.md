# Shared-Hosting Capability Assessment

- Status: Incomplete
- Decision candidate: P1 cPanel/shared hosting
- Fallback: P2 hardened VPS
- Owner: Repository and Operations Owner
- Source: Issue #23

## Decision rule

P1 remains conditional. Any mandatory control marked Fail or Unverifiable blocks P1 for the Technical Preview. P2 must then be evaluated; controls may not be silently removed.

## Evidence matrix

| Capability | Requirement | Current evidence | Status |
|---|---|---|---|
| PHP runtime | Supported version and required extensions | Not supplied | Pending |
| Database | Engine/version, transaction and constraint support | Not supplied | Pending |
| SSH/Git | Controlled artifact deployment | Not supplied | Pending |
| Cron | Documented minimum interval | Not supplied | Pending |
| Worker/process | Persistent worker or safe documented alternative | Not supplied | Pending |
| HTTPS | Valid TLS and secure redirect | Not supplied | Pending |
| Domain/subdomain | Sandbox hostname and document-root control | Not supplied | Pending |
| Secrets | Non-public environment configuration | Not supplied | Pending |
| Filesystem | Private writable storage and permission control | Not supplied | Pending |
| Backup/export | Scheduled database/files backup | Not supplied | Pending |
| Restore | Tested restore to isolated target | Not supplied | Pending |
| Rollback | Versioned release retention and rollback | Not supplied | Pending |
| Logs | Application/server logs and correlation lookup | Not supplied | Pending |
| Resource visibility | CPU, memory, storage, process limits | Not supplied | Pending |
| Storage quota | Capacity and alert threshold | Not supplied | Pending |

## Mandatory evidence

Evidence should be a redacted hosting screenshot/export, vendor documentation tied to the subscribed plan, or an observed non-production capability check. Credentials and tokens are prohibited.

## P1 pass criteria

- Every row has owner, timestamp, evidence URL, and Pass result.
- Queue-dependent use cases have an executable process model.
- Secrets and private storage cannot be served publicly.
- Backup, restore, and rollback rehearsal meets REC-1.
- Deployment does not overwrite live files without a recoverable release boundary.

## Current conclusion

**P1 is Unverified and cannot yet be selected as the execution environment.** P2 is a fallback hypothesis, not an approved provider/runtime decision.
