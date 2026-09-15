# oneQay Tasks

**Current canonical engineering checkpoint:** Sprint172 closed
**Canonical engineering commit:** `636a07130650f2d3119d450f35cfcfaf2868898e`
**Latest engineering PR:** #764 — `Sprint172: add database configuration compatibility readiness`
**Canonical status authority:** `PROJECT_MANIFEST.md`

## Completed Sprint172 state

Sprint172 materialized `INSTALLATION_DATABASE_CONFIGURATION_COMPATIBILITY_READINESS` as the next bounded installer production-readiness correctness prerequisite.

- [x] Canonical `SecureInstallationReadiness` owner reused.
- [x] Stale installer-facing `DB_*` readiness keys removed from the canonical environment contract.
- [x] Canonical `ONEQAY_DB_DRIVER`, `ONEQAY_DB_HOST`, `ONEQAY_DB_DATABASE`, and `ONEQAY_DB_USERNAME` requirements aligned with application configuration.
- [x] Deterministic observed database facts added for connection state, engine, server version, charset, timezone, schema state, and least-privilege posture.
- [x] Configured driver restricted to `mysql` for the initial contract.
- [x] Observed engine identity accepts MySQL or MariaDB.
- [x] `utf8mb4` required.
- [x] UTC / `+00:00` timezone required.
- [x] Schema state restricted to `empty` or `recognized`.
- [x] Least-privilege evidence required.
- [x] Missing/incompatible database evidence fails closed without credential or arbitrary server-detail leakage.
- [x] Existing Sprint169–Sprint171 runtime, configuration, filesystem, governed release manifest, artifact integrity, and redaction readiness preserved.
- [x] No PDO/network database connection, schema/config mutation, migration execution, credential provisioning, installer route, deployment, or updater activation.
- [x] Focused PHP regression.
- [x] Dedicated exact-envelope workflow.
- [x] Exact-head PR qualification successful.
- [x] Repository-native Product Owner merge authority verified.
- [x] PR #764 squash merged at `636a07130650f2d3119d450f35cfcfaf2868898e`.
- [x] Initial reconciliation compatibility debt surfaced by PR #765.
- [x] Workflow-only Sprint171/Sprint172 compatibility correction PR #766 squash merged at `24fe2664eb294fe0142775ff0116006e0909f999`; application source and operational state unchanged.

Engineering envelope: 3 paths; SHA-256 `c0a1726ad384717f91889af2f2ce0f433cfdae7a64b3be62dc6fadde16104d16`.

Canonical reconciliation envelope: 6 paths; SHA-256 `d43b48ac559bdee72e785c9187f4dec04bdce55be84d76baa0d76d28b5a6f304`.

## Preserved lifecycle state

Machine-readable operational state under `ops/final-shift-close/` remains authoritative and unchanged: selected target `null`; migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; real target-bound capability/dependency evidence absent; producer dispatch not performed; feature activation `INACTIVE`; deployment authority `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`.

## Next engineering position

Begin **Sprint173 bounded discovery** only from fully reconciled Sprint172. Prioritize the smallest material non-duplicative P0/P1 business-completeness or production-readiness gap. Do not preselect database execution, administrator creation, environment mutation, migration/seeder execution, installer exposure, privileged updater UI, deployment, or operational activation; prove the next canonical gap first.

Author by Lab | zefry
