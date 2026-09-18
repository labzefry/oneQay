# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-18

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint182
**Objective:** `GOVERNED_RELEASE_INSTALLATION_PREFLIGHT_BRIDGE_FOUNDATION`
**Canonical engineering commit:** `ceb8fd7839a5077d59247b2cbdd2ba30559c4d25`
**Engineering PR:** #791 — `Sprint182: bridge governed release to installation preflight`
**Final engineering head:** `cc6eba43ccd9dfd316f652aaf133a7372a86380b`
**Exact-head pull-request qualification:** 68/68 successful
**Engineering envelope:** 6 paths — `4968d9fcf35b7a17d67b1909cfd1dad93f2b08b7d82b8c6d30be4dc09f067be3`
**Canonical reconciliation envelope:** 6 paths — `a78e4871e0852ac6c4f466b7f3278efde846a4ae84f5fe589123672e568aea35`
**Previous canonical checkpoint:** Sprint181 reconciliation `2046f7ad4a27bc8b453cf773fcb1b6cd90042a3e`
**Next position:** Sprint183 bounded discovery from the fully reconciled Sprint182 checkpoint.

> `ceb8fd7839a5077d59247b2cbdd2ba30559c4d25` is the canonical Sprint182 engineering evidence. The Sprint182 reconciliation squash must not replace it as the canonical engineering commit.

## 1. Purpose / Why

Sprint182 closes the release-evidence gap between the governed M7.5 artifact builder and the Sprint181 installation-readiness wizard. Before Sprint182, the trusted release artifact and canonical Release Manifest v1 existed, but the installer-facing `release/manifest.json` contract required by `SecureInstallationReadiness` could not be derived and qualified from the governed artifact without manual projection.

## 2. What changed

- Preserved the canonical M7.5 archive builder and Release Manifest v1 contract.
- Added `tools/build-installation-readiness-manifest.php` as the shared trusted-build projection.
- The shared tool binds the installer-facing manifest to exact source SHA, release ID, artifact filename, byte size, and SHA-256.
- Runtime requirements, host requirement policy, compatibility policy, `NO_SCHEMA_CHANGE` classification, and `Lab | zefry` attribution are emitted deterministically.
- The sidecar is validated through the canonical `SecureInstallationReadiness` authority.
- Build-time host/database fixtures are explicitly validator fixtures only; no runtime target capability or READY state is fabricated.
- A deliberately tampered artifact digest must fail `artifact_integrity`.
- M7.5 and the dedicated Sprint182 qualification use the same shared tool.
- Dedicated Sprint182 qualification directly builds the governed artifact, generates the sidecar, validates the canonical manifest, and proves deterministic artifact + sidecar reproduction.
- Sprint32/Sprint33/Sprint34 historical M7.5 isolation was extended through migration #27 only; migration source remains byte-preserved.
- No deployment, migration execution, permission provisioning, updater activation, Technical Preview activation, Production activation, durable-target selection, or producer dispatch was introduced.

## 3. Evidence / Qualification

- Canonical parent before engineering: `2046f7ad4a27bc8b453cf773fcb1b6cd90042a3e`.
- Exact engineering head: `cc6eba43ccd9dfd316f652aaf133a7372a86380b`.
- All 68 pull-request-triggered workflow runs completed successfully on the exact engineering head.
- Dedicated Sprint182 qualification completed successfully through real M7.5 artifact build, canonical readiness validation, tamper rejection, deterministic reproduction, and tracked-source cleanliness.
- Sprint32, Sprint33, Sprint34, Sprint171, Sprint173, Sprint174, Sprint175, Sprint181, Governance, PHP Foundation, and M7.1 gates succeeded.
- Repository-native Product Owner merge authority succeeded for the exact engineering head.
- Engineering PR #791 squash merged at `ceb8fd7839a5077d59247b2cbdd2ba30559c4d25`.
- Engineering envelope: exactly 6 paths; SHA-256 `4968d9fcf35b7a17d67b1909cfd1dad93f2b08b7d82b8c6d30be4dc09f067be3`.
- Canonical reconciliation envelope: exactly 6 paths; SHA-256 `a78e4871e0852ac6c4f466b7f3278efde846a4ae84f5fe589123672e568aea35`.

## 4. Known pre-existing workflow state

The legacy M7.5 push-event workflow startup failure was already present on canonical `main` before Sprint182, including at Sprint181 reconciliation `2046f7ad4a27bc8b453cf773fcb1b6cd90042a3e`. Sprint182 does not claim that historical startup defect as remediated. The dedicated Sprint182 workflow provides the active executable exact-head qualification for the governed artifact-to-installer bridge.

This pre-existing CI debt should be evaluated during Sprint183 bounded discovery because it can block canonical automated release execution even though the Sprint182 bridge itself is proven executable.

## 5. Operational boundaries / NO-GO

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

A valid release/installer manifest is evidence only. It does not grant authority to write environment configuration, execute migrations/seeding, provision real identities/permissions, deploy, activate the updater, Technical Preview, or Production.

## 6. Next position

Begin Sprint183 bounded discovery from the fully reconciled Sprint182 state. First assess the pre-existing M7.5 workflow startup failure as a material installation/release automation blocker, then select the smallest end-to-end P0/P1 correction that preserves the Sprint182 bridge and operational NO-GO.

## Documentation responsibility

`PROJECT_MANIFEST.md` is the canonical human-readable state. `README.md`, `CHANGELOG.md`, `TASKS.md`, and `ROADMAP.md` are reconciled summaries. Git history, merged PRs, workflows, tests, and machine-readable contracts preserve detailed provenance.

Author by Lab | zefry
