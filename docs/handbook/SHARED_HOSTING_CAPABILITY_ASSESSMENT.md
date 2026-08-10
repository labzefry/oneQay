# Shared-Hosting Capability Assessment

- Status: Incomplete / P1 Not Selected
- Decision candidate: P1 cPanel/shared hosting
- Fallback: P2 managed/hardened VPS or server
- Owner: Repository and Operations Owner
- Historical source: Issue #23
- Current architecture owner: DEC-009 — Deployment Stage 1 Runtime Requirements

## Decision rule

P1 remains conditional. Stage-1 environment selection is capability-based, not hosting-category-based. Any mandatory control that is Failed, Unverified, Not Supplied, or otherwise non-compliant blocks P1 selection until evidence changes. P2 must then be evaluated without silently weakening correctness, security, recovery, deployment, or observability requirements.

This assessment records repository evidence only. Unknown capability is never inferred as available.

## Evidence sources

Published bounded Platform Foundation evidence materially relevant to the current assessment includes:

- `docs/PLATFORM_APPLICATION_BOOTSTRAP_AND_RUNTIME_CAPABILITY_FOUNDATION.md`;
- `docs/PERSISTENCE_CAPABILITY_AND_DATABASE_CONNECTION_BOUNDARY_FOUNDATION.md`;
- DEC-005 canonical MySQL Server decision;
- substantive DEC-009 capability requirements.

No credential, token, password, or production secret is required or recorded here.

## Evidence matrix

| Capability | Requirement | Current repository evidence | Status |
| --- | --- | --- | --- |
| PHP runtime | Supported runtime and release-required extensions | PHP 8.3.26 observed; current foundation extensions JSON, OpenSSL, Mbstring, PDO, PDO MySQL, Filter, Session, Ctype observed | VERIFIED |
| PHP CLI | CLI capability for authorized operational commands | Runtime foundation/CI require PHP CLI semantics; exact target-host CLI execution not independently recorded | UNVERIFIED |
| Database engine | Canonical DEC-005 MySQL Server within supported LTS-family boundary | MariaDB 11.4.8 observed on cPanel; no compliant MySQL Server capability evidenced | VERIFIED BUT NON-COMPLIANT |
| Database connectivity | Least-privilege app connection, limits, topology/TLS as required | PDO/PDO MySQL and DB UI observed; actual oneQay connection, credential, TLS, connection limits remain unknown | UNVERIFIED |
| SSH | Controlled shell access where selected deployment model requires it | Account has no SSH | VERIFIED — UNAVAILABLE |
| Git UI | Controlled repository/artifact capability | cPanel Git Version Control UI observed | VERIFIED PARTIAL |
| Composer on runtime host | Not mandatory if trusted prebuilt artifact is used | Executable not proven | NOT REQUIRED for preferred prebuilt-artifact model |
| Build environment Composer | Reproducible trusted build | CI shows Composer capability; final application dependency build remains separately gated | VERIFIED PARTIAL |
| URL rewrite | Effective front-controller routing | Not proven on target | UNVERIFIED |
| Safe document root | Web root points only to public application surface | Exact target document-root mapping to `public` not proven | UNVERIFIED |
| Cron | Scheduler capability and adequate cadence | Cron Jobs UI observed; minimum interval not proven | VERIFIED PARTIAL |
| Worker/process | Persistent worker or safe bounded scheduled alternative | No persistent/background process capability proven | UNVERIFIED |
| HTTPS/TLS tooling | Certificate lifecycle capability | cPanel SSL/TLS tooling observed | VERIFIED PARTIAL |
| Secure redirect/cookies | Effective HTTPS redirect and secure session behavior | Not independently proven on target deployment | UNVERIFIED |
| Domain/subdomain | Preview hostname and document-root control | cPanel site context exists; safe document-root control remains unproven | VERIFIED PARTIAL |
| Secrets | Non-public environment-specific configuration | Architecture/configuration boundary defined; target storage/path behavior not proven | UNVERIFIED |
| Filesystem | Private writable storage and permission control | File Manager capability observed historically; required private-path isolation not proven | VERIFIED PARTIAL |
| Backup/export | Database/files backup capability | cPanel backup UI observed; schedule, coverage, retention, off-host copy not proven | VERIFIED PARTIAL |
| Restore | Tested restore to isolated target | No successful restore rehearsal evidence | NOT SUPPLIED |
| Rollback | Versioned release retention and recoverable rollback | No final deployment/rollback mechanism proven | NOT SUPPLIED |
| Logs | Application/server logs and correlation lookup | Errors, Raw Access and Metrics tools observed; application correlation lookup not proven | VERIFIED PARTIAL |
| Resource visibility | CPU, memory, storage, process limits | memory_limit 512M and execution limit 300s observed; full CPU/process/storage limits incomplete | VERIFIED PARTIAL |
| Upload/request limits | Bounded request/upload capability | upload_max_filesize 32M; post_max_size 32M observed | VERIFIED |
| Storage quota | Capacity and alert threshold | Not supplied | NOT SUPPLIED |
| Outbound DNS/HTTPS | Required build/runtime network access | Not supplied for target | NOT SUPPLIED |
| Symlink/atomic switch | Recoverable versioned publication mechanism where used | Symlink policy/final deployment method unknown | UNVERIFIED |
| Final deployment method | Build-once trusted artifact with recoverable publication | Not selected or proven | UNVERIFIED |

## Current known resource facts

Repository evidence currently records:

- PHP 8.3.26;
- Apache 2.4.63 on Linux x86_64;
- JSON, OpenSSL, Mbstring, PDO, PDO MySQL, Filter, Session, Ctype;
- memory limit 512M;
- maximum execution time 300 seconds;
- upload max filesize 32M;
- post max size 32M;
- Cron Jobs UI;
- backup UI;
- Errors / Raw Access / Metrics;
- SSL/TLS tools;
- File Manager;
- Git Version Control UI;
- MultiPHP Manager / Select PHP Version;
- MariaDB 11.4.8;
- no SSH.

These facts do not constitute a complete P1 Pass.

## MySQL Server blocker

DEC-005 makes **MySQL Server** the canonical relational engine family. MariaDB 11.4.8 evidence is retained as a factual hosting capability but is **non-compliant** with that canonical engine decision and cannot silently substitute for MySQL Server.

P1 may become eligible if a compliant MySQL Server connection is independently evidenced, including the required security, latency/availability, credential, connection-limit, backup, restore, and operational boundaries.

## Deployment-model interpretation

SSH absence alone does not automatically fail P1. A target without SSH may still qualify if an equivalent controlled deployment mechanism proves all mandatory properties, including trusted/versioned artifacts, public/private path safety, recoverable publication, release history, rollback/recovery, and auditability.

No such complete mechanism is currently proven.

## Mandatory evidence still required before P1 selection

At minimum, P1 remains blocked pending evidence for:

- canonical MySQL Server connectivity;
- actual application database connectivity/security/limits;
- safe document root exactly to the public application surface;
- effective URL rewrite/front-controller routing;
- scheduler cadence adequate for authorized workloads;
- safe worker/background execution model where required;
- environment secret isolation on the target;
- persistent private storage isolation;
- scheduled/covered backup and retention/off-host behavior where appropriate;
- isolated restore rehearsal;
- versioned/recoverable deployment and rollback;
- application-level logs with correlation lookup;
- complete resource/quota visibility;
- outbound DNS/HTTPS capability according to the execution model.

## Evidence quality rule

Acceptable evidence remains a redacted hosting screenshot/export, vendor documentation tied to the relevant plan, or an observed non-production capability check. Evidence must identify owner/source and observation time where practicable. Credentials and tokens are prohibited.

## P1 pass criteria

P1 may be selected only when every mandatory Stage-1 capability has verifiable evidence and no mandatory control is Failed, Unverified, Not Supplied, or non-compliant.

Additionally:

- queue-dependent workloads must have an executable safe process model;
- secrets and private storage must not be publicly servable;
- backup must be accompanied by successful restore evidence;
- deployment must not overwrite live files without a recoverable release boundary;
- rollback/recovery must be rehearsable;
- the database capability must comply with DEC-005;
- Product Owner deployment-execution authority remains separately required even after capability Pass.

## Current conclusion

**P1 SHARED HOSTING / CPANEL IS CONDITIONAL / NOT SELECTED.**

The repository contains meaningful partial capability evidence, so the historical all-`Not supplied` matrix is no longer accurate. However mandatory blockers remain, including the canonical MySQL Server requirement and multiple deployment/recovery/process controls.

P2 managed/hardened VPS/server remains the fallback execution class under DEC-009. No provider or runtime host is selected by this assessment, and no deployment authority is created.

Attribution: Lab | zefry
