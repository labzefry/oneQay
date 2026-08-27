# Sprint41 First-Party Identity Authentication Eligibility Administration Schema / Source Envelope Gate

## Status

**SCHEMA/SOURCE-ENVELOPE GATE ONLY / NO SOURCE IMPLEMENTATION AUTHORITY**

Date: **2026-08-27**

This document follows the published Sprint41 entry gate and freezes the exact schema decision, authorization contract, audit/idempotency model, route shape, source-preservation predecessor, and only changed-file envelope that may be used by a later Sprint41 source implementation.

Selected concern:

**First-Party Identity Authentication Eligibility Administration Foundation — disable-only**

This gate does not implement Sprint41 runtime behavior. It does not mutate canonical data or apply schema anywhere. It does not authorize re-enable/reactivation, Technical Preview activation, Production activation, updater wiring, deployment, or release.

Attribution: **Lab | zefry**

## 1. Canonical starting point

This gate is prepared from canonical `main` after publication of the Sprint41 schema/source-gate preservation predecessor PR #304, exact trigger-preservation correction PR #306, and Sprint38 canonical-state correction PR #308.

Canonical base:

- commit: `70ec2afcaa7073011ac9410260e326f572bfe4d8`;
- tree: `d01a9371ed1f3b0cc0c9fb10d7efdd5ec55b37d3`;
- signature: **verified / valid**;
- entry-gate publication: PR #303;
- schema/source-gate preservation predecessor: PR #304;
- exact Sprint38/Sprint39 gate-trigger preservation correction: PR #306;
- Sprint38 canonical post-Sprint40 state correction for this gate: PR #308.

The preservation predecessor recognizes this exact one-file gate successor:

`docs/SPRINT_41_FIRST_PARTY_IDENTITY_AUTHENTICATION_ELIGIBILITY_ADMINISTRATION_SCHEMA_SOURCE_ENVELOPE_GATE.md`

Sorted newline-terminated one-path SHA-256:

`1a2c18a7ec9b363a9b2ef3bc6ff86366ab58f4812e8968d83642e6ce359f48b2`

Unknown successor shapes remain fail-closed.

## 2. Inherited authority semantics

The later source implementation must preserve the published Sprint41 entry-gate decision without widening it.

Sprint41 selects only an administrative state transition:

**`first_party_authentication_enabled: true -> false`**

The later implementation must preserve:

1. exact authenticated actor derived server-side;
2. exact current tenant authority derived server-side;
3. exact target identity as an addressing value only, never authorization evidence;
4. target must belong to the actor's exact authorized tenant;
5. cross-tenant targeting fails closed;
6. target protected-control principals remain excluded;
7. actor cannot disable itself through this concern;
8. already-disabled target remains disabled;
9. retry/replay never re-enable the target;
10. concurrency converges only toward disabled state;
11. password credentials remain unchanged;
12. credential epoch remains unchanged;
13. TOTP secret/factor epoch remain unchanged;
14. tenant membership and organizational grants remain unchanged;
15. no replacement logical session authority is created;
16. Sprint40 request-time eligibility revalidation remains the consumer of the resulting disabled state;
17. re-enable/reactivation remains separately governed and not selected.

## 3. Canonical persistence finding

Canonical source already contains migration #14:

`apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php`

and the exact server-owned state:

`oneqay_identities.first_party_authentication_enabled`

That state is sufficient for the disable-only business mutation itself and must be reused.

Canonical migrations #1 through #14 also show dedicated mutation/audit journals for policy administration and protected-control administrator lifecycle. Those journals have domain-specific payloads and must not be repurposed for identity-authentication eligibility administration.

Therefore Sprint41 must not overload:

- `oneqay_policy_mutations` or its canonical equivalent;
- `oneqay_protected_control_admin_mutations`;
- identity recovery audit tables;
- session audit tables;
- credential/factor persistence.

## 4. Schema determination

Sprint41 schema classification is frozen as:

**MINIMAL FORWARD-ONLY JOURNAL SCHEMA CHANGE / MIGRATION #15 SELECTED**

Migration #14 and its eligibility field remain immutable.

Migration #15 is selected with the exact path:

`apps/web/database/migrations/0000_00_00_000015_create_identity_authentication_eligibility_administration_journal.php`

Migration #15 may create only one table:

`oneqay_identity_authentication_eligibility_mutations`

The table is limited to:

- `tenant_id`;
- `mutation_id`;
- `actor_identity_id`;
- `target_identity_id`;
- `operation`;
- `payload_fingerprint`;
- `outcome`;
- `occurred_at_unix`;
- primary key `(tenant_id, mutation_id)`.

Allowed values are frozen as:

- `operation = disable`;
- `outcome = applied | no_change`.

No reason text, free-form metadata, password/credential evidence, TOTP material, session secret, IP/device fingerprint, external-directory identifier, timestamp pair, reactivation state, auxiliary table, trigger, generated column, or lifecycle scheduler is selected.

The journal exists only to provide deterministic idempotency, concurrency/replay binding, and secret-free durable audit evidence for this exact disable-only mutation.

Migration #15 must be forward-only and must use the repository's canonical rollback-denial rule.

Migrations #1 through #14 remain immutable.

Migration #15 is **NOT AUTHORIZED FOR TECHNICAL PREVIEW OR PRODUCTION** by this gate.

## 5. Exact authorization contract

Sprint41 selects no new permission identifier.

The exact actor permission is the already-canonical protected-control permission:

`AdministrationPermission::MANAGE`

The later implementation must prove that the actor currently holds exact tenant-scoped control authority through canonical role-permission state.

This reuse is authorization composition only; Sprint41 must not mutate policy definitions, role assignments, or protected-control lifecycle state.

The target must be an ordinary identity for this concern.

The later implementation must deny:

- actor equals target;
- target outside actor tenant;
- missing target;
- target holding the canonical protected-control role/permission;
- malformed actor or target identity;
- missing/malformed authorization evidence;
- storage/runtime unavailability.

Protected-control identity disablement remains separately governed.

## 6. Privileged request context

The future delivery route must reuse the existing canonical policy-administration protected request context.

It must require:

- active first-party session authority;
- canonical current tenant/organization context;
- canonical policy-administration authorization context;
- existing privileged step-up behavior where required by the canonical middleware;
- existing throttling conventions.

No bypass middleware, alternate support role, API key, caller-provided tenant selector, or feature flag is selected.

## 7. Frozen route and payload

The later source implementation is allowed exactly one new protected route:

`POST /administration/identities/{identity_id}/authentication-disablement`

Frozen route name:

`identity.authentication-eligibility.disable`

The route parameter `identity_id` is addressing input only and must be verified against server-owned tenant and authorization evidence.

Request payload may contain only:

`mutation_id`

No boolean eligibility value is accepted.

No `enabled`, `disabled`, reason, actor, tenant, role, permission, session, organization, outlet, device, reactivation, or force flag is authorized in the payload.

The exact mutation identifier is parsed through a dedicated application value object and is bound to the exact actor + tenant + target + operation fingerprint.

## 8. Frozen application contracts

The later implementation must introduce:

`App\Application\Identity\IdentityAuthenticationEligibilityMutationId`

`App\Application\Identity\FirstPartyIdentityEligibilityAdministrationRepository`

`App\Application\Identity\FirstPartyIdentityEligibilityAdministrationService`

`App\Application\Identity\FirstPartyIdentityEligibilityAdministrationViolation`

The service exposes one business operation only:

**disable an exact eligible ordinary first-party identity**

It must not expose enable/reactivate, bulk mutation, arbitrary state assignment, session revocation, credential mutation, factor mutation, or grant mutation.

The service must use the existing canonical persistence transaction abstraction and canonical administration clock rather than introducing a second transaction or clock system.

## 9. Frozen durable repository behavior

The later adapter:

`App\Infrastructure\Identity\LaravelFirstPartyIdentityEligibilityAdministrationRepository`

must:

- fail closed unless persistence and canonical Local/Test/CI runtime boundaries are valid;
- verify actor tenant-scoped `AdministrationPermission::MANAGE`;
- verify exact target row belongs to the same tenant;
- deny actor == target;
- deny target protected-control authority;
- bind `mutation_id` to a deterministic fingerprint of tenant, actor, target, and operation;
- return prior deterministic outcome only when an existing journal row has the exact same fingerprint;
- inside one canonical transaction, perform a conditional `true -> false` update on the exact identity row;
- report `applied` only when the exact enabled row transitioned to disabled;
- report `no_change` only when the exact identity was already canonically disabled;
- insert exactly one matching journal row for a fresh mutation;
- fail closed on journal conflict, storage error, malformed state, contradictory row count, or authorization drift;
- never perform `false -> true`;
- never upsert a missing identity;
- never alter another tenant or identity.

The identity update and audit journal insertion must commit atomically.

## 10. Concurrency and replay model

Sprint41 freezes deterministic mutation behavior:

- same `mutation_id` + same fingerprint replays the prior outcome;
- same `mutation_id` + different fingerprint fails with mutation conflict;
- two distinct concurrent mutation IDs targeting the same enabled identity may have at most one `applied` state transition;
- every successful fresh mutation journal records its deterministic outcome;
- concurrency must never restore enabled state;
- stale retries cannot overwrite canonical disabled state.

No distributed queue, background worker, delayed job, or asynchronous state convergence is selected.

## 11. Audit boundary

The mutation journal is the canonical Sprint41 durable audit evidence.

It records only server-derived identifiers, operation, deterministic payload fingerprint, outcome, and occurred-at Unix time.

The source must never journal:

- passwords;
- password hashes;
- TOTP secrets;
- recovery secrets/codes;
- session identifiers or authority secrets;
- CSRF tokens;
- request bodies beyond the canonical mutation identifier;
- arbitrary operator notes.

No additional audit event table or external audit integration is selected.

## 12. Response and failure contract

The future controller may return only a minimal existing-style safe JSON envelope containing:

- `status`;
- deterministic `outcome` on success;
- correlation ID.

Failure must use a generic administration rejection code and must not disclose whether a cross-tenant or protected target exists.

The exact internal violation vocabulary may distinguish invalid payload, authorization denied, protected target, mutation conflict, persistence unavailable, and storage/transaction failure while the public response remains safe.

## 13. Preserved Sprint36-Sprint40 semantics

Sprint41 must preserve without reinterpretation:

- Sprint36 durable session authority, inventory, revoke-one, revoke-others, canonical logout;
- Sprint37 self-scoped tenant revoke-all;
- Sprint38 idle TTL **7200 seconds** and absolute TTL **43200 seconds**;
- Sprint39 current tenant membership and organization/outlet/device revalidation;
- Sprint40 request-time current identity eligibility revalidation.

Sprint41 does not administrator-revoke another identity's sessions.

A disabled target is denied on its next protected request by Sprint40 current-state revalidation.

## 14. Required source-preservation predecessor

Before opening the later Sprint41 source implementation PR, a separately qualified preservation predecessor must update exactly these seven workflow paths:

1. `.github/workflows/m7-5-preview-release-artifact.yml`
2. `.github/workflows/sprint35-privileged-totp-recovery-regression.yml`
3. `.github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml`
4. `.github/workflows/sprint37-first-party-all-session-termination-regression.yml`
5. `.github/workflows/sprint38-first-party-session-absolute-lifetime-regression.yml`
6. `.github/workflows/sprint39-first-party-session-organizational-access-revalidation-regression.yml`
7. `.github/workflows/sprint40-first-party-session-identity-disablement-revalidation-regression.yml`

Sorted newline-terminated seven-path SHA-256:

`f3f8b3ad0cca378307000ca242c4b9a4a8a7ab967d1649a5cd6408837001364c`

That predecessor may change only exact recognition/trigger/historical-isolation logic required for the frozen Sprint41 source successor.

It must not implement application behavior or create migration #15.

Unknown successor shapes remain fail-closed.

## 15. Exact later Sprint41 source envelope

After the required source-preservation predecessor is published, the Sprint41 source implementation PR is frozen to exactly these twelve paths:

1. `.github/workflows/sprint41-first-party-identity-authentication-eligibility-administration-regression.yml`
2. `apps/web/app/Application/Identity/FirstPartyIdentityEligibilityAdministrationRepository.php`
3. `apps/web/app/Application/Identity/FirstPartyIdentityEligibilityAdministrationService.php`
4. `apps/web/app/Application/Identity/FirstPartyIdentityEligibilityAdministrationViolation.php`
5. `apps/web/app/Application/Identity/IdentityAuthenticationEligibilityMutationId.php`
6. `apps/web/app/Delivery/Http/Identity/FirstPartyIdentityEligibilityAdministrationController.php`
7. `apps/web/app/Infrastructure/Identity/LaravelFirstPartyIdentityEligibilityAdministrationRepository.php`
8. `apps/web/app/Providers/AppServiceProvider.php`
9. `apps/web/database/migrations/0000_00_00_000015_create_identity_authentication_eligibility_administration_journal.php`
10. `apps/web/routes/web.php`
11. `apps/web/tests/first-party-identity-authentication-eligibility-administration.php`
12. `docs/FIRST_PARTY_IDENTITY_AUTHENTICATION_ELIGIBILITY_ADMINISTRATION_FOUNDATION.md`

Sorted newline-terminated twelve-path SHA-256:

`b2c5fc10a8baa2d56991d6dbd36b0407159d70953654ef322a9a11d23660489b`

No other path belongs to the authorized Sprint41 source envelope.

## 16. Role of authorized source paths

### 16.1 Sprint41 regression workflow

The new workflow must enforce the exact twelve-path fingerprint, migration #15 exclusivity, immutable migrations #1-#14, Local/Test/CI-only execution, authorization/tenant isolation, protected-target denial, replay/concurrency, durable journal evidence, disable-only semantics, no reactivation, and preserved Sprint36-Sprint40 regressions.

### 16.2 Application repository/service/violation/value object

These files may implement only the frozen disable-only command, deterministic mutation ID binding, fail-closed violation model, and repository contract.

### 16.3 HTTP controller

The controller may expose only the frozen POST route behavior, safe payload parsing, canonical current organizational actor context, and safe response envelope.

### 16.4 Durable adapter

The Laravel adapter may implement only exact tenant-control authorization, target eligibility/protected-control exclusion, conditional disable, replay/idempotency, and journal persistence described by this gate.

### 16.5 AppServiceProvider

Provider changes are limited to binding the new repository/service dependencies using existing connection, runtime, persistence, transaction, and clock primitives.

### 16.6 Migration #15

Migration #15 may create only the exact disablement mutation journal. It must not modify the eligibility column or any prior table.

### 16.7 Routes

`apps/web/routes/web.php` may add only the single frozen administration route with canonical active-session, policy-administration context, and throttle middleware.

### 16.8 Dedicated regression fixture

The dedicated fixture must prove at least:

- authorized ordinary target disable succeeds;
- exact field becomes false;
- another tenant is unchanged;
- another identity is unchanged;
- actor self-target fails;
- protected-control target fails;
- missing target fails;
- unauthorized actor fails;
- malformed mutation ID fails;
- exact replay returns deterministic prior outcome;
- conflicting reuse of mutation ID fails;
- concurrent disable converges and never re-enables;
- already-disabled target remains disabled;
- journal is secret-free and exact;
- credentials/factors/membership/grants are unchanged;
- Sprint40 denies subsequent protected use by the disabled target;
- no reactivation route or source path exists;
- migrations #1-#14 remain unchanged;
- migration #15 is not applied to Technical Preview or Production.

## 17. Explicit exclusions

The later Sprint41 source implementation must not add or mutate:

- re-enable/reactivation logic;
- bulk disablement;
- protected-control identity lifecycle;
- last-administrator/break-glass handling;
- administrator-targeted session revocation;
- credential/factor epoch mutation;
- password/TOTP/recovery mutation;
- tenant membership or organizational grants;
- new feature/environment arm;
- config mutation;
- dependency manifests/locks;
- external directory/HR/SSO lifecycle;
- UI pages;
- mobile/API token lifecycle;
- background workers;
- migrations #1-#14;
- migration #16 or later;
- Technical Preview activation;
- Production activation;
- updater wiring;
- deployment;
- release;
- phase-exit authority.

If implementation appears to require an excluded path or concern, source mutation must stop pending a separately governed envelope revision.

## 18. Runtime and activation boundary

Sprint41 source authority, if separately granted later, remains:

**Local/Test/CI only**

Technical Preview remains:

**NO SCHEMA APPLICATION / SPRINT40 NOT ACTIVATED**

Production remains:

**NO-GO / NOT AUTHORIZED**

Updater remains:

**DISABLED / UNWIRED**

Deployment and release remain:

**NOT AUTHORIZED**

## 19. Exit criteria for this gate stage

This schema/source-envelope gate is complete only when:

- this exact one-file document is qualified on its exact PR head;
- all materially triggered workflows succeed;
- queued/cancelled/no-runner/empty-job results are not treated as success;
- governance checks succeed;
- the PR is Ready;
- a top-level Product Owner exact-head merge authorization exists;
- `product-owner-merge-authority=SUCCESS`;
- final race-check confirms unchanged head, current main relationship, exact one-file envelope, and mergeability;
- squash merge uses `expected_head_sha`;
- post-merge canonical commit/tree/signature are verified.

Only after this gate is published may the separately governed seven-workflow source-preservation predecessor begin.

Publication of this gate does not itself authorize Sprint41 source implementation.

Attribution: **Lab | zefry**
