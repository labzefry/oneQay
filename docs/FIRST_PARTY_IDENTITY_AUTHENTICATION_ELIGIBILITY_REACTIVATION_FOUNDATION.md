# First-Party Identity Authentication Eligibility Reactivation Foundation

## Status

**SPRINT43 SOURCE IMPLEMENTATION / LOCAL-TEST-CI ONLY / NOT ACTIVATED**

Date: **2026-08-29**

Attribution: **Lab | zefry**

## 1. Scope

Sprint43 implements only administrator-authorized restoration of first-party authentication eligibility for one exact ordinary identity in the actor's server-derived tenant.

The only new state transition is:

`first_party_authentication_enabled: false -> true`

This transition means the target may attempt a future fresh first-party authentication flow. It does not restore, create, or revive authentication authority.

## 2. Source envelope

The implementation is intentionally bounded to exactly nine source paths:

1. `.github/workflows/sprint43-first-party-identity-authentication-eligibility-reactivation-regression.yml`
2. `apps/web/app/Application/Identity/FirstPartyIdentityEligibilityAdministrationRepository.php`
3. `apps/web/app/Application/Identity/FirstPartyIdentityEligibilityAdministrationService.php`
4. `apps/web/app/Delivery/Http/Identity/FirstPartyIdentityEligibilityAdministrationController.php`
5. `apps/web/app/Infrastructure/Identity/LaravelFirstPartyIdentityEligibilityAdministrationRepository.php`
6. `apps/web/routes/web.php`
7. `apps/web/tests/first-party-identity-authentication-eligibility-administration.php`
8. `apps/web/tests/first-party-identity-authentication-eligibility-reactivation.php`
9. `docs/FIRST_PARTY_IDENTITY_AUTHENTICATION_ELIGIBILITY_REACTIVATION_FOUNDATION.md`

Sorted newline-terminated source-envelope SHA-256:

`3d0293362f451fe4bf472d0d2c38c3eec3d67df75b451ba5b273e8dbdb0f2eed`

Unknown or wider source shapes remain fail closed.

## 3. Schema and durable evidence

Sprint43 is:

**NO_SCHEMA_CHANGE**

Canonical migrations #1 through #15 remain immutable.

Migration #16 remains **NOT SELECTED**.

The implementation reuses:

- `oneqay_identities.first_party_authentication_enabled`;
- `oneqay_identity_authentication_eligibility_mutations`.

No new table, column, index, trigger, scheduler, outbox, audit table, or migration is introduced.

The existing journal now has two bounded operation values:

- `disable`;
- `reactivate`.

Successful outcomes remain exactly:

- `applied`;
- `no_change`.

## 4. Authorization boundary

Reactivation is authorized only from the existing server-derived tenant-scoped policy-administration context.

The actor must currently hold:

`AdministrationPermission::MANAGE`

The target must be:

- one exact identity;
- in the same server-derived tenant as the actor;
- an ordinary identity;
- not the actor;
- not a protected-control identity.

Caller-provided tenant, actor, role, permission, scope, operation, enabled state, organization, outlet, device, or session data never becomes authority.

Cross-tenant, self, protected-control, missing, malformed, unauthorized, persistence-disabled, disallowed-runtime, and uncertain storage states fail closed.

## 5. Dedicated API

Canonical disablement remains unchanged:

`POST /administration/identities/{identity_id}/authentication-disablement`

Route name:

`identity.authentication-eligibility.disable`

Sprint43 adds one separate route:

`POST /administration/identities/{identity_id}/authentication-reactivation`

Route name:

`identity.authentication-eligibility.reactivate`

The request body contains exactly:

`mutation_id`

No generic toggle API, caller-selected operation field, boolean state setter, force mode, timed reactivation, or bulk operation is introduced.

The reactivation route reuses the same active-session, throttling, and policy-administration context middleware boundary as disablement.

Public success responses remain minimal:

- `status`;
- deterministic `outcome`;
- correlation ID.

Public failure remains the existing generic identity-authentication eligibility administration rejection envelope.

## 6. Deterministic mutation binding

Sprint43 reuses:

`IdentityAuthenticationEligibilityMutationId`

The `reactivate` payload fingerprint is SHA-256 over the exact newline-delimited ordered values:

1. server-derived tenant ID;
2. server-derived actor identity ID;
3. exact target identity ID;
4. `reactivate`;
5. `AdministrationPermission::MANAGE`;
6. `tenant`.

The operation is therefore part of both journal evidence and fingerprint binding.

A mutation ID previously bound to `disable` cannot be reused for `reactivate`.

A mutation ID previously bound to `reactivate` cannot be reused for `disable`.

Conflicting or malformed durable evidence fails closed.

## 7. Applied, no-change, replay, and convergence

For a fresh valid disabled target, the adapter performs only the exact conditional update:

`first_party_authentication_enabled = 0 -> true`

and inserts one matching `reactivate` journal row in the same canonical persistence transaction.

The result is `applied`.

For a fresh valid already-enabled target, eligibility remains true and one matching journal row is recorded with `no_change`.

Exact replay returns the previously stored outcome, does not duplicate journal evidence, and does not rewrite current target state.

Distinct mutation IDs converge deterministically: at most one conditional false-to-true update can return `applied`; later valid mutations converge to `no_change`.

## 8. Session non-resurrection

Reactivation does not invoke the Sprint42 disablement session-termination repository.

It does not:

- create a framework session;
- create a logical first-party session;
- clear `revoked_at_unix`;
- rewrite a previously revoked session;
- revive an expired session;
- change issued, last-seen, or expiry timestamps;
- synthesize MFA, step-up, recovery, or enrollment evidence.

A Sprint42-revoked session remains revoked after reactivation.

An expired session remains expired and unchanged.

Fresh authentication remains mandatory.

Sprint41 disablement remains independent and continues to terminate exact-target active logical sessions through the existing Sprint42 composition.

## 9. Credential, factor, membership, and grant preservation

Reactivation does not modify:

- password or password hash;
- credential epoch;
- TOTP secret or confirmation state;
- factor epoch;
- recovery codes or recovery authority;
- tenant membership;
- organization membership;
- outlet access;
- device access;
- tenant, organization, outlet, or device role assignments;
- permissions;
- protected-control role state.

Reactivation is not password recovery, account recovery, factor replacement, membership restoration, role restoration, permission restoration, or authorization restoration.

## 10. Historical preservation

Sprint43 preserves the previously published behavior of:

- Sprint36 durable first-party session inventory and revoke operations;
- Sprint37 self-scoped revoke-all;
- Sprint38 idle TTL of 7200 seconds and absolute TTL of 43200 seconds;
- Sprint39 tenant membership and organization/outlet/device revalidation;
- Sprint40 request-time identity authentication eligibility revalidation;
- Sprint41 dedicated disable-only administration semantics;
- Sprint42 disablement-triggered exact-target active-session termination.

The Sprint43 workflow executes the dedicated reactivation regression and the Sprint36-Sprint42 regression chain under the bounded source envelope.

## 11. Runtime and lifecycle locks

Sprint43 source is bounded to:

**Local / Test / CI only**

Technical Preview remains:

**NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED**

Sprint41 migration #15 remains not applied or activated in Technical Preview.

Sprint42 remains not activated in Technical Preview.

Sprint43 remains not activated in Technical Preview.

Production remains:

**NO-GO / NOT AUTHORIZED**

Updater remains:

**DISABLED / UNWIRED**

Deployment and release remain **NOT AUTHORIZED**.

Rollback remains **NOT AUTHORIZED**.

No new feature flag or environment variable is introduced.

## 12. Explicit non-authority

This source foundation does not authorize:

- Technical Preview activation;
- Production activation;
- deployment;
- release;
- updater wiring;
- rollback;
- migration #16;
- automatic or timed reactivation;
- generic eligibility toggles;
- protected-control reactivation lifecycle;
- self-service reactivation;
- bulk or cross-tenant reactivation;
- session resurrection;
- credential or factor mutation;
- membership, role, permission, organization, outlet, or device restoration.

Any later activation or broader lifecycle requires a separately governed stage.
