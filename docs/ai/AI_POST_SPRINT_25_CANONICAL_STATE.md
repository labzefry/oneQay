# oneQay — Post-Sprint 25 Canonical Program State

## Purpose and authority

This document is the canonical current-state supersession record after Sprint 25.

Older repository documents remain valid historical provenance, but they are not current authority where they conflict with this record or newer GitHub publication evidence.

GitHub remains the Single Source of Truth.

Attribution: **Lab | zefry**

## Verified repository baseline

Fresh post-merge verification established:

- repository: `labzefry/oneQay`;
- canonical branch: `main`;
- verified main SHA: `0d48445f1d0935f46a67bdbaf4e974c52c2d6189`;
- verified tree: `ca05ae631c90631639638d0651210dad67a16d2d`;
- parent: `b0307d5737d49b04264188393d6dd98a2d76e7b0`;
- commit: `feat(sprint25): add governed ordinary policy administration delivery foundation (#178)`;
- GitHub signature: **VERIFIED / VALID**;
- PR #178: **CLOSED / MERGED**;
- final Sprint 25 source head: `e9bdccc2cc2074f73fa6ebb07688224f8ace6c01`;
- Sprint 25 source envelope: exactly **18 changed files** after the published M7.4A preservation supplement;
- final source relation before merge: **ahead 25 / behind 0**.

These values are publication provenance for this reconciliation baseline. Future lifecycle mutations still require fresh GitHub verification.

## Sprint 25 publication proof

The final exact Sprint 25 source head passed all sixteen pull-request-triggered workflows, including:

- Governance Required Checks — **SUCCESS**;
- PHP Foundation Regression — **SUCCESS**;
- M7.1 Application Regression — **SUCCESS**;
- M7.2 Tenant Isolation Regression — **SUCCESS**;
- M7.3 Identity Organizational Context Regression — **SUCCESS**;
- M7.4A Technical Preview Interaction Regression — **SUCCESS**;
- M7.5 Preview Database Qualification Regression — **SUCCESS**;
- M7.5 Technical Preview Release Artifact — **SUCCESS**;
- Sprint 21 Role Permission Policy Regression — **SUCCESS**;
- Sprint 22 Policy Administration Regression — **SUCCESS**;
- Sprint 23 Initial Tenant Administrator Provisioning Regression — **SUCCESS**;
- Sprint 24 Protected Control Administrator Lifecycle Regression — **SUCCESS**;
- Sprint 25 Policy Administration Delivery Regression — **SUCCESS**;
- Updater Security Regression — **SUCCESS**;
- Updater Backend Regression — **SUCCESS**;
- Updater UI Contract Regression — **SUCCESS**.

The dedicated Sprint 25 workflow proved the exact final 18-file envelope, zero dependency changes, zero migration changes, Application framework independence, the canonical six-migration set, one bounded POST route, first-party session ownership, durable tenant/organizational re-verification, CSRF preservation, closed Sprint 22 mutation vocabulary, protected-control separation, actual in-process HTTP qualification, and preservation of Sprint 21 through Sprint 24.

Exact-head `product-owner-merge-authority` was **SUCCESS** for `e9bdccc2cc2074f73fa6ebb07688224f8ace6c01`. The valid authorization comment was authored by repository owner `labzefry` for that exact head.

PR #178 was squash-merged using expected-head protection. No independent review was required under the current Product Owner continuation model.

## Sprint 25 canonical state

**Sprint 25 — Governed Ordinary Policy Administration Delivery Foundation** is now:

**COMPLETE / IMPLEMENTED / PUBLISHED**.

Sprint 25 exposes the already-authorized Sprint 22 ordinary policy-administration capability through exactly one bounded first-party HTTP mutation route for Local/Test/CI qualification.

The canonical route is:

`POST /administration/policy/mutations`

with route name:

`policy-administration.mutate`

Sprint 25 creates no GET administration UI, no public API, no authentication bootstrap endpoint, no protected-control route, no background mutation surface, and no Production control plane.

## First-party session boundary

Sprint 25 consumes server-owned Laravel session state only.

The bounded session keys are namespaced under `oneqay.auth.` and carry:

- platform identity ID;
- tenant ID;
- organization ID;
- optional outlet ID;
- optional device ID.

Sprint 25 does not create a login route and does not establish those authentication/session values in production source.

The disposable regression uses a test-only dynamically registered session-seeding route solely inside the test process to emulate an already-authenticated first-party flow. That route is not present in canonical application routes.

## Durable context re-verification

Session values are not accepted as final authorization authority.

Every Sprint 25 request reconstructs bounded server-side identity and tenant value objects, then uses existing durable verification primitives:

- `ServerVerifiedPlatformIdentity`;
- `ServerVerifiedTenantContext`;
- `LaravelTenantMembershipVerifier`;
- `LaravelOrganizationalRelationshipVerifier`;
- `EnterOrganizationalContext`;
- request-scoped tenant and organizational context stores.

The resulting `VerifiedOrganizationalContext` is the only actor supplied to ordinary policy administration.

Request body, query, headers, and route parameters cannot replace actor, tenant, organization, outlet, or device authority.

Request-scoped contexts are cleared after each request in `finally` semantics.

## CSRF and web-session preservation

The Sprint 25 route remains inside Laravel's existing `web` middleware stack.

CSRF protection is not excluded, bypassed, or replaced by a custom header.

The disposable in-process HTTP regression proved that an otherwise authenticated mutation request without a valid CSRF token receives HTTP 419 and writes no policy mutation evidence.

Laravel `_token` is treated only as framework transport metadata and is removed before the strict business mutation payload is parsed.

## Closed ordinary request vocabulary

The Sprint 25 business payload is limited to:

- `mutation_id`;
- `operation`;
- `role`;
- operation-specific `permission`;
- operation-specific `target_identity`.

Unknown business fields fail closed.

Actor identity, tenant, scope IDs, authorization facts, repository names, table names, model names, SQL, and arbitrary command dispatch cannot be supplied through the request body.

## Sprint 22 remains the mutation authority

`PolicyAdministrationDeliveryService` delegates every accepted mutation to the existing:

`DurablePolicyAdministrationService`

Sprint 25 does not duplicate or replace Sprint 22:

- authorization evaluation;
- scope containment;
- protected-control rejection;
- target eligibility;
- idempotency/conflict handling;
- transaction semantics;
- durable policy mutation persistence;
- mutation journal evidence.

The Application-layer delivery command remains framework/database independent and maps only to existing Sprint 22 `DurablePolicyMutation` factories.

## Closed operation vocabulary

Only the existing Sprint 22 operations are accepted:

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

The existing `PolicyMutationOperation` remains the canonical operation validator.

Sprint 24 operations such as `control.administrator.delegate` and `control.administrator.revoke` are rejected by the Sprint 25 ordinary delivery parser.

## Protected-control preservation

Sprint 25 ordinary delivery cannot:

- grant `authorization.policy.manage`;
- revoke `authorization.policy.manage`;
- assign a role carrying the protected control permission;
- revoke a role carrying the protected control permission;
- invoke Sprint 23 initial administrator provisioning;
- invoke Sprint 24 protected-control delegation/revocation;
- invoke emergency protected-control recovery.

The Sprint 22 generic protected-control rejection remains authoritative.

Sprint 23 remains one-time bootstrap only.

Sprint 24 remains the dedicated tenant protected-control assignment lifecycle only.

## HTTP qualification proof

The disposable HTTP regression uses a temporary SQLite database, synthetic data only, existing migrations #1–#6, the actual Laravel Kernel, actual framework session cookie handling, and actual CSRF middleware.

It proved:

- missing CSRF -> HTTP 419 and no mutation evidence;
- ordinary role create succeeds under valid authority;
- ordinary permission grant succeeds;
- exact-scope device assignment succeeds;
- exact-scope device revocation succeeds;
- exact replay returns deterministic prior outcome;
- conflicting replay is rejected;
- request-supplied actor/tenant authority fields are rejected;
- protected permission mutation is rejected;
- protected role assignment is rejected;
- Sprint 24 operation strings are rejected;
- unknown operation strings are rejected;
- foreign-tenant target is rejected;
- actor without policy authority is rejected;
- narrower device-scoped authority cannot escape to broader tenant mutation;
- missing session context is rejected;
- syntactically valid session values without durable membership are rejected;
- Preview runtime receives no active Sprint 25 route;
- Production runtime receives no active Sprint 25 route;
- denied attempts create no unauthorized policy journal evidence;
- request-scoped verified contexts are cleared after processing.

## M7.4A preservation supplement

Sprint 25 route changes triggered a historical M7.4A Technical Preview workflow assertion that required the migration directory not to exist.

That assertion conflicted with the already-published canonical state because migrations #1–#6 legitimately exist.

A documentation-only preservation supplement was published before the M7.4A workflow was changed.

The final M7.4A preservation rule now correctly requires:

- Sprint 25 changes zero migration files;
- the canonical migration directory contains exactly migrations #1–#6;
- Technical Preview Application/Infrastructure/Delivery source contains no database implementation;
- dependency, secret, framework-independence, M7.1, M7.2, M7.3, POS-core, and Technical Preview interaction regressions remain active.

M7.4A passed on the final Sprint 25 source head.

## Canonical migration set

The canonical repository still contains exactly six forward-only migrations:

1. `0000_00_00_000001_create_foundational_context_graph.php`;
2. `0000_00_00_000002_create_organizational_access_grants.php`;
3. `0000_00_00_000003_create_scoped_role_permission_policy.php`;
4. `0000_00_00_000004_create_policy_mutation_journal.php`;
5. `0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php`;
6. `0000_00_00_000006_create_protected_control_administrator_mutation_journal.php`.

Sprint 25 added **zero migrations** and modified **zero migration files**.

## Runtime boundaries

Sprint 25 ordinary policy administration delivery is active only for:

- Local;
- Test;
- CI.

The delivery middleware returns fail-closed HTTP 404 outside that runtime allowlist.

Durable repositories continue to independently enforce persistence-enabled + Local/Test/CI restrictions.

`ONEQAY_PERSISTENCE_ENABLED=false` remains the default repository boundary.

## Technical Preview preservation

Technical Preview remains:

**NO_SCHEMA_CHANGE**.

The Sprint 25 route is not mounted under `/technical-preview`.

Sprint 25 delivery classes are not referenced by Technical Preview Application or Delivery source.

The Technical Preview release artifact remains deterministic and excludes the migration directory. Its final Sprint 25 workflow passed.

## Production and updater boundaries

Production remains:

**NO-GO / NOT AUTHORIZED**.

Updater remains:

**DISABLED / UNWIRED**.

Tenant policy-administration authority does not grant updater, release, deployment, rollback, infrastructure, host, or platform authority.

Updater security, backend, and UI contract regressions all passed on the final Sprint 25 head.

## Closure audit

Fresh bounded closure audit after PR #178 established:

- PR #178 is merged;
- source publication envelope is exactly 18 files;
- no open Sprint 25 lifecycle issue or PR remains;
- canonical search returned zero `TODO` findings;
- canonical search returned zero `FIXME` findings;
- canonical search returned zero `bypass` findings;
- migration directory remains exactly #1–#6;
- Product Owner merge authority was exact-head and successful;
- Technical Preview remains `NO_SCHEMA_CHANGE`;
- Production remains `NO-GO / NOT AUTHORIZED`;
- updater remains separate and disabled/unwired.

## Authentication gap after Sprint 25

Sprint 25 deliberately consumes an already-established authenticated first-party session but does not create one.

Fresh canonical inspection shows the foundational `oneqay_identities` relation contains only:

- `tenant_id`;
- `id`.

The canonical source currently contains no first-party application password/credential verification foundation for those tenant identities.

Therefore the next safe step is **not** to create a login endpoint that trusts arbitrary session seeding, hard-coded administrators, request headers, or environment-owner authority.

A durable credential-verification primitive must exist first.

## Next bounded engineering concern

The next logical concern is a **Governed First-Party Identity Credential Verification Foundation**.

A future Sprint 26 entry gate may authorize a Local/Test/CI-only credential foundation that:

- binds credentials to exact `(tenant_id, identity_id)` identities;
- stores only one-way password hashes using the locked application/runtime password hashing facility;
- never stores plaintext passwords or reversible credentials;
- verifies credentials through a dedicated Application contract and guarded Infrastructure repository;
- uses generic authentication failure semantics resistant to identity enumeration;
- supports deterministic synthetic qualification only;
- introduces no login/session-writing route yet;
- introduces no password reset, registration, email verification, MFA enrollment, TOTP secret, remember-me token, API token, or external identity-provider integration;
- remains separate from updater and platform authority;
- remains Local/Test/CI-only;
- keeps Technical Preview `NO_SCHEMA_CHANGE` unless separately and explicitly authorized;
- keeps Production `NO-GO / NOT AUTHORIZED`.

Because canonical identities currently have no credential relation, Sprint 26 may require one additive forward-only migration #7, but that schema change is **not authorized by this post-Sprint25 reconciliation document**. It requires a separately published Sprint 26 entry gate with an exact migration and source envelope.

Interactive login/session establishment should follow only after credential verification is safely established and separately authorized.

## Canonical declaration

As of this reconciliation:

- Sprint 21 remains **COMPLETE / IMPLEMENTED / PUBLISHED**;
- Sprint 22 remains **COMPLETE / IMPLEMENTED / PUBLISHED**;
- Sprint 23 remains **COMPLETE / IMPLEMENTED / PUBLISHED**;
- Sprint 24 remains **COMPLETE / IMPLEMENTED / PUBLISHED**;
- Sprint 25 is **COMPLETE / IMPLEMENTED / PUBLISHED**;
- canonical migrations remain exactly #1–#6;
- ordinary policy administration delivery exists for Local/Test/CI only;
- first-party authenticated session establishment remains unresolved;
- first-party tenant identity credential verification remains unresolved;
- Technical Preview remains `NO_SCHEMA_CHANGE`;
- Production remains `NO-GO / NOT AUTHORIZED`;
- updater remains `DISABLED / UNWIRED`;
- emergency protected-control recovery remains unresolved and unauthorized;
- next source work requires a separately published Sprint 26 entry gate.

GitHub remains the Single Source of Truth.

Attribution: **Lab | zefry**
