# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-15

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint169
**Objective:** `SECURE_INSTALLATION_READINESS_FOUNDATION`
**Canonical engineering commit:** `2a53d9db9547340bd4d791b34fabb80ec600fc8b`
**Engineering PR:** #756 — `Sprint169: secure installation readiness foundation`
**Final engineering head:** `5d2b27404352da7f81723f08977de34aef00faf7`
**Sprint169 regression:** `34936197402` — successful
**Governance Required Checks:** `34936197759` — successful
**PHP Foundation Regression:** `34936197571` — successful
**M7.1 Application Regression:** `34936197394` — successful
**Engineering envelope:** 3 paths — `2bfeedcabc1a09617876a6ce0719cb31a3246c157ad78472feda5db18f9c4fd1`
**Canonical reconciliation envelope:** 6 paths — `7f6b2b61e87d8891dc3c4d1a047ac92f9209d3286c445e77518f796179ce4d93`
**Next position:** Sprint170 bounded discovery from the fully reconciled Sprint169 checkpoint; no objective preselected.

> `2a53d9db9547340bd4d791b34fabb80ec600fc8b` is the canonical Sprint169 engineering evidence. The reconciliation squash must never replace it as the canonical engineering commit.

## 1. Purpose / Why

Sprint169 deliberately shifted production-readiness work away from repeated read-only operational dashboards. The repository had a detailed installer specification but no executable canonical installer infrastructure owner. This Sprint establishes the first bounded, non-destructive installation-readiness foundation without exposing or activating an installer.

## 2. What changed

- Added canonical `App\Infrastructure\Installation\SecureInstallationReadiness` owner.
- Added deterministic read-only preflight for minimum PHP runtime, required PHP extensions, required environment configuration, application-key readiness, production debug posture, and production HTTPS URL posture.
- Readiness output is deliberately redacted and does not echo database credentials or other supplied secrets.
- Placeholder/missing application key, missing configuration, missing required extensions, unsupported PHP, production debug, or non-HTTPS production URL fail closed.
- Added focused source regression and dedicated Sprint169 workflow.
- No installer route, administrator creation, environment-file mutation, migration execution, permission provisioning, deployment, updater activation, or operational activation was introduced.

## 3. Evidence / Qualification

- Parent canonical post-Sprint168 checkpoint: `4b9240e6cfb0f4680066aef190d5a1cf03fc8515`.
- Exact engineering head: `5d2b27404352da7f81723f08977de34aef00faf7`.
- Dedicated Sprint169 run `34936197402`: successful.
- Governance `34936197759`, PHP Foundation `34936197571`, and M7.1 `34936197394`: successful.
- Repository-native Product Owner merge authority applied to the exact engineering head.
- Engineering PR #756 squash merged at `2a53d9db9547340bd4d791b34fabb80ec600fc8b`.
- Engineering envelope: exactly 3 paths; SHA-256 `2bfeedcabc1a09617876a6ce0719cb31a3246c157ad78472feda5db18f9c4fd1`.

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

Sprint169 is source readiness only and grants none of those operational authorities.

## 5. Next position

Begin Sprint170 bounded discovery only after Sprint169 canonical reconciliation closes. Prioritize the smallest material non-duplicative P0/P1 business-completeness or production-readiness gap. For installation readiness, prefer a coherent next end-to-end prerequisite such as deterministic writable-path/package/application prerequisite qualification rather than exposing a web installer or executing migrations. Do not infer deployment or runtime activation authority.

## Documentation responsibility

`PROJECT_MANIFEST.md` is the canonical human-readable state. `README.md`, `CHANGELOG.md`, `TASKS.md`, and `ROADMAP.md` are reconciled summaries. Git history, merged PRs, workflows, tests, and machine-readable contracts preserve detailed provenance.

Author by Lab | zefry
