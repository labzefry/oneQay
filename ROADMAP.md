# oneQay Roadmap

**Roadmap checkpoint:** post-Sprint151 reconciliation
**Canonical engineering baseline:** `68b8362f326e56cfec478f0275b6d29ed0f54dec`
**Current state authority:** `PROJECT_MANIFEST.md`

This roadmap describes sequencing and gates. It does **not** grant operational authority. Completed roadmap items are bounded repository objectives, not evidence of deployment or activation.

## Horizon A — Platform and repository foundation — completed

- [x] Modular Monolith First architecture
- [x] Clean Architecture / DDD boundaries
- [x] Tenant-context-first isolation and deny-by-default authorization
- [x] First-party session and privileged authentication foundations
- [x] Versioned REST governance, stable error envelope, correlation and idempotency conventions
- [x] CI/governance, exact-head qualification, historical-regression and merge-authorization controls
- [x] Transactional-outbox readiness and infrastructure-adapter evolution boundary

## Horizon B — Bounded POS foundations — completed as source engineering

- [x] Sale/payment/receipt evidence foundations
- [x] Tenant/outlet-scoped catalog preparation
- [x] Shift/register opening foundation
- [x] Durable idempotency and scoped authorization preservation

## Horizon C — JRN-010 cash and variance evidence — completed through bounded source objectives

Representative completed milestones include Sprint55 expected-cash derivation, Sprint64 cash-variance source foundation, Sprint70 durable cash-variance explanation, and Sprint80 scoped reviewer authorization.

## Horizon D — Final Shift Close source/readiness chain — completed through Sprint151 engineering boundary

- [x] Source-only migration #27 materialization — Sprint88
- [x] Application-readiness contract — Sprint89
- [x] Nine-component runtime dependency inventory / allowlist block — Sprint107
- [x] Durable-runtime readiness capability shape — Sprint110
- [x] Exact selected-target identity and deterministic selection fingerprint — Sprint111
- [x] Source-only attestation producer/ingestion, selection persistence and binding readiness — Sprint113–Sprint117
- [x] Canonical runtime-control-plane and HTTP/throttle hardening — Sprint130–Sprint147
- [x] Target-bound durable-runtime capability-evidence identity qualification — Sprint148
- [x] Trusted protected-environment target-bound capability-evidence producer source — Sprint149
- [x] Exact selected-runtime-class full nine-component dependency-envelope qualifier — Sprint150
- [x] Target-bound dependency-envelope evidence construction source foundation — Sprint151

### Sprint151 evidence

- PR #720 squash merged at canonical engineering commit `68b8362f326e56cfec478f0275b6d29ed0f54dec`.
- Final engineering head `f41e32bc44e0ee0346f5efd15d54f6af21b8a19a`.
- Exact-head CI: 36/36 successful.
- Repository-native Product Owner authority run: `34764011476` successful.
- Engineering envelope: five paths, SHA-256 `ef000ec9172dcee1f08a6c8ea9149fb957a307e7929eaaa42c5d4b08bad94c54`.
- Reconciliation envelope: six paths, SHA-256 `82d72910aaa4409113cfbb8b2b7f326daa51c205d7a6bfefa90ad5624604fb25`.
- Dependency-evidence source foundation is `MATERIALIZED_SOURCE_ONLY`; dispatchable producer remains `NOT_IMPLEMENTED`; real dependency evidence remains `NONE`.
- Runtime allowlist remains `local/test/ci`; no selected runtime class was enabled.

Migration #27 remains `NOT_EXECUTED`; completion of this engineering horizon does not authorize operational use.

## Horizon E — Sprint152+ bounded engineering — next

Status: **BOUNDED DISCOVERY NEXT AFTER SPRINT151 CLOSURE**.

Selection rules:

1. prove a material non-duplicative gap exists before adding source;
2. check historical regressions, machine-readable contracts, and current source foundations before creating a new invariant owner;
3. prioritize production-readiness prerequisites toward a qualified isolated non-production durable target without operational mutation;
4. freeze the smallest meaningful bounded source envelope;
5. stay fail-closed and deny-by-default;
6. avoid target persistence, producer dispatch, migration execution, permission provisioning, activation, deployment, and allowlist widening unless separately authorized;
7. qualify exact head in CI before merge;
8. document Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position;
9. reconcile canonical project-state documentation at closure.

No roadmap text pre-authorizes a specific Sprint152 implementation.

## Horizon F — Operational qualification — blocked / separate authority

- [ ] Qualified non-synthetic durable runtime target — missing
- [ ] Selected target — currently `null`
- [ ] Trusted capability-evidence producer dispatch — `NOT_PERFORMED`
- [ ] Real target-bound capability evidence — `NONE`
- [ ] Dispatchable dependency-envelope evidence producer — `NOT_IMPLEMENTED`
- [ ] Real target-bound dependency-envelope evidence — `NONE`
- [ ] Migration #27 execution — `NOT_EXECUTED`
- [ ] Permission provisioning — `NONE`
- [ ] Runtime allowlist change for selected durable runtime — `NOT_IMPLEMENTED`
- [ ] Feature activation — `INACTIVE`
- [ ] Deployment authority — `NOT_GRANTED`

Current target selection remains `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`.

## Horizon G — Technical Preview activation — NO-GO

Technical Preview activation remains `NOT_AUTHORIZED`.

## Horizon H — Production activation — NO-GO

Production activation remains `NOT_AUTHORIZED`.

## Horizon I — Updater/release activation — inactive

Updater activation remains `INACTIVE`. Release/deployment procedures do not establish authority or evidence that a release occurred.

## Roadmap maintenance rule

At every material sprint closure, update the canonical manifest and four root summaries, preserve detailed per-sprint evidence, make the just-closed workflow successor-compatible, and never infer operational activation from source evidence.

Author by Lab | zefry
