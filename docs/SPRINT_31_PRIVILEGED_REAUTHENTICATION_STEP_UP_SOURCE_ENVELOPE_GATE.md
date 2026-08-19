# Sprint 31 — Privileged Reauthentication / Step-Up Session Freshness — Source Envelope Gate

> **Status:** SOURCE-ENVELOPE GATE / DOCUMENTATION-ONLY / NO SOURCE MERGE AUTHORITY
> **Canonical baseline:** `768a83237b970e96993a21b674cd98d340f8294b`
> **Canonical baseline tree:** `53492dce352ce57f06af840a7e5ec402549fafa4`
> **Entry gate publication:** PR #201
> **Repository:** `labzefry/oneQay`
> **Attribution:** Lab | zefry

## 1. Purpose

This gate freezes the exact bounded source envelope for Sprint 31 — **Privileged Reauthentication / Step-Up Session Freshness Foundation**.

It follows the published Sprint 31 entry gate and creates no source implementation or merge authority by itself. A later source PR must remain entirely inside the exact path envelope below and must receive its own exact-head Product Owner authority before merge.

## 2. Fresh minimal-delta verification

The canonical source already provides the reusable primitives required by Sprint 31:

- `VerifyFirstPartyIdentityCredential` performs bounded first-party password verification against the existing tenant-scoped credential boundary.
- `PrivilegedTotpMfaService::challenge()` verifies a confirmed privileged TOTP factor, consumes the matched time step through the existing replay-safe durable state, and returns the server verification timestamp.
- `PrivilegedTotpMfaService::requiredState()` re-evaluates whether the authenticated principal currently requires protected-control MFA.
- `FirstPartySessionController` invalidates the prior session when establishing a new full or pending session and invalidates the session on logout, so old step-up evidence cannot survive a new login or logout transition.
- `PrivilegedTotpMfaController` invalidates the pending session before establishing the verified full session, so pre-existing step-up evidence cannot survive the Sprint 30 challenge transition.
- `RequirePolicyAdministrationSessionContextMiddleware` is the existing narrow enforcement point immediately before policy-administration delivery.
- `PolicyAdministrationDeliveryService` continues to re-run the durable policy-administration authorization path for every mutation.

Therefore Sprint 31 requires no new credential store, no new TOTP store, no new cryptography, no new authentication dependency, and no schema change.

## 3. Exact source envelope

A Sprint 31 source candidate is permitted to change **exactly these 12 paths and no others**:

1. `.github/workflows/sprint31-privileged-reauthentication-step-up-regression.yml`
2. `apps/web/app/Application/Identity/PrivilegedStepUpClock.php`
3. `apps/web/app/Application/Identity/PrivilegedStepUpService.php`
4. `apps/web/app/Application/Identity/PrivilegedStepUpViolation.php`
5. `apps/web/app/Delivery/Http/Identity/FirstPartySessionKeys.php`
6. `apps/web/app/Delivery/Http/Identity/PrivilegedReauthenticationController.php`
7. `apps/web/app/Delivery/Http/Middleware/RequirePolicyAdministrationSessionContextMiddleware.php`
8. `apps/web/app/Providers/AppServiceProvider.php`
9. `apps/web/config/oneqay.php`
10. `apps/web/routes/web.php`
11. `apps/web/tests/privileged-reauthentication-step-up.php`
12. `docs/PRIVILEGED_REAUTHENTICATION_STEP_UP_FOUNDATION.md`

Sorted-path SHA-256:

`1ea89ade54bfcfed1c8a276f86b2305fbb8ed80e970e60b478db8360e1e1800c`

Any source candidate with an additional path, missing path, rename, migration, dependency mutation, temporary transport file, payload chunk, workflow helper, or generated artifact is outside this gate.

## 4. Explicitly reused unchanged source

The following existing source is required behavior but is **not authorized to change** in Sprint 31:

- `apps/web/app/Application/Identity/FirstPartyIdentityCredentialVerifier.php`
- `apps/web/app/Application/Identity/VerifyFirstPartyIdentityCredential.php`
- `apps/web/app/Infrastructure/Identity/LaravelFirstPartyIdentityCredentialVerifier.php`
- `apps/web/app/Application/Identity/PrivilegedTotpClock.php`
- `apps/web/app/Application/Identity/PrivilegedTotpEngine.php`
- `apps/web/app/Application/Identity/PrivilegedTotpMfaRepository.php`
- `apps/web/app/Application/Identity/PrivilegedTotpMfaService.php`
- `apps/web/app/Application/Identity/PrivilegedTotpMfaState.php`
- `apps/web/app/Application/Identity/PrivilegedTotpMfaViolation.php`
- `apps/web/app/Infrastructure/Identity/LaravelPrivilegedTotpMfaRepository.php`
- `apps/web/app/Infrastructure/Identity/OtphpPrivilegedTotpEngine.php`
- `apps/web/app/Delivery/Http/Identity/FirstPartySessionController.php`
- `apps/web/app/Delivery/Http/Identity/PrivilegedTotpMfaController.php`
- `apps/web/app/Application/Authorization/PolicyAdministrationDeliveryService.php`

Sprint 31 must compose these published boundaries rather than fork or replace them.

## 5. Feature arm and configuration

The only new feature arm is:

`ONEQAY_PRIVILEGED_STEP_UP_ENABLED`

The exact config surface is:

- `oneqay.privileged_step_up.enabled`
- `oneqay.privileged_step_up.freshness_seconds`

Required values:

- source default `enabled=false`;
- `freshness_seconds=300` as a source-fixed value for Sprint 31;
- runtime classes Local/Test/CI only;
- route and enforcement logic must fail closed if step-up is armed while privileged TOTP MFA is unavailable or disabled;
- false preserves the published Sprint 30 behavior.

No Preview or Production configuration activation is authorized.

## 6. Application orchestration boundary

`PrivilegedStepUpService` is a thin orchestration service only.

Its allowed dependencies are:

- `VerifyFirstPartyIdentityCredential` for fresh password verification;
- `PrivilegedTotpMfaService` for protected-control re-evaluation plus replay-safe TOTP challenge.

Required sequence:

1. derive tenant and identity from the already-authenticated server-side context;
2. verify the supplied password through `VerifyFirstPartyIdentityCredential`;
3. reject generically if password verification fails;
4. call the existing `PrivilegedTotpMfaService::challenge()` with the same tenant/identity and supplied six-digit TOTP;
5. use the timestamp returned by the successful TOTP challenge as the authoritative step-up verification time;
6. map credential/TOTP failures to one generic Sprint 31 violation surface.

The service must not implement password hashing, TOTP/HMAC/Base32 logic, TOTP secret access, provisioning URI generation, factor replacement, recovery, or durable step-up storage.

## 7. Step-up clock

`PrivilegedStepUpClock` exists only for deterministic freshness evaluation at the protected-operation middleware boundary.

`AppServiceProvider` must bind it to a server clock implementation returning Unix seconds.

The clock is not client-controlled and must never accept request timestamps.

The TOTP challenge timestamp itself continues to come from the published Sprint 30 `PrivilegedTotpClock` through `PrivilegedTotpMfaService::challenge()`.

## 8. HTTP endpoint

The only new route is:

`POST /auth/reauthenticate/privileged`

Required route semantics:

- Local/Test/CI only;
- registered only when the Sprint 31 arm and Sprint 30 privileged TOTP arm are both enabled;
- first-party Web session and CSRF model preserved;
- throttles `5/minute` and `20/hour`;
- request body after `_token` removal contains exactly `password` and `code`;
- no request field may select tenant, identity, organization, outlet, device, role, permission, or scope;
- `code` is exactly six ASCII decimal digits;
- no GET, polling, alternate-factor, recovery, reset, replacement, or token endpoint is added.

## 9. Full-session precondition and context re-verification

`PrivilegedReauthenticationController` must require the canonical five Sprint 27 full-session facts:

- `oneqay.auth.identity_id`
- `oneqay.auth.tenant_id`
- `oneqay.auth.organization_id`
- `oneqay.auth.outlet_id` when present
- `oneqay.auth.device_id` when present

It must also require valid published login-level MFA evidence:

`oneqay.auth.mfa_verified_at`

The controller must reconstruct and re-enter the server-side tenant/organizational context using the published verified identity/context boundaries before invoking step-up verification.

Pending MFA session state must not satisfy Sprint 31 reauthentication.

## 10. Step-up session evidence

`FirstPartySessionKeys` may add exactly these security-evidence constants:

- `STEP_UP_VERIFIED_AT = 'oneqay.auth.step_up_verified_at'`
- `STEP_UP_SCOPE = 'oneqay.auth.step_up_scope'`
- `STEP_UP_CONTEXT = 'oneqay.auth.step_up_context'`

`FirstPartySessionKeys::all()` must remain the canonical five Sprint 27 full-context keys only.

The step-up scope is exactly:

`policy_administration`

`STEP_UP_CONTEXT` is a server-written associative structure containing the exact current full-session context values:

- `identity_id`
- `tenant_id`
- `organization_id`
- `outlet_id` nullable
- `device_id` nullable

No client-supplied context value may participate in generating authoritative step-up evidence.

## 11. Successful transition

After password verification and replay-safe TOTP challenge both succeed:

1. re-verified full context is held server-side;
2. the current login-level `mfa_verified_at` value is preserved as separate login evidence;
3. the browser session is invalidated/rotated;
4. the CSRF token is regenerated;
5. the canonical five full-session context keys are rewritten from the re-verified server context;
6. the prior login-level `mfa_verified_at` is rewritten unchanged;
7. `STEP_UP_VERIFIED_AT` is written from the successful TOTP challenge timestamp;
8. `STEP_UP_SCOPE` is written as `policy_administration`;
9. `STEP_UP_CONTEXT` is written from the exact re-verified five-context structure.

No other session security evidence may be silently promoted or synthesized.

## 12. Policy-administration enforcement

`RequirePolicyAdministrationSessionContextMiddleware` remains the exact enforcement point.

When `oneqay.privileged_step_up.enabled=false`, the published Sprint 30 middleware behavior is preserved.

When the Sprint 31 arm is enabled, the middleware must fail closed unless all of the following are true:

1. runtime is Local/Test/CI;
2. privileged TOTP MFA is also enabled;
3. canonical five-session context is structurally valid;
4. tenant/organizational context re-entry succeeds;
5. published `mfa_verified_at` is structurally valid;
6. `PrivilegedTotpMfaService::requiredState()` proves the current principal remains protected-control and the factor remains confirmed;
7. step-up scope equals `policy_administration`;
8. step-up context exactly matches the current canonical five-session context;
9. `step_up_verified_at` is a positive integer;
10. server clock is not earlier than `step_up_verified_at`;
11. evidence age is not greater than exactly 300 seconds.

The existing policy-administration delivery path must still run durable authorization for every mutation. Step-up evidence grants no role, permission, membership, or protected-control authority.

## 13. Failure behavior

Sprint 31 failures must be generic and secret-minimal.

The reauthentication endpoint must not distinguish among:

- wrong password;
- wrong TOTP;
- absent/unconfirmed factor;
- replayed TOTP;
- authorization loss;
- malformed session;
- context mismatch;
- disabled/misconfigured feature state.

Responses, exceptions intended for HTTP delivery, logs, tracked fixtures, and workflow output must not contain passwords, TOTP codes, TOTP secrets, decrypted factor material, credential hashes, encryption keys, or provisioning URIs.

## 14. No schema change

Sprint 31 source classification is exactly:

**NO_SCHEMA_CHANGE**

The source candidate must contain no file under `apps/web/database/migrations/`.

Migration #10 is forbidden.

Migrations #1–#9 remain unchanged. Durable TOTP replay state continues to use migration #9 only. Step-up evidence remains session-local.

## 15. Dedicated regression

The new test `apps/web/tests/privileged-reauthentication-step-up.php` must prove at minimum:

- feature source default is disabled;
- route is absent outside Local/Test/CI or when required arms are not jointly enabled;
- full authenticated protected-control session is required;
- pending MFA state cannot step up;
- request cannot select or override identity/tenant/context;
- password-only and TOTP-only attempts fail;
- wrong password does not produce step-up evidence;
- wrong/replayed TOTP does not produce step-up evidence;
- same TOTP time-step has at most one winner under the existing replay-safe boundary;
- successful step-up rotates the session and regenerates CSRF state;
- login-level MFA evidence remains distinct;
- exact server-derived context binding is written;
- policy mutation is denied without fresh step-up when armed;
- exact 300-second evidence is accepted and evidence older than 300 seconds is denied;
- future-clock/malformed timestamps are denied;
- tenant/identity/organization/outlet/device/scope mismatch is denied;
- loss of current protected-control state is denied;
- ordinary Sprint 30 policy behavior is preserved when the arm is disabled;
- login, pending-MFA establishment, successful Sprint 30 challenge, and logout cannot preserve stale Sprint 31 evidence;
- no secret material is emitted.

## 16. Dedicated workflow

`.github/workflows/sprint31-privileged-reauthentication-step-up-regression.yml` must:

- enforce the exact 12-path source envelope and fingerprint;
- reject any migration path and explicitly reject migration #10;
- verify source-default-disabled step-up configuration and exact 300-second freshness;
- lint PHP source;
- run the dedicated Sprint 31 regression;
- preserve root Platform Foundation regression;
- preserve M7 tenant/identity regressions;
- preserve Sprint 21–30 identity/control regressions;
- preserve Technical Preview / Production / updater separation;
- confirm tracked source remains unchanged after tests.

Historical workflows must not be weakened or broadened merely to admit Sprint 31.

## 17. Exact preservation boundary

A later source candidate must preserve without source mutation:

- Sprint 26 credential-verification implementation;
- Sprint 27 first-party session semantics;
- Sprint 28 password enrollment;
- Sprint 29 first-control-principal bootstrap;
- Sprint 30 TOTP dependency, cryptography adapter, encrypted factor storage, migration #9, replay protection, pending/full session separation, and login-level MFA semantics;
- Technical Preview `NO_SCHEMA_CHANGE`;
- Production `NO-GO / NOT AUTHORIZED`;
- updater `DISABLED / UNWIRED`;
- `ONEQAY_PERSISTENCE_ENABLED=false` source default;
- JRN-003 unresolved status.

## 18. Explicit exclusions

This envelope authorizes no path for:

- migration #10 or schema change;
- Composer/npm dependency changes;
- password change/reset/recovery/rotation/revocation;
- MFA recovery or recovery codes;
- TOTP secret replacement/reset/deletion;
- multiple factors;
- WebAuthn/passkeys;
- federation/OIDC/SAML;
- API-token or Android authentication;
- support impersonation or break-glass flow;
- updater activation;
- Preview authentication/step-up activation;
- Production authentication/step-up activation;
- deployment or Release authority.

## 19. Publication result

If this documentation-only gate is later authorized and merged, it means only:

**The exact 12-path Sprint 31 source envelope and implementation contract are frozen for a later separately authorized source candidate.**

It does not authorize merge of that later source candidate.
