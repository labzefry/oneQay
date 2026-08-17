# Sprint 22 Entry Gate — Governed Durable Authorization Policy Administration Foundation

## Identity and authority

- Product: `oneQay`
- Engineering entity: **Lab | zefry**
- Repository: `labzefry/oneQay`
- Canonical branch: `main`
- Exact base: `b4a3ebba895aea9f29b1b04b61ed9817d9faef9a`
- Exact base tree: `4e267b0e03054111f4050626d5ebe4edeb2d956e`
- Sprint 21: **COMPLETE / IMPLEMENTED / PUBLISHED**
- Post-Sprint 21 canonical state reconciliation: **PUBLISHED**
- Production readiness: **NO-GO**

GitHub remains the Single Source of Truth.

The Product Owner directed continuation only after Sprint 21 closure was freshly audited. That audit found no known unresolved bounded-scope Sprint 21 source/lifecycle defect and the documentation drift discovered by the audit was separately reconciled and published before this gate.

This document authorizes **Sprint 22 — Governed Durable Authorization Policy Administration Foundation** for bounded Local/Test/CI implementation after this documentation-only gate is published.

Independent review is not an additional mandatory gate under the current Product Owner continuation model unless the Product Owner explicitly reactivates it. Exact-head Product Owner authority, required CI, changed-file scope, tenant isolation, and branch protection remain mandatory.

No Preview/Production policy mutation, Production schema execution, tenant-admin bootstrap, business-role catalog, UI/API administration surface, deployment, Release, updater activation, POS persistence, customer-data persistence, or Production readiness is authorized.

Attribution: **Lab | zefry**

## Why Sprint 22

Sprint 21 established durable **read-only** role/permission evaluation over `VerifiedOrganizationalContext`: tenant-scoped roles, exact role-permission facts, tenant/organization/outlet/device assignments, exact matching, deny-by-default, reserved platform namespace, Local/Test/CI-only durable reads, and Preview/Production denial.

Sprint 21 intentionally did not expose policy mutation because write authority itself required a separately governed security model. Sprint 22 closes only that administration-mechanics gap.

## Exact control permission

Sprint 22 introduces exactly one governance/control-plane permission:

`authorization.policy.manage`

It is not a business permission catalog. It grants only bounded policy-administration mechanics under this gate. It does not imply platform-superadmin, updater authority, ownership, billing, POS authority, customer-data access, Production policy authority, or wildcard authority.

The existing Sprint 21 exact-match evaluator remains authoritative.

## No bootstrap bypass

No canonical safe first-tenant-administrator provisioning authority currently exists. Sprint 22 must not invent an implicit Owner/Admin role, tenant superadmin, first-user elevation, environment superuser, platform-superadmin inheritance, updater reuse, bootstrap header/token, hard-coded administrator, or application-level `if no admin then allow` rule.

Local/Test/CI tests may directly pre-provision a **synthetic** principal and synthetic role containing exact `authorization.policy.manage`. This is test fixture state only, not a runtime bootstrap feature. Real tenant-admin bootstrap remains separately unauthorized.

## Protected control authority

Any durable role containing exact `authorization.policy.manage` is a **protected control role**.

Sprint 22 must fail closed when asked to:

- grant `authorization.policy.manage`;
- revoke `authorization.policy.manage`;
- assign a protected control role;
- revoke a protected control role;
- mutate any other permission on a protected control role;
- delete a protected control role.

Role deletion is not authorized anywhere in Sprint 22.

Thus Sprint 22 cannot manufacture, transfer, remove, or rewrite the authority that authorizes Sprint 22 itself.

## Platform privilege separation

The privileged updater security boundary remains unchanged and independent. Sprint 22 must not modify or reinterpret platform-superadmin semantics, `platform.system-update.install`, privileged reauthentication, TOTP step-up, privileged updater audit, or `RequirePrivilegedUpdateAuthorization`.

Platform-superadmin must never substitute for `authorization.policy.manage`. Tenant policy remains unable to grant `platform.*` or represent `platform-superadmin`, `platform-*`, or `platform_*` roles.

## Exact authorized operations

Sprint 22 authorizes only:

1. create a tenant-scoped role;
2. grant one exact non-control permission to a non-protected role;
3. revoke one exact non-control permission from a non-protected role;
4. assign a non-protected role at tenant scope;
5. assign a non-protected role at organization scope;
6. assign a non-protected role at outlet scope;
7. assign a non-protected role at device scope;
8. revoke one exact non-protected role assignment from those scopes.

Not authorized: role deletion/rename, inheritance, wildcard/negative permissions, ABAC, bulk import, policy cloning, default roles, cross-tenant mutation, or delivery-exposed raw SQL.

## Verified actor and target scope

Every operation requires an existing acting `VerifiedOrganizationalContext`. Before any mutation, `DurableScopedAuthorizationPolicy` must require exact `authorization.policy.manage`.

Raw tenant/identity/organization/outlet/device/role/permission claims never establish authority.

Target scope is derived from the verified actor context:

- tenant assignment → actor tenant;
- organization assignment → actor tenant + organization;
- outlet assignment → actor tenant + organization + exact verified outlet;
- device assignment → actor tenant + organization + exact verified outlet + device.

Only the target `PlatformIdentityId` is independently supplied. Existing relational constraints must prove same-tenant identity and required membership/access for the chosen scope. Arbitrary foreign scope IDs are not accepted.

## Tenant isolation

Every read/write is explicitly tenant-scoped. Same textual role, identity, organization, outlet, device, mutation, or permission identifiers may independently exist under another tenant. No global ID lookup or global mutation identifier is authorized.

## Idempotency

Every mutation requires a tenant-scoped canonical `PolicyMutationId`.

- same tenant + same mutation ID + same canonical payload → idempotent replay and prior deterministic outcome;
- same tenant + same mutation ID + different payload → stable conflict, no second business mutation;
- same mutation ID under another tenant → independent;
- malformed mutation ID → rejected before storage mutation.

The canonical fingerprint binds actor identity, operation, scope, target identity when applicable, scope identifiers, role, and permission when applicable. Secrets, TOTP material, tokens, credentials, raw headers, and raw request bodies are prohibited.

## Transaction and audit journal

Sprint 22 reuses `PersistenceTransaction`. Business mutation and durable journal record must commit atomically. Any write, relationship assertion, idempotency, or journal failure rolls back the entire operation.

One additive forward-only migration is authorized:

`apps/web/database/migrations/0000_00_00_000004_create_policy_mutation_journal.php`

Migrations #1–#3 remain immutable.

The new append-only table is `oneqay_policy_mutations` with bounded fields:

- `tenant_id`;
- `mutation_id`;
- `actor_identity_id`;
- `operation`;
- `scope_type`;
- nullable `organization_id`;
- nullable `outlet_id`;
- nullable `device_id`;
- nullable `target_identity_id`;
- `role_id`;
- nullable `permission_id`;
- `payload_fingerprint`;
- `outcome`;
- `occurred_at_unix`.

Primary key: `tenant_id + mutation_id`.

Application code may never update/delete an existing journal row. The journal contains no passwords, TOTP data, sessions, tokens, credentials, arbitrary request payloads, or customer PII.

Canonical outcomes: `applied`, `no_change`.

Canonical operations only:

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

No free-form operation value is allowed.

## Safe mutation mechanics

Unrestricted `upsert` and `updateOrInsert` are prohibited for policy relationships.

Create/grant/assign may use insert-if-absent only with exact scoped readback/relationship verification. Existing incompatible state fails closed instead of being rewritten. Revocation must use the complete tenant + exact scope predicate and may not delete broader rows.

## Application and infrastructure boundaries

New Application authorization-administration classes remain framework/database independent. They may depend on Domain identifiers, `VerifiedOrganizationalContext`, `DurableScopedAuthorizationPolicy`, and `PersistenceTransaction`, but not Laravel DB, `DB::`, `Schema::`, PDO, query builder, HTTP requests, sessions, routes, or updater internals.

`LaravelDurablePolicyAdministrationRepository` is the only new Sprint 22 Laravel policy-mutation adapter. It independently enforces persistence enabled, runtime in exact `local/test/ci`, tenant predicates, protected control authority, target membership/access, idempotency conflict, and append-only journal semantics.

Application policy is authoritative; DB constraints/infrastructure checks are defense in depth.

## Error and clock boundaries

Stable non-sensitive administration errors must cover authorization denied, persistence disabled, runtime denied, invalid mutation, protected control authority, invalid target scope, membership/access denial, mutation conflict, relationship conflict, storage failure, and transaction failure. Errors cannot expose SQL, DSN, credentials, filesystem paths, secrets, or raw DB exception text.

A framework-independent `PolicyAdministrationClock` is authorized for deterministic audit time. Tests use a synthetic deterministic clock. `occurred_at_unix` must be positive.

## Migration set and forward-only rule

After Sprint 22 source implementation the exact canonical migration set is expected to be:

1. `0000_00_00_000001_create_foundational_context_graph.php`;
2. `0000_00_00_000002_create_organizational_access_grants.php`;
3. `0000_00_00_000003_create_scoped_role_permission_policy.php`;
4. `0000_00_00_000004_create_policy_mutation_journal.php`.

Migration #4 has no seed data. `down()` throws exact established message:

`Forward-only generated migration; rollback is not authorized.`

## Preview and Production preservation

Technical Preview remains synthetic and `NO_SCHEMA_CHANGE`. Sprint 22 must not enable persistence in Preview, execute migration #4 against Preview, wire administration into Preview journeys, package migrations in Preview release payload, expose Preview admin endpoints, or claim Preview durable administration.

Production persistence, schema execution, policy mutation, admin bootstrap, role editor, deployment, GitHub Release, and updater activation remain **NOT AUTHORIZED**. Production readiness remains **NO-GO**.

## Required regression proof

Dedicated Local/Test/CI disposable regression must prove at least:

1. disabled/Preview/Production mutation denial before schema mutation;
2. exact four migrations execute in order on disposable SQLite and journal exists;
3. synthetic pre-provisioned control principal can perform non-control mutation;
4. non-control principal is denied before mutation;
5. role create and exact permission grant/revoke;
6. tenant/org/outlet/device assignment and exact revocation;
7. organization membership requirement;
8. outlet/device Sprint 20 access requirement;
9. cross-tenant same IDs remain independent;
10. foreign target identity is denied;
11. target scope comes only from verified actor context;
12. control permission cannot be granted/revoked;
13. protected control role cannot be assigned/revoked/permission-mutated;
14. same mutation ID + same payload is idempotent;
15. same mutation ID + different payload conflicts with no second mutation;
16. same mutation ID can exist in another tenant;
17. transaction failure rolls back business change + journal;
18. storage failures are bounded/non-sensitive;
19. no unrestricted `upsert`/`updateOrInsert`;
20. all DB access is tenant-scoped;
21. journal is safe/bounded;
22. Sprint 21 read-only repository remains read-only;
23. privileged updater regression remains green;
24. M7.1/M7.2/M7.3 remain green;
25. Preview DB remains non-mutating;
26. Technical Preview release remains `NO_SCHEMA_CHANGE` and excludes all migrations.

Synthetic test roles/permissions do not become product catalog entries.

## Dependency boundary

No Composer/npm manifest or lockfile changes are authorized. No new dependency is required.

## Exact authorized implementation paths

Sprint 22 source implementation is limited to exactly these 22 paths:

1. `apps/web/app/Application/Authorization/AdministrationPermission.php` — new;
2. `apps/web/app/Application/Authorization/PolicyMutationId.php` — new;
3. `apps/web/app/Application/Authorization/PolicyMutationOperation.php` — new;
4. `apps/web/app/Application/Authorization/PolicyAssignmentScope.php` — new;
5. `apps/web/app/Application/Authorization/DurablePolicyMutation.php` — new;
6. `apps/web/app/Application/Authorization/DurablePolicyAdministrationRepository.php` — new;
7. `apps/web/app/Application/Authorization/DurablePolicyAdministrationService.php` — new;
8. `apps/web/app/Application/Authorization/DurablePolicyAdministrationViolation.php` — new;
9. `apps/web/app/Application/Authorization/PolicyAdministrationClock.php` — new;
10. `apps/web/app/Infrastructure/Authorization/LaravelDurablePolicyAdministrationRepository.php` — new;
11. `apps/web/app/Providers/AppServiceProvider.php`;
12. `apps/web/database/migrations/0000_00_00_000004_create_policy_mutation_journal.php` — new;
13. `apps/web/tests/authorization-administration-persistence.php` — new;
14. `apps/web/tests/authorization-persistence.php`;
15. `apps/web/tests/tenant-isolation.php`;
16. `apps/web/tests/identity-org-context.php`;
17. `.github/workflows/m7-2-tenant-isolation-regression.yml`;
18. `.github/workflows/m7-3-identity-org-context-regression.yml`;
19. `.github/workflows/m7-5-preview-db-qualification-regression.yml`;
20. `.github/workflows/sprint21-role-permission-policy-regression.yml`;
21. `.github/workflows/sprint22-policy-administration-regression.yml` — new;
22. `docs/DURABLE_AUTHORIZATION_POLICY_ADMINISTRATION_FOUNDATION.md` — new.

No other source path is authorized. If CI reveals a required preservation path outside this envelope, a bounded documentation-only supplement must be published before that path is mutated.

Explicit exclusions include `src/Auth/**`, generic `src/Persistence/**`, edits to migrations #1–#3, write methods on Sprint 21 `DurableRolePermissionRepository`, business role catalog, business permission catalog beyond exact control capability `authorization.policy.manage`, UI/API routes/controllers, tenant onboarding/bootstrap, live/Production DB work, POS persistence, customer PII, and updater changes.

## Merge gate

Source implementation may merge only when canonical base is current or safely rebuilt, exact changed-file envelope stays within these 22 paths, dependency locks are unchanged, every triggered required/application/preservation check succeeds, dedicated Sprint 22 regression succeeds, exact-head `product-owner-merge-authority` succeeds, no ruleset is weakened, and Preview/Production boundaries remain unchanged.

Any head change requires fresh exact-head Product Owner authorization.

## Entry-gate decision

**AUTHORIZED FOR BOUNDED IMPLEMENTATION AFTER THIS DOCUMENTATION-ONLY ENTRY GATE IS PUBLISHED.**

Security invariant:

> No policy write is permitted unless an already-provisioned exact-context durable authority has `authorization.policy.manage`; Sprint 22 itself cannot manufacture, transfer, revoke, or rewrite that control authority.

Attribution: **Lab | zefry**
