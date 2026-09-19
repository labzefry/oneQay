# Changelog

## 2026-09-19 — Sprint198 closed canonically

**Sprint198: Guarded POS Business Workspace Delivery Integration**

- Integrated the already-qualified POS business workspace providers into one guarded aggregate delivery provider.
- Registered `PosOperationsHubServiceProvider` in the application provider bootstrap exactly once.
- Preserved each child workspace's existing Local/Test/CI, persistence, session-control, feature-flag, authorization, and prerequisite gates.
- Kept close-dependent Shift History and Cash Variance Reconciliation delivery blocked while canonical Final Shift Close remains `INACTIVE`.
- Added exact boot-level delivery integration regression covering the delivered POS business workspace surface and NO-GO preservation.
- Extended historical compatibility only where exact-head evidence proved it necessary; no fake-green bypasses were introduced.\n- Reconciliation additionally corrected Sprint162 post-merge preservation so it derives provider presence from the PR base state rather than assuming all non-engineering branches predate Sprint198.
- Final engineering head `4c6b5ba627bf8bf0d28b4360d10c8f249b66b73f`: 100/100 SUCCESS.
- Engineering PR #827 squash merged at `da8b0a0579e7788b22a1ee1cd78130ff29cdd99a`.
- Dedicated Sprint198 run `35435832824`, M7.5 DB `35435832860`, M7.5 release `35435832346`, M7.4A `35435832619`, M7.3 `35435832336`, M7.2 `35435832339`, M7.1 `35435832779`, Governance `35435832362`, and PHP Foundation `35435832289`: SUCCESS.
- Engineering path hash: `4e04f75c0df2b340b0a66ad5d2fa545d364a088740e0af92861909c4848d0a45`.
- Reconciliation path hash: `c0cffa516ac414b5e780c572c74d99e6cf06dd2e8c691553e28c485116e74682`.
- Operational NO-GO unchanged.
- Next position: Sprint199 business-first bounded discovery.

Author by Lab | zefry

## 2026-09-19 — Sprint197 closed canonically

**Sprint197: Atomic Technical Preview Activation Health Rollback**

- Added guarded atomic Synthetic Technical Preview activation execution after Sprint196 target preflight.
- Activation requires the exact separately provisioned one-time approval token and immutable request/authority/readiness/preflight bindings.
- Only the exact `ONEQAY_TECHNICAL_PREVIEW_ENABLED` off-switch is changed; no migration, business persistence, Production, updater, or general deployment authority is introduced.
- Added in-process post-activation liveness, readiness, Preview-surface, runtime-policy, and session-contract health checks.
- Any failed post-activation health result restores the original runtime environment byte-for-byte and writes private rollback evidence.
- Successful execution writes a private receipt with conceptual authority/readiness/preflight consumption and non-secret digests.
- Preserved canonical repository Technical Preview `NOT_AUTHORIZED` status separately from host runtime receipt status.
- Final engineering head `027c84bb282aefd314d8da3d270c925ba5837841`: 85/85 SUCCESS.
- Engineering PR #825 squash merged at `0c74e535cfeb281edaff5a2967752baee0db5227`.
- Dedicated Sprint197 run `35428627303`, M7.5 `35428627419`, M7.1 `35428627367`, Governance `35428627552`, PHP Foundation `35428627503`, cPanel `35428627529`, and shared-runtime `35428627836`: SUCCESS.
- Engineering path hash: `08a73cc8338a51da3ed294b1a6c6a62986e0527100213414036d55b839b10a44`.
- Reconciliation path hash: `c0cffa516ac414b5e780c572c74d99e6cf06dd2e8c691553e28c485116e74682`.
- Operational NO-GO unchanged; repository merge did not activate a live host.
- Next position: Sprint198 business-first bounded discovery.

Author by Lab | zefry

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
- Reconciliation path hash: `c0cffa516ac414b5e780c572c74d99e6cf06dd2e8c691553e28c485116e74682`.
- Operational NO-GO unchanged.
- Next position: Sprint197 business-first bounded discovery.

Author by Lab | zefry
