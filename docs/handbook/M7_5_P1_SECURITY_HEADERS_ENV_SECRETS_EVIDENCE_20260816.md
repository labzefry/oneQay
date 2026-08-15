# M7.5 P1 Security Headers + Environment Secrets Evidence — 2026-08-16

Attribution: **Lab | zefry**

## Purpose

This additive record reconciles bounded non-Production Technical Preview evidence for the environment/secret boundary and records a source-level HTTP security-header hardening patch that still requires deployment and runtime re-verification.

This record does not contain raw screenshots, cookie values, session IDs, tokens, passwords, runtime `APP_KEY`, raw `.env`, database identities, customer data, BPJS data, personal data, or Production data.

It does not authorize M7.6, M7.7, Phase 0 Exit, Release, Production, database creation, migration, permanent schema, rollback, or restore.

## Governed baseline

Published `main` at the start of this work:

`d947bde3f0d009227453b375fec140d71e1b991d`

Published tree:

`4ec4d4f28140efd5051f5a59f4d32e19420f3aa7`

Canonical evaluator before this reconciliation:

- **18 VERIFIED**;
- **11 BLOCKED**;
- outcome: **BLOCKED**;
- lifecycle authority created: **false**.

Active non-Production Preview release observed during the runtime checks remains:

`m75-preview-0edea8cdcc0c`

The active release predates the security-header source hardening introduced by this branch.

## Environment secrets

**Status: VERIFIED for the bounded M7.5 Technical Preview environment/secret isolation control**

The live Technical Preview evidence already established that:

- the active runtime `.env` is stored in the private release area outside the public web root;
- the runtime `.env` permission is `0600`;
- direct browser access to `/.env` returns `404 Not Found` and does not disclose configuration content;
- the repository does not contain the runtime `APP_KEY` or runtime credentials.

Repository-level controls reinforce that runtime boundary:

- `apps/web/.gitignore` ignores `/.env` and `/.env.*`;
- `apps/web/environment.example` contains placeholders rather than real credentials;
- application configuration reads secret-bearing values from environment variables rather than embedding runtime credentials in source.

This evidence is sufficient for the M7.5 target-side environment/secret isolation requirement. It does **not** claim centralized Production secret management, enterprise vault integration, secret rotation automation, or Production credential governance.

Therefore this reconciliation promotes only:

`RUNTIME:ENVIRONMENT_SECRETS: PARTIAL -> VERIFIED`

## Runtime session-cookie observation

A bounded browser inspection of the current HTTPS Technical Preview showed the application session boundary with:

- `oneqay-session`: `Secure` enabled;
- `oneqay-session`: `HttpOnly` enabled;
- `oneqay-session`: `SameSite=Lax`;
- `XSRF-TOKEN`: `Secure` enabled;
- `XSRF-TOKEN`: `SameSite=Lax`.

No cookie value or session identifier is recorded by this evidence package.

The inspected application session was invalidated through the normal Technical Preview logout flow after the observation.

## Security boundary

**Status: PARTIAL — not promoted by this reconciliation**

Existing source/runtime evidence already establishes material security controls including:

- Technical Preview fail-closed outside authorized non-Production runtime classes;
- session ID regeneration on synthetic sign-in;
- logout session invalidation and CSRF-token regeneration;
- principal plus verified-context checks before protected Preview POS operations;
- safe rejection of invalid synthetic sale/context requests;
- private/public filesystem separation;
- direct non-disclosure checks for sensitive runtime/configuration paths;
- safe correlation identifiers and generic safe-error-envelope regression coverage.

However, a read-only browser response-header inspection of the active Technical Preview release found these six headers absent before this source hardening:

- `Strict-Transport-Security`;
- `Content-Security-Policy`;
- `X-Content-Type-Options`;
- `X-Frame-Options`;
- `Referrer-Policy`;
- `Permissions-Policy`.

The active release therefore does **not** provide sufficient evidence to promote `RUNTIME:SECURITY_BOUNDARY` to VERIFIED.

## Source security-header hardening

This branch introduces `SecurityHeadersMiddleware` and registers it globally in the Laravel application.

The middleware is designed to emit:

- `Content-Security-Policy` with same-origin default policy, framing/object denial, same-origin forms, bounded Cloudflare telemetry allowances, and insecure-request upgrade;
- `X-Content-Type-Options: nosniff`;
- `X-Frame-Options: DENY`;
- `Referrer-Policy: strict-origin-when-cross-origin`;
- bounded `Permissions-Policy`;
- `Strict-Transport-Security` only when the request is actually HTTPS.

The CSP deliberately allows the currently observed Cloudflare telemetry endpoints rather than using a policy that would knowingly break the existing Technical Preview delivery path.

The patch does not introduce credentials, external provider tokens, database access, persistence, migrations, or schema changes.

## Regression coverage

The application regression now verifies that:

- non-HTTPS requests do not receive HSTS;
- HTTPS responses receive bounded HSTS;
- CSP contains same-origin default policy plus frame/object denial;
- MIME sniffing is disabled;
- framing is denied;
- the referrer policy is bounded;
- the permissions policy is present and deterministic;
- the security-header path does not leak the test `APP_KEY`.

CI success is source-level verification only. It is **not** equivalent to live Technical Preview verification.

## Required runtime re-verification

Before `RUNTIME:SECURITY_BOUNDARY` can be reconsidered for VERIFIED, a governed artifact containing this patch must be deployed to the non-Production Technical Preview and the live response headers must be checked again.

At minimum, the live HTTPS response must demonstrate the intended six security headers without breaking the Technical Preview application journey.

Until that happens:

`RUNTIME:SECURITY_BOUNDARY = PARTIAL`

## Deterministic evaluator reconciliation

The additive sanitized input package is:

`docs/evidence/runtime/p1-cpanel-live-runtime-reconciled-20260816-security-env.json`

The deterministic evaluator-shaped report is:

`docs/evidence/runtime/p1-cpanel-live-runtime-reconciled-20260816-security-env.report.json`

Result proposed by this Draft PR:

- **19 VERIFIED**;
- **10 BLOCKED**;
- outcome: **BLOCKED**;
- lifecycle authority created: **false**.

The remaining blockers are:

- `ENGINE:PORTABILITY_CONTRACT:UNVERIFIED`;
- `ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED`;
- `ENGINE:TENANT_ISOLATION:PARTIAL`;
- `RUNTIME:BACKGROUND_EXECUTION:PARTIAL`;
- `RUNTIME:BACKUP_RESTORE:PARTIAL`;
- `RUNTIME:DEPLOYMENT_RECOVERY:PARTIAL`;
- `RUNTIME:OBSERVABILITY_LOGGING:PARTIAL`;
- `RUNTIME:QUEUE_EXECUTION:UNVERIFIED`;
- `RUNTIME:ROLLBACK:NOT_SUPPLIED`;
- `RUNTIME:SECURITY_BOUNDARY:PARTIAL`.

## Lifecycle boundary

- M7.5: **BLOCKED / INCOMPLETE**;
- M7.6: **BLOCKED / NOT AUTHORIZED**;
- M7.7: **BLOCKED / NOT AUTHORIZED**;
- Phase 0 Exit: **NOT APPROVED**;
- Release: **NOT AUTHORIZED**;
- Production: **NOT AUTHORIZED**;
- Production readiness: **NO-GO**.

No lifecycle authority is created by adding headers, passing CI, or reconciling the environment-secret evidence.

Attribution: **Lab | zefry**
