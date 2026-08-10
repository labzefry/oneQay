# oneQay Project Manifest

> Dokumen identitas teknis kanonis oneQay. Bila informasi di dokumen lain bertentangan, keputusan berstatus **Approved** di manifest ini berlaku sampai digantikan melalui ADR atau pull request yang disetujui.

## Metadata

| Atribut | Nilai | Status |
| --- | --- | --- |
| Product | oneQay | Approved |
| Tagline | The Future of Intelligent Business Management | Approved |
| Developer & Product Engineering Entity | Lab \| zefry | Approved |
| Category | Enterprise SaaS POS & ERP Platform | Approved |
| Enterprise Vision | Enterprise Intelligent Business Management Platform | Approved — GOV-051 substantive Product Owner decision; canonical representation published through PR #69 |
| Repository | `labzefry/oneQay` | Approved |
| Source of Truth | GitHub | Approved |
| Delivery model | Multi-tenant SaaS | Approved |
| Architecture baseline | Modular Monolith, Clean Architecture | Approved |
| Handbook version | 1.0 | Approved |
| Product version | Belum ditetapkan | Under Review |
| License | Proprietary / All Rights Reserved | Approved — DEC-010 product policy; final legal text remains Legal Review Required |

Engineering collaboration tooling is governed separately by `AI_CONSTITUTION.md` and is not product authorship or product attribution metadata. Canonical product/development attribution is **Lab | zefry**.

## Canonical product naming

The canonical product name is **oneQay**.

Current and future canonical references must use `oneQay`; immutable GitHub identifiers, repository path `labzefry/oneQay`, SHAs, historical commit messages, branch names, dan quoted historical evidence tidak ditulis ulang hanya untuk brand normalization.

Repository identifier `labzefry/oneQay`, immutable GitHub URLs, SHAs, commit messages, branch names, and quoted historical evidence are preserved as recorded and are not rewritten merely for brand normalization.

## Status definitions

| Status | Arti |
| --- | --- |
| Approved | Telah disetujui dan mengikat implementasi sesuai scope keputusan; status Approved tidak boleh dibaca melampaui boundary keputusan pemiliknya |
| Proposed | Usulan siap direview, belum mengikat |
| Under Review | Sedang dianalisis atau membutuhkan keputusan |
| Deferred | Sengaja ditunda sampai entry criteria terpenuhi |
| Deprecated | Tidak boleh digunakan untuk pekerjaan baru |

## Product intent

oneQay diarahkan menjadi platform intelligent business management yang menyatukan fungsi transaksi, POS, ERP, administrasi tenant, integrasi, marketplace, plugin, insight, dan AI-assisted capabilities dalam fondasi yang aman serta dapat berkembang dari shared hosting menuju Kubernetes tanpa mengubah business logic.

M6 telah mempublikasikan representasi canonical Enterprise Vision **Enterprise Intelligent Business Management Platform** melalui PR #69. Publication tersebut tidak dengan sendirinya mempromosikan substantive Enterprise Vision decision; Product Owner kemudian memberikan keputusan substantif terpisah GOV-051 yang **APPROVED** pada verified repository baseline `762149757e4bc1fa79cc16bc4761f4147be0f7ea` dan canonical artifact blob `bb1cace72a6fdb359e15e22467443d9f3916c336`. Approval tersebut menetapkan long-term product direction, tetapi tidak menyatakan bahwa seluruh capability telah terimplementasi atau production-ready dan tidak memberikan Sprint 14 atau implementation authority.

Product Owner kemudian memberikan substantive DEC-000 Product Vision and Decision Rights decision yang **APPROVED** pada decision baseline `792b2dc30636bc53baa7d66b43cf2dab4a348dd4`, tree `08f03b895d5e2ae7ca402e9866384990e126add3`, canonical owner artifact blob `843544b9e31dd4c47638b88dd204f4e594295df4`, dan readiness artifact blob `b493a5d66edc1bbffab0126bdacf2ca1ce14fa8f`. DEC-000 mengesahkan GD-003 dalam boundary product/discovery governance saja dan tidak memberikan implementation authority.

Product Owner kemudian memberikan substantive DEC-001 MVP Scope and Non-Scope decision yang **APPROVED** pada decision baseline `17f156b9861972b4924a5ed01bfabd5a1a79461a` dan verified tree `33241c18a1b7da2efc7dd2889c13c25c6e8526d5`. DEC-001 menetapkan **POS CORE TRANSACTION & OUTLET OPERATIONS** sebagai first bounded MVP delivery slice dengan actor, journey, dependency, explicit non-scope, outcome, guardrail, dan open-item boundary yang direkam di `docs/handbook/DEC_001_DECISION_RECORD.md`. DEC-001 tidak memberi Sprint 14, application/business implementation, SQL/schema/migration, production database, deployment, release, ADR acceptance, JRN resolution, atau production-readiness authority.

Product Owner kemudian memberikan substantive DEC-002 Backend Language / Application Framework decision yang **APPROVED** pada decision baseline `504b10be44d45dfcfec9b6cfed4f72ed5748b564` dan verified tree `e4622a45f9f298b95358b3d662be3cd48607e4d9`. DEC-002 menetapkan PHP sebagai backend language, Laravel sebagai application framework, Modular Monolith First + Clean Architecture sebagai architecture direction, Domain/Application tetap framework-independent, dan framework berperan sebagai delivery/composition/infrastructure. Keputusan direkam di `docs/handbook/DEC_002_DECISION_RECORD.md` dan direkonsiliasi melalui ADR-001 tanpa memberi implementation, dependency-change, Sprint 14, deployment, release, atau production-readiness authority.

Product Owner kemudian memberikan substantive DEC-003 Frontend / PWA Stack decision yang **APPROVED** pada decision baseline `dcb7e3f8de890530a00a0dd4fd310bc10762c72f` dan verified tree `b78d1f1452469a8ba856092e647bef92410f2517`. DEC-003 menetapkan Vue 3, Vue Composition API, TypeScript-first, Inertia untuk first-party authenticated Web/PWA delivery, Vite, local-first state dengan Pinia secara bounded, Modern Monolith Web Delivery + Explicit API Boundaries, dan PWA foundation dengan service-worker/cache security boundary. Keputusan direkam di `docs/handbook/DEC_003_DECISION_RECORD.md` dan direkonsiliasi melalui ADR-002 tanpa memberi package/dependency installation, frontend/backend implementation, Sprint 14, deployment, release, atau production-readiness authority.

Product Owner kemudian memberikan substantive DEC-004 Android Approach decision yang **APPROVED** pada decision baseline `97b2e5066118af2b3e9467afc71e84dce228eb38` dan verified tree `2f979948184f475b52cf87b2d105c56364ebe883`. DEC-004 menetapkan **Hybrid Staged Approach**, Native Android menggunakan Kotlin, Jetpack Compose, PWA sebagai preferred general mobile-capable channel, explicit API/mobile boundary, bounded native device adapters, DEC-006 authentication boundary, DEC-008 offline boundary, minimal tenant/session-scoped local state, dan distribution compatibility direction. Keputusan direkam di `docs/handbook/DEC_004_DECISION_RECORD.md` dan direpresentasikan melalui ADR-008 tanpa memberi Android project/source, Gradle/dependency, API implementation, Sprint 14, deployment, release, atau production authority.

Product Owner kemudian memberikan substantive DEC-005 Database Engine and Physical Tenancy Model decision yang **APPROVED** pada decision baseline `63646e1cccc611a1911c452397059983030dfe66` dan verified tree `80cd3bbf1a0c1d454e73c89f17d8896941f369cd`. DEC-005 menetapkan **MySQL Server** sebagai canonical relational database engine family, supported MySQL LTS-family boundary dengan exact version deferred, shared database/shared schema sebagai default physical tenancy dengan immutable tenant isolation key, bounded future hybrid isolation path, Application-authoritative tenant authorization with database defense-in-depth, database/vendor behavior sebagai Infrastructure concern, compatible/recoverable schema-evolution principle, dan verified-restoration recoverability principle. Keputusan direkam di `docs/handbook/DEC_005_DECISION_RECORD.md` dan direpresentasikan melalui reconciled ADR-003 tanpa memberi schema/SQL/migration/database implementation, dependency, Sprint 14, deployment, release, atau production authority.

Product Owner kemudian memberikan substantive DEC-006 Authentication / MFA / Session Architecture decision yang **APPROVED** pada decision baseline `c495f1b1f6e4e624bf3669ee94a786a1d7e865ce` dan verified tree `795d53f326e6ee52474f79b284dea1ce744da`. DEC-006 menetapkan first-party oneQay platform identity, server-side first-party Web/PWA session, explicit Android/API token boundary, server-authoritative rotation/revocation, TOTP privileged MFA baseline, WebAuthn/passkey evolution direction, adaptive password/credential security principles, high-risk recovery dengan JRN-003 tetap unresolved, global identity + tenant memberships, controlled support impersonation/break-glass separation, dan future OIDC-compatible federation. Keputusan direkam di `docs/handbook/DEC_006_DECISION_RECORD.md` dan direpresentasikan melalui reconciled ADR-004 tanpa memberi authentication implementation, package/dependency, identity schema, SQL/migration, Sprint 14, deployment, release, atau production authority.

Product Owner kemudian memberikan substantive DEC-007 Payment Provider and Compliance Boundary decision yang **APPROVED** pada decision baseline `50955d101c455c6af7356197d9e06d6d76e753bb` dan verified tree `2987eccc6bf4ba8ece23ee2343b178e518a454b3`. DEC-007 menetapkan **CASH-FIRST + CONFIGURABLE MANUAL / EXTERNAL RECORDED TENDERS** untuk first bounded MVP, memisahkan `CASH_COUNTED`, `OPERATOR_RECORDED`, dan future `PROVIDER_VERIFIED` evidence, menetapkan future provider-abstracted electronic-payment boundary, menunda provider selection, mempertahankan sale-level payment sufficiency dan idempotency/replay controls, memisahkan refund/reversal/dispute dan settlement/reconciliation, meminimalkan restricted payment-account-data exposure, mempertahankan jurisdiction-neutral architecture serta DEC-008 offline ownership, dan merekonsiliasi ADR-005 sambil menjaga historical PAY-1 provenance. Keputusan direkam di `docs/handbook/DEC_007_DECISION_RECORD.md` dan direpresentasikan melalui materially reconciled ADR-005 tanpa memberi provider selection/account/contract, payment source, package, schema/SQL/migration, real-money processing, offline, Sprint 14, deployment, release, atau production authority.

Product Owner kemudian memberikan substantive DEC-008 Offline POS Semantics and Conflict Resolution decision yang **APPROVED** pada decision baseline `21f1b2e938d9f6a42b1dd2ff717c4e049b5119d7` dan verified tree `8cf993f0c82c84bdc46a18aa70c4cb5425b89ac6`. DEC-008 menetapkan **STAGED / HYBRID OFFLINE ARCHITECTURE** dengan first bounded MVP tetap **ONLINE-AUTHORITATIVE TRANSACTIONS**, O1 bounded degraded/read-only direction, future O2 provisional client operations yang memerlukan server validation/acceptance, stable operation identity, deterministic replay/idempotency, explicit conflict classification/resolution, server-authoritative inventory/payment/tenant/shift correctness, bounded local-data security, bounded causal ordering, offline reconciliation/failure recovery/audit, dan Native Android sebagai preferred initial future O2 transactional-offline channel. Keputusan direkam di `docs/handbook/DEC_008_DECISION_RECORD.md` dan direpresentasikan melalui materially reconciled ADR-006 tanpa memberi offline transaction source, Android/PWA implementation, local database/queue technology, schema/SQL/migration, package/dependency, Sprint 14, deployment, release, atau production authority.

Product Owner kemudian memberikan substantive DEC-009 Deployment Stage 1 Runtime Requirements decision yang **APPROVED** pada decision baseline `0fdc0a53403f16fbc6908630ea350af2c0de466b` dan verified tree `45c0aa49657db8f95ca08e662ec641e6d9d5f25a`. DEC-009 menetapkan **CAPABILITY-BASED STAGED / HYBRID PORTABILITY MODEL**, P1 Shared Hosting/cPanel sebagai conditional candidate yang **NOT SELECTED**, P2 Managed/Hardened VPS or Server sebagai fallback execution class, Stage-1 `Preview` environment, PHP `>=8.2` baseline, Build Once / Deploy Trusted Artifact, DEC-005 canonical MySQL Server requirement, bounded scheduler/worker/session/cache/storage/HTTPS/secrets/observability/backup/restore/release/rollback requirements, dan provider-neutral Domain/Application portability. Keputusan direkam di `docs/handbook/DEC_009_DECISION_RECORD.md` dan direpresentasikan melalui materially reconciled ADR-007 tanpa memberi hosting procurement, infrastructure provisioning, dependency installation, schema/SQL/migration, deployment, release, Sprint 14, atau production authority.

Product Owner kemudian memberikan substantive DEC-010 Product License and Third-Party Notice Policy decision yang **APPROVED** pada decision baseline `5cc572675dd7871a3ca841cedf06fbc8ea74f839` dan verified tree `72704fffb97aa053092ba20271728bd2b0198982`. DEC-010 menetapkan **PROPRIETARY / ALL RIGHTS RESERVED** sebagai default product/source licensing direction, memisahkan repository visibility dari product rights, membatasi third-party commercialization/distribution tanpa separately authorized written agreement, menetapkan external-contribution legal gating, dependency-license acceptance matrix dan pre-adoption fail-closed compatibility gate, NOTICE/SBOM traceability, trademark separation, plugin/marketplace serta AI/model/data licensing boundaries, dan asset provenance requirements. Keputusan direkam di `docs/handbook/DEC_010_DECISION_RECORD.md`; final externally relied-upon legal text tetap **LEGAL REVIEW REQUIRED** dan DEC-010 tidak memberi dependency adoption, implementation, distribution, deployment, release, Sprint 14, atau production authority.

## Current delivery gate

| Item | Status | Gate |
| --- | --- | --- |
| Handbook 1.0 governance baseline | Approved | PR #1 disetujui dan di-merge ke `main` |
| Phase 0 governance and discovery | In Progress | Phase 0 exit belum disetujui secara eksplisit |
| Bounded Platform Foundation through Sprint 13 | Published | Sprint 12 dan Sprint 13 adalah fakta repository yang telah dipublikasikan melalui authority terpisah |
| M5.1 Canonical State Reconciliation | Published / Complete | PR #66 |
| M5.2 CI & Lifecycle Control Hardening | Published / Enforcement Complete | PR #67 |
| M5.3 Governance & Program State Synchronization | Published / Complete | PR #68; published commit `e45f5b4c0f143abc6e255e4e8550bf3504348aae` |
| M6 Enterprise Vision Canonicalization | Published / Publication Complete | PR #69; source head `e6a3345b09a6b270ac7e09abd78c6356f426e363`; published commit `0b7b28028966ac38af0f32960054210c3a083916`; source/published tree `567df997bae70090b19465c75e4cc3b1e23b6579` |
| GOV-051 Enterprise Vision substantive decision | Approved / Decision Complete | Product Owner APPROVED `Enterprise Intelligent Business Management Platform` on verified baseline `762149757e4bc1fa79cc16bc4761f4147be0f7ea`; approval is product direction only, not implementation authority |
| DEC-000 Product Vision and Decision Rights | Approved / Decision Complete | Product Owner APPROVED D-000-01 through D-000-06 on baseline `792b2dc30636bc53baa7d66b43cf2dab4a348dd4`; `docs/handbook/DEC_000_DECISION_RECORD.md`; GD-003 Approved within DEC-000 boundary only |
| DEC-001 MVP Scope and Non-Scope | Approved / Decision Complete | Product Owner APPROVED **POS CORE TRANSACTION & OUTLET OPERATIONS** on baseline `17f156b9861972b4924a5ed01bfabd5a1a79461a`; `docs/handbook/DEC_001_DECISION_RECORD.md`; no implementation authority |
| DEC-002 Backend Language / Application Framework | Approved / Decision Complete | Product Owner APPROVED PHP + Laravel on baseline `504b10be44d45dfcfec9b6cfed4f72ed5748b564`; `docs/handbook/DEC_002_DECISION_RECORD.md`; ADR-001 Accepted after reconciliation; no implementation authority |
| DEC-003 Frontend / PWA Stack | Approved / Decision Complete | Product Owner APPROVED Vue 3 + Inertia + Vite with TypeScript-first, explicit API/mobile boundaries, local-first state, and bounded PWA direction on baseline `dcb7e3f8de890530a00a0dd4fd310bc10762c72f`; `docs/handbook/DEC_003_DECISION_RECORD.md`; ADR-002 Accepted after reconciliation; no implementation authority |
| DEC-004 Android Approach | Approved / Decision Complete | Product Owner APPROVED Hybrid Staged Approach with Kotlin + Jetpack Compose on baseline `97b2e5066118af2b3e9467afc71e84dce228eb38`; `docs/handbook/DEC_004_DECISION_RECORD.md`; ADR-008; no implementation authority |
| DEC-005 Database Engine and Physical Tenancy Model | Approved / Decision Complete | Product Owner APPROVED MySQL Server + shared database/shared schema default on baseline `63646e1cccc611a1911c452397059983030dfe66`; `docs/handbook/DEC_005_DECISION_RECORD.md`; reconciled ADR-003; no database/schema/SQL/migration implementation authority |
| DEC-006 Authentication / MFA / Session Architecture | Approved / Decision Complete | Product Owner APPROVED first-party identity + Web/PWA session + Android/API token boundary on baseline `c495f1b1f6e4e624bf3669ee94a786a1d7e865ce`; `docs/handbook/DEC_006_DECISION_RECORD.md`; reconciled ADR-004; JRN-003 unresolved; no implementation/package/schema/migration authority |
| DEC-007 Payment Provider and Compliance Boundary | Approved / Decision Complete | Product Owner APPROVED cash-first + configurable manual/external recorded tenders and provider-abstracted future electronic-payment direction on baseline `50955d101c455c6af7356197d9e06d6d76e753bb`; `docs/handbook/DEC_007_DECISION_RECORD.md`; reconciled ADR-005; provider selection deferred; no payment/provider/package/schema/SQL/implementation authority |
| DEC-008 Offline POS Semantics and Conflict Resolution | Approved / Decision Complete | Product Owner APPROVED Staged / Hybrid Offline Architecture on baseline `21f1b2e938d9f6a42b1dd2ff717c4e049b5119d7`; first-MVP online-authoritative transactions; future provisional server-validated offline direction; `docs/handbook/DEC_008_DECISION_RECORD.md`; reconciled ADR-006; no offline/source/schema/package implementation authority |
| DEC-009 Deployment Stage 1 Runtime Requirements | Approved / Decision Complete | Product Owner APPROVED capability-based Stage-1 runtime architecture on baseline `0fdc0a53403f16fbc6908630ea350af2c0de466b`; `docs/handbook/DEC_009_DECISION_RECORD.md`; reconciled ADR-007; P1 conditional/not selected; no deployment/implementation authority |
| DEC-010 Product License and Third-Party Notice Policy | Approved / Decision Complete | Product Owner APPROVED Proprietary / All Rights Reserved product policy on baseline `5cc572675dd7871a3ca841cedf06fbc8ea74f839`; `docs/handbook/DEC_010_DECISION_RECORD.md`; external contribution legally gated; dependency-license pre-adoption gate; final legal text remains Legal Review Required; no implementation/distribution/deployment authority |
| Final/business application implementation | Blocked | Tidak ada authority untuk implementasi business/final/production application baru |
| Sprint 14 | Not Authorized | Memerlukan Product Owner authority terpisah |
| Production readiness | NO-GO | Tidak ada deployment, release, atau production-migration authority |

Rencana kickoff berada di `docs/handbook/PHASE_0_KICKOFF.md`.

## Canonical Phase 0 semantics

**Phase 0 — Governance and Discovery: In Progress** adalah status program governance/discovery. Status tersebut tidak berarti repository tidak memiliki source code teknis dan tidak menghapus source yang telah dipublikasikan secara sah sebagai bounded Platform Foundation.

Published Sprint 12 dan Sprint 13 tetap merupakan fakta repository. Publikasi itu tidak berarti Phase 0 telah selesai, tidak otomatis memulai Phase 1 secara penuh, tidak menyetujui final business application, dan tidak memberi authority untuk Sprint 14.

Mulai M5.3, frasa **application implementation Blocked** harus dibaca sebagai **final/business/production application implementation Blocked**. Tidak ada source authority baru yang diberikan oleh klarifikasi ini.

Historical Phase 0 no-code language dan lifecycle discrepancies tetap dipertahankan sebagai fakta historis. M5.3 hanya menyelaraskan makna kanonik saat ini; M5.3 tidak menulis ulang approval, merge, review, atau sequencing masa lalu.

## M6 Enterprise Vision boundary

M6 memisahkan secara tegas:

1. Product Vision;
2. Product Capability Map;
3. Product Architecture Direction;
4. Delivery Roadmap;
5. Implementation Authority.

Published canonical Enterprise Vision representation oneQay adalah:

**Enterprise Intelligent Business Management Platform**.

High-level capability families mencakup Core Business Platform, Platform Capabilities, Extensibility, AI Platform, dan Channels. Detailnya berada di `docs/handbook/ENTERPRISE_VISION.md`. PR #69 mengesahkan representasi dan provenance; GOV-051 kemudian secara terpisah mengesahkan substantive Enterprise Vision sebagai binding long-term product direction.

Capability-map presence tidak memberikan implementation authority. GOV-051 sendiri tidak mempromosikan bounded context Proposed, ADR, GD-003, GD-007, JRN, Sprint 14, final/business application implementation, deployment, release, SQL/migration, production database modification, atau production readiness. GD-003 kemudian secara terpisah **Approved** melalui substantive DEC-000, tetap tanpa implementation authority.

## Product identity and engineering-tooling boundary

- Canonical product attribution: **Lab | zefry**.
- Canonical product name: **oneQay**.
- Nama alat atau model AI yang dipakai dalam engineering adalah governance/tooling metadata, bukan identitas produk, bukan author produk, dan bukan attribution source code.
- Collaboration model tetap diatur melalui `AI_CONSTITUTION.md` dan GD-002.
- Tidak boleh menambahkan attribution yang menyatakan source code atau produk dibuat oleh AI.
- AI Assistant sebagai capability produk tetap merupakan domain produk tersendiri dan statusnya tidak dipromosikan hanya karena Enterprise Vision memetakan AI Platform secara directional.

## Governance decision register

| ID | Keputusan | Status | Dokumen pemilik |
| --- | --- | --- | --- |
| GD-001 | GitHub sebagai Single Source of Truth | Approved | `AI_CONSTITUTION.md` |
| GD-002 | ChatGPT + GitHub sebagai collaboration model eksklusif | Approved | `AI_CONSTITUTION.md` |
| GD-003 | Product vision dan decision rights | **Approved — DEC-000** | `docs/handbook/PRODUCT_VISION_AND_DECISION_RIGHTS.md`; `docs/handbook/DEC_000_DECISION_RECORD.md` |
| GD-004 | Final/business application implementation tetap diblokir sampai gate yang berlaku disetujui | Approved | `docs/handbook/PHASE_0_KICKOFF.md` |
| GD-005 | Stakeholder and actor map | Proposed | `docs/handbook/STAKEHOLDER_AND_ACTOR_MAP.md` |
| GD-006 | Current process and user journeys | Proposed | `docs/handbook/CURRENT_PROCESS_AND_USER_JOURNEYS.md` |
| GD-007 | Domain event storming | Proposed | `docs/handbook/DOMAIN_EVENT_STORMING.md`; corrections tracked in Issue #10/#12; governance controls tracked in Issue #14/#16/#18/#20 |

GD-003 is Approved only through the explicit Product Owner substantive DEC-000 decision on baseline `792b2dc30636bc53baa7d66b43cf2dab4a348dd4` and the corresponding bounded decision record. The approval governs Product Vision and Decision Rights only. It does not approve DEC-001 through DEC-012, accept an ADR, promote GD-007, resolve JRN-003/JRN-013, start Sprint 14, or grant final/business/production implementation, SQL/migration, production DB, deployment, release, or production-readiness authority.

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
| Android Native | Kapabilitas perangkat dan pengalaman native | Approved — DEC-004 bounded complementary delivery direction; implementation not authorized |
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
| Default isolation model | Shared database + shared schema dengan tenant-scoped data | Approved — DEC-005; no schema/implementation authority |
| Dedicated deployment option | Bounded future dedicated database/storage isolation untuk justified tenant | Approved direction — DEC-005; implementation separately gated |
| Tenant timezone/currency/locale | Wajib tersimpan sebagai konfigurasi tenant | Approved |
| Cross-tenant query | Deny by default; hanya platform operation terotorisasi | Approved |

## Deployment evolution

| Stage | Environment | Status | Exit criteria utama |
| ---: | --- | --- | --- |
| 1 | Capability-Based Preview | Approved direction — DEC-009; P1 cPanel conditional/not selected | Mandatory runtime/security/database/deployment/recovery/observability capabilities verified |
| 2 | Managed / Hardened VPS or Server | Planned / P2 fallback execution class | Used when P1 cannot satisfy a mandatory Stage-1 capability; no provider selected |
| 3 | Dedicated Server | Planned | Beban dan isolasi memerlukan host khusus |
| 4 | Docker | Planned | Pipeline, observability, dan state externalization siap |
| 5 | Cloud | Planned | Autoscaling, managed services, dan DR layak biaya |
| 6 | Kubernetes | Deferred | Skala dan kompleksitas operasional membenarkan orkestrasi |

Perpindahan stage tidak boleh mengubah domain atau business logic.

## Technology decision register

| ID | Keputusan | Status | Dokumen pemilik |
| --- | --- | --- | --- |
| TD-001 | Bahasa dan framework backend | Approved — DEC-002 | `docs/handbook/DEC_002_DECISION_RECORD.md`; `docs/adr/ADR-001-technical-preview-backend.md` |
| TD-002 | Framework web frontend | Approved — DEC-003 | `docs/handbook/DEC_003_DECISION_RECORD.md`; `docs/adr/ADR-002-technical-preview-frontend-pwa.md` |
| TD-003 | Android native stack | Approved — DEC-004 | `docs/handbook/DEC_004_DECISION_RECORD.md`; `docs/adr/ADR-008-android-delivery-approach.md` |
| TD-004 | Relational database engine | Approved — DEC-005 | `docs/handbook/DEC_005_DECISION_RECORD.md`; `docs/adr/ADR-003-technical-preview-database-tenancy.md`; `DATABASE.md` |
| TD-005 | Cache dan queue technology | Deferred | ADR |
| TD-006 | Authentication protocol/provider | Approved — DEC-006 | `docs/handbook/DEC_006_DECISION_RECORD.md`; `docs/adr/ADR-004-technical-preview-authentication.md`; `SECURITY.md` |
| TD-007 | Observability stack | Deferred | DEPLOYMENT.md / ADR |
| TD-008 | Payment gateway strategy | Approved — DEC-007 | `docs/handbook/DEC_007_DECISION_RECORD.md`; `docs/adr/ADR-005-technical-preview-payment-boundary.md`; provider selection deferred |
| TD-009 | AI provider and data boundary | Under Review | SECURITY.md / ADR |
| TD-010 | Deployment Stage 1 runtime requirements | Approved — DEC-009 | `docs/handbook/DEC_009_DECISION_RECORD.md`; `docs/adr/ADR-007-technical-preview-deployment.md`; `DEPLOYMENT.md` |

No framework or vendor is selected merely because it appears in historical candidate material. PHP/Laravel is binding only through the explicit Product Owner substantive DEC-002 decision and Accepted ADR-001 within that exact boundary. Vue 3/Inertia/Vite is binding only through the explicit Product Owner substantive DEC-003 decision and Accepted ADR-002 within that exact boundary. Kotlin/Jetpack Compose and the Hybrid Staged Android direction are binding only through the explicit Product Owner substantive DEC-004 decision and ADR-008 within that exact boundary; this does not create Android implementation authority. MySQL Server and the shared-database/shared-schema default are binding only through the explicit Product Owner substantive DEC-005 decision and reconciled ADR-003 within that exact boundary; this does not create schema/SQL/migration/database implementation authority. First-party oneQay identity, server-side Web/PWA session, explicit Android/API token boundary, TOTP privileged MFA baseline, passkey evolution, and tenant-aware membership are binding only through substantive DEC-006 and reconciled ADR-004 within that exact boundary; this does not create authentication/package/schema/migration implementation authority. Cash-first + configurable manual/external recorded tenders, explicit operator-recorded/provider-verified evidence semantics, and provider-abstracted future electronic-payment architecture are binding only through substantive DEC-007 and reconciled ADR-005 within that exact boundary; no specific provider, payment implementation, package, schema/SQL/migration, real-money processing, or offline authority is created. Staged / Hybrid Offline Architecture, first-MVP online-authoritative transactions, and future provisional server-validated offline operation semantics are binding only through substantive DEC-008 and reconciled ADR-006 within that exact boundary; no offline source implementation, Android/PWA transactional-offline implementation, queue/local-database technology, schema/SQL/migration, Sprint 14, deployment, release, or production authority is created. Capability-Based Staged / Hybrid Portability, Stage-1 Preview runtime requirements, P1 conditional/not-selected status, P2 fallback class, Build Once / Deploy Trusted Artifact, and the DEC-005 MySQL Server requirement are binding only through substantive DEC-009 and reconciled ADR-007 within that exact boundary; no infrastructure procurement/provisioning, source/dependency/schema/SQL/migration implementation, deployment, release, Sprint 14, or production authority is created.

## Environment classes

| Environment | Data policy | Deployment source | Approval |
| --- | --- | --- | --- |
| Local | Synthetic only | Developer branch | Tidak untuk produksi |
| Test / CI | Synthetic/masked | CI artifact | Otomatis sesuai quality gate |
| Preview | Masked; production-like | Release candidate | Release Manager / governed Preview authority |
| Production | Real tenant data | Signed release artifact | Authorized approver |

Historical human-facing `Staging` terminology must be mapped to runtime `Preview` and must not create a fifth implicit environment class.

## Dependency policy

- Dependency baru membutuhkan tujuan, owner, license, maintenance status, security review, dan exit strategy.
- Version harus dikunci secara reproducible melalui lockfile.
- Dependency tidak boleh mengakses data, jaringan, atau filesystem melebihi kebutuhan.
- Critical vulnerability memblokir release kecuali exception terdokumentasi dan memiliki expiry.
- License compatibility is a **PRE-ADOPTION** gate under DEC-010. Permissive licenses are allow-with-compliance; weak/file-level copyleft is conditional; strong and network copyleft are blocked by default; proprietary/commercial is conditional; source-available/non-OSI custom is blocked pending review; unknown/custom/no-license is blocked.
- License intake mencakup direct/transitive dependency, exact version/license, provenance, runtime/build/dev classification, server/distributed/mobile/browser usage, modification, notice/attribution, source-offer obligations, dan commercial rights. Unknown license fails closed.
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
| `docs/handbook/ENTERPRISE_VISION.md` | Published canonical Enterprise Vision representation, capability map, dan conceptual product evolution; substantive status Approved through GOV-051; no implementation authority implied |
| `docs/handbook/PRODUCT_VISION_AND_DECISION_RIGHTS.md` | Approved Product Vision and Decision Rights through DEC-000; no implementation authority implied |
| `docs/handbook/DEC_000_DECISION_RECORD.md` | DEC-000 substantive Product Owner decision provenance, dispositions, and boundaries |
| `docs/handbook/DEC_001_DECISION_RECORD.md` | DEC-001 substantive Product Owner MVP scope/non-scope decision provenance, approved bounded slice, deferred boundaries, and no-implementation authority |
| `docs/handbook/DEC_002_DECISION_RECORD.md` | DEC-002 substantive Product Owner backend language/application framework decision provenance, approved PHP/Laravel boundary, and no-implementation authority |
| `docs/handbook/DEC_003_DECISION_RECORD.md` | DEC-003 substantive Product Owner frontend/Web-PWA decision provenance, approved Vue/Inertia/Vite boundary, explicit API/mobile independence, PWA guardrails, and no-implementation authority |
| `docs/handbook/DEC_004_DECISION_RECORD.md` | DEC-004 substantive Product Owner Android delivery decision provenance, Hybrid Staged Approach, Kotlin/Jetpack Compose direction, explicit API/device/offline boundaries, and no-implementation authority |
| `docs/handbook/DEC_005_DECISION_RECORD.md` | DEC-005 substantive Product Owner database-engine/physical-tenancy decision provenance, MySQL Server, shared-schema default, tenant-isolation/recoverability boundaries, and no-implementation authority |
| `docs/handbook/DEC_006_DECISION_RECORD.md` | DEC-006 substantive Product Owner identity/authentication architecture provenance, Web/PWA session, Android/API token boundary, MFA/passkey/recovery/tenant-membership/federation directions, and no-implementation authority |
| `docs/handbook/DEC_007_DECISION_RECORD.md` | DEC-007 substantive Product Owner payment architecture/compliance provenance, cash/manual/external tender direction, provider abstraction/deferral, evidence/sufficiency/idempotency/refund/settlement/PCI/jurisdiction/offline boundaries, and no-implementation authority |
| `docs/handbook/DEC_008_DECISION_RECORD.md` | DEC-008 substantive Product Owner offline architecture provenance, Staged / Hybrid Offline Architecture, online-authoritative first MVP, future provisional server-validated operations, replay/conflict/security/reconciliation boundaries, and no-implementation authority |
| `docs/handbook/DEC_009_DECISION_RECORD.md` | DEC-009 substantive Product Owner Stage-1 runtime provenance, capability-based portability model, Preview environment, P1 conditional/not-selected status, P2 fallback class, runtime/build/database/recovery boundaries, and no-deployment/no-implementation authority |
| `docs/handbook/DEC_010_DECISION_RECORD.md` | DEC-010 substantive Product Owner product-license and third-party notice policy provenance, Proprietary / All Rights Reserved direction, contributor/dependency/notice/trademark/plugin/AI/asset boundaries, and Legal Review Required final-text status |
| `docs/adr/ADR-001-technical-preview-backend.md` | Accepted representation of DEC-002 with preserved Technical Preview provenance and framework-independence guardrails |
| `docs/adr/ADR-002-technical-preview-frontend-pwa.md` | Accepted representation of DEC-003 with preserved F1 Technical Preview provenance, explicit API/mobile boundaries, PWA/offline guardrails, and implementation-authority separation |
| `docs/adr/ADR-003-technical-preview-database-tenancy.md` | Accepted representation of DEC-005 after publication; preserves D1 Technical Preview provenance while establishing MySQL Server + shared database/shared schema default and no schema/SQL/migration authority |
| `docs/adr/ADR-004-technical-preview-authentication.md` | Accepted representation of DEC-006 after publication; preserves A1 Technical Preview provenance while establishing hybrid Web/PWA session + Android/API authentication, privileged MFA, recovery/JRN-003, tenant-aware identity, and no implementation authority |
| `docs/adr/ADR-005-technical-preview-payment-boundary.md` | Accepted representation of DEC-007 after governed publication; preserves historical PAY-1 synthetic cash-only provenance while establishing cash-first + manual/external recorded tenders and provider-abstracted future electronic-payment architecture with no implementation/provider-selection authority |
| `docs/adr/ADR-006-technical-preview-offline-boundary.md` | Accepted representation of DEC-008 after governed publication; preserves historical OFF-1 online-only Technical Preview provenance while establishing staged/hybrid offline architecture and no offline implementation authority |
| `docs/adr/ADR-007-technical-preview-deployment.md` | Accepted representation of DEC-009 only after governed publication; preserves historical P1/P2 Technical Preview provenance while establishing capability-based Stage-1 Preview runtime requirements and no deployment authority |
| `docs/adr/ADR-008-android-delivery-approach.md` | Accepted representation of DEC-004 after successful publication; Hybrid Staged Android direction with Kotlin/Jetpack Compose, PWA complementarity, DEC-006/DEC-008 boundaries, and no-implementation authority |
| API_SPEC.md | Governance API |
| DATABASE.md | Governance data dan skema; canonical MySQL Server/tenancy direction owned by DEC-005 |
| SECURITY.md | Security baseline |
| DEPLOYMENT.md | Operasi dan deployment; Stage-1 capability architecture owned by DEC-009 |
| TESTING.md | Quality strategy |

## Initial risks

| ID | Risiko | Severity | Mitigasi awal |
| --- | --- | --- | --- |
| R-001 | Kebocoran data lintas tenant | Critical | Tenant context enforcement dan isolation tests |
| R-002 | Scope POS/ERP terlalu luas | High | MVP boundary, capability-map semantics, dan phased roadmap |
| R-003 | Ketergantungan shared hosting | High | DEC-009 capability-based portability, P1 evidence gates, P2 fallback, and infrastructure abstraction |
| R-004 | Plugin merusak keamanan/stabilitas | High | Signed package, capability policy, sandbox strategy |
| R-005 | Update gagal dan merusak tenant | Critical | Backup, integrity check, health gate, rollback |
| R-006 | AI memproses data sensitif | High | Data classification, redaction, consent, provider policy |
| R-007 | Enterprise Vision disalahartikan sebagai implementation authority | High | Pisahkan Vision, Capability Map, Architecture Direction, Roadmap, dan explicit Product Owner implementation authority |
| R-008 | Inconsistent product capitalization creates identity drift | Medium | Canonical form `oneQay`; normalize current canonical docs without rewriting immutable history |
| R-009 | Third-party license, contribution, atau asset provenance tidak kompatibel dengan proprietary product policy | High | DEC-010 pre-adoption fail-closed license gate, contributor legal gating, auditable notice/SBOM inventory, dan qualified legal review where applicable |

## Mandatory update rule

Setiap perubahan resmi minimal memperbarui manifest, task, dan changelog bila status, scope, capability, keputusan, atau risiko proyek berubah. Perubahan arsitektur, API, database, deployment, security, testing, dan UI/UX juga harus memperbarui dokumen pemiliknya.

## Approval

Baseline governance Handbook 1.0 disetujui melalui PR #1. Item berstatus Approved mengikat seluruh pekerjaan berikutnya hanya sesuai scope keputusan masing-masing; item Proposed, Under Review, dan Deferred tidak boleh diperlakukan sebagai keputusan final.
