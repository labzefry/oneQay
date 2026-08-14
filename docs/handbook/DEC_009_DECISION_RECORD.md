# DEC-009 — Deployment Stage 1 Runtime Requirements Decision Record

- Status: Approved / Substantive Decision Complete
- Decision owner: Product Owner `labzefry`
- Product: oneQay
- Developer & Product Engineering Entity: Lab | zefry
- Decision baseline: `0fdc0a53403f16fbc6908630ea350af2c0de466b`
- Verified baseline tree: `45c0aa49657db8f95ca08e662ec641e6d9d5f25a`
- Published predecessor: DEC-008 / PR #82
- Publication authority: documentation representation only; no deployment or implementation authority

## Current database-decision reconciliation

DEC-009 remains the owner of Stage-1 Preview runtime capability requirements. The later substantive **DEC-005R — Portable Relational Persistence Architecture** partially supersedes DEC-005's sole-MySQL engine selection. Accordingly, only the database-engine dependency in D-009-05 and directly dependent P1 evidence is reconciled; all other DEC-009 runtime, security, recovery, observability, deployment, and authority requirements remain unchanged.

## Decision

DEC-009 establishes a **Capability-Based Staged / Hybrid Portability Model** for the first governed deployment stage. Environment selection is based on verified capability compliance rather than hosting category.

- P1 Shared Hosting / cPanel: **CONDITIONAL / NOT SELECTED**.
- P2 Managed / Hardened VPS or Server: **FALLBACK EXECUTION CLASS**.
- No hosting provider is selected.
- Stage 1 governs the **Preview** environment class, not Production.
- Domain and Application layers remain infrastructure- and provider-independent.

## D-009 dispositions

### D-009-01 — Stage 1 deployment posture

Approved: **Capability-Based Staged / Hybrid Portability Model**. P1 remains eligible only if every mandatory runtime, security, database, deployment, recovery, observability, and process capability is verified. Otherwise P2 is evaluated without changing Domain/Application business logic.

### D-009-02 — PHP runtime

Approved: PHP baseline `>=8.2`; PHP CLI mandatory; exact supported minor/patch deferred to the authorized Laravel/release compatibility matrix; required extensions must be declared and validated for the authorized release.

### D-009-03 — Composer / build capability

Approved: **Build Once / Deploy Trusted Artifact**. Composer is mandatory in the trusted build environment. Composer on the runtime host is not inherently required for a verified prebuilt artifact. Reproducible dependency locking becomes mandatory only after dependencies are separately authorized.

### D-009-04 — Web server / request runtime

Approved provider-neutral requirements: HTTPS, secure front-controller routing, a public-only document root, rewrite/routing capability, bounded request/upload limits, headers, timeout control, static-asset delivery, and trusted-proxy configuration where applicable. No web-server vendor is selected.

### D-009-05 — Database connectivity

Approved as reconciled by DEC-005R: Stage 1 requires an **authorized and qualified relational engine profile under DEC-005R**. MariaDB, MySQL, or PostgreSQL profile identity alone does not constitute runtime qualification. MariaDB 11.4 family is the current Stage-1 profile direction because repository evidence already observes that family, but actual application connectivity, least-privilege credentials, externalized secrets, connection-limit visibility, topology-appropriate TLS, backup/export, verified restore, controlled migration boundary, transaction semantics, and tenant isolation must still be qualified. No schema, SQL, DDL, migration execution, live database connection, or data movement is authorized.

### D-009-06 — Queue / background work

Approved: Stage 1 must support safe background execution where authorized workloads require it. Persistent supervised workers are preferred; scheduled short-lived execution is acceptable only when latency, correctness, retry, idempotency, and workload behavior prove it safe. Exact queue technology, driver, concurrency, retry, and supervisor remain deferred.

### D-009-07 — Scheduler / cron

Approved: cron-equivalent capability is mandatory. The target must be capable of the standard Laravel scheduler model, including one-minute cadence when an authorized schedule requires it. Exact cron expressions remain implementation-gated.

### D-009-08 — Cache / session

Approved: server-side Web/PWA session, application cache, and rate-limit/temporary-state capability are mandatory. Tenant-aware behavior applies where relevant and cache is never source of truth. Redis is **not mandatory** for first bounded Stage 1; exact driver remains deferred.

### D-009-09 — File / object storage

Approved: persistent writable storage where required, explicit public/private separation, non-public sensitive paths, controlled permissions, backup coverage, temp isolation, log rotation, and future object-storage portability. No object-storage provider is selected.

### D-009-10 — Frontend build / static assets

Approved: Vue/Inertia/Vite/TypeScript assets are built in a trusted build environment and deployed as compiled release assets. Node is a build-time capability; Node runtime on the application host is not mandatory merely to serve compiled assets. Exact versions remain deferred.

### D-009-11 — Android boundary

Approved: Stage-1 server architecture must remain compatible with future secure, versioned HTTPS REST/API delivery for Android. No Android implementation, distribution, or offline transaction authority is granted.

### D-009-12 — HTTPS / TLS / DNS

Approved: HTTPS, valid certificate lifecycle capability, secure redirect, secure cookies, and sufficient DNS control are required. HSTS is enabled only after topology implications are validated. No DNS mutation, domain purchase, certificate installation, or provider selection is authorized.

### D-009-13 — Secrets / configuration

Approved: environment-specific externalized configuration; secrets outside repository, clients, logs, issues, PRs, and chat; least-scope credentials; rotation/revocation capability; protected encryption/application keys; and fail-closed handling for missing critical configuration. No actual production `.env` or credential is authorized.

### D-009-14 — Logging / observability

Approved minimum capability: application/runtime logs, correlation lookup, security-event visibility, health indicators, log rotation, safe diagnostics, and sufficient operator access. Exact stack/vendor is deferred; detailed retention remains DEC-011.

### D-009-15 — Backup / restore

Approved: database backup, persistent-file backup where applicable, recovery metadata, integrity verification, isolated restore, restore rehearsal, off-host copy where appropriate, and access control/audit. Backup success alone is not recoverability. Final RPO/RTO remain DEC-012.

### D-009-16 — Release / rollback

Approved: versioned artifacts, release/commit identity, bounded-safe or atomic publication where supported, previous-release retention, recoverable application/configuration rollback, migration compatibility, and post-publication health verification. Direct live overwrite without a recoverable release boundary is unsupported. Deployment execution is not authorized.

### D-009-17 — Health / availability

Approved minimum capability: application health, configuration/runtime readiness, database dependency health, worker/scheduler awareness where applicable, storage dependency health where applicable, and basic uptime/error monitoring. No formal SLO/SLA is approved.

### D-009-18 — Network / outbound access

Approved: required DNS resolution, outbound HTTPS where build/runtime integrations require it, controlled package/build network access, separately authorized external API/email connectivity, and limited inbound exposure. No external provider is authorized.

### D-009-19 — Environment classes

Approved canonical operational classes: **Local**, **Test / CI**, **Preview**, and **Production**. DEC-009 Stage 1 governs **Preview**. Existing `Staging` language must be reconciled to or explicitly mapped to runtime `preview`. Production remains separately gated.

### D-009-20 — Portability / hosting lock-in

Approved: Domain/Application remain independent from cPanel, shared-hosting APIs, VPS vendors, specific web servers, cache/queue providers, container/cloud providers, and relational engine vendor identity. Environment and engine differences remain Configuration/Infrastructure concerns. DEC-005R establishes a zero-business-code-change target between officially qualified relational engine profiles; this does not mean Infrastructure adapters/configuration are identical between engines.

### D-009-21 — P1 shared-hosting / cPanel hypothesis

Approved current disposition: **P1 CONDITIONAL / NOT SELECTED**. Existing cPanel evidence is partial and does not constitute a Pass. DEC-005R removes the former sole-MySQL-family incompatibility as an architecture blocker: observed MariaDB 11.4.8 is now **verified engine-family/version evidence**, not runtime qualification. Current blockers still materially include safe document-root mapping, effective rewrite, cron cadence, worker/process model, atomic/versioned deployment, rollback, restore rehearsal, actual application DB connection/security/limits, engine-profile runtime qualification, and complete resource/quota/outbound evidence. SSH absence alone is not automatic failure if an equivalent controlled recoverable deployment mechanism is independently proven.

### D-009-22 — Explicit non-scope

DEC-009 does not authorize hosting procurement, server purchase, provisioning, domain purchase, DNS mutation, certificate installation, production secrets or `.env`, source/Laravel/Vue/PWA/Android implementation, dependency installation, schema/SQL/DDL/migration, engine adapter implementation, DBME implementation, payment-provider work, real-money processing, transactional-offline implementation, deployment execution, release, production promotion, Sprint 14, final RPO/RTO, DEC-010 license policy, DEC-011 privacy/retention/jurisdiction policy, or DEC-012 support/recovery objectives.

## Current cPanel evidence

Published Sprint 07/08 evidence demonstrates, without credentials:

- PHP 8.3.26;
- Apache 2.4.63 on Linux x86_64;
- JSON, OpenSSL, Mbstring, PDO, PDO MySQL, Filter, Session, and Ctype;
- memory limit 512M;
- max execution time 300 seconds;
- upload/post limits 32M;
- Cron Jobs UI, backup UI, SSL/TLS tools, Git Version Control UI, MultiPHP tools, log/metrics tools;
- MariaDB 11.4.8;
- no SSH.

This evidence does **not** establish a complete P1 Pass. Under DEC-005R, MariaDB 11.4.8 is valid **engine-family/version evidence** and is no longer rejected solely because it is not MySQL Server. It remains **NOT YET RUNTIME QUALIFIED** until the required application/runtime/security/recovery evidence is established. Other mandatory capabilities remain unverified or not supplied as recorded in the shared-hosting assessment.

## ADR-007 disposition

ADR-007 remains the Accepted representation of substantive DEC-009 while preserving Issue #23, historical P1 conditional cPanel/shared-hosting planning, P2 hardened VPS fallback, and Technical Preview v0.0.1 provenance. DEC-005R requires only a bounded reconciliation of its database/runtime dependency; no DEC-009R is created.

## Portability and authority boundary

DEC-009 defines runtime capability requirements, not deployment execution. Moving from P1 to P2, between qualified relational engine profiles, or to later infrastructure stages must not require rewriting Domain/Application business logic.

This decision grants no application/source implementation, dependency installation, infrastructure provisioning, hosting procurement, DNS/certificate mutation, production secret, database schema/SQL/migration, engine adapter/DBME implementation, deployment, release, Sprint 14, or production authority.

Phase 0 remains **IN PROGRESS**. Sprint 14 remains **NOT AUTHORIZED**. M7.5 remains **BLOCKED / NOT AUTHORIZED** pending actual sanitized target evidence, DEC-009 capability verification including relational engine-profile qualification, and separate Product Owner authority. Production readiness remains **NO-GO**.

Attribution: Lab | zefry
