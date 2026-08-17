# Sprint 21 Entry Gate — Durable Scoped Role & Permission Policy Foundation

## Identity

- Product: `oneQay`
- Developer and Product Engineering Entity: **Lab | zefry**
- Repository: `labzefry/oneQay`
- Decision date: 2026-08-17
- Exact entry-gate base: `27c2b8ca0bee81a52f3b16dc464911548b2c0329`
- Exact entry-gate base tree: `6b12240c0518fd8745f12d9e5ddd259a4a72f19a`
- Sprint 20 Durable Identity & Organizational Access Persistence Foundation: **COMPLETE / IMPLEMENTED / PUBLISHED**
- Production readiness: **NO-GO**

GitHub remains the Single Source of Truth.

Attribution: **Lab | zefry**

## Product Owner continuation

The Product Owner directed the project to continue immediately to the next bounded engineering stage after Sprint 20.

This entry gate authorizes **Sprint 21 — Durable Scoped Role & Permission Policy Foundation**, including Local/Test/CI implementation and normal Ready/Merge lifecycle after exact-head required checks and repository-native Product Owner merge-authority status succeed.

Independent review is not an additional requirement under the current Product Owner continuation model.

This authority does **not** grant Preview/cPanel/live/Production persistence, Production policy administration, customer-data persistence, Production schema execution, deployment, Release/GitHub Release, updater activation, or Production readiness.

## Why Sprint 21 is the next bounded capability

Sprint 05 established the framework-agnostic authorization boundary with:

- `AuthorizationSubject`;
- canonical `PermissionIdentifier`;
- immutable authorization context and decision;
- deny-by-default policy;
- synthetic explicit grants;
- tenant-bound evaluation.

Sprint 20 established durable organizational access facts for:

`tenant membership → organization membership → outlet access → device access`

The remaining bounded gap is durable **role/permission policy evaluation** over verified organizational context.

Sprint 21 closes only that evaluation gap.

It does not yet authorize a user-facing role editor, permission editor, grant/revoke API, business role catalog, or Production authorization store.

## Canonical authorization inputs

Sprint 21 must preserve and reconcile existing canonical authorization concepts rather than invent replacements.

### Sprint 05 permission identifier semantics

The existing root Authorization Foundation uses a canonical, lowercase, dot-delimited permission identifier with these characteristics:

- at least two dot-separated segments;
- each segment starts with a lowercase letter;
- remaining characters are lowercase letters, digits, `_`, or `-`;
- maximum total length: 96;
- identity-bearing segments beginning with `tenant_` or `user_` are rejected.

Sprint 21 Application permission identifiers must preserve those format semantics.

### Existing platform-privileged vocabulary

The privileged update security foundation already contains the canonical privileged concepts:

- `platform-superadmin` semantics through `isPlatformSuperadmin()`;
- privileged capability `platform.system-update.install`;
- fresh privileged session requirements;
- explicit reauthentication;
- TOTP step-up;
- fail-closed privileged authorization.

Sprint 21 must not reinterpret, persist, replace, or weaken that boundary.

## Reserved platform namespace

Tenant-scoped durable role/permission policy must not become a path to platform-global privilege.

Therefore Sprint 21 reserves:

- any role identifier beginning with `platform-` or `platform_`;
- the semantic identifier `platform-superadmin`;
- any permission identifier beginning with `platform.`.

Tenant policy evaluation must reject these identifiers before durable lookup.

The existing privileged updater security remains an independent authority boundary.

No tenant role may grant `platform.system-update.install`.

## No business role catalog in Sprint 21

Sprint 21 must not publish built-in business role names such as:

- owner;
- administrator/admin;
- manager;
- cashier;
- supervisor;
- accountant;
- inventory operator;
- auditor;
- support;
- any other product role taxonomy not already approved as canonical.

Synthetic role names may be used in Local/Test/CI regression only and must be clearly synthetic.

No synthetic role becomes a product role.

## No business permission catalog in Sprint 21

Sprint 21 must not publish a production business permission catalog for Catalog, Sale, Payment, Inventory, Shift/Register, Customer, Reporting, or other product modules.

Synthetic permission identifiers may be used in Local/Test/CI regression only.

No synthetic permission becomes a product permission.

This stage establishes policy mechanics, persistence shape, scope semantics, and fail-closed evaluation only.

## Read-only application policy boundary

Sprint 21 intentionally publishes a **read-only Application authorization repository**.

The Application layer may:

- validate role identifiers;
- validate permission identifiers;
- evaluate whether an already verified organizational context has an exact permission;
- require a permission and fail closed when it is absent.

The Application layer must not expose methods to:

- create a role;
- delete a role;
- grant a permission to a role;
- remove a permission from a role;
- assign a role to an identity;
- revoke a role from an identity;
- bulk import policy;
- seed business roles.

Policy administration requires a later, separately authorized stage because write authority itself must be governed by a canonical administrative permission model.

## Trust model

Sprint 21 authorization evaluation accepts only an existing `VerifiedOrganizationalContext`.

The durable policy must never treat raw client-provided tenant, identity, organization, outlet, device, role, or permission hints as verified context authority.

The trust progression is:

`VerifiedPlatformIdentity`

→ `VerifiedTenantContext`

→ verified organizational relationship/access

→ `VerifiedOrganizationalContext`

→ canonical tenant permission identifier

→ durable scoped role lookup

→ exact role-permission lookup

→ allow or deny.

If any required durable policy state is missing, malformed, unavailable, or inconsistent, authorization must fail closed.

## Scope hierarchy

Sprint 21 defines exactly four durable role-assignment scopes:

1. `tenant`;
2. `organization`;
3. `outlet`;
4. `device`.

Scope containment is explicit and deterministic.

For a verified organizational context:

- a tenant-scoped role applies throughout the verified tenant;
- an organization-scoped role applies to that organization and its verified descendant outlet/device contexts;
- an outlet-scoped role applies to that outlet and its verified descendant device contexts;
- a device-scoped role applies only to that exact device context.

No assignment may cross tenant boundaries.

No organization assignment may apply to another organization.

No outlet assignment may apply to another outlet.

No device assignment may apply to another device.

The same textual identifiers existing in another tenant must not create access.

## Permission semantics

Sprint 21 durable permission evaluation is exact-match only.

It must not implement:

- `*` wildcard permissions;
- prefix wildcard permissions;
- suffix wildcard permissions;
- implicit namespace inheritance;
- deny rules;
- negative permissions;
- role inheritance;
- nested roles;
- dynamic expression evaluation;
- client-provided policy expressions;
- ABAC expressions;
- superadmin bypass.

A permission is allowed only when at least one applicable durable role has the exact canonical permission identifier.

Otherwise the result is denied.

## Role identifier semantics

`RoleIdentifier` must:

- trim input;
- normalize to lowercase;
- allow only lowercase letters, digits, `_`, and `-`;
- begin with a lowercase letter;
- be bounded to 64 characters;
- reject blank values;
- reject `platform-superadmin`;
- reject identifiers beginning with `platform-` or `platform_`.

Role identifiers do not carry tenant, identity, organization, outlet, or device identifiers.

## Application permission identifier semantics

`PermissionIdentifier` must:

- preserve Sprint 05 canonical format semantics;
- normalize to lowercase;
- require dot-delimited segments;
- remain bounded to 96 characters;
- reject identity-bearing segments beginning with `tenant_` or `user_`;
- reject wildcard characters;
- reject the reserved `platform.` namespace.

It must not silently translate aliases.

## Durable schema

Sprint 21 adds one forward-only migration:

`apps/web/database/migrations/0000_00_00_000003_create_scoped_role_permission_policy.php`

The migration must execute only after the Sprint 19 and Sprint 20 canonical migrations.

It adds exactly six policy tables.

### `oneqay_roles`

Tenant-scoped role definitions:

- `tenant_id`;
- `id`.

Primary key:

`tenant_id + id`

Foreign key:

`tenant_id → oneqay_tenants.id`

No role rows are seeded by the migration.

### `oneqay_role_permissions`

Exact permission facts for roles:

- `tenant_id`;
- `role_id`;
- `permission_id`.

Primary key:

`tenant_id + role_id + permission_id`

Foreign key:

`tenant_id + role_id → oneqay_roles`

No wildcard or inherited permission row semantics are introduced.

### `oneqay_tenant_role_assignments`

Tenant-wide role assignments:

- `tenant_id`;
- `identity_id`;
- `role_id`.

Primary key binds all three columns.

Foreign keys must bind the identity and role to the same tenant.

### `oneqay_organization_role_assignments`

Organization-scoped role assignments:

- `tenant_id`;
- `identity_id`;
- `organization_id`;
- `role_id`.

The assignment must reference the existing Sprint 19 `oneqay_identity_organizations` membership row and the same-tenant role.

### `oneqay_outlet_role_assignments`

Outlet-scoped role assignments:

- `tenant_id`;
- `identity_id`;
- `organization_id`;
- `outlet_id`;
- `role_id`.

The assignment must reference the existing Sprint 20 `oneqay_outlet_access_grants` row and the same-tenant role.

### `oneqay_device_role_assignments`

Device-scoped role assignments:

- `tenant_id`;
- `identity_id`;
- `organization_id`;
- `outlet_id`;
- `device_id`;
- `role_id`.

The assignment must reference the existing Sprint 20 `oneqay_device_access_grants` row and the same-tenant role.

## Why separate scope tables are required

Sprint 21 intentionally avoids a polymorphic `scope_type + scope_id` role-assignment table.

Separate assignment tables allow relational foreign keys to prove the exact tenant/organization/outlet/device relationship rather than relying on application convention alone.

This prevents a generic scope identifier from bypassing parent-context integrity.

## Forward-only migration rule

The Sprint 21 migration must preserve the established forward-only migration contract.

`down()` must not perform destructive rollback.

It must throw the canonical forward-only `LogicException` message.

Sprint 21 does not authorize live rollback or destructive policy-table removal.

## Runtime boundary

The durable authorization repository must reuse the Sprint 19/20 persistence runtime contract.

Durable policy evaluation is available only when:

- `ONEQAY_PERSISTENCE_ENABLED=true`; and
- runtime class is one of `local`, `test`, or `ci`.

It must fail closed for:

- `preview`;
- `production`;
- blank runtime class;
- unknown runtime class;
- persistence disabled.

Sprint 21 must not broaden the runtime allowlist.

## Repository behavior

`LaravelDurableRolePermissionRepository` must be read-only.

It may issue bounded `SELECT` queries only.

It must not contain application policy mutation operations such as:

- insert;
- insertOrIgnore;
- update;
- updateOrInsert;
- upsert;
- delete;
- truncate;
- schema mutation.

The repository must:

1. derive tenant/identity/organization/outlet/device only from `VerifiedOrganizationalContext`;
2. query tenant-level role assignments;
3. query organization-level assignments for the exact verified organization;
4. query outlet-level assignments only when an exact verified outlet exists;
5. query device-level assignments only when an exact verified device exists;
6. validate every returned durable role identifier before using it;
7. deduplicate applicable role identifiers;
8. perform exact permission lookup under the same tenant and applicable role set;
9. return `false` when no applicable role grants the permission;
10. fail closed with bounded error classification for malformed durable policy state or storage failure.

The repository must never return policy rows, foreign tenant identifiers, SQL details, DSNs, credentials, or raw database exceptions to delivery code.

## Policy behavior

`DurableScopedAuthorizationPolicy` must:

- depend on the read-only Application repository contract;
- deny when verified context is missing;
- deny when no exact applicable permission exists;
- provide a bounded `require()` operation that throws a generic authorization-denied violation;
- never implement a platform-superadmin bypass;
- never interpret client role claims;
- never infer permission from route names or URLs.

## Existing privileged updater isolation

Sprint 21 must preserve the privileged updater security foundation unchanged.

The following remain independent and authoritative for privileged update authorization:

- `VerifiedPrivilegedPlatformIdentity::isPlatformSuperadmin()`;
- `PrivilegedUpdateCapability::INSTALL`;
- fresh session validation;
- explicit reauthentication;
- TOTP step-up evidence;
- privileged audit behavior.

Sprint 21 tenant role/permission policy must not be injected into `RequirePrivilegedUpdateAuthorization`.

A tenant role can never substitute for platform-superadmin status.

## Technical Preview preservation

Technical Preview remains synthetic/non-durable.

Sprint 21 must not:

- enable `ONEQAY_PERSISTENCE_ENABLED` in Preview;
- execute the Sprint 21 migration against Preview;
- package migrations in the governed Technical Preview release artifact;
- wire durable scoped role/permission policy into current Preview journeys;
- claim Preview role administration or durable RBAC.

The Technical Preview Release Artifact must retain `NO_SCHEMA_CHANGE` classification.

## Exact authorized implementation paths

Sprint 21 source implementation is limited to exactly these 17 paths:

1. `apps/web/app/Application/Authorization/RoleIdentifier.php` — new;
2. `apps/web/app/Application/Authorization/PermissionIdentifier.php` — new;
3. `apps/web/app/Application/Authorization/DurableRolePermissionRepository.php` — new;
4. `apps/web/app/Application/Authorization/DurableScopedAuthorizationPolicy.php` — new;
5. `apps/web/app/Application/Authorization/DurableAuthorizationViolation.php` — new;
6. `apps/web/app/Infrastructure/Authorization/LaravelDurableRolePermissionRepository.php` — new;
7. `apps/web/app/Providers/AppServiceProvider.php`;
8. `apps/web/database/migrations/0000_00_00_000003_create_scoped_role_permission_policy.php` — new;
9. `apps/web/tests/authorization-persistence.php` — new;
10. `apps/web/tests/run.php`;
11. `apps/web/tests/tenant-isolation.php`;
12. `apps/web/tests/identity-org-context.php`;
13. `.github/workflows/m7-2-tenant-isolation-regression.yml`;
14. `.github/workflows/m7-3-identity-org-context-regression.yml`;
15. `.github/workflows/m7-5-preview-db-qualification-regression.yml`;
16. `.github/workflows/sprint21-role-permission-policy-regression.yml` — new;
17. `docs/DURABLE_SCOPED_ROLE_PERMISSION_POLICY_FOUNDATION.md` — new.

No other repository path is authorized by this entry gate.

If implementation discovery requires another path, Sprint 21 must stop and publish a bounded supplement before mutating outside this envelope.

## Explicit non-scope

Sprint 21 must not modify:

- `src/Auth/Foundation.php`;
- `src/Authorization/Foundation.php`;
- root `composer.json` or `composer.lock`;
- `apps/web/composer.json`;
- `apps/web/composer.lock`;
- `apps/web/package.json`;
- `apps/web/package-lock.json`;
- Sprint 19 foundational migration;
- Sprint 20 organizational access migration;
- `apps/web/app/Application/SystemUpdate/Security/**`;
- `apps/web/app/Infrastructure/SystemUpdate/Security/**`;
- POS Application/Domain source;
- routes/controllers/UI;
- Technical Preview fixture source;
- deployment/updater/release source;
- Preview/cPanel/live runtime evidence.

## No policy write API

The absence of role/permission administration is intentional.

Sprint 21 must not expose:

- HTTP role endpoints;
- HTTP permission endpoints;
- command-line role mutation commands;
- seeders that create business roles;
- automatic default roles;
- migration-seeded identities or role assignments.

Local/Test/CI regression may insert clearly synthetic policy rows directly into the disposable test database to prove evaluation semantics.

Those fixtures are test evidence only.

## Regression requirements

`apps/web/tests/authorization-persistence.php` must use a disposable SQLite database and prove at minimum:

1. Sprint 19 migration executes first;
2. Sprint 20 migration executes second;
3. Sprint 21 migration executes third;
4. exactly the expected Sprint 19/20/21 migration set exists;
5. all six Sprint 21 policy tables exist;
6. persistence-disabled evaluation fails closed;
7. Preview runtime evaluation fails closed;
8. Production runtime evaluation fails closed;
9. canonical role normalization works;
10. malformed role identifiers fail;
11. `platform-superadmin` role identifier fails;
12. `platform-*` and `platform_*` role namespaces fail;
13. canonical permission normalization works;
14. malformed permissions fail;
15. `platform.*` permission namespace fails;
16. wildcard permissions fail;
17. synthetic tenant-scoped role grants an exact permission within the tenant;
18. tenant-scoped assignment applies to verified descendant organization/outlet/device contexts;
19. organization-scoped assignment applies only to the exact organization and verified descendants;
20. outlet-scoped assignment applies only to the exact outlet and verified descendant device;
21. device-scoped assignment applies only to the exact device;
22. a role without the requested permission denies;
23. a permission on a role without an applicable identity-role assignment denies;
24. a foreign-tenant role with the same textual role identifier does not grant access;
25. a foreign-tenant permission row does not grant access;
26. same textual organization/outlet/device identifiers in another tenant do not bypass scoping;
27. corrupted durable role identifier fails closed;
28. storage failure is bounded and fail-closed;
29. repository source is read-only and contains no policy mutation API;
30. no Production/Preview/cPanel/live database is touched;
31. no credential, DSN, customer data, or private path leaks through bounded failures.

## M7.2 preservation

The M7.2 tenant isolation workflow must evolve from an exact two-migration expectation to an exact three-migration expectation:

1. `0000_00_00_000001_create_foundational_context_graph.php`;
2. `0000_00_00_000002_create_organizational_access_grants.php`;
3. `0000_00_00_000003_create_scoped_role_permission_policy.php`.

It must verify tenant-scoped role, role-permission, and role-assignment foreign-key boundaries while preserving all previous Sprint 19/20 isolation checks.

## M7.3 preservation

The M7.3 identity/organizational context workflow must preserve existing behavioral authorization controls and additionally verify:

- policy evaluation requires verified organizational context;
- tenant role assignments do not cross tenants;
- organization/outlet/device role scopes do not cross verified hierarchy;
- platform namespace remains reserved;
- existing synthetic organizational verifier behavior remains intact.

## M7.5 Preview DB preservation

The Preview DB qualification workflow must syntax-check the new authorization surfaces and preserve:

- no Preview schema mutation;
- no Preview durable policy activation;
- no durable policy wiring into Technical Preview journey;
- no dependency manifest or lockfile change;
- no credentials in source.

## Dedicated Sprint 21 workflow

`.github/workflows/sprint21-role-permission-policy-regression.yml` must trigger on the Sprint 21 authorization/persistence surfaces and must enforce:

- exact source checkout;
- dependency manifest/lock preservation;
- locked Composer install;
- PHP syntax;
- Domain/Application framework independence;
- exact three-migration set;
- exact six Sprint 21 policy tables;
- forward-only migration contract;
- repository read-only source boundary;
- reserved platform role/permission namespace;
- Sprint 21 disposable SQLite regression;
- M7.2 regression;
- M7.3 regression;
- privileged update security regression;
- no Preview/live/Production persistence enablement.

## Dependency boundary

Sprint 21 adds no PHP or npm dependency.

`composer.json`, `composer.lock`, `package.json`, and `package-lock.json` remain unchanged.

Laravel remains pinned to the existing canonical version.

## Security properties

Sprint 21 must preserve these security properties:

- deny by default;
- tenant isolation first;
- verified context required;
- exact permission match only;
- reserved platform privilege namespace;
- no implicit superadmin;
- no wildcard permission;
- no role inheritance;
- no policy write API;
- no client role trust;
- no route-name authorization inference;
- no Preview/Production durable policy activation;
- bounded, generic failures;
- no secret/data leakage.

## Completion criteria

Sprint 21 implementation is complete only when:

1. this entry gate is merged to canonical `main`;
2. source implementation begins from that exact canonical merge commit;
3. source PR changes exactly the 17 authorized paths;
4. all required and triggered CI checks succeed at the exact source head;
5. repository-native `product-owner-merge-authority` succeeds for that exact head;
6. the source PR is merged without weakening branch/ruleset governance;
7. post-merge canonical main SHA/tree/parent/signature are verified;
8. Production readiness remains `NO-GO`.

## Post-Sprint 21 candidate direction

Sprint 21 deliberately leaves policy administration and business vocabulary unresolved.

A later bounded stage may establish one of these, only after inspection and explicit authority:

- canonical business role and permission catalog;
- governed role/permission administration commands/services;
- POS permission wiring;
- privileged policy administration requiring fresh step-up authorization;
- audit persistence for policy changes.

No later-stage capability is authorized by this document.

## Final authority boundary

Sprint 21 authorizes only a Local/Test/CI durable scoped role/permission **evaluation foundation**.

It does not authorize Production RBAC, business role taxonomy, permission administration, user management, Preview durable RBAC, live migration execution, Release, updater activation, or Production readiness.

Production readiness remains **NO-GO**.

Attribution: **Lab | zefry**
