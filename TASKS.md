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
| GOV-002 | Project manifest | Review | Draft siap dipublikasikan dan direview |
| GOV-003 | AI constitution | Review | Draft siap dipublikasikan dan direview |
| GOV-004 | Architecture baseline | Review | Draft siap dipublikasikan dan direview |
| GOV-005 | Product/engineering roadmap | Review | Draft siap dipublikasikan dan direview |
| GOV-006 | Coding standards | Review | Draft siap dipublikasikan dan direview |
| GOV-007 | Database handbook | Review | Draft siap dipublikasikan dan direview |
| GOV-008 | API governance | Review | Draft siap dipublikasikan dan direview |
| GOV-009 | Security handbook | Review | Draft siap dipublikasikan dan direview |
| GOV-010 | Deployment handbook | Review | Draft siap dipublikasikan dan direview |
| GOV-011 | Testing strategy | Review | Draft siap dipublikasikan dan direview |
| GOV-012 | UI/UX guideline | Review | Draft siap dipublikasikan dan direview |
| GOV-013 | Installer specification | Review | Draft siap dipublikasikan dan direview |
| GOV-014 | Updater specification | Review | Draft siap dipublikasikan dan direview |
| GOV-015 | Contribution workflow | Review | Draft siap dipublikasikan dan direview |
| GOV-016 | Release management | Review | Draft siap dipublikasikan dan direview |
| GOV-017 | Task governance | Review | Draft siap dipublikasikan dan direview |
| GOV-018 | Changelog baseline | Review | Draft siap dipublikasikan dan direview |
| GOV-019 | Markdown/link/security consistency validation | In Progress | Jalankan local quality checks |
| GOV-020 | Publish handbook branch and draft PR | Blocked | GitHub App membutuhkan Contents: Read and write |
| GOV-021 | Product Owner handbook review | Blocked | Menunggu GOV-020 |
| GOV-022 | Phase 0 governance and discovery kickoff pack | Review | `docs/handbook/PHASE_0_KICKOFF.md` |
| GOV-023 | Standardize engineering collaboration to ChatGPT + GitHub only | Review | Remove external and third-party AI workflow references |

## Decisions required before source code

| ID | Decision | Status | Required output |
|---|---|---|---|
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
| DSC-001 | Stakeholder and actor map | P0 | Handbook approval |
| DSC-002 | POS/ERP domain event storming | P0 | Stakeholder availability |
| DSC-003 | User journeys and service blueprint | P0 | DSC-001 |
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
