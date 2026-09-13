# oneQay Tasks

**Current engineering checkpoint:** post-Sprint143
**Canonical engineering commit:** `b307d925400e9707c137f75bcfa3823a182fb84f`
**Canonical status authority:** `PROJECT_MANIFEST.md`

This file is the current workboard. It separates **completed source engineering**, **next bounded engineering**, and **operational actions that remain unauthorized**.

## Completed through Sprint143

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
- [x] Canonical cross-provider positive registration metadata regression, including DB-attestation `GET,HEAD` ownership — Sprint135
- [x] Canonical authenticated HTTP positive-path regression — Sprint136
- [x] Canonical authenticated HTTP fail-closed regression — Sprint137
- [x] Canonical authenticated HTTP throttle enforcement regression — Sprint138
- [x] Canonical auth-before-throttle hardening and same-IP budget-isolation regression — Sprint139
- [x] Canonical direct-middleware auth-rejection response hardening — Sprint140
- [x] Canonical registered-route HTTP-kernel auth-rejection response propagation — Sprint141
- [x] Canonical authenticated throttle-rejection response privacy/security hardening — Sprint142
- [x] Canonical DB-attestation HEAD throttle-rejection privacy/security parity hardening — Sprint143
- [x] DB-attestation remains `GET,HEAD` with exact `throttle:2,1`
- [x] Two same-IP authenticated HEAD requests succeed; third HEAD remains HTTP `429`
- [x] Third HEAD performs no third synthetic database identity read
- [x] HEAD throttle rejection preserves empty body, `no-store, private`, `Pragma: no-cache`, `nosniff`, robot exclusion, and Laravel rate-limit metadata
- [x] Materialization POST and DB-attestation GET behavior preserved
- [x] Sprint135 and Sprint138–Sprint142 executable ownership preserved
- [x] Sprint143 PR #703 squash merged
- [x] Sprint143 exact-head pull-request qualification — 27/27 successful
- [x] Sprint143 Product Owner merge-authority workflow run `34746609129` successful
- [x] Sprint143 post-merge engineering verification completed

## Post-Sprint143 canonical reconciliation

- [x] Advance `PROJECT_MANIFEST.md` to Sprint143 engineering truth
- [x] Advance `README.md` to Sprint143 current status
- [x] Record Sprint143 closure in `CHANGELOG.md`
- [x] Advance `TASKS.md` current workboard
- [x] Advance `ROADMAP.md` completed engineering horizon and Sprint144 next position
- [x] Convert Sprint143 full-envelope workflow ownership to successor-compatible historical regression ownership

GitHub PR/CI state remains authoritative for the transient publication status of this bounded reconciliation.

## Next bounded engineering

### Sprint144 — bounded discovery next

Sprint144 has **not** been selected, started, or completed merely because it is named here.

The next engineering activity must:

- discover the smallest remaining non-duplicative executable gap from canonical post-Sprint143;
- preserve Sprint130–Sprint143 control-plane ownership and historical regressions;
- preserve existing POS/JRN evidence;
- remain fail-closed and deny-by-default;
- avoid operational adapter invocation or runtime-token provisioning unless separately authorized;
- freeze a bounded source envelope before implementation;
- qualify the exact PR head before merge.

No specific Sprint144 implementation is canonical until bounded discovery freezes the objective and envelope.

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
