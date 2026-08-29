# Sprint43 First-Party Identity Authentication Eligibility Reactivation Entry Gate

## Status

**ENTRY GATE / CONCERN SELECTED / SOURCE NOT AUTHORIZED**

Date: **2026-08-29**

Attribution: **Lab | zefry**

## Canonical baseline

This entry gate is selected from canonical `main`:

`23c3ad1e920c04d8bca516e9e9737fe8bb0bbf80`

Canonical tree:

`e829f97237a754cb7e1f7fbc8bbe4b0eeb3abfd2`

GitHub signature:

**verified / valid**

The post-Sprint42 canonical reconciliation is published through PR #338, and the exact Sprint43 entry-gate preservation predecessor is published through PR #339.

## Selected concern

**Sprint43 First-Party Identity Authentication Eligibility Reactivation Foundation**

The concern is deliberately narrow:

> An authorized tenant-scoped policy administrator may restore first-party authentication eligibility for one exact ordinary same-tenant identity that is currently disabled, without restoring any prior logical session authority, organizational grant, membership, credential state, factor state, or privileged evidence.

Sprint43 selects only the missing governed inverse lifecycle transition after the published Sprint41 disable-only administration and Sprint42 exact-target active-session termination.

The intended identity-state direction is:

`first_party_authentication_enabled: false -> true`

This entry gate does not authorize source implementation of that transition.

## Architectural basis

DEC-006 establishes server-authoritative authentication state, revocation, and current authorization re-evaluation.

Current canonical composition already provides:

- Sprint36 durable logical session authority and self-service session controls;
- Sprint37 tenant-scoped revoke-all;
- Sprint38 fixed idle and absolute session lifetime;
- Sprint39 tenant-membership and organizational-access revalidation;
- Sprint40 request-time identity authentication-eligibility revalidation;
- Sprint41 authorized disable-only identity authentication-eligibility administration;
- Sprint42 exact-target active logical-session termination after successful disablement outcomes.

The remaining ordinary-identity lifecycle gap is a separately governed administrative path to restore authentication eligibility without undoing any security consequences of prior disablement.

## Frozen actor and target boundary

Sprint43 must inherit the existing tenant-scoped policy-administration authority model without broadening it.

The actor must remain server-derived from the existing authenticated tenant context and existing `AdministrationPermission::MANAGE` authority.

The target is limited to:

- one exact same-tenant first-party identity;
- an ordinary identity;
- not the actor identity;
- not a protected-control identity;
- currently disabled for first-party authentication eligibility.

No caller-supplied tenant may become authority.

No bulk target, wildcard, global identity selector, organization selector, outlet selector, device selector, session authority selector, credential selector, factor selector, or membership selector is selected.

## Frozen reactivation semantics

The selected state transition is only:

`false -> true`

A successful reactivation must mean only that the exact target identity becomes eligible to attempt a future fresh first-party authentication flow, subject to every other current control.

Reactivation must not:

- create a framework session;
- create a logical first-party session authority;
- clear or rewrite `revoked_at_unix` on any prior session row;
- revive a Sprint42-terminated authority;
- restore an expired authority;
- restore tenant membership;
- recreate organization/outlet/device relationships;
- restore role/permission grants;
- change a password or password hash;
- change credential epoch evidence;
- change TOTP or recovery factors;
- change factor epoch evidence;
- synthesize MFA evidence;
- synthesize privileged step-up evidence;
- issue an enrollment or recovery capability;
- auto-login the target;
- bypass current credential verification.

Fresh authentication remains mandatory after reactivation.

## Sprint40-Sprint42 preservation

Sprint40 request-time eligibility revalidation remains mandatory and independent.

Sprint41 disablement remains a dedicated disable-only administrative concern.

Sprint42 terminated logical session authorities remain monotonically revoked. Sprint43 must never make an old terminated authority valid again merely because current identity eligibility later becomes true.

Authentication eligibility, credential validity, factor validity, logical-session authority, tenant membership, and organization/outlet/device authorization remain independent server-authoritative controls.

## Public delivery boundary

The existing Sprint41 route:

`POST /administration/identities/{identity_id}/authentication-disablement`

must remain disable-only and must not be converted into a caller-controlled boolean toggle.

This entry gate does **not** select an exact Sprint43 public route, controller, request payload, response vocabulary, or mutation endpoint.

The later schema/source-envelope gate must freeze any delivery shape before source implementation.

No generic `enabled=true|false`, `active=true|false`, action parameter, mode parameter, or caller-selected operation flag is authorized by this entry gate.

## Replay and idempotency boundary

Reactivation must be deterministic, bounded, and replay-safe.

This entry gate does not yet freeze whether Sprint43 reuses the existing Sprint41 mutation journal shape or requires a separately bounded additive evidence structure.

The later schema/source-envelope gate must decide:

- mutation identifier requirements;
- exact operation fingerprinting;
- fresh `applied` versus `no_change` semantics;
- exact replay behavior;
- conflicting replay denial;
- concurrency behavior.

No replay, retry, or concurrent path may restore any revoked session authority or widen authorization.

## Audit and evidence boundary

Sprint43 security evidence must be:

- server-derived;
- tenant-bound;
- target-identity-bound;
- secret-free;
- deterministic;
- sufficient to distinguish reactivation from disablement.

This entry gate does not authorize silently repurposing a Sprint36/Sprint37 self-service session audit event.

It does not select a new table, journal, column, audit event, or migration.

The later schema/source-envelope gate must explicitly determine the minimum durable evidence shape.

## Schema decision

**DEFERRED / NO MIGRATION #16 SELECTED BY THIS ENTRY GATE**

Canonical source migrations remain exactly **#1 through #15**.

Migrations #1 through #15 remain immutable.

This entry gate does not create, modify, execute, or authorize migration #16.

Rollback remains unauthorized.

## Runtime boundary

Any later Sprint43 source implementation remains bounded to:

**Local / Test / CI only**

unless separate lifecycle authority explicitly changes that posture.

Technical Preview remains:

**NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED**

Sprint41 migration #15, Sprint42 source, and any future Sprint43 source remain unactivated/unapplied in Technical Preview unless separately authorized.

Production remains:

**NO-GO / NOT AUTHORIZED**

Updater remains:

**DISABLED / UNWIRED**

Deployment and release remain:

**NOT AUTHORIZED**

## Explicit exclusions

Sprint43 entry-gate authority does not select or authorize:

- automatic or timed reactivation;
- self-service reactivation;
- protected-control identity reactivation;
- protected-control disablement lifecycle;
- last-administrator or break-glass lifecycle;
- bulk reactivation;
- cross-tenant or global reactivation;
- identity creation or deletion;
- tenant-membership restoration;
- organization/outlet/device grant restoration;
- role/permission restoration;
- password change/reset/overwrite;
- credential epoch mutation;
- TOTP/recovery/factor mutation;
- session resurrection;
- automatic login;
- independent administrator session inventory;
- API/mobile bearer-token lifecycle;
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

Before any Sprint43 source mutation, a separately published **Sprint43 schema/source-envelope gate** must freeze at minimum:

1. exact schema decision and whether migration #16 remains unnecessary;
2. exact durable evidence/journal decision for reactivation;
3. exact mutation identifier, fingerprint, replay, conflict, and concurrency semantics;
4. exact public delivery boundary without converting Sprint41 disablement into a generic toggle;
5. exact application/repository contract;
6. exact authorization, self-target, protected-control, and same-tenant checks;
7. exact behavior for already-enabled targets;
8. exact proof that prior revoked/expired session authorities are never restored;
9. exact source/workflow/test/document changed-file envelope and sorted-path fingerprint;
10. preservation tests for Sprint36 through Sprint42;
11. explicit proof that no membership/grant/credential/factor restoration, Preview, Production, updater, deployment, or release authority is introduced.

## Authority boundary

This document selects the Sprint43 concern only.

It does **not** authorize source implementation, schema mutation, migration #16, route/API changes, dependency changes, runtime activation, Technical Preview, Production, updater, deployment, or release.

Unknown future shapes remain outside authority and must fail closed.
