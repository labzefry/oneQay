# oneQay Tasks

**Current engineering checkpoint:** post-Sprint134  
**Canonical engineering commit:** `185cbe9ddd4346b9d8c9e7ac7af283a20b9617bd`  
**Canonical status authority:** `PROJECT_MANIFEST.md`

This file is the current workboard. It deliberately separates **completed source engineering**, **next bounded engineering**, and **operational actions that remain unauthorized**.

## Completed through Sprint134

- [x] Core modular-monolith architecture and repository governance foundation
- [x] Tenant-context-first and deny-by-default authorization foundations
- [x] Versioned REST / correlation / idempotency governance foundations
- [x] POS shift/register opening foundation
- [x] POS sale completion, payment recording, and receipt evidence foundation
- [x] Tenant/outlet-scoped catalog preparation foundation
- [x] JRN-010 prerequisite expected-cash derivation work
- [x] Immutable sale-to-shift binding readiness/evidence work
- [x] Cash-variance source readiness and durable source foundation
- [x] Cash-variance adjudication/explanation evidence chain
- [x] Maker-checker / scoped reviewer authorization hardening
- [x] Final Shift Close migration #27 source materialization
- [x] Final Shift Close application-readiness contract chain
- [x] Runtime binding / manifest / DB-binding attestation readiness chain
- [x] Canonical runtime control-plane token policy — Sprint130
- [x] Canonical middleware positive-path regression — Sprint131
- [x] Canonical controller positive-path regression — Sprint132
- [x] Canonical controller fail-closed regression — Sprint133
- [x] Canonical control-plane delivery-gate registration regression — Sprint134
- [x] Sprint134 PR #684 squash merged
- [x] Sprint134 exact-head pull-request qualification — 18/18 successful
- [x] Sprint134 Product Owner merge-authority workflow successful

## Current documentation reconciliation

- [x] Detect stale root status documents still reporting post-Sprint48/post-Sprint54 state
- [x] Establish `PROJECT_MANIFEST.md` as canonical human-readable project-state authority
- [x] Reconcile `README.md` with post-Sprint134 engineering state
- [x] Reconcile `TASKS.md` current workboard
- [x] Reconcile `CHANGELOG.md` with material Sprint55–Sprint134 progress
- [x] Reconcile `ROADMAP.md` with completed horizon and next bounded horizon
- [x] Convert Sprint134 active full-envelope workflow lock into successor-compatible historical regression ownership
- [ ] Qualify the reconciliation delta in CI
- [ ] Publish the reconciliation through normal PR governance

## Next bounded engineering

### Sprint135 — not yet completed

The next engineering activity is **bounded discovery from canonical post-Sprint134**.

Sprint135 should:

- select only the smallest non-duplicative executable gap;
- preserve the Sprint130–Sprint134 control-plane regression chain;
- preserve historical POS/JRN regression evidence;
- remain fail-closed and deny-by-default;
- avoid operational adapter invocation unless separately authorized;
- maintain exact-head qualification and bounded source-envelope governance.

No specific Sprint135 implementation should be treated as selected until bounded discovery freezes the objective and source envelope.

## Operational blockers / separate authority required

These are **not engineering TODOs that may be silently executed**. They are lifecycle gates requiring their own authority and evidence.

- [ ] Execute migration #27 — current state `NOT_EXECUTED`
- [ ] Provision Final Shift Close permission — current state `NONE`
- [ ] Activate Final Shift Close feature — current state `INACTIVE`
- [ ] Qualify a non-synthetic durable activation target
- [ ] Select a durable activation target — current selection is `null`
- [ ] Perform real runtime-binding manifest materialization
- [ ] Perform real operational DB-binding attestation
- [ ] Grant deployment authority — current state `NOT_GRANTED`
- [ ] Authorize Technical Preview activation — current state `NOT_AUTHORIZED`
- [ ] Authorize Production activation — current state `NOT_AUTHORIZED`
- [ ] Activate updater — current state `INACTIVE`

The machine-readable operational gate is defined by `ops/final-shift-close/STATE.json` and `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`.

## Maintenance rule

At every material sprint closure:

1. reconcile `PROJECT_MANIFEST.md` first;
2. update README, TASKS, ROADMAP, and CHANGELOG only as summaries of that canonical state;
3. preserve detailed evidence in `docs/SPRINT*.md`, contracts, workflows, and Git history;
4. never label source-published work as deployed/activated without operational evidence;
5. never use a stale historical heading as the current project state.

Author by Lab | zefry
