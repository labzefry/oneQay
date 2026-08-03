# Phase 0 Technical Preview Exit Evidence

- Status: Not Ready
- Target: authorize application skeleton for Technical Preview v0.0.1
- Tracking: Issue #23
- Source-code authority: Not granted

## Product Owner selections recorded

| Item | Selection | Evidence state |
|---|---|---|
| Backend | B1 Laravel/PHP | Selected; ADR Proposed |
| Frontend | F1 Vue 3 + Inertia + Vite | Selected; ADR Proposed |
| Database/tenancy | D1 MySQL-compatible shared schema | Selected; engine/version Pending |
| Authentication | A1 first-party session + privileged TOTP | Selected; ADR Proposed |
| Deployment | P1 conditional, P2 fallback | Hosting evidence Pending |
| Payment | PAY-1 synthetic cash-only | Selected; ADR Proposed |
| Offline | OFF-1 online-only | Selected; ADR Proposed |
| Tenant isolation | TEN-1 two synthetic tenants | Selected; verification Pending |
| Recovery | REC-1 RPO 24h/RTO 4h | Selected; capability/rehearsal Pending |
| SLO | SLO-1 | Selected; measurement plan Pending |
| Data | DATA-1 synthetic only | Selected; baseline Proposed |

The Product Owner decision is recorded on Issue #23. The user-provided decision date and hosting fields were blank; missing evidence is not inferred.

## Exit checklist

- [ ] Exact-head content approval for all required ADRs.
- [ ] ADR-001 through ADR-007 moved from Proposed to Accepted by explicit decision.
- [ ] MVP preview scope/non-scope and success metrics approved.
- [ ] Data inventory/classification approved.
- [ ] Threat model reviewed with no unresolved Critical skeleton blocker.
- [ ] P1 capability assessment passes, or P2 option is separately decided.
- [ ] Backup/restore/rollback approach is feasible against target environment.
- [ ] Supported environment and quality-gate matrix approved.
- [ ] Tenant-isolation acceptance plan approved.
- [ ] JRN-003 and JRN-013 remain visible and do not become implicitly resolved.
- [ ] Repository lifecycle risk is documented for High/Critical ready/merge actions.
- [ ] PROJECT_MANIFEST, ARCHITECTURE, ROADMAP, TASKS, and CHANGELOG are consistent.
- [ ] Product Owner issues an explicit Phase 0 preview exit statement tied to the latest exact head.

## Explicit blockers

1. Hosting runtime, database, worker, HTTPS, backup, restore, rollback, and quota evidence is missing.
2. All ADRs remain Proposed.
3. Threat, data, recovery, and isolation artifacts have not received exact-head approval.
4. No application-skeleton authority has been granted.

## Exit decision template

```text
I act as Product Owner OneQay.

For PR #[number] at exact head [40-character SHA], I approve the Phase 0
Technical Preview decision package and move ADR-001 through ADR-007 to
Accepted. I approve Phase 0 preview exit for application skeleton only.

Target environment: [P1/P2 with evidence URL]
Decision timestamp: [ISO-8601 +07:00]

This does not approve production/pilot release, real payment, offline
transaction processing, GD-007, JRN-003, or JRN-013. Ready transition and
merge require separate exact-head authority.
```

Until that statement exists, application implementation remains Blocked.
