# Changelog

This changelog records **material canonical progress**, not every intermediate compatibility or CI-only commit. Detailed provenance remains in merged pull requests, Git history, workflows, tests, and machine-readable contracts.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

## 2026-09-14 — Sprint158 closed

**Sprint158: operational POS shift-start workspace**

- **Purpose / Why:** Sprint157 delivered real cashier sale entry, but no operational UI existed to establish its required exact-device active shift and opening-cash evidence even though both backend mutation contracts already existed.
- **Objective / Gap:** `POS_SHIFT_START_WORKSPACE`.
- **What changed:** added a scoped read-only shift-start snapshot/repository, exact-context Laravel reader, authorized query service, guarded provider/controller, fail-closed config, Vue/Inertia three-stage workspace, and executable SQLite regression.
- Requires both existing deny-by-default permissions `pos.shift.open` and `pos.shift.opening-cash.record`.
- Reads active shift and opening-cash evidence only for the exact tenant + organization + outlet + device context.
- Reuses canonical POST `/pos/shifts/open` and POST `/pos/shifts/opening-cash`; no composite mutation engine was introduced.
- Preserves partial completion: a successfully opened shift remains active while opening-cash evidence can be resumed independently.
- No automatic network retry was introduced; uncertain results require authoritative status refresh before resubmission.
- Opening-cash client conversion remains integer/scale aware while server Money validation remains final.
- `routes/web.php`, global provider registry, Composer manifest, database migrations, and operational state remained unchanged.
- Engineering PR #734 squash merged.
- Parent canonical post-Sprint157 checkpoint: `700f2133032047d213d38e2e0317641599831a9f`.
- Final exact engineering head: `1e2a0ae959a88870ae4728f46f07b08ae0c08a2c`.
- Complete exact-head PR-triggered matrix: successful.
- Sprint158 regression run `34816888058`: successful.
- M7.1 Application Regression run `34816888381`: successful.
- Governance Required Checks run `34816888368`: successful.
- PHP Foundation Regression run `34816887952`: successful.
- Sprint96 run `34816888176`, Sprint97 run `34816888300`, Sprint126 run `34816888435`, Sprint148 run `34816888194`, Sprint156 run `34816888289`, and Sprint157 run `34816888205`: successful.
- Repository-native exact-head Product Owner merge authority: successful.
- Engineering envelope: exactly 11 paths; SHA-256 `3828b5914b64b4862ce4d39ff037261b796c232e5ac7c6fb001913a096ac1666`.
- Canonical engineering squash: `d6eb8f7f359584130deea9ec0ed3572add3c05aa`.
- Post-merge verification proved exactly one squash commit over `700f2133032047d213d38e2e0317641599831a9f` and exactly the qualified 11-path delta.
- Post-Sprint158 reconciliation envelope: six paths; SHA-256 `0257dde337eee65e156c49f59b54a61506b47866babb2c68a8bcc3a1a2e3321f`.
- **Operational boundaries / NO-GO:** target selection remains blocked/null; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; real capability/dependency evidence remains `NONE`; Final Shift Close runtime allowlist remains Local/Test/CI; feature activation remains `INACTIVE`; deployment authority remains `NOT_GRANTED`; Technical Preview/Production remain `NOT_AUTHORIZED`; updater remains `INACTIVE`.
- **Next position:** Sprint159 bounded discovery from canonical post-Sprint158; no objective or source envelope is preselected.

## 2026-09-14 — Sprint157 closed

**Sprint157: operational POS cashier sale-entry workspace**

- Added exact-context cashier read models, active positive-stock catalog delivery, guarded Vue/Inertia cashier workspace, and executable SQLite regression.
- Reused existing `pos.sale.complete` authorization and canonical sale mutation authority; no second transaction engine was introduced.
- Engineering PR #732 squash merged at `b4d21b208a0580f4b40565b028dd6aed9bb190b8`.
- Final engineering head `0ff14cf95aa54cd798fe5d1b5611c2890757e5b3`; complete exact-head matrix and Product Owner authority successful.
- Engineering envelope SHA-256: `f363bbfff9b1a52479c0f6d76e7cefe4b14ac89c597b2cd7894713e34bcc2f5b`.
- Reconciliation envelope SHA-256: `4393c47067856f6d426cc2ce3f976bda78a72c13adaedb47ff53cc93f2c4ca1c`.

## 2026-09-14 — Sprint156 closed

**Sprint156: POS operational sales reporting**

- Added read-only tenant + organization + outlet scoped operational sales reporting using canonical POS persistence.
- Preserved currency and currency-scale boundaries and deny-by-default authorization.
- Engineering PR #730 squash merged at `259cc00037ee0d3fb909cbcf2c87d39ffb26f9b9`.

## 2026-09-14 — Sprint155 closed

- Added deterministic source-only Final Shift Close activation transport handoff envelope and executable regression.
- Engineering PR #728 squash merged at `1e84e1b3e07915a1d20b56fe768b0d1454f901d2`.
- Concrete configuration-mutation transport and dispatchable activation executor remained `NOT_IMPLEMENTED`; feature activation remained `INACTIVE`.

## Earlier material engineering history

Sprint88–Sprint155 established the Final Shift Close source/readiness chain through migration source materialization, runtime dependency/readiness, selected-target identity, attestation/selection binding, DB binding/control-plane hardening, target-bound capability/dependency evidence foundations, permission-provisioning binding, and feature-activation source foundations. Earlier work also established architecture/governance, authentication/session hardening, POS sale/payment/receipt/catalog/inventory foundations, cash/variance evidence, and regression-preservation controls.

## Current lifecycle boundary

Machine-readable authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

Current values remain migration #27 `NOT_EXECUTED`, permission provisioning `NONE`, target selection blocked with selected target `null`, real capability evidence `NONE`, real dependency-envelope evidence `NONE`, feature activation `INACTIVE`, deployment authority `NOT_GRANTED`, Technical Preview/Production `NOT_AUTHORIZED`, and updater `INACTIVE`.

## Sprint description standard

Each material sprint records: **Purpose / Why; Objective / Gap; What changed; Evidence / Qualification; Operational boundaries / NO-GO; Next position**.

Author by Lab | zefry
