# Sprint 33 — Recovery-Bound Password Reset Completion Foundation — Source-Envelope Gate

Attribution: **Lab | zefry**

## 1. Gate identity and authority boundary

This documentation-only source-envelope gate is prepared from the published Sprint 33 entry-gate canonical base:

- canonical `main`: `42d6105749620edb307fd12e7d116798f71cdd9e`;
- canonical tree: `fa7de4aa0ef59498f353e8046e67581dedb25d04`;
- published entry gate: PR #211;
- concern: **Recovery-Bound Password Reset Completion Foundation**;
- source-envelope gate preparation: authorized by Product Owner continuation to the next governed stage;
- source implementation: **NOT AUTHORIZED** by this gate preparation;
- migration #11: **NOT AUTHORIZED**;
- Technical Preview: **`NO_SCHEMA_CHANGE`**;
- Production: **`NO-GO / NOT AUTHORIZED`**;
- updater: **`DISABLED / UNWIRED`**.

This PR itself may change only this documentation file. It grants no source, workflow, dependency, migration, schema, route, runtime, Ready, merge, Preview, Production, updater, deployment, or release mutation authority.

## 2. Frozen no-schema architecture decision

The source-envelope design freezes a no-schema credential-revocation mechanism using the already-published `oneqay_identity_recovery_audit` table.

The credential epoch for an exact `(tenant_id, identity_id)` is:

**the integer count of durable `password_reset_completed` recovery-audit rows for that exact tenant + identity.**

This count is selected instead of a timestamp because it changes on every successful reset even when two legitimate resets happen inside the same wall-clock second.

Rules:

1. initial epoch is `0` when no `password_reset_completed` event exists;
2. successful normal first-party login captures the current durable epoch and stores it as session security evidence;
3. the session key is exactly `oneqay.auth.credential_epoch`;
4. this key remains separate from the five canonical Sprint 27 full-context keys and must not be added to `FirstPartySessionKeys::all()`;
5. a missing legacy epoch is interpreted as `0` only when the durable epoch is also exactly `0`;
6. after the first successful reset, any missing or stale epoch fails closed because durable epoch is greater than the session epoch;
7. the existing full session may not rewrite or advance its own epoch; only a fresh normal password login may capture the current epoch;
8. credential-epoch lookup remains independent of the recovery feature arm so disabling recovery after a reset cannot make stale sessions authoritative again;
9. credential-epoch persistence remains Local/Test/CI-only through the existing durable-persistence/runtime boundary;
10. no new table, column, migration, cache authority, or cryptographic protocol is introduced.

For the current repository, the ordinary non-privileged full-session action that must compose this check is recovery-code rotation. Protected-control identities are already ineligible for the Sprint 32/33 recovery path, so Sprint 33 does not widen protected-control or MFA semantics.

## 3. Frozen restricted recovery evidence extension

Sprint 32 already places server-owned recovery tenant, identity, state, proof time, and expiry into the restricted session. Sprint 33 freezes one additional restricted-only key:

`oneqay.auth.recovery.code_id`

Requirements:

- exact value is the non-secret 32-character lowercase-hex `code_id` of the recovery code already consumed by Sprint 32 proof;
- `RecoveryCodeRepository::consume()` returns this `code_id` together with tenant, identity, and proof time;
- `RecoveryCodeService` carries it into `VerifiedRecoveryProof`;
- `RecoveryCodeController` writes it only into restricted recovery session state;
- the caller cannot submit, select, override, or observe this identifier;
- it must be included in `FirstPartySessionKeys::recovery()` and rejected wherever a clean anonymous/full-session separation is required;
- it is never a normal/full-session authentication key.

The five canonical Sprint 27 context keys remain unchanged.

## 4. Frozen password-reset delivery contract

The later source candidate may add exactly one route:

`POST /auth/recovery/password-reset`

Exact route behavior:

- normal Laravel web/CSRF middleware remains mandatory;
- route exists only in Local/Test/CI;
- route exists only while existing `ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=true` and the published restricted-session TTL remains exactly 600 seconds;
- no new Sprint 33 feature flag is introduced;
- throttle chain is exactly `throttle:5,1` and `throttle:20,60`;
- accepted business payload after `_token` removal is exactly one string field named `password`;
- no tenant, identity, organization, outlet, device, role, permission, code, code identifier, proof timestamp, expiry timestamp, epoch, or factor selector is accepted from the request;
- response success is secret-free and `Cache-Control: no-store, private`;
- invalid, expired, replayed, ineligible, malformed, cross-context, or storage-failure conditions collapse to the generic authentication-recovery failure family;
- successful reset never establishes a normal/full session.

Technical Preview and Production must expose no Sprint 33 recovery-reset route even if the feature environment variable is externally set true.

## 5. Frozen password policy and credential mutation

The source candidate must preserve the published opaque-password policy:

- minimum 12 bytes;
- maximum 4096 bytes;
- no trim;
- no normalization;
- sensitive parameter handling at Application/Delivery boundaries where supported;
- no plaintext/reversible password storage, logging, response, session, audit, exception, or telemetry;
- hash with `password_hash($password, PASSWORD_DEFAULT)`.

Credential mutation is update-only on the existing exact row in `oneqay_identity_password_credentials` identified by `(tenant_id, identity_id)`.

Forbidden credential operations include insert, insert-or-update, update-or-insert, upsert, delete, truncate, row recreation, administrative set, fallback enrollment, or bootstrap behavior.

Exactly one existing password hash must be replaced on success. Missing credential state fails closed.

## 6. Frozen atomic completion transaction

`RecoveryPasswordResetService` must run completion through the existing `PersistenceTransaction` and current server clock boundary. The Infrastructure repository must perform the following inside one transaction:

1. lock the exact consumed recovery-code row by server-owned `tenant_id + code_id`;
2. revalidate exact tenant + recovered identity ownership;
3. require that the code is consumed and not revoked;
4. require matching durable `proof_succeeded` audit evidence for the same tenant + identity + code;
5. reject an existing `password_reset_completed` event for that same code;
6. lock/revalidate the identity and existing credential row;
7. revalidate that the identity is not assigned the protected-control role;
8. revalidate that no confirmed TOTP factor exists;
9. validate and hash the new password;
10. update exactly one existing credential row;
11. revoke every other unconsumed/unrevoked recovery code for that tenant + identity at the same server event time;
12. append exactly one secret-free `password_reset_completed` audit event bound to the consumed code and canonical correlation ID.

The code-row lock plus prior-completion check must make replay/concurrency fail closed with **at most one winner**.

The completion event is the only durable evidence used to advance the credential epoch. No session table or credential-version column is introduced.

## 7. Frozen restricted-session expiry and terminal behavior

The reset service/controller must verify all of the following before durable mutation:

- recovery state equals exactly `password_reset_required`;
- tenant and identity are valid server-owned values;
- recovery `code_id` matches exact 32-character lowercase hex;
- `proved_at` is a positive integer;
- `expires_at` is a positive integer;
- `expires_at === proved_at + 600`;
- server time is not before `proved_at`;
- server time is not after `expires_at`.

A reset attempt must never extend or refresh the 600-second expiry.

After durable success:

- invalidate the restricted session;
- regenerate CSRF token;
- do not write any of the five canonical full-session keys;
- do not write `oneqay.auth.credential_epoch` from the recovery flow;
- do not synthesize MFA, step-up, organization, outlet, or device evidence;
- require a fresh normal login using the replacement password.

Old password authentication must fail and new password authentication must succeed through the existing Sprint 26/27 normal path.

## 8. Frozen credential-epoch capture and re-evaluation

The later source candidate freezes these responsibilities:

### `FirstPartyCredentialEpochRepository`

Read-only Application contract returning the current non-negative integer reset epoch for exact tenant + identity.

### `LaravelFirstPartyCredentialEpochRepository`

- reads only `oneqay_identity_recovery_audit`;
- counts only rows with exact tenant, exact identity, and `event_type = password_reset_completed`;
- no recovery feature-arm dependency;
- no write/update/delete/upsert/truncate behavior;
- Local/Test/CI + durable-persistence fail-closed boundary;
- no TOTP table/secret access.

### `VerifyFirstPartyCredentialEpoch`

- captures the current durable epoch for a freshly verified login;
- compares later session evidence against the current durable epoch using exact integer equality;
- accepts missing legacy session epoch as zero only while durable epoch is zero;
- rejects negative, non-integer, future/invented, or stale session epoch evidence.

### `FirstPartySessionController`

A successful normal full login captures the durable epoch only after password verification and durable context verification, then stores it as `oneqay.auth.credential_epoch` alongside—but separate from—the five canonical context facts.

### `RecoveryCodeController::rotate`

Before recovery-code rotation may use a normal/full session, it must verify that the session credential epoch is current. A pre-reset stale session therefore cannot rotate fresh recovery codes after password reset.

Protected-control/MFA routes remain separately governed and are not widened by this source slice.

## 9. Exact future source envelope

A later Sprint 33 source candidate is authorized only if a separately granted Product Owner source implementation authority explicitly references this published gate. That candidate must contain exactly **39 changed paths** and no others.

Sorted-path SHA-256 with one trailing newline:

`04a1177c12712183a7dda4ae81be1356c0e41294533336c9f999d376c224712a`

Exact paths:

1. `.github/workflows/m7-2-tenant-isolation-regression.yml`
2. `.github/workflows/m7-3-identity-org-context-regression.yml`
3. `.github/workflows/m7-4a-technical-preview-interaction-regression.yml`
4. `.github/workflows/m7-5-preview-db-qualification-regression.yml`
5. `.github/workflows/m7-5-preview-release-artifact.yml`
6. `.github/workflows/sprint21-role-permission-policy-regression.yml`
7. `.github/workflows/sprint22-policy-administration-regression.yml`
8. `.github/workflows/sprint23-initial-tenant-admin-provisioning-regression.yml`
9. `.github/workflows/sprint24-protected-control-admin-lifecycle-regression.yml`
10. `.github/workflows/sprint25-policy-administration-delivery-regression.yml`
11. `.github/workflows/sprint26-identity-credential-verification-regression.yml`
12. `.github/workflows/sprint27-first-party-session-establishment-regression.yml`
13. `.github/workflows/sprint28-initial-password-enrollment-regression.yml`
14. `.github/workflows/sprint29-first-control-principal-credential-bootstrap-regression.yml`
15. `.github/workflows/sprint30-privileged-totp-mfa-regression.yml`
16. `.github/workflows/sprint31-privileged-reauthentication-step-up-regression.yml`
17. `.github/workflows/sprint32-authentication-recovery-proof-regression.yml`
18. `.github/workflows/sprint33-recovery-bound-password-reset-regression.yml`
19. `apps/web/app/Application/Identity/FirstPartyCredentialEpochRepository.php`
20. `apps/web/app/Application/Identity/RecoveryCodeRepository.php`
21. `apps/web/app/Application/Identity/RecoveryCodeService.php`
22. `apps/web/app/Application/Identity/RecoveryPasswordResetRepository.php`
23. `apps/web/app/Application/Identity/RecoveryPasswordResetService.php`
24. `apps/web/app/Application/Identity/RecoveryPasswordResetViolation.php`
25. `apps/web/app/Application/Identity/VerifiedRecoveryProof.php`
26. `apps/web/app/Application/Identity/VerifyFirstPartyCredentialEpoch.php`
27. `apps/web/app/Delivery/Http/Identity/FirstPartySessionController.php`
28. `apps/web/app/Delivery/Http/Identity/FirstPartySessionKeys.php`
29. `apps/web/app/Delivery/Http/Identity/RecoveryCodeController.php`
30. `apps/web/app/Delivery/Http/Identity/RecoveryPasswordResetController.php`
31. `apps/web/app/Infrastructure/Identity/LaravelFirstPartyCredentialEpochRepository.php`
32. `apps/web/app/Infrastructure/Identity/LaravelRecoveryCodeRepository.php`
33. `apps/web/app/Infrastructure/Identity/LaravelRecoveryPasswordResetRepository.php`
34. `apps/web/app/Providers/AppServiceProvider.php`
35. `apps/web/routes/web.php`
36. `apps/web/tests/authentication-recovery-proof.php`
37. `apps/web/tests/first-party-session-establishment.php`
38. `apps/web/tests/recovery-bound-password-reset.php`
39. `docs/RECOVERY_BOUND_PASSWORD_RESET_COMPLETION_FOUNDATION.md`

No migration path is present. `apps/web/config/oneqay.php`, dependency manifests/lockfiles, `.env`/`.env.*`, Preview source, Production source, updater source, deployment source, and release source are outside this envelope.

## 10. Historical workflow successor compatibility

The 17 existing workflows in the exact envelope may be changed only to preserve executable historical coverage while recognizing the exact Sprint 33 successor.

Rules:

- Sprint 33 recognition must require both exact **39-path count** and exact fingerprint `04a1177c12712183a7dda4ae81be1356c0e41294533336c9f999d376c224712a`;
- no branch-name-only, PR-number-only, wildcard, file-count-only, prefix, or future-envelope recognition;
- existing Sprint 30, Sprint 31, and Sprint 32 exact successor fingerprints remain intact;
- migration #10 remains canonical and unchanged for Sprint 33 executable authentication/session/recovery regressions;
- historical stale migration-shape guards may defer only for the exact Sprint 33 successor while substantive tests remain blocking;
- `.github/workflows/m7-5-preview-release-artifact.yml` may isolate the already-published migration #10 only from its schema-free Technical Preview release regression after exact Sprint 33 recognition, preserving the existing Sprint 32 isolation principle;
- no `continue-on-error`, status-check rename, job removal, test suppression, dependency-audit suppression, or broad fail-open behavior;
- Sprint 21–32 executable regressions remain required by the dedicated Sprint 33 workflow.

## 11. Dedicated Sprint 33 qualification workflow

`.github/workflows/sprint33-recovery-bound-password-reset-regression.yml` must enforce at minimum:

- exact 39-path envelope and exact fingerprint;
- no migration diff and canonical migrations exactly #1–#10;
- byte-identical migrations #1–#10 relative to source base;
- no Composer/npm dependency mutation;
- no `.env`/`.env.*` mutation;
- recovery feature source default remains false;
- persistence source default remains false;
- Local/Test/CI-only reset route;
- Technical Preview no reset route and `NO_SCHEMA_CHANGE`;
- Production no reset route / no auth activation;
- updater remains disabled/unwired;
- exact `POST /auth/recovery/password-reset` route and throttle contract;
- exact one-field `password` payload;
- 12/4096-byte boundary success and 11/4097-byte denial;
- no trim/normalization;
- exact restricted-session tenant + identity + state + proof + expiry + code-id binding;
- exact 600-second expiry, including no extension on failed attempt;
- invalid/missing/expired/full-session/pending-MFA/MFA-evidence/step-up-evidence collisions denied;
- protected-control and confirmed-TOTP reset denial;
- TOTP secret non-read/non-decrypt/non-mutation proof;
- existing credential update-only proof;
- old password fails after reset;
- new password succeeds through normal verifier/login;
- exact consumed-code `proof_succeeded` requirement;
- reset replay denied;
- concurrent same-proof reset has at most one winner;
- all remaining unused recovery codes revoked on success;
- exactly one secret-free `password_reset_completed` audit row for winning reset;
- successful reset invalidates restricted session and regenerates CSRF;
- no automatic/full session or canonical five-key population from reset;
- recovery flow never writes `oneqay.auth.credential_epoch`;
- full normal login captures durable epoch count;
- missing legacy epoch passes only when durable epoch is zero;
- pre-reset session epoch fails after reset;
- fresh post-reset login epoch passes;
- later reset invalidates the prior post-reset session epoch;
- Sprint 21–32 executable preservation;
- tracked source remains clean after tests.

## 12. Explicit non-authority

This source-envelope gate preparation and its Draft PR do **not** authorize:

- any of the 39 source/workflow/test/document paths to be mutated yet, other than this gate document itself;
- Sprint 33 application/source implementation;
- creation/modification/execution of migration #11;
- modification of migrations #1–#10;
- new credential-version or session-version schema;
- new feature flag;
- password reset outside the Sprint 32 restricted recovery path;
- authenticated password change;
- administrative password setting/overwrite;
- initial enrollment/bootstrap expansion;
- protected-control recovery;
- MFA/TOTP recovery, bypass, reset, replacement, deletion, or secret disclosure;
- support/operator bypass;
- email/SMS recovery;
- passkeys/WebAuthn;
- OAuth/OIDC/SAML/federation;
- API/bearer token auth;
- Technical Preview auth/schema activation;
- Production auth/schema activation;
- updater activation/wiring;
- deployment or release authority;
- Ready or merge authority for this gate PR;
- merge authority for any later source PR.

## 13. Next lifecycle step after publication

If and only if this documentation-only source-envelope gate is qualified and separately merged under a new exact-head Product Owner merge authorization, the next governed action is **not automatic source mutation**.

A separate Product Owner **Sprint 33 source implementation authorization** must explicitly authorize implementation against the exact publication of this gate and its exact 39-path/fingerprint contract.

Until that happens:

**SPRINT 33 SOURCE IMPLEMENTATION REMAINS NOT AUTHORIZED.**
