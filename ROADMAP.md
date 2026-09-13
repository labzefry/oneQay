# oneQay Roadmap

**Roadmap checkpoint:** post-Sprint150 reconciliation
**Canonical engineering baseline:** `d743a054092231729fa0e33cd34538f9d1e81787`
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

## Horizon D — Final Shift Close source/readiness chain — completed through Sprint150 engineering boundary

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

### Sprint150 evidence

- PR #718 squash merged at canonical engineering commit `d743a054092231729fa0e33cd34538f9d1e81787`.
- Final engineering head `7102733080735f4591bb17df76bdec928975e826`.
- Exact-head CI: 35/35 successful.
- Repository-native Product Owner authority run: `34761524119` successful.
- Engineering envelope: six paths, SHA-256 `9f261895ab0373af5d6d385c3db3f93e5e510061b8e843e4110ecce992d9a0e6`.
- Reconciliation envelope: six paths, SHA-256 `578765b03de34048670791017fcffe8680b65e6bede9f57d22affb255f5ee43f`.
- Dependency-envelope qualifier source is materialized, but real dependency evidence remains `NONE` and dependency-evidence producer remains `NOT_IMPLEMENTED`.
- Runtime allowlist remains `local/test/ci`; no selected runtime class was enabled.

Migration #27 remains `NOT_EXECUTED`; completion of this engineering horizon does not authorize operational use.

## Horizon E — Sprint151+ bounded engineering — next

Status: **BOUNDED DISCOVERY NEXT AFTER SPRINT150 CLOSURE**.

Selection rules:

1. prove a material non-duplicative gap exists before adding source;
2. check historical regressions and machine-readable contracts before creating a new invariant owner;
3. prioritize production-readiness prerequisites toward a qualified isolated non-production durable target without operational mutation;
4. freeze the smallest meaningful bounded source envelope;
5. stay fail-closed and deny-by-default;
6. avoid target persistence, producer dispatch, migration execution, permission provisioning, activation, deployment, and allowlist widening unless separately authorized;
7. qualify exact head in CI before merge;
8. document Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position;
9. reconcile canonical project-state documentation at closure.

No roadmap text pre-authorizes a specific Sprint151 implementation.

## Horizon F — Operational qualification — blocked / separate authority

- [ ] Qualified non-synthetic durable runtime target — missing
- [ ] Selected target — currently `null`
- [ ] Trusted capability-evidence producer dispatch — `NOT_PERFORMED`
- [ ] Real target-bound capability evidence — `NONE`
- [ ] Trusted dependency-envelope evidence producer — `NOT_IMPLEMENTED`
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
