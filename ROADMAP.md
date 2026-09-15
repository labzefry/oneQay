# oneQay Roadmap

**Roadmap checkpoint:** Sprint171 closed canonically
**Canonical engineering baseline:** `cbf26a53c1a784b8e6eda65cc1d047c90ea590e8`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint171 horizon

Sprint171 closed `INSTALLATION_GOVERNED_RELEASE_ARTIFACT_READINESS`, extending secure installation readiness with a deterministic, fail-closed governed release manifest and observed artifact identity boundary before any future runtime release mutation.

The capability accepts only manifest schema version 1, canonical `oneQay` / `labzefry/oneQay` identity, supported release channels, immutable source-commit form, safe artifact identity, positive byte size, SHA-256, `NO_SCHEMA_CHANGE`, and `Lab | zefry` attribution. Observed artifact filename, size, and digest must match exactly.

Engineering PR #760 squash merged at `cbf26a53c1a784b8e6eda65cc1d047c90ea590e8`. Engineering envelope: 3 paths, SHA-256 `cf2f56c6ba42404726fe225a559b7262580191a9bf1c8b03c4ebf39fe2f29d88`.

Workflow-only successor compatibility correction PR #762 squash merged at `1e568085d6b9743eb05c73fb9a78c019b8d82f04` without changing application source or operational authority.

Canonical reconciliation envelope: 6 paths, SHA-256 `a8627a615280e7592638964a5d7f496f77310ef28f085439f2ba584d6a97d301`.

## Product progression through Sprint171

The product now combines the existing tenant/security/API/POS operational foundations with a canonical installation-readiness owner that qualifies runtime/configuration/security posture, required filesystem write surfaces, and governed release artifact identity/integrity prerequisites. It remains intentionally separate from artifact transport, extraction, staging, activation, privileged updater mutation, and deployment.

## Operational boundary

Machine-readable operational state under `ops/final-shift-close/` remains authoritative. Selected durable target remains `null`; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; producer dispatch remains not performed; runtime allowlist remains Local/Test/CI; Final Shift Close remains inactive; deployment/Technical Preview/Production remain unauthorized; updater remains inactive.

Release publication, artifact download/extraction, external signature/provenance verification, migration execution, installer exposure, and runtime activation remain separately gated.

## Sprint172 selection rule

Begin Sprint172 bounded discovery from fully reconciled Sprint171. Prioritize the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap rather than mechanically extending the installer or returning to dashboard expansion.

No artifact transport, extraction, updater control-plane mutation, privileged updater UI, deployment, migration, or operational activation objective is preselected. Live repository evidence must prove the next bounded gap first.

Author by Lab | zefry
