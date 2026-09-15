# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-15

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint175
**Objective:** `INSTALLATION_GOVERNED_HOST_PLATFORM_REQUIREMENTS_READINESS`
**Canonical engineering commit:** `6357d883fe04ec0515f9d21c6adc0ee907787cde`
**Engineering PR:** #775 — `Sprint175: add governed host platform requirements readiness`
**Final engineering head:** `7eebbdb2a71b4cb35dafac58e6df2c955866264c`
**Sprint175 regression:** `34998600504` — successful
**Governance Required Checks:** `34998600362` — successful
**PHP Foundation Regression:** `34998600326` — successful
**M7.1 Application Regression:** `34998600367` — successful
**Engineering envelope:** 3 paths — `3b06f39902fda4e43096b622cffad62ad4308652f760a300c44d5a54616ef8e0`
**Canonical reconciliation envelope:** 6 paths — `be033502c6e72d211415751966e6dc48e3453ce6fae6003d57b3da807cee1460`
**Previous canonical checkpoint:** Sprint174 reconciliation `6b41347fc3174893a0287462d6b68aaa6be99924`
**Next position:** Sprint176 bounded discovery from the fully reconciled Sprint175 checkpoint; no objective preselected.

> `6357d883fe04ec0515f9d21c6adc0ee907787cde` is the canonical Sprint175 engineering evidence. The Sprint175 reconciliation squash must not replace it as the canonical engineering commit.

## 1. Purpose / Why

Sprint175 closes the installer Step 2 host/platform readiness gap proven from `INSTALLER.md` and `RELEASE.md`. Host requirements are now governed by the immutable release contract instead of being hardcoded in the installer, while observed target facts are evaluated deterministically by the existing canonical readiness owner.

## 2. What changed

- Reused canonical `App\Infrastructure\Installation\SecureInstallationReadiness`; no parallel host inspector or installer owner was introduced.
- Governed Release Manifest v1 now requires bounded `host_requirements`.
- Host requirements cover supported OS families, web-server interfaces, minimum memory, minimum execution-time budget, minimum free disk, and a canonical required-capability set.
- Required capabilities cover HTTPS, DNS, time synchronization, outbound allowlisting, scheduler, archive support, temporary-directory readiness, and required tools.
- Observed host/platform state is supplied as deterministic facts; the readiness owner performs no shell, command, network, DNS, scheduler, archive, package-install, or filesystem mutation.
- Missing, malformed, unsupported, or insufficient host facts fail closed.
- Unlimited observed execution time is represented deterministically without weakening the governed minimum requirement.
- Failure output does not echo untrusted host/platform values.
- Existing PHP/runtime, environment/key/debug/HTTPS, filesystem-write, governed artifact identity/integrity, release compatibility-policy, deterministic database compatibility, attribution, and redaction controls remain preserved.
- No artifact publication/download/extraction, database execution, environment/schema/configuration mutation, migration/seeder execution, credential or administrator provisioning, installer exposure, updater activation, deployment, Technical Preview, Production, durable-target selection, or producer dispatch was introduced.

## 3. Evidence / Qualification

- Canonical parent: `6b41347fc3174893a0287462d6b68aaa6be99924`.
- Exact engineering head: `7eebbdb2a71b4cb35dafac58e6df2c955866264c`.
- Dedicated Sprint175 run `34998600504`: successful.
- Governance `34998600362`, PHP Foundation `34998600326`, and M7.1 `34998600367`: successful.
- Surfaced Sprint169–Sprint174, POS successor, and Final Shift Close historical controls completed successfully on the exact engineering head.
- Repository-native Product Owner merge authority verified on the exact engineering head.
- Engineering PR #775 squash merged at `6357d883fe04ec0515f9d21c6adc0ee907787cde`.
- Engineering envelope: exactly 3 paths; SHA-256 `3b06f39902fda4e43096b622cffad62ad4308652f760a300c44d5a54616ef8e0`.
- Canonical reconciliation envelope: exactly 6 paths; SHA-256 `be033502c6e72d211415751966e6dc48e3453ce6fae6003d57b3da807cee1460`.

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

Governed host/platform readiness grants no authority to probe a host, execute shell commands, open network/DNS connections, mutate scheduler/filesystem/configuration, install packages, transport/extract artifacts, connect to production databases, migrate, provision, expose an installer, update, deploy, or activate any runtime.

## 5. Next position

Begin Sprint176 bounded discovery only after Sprint175 canonical reconciliation closes. Prioritize the smallest material non-duplicative P0/P1 business-completeness or production-readiness gap. Do not mechanically add further installer checks; live canonical evidence must prove the next blocker to a real merchant end-to-end path or production-ready installation lifecycle.

## Documentation responsibility

`PROJECT_MANIFEST.md` is the canonical human-readable state. `README.md`, `CHANGELOG.md`, `TASKS.md`, and `ROADMAP.md` are reconciled summaries. Git history, merged PRs, workflows, tests, and machine-readable contracts preserve detailed provenance.

Author by Lab | zefry
