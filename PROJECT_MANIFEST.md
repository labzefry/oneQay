# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-18

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint181
**Objective:** `INSTALLATION_READINESS_WIZARD_DELIVERY_FOUNDATION`
**Canonical engineering commit:** `deb999fcd0694ba85c132d1b490bd90c0dc86309`
**Engineering PR:** #789 — `Sprint181: deliver installation readiness wizard foundation`
**Final engineering head:** `c12595cd88938adaea3f470b74c315b34732bf6b`
**Exact-head surfaced qualification:** 66/66 successful
**Engineering envelope:** 4 paths — `dffcd90da1967e207fe5b65007c354ce0decb1f5d781db9733623cb9ca807e04`
**Canonical reconciliation envelope:** 6 paths — `4fd83153da1067e5fa7a3f8ed145af7e8cbde3c3cba24149a51cd10770d3d955`
**Previous canonical checkpoint:** Sprint180 reconciliation `d819b94179948477d0d485ed41106ac093838fe6`
**Next position:** Sprint182 bounded discovery from the fully reconciled Sprint181 checkpoint.

> `deb999fcd0694ba85c132d1b490bd90c0dc86309` is the canonical Sprint181 engineering evidence. The Sprint181 reconciliation squash must not replace it as the canonical engineering commit.

## 1. Purpose / Why

Sprint181 converts the existing Sprint169–Sprint175 installation-readiness foundations into an operator-usable, read-only installation preflight surface. Before Sprint181, the readiness engine existed only as source/tests and could not be consumed through the application UI.

## 2. What changed

- Reused the existing `/system/update` read-only operator surface; no route was added.
- Preserved the updater control plane, install hard-disablement, privileged updater isolation, and deployment-authority boundary.
- Added an Installation Readiness Wizard section that renders deterministic READY/BLOCKED checks without exposing mutation controls.
- Reused `SecureInstallationReadiness` as the authoritative readiness evaluator rather than duplicating installation policy.
- Observes PHP/runtime and filesystem facts from the server.
- Loads a governed release manifest only when an actual `release/manifest.json` exists.
- Computes artifact filename, size, and SHA-256 only when the governed artifact exists beside that manifest.
- Performs read-only database connectivity, engine/version, charset, UTC/timezone, schema-state, and bounded grant observation only when database configuration is present.
- Host-platform capabilities that cannot be truthfully proven from PHP process state remain explicitly unresolved/fail-closed.
- The wizard exposes no form, fetch request, install action, migration, seeding, config write, deployment, activation, or updater mutation.
- No secret/raw database grant material is returned in Inertia props.

## 3. Evidence / Qualification

- Canonical parent before engineering: `d819b94179948477d0d485ed41106ac093838fe6`.
- Exact engineering head: `c12595cd88938adaea3f470b74c315b34732bf6b`.
- All 66 surfaced PR-triggered workflow runs completed successfully on the exact engineering head.
- Dedicated Sprint181 regression, read-only updater UI regression, privileged updater security, Governance, PHP Foundation, M7.1, Sprint169, and Sprint175 gates succeeded.
- Repository-native Product Owner merge authority succeeded for the exact engineering head.
- Engineering PR #789 squash merged at `deb999fcd0694ba85c132d1b490bd90c0dc86309`.
- Engineering envelope: exactly 4 paths; SHA-256 `dffcd90da1967e207fe5b65007c354ce0decb1f5d781db9733623cb9ca807e04`.
- Canonical reconciliation envelope: exactly 6 paths; SHA-256 `4fd83153da1067e5fa7a3f8ed145af7e8cbde3c3cba24149a51cd10770d3d955`.

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

A READY installation preflight is evidence only. It does not grant authority to write environment configuration, execute migrations/seeding, provision real identities/permissions, deploy, activate the updater, Technical Preview, or Production.

## 5. Next position

Begin Sprint182 bounded discovery from the fully reconciled Sprint181 state. Select the smallest material P0/P1 blocker that advances the installation/onboarding/merchant journey without crossing operational authority.

## Documentation responsibility

`PROJECT_MANIFEST.md` is the canonical human-readable state. `README.md`, `CHANGELOG.md`, `TASKS.md`, and `ROADMAP.md` are reconciled summaries. Git history, merged PRs, workflows, tests, and machine-readable contracts preserve detailed provenance.

Author by Lab | zefry
