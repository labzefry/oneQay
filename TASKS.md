# OneQay Tasks

## Status legend

| Status | Meaning |
| --- | --- |
| Backlog | Belum diprioritaskan |
| Ready | Scope dan acceptance criteria siap |
| In Progress | Sedang dikerjakan |
| Blocked | Menunggu dependency/decision/authority |
| Review | Menunggu review/approval |
| Done | Evidence dan Definition of Done lengkap |

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
| GOV-024 | Product vision and decision rights | In Progress | Issue #2; draft document prepared for Product Owner review |
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

## Decisions required before source code

| ID | Decision | Status | Required output |
| --- | --- | --- | --- |
| DEC-000 | Product Owner, delegates, and decision rights | In Progress | Approved Product Vision and Decision Rights document |
| DEC-001 | MVP scope and non-scope | Ready | Approved product brief |
| DEC-002 | Backend language/framework | Ready | ADR-001 |
| DEC-003 | Frontend/PWA stack | Ready | ADR-002 |
| DEC-004 | Android approach | Backlog | ADR |
| DEC-005 | Database engine and physical tenancy model | Ready | ADR-003 |
| DEC-006 | Authentication/MFA/session architecture | Ready | ADR-004 |
| DEC-007 | Payment provider and compliance boundary | Ready | ADR-005 |
| DEC-008 | Offline POS semantics and conflict resolution | Ready | ADR-006 |
| DEC-009 | Deployment stage 1 runtime requirements | Ready | ADR-007 |
| DEC-010 | Product license and third-party notice policy | Ready | Legal decision |
| DEC-011 | Data retention, privacy, and jurisdiction | Ready | Policy/ADR |
| DEC-012 | RPO/RTO and support objectives | Backlog | Operational policy |

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
| DSC-008 | Vendor and dependency evaluation rubric | P1 | Security/licensing policy |

## Phase 1 candidate backlog

Items ini belum boleh diimplementasikan sebelum entry criteria ROADMAP Phase 0 terpenuhi:

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
| P0-TP-001 | Record B1/F1/D1/A1 and PAY-1/OFF-1/TEN-1/REC-1/SLO-1/DATA-1 | Review | Issue #23; ADR exact-head approval pending |
| P0-TP-002 | Complete P1 shared-hosting capability assessment | Blocked | PHP, DB, SSH/Git, cron, worker, HTTPS, backup/restore, rollback, and quota evidence missing |
| P0-TP-003 | Review ADR-001 through ADR-007 | Review | All ADRs remain Proposed |
| P0-TP-004 | Review data inventory/classification baseline | Review | Product Owner and security exact-head review |
| P0-TP-005 | Review Technical Preview threat model | Review | Critical/High threats require mapped verification |
| P0-TP-006 | Review REC-1 recovery plan | Review | Target-environment capability and rehearsal pending |
| P0-TP-007 | Approve Phase 0 preview exit | Blocked | P0-TP-002 through P0-TP-006 and explicit exact-head decision |
| P0-TP-008 | Authorize application skeleton | Blocked | Separate source-code authority after P0-TP-007 |
| P0-TP-009 | Execute T+5 Technical Preview | Blocked | Source-code authority and Day 1 gates |

PR #24 through PR #33 technical merges and Issue #23 closure do not set any task above to Done, accept an ADR, approve Phase 0 exit, grant source-code authority, ratify prior lifecycle actions, complete GOV-034 through GOV-042, or provide substantive approval or completion evidence. Phase 0 remains In Progress; application implementation remains Blocked; Phase 0 preview exit remains Not Ready; P1 remains conditional and Unverified; ADR-001 through ADR-007 and GD-007 remain Proposed; JRN-003 and JRN-013 remain unresolved; hosting evidence remains Pending, Not supplied, or Unverified.
