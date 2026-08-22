# Sprint 36 — First-Party Session Inventory & Revocation Foundation — Schema / Source-Envelope Gate

Attribution: **Lab | zefry**

## 1. Status and exact canonical base

This documentation-only schema / source-envelope gate follows the published Sprint36 entry gate.

Exact canonical baseline:

- canonical `main`: `66074cb8a1bddd354d341f172452df2294c6117c`;
- canonical tree: `88c3e15b472037e8c09aa75203a11307cba7a23e`;
- selected concern: **First-Party Session Inventory & Revocation Foundation**;
- schema/source-envelope gate preparation: **AUTHORIZED** by the Product Owner on the exact canonical base above;
- Sprint36 source implementation: **NOT AUTHORIZED by this gate**;
- migration #13 creation/execution: **NOT AUTHORIZED by this gate**;
- migrations #1–#12: **immutable published source**;
- Technical Preview: **`NO_SCHEMA_CHANGE`**;
- Production: **`NO-GO / NOT AUTHORIZED`**;
- updater: **`DISABLED / UNWIRED`**;
- deployment and release: **NOT AUTHORIZED**.

This gate selects and freezes the later implementation contract. It does not itself create schema, application source, workflow source, runtime activation, deployment, or release changes.

## 2. Repository gap and design decision

The canonical first-party login flow establishes tenant, identity, organization, optional outlet/device, credential-epoch evidence, privileged MFA evidence when required, and Laravel session state. Canonical logout invalidates only the current Laravel session. There is no durable server-owned registry that can safely enumerate or revoke another active first-party session belonging to the same identity.

The existing Laravel session store is a framework concern and must not become the oneQay ownership authority. Raw framework session IDs must not be exposed or accepted as Sprint36 public handles.

Sprint34 password `credential_epoch` and Sprint35 TOTP `factor_epoch` remain separate freshness authorities. They are necessary inputs to session validity but are not a replacement for a durable first-party session authority.

Therefore Sprint36 selects a dedicated durable oneQay session registry and secret-free lifecycle audit.

## 3. Schema decision — migration #13 SELECTED for later implementation

The later Sprint36 source implementation is authorized to create exactly one additive forward-only migration only after separate Product Owner source authority is granted:

`apps/web/database/migrations/0000_00_00_000013_create_first_party_session_authority.php`

Migration #13 is **SELECTED by this gate** but is not created or executed by this documentation-only PR.

Migration #13 must create exactly these two durable authority tables:

1. `oneqay_identity_first_party_sessions`;
2. `oneqay_identity_first_party_session_audit`.

Migrations #1–#12 must remain byte-identical. `down()` for migration #13 must throw the repository-standard `LogicException` rollback prohibition.

## 4. Exact first-party session registry schema

`oneqay_identity_first_party_sessions` is the durable server-owned authority for an inventoried full first-party session.

The table must contain these columns and semantics:

- `tenant_id`: string length 64, required;
- `authority_id`: char length 32, required, server-generated internal authority identifier;
- `public_handle`: string length 43, required, opaque high-entropy browser-visible selector generated from 32 random bytes using unpadded base64url encoding;
- `identity_id`: string length 96, required;
- `organization_id`: string length 64, required;
- `outlet_id`: string length 64, nullable;
- `device_id`: string length 64, nullable;
- `credential_epoch`: unsigned 64-bit integer, required;
- `factor_epoch`: unsigned 64-bit integer, nullable and present only when privileged TOTP evidence is required for that session authority;
- `issued_at_unix`: unsigned 64-bit integer, required;
- `last_seen_at_unix`: unsigned 64-bit integer, required;
- `expires_at_unix`: unsigned 64-bit integer, required;
- `revoked_at_unix`: unsigned 64-bit integer, nullable and monotonic from `NULL` to one revocation timestamp only.

The table must use:

- primary key `['tenant_id', 'authority_id']`;
- globally unique `public_handle`;
- an ownership/activity index covering `tenant_id`, `identity_id`, `revoked_at_unix`;
- an expiry lookup index covering `tenant_id`, `identity_id`, `expires_at_unix`;
- a restrictive composite foreign key from `tenant_id + identity_id` to `oneqay_identities`;
- restrictive tenant-scoped context foreign keys where the existing canonical context graph supports them without weakening nullable outlet/device semantics.

`authority_id` is internal and must never be accepted from route payload or returned in inventory output. `public_handle` is a selector, not authentication authority: possession of it alone grants no access and every operation remains authenticated and server ownership-bound.

No raw Laravel session ID, session cookie, CSRF token, password material, TOTP secret, recovery material, IP history, browser fingerprint, or user-agent fingerprint is stored in this registry.

No general `revocation_version` column is selected. Revocation authority is monotonic `revoked_at_unix`, and concurrent writes must use conditional affected-row semantics.

## 5. Exact secret-free audit schema

`oneqay_identity_first_party_session_audit` must contain:

- `tenant_id`: string length 64, required;
- `audit_id`: char length 32, required;
- `identity_id`: string length 96, required;
- `actor_authority_id`: char length 32, nullable for initial issuance and required for authenticated revocation actions;
- `target_authority_id`: char length 32, required;
- `event_type`: string length 32, required;
- `correlation_id`: string length 128, required;
- `occurred_at_unix`: unsigned 64-bit integer, required.

The table must use:

- primary key `['tenant_id', 'audit_id']`;
- owner/time index `['tenant_id', 'identity_id', 'occurred_at_unix']`;
- target index `['tenant_id', 'target_authority_id']`;
- restrictive composite foreign key from `tenant_id + identity_id` to `oneqay_identities`.

Frozen event types are:

- `session_issued`;
- `session_revoked`;
- `other_sessions_revoked`;
- `session_logout`.

Audit must remain secret-free and must never contain public handles, raw framework session IDs, session cookies, CSRF tokens, password/TOTP/recovery secrets, credential/factor ciphertext, or unrelated tenant data.

## 6. Full-session authority issuance

Only a canonical **full first-party session** may receive a Sprint36 authority.

No authority row may be issued for:

- clean anonymous state;
- pending-MFA state;
- password-recovery restricted state;
- TOTP-recovery restricted state;
- malformed/colliding session state.

For a non-privileged identity, successful first-party password login may establish a full session and atomically issue a session authority capturing the current credential epoch with `factor_epoch = NULL`.

For an identity requiring privileged TOTP, password verification alone must not issue an authority. Authority issuance occurs only after the canonical TOTP challenge succeeds and the full session is established. That authority captures both current password `credential_epoch` and current confirmed TOTP `factor_epoch`.

Tenant, identity, organization, outlet, device, credential epoch, factor epoch, timestamps, authority ID, and public handle are all server-derived. The caller cannot select or override them.

Issuance must append exactly one `session_issued` audit event in the same durable transaction as authority creation.

## 7. Session key and framework-session separation

`FirstPartySessionKeys` must gain exactly one internal full-session key for Sprint36 session authority:

`oneqay.auth.session_authority_id`

The Laravel session stores the internal `authority_id`; it must not store a caller-supplied public handle as ownership authority.

Laravel session rotation and the oneQay logical session authority are distinct concepts. A framework session ID may rotate while the same oneQay authority remains active.

The existing privileged step-up transition currently rotates the Laravel session while preserving full identity context. Sprint36 must preserve the same `authority_id` across that successful step-up rotation; it must not accidentally create a second logical active session merely because Laravel rotated its internal session ID.

A successful privileged TOTP challenge is different: it converts pending-MFA state into a newly established full session and therefore must issue a new Sprint36 authority.

## 8. Lifetime, touch, expiry, and stale-authority semantics

Sprint36 selects a fixed session-authority idle lifetime of exactly **7200 seconds**.

The later source must expose this as a source-fixed config value under `oneqay.session_control.idle_ttl_seconds = 7200`; it is not environment-variable configurable.

At issuance:

- `issued_at_unix = now`;
- `last_seen_at_unix = now`;
- `expires_at_unix = now + 7200`.

An authenticated protected request may extend authority lifetime only after successful request-time validation. To bound database write amplification, durable last-seen/expiry touch may occur at most once per 60 seconds per authority. A skipped touch does not weaken expiry enforcement.

An authority is inactive if any of the following is true:

- `revoked_at_unix` is non-null;
- current time is greater than `expires_at_unix`;
- current password credential epoch differs from captured `credential_epoch`;
- privileged TOTP is currently required and the authority lacks valid MFA/factor evidence;
- current confirmed TOTP factor epoch differs from captured `factor_epoch`;
- tenant/identity ownership is missing or inconsistent;
- the authority ID in the Laravel session is absent, malformed, foreign, or unknown where a Sprint36 full session is required.

Stale credential/factor authorities are treated as inactive even if their registry row has not yet been physically marked revoked. Sprint36 must not increment `credential_epoch` or `factor_epoch` merely because a session is listed or revoked.

## 9. Request-time revocation enforcement

The later source must introduce `EnforceActiveFirstPartySessionAuthorityMiddleware`.

When `oneqay.session_control.enabled` is active, this middleware must fail closed before protected full-session operations unless the current session authority is server-owned, unrevoked, unexpired, tenant/identity-bound, and current against credential/factor freshness.

On invalid authority the middleware must invalidate the local Laravel session, regenerate CSRF, clear request context, and return a generic authentication/session denial without revealing whether the failure came from revocation, expiry, credential epoch, factor epoch, ownership, or missing storage.

This enforcement must cover every Local/Test/CI route in the later envelope that consumes a full authenticated session, including Sprint36 inventory/revocation operations, logout, authenticated password change, privileged reauthentication, full-session recovery-code rotation, privileged TOTP recovery-code rotation, protected enrollment issuance, and policy-administration mutation.

Pending-MFA, anonymous recovery proof, restricted recovery, health, Technical Preview, and updater routes must not be reclassified as full-session routes by Sprint36.

## 10. Feature arm and runtime boundary

The later source must add:

`ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false`

mapped to:

`oneqay.session_control.enabled`

The source default is `false`.

The feature is permitted only when all of the following are true:

- runtime is Local/Test/CI;
- durable persistence is enabled;
- `oneqay.session_control.enabled` is true;
- fixed `oneqay.session_control.idle_ttl_seconds` equals exactly 7200.

Missing, malformed, disabled, or unauthorized runtime state fails closed.

Technical Preview must not receive Sprint36 routes or schema activation. Production remains unauthorized.

## 11. Frozen inventory contract

The later source must expose exactly:

`GET /auth/sessions`

The endpoint requires an active Sprint36 full-session authority and accepts no request body or caller-selected tenant/identity.

It returns only active sessions owned by the exact current tenant + identity. The response item contract is frozen to:

- `handle`: opaque `public_handle`;
- `current`: server-derived boolean;
- `organization_id`;
- nullable `outlet_id`;
- nullable `device_id`;
- `issued_at_unix`;
- `last_seen_at_unix`;
- `expires_at_unix`.

Revoked, expired, stale-credential, stale-factor, foreign-tenant, and foreign-identity authorities are not returned as active inventory items.

No raw session ID, internal authority ID, cookie, CSRF token, IP address/history, browser fingerprint, user-agent fingerprint, password/TOTP/recovery material, role/permission authority, credential epoch, or factor epoch is returned.

Inventory is `Cache-Control: no-store, private`.

## 12. Frozen revocation contract

Sprint36 selects two remote revocation operations and preserves canonical current-session logout.

### 12.1 Revoke one owned non-current session

Exact route:

`DELETE /auth/sessions/{public_handle}`

The route accepts no request body. The path handle must match the exact canonical opaque-handle format.

A session may revoke only an active non-current authority belonging to the exact same tenant + identity. The caller cannot provide tenant, identity, authority ID, credential epoch, factor epoch, or revocation timestamp.

Successful revocation conditionally changes `revoked_at_unix` from null to `now` exactly once and appends exactly one `session_revoked` audit event.

Already-revoked, expired, stale, missing, malformed, foreign-tenant, and foreign-identity targets use enumeration-safe generic behavior and must not disclose whether the guessed handle exists.

The current session is not revocable through this route. An exact owned current handle must fail with a stable current-session semantic directing the client to canonical logout; the operation must not silently revoke every session.

### 12.2 Revoke other owned sessions

Exact route:

`POST /auth/sessions/revoke-others`

The route accepts no request body.

It atomically or equivalently deterministically revokes every other active authority owned by the exact current tenant + identity while preserving the exact current authority. It appends a bounded secret-free `other_sessions_revoked` audit event tied to the actor authority and correlation ID.

Concurrent revoke-one/revoke-others operations must be monotonic and idempotent in effect. No revoked row may become active again through stale replay.

### 12.3 Current-session logout

Canonical `POST /auth/logout` remains the only Sprint36 operation that terminates the exact current full session intentionally.

When a current Sprint36 authority exists, successful logout must conditionally revoke that authority, append exactly one `session_logout` audit event, invalidate the Laravel session, and regenerate CSRF. Logout must remain safe when the local session is already stale or absent.

Sprint36 does **not** introduce `revoke-all`.

## 13. Privileged mutation and step-up contract

Inventory is read-only and does not itself require fresh step-up beyond a valid active full session.

For an identity for which canonical privileged TOTP protection is required, both remote revocation mutations require fresh canonical privileged step-up evidence scoped exactly to:

`session_control`

Freshness remains exactly **300 seconds**, preserving Sprint31 semantics.

The existing policy-administration scope remains `policy_administration` and cannot satisfy `session_control`; conversely `session_control` cannot satisfy policy administration.

The later source may extend `PrivilegedReauthenticationController` with a server-selected session-control reauthentication action exposed as:

`POST /auth/reauthenticate/session-control`

Payload remains the canonical closed privileged reauthentication proof fields:

- `password`;
- `code`.

The caller cannot submit a scope field. The route selects `session_control` server-side.

For non-privileged identities, remote revocation mutations require only the valid active full-session authority; Sprint36 does not invent a new password/TOTP proof requirement for identities that canonical policy does not classify as privileged.

`RequireFirstPartySessionControlMutationContextMiddleware` must derive privilege server-side, validate active ownership, and enforce exact scope/context/freshness when privileged protection applies.

## 14. Credential epoch and factor epoch preservation

Password `credential_epoch` and privileged TOTP `factor_epoch` remain distinct durable authorities.

Sprint36 must capture and compare them, not mutate them.

A password change/reset advancing `credential_epoch` immediately makes every prior authority with an older captured credential epoch inactive at the next enforced request and excludes it from active inventory.

A successful TOTP factor replacement advancing `factor_epoch` immediately makes every prior privileged authority with an older captured factor epoch inactive at the next enforced request and excludes it from active inventory.

No session revocation operation may masquerade as a password or factor mutation.

No missing `factor_epoch` may be tolerated for a session that currently requires confirmed privileged TOTP evidence.

## 15. Concurrency and fail-closed requirements

The future implementation must prove all of the following:

- two concurrent revoke-one calls have at most one durable revocation transition;
- repeated revoke-one is idempotent in effect and enumeration-safe;
- revoke-one racing revoke-others cannot reactivate or preserve the target accidentally;
- revoke-others always preserves the exact current authority;
- a request racing a committed revocation cannot remain authoritative on its next enforcement point;
- stale cached inventory cannot revive authority;
- Laravel session rotation cannot create an unregistered authority or lose valid ownership silently;
- password credential epoch advancement invalidates old authorities;
- TOTP factor epoch advancement invalidates old privileged authorities;
- malformed/negative/future epoch evidence fails closed;
- foreign tenant/identity handles cannot be enumerated through status differences;
- current-session targeting cannot be confused with remote-session revocation;
- no operation trusts caller-selected tenant, identity, authority, epoch, privilege, scope, current flag, or timestamps.

## 16. Frozen later source changed-file envelope

The later Sprint36 source implementation is frozen to exactly these **23 paths**:

1. `.github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml`
2. `apps/web/app/Application/Identity/FirstPartySessionAuthorityClock.php`
3. `apps/web/app/Application/Identity/FirstPartySessionAuthorityRepository.php`
4. `apps/web/app/Application/Identity/FirstPartySessionAuthorityService.php`
5. `apps/web/app/Application/Identity/FirstPartySessionAuthorityViolation.php`
6. `apps/web/app/Application/Identity/FirstPartySessionInventoryItem.php`
7. `apps/web/app/Application/Identity/IssuedFirstPartySessionAuthority.php`
8. `apps/web/app/Delivery/Http/Identity/FirstPartySessionController.php`
9. `apps/web/app/Delivery/Http/Identity/FirstPartySessionControlController.php`
10. `apps/web/app/Delivery/Http/Identity/FirstPartySessionKeys.php`
11. `apps/web/app/Delivery/Http/Identity/PrivilegedReauthenticationController.php`
12. `apps/web/app/Delivery/Http/Identity/PrivilegedTotpMfaController.php`
13. `apps/web/app/Delivery/Http/Middleware/EnforceActiveFirstPartySessionAuthorityMiddleware.php`
14. `apps/web/app/Delivery/Http/Middleware/RequireFirstPartySessionControlMutationContextMiddleware.php`
15. `apps/web/app/Infrastructure/Identity/LaravelFirstPartySessionAuthorityRepository.php`
16. `apps/web/app/Providers/AppServiceProvider.php`
17. `apps/web/bootstrap/app.php`
18. `apps/web/config/oneqay.php`
19. `apps/web/database/migrations/0000_00_00_000013_create_first_party_session_authority.php`
20. `apps/web/routes/web.php`
21. `apps/web/tests/first-party-session-inventory-revocation.php`
22. `apps/web/tests/run.php`
23. `docs/FIRST_PARTY_SESSION_INVENTORY_REVOCATION_FOUNDATION.md`

Frozen sorted-path SHA-256, newline-delimited sorted paths with a trailing newline:

`ea735f8f5ee06d480863f9d1ba7ae58a91642109963c3f340e8453f3205bb7ae`

No file outside this exact envelope may be changed by the later Sprint36 implementation without a new Product Owner-authorized gate amendment.

`apps/web/bootstrap/app.php` is included only to register the bounded Sprint36 middleware aliases required by the delivery contract; it is not authority for unrelated application bootstrap changes.

## 17. Ownership of responsibilities

Application layer owns:

- session-authority issuance orchestration;
- active-authority validation contract;
- inventory/revocation orchestration;
- current/non-current semantics;
- epoch freshness interpretation;
- concurrency-safe domain violations and privacy-safe inventory values.

Infrastructure owns:

- durable registry/audit transactions;
- conditional revocation affected-row enforcement;
- owner-bound lookup by public handle;
- current credential/factor freshness reads needed by session authority;
- bounded touch/update semantics;
- storage failures translated to fail-closed durable/application violations.

Delivery owns:

- Laravel session key management;
- full/pending/recovery-state collision denial;
- current authority derivation;
- middleware enforcement;
- route payload closure;
- generic failure envelopes;
- `no-store` response handling;
- CSRF/session invalidation on logout or authority denial;
- server-selected privileged step-up scope;
- Local/Test/CI route visibility.

The existing canonical password verifier, TOTP engine/service, step-up freshness boundary, tenant context, organizational context, and policy authorities are reused and not replaced.

## 18. Dedicated Sprint36 qualification contract

The future workflow `.github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml` must fail closed unless the exact 23-path source envelope and fingerprint above are preserved.

Executable qualification must prove at minimum:

- migrations #1–#12 byte preservation;
- exact migration #13 additive schema and forward-only rollback prohibition;
- source-default-disabled Sprint36 feature arm;
- Local/Test/CI-only feature availability;
- exact 7200-second authority idle TTL;
- bounded 60-second durable touch frequency;
- no authority issuance for anonymous, pending-MFA, password-recovery, or TOTP-recovery state;
- non-privileged login issues exactly one authority after successful canonical authentication;
- privileged password login does not issue authority before successful TOTP challenge;
- successful privileged TOTP challenge issues exactly one authority with current credential and factor epochs;
- privileged step-up Laravel rotation preserves the same logical authority;
- raw Laravel session IDs are never returned or accepted as public handles;
- opaque public handles are high entropy and unique;
- inventory is exact tenant + identity owned, active-only, current-aware, and privacy-safe;
- cross-tenant and cross-identity inventory isolation;
- foreign/guessed handle probing is enumeration-safe;
- revoke-one cannot revoke the current session and cannot revoke a foreign session;
- revoke-one is monotonic and idempotent in effect;
- revoke-others preserves exactly the current authority;
- canonical logout revokes the current authority and invalidates local session state;
- no `revoke-all` route exists;
- privileged remote revocation requires exact fresh `session_control` step-up;
- `policy_administration` and `session_control` scopes cannot substitute for each other;
- ordinary identities do not receive caller-invented privileged requirements;
- stale/malformed/negative/future credential or factor evidence fails closed;
- password credential-epoch advancement invalidates prior authorities without Sprint36 changing the epoch;
- TOTP factor-epoch advancement invalidates prior privileged authorities without Sprint36 changing the epoch;
- concurrent revocation has at most the intended durable transition and never resurrects authority;
- audit is secret-free and bounded to frozen event types;
- middleware invalidates stale/revoked local sessions and regenerates CSRF;
- pending/recovery flows remain semantically distinct;
- Sprint21–Sprint35 executable regressions remain preserved;
- Technical Preview remains `NO_SCHEMA_CHANGE`;
- Production remains `NO-GO / NOT AUTHORIZED`;
- updater remains `DISABLED / UNWIRED`;
- persistence and selected authentication feature source defaults remain fail-closed.

The preservation chain must continue repository-native M7 and historical Sprint governance required by canonical main.

## 19. Explicit exclusions

Sprint36 does not select or authorize:

- support/admin session impersonation or takeover;
- caller-selected tenant/identity ownership;
- raw framework session-ID exposure;
- IP-history or browser-fingerprint tracking;
- trusted-device bypass;
- `revoke-all`;
- password overwrite/reset expansion;
- TOTP secret mutation or factor replacement expansion;
- custom password comparison or custom TOTP cryptography;
- passkeys/WebAuthn;
- OAuth/OIDC/SAML/federation;
- API/bearer-token authentication;
- email/SMS session controls;
- Technical Preview schema/authentication activation;
- Production schema/authentication activation;
- updater activation;
- deployment;
- release;
- Phase 0 Exit.

## 20. Lifecycle authority boundary

The Product Owner authority for this gate covers only bounded documentation preparation, commit, and Draft PR on the exact single documentation path:

`docs/SPRINT_36_FIRST_PARTY_SESSION_INVENTORY_REVOCATION_SCHEMA_SOURCE_ENVELOPE_GATE.md`

It does **not** authorize:

- Ready transition;
- repository-native merge authorization;
- merge;
- creation or execution of migration #13;
- any mutation inside the frozen 23-path future source envelope;
- mutation of migrations #1–#12;
- Technical Preview/Production activation;
- updater, deployment, or release activity.

If this documentation-only gate is later independently qualified and published under new exact-head Product Owner lifecycle authority, the next bounded lifecycle stage is **Sprint36 source implementation against the exact frozen 23-path envelope**, requiring a separate Product Owner authority against the exact resulting canonical main/tree.
