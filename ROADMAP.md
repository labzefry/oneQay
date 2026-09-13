# oneQay Roadmap

**Roadmap checkpoint:** post-Sprint145
**Canonical engineering baseline:** `6d4fc06ac1166d15d8598d2a6d39d594f2493767`
**Current state authority:** `PROJECT_MANIFEST.md`

This roadmap describes sequencing and gates. It does **not** grant operational authority. A completed roadmap item means its bounded repository objective was completed; it does not imply deployment, migration execution, runtime activation, Technical Preview activation, or Production activation unless separately evidenced.

## Horizon A — Platform and repository foundation — completed

- [x] Modular Monolith First architecture
- [x] Clean Architecture / DDD boundaries
- [x] Tenant-context-first isolation model
- [x] Deny-by-default authorization foundation
- [x] First-party session and privileged authentication foundations
- [x] Versioned REST governance, stable error envelope, correlation and idempotency conventions
- [x] CI/governance, exact-head qualification, historical-regression and merge-authorization controls
- [x] Transactional-outbox readiness and infrastructure-adapter evolution boundary

## Horizon B — Bounded POS foundations — completed as source engineering

- [x] Sale completion / bounded payment recording / deterministic receipt evidence
- [x] Tenant/outlet-scoped catalog preparation foundation
- [x] Shift/register opening foundation
- [x] Durable idempotency and scoped authorization preservation

## Horizon C — JRN-010 cash and variance evidence — completed through bounded source objectives

- [x] Expected-cash derivation readiness
- [x] Immutable sale-to-shift binding readiness
- [x] Cash-variance source readiness and durable source foundation
- [x] Variance adjudication/explanation foundation
- [x] Explanation author/reviewer authorization hardening
- [x] Maker-checker / review-decision evidence chain

Representative milestones: Sprint55, Sprint64, Sprint70, and Sprint80.

## Horizon D — Final Shift Close source/readiness chain — completed through Sprint145 engineering boundary

- [x] Source-only migration #27 materialization — Sprint88
- [x] Final Shift Close application-readiness contract — Sprint89
- [x] Runtime-binding / manifest / DB-binding attestation readiness
- [x] Canonical token policy — Sprint130
- [x] Middleware/controller positive and fail-closed qualification — Sprint131–Sprint133
- [x] Delivery-gate and registration metadata qualification — Sprint134–Sprint135
- [x] Authenticated HTTP positive/fail-closed/throttle qualification — Sprint136–Sprint138
- [x] Authentication-before-throttle hardening — Sprint139
- [x] Authentication-rejection hardening and HTTP-kernel propagation — Sprint140–Sprint141
- [x] Authenticated throttle-rejection response hardening — Sprint142
- [x] DB-attestation HEAD parity — Sprint143
- [x] Canonical named-route ownership hardening — Sprint144
- [x] Canonical controller-action ownership hardening — Sprint145

### Sprint145 description

**Purpose / Why:** remove the final ownership ambiguity after Sprint144 by requiring the throttle-response hardener to distinguish the canonical controller action from noncanonical actions that otherwise share route name, method, and path.

**Objective / Gap:** ownership now requires route name + canonical controller action + exact method + exact path.

**What changed:** action identity was added to the hardener; Sprint145 added executable canonical/noncanonical action regression coverage; Sprint144's historical positive fixture was made successor-compatible with Laravel controller metadata.

**Evidence / Qualification:** PR #707; 29/29 exact-head CI; authority run `34749677796`; canonical engineering commit `6d4fc06ac1166d15d8598d2a6d39d594f2493767`; six-path engineering envelope SHA-256 `ed1a67c7a7b89e26cd4c3ade350132b8eca7c4e2142f76d9b69495ac0ba2fad2`.

**Operational boundaries / NO-GO:** migration execution, permissions, runtime tokens, deployment/release, target activation, Technical Preview, Production, and updater activation remain outside this completed engineering boundary.

Migration #27 remains source-only and `NOT_EXECUTED`; completion of this engineering horizon does not authorize operational use.

## Horizon E — Sprint146+ bounded engineering — next

Status: **NOT YET SELECTED / NOT COMPLETED**.

Sprint146 begins only after bounded discovery identifies the smallest non-duplicative remaining engineering gap from canonical post-Sprint145.

Selection rules:

1. prove the gap exists before adding source;
2. freeze a bounded source envelope;
3. preserve Sprint130–Sprint145 owned regressions and historical compatibility;
4. stay fail-closed and deny-by-default;
5. avoid operational adapter invocation and runtime-token provisioning unless separately authorized;
6. qualify exact head in CI before merge;
7. document the sprint using Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position;
8. reconcile canonical project-state documentation at closure.

No roadmap text pre-authorizes a specific Sprint146 implementation.

## Horizon F — Operational qualification — blocked / separate authority

- [ ] Qualified non-synthetic durable runtime target — missing
- [ ] Selected target — currently `null`
- [ ] Migration #27 execution authority — not granted
- [ ] Permission provisioning authority — current state `NONE`
- [ ] Feature activation authority — current state `INACTIVE`
- [ ] Real operational runtime token provisioning — not performed
- [ ] Real operational binding/attestation evidence — not performed
- [ ] Deployment authority — `NOT_GRANTED`

Current target selection remains `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`.

Production remains rejected as the first activation target; any first activation target must be an isolated non-production, non-synthetic durable runtime with required evidence controls.

## Horizon G — Technical Preview activation — NO-GO

Technical Preview activation remains `NOT_AUTHORIZED`.

## Horizon H — Production activation — NO-GO

Production activation remains `NOT_AUTHORIZED`.

## Horizon I — Updater/release activation — inactive

Updater activation remains `INACTIVE`. Release/deployment procedures do not establish authority or evidence that a release occurred.

## Roadmap maintenance rule

At every material sprint closure:

- record the six mandatory sprint-description fields;
- move only genuinely completed bounded objectives into the completed horizon;
- never infer operational activation from source evidence;
- update `PROJECT_MANIFEST.md` first;
- reconcile README, TASKS, CHANGELOG, and ROADMAP;
- make the just-closed sprint workflow successor-compatible;
- retain detailed per-sprint evidence in `docs/` and machine-readable contracts.

Author by Lab | zefry
