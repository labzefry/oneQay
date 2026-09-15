# oneQay Roadmap

**Roadmap checkpoint:** Sprint173 closed canonically  
**Canonical engineering baseline:** `933b06d0790834fb830ca9a55b443db69eca65f0`  
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint173 horizon

Sprint173 closed `INSTALLATION_RELEASE_RUNTIME_REQUIREMENTS_READINESS`, moving PHP minimum-version and required-extension ownership into the governed release manifest so installer readiness cannot silently drift from an immutable release artifact's runtime contract.

The release manifest now supplies bounded `runtime_requirements.php_min` and `runtime_requirements.php_extensions`. Missing, malformed, duplicated, unsafe, or unsatisfied requirements fail closed. The installer-owned `REQUIRED_EXTENSIONS` hardcode was removed while existing environment, filesystem, artifact identity/integrity, database compatibility, and redaction controls remain preserved.

Engineering PR #771 squash merged at `933b06d0790834fb830ca9a55b443db69eca65f0`. Engineering envelope: 3 paths, SHA-256 `83593746e455ea2aa7353482e6b1c35faac4c740b2b9bb897e93bcc71fc1748b`.

Superseded PR #768 exposed stale Sprint169 workflow coupling to `REQUIRED_EXTENSIONS`. Workflow-only successor correction PR #769 squash merged at `e28c2b01aa76ad770896c6eb21b398e8cb188fdb` without changing application source or operational authority.

Canonical reconciliation envelope: 6 paths, SHA-256 `319054712753f696394ff98959688230e9091065fcdae394f7231bd9b6a01ab6`.

## Product progression through Sprint173

The product combines tenant/security/API/POS operational foundations with a canonical installation-readiness owner that now qualifies release-governed runtime requirements, canonical configuration, filesystem write surfaces, governed artifact identity/integrity, and deterministic database compatibility prerequisites. These checks remain intentionally separate from real artifact transport/extraction, database execution, schema mutation, migrations, administrator bootstrap, environment mutation, installer exposure, updater activation, and deployment.

## Operational boundary

Machine-readable operational state under `ops/final-shift-close/` remains authoritative. Selected durable target remains `null`; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; producer dispatch remains not performed; runtime allowlist remains Local/Test/CI; Final Shift Close remains inactive; deployment/Technical Preview/Production remain unauthorized; updater remains inactive.

## Sprint174 selection rule

Begin Sprint174 bounded discovery from fully reconciled Sprint173. Prioritize the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap rather than mechanically extending the installer or returning to dashboard expansion.

No artifact transport/extraction, database connectivity execution, administrator creation, environment mutation, migration/seeder execution, updater control-plane mutation, privileged updater UI, deployment, or operational activation objective is preselected. Live repository evidence must prove the next bounded gap first.

Author by Lab | zefry
