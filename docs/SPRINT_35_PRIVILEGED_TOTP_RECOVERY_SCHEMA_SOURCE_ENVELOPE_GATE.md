# Sprint 35 — Privileged TOTP Recovery & Factor Replacement — Schema / Source-Envelope Gate

Attribution: **Lab | zefry**

## 1. Status and exact canonical base

This is the documentation-only schema / source-envelope gate following the published Sprint35 entry gate in PR #219.

Exact canonical baseline:

- `main`: `77d5f4a3a6e87648890024358aeae2456dfcad75`;
- tree: `c1a70dc974c9cff0b45b7c6428653beeedcad931`;
- parent: `e20473402d37f00f729c6ecaefc735e8e838e03c`;
- Sprint35 selected concern: **Privileged TOTP Recovery & Factor Replacement Foundation**.

This gate is documentation-only. It selects the schema design and freezes a later source envelope, but it does **not** implement that source and does not authorize deployment, Technical Preview activation, Production activation, updater wiring, release, or schema execution outside Local/Test/CI qualification.

## 2. Repository evidence and blocking gap

Canonical source already contains the Sprint30 privileged TOTP foundation, including `PrivilegedTotpMfaService`, `PrivilegedTotpMfaRepository`, `PrivilegedTotpEngine`, the Laravel durable repository, the privileged TOTP delivery controller, and executable `privileged-totp-mfa.php` regression.

Migration #9 created the existing durable TOTP-factor authority. Canonical migrations are now #1 through #11, with #1–#11 immutable.

The current model has no separately governed privileged-MFA recovery trust root and no generic durable monotonic TOTP-factor generation/epoch. Sprint32 password-recovery codes intentionally exclude confirmed privileged-TOTP identities and must not be repurposed as MFA recovery authority. The password `credential_epoch` introduced by Sprint34 is credential authority and must not be overloaded as TOTP-factor authority.

Therefore a safe factor replacement cannot be implemented as a no-schema shortcut without either weakening replay/session invalidation or fabricating semantics in unrelated password-recovery evidence.

## 3. Schema decision — migration #12 SELECTED for later source implementation

This gate selects a single forward-only migration #12 for the later Sprint35 source implementation:

`apps/web/database/migrations/0000_00_00_000012_add_totp_factor_epoch_and_recovery_authority.php`

Migration #12 is selected because a dedicated privileged-factor recovery authority and monotonic factor generation are required. **This documentation gate does not create or execute migration #12.**

The later migration must perform only these bounded schema changes:

1. add `factor_epoch` to `oneqay_identity_totp_factors` as unsigned 64-bit / `unsignedBigInteger`, non-null, default `0`;
2. create `oneqay_identity_totp_recovery_codes` as a tenant + identity scoped dedicated MFA recovery-code authority;
3. create `oneqay_identity_totp_recovery_audit` as secret-free successful transition evidence;
4. add only the minimum indexes/unique constraints required for exact selector lookup, tenant/identity ownership, single-use/revocation state, and deterministic audit binding;
5. leave all password-recovery tables and password `credential_epoch` semantics unchanged.

Migration #12 must be forward-only. `down()` must fail using the repository-standard `LogicException` rollback prohibition. Migrations #1–#11 must remain byte-identical.

No historical TOTP recovery backfill is required because there is no pre-Sprint35 TOTP recovery authority to preserve. Existing confirmed TOTP factors start with `factor_epoch = 0`.

## 4. Dedicated recovery-code trust root

Sprint35 privileged TOTP recovery must use **dedicated recovery codes**, not Sprint32 password-recovery codes.

The later source must preserve the repository's proven opaque recovery-code pattern:

- selector is an opaque lookup locator;
- secret is high-entropy Restricted authentication material;
- only a non-reversible digest of the secret is stored;
- complete plaintext codes are returned only at issuance/rotation and are never durable;
- proof uses row locking plus constant-time digest verification;
- a code is single-use and concurrent proof permits at most one winner;
- failures are generic and do not disclose identity, tenant, factor existence, privilege state, selector existence, or recovery eligibility.

The dedicated code namespace/prefix must differ from password recovery so the two authorities cannot be confused or accepted cross-flow.

## 5. Issuance and rotation authority

Dedicated privileged-TOTP recovery codes may be issued or rotated only while all of the following are true:

- runtime is Local/Test/CI;
- `ONEQAY_PERSISTENCE_ENABLED=true`;
- `ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=true`;
- a valid current full first-party session exists;
- the server derives tenant, identity, organization, outlet, and device from canonical session state;
- password credential epoch is current;
- the exact identity has a confirmed privileged TOTP factor;
- the caller freshly proves the exact current password;
- the caller freshly proves the exact current TOTP using the canonical `PrivilegedTotpMfaService` / engine boundary;
- no pending-MFA, restricted password-recovery, or restricted TOTP-recovery state collides with the full session.

Issuance/rotation must atomically revoke all prior unused dedicated privileged-TOTP recovery codes for that tenant + identity and create a fresh bounded code set plus secret-free audit evidence.

The later source must not create an administrative/support master recovery code or allow a caller-selected tenant, identity, factor, epoch, role, or protected-control state.

## 6. Proof and restricted TOTP-recovery session

Dedicated TOTP-recovery proof is allowed only from a clean anonymous browser session.

On successful proof the server must:

1. consume exactly one dedicated code atomically;
2. revalidate tenant, identity, password-credential existence, confirmed TOTP factor, and current factor epoch;
3. write secret-free `proof_succeeded` evidence bound to the consumed dedicated code;
4. invalidate/rotate the browser session and regenerate CSRF;
5. establish only a restricted TOTP-recovery session.

The restricted session must be tenant + identity + consumed-code + factor-epoch bound, with a fixed lifetime of exactly **600 seconds**, matching the existing bounded recovery-session precedent but using distinct session keys and state.

It must contain no normal/full session keys, no pending-login MFA evidence, no privileged `mfa_verified_at`, no step-up scope/evidence, and no password-recovery state.

Failure must never extend the fixed expiry.

## 7. Factor replacement contract

The restricted TOTP-recovery session grants only the authority to replace the exact existing confirmed TOTP factor for the server-bound tenant + identity.

Replacement is **UPDATE-ONLY**. It must not delete the existing factor as a shortcut and must not bootstrap a missing factor.

The source must use the existing canonical TOTP engine/cryptography boundary. No custom TOTP/HMAC/Base32 implementation is permitted.

A replacement flow must:

1. validate restricted recovery state and exact expiry;
2. revalidate consumed dedicated-code proof and current durable `factor_epoch`;
3. lock the exact TOTP-factor authority;
4. generate a fresh TOTP secret through the canonical engine boundary;
5. expose the new enrollment material only transiently to the replacement flow;
6. require a valid TOTP challenge produced from the **new** secret before committing replacement;
7. atomically replace the encrypted/secured factor secret on the existing row;
8. preserve confirmed-factor semantics and set the appropriate fresh confirmation timestamp;
9. increment `factor_epoch` from old+1 exactly once;
10. revoke every remaining unused dedicated privileged-TOTP recovery code for the identity;
11. append exactly one secret-free `factor_replaced` audit event bound to the proof/code and correlation id.

The existing TOTP secret must never be returned, decrypted for caller display, copied into audit text, or used as recovery material.

Concurrent replacements from the same starting factor epoch must have at most one winner.

## 8. Factor-epoch and session invalidation semantics

`oneqay_identity_totp_factors.factor_epoch` is the generic durable authority for TOTP-factor generation after Sprint35.

It is separate from `oneqay_identity_password_credentials.credential_epoch`.

Any session evidence that asserts successful privileged TOTP verification after Sprint35 must be bound to the durable factor epoch current at verification time. A malformed, missing where required, stale, negative, or invented future factor epoch must fail closed for privileged/protected operations.

Successful factor replacement must invalidate the restricted recovery session and regenerate CSRF. It must not auto-login, synthesize a full session, synthesize privileged MFA verification, or synthesize step-up authority.

After replacement the identity must perform a fresh normal password login and then satisfy the canonical TOTP challenge using the replacement factor. Pre-replacement privileged MFA/step-up evidence must not survive factor-epoch advancement.

Password credential epoch is not incremented solely because the TOTP factor changed.

## 9. Frozen HTTP delivery contract for later source

The later source envelope is bounded to these Local/Test/CI-only POST routes:

- `POST /auth/mfa/recovery/codes/rotate`
- `POST /auth/mfa/recovery/proof`
- `POST /auth/mfa/recovery/totp/replace/start`
- `POST /auth/mfa/recovery/totp/replace/confirm`

Normal Laravel web/CSRF semantics apply.

All four routes must use bounded throttling at least equivalent to the existing authentication-recovery controls (`5/minute` and `20/hour`) and generic secret-free failure envelopes.

`rotate` may accept only current-password and current-TOTP proof fields selected by the server contract. `proof` accepts only the dedicated recovery code. `replace/start` accepts no caller-selected identity/factor authority. `replace/confirm` accepts only the new-factor TOTP confirmation code required to prove possession of the newly generated factor.

No GET endpoint may reveal a current TOTP secret. No route is available in Technical Preview or Production.

## 10. Frozen later source changed-file envelope

The later Sprint35 source implementation is frozen to exactly these **19 paths**:

1. `.github/workflows/sprint35-privileged-totp-recovery-regression.yml`
2. `apps/web/app/Application/Identity/IssuedPrivilegedTotpRecoveryCodeSet.php`
3. `apps/web/app/Application/Identity/PrivilegedTotpFactorEpochRepository.php`
4. `apps/web/app/Application/Identity/PrivilegedTotpRecoveryClock.php`
5. `apps/web/app/Application/Identity/PrivilegedTotpRecoveryRepository.php`
6. `apps/web/app/Application/Identity/PrivilegedTotpRecoveryService.php`
7. `apps/web/app/Application/Identity/PrivilegedTotpRecoveryViolation.php`
8. `apps/web/app/Application/Identity/VerifiedPrivilegedTotpRecoveryProof.php`
9. `apps/web/app/Delivery/Http/Identity/FirstPartySessionKeys.php`
10. `apps/web/app/Delivery/Http/Identity/PrivilegedTotpRecoveryController.php`
11. `apps/web/app/Infrastructure/Identity/LaravelPrivilegedTotpFactorEpochRepository.php`
12. `apps/web/app/Infrastructure/Identity/LaravelPrivilegedTotpRecoveryRepository.php`
13. `apps/web/app/Providers/AppServiceProvider.php`
14. `apps/web/database/migrations/0000_00_00_000012_add_totp_factor_epoch_and_recovery_authority.php`
15. `apps/web/routes/web.php`
16. `apps/web/tests/privileged-totp-mfa.php`
17. `apps/web/tests/privileged-totp-recovery.php`
18. `apps/web/tests/run.php`
19. `docs/PRIVILEGED_TOTP_RECOVERY_FACTOR_REPLACEMENT_FOUNDATION.md`

Frozen sorted-path SHA-256 (newline-delimited sorted paths with a trailing newline):

`aaf7fb11490250d29c68dc7b46b62d2ee2239707ca53e004f9c0652878928e3f`

No file outside this envelope may be changed by the later Sprint35 implementation without a new Product Owner-authorized gate amendment.

## 11. Ownership of responsibilities in the frozen envelope

Application layer owns recovery-code issuance/proof/replacement orchestration, violations, factor-epoch verification contract, and secret-safe value objects.

Infrastructure owns durable transactions, row locks, digest comparison/storage, TOTP-factor epoch reads, code/audit persistence, factor update, and exact affected-row enforcement.

Delivery owns session collision checks, restricted-state creation/destruction, CSRF/session disposition, route payload closure, throttling, and generic failure response.

`AppServiceProvider` may bind only the new interfaces to durable adapters and must preserve the published Technical Preview verifier boundary introduced during Sprint34.

The existing `PrivilegedTotpMfaService` and canonical TOTP engine remain the cryptographic authority and are reused rather than replaced.

## 12. Dedicated qualification contract

The future `sprint35-privileged-totp-recovery-regression.yml` must fail closed unless the implementation matches the exact 19-path envelope and frozen fingerprint above.

Executable qualification must prove at minimum:

- migrations #1–#11 byte preservation;
- exact migration #12 forward-only schema and rollback prohibition;
- dedicated TOTP recovery codes cannot be accepted by password recovery and password-recovery codes cannot be accepted by TOTP recovery;
- code secrets are never durable plaintext;
- exact single-use proof and concurrent at-most-one winner;
- clean-anonymous proof requirement;
- generic enumeration-safe failures;
- exact 600-second restricted-session expiry and no failure-based extension;
- restricted-state collision denial against full, pending-MFA, password-recovery, privileged-MFA, and step-up state;
- caller cannot choose tenant/identity/factor/epoch;
- missing/unconfirmed TOTP factor fails closed;
- factor replacement is update-only;
- current factor secret is never disclosed;
- replacement uses the canonical TOTP engine;
- new secret must be proven by a valid new-factor TOTP before durable replacement;
- stale/negative/future factor epoch fails closed;
- successful replacement increments factor epoch exactly once;
- concurrent replacement from one starting epoch has at most one winner;
- remaining dedicated recovery codes are revoked after replacement;
- audit is secret-free;
- current restricted session is invalidated and CSRF regenerated;
- no automatic login, MFA verification, or step-up authority after replacement;
- fresh normal login + replacement-factor challenge is required;
- pre-replacement privileged evidence fails after factor-epoch advancement;
- password credential epoch is not misused as factor epoch;
- Sprint30, Sprint31, Sprint32, Sprint33, and Sprint34 regressions remain preserved;
- Technical Preview remains `NO_SCHEMA_CHANGE`;
- Production remains `NO-GO / NOT AUTHORIZED`;
- updater remains `DISABLED / UNWIRED`;
- source defaults for persistence and authentication recovery remain false.

The preservation chain must also continue repository-native M7 and historical Sprint checks required by canonical governance.

## 13. Explicit exclusions

Sprint35 does not select or authorize:

- administrative/support TOTP reset;
- factor disablement or deletion as a recovery shortcut;
- disclosure/decryption of an existing factor secret to the caller;
- password overwrite or password recovery expansion;
- reuse of Sprint32 password recovery codes as privileged MFA recovery;
- passkeys/WebAuthn;
- OAuth/OIDC/SAML/federation;
- API/bearer-token authentication;
- email/SMS recovery delivery;
- biometric recovery;
- trusted-device bypass;
- production or Technical Preview authentication activation;
- updater activation;
- deployment or release.

## 14. Lifecycle authority boundary

The Product Owner authority for this PR covers preparation, Ready transition, exact-head merge authorization, and squash merge of this **documentation-only gate** after repository-native checks pass.

Publication of this gate does **not** itself authorize creation of migration #12 or mutation of any of the frozen 19 source paths. Those source mutations require new Product Owner authority against the exact published main/tree produced by this gate.

After this gate is published, the next bounded stage is **Sprint35 source implementation against the frozen 19-path envelope**, subject to new authority.
