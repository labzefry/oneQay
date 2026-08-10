# oneQay Deployment Handbook

## Goals

Deployment harus reproducible, auditable, secure, recoverable, dan tidak mengubah business logic antar environment. Artifact yang sama dipromosikan; konfigurasi dan secret membedakan environment.

## Environments

| Environment | Purpose | Data |
|---|---|---|
| Local | Development | Synthetic |
| Test / CI | Automated validation | Synthetic/masked |
| Preview | Production-like rehearsal | Masked |
| Production | Tenant operation | Real, classified |

Historical documentation may use `Staging` as a human-facing label. Under substantive DEC-009, the canonical Stage-1 runtime classification is `Preview`; `Staging` must be explicitly mapped to `Preview` rather than treated as an additional environment class.

Production access menggunakan least privilege, MFA, approval, audit, dan break-glass procedure.

## Configuration

- Environment variable atau secret manager sebagai sumber konfigurasi runtime.
- `.env` real tidak boleh di-commit.
- Config schema divalidasi saat startup/install.
- Default harus aman; missing critical config menyebabkan fail-closed.
- Feature flag memiliki owner, scope, expiry, audit, dan removal task.

## Deployment stages

### Stage 1 — Capability-Based Preview

Stage 1 mengikuti substantive DEC-009 **Capability-Based Staged / Hybrid Portability Model**. Environment dipilih berdasarkan pemenuhan capability, bukan kategori hosting.

Mandatory capability mencakup secure public-only document root, HTTPS, environment separation, externalized secrets, scheduler/cron, safe background-execution model where required, controlled file permission, canonical MySQL Server connectivity under DEC-005, server-side session/cache capability, persistent private storage where required, backup plus verified restore capability, log/correlation access, health/readiness, resource visibility, trusted versioned release artifact, recoverable publication, dan rollback/recovery.

Preferred build model adalah **Build Once / Deploy Trusted Artifact**. Composer dan Node/build tooling dapat berada di trusted build environment dan tidak wajib tersedia pada runtime host jika artifact terverifikasi sudah membawa dependencies dan compiled assets yang diperlukan.

P1 Shared Hosting / cPanel tetap **CONDITIONAL / NOT SELECTED** dan hanya eligible jika seluruh mandatory Stage-1 capability terbukti. P2 Managed / Hardened VPS or Server adalah **FALLBACK EXECUTION CLASS** bila P1 gagal atau tetap unverifiable pada satu mandatory requirement. Tidak ada provider yang dipilih oleh DEC-009.

Constraint hosting tidak boleh masuk ke Domain/Application layer.

### Stage 2 — VPS / Managed Server Evolution

Tambahkan OS hardening, dedicated service account, firewall, automated provisioning/deploy, reverse proxy, process supervision, centralized logs, monitoring, offsite backup, dan restore rehearsal sesuai kebutuhan environment. Under DEC-009, a managed/hardened VPS/server may also serve as the P2 Stage-1 fallback execution class when P1 cannot satisfy mandatory capabilities; this does not change Domain/Application business logic.

### Stage 3 — Dedicated Server

Tambahkan capacity planning, storage/redundancy design, network segmentation, hardware monitoring, failover/DR decision, dan maintenance lifecycle.

### Stage 4 — Docker

Image immutable, non-root, minimal base, health check, read-only filesystem bila memungkinkan, externalized state, pinned dependency, vulnerability scan, SBOM, resource limit, dan image signing policy.

### Stage 5 — Cloud

Gunakan least-privilege IAM, private networking, managed secrets, managed database/storage sesuai ADR, autoscaling, multi-zone decision, cost guardrail, centralized observability, backup, dan DR.

### Stage 6 — Kubernetes

Hanya setelah ada platform ownership. Wajib resource request/limit, probes, disruption budget, network policy, secret integration, autoscaling criteria, policy enforcement, rollout strategy, cluster backup, dan workload isolation.

## Stage-1 runtime boundaries

Substantive DEC-009 menetapkan boundary provider-neutral berikut:

- PHP baseline `>=8.2`; PHP CLI mandatory; exact supported minor/patch mengikuti authorized Laravel/release compatibility matrix.
- HTTPS, secure front controller, rewrite/routing, public-only document root, bounded request/upload/timeout controls, dan trusted proxy policy where applicable.
- DEC-005 canonical **MySQL Server** requirement; MariaDB tidak boleh silent substitute.
- Cron-equivalent scheduler capability; safe worker/background model where authorized workloads require it.
- Server-side Web/PWA session, application cache, and rate-limit/temporary-state capability; Redis is not mandatory for first bounded Stage 1.
- Persistent private storage, backup coverage, isolated restore, release metadata, health/readiness, logging/correlation, and recoverable rollback.
- Secrets remain environment-specific and externalized; no production `.env` or credential belongs in repository/client/logs.
- Domain/Application remain independent from cPanel, specific VPS/web-server/cache/queue/container/cloud providers.

DEC-009 defines requirements only. It does not authorize infrastructure provisioning, DNS/certificate mutation, deployment execution, migration execution, release, production promotion, or Sprint 14.

## Release artifact

Artifact harus memiliki version, commit SHA, build timestamp, compatibility metadata, checksum/signature, migration set, SBOM sesuai maturity, changelog, dan installation/update instruction. Build sekali, promote artifact yang sama.

## Deployment pipeline

1. verify clean/tagged source;
2. restore dependencies reproducibly;
3. lint, type, test, scan;
4. build artifact;
5. generate checksum/SBOM;
6. deploy to Preview;
7. migrate and smoke test only when separately authorized;
8. approval;
9. backup and preflight production;
10. deploy/migrate only with separate deployment/migration authority;
11. health and business verification;
12. observe and close/rollback.

## Database migration

Migration memiliki preflight, compatibility window, lock/load estimate, backup, rehearsal, progress signal, verification, dan recovery. Destructive contract migration dipisahkan dari deploy yang menghapus compatibility. DEC-009 does not authorize migration execution.

## Deployment strategies

- Atomic directory/symlink release untuk compatible hosting.
- Equivalent controlled versioned artifact publication may be used where symlink/SSH is unavailable only when recoverability, path safety, release history, rollback, and auditability are proven.
- Rolling/blue-green/canary dipilih sesuai platform dan risk.
- Maintenance mode hanya bila zero-downtime tidak aman; harus memiliki status page/message dan bypass terkontrol.
- Feature flag bukan pengganti incomplete migration safety.
- Direct overwrite of live application files without a recoverable release boundary is unsupported.

## Health verification

Technical checks: process, config, database, cache/queue, storage, external dependency, scheduler, error rate, latency. Business checks: login, tenant isolation, catalog read, controlled transaction smoke, audit, notification/payment callback sesuai environment and only when those capabilities are authorized.

## Rollback

Rollback decision memiliki owner dan threshold. Application rollback hanya dilakukan bila schema masih compatible. Jika data telah berubah, gunakan recovery/forward fix yang direhearsal. Semua rollback dicatat dan diikuti verification.

## Backup and disaster recovery

- Encrypted offsite backup dan retention where appropriate.
- Restore runbook serta periodic rehearsal.
- Backup success alone is not recoverability evidence; successful restore evidence is required.
- RPO/RTO per capability remain separately governed by DEC-012.
- Dependency inventory dan contact tree.
- DR exercise menghasilkan evidence dan task perbaikan.

## Observability and alerts

Pantau availability, error rate, latency, saturation, job backlog/failure, database, storage, external dependencies, auth anomaly, tenant isolation denial, payment/reconciliation, updater, dan backup. Alert harus actionable, memiliki owner/runbook, serta tidak membocorkan data. Detailed retention remains separately governed where applicable by DEC-011.

## Cloudflare operations

DNS/SSL/wildcard/cache operation menggunakan scoped token, validation, idempotency, audit, retry, quota awareness, dan rollback. Cache purge harus sempit; global purge memerlukan approval sesuai risk. This section defines operational controls only and does not authorize Cloudflare or DNS mutation under DEC-009.

## Deployment Definition of Done

Artifact terversi, quality gate lulus, migration dan backup direhearsal where applicable, approval tersedia, deployment tercatat, health/business checks lulus, monitoring normal, rollback siap, dan changelog/release record diperbarui. Deployment Definition of Done does not itself grant deployment authority.

## Governance required-check workflow

The repository uses
`.github/workflows/governance-required-checks.yml` as a narrowly scoped
repository-governance control.

It produces the following protected-branch checks:

- `governance-validation`;
- `markdown-lint`;
- `secret-scan`.

This workflow:

- runs for pull requests targeting `main`;
- may be started manually through `workflow_dispatch` for diagnostics;
- uses read-only repository-content permission;
- does not use deployment environments;
- does not access deployment credentials or repository secrets;
- does not build application artifacts;
- does not publish packages or container images;
- does not execute database migrations;
- does not release or deploy oneQay.

A successful workflow run is governance evidence only. It is not deployment
approval, release authority, Phase 0 exit approval, application source-code
authority, or merge authority.

Application deployment remains unavailable until the relevant architecture,
technology, security, testing, hosting, deployment, release, and lifecycle decisions receive separate Product Owner approval and execution authority.

Attribution: Lab | zefry
