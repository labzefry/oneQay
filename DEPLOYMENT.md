# oneQay Deployment Handbook

## Goals

Deployment harus reproducible, auditable, secure, recoverable, dan tidak mengubah business logic antar environment. Artifact yang sama dipromosikan; konfigurasi dan secret membedakan environment.

## Environments

| Environment | Purpose | Data |
|---|---|---|
| Local | Development | Synthetic |
| Test | Automated validation | Synthetic/masked |
| Staging | Production-like rehearsal | Masked |
| Production | Tenant operation | Real, classified |

Production access menggunakan least privilege, MFA, approval, audit, dan break-glass procedure.

## Configuration

- Environment variable atau secret manager sebagai sumber konfigurasi runtime.
- `.env` real tidak boleh di-commit.
- Config schema divalidasi saat startup/install.
- Default harus aman; missing critical config menyebabkan fail-closed.
- Feature flag memiliki owner, scope, expiry, audit, dan removal task.

## Deployment stages

### Stage 1 — Shared Hosting / cPanel

Wajib tersedia secure document root, environment separation, HTTPS, scheduler/cron, controlled file permission, database backup, log access, health endpoint, installer lock, atomic release strategy yang didukung host, dan rollback package.

Constraint shared hosting tidak boleh masuk ke domain/application layer.

### Stage 2 — VPS

Tambahkan OS hardening, dedicated service account, firewall, automated provisioning/deploy, reverse proxy, process supervision, centralized logs, monitoring, offsite backup, dan restore rehearsal.

### Stage 3 — Dedicated Server

Tambahkan capacity planning, storage/redundancy design, network segmentation, hardware monitoring, failover/DR decision, dan maintenance lifecycle.

### Stage 4 — Docker

Image immutable, non-root, minimal base, health check, read-only filesystem bila memungkinkan, externalized state, pinned dependency, vulnerability scan, SBOM, resource limit, dan image signing policy.

### Stage 5 — Cloud

Gunakan least-privilege IAM, private networking, managed secrets, managed database/storage sesuai ADR, autoscaling, multi-zone decision, cost guardrail, centralized observability, backup, dan DR.

### Stage 6 — Kubernetes

Hanya setelah ada platform ownership. Wajib resource request/limit, probes, disruption budget, network policy, secret integration, autoscaling criteria, policy enforcement, rollout strategy, cluster backup, dan workload isolation.

## Release artifact

Artifact harus memiliki version, commit SHA, build timestamp, compatibility metadata, checksum/signature, migration set, SBOM sesuai maturity, changelog, dan installation/update instruction. Build sekali, promote artifact yang sama.

## Deployment pipeline

1. verify clean/tagged source;
2. restore dependencies reproducibly;
3. lint, type, test, scan;
4. build artifact;
5. generate checksum/SBOM;
6. deploy to staging;
7. migrate and smoke test;
8. approval;
9. backup and preflight production;
10. deploy/migrate;
11. health and business verification;
12. observe and close/rollback.

## Database migration

Migration memiliki preflight, compatibility window, lock/load estimate, backup, rehearsal, progress signal, verification, dan recovery. Destructive contract migration dipisahkan dari deploy yang menghapus compatibility.

## Deployment strategies

- Atomic directory/symlink release untuk compatible hosting.
- Rolling/blue-green/canary dipilih sesuai platform dan risk.
- Maintenance mode hanya bila zero-downtime tidak aman; harus memiliki status page/message dan bypass terkontrol.
- Feature flag bukan pengganti incomplete migration safety.

## Health verification

Technical checks: process, config, database, cache/queue, storage, external dependency, scheduler, error rate, latency. Business checks: login, tenant isolation, catalog read, controlled transaction smoke, audit, notification/payment callback sesuai environment.

## Rollback

Rollback decision memiliki owner dan threshold. Application rollback hanya dilakukan bila schema masih compatible. Jika data telah berubah, gunakan recovery/forward fix yang direhearsal. Semua rollback dicatat dan diikuti verification.

## Backup and disaster recovery

- Encrypted offsite backup dan retention.
- Restore runbook serta periodic rehearsal.
- RPO/RTO per capability.
- Dependency inventory dan contact tree.
- DR exercise menghasilkan evidence dan task perbaikan.

## Observability and alerts

Pantau availability, error rate, latency, saturation, job backlog/failure, database, storage, external dependencies, auth anomaly, tenant isolation denial, payment/reconciliation, updater, dan backup. Alert harus actionable, memiliki owner/runbook, serta tidak membocorkan data.

## Cloudflare operations

DNS/SSL/wildcard/cache operation menggunakan scoped token, validation, idempotency, audit, retry, quota awareness, dan rollback. Cache purge harus sempit; global purge memerlukan approval sesuai risk.

## Deployment Definition of Done

Artifact terversi, quality gate lulus, migration dan backup direhearsal, approval tersedia, deployment tercatat, health/business checks lulus, monitoring normal, rollback siap, dan changelog/release record diperbarui.

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
technology, security, testing, hosting, and deployment decisions receive
separate Product Owner approval.

Attribution: Lab | zefry
