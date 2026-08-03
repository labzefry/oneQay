# OneQay Tasks

## Status legend

| Status | Meaning |
|---|---|
| Backlog | Belum diprioritaskan |
| Ready | Scope dan acceptance criteria siap |
| In Progress | Sedang dikerjakan |
| Blocked | Menunggu dependency/decision/authority |
| Review | Menunggu review/approval |
| Done | Evidence dan Definition of Done lengkap |

## Handbook 1.0

| ID | Task | Status | Evidence / next action |
|---|---|---|---|
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
| GOV-029 | Correct approved PR #11 Domain Event Storming audit findings | Review | Issue #12 dibuka kembali; empat koreksi pada head PR #13 `e4a3b7b` diratifikasi setelah merge; closure diblokir Issue #16/#18 |
| GOV-030 | Reconcile PR #13 merge-before-approval | Review | Issue #14 dibuka kembali; recurrence berlanjut pada PR #15/#17; completion diblokir Issue #16/#18 |
| GOV-031 | Harden exact-head approval and issue closure controls | Review | Issue #16 dibuka kembali; PR #17 merged sebelum approval; effectiveness dan protection evidence diblokir Issue #18 |
| GOV-032 | Reconcile PR #17 recurrence and enforce protection gate | Review | Issue #18; audit pasca-merge, direct protection evidence/formal risk acceptance, dan separate Product Owner decision masih pending |

## Decisions required before source code

| ID | Decision | Status | Required output |
|---|---|---|---|
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
|---|---|---|---|
| DSC-000 | Product vision and decision-rights workshop | P0 | Handbook approval; Issue #2 |
| DSC-001 | Stakeholder and actor map | P0 | Handbook approval; Issue #4 |
| DSC-002 | POS/ERP domain event storming | P0 | Stakeholder availability; Issue #8; correction Issue #10/#12; governance Issue #14/#16/#18 |
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
