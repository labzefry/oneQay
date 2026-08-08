# oneQay Current Process and User Journeys

> **Status:** Proposed — current-state hypotheses menunggu discovery dan persetujuan Product Owner  
> **Phase:** 0 — Governance & Discovery  
> **Owner:** Product Owner oneQay  
> **Tracking:** GitHub Issue #6  
> **Dependencies:** Product Vision and Decision Rights serta Stakeholder and Actor Map berstatus Proposed

## Purpose

Dokumen ini membentuk baseline untuk memahami bagaimana target actor kemungkinan menjalankan pekerjaan saat ini, masalah yang mereka alami, handoff antarperan, exception, control, serta outcome yang dibutuhkan. Baseline ini digunakan untuk merencanakan discovery, bukan untuk mengklaim bahwa proses bisnis telah tervalidasi.

Hasilnya menjadi input bagi domain event storming, MVP slicing, data classification, threat modeling, architecture options, permission design, API contracts, dan testing strategy. Dokumen ini tidak memberi izin membuat source code baru.

## Evidence semantics

| Label | Arti | Penggunaan |
|---|---|---|
| Approved constraint | Aturan yang telah disetujui pada dokumen kanonis | Mengikat seluruh discovery dan keputusan berikutnya |
| Observed fact | Bukti aktual dari interview, observation, artifact, atau data yang sah | Harus memiliki sumber, tanggal, scope, dan sanitization |
| Assumption | Pernyataan yang belum dibuktikan | Memerlukan owner dan validation method |
| Journey hypothesis | Dugaan urutan pekerjaan untuk memandu discovery | Tidak boleh dianggap requirement final |
| Target-state direction | Outcome yang diinginkan tanpa menetapkan solusi | Memerlukan prioritas dan success metric |
| Open question | Ketidakpastian yang dapat mengubah scope atau control | Harus ditelusuri sampai diputuskan, ditunda, atau ditolak |

Saat dokumen ini pertama kali dibuat, seluruh current process dan journey di bawah adalah **Journey hypothesis**, kecuali approved constraints yang disebut secara eksplisit.

## Approved constraints

- GitHub adalah Single Source of Truth untuk pengembangan oneQay.
- Engineering collaboration menggunakan ChatGPT + GitHub saja.
- oneQay adalah platform multi-tenant; Tenant ID merupakan batas isolasi utama.
- Domain/subdomain hanya media akses dan bukan bukti authorization.
- Business logic tidak bergantung pada framework, database, UI, atau provider infrastruktur.
- Tindakan finansial, inventory, privileged access, support, release, dan cross-tenant operation harus dapat diaudit.
- Keputusan framework, database, payment, offline semantics, provider, dan deployment detail membutuhkan evidence serta ADR/policy terkait.
- Final/business/production application implementation baru tetap Blocked sampai authority dan gate yang berlaku dipenuhi; bounded Platform Foundation yang telah dipublikasikan melalui Sprint 13 tetap merupakan fakta repository.

## Discovery objectives

Discovery harus menjawab:

1. pekerjaan apa yang dilakukan setiap actor dan outcome apa yang mereka anggap berhasil;
2. trigger, precondition, handoff, data, decision, exception, dan recovery pada setiap proses;
3. bagian mana yang manual, terfragmentasi, ambigu, lambat, rawan error, atau sulit diaudit;
4. kapan tenant, organization, outlet, warehouse, device, register, shift, user, customer, atau supplier context diperlukan;
5. bagaimana uang, stock, payment status, refund, dan reconciliation dijaga konsisten;
6. connectivity, device, locale, timezone, currency, receipt, fiscal, privacy, dan accessibility constraints;
7. control mana yang wajib dipisahkan dan compensating control apa yang mungkin diperlukan tenant kecil;
8. journey mana yang paling penting bagi MVP berdasarkan evidence, bukan keluasan fitur.

## Research and observation protocol

| Method | Best use | Minimum evidence | Prohibited shortcut |
|---|---|---|---|
| Contextual interview | Tujuan, terminology, exception, decision | Sanitized notes, actor role, context, confidence | Menganggap opini tunggal mewakili seluruh segmen |
| Task observation | Urutan kerja, handoff, workaround, latency | Timestamped steps dan artifact references | Merekam personal/financial data yang tidak diperlukan |
| Artifact review | Form, receipt, spreadsheet, report, policy | Redacted sample dan provenance | Mengunggah credential atau production data ke GitHub |
| Process workshop | Handoff, policy, conflict, ownership | Reviewed process map dan unresolved dissent | Memaksa konsensus palsu |
| Synthetic scenario | Error, fraud, offline, recovery, edge case | Scenario, expected outcome, open decisions | Mengklaim hasil synthetic sebagai user evidence |
| Quantitative baseline | Frequency, duration, error, volume | Metric definition, source, period, bias | Menetapkan target tanpa baseline dan anti-gaming guardrail |

Evidence pada GitHub harus disanitasi. Data pribadi, credential, data produksi, informasi pembayaran, dan informasi komersial sensitif tidak boleh disalin tanpa klasifikasi, necessity, authorization, serta protection yang disetujui.

## Journey inventory

| ID | Journey hypothesis | Primary actors | Business outcome | Risk class | Discovery priority |
|---|---|---|---|---|---|
| JRN-001 | Tenant onboarding and first administration | Tenant Owner, Platform Administrator | Tenant dapat memulai secara aman dengan ownership jelas | High | P0 |
| JRN-002 | Organization, outlet, device, and register setup | Tenant Administrator, Outlet Manager | Context operasional terbentuk tanpa privilege berlebih | High | P0 |
| JRN-003 | User invitation, role delegation, and access recovery | Tenant Owner, Tenant Administrator, User | User memperoleh akses minimum dan dapat memulihkan akun | Critical | P0 |
| JRN-004 | Catalog, price, tax, and availability preparation | Manager, Catalog/Inventory role | Item siap dijual dengan harga dan aturan yang benar | High | P0 |
| JRN-005 | Shift/register opening | Cashier, Outlet Manager | Register siap bertransaksi dengan accountability | High | P0 |
| JRN-006 | Sale, payment recording, and receipt | Cashier, Customer, Payment provider | Sale diselesaikan sekali, akurat, dan dapat dibuktikan | Critical | P0 |
| JRN-007 | Void, cancellation, return, and refund | Cashier/Manager, Customer, Finance, Payment provider | Koreksi terotorisasi tanpa kehilangan audit atau uang | Critical | P0 |
| JRN-008 | Receiving, stock movement, count, and adjustment | Inventory, Purchasing, Manager | Stock berubah berdasarkan event yang sah dan terlacak | Critical | P0 |
| JRN-009 | Purchase request to supplier settlement | Purchasing, Supplier, Inventory, Finance | Procurement dapat dicocokkan dari kebutuhan sampai pembayaran | High | P1 |
| JRN-010 | Shift close and payment reconciliation | Cashier, Manager, Finance | Expected dan actual amount dapat direkonsiliasi | Critical | P0 |
| JRN-011 | Operational and management reporting | Manager, Tenant Owner, Finance | Keputusan memakai data yang konsisten dan dapat ditelusuri | High | P1 |
| JRN-012 | Support diagnosis and controlled assistance | User, Platform Support, Security/Operations | Masalah dipulihkan tanpa akses data yang tidak sah | Critical | P0 |
| JRN-013 | Tenant suspension, export, restore, and termination | Tenant Owner, Platform Operations, Finance | Lifecycle tenant terkontrol tanpa kehilangan atau kebocoran data | Critical | P1 |

Prioritas adalah hipotesis berbasis risiko. Urutan MVP hanya dapat ditetapkan setelah value, frequency, dependency, capacity, dan evidence tersedia.

## Journey detail template

Setiap journey hasil discovery wajib mencatat:

| Field | Required content |
|---|---|
| Actor and scope | Human/system actor, Tenant ID, organization/outlet/device/register scope |
| Trigger | Event yang memulai journey |
| Preconditions | Identity, authorization, configuration, state, connectivity, dan dependency |
| Current steps | Urutan aktual, handoff, tool, wait, re-entry, dan workaround |
| Decisions | Policy, threshold, approval, dan ambiguity point |
| Data | Input/output, classification, owner, minimum necessity, retention question |
| Controls | Authorization, separation of duties, validation, idempotency, audit, notification |
| Exceptions | Failure, timeout, duplicate, conflict, partial success, offline, cancellation |
| Recovery | Resume, compensate, retry, reconcile, rollback, escalation |
| Outcome | User/business outcome dan observable final state |
| Evidence | Source, date, confidence, disagreement, dan validation backlog |

## Critical journey hypotheses

### JRN-001 — Tenant onboarding and first administration

| Aspect | Hypothesis to validate |
|---|---|
| Actors | Tenant Owner, Platform Administrator, subscription/payment service bila relevan |
| Trigger | Prospective customer memulai trial, purchase, atau administrator membuat tenant terotorisasi |
| Preconditions | Ownership evidence, plan/entitlement rule, domain decision, locale/currency/timezone requirement |
| Current-state hypothesis | Informasi bisnis dikumpulkan, tenant dibuat, owner diundang, administrator melakukan setup awal, dan readiness diperiksa |
| Critical data | Tenant ID, company, owner identity, subscription, timezone, currency, locale, domain |
| Controls | Immutable tenant identity, verified owner, least privilege, secret-free invitation, audit, idempotent tenant creation |
| Exceptions | Duplicate company/domain, invitation expired, payment ambiguous, partial provisioning, owner inaccessible |
| Outcome | Satu tenant aktif dengan owner yang dapat diverifikasi dan setup state yang eksplisit |
| Evidence needed | Acquisition/onboarding workflow, required business fields, trial policy, support burden, legal and billing boundary |

Open questions: siapa yang boleh membuat tenant, kapan subscription aktif, apakah custom domain merupakan MVP, bagaimana incomplete onboarding dibersihkan, dan bagaimana tenant ownership dipindahkan.

### JRN-002/003 — Operational setup and access delegation

| Aspect | Hypothesis to validate |
|---|---|
| Actors | Tenant Owner, Tenant Administrator, Outlet Manager, invited user |
| Trigger | Tenant membutuhkan organization, outlet, device/register, serta operator |
| Preconditions | Tenant aktif, owner authenticated, organization policy tersedia |
| Current-state hypothesis | Struktur bisnis dibuat, outlet dikonfigurasi, device/register diregistrasi, user diundang, dan role/scope didelegasikan |
| Critical data | Organization/outlet, user identity, role, permission scope, device/register identity |
| Controls | Tenant binding, step-up untuk privilege, independent approval/notification bila relevan, audit before/after |
| Exceptions | Duplicate identity, wrong tenant/outlet, orphan owner, lost device, revoked user, invitation replay |
| Outcome | User dapat melakukan tugas yang diizinkan hanya pada scope yang benar |
| Evidence needed | Organization variants, staffing model, shared-device practice, role overlap, recovery process |

No role di dokumen stakeholder boleh diterjemahkan langsung menjadi technical permission sebelum use case dan abuse case tervalidasi.

### JRN-004/005 — Prepare sales operation and open shift

| Aspect | Hypothesis to validate |
|---|---|
| Actors | Manager, Catalog/Inventory role, Cashier |
| Trigger | Item perlu dijual atau register memulai periode operasi |
| Preconditions | Outlet, item, price, tax rule, availability, register, cashier access, opening-balance policy |
| Current-state hypothesis | Catalog/price disiapkan, availability diperiksa, cashier masuk pada register yang benar, lalu shift dibuka |
| Critical data | Item, price, tax, stock availability, outlet, register, shift, opening amount |
| Controls | Effective-date pricing, monetary precision, scope validation, dual control untuk change berisiko, audit |
| Exceptions | Missing price, overlapping promotion, stale stock, wrong timezone, register already open, shared cashier identity |
| Outcome | Shift aktif pada outlet/register yang benar dengan catalog yang dapat digunakan |
| Evidence needed | Pricing practice, tax/fiscal rule, cash float, device sharing, operating hours, offline need |

### JRN-006 — Sale, payment recording, and receipt

| Aspect | Hypothesis to validate |
|---|---|
| Actors | Cashier, Customer, Payment provider, Inventory system actor |
| Trigger | Customer siap membeli item/service |
| Preconditions | Active shift, authorized cashier, sellable item/price, tenant/outlet/register context |
| Current-state hypothesis | Item dipilih, quantity/price dihitung, adjustment terotorisasi, payment dimulai/dicatat, sale difinalkan, stock/event diperbarui, receipt diberikan |
| Critical data | Line item, quantity, price, tax, discount, total, payment state, customer-minimum data, receipt |
| Controls | Server-authoritative calculation, monetary precision, idempotency, tenant binding, payment verification, immutable completion evidence |
| Exceptions | Timeout, duplicate submit/callback, partial payment, payment success tetapi response gagal, price change, insufficient stock, printer failure |
| Outcome | Satu sale memiliki satu final state yang dapat direkonsiliasi; payment dan stock effect tidak ambigu |
| Evidence needed | Payment methods, peak latency, receipt rules, split payment, discount authority, stock timing, offline constraints |

Payment timeout atau connectivity loss tidak boleh dipetakan otomatis sebagai gagal maupun sukses. Status harus diverifikasi dan direkonsiliasi berdasarkan provider boundary yang belum diputuskan.

### JRN-007 — Void, cancellation, return, and refund

| Aspect | Hypothesis to validate |
|---|---|
| Actors | Cashier/Manager, Customer, Finance, Payment provider, Inventory role |
| Trigger | Sale perlu dikoreksi sebelum atau setelah completion |
| Preconditions | Original sale ditemukan dalam tenant/outlet scope; policy dan approval threshold tersedia |
| Current-state hypothesis | Jenis koreksi dipilih, reason/evidence dicatat, approval diperoleh, financial/stock effect dijalankan, dan hasil diberitahukan |
| Critical data | Original sale, actor, reason, amount, returned item condition, provider result, approval |
| Controls | Separation of duties, step-up, amount/tenant binding, idempotency, immutable audit, reconciliation |
| Exceptions | Original payment unavailable, partial return, provider rejects, stock cannot return, approval timeout, repeated refund |
| Outcome | Koreksi memiliki final state yang sah tanpa menghapus transaksi asal |
| Evidence needed | Return policy, refund timing, thresholds, cash vs electronic behavior, tax/fiscal requirements |

Void, cancellation, return, dan refund tidak boleh digabung secara semantik sebelum perbedaan event, timing, money, stock, dan compliance tervalidasi.

### JRN-008/009 — Inventory and purchasing lifecycle

| Aspect | Hypothesis to validate |
|---|---|
| Actors | Inventory Operator, Purchasing Officer, Manager, Supplier, Finance |
| Trigger | Stock dibutuhkan, diterima, dipindah, dihitung, disesuaikan, atau dibayar |
| Preconditions | Item/location/supplier teridentifikasi; actor scope dan approval policy tersedia |
| Current-state hypothesis | Kebutuhan dibuat, order disetujui, supplier memenuhi, receiving dicatat, discrepancy diselesaikan, stock bergerak, invoice dicocokkan, payment disetujui |
| Critical data | Item, supplier, location, quantity, unit/cost, order, receiving, invoice, adjustment, payment |
| Controls | Three-way match hypothesis, independent approval, movement ledger, reason/evidence, threshold, audit |
| Exceptions | Partial/over delivery, damaged item, wrong unit, duplicate invoice, stock negative, transfer in transit, count variance |
| Outcome | Setiap perubahan stock dan liability memiliki sumber, actor, waktu, scope, dan reconciliation path |
| Evidence needed | Procurement maturity, warehouse/outlet topology, valuation need, unit conversion, count practice, supplier payment flow |

Costing dan valuation method belum dipilih. Journey discovery hanya mengumpulkan kebutuhan dan invariants yang dapat memengaruhi ADR/data design.

### JRN-010 — Shift close and payment reconciliation

| Aspect | Hypothesis to validate |
|---|---|
| Actors | Cashier, Outlet Manager, Finance, Payment provider |
| Trigger | Shift atau business day selesai |
| Preconditions | Active shift dan daftar transaksi/payment events tersedia |
| Current-state hypothesis | Transaksi dihitung, payment dikelompokkan, actual cash/provider evidence dibandingkan, variance dijelaskan, approval dilakukan, lalu shift ditutup |
| Critical data | Expected/actual amount, payment method, sale/refund, cash movement, variance, settlement evidence |
| Controls | Independent review sesuai threshold, immutable close, controlled reopen, timezone, reconciliation audit |
| Exceptions | Late callback, missing settlement, cash variance, offline transaction, refund after close, cross-day timezone |
| Outcome | Shift memiliki expected, actual, variance, explanation, reviewer, dan final state yang jelas |
| Evidence needed | Close frequency, cash handling, settlement delay, responsibility, tolerance, reopen policy |

### JRN-012 — Support diagnosis and controlled assistance

| Aspect | Hypothesis to validate |
|---|---|
| Actors | Tenant user, Platform Support, Operations/Security, system providers |
| Trigger | User melaporkan error atau monitoring mendeteksi incident |
| Preconditions | Case/correlation reference, requester verification, severity, tenant scope, support policy |
| Current-state hypothesis | Case dicatat, identity/scope diverifikasi, diagnostic evidence dikumpulkan, access dielevasi hanya bila perlu, recovery dijalankan, dan case ditutup dengan audit |
| Critical data | Case, correlation ID, tenant/outlet context, redacted logs, access actions, resolution |
| Controls | Purpose limitation, least privilege, time-bound access, tenant consent bila diperlukan, redaction, break-glass review |
| Exceptions | Cross-tenant symptom, credential exposure, data loss, provider outage, unavailable owner, unresolved incident |
| Outcome | Service pulih atau escalation aktif tanpa akses data yang tidak sah |
| Evidence needed | Support channel, severity model, response expectation, diagnostic needs, consent, incident ownership |

## Actor and system handoffs

| Handoff | Required context | Failure to explore | Validation focus |
|---|---|---|---|
| Tenant Owner → Tenant Administrator | Tenant, delegated authority, expiry/scope | Excessive privilege atau orphan ownership | Approval, notification, recovery |
| Manager → Cashier | Outlet, register, shift, policy | Wrong outlet/register dan shared identity | Context visibility dan session/device practice |
| Cashier → Payment provider | Tenant, sale, amount, idempotency reference | Duplicate/ambiguous payment | Callback, retry, timeout, reconciliation |
| Sale → Inventory | Tenant, outlet/location, item, quantity, event state | Lost/double stock movement | Transaction boundary dan compensation |
| Purchasing → Supplier → Receiving | Order, item/unit, quantity, location | Wrong/partial/duplicate delivery | Matching dan discrepancy handling |
| Receiving → Finance | Order, receipt, invoice, variance | Payment tanpa evidence | Separation of duties dan match policy |
| Cashier/Manager → Finance | Sale/refund/shift evidence | Unreconciled money | Close, settlement, late event |
| Tenant User → Platform Support | Verified requester, tenant, case purpose | Unauthorized data access | Diagnostic minimization dan time-bound access |
| Platform → External provider | Contract version, correlation, tenant-safe reference | Silent partial failure | Timeout, retry, idempotency, audit |

## Pain-point hypothesis register

| ID | Hypothesis | Affected actors | Potential severity | Evidence status | Validation method |
|---|---|---|---|---|---|
| PP-001 | Data operasional tersebar dan direkonsiliasi manual | Owner, Manager, Finance | High | Assumption | Interview, artifact review, duration/error baseline |
| PP-002 | User tidak selalu mengetahui tenant/outlet/register context aktif | Admin, Manager, Cashier | Critical | Assumption | Task observation dan wrong-context scenario |
| PP-003 | Payment timeout/duplicate menghasilkan status ambigu | Cashier, Customer, Finance | Critical | Assumption | Provider/process review dan synthetic failure scenario |
| PP-004 | Refund dan void tidak memiliki approval/audit konsisten | Cashier, Manager, Finance | Critical | Assumption | Policy and transaction-sample review yang disanitasi |
| PP-005 | Stock berbeda antara catatan dan kondisi fisik | Inventory, Manager, Finance | High | Assumption | Count workflow dan variance baseline |
| PP-006 | Handoff purchasing, receiving, invoice, dan payment tidak mudah dicocokkan | Purchasing, Inventory, Finance | High | Assumption | End-to-end process walkthrough |
| PP-007 | Role dirangkap tanpa compensating control pada tenant kecil | Tenant Owner, Admin, Finance | High | Assumption | Organization-size interview dan abuse analysis |
| PP-008 | Support memerlukan akses luas karena diagnostic evidence tidak memadai | User, Support, Security | Critical | Assumption | Support-case review dan data-flow mapping |
| PP-009 | Connectivity/perangkat menghambat alur kasir | Cashier, Manager, Customer | High | Open question | Connectivity and device observation |
| PP-010 | Laporan terlambat atau tidak dapat ditelusuri ke transaksi sumber | Owner, Manager, Finance | High | Assumption | Report decision walkthrough dan lineage review |

Severity di atas menunjukkan dampak potensial bila hipotesis benar, bukan bukti bahwa masalah terjadi. Temuan yang tidak tervalidasi tidak boleh dipakai untuk mengklaim manfaat produk.

## Cross-journey invariants to validate

- Semua data tenant mempertahankan Tenant ID dan object scope yang benar.
- User tidak dapat bertindak hanya berdasarkan hostname, client-supplied tenant, atau hidden UI control.
- Nilai uang memakai definisi precision, rounding, currency, dan final state yang konsisten.
- Sale, payment, refund, stock movement, dan external callback tahan duplicate/retry sesuai semantics yang disetujui.
- Transaksi sumber tidak dihapus untuk menyembunyikan correction; gunakan event/status/audit yang sesuai.
- Stock movement memiliki source, quantity/unit, location, actor/system, time, dan reason.
- Privileged action, support access, close/reopen, void/refund, dan material adjustment dapat diaudit.
- Partial failure menghasilkan state eksplisit, recovery path, dan operator visibility.
- Timezone, locale, currency, tax/fiscal, receipt, privacy, dan retention requirement tidak diasumsikan universal.
- Offline behavior tidak ditentukan sebelum consistency, conflict, payment, security, dan recovery semantics disetujui melalui ADR.

## Open decision boundaries

Dokumen ini sengaja tidak memutuskan:

- target customer segment dan industry priority;
- MVP journey slice;
- organization/outlet/warehouse topology final;
- role/permission matrix dan approval threshold;
- payment provider, payment state model, dan compliance scope;
- offline support, conflict resolution, dan local data boundary;
- inventory costing/valuation;
- tax/fiscal/receipt rule per jurisdiction;
- customer identity requirement;
- retention, export, deletion, support impersonation, dan break-glass policy;
- framework, database, queue, cache, infrastructure provider, dan deployment detail.

Keputusan tersebut harus memiliki owner manusia, evidence, option analysis, serta ADR/policy bila diwajibkan handbook.

## Validation backlog

| ID | Validation activity | Target participants | Output | Dependency |
|---|---|---|---|---|
| VAL-001 | Confirm Product Owner and decision delegates | Product Owner | Named GitHub decision record | PV-001/SA-001 |
| VAL-002 | Business-owner and manager workflow interviews | Prospective Owner/Manager | Prioritized problems, decisions, outcomes | Stakeholder access |
| VAL-003 | Cashier/register contextual observation | Cashier/Manager | Sale, exception, shift, device/connectivity evidence | Synthetic-data protocol |
| VAL-004 | Inventory and purchasing workshop | Inventory/Purchasing/Supplier role | Movement, receiving, discrepancy, handoff evidence | Actor confirmation |
| VAL-005 | Finance and reconciliation workshop | Finance/Manager | Money states, settlement, close, refund controls | Payment boundary questions |
| VAL-006 | Support and incident scenario review | Support/Security/Operations | Diagnostic, access, escalation, recovery requirements | Operational participants |
| VAL-007 | Journey evidence synthesis | Product/Domain/UX reviewers | Corrected journey map and disputed points | VAL-002–006 |

## Acceptance gate

- [ ] Product Owner atau delegate manusia tercatat di GitHub.
- [ ] Journey priority dikoreksi menggunakan business value, frequency, dependency, dan risk evidence.
- [ ] Current-state fact, assumption, target direction, dan open question dibedakan.
- [ ] Critical journey memiliki actor, trigger, precondition, steps, data, controls, exception, recovery, outcome, dan evidence.
- [ ] Handoff, tenant/outlet/register context, money, stock, payment, audit, dan support boundary direview.
- [ ] Pain point divalidasi, ditolak, atau tetap ditandai assumption.
- [ ] Offline, payment, fiscal, privacy, retention, permission, dan technology decision tidak disetujui tersirat.
- [ ] Tidak ada data sensitif atau production data pada evidence repository.
- [ ] Final/business/production application implementation baru tetap Blocked tanpa authority dan gate yang berlaku.

## ChatGPT — Lanjutan

Gunakan repository `labzefry/oneQay` sebagai SSOT. Review Issue #6 dan draft PR Current Process and User Journeys pada head terbaru. Terapkan hanya koreksi yang disetujui Product Owner. Jangan mengubah journey hypothesis menjadi observed fact, menetapkan MVP/permission/technology, mempromosikan status Proposed, atau membuat source code baru tanpa evidence dan approval eksplisit. Setelah dokumen direview, siapkan Domain Event Storming sebagai issue dan draft PR terpisah.

## ChatGPT — Review Independen

Audit `CURRENT_PROCESS_AND_USER_JOURNEYS.md` terhadap `PRODUCT_VISION_AND_DECISION_RIGHTS.md`, `STAKEHOLDER_AND_ACTOR_MAP.md`, `PROJECT_MANIFEST.md`, `AI_CONSTITUTION.md`, `ARCHITECTURE.md`, `DATABASE.md`, `API_SPEC.md`, `SECURITY.md`, `ROADMAP.md`, `TASKS.md`, dan `PHASE_0_KICKOFF.md`. Cari current-state palsu, journey/actor yang hilang, approval tersirat, cross-tenant leakage, money/stock inconsistency, ambiguous payment/offline state, weak separation of duties, missing recovery, data berlebih, atau keputusan MVP/teknologi prematur. Klasifikasikan temuan Critical, High, Medium, atau Low dan berikan perbaikan minimal.
