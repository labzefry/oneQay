# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Canonical engineering checkpoint:** Sprint156
**Canonical engineering commit:** `259cc00037ee0d3fb909cbcf2c87d39ffb26f9b9`
**Latest engineering PR:** #730 — `Sprint156: add tenant-scoped POS operational sales reporting`
**Status date:** 2026-09-14

> This file is the canonical human-readable source of truth for current oneQay project status. Machine-readable operational state remains authoritative for operational gates.

## 1. Current project state

oneQay remains an actively engineered enterprise business-management platform using Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

Sprint156 closed the bounded P1 business-completeness gap `POS_OPERATIONAL_SALES_REPORTING` with a read-only tenant + organization + outlet scoped sales-summary surface over canonical POS sale, void, and cash-refund data. Currency and currency-scale boundaries remain explicit, authorization remains deny-by-default, and delivery remains fail-closed outside Local/Test/CI plus explicit feature arming.

Sprint156 also repaired historical regression ownership only where exact-head CI proved successor incompatibility. The global provider registry remains canonical; reporting is composed from the existing POS composition root. Historical Final Shift Close and POS workflows continue executing their substantive owned regressions rather than freezing unrelated successor PR shapes. Sprint148 additionally isolates qualification concurrency by exact head so stale runs cannot deadlock a new qualified head.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest completed engineering sprint | Sprint156 |
| Canonical engineering commit | `259cc00037ee0d3fb909cbcf2c87d39ffb26f9b9` |
| Latest engineering PR | #730, squash merged |
| Sprint156 final engineering head | `5e460d1c7c5174cc831106ade3e3fa6309acba4d` |
| Sprint156 exact-head CI | Complete PR-triggered matrix successful |
| Sprint156 Product Owner authority | `product-owner-merge-authority=success` for exact engineering head |
| Sprint156 engineering envelope | 24 paths; SHA-256 `34c6dab2c898ddd9133aaa6d5413ca7b345127020d8f04fe54f861a4d1a5e79c` |
| Post-Sprint156 reconciliation envelope | 6 paths; SHA-256 `adba5b23ef33aeb360ebb4090b3f848fc2a3704807a60026c1344b2e0d1a54f4` |
| POS operational sales reporting | Materialized as read-only Local/Test/CI delivery |
| Reporting authorization | `pos.reporting.sales-summary.view`, deny-by-default |
| Reporting scope | Tenant + organization + outlet |
| Reporting currency handling | Currency + currency scale preserved; no cross-currency aggregation |
| Runtime allowlist | `local`, `test`, `ci` only |
| Durable activation target | `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET` |
| Selected activation target | `null` |
| Final Shift Close migration #27 | `NOT_EXECUTED` |
| Permission provisioning | `NONE` |
| Real target-bound capability evidence | `NONE` |
| Real dependency-envelope evidence | `NONE` |
| Feature activation | `INACTIVE` |
| Deployment authority | `NOT_GRANTED` |
| Technical Preview activation | `NOT_AUTHORIZED` |
| Production activation | `NOT_AUTHORIZED` |
| Updater activation | `INACTIVE` |

## 2. Material engineering progress

### Platform, governance, and POS foundation

The repository has established modular-monolith architecture, tenant isolation, deny-by-default authorization, first-party session and privileged-authentication foundations, versioned REST governance, deterministic CI/governance controls, exact-head qualification, and repository-native Product Owner merge authorization.

Bounded POS work includes shift/register opening, sale completion/payment/receipt evidence, tenant/outlet-scoped catalog preparation, durable idempotency preservation, cash-variance and adjudication foundations, Final Shift Close source/readiness controls, and now tenant-scoped operational sales reporting.

### Final Shift Close engineering chain

- Sprint88 — migration #27 source materialization only; migration remained `NOT_EXECUTED`.
- Sprint107 — nine-component runtime dependency inventory and explicit allowlist block.
- Sprint110–Sprint111 — durable-runtime readiness and exact selected-target identity.
- Sprint113–Sprint118 — attestation/ingestion, selection persistence, migration selected-target binding, and trusted DB-binding producer source readiness.
- Sprint119–Sprint147 — runtime DB-binding/materialization control plane plus canonical HTTP/auth/throttle hardening.
- Sprint148 — exact target-bound capability-evidence identity qualification.
- Sprint149 — trusted capability-evidence producer source materialization without dispatch.
- Sprint150 — exact selected-runtime full dependency-envelope qualification.
- Sprint151 — deterministic target-bound dependency-envelope evidence construction source foundation.
- Sprint152 — selected-target database binding for permission provisioning.
- Sprint153 — trusted protected-environment dependency-envelope evidence producer source without dispatch.
- Sprint154 — selected-target-bound feature-activation execution-plan source foundation.
- Sprint155 — deterministic source-only activation transport handoff envelope.
- Sprint156 — product-readiness pivot to read-only POS operational sales reporting plus bounded historical regression successor compatibility.

## 3. Sprint156 description and closure evidence

### Purpose / Why

Post-Sprint155 discovery showed the remaining Final Shift Close blockers were external operational/runtime prerequisites rather than missing source abstraction. Continuing that chain would not materially shorten the path to a usable product. Canonical scope still required reporting, analytics, and a BI-like operational surface, while no operational sales-reporting owner existed.

### Objective / Gap

Bounded objective: `POS_OPERATIONAL_SALES_REPORTING`.

The objective was to provide one cohesive read-only sales-summary surface using canonical POS persistence while preserving tenant isolation, organization/outlet scope, currency boundaries, deny-by-default authorization, and all operational NO-GO state.

### What changed

- Added application summary model, repository contract, and authorized query service.
- Added Laravel read repository over canonical sale/void/cash-refund tables.
- Added guarded HTTP controller and dedicated reporting service provider.
- Added Inertia/Vue sales-summary dashboard.
- Added fail-closed `ONEQAY_POS_OPERATIONAL_REPORTING_ENABLED` configuration.
- Added executable SQLite regression covering tenant/outlet isolation, multi-currency grouping, state precedence, and persistence/runtime/feature fail-closed behavior.
- Preserved canonical Composer dependency metadata; the Sprint156 workflow executes canonical application tests plus the dedicated reporting regression.
- Restored the global provider registry to canonical state and composed reporting from the existing POS provider boundary.
- Corrected stale historical workflow ownership only where exact-head CI proved successor incompatibility; substantive runtime/security regressions remain active.
- Corrected Sprint148 concurrency scheduling to exact-head isolation without changing its substantive qualification steps.

### Evidence / Qualification

- Engineering PR: #730, squash merged.
- Parent canonical post-Sprint155 checkpoint: `4f939acd6cbcc3c49a45cba549a082c789cabd44`.
- Final exact engineering head: `5e460d1c7c5174cc831106ade3e3fa6309acba4d`.
- Complete exact-head PR-triggered matrix: **successful**.
- Sprint156 reporting regression run `34813484159`: successful.
- M7.1 Application Regression run `34813484204`: successful.
- Governance Required Checks run `34813484278`: successful.
- PHP Foundation Regression run `34813484276`: successful.
- Sprint96 runtime regression run `34813484081`: successful.
- Sprint97 HTTP delivery regression run `34813484181`: successful.
- Sprint126 token-hardening regression run `34813484356`: successful.
- Sprint148 capability-evidence regression run `34813484102`: successful after exact-head concurrency isolation.
- Repository-native Product Owner authorization comment: exact PR #730 and head `5e460d1c7c5174cc831106ade3e3fa6309acba4d`.
- `product-owner-merge-authority`: successful — exact-head authority verified.
- Engineering envelope: 24 paths; SHA-256 `34c6dab2c898ddd9133aaa6d5413ca7b345127020d8f04fe54f861a4d1a5e79c`.
- Canonical engineering squash commit: `259cc00037ee0d3fb909cbcf2c87d39ffb26f9b9`.
- Post-merge verification: exactly one squash commit above `4f939acd6cbcc3c49a45cba549a082c789cabd44`, with exactly the 24 qualified engineering paths.
- Post-Sprint156 reconciliation envelope: six paths; SHA-256 `adba5b23ef33aeb360ebb4090b3f848fc2a3704807a60026c1344b2e0d1a54f4`.

### Operational boundaries / NO-GO

Sprint156 does not select or persist a durable target, dispatch capability/dependency producers, execute migration #27, provision permissions, produce real target-bound capability/dependency evidence, widen the runtime allowlist, activate Final Shift Close, grant deployment authority, activate Technical Preview/Production, or activate the updater.

Machine-readable state after engineering merge still records target selection blocked with `selected_target=null`, migration #27 `NOT_EXECUTED`, permission provisioning `NONE`, feature activation `INACTIVE`, deployment authority `NOT_GRANTED`, Technical Preview and Production `NOT_AUTHORIZED`, and updater `INACTIVE`.

### Next position

After post-Sprint156 reconciliation is squash merged and verified, the next engineering position is **Sprint157 bounded discovery from canonical post-Sprint156**. No Sprint157 objective, implementation, or source envelope is preselected.

## 4. Operational truth — NO-GO remains authoritative

Current machine-readable state remains:

- migration #27: `NOT_EXECUTED`;
- permission provisioning: `NONE`;
- selected target: `null`;
- target selection: `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- capability-evidence producer dispatch: `NOT_PERFORMED`; real capability evidence: `NONE`;
- dependency-evidence producer dispatch: `NOT_PERFORMED`; real dependency evidence: `NONE`;
- runtime allowlist remains Local/Test/CI only;
- feature activation: `INACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview / Production activation: `NOT_AUTHORIZED`;
- updater activation: `INACTIVE`.

Machine-readable operational authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

## 5. Documentation responsibility model

- `PROJECT_MANIFEST.md` — canonical human-readable current project/lifecycle state;
- `README.md` — concise entry-point summary;
- `CHANGELOG.md` — material chronology;
- `TASKS.md` — current completed/pending workboard;
- `ROADMAP.md` — forward sequencing and lifecycle gates;
- `docs/SPRINT*.md` — detailed historical bounded-sprint evidence where materialized;
- `ops/final-shift-close/*.json` — machine-readable operational authority;
- merged PRs and Git history — immutable implementation provenance.

## 6. Mandatory sprint description and update rule

Every material sprint records **Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position**. Every material closed sprint reconciles this manifest and the four root summary documents, while the just-closed sprint preservation workflow remains source-only and successor-compatible.

Author by Lab | zefry
