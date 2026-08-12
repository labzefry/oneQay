# Phase 0 Controlled Implementation Bridge

- Product: oneQay
- Tagline: The Future of Intelligent Business Management
- Developer & Product Engineering Entity: Lab | zefry
- Repository: `labzefry/oneQay`
- GitHub: Single Source of Truth
- Decision: PHASE 0 CONTROLLED IMPLEMENTATION BRIDGE
- Product Owner substantive decision: APPROVED
- Publication status: Candidate / Draft PR lifecycle pending
- Exact publication preparation base: `e86aaa83f4f29ddb11ba8437b072fbd0e5569ffd`
- Exact publication preparation base tree: `15bab71ebddf3066865ed2fac9325ed35dfcaaf3`
- Phase 0: IN PROGRESS
- Phase 0 Exit: NOT APPROVED
- Sprint 14: NOT AUTHORIZED
- Deployment: NOT AUTHORIZED
- Release: NOT AUTHORIZED
- Production: NOT AUTHORIZED
- Production readiness: NO-GO
- Source implementation authority from this record: NOT GRANTED

## 1. Purpose

This record canonically publishes the Product Owner-approved **PHASE 0 CONTROLLED IMPLEMENTATION BRIDGE**.

The bridge removes a circular implementation gate in which application/runtime evidence that can only exist after source implementation was being treated as a prerequisite for authorizing any bounded source implementation at all.

The bridge does not weaken security, tenant isolation, recovery, runtime, deployment, governance, or Production requirements. It separates two readiness questions that must be evaluated at the correct lifecycle stage:

1. Local/Test/CI source implementation readiness; and
2. Preview runtime/deployment readiness.

## 2. Governing interpretation

The previous fully serial interpretation:

`P2 TARGET VERIFIED -> ALL IMPLEMENTATION SECURITY EVIDENCE -> PHASE 0 EXIT -> SOURCE CODE MAY BEGIN`

is replaced for current and future Technical Preview work by the following bounded model.

### Track A — Controlled Application Engineering

`M7.0 Controlled Implementation Bridge -> separate Product Owner source-code authority -> Local/Test/CI application skeleton -> configuration/secret boundary -> tenant-context kernel -> health/readiness -> synthetic tenants -> source-specific security tests -> later bounded identity/business vertical-slice authority`

### Track B — Preview Runtime Qualification

`P2 environment class -> actual P2 target identification -> DEC-009 capability verification -> Preview runtime qualification -> deployment/recovery capability -> restore/rollback rehearsal`

The tracks may progress in parallel where their dependencies are independent.

They must converge before Technical Preview deployment and acceptance.

## 3. BRIDGE-01 — Source readiness versus deployment readiness

**APPROVED.**

Actual P2 target verification is not a prerequisite for bounded Local/Test/CI source preparation.

Actual P2 target verification remains mandatory before:

- Preview deployment;
- runtime acceptance;
- infrastructure-dependent acceptance;
- deployment rehearsal on the selected target;
- rollback rehearsal on the selected target;
- restore rehearsal on the selected target;
- staging/Preview URL acceptance;
- Technical Preview operational acceptance.

DEC-009 remains fully binding for any actual Preview target.

If no actual P2 target evidence has been supplied, the canonical runtime-track disposition is:

`P2 ACTUAL TARGET: PENDING EXTERNAL INPUT`

No infrastructure capability may be invented or inferred from the environment-class label alone.

## 4. BRIDGE-02 — Security evidence dependency correction

**APPROVED.**

Security requirements are separated into pre-implementation design gates and post-implementation verification evidence.

### 4.1 Pre-implementation security design gates

Before the relevant bounded source work is authorized, the design boundary must identify at least:

- Critical/High threat mapping;
- tenant-isolation model;
- deny-by-default authorization direction;
- secret/configuration boundary;
- Synthetic-by-Default Preview data boundary;
- payment/offline boundaries;
- recovery requirements;
- fail-closed expectations;
- explicit tests that future implementation must satisfy.

### 4.2 Post-implementation verification evidence

Evidence that logically requires implemented source is generated after the relevant implementation exists, including:

- actual cross-tenant negative tests;
- actual authorization tests;
- session/MFA tests;
- transaction idempotency tests;
- money integrity tests;
- source-specific secret/configuration tests;
- migration/seeder tests;
- application restore evidence;
- application rollback evidence;
- application health evidence.

Post-implementation evidence must not be required to exist before the implementation needed to generate it has been separately authorized.

The obligation to pass that evidence before its applicable later acceptance/deployment gate remains unchanged.

## 5. BRIDGE-03 — Phase 0 state

Phase 0 remains **IN PROGRESS**.

Phase 0 Exit remains **NOT APPROVED**.

The bridge does not imply:

- Production readiness;
- customer pilot readiness;
- Phase 1 completion;
- Phase 2 commencement;
- broad final/business application authority;
- Sprint 14 authorization.

Published bounded Platform Foundation source through Sprint 13 remains repository history and demonstrates that Phase 0 program state and separately authorized bounded technical source work are not inherently mutually exclusive.

## 6. BRIDGE-04 — M7 Technical Preview Implementation Enablement

The next implementation-enablement workstream is defined as:

**M7 — TECHNICAL PREVIEW IMPLEMENTATION ENABLEMENT**

M7 is a bounded Technical Preview engineering workstream. It is not automatically Sprint 14 and does not independently grant source, deployment, release, or Production authority.

The governed sequence is:

| Micro-milestone | Purpose | Authority implication |
| --- | --- | --- |
| M7.0 | Controlled Implementation Bridge | Publishes the gate clarification only |
| M7.1 | Application Skeleton & Configuration Boundary | Requires separate Product Owner source-code authority |
| M7.2 | Tenant Kernel & Isolation Foundation | Requires bounded authority and applicable M7.1 evidence |
| M7.3 | Identity / Organization / Outlet / Device Minimum | Requires bounded authority and security design gates |
| M7.4 | POS Core Synthetic Vertical Slice | Requires bounded authority and transaction-security gates |
| M7.5 | Preview Runtime Qualification | Requires actual target evidence and DEC-009 assessment |
| M7.6 | Preview Deployment / Recovery Rehearsal | Requires qualified target plus separate deployment authority |
| M7.7 | Technical Preview Acceptance | Requires combined source, security, runtime, recovery, and operational evidence |

## 7. BRIDGE-05 — Future M7.1 source scope

After this bridge is canonically published and separate Product Owner source-code authority is granted, the first intended source slice is:

**M7.1 — APPLICATION SKELETON & CONFIGURATION BOUNDARY**.

Candidate bounded scope:

1. establish the canonical Laravel application skeleton consistent with DEC-002;
2. establish Vue 3 + Inertia + Vite + TypeScript-first frontend skeleton consistent with DEC-003;
3. preserve Clean Architecture / Modular Monolith boundaries;
4. establish practical Domain / Application / Infrastructure / Delivery separation without unnecessary overengineering;
5. establish environment/configuration boundary;
6. establish `.env.example` or equivalent template with no real secrets;
7. fail closed for missing critical configuration where applicable;
8. establish basic application bootstrap;
9. establish health/readiness foundation;
10. establish stable correlation/error-envelope foundation;
11. preserve tenant-context integration points without prematurely implementing complete business modules;
12. establish Local/Test/CI execution baseline;
13. establish dependency lockfiles only after exact dependency-adoption review;
14. maintain applicable CI/security checks;
15. preserve attribution: Lab | zefry.

M7.1 must not, merely by beginning, implement or authorize:

- full POS;
- a real payment provider;
- Production data;
- Production deployment;
- full offline transactions;
- Android;
- marketplace;
- AI platform;
- complete ERP;
- real customer data;
- Production secrets.

## 8. BRIDGE-06 — Dependency adoption

Technology decisions do not automatically install dependencies.

Before actual dependency adoption during M7.1, exact intended versions and licensing/security compatibility must be freshly verified.

Current governed technology directions remain:

- PHP + Laravel according to DEC-002;
- Vue 3, Composition API, TypeScript-first, Inertia, and Vite according to DEC-003;
- MySQL Server according to DEC-005;
- Apache ECharts as the approved/default Web/PWA visualization technology direction under the DEC-010 Supplement.

Apache ECharts is not installed merely because the application skeleton begins. Adoption occurs only when a bounded dashboard/reporting/visualization implementation actually requires it and after exact package/version review.

The following policies remain binding:

- ZERO MANDATORY COMMERCIAL SOFTWARE-LICENSE COST — CORE BASELINE;
- FREE / OPEN-SOURCE FIRST — NOT FOSS-ONLY.

## 9. BRIDGE-07 — P2 work continues in parallel

P2 Managed/Hardened VPS or Server remains the selected target Preview environment class for capability evidence collection.

An environment class is not evidence that an actual target complies.

If no actual sanitized target evidence exists, do not repeat the full DEC-009 target-capability matrix. Record only that the actual target is pending external input and continue separately authorized Local/Test/CI work that does not depend on the target.

The full DEC-009 target-capability assessment is performed when actual sanitized target evidence exists.

## 10. BRIDGE-08 — Phase 0 Exit reinterpretation

Phase 0 Exit is not permission to create any source file whatsoever.

Phase 0 Exit remains the governed readiness transition confirming that the Technical Preview program has sufficient combined product, architecture, security, data, runtime, recovery, and operational evidence to progress to the applicable later Preview execution/acceptance stage.

Bounded Local/Test/CI source work may occur before final Phase 0 Exit only under explicit Product Owner source authority.

No source authority is implied by this bridge.

## 11. BRIDGE-09 — Deployment hard stop

Even after source implementation begins, oneQay must not be deployed to Preview until all applicable gates are satisfied, including:

- an actual target is identified;
- DEC-009 mandatory capability evidence is sufficient;
- the DEC-005 MySQL Server requirement is satisfied;
- required HTTPS/runtime/storage/process capabilities are verified;
- a feasible deployment/rollback boundary exists;
- separate Product Owner deployment authority is granted.

Production remains entirely separate and is not authorized.

## 12. BRIDGE-10 — Anti-governance-loop rule

Governance must make engineering safe and auditable without creating a self-referential deadlock.

Do not create recurring work merely to:

- update current-main SHA;
- repeat DEC-000 through DEC-012;
- reopen M5;
- reopen M6;
- re-decide Apache ECharts;
- re-decide Laravel/Vue/MySQL;
- repeat the same DEC-009 capability matrix without new target evidence;
- recreate semantic checkpoint synchronization after every merge;
- demand implementation-generated evidence before the relevant implementation exists.

Historical governance discrepancies remain historical facts and are not rewritten by this rule.

## 13. Current governed status preserved by this bridge

| Item | Status after bridge |
| --- | --- |
| DEC-000 through DEC-012 | APPROVED / DECISION COMPLETE |
| DEC-010 Supplement | APPROVED / PUBLISHED |
| Phase 0 | IN PROGRESS |
| Phase 0 Exit | NOT APPROVED |
| P1 Shared Hosting / cPanel | CONDITIONAL / NOT SELECTED / CURRENTLY NOT READY |
| P2 environment class | SELECTED AS TARGET PREVIEW ENVIRONMENT CLASS FOR CAPABILITY EVIDENCE COLLECTION |
| P2 actual target | PENDING EXTERNAL INPUT unless fresh evidence proves otherwise |
| Sprint 14 | NOT AUTHORIZED |
| Deployment | NOT AUTHORIZED |
| Release | NOT AUTHORIZED |
| Production | NOT AUTHORIZED |
| Production readiness | NO-GO |
| JRN-003 | UNRESOLVED |
| JRN-013 | UNRESOLVED |
| GD-007 | PROPOSED |
| M7.0 | Product Owner substantive bridge decision APPROVED; publication lifecycle pending |
| M7.1 source implementation | NOT AUTHORIZED by this bridge; separate Product Owner authority required |

## 14. Publication and lifecycle boundary

This bridge record is documentation/governance publication only.

This publication candidate contains no Laravel application source, Vue application source, application `package.json`, Composer/npm dependency installation, Apache ECharts adoption, database schema, SQL, migrations, seeders, server provisioning, DNS change, certificate installation, deployment, release, or Production/customer-data processing.

Independent exact-head review is required before lifecycle progression beyond Draft publication preparation.

The independent reviewer must verify that:

1. DEC-009 runtime requirements are not weakened;
2. deployment is not approved;
3. Production is not approved;
4. only the circular dependency between implementation and implementation-generated evidence is removed;
5. security verification remains mandatory;
6. Local/Test/CI source work remains separately bounded;
7. P2 qualification remains mandatory before Preview deployment;
8. no architecture DEC is silently changed.

This record does not grant Product Owner Ready authority, Merge authority, source implementation authority, deployment authority, release authority, or Production authority.

Attribution: Lab | zefry
