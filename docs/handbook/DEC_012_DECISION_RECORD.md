# DEC-012 — RPO/RTO and Support Objectives Decision Record

- Status: Approved / Substantive Decision Complete
- Decision owner: Product Owner `labzefry`
- Product: oneQay
- Developer & Product Engineering Entity: Lab | zefry
- Repository: `labzefry/oneQay`
- Decision baseline: `a7821517a03cf868adf56bfa7d91c878d8c364ac`
- Verified baseline tree: `aa81d2f071725abc91f2cf9f71a2498832e47cd2`
- Published predecessor: DEC-011 / PR #85
- Scope: product/operational recovery and support policy only
- Final numerical Production RPO: **NOT APPROVED / DEFERRED**
- Final numerical Production RTO: **NOT APPROVED / DEFERRED**
- Customer contractual SLA: **NOT APPROVED / DEFERRED**
- Recovery verification: **EVIDENCE-GATED**

GitHub adalah Single Source of Truth. Baseline di atas adalah provenance keputusan substantif dan bukan klaim live-state permanen. Fresh verification tetap wajib sebelum mutation atau lifecycle transition berikutnya.

## Decision

DEC-012 establishes the bounded direction:

**CAPABILITY-TIERED / EVIDENCE-BASED RECOVERY & SUPPORT POLICY**.

Recovery and support objectives vary according to capability criticality. Backup success alone does not constitute verified recoverability. A recovery claim requires measured successful restoration evidence including integrity validation, tenant-isolation validation, applicable business-invariant validation, applicable reconciliation, DEC-011 privacy-aware recovery, and recorded achieved recovery evidence.

DEC-012 does not approve final numerical Production RPO/RTO, final numerical Production SLO, customer-contractual SLA, provider/cloud/region selection, HA/replication/multi-region implementation, infrastructure provisioning, implementation, deployment, release, Production, Phase 0 exit, or Sprint 14.

## Historical provenance protection

Historical Technical Preview values remain provenance only:

- REC-1 RPO 24 hours: **TECHNICAL PREVIEW PROPOSAL ONLY**.
- REC-1 RTO 4 hours: **TECHNICAL PREVIEW PROPOSAL ONLY**.
- SLO-1: **TECHNICAL PREVIEW PROVENANCE ONLY**.
- Technical Preview backup retention and other provisional recovery values remain preview evidence/proposals only.

No lower-stage target is promoted into Production policy or customer commitment by DEC-012.

## D-012 dispositions

### D-012-01 — Service / capability criticality

Approved: **FOUR-TIER CAPABILITY CRITICALITY MODEL**.

1. **CRITICAL TRANSACTION PATH**.
2. **IMPORTANT OPERATIONAL PATH**.
3. **ADMINISTRATIVE / NON-CRITICAL PATH**.
4. **DEFERRED / OPTIONAL CAPABILITY**.

Capability criticality determines recovery and support priority. Capability-map presence alone does not make a capability Critical.

Initial non-exhaustive Critical Transaction Path candidates may include tenant authorization required for transaction safety, sale transaction integrity, payment evidence integrity, and inventory transaction integrity when transaction-critical.

Important Operational Path candidates may include operational reporting required for reconciliation, scheduled operational work, and reconciliation support.

Administrative / Non-Critical means interruption does not threaten transaction or data integrity. Deferred / Optional may include optional analytics, marketing, future AI, and future marketplace capabilities.

This classification is directional and does not exhaustively classify every future capability.

### D-012-02 — RPO policy model

Approved: **BOUNDED HYBRID RPO MODEL**.

Default recovery-objective direction is capability-tier based. Future bounded tenant/contract profiles may apply stricter objectives only where separately justified and authorized.

No final numerical Production RPO is approved by DEC-012.

Numerical Production RPO requires later evidence including:

- business data-loss tolerance;
- actual backup capability;
- backup frequency/freshness;
- successful restore evidence;
- reconciliation capability;
- runtime capability;
- operating capacity.

Historical REC-1 RPO 24 hours remains Technical Preview proposal only.

### D-012-03 — RTO policy model

Approved: **CAPABILITY-TIER / LAYERED RTO MODEL**.

Recovery objectives distinguish where materially relevant:

- infrastructure restoration;
- database restoration;
- application technical availability;
- tenant-access restoration;
- transaction-capability restoration;
- business-operational recovery;
- dependency recovery;
- reconciliation completion.

These are not assumed to occur at the same timestamp.

No final numerical Production RTO is approved by DEC-012. Historical REC-1 RTO 4 hours remains Technical Preview proposal only.

### D-012-04 — Backup / restore evidence

Approved: **EVIDENCE-GATED RECOVERY CLAIM**.

Backup success alone is not recoverability evidence.

A recovery objective may be claimed as VERIFIED only when appropriate evidence records at minimum:

- source release / commit;
- rehearsal environment;
- backup creation time;
- backup freshness;
- integrity/checksum validation;
- restore start/end;
- successful isolated restore;
- schema/application compatibility;
- restored-data integrity;
- tenant isolation;
- critical business-invariant verification;
- application health;
- applicable replay/reconciliation;
- DEC-011 privacy/deletion reconciliation;
- achieved RPO;
- achieved RTO;
- evidence owner/operator;
- rehearsal date;
- result;
- remediation/re-test where failures occurred.

An unperformed rehearsal must not be represented as successful recovery evidence.

### D-012-05 — Disaster recovery model

Approved: **PROGRESSIVE / CAPABILITY-BASED DR DIRECTION**.

Bounded future progression:

1. same-environment-class restoration;
2. alternate compatible host/environment restoration;
3. standby capability only if justified;
4. failover only if justified;
5. multi-region only if justified by material business, risk, contractual, regulatory, or operational evidence.

DEC-012 does not select:

- hosting provider;
- cloud provider;
- country;
- region;
- database provider;
- replication product;
- HA product;
- orchestration platform;
- Kubernetes;
- multi-region implementation.

DEC-009 remains binding.

### D-012-06 — Support coverage

Approved: **SEVERITY-BASED BOUNDED SUPPORT MODEL**.

oneQay architecture and future operations must remain capable of differentiated support coverage according to severity, capability, and separately authorized tenant/customer profile.

DEC-012 does not establish:

- specific business hours;
- extended-hours commitment;
- 24x7 staffing commitment;
- customer response-time commitment;
- pricing;
- support packages.

24x7 support may become a future separately evidenced direction for defined Critical severity, enterprise, or high-assurance needs only after staffing, operating, business, and contractual evidence exists.

### D-012-07 — Incident severity model

Approved: **CRITICAL / HIGH / MEDIUM / LOW**.

Critical may include:

- cross-tenant security/privacy breach;
- material data loss or corruption;
- authentication/payment compromise;
- material Critical Transaction Path outage without safe workaround;
- recovery failure threatening integrity.

High may include:

- tenant-wide critical operational outage without safe workaround;
- material payment-evidence inconsistency;
- material inventory-integrity incident.

Medium means important capability degraded with a safe workaround.

Low means administrative, non-critical, minor, or cosmetic impact.

Security/privacy Critical incidents remain distinguishable from ordinary availability incidents. No contractual severity response times are approved.

### D-012-08 — Response / restoration objectives

Approved: **SEPARATE OBJECTIVE SEMANTICS**.

Distinguish:

- acknowledgement;
- triage/investigation;
- containment;
- mitigation;
- technical service restoration;
- data recovery;
- business-operational recovery;
- reconciliation;
- communication;
- post-incident remediation.

**ACKNOWLEDGEMENT TIME IS NOT RTO.**

**SERVICE RESTORATION IS NOT AUTOMATICALLY FULL BUSINESS RECOVERY.**

### D-012-09 — SLO / SLA / internal objectives

Approved taxonomy:

- **SLO:** engineering/product reliability objective.
- **INTERNAL OPERATIONAL TARGET:** non-contractual internal operating objective.
- **SLA:** customer contractual commitment.

DEC-012 approves the governance/taxonomy and internal/SLO direction only.

DEC-012 does not approve:

- final numerical Production SLO;
- contractual SLA;
- service credit;
- financial remedy;
- externally relied-upon uptime commitment.

Historical SLO-1 remains Technical Preview provenance only.

### D-012-10 — Availability and measurement

Approved future capability-aware measurement direction for:

- service availability;
- transaction success;
- latency where operationally material;
- scheduler/job health;
- queue/worker health where applicable;
- database/storage dependency health;
- backup freshness;
- recovery readiness;
- restore-evidence freshness;
- incident duration;
- degraded-operation duration.

No monitoring vendor or package is selected.

### D-012-11 — Dependency recovery

Approved: **DEPENDENCY CRITICALITY FOLLOWS THE CAPABILITY IT SERVES**.

Core dependency recovery may include where applicable:

- canonical database;
- required persistent storage;
- required application runtime;
- required tenant/authentication boundary;
- scheduler/worker when required by a Critical capability.

Optional/deferred dependencies do not automatically become part of the core Critical Transaction Path, including optional email/notification, optional external APIs, analytics, future AI/provider capabilities, and future marketplace capability.

Future payment-provider recovery preserves DEC-007. Offline/degraded operation preserves DEC-008. Runtime/provider boundaries preserve DEC-009.

### D-012-12 — Tenant / contract profiles

Approved architecture compatibility with future bounded recovery/support profiles such as:

- Standard SaaS;
- Enterprise;
- Dedicated;
- Regulated / High-Assurance.

This is product/architecture policy only.

DEC-012 does not create commercial plans, pricing, support packages, customer contracts, dedicated infrastructure, or stronger numerical recovery commitments.

Any stricter future profile requires technical evidence, operational capacity, business justification, and applicable contractual/legal authority.

### D-012-13 — Privacy / recovery interlock

Approved: DEC-011 remains binding over recovery.

Recovery must not:

- silently resurrect authoritatively deleted data;
- casually reintroduce expired data;
- bypass legal hold semantics;
- bypass backup expiry/retention policy;
- ignore deletion/tombstone reconciliation;
- copy unsafe Production data into non-production rehearsal.

DEC-011 owns privacy retention/deletion semantics. DEC-012 owns recovery objectives and recovery/service evidence. DEC-012 does not establish statutory retention periods.

### D-012-14 — Test / Preview versus Production objectives

Approved explicit separation between:

1. **TECHNICAL PREVIEW REHEARSAL TARGET**;
2. **PREVIEW OPERATIONAL TARGET**;
3. **INTERNAL FUTURE PRODUCTION RELIABILITY OBJECTIVE**;
4. **PRODUCT OWNER APPROVED PRODUCTION POLICY**;
5. **CUSTOMER CONTRACTUAL SLA**.

No lower-stage target automatically becomes a higher-stage commitment. REC-1 and SLO-1 are not silently promoted.

### D-012-15 — Recovery rehearsal / evidence

Approved: **RECOVERY OBJECTIVES ARE EVIDENCE-GATED**.

Future authorized operations must support, according to applicable risk:

- restore rehearsal;
- rollback rehearsal;
- failure simulation where appropriate;
- evidence retention;
- named evidence owner;
- failed-test remediation;
- corrective action;
- mandatory re-test after failed verification;
- re-test after material architecture/runtime changes;
- privacy-aware rehearsal cleanup.

A failed or missing rehearsal means the associated recovery claim remains **UNVERIFIED**.

### D-012-16 — Explicit non-scope

Approved.

DEC-012 does not authorize:

- backup implementation;
- restore implementation;
- DR/failover implementation;
- infrastructure provisioning;
- hosting/cloud/provider selection;
- region selection;
- replication technology;
- HA technology;
- multi-region implementation;
- numerical Production RPO;
- numerical Production RTO;
- final numerical Production SLO;
- customer SLA;
- service credits;
- pricing;
- support-package launch;
- staffing/on-call commitment;
- source/application implementation;
- schema;
- SQL;
- DDL;
- migrations;
- database modification;
- monitoring-provider adoption;
- dependency/package installation;
- production/customer-data processing;
- deployment;
- release;
- Production;
- Phase 0 exit;
- Sprint 14.

## Preserved decision ownership

DEC-012 preserves prior decision ownership:

- DEC-005 owns canonical MySQL/tenancy architecture and database recoverability principles.
- DEC-007 owns payment/provider architecture.
- DEC-008 owns offline transaction/degraded-operation semantics.
- DEC-009 owns runtime/deployment capability architecture and provider-selection boundary.
- DEC-010 owns licensing/dependency governance.
- DEC-011 owns privacy, retention/deletion, jurisdiction profiles, backup-data privacy, and privacy-aware recovery.

JRN-003 remains **UNRESOLVED**.

JRN-013 remains **UNRESOLVED**.

GD-007 is **NOT RESOLVED BY DEC-012**.

Initial launch jurisdiction remains **NOT YET CANONICALLY SELECTED**.

## Separate post-DEC-012 gaps

The following remain outside DEC-012 and are not adopted or implemented here:

- Zero Mandatory License-Cost baseline;
- Free / Open-Source First dependency preference;
- Apache ECharts visualization direction.

These may later be handled through a separately authorized DEC-010 supplement or equivalent bounded Product Owner decision.

## Program state preserved

- Phase 0: **IN PROGRESS**.
- Sprint 12: **PUBLISHED**.
- Sprint 13: **PUBLISHED**.
- Sprint 14: **NOT AUTHORIZED**.
- Final/business/production implementation: **BLOCKED / SEPARATELY GATED**.
- Deployment: **NOT AUTHORIZED**.
- Release: **NOT AUTHORIZED**.
- Production: **NOT AUTHORIZED**.
- Production readiness: **NO-GO**.

## Authority boundary

DEC-012 is an approved product/operational policy only. It does not itself authorize implementation, backup/restore execution, disaster-recovery execution, infrastructure, provider selection, dependency adoption, numerical Production objectives, customer SLA, deployment, release, Production, Phase 0 exit, or Sprint 14.

Attribution: Lab | zefry
