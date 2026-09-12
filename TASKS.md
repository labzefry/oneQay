# oneQay Tasks

**Current engineering checkpoint:** post-Sprint140
**Canonical engineering commit:** `446f9ff80f646d2e77d0885b887da58a37994d28`
**Canonical status authority:** `PROJECT_MANIFEST.md`

This file is the current workboard. It separates **completed source engineering**, **next bounded engineering**, and **operational actions that remain unauthorized**.

## Completed through Sprint140

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
- [x] Canonical cross-provider auth-rejection response privacy/security hardening — Sprint140
- [x] Materialization invalid expected token remains 503; missing/malformed/mismatched bearer remains 401
- [x] DB-attestation rejection remains cloaked 404 for invalid expected token and missing/malformed/mismatched bearer
- [x] Auth-rejection responses are explicit empty-body, `no-store, private`, `Pragma: no-cache`, `nosniff`, and robot-excluded
- [x] Matching bearer still reaches the next middleware stage; token policy, route registration, middleware ordering, and throttle limits remain unchanged
- [x] Sprint126/Sprint130/Sprint131 historical workflow representation checks reconciled without weakening their executable ownership
- [x] Sprint140 PR #697 squash merged
- [x] Sprint140 exact-head pull-request qualification — 25/25 successful
- [x] Sprint140 Product Owner merge-authority workflow run `34707917274` successful
- [x] Sprint140 post-merge engineering verification completed

## Post-Sprint140 canonical reconciliation

- [x] Advance `PROJECT_MANIFEST.md` to Sprint140 engineering truth
- [x] Advance `README.md` to Sprint140 current status
- [x] Record Sprint140 closure in `CHANGELOG.md`
- [x] Advance `TASKS.md` current workboard
- [x] Advance `ROADMAP.md` completed engineering horizon and Sprint141 next position
- [x] Convert Sprint140 full-envelope workflow ownership to successor-compatible historical regression ownership

GitHub PR/CI state remains authoritative for the transient publication status of this bounded reconciliation.

## Next bounded engineering

### Sprint141 — bounded discovery next

Sprint141 has **not** been selected, started, or completed merely because it is named here.

The next engineering activity must:

- discover the smallest remaining non-duplicative executable gap from canonical post-Sprint140;
- preserve Sprint130–Sprint140 control-plane ownership and historical regressions;
- preserve existing POS/JRN evidence;
- remain fail-closed and deny-by-default;
- avoid operational adapter invocation or runtime-token provisioning unless separately authorized;
- freeze a bounded source envelope before implementation;
- qualify the exact PR head before merge.

No specific Sprint141 implementation is canonical until bounded discovery freezes the objective and envelope.

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
