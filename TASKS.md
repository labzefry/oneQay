# oneQay Tasks

**Current canonical engineering checkpoint:** Sprint173 closed  
**Canonical engineering commit:** `933b06d0790834fb830ca9a55b443db69eca65f0`  
**Latest engineering PR:** #771 — `Sprint173: govern installation runtime requirements by release manifest v2`  
**Canonical status authority:** `PROJECT_MANIFEST.md`

## Completed Sprint173 state

Sprint173 materialized `INSTALLATION_RELEASE_RUNTIME_REQUIREMENTS_READINESS` as the next bounded installer/release production-readiness correctness prerequisite.

- [x] Canonical `SecureInstallationReadiness` owner reused.
- [x] Governed release manifest requires `runtime_requirements`.
- [x] `php_min` is validated and drives PHP runtime readiness.
- [x] `php_extensions` is validated and drives loaded-extension readiness.
- [x] Installer-owned `REQUIRED_EXTENSIONS` hardcode removed.
- [x] Empty/oversized extension sets fail closed.
- [x] Unsafe extension identifiers fail closed.
- [x] Case-insensitive duplicate extension requirements fail closed.
- [x] Missing/malformed runtime requirements fail both runtime and release-manifest readiness.
- [x] Runtime failure output does not echo untrusted requirement values.
- [x] Existing environment/key/debug/HTTPS, filesystem, artifact integrity, database compatibility, and redaction readiness preserved.
- [x] No artifact transport/extraction, network/database execution, configuration/schema mutation, migration/seeder execution, administrator provisioning, installer exposure, deployment, or updater activation.
- [x] Superseded PR #768 closed unmerged after stale Sprint169 workflow coupling surfaced.
- [x] Sprint169 workflow-only successor correction PR #769 squash merged at `e28c2b01aa76ad770896c6eb21b398e8cb188fdb`.
- [x] Focused PHP regression.
- [x] Dedicated exact-envelope workflow.
- [x] Exact-head engineering qualification successful.
- [x] Repository-native Product Owner merge authority verified.
- [x] Engineering PR #771 squash merged at `933b06d0790834fb830ca9a55b443db69eca65f0`.

Engineering envelope: 3 paths; SHA-256 `83593746e455ea2aa7353482e6b1c35faac4c740b2b9bb897e93bcc71fc1748b`.

Canonical reconciliation envelope: 6 paths; SHA-256 `319054712753f696394ff98959688230e9091065fcdae394f7231bd9b6a01ab6`.

## Preserved lifecycle state

Machine-readable operational state under `ops/final-shift-close/` remains authoritative and unchanged: selected target `null`; migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; real target-bound capability/dependency evidence absent; producer dispatch not performed; feature activation `INACTIVE`; deployment authority `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`.

## Next engineering position

Begin **Sprint174 bounded discovery** only from fully reconciled Sprint173. Prioritize the smallest material non-duplicative P0/P1 business-completeness or production-readiness gap. Do not preselect artifact transport/extraction, database execution, administrator creation, environment mutation, migration/seeder execution, installer exposure, privileged updater UI, deployment, or operational activation; prove the next canonical gap first.

Author by Lab | zefry
