# Sprint104 Final Shift Close Permission Provisioning Evidence

Author by Lab | zefry

## Purpose

Sprint104 materializes the trusted execution-evidence producer for the second Final Shift Close operational transition reserved by Sprint102: explicit bounded provisioning of `pos.shift.close` to one existing same-tenant role.

Sprint104 does **not** dispatch the executor, provision any permission, execute migration #27, change the canonical operational state, enable Final Shift Close, deploy, release, activate Technical Preview, activate Production, or activate the updater.

## Canonical policy path

The executor does not perform a direct SQL permission insert. It boots the canonical oneQay Laravel application and delegates the mutation to:

`DurablePolicyAdministrationService`

using:

`DurablePolicyMutation::permissionGrant(...)`

and the canonical identifier returned by:

`FinalShiftClosePermission::identifier()`

This preserves the existing policy-administration authorization, protected-control, transaction, replay, journal, and desired-state verification behavior rather than creating an independent mutation model.

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

The operational target is forced to MySQL-compatible persistence. The workflow uses `APP_ENV=ci` only to satisfy the canonical repository runtime boundary while connecting to the separately protected operational database through environment secrets; this does not activate a CI database or Synthetic Technical Preview runtime.

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

The executor does not choose a role and does not infer a default role.

The target role must already exist in the exact tenant and must not be a protected control role. No role is created by this executor.

## Actor authority

The supplied actor must:

- exist as an identity in the exact tenant;
- have durable membership in the supplied organization;
- already hold tenant-scoped protected control authority through an existing tenant role carrying `authorization.policy.manage`.

This requirement is additional to Product Owner operational authority. The operational ceremony therefore cannot bypass the canonical tenant control plane.

Organization membership is used to construct a valid `VerifiedOrganizationalContext`; the actual `permission.grant` mutation remains tenant-scoped under the canonical policy model.

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

## Mutation semantics

The only business mutation is:

`permission.grant`

for:

`pos.shift.close`

to the explicit existing `tenant_id + role_id` tuple.

The executor requires the canonical administration service outcome to be exactly `applied`. A `no_change` result is rejected because Sprint104 evidence is intended to prove one fresh bounded provisioning operation.

No role assignment is created, deleted, or changed. Before and after counts for the exact target role are compared across tenant, organization, outlet, and device role-assignment tables.

Consequently:

`FINAL_SHIFT_CLOSE_PERMISSION_DEFAULT_GRANT = NONE`

remains true.

## Durable audit evidence

The canonical `oneqay_policy_mutations` journal must contain the deterministic Sprint104 mutation with:

- exact tenant;
- exact actor identity;
- operation `permission.grant`;
- scope `tenant`;
- exact role;
- permission `pos.shift.close`;
- outcome `applied`;
- canonical SHA-256 payload fingerprint.

The final role-permission row must exist exactly once.

Only after those checks succeed is the target exact-head status written as:

`final-shift-close-permission-provisioning-evidence = success`

The executor first publishes `pending`; execution or verification failure produces `failure`.

## Separation from later operational boundaries

Provisioning `pos.shift.close` does not enable Final Shift Close by itself. Runtime authorization remains deny-by-default and requires an actor to actually hold a role carrying the permission in an allowed verified scope.

Sprint104 does not:

- assign the target role to any identity;
- create a default/global/wildcard/cross-tenant grant;
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
