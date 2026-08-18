# Sprint 23 Entry Gate — Governed Initial Tenant Administrator Provisioning Foundation

## Identity and authority

- Product: `oneQay`
- Engineering entity: **Lab | zefry**
- Repository: `labzefry/oneQay`
- Canonical branch: `main`
- Exact base: `891dfcc635f7e97d27659da81d8eab0cfaa42877`
- Exact base tree: `ec06c98d70d2b86134c45a4704f73690066240a8`
- Sprint 22: **COMPLETE / IMPLEMENTED / PUBLISHED**
- Post-Sprint 22 canonical state reconciliation: **PUBLISHED**
- Production readiness: **NO-GO**

GitHub remains the Single Source of Truth.

Sprint 22 deliberately left first-administrator provisioning unresolved. It proved that durable policy administration can be secure only after an exact tenant-scoped principal already possesses `authorization.policy.manage`, while protected-control rules correctly prevent the general policy-administration path from manufacturing, transferring, weakening, or deleting that authority.

This document authorizes **Sprint 23 — Governed Initial Tenant Administrator Provisioning Foundation** for bounded Local/Test/CI implementation after this documentation-only entry gate is published.

Independent review is not an additional mandatory gate under the current Product Owner continuation model unless the Product Owner explicitly reactivates it. Exact-head Product Owner authority, required CI, exact changed-file scope, tenant isolation, fail-closed runtime controls, and repository protection remain mandatory.

No Preview/Production bootstrap, Production schema execution, recovery/replacement administrator flow, business-role catalog, UI/API administration surface, onboarding flow, customer-data persistence, deployment, Release, updater activation, or Production readiness is authorized.

Attribution: **Lab | zefry**

## Why Sprint 23

Sprint 22 established a secure durable authorization control plane but intentionally required an already-provisioned protected control principal. That is correct for normal administration, but it leaves a bootstrap dependency for a newly initialized tenant.

Sprint 23 closes only the **initial control-principal provisioning mechanics** gap. It does not create a generic administrator-management feature.

The result must be a one-time, tenant-bound, auditable, idempotent provisioning ceremony that creates exactly one initial tenant-scoped control role assignment without introducing an implicit Owner/Admin/Superadmin rule or a reusable bypass around Sprint 22.

## Exact resulting control authority

A successful Sprint 23 provisioning ceremony creates only the exact control-plane authority required by Sprint 22:

- exact control role identifier: `authorization-policy-administrator`;
- exact permission: `authorization.policy.manage`;
- exact assignment scope: tenant;
- exact assignee: the already-existing, verified platform identity authorized by the provisioning grant.

`authorization-policy-administrator` is a control-plane role, not a business-role catalog entry.

After provisioning, the role is a Sprint 22 **protected control role** because it carries `authorization.policy.manage`. The general Sprint 22 administration service therefore remains unable to grant, revoke, reassign, or permission-rewrite it.

No other permission or role assignment is created by Sprint 23.

## Explicit trust-root model

Sprint 23 must not infer bootstrap authority from application state.

Authority must come from an explicitly pre-authorized, out-of-band provisioning grant exposed through a framework-independent Application contract:

`InitialTenantAdministratorProvisioningAuthority`

The authorization decision is bound to an exact tuple:

- tenant ID;
- verified platform identity ID;
- canonical provisioning ID.

The tuple is evaluated by a dedicated authority adapter supplied through trusted composition.

The provisioning service itself accepts **no bearer secret, password, TOTP, session token, HTTP header, query parameter, cookie, raw request body, updater privilege, environment-superuser flag, or platform-superadmin status** as proof of bootstrap authority.

Knowledge of tenant/identity/provisioning identifiers alone is not authority. The authority adapter must independently contain the matching pre-authorized tuple.

Sprint 23 source does not wire a Production authority provider. A bounded pre-authorized tuple adapter may be instantiated directly by Local/Test/CI regression fixtures only. Any future real Preview/Production authority provider requires a separate gate and threat review.

## No bootstrap shortcuts

Sprint 23 must not add or reinterpret any of the following:

- `if no administrator exists then allow`;
- first-user elevation;
- implicit Owner/Admin/Superadmin role;
- tenant-superadmin wildcard;
- environment superuser;
- platform-superadmin inheritance;
- updater privilege reuse;
- bootstrap HTTP header;
- bootstrap URL/query token;
- hard-coded administrator identity;
- hard-coded tenant authority;
- default administrator created by migration/seed;
- unauthenticated route/controller/command authority;
- direct mutation based only on raw tenant or identity IDs.

Migration #5 contains no seed data and creates no administrator automatically.

## Verified identity prerequisite

The candidate administrator must already exist as a valid platform identity and must be represented to the Application service through existing `VerifiedPlatformIdentity` semantics.

The durable repository must independently prove that the identity belongs to the exact tenant before any role, permission, assignment, or provisioning-journal mutation occurs.

Sprint 23 does not create identities, tenants, organizations, outlets, devices, or identity memberships.

A foreign-tenant identity is denied even if its textual identity ID matches the provisioning request.

## Canonical provisioning ID

Every ceremony requires a canonical tenant-local `InitialTenantAdministratorProvisioningId`.

The identifier must be validated before durable mutation and must have deterministic canonical string semantics comparable to the existing governed mutation-ID pattern.

Semantics:

- same tenant + same provisioning ID + same canonical provisioning payload after a successful ceremony → deterministic replay of the prior successful outcome;
- same tenant + same provisioning ID + different identity/payload → stable provisioning conflict;
- any second successful initialization attempt for the same tenant under a different provisioning ID → `already_initialized` denial;
- same textual provisioning ID under another tenant → independent;
- malformed provisioning ID → rejected before storage mutation.

## Canonical provisioning payload and fingerprint

The canonical SHA-256 provisioning fingerprint binds exactly:

- tenant ID;
- verified identity ID;
- provisioning ID;
- exact role `authorization-policy-administrator`;
- exact permission `authorization.policy.manage`;
- exact tenant assignment scope.

It contains no credential, password, TOTP material, token, session value, raw request payload, request header, filesystem path, or database connection material.

## One-time initialization invariant

There may be at most one successful initial administrator provisioning record per tenant.

Before first-time application, the repository must fail closed if that tenant already contains any durable role carrying exact `authorization.policy.manage`, regardless of role identifier or assignee.

This prevents Sprint 23 from becoming a recovery, replacement, or authority-duplication mechanism.

Once the tenant has been initialized, future administrator delegation must use separately governed lifecycle mechanics; Sprint 23 may not be reused to create another protected principal.

Administrator loss/recovery is explicitly out of scope and remains unresolved for a later separately gated stage.

## Exact atomic mutation

On the only valid first-time path, Sprint 23 atomically creates exactly:

1. tenant role `authorization-policy-administrator` if absent and compatible;
2. exact role-permission relationship to `authorization.policy.manage`;
3. exact tenant-scoped role assignment to the verified target identity;
4. append-only initial-provisioning journal record.

All four durable effects are one transaction.

Any authorization, tenant relationship, role compatibility, existing-control-authority, assignment, journal, idempotency, or storage failure must roll back the entire ceremony.

No partial control role may survive a failed provisioning attempt.

## Dedicated exception boundary

Sprint 23 is the only bounded path authorized to create the first protected control role and its first tenant assignment.

It must **not** weaken or reuse `DurablePolicyAdministrationService` to do this, because Sprint 22 correctly forbids protected-control mutations.

Instead, Sprint 23 introduces a dedicated repository contract with no generic role/permission administration methods.

The exception is constrained to:

- exact first initialization only;
- exact role `authorization-policy-administrator`;
- exact permission `authorization.policy.manage`;
- exact tenant assignment;
- exact pre-authorized tenant + verified identity + provisioning ID;
- Local/Test/CI runtime only;
- one successful initialization record per tenant.

After that one-time transaction, all Sprint 22 protected-control restrictions remain fully effective.

## Authority verification and defense in depth

The Application service must verify the out-of-band authority **before** entering the transaction.

The Infrastructure transaction path must repeat the critical authority check using the same authority contract before durable mutation.

The durable path also independently revalidates:

- persistence enabled;
- runtime class;
- tenant existence;
- same-tenant verified identity existence;
- one-time initialization state;
- exact role/permission constants;
- replay/conflict state.

A denied authority attempt leaves no provisioning journal row.

## Authority adapter boundary

A new Infrastructure adapter may provide bounded pre-authorized tuple matching for Local/Test/CI regression:

`PreauthorizedInitialTenantAdministratorProvisioningAuthority`

It receives its allowed tuple set only through constructor composition. It does not read HTTP input and does not treat supplied request data as its own authority store.

The application composition root must **not** bind a permissive default authority and must not create a Production/Preview provisioning authority.

A missing authority remains a closed state: no delivery-visible bootstrap action exists.

This adapter is a qualification mechanism, not a Production credential model.

## Transaction and append-only journal

Sprint 23 reuses the existing `PersistenceTransaction` boundary.

One additive forward-only migration is authorized:

`apps/web/database/migrations/0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php`

Migrations #1–#4 remain immutable.

The migration creates exactly:

`oneqay_initial_tenant_admin_provisionings`

The table stores bounded initialization evidence only:

- `tenant_id`;
- `provisioning_id`;
- `identity_id`;
- `role_id`;
- `permission_id`;
- `payload_fingerprint`;
- `outcome`;
- `occurred_at_unix`.

Primary key: `tenant_id`.

The table must bind `identity_id` to the same tenant using the existing composite tenant/identity relationship.

`provisioning_id` is preserved as tenant-local replay evidence. One row per tenant makes successful initial provisioning structurally one-time.

Application code may never update/delete an existing provisioning-journal row.

The journal contains no passwords, TOTP data, sessions, bearer tokens, credentials, arbitrary request payloads, contact/customer PII, or secret authority material.

Canonical successful outcome is exactly:

`applied`

## Concurrency and conflict behavior

The one-row-per-tenant journal constraint is the durable uniqueness boundary for successful initialization.

The repository must preflight current initialization/control-authority state, then repeat required checks inside the transaction.

If competing initialization attempts race, at most one may commit. The loser must surface a bounded `already_initialized` or provisioning-conflict outcome after durable state is reconciled; it must never create a second protected assignment or leak a raw database uniqueness error.

No ownership-rewriting `upsert` or `updateOrInsert` is authorized.

## Stable failure boundary

Sprint 23 must provide bounded non-sensitive failures for at least:

- authorization denied;
- persistence disabled;
- runtime denied;
- invalid provisioning ID;
- verified identity mismatch;
- tenant relationship denied;
- already initialized;
- provisioning conflict;
- incompatible pre-existing role state;
- storage failure;
- transaction failure.

Raw SQL/database exceptions, DSNs, credentials, secret values, framework stack traces, and filesystem paths must not be surfaced to delivery code.

## Application and Infrastructure boundaries

New Application provisioning classes remain framework/database independent.

They may depend on existing Domain tenant/identity identifiers, `VerifiedPlatformIdentity`, `AdministrationPermission`, role/permission identifiers, and `PersistenceTransaction` abstractions, but not Laravel DB, `DB::`, `Schema::`, PDO, query builder, HTTP requests, sessions, routes, controllers, commands, or updater internals.

`LaravelInitialTenantAdministratorProvisioningRepository` is the only new Laravel durable provisioning adapter.

`PreauthorizedInitialTenantAdministratorProvisioningAuthority` is a bounded Infrastructure authority adapter and must not be auto-populated from raw request values.

No new delivery layer is authorized.

## Migration set and forward-only rule

After Sprint 23 source implementation, the exact canonical migration set is expected to be:

1. `0000_00_00_000001_create_foundational_context_graph.php`;
2. `0000_00_00_000002_create_organizational_access_grants.php`;
3. `0000_00_00_000003_create_scoped_role_permission_policy.php`;
4. `0000_00_00_000004_create_policy_mutation_journal.php`;
5. `0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php`.

Migration #5 has no seed data. `down()` throws the established forward-only rollback denial.

## Preview and Production preservation

Technical Preview remains synthetic and **NO_SCHEMA_CHANGE**.

Sprint 23 must not:

- enable persistence in Preview;
- execute migration #5 against Preview;
- package migrations in the Preview release payload;
- expose Preview bootstrap endpoints;
- bind a Preview provisioning authority;
- provision a real Preview administrator;
- authorize Production bootstrap or Production policy mutation.

`ONEQAY_PERSISTENCE_ENABLED` remains `false` by default.

Durable provisioning may run only when persistence is enabled and runtime class is exactly `local`, `test`, or `ci`.

Preview and Production remain denied before schema/mutation access.

Production persistence, schema execution, administrator provisioning, administrator recovery, policy mutation, deployment, GitHub Release, and updater activation remain **NOT AUTHORIZED**. Production readiness remains **NO-GO**.

## Required regression proof

Dedicated Local/Test/CI disposable regression must prove at least:

1. persistence-disabled denial before provisioning schema access;
2. Preview denial before provisioning schema access;
3. Production denial before provisioning schema access;
4. exact five migrations execute in order on disposable SQLite;
5. migration #5 creates only the bounded provisioning journal;
6. no migration seed/admin state exists;
7. exact pre-authorized tuple can provision the initial administrator;
8. tuple mismatch is denied before mutation;
9. missing authority is denied/closed;
10. verified identity must match the authorized identity;
11. identity must exist under the exact tenant;
12. foreign-tenant identity is denied;
13. same textual IDs across tenants remain independent;
14. successful provisioning creates exact role `authorization-policy-administrator`;
15. the role receives only `authorization.policy.manage`;
16. the role is assigned only at tenant scope to the verified identity;
17. no organization/outlet/device assignment is created;
18. no additional role/permission is created;
19. successful provisioning writes exactly one bounded journal row;
20. same tenant + same provisioning ID + same payload replays deterministically;
21. same tenant + same provisioning ID + different payload conflicts;
22. a second provisioning ID for an already initialized tenant is denied;
23. any pre-existing control role in the tenant causes `already_initialized` denial;
24. denied attempts leave no journal row;
25. transaction failure rolls back role + permission + assignment + journal;
26. incompatible pre-existing role state fails closed rather than being rewritten;
27. no unrestricted `upsert`/`updateOrInsert`;
28. all durable DB access is tenant-scoped;
29. raw DB failures are mapped to bounded errors;
30. Sprint 21 evaluator remains read-only;
31. Sprint 22 general administration still rejects grant/revoke/assign/rewrite of the newly protected role;
32. device/outlet scope-containment regression remains green;
33. tenant/isolation and identity/organization regressions remain green;
34. privileged updater authority remains separate and unchanged;
35. Preview DB qualification remains non-mutating;
36. Technical Preview release remains `NO_SCHEMA_CHANGE` and excludes all migrations.

Synthetic provisioning authority tuples used by regression are test fixtures only and do not become runtime defaults.

## Dependency boundary

No Composer/npm manifest or lockfile changes are authorized. No new dependency is required.

No credential, signing library, HTTP client, queue, cache, or external identity-provider integration is introduced by Sprint 23.

## Exact authorized implementation paths

Sprint 23 source implementation is limited to exactly these 17 paths:

1. `apps/web/app/Application/Authorization/InitialTenantAdministratorProvisioningId.php` — new;
2. `apps/web/app/Application/Authorization/InitialTenantAdministratorProvisioningAuthority.php` — new;
3. `apps/web/app/Application/Authorization/InitialTenantAdministratorProvisioningRepository.php` — new;
4. `apps/web/app/Application/Authorization/InitialTenantAdministratorProvisioningService.php` — new;
5. `apps/web/app/Application/Authorization/InitialTenantAdministratorProvisioningViolation.php` — new;
6. `apps/web/app/Infrastructure/Authorization/PreauthorizedInitialTenantAdministratorProvisioningAuthority.php` — new;
7. `apps/web/app/Infrastructure/Authorization/LaravelInitialTenantAdministratorProvisioningRepository.php` — new;
8. `apps/web/app/Providers/AppServiceProvider.php`;
9. `apps/web/database/migrations/0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php` — new;
10. `apps/web/tests/initial-tenant-administrator-provisioning.php` — new;
11. `.github/workflows/m7-2-tenant-isolation-regression.yml`;
12. `.github/workflows/m7-3-identity-org-context-regression.yml`;
13. `.github/workflows/m7-5-preview-db-qualification-regression.yml`;
14. `.github/workflows/sprint21-role-permission-policy-regression.yml`;
15. `.github/workflows/sprint22-policy-administration-regression.yml`;
16. `.github/workflows/sprint23-initial-tenant-admin-provisioning-regression.yml` — new;
17. `docs/INITIAL_TENANT_ADMINISTRATOR_PROVISIONING_FOUNDATION.md` — new.

No other source path is authorized.

If CI reveals a legitimately required preservation path outside this envelope, that path must not be mutated until a bounded documentation-only preservation supplement is published.

Explicit exclusions include:

- edits to migrations #1–#4;
- changes to Sprint 21 `DurableRolePermissionRepository` write surface;
- weakening Sprint 22 protected-control checks;
- `src/Auth/**` or generic root auth foundation changes;
- generic `src/Persistence/**` foundation changes;
- HTTP routes/controllers/middleware for bootstrap;
- console/bootstrap commands exposed as authority;
- environment-based superuser flags;
- default/seed administrator records;
- business role/permission catalog;
- administrator recovery/replacement;
- Preview/Production durable bootstrap;
- customer PII;
- updater privilege changes.

## Workflow preservation rule

The five existing durable authorization/isolation workflows listed in the exact implementation envelope may be updated only to recognize the Sprint 23 authorized source envelope, exact five-migration set, and new preservation regression.

They may not reduce existing Sprint 21/Sprint 22 checks, remove tenant predicates, relax protected-control assertions, reduce Preview/Production denial, weaken dependency preservation, or stop proving Technical Preview migration exclusion.

The dedicated Sprint 23 workflow must run the new disposable provisioning regression plus the existing Sprint 21 and Sprint 22 durable authorization regressions.

## Merge gate

Source implementation may merge only when:

1. canonical base is freshly verified;
2. branch is behind 0 or safely rebuilt on canonical main;
3. changed-file envelope is exactly within the 17 authorized paths;
4. dependency manifests/locks remain unchanged;
5. migrations #1–#4 remain byte-identical;
6. migration #5 is the only new migration;
7. all triggered required/application/isolation/authorization/Preview-preservation checks succeed;
8. dedicated Sprint 23 regression succeeds;
9. exact-head `product-owner-merge-authority` succeeds;
10. no ruleset/security control is weakened;
11. Preview remains `NO_SCHEMA_CHANGE`;
12. Production and updater boundaries remain unchanged.

Any source-head change requires fresh exact-head Product Owner authorization.

## Entry-gate decision

**AUTHORIZED FOR BOUNDED IMPLEMENTATION AFTER THIS DOCUMENTATION-ONLY ENTRY GATE IS PUBLISHED.**

Security invariant:

> The first tenant control principal may be created only once, for an already-existing verified same-tenant identity, under an exact out-of-band pre-authorized tenant + identity + provisioning tuple; no application state, bearer input, platform-superadmin/updater privilege, or absence-of-admin condition may manufacture bootstrap authority.

Attribution: **Lab | zefry**
