# oneQay Roadmap

**Roadmap checkpoint:** post-Sprint156 canonical reconciliation
**Canonical engineering baseline:** `259cc00037ee0d3fb909cbcf2c87d39ffb26f9b9`
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
- [x] Cash variance/adjudication/reviewer foundations

## Horizon C — Final Shift Close source/readiness chain — source-ready through Sprint155

- [x] Source-only migration #27 materialization — Sprint88
- [x] Runtime dependency inventory / allowlist block — Sprint107
- [x] Durable-runtime readiness and selected-target identity — Sprint110–Sprint111
- [x] Attestation/ingestion, selection persistence, and migration selected-target binding readiness — Sprint113–Sprint118
- [x] Runtime DB-binding/materialization control-plane hardening — Sprint119–Sprint129
- [x] Canonical runtime-control-plane and HTTP/throttle hardening — Sprint130–Sprint147
- [x] Target-bound capability-evidence identity and producer source — Sprint148–Sprint149
- [x] Selected-runtime dependency-envelope qualification/evidence foundations — Sprint150–Sprint153
- [x] Feature-activation execution-plan source foundation — Sprint154
- [x] Deterministic source-only feature-activation transport handoff envelope — Sprint155

Operational execution remains separately gated. Migration #27 remains `NOT_EXECUTED`, selected target remains `null`, permissions remain `NONE`, and feature activation remains `INACTIVE`.

## Horizon D — Product-readiness operational reporting — Sprint156 engineering complete

- [x] Select a non-duplicative product-value gap instead of extending source abstraction where external runtime prerequisites are the true blocker
- [x] Materialize read-only POS operational sales reporting
- [x] Scope reporting by tenant + organization + outlet
- [x] Preserve currency and currency-scale boundaries
- [x] Apply deny-by-default reporting authorization
- [x] Gate delivery to Local/Test/CI plus explicit feature arming
- [x] Add Vue/Inertia operational dashboard
- [x] Add executable SQLite isolation/multi-currency/state/fail-closed regression
- [x] Restore canonical global provider registry and compose reporting through existing POS composition root
- [x] Repair stale historical successor-envelope ownership while preserving substantive regressions
- [x] Make Sprint148 qualification concurrency exact-head isolated
- [x] Qualify exact engineering head successfully
- [x] Obtain repository-native Product Owner merge authority
- [x] Squash merge engineering PR #730 at `259cc00037ee0d3fb909cbcf2c87d39ffb26f9b9`

### Sprint156 evidence

- Parent canonical post-Sprint155 checkpoint: `4f939acd6cbcc3c49a45cba549a082c789cabd44`.
- Final engineering head: `5e460d1c7c5174cc831106ade3e3fa6309acba4d`.
- Complete exact-head PR-triggered matrix: successful.
- Sprint156 regression run `34813484159`: successful.
- M7.1 run `34813484204`: successful.
- Governance run `34813484278`: successful.
- PHP Foundation run `34813484276`: successful.
- Sprint96, Sprint97, Sprint126, and Sprint148 regressions all successful on the exact engineering head.
- Engineering envelope: 24 paths, SHA-256 `34c6dab2c898ddd9133aaa6d5413ca7b345127020d8f04fe54f861a4d1a5e79c`.
- Reconciliation envelope: six paths, SHA-256 `adba5b23ef33aeb360ebb4090b3f848fc2a3704807a60026c1344b2e0d1a54f4`.

## Horizon E — Sprint157+ bounded engineering — next after Sprint156 reconciliation

Status: **BOUNDED DISCOVERY NEXT AFTER SPRINT156 CLOSURE**.

Selection rules:

1. prove a material non-duplicative gap exists before adding source;
2. inspect historical regressions, machine-readable contracts, and current source foundations before creating a new invariant owner;
3. prioritize P0 security/data/tenant/auth/transaction/deployment blockers and P1 business completeness over low-value abstraction;
4. recognize when the true blocker is external/operational and avoid inventing another source-only layer;
5. freeze the smallest meaningful bounded source envelope;
6. stay fail-closed, deny-by-default, and tenant-isolated;
7. avoid target persistence, producer dispatch, migration execution, permission provisioning, activation, deployment, and allowlist widening unless separately authorized;
8. qualify exact head in CI before merge;
9. document Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position;
10. reconcile canonical project-state documentation at closure.

No roadmap text pre-authorizes a specific Sprint157 implementation.

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
- [ ] Runtime allowlist widening for a selected durable runtime — not authorized
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

At every material sprint closure, update the canonical manifest and four root summaries, preserve detailed evidence in workflows/contracts/Git history, keep the just-closed preservation workflow successor-compatible, and never infer operational activation from source evidence.

Author by Lab | zefry
