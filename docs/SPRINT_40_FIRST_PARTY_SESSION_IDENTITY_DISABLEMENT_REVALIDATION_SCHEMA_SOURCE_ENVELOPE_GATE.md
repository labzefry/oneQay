# Sprint40 First-Party Session Identity Disablement Revalidation Schema / Source Envelope Gate

## Status

**SCHEMA/SOURCE-ENVELOPE GATE ONLY / NO SOURCE IMPLEMENTATION AUTHORITY**

Date: **2026-08-25**

This document follows the published Sprint40 entry gate and freezes the exact schema decision, source-preservation predecessor, and only changed-file envelope that may be used by a later Sprint40 source implementation.

Selected concern:

**First-Party Session Identity Disablement Revalidation Foundation**

This gate does not implement Sprint40 runtime behavior. It selects a minimal forward-only migration #14 for a later Local/Test/CI source stage, but does not create that migration or mutate schema in this PR. It does not authorize identity-administration mutation, Technical Preview activation, Production activation, updater wiring, deployment, or release.

Attribution: **Lab | zefry**

## 1. Canonical starting point

This gate is prepared from canonical `main` after publication of the Sprint40 schema/source-gate preservation predecessor PR #269.

Canonical base:

- commit: `38d040b2b0b159d614476ddf14604dd1080314b1`;
- tree: `8287c12599a6ec9303be634bf21258bafbfc7688`;
- parent: `fd629f995faf376eed5ef8b563f09d13286f4af2`;
- signature: **verified / valid**;
- entry-gate publication: PR #268;
- schema/source-gate preservation predecessor: PR #269.

The preservation predecessor recognizes this exact one-file gate successor:

`docs/SPRINT_40_FIRST_PARTY_SESSION_IDENTITY_DISABLEMENT_REVALIDATION_SCHEMA_SOURCE_ENVELOPE_GATE.md`

Sorted newline-terminated one-path SHA-256:

`9a3a85fb3de7ce007b09586af60e18b0cefd6ec1b6fba1b3c2b64e508354d803`

Unknown successor shapes remain fail-closed.

## 2. Inherited frozen authority semantics

The later Sprint40 source implementation must preserve the published entry-gate decision without reinterpretation.

A usable first-party logical session authority must continue to satisfy every Sprint36 through Sprint39 invariant and additionally prove that the exact tenant-scoped identity represented by the current authority remains eligible for first-party authenticated use according to canonical server-owned identity state.

The implementation must preserve all of the following:

1. durable logical first-party session authority remains distinct from raw Laravel session identifiers;
2. exact tenant + identity ownership remains server-derived;
3. `credential_epoch` remains authoritative;
4. privileged `factor_epoch` remains authoritative where applicable;
5. revocation state remains authoritative;
6. sliding idle lifetime remains exactly **7200 seconds**;
7. absolute lifetime remains exactly **43200 seconds / 12 hours** from durable `issued_at_unix`;
8. current first-party identity eligibility is independent server-owned evidence;
9. current tenant membership remains required;
10. exact current organization/outlet/device relationship authorization remains required;
11. caller-supplied identity or eligibility selectors never become authority;
12. missing, malformed, contradictory, stale, impossible, or disabled eligibility evidence fails closed;
13. identity eligibility must not auto-enable, repair, recreate, or replace identity state;
14. identity eligibility must not mint a replacement logical authority or switch tenant/identity/organization/outlet/device context;
15. replay, retry, session rotation, inventory access, privileged step-up, or stale local session state must not resurrect disabled access;
16. another tenant and another identity remain isolated.

Identity eligibility is additive authority evidence. It does not replace credential/factor epochs, revocation, lifetime, membership, or organizational revalidation.

## 3. Schema determination

Sprint40 schema classification is frozen as:

**MINIMAL FORWARD-ONLY SCHEMA CHANGE / MIGRATION #14 SELECTED**

Canonical persistence evidence establishes that `oneqay_identities` currently contains only:

- `tenant_id`;
- `id`;
- primary key `(tenant_id, id)`.

There is no independently governed server-owned identity eligibility field. Password-credential existence and credential epochs are credential-validity evidence and must not be repurposed as principal eligibility.

Therefore migration #14 is required and selected with this exact path:

`apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php`

The migration may perform only this schema mutation:

- alter `oneqay_identities`;
- add boolean column `first_party_authentication_enabled`;
- column is **NOT NULL**;
- default is **true** so existing canonical identities remain explicitly eligible when the migration is applied in the authorized Local/Test/CI source stage;
- add no index because exact lookup already uses the existing composite primary key `(tenant_id, id)`;
- add no timestamp, reason, actor, enum, lifecycle journal, foreign key, trigger, generated column, or auxiliary table.

The default value is migration compatibility for existing rows. Runtime code must never treat a missing column, missing row, query failure, malformed value, or storage-unavailable condition as an implicit `true` fallback.

Migration #14 must preserve the repository's forward-only rule:

`throw new LogicException('Forward-only generated migration; rollback is not authorized.');`

Migrations #1 through #13 remain immutable. Migration #14 is the only selected new migration artifact for Sprint40.

Technical Preview remains schema-frozen under its existing authority. Migration #14 is **NOT AUTHORIZED FOR TECHNICAL PREVIEW OR PRODUCTION** by this gate.

## 4. Frozen identity-eligibility contract

The later source implementation must introduce exactly this application contract:

`App\Application\Identity\FirstPartyIdentityEligibilityVerifier`

Its public method is frozen as:

`public function isEligible(TenantId $tenantId, PlatformIdentityId $identityId): bool;`

The contract is read-only. It may answer only whether the exact tenant-scoped identity is currently eligible for first-party authenticated use.

It must not expose disable/enable mutation, reason fields, timestamps, lifecycle administration, session mutation, organizational mutation, credential mutation, or caller-selectable authority.

## 5. Frozen durable verifier adapter

The later source implementation must introduce exactly this adapter:

`App\Infrastructure\Identity\LaravelFirstPartyIdentityEligibilityVerifier`

It must:

- implement `FirstPartyIdentityEligibilityVerifier`;
- receive the existing database `Connection` plus canonical persistence/runtime/session-control availability from `AppServiceProvider`;
- fail closed unless persistence is enabled, runtime is one of `local`, `test`, or `ci`, and the existing session-control boundary is valid;
- query only `oneqay_identities` by exact `tenant_id` + `id`;
- read only `first_party_authentication_enabled`;
- return `true` only for canonical enabled evidence;
- return `false` for disabled, missing, malformed, contradictory, storage-unavailable, query-error, or schema-missing evidence;
- perform no write, auto-repair, backfill, caching, lifecycle mutation, audit mutation, or session mutation.

The verifier must re-read current durable state for each protected request; no request-external cache may grandfather previously enabled state after disablement.

## 6. Frozen service-provider binding

`apps/web/app/Providers/AppServiceProvider.php` may change only as required to:

- import the new verifier interface and Laravel adapter;
- bind `FirstPartyIdentityEligibilityVerifier` as a scoped dependency;
- construct the adapter from the existing database connection, persistence-enabled state, runtime class, and existing `sessionControlEnabled()` result.

No new provider, feature arm, environment variable, config key, runtime class, Technical Preview override, or Production binding is authorized.

## 7. Frozen request-time composition

The only request-time application behavior mutation remains in:

`apps/web/app/Delivery/Http/Middleware/EnforceActiveFirstPartySessionAuthorityMiddleware.php`

The exact sequence is frozen as:

1. preserve existing restricted/malformed framework-session fail-closed checks;
2. derive tenant, identity, logical authority ID, organization, optional outlet, and optional device only from current server-side session state;
3. parse and canonicalize current tenant + identity IDs;
4. run existing `FirstPartySessionAuthorityService::assertActiveCurrent(...)` first, preserving Sprint36-Sprint38 ownership, epochs, revocation, idle lifetime, absolute lifetime, runtime, and persistence semantics;
5. immediately verify `FirstPartyIdentityEligibilityVerifier::isEligible($tenantId, $identityId)` using those exact already-validated server-owned coordinates;
6. fail closed through the existing generic middleware denial path when identity eligibility is not current;
7. preserve the existing Sprint39 domain-ID parsing and device-without-outlet fail-closed rule;
8. preserve exact current tenant membership revalidation;
9. preserve exact organization/outlet/device relationship revalidation;
10. continue the protected request only after every required session, identity, membership, and organizational layer succeeds.

Identity eligibility must be evaluated before Sprint39 organizational revalidation so a disabled identity cannot continue merely because membership or organizational grants remain current.

No query parameter, route parameter, header, request body field, cookie selector, or other caller-controlled identity-state input may become authority.

## 8. Failure handling boundary

If identity eligibility fails:

- the protected request must not continue;
- the existing middleware denial behavior may invalidate the local Laravel session and regenerate the CSRF token;
- the existing generic `SESSION_AUTHORITY_DENIED` response remains the only authorized public error contract;
- canonical logout behavior remains preserved;
- no durable session-row transition is required solely because identity eligibility failed.

This gate authorizes no new revocation reason, no new audit event, no recovery route, no replacement login/session authority, and no identity reactivation behavior.

## 9. No new HTTP, API, audit, or configuration contract

Sprint40 adds:

**NO NEW ROUTE / NO NEW PUBLIC API / NO NEW REQUEST PAYLOAD**

Sprint40 adds:

**NO NEW AUDIT EVENT**

Sprint40 adds:

**NO NEW FEATURE ARM / NO NEW ENVIRONMENT VARIABLE**

The existing source-default-disabled boundary remains:

`ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false`

The fixed lifetime configuration remains unchanged:

- `idle_ttl_seconds = 7200`;
- `absolute_ttl_seconds = 43200`.

## 10. Required source-preservation predecessor

Before opening the later Sprint40 source implementation PR, a separately qualified preservation predecessor must update exactly these six workflow paths:

1. `.github/workflows/m7-5-preview-release-artifact.yml`
2. `.github/workflows/sprint35-privileged-totp-recovery-regression.yml`
3. `.github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml`
4. `.github/workflows/sprint37-first-party-all-session-termination-regression.yml`
5. `.github/workflows/sprint38-first-party-session-absolute-lifetime-regression.yml`
6. `.github/workflows/sprint39-first-party-session-organizational-access-revalidation-regression.yml`

Sorted newline-terminated six-path SHA-256:

`671946562a3eef1375d87b70936bac263aa18f660d6ea274406ed57f1c653351`

That predecessor may change only recognition/trigger/isolation logic necessary for the exact Sprint40 source successor while preserving historical executable behavior and unknown-shape fail-closed semantics.

In particular:

- M7.5 must recognize only the exact Sprint40 source fingerprint and isolate migration #14 from schema-free Technical Preview regression execution; it must not activate or apply migration #14 to Technical Preview;
- Sprint35-Sprint37 must retain historical regression behavior while recognizing the exact Sprint40 successor and isolating later migration artifacts only where necessary for historical execution;
- Sprint38-Sprint39 must additionally ensure their path filters trigger for the exact Sprint40 source paths and preserve their published NO_SCHEMA_CHANGE historical semantics by isolating migration #14 from those historical regressions rather than weakening their old contracts;
- no wildcard successor acceptance, broad bypass, `paths-ignore` escape, disabled job, or migration-blind behavior is authorized.

The source-preservation predecessor is governance compatibility only. It must not implement Sprint40 application behavior or create migration #14.

## 11. Exact Sprint40 source implementation envelope

After the required preservation predecessor is published, the Sprint40 source implementation PR is frozen to exactly these eight paths:

1. `.github/workflows/sprint40-first-party-session-identity-disablement-revalidation-regression.yml`
2. `apps/web/app/Application/Identity/FirstPartyIdentityEligibilityVerifier.php`
3. `apps/web/app/Delivery/Http/Middleware/EnforceActiveFirstPartySessionAuthorityMiddleware.php`
4. `apps/web/app/Infrastructure/Identity/LaravelFirstPartyIdentityEligibilityVerifier.php`
5. `apps/web/app/Providers/AppServiceProvider.php`
6. `apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php`
7. `apps/web/tests/first-party-session-identity-disablement-revalidation.php`
8. `docs/FIRST_PARTY_SESSION_IDENTITY_DISABLEMENT_REVALIDATION_FOUNDATION.md`

Sorted newline-terminated eight-path SHA-256:

`a9caf2b68210a38687fee543256aec04dc1e67ee1ef403608f7db69139957ff8`

No other path belongs to the authorized Sprint40 source envelope.

## 12. Role of every authorized source path

### 12.1 Sprint40 regression workflow

New workflow:

`.github/workflows/sprint40-first-party-session-identity-disablement-revalidation-regression.yml`

It must:

- enforce exactly the eight-path Sprint40 source envelope and fingerprint;
- enforce immutable migrations #1 through #13 and exactly one selected migration #14 path;
- validate the exact `oneqay_identities.first_party_authentication_enabled` schema shape;
- reject any additional table/column/index/audit/config/route/dependency mutation;
- lint all Sprint40 PHP source;
- install locked dependencies without mutation;
- execute migration #14 only in governed Local/Test/CI test storage;
- run the dedicated Sprint40 identity-disablement revalidation regression;
- preserve Sprint36 inventory/revocation behavior;
- preserve Sprint37 all-session termination behavior;
- preserve Sprint38 absolute-lifetime behavior;
- preserve Sprint39 organizational-access revalidation behavior;
- preserve full application regression where canonical governance requires it;
- preserve no-new-route, no-new-audit-event, no-new-feature-arm boundaries;
- prove Technical Preview and Production remain unactivated;
- fail closed for unknown source shapes.

### 12.2 Eligibility verifier interface

`apps/web/app/Application/Identity/FirstPartyIdentityEligibilityVerifier.php`

This file may contain only the frozen read-only `isEligible(TenantId, PlatformIdentityId): bool` contract required by this gate.

### 12.3 Durable eligibility verifier

`apps/web/app/Infrastructure/Identity/LaravelFirstPartyIdentityEligibilityVerifier.php`

This file may contain only the fail-closed exact identity-eligibility read adapter described by this gate.

### 12.4 Request-time middleware

`apps/web/app/Delivery/Http/Middleware/EnforceActiveFirstPartySessionAuthorityMiddleware.php`

This file may change only to inject and call the new eligibility verifier in the frozen sequence while preserving every existing Sprint36-Sprint39 check and the existing generic denial path.

### 12.5 Service-provider wiring

`apps/web/app/Providers/AppServiceProvider.php`

This file may change only for the scoped interface-to-adapter binding described by this gate.

### 12.6 Migration #14

`apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php`

This file may implement only the exact single-column forward-only schema change defined in section 3.

### 12.7 Dedicated regression fixture

`apps/web/tests/first-party-session-identity-disablement-revalidation.php`

The fixture must prove at least:

- enabled exact identity + valid existing authority continues only when Sprint39 access also remains valid;
- disabled exact identity fails closed;
- missing identity row fails closed;
- malformed eligibility value fails closed where the test database permits malformed storage evidence;
- persistence/runtime/session-control unavailable states fail closed;
- caller input cannot override durable identity state;
- current credentials, factor state, membership, or organizational access do not override disablement;
- another tenant and another identity remain isolated;
- a direct test-only durable eligibility transition from enabled to disabled is observed on the next protected request without cache grandfathering;
- session rotation and privileged step-up do not bypass revalidation;
- Sprint36-Sprint39 preserved behaviors remain green;
- no identity-administration public route or mutation is required.

Direct database state setup in the dedicated fixture is test evidence only; it does not authorize an application producer for disable/enable lifecycle mutation.

### 12.8 Foundation documentation

`docs/FIRST_PARTY_SESSION_IDENTITY_DISABLEMENT_REVALIDATION_FOUNDATION.md`

It must document implemented semantics, migration #14, exact verifier behavior, exact middleware ordering, preserved Sprint36-Sprint39 behavior, Local/Test/CI-only boundary, qualification evidence, and explicit non-authority for Technical Preview, Production, updater, deployment, and release.

## 13. Explicitly excluded source paths and concerns

The later Sprint40 source implementation must not mutate any path outside the exact eight-path envelope.

Specifically excluded are:

- `apps/web/app/Application/Identity/FirstPartySessionAuthorityService.php`;
- `apps/web/app/Application/Identity/FirstPartySessionAuthorityRepository.php`;
- `apps/web/app/Infrastructure/Identity/LaravelFirstPartySessionAuthorityRepository.php`;
- credential/factor epoch repositories and services;
- tenant membership verifier interface/implementation;
- organizational relationship verifier interface/implementation;
- durable organizational-access repositories;
- session controllers;
- `apps/web/routes/web.php`;
- `apps/web/bootstrap/app.php`;
- `apps/web/config/oneqay.php`;
- `apps/web/environment.example`;
- `apps/web/tests/run.php`;
- dependency manifests or lock files;
- migrations #1 through #13;
- any migration #15 or later.

Also excluded are identity disable/enable administration routes or UI, lifecycle journals, bulk disablement, reason/timestamp/actor metadata, timed reactivation, external directory/SSO lifecycle synchronization, new public errors, new audit vocabulary, new feature flags, force-logout of another identity beyond existing self-scoped contracts, impersonation, trusted-device enrollment, WebAuthn/passkeys, API/mobile token authority, Technical Preview activation, Production activation, updater wiring, deployment, release, and phase-exit authority.

If implementation appears to require any excluded path or concern, source mutation must stop pending a separately governed envelope revision.

## 14. Runtime and activation boundary

Sprint40 source authority remains:

**Local/Test/CI only**

Technical Preview remains:

**NO NEW SCHEMA APPLICATION / NOT ACTIVATED BY THIS GATE**

Production remains:

**NO-GO / NOT AUTHORIZED**

Updater remains:

**DISABLED / UNWIRED**

Deployment and release remain:

**NOT AUTHORIZED**

## 15. Exit criteria for this gate stage

This schema/source-envelope gate stage is complete only when:

- this exact one-file document is qualified on its exact PR head;
- all triggered workflows succeed without bypass;
- governance subchecks succeed;
- the PR is Ready;
- a top-level Product Owner exact-head merge authorization exists;
- `product-owner-merge-authority=SUCCESS`;
- final race-check confirms unchanged head, unchanged canonical base relationship, exact one-file envelope, and `behind_by=0`;
- squash merge uses the exact authorized head SHA;
- merged canonical `main` commit/tree/parent/signature are verified.

Only after this gate is published may the separately governed six-workflow source-preservation predecessor begin. Publication of this gate does not itself authorize source implementation.
