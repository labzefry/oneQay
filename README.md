# oneQay

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
| Latest completed micro-milestone | M7.4 — POS Core Synthetic Vertical Slice |
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
- M7.5 — Preview Runtime Qualification: **BLOCKED / NOT AUTHORIZED** pending actual sanitized P2 target evidence and DEC-009 capability verification;
- M7.6 — Preview Deployment / Recovery Rehearsal: **BLOCKED**;
- M7.7 — Technical Preview Acceptance: **BLOCKED**;
- Sprint 14: **Not Authorized**;
- final/business/production application implementation: **Blocked unless separately authorized**;
- deployment/release/production migration: **Not Authorized**;
- production readiness: **NO-GO**.

M7.0–M7.4 publication facts do not imply Phase 0 exit, Sprint 14 authority, M7.5 runtime-qualification authority, deployment, release, or Production authority. M7.5 remains gated by actual sanitized P2 target evidence and DEC-009 capability verification.

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

Setiap tahap harus memiliki entry criteria, exit criteria, backup, rollback, observability, security controls, dan perkiraan beban operasional. Perpindahan stage membutuhkan evidence serta authority yang sesuai. Historical M6 work tidak memberikan deployment authority, dan M7.0–M7.4 publication juga tidak memberikan deployment authority.

## Integrasi Cloudflare

Arsitektur dapat menyediakan controlled Cloudflare integration apabila scope dan decision yang berlaku mengotorisasinya, misalnya untuk DNS record tenant, wildcard DNS, SSL, cache purge, zone validation, serta audit operation.

API token dan secret wajib disimpan melalui environment variable atau secret manager. Secret dilarang disimpan di source code, repository, log, database tanpa proteksi yang disetujui, atau response API. Tidak ada authority implementasi provider baru dari reconciliation ini.

## Installer dan updater

oneQay mempertahankan spesifikasi:

- **Installer Wizard** untuk pemeriksaan environment, konfigurasi database, pembuatan administrator, environment generation, migration, seeding, optimization, dan installation report;
- **Auto Updater** untuk version check, release download, backup, integrity verification, maintenance mode, installation, migration, optimization, health verification, serta recovery/rollback.

Executable migration, production deployment, release, dan production database modification tetap mengikuti gate terpisah dan tidak diotorisasi oleh M7.0–M7.4 publication.

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