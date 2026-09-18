# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-18

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint178
**Objective:** `MERCHANT_FIRST_PARTY_APPLICATION_ENTRY_FOUNDATION`
**Canonical engineering commit:** `8992c2ed1b6278d113e24e38a847bedeac345161`
**Engineering PR:** #783 — `Sprint178: add merchant first-party application entry`
**Final engineering head:** `ee0e2e8acc5238fa0cb2e3be56b6e58cf2092239`
**Exact-head surfaced qualification:** 60/60 successful
**Engineering envelope:** 4 paths — `9d27ecd0802230d3484aa7ca424064313595e8174172eb889c2736250e2815c5`
**Canonical reconciliation envelope:** 6 paths — `d994709453d1415d23d2bdfc8ecade257d8baa8b07b9aff98654a2b4cb6bb0f5`
**Previous canonical checkpoint:** Sprint177 reconciliation `80746f4ed048bca7474eba50d6b24be253cf9d46`
**Next position:** Sprint179 bounded discovery from the fully reconciled Sprint178 checkpoint.

> `8992c2ed1b6278d113e24e38a847bedeac345161` is the canonical Sprint178 engineering evidence. The Sprint178 reconciliation squash must not replace it as the canonical engineering commit.

## 1. Purpose / Why

Sprint178 closes the proven merchant usability gap between the existing first-party authentication/session authority foundation and the already-delivered POS Operations Hub. A bootstrapped merchant now has a guarded browser entry surface instead of requiring direct API-level interaction.

## 2. What changed

- Reused the existing `Foundation` Inertia surface rather than adding a competing route/controller.
- Added server-rendered merchant-entry bootstrap metadata only when Local/Test/CI runtime, persistence, and session control are enabled.
- Added merchant sign-in UI that calls the existing first-party login endpoint.
- Reused existing TOTP enrollment and challenge endpoints and their pending-session semantics.
- Successful full session authority transitions to the existing `/pos` Operations Hub.
- Public self-registration, implicit permission grants, new authentication architecture, and new POS capability were not introduced.
- Production-like runtime retains the prior Foundation posture.
- The initial engineering shape that touched the shared root route was superseded before merge; the final envelope leaves `apps/web/routes/web.php` unchanged.
- No migration execution, permission provisioning, deployment, Technical Preview/Production authorization, updater activation, durable-target selection, or producer dispatch occurred.

## 3. Evidence / Qualification

- Canonical parent before engineering: `80746f4ed048bca7474eba50d6b24be253cf9d46`.
- Exact engineering head: `ee0e2e8acc5238fa0cb2e3be56b6e58cf2092239`.
- All 60 surfaced PR-triggered workflow runs completed successfully on the exact engineering head.
- Dedicated Sprint178 regression, Governance, PHP Foundation, and M7.1 qualification succeeded.
- Repository-native Product Owner merge authority succeeded for the exact engineering head.
- Engineering PR #783 squash merged at `8992c2ed1b6278d113e24e38a847bedeac345161`.
- Engineering envelope: exactly 4 paths; SHA-256 `9d27ecd0802230d3484aa7ca424064313595e8174172eb889c2736250e2815c5`.
- Canonical reconciliation envelope: exactly 6 paths; SHA-256 `d994709453d1415d23d2bdfc8ecade257d8baa8b07b9aff98654a2b4cb6bb0f5`.

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

Sprint178 grants no authority to expose public self-registration, provision a real merchant, widen runtime authorization, execute migrations, deploy, activate Technical Preview/Production, select a durable target, or dispatch producers.

## 5. Next position

Begin Sprint179 bounded discovery from the fully reconciled Sprint178 state. Identify the smallest material P0/P1 blocker in the merchant end-to-end journey using live canonical evidence. Do not preselect the objective and do not widen operational authority.

## Documentation responsibility

`PROJECT_MANIFEST.md` is the canonical human-readable state. `README.md`, `CHANGELOG.md`, `TASKS.md`, and `ROADMAP.md` are reconciled summaries. Git history, merged PRs, workflows, tests, and machine-readable contracts preserve detailed provenance.

Author by Lab | zefry
