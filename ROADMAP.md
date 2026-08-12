# oneQay Roadmap

## Roadmap principles

- Outcome lebih penting daripada jumlah fitur.
- Security, tenant isolation, migration, observability, backup, dan rollback adalah bagian fitur.
- Setiap fase memiliki entry/exit criteria dan tidak otomatis terikat tanggal sebelum kapasitas disetujui.
- Marketplace, plugin, AI automation, cloud, dan Kubernetes tidak dipercepat sebelum fondasi siap.
- Product Vision, Capability Map, Architecture Direction, Delivery Roadmap, dan Implementation Authority harus tetap dibedakan.
- Keberadaan capability pada roadmap atau Enterprise Capability Map tidak memberikan implementation authority.

## Canonical product identity

Nama produk canonical adalah **oneQay**.

Dokumen current-state dan future-facing harus menggunakan `oneQay`. Immutable GitHub identifiers, repository path `labzefry/oneQay`, SHAs, historical commit messages, branch names, dan quoted historical evidence tidak ditulis ulang hanya untuk normalisasi branding.

## Phase 0 — Governance and discovery

**Outcome:** konstitusi engineering, scope, domain, risiko, dan keputusan teknologi siap untuk implementasi.

Deliverables:

- 18 dokumen AI Engineering Handbook;
- project manifest dan decision register;
- stakeholder, actor, journey, dan domain discovery;
- MVP scope/non-scope;
- data classification dan threat model awal;
- ADR technology stack, database, tenancy, auth, dan deployment stage 1;
- backlog prioritas serta acceptance criteria.

Exit criteria:

- handbook direview dan disetujui;
- tidak ada Critical open decision untuk memulai skeleton;
- MVP dan success metrics disetujui;
- risiko Critical memiliki owner dan mitigation.

### Canonical Phase 0 semantics

Phase 0 tetap **In Progress** sampai exit criteria-nya disetujui secara eksplisit. Status ini adalah status program governance/discovery dan tidak boleh dibaca sebagai pernyataan bahwa repository tidak memiliki source code teknis.

Repository telah mempublikasikan bounded Platform Foundation melalui Sprint 12 dan Sprint 13 melalui lifecycle authority yang terpisah. Fakta publikasi tersebut tidak berarti Phase 0 telah selesai, tidak memulai Phase 1 secara otomatis, tidak menyetujui final business application, dan tidak memberi authority untuk Sprint 14.

Mulai M5.3, istilah **application implementation Blocked** berarti final/business/production application implementation yang belum diotorisasi. Istilah tersebut tidak membatalkan atau menghapus bounded Platform Foundation source yang telah dipublikasikan melalui Sprint 13.

Phase 0 Controlled Implementation Bridge menegaskan bahwa final Phase 0 Exit bukan prerequisite untuk setiap bounded Local/Test/CI source file. Bounded Technical Preview source preparation dapat berlangsung sebelum final Phase 0 Exit hanya setelah bridge dipublikasikan dan Product Owner memberikan source authority yang terpisah. Preview runtime/deployment tetap memerlukan actual target qualification sesuai DEC-009 dan authority deployment terpisah.

Sprint 14 tetap **Not Authorized** dan production readiness tetap **NO-GO**.

## Phase 1 — Platform foundation

**Outcome:** fondasi multi-tenant yang aman, dapat diuji, diinstal, dan dioperasikan.

Scope candidate:

- tenant lifecycle dan configuration;
- identity, MFA, role/permission, session;
- organization, outlet, device;
- audit log dan error correlation;
- migration/seeder framework;
- configuration/secret handling;
- installer baseline;
- CI quality gate dan deployment stage 1;
- backup/restore rehearsal.

Exit criteria:

- tenant-isolation test lulus;
- privileged access dan audit tervalidasi;
- installation clean-room lulus;
- backup restore lulus;
- Critical/High security issue nol.

Published bounded Platform Foundation work through Sprint 13 is preserved as repository fact and must not be reclassified retroactively as evidence that all Phase 1 entry or exit criteria were satisfied.

## Phase 2 — POS minimum viable product

**Outcome:** transaksi penjualan inti dapat dilakukan aman dan konsisten.

Scope candidate:

- catalog, price, tax configuration;
- outlet inventory baseline;
- cart, sale, payment recording, receipt;
- shift/register lifecycle;
- void/refund dengan authorization;
- basic customer;
- daily reconciliation dan operational report;
- PWA experience untuk flow utama.

Gate khusus:

- idempotency transaksi;
- monetary precision dan rounding tests;
- offline requirement diputuskan melalui ADR;
- audit untuk void/refund;
- performance budget kasir.

## Phase 3 — ERP operations

**Outcome:** operasi persediaan dan procurement terintegrasi.

Scope candidate:

- purchasing dan supplier;
- receiving, transfer, adjustment, stock count;
- warehouse/outlet policy;
- cost and valuation strategy;
- accounts receivable/payable foundation;
- management reporting.

Exit criteria meliputi reconciliation, migration safety, permission separation, dan period-close controls.

## Phase 4 — SaaS commercial platform

**Outcome:** oneQay dapat mengelola subscription dan tenant lifecycle secara komersial.

Scope candidate:

- plan, entitlement, quota;
- subscription/billing integration;
- trial, upgrade, downgrade, suspension;
- tenant domain dan Cloudflare automation;
- customer portal;
- support and operational controls.

## Phase 5 — Public ecosystem

**Outcome:** integrasi eksternal aman dan terkendali.

Scope candidate:

- public API and developer portal;
- API keys/OAuth decision;
- webhook management;
- integration catalog;
- marketplace governance;
- plugin signing, permission, compatibility, sandbox, kill switch.

## Phase 6 — Intelligent operations

**Outcome:** AI Assistant memberi insight dan bantuan tanpa mengorbankan data boundary.

Scope candidate:

- AI Gateway dan provider abstraction;
- tenant-authorized retrieval;
- explainable analytics;
- assisted workflow dengan human confirmation;
- evaluation suite, red-team, cost and latency budget;
- prompt/model versioning dan safe fallback.

## Enterprise product evolution

M6 menambahkan peta evolusi konseptual yang melengkapi phase delivery di atas. Ini bukan release commitment dan tidak mengubah authority phase/sprint.

| Evolution stage | Directional purpose | Authority implication |
| --- | --- | --- |
| E0 — Foundation | Governance, tenancy, identity, configuration, audit, data safety, quality, recovery | Existing Sprint 12/13 publication preserved; no new source authority |
| E1 — Core Transaction Platform | Controlled business transactions and approved POS/commerce slice | Requires separate Product Owner implementation authority |
| E2 — Business Management | Inventory, procurement, CRM, finance/accounting foundation, reporting, workflow | Directional only until separately authorized |
| E3 — Enterprise Management | Multi-unit governance, advanced administration, configurable process and control | Directional only until separately authorized |
| E4 — Intelligence | Business Intelligence, AI insight, recommendation, bounded automation | Directional only; AI/data/security gates remain mandatory |
| E5 — Ecosystem | Public API, partner integration, marketplace, plugin/extension ecosystem | Directional only; trust/compatibility/security gates remain mandatory |

Canonical detail resides in `docs/handbook/ENTERPRISE_VISION.md`. M6 representation/publication completed through PR #69, M6 closure completed through PR #71, and the substantive Enterprise Vision was separately Approved through GOV-051.

## Infrastructure evolution track

| Stage | Trigger | Required readiness |
| --- | --- | --- |
| Shared Hosting | Initial controlled launch | scheduler, backup, secure config, monitoring baseline |
| VPS | Resource/control limit reached | automated deploy, externalized state, hardening |
| Dedicated | Sustained workload/isolation need | capacity model, HA/DR decision |
| Docker | Reproducibility and portability need | container-safe state and jobs |
| Cloud | Managed service/autoscaling value proven | cost, IAM, network, DR governance |
| Kubernetes | Operational complexity justified | SRE ownership, observability, platform maturity |

## Cross-cutting workstreams

Security, performance, accessibility, localization, privacy, observability, testing, documentation, installer/updater, data migration, dan release engineering berjalan pada setiap fase, bukan sebagai fase terakhir.

## Prioritization

Gunakan urutan: legal/security necessity, tenant/data integrity, revenue/operational value, risk reduction, dependency enablement, user experience, optimization. Setiap item roadmap diturunkan menjadi task dengan owner, acceptance criteria, dependency, risk, dan evidence.

## Roadmap change control

Perubahan fase harus memperbarui PROJECT_MANIFEST.md, ROADMAP.md, TASKS.md, CHANGELOG.md, serta ADR/dokumen domain yang terdampak. Tanggal hanya ditambahkan setelah kapasitas, dependency, dan risk buffer tersedia.

## Accelerated Technical Preview track

Technical Preview v0.0.1 is a gated T+5 workstream tracked by Issue #23. It is a synthetic sandbox preview, not a production or pilot release.

| Working day | Planned outcome | Entry gate |
| ---: | --- | --- |
| 1 | Exact-head review of ADR, data, threat, hosting, recovery, and exit evidence | Product Owner decision package recorded |
| 2 | Application skeleton, configuration boundary, CI, tenant context | M7.0 bridge published and separate Product Owner M7.1 source-code authority explicitly granted; final Phase 0 Exit and actual P2 qualification are not prerequisites for bounded Local/Test/CI preparation |
| 3 | Identity, organization/outlet/device, catalog/cart, cash-sale vertical slice | Separate bounded authority plus applicable Day 2 quality and isolation gates |
| 4 | Migration/seeder, installer, deployment, backup/restore/rollback rehearsal | Actual target environment capability verified and separate deployment/rehearsal authority granted where required |
| 5 | Security, isolation, smoke, recovery, and staging acceptance | Combined source/security/runtime/recovery gates pass with no unresolved Critical/High Preview defect |

The source-engineering clock may begin only after the applicable bounded source authority is granted. Preview deployment/operational acceptance may not begin until the actual target is identified and DEC-009 mandatory capability evidence is sufficient. P1 remains conditional/not selected and P2 actual target evidence must not be invented. A missed mandatory gate moves the target; quality, tenant isolation, audit, security, or recovery controls must not be removed to preserve the date.

This track does not promote GD-007, resolve JRN-003/JRN-013, authorize production data or real payment, or change Phase 0 from In Progress before an explicit exact-head exit decision.

Historical Technical Preview planning language is preserved as planning history. Later bounded Platform Foundation publications through Sprint 13 are repository facts but do not retroactively rewrite historical lifecycle events.

## M7 — Technical Preview Implementation Enablement

M7 is a bounded Technical Preview workstream. Its labels describe sequencing and do not independently grant implementation authority or convert M7 into Sprint 14.

| Micro-milestone | Controlled outcome | Gate |
| --- | --- | --- |
| M7.0 — Controlled Implementation Bridge | Separate Local/Test/CI source readiness from Preview runtime/deployment readiness | Product Owner substantive bridge decision plus governed publication lifecycle |
| M7.1 — Application Skeleton & Configuration Boundary | Laravel/Vue/Inertia/Vite/TypeScript-first skeleton, config/secret boundary, health/readiness/correlation foundations, Local/Test/CI baseline | Separate exact bounded Product Owner source-code authority; exact dependency review before adoption |
| M7.2 — Tenant Kernel & Isolation Foundation | Tenant context and isolation primitives with negative verification | Separate bounded authority and applicable M7.1 evidence |
| M7.3 — Identity / Organization / Outlet / Device Minimum | Minimum first-party identity and organizational context | Separate bounded authority and applicable security gates |
| M7.4 — POS Core Synthetic Vertical Slice | Synthetic bounded POS core flow | Separate bounded authority and transaction/security gates |
| M7.5 — Preview Runtime Qualification | Qualify the actual P2 target under DEC-009 | Actual sanitized target evidence |
| M7.6 — Preview Deployment / Recovery Rehearsal | Deploy/recover/rollback on qualified target | Qualified target plus separate deployment authority |
| M7.7 — Technical Preview Acceptance | Combined technical acceptance | Required source, security, runtime, recovery, and operational evidence |

Track A (Controlled Application Engineering) and Track B (Preview Runtime Qualification) may progress in parallel when their dependencies are independent. Both tracks converge before Technical Preview deployment/acceptance.

## M5 — Engineering State, CI & Governance Stabilization

M5 was a control-plane and canonical-state stabilization track. It did not authorize Enterprise Vision, Sprint 14, production deployment, release, SQL execution, migration execution, or production database modification.

| Micro-milestone | Canonical state | Result |
| --- | --- | --- |
| M5.1 — Canonical State Reconciliation | PUBLISHED / COMPLETE | Canonical `docs/ai/` checkpoint location established and stale duplicate root state reduced to pointer stubs; published through PR #66 |
| M5.2 — CI & Lifecycle Control Hardening | PUBLISHED / ENFORCEMENT COMPLETE | A-03 and A-05 resolved; protected contexts include PHP foundation regression and exact-head Product Owner merge authority; published through PR #67 |
| M5.3 — Governance & Program State Synchronization | PUBLISHED / COMPLETE | A-06, A-07, and A-08 reconciled; published through PR #68 as `e45f5b4c0f143abc6e255e4e8550bf3504348aae` |

M5 publication facts remain immutable repository history.

## M6 — Enterprise Vision Canonicalization

**State:** PUBLISHED / PUBLICATION COMPLETE.

M6 publication scope covered Enterprise Vision analysis/documentation, capability-map and conceptual evolution definition, current program-state synchronization, brand normalization to `oneQay`, bounded publication preparation, validation, and independent review. The canonical representation was published through PR #69; M6 closure was completed through PR #71; substantive Enterprise Vision approval was separately granted through GOV-051.

M6 publication outcome preserved:

- Phase 0: In Progress;
- Sprint 12: Published;
- Sprint 13: Published;
- Sprint 14: Not Authorized;
- final/business/production application implementation: Blocked unless separately authorized;
- production readiness: NO-GO;
- ADR/GD/JRN statuses unless separately decided;
- historical lifecycle discrepancies as historical facts.

M6 publication did not and does not create Ready, Merge, deployment, release, SQL/migration execution, production database modification, Sprint 14, or new business/application source implementation authority.

Attribution: Lab | zefry
