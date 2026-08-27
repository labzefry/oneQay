# Sprint41 First-Party Identity Authentication Eligibility Administration Entry Gate

## Status

**SELECTED CONCERN / ENTRY GATE ONLY / NO SOURCE IMPLEMENTATION AUTHORITY**

Date: **2026-08-27**

This document selects and freezes the bounded Sprint41 concern **First-Party Identity Authentication Eligibility Administration Foundation**.

Sprint41 entry-gate scope is intentionally narrower than the broad concern name: this stage selects **administrative disablement only** for an already-published first-party identity eligibility flag. Re-enable/reactivation is not selected.

This entry gate does not authorize source implementation, a new route or API, schema mutation, migration #15, Technical Preview activation, Production activation, updater wiring, deployment, or release.

## Canonical starting point

This entry gate is prepared from canonical `main` after publication of the Sprint41 preservation predecessor PR #302:

- canonical base commit: `76e4c0c0a8a8a7e7ebaa8e97966818ae7c597884`;
- canonical base tree: `44621a040d16b91ee7c5d3b0bbb5c6b55c986211`;
- canonical base signature: **verified / valid**;
- predecessor: PR #302, `ci: preserve Sprint41 eligibility-administration entry gate`.

The predecessor recognizes only this exact one-document successor:

`docs/SPRINT_41_FIRST_PARTY_IDENTITY_AUTHENTICATION_ELIGIBILITY_ADMINISTRATION_ENTRY_GATE.md`

Its sorted newline-terminated one-path SHA-256 is:

`074a842bd6fb56ac06ae4658aeb6596b3674d426c4d58fee594b699820eac5f2`

Unknown successor shapes remain fail-closed.

## Selected concern

Sprint41 selects:

**First-Party Identity Authentication Eligibility Administration Foundation**

The bounded problem is to establish a future privileged, server-authoritative administrative mutation that can make an otherwise ordinary first-party identity no longer eligible for first-party authenticated use.

The selected mutation direction at this entry gate is only:

**`first_party_authentication_enabled: true → false`**

Sprint41 does **not** select `false → true` reactivation.

The already-published Sprint40 request-time revalidation remains the consumer of this state. Sprint41 is the separately governed producer concern that can eventually make the canonical server-owned eligibility evidence disabled.

## Selection rationale

Sprint40 publishes request-time fail-closed consumption of current identity eligibility and intentionally excludes the administrative producer lifecycle that changes that eligibility.

Canonical source now contains migration #14 and the server-owned field:

`first_party_authentication_enabled`

Sprint40 proves that an otherwise-valid first-party logical session authority must fail protected request authorization when the exact identity is currently ineligible.

Without a governed producer, however, canonical application authority has no bounded administrative path to transition an eligible ordinary identity into the disabled state.

Sprint41 therefore closes only the next producer-side gap:

- select a privileged administrative disablement mutation;
- preserve exact tenant and identity isolation;
- make the state transition monotonic and server-authoritative;
- preserve Sprint40 request-time enforcement;
- require secret-free auditable mutation evidence;
- avoid enabling, reactivation, bulk lifecycle, or unrelated identity-management scope.

## Frozen disablement semantics

A future Sprint41 implementation must preserve all of the following:

- the mutation is **disable-only**;
- the stored state transition is only `true → false`;
- an already-disabled identity remains disabled;
- retry and replay must not re-enable the identity;
- concurrent disable requests must converge on disabled state;
- at most one logical state transition from enabled to disabled may occur;
- the mutation must not modify password credentials;
- the mutation must not modify credential epoch;
- the mutation must not modify TOTP secret material;
- the mutation must not modify factor epoch;
- the mutation must not recreate or delete tenant membership;
- the mutation must not recreate, broaden, or switch organization/outlet/device grants;
- the mutation must not mint replacement session authority;
- the mutation must not alter another tenant or another identity;
- missing, malformed, contradictory, cross-tenant, or unauthorized target evidence fails closed.

This stage selects no automatic timed disablement and no scheduled reactivation.

## Target identity boundary

Sprint41 is limited to an exact ordinary first-party identity target.

The future implementation must derive target authority from canonical server-owned tenant and identity evidence. A caller-provided identifier may eventually be used only as an addressing input after authorization; it must never become proof that the caller is allowed to mutate that identity.

The mutation must prove:

1. an exact authenticated actor;
2. an exact current tenant authority for that actor;
3. an exact target identity;
4. target membership in the exact authorized tenant context;
5. existing canonical protected-control authorization for the actor to perform the future administrative mutation;
6. another tenant remains inaccessible;
7. another identity remains untouched.

This entry gate intentionally excludes protected-control principal lifecycle mutation.

Therefore Sprint41 does **not** authorize disabling:

- the currently acting protected-control identity;
- bootstrap/control-plane principals;
- identities whose disablement would require break-glass or last-administrator lifecycle handling.

Exact protected-control eligibility criteria and the future permission name are deferred to the separately governed schema/source-envelope gate.

## Authorization boundary

Identity eligibility disablement is a privileged administrative mutation.

The future implementation must:

- be authenticated server-side;
- deny anonymous access;
- deny ordinary self-service disablement;
- deny caller-controlled tenant authority;
- deny cross-tenant targeting;
- use existing canonical authorization primitives rather than inventing a bypass;
- require explicit protected-control authorization for the exact actor and target scope;
- fail closed when authorization evidence is missing, malformed, stale, contradictory, or unavailable.

This entry gate does not select a new permission string, policy role, or step-up scope.

If a new permission or privileged reauthentication composition is required, the next schema/source-envelope gate must freeze it explicitly before source implementation is authorized.

## Composition with Sprint40 request-time revalidation

Sprint41 must not replace Sprint40.

After a successful future disablement mutation:

- `first_party_authentication_enabled` becomes `false` for the exact target identity;
- Sprint40 request-time identity eligibility revalidation remains independently mandatory;
- a previously-issued logical session authority for that disabled identity must fail protected request authorization when subsequently evaluated;
- credential/factor epochs, revocation state, lifetime, membership, and organizational revalidation remain independent checks.

Disablement effectiveness must not depend on the caller logging out voluntarily.

The future mutation may not weaken or bypass:

- Sprint36 durable logical-session ownership and inventory/revocation;
- Sprint37 tenant-scoped revoke-all semantics;
- Sprint38 idle and absolute lifetime semantics;
- Sprint39 current membership and organization/outlet/device revalidation;
- Sprint40 current identity eligibility revalidation.

## Session and revocation boundary

Sprint41 entry gate does **not** select administrator-targeted session revocation as a side effect.

The security property selected here is that Sprint40 request-time revalidation denies continued protected use once canonical eligibility is false.

This gate does not authorize:

- administrator revoke-one of another identity;
- administrator revoke-all of another identity;
- deletion of durable session rows;
- direct manipulation of another identity's Laravel session;
- credential-epoch bumping as a substitute for eligibility disablement.

A later source-envelope decision may preserve existing self-scoped revocation behavior, but it may not silently broaden session-control authority to administrator-targeted revocation without separate explicit governance.

## Reactivation boundary

**Re-enable / reactivation is explicitly NOT SELECTED.**

Sprint41 entry-gate authority does not permit:

- `false → true`;
- automatic re-enable;
- timed reactivation;
- support reactivation;
- bulk reactivation;
- self-service reactivation;
- reactivation through login;
- reactivation through password reset;
- reactivation through TOTP recovery;
- reactivation through tenant or organizational grant restoration.

This separation is deliberate.

A future reactivation concern must independently decide how historical logical session authorities are handled so that re-enabling an identity cannot silently resurrect authority that should require fresh authentication or another explicit lifecycle decision.

## Schema decision

Sprint41 entry-gate schema direction is:

**NO NEW SCHEMA SELECTED / REUSE EXISTING CANONICAL SPRINT40 ELIGIBILITY FIELD**

Canonical source already contains migration #14:

`apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php`

and the canonical field:

`first_party_authentication_enabled`

At this entry-gate stage:

- migrations #1 through #14 remain canonical;
- migrations #1 through #14 remain immutable;
- migration #15 is **NOT REQUIRED / NOT SELECTED / NOT AUTHORIZED**;
- no new table, column, index, trigger, generated field, or schema mutation is selected;
- Technical Preview remains `NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED`.

The separately governed schema/source-envelope gate must re-verify this decision against exact canonical source before any implementation is authorized.

## Persistence mutation boundary

A later implementation, if separately authorized, must be update-only against the existing exact target identity row.

The mutation must not:

- create a missing identity row;
- upsert an identity;
- switch tenant ownership;
- mutate identity primary keys;
- infer eligibility from missing state;
- default a malformed state to enabled;
- overwrite unrelated identity attributes.

Missing target identity or contradictory ownership evidence must fail closed.

## Route and API impact

This entry gate selects:

**NO ROUTE / API SHAPE YET**

No controller, route name, HTTP method, URI, request payload, response envelope, UI action, mobile endpoint, or API version change is authorized at this stage.

The next schema/source-envelope gate may select a minimal protected administration delivery surface only after exact authorization and source paths are frozen.

No caller-provided boolean eligibility field is authorized as generic mutable input.

## Audit boundary

Administrative disablement is a business/security mutation and therefore requires future durable, secret-free audit evidence.

This entry gate freezes only these audit properties:

- exact server-derived tenant context;
- exact actor identity;
- exact target identity;
- transition type representing administrative first-party authentication disablement;
- success evidence only after durable state mutation succeeds;
- no password, TOTP secret, recovery code, session secret, CSRF token, or other Restricted secret material;
- replay/concurrency must not create misleading duplicate transition evidence after state has already converged.

The exact audit event name and persistence representation are **NOT SELECTED** at this entry gate and must be frozen by the next gate before source implementation.

## Concurrency and idempotency

A future implementation must be fail-closed under concurrency.

Required semantics:

- two concurrent authorized attempts targeting the same enabled identity must converge to disabled state;
- no race may restore `true`;
- a stale write may not overwrite newer authoritative state;
- retry after a confirmed successful transition must remain disabled;
- repeated disable requests must not create multiple logical state transitions;
- another identity remains unaffected;
- another tenant remains unaffected.

The exact transaction/locking strategy is deferred to the schema/source-envelope gate.

## Preserved authentication and recovery boundaries

Sprint41 must not alter:

- password verification rules;
- password reset or password change semantics;
- recovery-code rotation/proof;
- restricted recovery state;
- privileged TOTP enrollment/challenge/recovery;
- credential epoch validation;
- factor epoch validation;
- 300-second privileged step-up freshness already canonical where applicable;
- login/session establishment semantics.

Identity disablement is an independent current-eligibility authority and must not be synthesized from credential or factor state.

## Preserved session-control boundaries

Sprint41 must preserve:

- durable logical session authority;
- exact tenant + identity ownership;
- opaque inventory handles;
- revoke-one;
- revoke-others;
- canonical logout;
- tenant-scoped self revoke-all;
- disabled-by-default session-control feature boundary;
- secret-free existing session audit vocabulary.

No administrator-targeted session-control widening is authorized.

## Preserved lifetime boundaries

Sprint41 must preserve:

- idle TTL: **7200 seconds**;
- absolute TTL: **43200 seconds / 12 hours**;
- immutable issuance origin;
- equality-boundary behavior;
- deadline + 1 fail-closed behavior;
- clock rollback fail-closed behavior.

Disablement must not reset or extend logical-session lifetime.

## Preserved organizational-access boundaries

Sprint41 must preserve:

- current tenant membership revalidation;
- exact current organization/outlet/device relationship revalidation;
- no fallback from narrow authority to broader organization-only access;
- no automatic grant recreation;
- no caller-controlled organizational authority;
- another tenant and another identity remain isolated.

Disabling identity eligibility must not modify organizational grants.

## Runtime and activation boundary

Sprint41 entry-gate work remains:

**GOVERNANCE / DESIGN ONLY**

Technical Preview remains:

**`NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED`**

Production remains:

**`NO-GO / NOT AUTHORIZED`**

Updater remains:

**`DISABLED / UNWIRED`**

Deployment and release remain:

**NOT AUTHORIZED**

This entry gate does not arm any runtime.

## Frozen regression targets

A later schema/source-envelope gate must freeze an implementation envelope capable of proving at least:

1. an authorized exact actor can disable one eligible ordinary identity only after all selected authorization checks succeed;
2. successful mutation changes only `true → false`;
3. already-disabled state remains disabled;
4. retry cannot re-enable;
5. concurrency converges to disabled state;
6. another tenant remains unchanged;
7. another identity remains unchanged;
8. cross-tenant target fails closed;
9. missing target fails closed;
10. malformed target evidence fails closed;
11. unauthorized actor fails closed;
12. ordinary self-service mutation is denied;
13. protected-control identities remain outside this concern;
14. password credential material is unchanged;
15. credential epoch is unchanged;
16. TOTP secret material is unchanged;
17. factor epoch is unchanged;
18. tenant membership is unchanged;
19. organization/outlet/device grants are unchanged;
20. no replacement session authority is created;
21. no administrator-targeted session revocation authority is invented;
22. Sprint40 request-time revalidation denies a disabled target on subsequent protected request;
23. Sprint36–Sprint39 session, lifetime, tenant, and organizational controls remain preserved;
24. no `false → true` path exists;
25. no migration #15 exists or is introduced;
26. no route/API is introduced until separately frozen;
27. future audit evidence is server-derived and secret-free;
28. Technical Preview, Production, updater, deployment, and release remain locked.

Unknown or contradictory authority states must fail closed.

## Explicit exclusions

Sprint41 entry-gate authority excludes:

- source implementation;
- migration #15;
- any schema mutation;
- enable/reactivation;
- timed reactivation;
- bulk disablement;
- self-service disablement;
- protected-control identity disablement;
- last-administrator or break-glass lifecycle handling;
- administrator-targeted revoke-one/revoke-all;
- password mutation;
- credential epoch mutation;
- factor epoch mutation;
- TOTP secret mutation;
- recovery-code mutation;
- tenant membership mutation;
- organizational grant mutation;
- external directory synchronization;
- HR-driven lifecycle synchronization;
- federation/SSO lifecycle;
- API/mobile token lifecycle;
- support impersonation;
- new public API;
- UI implementation;
- Technical Preview activation;
- Production activation;
- updater wiring;
- deployment;
- release;
- Phase-exit authority.

These exclusions are not implied future requirements. Each requires separate Product Owner selection and bounded governance.

## Source-envelope non-authority

This entry gate does not freeze or authorize an application source changed-file envelope.

It does not authorize modifications to:

- identity repositories;
- identity controllers;
- routes;
- service-provider wiring;
- session middleware;
- session authority services;
- policy repositories;
- recovery services;
- configuration;
- application tests;
- migrations;
- deployment artifacts.

Those paths may be considered only in a separately governed Sprint41 schema/source-envelope gate after this entry gate is published.

## Exit criteria for this stage

This entry-gate stage is complete only when:

- this exact one-document successor is qualified on its exact PR head;
- all materially triggered workflows succeed;
- queued/cancelled/no-runner/empty-job results are not treated as success;
- governance checks succeed;
- the PR is Ready;
- a top-level Product Owner exact-head merge authorization exists;
- `product-owner-merge-authority=SUCCESS`;
- final race-check confirms unchanged head, current main relationship, exact one-file envelope, and mergeability;
- squash merge uses `expected_head_sha`;
- post-merge canonical commit/tree/signature are verified.

Publication of this entry gate authorizes no Sprint41 source implementation.

The next separately governed boundary after publication is:

**Sprint41 schema/source-envelope gate selection and publication.**

Attribution: **Lab | zefry**
