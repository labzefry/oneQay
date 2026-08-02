# OneQay

> **The Future of Intelligent Business Management**

**OneQay** adalah platform **Enterprise SaaS POS & ERP** terpadu yang dirancang untuk membantu bisnis mengelola transaksi, operasional, pelanggan, persediaan, keuangan, integrasi, dan pengambilan keputusan dalam satu ekosistem yang aman, modular, serta siap berkembang.

| Informasi | Nilai |
|---|---|
| Produk | OneQay |
| Kategori | Enterprise SaaS POS & ERP Platform |
| Author | Lab \| Zefry |
| Repository | GitHub sebagai Single Source of Truth |
| Status | Handbook 1.0 draft complete / menunggu review dan publikasi |
| Versi handbook | 1.0 |

## Visi

OneQay dibangun sebagai fondasi intelligent business management yang dapat digunakan mulai dari usaha tunggal hingga organisasi multi-cabang dan multi-tenant. Sistem harus tetap konsisten ketika bertumbuh dari shared hosting menuju VPS, dedicated server, container, cloud, dan Kubernetes.

Tujuan utamanya adalah menghadirkan platform yang:

- mudah digunakan untuk operasional harian;
- aman untuk data bisnis dan transaksi;
- modular tanpa kehilangan konsistensi domain;
- dapat dikembangkan tanpa ketergantungan berlebihan pada infrastruktur;
- siap diintegrasikan melalui API;
- dapat diobservasi, diuji, dipulihkan, dan diperbarui secara terkendali;
- memiliki tata kelola pengembangan yang dapat dipahami manusia maupun AI coding agent.

## Target platform

OneQay dirancang untuk mendukung:

- Web Application
- Progressive Web App (PWA)
- Android Native
- REST API
- Public API
- Admin Dashboard
- Landing Website
- Content Management System (CMS)
- Marketplace
- Plugin System
- AI Assistant

Daftar tersebut adalah ruang lingkup produk tingkat tinggi, bukan janji bahwa seluruh platform tersedia pada rilis pertama. Prioritas implementasi ditetapkan melalui roadmap, keputusan arsitektur, dan backlog terversi.

## Status proyek

Repository ini berada pada fase **engineering handbook-first**. Fokus awal bukan membuat source code aplikasi, melainkan menetapkan konstitusi, arsitektur, standar, proses, dan kontrol kualitas yang akan menjadi acuan permanen pengembangan OneQay.

Source code produksi tidak boleh dimulai sebelum keputusan minimum berikut terdokumentasi dan disetujui:

1. batas domain dan ruang lingkup MVP;
2. arsitektur aplikasi dan strategi modularisasi;
3. model multi-tenant dan isolasi data;
4. baseline keamanan;
5. strategi database dan migration;
6. kontrak API dan versioning;
7. standar testing dan quality gate;
8. lingkungan deployment tahap pertama;
9. proses release, rollback, installer, dan updater.

## Prinsip arsitektur

Pengembangan OneQay mengikuti prinsip berikut:

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

Detail dan keputusan yang mengikat akan ditetapkan dalam [ARCHITECTURE.md](ARCHITECTURE.md) serta Architecture Decision Records di `docs/adr/`.

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

Model isolasi, indeks, constraint, backup, restore, serta pengujian anti-kebocoran lintas tenant akan dirinci dalam [DATABASE.md](DATABASE.md) dan [SECURITY.md](SECURITY.md).

## GitHub sebagai Single Source of Truth

Seluruh artefak pengembangan dikelola melalui GitHub, termasuk:

- source code;
- dokumentasi;
- roadmap dan backlog;
- issue dan diskusi teknis;
- pull request dan review;
- CI/CD;
- release, tag, dan changelog;
- keputusan arsitektur;
- kontrol perubahan dan audit history.

Perubahan yang tidak terlacak di GitHub tidak dianggap sebagai bagian resmi dari proyek.

### Branch strategy

| Branch | Kegunaan |
|---|---|
| `main` | Kondisi stabil dan dapat dirilis |
| `develop` | Integrasi perubahan yang telah direview |
| `feature/*` | Pengembangan fitur |
| `release/*` | Stabilisasi kandidat rilis |
| `hotfix/*` | Perbaikan kritis dari versi produksi |
| `bugfix/*` | Perbaikan defect non-darurat |
| `experiment/*` | Eksperimen yang belum menjadi komitmen produk |

Strategi ini merupakan baseline. Protection rules, kebutuhan `develop`, serta release flow final akan ditetapkan dalam [CONTRIBUTING.md](CONTRIBUTING.md) dan [RELEASE.md](RELEASE.md).

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

## Tata kelola perubahan

Sebelum melakukan perubahan, kontributor manusia maupun AI wajib membaca sekurang-kurangnya:

1. `README.md`
2. `PROJECT_MANIFEST.md`
3. `AI_CONSTITUTION.md`
4. `ARCHITECTURE.md`
5. `TASKS.md`
6. `CHANGELOG.md`
7. `ROADMAP.md`

Setiap perubahan wajib memperbarui dokumentasi yang terdampak. Minimal:

- `PROJECT_MANIFEST.md`
- `TASKS.md`
- `CHANGELOG.md`

Dokumen tambahan yang wajib diperbarui sesuai dampaknya:

| Jenis perubahan | Dokumen wajib |
|---|---|
| Arsitektur | `ARCHITECTURE.md` dan ADR terkait |
| API | `API_SPEC.md` |
| Database | `DATABASE.md` dan migration |
| Security | `SECURITY.md` |
| Deployment | `DEPLOYMENT.md` |
| Testing/quality gate | `TESTING.md` |
| UI/UX | `UI_GUIDELINE.md` |
| Installer/updater | `INSTALLER.md` / `UPDATER.md` |
| Release | `RELEASE.md` dan `CHANGELOG.md` |

Breaking change, penghapusan modul, perubahan skema tanpa migration, perubahan API tanpa versioning, hardcoded secret, dan pengabaian dokumentasi tidak diperbolehkan.

## Engineering handbook

Handbook dikembangkan secara bertahap dan diperlakukan sebagai living documentation.

| Urutan | Dokumen | Tujuan | Status |
|---:|---|---|---|
| 1 | `README.md` | Orientasi, visi, ruang lingkup, dan navigasi proyek | Selesai |
| 2 | `PROJECT_MANIFEST.md` | Identitas teknis dan inventaris kapabilitas proyek | Draft selesai |
| 3 | `AI_CONSTITUTION.md` | Aturan permanen untuk AI engineering agent | Draft selesai |
| 4 | `ARCHITECTURE.md` | Arsitektur logis, deployment, dan batas modul | Draft selesai |
| 5 | `ROADMAP.md` | Tahapan produk dan engineering | Draft selesai |
| 6 | `CODING_STANDARDS.md` | Standar implementasi lintas platform | Draft selesai |
| 7 | `DATABASE.md` | Model data, tenancy, migration, dan integritas | Draft selesai |
| 8 | `API_SPEC.md` | Kontrak, versioning, error, dan governance API | Draft selesai |
| 9 | `SECURITY.md` | Baseline keamanan dan respons insiden | Draft selesai |
| 10 | `DEPLOYMENT.md` | Environment, CI/CD, backup, dan rollback | Draft selesai |
| 11 | `TESTING.md` | Strategi testing dan quality gate | Draft selesai |
| 12 | `UI_GUIDELINE.md` | Design system, aksesibilitas, dan UX | Draft selesai |
| 13 | `INSTALLER.md` | Spesifikasi Installer Wizard | Draft selesai |
| 14 | `UPDATER.md` | Spesifikasi Auto Updater yang aman | Draft selesai |
| 15 | `CONTRIBUTING.md` | Workflow kontribusi dan pull request | Draft selesai |
| 16 | `RELEASE.md` | Versioning, release, rollback, dan EOL | Draft selesai |
| 17 | `TASKS.md` | Backlog dan status pekerjaan terkontrol | Draft selesai |
| 18 | `CHANGELOG.md` | Riwayat perubahan berbasis versi | Draft selesai |

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

Folder dibuat ketika dokumen pertamanya diperlukan. File kosong dan placeholder tanpa nilai informasi harus dihindari.

## Deployment evolution

OneQay harus dapat berevolusi melalui tahapan berikut tanpa mengubah business logic:

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

Setiap tahap harus memiliki entry criteria, exit criteria, backup, rollback, observability, security controls, dan perkiraan beban operasional. Detailnya akan ditetapkan dalam [DEPLOYMENT.md](DEPLOYMENT.md).

## Integrasi Cloudflare

Arsitektur akan menyediakan modul integrasi Cloudflare API untuk:

- membuat, memperbarui, dan menghapus DNS record tenant;
- mendukung wildcard DNS;
- mengelola kebutuhan SSL;
- melakukan cache purge;
- memvalidasi zone;
- mencatat audit operation dan kegagalan integrasi.

API token dan secret wajib disimpan melalui environment variable atau secret manager. Secret dilarang disimpan di source code, repository, log, database tanpa proteksi yang disetujui, atau response API.

## Installer dan updater

OneQay akan memiliki spesifikasi:

- **Installer Wizard** untuk pemeriksaan environment, konfigurasi database, pembuatan administrator, environment generation, migration, seeding, optimization, dan installation report.
- **Auto Updater** untuk version check, release download, backup, integrity verification, maintenance mode, installation, migration, optimization, health verification, serta rollback.

Workflow final, trust model, signing, kompatibilitas versi, recovery, dan failure handling akan dirinci sebelum implementasi dalam [INSTALLER.md](INSTALLER.md) serta [UPDATER.md](UPDATER.md).

## Cara berkontribusi

Pada fase handbook:

1. pilih satu dokumen atau keputusan dengan scope yang jelas;
2. buat issue jika keputusan membutuhkan diskusi;
3. gunakan branch sesuai jenis pekerjaan;
4. ubah hanya dokumen yang berada dalam scope;
5. sertakan alasan, dampak, risiko, dan validasi pada pull request;
6. pastikan tautan dan istilah konsisten;
7. minta review sebelum merge;
8. perbarui living documentation yang terdampak.

Petunjuk final akan tersedia dalam [CONTRIBUTING.md](CONTRIBUTING.md).

## Definition of Done untuk dokumentasi

Dokumen dianggap selesai apabila:

- tujuan dan audiensnya jelas;
- istilah konsisten dengan dokumen kanonis;
- aturan normatif menggunakan bahasa yang tegas;
- asumsi dan keputusan yang belum final ditandai;
- tidak mengandung secret atau informasi sensitif;
- tautan internal dan struktur heading valid;
- dampak keamanan, multi-tenancy, operasional, testing, dan kompatibilitas telah dipertimbangkan;
- perubahan dapat ditelusuri melalui commit atau pull request;
- dokumen terkait telah diperbarui bila diperlukan;
- telah direview oleh pemilik keputusan yang relevan.

## Lisensi

Lisensi proyek akan ditetapkan dalam file `LICENSE`. Seluruh dependency dan aset pihak ketiga wajib mematuhi lisensi asalnya serta dicatat sesuai kebutuhan kepatuhan proyek.

Copyright © Lab | Zefry.
