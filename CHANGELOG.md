# Changelog

This changelog records **material canonical progress**, not every intermediate compatibility or CI-only commit. Detailed provenance remains in merged pull requests, Git history, workflows, tests, and machine-readable contracts.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

## 2026-09-14 — Sprint159 closed

**Sprint159: operational POS sale correction workspace**

- **Purpose / Why:** canonical POS already had authoritative full-sale void and full CASH refund APIs but no operational correction workspace.
- **Objective / Gap:** `POS_SALE_CORRECTION_WORKSPACE`.
- **What changed:** added validated correction read models, exact tenant + organization + outlet batch reads, original sale device and shift visibility, independent void/refund permission gating, dedicated guarded provider/controller, explicit workspace config, Vue/Inertia correction UI, and executable SQLite regression.
- CASH correction remains explicit two-stage `COMPLETED → VOIDED → REFUNDED`; no composite server mutation was introduced.
- MANUAL_EXTERNAL sales may be voided but are marked for external settlement rather than in-app CASH refund.
- Impossible or mismatched void/refund evidence fails closed.
- No arbitrary client refund amount or automatic network retry was introduced.
- Existing `VoidSale`, `RecordCashRefund`, `pos.sales.void`, and `pos.sales.cash-refund` remain mutation authorities.
- `routes/web.php`, global provider registry, Composer manifest, database migrations, and operational state remained unchanged.
- Engineering PR #736 squash merged.
- Parent canonical post-Sprint158 checkpoint: `6a23eefcc10a60d306d969834c594c73a7b7d2bf`.
- Final exact engineering head: `475c4d8fb9a1e17467e71c77fb837351d77e48e2`.
- Complete surfaced exact-head PR-triggered matrix: successful.
- Sprint159 regression run `34818568216`: successful.
- M7.1 Application Regression run `34818568151`: successful.
- Governance Required Checks run `34818568451`: successful.
- PHP Foundation Regression run `34818568650`: successful.
- Sprint96 `34818568084`, Sprint97 `34818569245`, Sprint126 `34818568682`, Sprint148 `34818568207`, Sprint156 `34818568303`, Sprint157 `34818568110`, and Sprint158 `34818568571`: successful.
- Repository-native exact-head Product Owner merge authority: successful.
- Engineering envelope: exactly 11 paths; SHA-256 `6a5f49136334f99c182220a7db89a7869611be8a5b65a6cd3fe4c73fcc40cf00`.
- Canonical engineering squash: `e4e6a0f55bbb3b6fd4126df66f4815e281ad0dc4`.
- Post-merge verification proved exactly one squash commit over `6a23eefcc10a60d306d969834c594c73a7b7d2bf` and exactly the qualified 11-path delta.
- Post-Sprint159 reconciliation envelope: six paths; SHA-256 `e7ebee58804975f9d64bc3061c13dccd4c1212877fe73cb75072163e101070dc`.
- **Operational boundaries / NO-GO:** target selection remains blocked/null; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; real capability/dependency evidence remains `NONE`; Final Shift Close runtime allowlist remains Local/Test/CI; feature activation remains `INACTIVE`; deployment authority remains `NOT_GRANTED`; Technical Preview/Production remain `NOT_AUTHORIZED`; updater remains `INACTIVE`.
- **Next position:** Sprint160 bounded discovery from canonical post-Sprint159; no objective or source envelope is preselected.

## 2026-09-14 — Sprint158 closed

**Sprint158: operational POS shift-start workspace**

- Added exact tenant + organization + outlet + device shift-start status over existing shift-opening and opening-cash authorities.
- Added resumable three-state UI: shift required, opening cash required, ready for cashier.
- Required existing `pos.shift.open` and `pos.shift.opening-cash.record` permissions.
- Engineering PR #734 squash merged at `d6eb8f7f359584130deea9ec0ed3572add3c05aa`.
- Final engineering head `1e2a0ae959a88870ae4728f46f07b08ae0c08a2c`; complete exact-head matrix and Product Owner authority successful.
- Engineering envelope SHA-256: `3828b5914b64b4862ce4d39ff037261b796c232e5ac7c6fb001913a096ac1666`.
- Reconciliation envelope SHA-256: `0257dde337eee65e156c49f59b54a61506b47866babb2c68a8bcc3a1a2e3321f`.

## 2026-09-14 — Sprint157 closed

**Sprint157: operational POS cashier sale-entry workspace**

- Added scoped cashier read models, active positive-stock catalog, exact-device active-shift readiness, guarded Vue/Inertia cashier UI, and executable regression.
- Reused canonical `pos.sales.complete` mutation authority and existing deny-by-default sale permission.
- Engineering PR #732 squash merged at `b4d21b208a0580f4b40565b028dd6aed9bb190b8`.

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

Sprint88–Sprint155 established the Final Shift Close source/readiness chain through migration source materialization, runtime dependency/readiness, selected-target identity, attestation/selection binding, DB binding/control-plane hardening, target-bound capability/dependency evidence foundations, permission-provisioning binding, and feature-activation source foundations. Earlier work also established architecture/governance, authentication/session hardening, POS sale/payment/receipt/catalog/inventory foundations, full-sale void/refund, cash/variance evidence, and regression-preservation controls.

## Current lifecycle boundary

Machine-readable authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

Current values remain migration #27 `NOT_EXECUTED`, permission provisioning `NONE`, target selection blocked with selected target `null`, real capability evidence `NONE`, real dependency-envelope evidence `NONE`, feature activation `INACTIVE`, deployment authority `NOT_GRANTED`, Technical Preview/Production `NOT_AUTHORIZED`, and updater `INACTIVE`.

## Sprint description standard

Each material sprint records: **Purpose / Why; Objective / Gap; What changed; Evidence / Qualification; Operational boundaries / NO-GO; Next position**.

Author by Lab | zefry
