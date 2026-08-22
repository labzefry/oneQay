# Sprint 37 — First-Party All-Session Termination (Tenant-Scoped Revoke-All) — Schema / Source Envelope Gate

Attribution: Lab | zefry

## 1. Governed purpose

This gate follows the published Sprint37 entry gate and freezes the exact schema decision plus the only changed-file envelope that may be used by a later Sprint37 source implementation.

Selected concern:

> **First-Party All-Session Termination (Tenant-Scoped Revoke-All) Foundation**

Canonical base at gate preparation:

- `main`: `eb0d32941dfacfa427cc8b2ae575531c89baf33c`
- tree: `1b9e9a50863a9ee62536fabe803f2538d8ef3b64`
- parent: `dc7519fcef56b0884782be116764f48a2c82772f`
- canonical commit signature: verified / valid
- entry gate publication: PR #239
- schema/source-gate preservation predecessor: PR #240

This document is a planning and authority boundary only. It does not implement Sprint37 runtime behavior.

## 2. Inherited frozen security semantics

The later source implementation must preserve the published Sprint37 entry-gate decision without reinterpretation:

1. Revoke all active durable first-party logical session authorities owned by the exact current tenant + identity pair.
2. Include the current logical first-party session authority.
3. Derive tenant, identity, actor authority, and ownership only from server-side authenticated session context.
4. Accept no caller-supplied tenant, identity, authority ID, public handle, owner selector, or target list as authority.
5. Make revocation durable before reporting successful terminal session state.
6. After durable revoke-all succeeds, invalidate the current Laravel session and regenerate its CSRF token.
7. Repeated, replayed, and concurrent execution must be monotonic and must not resurrect revoked authority.
8. Other tenant and other identity authorities remain untouched.
9. Protected privileged identities reuse the existing `session_control` step-up scope with exactly 300 seconds freshness.
10. Ordinary identities must not receive an invented privileged MFA requirement.
11. Existing inventory, revoke-one, revoke-others, and canonical current-session logout behavior remain preserved.

## 3. Schema determination

Sprint37 schema classification is frozen as:

> **NO_SCHEMA_CHANGE**

Migration #13 already provides the durable first-party session authority table and audit table required by this concern. The existing audit `event_type` storage can represent the bounded `all_sessions_revoked` event without a new column or entity.

Therefore:

- migration #14 is **NOT REQUIRED**;
- migration #14 is **NOT SELECTED**;
- migration #14 is **NOT AUTHORIZED**;
- migrations #1 through #13 are immutable for Sprint37;
- no table, column, index, foreign key, enum, trigger, or schema rewrite belongs to the Sprint37 source envelope.

Any later discovery that truly requires schema mutation invalidates this source envelope and requires a new governed schema decision before source work continues.

## 4. Frozen HTTP contract

The later source implementation may add exactly the previously frozen endpoint:

- method: `POST`
- path: `/auth/sessions/revoke-all`
- route name: `auth.sessions.revoke-all`

Payload policy remains closed:

- framework CSRF input may be present;
- tenant, identity, authority, handle, count, filter, selector, device, organization, outlet, or arbitrary scope input must not be accepted as authority.

Middleware direction remains:

- active first-party logical session authority required;
- existing session-control mutation context required;
- existing session-control throttling direction retained;
- disabled feature state fails closed;
- non-Local/Test/CI runtime state fails closed;
- persistence/runtime preconditions remain fail-closed.

## 5. Frozen application behavior

### 5.1 Repository contract

The first-party session authority repository may be extended with one bounded revoke-all operation for an exact server-derived tenant + identity owner.

The operation must:

- target only active, unrevoked logical session authorities for the exact tenant + identity pair;
- include the current actor authority in the affected owner set;
- converge safely under concurrent execution;
- return a bounded server-derived result such as affected-row count without exposing internal authority identifiers;
- write the bounded audit summary event `all_sessions_revoked` within the same durable transactional boundary;
- remain secret-free.

### 5.2 Application service

The application service may expose one revoke-all use case that:

- first proves the current logical authority is active under existing credential/factor epoch rules;
- derives tenant, identity, current authority, timestamp, and correlation context from existing trusted inputs;
- delegates durable all-session revocation to the repository;
- does not mutate credential epoch or factor epoch;
- does not create an alternate authentication authority.

### 5.3 HTTP delivery

The session-control controller may expose one `revokeAll` action that:

- applies the existing closed-payload rule;
- obtains tenant, identity, current logical authority, and correlation ID from server-owned session/request state only;
- invokes the bounded application use case;
- only after durable success invalidates the current Laravel session and regenerates the CSRF token;
- returns a generic bounded response without internal authority identifiers or secrets;
- preserves existing generic-denial behavior for rejected requests.

## 6. Audit contract

The only new bounded audit event selected for Sprint37 is:

`all_sessions_revoked`

The existing first-party session audit table remains authoritative.

The audit record may contain server-derived tenant/identity ownership, actor authority context, a bounded target/summary authority context where required by the existing schema, correlation ID, and occurrence time.

It must not contain passwords, password hashes, TOTP secrets, recovery codes, session secrets, cookies, CSRF tokens, bearer tokens, or other authentication secrets.

## 7. Exact Sprint37 source implementation envelope

A later Sprint37 source PR is frozen to exactly these eleven paths:

1. `.github/workflows/sprint35-privileged-totp-recovery-regression.yml`
2. `.github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml`
3. `.github/workflows/sprint37-first-party-all-session-termination-regression.yml`
4. `apps/web/app/Application/Identity/FirstPartySessionAuthorityRepository.php`
5. `apps/web/app/Application/Identity/FirstPartySessionAuthorityService.php`
6. `apps/web/app/Delivery/Http/Identity/FirstPartySessionControlController.php`
7. `apps/web/app/Infrastructure/Identity/LaravelFirstPartySessionAuthorityRepository.php`
8. `apps/web/routes/web.php`
9. `apps/web/tests/first-party-session-all-session-termination.php`
10. `apps/web/tests/first-party-session-inventory-revocation.php`
11. `docs/FIRST_PARTY_ALL_SESSION_TERMINATION_FOUNDATION.md`

Sorted-path SHA-256 of the newline-terminated sorted source changed-file list:

`a221779e05b1e8ab220610b5068be5d1eb01bc08b516b338ba8f22373e7d89d0`

No other path belongs to the authorized source envelope.

## 8. Role of every authorized source path

### 8.1 Historical preservation workflows

`.github/workflows/sprint35-privileged-totp-recovery-regression.yml`

May change only as required to recognize the exact eleven-path Sprint37 source successor shape while retaining historical Sprint35 executable preservation, including isolation of migration #13 from the historical Sprint35 executable horizon. Unknown successor shapes remain fail-closed.

`.github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml`

May change only as required to recognize the exact eleven-path Sprint37 source successor shape while continuing to execute Sprint36 preservation against the governed successor. Unknown successor shapes remain fail-closed.

Neither historical workflow may be disabled, broadly relaxed, converted to permissive path matching, or made migration-blind.

### 8.2 Sprint37 regression workflow

`.github/workflows/sprint37-first-party-all-session-termination-regression.yml`

Must freeze the exact eleven-path source envelope, enforce migrations #1 through #13 immutability and absence of migration #14, run the dedicated Sprint37 regression, preserve full application regression, and preserve Local/Test/CI plus disabled-by-default activation boundaries.

### 8.3 Repository and service surfaces

`apps/web/app/Application/Identity/FirstPartySessionAuthorityRepository.php`

May add only the bounded repository contract needed for exact-owner revoke-all.

`apps/web/app/Application/Identity/FirstPartySessionAuthorityService.php`

May add only the bounded application use case needed to assert current authority and perform exact-owner revoke-all.

`apps/web/app/Infrastructure/Identity/LaravelFirstPartySessionAuthorityRepository.php`

May add only the durable exact-owner bulk revocation plus the `all_sessions_revoked` audit event using migration #13 storage. Existing event validation remains closed except for this one governed event addition.

### 8.4 Delivery and route surfaces

`apps/web/app/Delivery/Http/Identity/FirstPartySessionControlController.php`

May add only the closed-payload revoke-all delivery action and current Laravel session invalidation/CSRF regeneration after durable success.

`apps/web/routes/web.php`

May add only `POST /auth/sessions/revoke-all` named `auth.sessions.revoke-all` under the existing session-control feature/runtime boundary and existing mutation middleware/throttling direction.

### 8.5 Regression surfaces

`apps/web/tests/first-party-session-all-session-termination.php`

Must prove the complete Sprint37 security contract, including exact tenant + identity ownership, inclusion of current authority, local framework-session termination, privileged step-up behavior, ordinary-identity behavior, replay/concurrency monotonicity, fail-closed feature/runtime state, schema immutability, and secret-free audit behavior.

`apps/web/tests/first-party-session-inventory-revocation.php`

May evolve only where the published Sprint37 addition makes the historical assertion intentionally obsolete: the prior explicit absence of `auth.sessions.revoke-all` and the prior closed audit-event allowlist. All other Sprint36 inventory, revoke-one, revoke-others, current logout, ownership, epoch, runtime, persistence, and migration assertions must remain preserved.

### 8.6 Foundation documentation

`docs/FIRST_PARTY_ALL_SESSION_TERMINATION_FOUNDATION.md`

Must document the implemented Sprint37 security semantics, Local/Test/CI boundary, reused feature arm, NO_SCHEMA_CHANGE result, migration immutability, test evidence, and explicit non-authority for Preview/Production/updater/deployment/release.

## 9. Required Sprint37 source regression outcomes

The later source implementation must prove at minimum:

- exact current tenant + identity owner is derived server-side;
- every active owned logical first-party session authority is revoked;
- current logical authority is included;
- another tenant's authorities are untouched;
- another identity's authorities are untouched;
- caller selectors cannot influence ownership or target set;
- durable revocation occurs before local framework-session terminal handling;
- current Laravel session is invalidated after durable success;
- CSRF token is regenerated after durable success;
- repeated/replayed requests do not resurrect authority;
- concurrent revoke-all converges monotonically;
- protected privileged identities require fresh `session_control` step-up within exactly 300 seconds;
- ordinary identities are not assigned an invented privileged MFA requirement;
- disabled session-control feature fails closed;
- disallowed runtime fails closed;
- persistence/runtime failures remain fail-closed;
- credential and factor epochs are not mutated;
- audit event is exactly the bounded `all_sessions_revoked` addition and remains secret-free;
- inventory, revoke-one, revoke-others, and canonical logout remain preserved;
- migrations #1 through #13 remain unchanged;
- migration #14 does not exist for Sprint37;
- existing full application regression remains green.

## 10. Runtime and activation boundary

Sprint37 source implementation must reuse:

`ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false`

with canonical configuration:

`oneqay.session_control.enabled`

No new feature arm is authorized.

Execution remains limited to Local / Test / CI governed runtime until later explicit authority.

Technical Preview remains `NO_SCHEMA_CHANGE` and receives no activation authority from this gate.

Production remains `NO-GO / NOT AUTHORIZED`.

Updater remains `DISABLED / UNWIRED`.

Deployment and release remain not authorized.

## 11. Explicit exclusions from the source envelope

The later Sprint37 source stage does not include:

- migration #14 or any schema mutation;
- configuration-file changes or new feature flags;
- middleware implementation changes;
- provider/bootstrap changes;
- credential or factor epoch mutation;
- cross-tenant global identity sign-out;
- administrator revocation of another identity;
- account suspension/disablement;
- trusted-device state;
- risk scoring, IP reputation, or browser fingerprint authority;
- API/mobile token lifecycle;
- WebAuthn/passkeys;
- federation/SSO;
- support impersonation or break-glass administration;
- Technical Preview activation;
- Production activation;
- updater wiring;
- deployment;
- release.

## 12. Changed-file envelope of this gate publication

This schema/source-envelope gate itself is documentation-only and changes exactly one path:

`docs/SPRINT_37_FIRST_PARTY_ALL_SESSION_TERMINATION_SCHEMA_SOURCE_ENVELOPE_GATE.md`

Sorted-path SHA-256 of the newline-terminated sorted gate changed-file list:

`872d2c9cc2e535bfc2882ad64927cc9873fe001af4ece3bb75308e66d1008b32`

No application source, test, workflow, migration, canonical-state document, roadmap, manifest, Preview artifact, updater artifact, deployment artifact, or release artifact belongs to this gate publication.

## 13. Non-authority statement

Publishing this gate freezes the schema decision and the later exact eleven-path source envelope only.

It does **not** itself authorize or perform:

- Sprint37 application-source mutation;
- route/controller/service/repository mutation;
- test mutation;
- workflow mutation beyond the already-published predecessor PR #240;
- schema mutation;
- migration #14;
- Technical Preview activation;
- Production activation;
- updater wiring;
- deployment;
- release.

## 14. Next governed stage after publication

After this gate is published, the next bounded stage is the **Sprint37 source implementation** using exactly the eleven-path envelope and fingerprint frozen above.

That source stage requires separate Product Owner authority. No source mutation may begin solely from publication of this gate.
