# oneQay Tasks

**Current engineering checkpoint:** post-Sprint146
**Canonical engineering commit:** `a5e4aec8c142e7478a0e58d2d732dbf106393b06`
**Canonical status authority:** `PROJECT_MANIFEST.md`

This file separates **completed source engineering**, **next bounded engineering**, and **operational actions that remain unauthorized**.

## Completed through Sprint146

- [x] Core modular-monolith architecture and repository governance foundation
- [x] Tenant-context-first and deny-by-default authorization foundations
- [x] Versioned REST / correlation / idempotency governance foundations
- [x] POS shift/register, sale/payment/receipt, and catalog foundations
- [x] JRN-010 expected-cash, sale-to-shift, variance, explanation/adjudication, maker-checker, and reviewer-control chain
- [x] Final Shift Close migration #27 source materialization; execution remains unauthorized
- [x] Final Shift Close application/runtime readiness chain
- [x] Runtime binding / manifest / DB-binding attestation readiness chain
- [x] Canonical runtime control-plane token policy — Sprint130
- [x] Middleware/controller positive-path and fail-closed qualification — Sprint131–Sprint133
- [x] Delivery-gate and registration metadata qualification — Sprint134–Sprint135
- [x] Authenticated HTTP positive/fail-closed/throttle qualification — Sprint136–Sprint138
- [x] Authentication-before-throttle hardening — Sprint139
- [x] Authentication-rejection hardening and HTTP-kernel propagation — Sprint140–Sprint141
- [x] Authenticated throttle-rejection response hardening — Sprint142
- [x] DB-attestation HEAD throttle parity — Sprint143
- [x] Named-route identity ownership hardening — Sprint144
- [x] Canonical controller-action identity ownership hardening — Sprint145
- [x] Canonical per-route throttle-budget identity ownership hardening — Sprint146
- [x] Sprint146 PR #709 squash merged
- [x] Sprint146 exact-head PR qualification — 30/30 successful
- [x] Sprint146 Product Owner merge-authority run `34750648988` successful
- [x] Sprint146 canonical engineering commit `a5e4aec8c142e7478a0e58d2d732dbf106393b06`
- [x] Sprint146 post-merge engineering NO-GO verification completed

## Sprint146 description

### Purpose / Why

Sprint145 secured throttle-response request ownership using route name + canonical controller action + exact method + exact path, but the response-side hardener still accepted any present `X-RateLimit-Limit` value.

### Objective / Gap

Require the previously qualified canonical request identity plus the exact canonical rate-limit ceiling: materialization POST `1`, DB-attestation GET/HEAD `2`.

### What changed

- Added canonical per-route throttle-limit constants and exact-string response matching.
- Added a Sprint146 executable regression proving mismatched, arbitrary, and numeric-alias ceilings remain framework-owned.
- Added Sprint146 contract, workflow, and detailed six-section description.
- Preserved Sprint145 action identity and Sprint142 real HTTP-kernel throttle evidence.

### Evidence / Qualification

- PR #709, merged.
- Final authorized engineering head `b520b8e8c565a96b4c41e7838a68492f4b836066`.
- 30/30 exact-head workflows successful.
- Product Owner authority run `34750648988` successful.
- Canonical engineering commit `a5e4aec8c142e7478a0e58d2d732dbf106393b06`.
- Engineering envelope: five paths.
- Engineering envelope SHA-256 `bbc0da27fe84ca8a1fafcf4b76bcf9e1f42e595c01d35544a94793c1a7fec161`.

### Operational boundaries / NO-GO

No migration execution, permission provisioning, feature activation, runtime-token provisioning, operational manifest/DB invocation, real target activation, deployment/release, Technical Preview activation, Production activation, or updater activation occurred.

### Next position

Sprint147 bounded discovery from canonical post-Sprint146; no implementation objective or source envelope is preselected.

## Post-Sprint146 canonical reconciliation

- [x] Make Sprint146 workflow successor-compatible while preserving owned regression
- [x] Advance `PROJECT_MANIFEST.md` to Sprint146 engineering truth
- [x] Advance `README.md` to Sprint146 current status
- [x] Record Sprint146 closure in `CHANGELOG.md`
- [x] Advance `TASKS.md`
- [x] Advance `ROADMAP.md` to Sprint147 next position
- [x] Preserve mandatory sprint-description format

GitHub PR/CI state remains authoritative for the transient publication status of this reconciliation until it is squash merged.

## Next bounded engineering

### Sprint147 — bounded discovery next

Sprint147 is **not** considered started or complete merely because it is named here.

The next engineering activity must:

- identify the smallest non-duplicative remaining executable gap from canonical post-Sprint146;
- preserve Sprint130–Sprint146 ownership and historical regressions;
- freeze a bounded envelope before implementation;
- remain fail-closed and deny-by-default;
- keep all operational actions outside scope unless separately authorized;
- qualify exact head before merge;
- document Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position.

No Sprint147 implementation is preselected.

## Operational blockers / separate authority required

- [ ] Execute migration #27 — `NOT_EXECUTED`
- [ ] Provision Final Shift Close permission — `NONE`
- [ ] Activate Final Shift Close feature — `INACTIVE`
- [ ] Qualify and select a non-synthetic durable activation target — current selection `null`
- [ ] Perform real runtime-binding manifest materialization
- [ ] Perform real operational DB-binding attestation
- [ ] Provision an operational runtime control-plane token
- [ ] Grant deployment authority — `NOT_GRANTED`
- [ ] Authorize Technical Preview activation — `NOT_AUTHORIZED`
- [ ] Authorize Production activation — `NOT_AUTHORIZED`
- [ ] Activate updater — `INACTIVE`

Machine-readable operational authority remains in `ops/final-shift-close/STATE.json` and `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`.

## Maintenance rule

At every material sprint closure:

1. write the sprint description using the six mandatory description fields;
2. reconcile `PROJECT_MANIFEST.md` first;
3. reconcile README, TASKS, ROADMAP, and CHANGELOG;
4. preserve detailed evidence in `docs/SPRINT*.md`, contracts, workflows, and Git history;
5. never label source-published work as deployed or activated without operational evidence;
6. make the just-closed workflow successor-compatible.

Author by Lab | zefry
