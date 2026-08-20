# Sprint 34 — Authenticated In-Session Password Change Foundation — Schema / Source-Envelope Gate

Attribution: **Lab | zefry**

## 1. Gate identity and Product Owner authority

This documentation-only gate follows the published Sprint 34 Authenticated In-Session Password Change Foundation entry gate.

Exact canonical baseline at preparation time:

- canonical `main`: `7df6ff9f918ab31ff301b38bb7983121f91934e5`;
- canonical tree: `fce522f5b478b84094acd0226aa4eb243df1180a`;
- published Sprint 34 entry gate: PR #215;
- concern: **Authenticated In-Session Password Change Foundation**;
- schema/source-envelope gate preparation: **AUTHORIZED** by the Product Owner continuation instruction;
- migration #11 design selection for this future source envelope: **AUTHORIZED** by that Product Owner instruction;
- migration #11 creation/execution: **NOT AUTHORIZED by this documentation-only gate itself**;
- Sprint 34 application/source implementation: **NOT AUTHORIZED by this gate preparation**;
- Ready transition for this gate PR: requires a new exact-head Product Owner authorization;
- merge for this gate PR: requires a new exact-head Product Owner authorization;
- Technical Preview: **`NO_SCHEMA_CHANGE`**;
- Production: **`NO-GO / NOT AUTHORIZED`**;
- updater: **`DISABLED / UNWIRED`**.

This PR may change only this gate document. It does not create migration #11, application source, workflow YAML, tests, runtime behavior, Preview/Production behavior, updater wiring, deployment, or release state.

## 2. Frozen schema decision — generic durable credential epoch

Sprint 33 derived credential epoch from the count of `password_reset_completed` rows in `oneqay_identity_recovery_audit`. That mechanism correctly invalidates sessions after recovery-bound password reset but is intentionally recovery-specific.

Sprint 34 must not fabricate `password_reset_completed` or any other recovery event to represent a normal authenticated password change.

The selected minimal generic design is therefore one additive column on the existing exact password-credential authority:

`oneqay_identity_password_credentials.credential_epoch`

The future migration path is exactly:

`apps/web/database/migrations/0000_00_00_000011_add_credential_epoch_to_identity_password_credentials.php`

Frozen column semantics:

- type: unsigned 64-bit integer compatible with Laravel `unsignedBigInteger`;
- non-null;
- default: `0`;
- scoped by the existing credential primary key `(tenant_id, identity_id)`;
- no new index is required because exact credential lookup already uses the primary key;
- no password hash, secret, token, session identifier, TOTP material, or recovery-code secret is stored in this column;
- the value is monotonic and may increase only when the exact password credential is successfully replaced through a separately governed credential-mutation path.

Migration #11 introduces no new table and does not modify the primary key, foreign key, tenant boundary, credential hash type, or identity relationship.

## 3. Frozen migration #11 backfill and preservation semantics

Migrations #1 through #10 remain byte-identical and immutable.

Migration #11 is additive and forward-only. Its `down()` path must deny rollback by throwing the repository-standard `LogicException` used by prior governed forward-only migrations.

The migration must preserve the effective Sprint 33 epoch for existing Local/Test/CI data.

For every existing exact credential row `(tenant_id, identity_id)`:

1. add `credential_epoch` with default `0`;
2. determine the historical count of durable recovery-audit rows for the same exact tenant + identity where `event_type = password_reset_completed`;
3. set that credential row's epoch to that exact non-negative historical count;
4. count neither `proof_succeeded` nor any other recovery event;
5. never infer tenant or identity across boundaries;
6. fail the migration rather than truncate, wrap, cap, or silently coerce an invalid/out-of-range epoch;
7. leave credentials with no historical successful reset at epoch `0`.

The materialization must be deterministic for both the repository's SQLite CI qualification and MySQL-compatible target architecture.

No migration #12 is selected or implied.

## 4. Generic credential-epoch authority after migration #11

After migration #11 is present in a separately authorized Sprint 34 source candidate, the credential row becomes the sole durable current credential-epoch authority.

Frozen rules:

1. `LaravelFirstPartyCredentialEpochRepository` reads only the exact credential row's `credential_epoch` for current epoch authority;
2. it no longer derives current authority by counting recovery-audit rows;
3. a missing credential row fails closed rather than returning an invented authority;
4. an invalid, negative, non-integer, out-of-range, or otherwise unrepresentable epoch fails closed;
5. `VerifyFirstPartyCredentialEpoch` retains exact integer-equality semantics;
6. a legacy session missing `oneqay.auth.credential_epoch` remains acceptable only while the durable credential epoch is exactly `0`;
7. a fresh normal first-party login captures the current durable epoch into `oneqay.auth.credential_epoch`;
8. `oneqay.auth.credential_epoch` remains separate from the five canonical Sprint 27 context keys and must not be added to `FirstPartySessionKeys::all()`;
9. an authenticated session may never advance or rewrite its own epoch;
10. only a fresh normal login after credential verification may capture the new durable epoch.

This generalizes Sprint 33 session invalidation without weakening any Sprint 33 recovery property.

## 5. Sprint 33 recovery-reset compatibility under generic epoch

Recovery-bound password reset remains a separately governed and already-published path.

The Sprint 34 source candidate must preserve every Sprint 33 proof/reset invariant while changing only the durable epoch source.

Inside the existing locked Sprint 33 password-reset transaction, `LaravelRecoveryPasswordResetRepository` must:

- lock/revalidate the exact credential row as before;
- read the exact current `credential_epoch`;
- reject invalid or unrepresentable epoch state;
- update the password hash and increment `credential_epoch` by exactly one in the same successful credential-row mutation;
- preserve update-only semantics;
- preserve exact consumed-code binding;
- preserve remaining recovery-code revocation;
- preserve exactly one secret-free `password_reset_completed` recovery-audit event for the winning reset;
- preserve replay/concurrency at most one winner;
- preserve protected-control and confirmed-TOTP recovery denial;
- preserve restricted-session invalidation and fresh-login requirement.

The recovery audit remains recovery evidence. It is no longer the current-session epoch authority after migration #11.

Historical reset count and generic epoch must agree immediately after migration backfill. Each successful later reset advances both the recovery evidence history and the generic credential epoch exactly once.

## 6. Frozen Sprint 34 delivery contract

The future bounded route is exactly:

`POST /auth/password/change`

Route rules:

- normal Laravel web/CSRF middleware remains mandatory;
- route exists only in Local/Test/CI;
- no new Sprint 34 environment feature flag is introduced;
- durable persistence must be operational or the request fails closed;
- throttle chain is exactly `throttle:5,1` and `throttle:20,60`;
- Technical Preview exposes no Sprint 34 password-change route;
- Production exposes no Sprint 34 password-change route;
- updater and deployment behavior remain unchanged.

Closed business payload after normal `_token` handling permits only these keys:

- required string `current_password`;
- required string `new_password`;
- optional string `totp_code`, permitted only when the server determines fresh privileged TOTP proof is required.

Caller-provided tenant, identity, organization, outlet, device, role, permission, protected-control state, factor-required flag, credential epoch, step-up scope, recovery code, recovery proof, audit identifier, or credential-row selector is forbidden.

For a non-privileged identity where server policy does not require TOTP, supplying `totp_code` is rejected rather than silently ignored.

On success the response is secret-free, includes the canonical correlation ID, uses HTTP 200, and carries `Cache-Control: no-store, private`.

Invalid session, stale epoch, wrong current password, invalid replacement password, same-password replacement, missing credential, privilege/TOTP failure, concurrency loss, storage failure, or malformed payload collapse into one generic password-change failure family without enumeration detail.

## 7. Full-session trust boundary clarification

Sprint 34 must reuse the actual published Sprint 27 session semantics rather than invent a parallel session model.

Required server-owned context is:

- `oneqay.auth.identity_id`;
- `oneqay.auth.tenant_id`;
- `oneqay.auth.organization_id`.

`oneqay.auth.outlet_id` and `oneqay.auth.device_id` retain their published optional semantics: when present they must be valid server-established values; their absence is not converted into caller authority or a synthetic value.

The separate `oneqay.auth.credential_epoch` must be an exact current integer authority.

The password-change path must reject:

- missing or malformed required full-session context;
- stale/missing-after-epoch-zero/negative/future/malformed credential epoch;
- any pending-MFA restricted session state;
- any recovery restricted-session state;
- caller attempts to replace server context.

Existing full-session MFA or step-up evidence must not be trusted as a substitute for the fresh password/TOTP reauthentication frozen below.

## 8. Frozen current-password reauthentication and TOCTOU protection

A valid session alone is insufficient authority to change a password.

The future `AuthenticatedPasswordChangeService` may use the existing `VerifyFirstPartyIdentityCredential` for an early generic current-password preflight, but the authoritative verification must occur again after the exact credential row is locked inside the password-change transaction.

The Infrastructure repository must therefore receive the sensitive current password and re-run `password_verify` against the locked current hash before mutation.

This locked recheck is mandatory so a concurrent credential change cannot create a stale-read authorization gap between preflight verification and replacement.

Current password requirements:

- opaque bytes;
- non-empty;
- maximum 4096 bytes;
- no trim or normalization;
- never logged, audited, returned, placed in session, or persisted in plaintext/reversible form.

## 9. Frozen new-password policy and same-password denial

`new_password` is opaque byte input with the already-published bounded replacement policy:

- minimum 12 bytes;
- maximum 4096 bytes;
- no trim;
- no normalization;
- hash using PHP `PASSWORD_DEFAULT`;
- no plaintext/reversible persistence;
- no logging, response, session, audit, exception, or telemetry exposure.

After locking the current credential row, the repository must reject a replacement for which `password_verify($newPassword, $currentHash)` succeeds.

Sprint 34 introduces no password-history table, history window, complexity regex, normalization, breach-service dependency, or third-party password policy dependency.

## 10. Frozen privileged/TOTP reauthentication

Server policy determines whether privileged TOTP reauthentication is required.

The future service must reuse the existing `PrivilegedTotpMfaService` / canonical TOTP infrastructure. It must not implement custom TOTP, HMAC, Base32, factor storage, or secret decryption outside that boundary.

Rules:

1. ordinary identities do not provide `totp_code`;
2. when `PrivilegedTotpMfaService::requiredState()` returns no privileged requirement, `totp_code` must be absent;
3. when protected-control policy requires TOTP, the factor must be in the published confirmed state;
4. `totp_code` is required and must satisfy the existing exact six-digit TOTP input boundary;
5. a fresh `challenge()` must succeed for this password-change attempt;
6. historical full-session MFA evidence or Sprint 31 step-up evidence does not replace this fresh challenge;
7. pending/absent/ineligible factor state fails closed;
8. the password-change path never reads, returns, replaces, deletes, disables, resets, or re-enrolls the TOTP secret/factor.

If TOTP proof succeeds but a later locked credential race causes the password mutation to lose, the attempt fails closed. Consuming that one-time TOTP step is acceptable; no credential mutation may proceed on stale authority.

## 11. Frozen authenticated password-change transaction

The exact transaction is driven through the existing `PersistenceTransaction` boundary.

`LaravelAuthenticatedPasswordChangeRepository` must execute these credential/recovery mutations in one durable transaction after any required fresh TOTP challenge:

1. lock the exact credential row by server-owned tenant + identity;
2. require that the exact credential row exists and contains a valid current hash;
3. require that durable `credential_epoch` exactly equals the session epoch supplied from validated server session state;
4. reverify `current_password` against the locked current hash;
5. reject `new_password` if it verifies against the current hash;
6. validate the new password's 12–4096 byte boundary without trim/normalization;
7. hash the replacement with `PASSWORD_DEFAULT`;
8. atomically update exactly one existing credential row with the new hash and `credential_epoch = old_epoch + 1`;
9. revoke every still-unused and unrevoked recovery code for the same exact tenant + identity using the server-owned event time;
10. return only non-secret success to the Application layer.

The mutation remains update-only. It must not insert, upsert, delete, truncate, recreate, bootstrap, enroll, or administratively set a credential as fallback.

Missing credential state fails closed.

Concurrent password-change attempts from the same starting epoch must serialize on the locked credential row. At most one may win; every later contender must fail the expected-epoch/current-password revalidation.

## 12. Recovery-code disposition

Successful authenticated password change revokes every pre-change unused/unrevoked Sprint 32 recovery code for the exact tenant + identity.

It does not:

- consume a recovery code as proof;
- create recovery proof state;
- append `proof_succeeded`;
- append or fabricate `password_reset_completed`;
- extend a recovery TTL;
- create new recovery codes automatically.

A new recovery-code rotation remains a separately invoked published operation after the user completes a fresh normal login.

## 13. Security-event evidence decision

Sprint 34 deliberately does **not** repurpose `oneqay_identity_recovery_audit` for normal password-change events and does not create a second audit table in migration #11.

The bounded schema purpose of migration #11 is credential-session revocation authority only.

The HTTP operation preserves canonical correlation-ID handling and secret-free success/failure envelopes. Any future generalized durable credential/security audit journal is separately governed and is not silently introduced in this Sprint 34 foundation.

This decision avoids semantic contamination of recovery evidence while keeping migration #11 minimal.

## 14. Session disposition after success

After the durable password-change transaction succeeds:

- current full session is invalidated;
- CSRF token is regenerated;
- the controller must not rewrite the five canonical full-session context keys;
- it must not write the new credential epoch into the old session;
- it must not synthesize MFA, step-up, recovery, organization, outlet, or device evidence;
- it must not establish automatic/full login;
- fresh normal login with the replacement password is mandatory.

Every other pre-change session for that same identity retains the old epoch and therefore fails exact durable-epoch comparison when exercising an epoch-protected authenticated action.

Fresh normal login captures the new epoch.

## 15. No new feature/config/dependency surface

Sprint 34 source design introduces no new environment variable or feature flag.

The existing fail-closed runtime/persistence boundaries remain authoritative:

- Local/Test/CI only;
- durable persistence source default remains disabled;
- Technical Preview remains `NO_SCHEMA_CHANGE` and must not execute migration #11;
- Production remains `NO-GO / NOT AUTHORIZED` and must not execute migration #11;
- updater remains `DISABLED / UNWIRED`.

No Composer/npm manifest or lockfile change is selected.

No `.env` or `.env.*` path is selected.

## 16. Exact future Sprint 34 source envelope

A later source implementation is valid only after this gate is separately published and the Product Owner grants a new source implementation authority referencing the exact published gate/base.

The future source candidate must contain exactly **35 changed paths** and no others.

Sorted-path SHA-256 with one trailing newline:

`e3b724002cfc0be1ef890d1b5594a2a5179123f949f6f486354e21950c7328eb`

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
19. `.github/workflows/sprint34-authenticated-password-change-regression.yml`
20. `apps/web/app/Application/Identity/AuthenticatedPasswordChangeClock.php`
21. `apps/web/app/Application/Identity/AuthenticatedPasswordChangeRepository.php`
22. `apps/web/app/Application/Identity/AuthenticatedPasswordChangeService.php`
23. `apps/web/app/Application/Identity/AuthenticatedPasswordChangeViolation.php`
24. `apps/web/app/Delivery/Http/Identity/AuthenticatedPasswordChangeController.php`
25. `apps/web/app/Infrastructure/Identity/LaravelAuthenticatedPasswordChangeRepository.php`
26. `apps/web/app/Infrastructure/Identity/LaravelFirstPartyCredentialEpochRepository.php`
27. `apps/web/app/Infrastructure/Identity/LaravelRecoveryPasswordResetRepository.php`
28. `apps/web/app/Providers/AppServiceProvider.php`
29. `apps/web/database/migrations/0000_00_00_000011_add_credential_epoch_to_identity_password_credentials.php`
30. `apps/web/routes/web.php`
31. `apps/web/tests/authenticated-password-change.php`
32. `apps/web/tests/authentication-recovery-proof.php`
33. `apps/web/tests/first-party-session-establishment.php`
34. `apps/web/tests/recovery-bound-password-reset.php`
35. `docs/AUTHENTICATED_PASSWORD_CHANGE_FOUNDATION.md`

No other Application, Domain, Infrastructure, Delivery, config, dependency, lockfile, environment, Preview, Production, updater, deployment, or release path is authorized by this envelope.

## 17. Existing files intentionally excluded from source mutation

The following existing contracts remain adequate and must stay unchanged unless a future separately published correction gate proves otherwise:

- `apps/web/app/Application/Identity/FirstPartyCredentialEpochRepository.php`;
- `apps/web/app/Application/Identity/VerifyFirstPartyCredentialEpoch.php`;
- `apps/web/app/Application/Identity/VerifyFirstPartyIdentityCredential.php`;
- `apps/web/app/Application/Identity/PrivilegedTotpMfaService.php`;
- `apps/web/app/Application/Identity/RecoveryPasswordResetRepository.php`;
- `apps/web/app/Delivery/Http/Identity/FirstPartySessionController.php`;
- `apps/web/app/Delivery/Http/Identity/FirstPartySessionKeys.php`;
- `apps/web/app/Delivery/Http/Identity/RecoveryCodeController.php`;
- `apps/web/app/Delivery/Http/Identity/RecoveryPasswordResetController.php`;
- `apps/web/config/oneqay.php`;
- Composer/npm manifests and lockfiles.

The new source must compose these published contracts instead of broadening them merely for convenience.

## 18. Historical workflow successor compatibility

The 18 existing workflow YAML files in the exact envelope may change only to recognize the exact Sprint 34 successor while preserving substantive historical regressions.

Rules:

- Sprint 34 successor recognition requires both exact **35-path count** and exact fingerprint `e3b724002cfc0be1ef890d1b5594a2a5179123f949f6f486354e21950c7328eb`;
- the published Sprint 33 **39-path** fingerprint `04a1177c12712183a7dda4ae81be1356c0e41294533336c9f999d376c224712a` remains intact;
- prior exact Sprint 30–32 fingerprints remain intact;
- no branch-name-only, PR-number-only, wildcard, prefix-only, file-count-only, or future-envelope shortcut is permitted;
- historical stale migration-shape checks may defer only for the exact Sprint 34 successor while substantive executable tests remain blocking;
- migration #11 is canonical only for the exact Sprint 34 successor and must not rewrite migrations #1–#10;
- the M7.5 Technical Preview release path must continue to enforce schema-free Preview behavior and must isolate Sprint 34 migration #11 from Preview release qualification only after exact Sprint 34 recognition;
- no `continue-on-error`, job removal, status-check rename, dependency-audit suppression, test suppression, or broad fail-open behavior is permitted.

## 19. Dedicated Sprint 34 qualification workflow

`.github/workflows/sprint34-authenticated-password-change-regression.yml` must enforce at minimum:

- exact 35-path envelope and exact fingerprint;
- source parent is the exact separately published schema/source-envelope gate base authorized for source implementation;
- migrations are exactly #1 through #11 for Sprint 34 Local/Test/CI qualification;
- migrations #1–#10 are byte-identical to the source base;
- migration #11 is additive/forward-only and only adds generic credential epoch authority;
- migration #11 preserves exact historical Sprint 33 reset-derived epoch through backfill;
- no migration #12;
- no dependency or lockfile mutation;
- no `.env`/`.env.*` mutation;
- no new Sprint 34 feature flag;
- Local/Test/CI-only password-change route;
- Technical Preview route denial and `NO_SCHEMA_CHANGE`;
- Production route/schema denial;
- updater remains disabled/unwired;
- exact `POST /auth/password/change` route and throttle chain;
- closed payload and conditional TOTP contract;
- caller cannot select tenant/identity/context/epoch/privilege;
- exact full-session and pending/recovery collision checks;
- current durable epoch check;
- locked current-password reauthentication;
- current-password failure generic and secret-free;
- 12-byte and 4096-byte replacement success boundaries;
- 11-byte and 4097-byte denial;
- no trim/no normalization;
- same-password denial;
- protected-control confirmed-TOTP fresh challenge requirement;
- ordinary identity rejects caller-supplied `totp_code`;
- TOTP secret non-disclosure/non-mutation preservation;
- update-only credential replacement;
- missing credential fail-closed;
- epoch increments exactly one per successful change;
- same-starting-epoch concurrency at most one winner;
- all unused/unrevoked pre-change recovery codes revoked;
- no fabricated recovery audit event from normal password change;
- current session invalidated and CSRF regenerated;
- no automatic full login or epoch rewrite into old session;
- old password fails after success;
- new password succeeds through normal login;
- old sessions fail stale epoch checks;
- fresh login captures new epoch;
- Sprint 33 recovery reset still increments generic epoch exactly once and preserves recovery audit;
- Sprint 21–33 executable preservation;
- tracked source cleanliness.

## 20. Test-path responsibilities

The four exact test paths in the envelope are selected for distinct reasons:

### `apps/web/tests/authenticated-password-change.php`

New dedicated executable behavior for the complete Sprint 34 password-change boundary, including ordinary and privileged flows, concurrency, recovery-code revocation, session invalidation, and secret handling.

### `apps/web/tests/recovery-bound-password-reset.php`

Updates canonical migration expectation from #1–#10 to #1–#11 and proves Sprint 33 reset semantics after current epoch authority moves to the credential row.

### `apps/web/tests/first-party-session-establishment.php`

Updates canonical migration compatibility and proves fresh login captures generic credential epoch while legacy/missing/stale rules remain fail closed.

### `apps/web/tests/authentication-recovery-proof.php`

Updates canonical migration compatibility and proves Sprint 32 recovery proof remains unchanged by migration #11 and Sprint 34 delivery.

No other historical test file is selected for mutation. The dedicated Sprint 34 workflow must still execute the historical Sprint 21–33 preservation chain.

## 21. Source implementation authority boundary

Publication of this gate is a prerequisite, not source implementation authority by itself.

A later Product Owner source authorization must explicitly reference at minimum:

- exact published schema/source-envelope gate commit and tree;
- source branch name;
- migration #11 creation authority for the exact path frozen here;
- exact 35 changed paths;
- exact sorted-path fingerprint `e3b724002cfc0be1ef890d1b5594a2a5179123f949f6f486354e21950c7328eb`;
- authority to create/update one bounded source commit and one Draft PR;
- continued Local/Test/CI-only runtime boundary;
- Technical Preview `NO_SCHEMA_CHANGE`;
- Production `NO-GO / NOT AUTHORIZED`;
- updater `DISABLED / UNWIRED`;
- separate exact-head Ready/Merge authority after qualification.

Without that later source authorization, none of the 35 future source paths may be mutated under Sprint 34 authority.

## 22. Explicit non-authority of this gate PR

This schema/source-envelope gate preparation does **not** authorize:

- creation or execution of migration #11 in this PR;
- modification of migrations #1–#10;
- application/source implementation;
- workflow YAML mutation;
- test mutation;
- route/runtime mutation;
- dependency or environment mutation;
- Technical Preview schema/auth activation;
- Production schema/auth activation;
- updater activation;
- deployment;
- release;
- generalized credential audit infrastructure;
- administrative password overwrite;
- support/operator credential setting;
- MFA/TOTP recovery or factor lifecycle;
- passkeys/WebAuthn;
- federation;
- API/bearer authentication;
- Ready transition for this gate PR without a new exact-head Product Owner authorization;
- merge of this gate PR without a new exact-head Product Owner authorization.

## 23. Next governed lifecycle step

If and only if this documentation-only gate is technically qualified and then published through new exact-head Product Owner Ready/Merge authorization, the next step is a separately authorized **Sprint 34 source implementation** against that exact published canonical base.

The source implementation must remain inside the exact 35-path envelope and fingerprint frozen here. Any material architecture or path change requires another documentation-only correction gate before source mutation.

Authorities from PR #211 through PR #215 are historical/consumed and do not provide standing Sprint 34 source or future merge authority.
