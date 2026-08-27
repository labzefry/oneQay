# oneQay

## Canonical post-Sprint40 M7.5 preservation closure — 2026-08-27

This current-facing section supersedes older pre-Sprint40/current-state wording retained below as historical provenance. It records repository state only and creates no new implementation or lifecycle authority.

- Canonical `main`: `fe502ee40471633e292606ef203a2f0e90754175`; tree `6b494a9a152539a0e922bb564ff96930ff82d86c`; GitHub signature **verified / valid**.
- Sprint40 **First-Party Session Identity Disablement Revalidation Foundation** source is **IMPLEMENTED / PUBLISHED** through PR #286 as `03e86d4e677632a7516c8f4ed2c34045647b774a`, from qualified source head `c8d0f1ab6477f1c743247a519cbc1e6996365199`.
- The Sprint40 source envelope remains exactly **8 paths** with SHA-256 `a9caf2b68210a38687fee543256aec04dc1e67ee1ef403608f7db69139957ff8`.
- Canonical source migration files are exactly **#1–#14**. Migration #14 exists in source and adds only `first_party_authentication_enabled`; this does **not** authorize or imply schema application in Technical Preview or Production.
- Post-Sprint40 historical-regression preservation is published through PR #295 (Sprint32 horizon) and PR #296 (Sprint39 horizon). The bounded M7.5 seven-workflow correction is published through PR #297 and corrected for canonical-main push behavior through PR #298.
- The governed seven-workflow changed-path fingerprint remains `4784ffca1c940d3fa54a2a3988ead07e2de993bde8d3af2bd41014dbdf905be0`.
- Canonical main-push oracle **M7.5 Technical Preview Release Artifact #307** (run `33040247339`) completed **SUCCESS** on `fe502ee40471633e292606ef203a2f0e90754175`. Full-source tests, historical M7.2/M7.3 fixtures with temporary migration #10–#14 isolation, restoration verification, POS/Preview/background regressions, manifest/checksum validation, deterministic archive reproduction, artifact upload, and tracked-source cleanliness all succeeded.
- The oracle and generated qualification artifact are CI evidence only. **Technical Preview remains `NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED`; Production remains `NO-GO / NOT AUTHORIZED`; updater remains `DISABLED / UNWIRED`; deployment and release remain `NOT AUTHORIZED`.**
- PR #295–#298 changed workflow-governance/preservation behavior only; they did not add application source, apply schema, activate runtime, or grant standing successor authority.
- No post-Sprint40 successor implementation concern is selected or authorized by this reconciliation. Any next concern requires fresh canonical-main verification and separate bounded Product Owner authority.

Attribution: **Lab | zefry**


## Canonical Sprint40 pre-source program state — 2026-08-25

For current identity/session governance, schema selection, workflow preservation, runtime activation, and next-work interpretation, this section supersedes older current-facing sections retained below as historical provenance.

- Sprint 21 through Sprint 39 governed identity/control foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint40 has selected **First-Party Session Identity Disablement Revalidation Foundation** as the next governed concern, but its application source implementation is **NOT YET IMPLEMENTED / NOT AUTHORIZED BY THIS DOCUMENTATION SYNCHRONIZATION**.
- Sprint40 entry-gate PR #268 and schema/source-envelope gate PR #270 are **PUBLISHED**. The gate selects a minimal forward-only migration #14 for later source implementation: `apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php`, adding only non-null boolean `first_party_authentication_enabled` with default `true` to `oneqay_identities`.
- Canonical source migrations on `main` remain exactly **#1 through #13**. Migration #14 is **SELECTED FOR THE LATER SPRINT40 SOURCE STAGE BUT DOES NOT YET EXIST OR APPLY ON CANONICAL `main`**.
- The frozen future Sprint40 source implementation envelope is exactly eight paths with sorted newline-terminated SHA-256 `a9caf2b68210a38687fee543256aec04dc1e67ee1ef403608f7db69139957ff8`.
- Sprint40 source-preservation predecessor PR #271 is **PUBLISHED**; its historical compatibility prerequisites/corrections were published through PR #272 and PR #273. Canonical source-preservation publication commit is `31fe2214312618448356fdae668d6bace215b1a7`.
- Documentation-synchronization preservation predecessor PR #274 is **PUBLISHED** as canonical commit `7be563d66e2f8441cf28dd2ed9ce5d6c0704098f`, tree `adbbce29218e312b243076dc3ee984e68ce79b65`, with verified/valid Git signature. It recognizes only the exact 13-document synchronization fingerprint `b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d`.
- Sprint40 request-time semantics remain fail-closed and server-authoritative: the exact session identity must remain currently eligible; missing, disabled, malformed, or contradictory eligibility evidence must deny access without converting credential/factor epoch, tenant membership, organization/outlet/device access, or caller-supplied selectors into identity authority.
- Technical Preview remains **`NO_SCHEMA_CHANGE` / NOT ACTIVATED FOR SPRINT40**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment and release remain **NOT AUTHORIZED**.
- This synchronization publishes documentation only. It grants no Sprint40 source mutation, migration creation/execution, schema application, route/API addition, runtime activation, Preview/Production activation, updater, deployment, or release authority.
- After this 13-document synchronization is published, the next logical governed stage is the already-frozen Sprint40 eight-path source implementation against a freshly verified canonical `main`; that source stage still requires its own separately bounded authority.

Attribution: **Lab | zefry**

## Canonical post-Sprint 33 program-state reconciliation — 2026-08-20

For current identity, security, recovery, schema, runtime, workflow, and next-work interpretation, this section supersedes older current-facing post-Sprint32 wording retained below as historical provenance.

- Sprint 21 through Sprint 33 governed control/identity foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint 33 Recovery-Bound Password Reset Completion Foundation is published through source PR #213 as `9eba56d92b4b714225d677990ffed93687b0b2cb` with tree `492e723b6343dab518b43645883976ad20f0054c`, parent `c89baa55318dca230cd0ef792df80e3d54b8165d`, and a verified/valid publication signature after **24/24** exact-head pull-request workflows succeeded.
- The qualified Sprint33 source head was `a7a50644cbe67e6f08138c79cf50a9350e8e220d`; source remained exactly **39 paths** with sorted-path SHA-256 `04a1177c12712183a7dda4ae81be1356c0e41294533336c9f999d376c224712a`.
- Sprint33 entry-gate PR #211 and source-envelope gate PR #212 remain published provenance; their authorities and PR #213 merge authority are consumed and grant no standing successor authority.
- Canonical source migrations remain exactly **#1 through #10** and are unchanged by Sprint33. No migration #11 is authorized.
- `ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=false` remains the source default and recovery execution remains bounded to **Local/Test/CI**.
- Sprint32 proof still establishes only `password_reset_required` restricted state for exactly **600 seconds**; Sprint33 binds the consumed server-owned recovery `code_id` into that restricted evidence and exposes only `POST /auth/recovery/password-reset` inside the same bounded recovery arm.
- Reset accepts only opaque `password` input of **12–4096 bytes**, performs no trim/normalization, hashes with `PASSWORD_DEFAULT`, updates only the existing exact credential row, revokes remaining unused recovery codes, and appends exactly one secret-free `password_reset_completed` audit event atomically.
- Credential epoch is derived without schema change from the durable count of `password_reset_completed` rows. Fresh normal login captures the epoch; stale, malformed, negative, future, or post-reset legacy-missing epoch evidence fails closed as applicable.
- Protected-control principals and identities with confirmed privileged TOTP remain ineligible for recovery completion; TOTP secret material is not read, decrypted, replaced, deleted, or mutated.
- Successful reset invalidates the restricted session and regenerates CSRF but establishes no normal/full login, MFA evidence, step-up evidence, or epoch evidence; fresh normal login remains mandatory.
- Technical Preview remains **`NO_SCHEMA_CHANGE`**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Durable persistence remains source-default-disabled with `ONEQAY_PERSISTENCE_ENABLED=false`.
- Authenticated in-session password change, administrative password overwrite, MFA/TOTP recovery and factor lifecycle, protected-control recovery bypass, support/admin bypass, email/SMS recovery delivery, passkeys/WebAuthn, federation, API-token authentication, Preview/Production auth/schema activation, updater activation, deployment, and release remain separately governed.
- Sprint32 + Sprint33 now form a bounded Local/Test/CI end-to-end recovery sequence for eligible non-protected identities without confirmed privileged TOTP, but this does not activate recovery in Technical Preview or Production.
- This reconciliation selects **no new post-Sprint33 implementation concern** and grants no Sprint34, migration #11, source, Preview, Production, updater, deployment, or release authority.

The detailed factual baseline is `docs/ai/AI_POST_SPRINT_33_CANONICAL_STATE.md`. Historical sections below remain preserved as provenance and must not override this section for current-state interpretation.

Attribution: **Lab | zefry**

## Canonical post-Sprint 32 program-state reconciliation — 2026-08-19

For current identity, security, recovery, schema, runtime, workflow, and next-work interpretation, this section supersedes older current-facing post-Sprint30/post-Sprint31 wording retained below as historical provenance.

- Sprint 21 through Sprint 32 governed control/identity foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint 31 Privileged Reauthentication / Step-Up Session Freshness Foundation remains published with exact **300-second** freshness for the `policy_administration` scope and its source-default-disabled Local/Test/CI boundary.
- Sprint 32 Authentication Recovery / JRN-003 Recovery Proof Foundation is published through source PR #208 as `914f93f8636bbd0901c61d8a8f14ad69c2c8fbfe` with tree `89f8dcea209ea912ba2539f3c6224a3a0519c8f7`, parent `7f2cc64e5a85158fb24cf03b61d2b36ead73190a`, and a verified/valid publication signature after **24/24** exact-head pull-request workflows succeeded.
- Sprint 32 source remained within the exact **32-path** envelope whose sorted-path SHA-256 is `db230ab3b77fff67f0bd12d7d7b615146d9d9df9a0af12014214e1862e9f6867`.
- Canonical source migrations are exactly **#1 through #10**. Migrations #1–#9 remain immutable. Migration #10 creates only `oneqay_identity_recovery_codes` and `oneqay_identity_recovery_audit`. No migration #11 is authorized.
- `ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=false` remains the source default and Sprint 32 recovery execution remains bounded to **Local/Test/CI**.
- Successful recovery-code rotation issues exactly **8** `rq1.<22-char selector>.<43-char secret>` codes, persists no plaintext recovery secret/code, and uses SHA-256 digest verification with `hash_equals` plus secret-free audit evidence.
- Recovery-code rotation and proof are atomic; same-code replay/concurrency is fail-closed with at most one winner.
- Successful recovery proof establishes only the restricted `password_reset_required` session for exactly **600 seconds**. It does **not** establish a normal/full authenticated session, does not populate the five canonical Sprint27 full-session keys, and does not read/decrypt the TOTP secret.
- Technical Preview remains **`NO_SCHEMA_CHANGE`**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Durable persistence remains source-default-disabled with `ONEQAY_PERSISTENCE_ENABLED=false`.
- Password reset/change/overwrite, automatic/full login from recovery proof, MFA/TOTP recovery, factor replacement/deletion, protected-control recovery, support/admin bypass, email/SMS recovery, passkeys, federation, API-token authentication, Preview/Production auth/schema activation, updater activation, deployment, and release authority remain separately governed and **NOT AUTHORIZED** by Sprint 32 or this reconciliation.
- Sprint 32 publishes the JRN-003 **recovery-proof foundation** only; this reconciliation does not claim end-to-end password recovery completion because password reset/change/overwrite remain excluded.
- This reconciliation selects **no new post-Sprint32 implementation concern** and grants no Sprint33, migration #11, source, Preview, Production, updater, deployment, or release authority. Any subsequent source work requires a separately bounded Product Owner entry gate.

The detailed factual baseline is `docs/ai/AI_POST_SPRINT_32_CANONICAL_STATE.md`. Historical sections below remain preserved as provenance and must not override this section for current-state interpretation.

Attribution: **Lab | zefry**

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

## Canonical post-Sprint 28 program-state consolidation — 2026-08-18

For current project, lifecycle, and next-work interpretation, this section supersedes every older current-facing milestone/status/next-work statement retained below as historical provenance.

- Sprint 21 through Sprint 28 governed foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint 28 source publication PR #188 was squash-published as `b012262b0028c21c7662d5a9edec3cbf249bba5e`; post-Sprint28 canonical reconciliation PR #189 was squash-published as `68a9b5736a3fc169b50984857954322b169bc42e`.
- Published identity/control progression now includes durable role/permission policy, policy administration, initial tenant-administrator provisioning, protected-control administrator lifecycle, policy-administration delivery, first-party credential verification, first-party login/session establishment, and first-party initial password enrollment.
- Canonical source migrations are exactly **#1 through #8**; migrations #1–#7 remain immutable and migration #8 is the additive forward-only initial-password-enrollment migration.
- First-party credential verification, login/session establishment, and initial password enrollment remain bounded to **Local/Test/CI** under their published runtime and persistence gates.
- Technical Preview remains **`NO_SCHEMA_CHANGE`** and does not receive Sprint 26–28 credential/login/enrollment authority.
- Production remains **`NO-GO / NOT AUTHORIZED`**.
- Updater remains **`DISABLED / UNWIRED`**.
- Durable application persistence remains default-disabled with **`ONEQAY_PERSISTENCE_ENABLED=false`**.
- The next logical governed identity concern is **First-Control-Principal Bootstrap Credential Foundation**. It requires a new bounded entry gate before any source implementation and is **NOT AUTHORIZED** by this documentation consolidation.

The authoritative detailed post-Sprint28 publication record is `docs/ai/AI_POST_SPRINT_28_CANONICAL_STATE.md`. Historical M7.5/updater and earlier milestone sections below remain preserved as provenance but must not override this section for current-state interpretation.

Attribution: **Lab | zefry**

## Canonical M7.5 lifecycle closure — 2026-08-17

For current M7/lifecycle interpretation, this section supersedes the older current-facing consolidation retained below as historical checkpoint/provenance.

- M7.0–M7.4A: **DONE / PUBLISHED**.
- M7.5 Preview Runtime Qualification: **CLOSED / EVIDENCE_COMPLETE / PUBLISHED**.
- Canonical M7.5 evaluator after PR #129 and cleanup publication PR #130: **29 VERIFIED / 0 BLOCKED**.
- `lifecycle_authority_created=false` remains true for the M7.5 evidence package; this documentation closure does not authorize any later lifecycle stage.
- `ENGINE:TENANT_ISOLATION`, `ENGINE:RESTORE_VERIFIED`, and `RUNTIME:BACKUP_RESTORE` are **VERIFIED** within the bounded non-Production Technical Preview evidence catalog.
- M7.6: **NOT AUTHORIZED**.
- M7.7: **NOT AUTHORIZED**.
- Phase 0: **IN PROGRESS**; Phase 0 Exit: **NOT APPROVED**.
- Sprint 14, Release, and Production: **NOT AUTHORIZED**.
- Production readiness: **NO-GO**.

The next engineering direction after this closure is the separately gated Secure Web Updater architecture foundation and release-control-plane design. No source, workflow, deployment, cPanel, database/schema/migration, restore, M7.6, M7.7, Release, or Production authority is created by this closure.

Historical SHAs, PRs, evidence snapshots, and prior checkpoint wording below remain preserved and must be interpreted as historical where superseded by this closure.

Attribution: **Lab | zefry**

## Canonical program-state consolidation — 2026-08-16

For current M7/lifecycle interpretation, this section supersedes older current-facing wording retained below as historical checkpoint/provenance.

- M7.0: **DONE / PUBLISHED**.
- M7.1: **DONE / PUBLISHED** through PR #92.
- M7.2: **DONE / PUBLISHED** through PR #93.
- M7.3: **DONE / PUBLISHED** through PR #94.
- M7.4: **DONE / PUBLISHED** through PR #96.
- M7.4A: **DONE / PUBLISHED** through PR #98.
- M7.5: **IN PROGRESS / QUALIFICATION MATERIAL PROGRESS**.
- Canonical M7.5 evaluator after PR #124: **26 VERIFIED / 3 BLOCKED**.
- M7.5 overall qualification: **BLOCKED / INCOMPLETE**.
- Remaining blockers: `ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED`, `ENGINE:TENANT_ISOLATION:PARTIAL`, and `RUNTIME:BACKUP_RESTORE:PARTIAL`.
- `lifecycle_authority_created=false`.
- M7.6: **NOT AUTHORIZED**.
- M7.7: **NOT AUTHORIZED**.
- Phase 0: **IN PROGRESS**; Phase 0 Exit: **NOT APPROVED**.
- Sprint 14, Release, and Production: **NOT AUTHORIZED**.
- Production readiness: **NO-GO**.

Historical SHAs, PRs, evidence snapshots, and prior checkpoint wording below remain preserved and must not be reinterpreted as newer than this consolidation.

> **The Future of Intelligent Business Management**

**oneQay** adalah platform business management multi-tenant dengan Enterprise Vision **Enterprise Intelligent Business Management Platform** yang telah disetujui melalui GOV-051. Persetujuan visi tersebut tidak berarti seluruh capability telah diimplementasikan, disetujui untuk delivery, atau production-ready.

| Informasi | Nilai |
| --- | --- |
| Produk | oneQay |
| Kategori | Enterprise SaaS POS & ERP Platform |
| Enterprise Vision | Approved — Enterprise Intelligent Business Management Platform |
| Developer & Product Engineering Entity | Lab \| zefry |
| Repository | `labzefry/oneQay` |
| Source of Truth | GitHub |
| Current delivery phase | Phase 0 — Governance and Discovery: In Progress |
| Current engineering workstream | M7 — Technical Preview Implementation Enablement |
| Latest completed micro-milestone | M7.4A — Technical Preview Interaction Layer |
| Next gated micro-milestone | M7.5 — Preview Runtime Qualification — Blocked pending actual sanitized P2 target evidence and DEC-009 capability verification |
| Sprint 14 | Not Authorized |
| Production readiness | NO-GO |

Engineering collaboration tooling is governed separately by `AI_CONSTITUTION.md` and is not product authorship attribution.

## Canonical product name

Nama produk wajib ditulis **oneQay** pada current/future-facing canonical material.

Bentuk `OneQay`, `ONEQAY`, `Oneqay`, dan `oneqay` bukan canonical current product identity. Repository identifier `labzefry/oneQay`, immutable GitHub URLs, SHAs, historical commit messages, branch names, dan quoted historical evidence tidak ditulis ulang hanya untuk normalisasi branding.

## Visi

oneQay adalah **Enterprise Intelligent Business Management Platform** yang dapat digunakan mulai dari usaha tunggal hingga organisasi multi-cabang dan multi-tenant, lalu berkembang bertahap dari fondasi transaksi dan operasional menjadi business management, enterprise management, intelligence, dan ecosystem platform tanpa mengganti fondasi business logic ketika infrastruktur bertumbuh.

Tujuan arah produk adalah menghadirkan platform yang:

- mudah digunakan untuk operasional harian;
- aman untuk data bisnis dan transaksi;
- modular tanpa kehilangan konsistensi domain;
- dapat dikembangkan tanpa ketergantungan berlebihan pada infrastruktur;
- API-first dan integration-ready;
- dapat diobservasi, diuji, dipulihkan, dan diperbarui secara terkendali;
- extensible melalui boundary yang disetujui;
- AI-ready dengan deterministic controls dan human accountability;
- memiliki tata kelola pengembangan yang dapat dibuktikan melalui GitHub.

Detail canonical Enterprise Vision berada di `docs/handbook/ENTERPRISE_VISION.md`. M6 adalah historical completed work; substantive Enterprise Vision kemudian Approved melalui GOV-051.

## Enterprise Capability Map direction

M6 mengelompokkan capability directional ke dalam:

- **Core Business Platform:** Tenant & Organization, Identity & Access, Master Data, POS / Commerce, Inventory, Procurement, Finance / Accounting, CRM, HRM, Reporting & Business Intelligence;
- **Platform Capabilities:** Workflow, Notification, Audit, File / Document, Search, API, Integration, Webhook / Event Integration, Configuration, Localization, Observability, Recovery & Operational Control;
- **Extensibility:** Marketplace, Plugin / Extension, Public API, Partner Integration;
- **AI Platform:** AI Assistant, AI Insight, AI Automation, AI Recommendation, AI Analytics, AI Gateway / Policy Boundary;
- **Channels:** Web Application, PWA, Mobile / Android, Admin Platform, public/customer-facing surfaces, dan API/partner consumers.

Capability-map presence tidak memberikan implementation authority.

## Product evolution

M6 menetapkan enam evolution stages konseptual:

1. **E0 — Foundation**
2. **E1 — Core Transaction Platform**
3. **E2 — Business Management**
4. **E3 — Enterprise Management**
5. **E4 — Intelligence**
6. **E5 — Ecosystem**

Stage tersebut bukan release commitment. Setiap bounded implementation tetap memerlukan Product Owner authority dan gate yang berlaku.

## Target platform

oneQay diarahkan untuk mendukung secara bertahap:

- Web Application
- Progressive Web App (PWA)
- Android / Mobile
- REST API
- Public API
- Admin Platform
- Landing Website
- Content Management System (CMS)
- Marketplace
- Plugin / Extension System
- AI Platform capabilities

Status masing-masing capability mengikuti `PROJECT_MANIFEST.md`, ADR, roadmap, dan lifecycle authority; daftar tersebut bukan bukti implementation readiness atau janji seluruh platform tersedia pada rilis pertama.

## Status proyek

Current canonical state:

- Phase 0 — Governance and Discovery: **In Progress**;
- bounded Platform Foundation Sprint 12: **Published**;
- bounded Platform Foundation Sprint 13: **Published**;
- M5.1: **PUBLISHED / COMPLETE**;
- M5.2: **PUBLISHED / ENFORCEMENT COMPLETE**;
- M5.3: **PUBLISHED / COMPLETE** through PR #68;
- M6: **PUBLISHED / COMPLETE** as historical Enterprise Vision canonicalization work;
- GOV-051 Enterprise Vision: **APPROVED / DECISION COMPLETE**;
- M7.0 — Controlled Implementation Bridge: **DONE / PUBLISHED**;
- M7.1 — Application Skeleton & Configuration Boundary: **DONE / PUBLISHED** through PR #92;
- M7.2 — Tenant Kernel & Isolation Foundation: **DONE / PUBLISHED** through PR #93;
- M7.3 — Identity / Organization / Outlet / Device Minimum: **DONE / PUBLISHED** through PR #94;
- M7.4 — POS Core Synthetic Vertical Slice: **DONE / PUBLISHED** through PR #96;
- M7.4A — Technical Preview Interaction Layer: **DONE / PUBLISHED** through PR #98;
- M7.5 — Preview Runtime Qualification: **BLOCKED / NOT AUTHORIZED** pending actual sanitized P2 target evidence and DEC-009 capability verification;
- M7.6 — Preview Deployment / Recovery Rehearsal: **BLOCKED**;
- M7.7 — Technical Preview Acceptance: **BLOCKED**;
- Sprint 14: **Not Authorized**;
- final/business/production application implementation: **Blocked unless separately authorized**;
- deployment/release/production migration: **Not Authorized**;
- production readiness: **NO-GO**.

M7.0–M7.4A publication facts do not imply Phase 0 exit, Sprint 14 authority, M7.5 runtime-qualification authority, deployment, release, or Production authority. M7.5 remains gated by actual sanitized P2 target evidence and DEC-009 capability verification.

Broader final/business application implementation tetap memerlukan keputusan minimum yang relevan untuk scope-nya, termasuk MVP boundary, domain/architecture decisions, multi-tenant/data controls, security baseline, database/migration governance, API contracts, testing/quality gates, deployment environment, dan release/recovery controls.

## Prinsip arsitektur

Pengembangan oneQay mengikuti prinsip berikut:

- **Modular Monolith First** — mengutamakan kesederhanaan operasional dengan batas modul yang tegas dan jalur evolusi yang jelas.
- **Clean Architecture** — business logic tidak bergantung pada framework, database, UI, atau penyedia infrastruktur.
- **Domain-Driven Design** — model dan bahasa sistem mengikuti domain bisnis.
- **SOLID** — komponen memiliki tanggung jawab yang jelas dan dapat dikembangkan secara aman.
- **API First** — kontrak API dirancang, direview, dan diversi sebelum implementasi konsumen.
- **Multi-Tenant by Design** — setiap data tenant memiliki konteks tenant yang tervalidasi dan tidak boleh bocor lintas tenant.
- **Secure by Default** — autentikasi, otorisasi, validasi, audit, secret management, dan perlindungan data menjadi bagian desain.
- **Observable and Testable** — logging, metrics, tracing, health check, serta automated testing direncanakan sejak awal.
- **Cloud Ready, Infrastructure Independent** — perpindahan lingkungan tidak mengubah business logic.
- **Event-Driven Ready** — modul dapat menerbitkan dan mengonsumsi domain event tanpa mewajibkan microservices pada fase awal.
- **Human Accountable AI** — AI tidak boleh menjadi sumber otorisasi atau mutation irreversible tanpa deterministic controls dan human accountability.

Detail dan keputusan yang mengikat berada di `ARCHITECTURE.md`, `PROJECT_MANIFEST.md`, serta Architecture Decision Records di `docs/adr/`.

## Multi-tenant

Setiap tenant sekurang-kurangnya memiliki:

- Tenant ID
- nama perusahaan
- nama toko atau unit bisnis
- domain atau subdomain akses
- subscription
- configuration
- timezone
- currency
- locale

**Tenant ID adalah batas isolasi data utama.** Domain dan subdomain hanya menjadi media akses, bukan sumber otorisasi tunggal. Setiap request, query, cache key, job, file, event, log yang relevan, dan operasi administratif wajib mempertahankan tenant context.

Model isolasi, indeks, constraint, backup, restore, serta pengujian anti-kebocoran lintas tenant dirinci melalui `DATABASE.md`, `SECURITY.md`, dan ADR yang berlaku.

## GitHub sebagai Single Source of Truth

Seluruh artefak resmi dikelola melalui GitHub, termasuk:

- source code;
- dokumentasi;
- roadmap dan backlog;
- issue dan diskusi teknis;
- pull request dan review;
- CI/CD;
- release, tag, dan changelog;
- keputusan arsitektur;
- lifecycle authority;
- kontrol perubahan dan audit history.

Perubahan yang tidak terlacak di GitHub tidak dianggap sebagai bagian resmi proyek.

### Branch strategy

| Branch | Kegunaan |
| --- | --- |
| `main` | Kondisi stabil dan dapat dirilis sesuai gate |
| `develop` | Integrasi bila diaktifkan oleh release policy |
| `feature/*` | Pengembangan fitur yang diotorisasi |
| `release/*` | Stabilisasi kandidat rilis |
| `hotfix/*` | Perbaikan kritis dari versi produksi |
| `bugfix/*` | Perbaikan defect non-darurat |
| `experiment/*` | Eksperimen yang belum menjadi komitmen produk |
| `agent/*` | Bounded ChatGPT-assisted work |

Protection rules, kebutuhan `develop`, dan release flow mengikuti `CONTRIBUTING.md`, `RELEASE.md`, serta repository ruleset yang aktif.

### Conventional Commits

Commit menggunakan format:

```text
<type>(optional-scope): deskripsi singkat
```

Type yang diizinkan:

- `feat:`
- `fix:`
- `docs:`
- `refactor:`
- `perf:`
- `test:`
- `build:`
- `ci:`
- `security:`
- `chore:`

Setiap commit harus atomik, dapat ditinjau, dan menjelaskan satu tujuan perubahan yang koheren.

## Governance lifecycle

Perubahan material mengikuti bounded lifecycle:

1. Product Owner START authority untuk scope kerja bila diperlukan;
2. bounded branch;
3. Draft PR;
4. exact-head validation;
5. independent review;
6. separate Product Owner READY authority;
7. separate exact-head Product Owner MERGE authority;
8. repository protection dan required checks;
9. publication verification.

Reviewer approval bukan Product Owner lifecycle authority.

## Required protected checks

Current protected contexts published through M5.2:

1. `governance-validation`
2. `markdown-lint`
3. `secret-scan`
4. `php-foundation-regression`
5. `product-owner-merge-authority`

## Tata kelola perubahan

Sebelum perubahan material, gunakan dokumen sesuai scope:

1. `PROJECT_MANIFEST.md`
2. `AI_CONSTITUTION.md`
3. `ARCHITECTURE.md`
4. `ROADMAP.md`
5. `TASKS.md`
6. `CHANGELOG.md`
7. `docs/handbook/ENTERPRISE_VISION.md` untuk canonical Enterprise Vision
8. canonical current-state files di `docs/ai/`

Root `AI_SESSION_STATE.md`, `AI_PROJECT_STATE.md`, dan `AI_NEXT_TASK.md` adalah deprecated pointer stubs; canonical mutable state berada di `docs/ai/`.

Setiap perubahan wajib memperbarui dokumentasi yang terdampak. Minimal manifest, tasks, dan changelog diperiksa; dokumen architecture/API/database/security/deployment/testing/UI/installer/updater/release diperbarui sesuai dampak.

Breaking change, penghapusan modul, perubahan skema tanpa migration, perubahan API tanpa versioning, hardcoded secret, dan pengabaian dokumentasi tidak diperbolehkan.

## Engineering handbook

Handbook tetap living documentation. Daftar berikut adalah baseline document set yang telah menjadi bagian governance repository; status delivery/proyek aktual harus dibaca dari manifest, roadmap, tasks, changelog, dan `docs/ai/`.

| Urutan | Dokumen | Tujuan |
| ---: | --- | --- |
| 1 | `README.md` | Orientasi, visi, ruang lingkup, dan navigasi proyek |
| 2 | `PROJECT_MANIFEST.md` | Identitas teknis dan inventaris kapabilitas proyek |
| 3 | `AI_CONSTITUTION.md` | Aturan permanen untuk ChatGPT pada proyek |
| 4 | `ARCHITECTURE.md` | Arsitektur logis, deployment, dan batas modul |
| 5 | `ROADMAP.md` | Tahapan produk dan engineering |
| 6 | `CODING_STANDARDS.md` | Standar implementasi lintas platform |
| 7 | `DATABASE.md` | Model data, tenancy, migration, dan integritas |
| 8 | `API_SPEC.md` | Kontrak, versioning, error, dan governance API |
| 9 | `SECURITY.md` | Baseline keamanan dan respons insiden |
| 10 | `DEPLOYMENT.md` | Environment, CI/CD, backup, dan rollback |
| 11 | `TESTING.md` | Strategi testing dan quality gate |
| 12 | `UI_GUIDELINE.md` | Design system, aksesibilitas, dan UX |
| 13 | `INSTALLER.md` | Spesifikasi Installer Wizard |
| 14 | `UPDATER.md` | Spesifikasi Auto Updater yang aman |
| 15 | `CONTRIBUTING.md` | Workflow kontribusi dan pull request |
| 16 | `RELEASE.md` | Versioning, release, rollback, dan EOL |
| 17 | `TASKS.md` | Backlog dan status pekerjaan terkontrol |
| 18 | `CHANGELOG.md` | Riwayat perubahan berbasis versi |
| 19 | `docs/handbook/ENTERPRISE_VISION.md` | Approved Enterprise Vision, capability map, dan conceptual evolution |

Struktur dokumentasi lanjutan:

```text
docs/
├── architecture/
├── diagrams/
├── database/
├── api/
├── deployment/
├── uiux/
├── adr/
└── handbook/
```

File kosong dan placeholder tanpa nilai informasi harus dihindari.

## Deployment evolution

oneQay harus dapat berevolusi melalui tahapan berikut tanpa mengubah business logic:

```text
Shared Hosting (cPanel)
    ↓
VPS
    ↓
Dedicated Server
    ↓
Docker
    ↓
Cloud
    ↓
Kubernetes
```

Setiap tahap harus memiliki entry criteria, exit criteria, backup, rollback, observability, security controls, dan perkiraan beban operasional. Perpindahan stage membutuhkan evidence serta authority yang sesuai. Historical M6 work tidak memberikan deployment authority, dan M7.0–M7.4A publication juga tidak memberikan deployment authority.

## Integrasi Cloudflare

Arsitektur dapat menyediakan controlled Cloudflare integration apabila scope dan decision yang berlaku mengotorisasinya, misalnya untuk DNS record tenant, wildcard DNS, SSL, cache purge, zone validation, serta audit operation.

API token dan secret wajib disimpan melalui environment variable atau secret manager. Secret dilarang disimpan di source code, repository, log, database tanpa proteksi yang disetujui, atau response API. Tidak ada authority implementasi provider baru dari reconciliation ini.

## Installer dan updater

oneQay mempertahankan spesifikasi:

- **Installer Wizard** untuk pemeriksaan environment, konfigurasi database, pembuatan administrator, environment generation, migration, seeding, optimization, dan installation report;
- **Auto Updater** untuk version check, release download, backup, integrity verification, maintenance mode, installation, migration, optimization, health verification, serta recovery/rollback.

Executable migration, production deployment, release, dan production database modification tetap mengikuti gate terpisah dan tidak diotorisasi oleh M7.0–M7.4A publication.

## Cara berkontribusi

1. pilih satu issue/task dengan scope dan authority yang jelas;
2. gunakan bounded branch sesuai jenis pekerjaan;
3. pertahankan exact-head review dan lifecycle evidence;
4. ubah hanya file yang diperlukan oleh scope;
5. sertakan alasan, dampak, risiko, dan validasi pada pull request;
6. pastikan tautan, istilah, dan canonical brand `oneQay` konsisten;
7. minta independent review sesuai risk;
8. jangan mark Ready atau merge tanpa Product Owner lifecycle authority yang berlaku;
9. perbarui living documentation yang terdampak.

Detail final berada di `CONTRIBUTING.md`.

## Definition of Done untuk dokumentasi

Dokumen dianggap selesai apabila:

- tujuan dan audiensnya jelas;
- nama produk menggunakan canonical `oneQay` untuk current/future-facing text;
- istilah konsisten dengan dokumen kanonis;
- aturan normatif menggunakan bahasa yang tegas;
- asumsi dan keputusan yang belum final ditandai;
- tidak mengandung secret atau informasi sensitif;
- tautan internal dan struktur heading valid;
- dampak keamanan, multi-tenancy, operasional, testing, dan kompatibilitas dipertimbangkan;
- perubahan dapat ditelusuri melalui commit atau pull request;
- dokumen terkait diperbarui bila diperlukan;
- telah direview oleh pemilik keputusan yang relevan.

## Lisensi

Lisensi produk mengikuti status pada `PROJECT_MANIFEST.md` dan file `LICENSE`. Seluruh dependency dan aset pihak ketiga wajib mematuhi lisensi asalnya serta kebutuhan kepatuhan proyek.

Attribution: Lab | zefry
