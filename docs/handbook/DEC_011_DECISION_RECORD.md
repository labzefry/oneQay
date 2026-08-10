# DEC-011 — Data Retention, Privacy, and Jurisdiction Decision Record

- Status: Approved / Substantive Decision Complete
- Decision owner: Product Owner `labzefry`
- Product: oneQay
- Developer & Product Engineering Entity: Lab | zefry
- Repository: `labzefry/oneQay`
- Decision baseline: `6c6af7f99d25f177c91f92cdd163a277affc5153`
- Verified baseline tree: `efa336169e902e6bddd7f3fff47a0e91d15b5a19`
- Published predecessor: DEC-010 / PR #84
- Initial commercial / launch jurisdiction: **NOT YET CANONICALLY SELECTED**
- Legal status: **QUALIFIED LEGAL REVIEW REQUIRED** before jurisdiction-specific legal implementation and externally relied-upon legal documents
- Scope: product/privacy architecture policy only

GitHub adalah Single Source of Truth. Baseline di atas adalah provenance keputusan substantif dan bukan klaim live-state permanen. Fresh verification tetap wajib sebelum mutation atau lifecycle transition berikutnya.

## Decision

DEC-011 establishes the following bounded direction:

**BOUNDED PRIVACY-BY-DESIGN + HYBRID BOUNDED RETENTION + JURISDICTION-PROFILE ARCHITECTURE**.

DEC-011 does not select Indonesia or any other launch jurisdiction. Jurisdiction-specific legal requirements, final legal roles, statutory retention periods, legal notices/contracts, hosting/data-residency region, providers/subprocessors, implementation, deployment, release, production, DEC-012, and Sprint 14 remain separately gated.

## D-011 dispositions

### D-011-01 — Data inventory and classification

Approved. Maintain an auditable canonical processing/data inventory while preserving the existing security classification: Public, Internal, Confidential, and Restricted.

Technical Preview `Prohibited` remains a preview handling state and is not promoted into a universal production security class.

Privacy-processing attributes may include personal-data and sensitive/high-risk indicators, tenant/data owner, business owner, purpose, data-subject category, processing-basis profile, system/location, recipient/subprocessor, retention rule, export rule, deletion/anonymization disposition, legal-hold state, and audit evidence.

This is policy only; no physical schema is authorized.

### D-011-02 — Privacy roles

Approved: **ACTIVITY-BASED PRIVACY ROLE MODEL**.

oneQay is not globally and permanently declared only a controller, processor, joint controller, or independent controller. Privacy responsibility depends on actual processing activity, purpose, jurisdiction, contractual relationship, and data ownership/responsibility.

Final statutory controller/processor/joint-controller classification remains **QUALIFIED LEGAL / CONTRACT REVIEW REQUIRED**.

### D-011-03 — Purpose limitation / processing basis

Approved. Personal-data processing requires a defined purpose, minimum necessary scope, accountable owner, applicable processing-basis profile, access boundary, retention rule, and deletion/end-of-purpose rule.

No universal legal basis is hard-coded. Exact lawful basis remains jurisdiction/context dependent.

### D-011-04 — Data minimization

Approved: **MINIMIZATION BY DEFAULT**.

Required versus optional data must be explicit; unnecessary personal/sensitive data is avoided; masking, redaction, pseudonymization, anonymization, and aggregation are used where appropriate; logs/diagnostics contain minimum necessary data; telemetry is minimized; and test/demo evidence uses synthetic data by default.

Customer identity must not silently become mandatory for every POS sale without separate business/legal evidence.

### D-011-05 — Tenant privacy and isolation

Approved: preserve DEC-005.

Shared database + shared schema + immutable tenant isolation key remains the default physical-tenancy direction. Privacy requirements do not silently convert oneQay to database-per-tenant.

Policy requires server-authoritative tenant context, tenant-scoped access, cross-tenant fail closed, explicit platform-admin exceptional-access contracts, step-up where appropriate, purpose limitation, time-bounded support/impersonation where applicable, audit, and tenant-scoped export/deletion/recovery boundaries.

Stronger physical isolation remains a separately evidenced and authorized future evolution path.

### D-011-06 — Retention policy model

Approved: **HYBRID BOUNDED RETENTION MODEL**.

Retention precedence:

1. statutory / mandatory legal obligation;
2. legal hold / investigation / dispute;
3. contractual obligation;
4. security/integrity requirement;
5. bounded product default;
6. tenant configuration within allowed bounds.

No universal global retention duration is approved. Tenant configuration must not shorten mandatory retention, bypass legal hold, or retain data indefinitely without purpose/authority.

### D-011-07 — Data-class retention

Approved framework; final production periods remain deferred.

Technical Preview retention values remain historical/proposed preview values only.

Future retention decisions use the categories:

- PRODUCT DEFAULT CANDIDATE;
- SECURITY REQUIREMENT CANDIDATE;
- BUSINESS / CONTRACT INPUT REQUIRED;
- STATUTORY / JURISDICTION INPUT REQUIRED;
- DEC-012 DEPENDENCY;
- NOT YET APPLICABLE.

Transaction/payment/fiscal retention must not be invented before applicable jurisdiction and business model are known.

### D-011-08 — Deletion / anonymization / pseudonymization

Approved: **SERVER-AUTHORITATIVE DATA-LIFECYCLE SEMANTICS**.

End-of-retention handling may use, according to classification and obligation, hard deletion, anonymization, pseudonymization, archival, legal hold, minimal immutable audit evidence, and backup expiry.

Deletion is not merely hiding records from UI or arbitrary soft deletion. Future implementation must reconcile authoritative data, derived data, cache/search, temporary exports, files, controllable local/device copies, and backup lifecycle.

No deletion implementation is authorized.

### D-011-09 — Backup and recovery privacy

Approved. Require encryption/access control, explicit backup expiry, privacy-aware restore, no silent resurrection of authoritatively deleted data, future deletion/tombstone reconciliation after restore, isolated restore verification, and auditability.

DEC-012 retains final RPO/RTO/service/recovery objectives. DEC-011 owns privacy retention/deletion semantics for backup data.

### D-011-10 — Logging / audit / security retention

Approved. Logs and audit must exclude secrets and Restricted authentication/payment material, minimize personal/sensitive data, preserve integrity, carry safe correlation, support tenant and privileged-access accountability, audit applicable export/delete/impersonation/security actions, be classified, have explicit retention rules, and support lawful hold where required.

Final retention periods remain separately determined.

### D-011-11 — Data-subject / individual rights

Approved architecture direction only.

Future architecture must be capable of handling, where applicable, access, correction, deletion, restriction, objection, portability/export, and consent withdrawal.

Responsibility routing must support tenant/customer organization handling where it holds the relevant responsibility and oneQay handling where oneQay holds the relevant responsibility.

No rights-request workflow implementation is authorized.

### D-011-12 — Consent / cookie / tracking boundary

Approved: separate essential first-party authentication/session processing from optional analytics, advertising/marketing tracking, optional telemetry, and consent-dependent technology.

Optional processing remains separately gated until purpose, necessity, processing basis, jurisdiction, provider, retention, and disclosure/consent requirements are established.

No analytics, advertising, or telemetry provider is selected.

### D-011-13 — Sensitive / high-risk data

Approved: **FAIL CLOSED / REVIEW REQUIRED** for unknown high-risk processing.

Avoid unnecessary sensitive/high-risk data; require classification before adoption; apply stronger least privilege, encryption, audit, and monitoring where applicable; and require privacy/security impact review for high-risk processing.

### D-011-14 — Children / age-dependent data

Approved current posture: **NOT TARGETED / SEPARATELY GATED**.

DEC-011 does not intentionally add children/minor-focused personal-data processing and does not establish a universal age threshold. Future scope requires separate Product Owner, privacy, security, jurisdiction, and qualified legal review.

### D-011-15 — Data residency / cross-border transfer

Approved: **JURISDICTION-PROFILE ARCHITECTURE**.

Future architecture must be able to identify data category, tenant/data owner, processing purpose, system/provider, storage/processing location, backup location, subprocessor, transfer path, applicable privacy profile, and retention/deletion controls.

No universal data localization, cloud, hosting provider, country, region, or transfer mechanism is selected.

### D-011-16 — Jurisdiction model

Approved: **STAGED JURISDICTION EXPANSION**.

Domain/Application boundaries remain jurisdiction-neutral while policy/configuration/Infrastructure/legal-compliance boundaries remain capable of explicit jurisdiction profiles.

Current state remains:

**INITIAL JURISDICTION: NOT YET CANONICALLY SELECTED**.

Before jurisdiction-specific legal implementation and production launch, Product Owner/business input must establish intended initial commercial jurisdiction(s).

### D-011-17 — Processor / subprocessor governance

Approved: **PRE-ADOPTION PRIVACY / SECURITY / LEGAL GATE**.

Future services processing tenant/personal data require assessment of purpose, data category, responsibility, security, processing/storage locations, subprocessor chain, cross-border implications, retention/deletion, incident obligations, provider reuse/training terms where relevant, audit/evidence, termination/exit, and contract/legal requirements.

No provider or subprocessor is selected.

### D-011-18 — AI / model / data privacy

Approved: **FAIL CLOSED / NO PROVIDER**.

Before tenant/personal data enters a future AI boundary require classification, minimization/redaction, tenant authorization, purpose, processing-basis profile, provider/model retention review, provider training/reuse review, processing-location/cross-border review, deletion semantics, audit, required disclosure/consent, and security review.

Restricted secrets must not become ordinary AI context. DEC-010 licensing requirements remain binding. No AI provider, model, dataset, or implementation is approved.

### D-011-19 — Client / offline / mobile privacy

Approved: preserve DEC-004 and DEC-008.

Local state must remain minimal, classified, tenant-scoped, user/device/session scoped where applicable, protected according to classification, bounded in retention, invalidated/isolated after material tenant/user/security changes, and excluded from unsafe logs/analytics.

Server remains authoritative. No local database, encryption library, secure-storage library, queue technology, sync technology, or transactional-offline implementation is approved.

### D-011-20 — Incident / breach privacy

Approved: **JURISDICTION-AWARE INCIDENT POLICY**.

Future incident response must support detect, classify, contain, preserve evidence, determine affected tenant/data/data-subject scope, assign responsibility, communicate where required, support regulator/data-subject notification where applicable, remediate, verify, and record follow-up.

No global statutory notification deadline is hard-coded. Jurisdiction-specific deadlines remain qualified-legal-review inputs.

### D-011-21 — Legal hold / dispute / statutory retention

Approved. Legal/statutory/contractual hold may override ordinary deletion only with explicit authority, reason, affected data scope, owner, effective time, audit, review/expiry, and release/resumption process.

Legal hold must not become silent indefinite retention.

### D-011-22 — Test / Preview / demo data

Approved: **SYNTHETIC BY DEFAULT**.

- Local: Synthetic.
- Test / CI: Synthetic by default.
- Preview: Synthetic or separately approved masked data.
- Production: Real classified data only after separate production authority.

Raw production/customer/credential/payment-sensitive data must not be copied into development/test/demo merely for convenience. Masked data requires an approved process and residual-risk review.

### D-011-23 — Export / portability / data egress

Approved architecture direction.

Future authorized export must support tenant/object scoping, authorization, step-up where appropriate, audit, secure generation, protected temporary storage, bounded expiry, secure delivery, deletion evidence, and machine-readable format where legally/business required.

No export implementation is authorized. **JRN-013 remains UNRESOLVED**.

### D-011-24 — Explicit non-scope

Approved.

DEC-011 does not authorize:

- application/source implementation;
- schema/SQL/DDL/migrations;
- database changes;
- final Privacy Policy;
- final DPA;
- final SaaS/customer terms;
- final controller/processor legal classification;
- final statutory retention periods;
- provider/subprocessor selection;
- hosting-region selection;
- procurement;
- production/customer-data ingestion;
- actual cross-border transfer;
- analytics/advertising-provider adoption;
- telemetry implementation;
- cookie/consent implementation;
- data-subject-right workflow implementation;
- deletion implementation;
- AI provider/model/dataset adoption;
- offline transaction implementation;
- Android/PWA privacy implementation;
- marketplace/plugin implementation;
- deployment;
- release;
- production;
- DEC-012;
- Sprint 14.

## Qualified legal review boundary

DEC-011 is product and architecture policy only and is not formal legal advice.

Qualified legal review remains required for actual applicable jurisdiction, extraterritorial applicability, controller/processor/joint-controller classification, lawful bases, Privacy Policy/privacy notice, DPA/customer terms, statutory retention periods, individual-right requirements/deadlines, children/minor requirements, sensitive/high-risk processing, DPO/privacy-function requirements, cross-border transfer/localization, incident notification obligations, legal holds, subprocessor contracts, payment/fiscal/consumer rules, cookie/tracking/marketing, and AI processing.

Jurisdiction-specific legal facts must be current-verified before becoming canonical legal requirements.

## Preserved decisions and boundaries

- DEC-004 Android/local-state direction remains binding.
- DEC-005 physical tenancy and tenant-isolation architecture remains binding.
- DEC-006 identity/session architecture remains binding; **JRN-003 remains UNRESOLVED**.
- DEC-008 offline semantics remains binding.
- DEC-009 deployment/runtime boundaries remain binding.
- DEC-010 licensing and third-party notice policy remains binding.
- DEC-012 retains final RPO/RTO and support/service/recovery-objective ownership.
- GD-007 remains Proposed.
- JRN-013 remains UNRESOLVED.
- Historical Technical Preview DATA-1/REC-1/SLO-1/TEN-1 and retention/RPO/RTO values remain historical/proposed and are not production policy.

## Program and lifecycle boundary

- Phase 0: **IN PROGRESS**.
- Sprint 14: **NOT AUTHORIZED**.
- Final/business/production implementation: **BLOCKED / SEPARATELY GATED**.
- Deployment: **NOT AUTHORIZED**.
- Release: **NOT AUTHORIZED**.
- Production readiness: **NO-GO**.
- DEC-012: **BACKLOG**.

Publication Preparation, Independent Exact-Head Review, Product Owner READY Authority, Product Owner MERGE Authority, Implementation Authority, Deployment Authority, Release Authority, and Production Authority remain separate lifecycle gates.

Attribution: **Lab | zefry**
