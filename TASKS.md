# oneQay Tasks

**Current engineering checkpoint:** post-Sprint138
**Canonical engineering commit:** `7f562ea48a0255b7e9803f6b268bd00a7e3b3dcf`
**Canonical status authority:** `PROJECT_MANIFEST.md`

This file is the current workboard. It separates **completed source engineering**, **next bounded engineering**, and **operational actions that remain unauthorized**.

## Completed through Sprint138

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
- [x] Materialization first request allowed; second request throttled with HTTP 429 and writer remains at one invocation
- [x] DB attestation first two requests allowed; third request throttled with HTTP 429 and synthetic reader remains at two invocations
- [x] Sprint138 PR #692 squash merged
- [x] Sprint138 exact-head pull-request qualification — 22/22 successful
- [x] Sprint138 Product Owner merge-authority workflow run `34701268872` successful
- [x] Sprint138 post-merge operational NO-GO verification

## Post-Sprint138 documentation reconciliation

- [x] Advance `PROJECT_MANIFEST.md` to Sprint138 engineering truth
- [x] Advance `README.md` to Sprint138 current status
- [x] Record Sprint138 closure in `CHANGELOG.md`
- [x] Advance `TASKS.md` current workboard
- [x] Advance `ROADMAP.md` completed engineering horizon and Sprint139 next position
- [x] Convert Sprint138 full-envelope workflow ownership to successor-compatible historical regression ownership

GitHub PR/CI state remains authoritative for the transient publication status of this bounded reconciliation.

## Next bounded engineering

### Sprint139 — bounded discovery next

Sprint139 has **not** been selected, started, or completed merely because it is named here.

The next engineering activity must:

- discover the smallest remaining non-duplicative executable gap from canonical post-Sprint138;
- preserve Sprint130–Sprint138 control-plane ownership and historical regressions;
- preserve existing POS/JRN evidence;
- remain fail-closed and deny-by-default;
- avoid operational adapter invocation or runtime-token provisioning unless separately authorized;
- freeze a bounded source envelope before implementation;
- qualify the exact PR head before merge.

No specific Sprint139 implementation is canonical until bounded discovery freezes the objective and envelope.

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
