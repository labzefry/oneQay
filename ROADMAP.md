# oneQay Roadmap

**Roadmap checkpoint:** Sprint172 closed canonically
**Canonical engineering baseline:** `636a07130650f2d3119d450f35cfcfaf2868898e`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint172 horizon

Sprint172 closed `INSTALLATION_DATABASE_CONFIGURATION_COMPATIBILITY_READINESS`, correcting installer preflight to consume the same canonical `ONEQAY_DB_*` configuration names as the application and adding a deterministic, fail-closed observed database compatibility boundary before any future database execution or migration work.

The capability requires configured `mysql`, observed MySQL or MariaDB engine identity, valid server-version form, `utf8mb4`, UTC / `+00:00`, `empty` or `recognized` schema state, and least-privilege evidence. Missing, disconnected, incompatible, or non-least-privilege evidence fails closed without exposing credentials or arbitrary server facts.

Engineering PR #764 squash merged at `636a07130650f2d3119d450f35cfcfaf2868898e`. Engineering envelope: 3 paths, SHA-256 `c0a1726ad384717f91889af2f2ce0f433cfdae7a64b3be62dc6fadde16104d16`.

Initial reconciliation PR #765 exposed stale Sprint171 successor-document evidence assumptions. Workflow-only compatibility correction PR #766 squash merged at `24fe2664eb294fe0142775ff0116006e0909f999` without changing application source, canonical Sprint172 engineering evidence, or operational authority.

Canonical reconciliation envelope: 6 paths, SHA-256 `d43b48ac559bdee72e785c9187f4dec04bdce55be84d76baa0d76d28b5a6f304`.

## Product progression through Sprint172

The product now combines the existing tenant/security/API/POS operational foundations with a canonical installation-readiness owner that qualifies runtime/configuration/security posture, required filesystem write surfaces, governed release artifact identity/integrity, and deterministic database compatibility prerequisites. It remains intentionally separate from real database connectivity execution, schema mutation, migrations, credential provisioning, administrator bootstrap, environment mutation, artifact transport/extraction, staging, activation, privileged updater mutation, and deployment.

## Operational boundary

Machine-readable operational state under `ops/final-shift-close/` remains authoritative. Selected durable target remains `null`; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; producer dispatch remains not performed; runtime allowlist remains Local/Test/CI; Final Shift Close remains inactive; deployment/Technical Preview/Production remain unauthorized; updater remains inactive.

Database execution, release publication, artifact download/extraction, external signature/provenance verification, migration execution, installer exposure, and runtime activation remain separately gated.

## Sprint173 selection rule

Begin Sprint173 bounded discovery from fully reconciled Sprint172. Prioritize the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap rather than mechanically extending the installer or returning to dashboard expansion.

No database connectivity execution, administrator creation, environment mutation, migration/seeder execution, artifact transport, extraction, updater control-plane mutation, privileged updater UI, deployment, or operational activation objective is preselected. Live repository evidence must prove the next bounded gap first.

Author by Lab | zefry
