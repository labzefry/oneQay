# Sprint39 First-Party Session Organizational Access Revalidation Schema / Source Envelope Gate

## Status

**SCHEMA/SOURCE-ENVELOPE GATE ONLY / NO SOURCE IMPLEMENTATION AUTHORITY**

Date: **2026-08-25**

This document follows the published Sprint39 entry gate and freezes the exact schema decision plus the only changed-file envelope that may be used by a later Sprint39 source implementation.

Selected concern:

**First-Party Session Organizational Access Revalidation Foundation**

This gate does not implement Sprint39 runtime behavior. It does not authorize application-source mutation, schema mutation, migration creation, Technical Preview activation, Production activation, updater wiring, deployment, or release.

Attribution: **Lab | zefry**

## 1. Canonical starting point

This gate is prepared from canonical `main` after publication of the Sprint39 schema/source-gate preservation predecessor PR #259.

Canonical base:

- commit: `583c50e402452911cf1764c6f3be5d8329ac87f2`;
- tree: `a0f63b57c1be63b78aa7f242932d338ed53ecdb6`;
- parent: `7ed812b18ce891e4dee6399b42919c27057bd85e`;
- signature: **verified / valid**;
- entry-gate publication: PR #258;
- schema/source-gate preservation predecessor: PR #259.

The preservation predecessor recognizes this exact one-file gate successor:

`docs/SPRINT_39_FIRST_PARTY_SESSION_ORGANIZATIONAL_ACCESS_REVALIDATION_SCHEMA_SOURCE_ENVELOPE_GATE.md`

Sorted newline-terminated one-path SHA-256:

`c66722c0e61aac85deb260fb5f8989aeae55cacdd61bb60ed9beadc161b570af`

Unknown successor shapes remain fail-closed.

## 2. Inherited frozen authority semantics

The later Sprint39 source implementation must preserve the published entry-gate decision without reinterpretation.

A usable first-party logical session authority must continue to satisfy every published Sprint36 through Sprint38 invariant and additionally prove that the organizational access represented by that authority remains currently authorized in durable server-owned access state.

The later implementation must preserve all of the following:

1. durable logical first-party session authority remains distinct from raw framework session identifiers;
2. tenant and identity ownership remain server-derived;
3. `credential_epoch` remains authoritative;
4. privileged `factor_epoch` remains authoritative where applicable;
5. revocation state remains authoritative;
6. sliding idle lifetime remains exactly **7200 seconds**;
7. absolute lifetime remains exactly **43200 seconds / 12 hours** from durable `issued_at_unix`;
8. exact organization, optional outlet, and optional device coordinates represented by the current authority remain authoritative context coordinates;
9. current durable tenant membership must still exist for the exact tenant + identity;
10. current durable organizational relationship authorization must still permit the exact represented organization/outlet/device coordinates;
11. caller-supplied tenant, identity, organization, outlet, or device selectors never become authorization authority;
12. malformed, missing, contradictory, impossible, stale, or removed access evidence fails closed;
13. outlet-bound or device-bound authority must never silently fall back to broader organization-only permission;
14. revalidation must not recreate a removed grant;
15. revalidation must not auto-switch organizational context;
16. revalidation must not mint a replacement logical authority;
17. session rotation, inventory access, privileged step-up, retry, replay, or concurrency must not bypass access revalidation;
18. another tenant and another identity remain isolated.

Organizational revalidation is additive authorization evidence. It does not replace session ownership, epochs, revocation, idle lifetime, absolute lifetime, or existing runtime/persistence gates.

## 3. Schema determination

Sprint39 schema classification is frozen as:

**NO_SCHEMA_CHANGE**

Rationale:

- durable tenant membership already exists as server-owned authorization state;
- durable organization/outlet/device relationship access already exists as server-owned authorization state;
- migration #13 already persists the organization, optional outlet, and optional device coordinates attached to the logical first-party session authority;
- the existing request-time session authority path already provides the exact tenant, identity, and organizational coordinates that must be revalidated;
- no new durable entity, field, index, constraint, audit structure, or persistence concept is required to determine whether current access remains permitted.

Therefore:

- canonical migrations remain exactly **#1 through #13**;
- migrations #1 through #13 are immutable for Sprint39;
- migration #14 is **NOT REQUIRED**;
- migration #14 is **NOT SELECTED**;
- migration #14 is **NOT AUTHORIZED**;
- no table, column, index, foreign key, enum, trigger, generated field, migration artifact, rollback path, or schema rewrite belongs to the Sprint39 source envelope.

Any later discovery that truly requires schema mutation invalidates this source envelope. Source work must then stop and a new governed schema decision must be published before implementation continues.

## 4. Existing authority surfaces to reuse unchanged

Sprint39 must reuse the existing tenant and organizational authority contracts rather than create replacement abstractions.

### 4.1 Tenant membership verifier

Existing interface:

`App\Application\Tenancy\TenantMembershipVerifier`

Its canonical contract already verifies a principal against a tenant hint and returns a verified tenant context or `null`.

The later implementation may consume this interface through dependency injection, but the interface itself is excluded from the source envelope.

The returned tenant context must be checked so its server-verified tenant ID equals the exact tenant represented by the already-validated current logical authority. A missing membership, malformed verified tenant ID, or tenant mismatch fails closed.

### 4.2 Organizational relationship verifier

Existing interface:

`App\Application\Organization\OrganizationalRelationshipVerifier`

Its canonical contract already accepts:

- exact platform identity;
- exact tenant;
- exact organization;
- optional outlet;
- optional device.

The existing durable implementation is already backed by durable organizational-access grants and fails closed for invalid relationships, including device-without-outlet structure.

The later implementation may consume this interface through dependency injection, but the interface and its implementation are excluded from the source envelope.

### 4.3 Existing service-provider bindings

The application provider already binds `TenantMembershipVerifier` and `OrganizationalRelationshipVerifier` to their durable implementations for normal governed Local/Test/CI runtime.

No provider mutation, replacement binding, new provider, new feature arm, or new runtime class is required or authorized.

## 5. Frozen request-time implementation boundary

The only application code surface authorized for the later Sprint39 implementation is:

`apps/web/app/Delivery/Http/Middleware/EnforceActiveFirstPartySessionAuthorityMiddleware.php`

The middleware may change only as required to add current durable organizational-access revalidation after the existing logical session authority has passed its established validation.

The later implementation must preserve this sequence:

1. reject restricted or malformed framework-session state using the existing fail-closed rules;
2. derive tenant, identity, logical authority ID, organization, optional outlet, and optional device from the current server-side session state;
3. validate the logical authority through the existing `FirstPartySessionAuthorityService::assertActiveCurrent(...)` contract, preserving exact ownership, context equality, credential/factor epochs, revocation, idle lifetime, absolute lifetime, runtime, and persistence checks;
4. only after that existing authority succeeds, parse the same verified current coordinates into the existing domain ID types required by the durable access verifiers;
5. verify current durable tenant membership for the exact identity + tenant;
6. require the server-verified membership tenant to equal the exact current authority tenant;
7. verify the exact organization/outlet/device relationship through the existing `OrganizationalRelationshipVerifier`;
8. fail closed if any required current access evidence is absent, malformed, mismatched, removed, or denied;
9. continue the protected request only when both the existing logical authority and current organizational access remain valid.

The middleware must not use query parameters, route parameters, headers, request body fields, cookies, or any other caller-controlled organizational selector as authority for this revalidation.

## 6. Frozen structural rules

The later implementation must enforce the following structural semantics without broadening authorization:

- organization is required for a usable full-session authority under the existing session contract;
- outlet remains optional;
- device remains optional;
- a device-bound authority without a valid outlet fails closed;
- an outlet-bound authority cannot fall back to organization-only access;
- a device-bound authority cannot fall back to outlet-only or organization-only access;
- exact tenant + identity ownership remains required before organizational revalidation;
- exact current organizational coordinates must remain those already validated against the durable session authority;
- a malformed domain identifier fails closed rather than being normalized into a different authority;
- a removed tenant membership denies the request even if the logical session row itself is otherwise active;
- a removed organization, outlet, or device grant denies the request even if the logical session row itself is otherwise active;
- loss of access must not modify or recreate organizational grants;
- loss of access must not choose another organization, outlet, or device;
- loss of access must not issue a replacement logical authority.

## 7. Failure handling boundary

The existing middleware denial path remains the only authorized request-level failure handling for this concern.

If organizational access revalidation fails:

- the protected request must not continue;
- the local framework session may be invalidated using the middleware's existing denial behavior;
- the CSRF token may be regenerated using the existing denial behavior;
- the existing generic session-authority denial response remains applicable;
- canonical logout behavior remains preserved.

This gate does not authorize:

- a new durable session-row transition solely for organizational access loss;
- a new durable revocation reason;
- a new public error vocabulary;
- a new recovery route;
- a new login or replacement authority;
- a new audit event.

Any future proposal for durable state mutation specifically on organizational-access loss requires a separate Product Owner selection and governed envelope.

## 8. No new HTTP, API, audit, or configuration contract

Sprint39 adds:

**NO NEW ROUTE / NO NEW PUBLIC API / NO NEW REQUEST PAYLOAD**

Existing session-control routes remain unchanged, including inventory, revoke-one, revoke-others, revoke-all, privileged reauthentication/step-up, and canonical logout.

Sprint39 also adds:

**NO NEW AUDIT EVENT**

Existing secret-free server-derived session audit vocabulary remains unchanged.

Sprint39 also adds:

**NO NEW FEATURE ARM / NO NEW ENVIRONMENT VARIABLE**

The implementation must remain under the existing disabled-by-default session-control boundary:

`ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false`

The existing fixed lifetime configuration remains unchanged:

- `idle_ttl_seconds = 7200`;
- `absolute_ttl_seconds = 43200`.

## 9. Exact Sprint39 source implementation envelope

A later Sprint39 source implementation PR is frozen to exactly these eight paths:

1. `.github/workflows/sprint35-privileged-totp-recovery-regression.yml`
2. `.github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml`
3. `.github/workflows/sprint37-first-party-all-session-termination-regression.yml`
4. `.github/workflows/sprint38-first-party-session-absolute-lifetime-regression.yml`
5. `.github/workflows/sprint39-first-party-session-organizational-access-revalidation-regression.yml`
6. `apps/web/app/Delivery/Http/Middleware/EnforceActiveFirstPartySessionAuthorityMiddleware.php`
7. `apps/web/tests/first-party-session-organizational-access-revalidation.php`
8. `docs/FIRST_PARTY_SESSION_ORGANIZATIONAL_ACCESS_REVALIDATION_FOUNDATION.md`

Sorted-path SHA-256 of the newline-terminated sorted source changed-file list:

`2cfc92c34f46375b11bf3fe92f9094cefa598d234133847fdd6629be211f12c4`

No other path belongs to the authorized Sprint39 source envelope.

## 10. Role of every authorized source path

### 10.1 Historical preservation workflows

The following four workflow paths may change only as required to recognize the exact eight-path Sprint39 source successor fingerprint while retaining their published executable preservation behavior:

- `.github/workflows/sprint35-privileged-totp-recovery-regression.yml`;
- `.github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml`;
- `.github/workflows/sprint37-first-party-all-session-termination-regression.yml`;
- `.github/workflows/sprint38-first-party-session-absolute-lifetime-regression.yml`.

They must continue to preserve their historical tests, migration isolation/immutability, configuration checks, runtime boundaries, and fail-closed unknown-shape behavior.

They must not be disabled, converted to wildcard successor acceptance, made migration-blind, or bypassed with broad `paths-ignore` semantics.

A separately published source-preservation predecessor may be required before opening the later exact eight-path source implementation PR. This gate freezes that governance requirement but does not itself perform the source implementation.

### 10.2 Sprint39 regression workflow

New path:

`.github/workflows/sprint39-first-party-session-organizational-access-revalidation-regression.yml`

The workflow must:

- enforce exactly the eight-path Sprint39 source envelope and fingerprint;
- reject environment/dependency/schema paths outside the frozen envelope;
- enforce **NO_SCHEMA_CHANGE**;
- enforce exactly migrations #1 through #13 and absence of migration #14;
- lint the Sprint39 middleware and regression fixture;
- install locked application dependencies without dependency mutation;
- run the dedicated Sprint39 organizational-access revalidation regression;
- preserve Sprint36 session inventory/revocation behavior;
- preserve Sprint37 all-session termination behavior;
- preserve Sprint38 absolute-lifetime behavior;
- preserve the full application regression where required by canonical governance;
- preserve no-new-route, no-new-audit-event, no-new-feature-arm boundaries;
- preserve Local/Test/CI and disabled-by-default activation boundaries;
- fail closed for unknown source shapes.

### 10.3 Request-time middleware

Path:

`apps/web/app/Delivery/Http/Middleware/EnforceActiveFirstPartySessionAuthorityMiddleware.php`

This is the only authorized application-code mutation.

It may change only to:

- inject the already-existing `TenantMembershipVerifier`;
- inject the already-existing `OrganizationalRelationshipVerifier`;
- parse already-validated current tenant/identity/organization/outlet/device session coordinates into existing domain IDs;
- verify the current tenant membership after the logical authority succeeds;
- verify the current exact organizational relationship after the logical authority succeeds;
- fail closed through the existing middleware denial path if current durable access is no longer valid.

It must preserve the existing `FirstPartySessionAuthorityService` contract rather than moving or duplicating its session ownership, epoch, revocation, idle, or absolute-lifetime authority into the middleware.

### 10.4 Dedicated regression fixture

New path:

`apps/web/tests/first-party-session-organizational-access-revalidation.php`

It must prove the frozen Sprint39 behaviors directly and deterministically without relying on Production or Technical Preview activation.

The fixture must use existing durable authority contracts and may establish or remove test-only durable access state using already-existing repository capabilities. It must not require a new migration, table, schema concept, public route, or feature arm.

### 10.5 Foundation documentation

New path:

`docs/FIRST_PARTY_SESSION_ORGANIZATIONAL_ACCESS_REVALIDATION_FOUNDATION.md`

It must document:

- implemented request-time revalidation semantics;
- exact current tenant membership requirement;
- exact organization/outlet/device relationship requirement;
- fail-closed access-loss behavior;
- preserved Sprint36 through Sprint38 semantics;
- `NO_SCHEMA_CHANGE` result;
- migrations #1 through #13 immutability and absence of migration #14;
- source-default-disabled Local/Test/CI boundary;
- no-new-route, no-new-audit-event, no-new-feature-arm results;
- preservation and qualification evidence;
- explicit non-authority for Technical Preview, Production, updater, deployment, and release.

## 11. Explicitly excluded source paths

The later Sprint39 source implementation must not mutate any path outside the exact eight-path envelope.

Specifically excluded are:

- `apps/web/app/Application/Identity/FirstPartySessionAuthorityService.php`;
- `apps/web/app/Application/Identity/FirstPartySessionAuthorityRepository.php`;
- `apps/web/app/Infrastructure/Identity/LaravelFirstPartySessionAuthorityRepository.php`;
- `apps/web/app/Application/Tenancy/TenantMembershipVerifier.php`;
- `apps/web/app/Infrastructure/Tenancy/LaravelTenantMembershipVerifier.php`;
- `apps/web/app/Application/Organization/OrganizationalRelationshipVerifier.php`;
- `apps/web/app/Infrastructure/Organization/LaravelOrganizationalRelationshipVerifier.php`;
- `apps/web/app/Application/Organization/EnterOrganizationalContext.php`;
- `apps/web/app/Application/Organization/OrganizationalContextStore.php`;
- durable organizational-access repository interfaces and implementations;
- `apps/web/app/Providers/AppServiceProvider.php`;
- `apps/web/app/Delivery/Http/Identity/FirstPartySessionKeys.php`;
- session controllers;
- `apps/web/routes/web.php`;
- `apps/web/bootstrap/app.php`;
- `apps/web/config/oneqay.php`;
- `apps/web/environment.example`;
- `apps/web/tests/run.php`;
- every database migration;
- every dependency manifest or lock file.

If implementation appears to require any excluded path, the eight-path envelope is invalid for that design and source mutation must stop pending a separately governed envelope revision.

## 12. Explicitly excluded concerns

Sprint39 source authority does not include:

- migration #14;
- schema mutation;
- new session route or API;
- new request payload authority;
- grant-administration UI or mutation flows;
- automatic grant restoration;
- organization/outlet/device auto-switching;
- administrator force-logout of another identity;
- account disablement or suspension lifecycle;
- support impersonation;
- trusted-device enrollment;
- remembered-device semantics;
- browser/device fingerprinting;
- IP reputation or geolocation trust;
- behavioral or adaptive risk scoring;
- WebAuthn/passkeys;
- federation or SSO;
- API/mobile token authority;
- break-glass access;
- new audit vocabulary;
- Technical Preview activation;
- Production activation;
- updater wiring;
- deployment;
- release;
- Phase-exit authority.

These exclusions are not implied future requirements. Any such concern requires separate Product Owner selection and governance.

## 13. Frozen regression targets

The later source implementation must prove at minimum all of the following:

1. an otherwise-valid logical authority with current exact tenant membership and exact organization relationship proceeds;
2. removed tenant membership causes request-time authority to fail closed;
3. removed organization relationship causes request-time authority to fail closed;
4. removed outlet relationship causes an outlet-bound authority to fail closed;
5. removed device relationship causes a device-bound authority to fail closed;
6. a malformed device-bound authority/context fails closed;
7. device without outlet fails closed;
8. an outlet-bound authority does not fall back to organization-only permission;
9. a device-bound authority does not fall back to outlet-only or organization-only permission;
10. another tenant remains isolated and unaffected;
11. another identity remains isolated and unaffected;
12. caller-controlled organizational coordinates cannot override current authority coordinates;
13. current access revalidation does not recreate a removed grant;
14. current access revalidation does not auto-switch organizational context;
15. current access revalidation does not mint a replacement logical authority;
16. framework-session rotation does not bypass revalidation;
17. privileged step-up does not bypass revalidation;
18. inventory access does not bypass or restore removed organizational access;
19. retry, replay, and concurrent request timing do not resurrect stale access;
20. revoke-one remains preserved;
21. revoke-others remains preserved;
22. tenant-scoped revoke-all remains preserved;
23. canonical logout remains preserved;
24. credential-epoch invalidation remains preserved;
25. privileged factor-epoch invalidation remains preserved;
26. exactly 7200-second idle lifetime remains preserved;
27. exactly 43200-second absolute lifetime remains preserved;
28. effective expiry remains bounded by the earlier idle or absolute deadline;
29. exact absolute-deadline equality behavior remains preserved;
30. deadline + 1 second remains fail-closed;
31. no new public route, route name, request payload, or public authority selector exists;
32. no new audit event exists;
33. no new feature arm or environment variable exists;
34. migrations remain exactly #1 through #13;
35. migration #14 does not exist;
36. Local/Test/CI boundary remains preserved;
37. Technical Preview remains not activated by Sprint39;
38. Production remains `NO-GO / NOT AUTHORIZED`;
39. updater remains `DISABLED / UNWIRED`;
40. deployment and release remain not authorized.

Unknown or contradictory authority state must remain fail-closed.

## 14. Runtime and activation boundary

Sprint39 source implementation remains:

**Local/Test/CI only**

Existing session-control source default remains:

`ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false`

Technical Preview remains:

**NO_SCHEMA_CHANGE / NOT ACTIVATED BY THIS GATE**

Production remains:

**NO-GO / NOT AUTHORIZED**

Updater remains:

**DISABLED / UNWIRED**

Deployment and release remain:

**NOT AUTHORIZED**

No protected runtime is armed by publishing this gate.

## 15. Changed-file envelope of this gate publication

This schema/source-envelope gate publication itself is documentation-only and changes exactly one path:

`docs/SPRINT_39_FIRST_PARTY_SESSION_ORGANIZATIONAL_ACCESS_REVALIDATION_SCHEMA_SOURCE_ENVELOPE_GATE.md`

Sorted-path SHA-256 of the newline-terminated sorted gate changed-file list:

`c66722c0e61aac85deb260fb5f8989aeae55cacdd61bb60ed9beadc161b570af`

No application source, test, workflow, migration, canonical-state document, roadmap, manifest, Preview artifact, updater artifact, deployment artifact, or release artifact belongs to this gate publication.

## 16. Non-authority statement

Publishing this gate freezes only:

- the Sprint39 `NO_SCHEMA_CHANGE` determination;
- migrations #1 through #13 immutability and migration #14 non-authority;
- the request-time implementation boundary;
- the exact eight-path future source implementation envelope;
- the exact source-envelope fingerprint;
- required regression and preservation outcomes.

It does **not** itself authorize or perform:

- Sprint39 application-source mutation;
- middleware mutation;
- test creation;
- Sprint39 regression-workflow creation;
- historical workflow mutation beyond the separately published preservation predecessor PR #259;
- schema mutation;
- migration #14;
- Technical Preview activation;
- Production activation;
- updater wiring;
- deployment;
- release.

## 17. Exit criteria for this stage

This schema/source-envelope gate stage is complete only when:

- this exact one-file gate is qualified on its exact PR head;
- all triggered workflows succeed without bypass;
- required governance subchecks succeed;
- the PR is Ready;
- a top-level Product Owner exact-head merge authorization exists;
- `product-owner-merge-authority=SUCCESS`;
- final race-check confirms unchanged head, unchanged canonical base relationship, exact one-file envelope, and `behind_by=0`;
- squash merge uses `expected_head_sha`;
- the publication commit is verified on canonical `main`.

## 18. Next governed stage after publication

After this gate is published, the next bounded stage is the **Sprint39 source implementation** using exactly the eight-path source envelope and fingerprint frozen in this document.

That source implementation requires separate Product Owner authority.

No Sprint39 source mutation may begin solely from publication of this gate.

Attribution: **Lab | zefry**
