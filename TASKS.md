# oneQay Tasks

**Current engineering checkpoint:** post-Sprint147
**Canonical engineering commit:** `50a3ba99b8f5628381d9df63f4f6a0e1020d550a`
**Canonical status authority:** `PROJECT_MANIFEST.md`

This file separates **completed source engineering**, **next bounded engineering**, and **operational actions that remain unauthorized**.

## Completed through Sprint147

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
- [x] Canonical throttle-rejection metadata identity ownership hardening — Sprint147
- [x] Sprint147 PR #711 squash merged
- [x] Sprint147 exact-head PR qualification — 31/31 successful
- [x] Sprint147 Product Owner merge-authority run `34752002084` successful
- [x] Sprint147 canonical engineering commit `50a3ba99b8f5628381d9df63f4f6a0e1020d550a`
- [x] Sprint147 post-merge engineering NO-GO verification completed

## Sprint147 description

### Purpose / Why

Sprint146 secured throttle-response ownership using canonical request identity plus exact per-route rate-limit ceilings, but the hardener still did not require the complete framework throttle-rejection metadata shape before rewriting a `429` response.

### Objective / Gap

Require the previously qualified request identity and exact canonical limit plus `X-RateLimit-Remaining: 0`, decimal non-empty `Retry-After`, and decimal non-empty `X-RateLimit-Reset`.

### What changed

- Added canonical throttle-rejection metadata validation to the hardener.
- Preserved explicit framework metadata-presence guards.
- Added Sprint147 executable regression coverage for materialization POST and DB-attestation GET/HEAD.
- Made the historical Sprint144 named-route fixture successor-compatible with the canonical metadata shape proved by Sprint142.
- Added Sprint147 contract, workflow, and detailed six-section description.

### Evidence / Qualification

- PR #711, merged.
- Final authorized engineering head `513d95dbb4d5ab95a8f6c3282f8911cf339a9697`.
- 31/31 exact-head workflows successful.
- Product Owner authority run `34752002084` successful.
- Canonical engineering commit `50a3ba99b8f5628381d9df63f4f6a0e1020d550a`.
- Engineering envelope: six paths.
- Engineering envelope SHA-256 `ffd176da808eda16fccdf0375fcae2fd5bcc1cfc4b931aca6492ca31eb9b1d40`.

### Operational boundaries / NO-GO

No migration execution, permission provisioning, feature activation, runtime-token provisioning, operational manifest/DB invocation, real target activation, deployment/release, Technical Preview activation, Production activation, or updater activation occurred.

### Next position

Sprint148 bounded discovery from canonical post-Sprint147; no implementation objective or source envelope is preselected.

## Post-Sprint147 canonical reconciliation

- [x] Make Sprint147 workflow successor-compatible while preserving owned regression
- [x] Advance `PROJECT_MANIFEST.md` to Sprint147 engineering truth
- [x] Advance `README.md` to Sprint147 current status
- [x] Record Sprint147 closure in `CHANGELOG.md`
- [x] Advance `TASKS.md`
- [x] Advance `ROADMAP.md` to Sprint148 next position
- [x] Preserve mandatory sprint-description format

GitHub PR/CI state remains authoritative for the transient publication status of this reconciliation until it is squash merged.

## Next bounded engineering

### Sprint148 — bounded discovery next

Sprint148 is **not** considered started or complete merely because it is named here.

The next engineering activity must:

- identify the smallest non-duplicative remaining executable gap from canonical post-Sprint147;
- preserve Sprint130–Sprint147 ownership and historical regressions;
- freeze a bounded envelope before implementation;
- remain fail-closed and deny-by-default;
- keep all operational actions outside scope unless separately authorized;
- qualify exact head before merge;
- document Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position.

No Sprint148 implementation is preselected.

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
