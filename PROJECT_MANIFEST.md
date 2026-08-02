# OneQay Project Manifest

> Dokumen identitas teknis kanonis OneQay. Bila informasi di dokumen lain bertentangan, keputusan berstatus **Approved** di manifest ini berlaku sampai digantikan melalui ADR atau pull request yang disetujui.

## Metadata

| Atribut | Nilai | Status |
|---|---|---|
| Product | OneQay | Approved |
| Tagline | The Future of Intelligent Business Management | Approved |
| Author | Lab \| Zefry | Approved |
| Category | Enterprise SaaS POS & ERP Platform | Approved |
| Repository | `labzefry/oneQay` | Approved |
| Source of Truth | GitHub | Approved |
| Engineering collaborator | ChatGPT | Approved |
| Collaboration model | ChatGPT + GitHub only | Approved |
| External generative-AI workflow | Tidak digunakan | Approved |
| Delivery model | Multi-tenant SaaS | Approved |
| Architecture baseline | Modular Monolith, Clean Architecture | Approved |
| Handbook version | 1.0 | Approved |
| Product version | Belum ditetapkan | Under Review |
| License | Proprietary / All Rights Reserved | Proposed |

## Status definitions

| Status | Arti |
|---|---|
| Approved | Telah disetujui dan mengikat implementasi |
| Proposed | Usulan siap direview, belum mengikat |
| Under Review | Sedang dianalisis atau membutuhkan keputusan |
| Deferred | Sengaja ditunda sampai entry criteria terpenuhi |
| Deprecated | Tidak boleh digunakan untuk pekerjaan baru |

## Product intent

OneQay menyatukan fungsi POS, ERP, administrasi tenant, integrasi, marketplace, plugin, dan AI assistant dalam satu platform yang aman serta dapat berkembang dari shared hosting menuju Kubernetes tanpa mengubah business logic.

## Current delivery gate

| Item | Status | Gate |
|---|---|---|
| Handbook 1.0 governance baseline | Approved | PR #1 disetujui dan di-merge ke `main` |
| Phase 0 governance and discovery | In Progress | Deliverable discovery dikelola melalui Issue #2, Issue #4, Issue #6, Issue #8, Issue #10, dan PR terpisah |
| Application implementation | Blocked | Menunggu exit criteria Phase 0 dan Accepted ADR minimum |

Rencana kickoff berada di `docs/handbook/PHASE_0_KICKOFF.md`.

## Governance decision register

| ID | Keputusan | Status | Dokumen pemilik |
|---|---|---|---|
| GD-001 | GitHub sebagai Single Source of Truth | Approved | `AI_CONSTITUTION.md` |
| GD-002 | ChatGPT + GitHub sebagai collaboration model eksklusif | Approved | `AI_CONSTITUTION.md` |
| GD-003 | Product vision dan decision rights | Proposed | `docs/handbook/PRODUCT_VISION_AND_DECISION_RIGHTS.md` |
| GD-004 | Application source code tetap diblokir sampai exit criteria Phase 0 | Approved | `docs/handbook/PHASE_0_KICKOFF.md` |
| GD-005 | Stakeholder and actor map | Proposed | `docs/handbook/STAKEHOLDER_AND_ACTOR_MAP.md` |
| GD-006 | Current process and user journeys | Proposed | `docs/handbook/CURRENT_PROCESS_AND_USER_JOURNEYS.md` |
| GD-007 | Domain event storming | Proposed | `docs/handbook/DOMAIN_EVENT_STORMING.md`; approved corrections tracked in Issue #10 |

GD-003 hanya dapat berubah menjadi Approved setelah Product Owner menyetujui head pull request terkait. Merge teknis tanpa decision statement tidak boleh dianggap sebagai approval substantif.

Persetujuan Product Owner atas lima koreksi review PR #9 hanya mengotorisasi correction scope pada Issue #10. Persetujuan tersebut tidak mempromosikan GD-007 atau event, aggregate, bounded context, dan policy hypothesis dari **Proposed**.

## Target platforms

| Platform | Target | Status awal |
|---|---|---|
| Web Application | Operasional utama | Approved |
| Progressive Web App | Akses mobile dan offline-terkendali | Approved |
| Android Native | Kapabilitas perangkat dan pengalaman native | Proposed |
| REST API | Kontrak internal dan integrasi | Approved |
| Public API | Ekosistem eksternal | Deferred |
| Admin Dashboard | Administrasi platform dan tenant | Approved |
| Landing Website | Akuisisi dan informasi produk | Approved |
| CMS | Konten publik dan operasional | Proposed |
| Marketplace | Distribusi extension/integration | Deferred |
| Plugin System | Ekstensibilitas terkontrol | Deferred |
| AI Assistant | Bantuan operasional dan insight | Proposed |

## Architecture guardrails

- Business logic tidak boleh mengimpor detail framework, database, transport, filesystem, vendor cloud, atau UI.
- Tenant context wajib tersedia sebelum akses data tenant.
- Semua perubahan skema menggunakan migration maju dan rollback plan.
- Semua perubahan API mengikuti versioning serta compatibility policy.
- Modul berkomunikasi melalui application contract dan domain event; akses tabel lintas modul dilarang tanpa keputusan arsitektur.
- Konfigurasi berasal dari environment atau configuration service; secret tidak boleh berada di repository.
- Side effect eksternal harus idempotent, dapat diaudit, dan memiliki timeout serta retry policy.

## Proposed bounded contexts

Daftar berikut adalah hipotesis arsitektur dan harus divalidasi melalui domain discovery sebelum implementasi:

1. Tenant & Subscription
2. Identity & Access Management
3. Organization, Outlet & Device
4. Catalog & Pricing
5. Inventory & Warehousing
6. Sales & Point of Sale
7. Purchasing & Supplier
8. Customer & Loyalty
9. Finance & Accounting
10. Reporting & Analytics
11. Content Management
12. Integration Hub
13. Marketplace & Plugin Management
14. AI Assistance
15. Platform Operations & Audit

Status seluruh bounded context: **Proposed**.

## Multi-tenant baseline

| Keputusan | Nilai | Status |
|---|---|---|
| Isolation key | Immutable Tenant ID | Approved |
| Access hostname | Domain/subdomain sebagai routing, bukan otorisasi | Approved |
| Default isolation model | Shared application dengan tenant-scoped data | Proposed |
| Dedicated deployment option | Dapat ditambahkan untuk tenant enterprise | Deferred |
| Tenant timezone/currency/locale | Wajib tersimpan sebagai konfigurasi tenant | Approved |
| Cross-tenant query | Deny by default; hanya platform operation terotorisasi | Approved |

## Deployment evolution

| Stage | Environment | Status | Exit criteria utama |
|---:|---|---|---|
| 1 | Shared Hosting / cPanel | Approved target awal | Operasional stabil, backup dan restore teruji |
| 2 | VPS | Planned | Kebutuhan resource atau kontrol melebihi shared hosting |
| 3 | Dedicated Server | Planned | Beban dan isolasi memerlukan host khusus |
| 4 | Docker | Planned | Pipeline, observability, dan state externalization siap |
| 5 | Cloud | Planned | Autoscaling, managed services, dan DR layak biaya |
| 6 | Kubernetes | Deferred | Skala dan kompleksitas operasional membenarkan orkestrasi |

Perpindahan stage tidak boleh mengubah domain atau business logic.

## Technology decision register

| ID | Keputusan | Status | Dokumen pemilik |
|---|---|---|---|
| TD-001 | Bahasa dan framework backend | Under Review | ADR |
| TD-002 | Framework web frontend | Under Review | ADR |
| TD-003 | Android native stack | Under Review | ADR |
| TD-004 | Relational database engine | Under Review | ADR / DATABASE.md |
| TD-005 | Cache dan queue technology | Deferred | ADR |
| TD-006 | Authentication protocol/provider | Under Review | SECURITY.md / ADR |
| TD-007 | Observability stack | Deferred | DEPLOYMENT.md / ADR |
| TD-008 | Payment gateway strategy | Under Review | ADR |
| TD-009 | AI provider and data boundary | Under Review | SECURITY.md / ADR |

Tidak ada framework atau vendor yang dianggap dipilih sebelum ADR berstatus Accepted.

## Environment classes

| Environment | Data policy | Deployment source | Approval |
|---|---|---|---|
| Local | Synthetic only | Developer branch | Tidak untuk produksi |
| Test | Synthetic/masked | CI artifact | Otomatis sesuai quality gate |
| Staging | Masked; production-like | Release candidate | Release Manager |
| Production | Real tenant data | Signed release artifact | Authorized approver |

## Dependency policy

- Dependency baru membutuhkan tujuan, owner, license, maintenance status, security review, dan exit strategy.
- Version harus dikunci secara reproducible melalui lockfile.
- Dependency tidak boleh mengakses data, jaringan, atau filesystem melebihi kebutuhan.
- Critical vulnerability memblokir release kecuali exception terdokumentasi dan memiliki expiry.
- Fork permanen dihindari; bila diperlukan wajib memiliki ownership dan upstream strategy.

## Canonical documents

| Dokumen | Otoritas |
|---|---|
| README.md | Orientasi proyek |
| PROJECT_MANIFEST.md | Identitas dan status keputusan |
| AI_CONSTITUTION.md | Aturan permanen ChatGPT pada proyek |
| ARCHITECTURE.md | Arsitektur dan boundary |
| ROADMAP.md | Urutan delivery |
| TASKS.md | Backlog operasional |
| CHANGELOG.md | Riwayat perubahan versi |
| API_SPEC.md | Governance API |
| DATABASE.md | Governance data dan skema |
| SECURITY.md | Security baseline |
| DEPLOYMENT.md | Operasi dan deployment |
| TESTING.md | Quality strategy |

## Initial risks

| ID | Risiko | Severity | Mitigasi awal |
|---|---|---|---|
| R-001 | Kebocoran data lintas tenant | Critical | Tenant context enforcement dan isolation tests |
| R-002 | Scope POS/ERP terlalu luas | High | MVP boundary dan phased roadmap |
| R-003 | Ketergantungan shared hosting | High | Infrastructure abstraction dan migration criteria |
| R-004 | Plugin merusak keamanan/stabilitas | High | Signed package, capability policy, sandbox strategy |
| R-005 | Update gagal dan merusak tenant | Critical | Backup, integrity check, health gate, rollback |
| R-006 | AI memproses data sensitif | High | Data classification, redaction, consent, provider policy |

## Mandatory update rule

Setiap perubahan resmi minimal memperbarui manifest, task, dan changelog bila status, scope, capability, keputusan, atau risiko proyek berubah. Perubahan arsitektur, API, database, deployment, security, testing, dan UI/UX juga harus memperbarui dokumen pemiliknya.

## Approval

Baseline governance Handbook 1.0 disetujui melalui PR #1. Item berstatus Approved mengikat seluruh pekerjaan berikutnya; item Proposed, Under Review, dan Deferred tidak boleh diperlakukan sebagai keputusan final.
