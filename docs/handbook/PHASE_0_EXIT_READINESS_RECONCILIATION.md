# Phase 0 Exit Readiness Reconciliation

- Status: Readiness Preparation / Not an Exit Decision
- Product: oneQay
- Repository: `labzefry/oneQay`
- Tracking: Issue #23
- Preparation baseline: `3de644aaadf4d4b0c048cf7eb30fe55babd8f7ed`
- Preparation baseline tree: `54b1f810ee34a508a4b0ac70dc526383e9f273d1`
- Authority: bounded readiness preparation only
- Phase 0 exit: Not Authorized
- Sprint 14: Not Authorized
- Source/application implementation: Not Authorized
- Deployment / Release / Production: Not Authorized

GitHub is the Single Source of Truth. The baseline above is preparation provenance, not a permanently current live-state declaration. Fresh Minimal Delta Verification remains mandatory before lifecycle mutation or substantive Product Owner decision.

## Purpose

This reconciliation separates decision work that has already been completed through later governed DEC/ADR lifecycles from Technical Preview evidence that remains genuinely unresolved.

It prevents Phase 0 exit review from re-opening completed technology decisions while also preventing historical Issue #23 planning selections from being misrepresented as current approved implementation authority.

## Already governed and no longer open as technology-selection questions

The following current decision directions are already separately governed:

- DEC-002 / ADR-001: backend PHP + Laravel direction — Approved / Accepted representation;
- DEC-003 / ADR-002: Vue 3 + Inertia + Vite with TypeScript-first direction — Approved / Accepted representation;
- DEC-005 / ADR-003: MySQL Server with shared database/shared schema default — Approved / Accepted representation;
- DEC-006 / ADR-004: first-party identity/session and privileged TOTP baseline — Approved / Accepted representation;
- DEC-007 / ADR-005: payment evidence/compliance boundary — Approved / Accepted representation;
- DEC-008 / ADR-006: offline semantics/conflict boundary — Approved / Accepted representation;
- DEC-009 / ADR-007: capability-based Stage-1 Preview runtime requirements — Approved / Accepted representation.

These decisions do not grant implementation, package adoption, schema/SQL/migration, infrastructure, deployment, release, or Production authority.

## P0-TP readiness disposition

| Task | Current disposition | Remaining requirement |
| --- | --- | --- |
| P0-TP-001 | Partially reconciled | Historical B1/F1/D1/A1/PAY-1/OFF-1 are superseded/refined by governed DEC decisions; TEN-1/REC-1/SLO-1/DATA-1 remain preview provenance/candidates where applicable |
| P0-TP-002 | Blocked | Select a compliant target Preview environment through DEC-009 evidence; P1 currently Conditional / Not Selected |
| P0-TP-003 | Substantively satisfied by later governed decisions | ADR-001 through ADR-007 have Accepted current representations; no re-acceptance should be required |
| P0-TP-004 | Review required | Preview-specific data inventory/retention/generator/isolation package needs current exact-state review; DEC-011 policy foundation is already Approved |
| P0-TP-005 | Review required | Threat model needs current security review and Critical/High verification mapping |
| P0-TP-006 | Blocked for verified-recovery claim | DEC-012 recovery policy is Approved, but restore/rollback rehearsal and measured achieved RPO/RTO are not yet evidence |
| P0-TP-007 | Blocked | Requires completion/disposition of P0-TP-002 through P0-TP-006 plus separate exact-state Product Owner exit decision |
| P0-TP-008 | Blocked | Requires separate source-code authority after P0-TP-007 |
| P0-TP-009 | Blocked | Requires source-code authority and Day-1 quality/security gates |

## Hosting/runtime evidence

DEC-009 is authoritative for Stage-1 Preview runtime requirements.

Current P1 Shared Hosting / cPanel disposition remains:

**CONDITIONAL / NOT SELECTED**.

Material unresolved evidence includes:

- canonical MySQL Server connectivity and operational boundary;
- public-only document root and rewrite/front-controller behavior;
- scheduler cadence;
- worker/background execution model where required;
- secrets/private storage isolation;
- backup retention/coverage plus successful restore rehearsal;
- versioned/recoverable deployment and rollback;
- application correlation/log evidence;
- full resource/quota visibility;
- outbound DNS/HTTPS capability.

MariaDB 11.4.8 remains factual cPanel evidence but is non-compliant with the canonical DEC-005 MySQL Server requirement and must not be treated as an implicit substitute.

P2 managed/hardened VPS/server remains the fallback execution class and is not selected by this document.

## Data/privacy evidence

DEC-011 has already Approved:

- auditable data inventory/classification direction;
- Public / Internal / Confidential / Restricted classification framework;
- Synthetic-by-Default handling for Preview;
- tenant privacy/isolation direction;
- privacy-aware backup/recovery semantics;
- bounded retention governance.

The preview-specific `TECHNICAL_PREVIEW_DATA_BASELINE.md` remains a candidate requiring current exact-state review because its object inventory, preview retention values, generator expectations, and isolation acceptance plan are Preview-specific implementation-readiness evidence rather than universal policy.

## Threat-model evidence

`TECHNICAL_PREVIEW_THREAT_MODEL.md` remains a Proposed readiness artifact. Its Critical/High threats must be mapped to verification or explicit blockers before skeleton authority.

Readiness review must specifically preserve fail-closed treatment for:

- cross-tenant access;
- broken authorization;
- secret exposure;
- session/MFA abuse;
- sale/stock idempotency and integrity;
- backup disclosure/restore failure;
- deployment without recoverable rollback;
- supply-chain/dependency risk;
- offline replay/stale mutation where relevant.

Documented controls are not equivalent to implemented controls.

## Recovery evidence

DEC-012 has already Approved an evidence-gated recovery policy. It explicitly preserves historical Technical Preview values only as provenance:

- REC-1 RPO 24h — proposal only;
- REC-1 RTO 4h — proposal only;
- SLO-1 — Technical Preview provenance only.

A verified recovery claim requires successful measured evidence, including source release, backup freshness, checksum/integrity, isolated restore, compatibility, restored-data integrity, tenant isolation, critical business invariants, health, applicable reconciliation, privacy-aware recovery, achieved RPO/RTO, operator/date/result, and remediation/re-test where applicable.

No unperformed rehearsal may be represented as recovery success.

## Recommended next decision package

Before any Phase 0 Technical Preview exit decision, prepare one exact-state package containing:

1. final bounded Technical Preview scope/non-scope and measurable success criteria;
2. target Preview environment disposition under DEC-009, with evidence or an explicit `NOT READY` blocker;
3. preview-specific data inventory/classification/retention package aligned to DEC-011;
4. threat-model review with Critical/High verification mapping;
5. recovery/rollback rehearsal plan aligned to DEC-012, clearly separating planned controls from executed evidence;
6. tenant-isolation acceptance plan for two deterministic synthetic tenants;
7. known limitations and unresolved JRN-003/JRN-013 visibility;
8. explicit statement that package approval is not source implementation, deployment, release, or Production authority.

## Exit decision options

After exact-head independent review, the Product Owner should have only bounded options:

- **APPROVE BOUNDED PHASE 0 TECHNICAL PREVIEW EXIT FOR APPLICATION-SKELETON PREPARATION ONLY**, if all mandatory pre-skeleton evidence is satisfied; or
- **NOT READY / CORRECTIVE ACTION REQUIRED**, if any mandatory blocker remains.

No approval should silently imply Sprint 14, final/business application implementation, dependency installation, database/schema/SQL/migration execution, infrastructure provisioning, deployment, release, or Production.

## Stop condition for this preparation PR

This readiness-reconciliation PR must remain Draft until:

- its exact final head/tree are verified;
- required checks pass;
- independent review is recorded on the exact head;
- separate Product Owner READY authorization is granted.

Merge remains separately gated by exact-head Product Owner MERGE authorization.

Publication of this document still does not itself approve Phase 0 exit.

Attribution: Lab | zefry
