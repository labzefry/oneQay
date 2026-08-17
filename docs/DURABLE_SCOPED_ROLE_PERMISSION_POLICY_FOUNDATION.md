# Durable Scoped Role & Permission Policy Foundation

## Status

- Product: `oneQay`
- Stage: **Sprint 21**
- Capability: **Durable Scoped Role & Permission Policy Foundation**
- State: **IMPLEMENTED / LOCAL-TEST-CI-ONLY / READ-ONLY-EVALUATION**
- Production readiness: **NO-GO**
- Attribution: **Lab | zefry**

## Purpose

Sprint 21 adds a durable, tenant-isolated role/permission evaluation boundary on top of the verified organizational context established by M7.3 and the durable organizational access hierarchy established by Sprint 20.

It deliberately does not publish a business role catalog, business permission catalog, role-management API, permission-management API, or Production authorization store.

## Canonical policy chain

Authorization evaluation is bounded to:

`VerifiedPlatformIdentity`

→ `VerifiedTenantContext`

→ verified organization/outlet/device access

→ `VerifiedOrganizationalContext`

→ canonical `PermissionIdentifier`

→ applicable durable scoped roles

→ exact role-permission lookup

→ allow or deny.

Raw client role claims, tenant hints, organization hints, outlet hints, device hints, route names, and URLs are never authorization authority.

## Canonical identifiers

### RoleIdentifier

Tenant role identifiers are normalized to lowercase and must:

- begin with a lowercase letter;
- contain only lowercase letters, digits, `_`, and `-`;
- be no longer than 64 characters;
- not be blank;
- not equal `platform-superadmin`;
- not begin with `platform-`;
- not begin with `platform_`.

### PermissionIdentifier

Permission identifiers preserve the existing Sprint 05 semantics:

- lowercase, dot-delimited identifiers;
- at least two segments;
- each segment begins with a lowercase letter;
- maximum length 96;
- identity-bearing segments beginning with `tenant_` or `user_` are rejected;
- wildcard characters are rejected;
- the `platform.` namespace is reserved and rejected.

No aliases or implicit translations exist.

## Reserved platform privilege

The existing privileged updater security boundary remains independent and unchanged.

The tenant policy system cannot represent:

- `platform-superadmin`;
- `platform-*` roles;
- `platform_*` roles;
- `platform.*` permissions.

Therefore tenant policy cannot grant `platform.system-update.install` and cannot substitute for:

- platform-superadmin verification;
- fresh privileged session requirements;
- explicit reauthentication;
- TOTP step-up;
- privileged security audit behavior.

## No built-in business role catalog

Sprint 21 does not define product roles such as Owner, Admin, Manager, Cashier, Supervisor, Accountant, Inventory Operator, or similar names.

Synthetic role names exist only in disposable Local/Test/CI regression data and are not product roles.

## No built-in business permission catalog

Sprint 21 does not define production permissions for POS, Catalog, Sale, Payment, Inventory, Shift/Register, Reporting, Customer, or other product modules.

Synthetic permission names exist only in disposable regression data and are not product permissions.

## Scope hierarchy

Exactly four role-assignment scopes are supported:

1. tenant;
2. organization;
3. outlet;
4. device.

Containment is deterministic:

- tenant role → all verified contexts inside that tenant;
- organization role → that organization and verified descendants;
- outlet role → that outlet and verified device descendants;
- device role → that exact device only.

No role assignment crosses tenant, organization, outlet, or device boundaries.

## Exact permission semantics

Authorization is exact-match, deny-by-default.

Sprint 21 does not implement:

- wildcard permissions;
- prefix/suffix matching;
- negative permissions;
- deny-rule precedence;
- role inheritance;
- nested roles;
- ABAC expressions;
- dynamic policy expressions;
- superadmin bypass.

If no applicable role has the exact canonical permission identifier, access is denied.

## Application contracts

The framework-independent Application authorization package contains:

- `RoleIdentifier`;
- `PermissionIdentifier`;
- `DurableRolePermissionRepository`;
- `DurableScopedAuthorizationPolicy`;
- `DurableAuthorizationViolation`.

The repository contract exposes only read evaluation.

There is no Application mutation method for creating roles, assigning roles, or granting permissions.

## Durable repository

`LaravelDurableRolePermissionRepository` is a read-only Infrastructure adapter.

It:

- requires persistence enabled;
- permits only `local`, `test`, or `ci` runtime classes;
- derives identity and scope exclusively from `VerifiedOrganizationalContext`;
- loads applicable roles under explicit tenant predicates;
- validates every durable role identifier before use;
- deduplicates roles;
- performs an exact permission lookup under the same tenant;
- returns false when no exact grant exists;
- classifies malformed policy state and storage failure with bounded generic errors.

It does not insert, update, upsert, delete, truncate, or mutate schema.

## Durable schema

Sprint 21 publishes migration:

`0000_00_00_000003_create_scoped_role_permission_policy.php`

It runs after the Sprint 19 foundational context graph migration and Sprint 20 organizational access migration.

It creates exactly six tables.

### oneqay_roles

Tenant-owned role identifiers with primary key:

`tenant_id + id`

### oneqay_role_permissions

Exact role-permission facts with primary key:

`tenant_id + role_id + permission_id`

### oneqay_tenant_role_assignments

Tenant-wide identity-role assignments bound to the same tenant identity and role.

### oneqay_organization_role_assignments

Organization assignments bound to the existing `oneqay_identity_organizations` membership row and same-tenant role.

### oneqay_outlet_role_assignments

Outlet assignments bound to the existing Sprint 20 `oneqay_outlet_access_grants` row and same-tenant role.

### oneqay_device_role_assignments

Device assignments bound to the existing Sprint 20 `oneqay_device_access_grants` row and same-tenant role.

Separate tables are intentional: relational foreign keys prove the exact parent hierarchy instead of relying on a weak polymorphic scope convention.

## Forward-only migration

The migration is additive and forward-only.

`down()` throws:

`Forward-only generated migration; rollback is not authorized.`

No live rollback authority is introduced.

## Read-only policy administration boundary

Sprint 21 deliberately does not expose policy mutation through Application, HTTP, CLI, UI, or seeders.

Policy-write authority requires a future separately governed administrative capability because the system must first define who is allowed to mutate authorization policy.

Regression fixtures may insert clearly synthetic policy rows directly into a disposable SQLite database. Those fixtures are evidence only.

## Runtime boundary

Durable policy evaluation is available only when both conditions are true:

- `ONEQAY_PERSISTENCE_ENABLED=true`;
- runtime class is `local`, `test`, or `ci`.

It fails closed for Preview, Production, blank, and unknown runtime classes.

The default application configuration remains persistence-disabled.

## Technical Preview preservation

Sprint 21 does not wire durable role/permission policy into current Technical Preview journeys.

Technical Preview remains:

- synthetic;
- non-durable for this policy;
- `NO_SCHEMA_CHANGE` in governed release packaging;
- without migration execution;
- without role/permission administration.

## Regression evidence

`tests/authorization-persistence.php` uses an isolated disposable SQLite database and proves:

- exact Sprint 19 → Sprint 20 → Sprint 21 migration order;
- exact three-migration set;
- six policy tables;
- disabled/Preview/Production runtime denial;
- canonical role and permission validation;
- reserved platform namespace rejection;
- tenant scope inheritance to verified descendants;
- organization scope containment;
- outlet scope containment;
- device exact-scope containment;
- exact permission matching;
- unassigned-role denial;
- foreign-tenant denial with same textual identifiers;
- malformed durable role data fail-closed behavior;
- storage failure classification;
- repository read-only source boundary;
- Application framework independence;
- private-path and database-detail non-disclosure.

M7.2, M7.3, and M7.5 Preview Database Qualification are evolved to preserve their existing controls while recognizing the third canonical migration and Sprint 21 authorization surfaces.

A dedicated Sprint 21 workflow also preserves the independent privileged updater security regression.

## Exact source envelope

The implementation is bounded to the 17 paths authorized by the Sprint 21 entry gate:

1. `apps/web/app/Application/Authorization/RoleIdentifier.php`
2. `apps/web/app/Application/Authorization/PermissionIdentifier.php`
3. `apps/web/app/Application/Authorization/DurableRolePermissionRepository.php`
4. `apps/web/app/Application/Authorization/DurableScopedAuthorizationPolicy.php`
5. `apps/web/app/Application/Authorization/DurableAuthorizationViolation.php`
6. `apps/web/app/Infrastructure/Authorization/LaravelDurableRolePermissionRepository.php`
7. `apps/web/app/Providers/AppServiceProvider.php`
8. `apps/web/database/migrations/0000_00_00_000003_create_scoped_role_permission_policy.php`
9. `apps/web/tests/authorization-persistence.php`
10. `apps/web/tests/run.php`
11. `apps/web/tests/tenant-isolation.php`
12. `apps/web/tests/identity-org-context.php`
13. `.github/workflows/m7-2-tenant-isolation-regression.yml`
14. `.github/workflows/m7-3-identity-org-context-regression.yml`
15. `.github/workflows/m7-5-preview-db-qualification-regression.yml`
16. `.github/workflows/sprint21-role-permission-policy-regression.yml`
17. `docs/DURABLE_SCOPED_ROLE_PERMISSION_POLICY_FOUNDATION.md`

No dependency manifest or lockfile changes are included.

## Explicit non-scope

Sprint 21 does not implement:

- business role taxonomy;
- business permission catalog;
- role/permission administration;
- user-management UI;
- POS permission wiring;
- Preview durable RBAC;
- Production durable RBAC;
- customer-data persistence;
- Production migration execution;
- deployment;
- GitHub Release;
- updater activation;
- Production readiness.

## Next candidate

A future bounded stage may define a canonical business role/permission vocabulary and governed policy administration. Any such stage must separately define privileged policy-write authority, audit requirements, and lifecycle controls before mutation endpoints are introduced.

No future capability is authorized by this document.

## Final state

Sprint 21 establishes a durable, tenant-isolated, scope-aware, exact-match authorization evaluation foundation for Local/Test/CI only.

Production readiness remains **NO-GO**.

Attribution: **Lab | zefry**
