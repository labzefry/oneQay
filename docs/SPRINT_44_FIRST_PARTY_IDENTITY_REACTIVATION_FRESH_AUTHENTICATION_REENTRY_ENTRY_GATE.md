# Sprint44 First-Party Identity Reactivation Fresh Authentication Re-entry Entry Gate

## Status

**ENTRY GATE / CONCERN SELECTED / SOURCE NOT AUTHORIZED**

Date: **2026-08-29**

Attribution: **Lab | zefry**

## 1. Selected concern

Sprint44 selects exactly one successor concern:

**First-Party Identity Reactivation Fresh Authentication Re-entry Foundation**

Sprint43 restored only first-party authentication eligibility for one exact authorized ordinary identity.

Sprint44 is limited to governing and proving the later fresh-authentication re-entry boundary after that reactivation. It must preserve the invariant that reactivation itself creates no session authority and that every previously revoked or expired session remains unusable.

## 2. Intended security outcome

A previously disabled ordinary identity that has been validly reactivated under Sprint43 may regain authenticated access only by completing the existing fresh first-party authentication path.

A successful Sprint44 flow must require all authentication/session controls that would apply to any fresh login at that time. Reactivation is not authentication.

No earlier framework session, logical session, public handle, authority identifier, credential proof, factor proof, step-up proof, recovery proof, or enrollment proof may become valid merely because eligibility changed from disabled to enabled.

## 3. Entry preconditions

Any later Sprint44 implementation must begin from all of these conditions:

- the target is one exact ordinary identity;
- the target remains in the exact server-derived tenant;
- Sprint43 reactivation has already completed successfully when reactivation is required;
- current first-party authentication eligibility is true;
- the fresh caller supplies credentials only through the existing authentication boundary;
- tenant, organization, outlet, device, role, permission, factor, session, and authority selection remain server controlled;
- all existing credential-epoch, factor-epoch, membership, organizational-access, lifetime, session-control, and eligibility checks remain authoritative.

Sprint44 does not permit caller-supplied tenant or authority context to become trusted re-entry authority.

## 4. Fresh authentication is mandatory

Sprint44 must preserve all of the following:

- reactivation never logs the target in;
- reactivation never creates a framework session;
- reactivation never creates a logical first-party session;
- reactivation never regenerates or restores an old logical-session authority;
- a reactivated identity must present valid current credentials through the normal fresh-authentication path;
- any required current MFA/factor flow remains independently mandatory;
- successful fresh authentication must establish only newly issued session authority according to the existing canonical session-establishment rules.

No shortcut from the Sprint43 administration endpoint to authenticated session state is permitted.

## 5. Old-session non-resurrection

Sprint44 must explicitly prove that re-entry never revives historical session authority.

A session that was:

- revoked before disablement;
- revoked by Sprint42 disablement-triggered termination;
- expired by absolute lifetime;
- invalidated by idle lifetime;
- invalidated by credential epoch;
- invalidated by factor epoch;
- invalidated by tenant membership;
- invalidated by organization/outlet/device access;
- invalidated by current identity eligibility;

must remain invalid after Sprint43 reactivation and after later successful fresh authentication.

Sprint44 must not clear or rewrite historical `revoked_at_unix` evidence.

A fresh session, if later authorized by the schema/source gate, must use newly issued session authority and must not reuse a revoked public handle or authority identifier.

## 6. Disable-reactivate-login-disable safety cycle

The later bounded proof must cover the security cycle:

1. an ordinary identity has an active valid fresh session;
2. Sprint41 disablement changes eligibility from true to false;
3. Sprint42 terminates exact-target active logical sessions;
4. Sprint43 reactivation changes eligibility from false to true without restoring session authority;
5. all old sessions remain invalid;
6. only fresh authentication may establish new authority;
7. a later valid disablement must again terminate that newly established active session.

The cycle must not create session duplication, resurrection, privilege restoration, or hidden bypass state.

## 7. Credential and factor boundaries

Sprint44 does not authorize changes to:

- password value or password hash;
- credential epoch;
- TOTP secret;
- factor epoch;
- recovery codes;
- recovery authority;
- privileged step-up semantics;
- enrollment state;
- password-reset state.

Fresh authentication must consume current canonical credential/factor state exactly as already governed.

Sprint44 is not credential recovery, password reset, factor recovery, MFA replacement, or factor enrollment.

## 8. Organizational and authorization boundaries

Sprint44 does not restore or mutate:

- tenant membership;
- organization membership;
- outlet access;
- device access;
- tenant/organization/outlet/device role assignments;
- permissions;
- grants;
- protected-control role state.

Successful re-entry must still satisfy all independently required current server-derived authorization and organizational-access checks.

Reactivation of a formerly eligible identity does not guarantee that the identity is presently permitted to enter a particular organization, outlet, device, or privileged surface.

## 9. Protected-control boundary

Sprint44 does not authorize protected-control lifecycle changes.

It does not authorize:

- protected-control identity reactivation;
- self-service reactivation;
- protected-control recovery bypass;
- support/admin bypass of normal authentication;
- automatic administrator restoration;
- emergency break-glass behavior.

Any protected-control reactivation or special recovery lifecycle remains a separately governed concern.

## 10. API and route boundary

This entry gate does not select a new public route or request/response shape.

The preferred architectural direction is to compose already-governed Sprint43 eligibility reactivation with the existing fresh first-party authentication/session-establishment path rather than introducing a special reactivation-login endpoint.

The later schema/source-envelope gate must explicitly decide whether any source route, controller, service, repository, workflow, test, or documentation changes are required.

Generic operations such as `login_after_reactivate`, `restore_session`, `resume_session`, or caller-selected session authority are not authorized by this entry gate.

## 11. Schema and migration boundary

This entry gate makes **no schema decision**.

Canonical source migrations remain exactly **#1 through #15** at entry.

Migration #16 remains **NOT SELECTED**.

The required next gate must determine whether Sprint44 can be proven with no schema change or whether a separately justified schema change is necessary.

No migration creation, modification, application, rollback, or runtime schema mutation is authorized here.

## 12. Required next gate

Before any Sprint44 source implementation, a separately governed:

**Sprint44 Schema / Source Envelope Gate**

must freeze at minimum:

- schema decision and migration #16 status;
- exact fresh-authentication/session-establishment composition strategy;
- whether source changes are actually required or only regression/integration proof is required;
- exact session issuance and non-resurrection invariants;
- exact disable-reactivate-login-disable convergence behavior;
- credential/factor epoch behavior;
- organization/outlet/device and tenant revalidation behavior;
- authorization and protected-control exclusions;
- failure and transaction semantics;
- exact source/test/workflow/document envelope;
- sorted newline-terminated changed-path SHA-256;
- historical compatibility predecessor envelope, if required.

Until that gate is published, Sprint44 application source is **NOT AUTHORIZED**.

## 13. Lifecycle locks

Sprint44 entry-gate work is bounded to repository governance and documentation.

Technical Preview remains:

**NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED**

Production remains:

**NO-GO / NOT AUTHORIZED**

Updater remains:

**DISABLED / UNWIRED**

Deployment, release, and rollback remain:

**NOT AUTHORIZED**

No Technical Preview activation, Production activation, migration execution, updater wiring, deployment, release, rollback, or operational reactivation is authorized.

## 14. Explicit non-authority

This entry gate does not authorize:

- Sprint44 source implementation;
- schema modification;
- migration #16;
- new authentication endpoints;
- automatic login after reactivation;
- old-session restoration;
- session resurrection;
- framework-session restoration;
- caller-selected tenant/session authority;
- credential or factor mutation;
- recovery bypass;
- protected-control lifecycle changes;
- membership/role/permission/grant restoration;
- bulk or cross-tenant re-entry;
- Technical Preview activation;
- Production activation;
- updater wiring;
- deployment;
- release;
- rollback.

Unknown or ambiguous successor behavior remains fail closed.

## 15. Entry-gate changed-file envelope

This entry gate changes exactly one path:

`docs/SPRINT_44_FIRST_PARTY_IDENTITY_REACTIVATION_FRESH_AUTHENTICATION_REENTRY_ENTRY_GATE.md`

Sorted newline-terminated one-path SHA-256:

`93559a76a6b31068316959787d8e7ebf041355955aa4226262958c39f791af89`

Any different path set is outside this gate.
