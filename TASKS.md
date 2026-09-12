# oneQay Tasks

**Current engineering checkpoint:** post-Sprint141
**Canonical engineering commit:** `81d4d19c61e99746495bec804c0e7d8a9778a257`
**Canonical status authority:** `PROJECT_MANIFEST.md`

This file is the current workboard. It separates **completed source engineering**, **next bounded engineering**, and **operational actions that remain unauthorized**.

## Completed through Sprint141

- [x] Core modular-monolith architecture and repository governance foundation
- [x] Tenant-context-first and deny-by-default authorization foundations
- [x] Versioned REST / correlation / idempotency governance foundations
- [x] POS shift/register opening foundation
- [x] POS sale completion, payment recording, and receipt evidence foundation
- [x] Tenant/outlet-scoped catalog preparation foundation
- [x] JRN-010 expected-cash and immutable sale-to-shift binding foundations
- [x] Cash-variance source, explanation/adjudication, maker-checker, and reviewer authorization chain
- [x] Final Shift Close migration #27 source materialization
- [x] Final Shift Close application/runtime readiness chain
- [x] Runtime binding / manifest / DB-binding attestation readiness chain
- [x] Canonical runtime control-plane token policy — Sprint130
- [x] Canonical middleware positive-path regression — Sprint131
- [x] Canonical controller positive-path regression — Sprint132
- [x] Canonical controller fail-closed regression — Sprint133
- [x] Canonical control-plane delivery-gate registration regression — Sprint134
- [x] Canonical cross-provider positive registration metadata regression — Sprint135
- [x] Canonical authenticated HTTP route → middleware → controller positive-path regression — Sprint136
- [x] Canonical authenticated HTTP route → middleware → controller fail-closed regression — Sprint137
- [x] Canonical authenticated HTTP throttle enforcement regression — Sprint138
- [x] Canonical auth-before-throttle hardening and same-IP budget-isolation regression — Sprint139
- [x] Canonical cross-provider direct-middleware auth-rejection response privacy/security hardening — Sprint140
- [x] Canonical registered-route HTTP-kernel auth-rejection response propagation and inertness regression — Sprint141
- [x] Materialization missing/malformed/mismatched bearer remains HTTP 401 through the real registered route and HTTP kernel
- [x] DB-attestation missing/malformed/mismatched bearer remains cloaked HTTP 404 through the real registered route and HTTP kernel
- [x] Composed HTTP rejections remain empty-body, `no-store, private`, `Pragma: no-cache`, `nosniff`, and robot-excluded without bearer/internal-path reflection
- [x] Sprint141 rejected requests leave both controllers, manifest writer/materializer, DB identity reader, and DB-attestation service unresolved
- [x] Sprint139 auth-before-throttle and Sprint140 direct-middleware response-hardening ownership preserved
- [x] Sprint141 PR #699 squash merged
- [x] Sprint141 exact-head pull-request qualification — 25/25 successful
- [x] Sprint141 Product Owner merge-authority workflow run `34709040922` successful
- [x] Sprint141 post-merge engineering verification completed

## Post-Sprint141 canonical reconciliation

- [x] Advance `PROJECT_MANIFEST.md` to Sprint141 engineering truth
- [x] Advance `README.md` to Sprint141 current status
- [x] Record Sprint141 closure in `CHANGELOG.md`
- [x] Advance `TASKS.md` current workboard
- [x] Advance `ROADMAP.md` completed engineering horizon and Sprint142 next position
- [x] Convert Sprint141 full-envelope workflow ownership to successor-compatible historical regression ownership

GitHub PR/CI state remains authoritative for the transient publication status of this bounded reconciliation.

## Next bounded engineering

### Sprint142 — bounded discovery next

Sprint142 has **not** been selected, started, or completed merely because it is named here.

The next engineering activity must:

- discover the smallest remaining non-duplicative executable gap from canonical post-Sprint141;
- preserve Sprint130–Sprint141 control-plane ownership and historical regressions;
- preserve existing POS/JRN evidence;
- remain fail-closed and deny-by-default;
- avoid operational adapter invocation or runtime-token provisioning unless separately authorized;
- freeze a bounded source envelope before implementation;
- qualify the exact PR head before merge.

No specific Sprint142 implementation is canonical until bounded discovery freezes the objective and envelope.

## Operational blockers / separate authority required

These are lifecycle gates, not engineering TODOs that may be silently executed.

- [ ] Execute migration #27 — current state `NOT_EXECUTED`
- [ ] Provision Final Shift Close permission — current state `NONE`
- [ ] Activate Final Shift Close feature — current state `INACTIVE`
- [ ] Qualify a non-synthetic durable activation target
- [ ] Select a durable activation target — current selection `null`
- [ ] Perform real runtime-binding manifest materialization
- [ ] Perform real operational DB-binding attestation
- [ ] Provision an operational runtime control-plane token
- [ ] Grant deployment authority — current state `NOT_GRANTED`
- [ ] Authorize Technical Preview activation — current state `NOT_AUTHORIZED`
- [ ] Authorize Production activation — current state `NOT_AUTHORIZED`
- [ ] Activate updater — current state `INACTIVE`

Machine-readable operational authority remains in `ops/final-shift-close/STATE.json` and `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`.

## Maintenance rule

At every material sprint closure:

1. reconcile `PROJECT_MANIFEST.md` first;
2. update README, TASKS, ROADMAP, and CHANGELOG as summaries of the same canonical state;
3. preserve detailed evidence in `docs/SPRINT*.md`, contracts, workflows, and Git history;
4. never label source-published work as deployed or activated without operational evidence;
5. make the just-closed active sprint workflow successor-compatible before the next PR depends on it.

Author by Lab | zefry
