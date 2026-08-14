# ADR-007: Stage-1 Deployment Runtime Requirements

- Status: Accepted — substantive DEC-009 representation; database dependency reconciled to DEC-005R
- Date: 2026-08-03
- Decision owner: Product Owner oneQay
- Historical evidence: Issue #23 / P1 conditional cPanel/shared hosting / P2 hardened VPS fallback
- Current substantive owner: DEC-009 — Deployment Stage 1 Runtime Requirements
- Database architecture dependency: DEC-005R — Portable Relational Persistence Architecture
- Current scope: Stage-1 Preview runtime architecture only; no deployment execution authority

## Historical Technical Preview provenance

Issue #23 originally recorded a bounded Technical Preview v0.0.1 deployment hypothesis:

- P1: cPanel/shared hosting, conditional;
- P2: hardened VPS fallback if worker, deployment, backup, or rollback capability was insufficient;
- P1 could be selected only when every mandatory shared-hosting capability passed;
- provider and final runtime remained unselected.

That historical P1/P2 planning is preserved as provenance. It must not be rewritten as though it had already contained the complete substantive DEC-009 architecture or as though P1 had already been approved as the execution environment.

## Current context

Substantive DEC-009 establishes a **Capability-Based Staged / Hybrid Portability Model**. Stage-1 environment selection is based on verified capability compliance rather than hosting category.

Current disposition:

- P1 Shared Hosting / cPanel: **CONDITIONAL / NOT SELECTED**;
- P2 Managed / Hardened VPS or Server: **FALLBACK EXECUTION CLASS**;
- Stage 1 governs **Preview**, not Production;
- no hosting provider is selected;
- Domain/Application business logic remains independent from hosting, infrastructure, and relational-engine vendors.

DEC-005R later supersedes DEC-005's sole-MySQL engine requirement. This ADR therefore changes only its database/runtime dependency; all other DEC-009 capability and authority boundaries remain intact.

## Decision

### Runtime and build

- PHP baseline is `>=8.2`.
- PHP CLI is mandatory.
- Exact supported PHP minor/patch is deferred to the authorized Laravel/release compatibility matrix.
- Required extensions are declared and validated by the authorized release.
- Preferred build model is **Build Once / Deploy Trusted Artifact**.
- Composer is required in the trusted build environment; it is not inherently required on the runtime host when a verified prebuilt artifact contains dependencies.
- Vue/Inertia/Vite/TypeScript assets are build-time outputs; Node need not run on the application host merely to serve compiled assets.

### Web/request runtime

Stage 1 requires HTTPS, secure front-controller routing, a public-only document root, rewrite/routing capability, bounded request and upload limits, header controls, timeout controls, static-asset delivery, and trusted-proxy configuration where applicable. No web-server vendor is selected.

### Database

DEC-005R is the current database-architecture dependency. Stage 1 requires an **authorized and qualified relational engine profile under DEC-005R**, not sole canonical MySQL Server.

MariaDB, MySQL, and PostgreSQL are authorized profile directions; profile identity or driver connectivity alone is not qualification. MariaDB 11.4 family is the Stage-1 profile direction because repository evidence already observes that family, but it remains **NOT YET RUNTIME QUALIFIED**.

Stage 1 requires least-privilege application credentials, externalized secrets, topology-appropriate TLS, known connection limits, controlled migration execution boundary, backup/export and verified restore capability, tenant-isolation enforcement, and evidence that the selected engine profile satisfies oneQay's portability and transactional requirements. This ADR does not create schema, SQL, DDL, migration, DBME, live-database, or production-database authority.

### Queue and scheduler

Safe background execution is required where authorized workloads need it. Persistent supervised workers are preferred. Scheduled short-lived execution is permitted only where correctness, retry, idempotency, latency, and workload behavior prove it safe.

Cron-equivalent scheduling is mandatory and must be capable of the standard Laravel scheduler model, including one-minute cadence when an authorized application schedule requires it. Exact queue technology, worker concurrency, retry/backoff, supervisor, and cron expressions remain deferred.

### Session and cache

Server-side Web/PWA session, application cache, and rate-limit/temporary-state capability are mandatory. Tenant-aware behavior applies where relevant. Cache is never source of truth. Redis is not mandatory for the first bounded Stage 1; exact drivers remain deferred.

### Storage

Stage 1 requires persistent writable storage where the authorized business scope needs it, explicit public/private separation, non-public sensitive paths, controlled permissions, temporary-file isolation, log rotation, backup coverage, and future object-storage portability. No object-storage provider is selected.

### HTTPS, DNS, secrets, and configuration

HTTPS, certificate lifecycle capability, secure redirects, secure cookies, and sufficient DNS control are mandatory. HSTS is enabled only after topology validation. Secrets remain externalized and environment-specific, outside repository/client/log/issues/PR/chat, least-scope, rotatable/revocable, and fail closed when critical configuration is missing.

No domain purchase, DNS mutation, certificate installation, production `.env`, password, token, credential, or DNS/TLS provider is authorized.

### Observability and health

Stage 1 requires application/runtime logs, correlation lookup, security-event visibility, log rotation, safe diagnostics, application/readiness health, database dependency health, worker/scheduler awareness where applicable, storage dependency health where applicable, and basic uptime/error monitoring. Exact observability technology is deferred. Detailed retention remains DEC-011.

### Backup, restore, release, and rollback

Database and applicable persistent-file backups, recovery metadata, integrity verification, isolated restore, restore rehearsal, off-host copy where appropriate, access control, and audit are mandatory. Backup success alone is not recoverability evidence. Final RPO/RTO remain DEC-012.

Stage 1 must support versioned release artifacts, release/commit identity, bounded-safe or atomic publication where supported, previous-release retention, recoverable application/configuration rollback, migration compatibility, and post-publication health verification. Direct overwrite of live application files without a recoverable release boundary is unsupported.

### Environment classes

Canonical operational environment classes are:

- Local;
- Test / CI;
- Preview;
- Production.

DEC-009 Stage 1 governs **Preview**. Existing human-facing `Staging` terminology must be explicitly mapped to runtime `preview` or reconciled. Production remains separately gated.

### Portability

Domain/Application layers must remain independent from cPanel, shared-hosting APIs, specific VPS vendors, specific web servers, cache/queue vendors, containers, cloud providers, and relational engine vendor identity. Environment/engine differences remain Configuration/Infrastructure concerns.

DEC-005R establishes a **ZERO BUSINESS-CODE CHANGE** target between officially qualified relational engine profiles. That target does not mean engine-specific Infrastructure adapters or configuration are identical.

## Current P1 evidence and blockers

Published Sprint 07/08 evidence demonstrates PHP 8.3.26, Apache 2.4.63 on Linux x86_64, the current required foundation PHP extensions, 512M memory limit, 300-second execution limit, 32M upload/post limits, cPanel Cron/backup/SSL/Git/log/metrics tooling, MariaDB 11.4.8, and no SSH.

Under DEC-005R, observed MariaDB 11.4.8 is **verified engine-family/version evidence** and is no longer rejected solely because it is not MySQL Server. It remains **NOT YET RUNTIME QUALIFIED**.

P1 remains **CONDITIONAL / NOT SELECTED** because mandatory capability evidence remains absent or unverified, including materially:

- relational engine-profile runtime qualification and actual application connectivity;
- safe document-root mapping to `public`;
- effective URL rewrite;
- minimum cron cadence;
- background worker/process model;
- atomic/versioned deployment;
- rollback;
- restore rehearsal;
- actual application database connection/security/limits;
- complete resource/quota/outbound capability.

SSH absence alone is not automatic failure if an equivalent controlled, recoverable, versioned deployment mechanism is independently proven.

## Consequences

- Hosting limitations cannot silently redefine business architecture or correctness requirements.
- Relational engine availability cannot silently become runtime qualification.
- Shared hosting remains cost-efficient only when all mandatory controls are proven.
- P2 adds operational ownership but provides stronger process/runtime control when P1 cannot satisfy mandatory capabilities.
- Later infrastructure or qualified relational-engine evolution must not require rewriting Domain/Application business logic.

## Explicit non-scope

This ADR does not authorize hosting/provider procurement, server provisioning, domain/DNS/certificate mutation, production secrets, source implementation, dependency installation, Laravel/Vue/PWA/Android implementation, schema/SQL/DDL/migrations, database adapter implementation, cross-engine CI implementation, DBME implementation, payment-provider work, offline implementation, M7.5 execution, deployment execution, release, production promotion, Sprint 14, final RPO/RTO, DEC-010, DEC-011, or DEC-012 decisions.

Phase 0 remains **IN PROGRESS**. M7.5 remains **BLOCKED / NOT AUTHORIZED**. Sprint 14 remains **NOT AUTHORIZED**. Deployment, Release, and Production remain **NOT AUTHORIZED**. Production readiness remains **NO-GO**.

Attribution: Lab | zefry
