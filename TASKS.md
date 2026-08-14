# oneQay Tasks

## Status legend

| Status | Meaning |
| --- | --- |
| Backlog | Belum diprioritaskan |
| Ready | Scope dan acceptance criteria siap |
| In Progress | Sedang dikerjakan |
| Blocked | Menunggu dependency/decision/authority |
| Review | Menunggu review/approval |
| Done | Evidence dan Definition of Done lengkap |

## Canonical naming rule

Nama produk canonical adalah **oneQay**. Current/future-facing task text harus menggunakan `oneQay`; immutable GitHub identifiers, repository path `labzefry/oneQay`, SHAs, historical commit messages, branch names, dan quoted historical evidence tidak ditulis ulang hanya untuk brand normalization.

## Handbook 1.0

| ID | Task | Status | Evidence / next action |
| --- | --- | --- | --- |
| GOV-001 | README project orientation | Done | `README.md` |
| GOV-002 | Project manifest | Done | `PROJECT_MANIFEST.md`; PR #1 |
| GOV-003 | AI constitution | Done | `AI_CONSTITUTION.md`; PR #1 |
| GOV-004 | Architecture baseline | Done | `ARCHITECTURE.md`; handbook baseline approval |
| GOV-005 | Product/engineering roadmap | Done | `ROADMAP.md`; handbook baseline approval |
| GOV-006 | Coding standards | Done | `CODING_STANDARDS.md`; handbook baseline approval |
| GOV-007 | Database handbook | Done | `DATABASE.md`; handbook baseline approval |
| GOV-008 | API governance | Done | `API_SPEC.md`; handbook baseline approval |
| GOV-009 | Security handbook | Done | `SECURITY.md`; handbook baseline approval |
| GOV-010 | Deployment handbook | Done | `DEPLOYMENT.md`; handbook baseline approval |
| GOV-011 | Testing strategy | Done | `TESTING.md`; handbook baseline approval |
| GOV-012 | UI/UX guideline | Done | `UI_GUIDELINE.md`; handbook baseline approval |
| GOV-013 | Installer specification | Done | `INSTALLER.md`; handbook baseline approval |
| GOV-014 | Updater specification | Done | `UPDATER.md`; handbook baseline approval |
| GOV-015 | Contribution workflow | Done | `CONTRIBUTING.md`; PR #1 |
| GOV-016 | Release management | Done | `RELEASE.md`; handbook baseline approval |
| GOV-017 | Task governance | Done | `TASKS.md`; PR #1 |
| GOV-018 | Changelog baseline | Done | `CHANGELOG.md`; PR #1 |
| GOV-019 | Markdown/link/security consistency validation | Done | 35 Markdown files linted; links and secret scan passed on PR #1 |
| GOV-020 | Publish handbook branch and draft PR | Done | PR #1 merged as `642437b` |
| GOV-021 | Product Owner handbook review | Done | Product Owner approved and merged PR #1 |
| GOV-022 | Phase 0 governance and discovery kickoff pack | Done | `docs/handbook/PHASE_0_KICKOFF.md`; PR #1 |
| GOV-023 | Standardize engineering collaboration to ChatGPT + GitHub only | Done | `AI_CONSTITUTION.md`; PR #1 |
| GOV-024 | Product vision and decision rights | Done | DEC-000 substantive Product Owner decision APPROVED on baseline `792b2dc30636bc53baa7d66b43cf2dab4a348dd4`; `docs/handbook/PRODUCT_VISION_AND_DECISION_RIGHTS.md`; `docs/handbook/DEC_000_DECISION_RECORD.md`; GD-003 Approved within DEC-000 boundary only |
| GOV-025 | Stakeholder and actor map | Review | Issue #4; PR #5 menunggu review dan persetujuan Product Owner |
| GOV-026 | Current process and user journeys | Review | Issue #6; draft document prepared for Product Owner review |
| GOV-027 | Domain event storming | Review | Issue #8 dan PR #9 merged; GD-007 tetap Proposed |
| GOV-028 | Correct approved Domain Event Storming review findings | Review | Issue #10 dan PR #11 merged tanpa approval substantif; audit lanjutan tercatat pada PR #11 |
| GOV-029 | Correct approved PR #11 Domain Event Storming audit findings | Review | Issue #12 dibuka kembali; empat koreksi pada head PR #13 `e4a3b7b` diratifikasi setelah merge; closure diblokir Issue #16/#18/#20 |
| GOV-030 | Reconcile PR #13 merge-before-approval | Review | Issue #14 dibuka kembali; recurrence berlanjut pada PR #15/#17/#19; completion diblokir Issue #16/#18/#20 |
| GOV-031 | Harden exact-head approval and issue closure controls | Review | Issue #16 dibuka kembali; protection control kembali dilanggar pada PR #17/#19; effectiveness diblokir Issue #18/#20 |
| GOV-032 | Reconcile PR #17 recurrence and enforce protection gate | Review | Issue #18 dibuka kembali; PR #19 merged tanpa required evidence/authority; completion diblokir Issue #20 |
| GOV-033 | Reconcile PR #19 recurrence and separate formal risk acceptance | Review | Issue #20; exact-head post-merge decision, protection evidence/risk acceptance, dan enforcement evidence masih pending |
| GOV-034 | Reconcile PR #25 recurrence and premature Issue #23 closure | Review | PR #25 head `ca2157096b310b114203d919cb8182e55a6fa5f9` merged as `93c8b8d4d8dae399c0d3f758c50460cf086e2322` without available separate exact-head lifecycle authority or published checks; Issue #23 closure is not completion evidence |
| GOV-035 | Reconcile PR #26 post-merge recurrence | Review | PR #26 head `63223b9b856bd67e739651a1e23cc071971998c3` merged as `294fe24381e88b61701868567cda4be532640ab0`; Product Owner approved content accuracy only, while lifecycle authority, protection disposition, independent review, and Issue #23 state alignment remain pending |
| GOV-036 | Reconcile PR #27 post-merge recurrence | Review | PR #27 head `c6adb55a9a6cd2ebedd78668ccaf5fd64c041d94` merged as `3c4bcfe9797a3ae7f4deb124568ef361d74125e5`; Product Owner approved content accuracy only, while lifecycle authority, repository-control disposition, protection evidence/risk acceptance, independent review, Issue #23 state alignment, and effectiveness evidence remain pending |
| GOV-037 | Reconcile PR #28 post-merge recurrence | Review | PR #28 head `0597d784f63cf6d5967cedae17ca8d0b5a2e4dc9` merged as `1009af84ec0ee7d7731890e379dde25279280c3a`; Product Owner approved content accuracy only, while lifecycle authority, repository-control disposition, protection evidence/risk acceptance, independent review, Issue #23 state alignment, and effectiveness evidence remain pending |
| GOV-038 | Reconcile PR #29 post-merge recurrence | Review | PR #29 head `54a5773c3ab65a33e35ef2646089727490a0ff8d` merged as `f55d86f1a3d89a6bcbbbcf7800851b9c61f8c047`; repository-native operational authority explicitly excluded ready/merge, while lifecycle authority, repository-control disposition, direct protection evidence or formal scoped risk acceptance, independent review, Issue #23 state alignment, root-cause analysis, and effectiveness evidence remain pending |
| GOV-039 | Reconcile PR #30 post-merge recurrence | Review | PR #30 head `f3703650f98e5d6abfdb21d9b67ac7c5567ea9f6` merged as `54bc51a7a150394748dcc5f6a2fb8e376206feba`; repository-native operational authority explicitly excluded ready/merge/auto-merge, while lifecycle authority, repository-control disposition, direct protection evidence or formal scoped risk acceptance, independent review, Issue #23 state alignment, root-cause analysis, and effectiveness evidence remain pending |
| GOV-040 | Reconcile PR #31 post-merge recurrence | Review | PR #31 head `10b5179b16c104e1877153b066e96a937ece9c9b` merged as `67059e563de26cee26cefd64cf9e7d5c4436ffc6`; repository-native operational authority explicitly excluded ready/merge/auto-merge/approval review, while repository-control disposition, direct protection evidence or formal scoped risk acceptance, independent exact-head review, actor or bypass identification, root-cause analysis, corrective/preventive action, effectiveness evidence, and Issue #23 state alignment remain pending |
| GOV-041 | Reconcile PR #32 post-merge recurrence | Review | PR #32 head `beb7b35aa718a746ad5dad9d5574c2293bd0ab40` merged as `d1a6160b37250bda691e906fc4ee06e37dd0c847`; repository-native operational authority explicitly excluded ready/merge/auto-merge/approval review and branch-protection/ruleset changes, while repository-control disposition, direct protection evidence or formal scoped risk acceptance, independent exact-head review, actor or bypass identification, root-cause analysis, corrective/preventive action, effectiveness evidence, and Issue #23 state alignment remain pending |
| GOV-042 | Reconcile PR #33 post-merge recurrence | Review | PR #33 head `28c776abf6ab7832dbdf61ea49203c6e9c13a55c` merged as `68df196efdf38919d73a6b6345b973d2c3698b29` without retrospective lifecycle authority; repository-control investigation completed, `main-protected-governance` containment and sentinel PR #34 effectiveness evidence are available, while GOV-042 remains Review and Issue #23 state alignment remains pending |
| GOV-043 | Restore stable required-check producers | Done | PR #38 published as `a59521ad31d8153198bb80dd7985142cb21e3775`; stable `governance-validation`, `markdown-lint`, and `secret-scan` contexts restored before M5 |

Historical GOV-029 through GOV-042 items remain Review where their historical lifecycle discrepancies have not been substantively closed. Current prospective enforcement improvements must not rewrite those historical records.

## M5 — Engineering State, CI & Governance Stabilization

| ID | Task | Status | Evidence / next action |
| --- | --- | --- | --- |
| GOV-044 | M5.1 — Canonical State Reconciliation | Done | PR #66 published as `153a33a4a2b5edb4a31285eca7d3491f9589b778`; canonical mutable AI checkpoints live under `docs/ai/`; root duplicates are pointer stubs |
| GOV-045 | M5.2 — CI & Lifecycle Control Hardening | Done | PR #67 published as `512344d0497787c729242cb1fd2d7d02ecfc40c2`; A-03 and A-05 resolved; five required contexts active on protected `main` |
| GOV-046 | M5.3 — Governance & Program State Synchronization | Done | PR #68 source head `aa799e657070a7d3283110a73a411f54a73b972c` published as `e45f5b4c0f143abc6e255e4e8550bf3504348aae`; source/published tree `e2bc0505f5abd98a7283b3cd3cd2c4c02ef23ece`; A-06/A-07/A-08 reconciled |

## M6 — Enterprise Vision Canonicalization

| ID | Task | Status | Evidence / next action |
| --- | --- | --- | --- |
| GOV-047 | M6 Enterprise Vision canonicalization publication | Done | PR #69 source head `e6a3345b09a6b270ac7e09abd78c6356f426e363` published as `0b7b28028966ac38af0f32960054210c3a083916`; source/published tree `567df997bae70090b19465c75e4cc3b1e23b6579`; publication itself did not grant substantive approval; GOV-051 later approved the Enterprise Vision separately |
| GOV-048 | Normalize canonical product name to `oneQay` | Done | Current/future-facing canonical product identity normalized through PR #69; immutable identifiers and historical evidence preserved |
| GOV-049 | Synchronize Enterprise Capability Map and conceptual evolution representation | Done | Published through PR #69: Core Business Platform, Platform Capabilities, Extensibility, AI Platform, Channels; evolution E0–E5; no implementation authority implied |
| GOV-050 | Reconcile A-09 Enterprise Vision anomaly at representation/publication level | Done | PR #69 publication verified; A-09 resolved at canonical representation/publication level; GOV-051 later completed the separate substantive decision |
| GOV-051 | Enterprise Vision substantive Product Owner decision | Done | Product Owner explicitly APPROVED `Enterprise Intelligent Business Management Platform` on verified baseline `762149757e4bc1fa79cc16bc4761f4147be0f7ea` and canonical artifact blob `bb1cace72a6fdb359e15e22467443d9f3916c336`; approval is product direction only and grants no implementation authority |

M6 publication itself did not grant substantive Enterprise Vision approval. GOV-051 separately approved the Enterprise Vision as binding long-term product direction, but does not authorize Sprint 14, final/business/production application implementation, new business source code, database/schema implementation, SQL/migration execution, production database modification, deployment, release, ADR/GD promotion, JRN resolution, or production-readiness transition.

## Decisions required before final/business application implementation

Published bounded Platform Foundation source through Sprint 13 is an existing repository fact. The decisions below remain gates for broader final/business/production application implementation and must not be read as retroactively invalidating published Sprint 12 or Sprint 13 foundation work.

| ID | Decision | Status | Required output |
| --- | --- | --- | --- |
| DEC-000 | Product Owner, delegates, and decision rights | Done | Product Owner substantive decision APPROVED; Approved `docs/handbook/PRODUCT_VISION_AND_DECISION_RIGHTS.md`; `docs/handbook/DEC_000_DECISION_RECORD.md`; GD-003 Approved only within DEC-000 boundary |
| DEC-001 | MVP scope and non-scope | Done | Product Owner substantive decision APPROVED on baseline `17f156b9861972b4924a5ed01bfabd5a1a79461a`; `docs/handbook/DEC_001_DECISION_RECORD.md`; implementation authority NOT GRANTED |
| DEC-002 | Backend language/framework | Done | Product Owner substantive decision APPROVED on baseline `504b10be44d45dfcfec9b6cfed4f72ed5748b564`; PHP + Laravel; `docs/handbook/DEC_002_DECISION_RECORD.md`; ADR-001 Accepted after reconciliation; implementation/dependency authority NOT GRANTED |
| DEC-003 | Frontend/PWA stack | Done | Product Owner substantive decision APPROVED on baseline `dcb7e3f8de890530a00a0dd4fd310bc10762c72f`; Vue 3 + Inertia + Vite with TypeScript-first, explicit API/mobile boundaries, bounded PWA direction; `docs/handbook/DEC_003_DECISION_RECORD.md`; ADR-002 Accepted after reconciliation; implementation/dependency authority NOT GRANTED |
| DEC-004 | Android approach | Done | Product Owner substantive DEC-004 decision APPROVED on baseline `97b2e5066118af2b3e9467afc71e84dce228eb38`; Hybrid Staged Approach; Native Android with Kotlin + Jetpack Compose; `docs/handbook/DEC_004_DECISION_RECORD.md`; `docs/adr/ADR-008-android-delivery-approach.md`; implementation/dependency authority NOT GRANTED |
| DEC-005 | Database engine and physical tenancy model | Done | **APPROVED HISTORICAL DECISION / PARTIALLY SUPERSEDED BY DEC-005R**; original Product Owner decision on baseline `63646e1cccc611a1911c452397059983030dfe66` selected MySQL Server and shared database/shared schema; historical provenance remains preserved; current engine/portability precedence is DEC-005R; no database/schema/SQL/migration/implementation authority |
| DEC-005R | Portable Relational Persistence Architecture | Done | Product Owner substantive decision **APPROVED — OPTION C**; published through PR #100 from source head `8ec7069b08c9127e402fa80e5e79ca26be2b63d6`, source/published tree `0862c851d30c11c37c39d13aa5660d042da91989`, squash commit `b5cbdeb6ea45d4f159f3d1cd39cadc561605c5ff`; database-neutral Domain/Application, qualified MariaDB/MySQL/PostgreSQL profile direction, Database Portability Contract and DBME/cross-engine qualification directions; no source/schema/SQL/migration/DBME/M7.5/deployment authority |
| DEC-006 | Authentication/MFA/session architecture | Done | Product Owner substantive DEC-006 APPROVED on baseline `c495f1b1f6e4e624bf3669ee94a786a1d7e865ce`; first-party oneQay identity; Web/PWA server-side session; explicit Android/API token boundary; TOTP privileged MFA baseline; WebAuthn/passkey evolution; global identity + tenant memberships; reconciled ADR-004; no implementation/package/schema/migration authority |
| DEC-007 | Payment provider and compliance boundary | Done | Product Owner substantive DEC-007 APPROVED on baseline `50955d101c455c6af7356197d9e06d6d76e753bb`; cash-first + configurable manual/external recorded tenders; operator-recorded versus provider-verified evidence separation; provider-abstracted future electronic architecture; provider selection deferred; sale-level payment sufficiency; idempotency/refund/settlement/PCI/jurisdiction boundaries; `docs/handbook/DEC_007_DECISION_RECORD.md`; materially reconciled ADR-005; no payment/provider/schema/SQL/implementation authority |
| DEC-008 | Offline POS semantics and conflict resolution | Done | Product Owner substantive DEC-008 APPROVED on baseline `21f1b2e938d9f6a42b1dd2ff717c4e049b5119d7`; Staged / Hybrid Offline Architecture; first-MVP online-authoritative transactions; future provisional server-validated offline operations; bounded replay/idempotency/conflict/security/reconciliation semantics; `docs/handbook/DEC_008_DECISION_RECORD.md`; materially reconciled ADR-006; no offline/source/schema/package/implementation authority |
| DEC-009 | Deployment stage 1 runtime requirements | Done | Product Owner substantive DEC-009 APPROVED on baseline `0fdc0a53403f16fbc6908630ea350af2c0de466b`; Capability-Based Staged / Hybrid Portability Model; P1 cPanel conditional/not selected; P2 managed/hardened VPS/server fallback class; current database dependency requires an authorized and runtime-qualified relational engine profile under DEC-005R; Stage-1 Preview environment; `docs/handbook/DEC_009_DECISION_RECORD.md`; materially reconciled ADR-007; no deployment/implementation authority |
| DEC-010 | Product license and third-party notice policy | Done | Product Owner substantive DEC-010 APPROVED on baseline `5cc572675dd7871a3ca841cedf06fbc8ea74f839`; Proprietary / All Rights Reserved product policy; repository visibility/rights separation; external contributions legally gated; dependency-license pre-adoption matrix; NOTICE/SBOM/trademark/plugin/AI/asset boundaries; `docs/handbook/DEC_010_DECISION_RECORD.md`; final legal text remains Legal Review Required; no dependency adoption/implementation/distribution/deployment authority |
| DEC-011 | Data retention, privacy, and jurisdiction | Done | Product Owner substantive DEC-011 APPROVED on baseline `6c6af7f99d25f177c91f92cdd163a277affc5153`; Bounded Privacy-by-Design + Hybrid Bounded Retention + Jurisdiction-Profile Architecture; initial jurisdiction NOT YET CANONICALLY SELECTED; qualified legal review required for jurisdiction-specific implementation; `docs/handbook/DEC_011_DECISION_RECORD.md`; no implementation/schema/provider/jurisdiction/deployment authority |
| DEC-012 | RPO/RTO and support objectives | Done | Product Owner substantive DEC-012 APPROVED on baseline `a7821517a03cf868adf56bfa7d91c878d8c364ac`; Capability-Tiered / Evidence-Based Recovery & Support Policy; final numerical Production RPO/RTO and customer SLA deferred; recovery claims evidence-gated; `docs/handbook/DEC_012_DECISION_RECORD.md`; no backup/DR/infrastructure/implementation/deployment/release/Production authority |

DEC-000 through DEC-012 and DEC-005R completion do not authorize final/business/production implementation. DEC-005R changes current relational architecture governance only; DEC-012 approves only bounded recovery/support policy. Neither decision promotes REC-1/SLO-1 Technical Preview values into Production commitments, establishes numerical Production RPO/RTO or customer SLA, selects provider/cloud/region/HA technology, resolves GD-007/JRN-003/JRN-013, exits Phase 0, starts Sprint 14, or grants implementation, deployment, release, or Production authority.

## Phase 0 discovery backlog

| ID | Task | Priority | Dependency |
| --- | --- | --- | --- |
| DSC-000 | Product vision and decision-rights workshop | P0 | Handbook approval; Issue #2 |
| DSC-001 | Stakeholder and actor map | P0 | Handbook approval; Issue #4 |
| DSC-002 | POS/ERP domain event storming | P0 | Stakeholder availability; Issue #8; correction Issue #10/#12; governance Issue #14/#16/#18/#20 |
| DSC-003 | Current process, user journeys, and service blueprint | P0 | DSC-001; Issue #6 |
| DSC-004 | Data inventory and classification | P0 | DSC-002 |
| DSC-005 | Threat model critical flows | P0 | DSC-002/004 |
| DSC-006 | MVP success metrics and SLO proposal | P0 | DEC-001 |
| DSC-007 | Shared-hosting capability assessment | P0 | Hosting facts |
| DSC-008 | Vendor and dependency evaluation rubric | P1 | Security/licensing policy; DEC-010 |

## Phase 1 candidate backlog

Items ini tidak memperoleh source-code authority baru dari M6, GOV-051, DEC-000, DEC-001, DEC-002, DEC-003, DEC-004, DEC-005, DEC-005R, DEC-006, DEC-007, DEC-008, DEC-009, DEC-010, DEC-011, atau DEC-012. Published bounded Platform Foundation through Sprint 13 must be preserved, tetapi pekerjaan baru untuk final/business application atau Sprint 14 tetap membutuhkan Product Owner authority dan gate yang berlaku.

- PLT-001 repository/application skeleton;
- PLT-002 tenant context and isolation enforcement;
- PLT-003 identity/MFA/authorization;
- PLT-004 organization/outlet/device;
- PLT-005 audit/correlation/error tracking;
- PLT-006 migration/seeder foundation;
- PLT-007 configuration and secret boundary;
- PLT-008 installer baseline;
- PLT-009 CI quality/security gates;
- PLT-010 backup/restore rehearsal.

## Task maintenance rules

- Setiap task memiliki owner sebelum In Progress.
- Blocked task mencantumkan blocker dan next action.
- Done membutuhkan evidence, bukan hanya implementasi.
- Scope baru tidak disisipkan diam-diam; buat task/issue baru.
- Perubahan status capability/decision memperbarui PROJECT_MANIFEST dan CHANGELOG.

## Phase 0 Accelerated Technical Preview

| ID | Task | Status | Dependency/evidence |
| --- | --- | --- | --- |
| P0-TP-001 | Record B1/F1/D1/A1 and PAY-1/OFF-1/TEN-1/REC-1/SLO-1/DATA-1 | Review | Issue #23; backend B1 later approved through DEC-002/ADR-001; frontend F1 later approved through DEC-003/ADR-002; database D1 provenance was later reconciled through DEC-005/ADR-003 and its current relational-engine precedence is now governed by DEC-005R while preserving that history; authentication A1 provenance later reconciled through DEC-006/ADR-004; PAY-1 provenance later superseded as current bounded payment direction by substantive DEC-007/reconciled ADR-005; OFF-1 provenance later reconciled through substantive DEC-008/reconciled ADR-006; remaining exact-head approvals pending |
| P0-TP-002 | Complete P1 shared-hosting capability assessment | Blocked | Partial Sprint 07/08 evidence reconciled through DEC-009; P1 remains conditional/not selected; selected relational engine-profile runtime qualification under DEC-005R, safe document root, rewrite, cron cadence, worker/process, deployment/rollback, restore, app DB limits/security, and complete resource/quota/outbound evidence remain blockers |
| P0-TP-003 | Review ADR-001 through ADR-007 | Review | ADR-001 Accepted via DEC-002; ADR-002 Accepted via DEC-003; ADR-003 Accepted historically via DEC-005 and current representation reconciled to DEC-005R while preserving D1/DEC-005 provenance; ADR-004 Accepted via DEC-006; ADR-005 Accepted as DEC-007 representation after governed publication; ADR-006 Accepted as DEC-008 representation after governed publication; ADR-007 Accepted as DEC-009 representation with its current database dependency reconciled to DEC-005R |
| P0-TP-004 | Review data inventory/classification baseline | Review | Product Owner and security exact-head review |
| P0-TP-005 | Review Technical Preview threat model | Review | Critical/High threats require mapped verification |
| P0-TP-006 | Review REC-1 recovery plan | Review | Target-environment capability and rehearsal pending |
| P0-TP-007 | Approve Phase 0 preview exit | Blocked | P0-TP-002 through P0-TP-006 and explicit exact-head decision; remains separately gated and is not a prerequisite for separately authorized bounded Local/Test/CI source preparation |
| P0-TP-008 | Authorize application skeleton | Done | M7.1 source authority was separately granted and the governed Application Skeleton & Configuration Boundary was published through PR #92; this historical task completion does not grant further source authority |
| P0-TP-009 | Execute T+5 Technical Preview | Blocked | Requires applicable source, security, runtime, recovery, and deployment gates; P2 qualification remains mandatory before Preview deployment |

PR #24 through PR #33 technical merges and Issue #23 closure do not themselves set any task above to Done, accept an ADR, approve Phase 0 exit, grant general application source-code authority, ratify prior lifecycle actions, complete GOV-034 through GOV-042, or provide substantive approval or completion evidence. Later independent Product Owner decisions separately Accept ADR-001 via DEC-002, ADR-002 via DEC-003, ADR-003 historically via DEC-005 with current representation reconciled to DEC-005R, ADR-004 via DEC-006, ADR-005 via DEC-007, ADR-006 via DEC-008, and ADR-007 via DEC-009 with its database dependency later reconciled to DEC-005R. DEC-012 recovery/support policy does not promote REC-1 or SLO-1 Technical Preview values or create successful rehearsal evidence. Phase 0 remains In Progress; final/business/production application implementation remains Blocked; Phase 0 preview exit remains Not Approved; bounded Local/Test/CI source preparation may occur only under separate Product Owner source authority; P1 remains conditional and Not Selected; P2 actual target remains pending external input unless fresh evidence proves otherwise; GD-007 remains Proposed; JRN-003 and JRN-013 remain unresolved; TEN-1, REC-1, SLO-1, and DATA-1 remain Proposed.

Issue #23 remains an open source of historical pre-M7.0 planning language. That historical wording must not override the later governed Phase 0 Controlled Implementation Bridge for bounded Local/Test/CI source preparation. Issue #23 mutation is outside this reconciliation authority.

## M7 — Technical Preview Implementation Enablement

M7 is the current bounded Technical Preview engineering workstream created by the Product Owner-approved Phase 0 Controlled Implementation Bridge. These labels sequence work; they do not independently grant source, deployment, release, or Production authority and do not authorize Sprint 14.

| ID | Task | Status | Dependency/evidence |
| --- | --- | --- | --- |
| M7.0 | Controlled Implementation Bridge | Done | Product Owner substantive bridge decision and governed publication completed; historical bridge authority does not grant standing future source authority |
| M7.1 | Application Skeleton & Configuration Boundary | Done | Governed publication completed through PR #92; no standing successor authority |
| M7.2 | Tenant Kernel & Isolation Foundation | Done | Governed publication completed through PR #93; M7.1 foundation preserved |
| M7.3 | Identity / Organization / Outlet / Device Minimum | Done | Governed publication completed through PR #94; M7.2 tenant isolation and server-controlled identity/organizational boundaries preserved |
| M7.4 | POS Core Synthetic Vertical Slice | Done | Governed publication completed through PR #96; bounded synthetic Local/Test/CI POS transaction evidence published; no standing M7.5, deployment, release, or Production authority |
| M7.4A | Technical Preview Interaction Layer | Done | Governed publication completed through PR #98; source head `893b73b8f20b2ede0d3a8896b3a015df5370dbed`, source/published tree `cdc140e5061481bec4b6b691b02b2b234181c2fb`, published commit `c0bdf8ad7539a5c83de2e5183fbf2eda9f17f02b`; synthetic-only interaction journey reuses M7.4 `CompleteSyntheticSale`; no standing M7.5, deployment, release, or Production authority |
| M7.5 | Preview Runtime Qualification | Blocked | NOT AUTHORIZED; actual sanitized P2 target evidence, DEC-009 capability verification, and selected relational engine-profile qualification under DEC-005R required before separate Product Owner M7.5 authority |
| M7.6 | Preview Deployment / Recovery Rehearsal | Blocked | NOT AUTHORIZED; qualified target, applicable source/security evidence, and separate deployment authority required |
| M7.7 | Technical Preview Acceptance | Blocked | NOT AUTHORIZED; combined source, security, runtime, recovery, and operational evidence required |

Track A Local/Test/CI engineering has published M7.4 and M7.4A. Track B P2 runtime qualification remains blocked until actual sanitized P2 target evidence is available, verified under DEC-009, the selected relational engine profile is qualified under DEC-005R, and separate Product Owner authority is granted. Both tracks converge before Technical Preview deployment/acceptance.

## DEC-010 Supplement publication state

- Status: **Done** as the intended successfully published state for the approved substantive supplement.
- Decision record: `docs/handbook/DEC_010_SUPPLEMENT_DECISION_RECORD.md`.
- D10S-01: **ZERO MANDATORY COMMERCIAL SOFTWARE-LICENSE COST — CORE BASELINE**.
- D10S-02: **FREE / OPEN-SOURCE FIRST PREFERENCE — NOT FOSS-ONLY**.
- D10S-03: **APACHE ECHARTS — DEFAULT WEB/PWA VISUALIZATION TECHNOLOGY CANDIDATE / APPROVED TECHNOLOGY DIRECTION**.
- D10S-04 preserves **Technology Policy Approval != Dependency Adoption Authority != Implementation Authority**.
- No package/version, package manager, frontend lockfile, ECharts/Vue wrapper, source implementation, deployment, release, Production, Phase 0 exit, or Sprint 14 authority is created.
