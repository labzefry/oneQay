# Phase 0 Technical Preview Exit Evidence

- Status: Not Ready
- Readiness preparation: In Progress through reopened Issue #23
- Target: prepare a bounded Product Owner decision package for possible application-skeleton authority
- Tracking: Issue #23
- Source-code authority: Not granted
- Phase 0 exit authority: Not granted
- Sprint 14 authority: Not granted

## Current governed decision state

The original Issue #23 Technical Preview selections are historical provenance where later governed decisions supersede or refine them. Current decision state must follow the separately published DEC/ADR records.

| Item | Current governed state | Readiness implication |
| --- | --- | --- |
| Backend | DEC-002 Approved; ADR-001 Accepted; PHP + Laravel direction | Decision satisfied; dependency/implementation remains separately gated |
| Frontend | DEC-003 Approved; ADR-002 Accepted; Vue 3 + Inertia + Vite, TypeScript-first direction | Decision satisfied; dependency/implementation remains separately gated |
| Database/tenancy | DEC-005 Approved; ADR-003 Accepted; MySQL Server, shared database/shared schema default | Decision satisfied; target-host MySQL capability still required |
| Authentication | DEC-006 Approved; ADR-004 Accepted; first-party identity/session with privileged TOTP baseline | Decision satisfied; JRN-003 remains Unresolved |
| Deployment | DEC-009 Approved; ADR-007 Accepted; capability-based Stage-1 Preview model | P1 remains Conditional / Not Selected; P2 is fallback execution class |
| Payment | DEC-007 Approved; ADR-005 Accepted | Technical Preview PAY-1 remains synthetic cash-only provenance; no provider/real-money authority |
| Offline | DEC-008 Approved; ADR-006 Accepted | Historical OFF-1 provenance is governed by DEC-008; no new offline implementation authority |
| Tenant isolation | TEN-1 two synthetic tenants remains preview provenance | Verification/acceptance evidence still pending |
| Recovery | DEC-012 Approved recovery policy; REC-1 24h/4h remains Technical Preview proposal only | Successful restore/rollback rehearsal and measured achieved RPO/RTO still pending |
| SLO | DEC-012 Approved SLO/SLA taxonomy; SLO-1 remains Technical Preview provenance only | No final numerical Production SLO/SLA is approved |
| Data | DEC-011 Approved privacy/data-classification policy and Synthetic-by-Default preview direction | Preview-specific DATA-1 inventory/retention baseline remains Proposed and needs exact-state review |

## Exit checklist

- [x] ADR-001 through ADR-007 have Accepted current representations through their separately governed DEC reconciliations.
- [x] Backend, frontend, database/tenancy, authentication, payment, offline, and Stage-1 runtime directions have separately Approved Product Owner decisions.
- [ ] Technical Preview scope/non-scope and measurable preview success criteria receive current exact-state Product Owner approval.
- [ ] Preview-specific data inventory/classification/retention baseline receives exact-state Product Owner and security review.
- [ ] Technical Preview threat model receives security review with no unresolved Critical skeleton blocker.
- [ ] A target Preview environment is selected from the DEC-009 capability model: P1 passes all mandatory evidence, or P2/another compliant class is separately selected.
- [ ] Backup/restore/rollback capability is verified against the selected target environment.
- [ ] At least one isolated restore and one recoverable deployment rollback rehearsal pass before any recovery claim is represented as verified.
- [ ] Tenant-isolation acceptance plan for two deterministic synthetic tenants receives exact-state approval; implementation evidence remains a later source-authorized gate.
- [x] JRN-003 and JRN-013 remain explicitly Unresolved and are not implicitly resolved by readiness work.
- [x] Repository lifecycle control requires independent exact-head review plus separate Product Owner READY and MERGE authority for governed PR publication.
- [ ] Canonical current-state documents are reconciled for the final readiness candidate without creating standing implementation authority.
- [ ] Product Owner issues a separate explicit Phase 0 Technical Preview exit decision tied to the final exact state.

## Current deployment blocker

DEC-009 makes environment selection capability-based rather than category-based.

P1 Shared Hosting / cPanel is currently **CONDITIONAL / NOT SELECTED**. Existing repository evidence is meaningful but does not constitute a Pass. Material blockers include:

- canonical MySQL Server capability/connectivity on the target;
- safe public-only document-root mapping;
- effective rewrite/front-controller routing;
- required scheduler cadence;
- safe worker/background execution model where required;
- target secret and private-storage isolation;
- actual application database security/connection-limit evidence;
- backup coverage plus successful isolated restore evidence;
- versioned/recoverable deployment and rollback;
- complete resource/quota visibility;
- outbound DNS/HTTPS capability according to the execution model.

MariaDB evidence is retained as factual hosting evidence but does not satisfy the canonical DEC-005 MySQL Server requirement.

P2 managed/hardened VPS/server remains the fallback execution class. No provider, server, hosting plan, domain, runtime host, or deployment execution is authorized by this readiness package.

## Preview data readiness

DEC-011 has already Approved the policy-level data inventory/classification direction and Synthetic-by-Default handling for Preview. The preview-specific `TECHNICAL_PREVIEW_DATA_BASELINE.md` remains a Proposed bounded artifact because its exact inventory, preview retention values, generator specification, and tenant-isolation acceptance plan still require current exact-state review.

No production/customer/payment/personal/credential data may be introduced into the Technical Preview by implication.

## Threat-model readiness

`TECHNICAL_PREVIEW_THREAT_MODEL.md` exists as a Proposed bounded artifact. It identifies Critical/High threats including cross-tenant access, broken authorization, secret exposure, session/MFA abuse, transaction/idempotency integrity, backup disclosure/restore failure, supply-chain risk, and deployment rollback failure.

Before skeleton authority, each Critical threat required for the skeleton must map to a verification or explicit blocker, and the threat model must receive current security review. Readiness documentation alone does not claim that those controls are implemented.

## Recovery readiness

DEC-012 has already Approved an evidence-gated recovery policy. Historical REC-1 values remain Technical Preview proposals only:

- RPO 24 hours — proposal/provenance only;
- RTO 4 hours — proposal/provenance only.

These values are not verified objectives until measured successful restoration evidence exists. Backup success alone is not recoverability. The Technical Preview recovery candidate must capture isolated restore, integrity, tenant isolation, application health, applicable business invariants, achieved RPO/RTO, operator/date/result, and remediation/re-test evidence where needed.

## Explicit remaining blockers

1. No compliant target Preview environment is selected; P1 remains Conditional / Not Selected and P2 remains an unselected fallback execution class.
2. Preview-specific data, threat-model, and recovery artifacts have not received the required current exact-state approvals/reviews.
3. Successful restore, rollback, and tenant-isolation execution evidence does not yet exist for the future source-authorized preview.
4. A current exact-state Technical Preview scope/success-criteria approval package is not yet complete.
5. No explicit Product Owner Phase 0 Technical Preview exit decision exists on the final readiness state.
6. No application-skeleton or broader source-code authority has been granted.

## Readiness decision rule

The next decision is not a re-approval of ADR-001 through ADR-007. Those current representations are already Accepted through their separately governed decisions.

The next Product Owner decision package must instead decide whether the remaining Preview-specific evidence is sufficient to grant a **bounded Phase 0 Technical Preview exit for application-skeleton preparation only**. If any mandatory blocker remains, the correct outcome is `NOT READY` or a bounded corrective action; implementation must not be inferred.

Any future Phase 0 exit authorization must be tied to the exact final reviewed state and remain separate from Product Owner READY authority, Product Owner MERGE authority, Sprint 14, deployment, release, and Production authority.

Attribution: Lab | zefry
