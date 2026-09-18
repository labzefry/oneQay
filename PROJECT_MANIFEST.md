# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management  
**Repository owner / attribution:** Lab | zefry  
**Default branch:** `main`  
**Status date:** 2026-09-19

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint184  
**Objective:** `PREBOOT_INSTALLATION_CONFIGURATION_PREPARATION`  
**Canonical engineering commit:** `dfb65d2782a580108d8ccd9a6f9203720fa036b7`  
**Engineering PR:** #797 — `Sprint184: prepare secure preboot installation configuration`  
**Final engineering head:** `a22d98f18618be3ccf5ff8274ebf5528b33f01d7`  
**Exact-head qualification:** 72/72 successful  
**Canonical main-push M7.5 qualification:** run `35378584615` — SUCCESS  
**Engineering envelope:** 9 paths — `e2537851052c57b1ec3b7d2e99e1b5d0208fb3c54da207d5280682eb85c686a0`  
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`  
**Previous canonical checkpoint:** Sprint183 reconciliation `15b8a048e5d604ddee0df41bf7964313334e36b9`  
**Next position:** Sprint185 bounded discovery from the fully reconciled Sprint184 checkpoint.

> `dfb65d2782a580108d8ccd9a6f9203720fa036b7` is the canonical Sprint184 engineering evidence. The reconciliation squash must not replace it as the canonical engineering commit.

## 1. Purpose / Why

Sprint184 closes the material gap between governed release readiness and an operator-capable installation setup step. Prior sprints could build, qualify, and preflight a governed release, but configuration preparation still required an external/manual process before the application runtime could be safely initialized.

## 2. What changed

- Added a secure pre-boot installation surface that can operate before Laravel runtime configuration is active.
- Bound installation preparation to the exact immutable governed release identity.
- Added a private, expiring, one-time installation authority whose submitted token is verified against a stored SHA-256 digest.
- Added bounded validation for HTTPS application URL and MySQL-compatible runtime configuration.
- Generates a fresh application key during configuration preparation.
- Writes configuration only to the private shared `.env.pending` boundary through an atomic write.
- Consumes the one-time authority after successful preparation and rejects replay once pending or active runtime configuration exists.
- Packages `public-surface/install.php` and the private installation implementation inside the governed M7.5 release artifact.
- Preserves Technical Preview, persistence, and system-update controls as disabled in the prepared configuration.
- Preserves legacy M7.5 and Sprint32/Sprint33/Sprint34 historical qualification through exact-envelope compatibility only; application, authentication, recovery, and migration source semantics remain unchanged.
- The operator-facing pre-boot surface is responsive and explicitly communicates migration, Technical Preview, and Production NO-GO.

## 3. Evidence / Qualification

- Canonical parent before Sprint184 engineering: `15b8a048e5d604ddee0df41bf7964313334e36b9`.
- Final engineering head `a22d98f18618be3ccf5ff8274ebf5528b33f01d7` completed 72/72 pull-request workflows successfully.
- Dedicated Sprint184 regression passed PHP/shell validity, authority/replay safety, pending-only configuration, governed artifact packaging, release binding, deterministic reproduction, and source cleanliness.
- Repository-native Product Owner merge authority succeeded on the exact qualified head.
- PR #797 squash merged at `dfb65d2782a580108d8ccd9a6f9203720fa036b7`.
- Canonical main-push M7.5 run `35378584615` completed successfully.
- Final engineering envelope: exactly nine paths; SHA-256 `e2537851052c57b1ec3b7d2e99e1b5d0208fb3c54da207d5280682eb85c686a0`.
- Canonical reconciliation envelope: exactly eight paths; SHA-256 `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

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

Sprint184 prepares configuration only. It does not create an active `.env`, execute migrations, provision production permissions, select a durable target, activate the updater, deploy an artifact, or activate Technical Preview or Production.

## 5. Next position

Begin Sprint185 bounded discovery from fully reconciled Sprint184. Select the smallest material P0/P1 blocker remaining after secure pre-boot configuration preparation, prioritizing completion of the governed installation/onboarding journey without crossing operational activation boundaries.

## Documentation responsibility

`PROJECT_MANIFEST.md` is the canonical human-readable state. `README.md`, `CHANGELOG.md`, `TASKS.md`, and `ROADMAP.md` are reconciled summaries. Git history, merged PRs, workflows, tests, and machine-readable contracts preserve detailed provenance.

Author by Lab | zefry