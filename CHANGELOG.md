# Changelog

## 2026-09-15 — Sprint173 closed

**Sprint173: Installation Release Runtime Requirements Readiness**

- **Objective:** `INSTALLATION_RELEASE_RUNTIME_REQUIREMENTS_READINESS`.
- Reused canonical `App\Infrastructure\Installation\SecureInstallationReadiness`; no parallel installer/release-readiness owner was introduced.
- Added governed release-manifest `runtime_requirements` with bounded `php_min` and `php_extensions`.
- Removed installer-owned `REQUIRED_EXTENSIONS` hardcode; PHP version and required extensions now derive from the governed release manifest.
- Runtime requirements fail closed for missing/malformed PHP version, empty/oversized extension sets, unsafe extension names, case-insensitive duplicates, or unsatisfied observed runtime facts.
- Untrusted runtime requirement values are not echoed in readiness failure output.
- Existing environment/key/debug/HTTPS, filesystem-write, governed artifact identity/integrity, database compatibility, and redaction readiness remains preserved.
- No artifact download/extraction, network/database connection, environment/schema/configuration mutation, migration/seeder execution, credential/admin provisioning, installer exposure, release publication, deployment, updater activation, Technical Preview, Production, durable-target selection, or producer dispatch was introduced.
- Superseded engineering PR #768 surfaced stale Sprint169 historical coupling to `REQUIRED_EXTENSIONS` and was closed unmerged.
- Workflow-only Sprint169 successor-compatibility correction PR #769 squash merged at `e28c2b01aa76ad770896c6eb21b398e8cb188fdb`; application source and operational state were unchanged.
- Exact engineering head `494503122ebf273fbfcc0791c5afca0e24bb5b29` completed surfaced PR-triggered qualification successfully.
- Sprint173 regression `34991613421`, Governance `34991613429`, PHP Foundation `34991613314`, and M7.1 `34991613882` succeeded.
- Repository-native Product Owner merge authorization verified on the exact engineering head.
- Engineering envelope: 3 paths; SHA-256 `83593746e455ea2aa7353482e6b1c35faac4c740b2b9bb897e93bcc71fc1748b`.
- Engineering PR #771 squash merged at `933b06d0790834fb830ca9a55b443db69eca65f0`.
- Canonical reconciliation envelope: 6 paths; SHA-256 `319054712753f696394ff98959688230e9091065fcdae394f7231bd9b6a01ab6`.
- **Operational NO-GO preserved:** migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; selected durable target `null`; feature activation `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`.
- **Next position:** Sprint174 bounded discovery from canonical post-Sprint173, prioritizing material P0/P1 production-readiness or business-completeness work with no objective preselected.

## Recent material progression

- **Sprint172:** database configuration compatibility readiness; engineering squash `636a07130650f2d3119d450f35cfcfaf2868898e`.
- **Sprint171:** governed release artifact installation readiness; engineering squash `cbf26a53c1a784b8e6eda65cc1d047c90ea590e8`.
- **Sprint170:** installation filesystem readiness; engineering squash `aa46ad0752f8ae2da145eb0c0f6324a3c5be6f25`.
- **Sprint169:** secure installation readiness foundation; engineering squash `2a53d9db9547340bd4d791b34fabb80ec600fc8b`.
- **Sprint168:** closed-shift historical POS performance; engineering squash `d3703a6b18f478acd812e7892c3871ce3aaf7bfa`.
- **Sprint162–Sprint167:** guarded POS operations, cash variance, replenishment, inventory accountability, product performance, and active-shift performance.
- **Sprint156–Sprint161:** operational reporting, cashier, shift start, sale correction, immutable sale history/receipt detail, and catalog/opening-inventory setup.
- **Sprint88–Sprint155:** Final Shift Close source/readiness chain; operational execution did not occur.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

Author by Lab | zefry
