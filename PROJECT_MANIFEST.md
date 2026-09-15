# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management  
**Repository owner / attribution:** Lab | zefry  
**Default branch:** `main`  
**Status date:** 2026-09-15

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint173  
**Objective:** `INSTALLATION_RELEASE_RUNTIME_REQUIREMENTS_READINESS`  
**Canonical engineering commit:** `933b06d0790834fb830ca9a55b443db69eca65f0`  
**Engineering PR:** #771 — `Sprint173: govern installation runtime requirements by release manifest v2`  
**Final engineering head:** `494503122ebf273fbfcc0791c5afca0e24bb5b29`  
**Sprint173 regression:** `34991613421` — successful  
**Governance Required Checks:** `34991613429` — successful  
**PHP Foundation Regression:** `34991613314` — successful  
**M7.1 Application Regression:** `34991613882` — successful  
**Engineering envelope:** 3 paths — `83593746e455ea2aa7353482e6b1c35faac4c740b2b9bb897e93bcc71fc1748b`  
**Sprint169 successor-compatibility correction:** PR #769 — squash `e28c2b01aa76ad770896c6eb21b398e8cb188fdb`  
**Canonical reconciliation envelope:** 6 paths — `319054712753f696394ff98959688230e9091065fcdae394f7231bd9b6a01ab6`  
**Next position:** Sprint174 bounded discovery from the fully reconciled Sprint173 checkpoint; no objective preselected.

> `933b06d0790834fb830ca9a55b443db69eca65f0` is the canonical Sprint173 engineering evidence. Neither the Sprint169 compatibility correction nor the reconciliation squash may replace it as the canonical engineering commit.

## 1. Purpose / Why

Sprint173 closes a release/installer correctness gap proven from `INSTALLER.md` and `RELEASE.md`: PHP minimum version and required extensions must be versioned by the governed release manifest, not owned as unversioned installer hardcode. The prior readiness owner still embedded its own required-extension list and fixed PHP minimum, which could drift from an immutable release artifact's actual runtime contract.

## 2. What changed

- Reused canonical `App\Infrastructure\Installation\SecureInstallationReadiness`; no parallel installer or release-readiness owner was introduced.
- Added governed manifest `runtime_requirements` containing bounded `php_min` and `php_extensions` requirements.
- Removed installer-owned `REQUIRED_EXTENSIONS` hardcode.
- PHP version and loaded-extension readiness now derive from the governed release manifest supplied to the assessment.
- Runtime requirements validate a bounded semantic PHP version shape, a non-empty extension set, safe extension identifiers, a maximum of 64 entries, and case-insensitive duplicate rejection.
- Missing, malformed, duplicated, or unsafe runtime requirements fail closed without echoing untrusted runtime values.
- Changing a valid manifest runtime contract changes readiness without source modification.
- Existing environment/key/debug/HTTPS, filesystem-write, governed artifact identity/integrity, deterministic database compatibility, and redaction controls remain preserved.
- No artifact download/extraction, network/database connection, environment/schema/configuration mutation, migration/seeder execution, credential or administrator provisioning, installer exposure, release publication, updater activation, deployment, Technical Preview, Production, durable-target selection, or producer dispatch was introduced.

## 3. Evidence / Qualification

- Parent corrected canonical base: `e28c2b01aa76ad770896c6eb21b398e8cb188fdb`.
- Exact engineering head: `494503122ebf273fbfcc0791c5afca0e24bb5b29`.
- Dedicated Sprint173 run `34991613421`: successful.
- Governance `34991613429`, PHP Foundation `34991613314`, and M7.1 `34991613882`: successful.
- Surfaced Sprint169–Sprint172, POS successor, and Final Shift Close historical controls completed successfully on the exact engineering head.
- Repository-native Product Owner merge authority verified on the exact engineering head.
- Engineering PR #771 squash merged at `933b06d0790834fb830ca9a55b443db69eca65f0`.
- Engineering envelope: exactly 3 paths; SHA-256 `83593746e455ea2aa7353482e6b1c35faac4c740b2b9bb897e93bcc71fc1748b`.
- Superseded PR #768 surfaced a stale Sprint169 workflow assertion requiring `REQUIRED_EXTENSIONS` and was closed unmerged.
- Workflow-only correction PR #769 squash merged at `e28c2b01aa76ad770896c6eb21b398e8cb188fdb`; application source and operational state were unchanged.
- Canonical reconciliation envelope: exactly 6 paths; SHA-256 `319054712753f696394ff98959688230e9091065fcdae394f7231bd9b6a01ab6`.

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

Runtime manifest readiness does not grant release publication, artifact transport/extraction, database execution, migration, administrator creation, environment mutation, installer exposure, deployment, updater activation, or runtime activation authority.

## 5. Next position

Begin Sprint174 bounded discovery only after Sprint173 canonical reconciliation closes. Prioritize the smallest material non-duplicative P0/P1 business-completeness or production-readiness gap. Do not preselect download/extraction, database execution, administrator creation, environment mutation, migration/seeder execution, installer exposure, updater activation, deployment, or operational activation.

## Documentation responsibility

`PROJECT_MANIFEST.md` is the canonical human-readable state. `README.md`, `CHANGELOG.md`, `TASKS.md`, and `ROADMAP.md` are reconciled summaries. Git history, merged PRs, workflows, tests, and machine-readable contracts preserve detailed provenance.

Author by Lab | zefry
