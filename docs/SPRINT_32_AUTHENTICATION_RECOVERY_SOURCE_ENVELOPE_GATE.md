# Sprint 32 — Authentication Recovery / JRN-003 — Source Envelope Gate

> **Status:** SOURCE-ENVELOPE GATE / DOCUMENTATION-ONLY / NO SOURCE AUTHORITY
> **Canonical baseline:** `2769af3005839666e85681dfcf649ba22b0cffd4`
> **Canonical baseline tree:** `28fdc66702f26eddc6adc3e210837a2d7b929cb8`
> **Entry-gate publication:** PR #206
> **Canonical product:** oneQay
> **Repository:** `labzefry/oneQay`
> **Attribution:** Lab | zefry

## 1. Purpose

This gate freezes the exact source, schema, delivery, session, security, test, and workflow envelope for the bounded Sprint 32 **Authentication Recovery / JRN-003 Recovery Proof Foundation** selected by the published entry gate.

The selected first slice remains exactly:

**user-held single-use recovery code -> restricted recovery session**.

This gate does not authorize source implementation. It creates only a source-envelope design target for a later separately authorized source PR.

## 2. Preserved canonical boundaries

Sprint 32 must preserve all published Sprint 21 through Sprint 31 identity/control foundations unless this gate explicitly says otherwise.

The following remain binding:

- canonical migrations #1 through #9 remain immutable;
- migration #10 is not present on the canonical baseline and may exist only in a later source candidate within this exact envelope;
- `ONEQAY_PERSISTENCE_ENABLED=false` remains the source default;
- `ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED=false` remains the source default;
- `ONEQAY_PRIVILEGED_STEP_UP_ENABLED=false` remains the source default;
- Sprint 30 TOTP remains replay-safe and continues to use `spomky-labs/otphp` 11.5.0;
- Sprint 31 privileged step-up remains Local/Test/CI only, scoped to `policy_administration`, with fixed freshness of 300 seconds;
- Technical Preview remains **`NO_SCHEMA_CHANGE`**;
- Production remains **`NO-GO / NOT AUTHORIZED`**;
- updater remains **`DISABLED / UNWIRED`**;
- password reset/change/overwrite remains excluded;
- MFA/TOTP recovery, factor replacement/reset/deletion, multiple factors, protected-control recovery, support-assisted recovery, break-glass, passkeys, federation, and API-token authentication remain excluded.

## 3. Exact changed-file envelope

A later Sprint 32 source candidate is authorized for design review only if its changed-file set is exactly the following **31 paths**, sorted lexicographically:

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
15. `.github/workflows/sprint31-privileged-reauthentication-step-up-regression.yml`
16. `.github/workflows/sprint32-authentication-recovery-proof-regression.yml`
17. `apps/web/app/Application/Identity/IssuedRecoveryCodeSet.php`
18. `apps/web/app/Application/Identity/RecoveryCodeClock.php`
19. `apps/web/app/Application/Identity/RecoveryCodeRepository.php`
20. `apps/web/app/Application/Identity/RecoveryCodeService.php`
21. `apps/web/app/Application/Identity/RecoveryCodeViolation.php`
22. `apps/web/app/Application/Identity/VerifiedRecoveryProof.php`
23. `apps/web/app/Delivery/Http/Identity/FirstPartySessionKeys.php`
24. `apps/web/app/Delivery/Http/Identity/RecoveryCodeController.php`
25. `apps/web/app/Infrastructure/Identity/LaravelRecoveryCodeRepository.php`
26. `apps/web/app/Providers/AppServiceProvider.php`
27. `apps/web/config/oneqay.php`
28. `apps/web/database/migrations/0000_00_00_000010_create_identity_recovery_codes.php`
29. `apps/web/routes/web.php`
30. `apps/web/tests/authentication-recovery-proof.php`
31. `docs/AUTHENTICATION_RECOVERY_PROOF_FOUNDATION.md`

The SHA-256 of the newline-separated sorted path list, including one final newline, is:

`6238b9b30da395c7b48c81b63fcf66446720d2611b68f9e90d5223e4c0be61b9`

Any source candidate with a different path count, different path, rename, deletion, extra documentation mutation, dependency mutation, or different fingerprint is outside this gate.

## 4. Minimal-delta exclusions

The source candidate must not modify the existing password verifier, Initial Password Enrollment implementation, Sprint 30 TOTP service/engine/repository, FirstPartySessionController, policy-administration middleware, Composer/npm manifests or lockfiles, `.env.example`, Technical Preview source, updater source, or deployment source.

The published password verifier is reused without modification.

The published Initial Password Enrollment implementation is used only as precedent for secure random generation and non-reversible one-time-token digest semantics. Its table and service are not repurposed for recovery.

Sprint 30 TOTP secret material must never be read, decrypted, copied, or changed by Sprint 32.

## 5. Dedicated feature arm and runtime

The exact new source-default-disabled arm is:

`ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=false`

The exact config key is:

`oneqay.authentication_recovery.enabled`

The exact fixed restricted-session lifetime is:

`oneqay.authentication_recovery.restricted_session_ttl_seconds = 600`

The TTL is fixed in source and is not environment-configurable in Sprint 32.

Recovery delivery exists only in **Local/Test/CI**. The feature arm must never expose recovery routes in Technical Preview or Production.

A disabled feature arm must preserve Sprint 31 behavior exactly.

## 6. Recovery code set and format

Each successful issuance/rotation produces exactly **8** fresh recovery codes.

Each code has the exact versioned form:

`rq1.<selector>.<secret>`

The components are frozen as follows:

- prefix: literal `rq1`;
- selector input: exactly 16 bytes from `random_bytes(16)`;
- selector encoding: unpadded base64url, exactly 22 characters;
- secret input: exactly 32 bytes from `random_bytes(32)`;
- secret encoding: unpadded base64url, exactly 43 characters;
- exact accepted pattern: `\Arq1\.[A-Za-z0-9_-]{22}\.[A-Za-z0-9_-]{43}\z`;
- selector is a non-secret opaque lookup locator;
- the secret is Restricted authentication material;
- the complete code is Restricted authentication material.

Recovery codes have no wall-clock expiry in Sprint 32. They remain valid only until consumed or revoked by a later successful rotation. This avoids inventing an arbitrary long-lived expiry policy while retaining explicit user-controlled rotation and single-use semantics.

Plaintext recovery codes are emitted only once in the successful rotation response and cannot be retrieved later.

## 7. Digest and cryptography boundary

Durable storage contains no plaintext recovery code and no plaintext secret component.

The exact verification material is:

`hash('sha256', <43-character secret>)`

stored as lowercase 64-character hexadecimal SHA-256.

Verification must compare the stored digest and supplied digest with `hash_equals`.

This intentionally reuses the published one-time-token digest pattern used by Initial Password Enrollment. Sprint 32 must not add a cryptography package and must not implement a custom password hash, TOTP primitive, HMAC protocol, encryption protocol, KDF, or Base32 implementation.

Random identifiers use `random_bytes` and deterministic printable encoding only.

## 8. Exact migration #10

The later source candidate may introduce exactly one new migration path:

`apps/web/database/migrations/0000_00_00_000010_create_identity_recovery_codes.php`

The migration is forward-only and its `down()` must throw the same forward-only authorization failure pattern used by canonical migrations.

Migration #10 creates exactly two additive tables and does not mutate migrations #1 through #9.

### 8.1 `oneqay_identity_recovery_codes`

Required columns:

- `tenant_id` — string length 64, not null;
- `code_id` — char length 32, not null, server-generated hexadecimal identifier;
- `identity_id` — string length 96, not null;
- `code_selector` — char length 22, not null;
- `secret_digest` — char length 64, not null;
- `issued_at_unix` — unsigned big integer, not null;
- `consumed_at_unix` — unsigned big integer, nullable;
- `revoked_at_unix` — unsigned big integer, nullable.

Required keys and indexes:

- primary key: `(tenant_id, code_id)`;
- global unique index on `code_selector`;
- foreign key `(tenant_id, identity_id)` -> `oneqay_identities(tenant_id, id)` with restrictive update/delete behavior;
- index on `(tenant_id, identity_id)`;
- index on `(tenant_id, identity_id, consumed_at_unix, revoked_at_unix)`.

There is no plaintext code column, no plaintext secret column, no password column, no TOTP column, and no recovery-channel email/phone column.

### 8.2 `oneqay_identity_recovery_audit`

Required columns:

- `tenant_id` — string length 64, not null;
- `audit_id` — char length 32, not null, server-generated hexadecimal identifier;
- `identity_id` — string length 96, not null;
- `event_type` — string length 32, not null;
- `code_id` — char length 32, nullable;
- `correlation_id` — string length 128, not null;
- `occurred_at_unix` — unsigned big integer, not null.

Required keys and indexes:

- primary key: `(tenant_id, audit_id)`;
- foreign key `(tenant_id, identity_id)` -> `oneqay_identities(tenant_id, id)` with restrictive update/delete behavior;
- nullable foreign key `(tenant_id, code_id)` -> `oneqay_identity_recovery_codes(tenant_id, code_id)` with restrictive update/delete behavior;
- index on `(tenant_id, identity_id, occurred_at_unix)`.

The only Sprint 32 durable audit event types are:

- `codes_rotated`;
- `proof_succeeded`.

Failed proof attempts remain generic security-log events with correlation identifiers and must not create a durable database amplification surface.

The audit table must never store the complete recovery code, selector, secret, secret digest, password, password hash, TOTP code, TOTP secret, provisioning URI, encryption key, or session identifier.

## 9. Migration/runtime separation

Migration #10 is a source artifact for later Local/Test/CI qualification only.

The source candidate must prove:

- source migrations become exactly #1 through #10;
- migrations #1 through #9 are byte-for-byte preserved;
- migration #10 is forward-only;
- Technical Preview migration/application state remains the published **NO_SCHEMA_CHANGE** baseline and must not apply migration #10;
- Production must not apply migration #10;
- no updater, deployment, or release action is introduced to apply migration #10.

## 10. Application contracts

The exact new Application-layer types are:

- `IssuedRecoveryCodeSet`;
- `RecoveryCodeClock`;
- `RecoveryCodeRepository`;
- `RecoveryCodeService`;
- `RecoveryCodeViolation`;
- `VerifiedRecoveryProof`.

`RecoveryCodeClock` exposes only a server-time `nowUnix(): int` boundary.

`IssuedRecoveryCodeSet` represents exactly 8 valid plaintext codes for one-time delivery. It must reject malformed or non-eight-code construction.

`VerifiedRecoveryProof` contains only the verified `TenantId`, `PlatformIdentityId`, and server `proved_at_unix` value needed to establish restricted recovery session evidence.

`RecoveryCodeService` owns format validation, password re-verification orchestration for rotation, server time validation, transaction entry, generic recovery failures, and conversion of repository results into the two typed value objects.

`RecoveryCodeRepository` owns durable eligibility re-checking, recovery-code generation/storage, rotation revocation, selector lookup, digest comparison, atomic single-use consumption, and transactional audit writes.

All durable rotation and proof-success mutations occur through the published `PersistenceTransaction` boundary.

## 11. Eligibility and privileged fail-closed rule

Sprint 32 must use a dedicated recovery eligibility check independent of the Sprint 30 feature arm.

An identity is eligible only when all of the following are true at the durable re-check immediately before rotation or proof consumption:

1. the tenant-scoped identity exists;
2. the identity has an existing first-party password credential;
3. the identity has no assignment to the canonical protected-control role `authorization-policy-administrator`;
4. the identity has no TOTP factor row with non-null `confirmed_at_unix`.

The recovery implementation may query the canonical protected-role assignment and TOTP-factor state needed for this eligibility test, but it must not decrypt or return any TOTP secret.

It must not call the Sprint 30 repository merely to discover eligibility because that repository correctly requires its own feature arm to be active. Sprint 32 therefore performs the narrow durable state query within `LaravelRecoveryCodeRepository` without modifying Sprint 30 source.

Any missing/malformed required durable state, protected-control assignment, or confirmed TOTP factor yields the same generic recovery failure.

A pending/unconfirmed TOTP row is not treated as a confirmed MFA recovery factor by this first-slice eligibility rule; protected-control status remains independently fail-closed.

## 12. Rotation route

The exact issuance/rotation route is:

`POST /auth/recovery/codes/rotate`

Exact route name:

`auth.recovery.codes.rotate`

Controls:

- Local/Test/CI only;
- `ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=true` required;
- full first-party authenticated session required;
- no pending MFA or restricted-recovery session state may coexist;
- identity, tenant, organization, outlet, and device are derived from the existing full session and durably re-entered using the existing organizational-context boundary;
- request payload after framework CSRF removal contains exactly one field: `password`;
- the password is re-verified with the existing `VerifyFirstPartyIdentityCredential` service;
- throttling is exactly `5/minute` and `20/hour`;
- durable eligibility is re-checked inside the same persistence transaction that performs rotation;
- the identity row is locked before rotation state changes to serialize concurrent rotations;
- all prior unconsumed and unrevoked codes for the identity are marked with the same server `revoked_at_unix` timestamp;
- exactly 8 new codes are inserted in the same transaction;
- exactly one `codes_rotated` audit event is inserted in that transaction;
- plaintext codes exist only in the returned `IssuedRecoveryCodeSet` and one successful response;
- successful response uses `Cache-Control: no-store, private`;
- failure is generic and returns no eligibility/password/MFA distinction.

Rotation does not invalidate or upgrade the existing full session and grants no new authorization.

## 13. Recovery proof route

The exact recovery proof route is:

`POST /auth/recovery/proof`

Exact route name:

`auth.recovery.proof`

Controls:

- Local/Test/CI only;
- `ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=true` required;
- throttling is exactly `5/minute` and `20/hour`;
- request payload after framework CSRF removal contains exactly one field: `recovery_code`;
- the submitted code must match the exact `rq1` pattern before storage access;
- proof is accepted only from a clean anonymous browser session with none of the canonical full-session, pending-MFA, step-up, or restricted-recovery evidence present;
- the selector is used only to locate the candidate durable row;
- the row is locked for update;
- the secret digest is verified with `hash_equals`;
- the durable eligibility rule is re-checked after the row is locked and before consumption;
- only a row with both `consumed_at_unix` and `revoked_at_unix` null is eligible;
- successful consumption updates exactly one row with server `consumed_at_unix`;
- concurrent same-code attempts have at most one winner;
- exactly one `proof_succeeded` durable audit event is inserted in the same transaction;
- generic failure must not disclose whether selector, identity, tenant, password credential, protected-control status, or TOTP state exists.

## 14. Restricted recovery session contract

Successful proof does not create a full login.

Before restricted recovery evidence is written, the current browser session is invalidated/rotated and the CSRF token is regenerated.

The exact recovery session keys are:

- `oneqay.auth.recovery.tenant_id`;
- `oneqay.auth.recovery.identity_id`;
- `oneqay.auth.recovery.state`;
- `oneqay.auth.recovery.proved_at`;
- `oneqay.auth.recovery.expires_at`.

The exact state value is:

`password_reset_required`

`expires_at` must equal `proved_at + 600` using server-written timestamps.

`FirstPartySessionKeys::all()` remains exactly the five Sprint 27 full-context keys. A new `recovery()` helper may enumerate only the five recovery keys above.

The restricted recovery session contains no organization, outlet, or device context and does not write:

- any of the five canonical full-session keys;
- pending MFA keys;
- `oneqay.auth.mfa_verified_at`;
- `oneqay.auth.step_up_verified_at`;
- `oneqay.auth.step_up_scope`;
- `oneqay.auth.step_up_context`.

No Sprint 32 route consumes `password_reset_required` to change a credential. The restricted session is intentionally an inert proof foundation until a separately governed password-reset execution gate exists.

## 15. Controller and response boundary

`RecoveryCodeController` contains exactly two delivery actions: rotation and proof.

Both actions use closed payload validation, generic safe error envelopes, correlation IDs, and `Cache-Control: no-store, private` on security-sensitive success/failure responses.

Rotation may return only:

- status;
- the eight one-time plaintext recovery codes;
- correlation ID.

Proof success may return only:

- status;
- state `password_reset_required`;
- correlation ID.

Proof success must not return identity, tenant, role, organization, outlet, device, TOTP state, code identifier, selector, digest, or session identifier.

## 16. Infrastructure repository atomicity

`LaravelRecoveryCodeRepository` must use the canonical database connection and the existing source-default persistence guard.

It must fail closed unless durable persistence is enabled and runtime class is Local/Test/CI.

Rotation transaction requirements:

1. lock the target identity row;
2. re-check identity/password-credential/protected-control/confirmed-TOTP eligibility;
3. revoke prior unused codes;
4. generate and insert exactly 8 unique code rows;
5. insert one `codes_rotated` audit record;
6. commit atomically.

Proof transaction requirements:

1. locate by exact selector and lock the recovery-code row;
2. validate record state and digest;
3. lock/re-check the target identity eligibility state;
4. atomically consume exactly one code;
5. insert one `proof_succeeded` audit record referencing its internal code ID;
6. commit atomically.

Any exception before commit must leave no partial rotation, consumption, or audit state.

## 17. Provider bindings

`AppServiceProvider.php` may add only the bindings needed for:

- `RecoveryCodeRepository` -> `LaravelRecoveryCodeRepository`;
- `RecoveryCodeClock` -> a server `time()` implementation.

The repository binding receives:

- database connection;
- persistence-enabled flag;
- runtime class;
- authentication-recovery feature flag.

No new provider, package, queue, mail, SMS, notification, cache, or external identity dependency is authorized.

## 18. Dedicated regression

The exact new application regression script is:

`apps/web/tests/authentication-recovery-proof.php`

It must prove at minimum:

- feature disabled preserves Sprint 31 behavior;
- migration #10 exact schema and forward-only classification;
- source migrations are exactly #1 through #10;
- eight-code rotation only;
- exact `rq1` code format;
- plaintext codes absent from durable storage;
- SHA-256 secret digest semantics and constant-time comparison boundary;
- selectors unique and opaque;
- old unused codes revoked on rotation;
- same-code replay rejected;
- concurrent same-code proof has at most one winner;
- wrong secret rejected generically;
- protected-control identity rejected;
- confirmed-TOTP identity rejected without decrypting TOTP secret;
- cross-tenant or selector redirection rejected;
- rotation requires current full session and fresh password;
- rotation does not permit client-selected identity/tenant/context;
- proof does not establish full-session keys;
- restricted session contains exactly the five recovery keys and exact `password_reset_required` state;
- restricted session expiry equals proved-at plus 600 seconds;
- restricted session cannot satisfy policy-administration middleware;
- no password credential is changed by Sprint 32;
- no TOTP factor is changed by Sprint 32;
- no recovery secret appears in logs or tracked fixtures;
- Technical Preview does not expose recovery routes and does not apply migration #10;
- Production does not expose recovery routes and does not apply migration #10;
- updater remains disabled/unwired.

## 19. Dedicated workflow and preservation

The exact new workflow is:

`.github/workflows/sprint32-authentication-recovery-proof-regression.yml`

It must validate the exact 31-path envelope and canonical fingerprint before executing Sprint 32-specific checks.

The 15 historical workflow files in this envelope may be modified only to add exact Sprint 32 successor recognition.

Required successor rules:

- all existing Sprint 31 and earlier exact-successor behavior remains unchanged;
- Sprint 32 recognition requires both exact path count **31** and exact sorted-path SHA-256 `6238b9b30da395c7b48c81b63fcf66446720d2611b68f9e90d5223e4c0be61b9`;
- no branch-name, PR-number, wildcard, file-count-only, substring, or broad bypass recognition;
- historical executable regression steps remain active rather than being skipped;
- historical migration guards may accept source migration #10 only inside the exact Sprint 32 successor envelope;
- Technical Preview assertions must continue to prove migration #10 is not applied to the Preview schema/runtime;
- Sprint 31 exact 26-path successor behavior must remain unchanged.

The dedicated Sprint 32 workflow must preserve Governance Required Checks, PHP Foundation Regression, M7.1 Application Regression, and the executable Sprint 21 through Sprint 31 regression chain.

## 20. Foundation document

`docs/AUTHENTICATION_RECOVERY_PROOF_FOUNDATION.md` is the only source-candidate foundation document authorized by this envelope.

It must describe the implemented bounded source behavior only and must not claim password reset, privileged recovery, MFA recovery, Preview/Production activation, deployment, release, or JRN-003 completion.

## 21. Explicitly forbidden source expansion

The later source candidate must not add or change:

- password reset/change/overwrite implementation;
- password credential mutation from restricted recovery state;
- automatic login after proof;
- full-session creation after proof;
- TOTP/MFA recovery or factor lifecycle;
- protected-control recovery;
- support-assisted recovery or break-glass;
- verified email/phone attributes;
- email/SMS provider or notification delivery;
- password/TOTP cryptography;
- Composer/npm dependencies;
- passkeys/WebAuthn;
- federation/OIDC/SAML;
- Android/API token authentication;
- support impersonation;
- Technical Preview recovery activation;
- Production recovery activation;
- updater activation;
- deployment or Release behavior;
- any migration other than the exact migration #10 path in this envelope.

## 22. Source PR qualification requirement

A later source PR must remain Draft until all applicable exact-head workflows succeed.

Before it can be marked Ready, it must prove:

1. base and merge-base are the exact then-current Sprint 32 source-envelope publication;
2. ahead-by exactly 1 and behind-by exactly 0 after final lineage cleanup;
3. one clean source commit directly above the published gate;
4. exactly 31 changed paths;
5. exact sorted-path SHA-256 fingerprint `6238b9b30da395c7b48c81b63fcf66446720d2611b68f9e90d5223e4c0be61b9`;
6. migrations #1 through #9 preserved and exact migration #10 only;
7. dedicated Sprint 32 recovery proof regression SUCCESS;
8. historical preservation workflows SUCCESS;
9. Governance, PHP Foundation, and M7.1 SUCCESS;
10. Preview/Production/updater exclusions remain executable and successful.

Any correction that requires a path outside this envelope requires a new correction gate rather than silently expanding the source PR.

## 23. Authority boundary

Publication of this source-envelope gate, if separately authorized, would mean only:

**the exact Sprint 32 recovery-proof source target, migration #10 schema, session contract, route contract, security invariants, and 31-path source envelope are frozen for later implementation.**

It would not authorize source mutation under the same merge authority.

The later source PR must receive its own exact-head Product Owner merge authorization after qualification.
