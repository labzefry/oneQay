# OneQay Roadmap

## Roadmap principles

- Outcome lebih penting daripada jumlah fitur.
- Security, tenant isolation, migration, observability, backup, dan rollback adalah bagian fitur.
- Setiap fase memiliki entry/exit criteria dan tidak otomatis terikat tanggal sebelum kapasitas disetujui.
- Marketplace, plugin, AI automation, cloud, dan Kubernetes tidak dipercepat sebelum fondasi siap.

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

**Outcome:** OneQay dapat mengelola subscription dan tenant lifecycle secara komersial.

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

## Infrastructure evolution track

| Stage | Trigger | Required readiness |
|---|---|---|
| Shared Hosting | Initial controlled launch | scheduler, backup, secure config, monitoring baseline |
| VPS | Resource/control limit reached | automated deploy, externalized state, hardening |
| Dedicated | Sustained workload/isolation need | capacity model, HA/DR decision |
| Docker | Reproducibility and portability need | container-safe state and jobs |
| Cloud | Managed service/autoscaling value proven | cost, IAM, network, DR governance |
| Kubernetes | Operational complexity justified | SRE ownership, observability, platform maturity |

## Cross-cutting workstreams

Security, performance, accessibility, localization, privacy, observability, testing, documentation, installer/updater, data migration, and release engineering berjalan pada setiap fase, bukan sebagai fase terakhir.

## Prioritization

Gunakan urutan: legal/security necessity, tenant/data integrity, revenue/operational value, risk reduction, dependency enablement, user experience, optimization. Setiap item roadmap diturunkan menjadi task dengan owner, acceptance criteria, dependency, risk, dan evidence.

## Roadmap change control

Perubahan fase harus memperbarui PROJECT_MANIFEST.md, ROADMAP.md, TASKS.md, CHANGELOG.md, serta ADR/dokumen domain yang terdampak. Tanggal hanya ditambahkan setelah kapasitas, dependency, dan risk buffer tersedia.

## Accelerated Technical Preview track

Technical Preview v0.0.1 is a gated T+5 workstream tracked by Issue #23. It is a synthetic sandbox preview, not a production or pilot release.

| Working day | Planned outcome | Entry gate |
| ---: | --- | --- |
| 1 | Exact-head review of ADR, data, threat, hosting, recovery, and exit evidence | Product Owner decision package recorded |
| 2 | Application skeleton, configuration boundary, CI, tenant context | Phase 0 preview exit and source-code authority explicitly approved |
| 3 | Identity, organization/outlet/device, catalog/cart, cash-sale vertical slice | Day 2 quality and isolation gates pass |
| 4 | Migration/seeder, installer, deployment, backup/restore/rollback rehearsal | Target environment capability verified |
| 5 | Security, isolation, smoke, recovery, and staging acceptance | No unresolved Critical/High preview defect |

The calendar target begins only after Day 1 exit evidence is approved. P1 remains Unverified until hosting evidence is complete. A missed mandatory gate moves the target; quality, tenant isolation, audit, or recovery controls must not be removed to preserve the date.

This track does not promote GD-007, resolve JRN-003/JRN-013, authorize production data or real payment, or change Phase 0 from In Progress before an explicit exact-head exit decision.
