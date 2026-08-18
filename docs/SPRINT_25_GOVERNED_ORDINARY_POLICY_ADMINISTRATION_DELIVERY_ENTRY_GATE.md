# Sprint 25 Entry Gate — Governed Ordinary Policy Administration Delivery Foundation

## Identity and authority

- Product: `oneQay`
- Engineering entity: **Lab | zefry**
- Repository: `labzefry/oneQay`
- Canonical branch: `main`
- Exact base: `c830e0e61cb9f379406aee5e583075a4ff40e719`
- Exact base tree: `51ae80b5613caee32f3be3be217c083b11dde79c`
- Sprint 24: **COMPLETE / IMPLEMENTED / PUBLISHED**
- Post-Sprint 24 canonical reconciliation: **PUBLISHED**
- Production readiness: **NO-GO / NOT AUTHORIZED**

GitHub remains the Single Source of Truth.

This document authorizes **Sprint 25 — Governed Ordinary Policy Administration Delivery Foundation** for bounded Local/Test/CI implementation after this documentation-only entry gate is published.

Independent review is not an additional mandatory gate under the current Product Owner continuation model unless the Product Owner explicitly reactivates it. Exact-head Product Owner authority, exact changed-file scope, required CI, tenant isolation, fail-closed runtime controls, and repository protection remain mandatory.

Attribution: **Lab | zefry**

## Why Sprint 25

Sprint 22 already provides a durable ordinary role/permission policy-administration Application service with:

- closed mutation operations;
- durable authorization checks;
- scope containment;
- protected-control rejection;
- verified-target eligibility;
- tenant-local idempotency;
- append-only mutation evidence;
- transaction-bound writes.

Sprint 23 provides one-time initial protected-control bootstrap and Sprint 24 provides a separate protected-control delegation/revocation lifecycle. Those protected-control paths intentionally remain outside ordinary policy administration.

The remaining bounded gap is a first-party HTTP delivery boundary for the **ordinary Sprint 22 capability only**.

Sprint 25 therefore adds a Local/Test/CI first-party session-based delivery foundation without creating a new authorization model, new policy persistence model, new protected-control capability, new authentication provider, or Production control plane.

## Existing service remains authoritative

Sprint 25 must call the existing:

`DurablePolicyAdministrationService`

for every authorized ordinary policy mutation.

Delivery code must not reproduce, bypass, replace, or partially reimplement Sprint 22 authorization logic.

Delivery code must not write directly to:

- `oneqay_roles`;
- `oneqay_role_permissions`;
- tenant/organization/outlet/device role-assignment tables;
- `oneqay_policy_mutations`;
- Sprint 23 initial-provisioning evidence;
- Sprint 24 protected-control lifecycle evidence.

No Delivery class may call `DB`, `Schema`, raw PDO, mysqli, query-builder mutation methods, SQL strings, or migration code.

## First-party session boundary

Sprint 25 does **not** authorize a new login, password, TOTP, bearer-token, API-key, personal-access-token, admin-secret, magic-link, or external identity-provider flow.

The delivery boundary consumes only server-owned first-party Laravel session state that was established outside Sprint 25.

The canonical bounded session attributes for the qualification foundation are:

- authenticated platform identity ID;
- tenant ID;
- organization ID;
- optional outlet ID;
- optional device ID.

The exact PHP session key names may be implementation constants, but they must be namespaced under `oneqay.auth.` and must not be accepted from request payload, query parameters, HTTP headers, route parameters, or cookies other than the framework-managed session cookie itself.

Sprint 25 creates **no endpoint that writes or bootstraps these authentication/context session attributes**.

Disposable Local/Test/CI regression may seed server-side session state directly through the framework test harness. That test mechanism is not Production authentication authority.

## Server verification of session-derived context

Possession of syntactically valid session values is not sufficient authority.

For every Sprint 25 mutation request, middleware must reconstruct bounded server-side value objects and then require durable verification through existing primitives:

- `ServerVerifiedPlatformIdentity`;
- `ServerVerifiedTenantContext`;
- `TenantMembershipVerifier` using `LaravelTenantMembershipVerifier`;
- `OrganizationalRelationshipVerifier` using `LaravelOrganizationalRelationshipVerifier`;
- `EnterOrganizationalContext`;
- request-scoped `TenantContextStore`;
- request-scoped `OrganizationalContextStore`.

The resulting `VerifiedOrganizationalContext` is the **only actor context** supplied to the policy-administration delivery service.

Raw request values must never be converted directly into `VerifiedPlatformIdentity`, `VerifiedTenantContext`, or `VerifiedOrganizationalContext` authority.

The middleware must clear tenant and organizational request-scoped context in `finally` semantics after each request, including failures.

## Runtime boundary

Sprint 25 delivery is permitted only when the runtime class is one of:

- `local`;
- `test`;
- `ci`.

The route must fail closed outside that allowlist. Preview and Production must not receive an active policy-administration delivery route.

This delivery guard is additional defense in depth. Existing durable repositories and transaction boundaries continue to enforce persistence-enabled + Local/Test/CI restrictions independently.

`ONEQAY_PERSISTENCE_ENABLED=false` remains the default.

## Web session and CSRF semantics

Sprint 25 must use Laravel's existing `web` middleware group.

The mutation endpoint must use **POST** only.

CSRF protection must remain active. Sprint 25 must not:

- exclude the route from CSRF verification;
- add CSRF exceptions;
- accept CSRF tokens from custom bypass headers;
- disable session regeneration protections;
- introduce stateless bearer authentication.

Regression proof must demonstrate that a mutation request lacking a valid CSRF token is rejected by the framework before the mutation service is reached.

## No authentication bootstrap in Sprint 25

Sprint 25 does not solve interactive authentication establishment.

A missing, incomplete, malformed, or unverifiable server-side session context must fail closed with no policy mutation journal row and no durable policy state change.

There is no fallback to:

- Technical Preview synthetic identity;
- `platform-superadmin`;
- updater authority;
- first user;
- environment owner;
- request-supplied tenant;
- header-supplied identity;
- a hard-coded administrator.

## Single mutation endpoint

Sprint 25 authorizes exactly one ordinary mutation route:

`POST /administration/policy/mutations`

The canonical route name is:

`policy-administration.mutate`

No GET mutation route, batch mutation route, bulk import, generic command executor, GraphQL mutation, public API, webhook, CLI command, background-job mutation entrypoint, or UI administration page is authorized.

The response may be a bounded first-party JSON response. It must not expose exception messages, SQL, stack traces, foreign-tenant identifiers, secret material, session contents, or internal database details.

## Closed request vocabulary

The request body is bounded to these semantic fields only:

- `mutation_id`;
- `operation`;
- `role`;
- optional `permission` only for permission operations;
- optional `target_identity` only for assignment/revocation operations.

Tenant ID, organization ID, outlet ID, device ID, actor identity ID, authorization permission, role table, assignment table, database name, model class, repository class, service class, SQL, and arbitrary scope objects must **not** be accepted from the request body.

Unknown request fields may be ignored only if the canonical mapper explicitly projects the closed allowlist before processing; strict rejection is preferred for the disposable foundation. In either case, unknown fields can never influence authority or persistence.

## Closed ordinary operation vocabulary

Sprint 25 may map only the existing Sprint 22 operation vocabulary:

- `role.create`;
- `permission.grant`;
- `permission.revoke`;
- `role.assign.tenant`;
- `role.assign.organization`;
- `role.assign.outlet`;
- `role.assign.device`;
- `role.revoke.tenant`;
- `role.revoke.organization`;
- `role.revoke.outlet`;
- `role.revoke.device`.

No free-form operation is accepted.

The existing `PolicyMutationOperation` remains the canonical validator.

The operation's scope is derived from the already verified actor context by existing `DurablePolicyMutation` factories. Request-supplied scope identifiers are prohibited.

## Canonical mutation construction

Sprint 25 introduces an Application-layer delivery command/mapper boundary that converts the closed delivery vocabulary into existing Sprint 22 value objects and `DurablePolicyMutation` factories.

The mapper must use existing canonical value objects:

- `PolicyMutationId`;
- `PolicyMutationOperation`;
- `RoleIdentifier`;
- `PermissionIdentifier`;
- `PlatformIdentityId`.

Malformed values fail closed before durable mutation.

The mapper must not accept arbitrary PHP class names, callable names, method names, model names, table names, repository names, or service names.

## Operation-specific payload rules

### `role.create`

Required:

- `mutation_id`;
- `operation`;
- `role`.

Forbidden:

- `permission`;
- `target_identity`.

### `permission.grant` and `permission.revoke`

Required:

- `mutation_id`;
- `operation`;
- `role`;
- `permission`.

Forbidden:

- `target_identity`.

### role assignment/revocation operations

Required:

- `mutation_id`;
- `operation`;
- `role`;
- `target_identity`.

Forbidden:

- `permission`.

Payload-shape violations fail before `DurablePolicyAdministrationService::apply()`.

## Protected-control preservation

Sprint 25 is ordinary policy administration only.

Existing Sprint 22 controls remain authoritative and must continue to reject:

- grant of `authorization.policy.manage`;
- revoke of `authorization.policy.manage`;
- assignment of a role carrying `authorization.policy.manage`;
- revocation of a role carrying `authorization.policy.manage`;
- any ordinary mutation of the canonical protected control role.

Sprint 25 must not special-case, override, catch-and-retry, or translate a protected-control rejection into an allowed mutation.

## Sprint 23 exclusion

The following must remain unreachable from Sprint 25 delivery:

- `InitialTenantAdministratorProvisioningService`;
- `InitialTenantAdministratorProvisioningAuthority`;
- `PreauthorizedInitialTenantAdministratorProvisioningAuthority`;
- `LaravelInitialTenantAdministratorProvisioningRepository` as a delivery dependency.

No bootstrap/provisioning route or request operation is authorized.

## Sprint 24 exclusion

The following must remain unreachable from Sprint 25 delivery:

- `ProtectedControlAdministratorLifecycleService`;
- `ProtectedControlAdministratorLifecycleRepository`;
- `LaravelProtectedControlAdministratorLifecycleRepository` as a delivery dependency;
- `control.administrator.delegate`;
- `control.administrator.revoke`.

No protected-control delegation or revocation route is authorized.

## Emergency recovery remains separate

A tenant with no remaining protected control principal remains outside Sprint 25 authority.

No emergency recovery token, platform override, updater recovery authority, database rewrite, environment switch, implicit owner, support-user escalation, or break-glass path is authorized.

Emergency protected-control recovery remains separately unresolved.

## Error mapping

Delivery error responses must be bounded and non-oracular.

At minimum:

- missing/unqualified session context -> generic authentication/context denial;
- malformed closed payload -> generic validation rejection;
- Sprint 22 authorization/protected-control/target/scope rejection -> generic policy mutation denial;
- persistence/runtime/storage/transaction failures -> generic mutation failure.

The response must preserve a bounded correlation identifier when available but must not expose internal exception detail.

Exact HTTP status mapping may use conventional 4xx/5xx values, but tests must prove denied operations do not mutate durable state or append unauthorized journal evidence.

## Idempotency preservation

Sprint 25 introduces no new idempotency mechanism.

The existing tenant-local Sprint 22 `PolicyMutationId` + canonical mutation fingerprint remains authoritative.

Exact replay returns the existing deterministic prior outcome. Same tenant + same mutation ID + conflicting payload remains a conflict. Same textual mutation ID across different tenants remains independent.

The HTTP layer must not generate or silently replace a client-supplied mutation ID.

## No new schema

Sprint 25 authorizes **zero migrations**.

The canonical migration set must remain exactly #1–#6:

1. `0000_00_00_000001_create_foundational_context_graph.php`;
2. `0000_00_00_000002_create_organizational_access_grants.php`;
3. `0000_00_00_000003_create_scoped_role_permission_policy.php`;
4. `0000_00_00_000004_create_policy_mutation_journal.php`;
5. `0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php`;
6. `0000_00_00_000006_create_protected_control_administrator_mutation_journal.php`.

Migrations #1–#6 are immutable under Sprint 25.

## No dependency changes

Sprint 25 authorizes no Composer or npm manifest/lockfile changes.

The implementation must use the currently locked Laravel/PHP/first-party application stack.

## Technical Preview preservation

Technical Preview remains:

**NO_SCHEMA_CHANGE**.

Sprint 25 ordinary policy-administration delivery must not be mounted beneath `/technical-preview`, referenced by `TechnicalPreviewController`, or made reachable through Preview Application/Delivery classes.

Technical Preview release artifact migration exclusion and deterministic `NO_SCHEMA_CHANGE` proof remain mandatory.

## Production and updater preservation

Production remains:

**NO-GO / NOT AUTHORIZED**.

Updater remains:

**DISABLED / UNWIRED**.

Sprint 25 policy-administration authority is tenant application authority only. It does not grant release, deployment, rollback, updater, infrastructure, platform, or host-management authority.

## Exact authorized implementation envelope

After this gate is published, Sprint 25 implementation is authorized to change **exactly 17 paths** and no others:

1. `apps/web/app/Application/Authorization/PolicyAdministrationDeliveryCommand.php`
2. `apps/web/app/Application/Authorization/PolicyAdministrationDeliveryService.php`
3. `apps/web/app/Application/Authorization/PolicyAdministrationDeliveryViolation.php`
4. `apps/web/app/Delivery/Http/Authorization/PolicyAdministrationController.php`
5. `apps/web/app/Delivery/Http/Middleware/RequirePolicyAdministrationSessionContextMiddleware.php`
6. `apps/web/app/Providers/AppServiceProvider.php`
7. `apps/web/routes/web.php`
8. `apps/web/tests/policy-administration-delivery.php`
9. `.github/workflows/m7-2-tenant-isolation-regression.yml`
10. `.github/workflows/m7-3-identity-org-context-regression.yml`
11. `.github/workflows/m7-5-preview-db-qualification-regression.yml`
12. `.github/workflows/sprint21-role-permission-policy-regression.yml`
13. `.github/workflows/sprint22-policy-administration-regression.yml`
14. `.github/workflows/sprint23-initial-tenant-admin-provisioning-regression.yml`
15. `.github/workflows/sprint24-protected-control-admin-lifecycle-regression.yml`
16. `.github/workflows/sprint25-policy-administration-delivery-regression.yml`
17. `docs/ORDINARY_POLICY_ADMINISTRATION_DELIVERY_FOUNDATION.md`

Any newly discovered preservation dependency outside this exact envelope requires a separately published documentation-only preservation supplement before that path may be modified.

## Existing files that are explicitly immutable

Sprint 25 must not modify:

- `DurablePolicyAdministrationService.php`;
- `DurablePolicyAdministrationRepository.php`;
- `LaravelDurablePolicyAdministrationRepository.php`;
- `DurablePolicyMutation.php`;
- `PolicyMutationOperation.php`;
- Sprint 23 provisioning source;
- Sprint 24 protected-control lifecycle source;
- migrations #1–#6;
- dependency manifests/locks;
- Preview controllers/services;
- updater source.

The delivery foundation adapts **to** those existing boundaries rather than changing them.

## Composition-root changes

`AppServiceProvider.php` may be changed only to bind existing durable verification contracts needed for server-side context reconstruction:

- `TenantMembershipVerifier` -> `LaravelTenantMembershipVerifier`;
- `OrganizationalRelationshipVerifier` -> `LaravelOrganizationalRelationshipVerifier`;
- `OrganizationalContextStore` -> `RequestOrganizationalContextStore`.

Existing repository bindings must be preserved.

No Sprint 23 bootstrap authority provider may be bound.

No Production-only provider, platform-superadmin provider, updater authority provider, or permissive fallback verifier may be added.

## Route changes

`apps/web/routes/web.php` may be changed only to add the single Sprint 25 POST route and required Sprint 25 middleware.

Existing health, system-update, Technical Preview, and database-qualification routes must preserve their semantics.

No Sprint 25 GET administration UI is authorized.

## Workflow preservation

The seven existing durable/isolation workflows in the authorized envelope may change only to:

- recognize the exact Sprint 25 17-path source envelope where exact-envelope enforcement requires it;
- trigger on the new controller/middleware/test/workflow/foundation document;
- run `policy-administration-delivery.php` in addition to prior regressions;
- assert migrations remain exactly #1–#6;
- assert Sprint 25 cannot reference Sprint 23/Sprint 24 protected-control delivery dependencies;
- assert Preview/Production/updater boundaries remain unchanged.

They must not remove prior Sprint 21–24, tenant-isolation, identity-context, Preview, updater, dependency, or security assertions merely to make Sprint 25 pass.

## Dedicated Sprint 25 regression

A new workflow:

`.github/workflows/sprint25-policy-administration-delivery-regression.yml`

must enforce at least:

- exact 17-file changed-path envelope;
- no dependency changes;
- zero migration changes and exact six-migration set;
- PHP syntax;
- Application layer remains framework/database independent;
- exactly one Sprint 25 POST route;
- no Sprint 25 route under Technical Preview;
- Local/Test/CI runtime allowlist;
- session context attributes are not read from request input/header/query;
- durable membership and organizational relationship verification;
- CSRF preservation;
- closed request vocabulary;
- closed Sprint 22 operation vocabulary;
- ordinary positive mutation proof;
- exact replay proof;
- conflicting replay proof;
- unauthorized actor denial;
- cross-tenant target denial;
- scope-containment denial;
- protected-control permission denial;
- protected-control role assignment/revocation denial;
- malformed/unknown operation denial;
- missing session denial;
- invalid session context denial;
- request context cleanup;
- Sprint 23 delivery exclusion;
- Sprint 24 delivery exclusion;
- Preview exclusion;
- Production exclusion;
- updater separation;
- prior Sprint 21–24 disposable regressions remain green.

## Disposable database qualification

Sprint 25 regression may create a disposable SQLite database, run existing migrations #1–#6, and seed synthetic tenant/identity/access/policy facts required for HTTP qualification.

It must not:

- mutate a shared or persistent database;
- use real customer data;
- create a seventh migration;
- weaken foreign keys;
- disable tenant isolation;
- write outside the disposable test target.

## Session regression requirements

The disposable regression must prove:

1. no authenticated session -> mutation denied and no journal row;
2. malformed session identity/tenant/context -> denied and no journal row;
3. syntactically valid but non-member identity -> denied and no journal row;
4. valid server session + durable membership + durable relationship + valid CSRF -> ordinary authorized mutation can succeed;
5. session context values cannot be replaced by request body actor/tenant/scope fields;
6. request-scoped verified contexts are cleared after success and failure.

## CSRF regression requirements

The regression must prove the actual route remains in Laravel's web middleware stack and is not excluded from CSRF protection.

At minimum:

- stateful request without a valid CSRF token is rejected;
- the same bounded request with a valid framework session token proceeds to Sprint 25 context and authorization checks;
- no Sprint 25 source disables or bypasses `ValidateCsrfToken` behavior.

## Ordinary positive-control matrix

Qualification should cover representative allowed ordinary operations without requiring every operation to produce a unique database fixture.

At minimum prove:

- ordinary role create;
- ordinary permission grant;
- an exact-scope ordinary role assignment;
- corresponding ordinary revocation;
- deterministic replay.

The dedicated mapper must nevertheless accept only the complete closed Sprint 22 operation vocabulary listed in this gate.

## Denial matrix

At minimum prove no durable mutation for:

- missing session;
- malformed session context;
- foreign tenant identity;
- actor lacking `authorization.policy.manage` for the requested scope;
- narrower actor attempting broader mutation;
- protected control permission mutation;
- protected control role assignment/revocation;
- Sprint 24 operation string;
- unknown operation string;
- malformed mutation ID;
- malformed role/permission/target identifier;
- payload-shape mismatch;
- Production runtime;
- Preview runtime.

## Logging and privacy

Sprint 25 must not log request bodies, session contents, CSRF tokens, cookies, authorization headers, identity secrets, exception messages, or database errors.

Existing `SafeRequestObservationMiddleware` remains the logging boundary. Sprint 25 source must not add a separate sensitive request logger.

Any response identity or target identifier must be limited to what is strictly required; the initial foundation should return only bounded outcome/correlation metadata.

## Response contract

A successful mutation response should be minimal and may contain only bounded fields such as:

- `status`;
- `outcome`;
- `correlation_id`.

It must not return role-assignment tables, tenant membership graphs, journal payload fingerprints, actor session data, raw mutation payloads, stack traces, or SQL information.

## Source acceptance gate

A future Sprint 25 source PR is merge-eligible only when all of the following are true on one exact final head:

- exact canonical base lineage is preserved;
- changed files are exactly the authorized 17 paths;
- branch is behind 0;
- all required repository workflows are `SUCCESS`;
- dedicated Sprint 25 workflow is `SUCCESS`;
- no prior assertion is skipped or weakened;
- no dependency or migration change exists;
- `product-owner-merge-authority` is `SUCCESS` for the exact final head;
- Product Owner authorization comment names the exact PR and exact head SHA;
- squash merge uses expected-head protection.

A CI failure must be fixed within this envelope or through a separately published preservation supplement. No test, security check, or fail-closed control may be disabled merely to obtain green CI.

## Explicit exclusions

Sprint 25 does **not** authorize:

- interactive authentication/login implementation;
- user registration;
- password reset;
- TOTP enrollment;
- Sprint 23 bootstrap delivery;
- Sprint 24 protected-control delivery;
- emergency recovery;
- administrator UI;
- public REST API;
- mobile API;
- GraphQL;
- batch/bulk policy mutation;
- Production activation;
- Preview control-plane activation;
- schema migration;
- dependency changes;
- updater activation;
- platform-superadmin;
- arbitrary SQL/model/repository dispatch;
- real customer data.

## Entry-gate declaration

After this documentation-only gate is itself published:

- Sprint 25 implementation authority becomes active only for the exact 17 paths above;
- Sprint 22 ordinary policy service remains the mutation authority;
- Sprint 23 bootstrap remains separate and unreachable;
- Sprint 24 protected-control lifecycle remains separate and unreachable;
- migrations remain exactly #1–#6;
- Technical Preview remains `NO_SCHEMA_CHANGE`;
- Production remains `NO-GO / NOT AUTHORIZED`;
- updater remains `DISABLED / UNWIRED`;
- emergency protected-control recovery remains unresolved and unauthorized.

GitHub remains the Single Source of Truth.

Attribution: **Lab | zefry**
