# Sprint104 Final Shift Close Permission Provisioning Evidence

Author by Lab | zefry

## Purpose

Sprint104 materializes the trusted execution-evidence producer for the second Final Shift Close operational transition reserved by Sprint102: explicit bounded provisioning of `pos.shift.close` to one existing same-tenant role.

Sprint104 does **not** dispatch the executor, provision any permission, execute migration #27, change the canonical operational state, enable Final Shift Close, deploy, release, activate Technical Preview, activate Production, or activate the updater.

## Operational control-plane boundary

Canonical ordinary policy administration is intentionally restricted to Local/Test/CI runtime classes. Sprint104 does not relabel a Production database as CI and does not weaken that application runtime guard.

The future executor therefore runs as an isolated operational control-plane ceremony with:

- `APP_ENV=production`;
- `ONEQAY_PERSISTENCE_ENABLED=false` for ordinary application persistence;
- a separately protected GitHub Environment;
- independent Product Owner exact-head operational authority;
- an explicit tenant/control-principal/role tuple;
- one database transaction;
- canonical oneQay value objects and canonical policy-mutation fingerprint semantics;
- exact durable policy journal evidence;
- no role-assignment mutation.

This is deliberately separate from the normal application delivery path. It does not make Production ordinary policy administration reachable and does not modify `LaravelDurablePolicyAdministrationRepository` runtime restrictions.

## Trust boundary

The executor is stored at:

`.github/workflows/final-shift-close-permission-provisioning.yml`

It is manual `workflow_dispatch` only and can run only from current canonical `main`.

It uses the fixed GitHub Environment:

`final-shift-close-permission-provisioning`

Sprint104 does not claim that environment reviewers or secrets are already configured. A future authorized operator must configure them separately.

Required environment secrets are:

- `ONEQAY_PERMISSION_DB_HOST`;
- `ONEQAY_PERMISSION_DB_PORT`;
- `ONEQAY_PERMISSION_DB_DATABASE`;
- `ONEQAY_PERMISSION_DB_USERNAME`;
- `ONEQAY_PERMISSION_DB_PASSWORD`.

The target must be MySQL-compatible. Missing secrets, non-Production runtime classification, or application persistence being enabled causes fail-closed execution.

## Exact future target PR contract

A future permission-provisioning state PR must:

1. target `main`;
2. be based on the exact current canonical `main` used by the executor;
3. come from the same repository;
4. change exactly one path: `ops/final-shift-close/STATE.json`;
5. start from canonical migration #27 state `EXECUTED`;
6. change only permission provisioning from `NONE` to `PROVISIONED`;
7. preserve Final Shift Close feature state `INACTIVE`;
8. preserve permission ID `pos.shift.close`;
9. preserve default grant `NONE`;
10. preserve every other state field exactly.

## Explicit provisioning tuple

The future manual executor requires all of the following explicit inputs:

- target state-transition PR number;
- target exact head SHA;
- tenant ID;
- actor identity ID;
- actor organization ID;
- existing target role ID.

The executor does not choose a role and does not infer a default role. The target role must already exist in the exact tenant and must not be a protected control role. No role is created by this executor.

## Actor authority

The supplied actor must:

- exist as an identity in the exact tenant;
- have durable membership in the supplied organization;
- already hold tenant-scoped protected control authority through an existing tenant role carrying `authorization.policy.manage`.

The ceremony locks the relevant tenant, actor, organization membership, target role, actor tenant-role assignments, and relevant role-permission evidence inside its transaction before mutation.

This database control authority is additional to Product Owner operational authority. The ceremony therefore cannot replace or manufacture a tenant control principal.

## Required exact-head prerequisites

Before any database mutation, the target PR exact head must have current success for:

- `final-shift-close-permission-provisioning-authority`;
- `product-owner-merge-authority`;
- `Governance Required Checks`;
- `PHP Foundation Regression`;
- `M7.1 Application Regression`.

The Sprint102 sequencing gate is intentionally an evidence consumer. Before trusted provisioning evidence exists, the state transition remains fail-closed; after evidence success, Sprint102 must be reevaluated before merge.

## Database preflight

Before provisioning the executor verifies:

- Production runtime classification is retained;
- ordinary application persistence remains disabled;
- canonical MySQL-compatible driver;
- migrations table exists;
- migration #27 is recorded exactly once;
- `oneqay_pos_shift_close_evidence` exists;
- tenant, identity, organization membership, role, role-permission, role-assignment, and policy-mutation journal tables exist;
- target tenant exists;
- actor exists in the target tenant;
- actor organization membership exists;
- actor has tenant-scoped `authorization.policy.manage` control authority;
- target role exists;
- target role does not carry protected `authorization.policy.manage`;
- target role does not already carry `pos.shift.close`;
- deterministic Sprint104 policy mutation journal identity does not already exist.

If the permission or deterministic journal already exists, the workflow refuses to manufacture retrospective evidence.

## Canonical mutation semantics retained

The operational transaction constructs canonical:

- `TenantId`;
- `PlatformIdentityId`;
- `OrganizationId`;
- `RoleIdentifier`;
- `FinalShiftClosePermission::identifier()`;
- `PolicyMutationId`;
- `VerifiedOrganizationalContext`;
- `DurablePolicyMutation::permissionGrant(...)`.

The canonical `DurablePolicyMutation::fingerprint(...)` result is stored unchanged in the policy journal. The operation recorded is canonical `PolicyMutationOperation::PERMISSION_GRANT` (`permission.grant`) with tenant scope and outcome `applied`.

The control-plane transaction writes exactly two durable facts atomically:

1. one `oneqay_policy_mutations` append-only audit row;
2. one `oneqay_role_permissions` row for the exact `tenant_id + role_id + pos.shift.close` tuple.

Any exception rolls back the complete transaction. There is no application-runtime-class override and no partial success evidence.

## No default grant

Before mutation, assignment counts for the target role are captured across:

- `oneqay_tenant_role_assignments`;
- `oneqay_organization_role_assignments`;
- `oneqay_outlet_role_assignments`;
- `oneqay_device_role_assignments`.

The same counts must remain exact after the permission grant. The executor does not insert, delete, or update any role assignment.

Consequently:

`FINAL_SHIFT_CLOSE_PERMISSION_DEFAULT_GRANT = NONE`

remains true.

Provisioning a permission onto an existing role is not equivalent to assigning that role to an actor.

## Durable evidence verification

Within the same transaction, the executor verifies:

- the new role-permission row exists exactly once;
- the policy journal row has the exact tenant, actor, role, permission, operation, scope, canonical fingerprint, and `applied` outcome;
- target-role assignment counts are unchanged.

Only after the operational transaction commits successfully is the target exact-head status written as:

`final-shift-close-permission-provisioning-evidence = success`

The executor first publishes `pending`; execution or verification failure produces `failure`.

## Separation from later operational boundaries

Provisioning `pos.shift.close` does not enable Final Shift Close by itself. Runtime authorization remains deny-by-default and requires an actor to actually hold a role carrying the permission in an allowed verified scope.

Sprint104 does not:

- assign the target role to any identity;
- create a default/global/wildcard/cross-tenant grant;
- enable ordinary Production policy administration;
- change the Local/Test/CI runtime restriction on durable policy administration;
- enable `ONEQAY_POS_SHIFT_CLOSE_ENABLED`;
- execute any migration;
- deploy application source;
- activate Technical Preview;
- activate Production;
- publish a release;
- activate the updater.

## Canonical boundaries after Sprint104 source publication

`FINAL_SHIFT_CLOSE_PERMISSION_PROVISIONING_EVIDENCE_PRODUCER = SOURCE_MATERIALIZED`

`FINAL_SHIFT_CLOSE_PERMISSION_PROVISIONING_EXECUTOR_TRIGGER = MANUAL_MAIN_ONLY`

`FINAL_SHIFT_CLOSE_PERMISSION_PROVISIONING_RUNTIME_CLASS = PRODUCTION_PRESERVED`

`FINAL_SHIFT_CLOSE_PERMISSION_PROVISIONING_APPLICATION_PERSISTENCE = DISABLED`

`FINAL_SHIFT_CLOSE_PERMISSION_PROVISIONING_EVIDENCE_CONTEXT = final-shift-close-permission-provisioning-evidence`

`FINAL_SHIFT_CLOSE_PERMISSION_DEFAULT_GRANT = NONE`

`SPRINT104_LIVE_PROVISIONING = NOT_PERFORMED`

`MIGRATION_27_LIVE_EXECUTION = NOT_AUTHORIZED`

`FINAL_SHIFT_CLOSE_PERMISSION_PROVISIONING = NONE`

`FINAL_SHIFT_CLOSE_FEATURE_ACTIVATION = INACTIVE`

`TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY = NOT_GRANTED`

`PRODUCTION_AUTHORITY = NOT_GRANTED`

`UPDATER_ACTIVATION_AUTHORITY = NOT_GRANTED`

Sprint104 changes no application, infrastructure, provider, route, UI, bootstrap, config, migration, canonical operational state, deployment, release, Technical Preview, Production, or updater source/state beyond the new executor, its qualification workflow, and this documentation.
