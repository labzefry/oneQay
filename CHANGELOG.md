# Changelog

## 2026-09-19 — Sprint202 closed canonically

**Sprint202: Merchant POS Authoritative Sale Receipt Continuity**

- Closed the post-checkout merchant usability gap with server-authoritative completed-sale receipt lines.
- Sale completion now projects product ID, quantity, unit price, line total, organization, outlet, and register/device context from the canonical completed receipt.
- Cashier renders a professional authoritative receipt, print action, and clean next-sale continuation without browser persistence.
- Catalog data is used only as an already-loaded display-name label; server receipt data remains authoritative for financial values.
- Failed checkout still has no automatic retry, preserving visible idempotency and duplicate-sale safety.
- Final engineering head `33c8ecd3852b5507fada858cfe6de3fb3924cd35`: 89/89 PR-triggered workflows SUCCESS.
- Engineering PR #835 squash merged at `09df0a239d894284a62dec2b5cc39754406eab5c`.
- Dedicated Sprint202 run `35444413243`, Sprint46 preservation `35444413057`, Sprint201 preservation `35444413281`, Sprint200 preservation `35444413718`, Sprint157 cashier `35444413136`, M7.5 `35444413550`, Sprint32 `35444413030`, Sprint33 `35444413212`, Sprint34 `35444413324`, M7.1 `35444413162`, Governance `35444413754`, and PHP Foundation `35444413157`: SUCCESS.
- Product Owner merge authority run `35444667944`: SUCCESS.
- Engineering path hash: `d49048acc4a472d919471c56083ca4f2ac77e988ef67f6de99fb4a601cbbd684`.
- Canonical reconciliation path hash: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.
- Operational NO-GO unchanged.
- Next position: Sprint203 business-first bounded discovery.

Author by Lab | zefry

## 2026-09-19 — Sprint201 closed canonically

**Sprint201: Merchant POS State-Aware Guided Operations**

- Upgraded the Sprint200 guided merchant POS home from route-order guidance into state-aware operational guidance.
- Reused already-authorized Catalog & Opening Stock, Shift Start, and Cashier read models; no new business-state authority was introduced.
- Recommends Catalog & Opening Stock when catalog or sellable inventory is not ready.
- Recommends Shift Start when the exact device shift or opening-cash readiness is incomplete.
- Recommends Cashier only when sellable inventory, active shift, and opening-cash readiness are verified.
- Uses Sales Summary only as a read-only fallback when no guarded mutation step is recommended.
- Withholds any recommendation when readiness evidence cannot be read safely while preserving the separately authorized workspace list.
- Updated Sprint200 preservation so the canonical state-aware successor remains compatible without weakening shared safety invariants.
- Final engineering head `d5a1fb81ed725052cd89f98a72c0eefeba93a946`: 87/87 PR-triggered workflows SUCCESS.
- Engineering PR #833 squash merged at `cec54de9ff3d056f5981165616584c343b0152c2`.
- Dedicated Sprint201 run `35442031183`, Sprint200 preservation `35442031497`, Sprint199 preservation `35442031296`, M7.5 release `35442031110`, Sprint32 `35442030463`, Sprint33 `35442030778`, Sprint34 `35442030406`, M7.1 `35442030479`, Governance `35442030465`, PHP Foundation `35442030320`, and Sprint162 `35442030524`: SUCCESS.
- Product Owner merge authority run `35442955614`: SUCCESS.
- Engineering path hash: `42c4cdd533a99adf2d0d5ba379e1e90575f0bd081ec08187107e9f7a0a03d766`.
- Canonical reconciliation path hash: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.
- Operational NO-GO unchanged.
- Next position: Sprint202 business-first bounded discovery.

Author by Lab | zefry

## 2026-09-19 — Sprint200 closed canonically

**Sprint200: Merchant POS Guided Operations Home**

- Turned the POS Operations Hub into a guided merchant operations home while preserving all existing server-side authorization boundaries.
- Added a compact command summary for delivered workspaces, business lanes, and the active outlet context.
- Moved full tenant, organization, outlet, and device identifiers behind explicit technical-context disclosure so internal IDs no longer dominate the merchant experience.
- Added a suggested starting workspace derived only from the already server-delivered destination list.
- Grouped delivered destinations into setup, shift, sell, stock, review, and control lanes for faster daily navigation.
- Preserved Sprint199 Account & Security self-service in the same enterprise workspace.
- Explicitly kept guidance advisory-only: destination authorization, prerequisites, persistence, and mutation eligibility remain enforced inside each workspace.
- Final engineering head `cf9d49063d9040b83729a48eaa298e5f67f98a45`: 86/86 PR-triggered workflows SUCCESS.
- Engineering PR #831 squash merged at `9c9c211416d216a396880d6e439e6d13c1438b73`.
- Dedicated Sprint200 run `35439890798`, M7.5 release `35439890767`, Sprint32 `35439891065`, Sprint33 `35439890470`, Sprint34 `35439890729`, M7.1 `35439890803`, Governance `35439890548`, PHP Foundation `35439890784`, Sprint162 `35439891289`, and Sprint199 preservation `35439890492`: SUCCESS.
- Product Owner merge authority run `35440079418`: SUCCESS.
- Engineering path hash: `c8c07a41fba8ae22eabde78ceaeeed8ae88d0a5906bcc1429348d384a061682f`.
- Canonical reconciliation path hash: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.
- Operational NO-GO unchanged.
- Next position: Sprint201 business-first bounded discovery.

Author by Lab | zefry

## 2026-09-19 — Sprint199 closed canonically

**Sprint199: Merchant Account Security Self-Service Workspace**

- Added merchant-facing Account & Security controls to the POS Operations Hub while reusing existing first-party authentication authority.
- Added authenticated password change with mandatory fresh sign-in after success.
- Added password recovery-code rotation and privileged authenticator recovery-code rotation using existing governed endpoints.
- Added Foundation sign-in password recovery and lost-authenticator replacement flows through existing restricted recovery sessions.
- Refreshed XSRF from the current cookie after recovery proof regenerates the session, preserving multi-step CSRF correctness.
- Kept recovery material response-only; no sensitive recovery values are persisted to localStorage or sessionStorage.
- Kept capability discovery server-derived from existing route registration; no new permission, schema, authentication engine, or authority was introduced.
- Final engineering head `b7be9bc6c268aa6a332c0e709e417c6384d08800`: 85/85 PR-triggered workflows SUCCESS.
- Engineering PR #829 squash merged at `f2692018b261a723b9b360efe650969926adb2d2`.
- Dedicated Sprint199 run `35438535934`, M7.5 release `35438535597`, Sprint32 `35438535499`, Sprint33 `35438535463`, Sprint34 `35438535446`, M7.1 `35438535440`, Governance `35438535437`, PHP Foundation `35438535925`, Sprint162 `35438535268`, and Sprint180 `35438536093`: SUCCESS.
- Product Owner merge authority run `35439039225`: SUCCESS.
- Engineering path hash: `2aba38a7f80dcc6178ce39f865789b4e20b26f2d3ebb96b1d307af0c628e77e4`.
- Canonical reconciliation path hash: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.
- Operational NO-GO unchanged.
- Next position: Sprint200 business-first bounded discovery.

Author by Lab | zefry

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
