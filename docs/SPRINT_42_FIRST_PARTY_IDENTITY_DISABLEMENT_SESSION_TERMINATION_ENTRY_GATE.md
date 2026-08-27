# Sprint42 First-Party Identity Disablement Session Termination Entry Gate

## Status

**ENTRY GATE / CONCERN SELECTED / SOURCE NOT AUTHORIZED**

Date: **2026-08-27**

Attribution: **Lab | zefry**

## Canonical baseline

This entry gate is selected from canonical main:

`02cd617709ff7305d9c38d0f6853f163e297f021`

Canonical tree:

`5b4ea67d26111475025e18df8ed808da6d39c022`

GitHub signature:

**verified / valid**

The post-Sprint41 canonical reconciliation is already published through PR #324 and the exact Sprint42 entry-gate historical-preservation predecessor is published through PR #326.

## Selected concern

**Sprint42 First-Party Identity Disablement Session Termination Foundation**

The concern is deliberately narrow:

> When an authorized Sprint41 identity-authentication disablement produces a fresh durable `true -> false` transition for an eligible ordinary identity, the exact target identity's active first-party logical session authorities must be terminated server-side as part of the same bounded security outcome before disablement success is exposed.

Sprint42 is selected to close the remaining server-authoritative session-invalidation gap after Sprint41 disablement while preserving Sprint40 request-time eligibility revalidation as an independent defense.

## Architectural basis

DEC-006 requires server-authoritative rotation, revocation, and re-evaluation after material security events including user suspension and administrator/security revocation.

Current canonical composition already provides:

- Sprint36 durable logical session authority, inventory, selective revocation, revoke-others, and logout;
- Sprint37 exact-owner revoke-all semantics;
- Sprint38 idle and absolute session lifetime controls;
- Sprint39 tenant-membership and organizational-access revalidation;
- Sprint40 current identity authentication-eligibility revalidation;
- Sprint41 authorized disable-only identity authentication-eligibility administration.

Sprint41 intentionally does **not** administrator-revoke the target identity's existing durable session rows.

Sprint42 selects only the missing composition between a fresh Sprint41 disablement and durable termination of the target identity's active first-party logical sessions.

## Frozen identity and tenant boundary

The actor and target remain governed by the existing Sprint41 administration boundary.

The actor must continue to be derived from the existing authenticated, tenant-scoped, policy-administration context and existing `AdministrationPermission::MANAGE` authority.

The target remains:

- the exact Sprint41 target identity;
- in the exact actor tenant;
- an ordinary first-party identity;
- not the actor identity;
- not a protected-control identity.

Sprint42 does not introduce a second target selector.

No caller-supplied tenant, session authority ID, public session handle, organization, outlet, device, role, permission, or owner selector becomes authority.

## Frozen termination semantics

For a fresh Sprint41 disablement transition whose durable eligibility outcome is `applied`:

- the exact target identity must remain disabled;
- every active, unrevoked first-party logical session authority for the exact target tenant + identity must transition monotonically to revoked;
- the administrator actor's own session authority must not be revoked merely because the target is disabled;
- another identity in the same tenant must remain untouched;
- a same-text identity in another tenant must remain untouched;
- expired or already-revoked target authorities must not be resurrected or converted back to active state;
- organization, outlet, and device coordinates on target session rows do not widen or narrow the exact tenant + target-identity termination set.

The future implementation must not report successful fresh disablement while leaving an active target logical authority because a termination sub-operation failed.

The exact transaction composition, repository shape, concurrency behavior, and rollback/failure mechanics are deferred to the separately governed schema/source-envelope gate, but any selected design must remain fail-closed and must not expose a split-success security state.

## Sprint40 preservation

Sprint40 request-time authentication-eligibility revalidation remains mandatory and independent.

Session termination must not replace, disable, weaken, or bypass Sprint40.

A stale client retaining framework-session state after durable termination must continue to fail closed through the existing server-authoritative request path.

## Sprint36-Sprint39 preservation

Sprint42 must preserve:

- Sprint36 opaque session-handle separation and exact tenant + identity ownership;
- Sprint36 revoke-one, revoke-others, inventory, and canonical logout behavior;
- Sprint37 self-scoped `POST /auth/sessions/revoke-all` semantics;
- Sprint38 idle TTL of exactly **7200 seconds**;
- Sprint38 absolute TTL of exactly **43200 seconds**;
- Sprint39 current tenant membership and exact organization/outlet/device revalidation;
- `session_control` privileged step-up freshness of exactly **300 seconds**.

Sprint42 does not convert the existing self-service revoke-all endpoint into an administrator-targeted endpoint.

## Public delivery boundary

No new public route, API endpoint, request payload, query parameter, path parameter, form field, or caller-selected session selector is selected by this entry gate.

The selected concern composes only with the already-published Sprint41 administrative disablement operation.

Any independent administrator-targeted session-management API remains separately governed and is not authorized here.

## Replay and idempotency boundary

Sprint41 mutation-ID replay and conflict semantics remain binding.

Sprint42 must not weaken:

- exact mutation-ID replay;
- mutation fingerprint binding;
- `applied` versus `no_change` outcome semantics;
- conflict denial for the same mutation ID with different fingerprint evidence.

The exact treatment of historical/pre-Sprint42 `no_change` rows and exact replay interaction with session-termination evidence must be frozen by the later schema/source-envelope gate before source implementation.

No replay or retry may reactivate an identity or restore a revoked session authority.

## Audit and evidence boundary

Security evidence for Sprint42 must remain server-derived, secret-free, tenant-bound, and target-identity-bound.

The existing Sprint41 journal remains canonical evidence for identity disablement.

The existing Sprint36/Sprint37 session audit vocabulary was designed around self-owner session control and must not be silently repurposed with ambiguous cross-identity actor semantics.

Therefore this entry gate does **not** select:

- a new audit event name;
- reuse of `all_sessions_revoked` for administrator-targeted disablement;
- a new journal/table/column;
- a migration #16.

The later schema/source-envelope gate must explicitly decide the minimum durable evidence shape, including whether the concern can remain no-schema-change or requires a separately bounded additive migration.

## Schema decision

**DEFERRED / NO MIGRATION #16 SELECTED BY THIS ENTRY GATE**

Canonical source migrations remain exactly **#1 through #15**.

Migrations #1 through #15 remain immutable for this entry-gate publication.

This entry gate does not create, modify, execute, or authorize migration #16.

Rollback remains unauthorized.

## Runtime boundary

Any later Sprint42 source implementation remains bounded to:

**Local / Test / CI only**

unless a separate lifecycle authority explicitly changes that posture.

Technical Preview remains:

**NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED**

Sprint41 source and migration #15 remain unactivated/unapplied in Technical Preview.

Production remains:

**NO-GO / NOT AUTHORIZED**

Updater remains:

**DISABLED / UNWIRED**

Deployment and release remain:

**NOT AUTHORIZED**

## Explicit exclusions

Sprint42 entry-gate authority does not select or authorize:

- identity re-enable/reactivation;
- timed or automatic reactivation;
- protected-control identity disablement;
- last-administrator or break-glass lifecycle;
- bulk identity disablement;
- independent administrator session inventory;
- independent administrator revoke-one/revoke-others/revoke-all endpoints;
- cross-tenant or global identity logout;
- password change/reset/overwrite;
- credential epoch mutation;
- TOTP/recovery/factor mutation;
- tenant-membership mutation;
- organization/outlet/device grant mutation;
- new role or permission identifiers;
- API/mobile bearer-token revocation;
- external directory/HR/SCIM/SSO lifecycle;
- notification/email/SMS delivery;
- background jobs;
- new feature flags or environment variables;
- Technical Preview activation;
- Production activation;
- updater wiring;
- deployment;
- release.

## Required next gate

Before any Sprint42 source mutation, a separately published **Sprint42 schema/source-envelope gate** must freeze at minimum:

1. exact schema decision: no-schema-change or explicitly bounded additive migration;
2. exact source/workflow/test/document changed-file envelope and sorted-path fingerprint;
3. exact durable transaction/ordering semantics between Sprint41 disablement and target-session termination;
4. exact repository/application contract for cross-identity administrative termination without widening self-service session control;
5. exact replay and `no_change` composition;
6. exact secret-free audit/evidence vocabulary and storage boundary;
7. exact failure/concurrency behavior;
8. preservation tests for Sprint36 through Sprint41;
9. explicit proof that no reactivation, protected-control lifecycle, Preview, Production, updater, deployment, or release authority is introduced.

## Authority boundary

This document selects the Sprint42 concern only.

It does **not** authorize source implementation, schema mutation, migration #16, route/API changes, dependency changes, runtime activation, Technical Preview, Production, updater, deployment, or release.

Unknown future shapes remain outside authority and must fail closed.
