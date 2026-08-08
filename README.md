# oneQay

> **The Future of Intelligent Business Management**

**oneQay** adalah platform business management multi-tenant yang sedang dikembangkan menuju visi **Enterprise Intelligent Business Management Platform**. Visi M6 tersebut bersifat directional dan tidak berarti seluruh capability telah diimplementasikan, disetujui untuk delivery, atau production-ready.

| Informasi | Nilai |
| --- | --- |
| Produk | oneQay |
| Enterprise Vision | Enterprise Intelligent Business Management Platform — Proposed M6 candidate |
| Developer & Product Engineering Entity | Lab \| zefry |
| Repository | `labzefry/oneQay` |
| Source of Truth | GitHub |
| Current delivery phase | Phase 0 — Governance and Discovery: In Progress |
| Active program | M6 — Enterprise Vision Canonicalization |
| Latest published technical capability | Sprint 13 |
| Sprint 14 | Not Authorized |
| Production readiness | NO-GO |

Engineering collaboration tooling is governed separately by `AI_CONSTITUTION.md` and is not product authorship attribution.

## Canonical product name

Nama produk wajib ditulis **oneQay** pada current/future-facing canonical material.

Bentuk `OneQay`, `ONEQAY`, `Oneqay`, dan `oneqay` bukan canonical current product identity. Repository identifier `labzefry/oneQay`, immutable GitHub URLs, SHAs, historical commit messages, branch names, dan quoted historical evidence tidak ditulis ulang hanya untuk normalisasi branding.

## Visi

oneQay diarahkan menjadi **Enterprise Intelligent Business Management Platform** yang dapat berkembang bertahap dari fondasi transaksi dan operasional menjadi business management, enterprise management, intelligence, dan ecosystem platform.

Arah capability tingkat tinggi meliputi:

- POS / Commerce;
- Inventory dan Procurement;
- Finance / Accounting;
- CRM;
- HRM;
- Reporting & Business Intelligence;
- Workflow, Notification, Audit, Search, File/Document, API, Integration, Configuration, Localization, Observability, dan Recovery;
- Public API, Partner Integration, Marketplace, serta Plugin / Extension;
- AI Assistant, AI Insight, AI Recommendation, AI Analytics, dan bounded AI Automation;
- Web Application, PWA, Mobile / Android, Admin Platform, dan public/customer-facing surfaces.

Detail canonical candidate berada di `docs/handbook/ENTERPRISE_VISION.md`.

Capability-map presence tidak memberikan implementation authority.

## Product evolution

M6 menggunakan enam evolution stages konseptual:

1. **E0 — Foundation**
2. **E1 — Core Transaction Platform**
3. **E2 — Business Management**
4. **E3 — Enterprise Management**
5. **E4 — Intelligence**
6. **E5 — Ecosystem**

Stage tersebut bukan release commitment. Setiap bounded implementation tetap memerlukan Product Owner authority dan gate yang berlaku.

## Target platform direction

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

Status masing-masing capability mengikuti `PROJECT_MANIFEST.md`, ADR, roadmap, dan lifecycle authority; daftar di atas bukan bukti implementation readiness.

## Status proyek

Current canonical state:

- Phase 0 — Governance and Discovery: **In Progress**;
- bounded Platform Foundation Sprint 12: **Published**;
- bounded Platform Foundation Sprint 13: **Published**;
- M5.1: **PUBLISHED / COMPLETE**;
- M5.2: **PUBLISHED / ENFORCEMENT COMPLETE**;
- M5.3: **PUBLISHED / COMPLETE** through PR #68;
- M6: **In Progress / bounded Enterprise Vision candidate**;
- Sprint 14: **Not Authorized**;
- final/business/production application implementation: **Blocked unless separately authorized**;
- deployment/release/production migration: **Not Authorized**;
- production readiness: **NO-GO**.

Published bounded foundation source must not be erased by older blanket no-source-code wording. Conversely, existing foundation publication does not imply Phase 0 exit or authority for Sprint 14/business application implementation.

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

Detail dan keputusan yang mengikat berada di `ARCHITECTURE.md`, `PROJECT_MANIFEST.md`, dan Architecture Decision Records di `docs/adr/`.

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

## Canonical documents

Sebelum perubahan material, gunakan dokumen sesuai scope:

1. `PROJECT_MANIFEST.md`
2. `AI_CONSTITUTION.md`
3. `ARCHITECTURE.md`
4. `ROADMAP.md`
5. `TASKS.md`
6. `CHANGELOG.md`
7. `docs/handbook/ENTERPRISE_VISION.md` untuk M6 Enterprise Vision
8. canonical current-state files di `docs/ai/`

Root `AI_SESSION_STATE.md`, `AI_PROJECT_STATE.md`, dan `AI_NEXT_TASK.md` adalah deprecated pointer stubs; canonical mutable state berada di `docs/ai/`.

## Deployment evolution

Business logic harus dapat berevolusi tanpa rewrite domain melalui tahapan:

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

Perpindahan stage membutuhkan evidence dan authority yang sesuai. M6 tidak memberikan deployment authority.

## Installer dan updater

oneQay mempertahankan spesifikasi Installer dan Updater sebagai arah operasional terkontrol. Executable migration, production deployment, release, dan production database modification tetap mengikuti gate terpisah dan tidak diotorisasi oleh M6.

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

Lisensi produk tetap mengikuti status pada `PROJECT_MANIFEST.md` dan file `LICENSE`. Seluruh dependency dan aset pihak ketiga wajib mematuhi lisensi asalnya serta kebutuhan kepatuhan proyek.

Attribution: Lab | zefry
