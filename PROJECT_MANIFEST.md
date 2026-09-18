# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-18

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint179
**Objective:** `MERCHANT_BOOTSTRAP_INITIAL_POS_OPERATION_AUTHORIZATION_FOUNDATION`
**Canonical engineering commit:** `fb0a886ac7f1447fa26f3eefcc808158d4ef044d`
**Engineering PR:** #785 — `Sprint179: authorize initial merchant POS operation`
**Final engineering head:** `94d1f2937a3ab71803738c2a2408170e63b6fcf1`
**Exact-head surfaced qualification:** 61/61 successful
**Engineering envelope:** 4 paths — `33206447002d40b489742fdb7b0c50670705400d16c184e352aa64aeb1534feb`
**Canonical reconciliation envelope:** 6 paths — `72d21048381af6505f8b6315141efef93f909e407d54377e3726d49f0c38ccff`
**Previous canonical checkpoint:** Sprint178 reconciliation `43a5e93c893ae0f66849c56e1ea2910de677b830`
**Next position:** Sprint180 bounded discovery from the fully reconciled Sprint179 checkpoint.

> `fb0a886ac7f1447fa26f3eefcc808158d4ef044d` is the canonical Sprint179 engineering evidence. The Sprint179 reconciliation squash must not replace it as the canonical engineering commit.

## 1. Purpose / Why

Sprint179 closes the proven authorization gap between merchant bootstrap/login readiness and meaningful POS use. A freshly bootstrapped merchant previously had control authority but did not yet have exact outlet/device access or POS permissions required by the existing POS Operations Hub.

## 2. What changed

- Preserved the Sprint176 atomic merchant-context bootstrap service unchanged.
- Added `MerchantPosReadyBootstrapService` as an outer transactional orchestration layer.
- Recorded exact outlet and device organizational access for the bootstrapped merchant principal.
- Created a separate non-control role: `merchant-initial-pos-operator`.
- Granted only initial POS permissions required for catalog preparation, inventory baseline, shift opening, opening cash, and sale completion.
- Assigned the operational role only at the exact bootstrapped device scope.
- Did not grant sale void, sale refund, Final Shift Close, or protected control authority to the operational role.
- Preserved the existing protected `authorization-policy-administrator` role without widening its permission set.
- Updated the guarded bootstrap command to invoke the POS-ready orchestration while retaining its zero-argument, preauthorized, Local/Test/CI-only delivery contract.
- Regression proves complete outer rollback when downstream POS authorization provisioning fails.

## 3. Evidence / Qualification

- Canonical parent before engineering: `43a5e93c893ae0f66849c56e1ea2910de677b830`.
- Exact engineering head: `94d1f2937a3ab71803738c2a2408170e63b6fcf1`.
- All 61 surfaced PR-triggered workflow runs completed successfully on the exact engineering head.
- Sprint176 atomic bootstrap and Sprint177 guarded-delivery regressions remained successful.
- Dedicated Sprint179 regression completed successfully.
- Repository-native Product Owner merge authority succeeded for the exact engineering head.
- Engineering PR #785 squash merged at `fb0a886ac7f1447fa26f3eefcc808158d4ef044d`.
- Engineering envelope: exactly 4 paths; SHA-256 `33206447002d40b489742fdb7b0c50670705400d16c184e352aa64aeb1534feb`.
- Canonical reconciliation envelope: exactly 6 paths; SHA-256 `72d21048381af6505f8b6315141efef93f909e407d54377e3726d49f0c38ccff`.

## 4. Operational boundaries / NO-GO

Machine-readable operational authority under `ops/final-shift-close/` remains authoritative and unchanged:

- durable activation target selection: `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- selected target: `null`;
- migration #27: `NOT_EXECUTED`;
- permission provisioning: `NONE`;
- producer dispatch: `NOT_PERFORMED`;
- runtime allowlist: Local/Test/CI only;
- feature activation: `INACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview / Production: `NOT_AUTHORIZED`;
- updater: `INACTIVE`.

Sprint179 grants no authority to provision a real merchant, widen runtime authorization, execute migrations, deploy, activate Technical Preview/Production, select a durable target, dispatch producers, or perform operational permission provisioning outside the bounded Local/Test/CI source qualification.

## 5. Next position

Begin Sprint180 bounded discovery from the fully reconciled Sprint179 state. Identify the smallest material P0/P1 blocker remaining in the real merchant end-to-end journey from live canonical evidence; do not preselect the objective or widen operational authority.

## Documentation responsibility

`PROJECT_MANIFEST.md` is the canonical human-readable state. `README.md`, `CHANGELOG.md`, `TASKS.md`, and `ROADMAP.md` are reconciled summaries. Git history, merged PRs, workflows, tests, and machine-readable contracts preserve detailed provenance.

Author by Lab | zefry
