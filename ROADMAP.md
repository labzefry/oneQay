# oneQay Roadmap

**Roadmap checkpoint:** post-Sprint161 canonical reconciliation
**Canonical engineering baseline:** `33080c0b5f5c6f66e9994ad7f05ba78dea241294`
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

## Horizon D — Product-readiness POS operational surfaces — Sprint156–Sprint161 completed

- [x] Sprint156 — read-only operational sales reporting
- [x] Sprint157 — cashier sale-entry workspace over canonical sale authority
- [x] Sprint158 — resumable exact-device shift-start workspace
- [x] Sprint159 — operational sale correction workspace over void/refund authorities
- [x] Sprint160 — immutable sale history and receipt-detail workspace
- [x] Sprint161 — operational catalog and opening-inventory setup workspace

## Horizon E — Catalog and opening-inventory setup — Sprint161 completed

- [x] Prove canonical catalog-preparation and inventory-baseline mutation authorities before adding UI source
- [x] Reuse existing `pos.catalog.prepare` and `pos.inventory.baseline` permissions deny-by-default
- [x] Reuse existing named POST mutation endpoints
- [x] Materialize tenant/outlet-scoped catalog and stock snapshot
- [x] Preserve precision-safe atomic price, currency, scale, stock, and sellable state
- [x] Derive baseline eligibility from canonical mutation conditions
- [x] Preserve explicit catalog → opening-inventory sequence
- [x] Avoid composite mutation, second stock engine, hidden retry, or migration
- [x] Lock further mutation after success or network ambiguity until authoritative refresh
- [x] Fail closed on malformed persisted currency rather than normalizing it
- [x] Gate delivery to Local/Test/CI plus persistence/session/capability flags and explicit workspace arming
- [x] Add Vue/Inertia setup workspace and executable SQLite regression
- [x] Disqualify initial exact head after CI exposed malformed-currency normalization defect
- [x] Qualify corrected exact engineering head successfully
- [x] Obtain repository-native Product Owner merge authority
- [x] Squash merge engineering PR #740 at `33080c0b5f5c6f66e9994ad7f05ba78dea241294`

### Sprint161 evidence

- Parent canonical post-Sprint160 checkpoint: `92d932020ef95bdc26460a4944841d141d6fad5b`.
- Initial disqualified head: `6cbb218d204e7e84ff6701328f6d884ccf5699ec`.
- Final engineering head: `fbe8e91df756855d38b8c6656b17f27b4cc32585`.
- Sprint161 regression run `34853239912`: successful.
- Complete surfaced final exact-head PR-triggered matrix: successful.
- Engineering envelope: 11 paths, SHA-256 `66d7c616fbe8ae0e6c3c262fc8054db26bcbaa67e07ed77000363041b056f613`.
- Reconciliation envelope: six paths, SHA-256 `fd24a20017a13eca06d93ade217b6c0a68db07c205b218b8d9c201122c6e7ccc`.

## Horizon F — Sprint162+ bounded engineering — next

Status: **BOUNDED DISCOVERY NEXT AFTER SPRINT161 CLOSURE**.

Selection rules:

1. prove a material non-duplicative P0/P1 gap exists before adding source;
2. inspect current source, historical regressions, and machine-readable contracts before creating a new invariant owner;
3. prioritize security/data/tenant/auth/transaction/deployment blockers and business completeness over low-value abstraction;
4. recognize when the true blocker is external/operational and avoid inventing another source-only layer;
5. reuse existing canonical POS/domain/authorization contracts rather than duplicating authority;
6. distinguish one-time inventory baseline from any future restock/adjustment need; Sprint161 grants no new stock-adjustment authority;
7. freeze the smallest meaningful bounded source envelope;
8. stay fail-closed, deny-by-default, and tenant-isolated;
9. avoid target persistence, producer dispatch, migration execution, permission provisioning, activation, deployment, and allowlist widening unless separately authorized;
10. qualify exact head in CI before merge and reconcile canonical project-state documentation at closure.

No roadmap text pre-authorizes a specific Sprint162 implementation.

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
