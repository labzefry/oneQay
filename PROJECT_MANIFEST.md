# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-15

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint172
**Objective:** `INSTALLATION_DATABASE_CONFIGURATION_COMPATIBILITY_READINESS`
**Canonical engineering commit:** `636a07130650f2d3119d450f35cfcfaf2868898e`
**Engineering PR:** #764 — `Sprint172: add database configuration compatibility readiness`
**Final engineering head:** `ab9716dc64c77a69da2c20fbafcc800d1114ef93`
**Sprint172 regression:** `34979406742` — successful
**Governance Required Checks:** `34979406633` — successful
**PHP Foundation Regression:** `34979406379` — successful
**M7.1 Application Regression:** `34979406335` — successful
**Engineering envelope:** 3 paths — `c0a1726ad384717f91889af2f2ce0f433cfdae7a64b3be62dc6fadde16104d16`
**Workflow compatibility correction:** PR #766 — squash `24fe2664eb294fe0142775ff0116006e0909f999`
**Canonical reconciliation envelope:** 6 paths — `d43b48ac559bdee72e785c9187f4dec04bdce55be84d76baa0d76d28b5a6f304`
**Next position:** Sprint173 bounded discovery from the fully reconciled Sprint172 checkpoint; no objective preselected.

> `636a07130650f2d3119d450f35cfcfaf2868898e` is the canonical Sprint172 engineering evidence. Neither the workflow-compatibility correction nor the reconciliation squash may replace it as the canonical engineering commit.

## 1. Purpose / Why

Sprint172 closes a material installer correctness gap discovered after Sprint171. The canonical Laravel database configuration reads `ONEQAY_DB_*`, while the preflight readiness contract still required stale `DB_*` keys. That mismatch could allow installation readiness to appear green for database configuration the application would not actually consume. `INSTALLER.md` also requires database connection/compatibility, charset, timezone, schema-state, and least-privilege checks before installation proceeds.

## 2. What changed

- Reused canonical `App\Infrastructure\Installation\SecureInstallationReadiness`; no parallel installer or database-readiness owner was introduced.
- Replaced stale installer-facing `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, and `DB_USERNAME` requirements with canonical `ONEQAY_DB_DRIVER`, `ONEQAY_DB_HOST`, `ONEQAY_DB_DATABASE`, and `ONEQAY_DB_USERNAME` configuration keys.
- Added deterministic observed database compatibility facts for connection state, engine, server-version shape, charset, timezone, schema state, and least-privilege posture.
- Initial supported configured driver remains `mysql`; observed engine identity may be MySQL or MariaDB.
- Requires `utf8mb4`, UTC / `+00:00`, recognized or empty schema state, and least-privilege evidence.
- Invalid, incomplete, disconnected, unsupported-engine, incompatible-charset/timezone/schema, or non-least-privilege evidence fails closed without echoing database credentials or arbitrary server facts.
- Existing Sprint169–Sprint171 runtime, environment, application-key, production-debug, HTTPS, filesystem-write, governed release manifest, artifact integrity, and redaction readiness remains preserved.
- No PDO/network connection, database/schema/configuration mutation, migration execution, credential provisioning, installer route, release publication, artifact transport/extraction, updater activation, deployment, or production activation was introduced.

## 3. Evidence / Qualification

- Parent canonical post-Sprint171 checkpoint: `920f31126c9560e6b94b132dfeeace0d0773f1ab`.
- Exact engineering head: `ab9716dc64c77a69da2c20fbafcc800d1114ef93`.
- Dedicated Sprint172 run `34979406742`: successful.
- Governance `34979406633`, PHP Foundation `34979406379`, and M7.1 `34979406335`: successful.
- Surfaced POS successor and Final Shift Close historical controls completed successfully on the exact engineering head.
- Repository-native Product Owner merge authority verified on the exact engineering head.
- Engineering PR #764 squash merged at `636a07130650f2d3119d450f35cfcfaf2868898e`.
- Engineering envelope: exactly 3 paths; SHA-256 `c0a1726ad384717f91889af2f2ce0f433cfdae7a64b3be62dc6fadde16104d16`.
- Initial reconciliation PR #765 surfaced stale Sprint171 successor-document evidence assumptions and was closed unmerged.
- Workflow-only compatibility correction PR #766 squash merged at `24fe2664eb294fe0142775ff0116006e0909f999`; application source, Sprint172 engineering evidence, and operational state were unchanged.
- Canonical reconciliation envelope: exactly 6 paths; SHA-256 `d43b48ac559bdee72e785c9187f4dec04bdce55be84d76baa0d76d28b5a6f304`.

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

Database connection execution, database mutation, migration execution, credential provisioning, installer exposure, release publication, artifact transport/extraction, deployment, and runtime activation remain separately gated. Sprint172 grants none of those authorities.

## 5. Next position

Begin Sprint173 bounded discovery only after Sprint172 canonical reconciliation closes. Prioritize the smallest material non-duplicative P0/P1 business-completeness or production-readiness gap. Continue installer progression only where live repository evidence proves the next missing prerequisite; do not preselect database execution, administrator creation, environment mutation, migration/seeder execution, installer exposure, updater activation, deployment, or operational activation.

## Documentation responsibility

`PROJECT_MANIFEST.md` is the canonical human-readable state. `README.md`, `CHANGELOG.md`, `TASKS.md`, and `ROADMAP.md` are reconciled summaries. Git history, merged PRs, workflows, tests, and machine-readable contracts preserve detailed provenance.

Author by Lab | zefry
