# oneQay Tasks

**Current engineering checkpoint:** post-Sprint145
**Canonical engineering commit:** `6d4fc06ac1166d15d8598d2a6d39d594f2493767`
**Canonical status authority:** `PROJECT_MANIFEST.md`

This file separates **completed source engineering**, **next bounded engineering**, and **operational actions that remain unauthorized**.

## Completed through Sprint145

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
- [x] Sprint145 PR #707 squash merged
- [x] Sprint145 exact-head PR qualification — 29/29 successful
- [x] Sprint145 Product Owner merge-authority run `34749677796` successful
- [x] Sprint145 canonical engineering commit `6d4fc06ac1166d15d8598d2a6d39d594f2493767`
- [x] Sprint145 post-merge engineering NO-GO verification completed

## Sprint145 description

### Purpose / Why

Close the ownership ambiguity left after Sprint144: route name + method + path were canonical, but controller-action identity was not yet required by the throttle-response hardener.

### Objective / Gap

Require canonical route name + canonical controller action + exact method + exact path before Final Shift Close throttle-response rewriting is owned.

### What changed

- Hardened action-identity ownership in the throttle-response middleware.
- Added a Sprint145 executable regression.
- Made the Sprint144 canonical positive fixture successor-compatible with Laravel controller metadata.

### Evidence / Qualification

- PR #707, merged.
- Final authorized engineering head `eeb93032fb8611e031d207ce95c1825dea7e2f2d`.
- 29/29 exact-head workflows successful.
- Product Owner authority run `34749677796` successful.
- Engineering envelope SHA-256 `ed1a67c7a7b89e26cd4c3ade350132b8eca7c4e2142f76d9b69495ac0ba2fad2`.

### Operational boundaries / NO-GO

No migration execution, permission provisioning, runtime-token provisioning, real target activation, deployment/release, Technical Preview activation, Production activation, or updater activation occurred.

## Post-Sprint145 canonical reconciliation

- [x] Make Sprint145 workflow successor-compatible while preserving owned regression
- [x] Advance `PROJECT_MANIFEST.md` to Sprint145 engineering truth
- [x] Advance `README.md` to Sprint145 current status
- [x] Record Sprint145 closure in `CHANGELOG.md`
- [x] Advance `TASKS.md`
- [x] Advance `ROADMAP.md` to Sprint146 next position
- [x] Establish mandatory sprint-description format for future sprint documentation

GitHub PR/CI state remains authoritative for the transient publication status of this reconciliation.

## Next bounded engineering

### Sprint146 — bounded discovery next

Sprint146 is **not** considered started or complete merely because it is named here.

The next engineering activity must:

- identify the smallest non-duplicative remaining executable gap from canonical post-Sprint145;
- preserve Sprint130–Sprint145 ownership and historical regressions;
- freeze a bounded envelope before implementation;
- remain fail-closed and deny-by-default;
- keep all operational actions outside scope unless separately authorized;
- qualify exact head before merge;
- document Purpose / Why, Objective / Gap, What changed, Evidence, NO-GO boundaries, and Next position.

No Sprint146 implementation is preselected.

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
