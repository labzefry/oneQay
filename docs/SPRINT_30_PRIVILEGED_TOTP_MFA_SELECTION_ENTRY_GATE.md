# Sprint 30 Selection & Entry Gate — Privileged TOTP MFA Foundation

## Identity and authority

- Product: `oneQay`
- Engineering entity: **Lab | zefry**
- Repository: `labzefry/oneQay`
- Canonical branch: `main`
- Exact canonical base: `3a675d54b2addd949edfb3e8e3562296575d48ec`
- Exact canonical base tree: `097b94060777b6e76562236d0885cba9c035906b`
- Sprint 29: **COMPLETE / IMPLEMENTED / PUBLISHED**
- Technical Preview: **NO_SCHEMA_CHANGE**
- Production: **NO-GO / NOT AUTHORIZED**
- Updater: **DISABLED / UNWIRED**
- Persistence default: `ONEQAY_PERSISTENCE_ENABLED=false`

GitHub remains the Single Source of Truth.

This documentation-only gate selects the next governed concern as:

**Sprint 30 — Privileged TOTP MFA Foundation**

This gate authorizes only the bounded Sprint 30 architecture/source-envelope preparation described below. It does **not** yet authorize application-source mutation, dependency mutation, migration #9, schema execution, Technical Preview activation, Production activation, deployment, Release, updater activation, password reset/recovery, or emergency administrator recovery.

Independent review is not an additional mandatory gate under the current Product Owner continuation model unless the Product Owner explicitly reactivates it. Exact-head Product Owner authority, required CI, exact changed-file scope, tenant isolation, fail-closed runtime controls, and repository protection remain mandatory.

Attribution: **Lab | zefry**

## Why Sprint 30 is the next governed concern

Sprint 26 provides exact tenant + identity password credential verification.

Sprint 27 provides first-party Local/Test/CI password login and server-side session establishment.

Sprint 28 provides two-step initial password enrollment for another existing same-tenant identity.

Sprint 29 closes the first-control-principal credential bootstrap circular dependency and allows the protected control principal to obtain its first password credential and then use the Sprint 27 login flow.

The next material security gap is therefore privileged MFA.

The already-approved DEC-006 authentication architecture establishes TOTP as the baseline deployable MFA for privileged roles and states that MFA is mandatory in principle for privileged actors. The Security Handbook repeats that privileged actors require MFA.

The first protected control principal is now capable of authenticating, but the repository has no published TOTP factor storage, enrollment, verification, privileged-login challenge, or MFA-backed session evidence.

Sprint 30 is selected to close that bounded gap before password lifecycle expansion or higher-risk recovery work.

## Why password reset/recovery is not selected first

Password change, reset, recovery, rotation, and revocation remain valid future credential-lifecycle concerns, but they are not selected as Sprint 30.

Reasons:

1. DEC-006 already requires privileged MFA in principle, so a privileged-MFA gap is an existing approved security requirement rather than a newly invented feature.
2. Password reset/recovery is explicitly a high-risk flow.
3. JRN-003 recovery semantics remain unresolved and require separate Product Owner resolution.
4. Emergency protected-control recovery must not be implemented as an informal bypass around normal authentication or MFA.
5. Adding recovery before a governed MFA lifecycle would create an additional account-takeover surface before the primary privileged second-factor control exists.

Authenticated password change remains separately governed and may be selected after the privileged MFA baseline is published and reconciled.

## Bounded privileged actor for Sprint 30

Sprint 30 does not attempt to implement every future privileged actor from the Enterprise Vision.

The exact first privileged class is the already-published tenant protected-control principal whose durable authority is represented by:

- role: `authorization-policy-administrator`;
- permission: `authorization.policy.manage`.

Sprint 30 must derive privileged status from durable server-side authorization state. A role label, session value, request field, client-side flag, tenant ID, route name, or browser state is never sufficient privilege evidence.

Future platform-superadmin, finance-privileged, release, secret-access, support-impersonation, and other privileged MFA classes remain separately gated unless a later Sprint 30 source-envelope gate explicitly proves that a shared implementation can include them without broadening authority.

## Selected authentication state machine

Sprint 30 must preserve the existing Sprint 27 password verifier and organizational-context checks while inserting an MFA gate before a protected-control session becomes authoritative.

The target state machine is:

1. client submits the existing bounded first-party login request;
2. Sprint 26 verifies the password for exact `(tenant_id, identity_id)`;
3. the server verifies tenant and organizational context using existing Sprint 27 boundaries;
4. the server derives whether the identity currently holds the protected-control role/permission from durable authorization state;
5. a non-privileged identity continues through the existing Sprint 27 session path without being silently promoted to privileged authority;
6. a protected-control identity with no confirmed TOTP factor receives only a restricted MFA-enrollment state;
7. a protected-control identity with a confirmed TOTP factor receives only a pending MFA-challenge state;
8. only successful TOTP verification may promote the pending authentication to a full privileged first-party session;
9. promotion must rotate/invalidate session state before authenticated privileged facts become authoritative;
10. logout clears full, pending-challenge, and restricted-enrollment state.

A password success alone must not create a usable protected-control session once Sprint 30 enforcement is enabled.

## Restricted MFA-enrollment state

A protected-control identity that has a valid password but no confirmed TOTP factor must not be locked into an impossible circular dependency and must not receive a normal privileged session.

Sprint 30 therefore selects a restricted enrollment state with these properties:

- it is server-side session state only;
- it is created only after successful password verification and durable same-tenant protected-control derivation;
- it may access only bounded TOTP enrollment, TOTP enrollment confirmation, and logout surfaces;
- it may not access policy administration, password-enrollment issuance, updater operations, release operations, tenant administration mutation, or any other privileged application surface;
- it is short-lived and fail-closed;
- it is invalidated when enrollment succeeds, fails irrecoverably, expires, or logout occurs;
- successful enrollment does not silently upgrade the restricted state into a full authenticated privileged session.

After successful enrollment confirmation, the identity must perform a fresh normal login and complete the TOTP challenge before a full privileged session is established.

## TOTP enrollment model

Sprint 30 enrollment is for the exact authenticated protected-control identity only.

The user must not supply an arbitrary target identity, tenant, role, permission, or factor owner during enrollment.

Enrollment must:

1. generate the TOTP secret server-side using an approved maintained TOTP library;
2. bind the pending factor to exact `(tenant_id, identity_id)`;
3. expose the enrollment secret/provisioning URI only through the bounded authenticated enrollment response required to configure an authenticator application;
4. never log the secret, provisioning URI, QR payload, factor ciphertext, or entered OTP code;
5. require an OTP confirmation generated from the new factor before the factor becomes confirmed/active;
6. reject a second factor creation if a factor already exists unless a later separately governed replacement/rotation flow is authorized;
7. invalidate the enrollment session after confirmation and require a fresh login.

Sprint 30 does not authorize QR-code package installation. A standards-compatible `otpauth://` provisioning URI is sufficient for the bounded foundation. A future UI may render that URI as a QR code under separate UI/dependency authority.

## TOTP verification and replay resistance

Sprint 30 must use a maintained RFC-compatible TOTP implementation rather than custom cryptographic protocol code.

Verification must:

- use the server-held factor secret only after authenticated tenant + identity binding is established;
- use bounded clock skew/window semantics;
- compare through the selected library's safe verification path;
- rate-limit challenge attempts;
- reject malformed codes without revealing factor state;
- persist enough replay evidence to prevent the same accepted time-step/code from being reused for privileged-session promotion;
- map internal cryptographic, storage, or timing failures to bounded generic authentication/MFA failures;
- never return the secret, ciphertext, internal clock step, or library exception details.

The exact period, digits, algorithm, clock-window, and accepted-step semantics must be frozen in the Sprint 30 source-envelope gate and covered by deterministic regression tests.

## Factor secret storage boundary

TOTP requires a server-side shared secret. The password credential table must not be reused for MFA-factor state.

Sprint 30 selects these storage principles:

- TOTP secret material is **Restricted** data;
- plaintext factor secret must not be stored in the database;
- persisted factor secret must be encrypted at rest using an application encryption key kept outside the database and repository;
- ciphertext must remain tenant + identity scoped;
- the factor record must support confirmed versus pending state;
- the factor record must support replay-prevention state for the last successfully accepted TOTP time step;
- foreign-key ownership must preserve exact tenant + identity binding;
- factor deletion/replacement/recovery is not part of Sprint 30;
- database cascade behavior must not silently weaken identity lifecycle invariants.

The exact physical columns, sizes, indexes, timestamps, encryption-key metadata, and forward-only migration contract must be frozen before migration #9 is authorized.

## Migration #9 decision state

A durable TOTP factor cannot be implemented safely by placing the secret in configuration, session-only state, the password credential table, a log, or another unrelated table.

Therefore a dedicated additive factor table is the preferred physical direction.

However this selection gate does **not** yet authorize migration #9.

Migration state after this PR remains:

**Canonical migrations = exactly #1 through #8.**

The next Sprint 30 source-envelope gate must either:

- authorize one exact additive forward-only migration #9 for the TOTP factor table, with complete table/column/index/foreign-key semantics; or
- present a stronger design proving that no new schema is required without misusing an existing table or weakening durability/replay controls.

No migration may be created before that gate is published.

Technical Preview remains `NO_SCHEMA_CHANGE`; even if a Local/Test/CI migration #9 is later authorized, Preview schema application remains separately denied.

## Cryptographic provider dependency

Custom TOTP cryptographic implementation is not authorized.

Current upstream verification on 2026-08-18 identifies `spomky-labs/otphp` stable `11.5.0` as the preferred candidate provider for the Sprint 30 source-envelope gate because it provides RFC 4226 / RFC 6238 OTP support and supports the project's PHP runtime family.

This selection gate does **not** mutate `composer.json` or `composer.lock` and does not yet authorize the dependency installation.

The source-envelope gate must re-verify the package before mutation and must freeze:

- exact package name;
- exact allowed version constraint;
- resulting lockfile;
- transitive dependency delta;
- license compatibility;
- current security-advisory state;
- no network dependency at runtime for OTP verification;
- no QR-code or unrelated package expansion.

If the candidate is no longer acceptable at source time, the dependency choice must be corrected through a new bounded gate before source mutation rather than silently substituted.

## Application architecture boundary

Sprint 30 source design must preserve Clean Architecture.

Framework-independent Application contracts are expected for:

- privileged-MFA requirement determination;
- factor enrollment orchestration;
- factor confirmation;
- factor verification;
- bounded violation/failure semantics;
- TOTP provider abstraction;
- persistence repository abstraction.

Application code must not directly depend on Laravel DB/query builder, HTTP request/session objects, framework encryption facades, filesystem paths, updater internals, or Production infrastructure.

Infrastructure owns database access, factor encryption/decryption, clock/provider composition, and the concrete TOTP library adapter.

HTTP delivery owns request validation, restricted enrollment/challenge session state, session rotation, throttling, and sanitized response envelopes.

## Session evidence boundary

A full privileged first-party session may contain only bounded server-verified MFA evidence required for authorization enforcement, such as a verified-at timestamp/version marker.

It must not store:

- TOTP secret;
- factor ciphertext;
- OTP code;
- provisioning URI;
- recovery code;
- password;
- role or permission authority as client-trusted facts.

Durable role/permission authority continues to be re-derived server-side.

Existing sessions that lack required Sprint 30 MFA evidence must fail closed for protected-control operations once enforcement is enabled.

## Policy-administration enforcement

`authorization.policy.manage` is a protected privileged permission.

Once Sprint 30 enforcement is enabled, policy-administration delivery must require both:

1. the existing durable protected-control authorization proof; and
2. current Sprint 30 verified-MFA session evidence.

MFA evidence does not replace authorization. Authorization does not replace MFA.

A valid TOTP code from another tenant or identity must never satisfy the protected-control session requirement.

## Feature arming and runtime boundary

Sprint 30 remains Local/Test/CI only.

The source-envelope gate may authorize one explicit fail-closed configuration arm such as:

`oneqay.privileged_totp_mfa.enabled`

backed by:

`ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED`

Repository/default value must remain `false`.

No `.env` or `.env.*` repository file mutation is authorized.

The implementation must remain unavailable in `preview` and `production` runtime classes.

Feature arming is an enable/disable control only. It is not an MFA secret, bypass token, recovery mechanism, or source of authorization.

## Technical Preview preservation

Technical Preview remains **NO_SCHEMA_CHANGE**.

Sprint 30 planning/source-envelope work must not:

- enable first-party password/TOTP authentication in Preview;
- register TOTP enrollment/challenge routes for Preview;
- apply migration #9 to Preview;
- package a schema-execution authority into the Technical Preview release artifact;
- alter Technical Preview synthetic sign-in semantics;
- claim Production-like MFA evidence from the Technical Preview sandbox.

## Production preservation

Production remains **NO-GO / NOT AUTHORIZED**.

Sprint 30 must not:

- activate Production password authentication;
- activate Production TOTP enrollment or challenge;
- create or migrate Production MFA factor data;
- authorize Production session changes;
- authorize deployment, GitHub Release, cPanel mutation, secret provisioning, or Production readiness.

A Production MFA rollout requires separate runtime, secret-management, operational-recovery, monitoring, incident, and deployment authority.

## Updater separation

Updater remains **DISABLED / UNWIRED**.

DEC-006 and the updater threat model require privileged MFA/step-up for future updater installation, but Sprint 30 does not activate or wire updater authorization.

Sprint 30 MFA evidence must not by itself grant updater, release, filesystem, rollback, hosting, cPanel, or platform-superadmin authority.

## Recovery, rotation, and factor replacement are not Sprint 30

Sprint 30 does not authorize:

- TOTP factor replacement;
- TOTP factor deletion/disable;
- MFA recovery codes;
- lost-device recovery;
- support-assisted MFA reset;
- emergency protected-control recovery;
- password change;
- forgot-password reset/recovery;
- password rotation/revocation;
- session/device inventory UI;
- WebAuthn/passkey;
- SMS/email OTP;
- OAuth/OIDC/SAML/federation;
- API/bearer-token authentication.

A user who loses the only TOTP factor is intentionally not given an informal bypass by Sprint 30. Recovery remains a separately governed high-risk concern tied to unresolved JRN-003 semantics.

## Mandatory Sprint 30 source-envelope gate

Before any Sprint 30 application source, dependency, workflow, or migration mutation begins, a second documentation-only gate must be published from a freshly verified live `main`.

That source-envelope gate must define all of the following exactly:

1. live base commit and tree;
2. exact package/version decision and lockfile authority;
3. exact migration #9 decision and full forward-only table contract, or an explicit no-new-schema proof;
4. exact Application/Infrastructure/Delivery/config/route/test/doc paths;
5. exact historical preservation workflow paths that require successor compatibility;
6. exact sorted changed-file count and SHA-256 fingerprint;
7. exact Local/Test/CI feature arm and default-off behavior;
8. exact restricted-enrollment and pending-challenge session keys/state transitions;
9. exact TOTP algorithm/digits/period/window/replay rules;
10. exact encryption boundary and secret non-disclosure requirements;
11. exact policy-administration MFA enforcement semantics;
12. dedicated Sprint 30 regression workflow and preservation chain;
13. Preview/Production/updater non-authority checks;
14. migration preservation proving canonical #1–#8 are unchanged;
15. a source-candidate supersession rule if CI demonstrates that the authorized preservation envelope is incomplete.

Only publication of that exact source-envelope gate may create source implementation authority.

## Minimum future source regression requirements

The source-envelope gate must require regression coverage for at least:

- runtime outside Local/Test/CI denied;
- feature arm false denied;
- persistence disabled denied where durable factor access is required;
- non-privileged Sprint 27 login behavior preserved;
- protected-control password success does not create a full session before MFA;
- protected-control identity without factor receives only restricted enrollment state;
- restricted enrollment state cannot access policy administration;
- arbitrary target identity/tenant enrollment denied;
- cross-tenant factor access denied;
- factor secret never appears in logs/output/session response after initial provisioning response;
- enrollment confirmation required before factor activation;
- duplicate factor enrollment denied;
- malformed/wrong/expired TOTP denied generically;
- accepted TOTP replay denied;
- successful TOTP challenge rotates session and creates bounded MFA evidence;
- policy administration requires durable authorization plus MFA evidence;
- MFA alone does not grant authorization;
- password alone does not grant protected-control session when MFA is enabled;
- logout clears pending/restricted/full MFA state;
- factor secret is encrypted at rest;
- migration #1–#8 remain immutable;
- Preview routes/registration/schema remain denied;
- Production routes/registration/schema remain denied;
- updater remains disabled/unwired;
- Sprint 26 verification, Sprint 27 session, Sprint 28 enrollment, Sprint 29 bootstrap, Sprint 21–25 authorization, tenant isolation, and organizational-context regressions remain successful.

## Canonical next-step statement

After publication of this selection gate, the repository next-work state becomes:

**Sprint 30 — Privileged TOTP MFA Foundation — SELECTED / ARCHITECTURE GATED / SOURCE NOT YET AUTHORIZED.**

The immediate next bounded action is the documentation-only Sprint 30 source-envelope gate described above.

No source mutation may precede that publication.

Attribution: **Lab | zefry**
