# oneQay Roadmap

**Roadmap checkpoint:** post-Sprint160 canonical reconciliation
**Canonical engineering baseline:** `e6ef6e77d8a2d0dea16d7c17dde78bece904b78b`
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

## Horizon D — Product-readiness operational reporting — Sprint156 completed

- [x] Read-only POS operational sales reporting
- [x] Tenant + organization + outlet scope
- [x] Currency and currency-scale boundary preservation
- [x] Deny-by-default reporting authorization
- [x] Guarded Local/Test/CI delivery and Vue/Inertia dashboard
- [x] Executable reporting regression

## Horizon E — Operational cashier usability — Sprint157 completed

- [x] Scoped cashier workspace read models
- [x] Tenant/outlet catalog and exact-device active-shift readiness
- [x] Active positive-stock server catalog only
- [x] Existing `pos.sale.complete` authorization and mutation authority reuse
- [x] Currency/scale-safe cart and tender validation
- [x] Explicit no-auto-retry behavior
- [x] Vue/Inertia cashier workspace and executable regression

## Horizon F — Operational shift start — Sprint158 completed

- [x] Reuse canonical shift-opening and opening-cash mutation authorities
- [x] Exact tenant + organization + outlet + device start-state read model
- [x] Require existing shift-open and opening-cash permissions
- [x] Resumable shift required → opening cash required → ready workflow
- [x] Preserve partial-completion state without hidden retry
- [x] Vue/Inertia shift-start workspace and executable regression

## Horizon G — Operational sale correction — Sprint159 completed

- [x] Prove canonical full-sale void and full CASH refund authorities before adding UI source
- [x] Materialize exact tenant + organization + outlet correction read model
- [x] Preserve original sale device visibility and original-shift eligibility semantics
- [x] Reuse existing `pos.sale.void` and `pos.sale.refund` permissions deny-by-default
- [x] Reuse existing `pos.sales.void` and `pos.sales.cash-refund` mutation endpoints
- [x] Validate amount/currency/scale/tender/scope/evidence consistency fail-closed
- [x] Preserve explicit `COMPLETED → VOIDED → REFUNDED` CASH sequence
- [x] Preserve MANUAL_EXTERNAL external-settlement boundary
- [x] Avoid arbitrary client refund amount, composite mutation, and hidden retry
- [x] Add network-uncertainty lockout with authoritative refresh
- [x] Gate delivery to Local/Test/CI plus persistence/session/correction capabilities and explicit workspace arming
- [x] Add Vue/Inertia sale correction workspace
- [x] Add executable SQLite scope/permission/evidence/fail-closed regression
- [x] Qualify complete surfaced exact engineering head successfully
- [x] Obtain repository-native Product Owner merge authority
- [x] Squash merge engineering PR #736 at `e4e6a0f55bbb3b6fd4126df66f4815e281ad0dc4`

## Horizon H — Immutable sale history and receipt detail — Sprint160 completed

- [x] Prove canonical immutable sale header and line persistence exists before adding read-model source
- [x] Materialize bounded latest-50 history in exact tenant + organization + outlet scope
- [x] Add exact canonical `sale-<24 hex>` receipt lookup without foreign-scope existence disclosure
- [x] Reuse existing `pos.reporting.sales-summary.view` permission deny-by-default
- [x] Extend existing `PosOperationalReportingServiceProvider` rather than create a parallel/global provider
- [x] Use canonical `oneqay_pos_sale_lines` as historical line authority
- [x] Avoid mutable current catalog names as historical receipt evidence
- [x] Validate contiguous line numbering, quantity, unit-price multiplication, line totals, currency/scale, and sum-to-sale-total fail-closed
- [x] Validate void/refund evidence sequence, amount, tender, organization, and outlet scope fail-closed
- [x] Preserve legitimate legacy nullable shift binding evidence
- [x] Deliver atomic money as strings for browser precision safety
- [x] Gate history delivery to Local/Test/CI, durable persistence, reporting enablement, exact session controls, and explicit history arming
- [x] Add Vue/Inertia sale-history/detail workspace
- [x] Add executable SQLite scope/exact-lookup/receipt-integrity/correction-evidence regression
- [x] Qualify complete surfaced exact engineering head successfully
- [x] Obtain repository-native Product Owner merge authority
- [x] Squash merge engineering PR #738 at `e6ef6e77d8a2d0dea16d7c17dde78bece904b78b`

### Sprint160 evidence

- Parent canonical post-Sprint159 checkpoint: `d4931eb7822d844cf74b3a60e593c35b00b1cfce`.
- Final engineering head: `d159aa26c748c5a624f0f71fe6c135f0d4f36be9`.
- Complete surfaced exact-head PR-triggered matrix: successful.
- Sprint160 regression run `34847797273`: successful.
- M7.1 run `34847797253`: successful.
- Governance run `34847797404`: successful.
- PHP Foundation run `34847797433`: successful.
- Engineering envelope: 10 paths, SHA-256 `f60bd3698cbc28cfccdf8b79c446e5e138203246afdb16aaea1c5637aa327181`.
- Reconciliation envelope: six paths, SHA-256 `388c671587b1d0e21206260c5f0003fb215d606df494eaa528b1e6d839de7847`.

## Horizon I — Sprint161+ bounded engineering — next

Status: **BOUNDED DISCOVERY NEXT AFTER SPRINT160 CLOSURE**.

Selection rules:

1. prove a material non-duplicative P0/P1 gap exists before adding source;
2. inspect historical regressions, machine-readable contracts, and current source foundations before creating a new invariant owner;
3. prioritize security/data/tenant/auth/transaction/deployment blockers and business completeness over low-value abstraction;
4. recognize when the true blocker is external/operational and avoid inventing another source-only layer;
5. reuse existing canonical POS/domain/authorization/reporting/history contracts rather than duplicating authority;
6. consider genuine product gaps such as inventory/stock operational visibility, coherent POS navigation/workspace integration, or other missing business-completeness surfaces only after live source confirms absence;
7. freeze the smallest meaningful bounded source envelope;
8. stay fail-closed, deny-by-default, and tenant-isolated;
9. avoid target persistence, producer dispatch, migration execution, permission provisioning, activation, deployment, and allowlist widening unless separately authorized;
10. qualify exact head in CI before merge and reconcile canonical project-state documentation at closure.

No roadmap text pre-authorizes a specific Sprint161 implementation.

## Horizon J — Operational qualification — blocked / separate authority

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

## Horizon K — Technical Preview activation — NO-GO

Technical Preview activation remains `NOT_AUTHORIZED`.

## Horizon L — Production activation — NO-GO

Production activation remains `NOT_AUTHORIZED`.

## Horizon M — Updater/release activation — inactive

Updater activation remains `INACTIVE`. Release/deployment procedures do not establish authority or evidence that a release occurred.

## Roadmap maintenance rule

At every material sprint closure, update the canonical manifest and four root summaries, preserve detailed evidence in workflows/contracts/Git history, keep the just-closed preservation workflow successor-compatible, and never infer operational activation from source evidence.

Author by Lab | zefry
