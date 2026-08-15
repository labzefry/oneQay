# M7.5 P1 Live Runtime Evidence — 2026-08-15

Attribution: **Lab | zefry**

## Purpose

This additive record captures sanitized live-runtime evidence observed on the Product Owner-authorized Technical Preview hostname after the earlier P1 cPanel capability classification.

It does not commit raw screenshots, secrets, account identifiers, credentials, database values, private keys, or Production data. It does not authorize M7.6, M7.7, Phase 0 Exit, Release, Production, or `oneqay.com`.

## Runtime target

Technical Preview hostname:

`oneqay.n07.my.id`

Current published source identity used to build the active Preview release:

`9d3d5eb084842750de884da67fe0770b7104cd7e`

Active release ID:

`m75-preview-9d3d5eb08484`

The runtime package was built by the governed M7.5 Technical Preview Release Artifact workflow from the published `main` source and then manually staged on cPanel under the Product Owner-authorized Preview boundary.

## Deployment layout evidence

The cPanel account was observed with a private application area outside `public_html` and a dedicated public document root for `oneqay.n07.my.id`.

The active release keeps the Laravel application payload, `vendor`, `storage`, `.env`, tests, Composer/package manifests, and other private source outside the public document root.

The public document root contains only the bounded web surface plus hosting-required files/directories. The oneQay public surface is limited to the generated `build/` assets and `index.php`, while the effective `.htaccess` preserves cPanel PHP directives and adds the oneQay front-controller/security rules.

No application `.env`, `vendor`, `storage`, tests, or business source was copied into the public web root.

## PHP runtime

**Status: VERIFIED**

The Preview hostname is assigned PHP 8.3 through the cPanel `alt-php83` runtime handler.

`/health/live` successfully boots the Laravel application from the private release payload, proving the web runtime can execute the published oneQay artifact on the target.

## Web runtime

**Status: VERIFIED**

Live request evidence from:

`https://oneqay.n07.my.id/health/live`

returned a successful JSON liveness payload with:

- `status = ok`;
- `service = oneqay-web`;
- a generated safe correlation ID.

This proves target-specific Apache/PHP/Laravel request execution for the Preview hostname.

## Readiness

**Status: VERIFIED for the current synthetic Preview configuration**

Live request evidence from:

`https://oneqay.n07.my.id/health/ready`

returned:

- `status = ready`;
- `service = oneqay-web`;
- a generated safe correlation ID.

The environment uses the explicit non-Production runtime class `preview`, a non-placeholder application key, and debug disabled.

This readiness result applies only to the bounded synthetic Preview configuration. It does not qualify relational persistence, recovery, queue/scheduler, payment-provider, or Production controls.

## URL rewrite

**Status: VERIFIED**

Non-file application routes such as `/health/live`, `/health/ready`, `/technical-preview`, `/technical-preview/context`, `/technical-preview/pos`, and `/technical-preview/receipt` resolve through the Laravel front controller on the target hostname.

## Filesystem and private/public boundary

**Status: VERIFIED for the active Preview web surface**

The private release contains writable Laravel runtime directories outside the public root.

Observed permissions include:

- `bootstrap/cache` at `0755`;
- `storage/framework/cache` at `0755`;
- `storage/framework/sessions` at `0755`;
- `storage/framework/views` at `0755`;
- `storage/logs` at `0755`;
- `.env` at `0600`.

The current runtime therefore has a concrete private/public separation and bounded writable runtime layout without using world-writable `0777` permissions.

## Environment secrets

**Status: VERIFIED for public non-disclosure / PARTIAL for broader operational secret management**

The active `.env` is stored in the private release outside `public_html` with permission `0600`.

Direct browser access to:

`https://oneqay.n07.my.id/.env`

returned `404 Not Found` and did not disclose configuration content.

The repository and this evidence record do not contain the runtime `APP_KEY` or any credential.

Broader secret rotation, centralized secret management, and future Production secret controls remain outside this evidence.

## PHP configuration-file exposure

**Status: VERIFIED SAFE for tested paths**

Direct browser access to both:

- `/php.ini`;
- `/.user.ini`;

returned `404 Not Found` and did not disclose hosting configuration content.

## Source and dependency exposure

**Status: VERIFIED SAFE for tested path**

Direct browser access to:

`/vendor/autoload.php`

returned `404 Not Found`.

Combined with the observed public document-root contents, this is direct evidence that the active private `vendor` tree is not being served from the Preview web root.

## TLS / HTTPS

**Status: VERIFIED for active Preview request serving**

All observed live evidence was obtained through `https://oneqay.n07.my.id/...` and the cPanel domain is configured with certificate coverage and Force HTTPS Redirect enabled.

This proves active HTTPS serving for the Preview hostname during the evidence session. Long-term renewal/recovery behavior is not inferred from this single live session.

## Preview isolation

**Status: VERIFIED for the current hostname boundary**

The active runtime is served only from the explicit Technical Preview hostname:

`oneqay.n07.my.id`

The UI repeatedly labels itself `SYNTHETIC TECHNICAL PREVIEW` and `Not Production Ready`.

`oneqay.com` was not modified or activated by this work.

## Session persistence

**Status: VERIFIED for the active synthetic interaction journey**

The runtime session driver was configured to file-backed sessions so the synthetic identity/context journey persists across requests.

Observed flow:

1. select `Demo Alpha`;
2. POST synthetic sign-in;
3. reach `/technical-preview/context`;
4. select the server-verified `tenant-alpha / organization-alpha / outlet-alpha / device-alpha` context;
5. reach `/technical-preview/pos`.

The runtime-specific session configuration remains environment data and is not committed to the repository.

## Synthetic POS end-to-end evidence

**Status: VERIFIED for the existing M7.4/M7.4A in-memory synthetic vertical slice**

Observed live journey:

`Demo Alpha -> tenant-alpha/outlet-alpha -> catalog -> cart -> CASH -> server-authoritative sale -> receipt`

The test transaction used one unit of `Synthetic Alpha Product` at IDR 1,999.

The resulting receipt displayed:

- synthetic sale ID;
- unique operation ID;
- actor `synthetic-principal-a`;
- device `device-alpha`;
- one line item at IDR 1,999;
- total IDR 1,999;
- tender `CASH`;
- evidence mode `CASH_COUNTED`;
- change IDR 0;
- safe correlation reference.

This is direct live evidence that the published M7.4A interaction layer can execute the existing M7.4 synthetic sale path on the cPanel Preview target.

It remains explicitly non-durable synthetic/in-memory evidence and does not prove relational persistence or payment-provider verification.

## Logout and fail-closed session boundary

**Status: VERIFIED**

The receipt `Keluar` action successfully cleared the Technical Preview interaction session.

After logout, direct access to the protected POS path did not restore the previous POS state and redirected back to `/technical-preview` identity selection.

This is live evidence that the synthetic interaction boundary fails closed after logout.

## Database connectivity

**Status: NOT QUALIFIED**

No oneQay relational database was created or connected as part of this live Preview session.

The active POS evidence remains the existing synthetic/in-memory implementation.

Therefore MariaDB/MySQL/PostgreSQL application connectivity, least-privilege grants, connection limits, transaction semantics, database-backed tenant isolation, schema/migration safety, and DEC-005R portability qualification remain unresolved.

## Backup / restore

**Status: PARTIAL**

The target cPanel platform exposes backup/export and restore interfaces, as already recorded in earlier evidence.

No isolated oneQay restore rehearsal has been completed in this session.

## Rollback / recovery rehearsal

**Status: NOT SUPPLIED**

A versioned release layout and previous release are present, but no deliberate rollback/recovery rehearsal was executed.

M7.6 remains a separate blocked milestone and is not implied by the successful M7.5 web-runtime activation.

## Queue / scheduler / background execution

**Status: NOT QUALIFIED**

No oneQay scheduler execution log, queue worker, persistent background worker, or bounded queue alternative was exercised during this live evidence session.

## Observability

**Status: PARTIAL / materially improved**

Live health and transaction responses emit safe correlation references, proving application-level correlation identifiers are active on the target.

A complete log lookup/recovery/alerting exercise was not performed.

## Resource qualification

**Status: PARTIAL**

The web runtime successfully served the synthetic journey under the current account limits, but no load, concurrency, saturation, storage-threshold, database-connection, or p95 qualification run was performed.

## Current qualification conclusion

The live session materially changes the earlier P1 evidence classification: target-specific web runtime, HTTPS serving, document-root isolation, routing, readiness, selected security non-disclosure checks, session lifecycle, synthetic tenant/context journey, POS sale, receipt, and logout fail-closed behavior are now directly evidenced.

However, **M7.5 overall qualification remains BLOCKED** because mandatory relational-engine/profile and recovery/operational controls are still incomplete, including at minimum:

- actual oneQay relational database connectivity;
- least-privilege database account evidence;
- database connection-limit visibility;
- transaction semantics on the selected engine profile;
- two-tenant negative isolation on the relational profile;
- successful isolated restore evidence;
- schema/migration boundary evidence where authorized;
- DEC-005R portability-contract qualification;
- queue/scheduler/background execution qualification where required;
- rollback/recovery rehearsal;
- remaining operational/resource controls required by the fail-closed evaluator.

Current classification:

- M7.5 PREPARATION: **DONE / PUBLISHED**;
- M7.5 P1 LIVE WEB RUNTIME EVIDENCE: **VERIFIED / MATERIAL PROGRESS**;
- M7.5 RELATIONAL ENGINE PROFILE QUALIFICATION: **BLOCKED / NOT SUPPLIED**;
- M7.5 OVERALL QUALIFICATION: **BLOCKED / INCOMPLETE**;
- M7.6: **BLOCKED / NOT AUTHORIZED**;
- M7.7: **BLOCKED / NOT AUTHORIZED**;
- Production readiness: **NO-GO**.

## Security and privacy statement

No raw screenshot is committed by this record. No cPanel username, home-directory account identifier, origin IP, password, token, private key, database credential, runtime `APP_KEY`, customer data, BPJS data, personal data, or Production data is recorded.

Attribution: **Lab | zefry**
