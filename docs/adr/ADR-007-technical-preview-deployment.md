# ADR-007: Technical Preview Stage-1 Deployment

- Status: Proposed
- Date: 2026-08-03
- Decision owner: Product Owner OneQay
- Evidence: Issue #23
- Scope: Technical Preview v0.0.1 only

## Context

Product Owner memilih P1 cPanel/shared hosting secara conditional, dengan P2 VPS fallback bila worker, deployment, backup, atau rollback capability tidak memadai. Hosting evidence belum tersedia.

## Proposed decision

P1 hanya dapat menjadi Stage-1 preview target jika seluruh mandatory capability pada shared-hosting assessment berstatus Pass. Satu mandatory Fail atau Unverifiable pada security, process model, deployment, database, backup/restore, atau rollback mengaktifkan evaluasi P2; tidak boleh ada silent degradation.

## Mandatory capabilities

- Supported PHP and required extensions.
- HTTPS, domain/subdomain, secure document root, and environment secrets.
- Database engine/version and controlled migration access.
- Git/SSH or equivalent atomic artifact deployment.
- Cron and queue execution compatible with required workloads.
- Writable storage isolation and non-public sensitive paths.
- Backup/export, restore, release retention, and rollback.
- Health checks, logs, error correlation, and resource visibility.

## P2 fallback

P2 adalah hardened VPS deployment dengan repeatable artifact/container strategy, least privilege, firewall, TLS, backup, monitoring, and documented operations. Provider dan runtime belum dipilih.

## Consequences

P1 menjaga roadmap Stage 1 tetapi dapat gagal pada background processing dan recovery controls. P2 menambah operational ownership namun memberikan process control yang lebih kuat.

## Acceptance conditions

- Shared-hosting assessment lengkap dan memiliki evidence URL.
- Recovery rehearsal memenuhi provisional RPO/RTO.
- Deployment dan rollback tidak mengandalkan overwrite langsung tanpa release history.
- Product Owner menyetujui exact head ADR.

Keputusan ini belum mengotorisasi deployment atau source code.
