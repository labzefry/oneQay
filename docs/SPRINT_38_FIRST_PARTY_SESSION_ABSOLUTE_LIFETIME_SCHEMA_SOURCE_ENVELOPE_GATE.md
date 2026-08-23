# Sprint 38 — First-Party Session Absolute Lifetime — Schema / Source Envelope Gate

Attribution: Lab | zefry

## 1. Governed purpose

This gate follows the published Sprint38 entry gate and freezes the exact schema decision plus the only changed-file envelope that may be used by a later Sprint38 source implementation.

Selected concern:

> **First-Party Session Absolute Lifetime Foundation**

Canonical base at gate preparation:

- `main`: `a5141c6af512790dde36586095fc3065ddab5c5e`
- tree: `98af398c0841c17c899a0c7851e4b4408c5f0d58`
- parent: `d98c7cc2ab42533b94675d4e315da6207922653f`
- canonical commit signature: verified / valid
- entry gate publication: PR #249
- schema/source-gate preservation predecessor: PR #250

This document is a planning and authority boundary only. It does not implement Sprint38 runtime behavior.

## 2. Inherited frozen lifetime semantics

The later source implementation must preserve the published Sprint38 entry-gate decision without reinterpretation:

1. Existing sliding idle lifetime remains exactly **7200 seconds**.
2. Absolute lifetime is exactly **43200 seconds (12 hours)** from server-owned `issued_at_unix`.
3. `absolute_deadline = issued_at_unix + 43200`.
4. Initial effective expiry is `min(issued_at_unix + 7200, absolute_deadline)`.
5. A valid later touch may set expiry only to `min(now + 7200, absolute_deadline)`.
6. `issued_at_unix` remains immutable for the logical authority lifetime.
7. Caller-supplied timestamps never become authority for lifetime calculation.
8. Request activity, framework-session rotation, inventory, revocation operations, or privileged step-up must never extend a logical authority beyond the fixed absolute deadline.
9. Existing tenant, identity, organization, outlet, device, credential-epoch, factor-epoch, revocation, idle-expiry, runtime, and persistence checks remain authoritative.
10. Existing expiry equality convention must remain unchanged; Sprint38 adds an absolute-age boundary without silently redefining the already-published idle-expiry boundary.
11. An expired or revoked authority must not be reactivated or resurrected by replay, concurrency, touch, MFA evidence, or step-up evidence.
12. An expired durable row may remain stored but must not be treated as an active session authority or active inventory item.

## 3. Schema determination

Sprint38 schema classification is frozen as:

> **NO_SCHEMA_CHANGE**

Migration #13 already stores the server-owned issuance timestamp in `issued_at_unix` and the durable effective expiry in `expires_at_unix`. The selected absolute-lifetime concern requires no new persistent entity, field, index, constraint, audit structure, or migration.

Therefore:

- migration #14 is **NOT REQUIRED**;
- migration #14 is **NOT SELECTED**;
- migration #14 is **NOT AUTHORIZED**;
- migrations #1 through #13 are immutable for Sprint38;
- no table, column, index, foreign key, enum, trigger, or schema rewrite belongs to the Sprint38 source envelope.

Any later discovery that truly requires schema mutation invalidates this source envelope and requires a new governed schema decision before source work continues.

## 4. No new HTTP or audit contract

Sprint38 adds no HTTP route, route name, request payload, controller action, caller selector, or public session identifier.

Existing session-control routes remain unchanged, including inventory, revoke-one, revoke-others, revoke-all, privileged session-control reauthentication, and canonical logout.

Sprint38 also adds no new first-party session audit event. Existing issuance, revocation, revoke-others, revoke-all, and current logout audit semantics remain unchanged.

Absolute expiration is request-time authority validity behavior, not a newly selected destructive mutation or audit-event family.

## 5. Frozen implementation behavior

### 5.1 Configuration

The existing `session_control` configuration may add exactly one fixed, source-controlled value:

`absolute_ttl_seconds => 43200`

The value must not be environment-configurable. The existing idle TTL remains exactly 7200 seconds and the existing feature arm remains unchanged.

### 5.2 Provider wiring

The application provider may pass the fixed absolute TTL into `FirstPartySessionAuthorityService` and extend the existing fail-closed session-control configuration check so the governed session-control capability is operational only when:

- feature arm is enabled under the existing rules;
- idle TTL is exactly 7200 seconds; and
- absolute TTL is exactly 43200 seconds.

No new provider, binding family, feature arm, runtime class, or authentication authority is selected.

### 5.3 First-party session authority service

The application service may change only as required to enforce the frozen absolute lifetime.

The service must:

- receive the fixed absolute TTL through governed provider/configuration wiring;
- fail closed if idle TTL is not exactly 7200 or absolute TTL is not exactly 43200;
- preserve server-owned issuance time as the origin of the fixed absolute deadline;
- issue the durable authority with effective expiry capped by the earlier idle and absolute deadlines;
- derive the absolute deadline for an existing authority from the stored `issued_at_unix` record value, never from caller input;
- deny authority that has exceeded the fixed absolute deadline in addition to existing revoked, idle-expiry, ownership, context, epoch, runtime, and persistence checks;
- cap any touch expiry at `min(now + 7200, issued_at_unix + 43200)`;
- never allow touch, inventory, step-up, or framework-session activity to renew an authority beyond the same fixed absolute deadline;
- preserve existing equality semantics at the published expiry boundary;
- preserve existing generic fail-closed violations and secret-free behavior.

The service must not introduce a second logical authority, new token, new public handle, new credential epoch, new factor epoch, or new persistence model.

### 5.4 Repository preservation

The existing repository interface and Laravel repository already store and expose the server-owned `issued_at_unix` and accept a service-computed `expiresAtUnix` during touch. They are deliberately excluded from this Sprint38 source envelope.

The later implementation must use the existing repository contract without weakening exact tenant + identity ownership, active-row filtering, revocation monotonicity, persistence fail-closed behavior, or audit-event validation.

## 6. Exact Sprint38 source implementation envelope

A later Sprint38 source PR is frozen to exactly these ten paths:

1. `.github/workflows/sprint35-privileged-totp-recovery-regression.yml`
2. `.github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml`
3. `.github/workflows/sprint37-first-party-all-session-termination-regression.yml`
4. `.github/workflows/sprint38-first-party-session-absolute-lifetime-regression.yml`
5. `apps/web/app/Application/Identity/FirstPartySessionAuthorityService.php`
6. `apps/web/app/Providers/AppServiceProvider.php`
7. `apps/web/config/oneqay.php`
8. `apps/web/tests/first-party-session-absolute-lifetime.php`
9. `apps/web/tests/first-party-session-inventory-revocation.php`
10. `docs/FIRST_PARTY_SESSION_ABSOLUTE_LIFETIME_FOUNDATION.md`

Sorted-path SHA-256 of the newline-terminated sorted source changed-file list:

`411950d5602dc7160668c88e08a3941ebccc8bdc82d20bee77ce4004f039d216`

No other path belongs to the authorized Sprint38 source envelope.

## 7. Role of every authorized source path

### 7.1 Historical preservation workflows

`.github/workflows/sprint35-privileged-totp-recovery-regression.yml`

`.github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml`

`.github/workflows/sprint37-first-party-all-session-termination-regression.yml`

These workflows may change only as required to recognize the exact ten-path Sprint38 source successor fingerprint while retaining their published executable preservation, migration isolation/immutability, and fail-closed unknown-shape behavior.

They must not be disabled, converted to wildcard successor acceptance, made migration-blind, or broadly relaxed.

A separately published preservation predecessor may be required before opening the later exact ten-path source PR. This gate freezes that governance requirement but does not itself perform or authorize the source implementation.

### 7.2 Sprint38 regression workflow

`.github/workflows/sprint38-first-party-session-absolute-lifetime-regression.yml`

Must:

- enforce exactly the ten-path Sprint38 source envelope and its fingerprint;
- enforce migrations #1 through #13 immutability and absence of migration #14;
- validate fixed idle TTL 7200 and absolute TTL 43200;
- run the dedicated Sprint38 absolute-lifetime regression;
- preserve Sprint36 session inventory/revocation regression;
- preserve full application regression;
- preserve Local/Test/CI and disabled-by-default activation boundaries;
- fail closed for unknown source shapes.

### 7.3 Service surface

`apps/web/app/Application/Identity/FirstPartySessionAuthorityService.php`

May change only to add the fixed absolute-TTL constructor/configuration requirement, derive the immutable absolute deadline from stored issuance evidence, enforce it during authority validation, and cap issue/touch expiry according to the frozen formulas.

Existing tenant/identity ownership, organizational context, credential/factor epoch, revocation, idle lifetime, inventory, logout/revocation use cases, and fail-closed behavior must remain preserved.

### 7.4 Provider and configuration surfaces

`apps/web/app/Providers/AppServiceProvider.php`

May change only to wire `oneqay.session_control.absolute_ttl_seconds` into the existing service and require exactly 43200 seconds in the existing session-control operational gate.

`apps/web/config/oneqay.php`

May change only to add fixed, non-environment-configurable `absolute_ttl_seconds => 43200` inside the existing `session_control` block. Existing `enabled` behavior and `idle_ttl_seconds => 7200` remain unchanged.

### 7.5 Regression surfaces

`apps/web/tests/first-party-session-absolute-lifetime.php`

Must prove at minimum:

- issuance uses server-owned time and remains bounded by the 7200-second idle lifetime;
- absolute deadline is exactly original `issued_at_unix + 43200`;
- touch before the hard deadline remains capped at the earlier idle/absolute deadline;
- activity near the hard deadline cannot extend expiry beyond it;
- authority beyond the absolute lifetime is denied even if other owner/context/epoch evidence remains valid;
- existing idle expiry may terminate authority earlier than the absolute maximum;
- replayed or concurrent touch behavior cannot extend an authority past the same fixed hard deadline;
- framework-session rotation and privileged step-up do not reset original issuance age;
- expired durable rows are not returned as active owner inventory;
- exact tenant + identity isolation remains preserved;
- credential and factor epoch enforcement remains preserved;
- revoked authorities remain revoked;
- disabled feature, invalid configuration, disallowed runtime, and persistence failure remain fail-closed;
- migrations #1 through #13 remain unchanged and migration #14 does not exist;
- no secret-bearing data is added to session inventory, audit, response, or diagnostics.

`apps/web/tests/first-party-session-inventory-revocation.php`

May evolve only as required to pass the new fixed absolute-TTL constructor/configuration value and to prove the existing Sprint36 inventory, revoke-one, revoke-others, canonical logout, privileged session-control mutation, ownership, epoch, runtime, persistence, and migration guarantees remain preserved under the new lifetime cap.

### 7.6 Foundation documentation

`docs/FIRST_PARTY_SESSION_ABSOLUTE_LIFETIME_FOUNDATION.md`

Must document implemented 7200-second idle plus 43200-second absolute lifetime behavior, source-default-disabled Local/Test/CI boundary, NO_SCHEMA_CHANGE result, migration immutability, preservation evidence, and explicit non-authority for Preview/Production/updater/deployment/release.

## 8. Explicitly excluded source paths and concerns

The later Sprint38 source stage does not include mutation of:

- `apps/web/app/Application/Identity/FirstPartySessionAuthorityRepository.php`;
- `apps/web/app/Infrastructure/Identity/LaravelFirstPartySessionAuthorityRepository.php`;
- session controllers;
- middleware;
- `apps/web/routes/web.php`;
- `apps/web/bootstrap/app.php`;
- any database migration;
- any audit-event allowlist or audit schema;
- any credential, factor, password, recovery-code, or TOTP persistence;
- any new environment variable or new feature arm.

It also excludes:

- trusted-device or remembered-device semantics;
- device trust scoring;
- IP reputation, browser fingerprinting, or adaptive/risk authentication;
- cross-tenant/global identity logout;
- administrator revocation of another identity;
- account suspension or disablement;
- API/mobile token lifecycle;
- WebAuthn/passkeys;
- federation/SSO;
- support impersonation or break-glass administration;
- Technical Preview activation;
- Production activation;
- updater wiring;
- deployment;
- release.

## 9. Runtime and activation boundary

Sprint38 source implementation must reuse the existing feature arm:

`ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false`

Canonical configuration remains under:

`oneqay.session_control`

with frozen lifetime values:

- `idle_ttl_seconds = 7200`;
- `absolute_ttl_seconds = 43200`.

Execution remains limited to Local / Test / CI governed runtime until a later explicit authority changes activation status.

Technical Preview remains `NO_SCHEMA_CHANGE` and receives no activation authority from this gate.

Production remains `NO-GO / NOT AUTHORIZED`.

Updater remains `DISABLED / UNWIRED`.

Deployment and release remain not authorized.

## 10. Required Sprint38 source regression outcomes

The later source implementation must prove at minimum:

- exactly 12-hour absolute lifetime from immutable original issuance;
- existing exactly 2-hour sliding idle lifetime remains intact;
- effective expiry always resolves to the earlier idle or absolute deadline;
- current authority validation fails after absolute deadline;
- active inventory excludes absolute-expired authority;
- touch cannot extend beyond absolute deadline;
- replay/concurrency cannot extend or resurrect authority;
- framework-session rotation and step-up cannot reset absolute age;
- tenant/identity/context isolation remains unchanged;
- credential/factor epoch enforcement remains unchanged;
- revoke-one, revoke-others, revoke-all, and canonical logout remain preserved;
- no new route, public identifier, payload authority, or audit event exists;
- source-default-disabled and runtime/persistence fail-closed behavior remains preserved;
- migrations #1 through #13 remain immutable;
- migration #14 does not exist for Sprint38;
- existing full application regression remains green.

## 11. Changed-file envelope of this gate publication

This schema/source-envelope gate itself is documentation-only and changes exactly one path:

`docs/SPRINT_38_FIRST_PARTY_SESSION_ABSOLUTE_LIFETIME_SCHEMA_SOURCE_ENVELOPE_GATE.md`

Sorted-path SHA-256 of the newline-terminated sorted gate changed-file list:

`ce1f2c0f1f1aaa0bab782eab85f6b023e2749cd98835812288711d37117887f9`

No application source, test, workflow, migration, canonical-state document, roadmap, manifest, Preview artifact, updater artifact, deployment artifact, or release artifact belongs to this gate publication.

## 12. Non-authority statement

Publishing this gate freezes the schema decision and the later exact ten-path source envelope only.

It does **not** itself authorize or perform:

- Sprint38 application-source mutation;
- provider or configuration mutation;
- test mutation;
- Sprint38 regression-workflow creation;
- historical workflow mutation beyond the already-published predecessor PR #250;
- schema mutation;
- migration #14;
- Technical Preview activation;
- Production activation;
- updater wiring;
- deployment;
- release.

## 13. Next governed stage after publication

After this gate is published, the next bounded stage is the **Sprint38 source implementation** using exactly the ten-path envelope and fingerprint frozen above.

That source stage requires separate Product Owner authority. No source mutation may begin solely from publication of this gate.
