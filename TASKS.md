# oneQay Tasks

**Current engineering checkpoint:** Sprint162 closed
**Canonical engineering commit:** `332bcff11b40307d350c7ce5b3a6c08913c4251c`
**Canonical status authority:** `PROJECT_MANIFEST.md`

This file separates completed source engineering, next bounded engineering, and operational actions that remain separately gated.

## Completed through Sprint162

- [x] Core modular-monolith architecture and repository governance foundation
- [x] Tenant-context-first and deny-by-default authorization foundations
- [x] Versioned REST / correlation / idempotency governance foundations
- [x] POS shift/register, sale/payment/receipt, catalog, inventory, void/refund, cash-variance, adjudication, and reviewer-control foundations
- [x] Final Shift Close source/readiness chain through Sprint155 without operational activation
- [x] Read-only POS operational sales reporting — Sprint156
- [x] Operational POS cashier sale-entry workspace — Sprint157
- [x] Operational POS shift-start workspace — Sprint158
- [x] Operational POS sale correction workspace — Sprint159
- [x] Immutable POS sale-history and receipt-detail workspace — Sprint160
- [x] Operational POS catalog and opening-inventory setup workspace — Sprint161
- [x] Guarded POS operations hub — Sprint162
- [x] Verified absence of shared frontend layout/POS home navigation owner before Sprint162 implementation
- [x] Verified no canonical restock/stock-adjustment application authority was available to expose safely
- [x] Reused existing POS permissions only; no hub-specific permission created
- [x] Shift Start access composed as open-shift AND opening-cash
- [x] Cashier access reuses complete-sale
- [x] Reporting/history access reuses `pos.reporting.sales-summary.view`
- [x] Corrections access composed as void OR refund
- [x] Catalog/opening-stock access composed as catalog prepare AND inventory baseline
- [x] Shift Close access reuses `pos.shift.close`
- [x] Every visible destination requires both authorization and delivered named-route existence
- [x] Target workspaces retain independent authorization
- [x] `/pos` / `pos.operations.hub` delivered by child provider, not global provider registry
- [x] `ONEQAY_POS_OPERATIONS_HUB_ENABLED` default false
- [x] Local/Test/CI + persistence + exact-session delivery gate
- [x] Read-only responsive Vue/Inertia hub
- [x] Executable permission-composition and route-discovery regression
- [x] Exact engineering head `2d75efbdb4b3eb2b98ad7973866fa6576b1ffd79` fully qualified
- [x] Sprint162 regression run `34857221294` successful
- [x] M7.1, Governance, PHP Foundation, Sprint156–Sprint161, and surfaced historical matrix successful
- [x] Repository-native exact-head Product Owner merge authority successful
- [x] Sprint162 engineering PR #742 squash merged
- [x] Sprint162 engineering squash `332bcff11b40307d350c7ce5b3a6c08913c4251c`
- [x] Engineering merge verified as exactly one commit / 9 paths over post-Sprint161 canonical main
- [x] Post-engineering machine-readable operational NO-GO verification completed
- [x] Sprint162 workflow reconciled to successor-compatible historical regression ownership
- [x] Sprint162 canonical six-path reconciliation materialized

## Sprint162 engineering evidence

- Objective: `POS_OPERATIONS_HUB`
- Parent canonical checkpoint: `e0330761a325a5f9e5faa4c5c0089b6868f979c8`
- Final engineering head: `2d75efbdb4b3eb2b98ad7973866fa6576b1ffd79`
- Engineering PR: #742
- Engineering envelope: 9 paths
- Engineering envelope SHA-256: `afadd8577d794fffc100b8ab98d77dfc55599ead9d32963c1ef84ee8a086afd2`
- Sprint162 regression run: `34857221294`
- Engineering squash: `332bcff11b40307d350c7ce5b3a6c08913c4251c`
- Reconciliation envelope: six paths
- Reconciliation envelope SHA-256: `66500e314da09a14dbc35624dc0e0468c6ca3707bcf0a91261b4f850b207b734`
- Operational mutation: `NOT_PERFORMED`
- Final Shift Close runtime allowlist: Local/Test/CI only
- Migration #27: `NOT_EXECUTED`
- Permission provisioning: `NONE`
- Feature activation: `INACTIVE`

## Next bounded engineering

### Sprint163 — bounded discovery after Sprint162 closure

The next engineering activity must:

- identify the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap from canonical post-Sprint162;
- inspect current source/contracts/regressions before creating a new invariant owner;
- prefer product value over source-only abstraction where external operational prerequisites remain the true blocker;
- reuse canonical POS/domain/authorization owners rather than duplicating them;
- treat `/pos` as navigation only, never as an authorization bypass or capability activator;
- never infer a stock-adjustment/restock mutation authority from the one-time inventory baseline;
- freeze the smallest meaningful bounded source envelope before implementation;
- remain fail-closed, deny-by-default, tenant-isolated, and exact-context scoped;
- qualify exact head before merge;
- preserve the six-section sprint documentation rule.

No Sprint163 implementation is preselected.

## Operational blockers / separate authority required

- [ ] Qualify/select and persist a real non-synthetic durable activation target — current selection `null`
- [ ] Produce real selected-target DB-binding evidence — `NONE`
- [ ] Execute migration #27 — `NOT_EXECUTED`
- [ ] Execute selected-target-bound permission provisioning — `NONE`
- [ ] Dispatch trusted capability-evidence producer — `NOT_PERFORMED`
- [ ] Produce real target-bound durable-runtime capability evidence — `NONE`
- [ ] Dispatch trusted dependency-envelope evidence producer — `NOT_PERFORMED`
- [ ] Produce real target-bound dependency-envelope evidence — `NONE`
- [ ] Widen selected durable runtime class in Final Shift Close runtime allowlist — not authorized
- [ ] Execute Final Shift Close feature activation — `INACTIVE`
- [ ] Grant deployment authority — `NOT_GRANTED`
- [ ] Authorize Technical Preview activation — `NOT_AUTHORIZED`
- [ ] Authorize Production activation — `NOT_AUTHORIZED`
- [ ] Activate updater — `INACTIVE`

Machine-readable operational authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

## Maintenance rule

At every material sprint closure: reconcile `PROJECT_MANIFEST.md`, README, TASKS, ROADMAP, and CHANGELOG; preserve detailed evidence in workflows/contracts/Git history; never label source-published work as deployed or activated without operational evidence; and keep preservation workflows successor-compatible.

Author by Lab | zefry
