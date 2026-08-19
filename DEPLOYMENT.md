# oneQay Deployment Handbook

## Canonical post-Sprint 30 program-state reconciliation — 2026-08-19

For current identity, security, schema, runtime, workflow, and next-work interpretation, this section supersedes older current-facing post-Sprint28/post-Sprint29 wording retained below as historical provenance.

- Sprint 21 through Sprint 30 governed control/identity foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint 29 First-Control-Principal Bootstrap Credential Foundation is published through source PR #195 and closes the first protected-control credential circular dependency without credential overwrite, password recovery, or session creation.
- Sprint 30 Privileged TOTP MFA Foundation is published through PR #199 as `6d41755eba4030c2b0b7c4f3b7a5806b761b0ad7` with tree `bf1d56af5524e77919833bd64b585cdca84af55d` after **22/22** exact-head workflows succeeded.
- Sprint 30 source remained within the exact **46-path** envelope whose sorted-path SHA-256 is `95daaf86ba93ae797fccf3825d65d27acd4f71ee58916898a16fbc83d432a5ce`.
- Canonical source migrations are exactly **#1 through #9**. Migration #9 adds one tenant-scoped TOTP-factor row per identity with encrypted secret ciphertext and monotonic accepted-time-step replay state.
- The direct TOTP dependency is pinned to `spomky-labs/otphp` **11.5.0**; oneQay does not implement custom TOTP/HMAC/Base32 cryptography.
- `ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED` remains source-default **false** and Sprint 29–30 delivery remains bounded to **Local/Test/CI**.
- For an armed protected-control principal, password verification alone does not establish the full privileged session. Restricted enrollment/challenge state is used until successful confirmed TOTP challenge establishes full session MFA evidence.
- TOTP secrets are Restricted, encrypted at rest, context-bound to tenant + identity, and never stored as plaintext. Accepted TOTP time steps advance monotonically to deny replay.
- Technical Preview remains **`NO_SCHEMA_CHANGE`**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Durable persistence remains source-default-disabled with `ONEQAY_PERSISTENCE_ENABLED=false`.
- Password change/reset/recovery, MFA recovery, factor replacement/deletion, multiple factors, WebAuthn/passkeys, federation, API-token authentication, Preview auth activation, and Production auth activation remain separately governed.
- **JRN-003 remains UNRESOLVED**; this reconciliation creates no password/MFA recovery path.
- The next logical governed identity/security concern is **Privileged Reauthentication / Step-Up Session Freshness Foundation**. DEC-006 already requires risk-based reauthentication/step-up for sensitive operations. This concern is **CANDIDATE / NOT AUTHORIZED** until a separate bounded entry gate freezes semantics, freshness evidence, session transitions, routes, exact source envelope, schema decision, and preservation tests.

The detailed factual baseline is `docs/ai/AI_POST_SPRINT_30_CANONICAL_STATE.md`. Historical sections below remain preserved as provenance and must not override this section for current-state interpretation.

Attribution: **Lab | zefry**

## Canonical post-Sprint 28 deployment/runtime reconciliation — 2026-08-18

For current deployment/runtime interpretation, this section supersedes older current-facing M7.5/updater-next-work wording retained below as historical qualification provenance.

Sprint 28 is **COMPLETE / IMPLEMENTED / PUBLISHED** and adds canonical source schema/identity capability without changing deployment authority. Canonical migrations are exactly #1–#8, but Technical Preview remains **`NO_SCHEMA_CHANGE`** and continues excluding application migrations from its governed release artifact. Sprint 26–28 credential verification, login/session establishment, and initial-password-enrollment routes are absent from Preview and Production and remain Local/Test/CI-only.

Production remains **`NO-GO / NOT AUTHORIZED`**. No Production migration execution, authentication activation, session activation, enrollment activation, real-user rollout, or persistence activation is authorized. Updater remains **`DISABLED / UNWIRED`** and the updater/release-control-plane contracts below remain separate from identity credential work. Durable persistence remains default-disabled with `ONEQAY_PERSISTENCE_ENABLED=false`.

The next logical governed identity concern is **First-Control-Principal Bootstrap Credential Foundation**. It requires a separately published bounded entry gate and does not grant deployment, schema, Preview, Production, updater, or release authority.

The authoritative detailed post-Sprint28 state is `docs/ai/AI_POST_SPRINT_28_CANONICAL_STATE.md`. Historical M7.5 and earlier deployment sections below remain provenance and must not override this section.

Attribution: **Lab | zefry**

## Canonical M7.5 lifecycle closure — 2026-08-17

For current Preview/runtime interpretation, this section supersedes the older current-facing M7.5 consolidation retained below as historical planning and pre-qualification state.

The bounded non-Production Technical Preview runtime qualification is now **CLOSED / EVIDENCE_COMPLETE / PUBLISHED** with **29 VERIFIED / 0 BLOCKED**. PR #129 published the final restore and backup/restore evidence; PR #130 published secure retirement of the disposable rehearsal environment without changing the evaluator.

The M7.5 evidence package verifies the bounded runtime, database connectivity/least privilege/transaction/migration boundary, connection/resource visibility, outbound DNS/HTTPS, environment-secret isolation, security boundary, Database Portability Contract conformance, observability logging, PHP CLI, scheduler/cron, rollback/deployment recovery, background/queue execution, database-backed tenant isolation, and isolated backup/restore rehearsal needed by the M7.5 mandatory catalog.

This closure does **not** authorize a new deployment or upgrade runtime. It does not create Production recoverability, universal migration rollback safety, Production Release authority, M7.6 authority, or Production readiness. `lifecycle_authority_created=false` remains true for the M7.5 evidence package.

M7.6, M7.7, Phase 0 Exit, Sprint 14, Release, and Production remain **NOT AUTHORIZED**; production readiness remains **NO-GO**.

The next candidate engineering direction is separately gated Secure Web Updater / release-control-plane architecture using trusted governed artifacts, immutable release directories, stable public bootstrap, private active-release pointer, shared runtime configuration, health gates, and rollback. No such implementation is authorized by this closure.

Attribution: **Lab | zefry**

## Canonical program-state consolidation — 2026-08-16

For current Preview/runtime interpretation, this section supersedes older M7.5/current-target wording retained below as historical planning and pre-qualification state.

The bounded non-Production Technical Preview on P1/cPanel has been materially exercised through governed publication/evidence up to PR #124. The canonical M7.5 evaluator is **26 VERIFIED / 3 BLOCKED**, outcome **BLOCKED / INCOMPLETE**, with `lifecycle_authority_created=false`.

Verified runtime evidence now includes the bounded web runtime, database connectivity/least privilege/transaction/migration boundary, connection/resource visibility, outbound DNS/HTTPS, environment-secret isolation, security boundary, Database Portability Contract conformance, safe observability logging, PHP CLI, scheduler/cron, bounded release rollback/deployment recovery, and short-lived Preview background/queue execution.

The remaining blockers are only:

- `ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED`;
- `ENGINE:TENANT_ISOLATION:PARTIAL`;
- `RUNTIME:BACKUP_RESTORE:PARTIAL`.

The observed rollback/recovery evidence is limited to the governed no-schema-change Technical Preview release rehearsal and must not be interpreted as database restore, universal rollback safety, Release, or Production readiness. Backup/export evidence is not successful restore evidence. M7.6, M7.7, Phase 0 Exit, Sprint 14, Release, and Production remain **NOT AUTHORIZED**; production readiness remains **NO-GO**.

Historical DEC-009/P1/P2 wording and prior qualification snapshots below remain preserved.

## Goals

Deployment harus reproducible, auditable, secure, recoverable, portable, dan tidak mengubah business logic antar environment atau antar officially qualified relational engine profile. Artifact yang sama dipromosikan; konfigurasi, secret, Infrastructure adapter, dan qualified engine profile membedakan environment tanpa mengubah Domain/Application business rules.

## Environments

| Environment | Purpose | Data |
|---|---|---|
| Local | Development | Synthetic |
| Test / CI | Automated validation | Synthetic by default |
| Preview | Production-like rehearsal | Synthetic or separately approved masked data |
| Production | Tenant operation | Real, classified; only after separate production authority |

Historical documentation may use `Staging` as a human-facing label. Under substantive DEC-009, the canonical Stage-1 runtime classification is `Preview`; `Staging` must be explicitly mapped to `Preview` rather than treated as an additional environment class.

Masked data requires an approved process and residual-risk review. Raw production/customer/credential/payment-sensitive data must not be copied into non-production merely for convenience. DEC-011 governs this privacy/data-handling boundary; it does not itself authorize production data processing.

Production access menggunakan least privilege, MFA, approval, audit, dan break-glass procedure.

## Configuration

- Environment variable atau secret manager sebagai sumber konfigurasi runtime.
- `.env` real tidak boleh di-commit.
- Config schema divalidasi saat startup/install.
- Default harus aman; missing critical config menyebabkan fail-closed.
- Feature flag memiliki owner, scope, expiry, audit, dan removal task.
- Relational engine-profile configuration berada di Infrastructure/Configuration boundary dan tidak boleh mengubah business use case.

## Deployment stages

### Stage 1 — Capability-Based Preview

Stage 1 mengikuti substantive DEC-009 **Capability-Based Staged / Hybrid Portability Model**. Environment dipilih berdasarkan pemenuhan capability, bukan kategori hosting.

DEC-005R merekonsiliasi database dependency DEC-009. Mandatory capability mencakup secure public-only document root, HTTPS, environment separation, externalized secrets, scheduler/cron, safe background-execution model where required, controlled file permission, **authorized and runtime-qualified relational engine profile under DEC-005R**, server-side session/cache capability, persistent private storage where required, backup plus verified restore capability, log/correlation access, health/readiness, resource visibility, trusted versioned release artifact, recoverable publication, dan rollback/recovery.

Engine/profile identity atau driver connectivity sendiri bukan runtime qualification. MariaDB 11.4 family adalah Stage-1 profile direction karena repository telah memiliki engine-family/version evidence tersebut, tetapi actual oneQay connectivity, security, limits, transaction semantics, tenant isolation, backup/restore, migration boundary, dan portability-contract evidence tetap harus dikualifikasi.

Preferred build model adalah **Build Once / Deploy Trusted Artifact**. Composer dan Node/build tooling dapat berada di trusted build environment dan tidak wajib tersedia pada runtime host jika artifact terverifikasi sudah membawa dependencies dan compiled assets yang diperlukan.

P1 Shared Hosting / cPanel tetap **CONDITIONAL / NOT SELECTED** dan hanya eligible jika seluruh mandatory Stage-1 capability terbukti. P2 Managed / Hardened VPS or Server adalah **FALLBACK EXECUTION CLASS** bila P1 gagal atau tetap unverifiable pada satu mandatory requirement. Tidak ada provider yang dipilih oleh DEC-009 atau DEC-005R.

Constraint hosting atau database engine tidak boleh masuk ke Domain/Application layer.

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

Substantive DEC-009, reconciled by DEC-005R for the database-engine dependency, menetapkan boundary provider-neutral berikut:

- PHP baseline `>=8.2`; PHP CLI mandatory; exact supported minor/patch mengikuti authorized Laravel/release compatibility matrix.
- HTTPS, secure front controller, rewrite/routing, public-only document root, bounded request/upload/timeout controls, dan trusted proxy policy where applicable.
- An **authorized and qualified relational engine profile under DEC-005R**; MariaDB, MySQL, and PostgreSQL are profile directions, not automatic qualification.
- MariaDB 11.4 family is Stage-1 profile direction subject to actual runtime qualification.
- Least-privilege database credentials, externalized secrets, known connection limits, appropriate TLS, tenant isolation, backup/verified restore, and controlled migration boundary remain mandatory.
- Cron-equivalent scheduler capability; safe worker/background model where authorized workloads require it.
- Server-side Web/PWA session, application cache, and rate-limit/temporary-state capability; Redis is not mandatory for first bounded Stage 1.
- Persistent private storage, backup coverage, isolated restore, release metadata, health/readiness, logging/correlation, and recoverable rollback.
- Secrets remain environment-specific and externalized; no production `.env` or credential belongs in repository/client/logs.
- Domain/Application remain independent from cPanel, specific VPS/web-server/cache/queue/container/cloud providers, and relational-engine vendor identity.

DEC-005R establishes the **ZERO BUSINESS-CODE CHANGE** target between officially qualified relational engine profiles. It does not assert zero Infrastructure/configuration differences and does not itself implement database adapters, cross-engine CI, or DBME.

DEC-009 defines requirements only. It does not authorize infrastructure provisioning, DNS/certificate mutation, M7.5 execution, database/DBME implementation, migration execution, release, production promotion, or Sprint 14.

## Release artifact

Artifact harus memiliki version, commit SHA, build timestamp, compatibility metadata, checksum/signature, migration set when separately authorized, SBOM sesuai maturity, changelog, dan installation/update instruction. Build sekali, promote artifact yang sama.

Where durable relational persistence is later implemented, release compatibility metadata must identify supported/qualified engine profiles without changing the business-code artifact semantics.

## Deployment pipeline

1. verify clean/tagged source;
2. restore dependencies reproducibly;
3. lint, type, test, scan;
4. build artifact;
5. generate checksum/SBOM;
6. qualify target runtime and selected relational engine profile when separately authorized;
7. deploy to Preview only with deployment authority;
8. migrate and smoke test only when separately authorized;
9. approval;
10. backup and preflight production;
11. deploy/migrate only with separate deployment/migration authority;
12. health and business verification;
13. observe and close/rollback.

## Database migration and mobility

Migration memiliki preflight, compatibility window, lock/load estimate, backup, rehearsal, progress signal, verification, reconciliation, dan recovery. Destructive contract migration dipisahkan dari deploy yang menghapus compatibility.

DEC-005R adds a future **oneQay Database Mobility & Migration Engine — DBME** architecture direction for source/target profile discovery, compatibility analysis, dry-run, proven-equivalent physical adaptation, fail-closed unsafe/lossy conversion, controlled data movement, reconciliation, controlled cutover, source retention, and rollback only where genuinely safe.

Neither DEC-005R nor DEC-009 authorizes executable migration/DBME, SQL/DDL, live database connection, credentials, data movement, or Production database mutation.

## Deployment strategies

- Atomic directory/symlink release untuk compatible hosting.
- Equivalent controlled versioned artifact publication may be used where symlink/SSH is unavailable only when recoverability, path safety, release history, rollback, and auditability are proven.
- Rolling/blue-green/canary dipilih sesuai platform dan risk.
- Maintenance mode hanya bila zero-downtime tidak aman; harus memiliki status page/message dan bypass terkontrol.
- Feature flag bukan pengganti incomplete migration safety.
- Direct overwrite of live application files without a recoverable release boundary is unsupported.

## Health verification

Technical checks: process, config, selected relational engine profile/database, cache/queue, storage, external dependency, scheduler, error rate, latency. Business checks: login, tenant isolation, catalog read, controlled transaction smoke, audit, notification/payment callback sesuai environment and only when those capabilities are authorized.

Engine-profile health alone tidak membuktikan Database Portability Contract atau business correctness; qualification evidence remains separate.

## Rollback

Rollback decision memiliki owner dan threshold. Application rollback hanya dilakukan bila schema masih compatible. Jika data telah berubah, gunakan recovery/forward fix yang direhearsal. Semua rollback dicatat dan diikuti verification.

Cross-engine/DBME rollback hanya boleh dinyatakan tersedia apabila source retention, reverse compatibility, data integrity, and operational safety have been proven; otherwise fail forward/recovery strategy must be explicit before cutover.

## Backup and disaster recovery

- Encrypted offsite backup dan retention where appropriate.
- Restore runbook serta periodic rehearsal.
- Backup success alone is not recoverability evidence; successful restore evidence is required.
- Privacy retention/deletion semantics for backup data remain governed by DEC-011.
- RPO/RTO per capability remain separately governed by DEC-012.
- Selected engine-profile qualification must include applicable backup/restore evidence.
- Dependency inventory dan contact tree.
- DR exercise menghasilkan evidence dan task perbaikan.

## Observability and alerts

Pantau availability, error rate, latency, saturation, job backlog/failure, database/engine-profile dependency, storage, external dependencies, auth anomaly, tenant isolation denial, payment/reconciliation, updater, dan backup. Alert harus actionable, memiliki owner/runbook, serta tidak membocorkan data. Detailed retention remains separately governed where applicable by DEC-011.

## Cloudflare operations

DNS/SSL/wildcard/cache operation menggunakan scoped token, validation, idempotency, audit, retry, quota awareness, dan rollback. Cache purge harus sempit; global purge memerlukan approval sesuai risk. This section defines operational controls only and does not authorize Cloudflare or DNS mutation under DEC-009.

## Deployment Definition of Done

Artifact terversi, quality gate lulus, target runtime dan selected relational engine profile qualified where applicable, migration dan backup direhearsal where applicable, approval tersedia, deployment tercatat, health/business checks lulus, monitoring normal, rollback/recovery siap, dan changelog/release record diperbarui. Deployment Definition of Done does not itself grant deployment authority.

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

A successful workflow run is governance evidence only. It is not deployment approval, release authority, Phase 0 exit approval, application source-code authority, or merge authority.

Application deployment remains unavailable until the relevant architecture, technology, security, testing, hosting, deployment, release, and lifecycle decisions receive separate Product Owner approval and execution authority. DEC-005R publication alone does not start M7.5 or deployment.

Attribution: Lab | zefry
