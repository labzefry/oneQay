# Changelog

## 2026-09-15 — Sprint172 closed

**Sprint172: Installation Database Configuration Compatibility Readiness**

- **Objective:** `INSTALLATION_DATABASE_CONFIGURATION_COMPATIBILITY_READINESS`.
- Reused canonical `App\Infrastructure\Installation\SecureInstallationReadiness`; no parallel installer/database-readiness owner was introduced.
- Corrected installer preflight from stale `DB_*` requirements to canonical `ONEQAY_DB_DRIVER`, `ONEQAY_DB_HOST`, `ONEQAY_DB_DATABASE`, and `ONEQAY_DB_USERNAME` keys actually consumed by the application.
- Added deterministic observed database compatibility readiness for connection state, MySQL/MariaDB engine identity, server-version form, `utf8mb4`, UTC / `+00:00`, empty/recognized schema state, and least-privilege posture.
- Missing, disconnected, incompatible, or non-least-privilege database evidence fails closed without echoing credentials or arbitrary server facts.
- Existing Sprint169–Sprint171 runtime, environment, application-key, production-debug, HTTPS, filesystem-write, governed release manifest, artifact integrity, and redaction readiness remains preserved.
- No PDO/network connection, database/schema/configuration mutation, migration execution, credential provisioning, installer route, release publication, artifact transport/extraction, deployment, updater activation, or operational activation was introduced.
- Exact engineering head `ab9716dc64c77a69da2c20fbafcc800d1114ef93` completed surfaced PR-triggered qualification successfully.
- Sprint172 regression `34979406742`, Governance `34979406633`, PHP Foundation `34979406379`, and M7.1 `34979406335` succeeded.
- Repository-native Product Owner merge authorization verified on the exact engineering head.
- Engineering envelope: 3 paths; SHA-256 `c0a1726ad384717f91889af2f2ce0f433cfdae7a64b3be62dc6fadde16104d16`.
- Engineering PR #764 squash merged at `636a07130650f2d3119d450f35cfcfaf2868898e`.
- Canonical reconciliation envelope: 6 paths; SHA-256 `d43b48ac559bdee72e785c9187f4dec04bdce55be84d76baa0d76d28b5a6f304`.
- **Operational NO-GO preserved:** migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; selected durable target `null`; feature activation `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`.
- **Next position:** Sprint173 bounded discovery from canonical post-Sprint172, prioritizing material P0/P1 production-readiness or business-completeness work with no objective preselected.

## Recent material progression

- **Sprint171:** governed release artifact installation readiness; engineering squash `cbf26a53c1a784b8e6eda65cc1d047c90ea590e8`.
- **Sprint170:** installation filesystem readiness; engineering squash `aa46ad0752f8ae2da145eb0c0f6324a3c5be6f25`.
- **Sprint169:** secure installation readiness foundation; engineering squash `2a53d9db9547340bd4d791b34fabb80ec600fc8b`.
- **Sprint168:** closed-shift historical POS performance; engineering squash `d3703a6b18f478acd812e7892c3871ce3aaf7bfa`.
- **Sprint167:** live active-shift POS performance.
- **Sprint166:** product-level sales performance.
- **Sprint165:** inventory accountability.
- **Sprint164:** positive-only inventory replenishment.
- **Sprint163:** cash-variance reconciliation.
- **Sprint162:** guarded POS operations hub.
- **Sprint156–Sprint161:** operational reporting, cashier, shift start, sale correction, immutable sale history/receipt detail, and catalog/opening-inventory setup.
- **Sprint88–Sprint155:** Final Shift Close source/readiness chain; operational execution did not occur.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

Author by Lab | zefry
