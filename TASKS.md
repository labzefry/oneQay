# oneQay Tasks

**Current engineering checkpoint:** post-Sprint148 reconciliation
**Canonical engineering commit:** `7a07a3163842e60332ccd3e3780d4e970280d46c`
**Canonical status authority:** `PROJECT_MANIFEST.md`

This file separates **completed source engineering**, **next bounded engineering**, and **operational actions that remain unauthorized**.

## Completed through Sprint148

- [x] Core modular-monolith architecture and repository governance foundation
- [x] Tenant-context-first and deny-by-default authorization foundations
- [x] Versioned REST / correlation / idempotency governance foundations
- [x] POS shift/register, sale/payment/receipt, and catalog foundations
- [x] JRN-010 expected-cash, sale-to-shift, variance, explanation/adjudication, maker-checker, and reviewer-control chain
- [x] Final Shift Close migration #27 source materialization; execution remains unauthorized
- [x] Final Shift Close application/runtime readiness chain
- [x] Durable-runtime readiness and exact selected-target identity readiness — Sprint110–Sprint111
- [x] Source-only attestation producer/ingestion, target-selection persistence and binding readiness — Sprint113–Sprint117
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
- [x] Target-bound durable-runtime capability-evidence identity qualification — Sprint148
- [x] Sprint148 PR #714 squash merged
- [x] Sprint148 exact-head PR qualification — 33/33 successful
- [x] Sprint148 repository-native Product Owner merge-authority run `34756294306` successful
- [x] Sprint148 canonical engineering commit `7a07a3163842e60332ccd3e3780d4e970280d46c`
- [x] Sprint148 post-merge engineering NO-GO verification completed
- [x] Sprint116 historical workflow converted to successor-compatible regression after exact-head CI proved its envelope conflict

## Sprint148 description

### Purpose / Why

Sprint110 established canonical durable-runtime capability claims and Sprint111 bound qualified readiness to an exact selected target, but the four boolean claims did not themselves prove capability-specific evidence for that exact environment, runtime class, source commit, artifact, readiness attestation, and selection fingerprint.

### Objective / Gap

Require future capability evidence for authenticated configuration mutation, read-before/write/read-after verification, non-mutating health attestation, and verified rollback to be individually `VERIFIED`, digest-qualified, secret-free, and bound to the exact Sprint111 selected-target identity while activation authority remains `NOT_GRANTED`, feature state remains `INACTIVE`, and runtime allowlist change remains `NOT_IMPLEMENTED`.

### What changed

- Added `FinalShiftCloseDurableRuntimeCapabilityEvidence` as a pure target-bound evidence qualifier.
- Added Sprint148 executable positive and fail-closed regression coverage.
- Added `DURABLE_RUNTIME_CAPABILITY_EVIDENCE_BINDING_CONTRACT.json`.
- Extended post-selection downstream readiness with the new evidence prerequisite.
- Added Sprint148 exact-head workflow and detailed six-section description.
- Expanded the engineering envelope from six to seven paths only after CI proved historical Sprint116 required successor-compatibility conversion.

### Evidence / Qualification

- PR #714, merged.
- Final exact engineering head `eba8687297e7b82bf5adfcd770d86233139b0454`.
- 33/33 exact-head pull-request workflows successful.
- Repository-native Product Owner authority run `34756294306` successful.
- Canonical engineering commit `7a07a3163842e60332ccd3e3780d4e970280d46c`.
- Engineering envelope: seven paths.
- Engineering envelope SHA-256 `f7d9cfb173b54ac863cc70f10b9ae3df2f6715a4abdae0c7c32cbdb399bda5a6`.
- Reconciliation envelope: six paths; SHA-256 `5c315771e9777b9d5a5b428a206c7cec711e14a7851a83c685a5a5cfb378dfc5`.

### Operational boundaries / NO-GO

No real capability evidence, target persistence/activation, migration execution, permission provisioning, feature activation, runtime-token provisioning, runtime allowlist change, operational manifest/DB invocation, deployment/release, Technical Preview activation, Production activation, or updater activation occurred.

### Next position

After reconciliation is squash merged and verified, Sprint149 bounded discovery from canonical post-Sprint148; no implementation objective or source envelope is preselected.

## Post-Sprint148 canonical reconciliation

- [x] Prepare Sprint148 workflow successor-compatible historical conversion
- [x] Advance `PROJECT_MANIFEST.md` to Sprint148 engineering truth
- [x] Advance `README.md` to Sprint148 current status
- [x] Record Sprint148 closure in `CHANGELOG.md`
- [x] Advance `TASKS.md`
- [ ] Advance `ROADMAP.md` to Sprint149 next position
- [ ] Commit successor-compatible Sprint148 workflow conversion
- [ ] Open reconciliation PR with exact six-path envelope
- [ ] Exact-head reconciliation CI qualification
- [ ] Repository-native reconciliation Product Owner authority
- [ ] Final race and reconciliation squash merge
- [ ] Post-merge canonical verification and Sprint148 CLOSED declaration

GitHub PR/CI state remains authoritative for the transient publication status of this reconciliation until it is squash merged.

## Next bounded engineering

### Sprint149 — bounded discovery after Sprint148 closure

Sprint149 is **not** considered started or complete merely because it is named here.

After post-Sprint148 reconciliation closes, the next engineering activity must:

- identify the smallest material non-duplicative production-readiness gap from canonical post-Sprint148;
- prove the invariant is not already owned by historical Sprint110–Sprint148 regressions;
- prefer prerequisites that genuinely advance qualified isolated non-production durable-runtime readiness without operational mutation;
- freeze a bounded envelope before implementation;
- remain fail-closed and deny-by-default;
- keep all operational actions outside scope unless separately authorized;
- qualify exact head before merge;
- document Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position.

No Sprint149 implementation is preselected.

## Operational blockers / separate authority required

- [ ] Qualify/select and persist a real non-synthetic durable activation target — current selection `null`
- [ ] Produce real target-bound durable-runtime capability evidence — `NONE`
- [ ] Materialize/dispatch trusted capability-evidence producer — `NOT_IMPLEMENTED`
- [ ] Execute migration #27 — `NOT_EXECUTED`
- [ ] Provision Final Shift Close permission — `NONE`
- [ ] Activate Final Shift Close feature — `INACTIVE`
- [ ] Perform real runtime-binding manifest materialization
- [ ] Perform real operational DB-binding attestation
- [ ] Provision an operational runtime control-plane token
- [ ] Widen selected durable runtime class in runtime allowlist — `NOT_IMPLEMENTED`
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
