# OneQay Phase 0 Governance and Discovery Kickoff

## Mission

Phase 0 mengubah visi OneQay dan Handbook 1.0 menjadi keputusan produk serta engineering yang dapat dibuktikan. Fase ini tidak membuat source code aplikasi. Output utamanya adalah scope MVP, domain model, risk baseline, architecture decisions, dan executable backlog yang telah disetujui.

## Entry conditions

- Handbook 1.0 draft tersedia untuk review.
- Product identity dan GitHub SSOT disepakati.
- Product Owner dan decision owners dapat diidentifikasi.
- Credential yang pernah terekspos telah direvoke.
- Tidak ada pekerjaan source code yang berjalan tanpa governance.

Publikasi handbook ke branch dan review tetap menjadi gate approval; discovery non-code dapat dimulai paralel tanpa menganggap draft sebagai final.

## Non-goals

- Membuat application skeleton atau production code.
- Memilih framework/vendor tanpa evidence dan ADR.
- Mendesain seluruh ERP hingga detail final.
- Mengaktifkan public API, plugin marketplace, AI automation, cloud, atau Kubernetes.
- Menentukan tanggal rilis sebelum scope, kapasitas, dependency, dan risk buffer tersedia.

## Workstreams

### 1. Product and stakeholder discovery

Deliverables:

- stakeholder map dan decision rights;
- actor/persona berbasis peran;
- problem statement dan current workflow;
- prioritized jobs-to-be-done;
- measurable product outcomes;
- MVP scope, non-scope, assumptions, dan constraints.

### 2. Domain discovery

Deliverables:

- ubiquitous language glossary;
- event-storming result;
- candidate bounded contexts dan ownership;
- critical invariants untuk tenant, POS, inventory, payment, refund, dan reconciliation;
- context map dan integration hypotheses.

### 3. Data, privacy, and compliance

Deliverables:

- data inventory dan classification;
- tenant isolation requirements;
- retention, export, deletion, and audit requirements;
- payment/fiscal/compliance boundary;
- jurisdiction dan data residency questions;
- initial data-flow diagram.

### 4. Security and abuse discovery

Deliverables:

- threat model untuk authentication, tenant administration, POS transaction, payment callback, data export/delete, installer, dan updater;
- privileged role dan separation-of-duties matrix;
- credential/secret lifecycle;
- security verification backlog;
- incident and vulnerability reporting owners.

### 5. Experience discovery

Deliverables:

- critical journeys untuk platform admin, tenant owner, manager, cashier, inventory, finance, dan customer;
- information architecture hypothesis;
- device/connectivity and offline constraints;
- accessibility, locale, currency, timezone, and receipt requirements;
- measurable usability and performance outcomes.

### 6. Architecture and operations discovery

Deliverables:

- shared-hosting/cPanel capability assessment;
- technology evaluation criteria;
- tenancy physical-model options;
- authentication, database, payment, offline, deployment, observability, and recovery options;
- minimum Accepted ADR set;
- RPO/RTO and service objective proposals.

## Required workshops

| Sequence | Workshop | Primary output |
|---:|---|---|
| 1 | Product vision and decision rights | Outcomes, owners, constraints |
| 2 | Current process and user journeys | Problem evidence and actors |
| 3 | Domain event storming | Events, commands, policies, boundaries |
| 4 | MVP slicing | Scope/non-scope and success metrics |
| 5 | Data and threat modeling | Classification, flows, abuse cases |
| 6 | Architecture options | ADR proposals and trade-offs |
| 7 | Operational readiness | Stage-1 environment, backup, recovery, support |
| 8 | Phase 0 review | Approval or corrective tasks |

Workshop dapat digabung untuk tim kecil, tetapi output dan decision ownership tidak boleh hilang.

## Minimum decision package

Sebelum source code, sekurang-kurangnya diperlukan:

1. Approved MVP scope and non-scope.
2. ADR backend language/framework.
3. ADR frontend/PWA stack.
4. ADR database engine and tenancy physical model.
5. ADR authentication/MFA/session architecture.
6. ADR payment and compliance boundary.
7. ADR offline POS semantics.
8. ADR deployment stage 1 and runtime requirements.
9. Data classification and retention baseline.
10. Threat model for critical flows.
11. Quality gates and supported environment matrix.
12. Initial recovery objectives and backup/restore plan.

## Decision evaluation rubric

Setiap option dinilai terhadap:

- business fit dan time-to-value;
- shared-hosting feasibility serta migration path;
- tenant isolation dan security;
- correctness untuk money, inventory, dan concurrency;
- operability, observability, backup, and recovery;
- ecosystem maturity, maintenance, and talent availability;
- license, supply-chain, and vendor lock-in;
- performance and scalability evidence;
- total cost of ownership;
- exit and replacement strategy.

## Evidence rules

- Keputusan membedakan fact, assumption, constraint, dan preference.
- Claim penting memiliki source atau experiment plan.
- Prototype/experiment berada di `experiment/*`, time-boxed, synthetic-data only, dan bukan production foundation.
- Tidak ada technology choice menjadi Approved tanpa Accepted ADR.
- Open question memiliki owner dan decision deadline yang disepakati setelah kapasitas tersedia.

## Phase 0 exit criteria

- Product Owner menyetujui MVP scope/non-scope dan success metrics.
- Candidate bounded contexts, ownership, dan critical invariants direview.
- Minimum ADR package berstatus Accepted.
- Data classification, privacy/compliance questions, dan threat models tersedia.
- Tenant isolation, payment, offline, installer/updater, backup, dan recovery requirements dapat diuji.
- Phase 1 backlog memiliki acceptance criteria, dependencies, risk class, dan evidence plan.
- No Critical unresolved decision untuk memulai platform foundation.
- PROJECT_MANIFEST, ROADMAP, ARCHITECTURE, TASKS, CHANGELOG, dan dokumen domain diperbarui.

## Stop conditions

Phase 0 tidak boleh dinyatakan selesai bila MVP masih ambigu, compliance/payment boundary belum diketahui, tenant isolation belum dapat diverifikasi, deployment stage 1 tidak feasible, recovery belum direncanakan, atau technology choice hanya didasarkan pada preferensi tanpa evidence.

## First actionable queue

1. Publikasikan handbook melalui draft PR.
2. Tetapkan Product Owner dan decision owners.
3. Jalankan workshop Product Vision and Decision Rights.
4. Selesaikan stakeholder/actor map.
5. Jalankan domain event storming.
6. Susun MVP decision package.
7. Buat ADR proposals berurutan dari dependency paling fundamental.
8. Review Phase 0 exit evidence sebelum mengizinkan source code.
