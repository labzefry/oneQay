# Changelog

This changelog records **material canonical progress**, not every intermediate compatibility or CI-only commit. Detailed provenance remains in merged pull requests, Git history, per-sprint evidence, workflows, and machine-readable contracts.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

## 2026-09-14 — Sprint156 engineering complete; canonical reconciliation in progress

**Sprint156: POS operational sales reporting**

- **Purpose / Why:** post-Sprint155 discovery showed the remaining Final Shift Close blockers were operational/runtime prerequisites, while canonical product scope still lacked an operational sales-reporting owner.
- **Objective / Gap:** `POS_OPERATIONAL_SALES_REPORTING`.
- **What changed:** added a read-only tenant + organization + outlet scoped sales-summary application model, repository contract, authorized query service, Laravel read repository, guarded HTTP delivery, dedicated reporting provider, Vue/Inertia dashboard, fail-closed reporting configuration, and executable SQLite regression.
- Preserved currency and currency-scale boundaries; no cross-currency aggregation was introduced.
- Authorization remains deny-by-default through `pos.reporting.sales-summary.view` and the durable scoped authorization policy.
- Global provider registration remains canonical; reporting is composed through the existing POS composition root.
- Historical workflow ownership was narrowed only where exact-head CI proved stale successor-envelope or unrelated global-registration freezing. Substantive runtime/security regressions remain active.
- Sprint96 and Sprint97 now preserve their runtime/HTTP contracts without owning successor full-PR shape.
- Sprint148 retains all evidence-binding regressions while concurrency is isolated by PR + exact head SHA, preventing stale-head scheduler deadlock.
- Engineering PR #730 squash merged.
- Parent canonical post-Sprint155 checkpoint: `4f939acd6cbcc3c49a45cba549a082c789cabd44`.
- Final exact engineering head: `5e460d1c7c5174cc831106ade3e3fa6309acba4d`.
- Complete exact-head PR-triggered matrix: successful.
- Sprint156 reporting regression run `34813484159`: successful.
- M7.1 Application Regression run `34813484204`: successful.
- Governance Required Checks run `34813484278`: successful.
- PHP Foundation Regression run `34813484276`: successful.
- Sprint96 run `34813484081`, Sprint97 run `34813484181`, Sprint126 run `34813484356`, and Sprint148 run `34813484102`: successful.
- Repository-native exact-head Product Owner merge authority: successful.
- Engineering envelope: exactly 24 paths; SHA-256 `34c6dab2c898ddd9133aaa6d5413ca7b345127020d8f04fe54f861a4d1a5e79c`.
- Canonical engineering squash commit: `259cc00037ee0d3fb909cbcf2c87d39ffb26f9b9`.
- Post-merge verification proved exactly one squash commit over `4f939acd6cbcc3c49a45cba549a082c789cabd44` and exactly the qualified 24-path engineering delta.
- Post-Sprint156 canonical reconciliation envelope: six paths; SHA-256 `adba5b23ef33aeb360ebb4090b3f848fc2a3704807a60026c1344b2e0d1a54f4`.
- **Operational boundaries / NO-GO:** target selection remains blocked/null; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; real capability/dependency evidence remains `NONE`; runtime allowlist remains Local/Test/CI; feature activation remains `INACTIVE`; deployment authority remains `NOT_GRANTED`; Technical Preview/Production remain `NOT_AUTHORIZED`; updater remains `INACTIVE`.
- **Next position:** after reconciliation squash merge and post-merge verification, Sprint157 bounded discovery; no objective or source envelope is preselected.

## 2026-09-14 — Sprint155 closed

**Sprint155: Final Shift Close feature-activation transport source foundation**

- Added a deterministic source-only activation transport handoff envelope and executable application regression.
- Engineering PR #728 squash merged at `1e84e1b3e07915a1d20b56fe768b0d1454f901d2`.
- Final engineering head `4871e0ade60e8ac5e6f44b2bc27f0319ca150e63`; 36/36 exact-head CI successful; Product Owner authority successful.
- Engineering envelope SHA-256: `29619b928a422615647184c5316d9679dd4c4d582d759e89e8704e335ed982cb`.
- Concrete configuration-mutation transport and dispatchable activation executor remained `NOT_IMPLEMENTED`; feature activation remained `INACTIVE`.

## 2026-09-14 — Sprint154 closed

- Engineering PR #726 squash merged at `5349acbffd1c90087d37a1a0f74ce0ffd7e87773`.
- Added the deterministic selected-target-bound activation execution-plan source foundation.
- Final engineering head `132dbd0048ad40248efe093e22f276b487890487`; 39/39 exact-head CI successful; Product Owner authority successful.

## 2026-09-13 — Sprint148–Sprint153 material chain

- Sprint148: target-bound durable-runtime capability-evidence identity qualification.
- Sprint149: trusted target-bound capability-evidence producer source without dispatch.
- Sprint150: selected-runtime full dependency-envelope qualification.
- Sprint151: deterministic target-bound dependency-envelope evidence source foundation.
- Sprint152: selected-target DB binding for permission provisioning.
- Sprint153: trusted dependency-envelope evidence producer source without dispatch.

## Earlier material engineering history

Sprint130–Sprint147 established canonical runtime-control-plane policy and authenticated HTTP/throttle hardening. Earlier Final Shift Close milestones include Sprint88 migration #27 source-only materialization, Sprint107 runtime dependency inventory, Sprint110 durable-runtime readiness, Sprint111 selected-target identity, Sprint113–Sprint118 attestation/selection and selected-target DB-binding readiness, and Sprint119–Sprint129 runtime binding/control-plane hardening. Earlier work also established architecture/governance, bounded POS foundations, authentication/session hardening, cash/variance evidence, and regression-preservation controls.

## Current lifecycle boundary

Machine-readable authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

Current high-level values remain migration #27 `NOT_EXECUTED`, permission provisioning `NONE`, target selection blocked with selected target `null`, real capability evidence `NONE`, real dependency-envelope evidence `NONE`, feature activation `INACTIVE`, deployment authority `NOT_GRANTED`, Technical Preview/Production `NOT_AUTHORIZED`, and updater `INACTIVE`.

## Sprint description standard

Each material sprint records: **Purpose / Why; Objective / Gap; What changed; Evidence / Qualification; Operational boundaries / NO-GO; Next position**.

Author by Lab | zefry
