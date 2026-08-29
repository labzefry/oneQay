# Sprint43 First-Party Identity Authentication Eligibility Reactivation Schema / Source Envelope Gate

## Status

**SCHEMA/SOURCE-ENVELOPE GATE ONLY / NO SOURCE IMPLEMENTATION AUTHORITY**

Date: **2026-08-29**

Attribution: **Lab | zefry**

## 1. Canonical starting point

This gate follows the published Sprint43 entry gate PR #340 and the exact schema/source-gate preservation predecessor PR #341.

Canonical base:

- commit: `fd258ab9650ba0e96413a0f2bc9bf06641769198`;
- tree: `cf156b9a54b29609386cb57d801cd7fbe0b2538f`;
- signature: **verified / valid**;
- Sprint43 entry gate: PR #340;
- schema/source-gate preservation predecessor: PR #341.

This gate changes exactly one path:

`docs/SPRINT_43_FIRST_PARTY_IDENTITY_AUTHENTICATION_ELIGIBILITY_REACTIVATION_SCHEMA_SOURCE_ENVELOPE_GATE.md`

Sorted newline-terminated one-path SHA-256:

`71ea234d8aa6e4691e5871e2165a61c162a04d30c824a6ae8b3aafb8c39962be`

Unknown successor shapes remain fail-closed.

## 2. Selected concern

**Sprint43 First-Party Identity Authentication Eligibility Reactivation Foundation**

Sprint43 may later implement only an administrator-authorized restoration of first-party authentication eligibility for one exact ordinary same-tenant identity.

The only selected identity-state direction is:

`first_party_authentication_enabled: false -> true`

Reactivation means only that the target becomes eligible to attempt a future fresh first-party authentication flow subject to every other current server-authoritative control.

This gate does not authorize source implementation.

## 3. Schema determination

Sprint43 schema classification is frozen as:

**NO_SCHEMA_CHANGE / MIGRATION #16 NOT SELECTED**

Canonical migration #14 already provides:

`oneqay_identities.first_party_authentication_enabled`

Canonical migration #15 already provides the durable identity-authentication eligibility mutation journal:

`oneqay_identity_authentication_eligibility_mutations`

with:

- `tenant_id`;
- `mutation_id`;
- `actor_identity_id`;
- `target_identity_id`;
- `operation`;
- `payload_fingerprint`;
- `outcome`;
- `occurred_at_unix`;
- primary key `(tenant_id, mutation_id)`.

The existing `operation` field has sufficient bounded capacity for the separately governed operation vocabulary selected below.

Migrations #1 through #15 remain immutable.

No table, column, index, foreign key, trigger, generated column, scheduler, outbox, auxiliary audit table, or migration #16 is selected.

Rollback remains unauthorized.

## 4. Durable evidence and journal decision

Sprint43 reuses the existing Sprint41 eligibility-administration journal table.

No new durable evidence table is selected.

The exact new operation vocabulary is:

`reactivate`

Canonical existing disablement remains:

`disable`

The journal is therefore a shared identity-authentication eligibility administration evidence surface with operation-specific deterministic binding. Sprint43 does not convert the public API into a generic operation selector.

Allowed Sprint43 successful outcomes remain exactly:

- `applied`;
- `no_change`.

A Sprint43 journal row records only the canonical server-derived tenant, mutation ID, actor identity, target identity, operation, payload fingerprint, outcome, and occurred-at timestamp already provided by migration #15.

Sprint43 must not write passwords, password hashes, credential epochs, TOTP/recovery material, factor epochs, framework session IDs, logical session secrets/handles, CSRF tokens, arbitrary reason text, organization/outlet/device restoration data, or caller-supplied tenant authority into the journal.

## 5. Exact mutation identifier and fingerprint semantics

Sprint43 reuses:

`App\Application\Identity\IdentityAuthenticationEligibilityMutationId`

No second mutation-ID type is selected.

The reactivation payload fingerprint must be deterministic and server-derived from the same bounded administration dimensions used by canonical eligibility administration, with the operation changed to the exact Sprint43 vocabulary.

The exact ordered fingerprint inputs are:

1. server-derived tenant ID;
2. server-derived actor identity ID;
3. exact target identity ID;
4. operation `reactivate`;
5. `AdministrationPermission::MANAGE`;
6. scope literal `tenant`.

The implementation must use SHA-256 over the canonical newline-delimited ordered values.

Because `operation` is fingerprinted and journal rows also store the operation, reuse of a mutation ID previously bound to `disable` as `reactivate`, or vice versa, is a conflicting replay and must fail closed.

No caller-provided operation, enabled flag, mode, tenant, permission, scope, actor, or fingerprint is accepted.

## 6. Authorization and target boundary

Sprint43 selects no new permission.

The actor must remain derived from the existing authenticated tenant-scoped policy-administration context and must currently hold:

`AdministrationPermission::MANAGE`

The exact target remains limited to:

- one exact same-tenant first-party identity;
- an ordinary identity;
- not actor self;
- not a protected-control identity.

The implementation must deny:

- actor equals target;
- cross-tenant target;
- missing target;
- protected-control target;
- malformed target;
- missing or invalid current actor authority;
- storage/runtime uncertainty.

No caller-supplied tenant may become authority.

No bulk, wildcard, global, organization-wide, outlet-wide, device-wide, session-wide, credential-wide, or factor-wide target is selected.

## 7. Fresh applied behavior

For a fresh valid mutation against an exact target whose canonical state is disabled:

`false -> true`

must occur atomically with insertion of exactly one matching `reactivate` journal row.

The returned outcome is:

`applied`

Before success:

- the final exact target eligibility state must be true;
- the actor must remain authorized;
- the target must remain same-tenant and non-protected;
- the journal evidence must match the exact mutation ID and fingerprint.

The operation must not create a framework session or logical session authority.

## 8. Already-enabled no-change behavior

For a fresh valid mutation against an exact target whose canonical state is already enabled:

- identity eligibility remains true;
- exactly one fresh matching `reactivate` journal row is inserted;
- outcome is `no_change`;
- no session, credential, factor, membership, role, permission, or organizational relationship is mutated.

Already-enabled is deterministic convergence, not authorization to bypass target validation.

## 9. Exact replay and conflicting replay

Same tenant + same mutation ID + exact same reactivation fingerprint:

- returns the previously recorded deterministic outcome;
- does not insert a duplicate journal row;
- does not rewrite the target state;
- does not create or restore any session authority;
- does not mutate credentials, factors, membership, or grants.

Same tenant + same mutation ID + different fingerprint must fail closed.

A different fingerprint includes any different actor, target, operation, permission/scope binding, or malformed stored evidence.

In particular, a mutation ID bound to Sprint41 `disable` cannot later be accepted as Sprint43 `reactivate`.

## 10. Concurrency contract

Concurrency must converge deterministically.

For distinct concurrent mutation IDs against one disabled target:

- at most one conditional `false -> true` update may produce `applied`;
- other valid fresh mutations may converge to `no_change`;
- every successful fresh mutation records exactly one deterministic journal row;
- final eligibility state is true;
- no concurrent path may clear or modify session revocation evidence.

For the same mutation ID:

- exact fingerprint replay returns the prior outcome;
- conflicting fingerprint reuse fails closed;
- duplicate journal evidence is prohibited.

No queue, delayed job, scheduler, timed reactivation, or asynchronous convergence is selected.

## 11. Transaction and failure semantics

Sprint43 reuses the existing:

`App\Application\Persistence\PersistenceTransaction`

and canonical policy-administration clock.

For a fresh reactivation:

1. perform existing authorization and target preflight;
2. check deterministic replay/conflict state;
3. obtain a positive server-derived administration timestamp;
4. execute the fresh conditional reactivation and journal insert inside one canonical persistence transaction;
5. verify final target eligibility and security boundary before returning success.

A fresh `applied` or `no_change` outcome must not commit without its matching journal row.

Persistence disabled, disallowed runtime, malformed state, contradictory row count, authorization drift, journal conflict, storage failure, or transaction failure must fail closed.

No second transaction manager is selected.

## 12. Exact repository and service contract extension

Sprint43 later source may extend the existing application contract:

`App\Application\Identity\FirstPartyIdentityEligibilityAdministrationRepository`

with only dedicated reactivation semantics, including:

`OPERATION_REACTIVATE = 'reactivate'`

and dedicated repository methods for:

- reactivation replay lookup;
- fresh reactivation application.

The contract must not expose arbitrary boolean state assignment, caller-selected operation strings, generic toggle methods, session restoration, credential mutation, factor mutation, membership restoration, or grant restoration.

The existing service:

`App\Application\Identity\FirstPartyIdentityEligibilityAdministrationService`

may add exactly one new business operation:

`reactivate(...): string`

Existing `disable(...): string` semantics remain canonical and unchanged.

The reactivation method must not invoke the Sprint42 disablement session-termination repository.

## 13. Exact durable adapter behavior

The existing adapter:

`App\Infrastructure\Identity\LaravelFirstPartyIdentityEligibilityAdministrationRepository`

may add only the bounded `reactivate` behavior frozen by this gate.

For the identity update it may target only:

- exact server-derived `tenant_id`;
- exact target `id`;
- `first_party_authentication_enabled = 0`.

It may update only:

`first_party_authentication_enabled = true`

It must not update:

- another identity row;
- another tenant;
- password/password hash;
- credential epoch;
- TOTP/recovery material;
- factor epoch;
- role or permission state;
- tenant membership;
- organization/outlet/device relationships;
- logical session rows;
- `revoked_at_unix`;
- issued/last-seen/expiry session evidence.

The adapter must retain canonical Local/Test/CI and persistence fail-closed guards.

## 14. Public route and payload strategy

Sprint41 disablement remains dedicated and unchanged:

`POST /administration/identities/{identity_id}/authentication-disablement`

Route name:

`identity.authentication-eligibility.disable`

Sprint43 selects one separate protected route:

`POST /administration/identities/{identity_id}/authentication-reactivation`

Frozen route name:

`identity.authentication-eligibility.reactivate`

The Sprint43 route must reuse the same canonical protected policy-administration request context, active-session requirement, tenant/organization context, privileged-step-up behavior where currently required, and throttling conventions used by Sprint41.

Request payload contains exactly:

`mutation_id`

The route parameter `identity_id` is addressing input only and must be verified against server-owned tenant and authority evidence.

No `enabled`, `active`, `disabled`, `reactivate`, `operation`, `mode`, `force`, tenant, actor, role, permission, session, organization, outlet, or device field is authorized in the request payload.

The Sprint41 disablement endpoint must never be converted into a generic toggle.

## 15. Controller and response contract

The existing controller:

`App\Delivery\Http\Identity\FirstPartyIdentityEligibilityAdministrationController`

may add a dedicated `reactivate` delivery method while preserving its existing disablement invocation behavior.

Both operations must retain minimal safe public responses:

- `status`;
- deterministic `outcome` on success;
- correlation ID.

Failure remains generic through the existing identity-authentication eligibility administration rejection vocabulary and must not disclose whether a denied target is cross-tenant, protected-control, or absent.

No automatic login or authentication response is selected.

## 16. Session non-resurrection proof

Sprint43 reactivation changes current identity authentication eligibility only.

The later source regression must prove that reactivation does not:

- insert a framework session;
- insert a logical first-party session authority;
- clear `revoked_at_unix`;
- rewrite any already-revoked session;
- revive Sprint42-terminated sessions;
- revive expired sessions;
- change session issued time;
- change session last-seen time;
- change session expiry;
- synthesize current MFA or privileged-step-up evidence.

A previously revoked session remains revoked even after identity eligibility becomes true.

Fresh authentication remains mandatory.

## 17. Membership, grant, credential, and factor preservation

The later source regression must prove exact preservation of:

- tenant membership;
- organization relationships;
- outlet relationships;
- device relationships;
- role assignments;
- permissions;
- password and password hash;
- credential epoch;
- TOTP factor state;
- recovery material;
- factor epoch;
- privileged evidence.

Reactivation is not account recovery, credential reset, factor replacement, membership restoration, or authorization restoration.

## 18. Sprint36-Sprint42 preservation

The later Sprint43 source must preserve without reinterpretation:

- Sprint36 durable first-party session inventory, revoke-one, revoke-others, canonical logout, and opaque handles;
- Sprint37 self-scoped revoke-all;
- Sprint38 idle TTL **7200 seconds** and absolute TTL **43200 seconds**;
- Sprint39 current tenant membership and organization/outlet/device revalidation;
- Sprint40 request-time identity authentication-eligibility revalidation;
- Sprint41 dedicated disable-only eligibility administration, authorization, target exclusions, mutation binding, journal, route, and safe response;
- Sprint42 exact-target active logical-session termination after successful disablement outcomes.

No Sprint43 operation may restore authority revoked by Sprint36-Sprint42.

## 19. Runtime and lifecycle boundary

No new feature flag or environment variable is selected.

Sprint43 source remains bounded to:

**Local / Test / CI only**

Technical Preview remains:

**NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED**

Sprint41 migration #15 remains not applied/activated in Technical Preview.

Sprint42 remains not activated in Technical Preview.

Future Sprint43 source remains not activated.

Production remains:

**NO-GO / NOT AUTHORIZED**

Updater remains:

**DISABLED / UNWIRED**

Deployment and release remain:

**NOT AUTHORIZED**

Rollback remains:

**NOT AUTHORIZED**

## 20. Exact future source-preservation predecessor

Before the later Sprint43 source implementation PR, a separately qualified preservation predecessor must update exactly these nine workflow paths:

1. `.github/workflows/m7-5-preview-release-artifact.yml`
2. `.github/workflows/sprint35-privileged-totp-recovery-regression.yml`
3. `.github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml`
4. `.github/workflows/sprint37-first-party-all-session-termination-regression.yml`
5. `.github/workflows/sprint38-first-party-session-absolute-lifetime-regression.yml`
6. `.github/workflows/sprint39-first-party-session-organizational-access-revalidation-regression.yml`
7. `.github/workflows/sprint40-first-party-session-identity-disablement-revalidation-regression.yml`
8. `.github/workflows/sprint41-first-party-identity-authentication-eligibility-administration-regression.yml`
9. `.github/workflows/sprint42-first-party-identity-disablement-session-termination-regression.yml`

Sorted newline-terminated nine-path SHA-256:

`b8afa8415c0675c2675c1b8dbd528b44e8181a8f79a97fe28b0a0996623e1749`

That predecessor may change only exact source-successor recognition, trigger, and historical fixture/isolation logic required for the frozen Sprint43 source envelope.

It must not implement Sprint43 application behavior.

Unknown successor shapes remain fail-closed.

## 21. Exact later Sprint43 source envelope

After the required source-preservation predecessor is published, the Sprint43 source implementation is frozen to exactly these nine paths:

1. `.github/workflows/sprint43-first-party-identity-authentication-eligibility-reactivation-regression.yml`
2. `apps/web/app/Application/Identity/FirstPartyIdentityEligibilityAdministrationRepository.php`
3. `apps/web/app/Application/Identity/FirstPartyIdentityEligibilityAdministrationService.php`
4. `apps/web/app/Delivery/Http/Identity/FirstPartyIdentityEligibilityAdministrationController.php`
5. `apps/web/app/Infrastructure/Identity/LaravelFirstPartyIdentityEligibilityAdministrationRepository.php`
6. `apps/web/routes/web.php`
7. `apps/web/tests/first-party-identity-authentication-eligibility-administration.php`
8. `apps/web/tests/first-party-identity-authentication-eligibility-reactivation.php`
9. `docs/FIRST_PARTY_IDENTITY_AUTHENTICATION_ELIGIBILITY_REACTIVATION_FOUNDATION.md`

Sorted newline-terminated nine-path SHA-256:

`3d0293362f451fe4bf472d0d2c38c3eec3d67df75b451ba5b273e8dbdb0f2eed`

No other path belongs to the authorized Sprint43 source envelope.

In particular, the source implementation must not modify:

- migrations;
- AppServiceProvider;
- configuration;
- dependency manifests or locks;
- Sprint40 request-time eligibility middleware/verifier;
- Sprint42 disablement session-termination contract or adapter;
- self-service session contracts/controllers;
- credential/factor repositories;
- policy/membership/grant persistence.

If source implementation requires another path, mutation must stop pending a separately governed envelope revision.

## 22. Role of authorized source paths

### Sprint43 workflow

The new workflow must enforce the exact nine-path source fingerprint, zero migration diff, migrations #1-#15 immutability, no migration #16, Local/Test/CI-only runtime, exact dedicated route/payload, operation-specific fingerprinting, deterministic replay/conflict behavior, authorization isolation, no session resurrection, and preserved Sprint36-Sprint42 regressions.

### Existing application repository

May add only the exact `reactivate` operation constant and dedicated reactivation persistence methods while preserving disablement methods.

### Existing service

May add only the exact `reactivate(...)` business operation and shared bounded helper logic where semantics are identical. Disablement must continue to compose Sprint42 termination exactly as before; reactivation must not call termination.

### Existing controller

May add only the dedicated Sprint43 reactivation delivery method with exact mutation-ID payload parsing and the existing safe response/rejection boundary.

### Existing Laravel adapter

May add only conditional `false -> true`, exact operation-specific replay/fingerprint/journal logic, and required verification. It must never touch session persistence.

### Routes

May add only the one frozen Sprint43 POST route and route name. Sprint41 disablement remains unchanged.

### Existing Sprint41 regression

May change only to prove its prior disable-only public and application semantics remain unchanged after the bounded repository/service/controller extension.

### Dedicated Sprint43 regression

Must prove at least:

- authorized disabled ordinary target returns `applied`;
- exact field becomes true;
- already-enabled target returns `no_change`;
- exact replay preserves prior outcome and journal cardinality;
- conflicting target replay fails;
- disable-operation mutation ID reused for reactivation fails;
- reactivation mutation ID reused for disablement fails;
- concurrent distinct mutation IDs converge with at most one `applied`;
- actor self-target fails;
- protected-control target fails;
- cross-tenant/missing target fails safely;
- unauthorized actor fails;
- malformed mutation ID fails;
- persistence-disabled and runtime-denied paths fail closed;
- journal operation equals `reactivate` and is secret-free;
- target password/hash and credential epoch remain unchanged;
- TOTP/recovery/factor state remains unchanged;
- memberships, roles, permissions, and organization/outlet/device relationships remain unchanged;
- prior Sprint42-revoked session remains revoked;
- expired session remains expired and unchanged;
- no logical or framework session is created;
- no `revoked_at_unix` value is cleared;
- fresh authentication remains required;
- Sprint41 disablement still terminates target active sessions through Sprint42;
- Sprint40 request-time revalidation remains independent;
- migrations #1-#15 remain unchanged;
- no migration #16 exists;
- Technical Preview/Production/updater/deployment/release posture remains unchanged.

### Foundation document

Must record only the published bounded Sprint43 implementation facts and preserve all lifecycle locks.

## 23. Explicit exclusions

Sprint43 source must not add or mutate:

- generic eligibility toggle API;
- caller-selected operation flag;
- automatic or timed reactivation;
- self-service reactivation;
- protected-control identity reactivation;
- protected-control disablement lifecycle;
- last-administrator or break-glass lifecycle;
- bulk/cross-tenant/global reactivation;
- identity creation or deletion;
- membership restoration;
- organization/outlet/device grant restoration;
- role/permission restoration;
- password change/reset/overwrite;
- credential epoch mutation;
- TOTP/recovery/factor mutation;
- session resurrection;
- session audit rewriting;
- automatic login;
- enrollment/recovery capability;
- administrator session inventory;
- API/mobile bearer-token lifecycle;
- external directory/HR/SCIM/SSO lifecycle;
- notification/email/SMS delivery;
- background jobs;
- new feature flags/environment variables;
- config changes;
- dependency changes;
- migration #16 or later;
- Technical Preview activation;
- Production activation;
- updater wiring;
- deployment;
- release.

## 24. Exit criteria for this gate stage

This gate stage is complete only when:

- this exact one-document gate is qualified on its exact PR head;
- the changed-file envelope is exactly the frozen one path and fingerprint;
- every materially triggered workflow is completed/success;
- queued, in-progress, cancelled, skipped material execution, or empty execution is not treated as success;
- the PR is non-draft;
- top-level Product Owner exact-head merge authorization exists;
- `product-owner-merge-authority = success`;
- final race-check confirms unchanged head, current base, exact envelope, mergeable state, and no unexpected repository race;
- squash merge uses the exact authorized head SHA;
- post-merge canonical SHA/tree/signature are verified.

## 25. Authority boundary

This document freezes the Sprint43 schema, evidence, application, API, replay, transaction, preservation, and exact future source envelopes.

It does **not** authorize Sprint43 source implementation itself.

It does **not** authorize schema mutation, migration #16, Technical Preview activation, Production activation, updater wiring, deployment, release, rollback, or any excluded lifecycle.

Unknown state remains fail-closed.
