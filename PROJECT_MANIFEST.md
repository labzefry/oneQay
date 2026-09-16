# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-16

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint176
**Objective:** `MERCHANT_CONTEXT_ATOMIC_BOOTSTRAP_FOUNDATION`
**Canonical engineering commit:** `af2ed4db8e49c4a75f1e1b743986cc20f3e3b0ff`
**Engineering PR:** #777 — `Sprint176: add atomic merchant context bootstrap foundation`
**Final engineering head:** `775343389659754d85f870eca55f808a0b28eea5`
**Sprint176 regression:** `35043179125` — successful
**Governance Required Checks:** `35043179113` — successful
**PHP Foundation Regression:** `35043179183` — successful
**M7.1 Application Regression:** `35043178985` — successful
**Engineering envelope:** 8 paths — `f1b48efc2a25623ae55c72b95407a19ef60b63f8dcb933f9d7f137f09a34b974`
**Canonical reconciliation envelope:** 6 paths — `4cc815fdb1c6489ab34334a14033acfe1452b50874f48c9e867e6f6da3858b03`
**Previous canonical checkpoint:** Sprint175 reconciliation `dd2e5b017706e8ff4145a767b9c5e01199c3728f`
**Next position:** Sprint177 bounded discovery only from the fully reconciled Sprint176 checkpoint; no objective preselected.

> `af2ed4db8e49c4a75f1e1b743986cc20f3e3b0ff` is the canonical Sprint176 engineering evidence. The Sprint176 reconciliation squash must not replace it as the canonical engineering commit.

## 1. Purpose / Why

Sprint176 closes a proven P0/P1 merchant end-to-end orchestration gap. The repository already had secure durable primitives for tenant/identity/organization/outlet/device persistence, initial tenant administrator provisioning, and first control principal credential bootstrap, but those primitives assumed prerequisite context and were not composed into one zero-context merchant bootstrap foundation.

## 2. What changed

- Added a bounded `MerchantContextBootstrapService` application owner that composes existing canonical persistence, initial-administrator, and first-control-credential primitives rather than duplicating them.
- Added an exact-tuple bootstrap authority covering tenant, identity, organization, outlet, device, and provisioning identity.
- Added a fresh-tenant state guard so bootstrap fails closed rather than mutating an existing tenant.
- Context graph creation, protected initial tenant administrator provisioning, and first control credential creation execute inside one outer durable transaction.
- Downstream credential-stage failure rolls back tenant, identity, organization, membership, outlet, device, control-role assignment, provisioning journal, and credential state.
- Password length follows the existing first-control-principal policy; plaintext password material is not persisted.
- Application-layer bootstrap contracts remain framework-independent.
- Runtime remains Local/Test/CI only. Preview and Production remain denied.
- No service-provider binding, public route, controller, UI, installer exposure, config activation, production runtime widening, migration execution, deployment, or operational activation was introduced.

## 3. Evidence / Qualification

- Canonical parent: `dd2e5b017706e8ff4145a767b9c5e01199c3728f`.
- Exact engineering head: `775343389659754d85f870eca55f808a0b28eea5`.
- Dedicated Sprint176 run `35043179125`: successful, including exact 8-path/hash qualification and focused atomic bootstrap/rollback regression.
- Governance `35043179113`, PHP Foundation `35043179183`, and M7.1 `35043178985`: successful.
- Surfaced installation, POS-successor, and Final Shift Close preservation controls completed successfully on the exact engineering head.
- Repository-native Product Owner merge authority verified on the exact engineering head.
- Engineering PR #777 squash merged at `af2ed4db8e49c4a75f1e1b743986cc20f3e3b0ff` with a verified GitHub signature.
- Engineering envelope: exactly 8 paths; SHA-256 `f1b48efc2a25623ae55c72b95407a19ef60b63f8dcb933f9d7f137f09a34b974`.
- Canonical reconciliation envelope: exactly 6 paths; SHA-256 `4cc815fdb1c6489ab34334a14033acfe1452b50874f48c9e867e6f6da3858b03`.

## 4. Operational boundaries / NO-GO

Machine-readable operational authority under `ops/final-shift-close/` remains authoritative and unchanged:

- durable activation target selection: `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- selected target: `null`;
- migration #27: `NOT_EXECUTED`;
- permission provisioning: `NONE`;
- real target-bound capability/dependency evidence: absent;
- producer dispatch: `NOT_PERFORMED`;
- runtime allowlist: Local/Test/CI only;
- feature activation: `INACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview / Production: `NOT_AUTHORIZED`;
- updater: `INACTIVE`.

Sprint176 grants no authority to expose merchant onboarding publicly, provision a real merchant, widen the runtime allowlist, execute migrations, deploy, activate Technical Preview/Production, select a durable target, or dispatch producers.

## 5. Next position

Begin Sprint177 bounded discovery only after Sprint176 canonical reconciliation closes. Ask what still blocks a real merchant end-to-end after an atomic merchant-context foundation exists. Prioritize the smallest material P0/P1 gap; likely candidates must be proven from live repository evidence rather than preselected. Do not mechanically return to dashboards or readiness-only work.

## Documentation responsibility

`PROJECT_MANIFEST.md` is the canonical human-readable state. `README.md`, `CHANGELOG.md`, `TASKS.md`, and `ROADMAP.md` are reconciled summaries. Git history, merged PRs, workflows, tests, and machine-readable contracts preserve detailed provenance.

Author by Lab | zefry
