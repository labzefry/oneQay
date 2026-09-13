# oneQay Roadmap

**Roadmap checkpoint:** post-Sprint148 reconciliation
**Canonical engineering baseline:** `7a07a3163842e60332ccd3e3780d4e970280d46c`
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

## Horizon D — Final Shift Close source/readiness chain — completed through Sprint148 engineering boundary

- [x] Source-only migration #27 materialization — Sprint88
- [x] Final Shift Close application-readiness contract — Sprint89
- [x] Durable-runtime readiness capability shape — Sprint110
- [x] Exact selected-target identity and deterministic selection fingerprint — Sprint111
- [x] Source-only attestation producer/ingestion, selection persistence and binding readiness — Sprint113–Sprint117
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
- [x] Canonical per-route throttle-budget identity response hardening — Sprint146
- [x] Canonical throttle-rejection metadata identity response hardening — Sprint147
- [x] Target-bound durable-runtime capability-evidence identity qualification — Sprint148

### Sprint148 description

**Purpose / Why:** Sprint110 required durable-runtime capability claims and Sprint111 bound qualified readiness to an exact target, but boolean capability claims did not themselves prove capability-specific evidence tied to that exact selected environment/runtime/source/artifact identity.

**Objective / Gap:** future evidence for authenticated configuration mutation, read-before/write/read-after verification, non-mutating health attestation, and verified rollback must each be `VERIFIED`, secret-free, digest-qualified, and bound to the exact Sprint111 selected-target identity while authority remains `NOT_GRANTED`, feature remains `INACTIVE`, and runtime allowlist change remains `NOT_IMPLEMENTED`.

**What changed:** added the Sprint148 pure capability-evidence qualifier, executable regressions, machine-readable contract, post-selection readiness prerequisite, active exact-head workflow, detailed documentation, and a CI-proven successor-compatible conversion of the historical Sprint116 workflow.

**Evidence / Qualification:** PR #714; final engineering head `eba8687297e7b82bf5adfcd770d86233139b0454`; 33/33 exact-head CI; repository-native authority run `34756294306`; canonical engineering commit `7a07a3163842e60332ccd3e3780d4e970280d46c`; final seven-path engineering envelope SHA-256 `f7d9cfb173b54ac863cc70f10b9ae3df2f6715a4abdae0c7c32cbdb399bda5a6`; post-Sprint148 reconciliation envelope SHA-256 `5c315771e9777b9d5a5b428a206c7cec711e14a7851a83c685a5a5cfb378dfc5`.

**Operational boundaries / NO-GO:** real capability evidence, trusted evidence producer, selected-target persistence/activation, migration execution, permission provisioning, feature activation, runtime tokens, runtime allowlist change, operational manifest/DB invocation, deployment/release, Technical Preview, Production, and updater activation remain outside this completed engineering boundary.

**Next position:** Sprint149 bounded discovery after canonical post-Sprint148 reconciliation; no implementation objective or source envelope is preselected.

Migration #27 remains source-only and `NOT_EXECUTED`; completion of this engineering horizon does not authorize operational use.

## Horizon E — Sprint149+ bounded engineering — next

Status: **BOUNDED DISCOVERY NEXT AFTER SPRINT148 CLOSURE**.

Sprint149 may begin only after post-Sprint148 reconciliation is squash merged and canonical main is verified.

Selection rules:

1. prove a material non-duplicative gap exists before adding source;
2. check that Sprint110–Sprint148 historical regressions do not already own the invariant;
3. prioritize production-readiness prerequisites that genuinely move toward a qualified isolated non-production durable target without performing operational mutation;
4. freeze the smallest meaningful bounded source envelope;
5. stay fail-closed and deny-by-default;
6. avoid operational adapter invocation, runtime-token provisioning, migration execution, permission provisioning, activation, deployment, and allowlist widening unless separately authorized;
7. qualify exact head in CI before merge;
8. document the sprint using Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position;
9. reconcile canonical project-state documentation at closure.

No roadmap text pre-authorizes a specific Sprint149 implementation.

## Horizon F — Operational qualification — blocked / separate authority

- [ ] Qualified non-synthetic durable runtime target — missing
- [ ] Selected target — currently `null`
- [ ] Real target-bound capability evidence — `NONE`
- [ ] Trusted capability-evidence producer — `NOT_IMPLEMENTED`
- [ ] Migration #27 execution authority — not granted
- [ ] Permission provisioning authority — current state `NONE`
- [ ] Feature activation authority — current state `INACTIVE`
- [ ] Runtime allowlist change for selected durable runtime class — `NOT_IMPLEMENTED`
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
