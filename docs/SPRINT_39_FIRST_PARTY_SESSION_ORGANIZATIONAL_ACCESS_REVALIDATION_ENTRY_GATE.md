# Sprint39 First-Party Session Organizational Access Revalidation Entry Gate

## Status

**SELECTED CONCERN / ENTRY GATE ONLY / NO SOURCE IMPLEMENTATION AUTHORITY**

Date: **2026-08-25**

This document selects and freezes the bounded Sprint39 concern **First-Party Session Organizational Access Revalidation Foundation**.

It is an entry gate only. It does not authorize source implementation, schema mutation, migration creation, Technical Preview activation, Production activation, updater wiring, deployment, or release.

## Canonical starting point

This entry gate is prepared from canonical `main` after publication of the Sprint39 entry-gate preservation predecessor PR #257:

- canonical base commit: `5a4d14a667185d4039371c79e13488d8beb84ca6`;
- canonical base tree: `a1c16a229cc0fc1a5efa1d86580cf2c5e6132187`;
- canonical base parent: `9b4a13b560a90a53eb7f62b1baafd94228956148`;
- canonical base signature: **verified / valid**;
- preservation predecessor: PR #257, `ci: preserve Sprint39 entry-gate successor`.

The predecessor recognizes this exact one-file successor:

`docs/SPRINT_39_FIRST_PARTY_SESSION_ORGANIZATIONAL_ACCESS_REVALIDATION_ENTRY_GATE.md`

Its sorted newline-terminated one-path SHA-256 is:

`b2c4936a3315788100b47537b32474e7079143942c38684cf2394628f1084dd2`

Unknown successor shapes remain fail-closed.

## Selected concern

Sprint39 selects:

**First-Party Session Organizational Access Revalidation Foundation**

The concern is limited to proving that a durable first-party logical session authority remains organizationally authorized at request time after the authority was issued.

It does not create a new organizational-access model and does not authorize grant administration.

## Selection rationale

Canonical organizational access is server-owned and durable. Existing foundations already provide tenant membership and exact organization/outlet/device relationship verification, while migration #13 persists the organizational coordinates attached to a logical first-party session authority.

The post-Sprint38 session contract already verifies durable session ownership, revocation, credential/factor epochs, idle lifetime, absolute lifetime, and equality-boundary behavior. It also requires the current session context to match the durable authority record.

However, a context that matched when the logical authority was issued can later become unauthorized because tenant membership or a selected organization/outlet/device relationship is removed. A request-time equality check against the durable session record alone must not convert historical access into continuing authorization.

The next meaningful bounded gap is therefore to revalidate the still-current durable organizational access represented by the already-authenticated logical authority before that authority remains usable.

This aligns with:

- server-side tenant membership authority;
- deny-by-default authorization;
- joiner/mover/leaver access handling;
- exact tenant and organizational isolation;
- fail-closed request-time authority evaluation.

## Frozen authority model

The Sprint39 foundation must preserve these authority layers as distinct evidence:

1. durable logical first-party session authority from Sprint36;
2. tenant + identity ownership derived server-side;
3. `credential_epoch` validation;
4. privileged `factor_epoch` validation where applicable;
5. revocation state;
6. idle lifetime of **7200 seconds**;
7. absolute lifetime of **43200 seconds / 12 hours** from durable `issued_at_unix`;
8. exact organization/outlet/device coordinates represented by the current logical authority;
9. current durable tenant membership and current durable organizational relationship authorization.

Organizational relationship evidence does not replace credential, factor, revocation, or lifetime evidence, and those controls do not replace organizational revalidation.

## Frozen request-time semantics

A usable first-party logical authority must continue to satisfy all previously published Sprint36 through Sprint38 invariants and, additionally, must still be authorized by current durable organizational access.

The future bounded implementation must enforce all of the following semantics:

- tenant and identity remain server-derived from the verified current authority and authenticated session context;
- organization, optional outlet, and optional device coordinates used for revalidation are the exact coordinates represented by the verified current authority/session context;
- caller-supplied tenant, identity, organization, outlet, or device selectors must not become authority;
- current durable tenant membership must still exist for the exact tenant + identity;
- the exact organization relationship must still be permitted;
- when an outlet is present, the exact tenant + identity + organization + outlet relationship must still be permitted;
- when a device is present, the exact tenant + identity + organization + optional outlet + device relationship must still be permitted;
- a device-bound authority without a structurally valid required organizational context fails closed;
- an outlet-bound or device-bound authority must not silently fall back to broader organization-only access;
- removal or loss of tenant membership or any required selected relationship causes the request-time authority evaluation to fail closed;
- malformed, missing, contradictory, or impossible relationship evidence fails closed;
- revalidation must not recreate a removed grant;
- revalidation must not auto-switch the current organization, outlet, or device context;
- revalidation must not mint a replacement logical authority;
- stale access must not resurrect through retry, replay, session rotation, inventory access, step-up activity, or concurrent request timing;
- another tenant or another identity remains untouched.

## Failure handling boundary

If organizational access revalidation fails, the request must not continue as an authorized protected request.

Future source design may invalidate the local Laravel session/current logical-session binding as part of fail-closed handling, but this entry gate does not authorize a new durable grant mutation, a new caller-selectable recovery route, or a replacement login authority.

Loss of organizational authorization is validation evidence. It does not, by itself, authorize rewriting historical organizational grants or silently restoring access.

Any durable session-row transition beyond what is strictly necessary to preserve the existing session authority contract must be frozen separately in the later schema/source-envelope gate before implementation.

## Preserved Sprint36 session semantics

Sprint39 must preserve:

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

## Preserved Sprint37 semantics

Sprint39 must preserve tenant-scoped revoke-all:

- `POST /auth/sessions/revoke-all`;
- exact current tenant + identity scope derived server-side;
- current authority included;
- durable revoke succeeds before local session invalidation;
- replay/concurrency remains monotonic;
- another tenant/identity remains untouched;
- existing `all_sessions_revoked` audit vocabulary remains secret-free.

## Preserved Sprint38 lifetime semantics

Sprint39 must preserve:

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

Organizational revalidation must not reset or extend either lifetime.

## Schema decision

Sprint39 entry-gate schema status is:

**NO_SCHEMA_CHANGE**

Rationale:

- existing durable organizational access foundations already represent tenant membership and organization/outlet/device relationships;
- migration #13 already stores the organizational coordinates required by durable first-party logical session authority;
- request-time revalidation can consume those existing authorities without a new durable schema concept.

Canonical migrations remain exactly **#1 through #13**.

Migrations #1 through #13 are immutable for this stage.

Migration #14 is:

**NOT REQUIRED / NOT SELECTED / NOT AUTHORIZED**

No table, column, index, migration artifact, rollback path, or schema mutation is authorized by this entry gate.

## Route and API impact

This entry gate selects:

**NO NEW ROUTE / NO NEW PUBLIC API / NO NEW REQUEST PAYLOAD**

Sprint39 must not create a caller-controlled owner or organizational selector.

Existing published session-control routes and canonical logout remain unchanged.

Any future route/API proposal requires separate Product Owner selection and authority.

## Audit impact

This entry gate selects:

**NO NEW AUDIT EVENT**

Existing server-derived secret-free session audit vocabulary remains unchanged.

Organizational revalidation is request-time authorization validation, not a new business mutation vocabulary.

If a future concern requires durable evidence specifically for access-loss denial, that audit concern must be separately selected and governed.

## Feature-arm decision

This entry gate selects:

**NO NEW FEATURE ARM**

Any future Sprint39 source work must remain under the existing disabled-by-default first-party session-control boundary:

`ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false`

No environment-controlled bypass or independent Sprint39 production arm is authorized.

## Runtime and activation boundary

Sprint39 remains:

**Local/Test/CI only**

Technical Preview remains:

**NO_SCHEMA_CHANGE / NOT ACTIVATED BY THIS GATE**

Production remains:

**NO-GO / NOT AUTHORIZED**

Updater remains:

**DISABLED / UNWIRED**

Deployment and release remain:

**NOT AUTHORIZED**

This entry gate does not arm any protected runtime.

## Frozen regression targets

A later schema/source-envelope gate must freeze an implementation envelope capable of proving at least these behaviors:

1. exact current tenant membership plus exact current organization relationship allows an otherwise-valid authority to proceed;
2. removed tenant membership causes the authority evaluation to fail closed;
3. removed organization relationship causes the authority evaluation to fail closed;
4. removed outlet relationship causes an outlet-bound authority to fail closed;
5. removed device relationship causes a device-bound authority to fail closed;
6. a malformed device-bound authority/context fails closed;
7. a device-bound or outlet-bound authority does not fall back to broader organization-only permission;
8. another tenant remains isolated;
9. another identity remains isolated;
10. caller-controlled organizational coordinates cannot override the durable current authority coordinates;
11. current access revalidation does not recreate grants or switch context;
12. session rotation does not bypass revalidation;
13. privileged step-up does not bypass revalidation;
14. revoke-one, revoke-others, revoke-all, inventory, and canonical logout remain preserved;
15. credential and factor epoch invalidation remain preserved;
16. 7200-second idle and 43200-second absolute lifetime semantics remain preserved;
17. equality-boundary and deadline + 1 behavior remain preserved;
18. no new public route or payload is introduced;
19. no new audit event is introduced;
20. migrations remain exactly #1 through #13 and no migration #14 exists;
21. disabled runtime, Technical Preview, Production, updater, deployment, and release boundaries remain preserved.

Unknown or contradictory authority states must remain fail-closed.

## Explicit exclusions

Sprint39 entry-gate authority excludes:

- migration #14;
- schema mutation;
- new session route or API;
- grant administration UI or mutation flows;
- automatic grant restoration;
- organization/outlet/device auto-switching;
- administrator force-logout of another identity;
- account disablement or suspension lifecycle;
- support impersonation;
- trusted-device enrollment;
- browser/device fingerprinting;
- IP reputation or geolocation trust;
- behavioral or risk scoring;
- WebAuthn/passkeys;
- federation or SSO;
- API/mobile token authority;
- break-glass access;
- new audit vocabulary;
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
- organizational verifier interfaces or implementations;
- organizational access repositories;
- service-provider wiring;
- routes/controllers;
- configuration;
- tests;
- workflows beyond the already-published preservation predecessor;
- migrations.

Those paths may be considered only in a separately governed Sprint39 schema/source-envelope gate after this entry gate is published.

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

Publication of this entry gate authorizes no Sprint39 source implementation.

The next separately governed boundary after publication is:

**Sprint39 schema/source-envelope gate selection and publication.**

Attribution: **Lab | zefry**
