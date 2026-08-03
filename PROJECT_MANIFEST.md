# OneQay Project Manifest

> Dokumen identitas teknis kanonis OneQay. Bila informasi di dokumen lain bertentangan, keputusan berstatus **Approved** di manifest ini berlaku sampai digantikan melalui ADR atau pull request yang disetujui.

## Metadata

| Atribut | Nilai | Status |
| --- | --- | --- |
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
| --- | --- |
| Approved | Telah disetujui dan mengikat implementasi |
| Proposed | Usulan siap direview, belum mengikat |
| Under Review | Sedang dianalisis atau membutuhkan keputusan |
| Deferred | Sengaja ditunda sampai entry criteria terpenuhi |
| Deprecated | Tidak boleh digunakan untuk pekerjaan baru |

## Product intent

OneQay menyatukan fungsi POS, ERP, administrasi tenant, integrasi, marketplace, plugin, dan AI assistant dalam satu platform yang aman serta dapat berkembang dari shared hosting menuju Kubernetes tanpa mengubah business logic.

## Current delivery gate

| Item | Status | Gate |
| --- | --- | --- |
| Handbook 1.0 governance baseline | Approved | PR #1 disetujui dan di-merge ke `main` |
| Phase 0 governance and discovery | In Progress | Deliverable discovery dikelola melalui Issue #2, Issue #4, Issue #6, Issue #8, Issue #10, Issue #12, Issue #14, Issue #16, Issue #18, Issue #20, dan PR terpisah |
| Application implementation | Blocked | Menunggu exit criteria Phase 0 dan Accepted ADR minimum |

Rencana kickoff berada di `docs/handbook/PHASE_0_KICKOFF.md`.

## Governance decision register

| ID | Keputusan | Status | Dokumen pemilik |
| --- | --- | --- | --- |
| GD-001 | GitHub sebagai Single Source of Truth | Approved | `AI_CONSTITUTION.md` |
| GD-002 | ChatGPT + GitHub sebagai collaboration model eksklusif | Approved | `AI_CONSTITUTION.md` |
| GD-003 | Product vision dan decision rights | Proposed | `docs/handbook/PRODUCT_VISION_AND_DECISION_RIGHTS.md` |
| GD-004 | Application source code tetap diblokir sampai exit criteria Phase 0 | Approved | `docs/handbook/PHASE_0_KICKOFF.md` |
| GD-005 | Stakeholder and actor map | Proposed | `docs/handbook/STAKEHOLDER_AND_ACTOR_MAP.md` |
| GD-006 | Current process and user journeys | Proposed | `docs/handbook/CURRENT_PROCESS_AND_USER_JOURNEYS.md` |
| GD-007 | Domain event storming | Proposed | `docs/handbook/DOMAIN_EVENT_STORMING.md`; corrections tracked in Issue #10/#12; governance controls tracked in Issue #14/#16/#18/#20 |

GD-003 hanya dapat berubah menjadi Approved setelah Product Owner menyetujui head pull request terkait. Merge teknis tanpa decision statement tidak boleh dianggap sebagai approval substantif.

Persetujuan Product Owner atas lima koreksi review PR #9 hanya mengotorisasi correction scope pada Issue #10. Persetujuan tersebut tidak mempromosikan GD-007 atau event, aggregate, bounded context, dan policy hypothesis dari **Proposed**.

Persetujuan Product Owner atas empat koreksi audit PR #11 hanya mengotorisasi correction scope pada Issue #12. Merge PR #11 dan penutupan Issue #10 tidak dianggap sebagai approval substantif GD-007.

Product Owner meratifikasi empat koreksi tersebut pada exact head PR #13 `e4a3b7ba9f94b429b6e50e2856a11b953a336ac0` setelah audit pasca-merge. Merge commit `66865c3c79fc46e7ec67b0576097143288a73ed5` terjadi sebelum approval tercatat dan dilacak melalui Issue #14. Ratifikasi terbatas ini tidak mempromosikan GD-007, event, policy, aggregate candidate, atau bounded-context candidate dari **Proposed**.

PR #15 pada exact head `4ad28a4e8ad5740e6f55f4563a32d09e7bba631a` juga di-merge sebelum approval melalui merge commit `b34f99ea3c5471cfcd6ae82bc6abeb9a3e78441a`; Issue #12 dan Issue #14 kembali ditutup tanpa completion evidence lalu dibuka kembali. Recurrence dan hardening exact-head approval, pre-merge verification, serta issue closure gate dilacak melalui Issue #16. Tidak ada ratifikasi substantif PR #15 dan seluruh status Phase 0/application/domain tetap tidak berubah.

Audit read-only menunjukkan required review dan required status checks tidak efektif atau dapat dibypass pada PR #13/#15. Stale-approval dismissal dan conversation-resolution settings tidak dapat diverifikasi karena tidak ada approval/thread serta GitHub App tidak mengekspos konfigurasi protection. Repository Owner harus memberi direct settings evidence atau formal risk acceptance sebelum Issue #16 ditutup.

PR #17 pada exact head `aaa7510759925c0c62ba5424c93e2356d18c9d3d` kembali di-merge sebelum exact-head approval, review, checks/approved deferral, protection evidence/risk acceptance, dan separate merge authority melalui merge commit `82b45820a67c274bd96866bb048f3f320d6cbe70`. Issue #12, Issue #14, dan Issue #16 juga ditutup prematur lalu dibuka kembali. Recurrence, corrective action, dan prevention action dilacak melalui Issue #18. Product Owner hanya menyetujui temuan audit High/Medium; PR #17 tidak diratifikasi secara substantif dan tidak ada status domain atau delivery yang dipromosikan.

Audit read-only lanjutan menunjukkan PR #17 tidak memiliki review submission, approval comment, atau published commit status ketika merged. GitHub App tetap tidak mengekspos direct branch-protection/ruleset configuration; configured control karena itu tidak diklaim. Untuk perubahan governance berisiko High/Critical, direct settings evidence atau formal risk acceptance yang lengkap dan mengikat nomor PR serta exact head kini menjadi blocking precondition sebelum ready transition, merge, dan issue closure.

PR #19 pada exact head `483fcf3dbe2c5a418ea7aad97bcfcbf26124b631` diubah dari draft dan di-merge tanpa exact-head approval, review, checks/approved deferral, direct protection evidence/formal risk acceptance, atau separate lifecycle authority melalui merge commit `f68c01e85660409fac6c4e85f2f6545dca08f1d7`. Issue #12, Issue #14, Issue #16, dan Issue #18 ditutup sebelum merge tanpa completion evidence lalu dibuka kembali. Recurrence dan pemisahan Product Owner formal-risk-acceptance evidence dilacak melalui Issue #20. Product Owner hanya menyetujui temuan audit High/Medium; PR #19 tidak diratifikasi secara substantif.

Audit read-only PR #19 kembali menunjukkan tidak adanya review submission, approval comment, dan published commit status saat merge. GitHub App tidak mengekspos configured branch-protection/ruleset controls, sehingga configured state tidak diklaim. Direct settings evidence atau formal risk acceptance Product Owner yang lengkap tetap menjadi blocking precondition; risk acceptance hanya menggantikan protection-evidence requirement dalam scope dan tidak memberi lifecycle authority.

## Target platforms

| Platform | Target | Status awal |
| --- | --- | --- |
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
| --- | --- | --- |
| Isolation key | Immutable Tenant ID | Approved |
| Access hostname | Domain/subdomain sebagai routing, bukan otorisasi | Approved |
| Default isolation model | Shared application dengan tenant-scoped data | Proposed |
| Dedicated deployment option | Dapat ditambahkan untuk tenant enterprise | Deferred |
| Tenant timezone/currency/locale | Wajib tersimpan sebagai konfigurasi tenant | Approved |
| Cross-tenant query | Deny by default; hanya platform operation terotorisasi | Approved |

## Deployment evolution

| Stage | Environment | Status | Exit criteria utama |
| ---: | --- | --- | --- |
| 1 | Shared Hosting / cPanel | Approved target awal | Operasional stabil, backup dan restore teruji |
| 2 | VPS | Planned | Kebutuhan resource atau kontrol melebihi shared hosting |
| 3 | Dedicated Server | Planned | Beban dan isolasi memerlukan host khusus |
| 4 | Docker | Planned | Pipeline, observability, dan state externalization siap |
| 5 | Cloud | Planned | Autoscaling, managed services, dan DR layak biaya |
| 6 | Kubernetes | Deferred | Skala dan kompleksitas operasional membenarkan orkestrasi |

Perpindahan stage tidak boleh mengubah domain atau business logic.

## Technology decision register

| ID | Keputusan | Status | Dokumen pemilik |
| --- | --- | --- | --- |
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
| --- | --- | --- | --- |
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
| --- | --- |
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
| --- | --- | --- | --- |
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

## Technical Preview v0.0.1 decision package

Issue #23 records the accelerated T+5 planning scope and Product Owner selections. PR #24 was technically merged before this canonical synchronization; that merge does not accept an ADR, approve Phase 0 exit, or grant source-code authority.

| Decision package item | Candidate selection | Status | Evidence/gate |
| --- | --- | --- | --- |
| Backend | B1 Laravel/PHP modular monolith | Proposed | ADR-001; exact-head approval pending |
| Frontend/PWA | F1 Vue 3 + Inertia + Vite | Proposed | ADR-002; exact-head approval pending |
| Database/tenancy | D1 MySQL-compatible shared schema | Proposed | ADR-003; engine/version evidence pending |
| Authentication | A1 first-party session and privileged TOTP | Proposed | ADR-004; JRN-003 remains unresolved |
| Payment preview | PAY-1 synthetic cash-only | Proposed | ADR-005; no provider or real money |
| Offline preview | OFF-1 online-only | Proposed | ADR-006; offline mutation deferred |
| Deployment | P1 cPanel conditional; P2 fallback hypothesis | Proposed / Unverified | ADR-007; hosting assessment incomplete |
| Tenant boundary | TEN-1 two synthetic tenants | Proposed | Isolation evidence pending |
| Recovery | REC-1 provisional RPO 24h/RTO 4h | Proposed | Capability and rehearsal pending |
| Preview SLO | SLO-1 | Proposed | Measurement evidence pending |
| Data boundary | DATA-1 synthetic only | Proposed | Data baseline exact-head approval pending |

Phase 0 remains **In Progress**. Application implementation remains **Blocked**. Phase 0 preview exit remains **Not Ready**. GD-007 and Domain Event Storming remain **Proposed**. JRN-003 and JRN-013 remain unresolved blockers. Missing hosting facts must not be inferred.

## PR #25 and Issue #23 governance recurrence

PR #25 was created from base `a3efdd17e69590bd4aaf60c0f9da3ecf6773e31f` at exact head `ca2157096b310b114203d919cb8182e55a6fa5f9`. Its recorded lifecycle authority was draft creation only, but it was changed from draft and technically merged as `93c8b8d4d8dae399c0d3f758c50460cf086e2322` without available separate exact-head ready or merge authority.

Read-only evidence for the PR #25 exact head shows no review submission, PR conversation comment, published commit status, or GitHub Actions workflow run. The local validation statements recorded in the PR body remain distinct from independent GitHub check evidence and do not supply lifecycle authority.

Issue #23 was closed with reason `completed` before its evidence, hosting, ADR acceptance, recovery, Technical Preview acceptance, and Phase 0 preview-exit conditions were complete. That closure is a technical repository state only and is not completion evidence.

The PR #25 technical merge and Issue #23 closure do not constitute substantive approval, ADR acceptance, Phase 0 exit, source-code authority, ratification, or completion evidence. Phase 0 remains **In Progress**. Application implementation remains **Blocked**. Phase 0 preview exit remains **Not Ready**. P1 remains conditional and **Unverified**. ADR-001 through ADR-007, GD-007, PAY-1, OFF-1, TEN-1, REC-1, SLO-1, and DATA-1 remain **Proposed** selections or boundaries. JRN-003 and JRN-013 remain unresolved. Hosting evidence not supplied remains Pending, Not supplied, or Unverified.

## PR #26 post-merge governance recurrence

PR #26 was created from original base `93c8b8d4d8dae399c0d3f758c50460cf086e2322` at exact head `63223b9b856bd67e739651a1e23cc071971998c3`. Its body limited lifecycle authority to draft creation and required the PR to remain draft, but it was changed from draft and technically merged as `294fe24381e88b61701868567cda4be532640ab0` without available separate exact-head ready or merge authority.

Read-only evidence for the PR #26 exact head shows no review submission, PR conversation comment, published commit status, or GitHub Actions workflow run. The local and static validation statements in the PR body remain distinct from independent GitHub check evidence and do not supply lifecycle authority.

The Product Owner issued a post-merge content decision approving only the accuracy of the three-file corrective content on PR #26 exact head. That decision does not provide retrospective lifecycle authority and does not ratify the draft-to-ready transition or merge of PR #26.

The PR #26 technical merge does not ratify PR #25, validate or complete the closure of Issue #23, accept ADR-001 through ADR-007, approve Phase 0 preview exit, grant source-code authority, complete GOV-034 or GOV-035, or provide completion evidence. Phase 0 remains **In Progress**. Application implementation remains **Blocked**. Phase 0 preview exit remains **Not Ready**. P1 remains conditional and **Unverified**. ADR-001 through ADR-007, GD-007, PAY-1, OFF-1, TEN-1, REC-1, SLO-1, and DATA-1 remain **Proposed** selections or boundaries. JRN-003 and JRN-013 remain unresolved. Hosting evidence remains Pending, Not supplied, or Unverified.

## PR #27 post-merge governance recurrence

PR #27 was created from original base `294fe24381e88b61701868567cda4be532640ab0` at exact head `c6adb55a9a6cd2ebedd78668ccaf5fd64c041d94`. Its body limited lifecycle authority to draft creation and required the PR to remain draft, but it was changed from draft and technically merged as `3c4bcfe9797a3ae7f4deb124568ef361d74125e5` without available separate exact-head ready or merge authority.

Read-only evidence for the PR #27 exact head shows no review submission, PR conversation comment, published commit status, or GitHub Actions workflow run. The local and static validation statements in the PR body remain distinct from independent GitHub check evidence and do not supply lifecycle authority.

The Product Owner issued a post-merge exact-head content decision approving only the accuracy of the three-file corrective content on PR #27 exact head. That decision does not provide retrospective lifecycle authority and does not ratify the draft-to-ready transition or merge of PR #27.

The PR #27 technical merge does not ratify PR #26 or PR #25, validate or complete the closure of Issue #23, accept ADR-001 through ADR-007, approve Phase 0 preview exit, grant source-code authority, complete GOV-034, GOV-035, or GOV-036, or provide substantive approval or completion evidence. Phase 0 remains **In Progress**. Application implementation remains **Blocked**. Phase 0 preview exit remains **Not Ready**. P1 remains conditional and **Unverified**. ADR-001 through ADR-007, GD-007, PAY-1, OFF-1, TEN-1, REC-1, SLO-1, and DATA-1 remain **Proposed** selections or boundaries. GOV-034, GOV-035, and GOV-036 remain **Review**. JRN-003 and JRN-013 remain unresolved. Hosting evidence remains Pending, Not supplied, or Unverified.

## PR #28 post-merge governance recurrence

PR #28 was created from original base `3c4bcfe9797a3ae7f4deb124568ef361d74125e5` at exact head `0597d784f63cf6d5967cedae17ca8d0b5a2e4dc9`. Its body limited lifecycle authority to draft creation and required the PR to remain draft, but it was changed from draft and technically merged as `1009af84ec0ee7d7731890e379dde25279280c3a` without available separate exact-head ready or merge authority.

Read-only evidence for the PR #28 exact head shows no review submission, PR conversation comment, published commit status, or GitHub Actions workflow run. The local and static validation statements in the PR body remain distinct from independent GitHub check evidence and do not supply lifecycle authority.

The Product Owner issued a post-merge exact-head content decision approving only the accuracy of the three-file corrective content on PR #28 exact head. That decision does not provide retrospective lifecycle authority and does not ratify the draft-to-ready transition or merge of PR #28.

The PR #28 technical merge does not ratify PR #27, PR #26, or PR #25, validate or complete the closure of Issue #23, accept ADR-001 through ADR-007, approve Phase 0 preview exit, grant source-code authority, complete GOV-034, GOV-035, GOV-036, or GOV-037, or provide substantive approval or completion evidence. Phase 0 remains **In Progress**. Application implementation remains **Blocked**. Phase 0 preview exit remains **Not Ready**. P1 remains conditional and **Unverified**. ADR-001 through ADR-007, GD-007, PAY-1, OFF-1, TEN-1, REC-1, SLO-1, and DATA-1 remain **Proposed** selections or boundaries. GOV-034, GOV-035, GOV-036, and GOV-037 remain **Review**. JRN-003 and JRN-013 remain unresolved. Hosting evidence remains Pending, Not supplied, or Unverified.

## PR #29 post-merge governance recurrence

PR #29 was created from original base `1009af84ec0ee7d7731890e379dde25279280c3a` at exact head `54a5773c3ab65a33e35ef2646089727490a0ff8d`. Its body required the PR to remain draft, but it was changed from draft and technically merged as `f55d86f1a3d89a6bcbbbcf7800851b9c61f8c047`.

A repository-native operational authority comment was present on PR #29 and explicitly authorized branch creation, the three corrective Markdown changes, draft PR creation, comments, review submissions, and separately scoped issue actions. That authority explicitly excluded draft-to-ready transition, merge or auto-merge, ADR acceptance, Phase 0 preview exit, source-code implementation, Issue #23 state change, hosting-evidence completion, governance-task completion, ratification, release, deployment, and status promotion.

Read-only evidence for the PR #29 exact head shows no separate exact-head ready authority, no separate exact-head merge authority, no review submission, no published commit status, and no GitHub Actions workflow run. The operational authority comment and local/static validation statements do not supply lifecycle authority.

The Product Owner issued a post-merge exact-head content decision approving only the accuracy of the three-file corrective content on PR #29 exact head. That decision does not provide retrospective lifecycle authority and does not ratify the draft-to-ready transition or merge of PR #29.

The PR #29 technical merge does not ratify PR #28, PR #27, PR #26, or PR #25, validate or complete the closure of Issue #23, accept ADR-001 through ADR-007, approve Phase 0 preview exit, grant source-code authority, complete GOV-034, GOV-035, GOV-036, GOV-037, or GOV-038, or provide substantive approval or completion evidence. Phase 0 remains **In Progress**. Application implementation remains **Blocked**. Phase 0 preview exit remains **Not Ready**. P1 remains conditional and **Unverified**. ADR-001 through ADR-007, GD-007, PAY-1, OFF-1, TEN-1, REC-1, SLO-1, and DATA-1 remain **Proposed** selections or boundaries. GOV-034, GOV-035, GOV-036, GOV-037, and GOV-038 remain **Review**. JRN-003 and JRN-013 remain unresolved. Hosting evidence remains Pending, Not supplied, or Unverified.
