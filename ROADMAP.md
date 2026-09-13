# oneQay Roadmap

**Roadmap checkpoint:** post-Sprint144
**Canonical engineering baseline:** `98840c29c21bcf6b1d81cb2afe21d07eb720e120`
**Current state authority:** `PROJECT_MANIFEST.md`

This roadmap describes sequencing and gates. It does **not** grant operational authority. A completed roadmap item means its bounded repository objective was completed; it does not imply deployment, migration execution, runtime activation, Technical Preview activation, or Production activation unless separately evidenced.

## Horizon A — Platform and repository foundation — completed

- [x] Modular Monolith First architecture
- [x] Clean Architecture / DDD boundaries
- [x] Tenant-context-first isolation model
- [x] Deny-by-default authorization foundation
- [x] First-party session and privileged authentication foundations
- [x] Versioned REST governance, stable error envelope, correlation and idempotency conventions
- [x] CI/governance, exact-head qualification, historical-regression and merge-authorization controls
- [x] Transactional-outbox readiness and infrastructure-adapter evolution boundary

## Horizon B — Bounded POS foundations — completed as source engineering

- [x] Sale completion / bounded payment recording / deterministic receipt evidence
- [x] Tenant/outlet-scoped catalog preparation foundation
- [x] Shift/register opening foundation
- [x] Durable idempotency and scoped authorization preservation
- [x] Historical compatibility/regression preservation across POS foundations

These foundations are repository/source achievements, not a statement that a complete production POS has been activated.

## Horizon C — JRN-010 cash and variance evidence — completed through bounded source objectives

- [x] Expected-cash derivation readiness
- [x] Immutable sale-to-shift binding readiness
- [x] Cash-variance source readiness and durable source foundation
- [x] Variance adjudication/explanation foundation
- [x] Explanation author/reviewer authorization hardening
- [x] Maker-checker / review-decision evidence chain

Representative milestones: Sprint55, Sprint64, Sprint70, and Sprint80.

## Horizon D — Final Shift Close source/readiness chain — completed through Sprint144 engineering boundary

- [x] Source-only migration #27 materialization — Sprint88
- [x] Final Shift Close application-readiness contract — Sprint89
- [x] Application/service and historical-compatibility hardening
- [x] Runtime-binding and manifest readiness foundations
- [x] DB-binding attestation readiness foundations
- [x] Runtime authorization and fail-closed control-plane hardening
- [x] Canonical token policy — Sprint130
- [x] Middleware/controller positive-path and fail-closed qualification — Sprint131–Sprint133
- [x] Delivery-gate and registration metadata qualification — Sprint134–Sprint135
- [x] Authenticated HTTP positive/fail-closed/throttle qualification — Sprint136–Sprint138
- [x] Authentication-before-throttle hardening — Sprint139
- [x] Authentication-rejection hardening and HTTP-kernel propagation — Sprint140–Sprint141
- [x] Authenticated throttle-rejection response hardening — Sprint142
- [x] DB-attestation HEAD parity hardening — Sprint143
- [x] Canonical named-route identity ownership hardening — Sprint144

Sprint144 requires the throttle-response hardener to match canonical route identity in addition to method and path, preserving canonical POST/GET/HEAD behavior while leaving same-path/method noncanonical routes framework-owned.

Migration #27 remains source-only and `NOT_EXECUTED`; completion of this engineering horizon does not authorize operational use.

## Horizon E — Sprint145+ bounded engineering — next

Status: **NOT YET SELECTED / NOT COMPLETED**.

Sprint145 begins only after bounded discovery identifies the smallest non-duplicative remaining engineering gap from canonical post-Sprint144.

Selection rules:

1. prove the gap exists before adding source;
2. freeze a bounded source envelope;
3. preserve Sprint130–Sprint144 owned regressions and historical compatibility;
4. stay fail-closed and deny-by-default;
5. avoid operational adapter invocation and runtime-token provisioning unless separately authorized;
6. qualify exact head in CI before merge;
7. reconcile canonical project-state documentation at closure.

No roadmap text pre-authorizes a specific Sprint145 implementation.

## Horizon F — Operational qualification — blocked / separate authority

Operational work may proceed only after its prerequisites and explicit authority exist.

- [ ] Qualified non-synthetic durable runtime target — missing
- [ ] Selected target — currently `null`
- [ ] Migration #27 execution authority — not granted
- [ ] Permission provisioning authority — current state `NONE`
- [ ] Feature activation authority — current state `INACTIVE`
- [ ] Authenticated operational runtime configuration channel — must be qualified for the selected environment
- [ ] Real operational runtime token provisioning — not performed
- [ ] Real operational binding/attestation evidence — not performed
- [ ] Deployment authority — `NOT_GRANTED`

Current target selection remains `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`.

Production remains rejected as the first activation target; any first activation target must be an isolated non-production, non-synthetic durable runtime with the required evidence controls.

## Horizon G — Technical Preview activation — NO-GO until separately authorized

Technical Preview activation remains `NOT_AUTHORIZED`.

## Horizon H — Production activation — NO-GO until separately authorized

Production activation remains `NOT_AUTHORIZED`.

## Horizon I — Updater/release activation — inactive until separately authorized

Updater activation remains `INACTIVE`. Release/deployment procedures are procedures only and do not establish authority or evidence that a release occurred.

## Roadmap maintenance rule

At every material sprint closure:

- move genuinely completed bounded objectives into the completed horizon;
- do not mark operational activation complete from source evidence;
- update `PROJECT_MANIFEST.md` first;
- reconcile README, TASKS, CHANGELOG, and ROADMAP against that manifest;
- make the just-closed sprint workflow successor-compatible;
- retain per-sprint detail in `docs/` rather than duplicating full evidence into every root document.

Author by Lab | zefry
