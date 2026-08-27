# First-Party Identity Authentication Eligibility Administration Foundation

## Status

**SPRINT41 SOURCE FOUNDATION / LOCAL-TEST-CI ONLY / DISABLE-ONLY**

Date: **2026-08-27**

Attribution: **Lab | zefry**

This foundation implements the bounded Sprint41 concern selected and governed by the published entry gate and schema/source-envelope gate.

The only administrative business transition implemented is:

**`first_party_authentication_enabled: true -> false`**

No `false -> true` reactivation authority exists.

## Canonical authority

Sprint41 source is bounded to the exact twelve-path envelope governed by the Sprint41 schema/source-envelope gate.

Sorted newline-terminated path SHA-256:

`b2c5fc10a8baa2d56991d6dbd36b0407159d70953654ef322a9a11d23660489b`

The required source-preservation predecessor was published before this implementation.

Its seven-workflow path fingerprint is:

`f3f8b3ad0cca378307000ca242c4b9a4a8a7ab967d1649a5cd6408837001364c`

Unknown implementation shapes remain outside Sprint41 authority.

## Administrative command

Sprint41 exposes exactly one mutation route:

`POST /administration/identities/{identity_id}/authentication-disablement`

Route name:

`identity.authentication-eligibility.disable`

The request payload accepts exactly one field:

`mutation_id`

No tenant, actor, organization, role, permission, enabled/disabled state, force, reason, session, or reactivation field is accepted from caller input.

The route requires:

- active first-party session authority;
- existing policy-administration session context;
- exact server-derived organizational actor context;
- canonical throttling;
- existing privileged step-up behavior through the policy-administration middleware where configured.

## Authorization

The actor must hold the existing tenant-scoped protected-control permission:

`AdministrationPermission::MANAGE`

Sprint41 creates no new permission identifier.

The target must:

- exist in the actor's exact tenant;
- be an ordinary first-party identity;
- not equal the actor identity;
- not hold tenant-scoped protected-control authority.

Missing, cross-tenant, self, protected-control, malformed, unauthorized, storage-unavailable, or contradictory target evidence fails closed.

Public rejection uses one generic error code:

`IDENTITY_AUTHENTICATION_ELIGIBILITY_ADMINISTRATION_REJECTED`

The response does not expose whether a denied target exists or is protected.

## Application contracts

Sprint41 introduces:

- `IdentityAuthenticationEligibilityMutationId`;
- `FirstPartyIdentityEligibilityAdministrationRepository`;
- `FirstPartyIdentityEligibilityAdministrationService`;
- `FirstPartyIdentityEligibilityAdministrationViolation`;
- `FirstPartyIdentityEligibilityAdministrationController`;
- `LaravelFirstPartyIdentityEligibilityAdministrationRepository`.

The service exposes disable-only behavior.

It does not expose enable, reactivate, bulk mutation, session revocation, password mutation, factor mutation, membership mutation, or grant mutation.

## Durable state transition

The adapter performs an exact same-tenant conditional update against:

`oneqay_identities.first_party_authentication_enabled`

The only application update is:

`true -> false`

An already-disabled target remains disabled and produces `no_change`.

A fresh enabled target produces `applied`.

No upsert is used.

No missing identity is created.

No other identity or tenant is mutated.

## Mutation identifier and replay

`mutation_id` is canonicalized to lowercase and limited to the closed `[a-z0-9_-]` identifier vocabulary with maximum length 64.

The durable fingerprint binds:

- tenant;
- actor identity;
- target identity;
- operation `disable`;
- existing administration permission;
- tenant scope.

Replay behavior is deterministic:

- same mutation ID + same fingerprint returns the prior outcome;
- same mutation ID + different fingerprint fails closed as mutation conflict;
- separate mutations against an already-disabled target converge to `no_change`;
- no retry can restore enabled state.

## Migration #15

Sprint41 introduces exactly one forward-only migration:

`0000_00_00_000015_create_identity_authentication_eligibility_administration_journal.php`

It creates exactly one table:

`oneqay_identity_authentication_eligibility_mutations`

The table contains only:

- `tenant_id`;
- `mutation_id`;
- `actor_identity_id`;
- `target_identity_id`;
- `operation`;
- `payload_fingerprint`;
- `outcome`;
- `occurred_at_unix`.

Primary key:

`(tenant_id, mutation_id)`

Allowed values:

- operation: `disable`;
- outcome: `applied` or `no_change`.

Migration #15 does not alter migration #14 or the eligibility column.

Migrations #1 through #14 remain immutable.

Rollback remains unauthorized.

## Atomicity

The identity transition and journal insertion occur inside the existing canonical `PersistenceTransaction`.

A fresh journal row is committed only with the matching durable outcome.

Transaction, relationship, storage, runtime, and persistence failures fail closed.

The adapter revalidates actor authority and target eligibility inside the transaction boundary.

## Secret-free audit evidence

The Sprint41 journal contains no:

- password or password hash;
- credential epoch;
- TOTP secret;
- factor epoch;
- recovery secret/code;
- session authority secret;
- CSRF token;
- arbitrary reason text;
- request-body metadata;
- external identity data.

The fingerprint contains only canonical non-secret identifiers and fixed operation/permission/scope vocabulary.

## Preserved identity state

Sprint41 does not modify:

- password credentials;
- credential epoch;
- TOTP factors or secrets;
- factor epoch;
- recovery state;
- tenant membership;
- organization/outlet/device grants;
- role or permission assignments;
- logical first-party session rows;
- session audit rows.

## Composition with Sprint40

Sprint41 is the producer of current server-owned eligibility disablement.

Sprint40 remains the independent request-time consumer.

After a successful Sprint41 disablement, `LaravelFirstPartyIdentityEligibilityVerifier` observes the exact target as ineligible.

A previously issued first-party logical session does not gain grandfathered access: Sprint40 request-time eligibility revalidation remains mandatory on protected requests.

Sprint41 does not administrator-revoke the target's sessions.

## Preserved Sprint36-Sprint40 controls

Sprint41 does not replace or weaken:

- Sprint36 durable logical session authority and inventory/revocation;
- Sprint37 tenant-scoped self revoke-all;
- Sprint38 idle TTL **7200 seconds** and absolute TTL **43200 seconds**;
- Sprint39 current membership and organization/outlet/device revalidation;
- Sprint40 current identity authentication eligibility revalidation.

## Runtime boundary

Sprint41 source implementation is authorized for:

**Local / Test / CI only**

Technical Preview remains:

**NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED**

Migration #15 is **not authorized for Technical Preview application** by Sprint41 source publication.

Production remains:

**NO-GO / NOT AUTHORIZED**

Updater remains:

**DISABLED / UNWIRED**

Deployment and release remain:

**NOT AUTHORIZED**

## Explicit exclusions

Sprint41 source does not implement:

- re-enable/reactivation;
- timed reactivation;
- bulk disablement;
- self-service disablement;
- protected-control identity disablement;
- last-administrator lifecycle;
- break-glass lifecycle;
- administrator-targeted session revocation;
- password reset/change;
- credential epoch mutation;
- TOTP/recovery mutation;
- factor epoch mutation;
- membership mutation;
- organizational grant mutation;
- external directory/HR/SSO lifecycle;
- new feature flags;
- background jobs;
- Technical Preview activation;
- Production activation;
- updater wiring;
- deployment;
- release.

Any such requirement must return to a separately governed Product Owner boundary.

## Regression guarantees

The Sprint41 regression proves:

- exact twelve-path source envelope;
- exact migration #15 exclusivity;
- migrations #1-#14 immutability;
- Local/Test/CI fail-closed runtime;
- exact tenant control actor authorization;
- self/protected/cross-tenant/missing target denial;
- applied disablement;
- exact replay;
- conflicting mutation-ID denial;
- already-disabled `no_change`;
- convergence toward disabled state;
- unrelated tenant/identity preservation;
- credential/membership/session preservation;
- strict one-field delivery payload;
- generic public rejection;
- route/provider wiring;
- no enable/reactivation route or update;
- secret-free journal;
- preserved Sprint40 request-time eligibility behavior;
- unchanged runtime/deployment/updater/Production posture.

Publication of this source foundation does not authorize Technical Preview, Production, deployment, updater, or release activation.
