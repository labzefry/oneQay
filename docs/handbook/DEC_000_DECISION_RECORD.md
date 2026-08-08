# DEC-000 — Product Vision and Decision Rights Decision Record

> **Status:** Approved — substantive Product Owner decision
> **Phase:** 0 — Governance & Discovery
> **Canonical product:** oneQay
> **Developer & Product Engineering Entity:** Lab | zefry
> **Repository:** `labzefry/oneQay`
> **Product Owner:** `labzefry`

## Decision provenance

- Decision: **DEC-000 — Product Vision and Decision Rights**.
- Decision result: **APPROVED**.
- Decision baseline / current main at decision time: `792b2dc30636bc53baa7d66b43cf2dab4a348dd4`.
- Decision baseline tree: `08f03b895d5e2ae7ca402e9866384990e126add3`.
- Canonical Product Vision and Decision Rights artifact: `docs/handbook/PRODUCT_VISION_AND_DECISION_RIGHTS.md`.
- Approved canonical artifact blob: `843544b9e31dd4c47638b88dd204f4e594295df4`.
- Canonical readiness artifact: `docs/handbook/DEC_000_READINESS_EVIDENCE.md`.
- Readiness artifact blob: `b493a5d66edc1bbffab0126bdacf2ca1ce14fa8f`.
- Readiness evidence publication: PR #73, source head `f74df2c13f4c5407332d25856e483eac36b8c686`, published as `792b2dc30636bc53baa7d66b43cf2dab4a348dd4`, source/published tree `08f03b895d5e2ae7ca402e9866384990e126add3`.

This record represents the explicit Product Owner substantive decision. It does not expand the decision beyond the bounded scope below.

## D-000-01 — Product Vision and Mission

**APPROVED.**

The Product Vision and Mission in `docs/handbook/PRODUCT_VISION_AND_DECISION_RIGHTS.md` are approved as the governing Phase 0 product and discovery direction for oneQay, aligned with the separately Approved Enterprise Vision.

Boundary:

- final MVP scope is not defined by this decision;
- directional Enterprise Capabilities are not automatically approved for implementation;
- Sprint 14 and any new implementation milestone are not started.

## D-000-02 — Product Principles and Outcome Direction

**APPROVED.**

The documented Product Principles and product-outcome directions are approved as governance constraints for future discovery and delivery decisions.

Candidate indicators remain directional until their definition, data source, owner, baseline, target period, and anti-gaming guardrails are separately approved. No target number is approved by DEC-000.

## D-000-03 — Segment, Actor, Problem, Scope, Deferred, and Non-goal Hypotheses

**ACCEPTED AS PHASE 0 DISCOVERY HYPOTHESES AND CONSTRAINTS.**

The documented target-user segments, business problems, product-direction scope, deferred areas, and non-goals are accepted as Phase 0 discovery hypotheses and constraints.

They are not final personas, do not complete stakeholder validation, and do not approve DEC-001 MVP scope/non-scope.

## D-000-04 — Decision-rights Matrix

**CONFIRMED.**

The canonical decision-rights matrix and human-accountability model are confirmed.

- Accountable Product Owner GitHub identity: `labzefry`.
- Current Product Owner delegates: **NONE RECORDED**.
- Until explicit human delegation is recorded in GitHub, the Product Owner retains final approval authority across the decision-rights matrix.
- ChatGPT, reviewers, implementers, inferred roles, and GitHub itself do not hold or substitute for Product Owner approval authority.

Any future delegation must identify the human GitHub identity, bounded decision domain, authority scope, and supersession/revocation path.

## D-000-05 — Open Decision Disposition

**APPROVED AS FOLLOWS.**

| ID | DEC-000 disposition | Continuing evidence / authority |
| --- | --- | --- |
| PV-001 | **SATISFIED for DEC-000 accountable Product Owner identification** | `labzefry` is accountable Product Owner; future delegates require separate explicit GitHub assignment and bounded scope |
| PV-002 | **OPEN — NOT RESOLVED** | Stakeholder interviews and problem evidence for initial customer segment / industry priority |
| PV-003 | **OPEN — NOT RESOLVED** | DEC-001 actor map, journeys, event-storming evidence, and MVP slicing |
| PV-004 | **OPEN — NOT RESOLVED** | Metric definition, data feasibility, baseline, targets, owner, and anti-gaming review |
| PV-005 | **OPEN — NOT RESOLVED** | Qualified legal/compliance evidence for legal, fiscal, payment, privacy, and jurisdiction boundaries |
| PV-006 | **OPEN — NOT RESOLVED** | Named release/incident delegates when needed; until then Product Owner authority remains applicable where the canonical matrix requires it |

Accepting PV-002 through PV-006 as open does not constitute approval, resolution, ADR acceptance, implementation authorization, or waiver of required evidence.

## Issue #2 closure semantics

The historical `Closed/completed` state of GitHub Issue #2 is preserved as historical workflow state only.

Issue #2 closure is **not** the substantive DEC-000 / GD-003 approval evidence. This Product Owner decision is the substantive authority for DEC-000; this bounded decision-record publication is the repository representation of that authority.

## D-000-06 — Implementation-authority Boundary

**CONFIRMED.**

DEC-000 governs Product Vision and Decision Rights only. It does **not** by itself authorize:

- DEC-001 through DEC-012 substantive decisions;
- acceptance of ADR-001 through ADR-007;
- promotion of GD-007;
- resolution of JRN-003 or JRN-013;
- Sprint 14;
- another implementation milestone;
- final/business/production application implementation;
- new business application source implementation;
- executable SQL/schema/migration;
- production database modification;
- deployment;
- release;
- production-readiness promotion;
- implicit framework, vendor, database, authentication, payment, AI provider, offline, physical-tenancy, API, or deployment-runtime selection.

## GD-003 disposition

The Product Owner **APPROVED** the substantive Product Vision and Decision Rights represented by DEC-000 and authorized GD-003 to be recorded as **Approved** through this bounded repository decision-record publication.

GD-003 approval is a governance/product-direction decision only and must not be interpreted as implementation authority.

## Preserved program boundaries

- Phase 0: **In Progress**.
- Sprint 12: **Published**.
- Sprint 13: **Published**.
- Sprint 14: **Not Authorized**.
- DEC-001 through DEC-012 retain their independent decision states and authority requirements.
- ADR-001 through ADR-007: **Proposed**.
- GD-007: **Proposed**.
- JRN-003: **Unresolved**.
- JRN-013: **Unresolved**.
- Final/business/production application implementation: **Not Authorized by DEC-000**.
- Production readiness: **NO-GO**.
- Deployment: **Not Authorized**.
- Release: **Not Authorized**.
- SQL/migration execution: **Not Authorized**.
- Production database modification: **Not Authorized**.

## Supersession

This decision remains binding within its scope until explicitly superseded by a later Product Owner decision recorded in GitHub. A later decision must identify what is superseded and must preserve immutable decision history.

Attribution: Lab | zefry
