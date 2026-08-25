# Sprint40 First-Party Session Identity Disablement Revalidation Entry Gate

## Status

**SELECTED CONCERN / ENTRY GATE ONLY / NO SOURCE IMPLEMENTATION AUTHORITY**

Date: **2026-08-25**

This document selects and freezes the bounded Sprint40 concern **First-Party Session Identity Disablement Revalidation Foundation**.

It is an entry gate only. It does not authorize source implementation, schema mutation, migration creation, identity-administration mutation, Technical Preview activation, Production activation, updater wiring, deployment, or release.

## Canonical starting point

This entry gate is prepared from canonical `main` after publication of the Sprint40 entry-gate preservation predecessor PR #267:

- canonical base commit: `55a1c950c9faa66b0e35b92c88ffdfd63b431432`;
- canonical base tree: `dc90fcf9b5bea7b79d21abbc887113222cc3a195`;
- canonical base parent: `8bf452e2bf5ffb63725e434c85292965decd221f`;
- canonical base signature: **verified / valid**;
- preservation predecessor: PR #267, `ci: preserve Sprint40 identity-disablement entry gate`.

The predecessor recognizes this exact one-file successor:

`docs/SPRINT_40_FIRST_PARTY_SESSION_IDENTITY_DISABLEMENT_REVALIDATION_ENTRY_GATE.md`

Its sorted newline-terminated one-path SHA-256 is:

`7a8c362aa7ec66500ba544911e3fb008a20f5d21ac4d00d6a12ea04749ed2466`

Unknown successor shapes remain fail-closed.

## Selected concern

Sprint40 selects:

**First-Party Session Identity Disablement Revalidation Foundation**

The concern is limited to proving that the exact identity represented by an otherwise-valid durable first-party logical session authority remains eligible for first-party authenticated use at request time after that authority was issued.

This concern does not define how an identity becomes disabled or re-enabled. It does not authorize identity-administration UI, an administrator mutation route, account lifecycle workflows, or a new public recovery mechanism.

## Selection rationale

Sprint36 through Sprint39 now protect first-party session authority through multiple independent layers:

- durable logical authority ownership;
- exact tenant + identity binding;
- credential epoch validation;
- privileged factor epoch validation where applicable;
- revocation state;
- idle lifetime;
- absolute lifetime;
- current tenant membership;
- current organization/outlet/device relationship authorization.

Those controls prove that the logical authority itself remains current and that its organizational access remains valid. They do not, by themselves, prove that the underlying identity is still globally eligible for authenticated use.

An identity can become ineligible after a session was issued. If request-time authority evaluation checks only session-row state, credential/factor epochs, and organizational access, a historical logical authority could otherwise continue to authorize requests even though the principal itself has been disabled by canonical server-owned identity authority.

The next bounded security gap is therefore to make current identity eligibility an independent request-time requirement for continued use of a first-party logical session authority.

This aligns with:

- deny-by-default authorization;
- joiner/mover/leaver lifecycle handling;
- server-owned identity authority;
- tenant and identity isolation;
- fail-closed request-time evaluation;
- separation between authentication evidence and ongoing principal eligibility.

## Frozen authority model

The Sprint40 foundation must preserve these authority layers as distinct evidence:

1. durable logical first-party session authority from Sprint36;
2. exact tenant + identity ownership derived server-side;
3. `credential_epoch` validation;
4. privileged `factor_epoch` validation where applicable;
5. revocation state;
6. idle lifetime of **7200 seconds**;
7. absolute lifetime of **43200 seconds / 12 hours** from durable `issued_at_unix`;
8. current identity eligibility for first-party authenticated use;
9. exact organization/outlet/device coordinates represented by the current logical authority;
10. current durable tenant membership and current durable organizational relationship authorization from Sprint39.

No one layer replaces another.

In particular:

- a current credential epoch does not override a disabled identity;
- a current factor epoch does not override a disabled identity;
- valid tenant membership does not override a disabled identity;
- valid organization/outlet/device access does not override a disabled identity;
- identity eligibility does not replace credential, factor, revocation, lifetime, membership, or organizational checks.

## Frozen identity-eligibility semantics

A usable first-party logical authority must be bound to an exact identity that is still eligible for first-party authenticated use according to canonical server-owned evidence.

The future bounded implementation must enforce all of the following semantics:

- the identity being revalidated is derived only from the verified current logical authority and authenticated server-side session context;
- caller-supplied identity, tenant, organization, outlet, device, account-state, or eligibility selectors must never become authority;
- an identity that canonical server-owned evidence marks disabled or otherwise not eligible for first-party authenticated use causes request-time authority evaluation to fail closed;
- missing, malformed, contradictory, stale, or impossible identity-eligibility evidence fails closed rather than defaulting to enabled;
- identity disablement must take precedence over continued use of an already-issued logical authority;
- a previously authenticated browser session must not grandfather access after identity disablement becomes effective;
- identity revalidation must not auto-enable, restore, repair, or recreate identity state;
- identity revalidation must not mint a replacement logical authority;
- identity revalidation must not silently switch to another identity;
- identity revalidation must not silently switch tenant, organization, outlet, or device context;
- retry, replay, session rotation, inventory access, privileged step-up, concurrent requests, or stale local Laravel session state must not resurrect a disabled identity's protected access;
- another tenant remains isolated;
- another identity remains isolated;
- disabling one identity must not invalidate an unrelated identity merely because both identities share a tenant or organizational context.

The exact durable representation of identity eligibility is not selected by this entry gate.

## Request-time composition with Sprint39

Sprint40 must compose with Sprint39 rather than replacing it.

For a protected request to proceed, all previously published first-party session invariants and both current-facing revalidation concerns must succeed:

- current identity eligibility; and
- current tenant membership plus exact organization/outlet/device relationship authorization.

This entry gate does not select the exact source-level ordering between the new identity-eligibility check and Sprint39 organizational revalidation.

The later schema/source-envelope gate may freeze an exact implementation sequence, but it must preserve these invariants:

- the durable logical authority must first be established as the exact current authority under existing Sprint36 through Sprint38 rules;
- both identity eligibility and Sprint39 organizational access must be evaluated from server-owned exact authority coordinates;
- neither revalidation layer may be skipped because the other succeeded;
- the protected request may continue only after every required layer succeeds.

## Failure handling boundary

If current identity revalidation fails, the request must not continue as an authorized protected request.

A later bounded source design may reuse the existing generic fail-closed session-authority denial path, including local Laravel session invalidation and CSRF regeneration where already canonical.

This entry gate does not authorize:

- a new public error contract;
- a new caller-selectable recovery route;
- a new durable audit event;
- automatic identity reactivation;
- automatic organizational grant restoration;
- creation of a replacement session authority.

Identity disablement is validation evidence for continued authorization. It is not authority to mutate unrelated tenants, unrelated identities, historical access grants, or credential state.

## Identity lifecycle producer boundary

Sprint40 selects **request-time consumption of current identity eligibility**, not the administrative producer lifecycle that changes that eligibility.

This entry gate does not authorize:

- an administrator `disable identity` route;
- an administrator `enable identity` route;
- identity suspension administration;
- an identity lifecycle dashboard;
- bulk disablement;
- self-service disablement;
- automatic timed reactivation;
- policy-driven account state changes;
- HR or directory synchronization;
- external identity-provider lifecycle synchronization.

If a later schema/source analysis determines that a minimal canonical server-owned identity-eligibility representation is required for Sprint40, only the separately governed schema/source-envelope gate may select and bound that representation.

## Preserved Sprint36 session semantics

Sprint40 must preserve:

- durable logical session authority distinct from raw Laravel session identifiers;
- non-public internal `authority_id`;
- opaque `public_handle` used only for inventory/revocation addressing;
- exact tenant + identity ownership;
- `credential_epoch` and privileged `factor_epoch` validation;
- session inventory;
- revoke-one;
- revoke-others;
- canonical logout;
- privileged `session_control` step-up with **300-second** freshness;
- disabled-by-default session-control feature boundary.

Identity eligibility must not be inferred from possession of a `public_handle`, a Laravel session identifier, or successful historical authentication.

## Preserved Sprint37 semantics

Sprint40 must preserve tenant-scoped revoke-all:

- `POST /auth/sessions/revoke-all`;
- exact current tenant + identity scope derived server-side;
- current authority included;
- durable revoke succeeds before local session invalidation;
- replay/concurrency remains monotonic;
- another tenant/identity remains untouched;
- existing `all_sessions_revoked` audit vocabulary remains secret-free.

Identity revalidation must not broaden revoke-all scope.

## Preserved Sprint38 lifetime semantics

Sprint40 must preserve:

- idle TTL: **7200 seconds**;
- absolute TTL: **43200 seconds / 12 hours**;
- effective expiry bounded by `min(now + 7200, issued_at_unix + 43200)`;
- immutable durable issuance origin;
- no extension beyond the absolute deadline;
- exact deadline equality remains valid;
- no non-advancing touch at equality when expiry cannot move into the future;
- deadline + 1 second fails closed;
- clock rollback fails closed;
- invalid lifetime configuration fails closed;
- inventory reports effective capped expiry.

Identity revalidation must not reset, extend, or replace either lifetime.

## Preserved Sprint39 organizational semantics

Sprint40 must preserve:

- current tenant membership revalidation;
- exact current organization relationship revalidation;
- exact outlet relationship revalidation when outlet-bound;
- exact device relationship revalidation when device-bound;
- fail-closed device-without-required-context behavior;
- no fallback from outlet/device-bound authority to broader organization-only access;
- no caller-controlled organizational authority;
- no automatic grant recreation;
- no automatic organizational context switch;
- no replacement authority creation;
- another tenant and another identity remain isolated.

A currently eligible identity with removed organizational access must still fail Sprint39 revalidation.

A currently authorized organizational relationship must not override Sprint40 identity disablement.

## Schema decision

Sprint40 entry-gate schema status is:

**NOT SELECTED AT ENTRY GATE / DEFERRED TO SEPARATELY GOVERNED SCHEMA-SOURCE ENVELOPE GATE**

Canonical repository evidence at this boundary does not establish a previously published generic identity `enabled` / `disabled` persistence authority that this entry gate can safely assume as the implementation contract.

Therefore this stage intentionally does not decide whether Sprint40 can be implemented with existing persistence or requires a minimal forward-only schema addition.

At this entry-gate stage:

- canonical migrations remain exactly **#1 through #13**;
- migrations #1 through #13 remain immutable;
- migration #14 is **NOT REQUIRED / NOT SELECTED / NOT AUTHORIZED**;
- no table, column, index, migration artifact, rollback path, or schema mutation is authorized.

Only the later schema/source-envelope gate may select `NO_SCHEMA_CHANGE` or a separately bounded schema change after inspecting exact canonical persistence and authority contracts.

## Route and API impact

This entry gate selects:

**NO NEW ROUTE / NO NEW PUBLIC API / NO NEW REQUEST PAYLOAD**

Sprint40 request-time revalidation must consume server-owned authority and must not depend on a caller-provided identity-state field.

Existing published session-control routes and canonical logout remain unchanged.

Any future identity-administration route/API requires separate Product Owner selection and authority.

## Audit impact

This entry gate selects:

**NO NEW AUDIT EVENT**

Existing server-derived secret-free session audit vocabulary remains unchanged.

Request-time identity eligibility evaluation is authorization validation, not a newly authorized identity-administration business mutation.

If a later concern requires durable audit evidence specifically for identity disablement or administrative state changes, that audit concern must be separately selected and governed.

## Feature-arm decision

This entry gate selects:

**NO NEW FEATURE ARM**

Any later Sprint40 source work must remain under the existing disabled-by-default first-party session-control boundary unless a separate authority explicitly changes that boundary:

`ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false`

No independent Sprint40 environment arm, bypass flag, Preview arm, or Production arm is authorized.

## Runtime and activation boundary

Sprint40 remains:

**Local/Test/CI only**

Technical Preview remains:

**NOT ACTIVATED BY THIS GATE**

Production remains:

**NO-GO / NOT AUTHORIZED**

Updater remains:

**DISABLED / UNWIRED**

Deployment and release remain:

**NOT AUTHORIZED**

This entry gate does not arm any protected runtime.

## Frozen regression targets

A later schema/source-envelope gate must freeze an implementation envelope capable of proving at least these behaviors:

1. an otherwise-valid authority for a currently eligible exact identity may continue only when every existing session and organizational invariant also succeeds;
2. a disabled exact identity causes request-time authority evaluation to fail closed;
3. missing identity-eligibility evidence fails closed;
4. malformed identity-eligibility evidence fails closed;
5. contradictory identity-eligibility evidence fails closed;
6. caller-controlled identity-state input cannot override server-owned evidence;
7. current credentials do not override identity disablement;
8. current privileged factor state does not override identity disablement;
9. current tenant membership does not override identity disablement;
10. current organization/outlet/device authorization does not override identity disablement;
11. a currently eligible identity with removed tenant membership still fails Sprint39 revalidation;
12. a currently eligible identity with removed organization/outlet/device access still fails Sprint39 revalidation;
13. another tenant remains isolated;
14. another identity remains isolated;
15. disabling one identity does not disable an unrelated identity;
16. session rotation does not bypass identity revalidation;
17. privileged step-up does not bypass identity revalidation;
18. inventory access does not resurrect a disabled identity's protected authority;
19. replay or retry does not resurrect disabled access;
20. revoke-one, revoke-others, revoke-all, inventory, and canonical logout remain preserved;
21. credential and factor epoch invalidation remain preserved;
22. 7200-second idle and 43200-second absolute lifetime semantics remain preserved;
23. equality-boundary and deadline + 1 behavior remain preserved;
24. no new public route or request payload is introduced;
25. no new audit event is introduced by request-time revalidation;
26. no new feature arm is introduced;
27. the later selected schema boundary is enforced exactly and no unselected migration is permitted;
28. Technical Preview, Production, updater, deployment, and release boundaries remain preserved.

Unknown or contradictory authority states must remain fail-closed.

## Explicit exclusions

Sprint40 entry-gate authority excludes:

- source implementation;
- migration #14 at this stage;
- schema mutation at this stage;
- identity disable/enable administration routes;
- identity lifecycle administration UI;
- bulk identity lifecycle mutation;
- automatic identity reactivation;
- new public session route or API;
- caller-provided identity-state authority;
- new audit vocabulary;
- automatic organizational grant restoration;
- organization/outlet/device auto-switching;
- administrator force-logout of another identity beyond already-published self-scoped semantics;
- support impersonation;
- trusted-device enrollment;
- browser/device fingerprinting;
- IP reputation or geolocation trust;
- behavioral or risk scoring;
- WebAuthn/passkeys;
- federation or SSO;
- external directory lifecycle synchronization;
- API/mobile token authority;
- break-glass access;
- Technical Preview activation;
- Production activation;
- updater wiring;
- deployment;
- release;
- Phase-exit authority.

These exclusions are not implied future requirements. Any such concern requires separate Product Owner selection and bounded governance.

## Source-envelope non-authority

This entry gate does not freeze or authorize an application source changed-file envelope.

It does not authorize modifications to:

- `FirstPartySessionAuthorityService`;
- session middleware;
- identity repositories or persistence adapters;
- tenant membership verifiers;
- organizational relationship verifiers;
- organizational access repositories;
- service-provider wiring;
- routes/controllers;
- configuration;
- tests;
- workflows beyond the already-published preservation predecessor;
- migrations.

Those paths may be considered only in a separately governed Sprint40 schema/source-envelope gate after this entry gate is published.

## Exit criteria for this stage

This entry-gate stage is complete only when:

- this exact one-file document is qualified on its exact PR head;
- all triggered workflows succeed without bypass;
- required governance subchecks succeed;
- the PR is Ready;
- a top-level Product Owner exact-head merge authorization exists;
- `product-owner-merge-authority=SUCCESS`;
- final race-check confirms unchanged head, unchanged main base relationship, exact one-file envelope, and `behind_by=0`;
- squash merge uses `expected_head_sha`;
- post-merge canonical commit/tree/parent/signature are verified.

Publication of this entry gate authorizes no Sprint40 source implementation.

The next separately governed boundary after publication is:

**Sprint40 schema/source-envelope gate selection and publication.**

Attribution: **Lab | zefry**
