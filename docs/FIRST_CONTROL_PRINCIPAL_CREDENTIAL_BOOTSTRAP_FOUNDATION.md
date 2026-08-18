# First-Control-Principal Credential Bootstrap Foundation

## Status

- Sprint: **29**
- Scope: **Local/Test/CI only**
- Delivery boundary: **explicitly armed interactive console ceremony**
- Schema: **NO_SCHEMA_CHANGE**
- Canonical migrations: **exactly #1–#8**
- Migration #9: **NOT AUTHORIZED / DOES NOT EXIST**
- Technical Preview: **DENIED / NO_SCHEMA_CHANGE**
- Production: **NO-GO / NOT AUTHORIZED**
- Updater: **DISABLED / UNWIRED**
- Attribution: **Lab | zefry**

## Purpose

Sprint 29 closes the single circular dependency left after Sprint 28: the exact Sprint 23 initial tenant control principal has no credential yet, cannot log in through Sprint 27 without a credential, and cannot use Sprint 28 self-enrollment because Sprint 28 correctly requires a different already-authenticated tenant-control issuer.

Sprint 29 establishes that first credential without creating a permanent alternate authentication path.

## Trust root

The bootstrap trust root is deliberately outside HTTP authentication and consists of all of the following:

1. trusted operator access to the Local/Test/CI console runtime;
2. runtime class exactly `local`, `test`, or `ci`;
3. durable application persistence explicitly enabled;
4. `ONEQAY_FIRST_CONTROL_CREDENTIAL_BOOTSTRAP_ENABLED=true` explicitly armed;
5. immutable Sprint 23 successful initial-provisioning evidence for the tenant;
6. the exact Sprint 23/24 canonical protected-control role graph;
7. the exact Sprint 23 principal still owning the protected-control assignment;
8. no existing Sprint 26 password credential for that principal;
9. no active Sprint 28 initial-password enrollment for that principal.

Any failed condition denies bootstrap.

## Console contract

The only Sprint 29 delivery surface is:

`oneqay:identity:first-control-credential-bootstrap {tenant_id}`

The command accepts the tenant ID and nothing else.

It does not accept:

- target identity;
- password;
- password hash;
- role or permission;
- token/session value;
- SQL/table input;
- updater/release/deployment authority.

The password is entered twice through hidden interactive prompts.

Confirmation mismatch fails closed before the Application service is called.

Successful sanitized output is limited to:

`ONEQAY_FIRST_CONTROL_CREDENTIAL_BOOTSTRAP|STATE=applied`

Failure output is limited to:

`ONEQAY_FIRST_CONTROL_CREDENTIAL_BOOTSTRAP_FAILED`

Target identity, password, password hash, SQL errors, stack traces, DSN, host, username, and secret values are not printed.

## Target derivation

The operator cannot select an identity.

The Infrastructure adapter resolves the target from:

`oneqay_initial_tenant_admin_provisionings`

for the exact tenant and requires the durable row to retain:

- `outcome = applied`;
- role `authorization-policy-administrator`;
- permission `authorization.policy.manage`;
- a non-empty exact `identity_id`.

The adapter then independently verifies that exact identity still exists in the tenant and still owns the exact protected-control assignment.

If the initial Sprint 23 principal has been delegated away or otherwise no longer owns protected control, Sprint 29 denies bootstrap instead of retargeting another identity.

This prevents Sprint 29 from becoming an emergency administrator-recovery mechanism.

## Protected-control revalidation

Immediately before credential insertion the durable adapter requires:

- exact protected role exists;
- protected role has exactly one permission;
- that permission is exactly `authorization.policy.manage`;
- no alternate role in the tenant carries that permission;
- the exact Sprint 23 principal owns the exact protected role assignment.

Role, permission, assignment, Sprint 23 journal, and Sprint 24 journal are read-only to Sprint 29.

## Password contract

Sprint 29 reuses the Sprint 28 initial-password bounds:

- minimum 12 bytes;
- maximum 4096 bytes;
- opaque case-sensitive input;
- no trimming;
- no lowercasing;
- no Unicode normalization by application code;
- no composition/class rule.

Infrastructure hashes with:

`password_hash($password, PASSWORD_DEFAULT)`

Plaintext password is never persisted.

The password and hash are marked/treated as sensitive and must not appear in application output or non-credential durable state.

## Credential mutation boundary

Sprint 29 writes only:

`oneqay_identity_password_credentials`

and only through one insert for the exact `(tenant_id, identity_id)` target derived from Sprint 23.

There is no update, upsert, replace, delete, reset, rotation, revocation, recovery, or overwrite path.

The existing Sprint 26 composite primary key remains the structural final race boundary. A duplicate or concurrent attempt cannot replace the credential and raw database uniqueness detail is not exposed by the console command.

## Active Sprint 28 enrollment interlock

If the exact target has an active Sprint 28 initial-password enrollment, Sprint 29 denies bootstrap.

Sprint 29 does not consume, revoke, modify, or delete Sprint 28 enrollment state.

This avoids two concurrent first-credential ceremonies owning the same target.

## No new schema

Sprint 29 adds no migration and no bootstrap-token table.

Canonical migrations remain exactly #1–#8.

The design intentionally reuses:

- Sprint 23 journal as durable target provenance;
- Sprint 24 protected-control assignment as current authority-state evidence;
- Sprint 26 credential table and composite primary key as the credential ownership/race invariant.

A second bootstrap state machine would add replay state and attack surface without providing an invariant that is missing from the existing model.

## Application boundary

Application source owns:

- password byte-length validation;
- transactional orchestration;
- bounded violation mapping;
- the framework-independent repository contract.

Application source does not import Laravel DB/query/schema/HTTP/session/console mechanics.

## Infrastructure boundary

`LaravelFirstControlPrincipalCredentialBootstrapRepository` owns the durable reads and exact credential insert.

It independently enforces:

- persistence enabled;
- Local/Test/CI runtime;
- explicit feature arm;
- Sprint 23 provenance;
- same-tenant identity existence;
- canonical protected-control graph;
- target protected assignment;
- no existing credential;
- no active Sprint 28 enrollment.

It never mutates identity, organization, role, permission, assignment, provisioning journal, protected-control journal, enrollment, session, updater, or deployment state.

## Feature arming

Configuration key:

`oneqay.first_control_principal_credential_bootstrap.enabled`

Environment input:

`ONEQAY_FIRST_CONTROL_CREDENTIAL_BOOTSTRAP_ENABLED`

Source-level default is `false`.

The command is registered only when both the runtime and feature-arm checks allow it. The Infrastructure adapter repeats those checks before durable mutation.

No `.env` or `.env.*` repository file is introduced or changed; the repository-wide env-file mutation prohibition remains intact.

## Session boundary

Successful bootstrap does not authenticate the principal and does not create a session.

The target must then use the ordinary Sprint 27 `/auth/login` path, which delegates credential verification to Sprint 26 and establishes the existing verified tenant/organizational session context.

Once a tenant-control administrator is normally authenticated, Sprint 28 may issue initial-password enrollment for a different same-tenant identity according to Sprint 28 rules.

## Runtime exclusions

The Sprint 29 command must not be registered in Preview or Production, even if the feature-arm environment input is present.

Technical Preview remains `NO_SCHEMA_CHANGE` and receives no Sprint 29 credential activation.

Production remains `NO-GO / NOT AUTHORIZED` and receives no bootstrap command, credential mutation, login activation, schema execution, deployment, or release authority.

## Explicit non-scope

Sprint 29 does not implement or authorize:

- password change;
- password reset or forgot-password;
- credential recovery;
- credential rotation/revocation/deletion;
- emergency protected-control recovery;
- MFA/TOTP/passkeys/WebAuthn;
- recovery codes;
- OAuth/OIDC/SAML/social login;
- API/bearer tokens;
- email/SMS delivery;
- remember-me;
- public self-registration;
- public bootstrap HTTP endpoints;
- default administrator password;
- updater activation;
- deployment or GitHub Release;
- Phase 0 Exit;
- Production readiness promotion.

## Security invariant

> A tenant's exact Sprint 23 initial control principal may receive its first password credential only through an explicitly armed Local/Test/CI hidden-input console ceremony, with the target derived from immutable Sprint 23 evidence, current protected-control state independently revalidated, and one insert into the existing Sprint 26 credential table; no alternate target, credential overwrite, HTTP bootstrap, new schema, Preview/Production activation, or emergency-recovery authority is created.

Attribution: **Lab | zefry**
