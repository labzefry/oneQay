# Changelog

## 2026-09-19 — Sprint196 closed canonically

**Sprint196: Technical Preview Target Environment Preflight**

- Corrected the installer-prepared deployed Preview session envelope while Technical Preview remains disabled.
- Added single-instance and synthetic-only runtime posture plus Production-data prohibition.
- Added private persistent shared file sessions with 60-minute lifetime, encryption, Secure cookie, and dedicated `oneqay-preview-session`.
- Added guarded read-only target-environment preflight after Sprint195 activation execution-readiness.
- Preflight validates HTTPS, exact host binding, single-instance posture, private session storage outside public root, exact runtime envelope, Preview off-switch, governed release metadata, config-cache cleanliness, health/recovery contracts, and Production-data prohibition.
- Added private 0600 non-secret preflight evidence with exact replay idempotency and fail-closed tamper handling.
- Preflight state is `TECHNICAL_PREVIEW_TARGET_ENVIRONMENT_PREFLIGHT_PASSED_NOT_ACTIVATED`.
- Installer exposes `RUN_TECHNICAL_PREVIEW_TARGET_PREFLIGHT` and reports `PREFLIGHT PASSED / NOT ACTIVATED`.
- Final engineering head `afc443420ddef9283c7f575ce311b97fc026e658`: 84/84 SUCCESS.
- Engineering PR #823 squash merged at `9948aeadc562b6188453872f09bd3afb754dd0c0`.
- Dedicated Sprint196 run `35426685626`: SUCCESS.
- M7.5 `35426685578`, M7.1 `35426686129`, Governance `35426686140`, PHP Foundation `35426685380`, cPanel `35426685589`, and shared-runtime `35426685604`: SUCCESS.
- Engineering path hash: `bb875f82e7cfc7a8347bf6d92f916f6124eb5b095e27f9582189ebec5c74e904`.
- Reconciliation path hash: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.
- Operational NO-GO unchanged.
- Next position: Sprint197 business-first bounded discovery.

Author by Lab | zefry
