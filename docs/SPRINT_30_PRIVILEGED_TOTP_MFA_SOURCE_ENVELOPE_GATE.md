# Sprint 30 Source-Envelope Gate — Privileged TOTP MFA Foundation

## Identity and authority

- Product: `oneQay`
- Engineering entity: **Lab | zefry**
- Repository: `labzefry/oneQay`
- Canonical branch: `main`
- Exact gate base: `fee400de29ae5875da89035e8e63406b225b3620`
- Exact gate base tree: `ada4f10f7fd00116b7d7f085e3cdee248e21d548`
- Sprint 29: **COMPLETE / IMPLEMENTED / PUBLISHED**
- Sprint 30 concern-selection PR #197: **PUBLISHED**
- Sprint 30 selected concern: **Privileged TOTP MFA Foundation**
- Technical Preview: **NO_SCHEMA_CHANGE**
- Production readiness: **NO-GO / NOT AUTHORIZED**
- Updater: **DISABLED / UNWIRED**
- Persistence default: `ONEQAY_PERSISTENCE_ENABLED=false`

GitHub remains the Single Source of Truth.

This documentation-only gate freezes the exact source, dependency, schema, authentication-state, cryptographic-provider, workflow-compatibility, and regression envelope for Sprint 30. Only after this gate is successfully qualified, exact-head Product Owner-authorized, race-checked, and squash-published does it authorize one bounded Sprint 30 source candidate using the exact source envelope below.

Independent review is not an additional mandatory gate under the current Product Owner continuation model unless the Product Owner explicitly reactivates it. Exact-head Product Owner authority, required CI, exact changed-file scope, tenant isolation, secret handling, fail-closed runtime controls, and repository protection remain mandatory.

Attribution: **Lab | zefry**

## Why this second gate exists

PR #197 selected Privileged TOTP MFA Foundation as the next governed concern but intentionally did not authorize dependency, migration, schema, route, or runtime mutation.

Sprint 30 crosses three boundaries that require a frozen source contract before implementation:

1. a third-party cryptographic protocol provider is required because custom TOTP cryptography is forbidden;
2. a durable encrypted factor record and replay boundary require an additive migration #9;
3. first-party password login must split into full-session, restricted enrollment, and pending MFA-challenge states without weakening the existing Sprint 27 session contract.

This gate resolves those implementation-shape decisions before source mutation.

## Direct dependency decision

Sprint 30 authorizes exactly one new direct Composer dependency:

`spomky-labs/otphp` at exact version `11.5.0`.

The upstream package was freshly verified on 2026-08-19 as the latest stable release, requires PHP `>=8.1`, and implements RFC 4226 / RFC 6238 OTP functionality. The oneQay application currently requires PHP `^8.2`, so the direct runtime requirement is compatible.

The source candidate must pin the direct requirement exactly rather than using a floating major/minor range:

`"spomky-labs/otphp": "11.5.0"`

Only `apps/web/composer.json` and `apps/web/composer.lock` are authorized dependency files.

Composer may resolve only the package and resolver-required transitive packages. The source candidate must not perform unrelated package upgrades, framework upgrades, Node dependency changes, or lockfile normalization outside the resolver-required delta. CI must run Composer validation, locked install, and security-advisory checks.

No QR-code package is authorized. In particular, provisioning secrets must not be sent to an external QR rendering service. A future QR presentation capability, if desired, must remain local/trusted and separately bounded.

## Fixed TOTP protocol profile

Sprint 30 uses one canonical TOTP profile:

- algorithm: `SHA-1`;
- digits: `6`;
- period: `30` seconds;
- generated secret size: `20` bytes before Base32 representation;
- acceptance window: previous, current, or next 30-second step only (`±1` step);
- issuer: `oneQay`;
- account label material: exact tenant ID plus exact identity ID, encoded through the provider provisioning-URI implementation;
- verification input: exactly six ASCII decimal digits;
- no whitespace trimming, Unicode normalization, case conversion, or alternate numeric characters.

The Application layer must not implement HMAC, dynamic truncation, Base32, provisioning-URI generation, or OTP comparison itself.

`PrivilegedTotpEngine` is the framework/vendor-independent Application port. `OtphpPrivilegedTotpEngine` is the only Sprint 30 protocol adapter and must delegate secret generation, provisioning URI creation, and code verification to OTPHP.

To make replay state deterministic, the adapter must test the allowed candidate time steps individually using the OTPHP verification primitive with no additional internal window, and return the exact matched integer time step or no match. Application or repository code must not duplicate the RFC algorithm.

## Feature arming and runtime boundary

Sprint 30 may add configuration:

`oneqay.privileged_totp_mfa.enabled`

backed by:

`ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED`

The repository/source default must remain `false`.

No `.env` or `.env.*` file change is authorized.

Sprint 30 TOTP enrollment/challenge routes and enforcement are usable only when both conditions hold:

1. runtime class is exactly `local`, `test`, or `ci`;
2. `ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED=true`.

When the feature is disabled, the currently published Sprint 27 login behavior remains preserved. Preview and Production remain denied regardless of the feature value.

## Protected-principal requirement boundary

Sprint 30 first scope is the tenant protected-control principal represented by the existing canonical pair:

- role: `authorization-policy-administrator`;
- permission: `authorization.policy.manage`.

The exact tenant and identity must always come from server-verified login/context state. Client possession of a tenant ID, identity ID, role label, pending session, factor record, or TOTP code is not authorization.

The durable MFA requirement check must revalidate that the exact identity under the exact tenant still holds the protected-control assignment and that the canonical role still carries the canonical permission.

A principal that does not meet this exact Sprint 30 protected-control requirement preserves the existing Sprint 27 password-to-session flow.

Sprint 30 does not generalize MFA enforcement to every future privileged role. Platform admin, finance privileged roles, support impersonation, release access, and secret access remain architecturally MFA-required by DEC-006 but require later bounded delivery scopes.

## Migration #9 — exact schema authority

After this gate is published, Sprint 30 source is authorized to add exactly one additive, forward-only migration:

`0000_00_00_000009_create_identity_totp_factors.php`

It creates exactly one new table:

`oneqay_identity_totp_factors`

The table contract is:

| Column | Contract |
| --- | --- |
| `tenant_id` | string length 64, non-null |
| `identity_id` | string length 96, non-null |
| `secret_ciphertext` | text, non-null |
| `created_at_unix` | unsigned big integer, non-null |
| `confirmed_at_unix` | unsigned big integer, nullable |
| `last_accepted_time_step` | unsigned big integer, nullable |

Structural constraints:

- primary key: `(tenant_id, identity_id)`;
- foreign key `(tenant_id, identity_id)` references `oneqay_identities(tenant_id, id)`;
- foreign key is restrict-on-delete and restrict-on-update;
- one Sprint 30 TOTP factor maximum per exact tenant + identity;
- no factor ID, recovery-code table, factor-history table, secondary token table, or alternate MFA table;
- `down()` remains forbidden and throws the repository-standard forward-only `LogicException`.

Migrations #1 through #8 remain immutable.

After Sprint 30 source publication, canonical source migrations become exactly **#1 through #9**. This is a source-schema capability only. Technical Preview remains **NO_SCHEMA_CHANGE** and is not authorized to execute migration #9. Production remains **NO-GO / NOT AUTHORIZED** and is not authorized to execute any Sprint 30 schema change.

## Factor-state model

Sprint 30 factor state is derived only from the exact row:

- no row: `absent`;
- row exists and `confirmed_at_unix IS NULL`: `pending`;
- row exists and `confirmed_at_unix IS NOT NULL`: `confirmed`.

No separate state column is authorized.

A pending row is an initial enrollment in progress, not an active second factor.

Once a row exists, Sprint 30 must not replace `secret_ciphertext`. A repeated enrollment-start request for the same pending factor returns the same server-decrypted secret/provisioning material under the restricted authenticated enrollment state rather than creating or replacing a factor.

A confirmed factor is immutable except for monotonic advancement of `last_accepted_time_step`.

Sprint 30 does not authorize factor deletion, factor replacement, factor reset, factor rotation, second-factor addition, recovery-code issuance, or administrator bypass.

## Secret encryption and context binding

The TOTP secret is **Restricted** data.

Plaintext TOTP secrets must never be persisted. `secret_ciphertext` must be produced through Laravel's existing authenticated `Encrypter` service using the application encryption key boundary already present in the framework.

Before encryption, the Infrastructure adapter must bind the secret to an internal versioned payload containing at minimum:

- payload version;
- exact tenant ID;
- exact identity ID;
- TOTP secret.

After decryption, all payload fields must be strictly validated and the tenant/identity values must match the row being read. A ciphertext copied to another tenant or identity must fail closed.

The encryption key, plaintext secret, decrypted payload, provisioning URI, OTP value, and ciphertext must not appear in application logs, audit details, exceptions, database diagnostics returned to clients, CI output, screenshots, or repository fixtures.

A provisioning URI and/or Base32 secret may be returned only to the exact restricted same-identity enrollment session because the user must be able to enroll an authenticator. That response is sensitive and must carry no cacheable/public delivery semantics. It must never be sent to an external QR provider.

## Replay prevention

`last_accepted_time_step` is the durable replay boundary.

For both initial confirmation and later MFA challenge:

1. the code must match one of the exact allowed `±1` steps;
2. the matched step must be strictly greater than the durable `last_accepted_time_step` when one already exists;
3. the transaction must repeat factor state, tenant/identity binding, protected-control requirement, and time-step monotonicity immediately before mutation;
4. exactly one atomic update may advance the accepted time step;
5. concurrent/replayed attempts for the same or older step must fail closed with a generic bounded MFA failure.

Initial enrollment confirmation writes both `confirmed_at_unix` and the matched `last_accepted_time_step`. Therefore the enrollment-confirmation OTP cannot be replayed immediately as the first login challenge.

Raw unique/update/database exceptions must not be exposed.

## Restricted pre-authentication session model

Sprint 30 must not place the existing full authenticated session keys into the session before privileged MFA succeeds.

The existing full-session keys remain:

- `oneqay.auth.identity_id`;
- `oneqay.auth.tenant_id`;
- `oneqay.auth.organization_id`;
- `oneqay.auth.outlet_id`;
- `oneqay.auth.device_id`.

Sprint 30 adds exactly these pending/restricted keys:

- `oneqay.auth.pending.identity_id`;
- `oneqay.auth.pending.tenant_id`;
- `oneqay.auth.pending.organization_id`;
- `oneqay.auth.pending.outlet_id`;
- `oneqay.auth.pending.device_id`;
- `oneqay.auth.pending.mfa_state`.

Allowed pending MFA state values are exactly:

- `enrollment_required`;
- `challenge_required`.

Sprint 30 also adds one full-session evidence key after successful challenge:

`oneqay.auth.mfa_verified_at`

It contains only a server-generated Unix timestamp. It is not a role, permission, token, secret, or standalone authorization source.

## Login state machine

When Sprint 30 enforcement is enabled, successful Sprint 26 password verification and organizational-context validation are followed by the protected-principal MFA requirement check.

### Non-protected principal

If the exact identity is not a Sprint 30 protected-control principal, existing Sprint 27 session establishment remains unchanged.

### Protected principal with no factor or pending factor

Password success must:

1. invalidate the prior session and regenerate the CSRF token;
2. write only the pending context keys;
3. set pending state to `enrollment_required`;
4. create no full authenticated keys;
5. return a bounded `MFA_ENROLLMENT_REQUIRED` disposition.

A pending factor remains enrollment-required until confirmation succeeds.

### Protected principal with confirmed factor

Password success must:

1. invalidate the prior session and regenerate the CSRF token;
2. write only the pending context keys;
3. set pending state to `challenge_required`;
4. create no full authenticated keys;
5. return a bounded `MFA_CHALLENGE_REQUIRED` disposition.

Password success alone therefore cannot create a policy-administration-capable session while Sprint 30 enforcement is enabled.

## Enrollment delivery

Sprint 30 may add exactly two enrollment POST routes under the existing Local/Test/CI first-party authentication boundary:

- `POST /auth/mfa/totp/enrollment/start`
- `POST /auth/mfa/totp/enrollment/confirm`

Both require the exact restricted same-identity `enrollment_required` session state and rate limiting consistent with existing authentication-sensitive routes.

Enrollment start:

- revalidates pending session values;
- revalidates protected-control requirement;
- generates a fresh provider secret only when no factor row exists;
- atomically inserts one encrypted pending factor row;
- if the same factor is already pending, safely reuses/decrypts that same secret;
- returns only bounded enrollment material to the restricted user;
- establishes no full session.

Enrollment confirmation:

- accepts exactly one six-digit `code` field and rejects unknown payload fields;
- revalidates pending session values and protected-control requirement;
- verifies against the pending encrypted factor;
- atomically transitions pending to confirmed and records the matched time step;
- invalidates the restricted session and regenerates the CSRF token;
- establishes no full session;
- requires a fresh normal password login afterward.

There is no silent enrollment-to-authenticated-session upgrade.

## MFA challenge delivery

Sprint 30 may add exactly one challenge POST route:

`POST /auth/mfa/totp/challenge`

It requires the exact restricted same-identity `challenge_required` state and authentication-sensitive rate limiting.

Challenge success must:

1. verify the exact confirmed factor;
2. atomically consume a strictly newer accepted time step;
3. revalidate exact tenant/identity/context facts;
4. invalidate the pending session and regenerate the CSRF token;
5. write the existing full authenticated context keys;
6. write `oneqay.auth.mfa_verified_at` from server time;
7. remove all pending MFA keys;
8. return the normal bounded success envelope.

Challenge failure must create no full session and must use a generic authentication/MFA failure that does not expose secret, expected time step, factor internals, or persistence details.

## Policy-administration enforcement

When Sprint 30 enforcement is enabled, `RequirePolicyAdministrationSessionContextMiddleware` must additionally require valid `oneqay.auth.mfa_verified_at` evidence in the same full session before policy-administration delivery can proceed.

The marker is necessary but never sufficient. Existing server-side tenant/identity/organization context revalidation and durable policy authorization remain mandatory.

A restricted enrollment/challenge session cannot call policy administration because it contains none of the existing full authenticated session keys.

Existing sessions created before enforcement without the MFA marker fail closed for policy administration once enforcement is enabled and must reauthenticate through the Sprint 30 flow.

## Application boundary

The Sprint 30 Application layer is framework- and vendor-independent.

Authorized new Application files are:

- `IssuedPrivilegedTotpEnrollment.php`;
- `PrivilegedTotpClock.php`;
- `PrivilegedTotpEngine.php`;
- `PrivilegedTotpMfaRepository.php`;
- `PrivilegedTotpMfaService.php`;
- `PrivilegedTotpMfaState.php`;
- `PrivilegedTotpMfaViolation.php`.

They must not import `Illuminate\`, OTPHP classes, query builders, PDO, `Schema::`, HTTP request/session classes, filesystem paths, updater internals, or Production infrastructure.

Sensitive secret/code method parameters must use `#[\SensitiveParameter]` where PHP signatures permit it.

The Application service may depend on the existing `PersistenceTransaction` boundary. The shared transaction adapter itself is not authorized for modification.

## Infrastructure boundary

Authorized new Infrastructure files are:

- `LaravelPrivilegedTotpMfaRepository.php`;
- `OtphpPrivilegedTotpEngine.php`.

`LaravelPrivilegedTotpMfaRepository` owns:

- exact tenant/identity factor-row access;
- protected-control requirement revalidation needed by the MFA state machine;
- encrypted secret persistence/decryption with context validation;
- insert-only pending-factor creation;
- pending-to-confirmed transition;
- monotonic accepted-time-step update;
- Local/Test/CI and persistence fail-closed checks.

It must not mutate identities, tenant memberships, organizations/outlets/devices, roles, permissions, role-permission relationships, protected-control assignments, password credentials, Sprint 28 enrollment rows, Sprint 23/24 journals, updater state, release state, or deployment state.

`OtphpPrivilegedTotpEngine` is the only OTPHP-aware class.

No raw SQL secret embedding, unrestricted `upsert`, credential overwrite, factor replacement, delete, truncate, or alternate bypass path is authorized.

## Exact source envelope

The Sprint 30 source candidate must differ from its exact gate publication base by **exactly 46 paths**:

1. `.github/workflows/m7-2-tenant-isolation-regression.yml`
2. `.github/workflows/m7-3-identity-org-context-regression.yml`
3. `.github/workflows/m7-4a-technical-preview-interaction-regression.yml`
4. `.github/workflows/m7-5-preview-db-qualification-regression.yml`
5. `.github/workflows/sprint21-role-permission-policy-regression.yml`
6. `.github/workflows/sprint22-policy-administration-regression.yml`
7. `.github/workflows/sprint23-initial-tenant-admin-provisioning-regression.yml`
8. `.github/workflows/sprint24-protected-control-admin-lifecycle-regression.yml`
9. `.github/workflows/sprint25-policy-administration-delivery-regression.yml`
10. `.github/workflows/sprint26-identity-credential-verification-regression.yml`
11. `.github/workflows/sprint27-first-party-session-establishment-regression.yml`
12. `.github/workflows/sprint28-initial-password-enrollment-regression.yml`
13. `.github/workflows/sprint29-first-control-principal-credential-bootstrap-regression.yml`
14. `.github/workflows/sprint30-privileged-totp-mfa-regression.yml`
15. `apps/web/app/Application/Identity/IssuedPrivilegedTotpEnrollment.php`
16. `apps/web/app/Application/Identity/PrivilegedTotpClock.php`
17. `apps/web/app/Application/Identity/PrivilegedTotpEngine.php`
18. `apps/web/app/Application/Identity/PrivilegedTotpMfaRepository.php`
19. `apps/web/app/Application/Identity/PrivilegedTotpMfaService.php`
20. `apps/web/app/Application/Identity/PrivilegedTotpMfaState.php`
21. `apps/web/app/Application/Identity/PrivilegedTotpMfaViolation.php`
22. `apps/web/app/Delivery/Http/Identity/FirstPartySessionController.php`
23. `apps/web/app/Delivery/Http/Identity/FirstPartySessionKeys.php`
24. `apps/web/app/Delivery/Http/Identity/PrivilegedTotpMfaController.php`
25. `apps/web/app/Delivery/Http/Middleware/RequirePolicyAdministrationSessionContextMiddleware.php`
26. `apps/web/app/Infrastructure/Identity/LaravelPrivilegedTotpMfaRepository.php`
27. `apps/web/app/Infrastructure/Identity/OtphpPrivilegedTotpEngine.php`
28. `apps/web/app/Providers/AppServiceProvider.php`
29. `apps/web/composer.json`
30. `apps/web/composer.lock`
31. `apps/web/config/oneqay.php`
32. `apps/web/database/migrations/0000_00_00_000009_create_identity_totp_factors.php`
33. `apps/web/routes/web.php`
34. `apps/web/tests/authorization-administration-persistence.php`
35. `apps/web/tests/authorization-persistence.php`
36. `apps/web/tests/first-control-principal-credential-bootstrap.php`
37. `apps/web/tests/first-party-session-establishment.php`
38. `apps/web/tests/identity-credential-verification.php`
39. `apps/web/tests/identity-org-context.php`
40. `apps/web/tests/initial-password-enrollment.php`
41. `apps/web/tests/initial-tenant-administrator-provisioning.php`
42. `apps/web/tests/policy-administration-delivery.php`
43. `apps/web/tests/privileged-totp-mfa.php`
44. `apps/web/tests/protected-control-administrator-lifecycle.php`
45. `apps/web/tests/tenant-isolation.php`
46. `docs/PRIVILEGED_TOTP_MFA_FOUNDATION.md`

The SHA-256 of the newline-terminated lexicographically sorted exact path list is:

`95daaf86ba93ae797fccf3825d65d27acd4f71ee58916898a16fbc83d432a5ce`

No path outside this exact list is authorized in the Sprint 30 source PR.

## Historical workflow compatibility authority

Sprint 30 schema/dependency/identity expansion necessarily crosses preservation workflows that previously encoded exact migration or successor-envelope expectations.

Exactly these 13 existing workflow YAMLs may receive bounded compatibility changes:

1. `m7-2-tenant-isolation-regression.yml`
2. `m7-3-identity-org-context-regression.yml`
3. `m7-4a-technical-preview-interaction-regression.yml`
4. `m7-5-preview-db-qualification-regression.yml`
5. `sprint21-role-permission-policy-regression.yml`
6. `sprint22-policy-administration-regression.yml`
7. `sprint23-initial-tenant-admin-provisioning-regression.yml`
8. `sprint24-protected-control-admin-lifecycle-regression.yml`
9. `sprint25-policy-administration-delivery-regression.yml`
10. `sprint26-identity-credential-verification-regression.yml`
11. `sprint27-first-party-session-establishment-regression.yml`
12. `sprint28-initial-password-enrollment-regression.yml`
13. `sprint29-first-control-principal-credential-bootstrap-regression.yml`

The compatibility changes may only:

- accept the exact Sprint 30 46-path successor fingerprint where an exact successor envelope is enforced;
- update exact canonical migration expectations from #1–#8 to #1–#9 where the workflow validates the live source schema;
- preserve old migration immutability and explicitly allow only the new additive #9 path;
- install/validate the new locked Composer dependency where identity source requires it;
- include the new Sprint 30 regression in preservation chains where appropriate;
- keep historical substantive tests running.

They must not introduce wildcard future-path acceptance, disable workflows, rename required status checks, use `continue-on-error`, suppress existing regressions, weaken tenant isolation, weaken migration immutability, weaken dependency/advisory checks, or relax Preview/Production/updater boundaries.

The new dedicated workflow is:

`.github/workflows/sprint30-privileged-totp-mfa-regression.yml`

## Dedicated Sprint 30 regression requirements

The dedicated workflow must fail closed unless all of the following hold:

1. exact 46-path file envelope and fingerprint match;
2. no `.env` / `.env.*` file changed;
3. direct Composer dependency is exactly `spomky-labs/otphp` `11.5.0`;
4. Composer metadata validates, locked install succeeds, and High/Critical advisories are rejected;
5. migrations are exactly #1–#9, with #1–#8 unchanged and #9 exactly the authorized table contract;
6. no migration #10 exists;
7. all Sprint 30 PHP source passes syntax validation;
8. Application identity files remain framework/vendor independent;
9. only `OtphpPrivilegedTotpEngine` imports OTPHP;
10. fixed TOTP profile is SHA-1 / 6 digits / 30 seconds / 20-byte generated secret / ±1 step;
11. no external QR provider/package or network delivery of provisioning secrets exists;
12. source default for `ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED` remains false;
13. routes are Local/Test/CI-only and absent from Preview/Production;
14. pending session keys are distinct from full authenticated session keys;
15. protected password success creates no full session before MFA;
16. enrollment confirmation creates no full session and forces fresh login;
17. challenge success rotates the session before full authenticated keys are established;
18. policy-administration middleware requires MFA evidence when enforcement is enabled;
19. secret ciphertext contains no plaintext secret and decrypts only under the exact bound tenant + identity;
20. copied/cross-tenant ciphertext fails closed;
21. pending-factor creation is insert-only and secret ciphertext cannot be replaced after row creation;
22. confirmation is one-way pending to confirmed;
23. accepted TOTP time steps advance monotonically;
24. same-step and older-step replay fail;
25. concurrent same-step challenge yields at most one success;
26. cross-tenant factor lookup, enrollment, and challenge fail;
27. no password credential, Sprint 28 enrollment, role, permission, organizational, updater, release, or deployment mutation occurs;
28. Sprint 21 through Sprint 29 substantive regressions remain successful;
29. M7 tenant/identity isolation remains successful;
30. Technical Preview remains NO_SCHEMA_CHANGE and does not execute or wire migration #9/TOTP auth;
31. Production remains NO-GO / NOT AUTHORIZED;
32. updater remains DISABLED / UNWIRED;
33. tracked source is clean after the test run.

All pull-request-triggered workflows on the exact source head must complete successfully before Product Owner merge authority is requested.

## Test-fixture compatibility

The existing test files listed in the exact source envelope may change only as required to:

- extend disposable schema setup from canonical migration #1–#8 assumptions to #1–#9;
- preserve prior assertions under the additive factor table;
- prepare protected-control identities for the new MFA state machine where enforcement is explicitly enabled in the test;
- assert that default-disabled Sprint 30 does not retroactively alter prior tests;
- preserve every prior tenant-isolation, authorization, credential, login, enrollment, and bootstrap invariant.

Historical tests must not be weakened or converted into no-op assertions.

## Technical Preview preservation

Technical Preview remains **NO_SCHEMA_CHANGE**.

Sprint 30 source must not:

- execute migration #9 in Technical Preview;
- wire first-party password/TOTP authentication into Technical Preview;
- expose TOTP enrollment/challenge routes in Preview runtime;
- make Preview persistence default-enabled;
- alter synthetic Preview sign-in behavior;
- alter the deterministic Preview release-artifact contract;
- infer Preview credential readiness from Local/Test/CI tests.

## Production preservation

Production remains **NO-GO / NOT AUTHORIZED**.

Sprint 30 source must not:

- register TOTP routes in Production;
- enable Production first-party authentication;
- execute migration #9 in Production;
- create Production factors;
- change Production persistence defaults;
- authorize deployment, GitHub Release, cPanel mutation, database migration execution, or Production readiness.

## Updater separation

Updater remains **DISABLED / UNWIRED**.

TOTP MFA foundation creates no updater install authority, release authority, filesystem authority, rollback authority, host authority, or platform-superadmin updater capability.

## Explicit non-authority

Sprint 30 source does not authorize or implement:

- password change;
- forgot-password / password reset;
- password recovery;
- password rotation/revocation/deletion;
- emergency protected-control recovery;
- MFA recovery codes;
- factor deletion;
- factor replacement/reset/rotation;
- multiple TOTP factors;
- administrator-set MFA secret;
- email/SMS OTP;
- WebAuthn/passkeys;
- OAuth/OIDC/SAML/federation;
- API/bearer token authentication;
- global enforcement for every privileged-role family;
- Technical Preview authentication activation;
- Production authentication activation;
- updater activation;
- deployment, Release, Phase 0 Exit, Sprint 14, or Production authority.

JRN-003 recovery semantics remain unresolved and are not silently resolved by Sprint 30.

## Source publication lifecycle after this gate

Only after this gate is squash-published may the next source lifecycle proceed:

1. fresh-read canonical `main` and record exact gate publication commit/tree/signature;
2. create one fresh bounded source branch from that exact publication;
3. mutate exactly the 46 authorized paths;
4. produce exactly one bounded Sprint 30 source candidate lineage;
5. open a non-draft source PR with exact base/head and exact 46-path fingerprint;
6. qualify every triggered workflow on the exact head;
7. record exact-head Product Owner merge authority;
8. run final race checks immediately before merge;
9. squash merge with `expected_head_sha` equal to the exact authorized source head;
10. fresh-verify main publication commit/tree/parent/signature;
11. publish a separate post-Sprint30 canonical reconciliation before selecting another governed concern.

Source implementation must not begin before this gate publication.

## Gate closure statement

If this documentation-only source-envelope gate is successfully qualified, exact-head Product Owner-authorized, race-checked, and squash-published, the repository state becomes:

**SPRINT 30 SOURCE AUTHORITY: GRANTED FOR EXACT 46-PATH PRIVILEGED TOTP MFA FOUNDATION ENVELOPE ONLY.**

Migration #9 and exact OTPHP 11.5.0 dependency authority exist only inside that bounded source envelope. Technical Preview remains NO_SCHEMA_CHANGE. Production remains NO-GO / NOT AUTHORIZED. Updater remains DISABLED / UNWIRED.

Attribution: **Lab | zefry**
