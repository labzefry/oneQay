# Ordinary Policy Administration Delivery Foundation

## Status

Sprint 25 qualification foundation for oneQay.

Attribution: **Lab | zefry**

## Purpose

This foundation exposes the existing Sprint 22 ordinary policy-administration Application service through one bounded first-party HTTP delivery route for Local/Test/CI qualification only.

It does not create a new authorization model, authentication provider, persistence model, protected-control lifecycle, Preview control plane, Production authority, or updater capability.

## Route

The only Sprint 25 route is:

`POST /administration/policy/mutations`

Route name:

`policy-administration.mutate`

The route remains in Laravel's normal `web` middleware stack, including session and CSRF protection.

No Sprint 25 GET administration page, login endpoint, public API, batch endpoint, webhook, command, or background mutation surface exists.

## Session boundary

Sprint 25 consumes server-owned first-party session state only. It does not create a login or write authentication session attributes.

Session attributes are namespaced under `oneqay.auth.` and include the authenticated identity plus tenant/organizational context hints.

Request body, query, headers, and route parameters cannot replace these session-owned actor/context values.

Every request reconstructs bounded server-side identity/tenant value objects and then performs durable verification through existing tenant-membership and organizational-relationship services.

The resulting `VerifiedOrganizationalContext` is request-scoped and is cleared after every request in `finally` semantics.

## Runtime boundary

The delivery middleware permits only:

- `local`;
- `test`;
- `ci`.

Preview and Production receive HTTP 404 from the Sprint 25 route boundary.

Durable persistence controls remain independent defense in depth, and `ONEQAY_PERSISTENCE_ENABLED=false` remains the default.

## Closed mutation vocabulary

The HTTP payload is limited to:

- `mutation_id`;
- `operation`;
- `role`;
- `permission` when required by a permission operation;
- `target_identity` when required by an assignment/revocation operation.

Laravel's framework `_token` CSRF field is removed as transport metadata before the strict business payload is parsed. All other unknown fields are rejected.

Only existing Sprint 22 operations are accepted:

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

Scope is derived from the server-verified actor context by existing `DurablePolicyMutation` factories. Scope IDs are never accepted from the mutation payload.

## Existing Sprint 22 authority

`PolicyAdministrationDeliveryService` delegates every accepted command to the existing `DurablePolicyAdministrationService`.

Sprint 25 does not duplicate or replace:

- authorization evaluation;
- scope containment;
- protected-control rejection;
- target eligibility;
- mutation replay/conflict rules;
- transaction handling;
- policy mutation persistence.

Application delivery command parsing remains framework/database independent.

## Protected-control exclusion

Ordinary delivery cannot grant or revoke `authorization.policy.manage`, and cannot assign/revoke a role carrying that control permission.

Sprint 23 initial administrator provisioning remains unreachable.

Sprint 24 `control.administrator.delegate` and `control.administrator.revoke` remain unreachable.

Emergency protected-control recovery remains outside Sprint 25.

## CSRF

CSRF protection is inherited from Laravel's existing `web` middleware group.

Sprint 25 adds no CSRF exception and no bypass header.

The disposable regression proves an authenticated session POST without a valid CSRF token returns 419 before policy mutation evidence is written.

## Durable context verification

The application composition root binds:

- `TenantMembershipVerifier` to `LaravelTenantMembershipVerifier`;
- `OrganizationalRelationshipVerifier` to `LaravelOrganizationalRelationshipVerifier`;
- `OrganizationalContextStore` to `RequestOrganizationalContextStore`.

Those adapters reuse the existing guarded durable organizational-access repository. The Delivery layer performs no direct database access.

## Response boundary

Success returns only bounded metadata:

- `status`;
- `outcome`;
- `correlation_id`.

Failure returns a generic error code/message plus correlation ID. Exception messages, SQL, stack traces, session values, target membership graphs, and database details are not exposed.

## Schema and dependencies

Sprint 25 introduces no migration and no dependency change.

Canonical migrations remain exactly #1–#6.

## Technical Preview

Technical Preview remains `NO_SCHEMA_CHANGE`.

The Sprint 25 route is not under `/technical-preview`, and Sprint 25 Application/Delivery classes are not referenced by Preview controllers or services.

The governed Technical Preview release artifact continues to exclude migrations and remains separate from Sprint 25 control-plane qualification.

## Production and updater

Production remains `NO-GO / NOT AUTHORIZED`.

Updater remains `DISABLED / UNWIRED`.

Tenant policy-administration authority does not imply updater, release, deployment, rollback, infrastructure, or platform authority.

## Regression coverage

The Sprint 25 disposable HTTP regression uses a temporary SQLite database, existing migrations #1–#6, and synthetic facts only. It proves:

- actual CSRF rejection;
- server-owned session context;
- durable membership and relationship verification;
- ordinary role create;
- ordinary permission grant;
- ordinary device assignment and revocation;
- exact replay;
- conflicting replay rejection;
- unknown authority-field rejection;
- protected-control permission rejection;
- protected-control role rejection;
- Sprint 24 operation rejection;
- unknown operation rejection;
- foreign-tenant target rejection;
- actor-without-authority rejection;
- narrower-scope escape rejection;
- missing session rejection;
- invalid membership/session context rejection;
- Preview/Production route denial;
- denied-attempt journal non-mutation;
- request-scoped context cleanup.

Prior Sprint 21–24, M7 tenant isolation, identity context, Preview, release-artifact, and updater security regressions remain mandatory.

Attribution: **Lab | zefry**
