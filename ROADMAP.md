# oneQay Roadmap

**Roadmap checkpoint:** post-Sprint162 canonical reconciliation
**Canonical engineering baseline:** `332bcff11b40307d350c7ce5b3a6c08913c4251c`
**Current state authority:** `PROJECT_MANIFEST.md`

This roadmap describes sequencing and gates. It does **not** grant operational authority. Completed roadmap items are bounded repository objectives, not evidence of deployment or activation.

## Horizon A — Platform and repository foundation — completed

- [x] Modular Monolith First architecture
- [x] Clean Architecture / DDD boundaries
- [x] Tenant-context-first isolation and deny-by-default authorization
- [x] First-party session and privileged authentication foundations
- [x] Versioned REST governance, stable error envelope, correlation and idempotency conventions
- [x] CI/governance, exact-head qualification, historical-regression and merge-authorization controls
- [x] Transactional-outbox readiness and infrastructure-adapter evolution boundary

## Horizon B — Bounded POS foundations — completed as source engineering

- [x] Sale/payment/receipt evidence foundations
- [x] Tenant/outlet-scoped catalog preparation
- [x] Shift/register opening foundation
- [x] Inventory baseline and durable stock mutation authority
- [x] Full-sale void and full CASH refund mutation foundations
- [x] Durable idempotency and scoped authorization preservation
- [x] Cash variance/adjudication/reviewer foundations

## Horizon C — Final Shift Close source/readiness chain — source-ready through Sprint155

Sprint88–Sprint155 established migration source materialization, runtime dependency/readiness, selected-target identity, attestation/selection binding, DB binding/control-plane hardening, capability/dependency evidence foundations, permission-provisioning binding, and feature-activation source foundations.

Operational execution remains separately gated. Migration #27 remains `NOT_EXECUTED`, selected target remains `null`, permissions remain `NONE`, and feature activation remains `INACTIVE`.

## Horizon D — Product-readiness POS operational surfaces — Sprint156–Sprint162 completed

- [x] Sprint156 — read-only operational sales reporting
- [x] Sprint157 — cashier sale-entry workspace over canonical sale authority
- [x] Sprint158 — resumable exact-device shift-start workspace
- [x] Sprint159 — operational sale correction workspace over void/refund authorities
- [x] Sprint160 — immutable sale history and receipt-detail workspace
- [x] Sprint161 — operational catalog and opening-inventory setup workspace
- [x] Sprint162 — guarded POS operations hub over existing delivered route and permission owners

## Horizon E — Guarded POS operations navigation — Sprint162 completed

- [x] Prove there was no existing shared frontend layout, POS home route, or central navigation owner before adding source
- [x] Prove no canonical restock/stock-adjustment application authority existed to expose safely instead
- [x] Add one read-only `/pos` operational entry point without creating mutation or persistence authority
- [x] Preserve exact verified tenant, organization, outlet, and device scope
- [x] Reuse existing POS permissions only; introduce no hub-specific permission
- [x] Preserve Shift Start authorization as `pos.shift.open` AND `pos.shift.opening-cash.record`
- [x] Preserve Cashier authorization as `pos.sale.complete`
- [x] Preserve Sales Summary / Sale History authorization as `pos.reporting.sales-summary.view`
- [x] Preserve Corrections authorization as `pos.sale.void` OR `pos.sale.refund`
- [x] Preserve Catalog & Opening Stock authorization as `pos.catalog.prepare` AND `pos.inventory.baseline`
- [x] Preserve Shift Close authorization as `pos.shift.close`
- [x] Require `Route::has()` in addition to permission before exposing each destination
- [x] Keep every target workspace's own authorization gate intact
- [x] Deny hub access when current context has no qualifying POS capability
- [x] Add `ONEQAY_POS_OPERATIONS_HUB_ENABLED`, default false
- [x] Gate delivery to Local/Test/CI + persistence + exact session controls + explicit hub arming
- [x] Register through bounded child provider rather than global provider registry
- [x] Add responsive Vue/Inertia read-only operations UI
- [x] Add executable permission-composition and route-discovery regression
- [x] Qualify exact engineering head successfully with no correction commit required
- [x] Obtain repository-native Product Owner merge authority
- [x] Squash merge engineering PR #742 at `332bcff11b40307d350c7ce5b3a6c08913c4251c`

### Sprint162 evidence

- Parent canonical post-Sprint161 checkpoint: `e0330761a325a5f9e5faa4c5c0089b6868f979c8`.
- Final engineering head: `2d75efbdb4b3eb2b98ad7973866fa6576b1ffd79`.
- Sprint162 regression run `34857221294`: successful.
- M7.1 run `34857221166`: successful.
- Governance Required Checks run `34857221221`: successful.
- PHP Foundation Regression run `34857220990`: successful.
- Sprint156–Sprint161 and all other surfaced final exact-head PR-triggered runs: successful.
- Engineering envelope: 9 paths, SHA-256 `afadd8577d794fffc100b8ab98d77dfc55599ead9d32963c1ef84ee8a086afd2`.
- Reconciliation envelope: six paths, SHA-256 `66500e314da09a14dbc35624dc0e0468c6ca3707bcf0a91261b4f850b207b734`.

## Horizon F — Sprint163+ bounded engineering — next

Status: **BOUNDED DISCOVERY NEXT AFTER SPRINT162 CLOSURE**.

Selection rules:

1. prove a material non-duplicative P0/P1 gap exists before adding source;
2. inspect current source, historical regressions, and machine-readable contracts before creating a new invariant owner;
3. prioritize security/data/tenant/auth/transaction/deployment blockers and business completeness over low-value abstraction;
4. recognize when the true blocker is external/operational and avoid inventing another source-only layer;
5. reuse existing canonical POS/domain/authorization contracts rather than duplicating authority;
6. treat the Sprint162 `/pos` hub as navigation only, never as an authorization bypass, capability activator, or new permission owner;
7. distinguish one-time inventory baseline from any future restock/adjustment need; Sprint161/Sprint162 grant no stock-adjustment authority;
8. freeze the smallest meaningful bounded source envelope;
9. stay fail-closed, deny-by-default, and tenant-isolated;
10. avoid target persistence, producer dispatch, migration execution, permission provisioning, activation, deployment, and allowlist widening unless separately authorized;
11. qualify exact head in CI before merge and reconcile canonical project-state documentation at closure.

No roadmap text pre-authorizes a specific Sprint163 objective or implementation.

## Horizon G — Operational qualification — blocked / separate authority

- [ ] Qualified non-synthetic durable runtime target — missing
- [ ] Selected target — currently `null`
- [ ] Real selected-target DB-binding evidence — `NONE`
- [ ] Migration #27 execution — `NOT_EXECUTED`
- [ ] Selected-target-bound permission provisioning — `NONE`
- [ ] Trusted capability-evidence producer dispatch — `NOT_PERFORMED`
- [ ] Real target-bound capability evidence — `NONE`
- [ ] Trusted dependency-envelope evidence producer dispatch — `NOT_PERFORMED`
- [ ] Real target-bound dependency-envelope evidence — `NONE`
- [ ] Final Shift Close runtime allowlist widening for a selected durable runtime — not authorized
- [ ] Feature activation — `INACTIVE`
- [ ] Deployment authority — `NOT_GRANTED`

Current target selection remains `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`.

## Horizon H — Technical Preview activation — NO-GO

Technical Preview activation remains `NOT_AUTHORIZED`.

## Horizon I — Production activation — NO-GO

Production activation remains `NOT_AUTHORIZED`.

## Horizon J — Updater/release activation — inactive

Updater activation remains `INACTIVE`. Release/deployment procedures do not establish authority or evidence that a release occurred.

## Roadmap maintenance rule

At every material sprint closure, update the canonical manifest and four root summaries, preserve detailed evidence in workflows/contracts/Git history, keep the just-closed preservation workflow successor-compatible, and never infer operational activation from source evidence.

Author by Lab | zefry
