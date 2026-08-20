# Sprint 34 — Authenticated In-Session Password Change Foundation — Entry Gate

Attribution: **Lab | zefry**

## 1. Product Owner direction and exact canonical base

This documentation-only entry gate is prepared as the next governed lifecycle step after the published post-Sprint33 canonical reconciliation.

Exact canonical baseline at preparation time:

- canonical `main`: `47990ca06e9bf855f0502b697fc065344d9537e5`;
- canonical tree: `d847cc877cf667ec3d5039806191e409ed0c1a36`;
- selected concern: **Authenticated In-Session Password Change Foundation**;
- entry-gate preparation: **AUTHORIZED by the instruction to continue to the next stage**;
- application/source implementation: **NOT AUTHORIZED by this gate**;
- workflow-YAML mutation: **NOT AUTHORIZED by this gate**;
- migration #11: **NOT AUTHORIZED by this gate**;
- Technical Preview: **`NO_SCHEMA_CHANGE`**;
- Production: **`NO-GO / NOT AUTHORIZED`**;
- updater: **`DISABLED / UNWIRED`**.

This gate changes documentation only. It does not authorize source implementation, schema execution, dependency mutation, Preview/Production activation, deployment, release, Ready transition, or merge.

## 2. Why this is the next bounded concern

Sprint 21 through Sprint 33 already publish the prerequisites for a safe authenticated password-change slice:

1. tenant-bound password credentials and first-party credential verification;
2. server-side first-party browser sessions with exact canonical tenant/identity/organization/outlet/device context;
3. initial password enrollment and first-control-principal credential bootstrap;
4. privileged TOTP MFA;
5. privileged reauthentication/step-up freshness;
6. recovery-code rotation and proof;
7. recovery-bound password reset completion;
8. credential-epoch evidence that invalidates pre-reset authenticated sessions after recovery-bound reset.

The canonical post-Sprint33 state explicitly leaves **authenticated in-session password change** separately governed. This gate selects only that concern. It does not select administrative credential overwrite, MFA recovery, factor lifecycle, support bypass, delivery channels, passkeys, federation, API tokens, Preview/Production authentication activation, updater activation, deployment, or release.

## 3. Selected Sprint 34 behavior

The bounded target behavior is:

**an already fully authenticated identity may change only its own existing password credential after server-bound session validation and fresh credential reauthentication; privileged/protected identities must additionally satisfy the published privileged TOTP reauthentication requirement; successful mutation revokes pre-change recovery capabilities, advances durable credential authority, terminates the current session, and requires a fresh normal login.**

This is not enrollment, bootstrap, forgot-password recovery, administrative password setting, impersonation, bulk credential rotation, password reset by support, or MFA-factor lifecycle management.

## 4. Full-session trust boundary

A later Sprint34 implementation may operate only from a valid normal/full first-party session.

The server must derive and validate the canonical Sprint27 context keys:

- `oneqay.auth.identity_id`;
- `oneqay.auth.tenant_id`;
- `oneqay.auth.organization_id`;
- `oneqay.auth.outlet_id`;
- `oneqay.auth.device_id`.

The separate credential-epoch evidence `oneqay.auth.credential_epoch` must be validated against durable current authority before password-change reauthentication or mutation proceeds.

The request must never select or override tenant, identity, organization, outlet, device, role, permission, privilege state, MFA requirement, credential epoch, or credential-row selector.

The password-change path must fail closed when any of the following applies:

- one or more canonical full-session context keys are missing or malformed;
- credential-epoch evidence is stale, malformed, negative, future, or otherwise invalid;
- a restricted pending-MFA session is present instead of a normal full session;
- a restricted recovery session is present or collides with normal-session state;
- durable persistence is disabled;
- runtime is outside Local/Test/CI;
- the exact tenant or identity cannot be revalidated;
- the exact existing credential row cannot be found;
- current-password reauthentication fails;
- the server determines privileged TOTP reauthentication is required and that proof fails.

## 5. Request and secret-handling contract

The later source design may accept only the minimum credential-change inputs required for reauthentication and replacement.

The intended business inputs are:

- `current_password` — sensitive current-password proof;
- `new_password` — sensitive replacement password;
- `totp_code` — sensitive and conditionally required only when the server determines privileged TOTP reauthentication is required.

No client-provided tenant, identity, role, protected-control flag, MFA-required flag, epoch, session scope, recovery code, audit identifier, or privilege selector is permitted.

Both password inputs must be treated as opaque bytes. No trimming or normalization is permitted.

`new_password` must preserve the published bounded password policy:

- minimum **12 bytes**;
- maximum **4096 bytes**;
- hash with PHP `PASSWORD_DEFAULT`;
- no plaintext/reversible durable storage.

`current_password`, `new_password`, password hashes, TOTP codes, TOTP secrets, session identifiers, CSRF tokens, recovery-code secrets, and reusable authentication material must never be written to logs, audit text, response bodies, exception text, or session state.

The replacement password must not silently equal the current credential. A later source-envelope gate must freeze the exact same-password rejection mechanism without introducing password-history scope.

## 6. Reauthentication requirement

A valid existing session by itself is insufficient authority to change a password.

For every eligible identity, the exact current credential must be freshly reverified.

For a protected-control identity or any identity whose canonical security state requires confirmed privileged TOTP, password verification alone must not authorize the change. The later source design must reuse the published TOTP verification primitive and must not implement custom TOTP/HMAC/Base32 cryptography.

A later implementation may either perform current-password + TOTP verification inside the bounded password-change flow or reuse a separately proven reauthentication primitive, but it must not:

- trust an arbitrary caller-selected step-up scope;
- treat old MFA evidence as fresh reauthentication without an exact bounded freshness rule;
- bypass confirmed-TOTP requirements;
- read or decrypt a TOTP secret outside the existing canonical TOTP infrastructure boundary;
- disable, replace, delete, reveal, or reset a factor as a side effect of password change.

The exact route/payload and whether the privileged path uses direct TOTP proof or a dedicated credential-change step-up scope must be frozen by the later source-envelope gate.

## 7. Atomic credential mutation requirement

Password change must be one atomic durable mutation boundary.

At minimum, the later implementation must:

1. derive tenant + identity only from validated server session state;
2. validate the session credential epoch before mutation;
3. lock and revalidate the exact existing password credential row;
4. revalidate identity existence and current protected-control/TOTP requirements;
5. verify current-password authority against the exact current credential state without a stale-read race;
6. verify privileged TOTP reauthentication when server policy requires it;
7. reject a replacement that is equivalent to the current password;
8. validate the new password's 12–4096-byte opaque-input boundary;
9. hash the replacement with `PASSWORD_DEFAULT`;
10. update exactly one existing credential row;
11. advance durable credential authority exactly once;
12. revoke every still-unused/unrevoked recovery code for that same tenant + identity;
13. emit only secret-free security/audit evidence selected by the later schema/source design.

Credential mutation remains **update-only**. Missing credential state fails closed. No credential insert, upsert, delete, truncate, row recreation, enrollment fallback, bootstrap fallback, or administrative-set fallback is permitted.

Concurrent password-change attempts must serialize on durable authority. At most one operation may win against a given starting credential/epoch state.

## 8. Recovery-code revocation boundary

A successful authenticated password change must invalidate recovery capabilities created before that credential mutation.

Therefore all remaining unused and unrevoked Sprint32 recovery codes for the same exact tenant + identity must be revoked atomically with the password mutation.

Password change must not:

- consume a recovery code as proof;
- create a recovery proof session;
- manufacture `proof_succeeded` or `password_reset_completed` evidence;
- extend the recovery-session TTL;
- create new recovery codes automatically.

A fresh recovery-code rotation, if desired, remains a separately invoked already-published authenticated operation after the user performs a fresh login.

## 9. Session disposition after successful change

Successful password change must not preserve the current browser session as a shortcut around credential revocation.

After the durable mutation succeeds:

- the current full session must be invalidated;
- the CSRF token must be regenerated;
- no canonical full-session keys may be re-established by the password-change response;
- no MFA, step-up, recovery, or credential-epoch evidence may be synthesized as automatic reauthentication;
- the user must perform a fresh normal first-party login with the replacement password.

Every other pre-change authenticated session for the same identity must fail closed through durable credential-authority advancement.

## 10. Credential-epoch schema decision — BLOCKING / NOT YET AUTHORIZED

Fresh canonical inspection shows that the current Sprint33 credential epoch is derived from the count of `password_reset_completed` events in `oneqay_identity_recovery_audit`.

That mechanism is intentionally recovery-specific. Reusing or fabricating `password_reset_completed` evidence for a normal authenticated password change would be semantically incorrect and is forbidden by this gate.

The existing `oneqay_identity_password_credentials` table contains only:

- `tenant_id`;
- `identity_id`;
- `password_hash`.

It has no generic credential version/epoch column.

Therefore a later source implementation must not begin until one of these is proven and separately authorized:

### Option A — preferred generic durable credential epoch

A separately governed migration #11 may add a generic monotonic credential-authority primitive, with exact migration semantics, historical reset-epoch backfill, concurrency behavior, and rollback prohibition frozen before source mutation.

A likely minimal candidate is a generic credential epoch/version attached to the exact password-credential authority, with migration logic that preserves the effective Sprint33 reset-derived epoch for existing Local/Test/CI data.

### Option B — proven no-schema mechanism

A later source-envelope/schema gate may instead prove a no-schema mechanism that:

- is generic to all password credential mutations rather than falsely recording recovery events;
- preserves existing Sprint33 session invalidation semantics;
- remains monotonic and concurrency-safe;
- does not expose or persist credential hashes or reusable secrets as session evidence;
- does not silently invalidate or reinterpret canonical session state in an unsafe way.

If neither option can be proven, Sprint34 implementation must stop. Security semantics must not be weakened merely to avoid migration #11.

**Migration #11 remains NOT AUTHORIZED by this entry gate.**

## 11. Schema, dependency, and runtime boundary

This entry gate itself is **documentation-only**.

It authorizes no schema change.

For the current published system:

- migrations remain exactly #1 through #10;
- migrations #1–#10 remain immutable;
- migration #11 remains **NOT AUTHORIZED**;
- no Composer/npm dependency or lockfile change is selected;
- no `.env` or `.env.*` mutation is selected;
- Technical Preview remains **`NO_SCHEMA_CHANGE`**;
- Production remains **`NO-GO / NOT AUTHORIZED`**;
- updater remains **`DISABLED / UNWIRED`**;
- durable persistence remains source-default-disabled;
- any eventual Sprint34 runtime delivery remains bounded to Local/Test/CI until separately authorized.

## 12. Intended delivery target for later source design

The intended bounded Web delivery target is:

`POST /auth/password/change`

The later source-envelope gate must freeze the exact route, middleware, feature-arm decision, request field set, throttling, correlation-ID behavior, generic failure envelope, cache-control requirements, and session disposition.

The route must remain unavailable in Technical Preview and Production unless separate future authority explicitly changes those lifecycle boundaries.

No UI page, email notification, SMS notification, support flow, administrative endpoint, API/bearer endpoint, or mobile-native credential-change flow is selected by this gate.

## 13. Mandatory preservation and qualification proof

Before eventual Sprint34 source can qualify, a separately published source-envelope/schema gate must require executable proof for at least:

- exact valid full-session context requirement;
- stale/malformed/missing credential-epoch rejection;
- pending-MFA and recovery-session collision rejection;
- current-password reauthentication success/failure;
- current-password TOCTOU/concurrent-change protection;
- protected-control and confirmed-TOTP reauthentication enforcement;
- TOTP secret non-disclosure and no factor mutation;
- 12-byte and 4096-byte new-password success boundaries;
- below-minimum and above-maximum rejection;
- no trim/no normalization behavior;
- same-password rejection;
- update-only existing credential mutation;
- missing credential fail-closed;
- atomic durable credential-authority advancement;
- concurrent change with at most one winner from the same starting authority;
- revocation of all pre-change unused recovery codes;
- secret-free security/audit evidence;
- current-session invalidation and CSRF regeneration;
- no automatic/full login after password change;
- fresh normal login required;
- all other pre-change sessions invalidated by durable credential authority;
- Sprint21–33 executable preservation;
- migrations #1–#10 preservation;
- exact migration #11 behavior if separately authorized;
- Technical Preview remains `NO_SCHEMA_CHANGE`;
- Production remains `NO-GO / NOT AUTHORIZED`;
- updater remains `DISABLED / UNWIRED`;
- persistence and relevant feature defaults remain fail closed.

## 14. Explicit non-authority

This entry gate does **not** authorize:

- Sprint34 application/source implementation;
- a Sprint34 source-envelope fingerprint;
- workflow YAML mutation;
- migration #11 creation, modification, execution, or publication;
- modification of migrations #1–#10;
- authenticated password change in Preview or Production;
- administrative password overwrite/set;
- initial enrollment or bootstrap expansion;
- forgot-password recovery or recovery bypass;
- protected-control bypass;
- support/operator password setting;
- automatic login after password change;
- TOTP recovery, disablement, replacement, deletion, reset, or secret disclosure;
- passkeys/WebAuthn;
- OAuth/OIDC/SAML/federation;
- API/bearer-token authentication;
- email/SMS credential delivery;
- updater activation/wiring;
- deployment or release;
- Phase 0 Exit;
- Ready transition for this entry-gate PR;
- merge authority for this entry-gate PR.

## 15. Next governed lifecycle step

This file is the **Sprint34 entry-gate preparation artifact only**.

If and only if this documentation-only gate is technically qualified and later published under a new exact-head Product Owner Ready/Merge authorization, the next bounded lifecycle step is a separate documentation-only **Sprint34 schema/source-envelope gate**.

That later gate must:

1. resolve the generic credential-epoch mechanism;
2. obtain explicit Product Owner authority before migration #11 is selected, if migration #11 is required;
3. freeze exact route and request semantics;
4. freeze exact Application/Infrastructure/Delivery responsibilities;
5. freeze exact changed-file envelope and sorted-path fingerprint;
6. freeze dedicated Sprint34 regression and historical preservation-chain changes;
7. preserve Technical Preview, Production, updater, deployment, and release boundaries;
8. explicitly state whether source implementation authority is granted only after that gate is separately published.

PR #211–#214 and their authorities are historical/consumed and grant no standing Sprint34 source or merge authority.
