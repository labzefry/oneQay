# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management  
**Repository owner / attribution:** Lab | zefry  
**Default branch:** `main`  
**Status date:** 2026-09-15

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint170  
**Objective:** `INSTALLATION_FILESYSTEM_READINESS`  
**Canonical engineering commit:** `aa46ad0752f8ae2da145eb0c0f6324a3c5be6f25`  
**Engineering PR:** #758 — `Sprint170: add installation filesystem readiness`  
**Final engineering head:** `00acde00e301f3f8aa3bf83b6b04f864d71d0dcd`  
**Sprint170 regression:** `34944847584` — successful  
**Governance Required Checks:** `34944847461` — successful  
**PHP Foundation Regression:** `34944847737` — successful  
**M7.1 Application Regression:** `34944847844` — successful  
**Engineering envelope:** 3 paths — `7a86c9fbe8d4e87bbcdc3bf72ad618649d9d2cebfe20e2eba51f841ef685b877`  
**Canonical reconciliation envelope:** 6 paths — `76de10f095cf53b630f96da884ebf6c1f4e581c4dc0f773312415c7788669b98`  
**Next position:** Sprint171 bounded discovery from the fully reconciled Sprint170 checkpoint; no objective preselected.

> `aa46ad0752f8ae2da145eb0c0f6324a3c5be6f25` is the canonical Sprint170 engineering evidence. The reconciliation squash must never replace it as the canonical engineering commit.

## 1. Purpose / Why

Sprint170 closes the next material installer production-readiness prerequisite after Sprint169. The repository now verifies that only the canonical Laravel runtime directories required by oneQay exist and are writable before installation can be considered ready, without mutating permissions or exposing an installer surface.

## 2. What changed

- Extended the canonical `App\Infrastructure\Installation\SecureInstallationReadiness` owner; no duplicate installer owner was introduced.
- Added fail-closed filesystem readiness for exactly:
  - `bootstrap/cache`;
  - `storage/framework/cache`;
  - `storage/framework/sessions`;
  - `storage/framework/views`;
  - `storage/logs`.
- Missing or non-writable required paths fail readiness with relative-path evidence only.
- Normal execution inspects canonical application-root paths read-only; deterministic injected path state is available for regression qualification.
- Existing PHP runtime, extension, environment, application-key, production-debug, HTTPS, and secret-redaction behavior remains preserved.
- No `chmod`, `chown`, `mkdir`, environment mutation, migration execution, administrator creation, installer route, deployment, updater, or production activation was introduced.

## 3. Evidence / Qualification

- Parent canonical post-Sprint169 checkpoint: `eb19f848057549cec1bb42d10a79426e1a683fe9`.
- Exact engineering head: `00acde00e301f3f8aa3bf83b6b04f864d71d0dcd`.
- Dedicated Sprint170 run `34944847584`: successful.
- Governance `34944847461`, PHP Foundation `34944847737`, and M7.1 `34944847844`: successful.
- Repository-native Product Owner merge authority verified on the exact engineering head.
- Engineering PR #758 squash merged at `aa46ad0752f8ae2da145eb0c0f6324a3c5be6f25`.
- Engineering envelope: exactly 3 paths; SHA-256 `7a86c9fbe8d4e87bbcdc3bf72ad618649d9d2cebfe20e2eba51f841ef685b877`.
- Canonical reconciliation envelope: exactly 6 paths; SHA-256 `76de10f095cf53b630f96da884ebf6c1f4e581c4dc0f773312415c7788669b98`.

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

Sprint170 is source readiness only and grants none of those operational authorities.

## 5. Next position

Begin Sprint171 bounded discovery only after Sprint170 canonical reconciliation closes. Prioritize the smallest material non-duplicative P0/P1 business-completeness or production-readiness gap. For installer progression, prove the next prerequisite before freezing scope; package/application prerequisite qualification is a candidate, but no Sprint171 objective is preselected. Do not infer deployment, migration execution, environment mutation, administrator bootstrap, or runtime activation authority.

## Documentation responsibility

`PROJECT_MANIFEST.md` is the canonical human-readable state. `README.md`, `CHANGELOG.md`, `TASKS.md`, and `ROADMAP.md` are reconciled summaries. Git history, merged PRs, workflows, tests, and machine-readable contracts preserve detailed provenance.

Author by Lab | zefry
