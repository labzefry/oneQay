# oneQay Roadmap

**Roadmap checkpoint:** post-Sprint158 canonical reconciliation
**Canonical engineering baseline:** `d6eb8f7f359584130deea9ec0ed3572add3c05aa`
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
- [x] Opening-cash evidence foundation
- [x] Inventory baseline and durable stock mutation authority
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
- [x] Historical successor-compatible regression preservation
- [x] Engineering PR #730 squash merged at `259cc00037ee0d3fb909cbcf2c87d39ffb26f9b9`

## Horizon E — Operational cashier usability — Sprint157 completed

- [x] Prove canonical sale/catalog/stock/shift authority existed before adding UI source
- [x] Materialize scoped cashier workspace read models
- [x] Bind cashier catalog to tenant/outlet and exact-device active-shift readiness
- [x] Expose only active positive-stock server catalog items
- [x] Reuse existing deny-by-default `pos.sale.complete` authorization
- [x] Preserve canonical `/pos/sales` / `CompleteSale` mutation authority
- [x] Reject mixed currency/scale cart state
- [x] Validate CASH and MANUAL_EXTERNAL tender input while keeping server rules authoritative
- [x] Avoid automatic hidden retry on network failure
- [x] Gate cashier delivery to Local/Test/CI plus persistence/session/sale-completion and explicit workspace arming
- [x] Add Vue/Inertia cashier workspace
- [x] Add executable SQLite scope/readiness/fail-closed regression
- [x] Qualify complete exact engineering head successfully
- [x] Obtain repository-native Product Owner merge authority
- [x] Squash merge engineering PR #732 at `b4d21b208a0580f4b40565b028dd6aed9bb190b8`

## Horizon F — Operational shift-start usability — Sprint158 completed

- [x] Prove canonical shift-opening and opening-cash mutation authorities existed before adding workspace source
- [x] Materialize exact-context read-only shift-start workspace snapshot and repository
- [x] Scope shift and opening-cash evidence by tenant + organization + outlet + device
- [x] Reuse existing deny-by-default `pos.shift.open` authorization
- [x] Reuse existing deny-by-default `pos.shift.opening-cash.record` authorization
- [x] Preserve canonical `/pos/shifts/open` / `OpenShift` mutation authority
- [x] Preserve canonical `/pos/shifts/opening-cash` / `RecordShiftOpeningCash` mutation authority
- [x] Preserve partial completion so an opened shift can resume opening-cash evidence without opening a second shift
- [x] Avoid automatic hidden retry for uncertain mutation outcomes
- [x] Preserve Money atomic-units, currency, and currency-scale boundaries
- [x] Gate shift-start delivery to Local/Test/CI plus persistence/session/capability checks and explicit workspace arming
- [x] Add Vue/Inertia three-state shift-start workspace
- [x] Add executable SQLite exact-scope/resumability/fail-closed regression
- [x] Qualify complete exact engineering head successfully
- [x] Obtain repository-native Product Owner merge authority
- [x] Squash merge engineering PR #734 at `d6eb8f7f359584130deea9ec0ed3572add3c05aa`

### Sprint158 evidence

- Parent canonical post-Sprint157 checkpoint: `700f2133032047d213d38e2e0317641599831a9f`.
- Final engineering head: `1e2a0ae959a88870ae4728f46f07b08ae0c08a2c`.
- Complete exact-head PR-triggered matrix: successful.
- Sprint158 regression run `34816888058`: successful.
- M7.1 run `34816888381`: successful.
- Governance run `34816888368`: successful.
- PHP Foundation run `34816887952`: successful.
- Sprint96, Sprint97, Sprint126, Sprint148, Sprint156, and Sprint157 regressions all successful on the exact engineering head.
- Engineering envelope: 11 paths, SHA-256 `3828b5914b64b4862ce4d39ff037261b796c232e5ac7c6fb001913a096ac1666`.
- Reconciliation envelope: six paths, SHA-256 `0257dde337eee65e156c49f59b54a61506b47866babb2c68a8bcc3a1a2e3321f`.

## Horizon G — Sprint159+ bounded engineering — next

Status: **BOUNDED DISCOVERY NEXT AFTER SPRINT158 CLOSURE**.

Selection rules:

1. prove a material non-duplicative P0/P1 gap exists before adding source;
2. inspect historical regressions, machine-readable contracts, and current source foundations before creating a new invariant owner;
3. prioritize security/data/tenant/auth/transaction/deployment blockers and business completeness over low-value abstraction;
4. recognize when the true blocker is external/operational and avoid inventing another source-only layer;
5. reuse existing canonical shift/cashier/sale/catalog/stock/authorization contracts rather than duplicating authority;
6. freeze the smallest meaningful bounded source envelope;
7. stay fail-closed, deny-by-default, tenant-isolated, and exact-context scoped;
8. avoid target persistence, producer dispatch, migration execution, permission provisioning, activation, deployment, and allowlist widening unless separately authorized;
9. qualify exact head in CI before merge;
10. reconcile canonical project-state documentation at closure.

No roadmap text pre-authorizes a specific Sprint159 implementation.

## Horizon H — Operational qualification — blocked / separate authority

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

## Horizon I — Technical Preview activation — NO-GO

Technical Preview activation remains `NOT_AUTHORIZED`.

## Horizon J — Production activation — NO-GO

Production activation remains `NOT_AUTHORIZED`.

## Horizon K — Updater/release activation — inactive

Updater activation remains `INACTIVE`. Release/deployment procedures do not establish authority or evidence that a release occurred.

## Roadmap maintenance rule

At every material sprint closure, update the canonical manifest and four root summaries, preserve detailed evidence in workflows/contracts/Git history, keep the just-closed preservation workflow successor-compatible, and never infer operational activation from source evidence.

Author by Lab | zefry
