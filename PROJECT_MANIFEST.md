# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-18

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint183
**Objective:** `GOVERNED_M7_5_RELEASE_WORKFLOW_EXECUTION_RESTORATION`
**Canonical engineering commit:** `cd3facf81e3b1734353656da3ba5800607900fcb`
**Initial engineering PR:** #793 — `Sprint183: restore governed M7.5 workflow execution`
**Initial engineering squash:** `03804129dac67c85fea3540d421a9b49adc3eb13`
**Corrective engineering PR:** #794 — `Sprint183: correct non-PR M7.5 historical compatibility`
**Final engineering head:** `f3527999a500463e9eea3f8b9b22dec24a34e93e`
**Exact-head corrective qualification:** 69/69 successful
**Canonical main-push M7.5 qualification:** run `35369464318` — SUCCESS
**Engineering envelope:** 1 path — `bcec6fc13a26f5c88f4408d76d362195ca9d546cc2df6d6c388a67640b93cce2`
**Canonical reconciliation envelope:** 6 paths — `09fc0a9ae283def9c130b48fa756b17624ae0fbf3f87ad988c40605fcaf362c6`
**Previous canonical checkpoint:** Sprint182 reconciliation `b876e8b0ce245d2d56697de4ebdc7c3fb695ff69`
**Next position:** Sprint184 bounded discovery from the fully reconciled Sprint183 checkpoint.

> `cd3facf81e3b1734353656da3ba5800607900fcb` is the canonical Sprint183 engineering evidence. The initial Sprint183 squash and the Sprint183 reconciliation squash must not replace it as the canonical engineering commit.

## 1. Purpose / Why

Sprint183 restores executable governed release automation. Sprint182 proved the release-artifact-to-installer-readiness bridge through a dedicated workflow, but the canonical M7.5 release workflow itself still failed GitHub Actions startup before a job could be created.

## 2. What changed

- Preserved the canonical M7.5 release artifact behavior, Release Manifest v1 contract, and Sprint182 installer-readiness bridge.
- Split the oversized historical Web regression command at the established composer-test boundary.
- Persisted only the two compatibility booleans required across the split through `GITHUB_ENV`.
- Kept every `run` command below the GitHub Actions command-size boundary that had prevented workflow startup.
- Restored M7.5 as a real pull-request workflow; both split historical Web regression steps execute successfully.
- Restored post-M7.4 historical compatibility by temporarily isolating newer POS persistence repositories only while M7.4 synthetic regression executes, then restoring them deterministically.
- Post-merge main push of the initial engineering squash exposed a separate non-PR compatibility gap: current migrations #10–#27 were visible to historical M7.2 because PR-diff classification is unavailable on push.
- Corrective PR #794 makes non-PR execution enter the same schema-free historical lane by isolating migrations #10–#27 for the historical checks and setting the same compatibility state used by the proven PR lane.
- Migration and application source remain byte-preserved; these are workflow fixture-isolation changes only.
- Final canonical main push M7.5 run `35369464318` completed successfully through historical regressions, packaging, installer sidecar materialization, manifest/artifact binding, deterministic reproduction, artifact upload, and tracked-source cleanliness.

## 3. Evidence / Qualification

- Canonical parent before Sprint183 engineering: `b876e8b0ce245d2d56697de4ebdc7c3fb695ff69`.
- Initial engineering head `798e2f1a223bbce7ed0032bb4996e292a337bb72` completed 69/69 pull-request workflows successfully.
- Initial PR #793 squash merged at `03804129dac67c85fea3540d421a9b49adc3eb13`.
- Main-push run after the initial squash started correctly but revealed non-PR historical fixture incompatibility; reconciliation was intentionally blocked.
- Corrective head `f3527999a500463e9eea3f8b9b22dec24a34e93e` completed 69/69 pull-request workflows successfully.
- Repository-native Product Owner merge authority succeeded on the exact corrective head.
- Corrective PR #794 squash merged at `cd3facf81e3b1734353656da3ba5800607900fcb`.
- Canonical main-push M7.5 run `35369464318` completed successfully.
- Final engineering envelope: exactly one path; SHA-256 `bcec6fc13a26f5c88f4408d76d362195ca9d546cc2df6d6c388a67640b93cce2`.
- Canonical reconciliation envelope: exactly six paths; SHA-256 `09fc0a9ae283def9c130b48fa756b17624ae0fbf3f87ad988c40605fcaf362c6`.

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

Restored release automation is build and qualification evidence only. It does not grant authority to deploy the generated artifact, execute migrations, provision production permissions, activate the updater, Technical Preview, or Production.

## 5. Next position

Begin Sprint184 bounded discovery from fully reconciled Sprint183. Select the smallest material P0/P1 blocker remaining in the installation/onboarding journey now that governed M7.5 release automation and the installer-preflight bridge are executable.

## Documentation responsibility

`PROJECT_MANIFEST.md` is the canonical human-readable state. `README.md`, `CHANGELOG.md`, `TASKS.md`, and `ROADMAP.md` are reconciled summaries. Git history, merged PRs, workflows, tests, and machine-readable contracts preserve detailed provenance.

Author by Lab | zefry
