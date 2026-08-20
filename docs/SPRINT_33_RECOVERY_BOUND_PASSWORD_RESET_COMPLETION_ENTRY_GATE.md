# Sprint 33 — Recovery-Bound Password Reset Completion Foundation — Entry Gate

Attribution: **Lab | zefry**

## 1. Product Owner authority and exact canonical base

This documentation-only entry gate is prepared under the Product Owner authority:

- canonical `main`: `436429b6327897f6d6044fcb8170a2273fb588ac`;
- canonical tree: `0b73323c5f2f05ec62ad53ea2d4dc7df96c3ef34`;
- concern: **Recovery-Bound Password Reset Completion Foundation**;
- entry-gate preparation authority: **GRANTED**;
- source implementation authority: **NOT GRANTED**;
- migration #11: **NOT AUTHORIZED**;
- Technical Preview: **`NO_SCHEMA_CHANGE`**;
- Production: **`NO-GO / NOT AUTHORIZED`**;
- updater: **`DISABLED / UNWIRED`**.

This gate changes documentation only. It does not authorize application source, workflow YAML, dependency, migration, schema, route, runtime, Preview, Production, updater, deployment, release, or merge mutation.

## 2. Canonical prerequisites preserved

Sprint 33 is defined only on top of the already-published Sprint 26–32 identity/recovery foundations:

1. password credentials remain tenant-bound in `oneqay_identity_password_credentials`, with one row keyed by `(tenant_id, identity_id)` and `password_hash` as the existing credential material;
2. Sprint 28 password policy remains the bounded opaque-password baseline: **12–4096 bytes**, no trimming or normalization, and `PASSWORD_DEFAULT` for password hashing;
3. Sprint 32 recovery proof remains source-default-disabled through `ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=false` and Local/Test/CI only;
4. successful Sprint 32 proof still consumes exactly one recovery code and establishes only restricted `password_reset_required` recovery state for exactly **600 seconds**;
5. the five canonical Sprint 27 full-session context keys remain distinct from restricted recovery state;
6. migrations are exactly #1–#10, with migration #10 already providing `oneqay_identity_recovery_codes` and `oneqay_identity_recovery_audit`;
7. protected-control identities and identities with a confirmed TOTP factor remain ineligible for this recovery path;
8. recovery processing must not read, decrypt, replace, delete, disable, or otherwise mutate a TOTP secret/factor.

## 3. Selected bounded Sprint 33 concern

Sprint 33 selects only this completion slice:

**a valid, unexpired, server-owned Sprint 32 `password_reset_required` restricted recovery session may replace the existing password credential of that exact recovered non-privileged identity, consume the recovery completion capability exactly once, revoke the remaining unused recovery codes for that identity, and then terminate the restricted recovery session without creating a normal/full authenticated session.**

Sprint 33 is not registration, initial enrollment, authenticated password change, administrative password setting, privileged-account recovery, MFA recovery, factor replacement/deletion, support bypass, email/SMS recovery, passkey recovery, federation recovery, API-token recovery, Production recovery, or Technical Preview recovery.

## 4. Recovery-session trust boundary

A later Sprint 33 source implementation may accept password reset only when all of the following are server-derived and valid:

- `oneqay.auth.recovery.tenant_id`;
- `oneqay.auth.recovery.identity_id`;
- `oneqay.auth.recovery.state = password_reset_required`;
- `oneqay.auth.recovery.proved_at`;
- `oneqay.auth.recovery.expires_at`;
- the exact non-secret `code_id` of the already-consumed Sprint 32 recovery proof, carried as additional restricted recovery evidence so completion can be durably bound to the consumed code.

The request must not be allowed to choose or override tenant, identity, organization, outlet, device, role, permission, recovery-code identifier, proof timestamp, or expiry timestamp.

The reset request must fail closed if:

- any canonical full-session context key is present;
- any pending MFA state is present;
- any login-level MFA evidence or step-up evidence is present;
- recovery state is missing or not exactly `password_reset_required`;
- `proved_at <= 0`;
- `expires_at` is invalid, does not represent the published 600-second recovery lifetime, or is already expired according to server time;
- the durable consumed recovery-code row cannot be matched exactly to the recovered tenant + identity + code identifier;
- the matching `proof_succeeded` evidence cannot be established;
- completion for that same consumed recovery code was already recorded.

The restricted recovery session remains a bearer capability and must remain under normal web-session integrity, CSRF, throttling, generic-failure, cache-control, and correlation-ID controls.

## 5. Password-reset input and credential mutation contract

The later bounded implementation must use exactly one server-accepted new-password value. Password confirmation, if provided by UI, must not become additional trust evidence.

The password value must:

- be treated as opaque bytes;
- be between **12 and 4096 bytes inclusive**;
- not be trimmed or normalized;
- be marked sensitive at Application/Delivery boundaries where supported;
- never be written to logs, audit payloads, session state, response bodies, exception text, or durable plaintext/reversible storage;
- be hashed with `password_hash(..., PASSWORD_DEFAULT)` before durable credential replacement.

Durable credential behavior must be **update-only** against the existing exact `(tenant_id, identity_id)` row in `oneqay_identity_password_credentials`.

The implementation must not use credential insert, insert-or-update, upsert, delete, truncate, credential-row recreation, administrative password setting, or a fallback path that silently turns reset into enrollment/bootstrap.

Missing credential state must fail closed because Sprint 32 recovery eligibility already requires an existing password credential.

## 6. Atomic durable completion and replay/concurrency safety

Password-reset completion must be one atomic durable transaction.

At minimum, that transaction must:

1. lock the exact consumed recovery-code row selected by server-owned recovery evidence;
2. revalidate exact tenant + identity ownership;
3. revalidate that the identity still exists and remains eligible for the non-privileged recovery path;
4. revalidate that the exact password credential row still exists;
5. revalidate that the identity is not a protected-control principal and has no confirmed TOTP factor;
6. verify the corresponding successful proof evidence;
7. verify that no prior `password_reset_completed` recovery-audit event exists for the same consumed recovery code;
8. replace exactly one password hash in the existing credential row;
9. revoke every other still-unused/unrevoked recovery code for that same tenant + identity;
10. append secret-free `password_reset_completed` evidence to the existing recovery audit table, bound to the same consumed recovery code and correlation ID.

Concurrent completion attempts for the same recovery proof must serialize on durable state. **At most one password reset may win.** A losing concurrent request or later replay must fail closed and must not perform another credential mutation.

No migration #11, new table, new column, alternate credential store, or rollback mutation is permitted by this design.

## 7. Session termination and credential-epoch revocation requirement

Successful password reset must not silently authenticate the recovered identity.

After durable completion succeeds:

- the restricted recovery session must be invalidated;
- the CSRF token must be regenerated;
- no canonical Sprint 27 full-session context keys may be written by the reset path;
- no MFA, step-up, organization, outlet, or device authentication evidence may be synthesized;
- the user must perform a fresh normal first-party login using the new password.

Sprint 33 must also prevent a pre-reset authenticated session for the same identity from remaining indefinitely authoritative after credential replacement.

Because migration #11 is forbidden, a later source-envelope gate must freeze and prove a **no-schema credential-epoch/session re-evaluation mechanism** using already-available durable recovery audit evidence. Any new session security evidence must remain separate from the canonical five Sprint 27 context keys, and recovery proof/reset must not populate a normal/full-session epoch as a shortcut to authentication.

If a safe no-schema re-evaluation mechanism cannot be proven against the live source and preservation suite, Sprint 33 source implementation must stop at the gate rather than weaken session-revocation semantics or introduce migration #11 without separate Product Owner authority.

## 8. Delivery boundary for later source design

The bounded reset delivery target is one Local/Test/CI-only POST endpoint under the normal Laravel web/CSRF middleware:

`POST /auth/recovery/password-reset`

The endpoint must:

- be unavailable unless durable persistence and the existing authentication-recovery feature arm are enabled;
- remain unavailable in Technical Preview and Production;
- accept only the bounded new-password payload required by the final source-envelope gate;
- derive recovered identity and consumed proof from server-owned restricted session evidence;
- use throttling and the canonical correlation ID;
- collapse invalid/expired/replayed/ineligible recovery states into the existing generic authentication-recovery failure family;
- return `Cache-Control: no-store, private`;
- expose no credential hash, recovery code, recovery code identifier, factor state, privilege state, or enumeration detail.

The exact controller/service/repository names and changed source paths are **not authorized or frozen by this entry gate**. They must be frozen by a separate documentation-only Sprint 33 source-envelope gate before source mutation.

## 9. Schema and dependency decision

Fresh canonical inspection is sufficient to classify this first Sprint 33 slice as **NO_SCHEMA_CHANGE**:

- the password credential row already exists and can be replaced in place;
- migration #10 already contains durable recovery-code state and secret-free recovery audit evidence sufficient for a bounded completion record;
- no new cryptographic protocol or third-party dependency is required for the password-replacement primitive;
- migrations #1–#10 must remain immutable.

Therefore:

- migration #11 is **FORBIDDEN / NOT AUTHORIZED** for this Sprint 33 entry-gate design;
- no Composer/npm manifest or lockfile change is selected by this gate;
- no `.env` or `.env.*` mutation is selected by this gate.

## 10. Mandatory preservation proof before source implementation can qualify

A later documentation-only source-envelope gate must enumerate exact files and a sorted-path fingerprint, and the eventual source candidate must include executable proof for at least:

- valid reset from exact unexpired restricted recovery state;
- 12-byte and 4096-byte password boundaries;
- rejection below/above the bounded password limits;
- password opacity/no trim/no normalization;
- update-only existing credential replacement;
- missing credential fail-closed;
- expired/malformed/missing/colliding recovery-session state fail-closed;
- no caller-controlled tenant/identity/code/proof selectors;
- exact consumed-code binding;
- replay denial after successful reset;
- concurrent same-proof reset with at most one winner;
- remaining unused recovery-code revocation after reset;
- protected-control and confirmed-TOTP revalidation at reset time;
- TOTP secret non-read/non-decrypt/non-mutation preservation;
- secret-free reset audit evidence;
- restricted-session invalidation and CSRF regeneration;
- no automatic/full login after reset;
- fresh normal login required after reset;
- pre-reset target-session credential-epoch/re-evaluation behavior;
- Sprint 21–32 preservation;
- migrations remain exactly #1–#10 with byte-identical historical migration files;
- Technical Preview remains `NO_SCHEMA_CHANGE`;
- Production remains `NO-GO / NOT AUTHORIZED`;
- updater remains `DISABLED / UNWIRED`;
- persistence and recovery feature defaults remain disabled.

## 11. Explicit non-authority

This entry gate does **not** authorize:

- Sprint 33 application/source implementation;
- creation, modification, or execution of migration #11;
- any modification to migrations #1–#10;
- password reset outside the restricted Sprint 32 recovery path;
- authenticated password change;
- administrative password overwrite/set;
- initial password enrollment or bootstrap expansion;
- automatic/full login after recovery or reset;
- privileged/protected-control recovery;
- MFA/TOTP recovery, bypass, reset, replacement, deletion, disablement, or secret disclosure;
- support/operator recovery bypass;
- email/SMS recovery;
- passkeys/WebAuthn;
- OAuth/OIDC/SAML/federation;
- API/bearer-token authentication;
- Technical Preview authentication/schema activation;
- Production authentication/schema activation;
- updater activation/wiring;
- deployment, release, or production publication;
- merge authority for this entry-gate PR;
- source-envelope authority unless separately granted/published under Product Owner governance.

## 12. Next governed lifecycle step

This file is the **Sprint 33 entry-gate preparation artifact only**.

If and only if this documentation-only gate is qualified and later published under a separate exact-head Product Owner merge authorization, the next bounded lifecycle step is a separate documentation-only **Sprint 33 source-envelope gate**. That later gate must freeze the exact implementation paths, exact regression/preservation paths, exact sorted-path fingerprint, exact no-schema session-revocation mechanism, and all route/session/audit details before any application source mutation can begin.

No authority from Sprint 32, PR #210, or this preparation instruction may be reused as Sprint 33 source or merge authority.
