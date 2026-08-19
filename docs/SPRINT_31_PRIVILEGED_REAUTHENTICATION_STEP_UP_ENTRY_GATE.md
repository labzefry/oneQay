# Sprint 31 — Privileged Reauthentication / Step-Up Session Freshness — Entry Gate

> **Status:** ENTRY GATE / DOCUMENTATION-ONLY / NO SOURCE AUTHORITY
> **Canonical baseline:** `65eb6a41d42efff04ebb1bc02a91a895458097b7`
> **Canonical baseline tree:** `123deb9b2e145cd406e1e577f21b70761e6ee57d`
> **Canonical product:** oneQay
> **Repository:** `labzefry/oneQay`
> **Attribution:** Lab | zefry

## 1. Purpose

This gate establishes the bounded Sprint 31 candidate **Privileged Reauthentication / Step-Up Session Freshness Foundation** after publication of Sprint 30 — Privileged TOTP MFA Foundation and the post-Sprint30 canonical reconciliation.

The gate exists because Sprint 30 proves privileged MFA at full-session establishment time, while DEC-006 separately requires risk-based reauthentication / step-up for sensitive operations. The current policy-administration middleware requires `oneqay.auth.mfa_verified_at` when privileged TOTP MFA is armed, but it does not yet establish or enforce operation-specific recent authentication.

This document creates **no application-source authority**. A separate source-envelope gate must be published before implementation.

## 2. Canonical facts preserved from Sprint 30

Sprint 31 must preserve all of the following without reinterpretation:

- Sprint 21–30 governed identity/control foundations are published within bounded authority.
- Canonical source migrations are exactly **#1 through #9**.
- Sprint 30 directly pins `spomky-labs/otphp` **11.5.0** and oneQay does not implement custom TOTP/HMAC/Base32 cryptography.
- `ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED=false` remains the source default.
- Protected-control login with privileged TOTP MFA armed requires restricted enrollment/challenge state before full-session establishment.
- `oneqay.auth.mfa_verified_at` is login-level MFA evidence only; it does not replace tenant, organizational, role, permission, or protected-control authorization.
- TOTP secrets remain encrypted, tenant+identity context-bound, and never stored as plaintext.
- TOTP confirmation/challenge remains replay-safe using monotonic durable accepted time-step state.
- Technical Preview remains **`NO_SCHEMA_CHANGE`**.
- Production remains **`NO-GO / NOT AUTHORIZED`**.
- Updater remains **`DISABLED / UNWIRED`**.
- Durable persistence remains source-default-disabled with `ONEQAY_PERSISTENCE_ENABLED=false`.
- JRN-003 remains **UNRESOLVED**; no password/MFA recovery or factor-replacement authority exists.

## 3. Selected first protected operation

Sprint 31 is intentionally narrow.

The first and only protected operation class in this sprint is:

**Policy administration mutation** — existing `POST /administration/policy/mutations`.

No updater operation, deployment operation, support impersonation, break-glass action, password lifecycle action, factor lifecycle action, tenant lifecycle action, payment/finance operation, or Production operation is added to the step-up scope in Sprint 31.

The canonical step-up scope identifier for this sprint is:

`policy_administration`

Future sensitive-operation scopes require separate authority.

## 4. Feature arm and runtime boundary

Sprint 31 source, if later authorized, must use a separate feature arm:

`ONEQAY_PRIVILEGED_STEP_UP_ENABLED`

Required semantics:

- source default: **false**;
- allowed runtime classes: **Local / Test / CI only**;
- false preserves the published Sprint 30 policy-administration behavior exactly;
- true never activates Preview or Production delivery;
- true requires the published privileged TOTP MFA foundation to be armed and usable;
- a misconfigured step-up arm must fail closed and must never downgrade to password-only or MFA-presence-only authorization;
- no hidden in-memory, test-only, header, query-string, environment-bypass, or superuser bypass is permitted.

The freshness window is fixed for Sprint 31 at:

**300 seconds / 5 minutes**.

A future change to the freshness window is separately governed.

## 5. Reauthentication factors

Successful Sprint 31 step-up requires **both** of the following for the identity already represented by the authenticated server-side session:

1. fresh verification of the existing tenant-scoped first-party password credential; and
2. a fresh TOTP code for the existing confirmed privileged TOTP factor.

The request must not be permitted to choose or override the identity, tenant, organization, outlet, or device being reauthenticated. Those facts are derived from the existing full authenticated session.

Password verification must reuse the published first-party credential-verification boundary. TOTP verification must reuse the published Sprint 30 TOTP engine/repository/service boundary and its monotonic replay protection. Sprint 31 must not implement a second password verifier, custom TOTP cryptography, or a parallel factor store.

## 6. Step-up endpoint contract

The source-envelope gate must preserve this selected endpoint contract:

`POST /auth/reauthenticate/privileged`

The endpoint exists only when all applicable Local/Test/CI feature/runtime gates are satisfied.

Minimum controls:

- authenticated full first-party session required;
- protected-control principal required;
- existing valid login-level MFA evidence required when Sprint 30 MFA is armed;
- CSRF protection remains applicable through the first-party Web session boundary;
- throttling baseline: `5/minute` and `20/hour`;
- password + TOTP input only; no identity/tenant/context selector;
- generic failure response that does not distinguish wrong password, wrong TOTP, absent factor, disabled identity, authorization loss, or factor state;
- response and logs must never contain password, TOTP code, TOTP secret, provisioning URI, credential hash, encryption key, or decrypted factor material.

There is no GET endpoint, no polling endpoint, no recovery endpoint, and no alternate factor endpoint in Sprint 31.

## 7. Step-up session evidence

Sprint 31 step-up evidence is **session-local security evidence**, not durable authorization and not a new identity/factor record.

The source-envelope gate must preserve separate evidence semantics equivalent to:

- `oneqay.auth.step_up_verified_at`;
- `oneqay.auth.step_up_scope`;
- server-derived binding to the exact current full-session identity/tenant/organization/outlet/device facts.

The exact internal representation of the context binding may be frozen by the source-envelope gate, but it must not trust client-supplied context.

`FirstPartySessionKeys::all()` must continue to represent the canonical five Sprint 27 full-context keys only. Step-up evidence, like `mfa_verified_at`, is separate security evidence and must not silently redefine the Sprint 27 full-context contract.

## 8. Freshness and authorization semantics

For `policy_administration` while Sprint 31 is armed, a request is allowed to reach the existing policy-administration delivery service only when all of these conditions hold:

1. the five canonical full authenticated session facts are valid;
2. current durable tenant/organizational authorization re-verification succeeds;
3. current protected-control authorization still succeeds;
4. published login-level MFA evidence remains present where required;
5. step-up scope is exactly `policy_administration`;
6. step-up evidence is bound to the exact current full-session context;
7. `step_up_verified_at` is a valid server-written timestamp; and
8. age of step-up evidence is not greater than **300 seconds**.

Step-up freshness does not grant role, permission, tenant membership, organization access, or protected-control status. Authorization must continue to be re-derived from durable server-side policy.

A context mismatch, stale timestamp, missing evidence, malformed evidence, authorization loss, session inconsistency, or feature/runtime inconsistency must fail closed.

## 9. Successful step-up transition

After both password and TOTP re-verification succeed:

- the accepted TOTP time step must be consumed atomically using the existing replay-safe factor state;
- the browser session identifier must be regenerated/rotated before new step-up evidence becomes authoritative;
- CSRF token rotation/regeneration must remain consistent with the existing first-party session security model;
- the exact five authenticated context facts are preserved only after server-side re-verification;
- login-level MFA evidence remains distinct from the new operation-level step-up evidence;
- step-up scope is exactly `policy_administration`;
- the freshness timestamp is written from the server clock, never from request input.

Successful step-up does **not** create a new login, new role, new permission, new tenant membership, new TOTP factor, new credential, or recovery authority.

## 10. Expiry and invalidation

Step-up evidence becomes unusable when any of the following occurs:

- more than 300 seconds have elapsed;
- logout;
- authenticated session invalidation/regeneration that does not explicitly preserve freshly re-verified step-up evidence;
- identity/tenant/organization/outlet/device context mismatch;
- durable authorization no longer permits the protected operation;
- protected-control status is lost;
- relevant security state is malformed or absent.

A valid freshness window may authorize multiple policy-administration mutations during that same 300-second window, but every mutation still requires normal durable authorization and policy validation.

## 11. Schema classification

Sprint 31 classification is:

**NO_SCHEMA_CHANGE**

Rules:

- migration #10 is **FORBIDDEN** in Sprint 31;
- migrations #1–#9 remain unchanged;
- no new table, column, factor record, recovery record, audit schema, token schema, session database schema, or credential schema is authorized;
- durable TOTP replay state continues to use migration #9 only;
- any future claim that durable step-up storage is required must be separately justified and separately authorized.

This schema classification does not alter the broader Technical Preview `NO_SCHEMA_CHANGE` boundary.

## 12. Security invariants

Any future Sprint 31 source candidate must prove at minimum:

- password-only step-up is rejected;
- TOTP-only step-up is rejected;
- stale login/session facts cannot be converted into fresh step-up authority;
- client-supplied tenant/identity/context cannot redirect reauthentication;
- unconfirmed/absent TOTP factor cannot satisfy step-up;
- replay of an already-consumed TOTP time step fails;
- concurrent same-step TOTP use has at most one winner;
- step-up evidence from another tenant, identity, organization, outlet, device, or operation scope is rejected;
- evidence older than 300 seconds is rejected;
- policy mutation without fresh step-up is rejected when the feature is armed;
- policy mutation continues to use ordinary Sprint 30 behavior when the feature is disabled;
- role/permission/protected-control authorization remains deny-by-default and durably re-verified;
- generic failure and anti-enumeration behavior is preserved;
- secrets/codes/passwords are absent from logs, responses, exception text, and tracked fixtures;
- Preview and Production routes remain unaffected;
- updater remains disabled/unwired.

## 13. Preservation requirements

A future Sprint 31 source candidate must preserve the executable regression chain through Sprint 30, including at minimum:

- tenant isolation;
- identity/organizational context;
- Sprint 21 role/permission policy;
- Sprint 22 policy administration;
- Sprint 23 initial tenant administration;
- Sprint 24 protected-control administrator lifecycle;
- Sprint 25 policy-administration delivery;
- Sprint 26 credential verification;
- Sprint 27 first-party session establishment;
- Sprint 28 initial password enrollment;
- Sprint 29 first-control-principal bootstrap credential foundation;
- Sprint 30 privileged TOTP MFA foundation;
- Governance Required Checks;
- PHP Foundation Regression;
- M7.1 Application Regression;
- Technical Preview / Production / updater separation.

Historical workflows must not be weakened with wildcard acceptance or broad bypasses merely to admit Sprint 31.

## 14. Required source-envelope gate

Before any Sprint 31 application/source mutation, a separate documentation-only **Sprint 31 source-envelope gate** must be published from the then-current canonical `main`.

That gate must:

1. perform fresh Minimal Delta Verification;
2. enumerate every exact changed path;
3. publish a sorted-path SHA-256 fingerprint;
4. freeze the exact feature/config keys and Local/Test/CI runtime behavior;
5. freeze the exact step-up route/controller/application/infrastructure boundaries;
6. freeze the exact session-evidence representation and 300-second freshness calculation;
7. freeze reuse of existing password verification and Sprint 30 TOTP replay-safe verification;
8. freeze the exact policy-administration enforcement point;
9. freeze the exact test and workflow preservation envelope;
10. explicitly prove **NO_SCHEMA_CHANGE** and no migration #10;
11. keep Technical Preview, Production, updater, recovery, factor lifecycle, federation, passkey, and API-token scope excluded; and
12. create no merge authority for its later source PR.

## 15. Explicit non-authority

This entry gate does **not** authorize:

- Sprint 31 application/source implementation;
- source-envelope mutation beyond a future documentation-only gate;
- migration #10 or any schema change;
- password change/reset/recovery/rotation/revocation;
- MFA recovery, recovery codes, TOTP replacement/reset/deletion, or multiple factors;
- WebAuthn/passkeys;
- federation/OIDC/SAML;
- Android/API-token authentication;
- support impersonation or break-glass implementation;
- updater/release activation;
- Technical Preview authentication/MFA/step-up activation;
- Production authentication/MFA/step-up activation;
- deployment, Release, Phase 0 Exit, Sprint 14, or Production authority.

**JRN-003 remains UNRESOLVED.**

## 16. Publication result if this gate is later authorized and merged

Publication of this document would mean only:

**Sprint 31 Privileged Reauthentication / Step-Up Session Freshness is SELECTED and its bounded entry semantics are frozen for source-envelope design.**

It would not mean source implementation is authorized.

The next action after publication would be a fresh, documentation-only Sprint 31 source-envelope gate.
