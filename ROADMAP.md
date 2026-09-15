# oneQay Roadmap

**Roadmap checkpoint:** Sprint174 closed canonically
**Canonical engineering baseline:** `3937cfc56d2615262160c9592d204715eb80ec89`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint174 horizon

Sprint174 closed `INSTALLATION_RELEASE_COMPATIBILITY_POLICY_READINESS`, requiring the governed release manifest to carry the compatibility metadata needed to decide whether an immutable release is acceptable for an installation context instead of relying on documentation-only release policy.

The release manifest now requires bounded compatibility policy for release version, build/provenance reference, supported-current-version range, deployment compatibility, rollback compatibility, public-bootstrap/layout compatibility, and release-notes reference. Missing, malformed, inverted, unsafe, or incompatible policy fails closed without leaking untrusted values.

Engineering PR #773 squash merged at `3937cfc56d2615262160c9592d204715eb80ec89`. Engineering envelope: 3 paths, SHA-256 `1f4a5333d32f79e57ac51d8c9ea8b1a1b3b0342d58b6439fbfe127d3a235aa91`.

Canonical reconciliation envelope: 6 paths, SHA-256 `c620402ddfb186f47fd0994f5751c3453811f08ca870400478442a1f9095075b`.

## Product progression through Sprint174

The product combines tenant/security/API/POS operational foundations with a canonical installation-readiness owner that now qualifies governed release identity, runtime requirements, release compatibility policy, canonical configuration, filesystem write surfaces, immutable artifact identity/integrity, and deterministic database compatibility prerequisites. These checks remain intentionally separate from real artifact transport/extraction, database execution, schema mutation, migrations, administrator bootstrap, environment mutation, installer exposure, updater activation, and deployment.

## Operational boundary

Machine-readable operational state under `ops/final-shift-close/` remains authoritative. Selected durable target remains `null`; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; producer dispatch remains not performed; runtime allowlist remains Local/Test/CI; Final Shift Close remains inactive; deployment/Technical Preview/Production remain unauthorized; updater remains inactive.

## Sprint175 selection rule

Begin Sprint175 bounded discovery from fully reconciled Sprint174. Prioritize the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap rather than mechanically extending installer checks or returning to dashboard expansion.

Host/platform capability readiness remains a known candidate from installer Step 2, but no objective is preselected. No artifact transport/extraction, database connectivity execution, administrator creation, environment mutation, migration/seeder execution, updater control-plane mutation, privileged updater UI, deployment, or operational activation objective is pre-authorized. Live repository evidence must prove the next bounded gap first.

Author by Lab | zefry
