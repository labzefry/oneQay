# oneQay Product Vision and Decision Rights

> **Status:** Approved — DEC-000 substantive Product Owner decision
> **Phase:** 0 — Governance & Discovery
> **Owner:** Product Owner oneQay (`labzefry`)
> **Tracking:** GitHub Issue #2; `docs/handbook/DEC_000_DECISION_RECORD.md`

## Purpose

Dokumen ini menetapkan arah product/discovery oneQay dan batas kewenangan pengambilan keputusan. Dokumen ini mencegah asumsi berubah menjadi keputusan, memastikan setiap keputusan material memiliki pemilik manusia, serta menjaga ChatGPT sebagai engineering collaborator tanpa mengambil alih otoritas bisnis, hukum, keamanan, atau release.

Dokumen ini tidak menyetujui source code baru, framework, vendor, rancangan database fisik, kontrak API, atau tanggal rilis.

M6 mempublikasikan `docs/handbook/ENTERPRISE_VISION.md` sebagai canonical Enterprise Vision tingkat tinggi, dan GOV-051 kemudian secara terpisah mengesahkan Enterprise Vision tersebut sebagai binding long-term product direction. GOV-051 tidak mempromosikan Product Vision and Decision Rights/GD-003. Product Owner kemudian secara terpisah mengesahkan DEC-000 pada decision baseline `792b2dc30636bc53baa7d66b43cf2dab4a348dd4`, sehingga dokumen ini sekarang **Approved** sebagai governing Phase 0 product/discovery direction dan decision-rights boundary tanpa memberikan implementation authority.

## Canonical product naming

Nama produk canonical adalah **oneQay**.

Current/future-facing product identity harus menggunakan `oneQay`. Repository identifier `labzefry/oneQay`, immutable GitHub URLs, SHAs, commit messages, branch names, dan quoted historical evidence tidak ditulis ulang hanya untuk brand normalization.

## Product identity

| Atribut | Keputusan | Status |
| --- | --- | --- |
| Product | oneQay | Approved |
| Tagline | The Future of Intelligent Business Management | Approved |
| Category | Enterprise SaaS POS & ERP Platform | Approved |
| Enterprise Vision | Enterprise Intelligent Business Management Platform | Approved — GOV-051 substantive Product Owner decision |
| Developer & Product Engineering Entity | Lab \| zefry | Approved |
| Source of Truth | GitHub | Approved |
| Delivery model | Multi-tenant SaaS | Approved |

Engineering collaboration tooling is governed separately through `AI_CONSTITUTION.md`; it is not product authorship metadata.

## Product vision

oneQay menjadi platform intelligent business management yang membantu bisnis menjalankan transaksi dan operasional secara terpadu, akurat, aman, serta mudah dikembangkan dari satu toko menuju organisasi multi-cabang dan multi-tenant tanpa mengganti fondasi business logic ketika infrastruktur bertumbuh.

The Approved Enterprise Vision **Enterprise Intelligent Business Management Platform** memperluas horizon directional tersebut. Approval GOV-051 tidak menyatakan bahwa seluruh POS/ERP/CRM/HRM/BI/AI/ecosystem capability telah Approved untuk implementasi.

## Enterprise Vision relationship

M6 memisahkan secara eksplisit:

1. Product Vision;
2. Product Capability Map;
3. Product Architecture Direction;
4. Delivery Roadmap;
5. Implementation Authority.

Enterprise Capability Map adalah peta arah, bukan MVP scope dan bukan implementation grant. Detailnya berada di `docs/handbook/ENTERPRISE_VISION.md`.

GOV-051 hanya mengesahkan substantive Enterprise Vision. GOV-051 tidak menyetujui decision-rights matrix, open decisions, acceptance gate, atau GD-003 pada dokumen ini. DEC-000 kemudian secara terpisah mengesahkan Product Vision and Decision Rights dan mengotorisasi GD-003 untuk direkam sebagai Approved; DEC-000 tetap bukan implementation grant.

## Mission

oneQay menjalankan visi tersebut dengan:

1. menyederhanakan pekerjaan operasional melalui pengalaman yang cepat, konsisten, dan dapat dipahami;
2. menyatukan transaksi, persediaan, pelanggan, pembelian, keuangan, pelaporan, dan capability business management lain hanya ketika scope-nya disetujui;
3. menjaga integritas uang, stok, identitas, tenant context, dan audit trail pada setiap alur kritis;
4. menyediakan kontrak integrasi yang terversi dan tidak mengikat business logic pada vendor infrastruktur;
5. mendukung pertumbuhan bertahap dari shared hosting menuju cloud dan Kubernetes berdasarkan kebutuhan yang terbukti;
6. menghadirkan insight dan bantuan cerdas dengan kontrol manusia, batas data, evaluasi, serta keamanan yang dapat diaudit.

## Product principles

| Prinsip | Implikasi keputusan |
| --- | --- |
| Trust before speed | Keamanan, akurasi finansial, isolasi tenant, dan recovery tidak dikorbankan demi percepatan rilis. |
| Operational simplicity | Alur inti meminimalkan langkah, ambiguitas, dan ketergantungan pada dukungan teknis. |
| Modular growth | Fitur baru mengikuti batas domain dan tidak membuat duplicate business logic. |
| Evidence over preference | Pilihan produk dan teknologi memakai bukti, trade-off, serta acceptance criteria. |
| API-first compatibility | Integrasi dirancang melalui kontrak berversi dengan backward compatibility. |
| Human accountability | Keputusan dan tindakan berisiko selalu memiliki approver manusia yang teridentifikasi. |
| Infrastructure independence | Perpindahan environment tidak mengubah aturan bisnis. |
| Inclusive by design | Aksesibilitas, locale, currency, timezone, perangkat, dan konektivitas dipertimbangkan sejak discovery. |
| Vision is not authority | Capability map dan roadmap tidak boleh digunakan sebagai substitusi Product Owner implementation authority. |

## Target users and business problems

Segmen berikut adalah hipotesis discovery, bukan komitmen MVP final.

| Segmen kandidat | Kebutuhan utama | Masalah yang perlu divalidasi |
| --- | --- | --- |
| Pemilik bisnis satu atau beberapa outlet | Kendali operasional dan ringkasan kinerja | Data tersebar, laporan terlambat, dan keputusan bergantung pada rekonsiliasi manual. |
| Manajer outlet/operasional | Visibilitas transaksi, stok, tim, dan pengecualian | Status antar-outlet tidak konsisten dan masalah diketahui setelah berdampak. |
| Kasir/frontline | Transaksi yang cepat dan tahan kesalahan | Proses lambat, status pembayaran ambigu, dan koreksi tidak memiliki kontrol yang cukup. |
| Tim inventory/purchasing | Stok akurat dan alur pengadaan terkendali | Selisih stok, transfer tidak terlacak, dan reorder tidak berbasis data yang konsisten. |
| Tim finance/administration | Rekonsiliasi dan jejak audit | Data transaksi, pembayaran, refund, dan pencatatan keuangan tidak mudah ditelusuri. |
| Platform/tenant administrator | Konfigurasi tenant, akses, subscription, dan dukungan | Perubahan hak akses atau konfigurasi berisiko memengaruhi tenant lain. |

Validasi segmen, actor, workflow, dan jobs-to-be-done dilakukan pada workshop berikutnya. Tidak ada persona yang dianggap final sebelum memiliki bukti discovery.

## Product outcomes

Outcome berikut menjadi arah pengukuran. Nilai target dan periode pengukuran ditetapkan saat MVP slicing setelah baseline tersedia.

| Outcome | Indikator kandidat | Guardrail |
| --- | --- | --- |
| Operasional lebih efisien | waktu menyelesaikan alur inti, langkah manual, time-to-first-value | tidak meningkatkan error atau beban dukungan |
| Transaksi dapat dipercaya | success rate, duplicate/replay rate, waktu rekonsiliasi | tidak ada kehilangan atau penggandaan nilai finansial |
| Persediaan lebih akurat | inventory variance, adjustment rate, traceability | setiap perubahan stok memiliki sumber dan audit trail |
| Adopsi berkelanjutan | active tenant/outlet/user, completion rate, retention | metrik tidak mengorbankan privasi atau aksesibilitas |
| Platform aman dan terisolasi | insiden lintas tenant, privileged-action coverage, security findings | target kebocoran lintas tenant adalah nol |
| Operasi dapat dipulihkan | backup success, restore evidence, RPO/RTO achievement | release diblokir tanpa recovery evidence yang diwajibkan |

Indikator tidak boleh digunakan sebagai target numerik sebelum definisi, sumber data, owner, baseline, dan anti-gaming guardrail disetujui.

## Scope horizon

### Product direction in scope

- multi-tenant platform administration;
- identity, role, permission, organization, outlet, dan device context;
- POS dan transaksi penjualan inti;
- catalog, pricing, inventory, purchasing, customer, dan reporting;
- finance/accounting foundation yang ditentukan melalui discovery;
- Web Application, PWA, REST API, dan Admin Dashboard sebagai target yang telah disetujui;
- installer, updater, auditability, observability, backup, dan recovery;
- jalur evolusi menuju CRM, HRM, Business Intelligence, integration platform, CMS, public API, marketplace, plugin, dan AI capabilities setelah gate masing-masing terpenuhi.

Daftar ini adalah arah produk keseluruhan. MVP pertama akan menjadi irisan yang lebih kecil dan harus disetujui melalui DEC-001.

### Deferred or subject to discovery

- Android Native / broader mobile implementation;
- CMS;
- Public API;
- marketplace dan plugin runtime;
- AI Assistant/AI Platform provider dan data boundary;
- cloud dan Kubernetes deployment;
- mode offline POS dan resolusi konflik;
- provider pembayaran serta batas kepatuhan;
- model fisik isolasi tenant;
- final CRM/HRM/BI scope dan bounded contexts.

### Non-goals for the initial delivery

- membangun seluruh kapabilitas ERP/CRM/HRM/BI untuk semua industri sekaligus;
- menganggap Enterprise Capability Map sebagai backlog yang otomatis Approved;
- memulai dengan microservices atau Kubernetes tanpa kebutuhan terukur;
- memilih framework/vendor berdasarkan preferensi tanpa ADR;
- menyimpan atau memproses data pembayaran yang memerlukan kepatuhan di luar batas yang disetujui;
- menjalankan tindakan finansial, administratif, atau lintas tenant secara otonom oleh AI;
- menjanjikan tanggal rilis sebelum scope, kapasitas, dependency, dan risk buffer tersedia;
- memulai final/business/production application implementation baru tanpa authority dan gate yang berlaku.

Published bounded Platform Foundation through Sprint 13 remains a repository fact and is not invalidated by this non-goal boundary.

## Decision roles

| Role | Authority and responsibility |
| --- | --- |
| Product Owner | Pemegang keputusan akhir untuk visi, prioritas, scope, risiko yang diterima, pendanaan, release, dan perubahan yang berdampak bisnis. |
| Decision Owner | Manusia yang ditunjuk Product Owner untuk menyiapkan dan mempertanggungjawabkan keputusan pada domain tertentu. Penunjukan wajib tercatat di GitHub. |
| Subject Matter Reviewer | Menilai bukti dan risiko pada Product, Architecture, Security, Data, UX, QA, Operations, Legal, atau Compliance. Tidak otomatis menjadi approver. |
| Implementer | Menjalankan keputusan yang telah disetujui dalam batas issue/PR dan tidak boleh mengubah intent secara diam-diam. |
| ChatGPT | Memfasilitasi discovery, menganalisis opsi, menyusun artefak, melakukan review independen, dan memeriksa konsistensi. ChatGPT tidak memiliki hak approval. |
| GitHub | Menjadi sistem pencatatan issue, ADR, review, approval, commit, PR, release, dan audit history; GitHub bukan pengambil keputusan. |

## Decision-rights matrix

**A** = Accountable/approver manusia, **R** = Responsible menyiapkan keputusan, **C** = Consulted, **I** = Informed.

| Decision domain | A | R | C | Required evidence |
| --- | --- | --- | --- | --- |
| Product vision, outcomes, dan priorities | Product Owner | Product/Business Analysis role | Architecture, UX, Finance/Commercial | Product brief, issue, PR approval |
| MVP scope dan non-scope | Product Owner | Product Owner atau delegate | Architecture, Security, Data, UX, QA, Operations | Journey evidence, success metrics, dependency/risk analysis |
| Architecture dan technology | Product Owner | Architecture Decision Owner | Security, Data, Operations, QA, affected implementers | Accepted ADR dan option comparison |
| Tenant isolation dan privileged access | Product Owner | Security/Architecture Decision Owner | Data, QA, Operations | Threat model, test strategy, Accepted ADR/policy |
| Data classification, retention, privacy | Product Owner | Data/Security Decision Owner | Legal/Compliance yang relevan, Architecture, Operations | Data inventory, flow, policy, unresolved legal questions |
| API dan integration contract | Product Owner atau delegated Product Approver | API/Product Decision Owner | Security, consumers, QA, Operations | Versioned contract, compatibility dan abuse analysis |
| UX, accessibility, locale, dan critical journeys | Product Owner | UX/Product Decision Owner | Target users, Security, QA, Engineering | Research evidence, prototype, acceptance criteria |
| Payment dan compliance boundary | Product Owner | Product/Security Decision Owner | Finance, Legal/Compliance, Architecture, Operations | Provider options, data-flow, threat/compliance analysis |
| Deployment, backup, dan recovery | Product Owner atau named Operations Approver | Operations/DevOps Decision Owner | Architecture, Security, Data, QA, Release | Runbook, rehearsal evidence, RPO/RTO proposal |
| Release, rollback, dan exception | Product Owner atau named Release Approver | Release Manager | QA, Security, Operations, Product | Release checklist, immutable artifact, test and recovery evidence |
| Incident containment | Named human Incident Commander | Operations/Security responder | Product Owner, affected owners, Legal/Compliance bila perlu | Incident record, timeline, containment dan recovery evidence |

Sampai delegate manusia tercatat, Product Owner tetap menjadi approver. ChatGPT dapat menjalankan fungsi analisis dari berbagai disiplin, tetapi tidak boleh mengisi kolom **A** atau menyatakan keputusan telah Approved.

## Decision lifecycle

1. **Frame** — masalah, actor, outcome, constraint, dan non-scope dicatat pada issue.
2. **Investigate** — fact, assumption, preference, risk, dependency, dan open question dipisahkan.
3. **Compare** — opsi dinilai memakai rubric Phase 0; status quo selalu menjadi salah satu opsi bila relevan.
4. **Review** — reviewer domain menilai bukti, keamanan, data, operability, compatibility, dan biaya perubahan.
5. **Approve** — approver manusia memberi keputusan eksplisit pada PR/ADR dengan SHA atau versi yang jelas.
6. **Record** — keputusan, alasan, konsekuensi, owner, tanggal, dan supersession path tersimpan di GitHub.
7. **Implement and verify** — pekerjaan diturunkan menjadi task dengan acceptance evidence; perubahan intent kembali ke tahap Frame.
8. **Revisit or supersede** — keputusan lama tidak dihapus; tandai Superseded dan tautkan penggantinya.

## Status and approval semantics

| Status | Makna |
| --- | --- |
| Draft | Belum siap untuk keputusan. |
| Proposed | Siap direview tetapi belum mengikat. |
| Under Review | Review aktif; temuan atau pertanyaan masih terbuka. |
| Approved/Accepted | Disetujui approver manusia dan mengikat sesuai scope. |
| Deferred | Ditunda dengan trigger untuk dibuka kembali. |
| Rejected | Tidak dipilih beserta alasan dan kondisi evaluasi ulang. |
| Superseded | Digantikan keputusan baru tanpa menghapus sejarah. |

Merge bukan selalu bukti approval substantif. PR harus menyatakan decision items yang disetujui; approval harus merujuk versi/head yang direview. Perubahan setelah approval membutuhkan review ulang.

Enterprise Vision publication tidak otomatis mempromosikan decision items pada dokumen ini dari Proposed menjadi Approved. GOV-051 adalah explicit Product Owner substantive approval hanya untuk Enterprise Vision dan tidak mempromosikan GD-003 atau item lain pada dokumen ini. GD-003 memperoleh approval hanya melalui substantive Product Owner DEC-000 yang terpisah.

## Escalation and stop conditions

Pekerjaan dihentikan dan dieskalasikan kepada Product Owner bila:

- tidak ada approver manusia yang berwenang;
- dua dokumen Approved saling bertentangan;
- keputusan dapat menyebabkan data loss, kebocoran lintas tenant, pelanggaran hukum/lisensi, atau tindakan yang sulit dipulihkan;
- payment, privacy, jurisdiction, fiscal, atau compliance boundary belum diketahui;
- evidence penting tidak tersedia atau risiko Critical/High belum memiliki treatment;
- scope melebar melampaui issue/PR;
- reviewer dan implementer tidak dapat memisahkan konflik kepentingan pada perubahan berisiko;
- keputusan teknologi diminta tanpa option analysis dan ADR;
- final/business/production source implementation diminta tanpa authority dan gate yang berlaku.

Eskalasi dicatat pada issue dengan pilihan, dampak, rekomendasi, owner keputusan, dan kebutuhan bukti. ChatGPT tidak boleh memilih diam-diam untuk melewati blocker.

## Workshop output checklist

- [x] Product Owner atau delegate manusia teridentifikasi di GitHub: accountable Product Owner `labzefry`; belum ada delegate tercatat.
- [x] Visi, misi, prinsip, dan product outcomes direview dan disetujui melalui DEC-000.
- [x] Segmen, actor, masalah, dan asumsi yang perlu divalidasi diterima sebagai Phase 0 discovery hypotheses and constraints.
- [x] Arah scope, deferred items, dan non-goals dipahami dan diterima dalam boundary DEC-000.
- [x] Decision-rights matrix dikonfirmasi melalui DEC-000.
- [x] Open questions memiliki owner dan next action; PV-002 sampai PV-006 tetap OPEN / NOT RESOLVED.
- [x] Tidak ada pilihan framework, vendor, database, API, atau deployment baru yang disetujui secara tersirat.
- [x] Enterprise Vision tidak diperlakukan sebagai implementation authority.
- [x] Keputusan final dicatat melalui `docs/handbook/DEC_000_DECISION_RECORD.md`; tidak ada dissent yang dicatat pada substantive decision.

## Open decisions and next discovery

| ID | Open decision | Owner | Next evidence |
| --- | --- | --- | --- |
| PV-001 | Accountable Product Owner identification | Product Owner | **Satisfied for DEC-000:** `labzefry`; future delegates require explicit GitHub assignment, bounded scope, and supersession/revocation path |
| PV-002 | **OPEN — NOT RESOLVED:** Segmen pelanggan awal dan prioritas industri | Product Owner | Stakeholder interviews dan problem evidence |
| PV-003 | **OPEN — NOT RESOLVED:** MVP scope/non-scope | Product Owner | DEC-001 actor map, journeys, event storming, MVP slicing |
| PV-004 | **OPEN — NOT RESOLVED:** Definisi serta target product outcomes | Product Owner | Baseline, data feasibility, metric definition, owner, target period, anti-gaming review |
| PV-005 | **OPEN — NOT RESOLVED:** Batas legal, fiscal, payment, privacy, dan jurisdiction | Product Owner | Qualified legal/compliance evidence |
| PV-006 | **OPEN — NOT RESOLVED:** Delegate untuk release dan incident authority | Product Owner | Named responsibility dan escalation coverage; until then Product Owner authority remains applicable where required |

## Acceptance gate

Dokumen dapat berubah dari Proposed menjadi Approved hanya bila Product Owner:

1. menyetujui visi, misi, product principles, dan arah outcome;
2. mengonfirmasi atau memperbaiki decision-rights matrix;
3. mengidentifikasi Product Owner/delegate manusia pada GitHub;
4. menerima daftar open decisions tanpa menganggapnya telah selesai;
5. menegaskan implementation boundary dan authority yang berlaku pada exact head.

Acceptance gate tersebut dipenuhi oleh substantive Product Owner DEC-000 decision pada baseline `792b2dc30636bc53baa7d66b43cf2dab4a348dd4`, baseline tree `08f03b895d5e2ae7ca402e9866384990e126add3`, canonical artifact blob `843544b9e31dd4c47638b88dd204f4e594295df4`, dan readiness artifact blob `b493a5d66edc1bbffab0126bdacf2ca1ce14fa8f`. Detail decision record berada di `docs/handbook/DEC_000_DECISION_RECORD.md`.

M6 START authority maupun GOV-051 Enterprise Vision approval sendiri tidak memenuhi acceptance gate dokumen/GD-003; approval tersebut diberikan terpisah melalui DEC-000.

## DEC-000 substantive decision record

- D-000-01 — Product Vision and Mission: **APPROVED**.
- D-000-02 — Product Principles and Outcome Direction: **APPROVED**.
- D-000-03 — Segment, actor, problem, scope, deferred, and non-goal hypotheses: **ACCEPTED AS PHASE 0 DISCOVERY HYPOTHESES AND CONSTRAINTS**.
- D-000-04 — Decision-rights matrix: **CONFIRMED**.
- D-000-05 — Open decision disposition: **APPROVED**; PV-001 satisfied for DEC-000 identity, PV-002 through PV-006 remain OPEN / NOT RESOLVED.
- D-000-06 — Implementation-authority boundary: **CONFIRMED**.
- Issue #2 closure remains historical workflow state and is not substantive approval evidence.
- GD-003 substantive Product Vision and Decision Rights: **APPROVED through DEC-000**.
- DEC-000 does not grant Sprint 14, implementation, SQL/migration, production DB, deployment, release, ADR acceptance, GD-007 promotion, JRN resolution, or production-readiness authority.

## Current program boundary

- Phase 0: In Progress.
- Enterprise Vision: Approved through GOV-051 as binding long-term product direction; not implementation authority.
- Product Vision and Decision Rights / GD-003: Approved through DEC-000; not implementation authority.
- Sprint 12: Published.
- Sprint 13: Published.
- Sprint 14: Not Authorized.
- Final/business/production application implementation: Blocked unless separately authorized.
- Production readiness: NO-GO.
- Deployment, release, SQL/migration execution, and production database modification: Not Authorized.
- GD-007: Proposed.
- JRN-003/JRN-013: Unresolved.

## ChatGPT — Lanjutan

Gunakan repository `labzefry/oneQay` sebagai SSOT. Terapkan hanya koreksi yang memiliki authority sesuai lifecycle pada `PRODUCT_VISION_AND_DECISION_RIGHTS.md`, `PROJECT_MANIFEST.md`, `TASKS.md`, `CHANGELOG.md`, dan dokumen canonical terkait. GD-003 sekarang Approved hanya dalam boundary DEC-000 dan tidak boleh diperluas menjadi implementation authority. Jangan mempromosikan item Proposed lain menjadi Approved tanpa approval manusia yang eksplisit pada exact head. Published bounded Platform Foundation history tidak boleh dihapus atau ditulis ulang. Final/business implementation baru tetap membutuhkan authority terpisah.

## ChatGPT — Review Independen

Audit `PRODUCT_VISION_AND_DECISION_RIGHTS.md` terhadap `docs/handbook/ENTERPRISE_VISION.md`, `README.md`, `PROJECT_MANIFEST.md`, `AI_CONSTITUTION.md`, `ARCHITECTURE.md`, `ROADMAP.md`, `TASKS.md`, dan `docs/handbook/PHASE_0_KICKOFF.md`. Cari konflik otoritas, approval implisit, conflation antara vision dan implementation authority, scope MVP yang terlalu dini, outcome yang tidak dapat diukur, peran manusia yang hilang, loophole bagi ChatGPT untuk self-approve, serta pelanggaran current implementation boundary. Klasifikasikan temuan Critical/High/Medium/Low dan berikan perbaikan minimal.

Attribution: Lab | zefry