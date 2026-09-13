# oneQay Roadmap

**Roadmap checkpoint:** post-Sprint155 reconciliation  
**Canonical engineering baseline:** `1e84e1b3e07915a1d20b56fe768b0d1454f901d2`  
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

## Horizon D — Final Shift Close source/readiness chain — completed through Sprint155 engineering boundary

- [x] Source-only migration #27 materialization — Sprint88
- [x] Nine-component runtime dependency inventory / allowlist block — Sprint107
- [x] Durable-runtime readiness capability shape — Sprint110
- [x] Exact selected-target identity and deterministic selection fingerprint — Sprint111
- [x] Attestation/ingestion, selection persistence, and migration selected-target DB binding readiness — Sprint113–Sprint118
- [x] Runtime DB-binding/materialization control-plane hardening — Sprint119–Sprint129
- [x] Canonical runtime-control-plane and HTTP/throttle hardening — Sprint130–Sprint147
- [x] Target-bound durable-runtime capability-evidence identity qualification — Sprint148
- [x] Trusted target-bound capability-evidence producer source — Sprint149
- [x] Full selected-runtime nine-component dependency-envelope qualifier — Sprint150
- [x] Dependency-envelope evidence deterministic construction source foundation — Sprint151
- [x] Permission provisioning selected-target database-binding hardening — Sprint152
- [x] Trusted dependency-envelope evidence producer source — Sprint153
- [x] Feature-activation executor deterministic source foundation — Sprint154
- [x] Deterministic source-only feature-activation transport handoff envelope — Sprint155

### Sprint155 evidence

- Engineering PR #728 squash merged at canonical engineering commit `1e84e1b3e07915a1d20b56fe768b0d1454f901d2`.
- Parent canonical post-Sprint154 checkpoint `056d0300af925c9e8adf04a11a107cc4f5fde196`.
- Final engineering head `4871e0ade60e8ac5e6f44b2bc27f0319ca150e63`.
- Exact-head CI: 36/36 successful.
- PHP Foundation Regression run `34774606244`: successful.
- M7.1 Application Regression run `34774606266`: successful and directly executed the Sprint155 regression.
- Repository-native Product Owner authority run `34775351008`: successful.
- Engineering envelope: three paths, SHA-256 `29619b928a422615647184c5316d9679dd4c4d582d759e89e8704e335ed982cb`.
- Reconciliation envelope: six paths, SHA-256 `323efb8b04badda3874aa7542285499b7be7b8df139cc86fd43b294aac7f8a38`.
- Source-only activation transport handoff envelope is materialized.
- Dispatchable executor, concrete configuration-mutation transport, and runtime allowlist widening remain `NOT_IMPLEMENTED`; network/executor dispatch remains `NOT_PERFORMED`; feature remains `INACTIVE`.

Migration #27 remains `NOT_EXECUTED`; completion of this engineering horizon does not authorize operational use.

## Horizon E — Sprint156+ bounded engineering — next

Status: **BOUNDED DISCOVERY NEXT AFTER SPRINT155 CLOSURE**.

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

No roadmap text pre-authorizes a specific Sprint156 implementation.

## Horizon F — Operational qualification — blocked / separate authority

- [ ] Qualified non-synthetic durable runtime target — missing
- [ ] Selected target — currently `null`
- [ ] Real selected-target DB-binding evidence — `NONE`
- [ ] Migration #27 execution — `NOT_EXECUTED`
- [ ] Selected-target-bound permission provisioning — `NONE`
- [ ] Trusted capability-evidence producer dispatch — `NOT_PERFORMED`
- [ ] Real target-bound capability evidence — `NONE`
- [ ] Trusted dependency-envelope evidence producer dispatch — `NOT_PERFORMED`
- [ ] Real target-bound dependency-envelope evidence — `NONE`
- [ ] Dispatchable feature-activation executor — `NOT_IMPLEMENTED`
- [ ] Concrete configuration-mutation transport — `NOT_IMPLEMENTED`
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

At every material sprint closure, update the canonical manifest and four root summaries, preserve detailed per-sprint evidence where materialized, keep the just-closed source-contract preservation workflow successor-compatible, and never infer operational activation from source evidence.

Author by Lab | zefry
