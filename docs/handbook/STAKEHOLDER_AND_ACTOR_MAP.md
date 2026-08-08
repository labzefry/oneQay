# oneQay Stakeholder and Actor Map

> **Status:** Proposed — menunggu discovery dan persetujuan Product Owner  
> **Phase:** 0 — Governance & Discovery  
> **Owner:** Product Owner oneQay  
> **Tracking:** GitHub Issue #4  
> **Dependency:** Product Vision and Decision Rights berstatus Proposed

## Purpose

Dokumen ini memetakan pihak yang memengaruhi atau dipengaruhi oneQay serta actor yang berinteraksi dengan produk. Peta ini menjadi hipotesis kerja untuk user journey, event storming, MVP slicing, data classification, threat modeling, permission design, dan operational readiness.

Tidak ada stakeholder, actor, kebutuhan, atau workflow di dokumen ini yang dianggap tervalidasi hanya karena ditulis atau di-merge. Validasi membutuhkan evidence discovery dan persetujuan manusia yang berwenang.

## Scope and non-scope

Dokumen ini mencakup:

- stakeholder internal dan eksternal;
- human actor, external organization, dan system actor;
- tujuan, aktivitas, decision participation, data, serta risiko actor;
- platform boundary dan tenant boundary;
- engagement hypothesis dan validation plan;
- separation of duties untuk tindakan berisiko.

Dokumen ini tidak mencakup:

- persona demografis final;
- desain layar atau user flow final;
- permission matrix implementatif;
- finalisasi MVP atau bounded context;
- keputusan framework, vendor, database, API, payment, deployment, atau AI provider;
- source code, migration, konfigurasi runtime, atau data produksi.

## Definitions

| Istilah | Definisi |
|---|---|
| Stakeholder | Manusia, kelompok, organisasi, atau otoritas yang memengaruhi keputusan atau menerima dampak oneQay, meski tidak menggunakan aplikasi secara langsung. |
| Human actor | Peran manusia yang berinteraksi dengan oneQay untuk mencapai tujuan tertentu. Satu manusia dapat memegang beberapa actor role sesuai authorization. |
| System actor | Sistem eksternal atau proses otomatis yang berinteraksi melalui kontrak; bukan manusia dan tidak memiliki decision authority. |
| Persona | Representasi berbasis riset mengenai pola kebutuhan dan perilaku pengguna; belum ditetapkan pada tahap ini. |
| Role | Kumpulan tanggung jawab dan kewenangan bisnis; tidak otomatis sama dengan role/permission teknis. |
| Tenant-scoped actor | Actor yang kewenangannya dibatasi pada Tenant ID dan scope organisasi/outlet yang tervalidasi. |
| Platform-scoped actor | Actor terotorisasi untuk operasi platform lintas tenant dengan least privilege, purpose limitation, dan audit tambahan. |

## Evidence status

| Status | Arti |
|---|---|
| Approved | Identitas atau aturan telah disetujui dan mengikat sesuai dokumen kanonis. |
| Proposed | Hipotesis siap divalidasi, belum mengikat desain final. |
| Under Review | Evidence sedang dikumpulkan atau konflik masih diselesaikan. |
| Deferred | Tidak diprioritaskan sampai trigger yang tercatat terpenuhi. |

Semua actor baru pada dokumen ini berstatus **Proposed** kecuali fakta identitas/governance yang sudah Approved pada `PROJECT_MANIFEST.md`.

## Context boundaries

### Project governance boundary

Mencakup Product Owner, decision owner, reviewer, implementer, dan release authority yang mengelola pengembangan oneQay melalui GitHub. Peran ini tidak otomatis memperoleh akses ke data tenant atau production.

### Platform boundary

Mencakup operasi SaaS lintas tenant seperti tenant lifecycle, subscription, platform security, support, incident response, release, dan audit. Akses lintas tenant harus deny by default, time-bound bila relevan, purpose-limited, serta diaudit.

### Tenant boundary

Mencakup pemilik bisnis, administrator tenant, manager, cashier, inventory, purchasing, finance, dan pengguna lain dalam satu Tenant ID. Domain/subdomain hanya media akses dan tidak menjadi bukti authorization.

### External ecosystem boundary

Mencakup customer, supplier, payment provider, fiscal/legal authority, communication provider, DNS/CDN provider, API consumer, dan integration partner. Interaksi memerlukan kontrak, authentication, data minimization, failure handling, serta ownership yang jelas.

## Stakeholder map

| Stakeholder | Primary interest | Influence | Impact | Engagement hypothesis | Status |
|---|---|---|---|---|---|
| Product Owner | Product outcomes, scope, risk, investment, release | High | High | Decision workshops dan explicit GitHub approval | Approved sebagai peran; identitas manusia Under Review |
| Prospective tenant/business owner | Nilai bisnis, biaya, kontrol, pertumbuhan | High | High | Interview, workflow review, dan MVP validation | Proposed |
| Tenant management | Visibilitas operasional dan accountability | High | High | Journey mapping dan report-decision review | Proposed |
| Frontline operations | Kecepatan, kejelasan, dan recovery dari error | Medium | High | Contextual interview dan task observation | Proposed |
| Finance/accounting | Akurasi, reconciliation, audit, period control | High | High | Process walkthrough dan control review | Proposed |
| Inventory/purchasing | Stock integrity dan procurement control | High | High | Event storming dan exception analysis | Proposed |
| Platform operations/support | Reliability, observability, recovery, support safety | High | High | Operational workshop dan incident scenario | Proposed |
| Security/privacy/compliance reviewer | Isolation, lawful processing, abuse prevention | High | High | Threat/data-flow review dan qualified advice | Proposed |
| Customer/end buyer | Transaksi, receipt, refund, privacy | Low | Medium | Journey/research sesuai scope MVP | Proposed |
| Supplier/integration partner | Contract stability dan traceability | Medium | Medium | Interface and failure-scenario review | Proposed |
| Hosting/infrastructure provider | Runtime limits dan service reliability | Medium | Medium | Capability assessment dan responsibility matrix | Proposed |
| Legal, fiscal, or regulatory authority | Kepatuhan dan evidence | High | High | Qualified review setelah jurisdiction diketahui | Under Review |

Influence dan impact adalah hipotesis awal. Nilainya harus dikoreksi berdasarkan segmen, jurisdiction, delivery model, dan evidence stakeholder discovery.

## Human actor map

| Actor | Scope | Primary goal and activity | Decision participation | Critical data | Key risks | Status |
|---|---|---|---|---|---|---|
| Product Owner | Governance | Menetapkan visi, prioritas, scope, risk acceptance, dan release | Accountable sesuai decision-rights matrix | Decision record dan product evidence | Approval ambigu atau scope drift | Approved sebagai peran; identitas manusia Under Review |
| Platform Administrator | Platform | Mengelola tenant lifecycle dan konfigurasi platform terotorisasi | Consulted untuk operability; tidak menentukan kebijakan bisnis tenant | Tenant metadata, configuration, audit | Cross-tenant exposure dan excessive privilege | Proposed |
| Platform Support | Platform dengan akses terbatas | Mendiagnosis masalah dan membantu tenant | Consulted untuk supportability | Support case, diagnostic metadata | Unauthorized impersonation dan data overexposure | Proposed |
| Tenant Owner | Tenant | Mengelola bisnis, subscription, organization, dan delegation | Accountable untuk kebijakan tenant dalam batas platform | Company, subscription, user, outlet | Account takeover dan unsafe delegation | Proposed |
| Tenant Administrator | Tenant | Mengelola user, role assignment, configuration, dan outlet | Responsible berdasarkan delegasi Tenant Owner | Identity, permission, configuration | Privilege escalation dan lockout | Proposed |
| Outlet Manager | Tenant/outlet | Mengawasi shift, transaksi, stock, exception, dan performance | Approver kandidat untuk exception outlet | Sales, staff, stock, reconciliation | Conflict of interest dan unauthorized override | Proposed |
| Cashier | Tenant/outlet/register | Memproses sale, payment recording, receipt, dan controlled correction | Informed/Responsible pada transaksi; bukan policy approver | Cart, payment status, customer minimum data | Duplicate sale, fraud, dan privacy exposure | Proposed |
| Inventory Operator | Tenant/outlet/warehouse | Receiving, transfer, count, dan adjustment proposal | Responsible; approval terpisah untuk material adjustment | Item, quantity, location, movement | Stock manipulation dan untraceable adjustment | Proposed |
| Purchasing Officer | Tenant | Mengelola supplier, request, order, dan receiving coordination | Responsible; approval sesuai threshold | Supplier, price, order, receiving | Self-dealing dan duplicate purchase | Proposed |
| Finance/Accounting | Tenant | Reconciliation, receivable/payable, close, tax evidence, dan report | Reviewer/approver sesuai control policy | Money, ledger, payment, tax, refund | Misstatement dan incompatible duties | Proposed |
| Auditor/Compliance Reviewer | Tenant atau platform sesuai mandate | Memeriksa evidence tanpa mengubah transaksi | Independent reviewer | Audit trail dan approved evidence | Excessive access dan loss of independence | Proposed |
| Customer/End Buyer | External/tenant transaction | Membeli, membayar, menerima receipt, meminta refund atau data rights | Tidak memiliki internal approval authority | Contact minimum, transaction, consent | Privacy misuse dan dispute | Proposed |
| Supplier Representative | External | Memenuhi order, delivery, invoice, dan correction | Consulted pada procurement event | Supplier identity, order, invoice | Fraud dan master-data poisoning | Proposed |

Actor role tidak boleh langsung diterjemahkan menjadi technical permission. Permission membutuhkan use case, object boundary, tenant/outlet scope, separation of duties, audit, dan abuse test.

## System and external actors

| Actor | Interaction | Trust boundary | Required control | Status |
|---|---|---|---|---|
| Payment provider | Payment intent/status, callback, settlement evidence | External financial service | Signature verification, idempotency, amount/tenant binding, reconciliation | Proposed |
| Email/SMS/push provider | Transactional notification | External communication service | Consent/purpose, redaction, retry, delivery evidence, secret isolation | Proposed |
| Cloudflare/DNS provider | Tenant DNS, SSL, cache, zone validation | External infrastructure service | Least-privilege token, zone allowlist, audit, safe retry | Proposed |
| Hosting/runtime platform | Menjalankan aplikasi dan stateful dependencies | Infrastructure boundary | Secure configuration, backup, health, capacity, recovery | Approved sebagai deployment direction; provider Deferred |
| Public API client | Mengakses kontrak integrasi tenant | External consumer | Versioned auth, authorization, tenant binding, quota, audit | Deferred |
| Marketplace/plugin package | Memperluas capability | Untrusted supply chain/runtime | Signing, permission, compatibility, sandbox/kill switch | Deferred |
| AI service/provider | Retrieval atau assisted insight | External processing boundary | Data classification, redaction, consent, tenant authorization, evaluation | Deferred |
| Scheduler/worker | Menjalankan background task | Internal asynchronous boundary | Tenant context, idempotency, timeout, retry, dead-letter handling | Proposed |
| Fiscal/tax service | Pelaporan atau validation sesuai jurisdiction | Regulatory/external service | Qualified requirements, immutable evidence, privacy controls | Under Review |

System actor tidak boleh menerima human decision authority. Kegagalan integrasi harus menghasilkan state yang eksplisit dan dapat dipulihkan, bukan dianggap sukses secara diam-diam.

## Separation-of-duties hypotheses

| Risky flow | Initiator | Reviewer/approver | Required evidence | Small-tenant consideration |
|---|---|---|---|---|
| Privileged role assignment | Tenant Administrator | Tenant Owner atau authorized independent approver | Before/after permission dan audit record | Step-up authentication dan notification bila dua orang tidak tersedia |
| Void/refund material | Cashier atau Manager | Role berbeda sesuai threshold | Original sale, reason, approval, payment outcome | Threshold dan post-event review dapat menjadi compensating control |
| Inventory adjustment material | Inventory Operator | Outlet Manager atau Inventory Approver | Count evidence, reason, delta, approval | Mandatory audit dan anomaly review bila role dirangkap |
| Supplier creation/change | Purchasing Officer | Finance atau authorized approver | Identity, banking change evidence, approval | Out-of-band verification untuk perubahan sensitif |
| Purchase and payment | Purchasing Officer | Manager/Finance sesuai stage dan threshold | Request, order, receiving, invoice, payment match | Compensating review bila organisasi sangat kecil |
| Period close/reopen | Finance | Finance Approver atau Tenant Owner | Reconciliation evidence, reason, immutable audit | Step-up authentication dan exception report |
| Production release | Implementer/Release Manager | Named human Release Approver | Review, checks, artifact identity, rollback/recovery | Self-approval tetap dilarang untuk perubahan High/Critical |
| Support access/impersonation | Platform Support | Authorized support policy dan tenant consent bila diperlukan | Purpose, time window, actor, actions, termination | Break-glass hanya untuk incident dengan review pasca-kejadian |

Detail threshold, permission, dan compensating control belum disetujui. Keputusan final memerlukan threat model, user journey, dan policy/ADR terkait.

## Data-access principles by actor

- Setiap actor tenant beroperasi dalam Tenant ID yang tervalidasi dan scope outlet/organization yang eksplisit.
- Actor platform tidak memperoleh akses lintas tenant hanya karena memiliki akun internal.
- Data finansial, identity, permission, audit, support, dan export diperlakukan sebagai area sensitif.
- Customer dan supplier hanya melihat atau mengubah data yang menjadi haknya melalui use case terotorisasi.
- System actor memperoleh credential dan data minimum untuk satu tujuan yang terdefinisi.
- Support, export, impersonation, dan break-glass membutuhkan purpose, approval sesuai risiko, time bound, dan audit.
- Deny by default berlaku saat tenant, actor, scope, object ownership, atau policy tidak dapat ditentukan.

## Engagement and discovery plan

| Sequence | Activity | Participants | Evidence | Output |
|---:|---|---|---|---|
| 1 | Product Owner and decision-owner confirmation | Product Owner | GitHub decision record | Named ownership dan escalation path |
| 2 | Business-owner problem interview | Prospective Tenant Owner/Manager | Sanitized notes dan problem examples | Prioritized problems dan outcomes |
| 3 | Frontline task observation | Cashier/Outlet/Inventory roles | Synthetic scenario dan task evidence | Current journey, error, exception |
| 4 | Finance and control workshop | Finance/Purchasing/Inventory | Process/control artifacts tanpa sensitive data | Reconciliation dan separation-of-duties needs |
| 5 | Platform operations workshop | Support/Security/Operations | Incident and recovery scenarios | Operability, audit, support boundary |
| 6 | Stakeholder-map review | Product Owner dan domain reviewers | Reviewed head SHA | Corrected actor map dan open questions |

Research tidak boleh menyimpan credential, data produksi, data pribadi yang tidak diperlukan, atau informasi komersial sensitif pada issue publik. Evidence harus disanitasi dan mengikuti data-classification policy ketika tersedia.

## Assumptions and open questions

| ID | Assumption/open question | Owner | Validation method | Downstream impact |
|---|---|---|---|---|
| SA-001 | Identitas Product Owner dan delegate manusia belum tercatat eksplisit | Product Owner | GitHub approval/assignment | Seluruh decision authority |
| SA-002 | Segmen awal membutuhkan POS dan inventory terpadu | Product Owner | Problem interviews dan workflow evidence | MVP slicing dan bounded context |
| SA-003 | Tenant kecil mungkin merangkap beberapa actor role | Product/Security | Organization-size research dan abuse analysis | Permission dan compensating controls |
| SA-004 | Struktur organization/outlet/warehouse berbeda antar-segmen | Product/Domain | Event storming dan sample workflow | Data model dan tenant scope |
| SA-005 | Payment/fiscal requirements bergantung pada provider dan jurisdiction | Product Owner | Qualified legal/compliance review | API, data, security, release |
| SA-006 | Offline cashier flow mungkin dibutuhkan | Product/UX/Architecture | Connectivity and task research | POS semantics dan ADR |
| SA-007 | Customer identity tidak selalu dibutuhkan untuk setiap sale | Product/Privacy | Data-minimization and journey review | Customer domain dan privacy |
| SA-008 | Platform support memerlukan controlled diagnostic access | Operations/Security | Support scenarios dan threat model | Audit, impersonation, data access |

Open question tidak memiliki deadline sampai Product Owner menyetujui owner, capacity, dan dependency. Ketiadaan deadline tidak mengubahnya menjadi keputusan.

## Downstream handoff

Setelah direview, peta ini menjadi input untuk:

1. current process and critical user journeys;
2. domain event storming dan ubiquitous language;
3. MVP scope/non-scope serta product outcomes;
4. data inventory, classification, retention, dan privacy;
5. threat model, authorization, dan separation-of-duties matrix;
6. context map, API actors, integration boundaries, dan ADR;
7. testing strategy untuk actor, tenant, object, dan function authorization.

Dokumen downstream wajib menautkan evidence dan tidak boleh menganggap seluruh hipotesis di sini telah tervalidasi.

## Acceptance gate

- [ ] Product Owner atau delegate manusia tercatat di GitHub.
- [ ] Stakeholder influence/impact dan engagement need direview.
- [ ] Human, system, external, platform-scoped, dan tenant-scoped actor dibedakan.
- [ ] Tujuan, data, decision participation, serta risiko actor dikoreksi berdasarkan evidence.
- [ ] Separation-of-duties hypotheses direview tanpa menetapkan permission final.
- [ ] Asumsi dan open questions memiliki owner serta validation method.
- [ ] Tidak ada persona, MVP, technology, atau compliance choice yang disetujui tersirat.
- [ ] Application source code tetap Blocked.

## ChatGPT — Lanjutan

Gunakan repository `labzefry/oneQay` sebagai SSOT. Review Issue #4 dan draft PR Stakeholder and Actor Map pada head terbaru. Terapkan hanya koreksi yang disetujui Product Owner. Jangan mempromosikan status Proposed, menetapkan persona final, menentukan permission implementatif, atau membuat source code tanpa evidence dan approval eksplisit. Setelah dokumen disetujui, buat issue dan draft PR terpisah untuk Current Process and User Journeys.

## ChatGPT — Review Independen

Audit `STAKEHOLDER_AND_ACTOR_MAP.md` terhadap `PRODUCT_VISION_AND_DECISION_RIGHTS.md`, `PROJECT_MANIFEST.md`, `AI_CONSTITUTION.md`, `ARCHITECTURE.md`, `SECURITY.md`, `DATABASE.md`, `ROADMAP.md`, `TASKS.md`, dan `PHASE_0_KICKOFF.md`. Cari actor yang hilang, scope lintas tenant, konflik decision rights, system actor yang diberi otoritas manusia, separation of duties yang lemah, persona atau kebutuhan palsu yang diklaim tervalidasi, data sensitif yang tidak perlu, serta keputusan MVP/teknologi tersirat. Klasifikasikan temuan Critical, High, Medium, atau Low dan berikan perbaikan minimal.
