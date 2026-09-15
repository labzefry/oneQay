# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-15

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint171
**Objective:** `INSTALLATION_GOVERNED_RELEASE_ARTIFACT_READINESS`
**Canonical engineering commit:** `cbf26a53c1a784b8e6eda65cc1d047c90ea590e8`
**Engineering PR:** #760 — `Sprint171: add governed release artifact installation readiness`
**Final engineering head:** `b2dbd36edb75174a944a77cc547811938e772ed0`
**Sprint171 regression:** `34968787888` — successful
**Governance Required Checks:** `34968787247` — successful
**PHP Foundation Regression:** `34968787525` — successful
**M7.1 Application Regression:** `34968787729` — successful
**Engineering envelope:** 3 paths — `cf2f56c6ba42404726fe225a559b7262580191a9bf1c8b03c4ebf39fe2f29d88`
**Canonical reconciliation envelope:** 6 paths — `a8627a615280e7592638964a5d7f496f77310ef28f085439f2ba584d6a97d301`
**Next position:** Sprint172 bounded discovery from the fully reconciled Sprint171 checkpoint; no objective preselected.

> `cbf26a53c1a784b8e6eda65cc1d047c90ea590e8` is the canonical Sprint171 engineering evidence. The reconciliation squash must never replace it as the canonical engineering commit.

## 1. Purpose / Why

Sprint171 closes the next material installer production-readiness prerequisite after filesystem readiness. The repository already defines a governed immutable release contract in `RELEASE.md` and ADR-009, but installation readiness had no executable boundary proving that a candidate manifest and observed artifact identity satisfy the minimum trusted release requirements before any download, extraction, or activation work.

## 2. What changed

- Extended canonical `App\Infrastructure\Installation\SecureInstallationReadiness`; no parallel installer or updater owner was introduced.
- Added fail-closed installer-facing governed release manifest readiness for schema version, canonical product/repository identity, release ID/channel/source commit, safe artifact filename/type/size/SHA-256, migration classification, and `Lab | zefry` attribution.
- Initial readiness accepts only manifest schema version `1` and migration classification `NO_SCHEMA_CHANGE`.
- Added observed artifact identity verification for exact filename, byte size, and SHA-256 equality with the governed manifest.
- Artifact digest comparison uses constant-time `hash_equals` after normalized hexadecimal validation.
- Invalid or incomplete manifest/artifact state fails closed without echoing untrusted values, digests, filesystem paths, or supplied secrets.
- Existing PHP runtime, extension, required environment, application-key, production-debug, HTTPS, and filesystem-write readiness remains preserved.
- No arbitrary URL, network download, archive extraction, external signature/provenance verification claim, environment mutation, migration execution, administrator creation, installer route, deployment, updater activation, or production activation was introduced.

## 3. Evidence / Qualification

- Parent canonical post-Sprint170 checkpoint: `ceeb02048236a0a88b2a5f725576ecc23b001d07`.
- Exact engineering head: `b2dbd36edb75174a944a77cc547811938e772ed0`.
- Dedicated Sprint171 run `34968787888`: successful.
- Governance `34968787247`, PHP Foundation `34968787525`, and M7.1 `34968787729`: successful.
- Surfaced POS successor and Final Shift Close historical controls completed successfully on the exact engineering head.
- Repository-native Product Owner merge authority verified on the exact engineering head.
- Engineering PR #760 squash merged at `cbf26a53c1a784b8e6eda65cc1d047c90ea590e8`.
- Engineering envelope: exactly 3 paths; SHA-256 `cf2f56c6ba42404726fe225a559b7262580191a9bf1c8b03c4ebf39fe2f29d88`.
- Canonical reconciliation envelope: exactly 6 paths; SHA-256 `a8627a615280e7592638964a5d7f496f77310ef28f085439f2ba584d6a97d301`.

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

Release publication, artifact download/extraction, signature/provenance verification, installer exposure, deployment, and runtime activation remain separately gated. Sprint171 grants none of those authorities.

## 5. Next position

Begin Sprint172 bounded discovery only after Sprint171 canonical reconciliation closes. Prioritize the smallest material non-duplicative P0/P1 business-completeness or production-readiness gap. Continue installer/release progression only where live repository discovery proves the next missing prerequisite; do not preselect download, extraction, activation, migration, privileged updater UI, or deployment work.

## Documentation responsibility

`PROJECT_MANIFEST.md` is the canonical human-readable state. `README.md`, `CHANGELOG.md`, `TASKS.md`, and `ROADMAP.md` are reconciled summaries. Git history, merged PRs, workflows, tests, and machine-readable contracts preserve detailed provenance.

Author by Lab | zefry
