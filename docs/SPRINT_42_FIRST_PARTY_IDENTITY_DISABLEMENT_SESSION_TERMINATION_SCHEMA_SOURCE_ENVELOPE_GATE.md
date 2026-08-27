# Sprint42 First-Party Identity Disablement Session Termination Schema / Source Envelope Gate

## Status

**SCHEMA/SOURCE-ENVELOPE GATE ONLY / NO SOURCE IMPLEMENTATION AUTHORITY**

Date: **2026-08-27**

Attribution: **Lab | zefry**

## 1. Canonical starting point

This gate follows the published Sprint42 entry gate PR #327 and the exact schema/source-gate preservation predecessor PR #328.

Canonical base:

- commit: `ceb6cd6574de80d0a51f10790275108f7d8ec1c1`;
- tree: `d092c8db8ef6b8f2cc5df219e5d7a0673d47e6ed`;
- signature: **verified / valid**;
- Sprint42 entry gate: PR #327;
- schema/source-gate preservation predecessor: PR #328.

This gate changes exactly one path:

`docs/SPRINT_42_FIRST_PARTY_IDENTITY_DISABLEMENT_SESSION_TERMINATION_SCHEMA_SOURCE_ENVELOPE_GATE.md`

Sorted newline-terminated one-path SHA-256:

`ea64d8bb82faab74b21dfc3cab1c5ac6550e4444fc91baee7567f4fe2680bd26`

Unknown successor shapes remain fail-closed.

## 2. Selected concern

**Sprint42 First-Party Identity Disablement Session Termination Foundation**

Sprint42 composes the published Sprint41 disable-only identity-authentication eligibility administration with the existing durable first-party logical session registry.

The later source implementation must establish this postcondition:

> Before the already-published Sprint41 administrative disablement operation returns a successful deterministic outcome, the exact target tenant + identity must be disabled and must have no active first-party logical session authority remaining.

Sprint40 request-time eligibility revalidation remains an independent mandatory defense and is not replaced by termination.

## 3. Schema determination

Sprint42 schema classification is frozen as:

**NO_SCHEMA_CHANGE / MIGRATION #16 NOT SELECTED**

Canonical source already provides all persistence required:

1. migration #13:
   - `oneqay_identity_first_party_sessions`;
   - `revoked_at_unix`;
   - exact `tenant_id + identity_id` ownership indexes;
2. migration #15:
   - `oneqay_identity_authentication_eligibility_mutations`;
   - deterministic mutation ID binding;
   - actor identity;
   - target identity;
   - operation;
   - payload fingerprint;
   - outcome;
   - occurred-at timestamp.

Migrations #1 through #15 remain immutable.

No table, column, index, foreign key, trigger, generated column, scheduler table, outbox table, or migration #16 is selected.

Rollback remains unauthorized.

## 4. Audit and evidence decision

Sprint42 must **not** repurpose the existing self-service session audit event `all_sessions_revoked` for administrator-targeted disablement.

No new session-audit event is selected.

No new audit table or journal is selected.

Durable security evidence is the composition of:

- the existing Sprint41 mutation journal row;
- the exact target identity's disabled eligibility state;
- monotonic `revoked_at_unix` values on any target logical sessions that were active at termination time.

The termination timestamp is server-derived.

No password, password hash, TOTP secret, recovery secret/code, cookie, framework session ID, CSRF token, bearer token, public session handle, arbitrary reason text, IP history, or browser fingerprint may be written as Sprint42 evidence.

## 5. Dedicated administrative termination repository

Sprint42 must not widen the self-service `FirstPartySessionAuthorityRepository` contract with administrator-targeted semantics.

The later source implementation introduces a separate application contract:

`App\Application\Identity\FirstPartyIdentityDisablementSessionTerminationRepository`

with one operation only:

`revokeActiveForIdentityDisablement(TenantId $tenantId, PlatformIdentityId $targetIdentityId, int $revokedAtUnix): int`

The method:

- accepts server-owned tenant + exact target identity only;
- accepts no actor-selected session authority ID;
- accepts no public handle;
- accepts no organization/outlet/device selector;
- accepts no caller-selected tenant;
- returns the non-negative number of active target authorities that transitioned to revoked;
- must never reactivate or rewrite an already-revoked authority.

The infrastructure adapter is frozen as:

`App\Infrastructure\Identity\LaravelFirstPartyIdentityDisablementSessionTerminationRepository`

It must reuse the existing canonical database connection, persistence boundary, runtime class, and session-control feature arm.

## 6. Exact session target set

The termination adapter may update only rows satisfying all of:

- exact `tenant_id`;
- exact `identity_id`;
- `revoked_at_unix IS NULL`;
- `expires_at_unix >= revokedAtUnix`.

It must set only:

`revoked_at_unix = revokedAtUnix`

It must not alter:

- authority ID;
- public handle;
- organization;
- outlet;
- device;
- credential epoch;
- factor epoch;
- issued time;
- last-seen time;
- expiry time.

Expired authorities are not active targets and remain unchanged.

Already-revoked authorities remain unchanged.

Another identity in the same tenant remains unchanged.

A same-text identity in another tenant remains unchanged.

Because Sprint41 denies actor == target, the administrator actor's own authority cannot be included merely by the disablement operation.

## 7. Transaction and ordering contract

The existing `PersistenceTransaction` remains the only application transaction abstraction.

For a fresh mutation:

1. Sprint41 preflight remains mandatory;
2. canonical administration clock produces a positive Unix timestamp;
3. one `PersistenceTransaction::run(...)` encloses the fresh mutation path;
4. existing Sprint41 `applyFresh(...)` performs the deterministic eligibility/journal transition;
5. before the transaction may return success, Sprint42 termination enforces the no-active-target-session postcondition;
6. any termination storage/runtime failure fails the transaction;
7. a fresh `applied` transition must not commit while target active session termination fails.

The later implementation must not introduce a second transaction manager, queue, delayed job, background worker, or asynchronous convergence.

## 8. Outcome, no-change, and replay composition

Sprint41 outcome vocabulary remains exactly:

- `applied`;
- `no_change`.

Sprint42 does not add another journal outcome.

The successful postcondition is convergent:

### Fresh `applied`

The exact identity transition, journal insertion, and target active-session termination are required before success.

### Fresh `no_change`

The target is already disabled. The fresh deterministic journal remains `no_change`, and the same invocation must still enforce zero active target logical sessions before success.

This closes stale historical/local-test state without changing identity eligibility.

### Exact replay

Same mutation ID + exact same fingerprint continues to return the prior deterministic Sprint41 outcome.

Before replay success is returned, Sprint42 must idempotently enforce zero active target logical sessions.

Replay:

- creates no duplicate Sprint41 journal row;
- creates no Sprint42 audit row;
- does not change the prior outcome;
- does not reactivate anything;
- may transition newly/stale active target session rows only from active to revoked.

### Conflicting replay

Same mutation ID + different fingerprint continues to fail closed before termination authority is granted.

## 9. Concurrency contract

Concurrency must converge monotonically.

For distinct concurrent mutation IDs against one enabled target:

- at most one eligibility transition may produce `applied`;
- other deterministic fresh outcomes may converge to `no_change`;
- every successful invocation enforces zero active target logical sessions before returning;
- no concurrent path may restore `first_party_authentication_enabled=true`;
- no concurrent path may clear `revoked_at_unix`;
- another tenant or identity must remain untouched.

For the same mutation ID:

- exact fingerprint replay remains deterministic;
- conflicting fingerprint reuse fails closed;
- duplicate journal evidence is prohibited.

## 10. Authorization and target boundary

Sprint42 inherits Sprint41 authority without widening it.

The actor must continue to be derived from the existing authenticated, tenant-scoped policy-administration context and must hold:

`AdministrationPermission::MANAGE`

The target remains:

- same tenant;
- ordinary first-party identity;
- not actor self;
- not protected-control identity.

Sprint42 introduces no new role or permission.

The session-termination repository receives only already-authorized server-derived tenant + target identity.

## 11. HTTP and public contract

Sprint42 adds **no new public route**.

The only public operation remains the already-published Sprint41 route:

`POST /administration/identities/{identity_id}/authentication-disablement`

Route name remains:

`identity.authentication-eligibility.disable`

Request payload remains exactly:

`mutation_id`

No session handle, session selector, revoke flag, terminate flag, enabled flag, force flag, tenant selector, actor selector, organization, outlet, device, or reason field is added.

The controller public response and generic rejection behavior remain unchanged.

## 12. Sprint36-Sprint41 preservation

The later source must preserve:

- Sprint36 inventory, revoke-one, revoke-others, canonical logout, opaque handle boundary;
- Sprint37 self-scoped `POST /auth/sessions/revoke-all`;
- Sprint38 idle TTL **7200 seconds**;
- Sprint38 absolute TTL **43200 seconds**;
- Sprint39 tenant membership and exact organization/outlet/device revalidation;
- Sprint40 request-time authentication eligibility revalidation;
- Sprint41 disable-only authorization, target exclusion, mutation ID, replay/conflict, journal, route, payload, and safe public response semantics.

Sprint42 must not create an administrator-targeted session inventory or independent revoke-one/revoke-others/revoke-all API.

## 13. Existing feature/runtime boundaries

No new feature flag or environment variable is selected.

The termination adapter must reuse existing canonical controls:

- durable persistence enabled boundary;
- runtime class restricted to **Local / Test / CI**;
- existing first-party session-control feature arm.

Failure of those controls fails closed.

Technical Preview remains:

**NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED**

Sprint41 migration #15 remains not applied/activated in Technical Preview.

Production remains:

**NO-GO / NOT AUTHORIZED**

Updater remains:

**DISABLED / UNWIRED**

Deployment and release remain:

**NOT AUTHORIZED**

## 14. Exact future source-preservation predecessor

Before the later Sprint42 source implementation PR, a separately qualified preservation predecessor must update exactly these eight workflow paths:

1. `.github/workflows/m7-5-preview-release-artifact.yml`
2. `.github/workflows/sprint35-privileged-totp-recovery-regression.yml`
3. `.github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml`
4. `.github/workflows/sprint37-first-party-all-session-termination-regression.yml`
5. `.github/workflows/sprint38-first-party-session-absolute-lifetime-regression.yml`
6. `.github/workflows/sprint39-first-party-session-organizational-access-revalidation-regression.yml`
7. `.github/workflows/sprint40-first-party-session-identity-disablement-revalidation-regression.yml`
8. `.github/workflows/sprint41-first-party-identity-authentication-eligibility-administration-regression.yml`

Sorted newline-terminated eight-path SHA-256:

`e773cbe308d202808021c452c75954cc80234c081bbb1f23cb757210f76b8c85`

That predecessor may change only exact source-successor recognition, trigger, and historical fixture/isolation logic needed for the frozen Sprint42 source envelope.

It must not implement Sprint42 application behavior.

Unknown successor shapes remain fail-closed.

## 15. Exact later Sprint42 source envelope

After the required source-preservation predecessor is published, the Sprint42 source implementation is frozen to exactly these eight paths:

1. `.github/workflows/sprint42-first-party-identity-disablement-session-termination-regression.yml`
2. `apps/web/app/Application/Identity/FirstPartyIdentityDisablementSessionTerminationRepository.php`
3. `apps/web/app/Application/Identity/FirstPartyIdentityEligibilityAdministrationService.php`
4. `apps/web/app/Infrastructure/Identity/LaravelFirstPartyIdentityDisablementSessionTerminationRepository.php`
5. `apps/web/app/Providers/AppServiceProvider.php`
6. `apps/web/tests/first-party-identity-authentication-eligibility-administration.php`
7. `apps/web/tests/first-party-identity-disablement-session-termination.php`
8. `docs/FIRST_PARTY_IDENTITY_DISABLEMENT_SESSION_TERMINATION_FOUNDATION.md`

Sorted newline-terminated eight-path SHA-256:

`6315890d318c3cdfca549bfacef6cb8d1ca66a4421416b49b4978095a98b6729`

No other path belongs to the authorized Sprint42 source envelope.

In particular, the source implementation must not modify:

- routes;
- HTTP controller;
- migrations;
- config;
- dependency manifests/locks;
- self-service `FirstPartySessionAuthorityRepository`;
- self-service session controller/service;
- Sprint40 middleware.

## 16. Role of authorized source paths

### Sprint42 workflow

The new workflow must enforce the exact eight-path source fingerprint, zero migration diff, migrations #1-#15 immutability, no migration #16, no route/controller/config/dependency diff, Local/Test/CI boundary, exact target ownership, transaction rollback, replay/no-change convergence, and preserved Sprint36-Sprint41 regressions.

### Termination repository contract

The new application interface exposes only exact identity-disablement session termination.

It must contain no public handle, actor authority, arbitrary session selector, reactivation, or cross-tenant method.

### Sprint41 administration service

The existing service may change only to compose the dedicated termination repository with the existing persistence transaction and clock.

It must preserve the public `disable(...)` API and Sprint41 violation/outcome semantics.

### Laravel termination adapter

The adapter may update only the exact active target session rows and only `revoked_at_unix`.

It must fail closed on disabled persistence, disallowed runtime, disabled session-control boundary, invalid timestamp, malformed storage result, or storage failure.

It must not insert session audit rows.

### AppServiceProvider

Provider changes are limited to binding the new dedicated repository and injecting it into the existing administration service using existing canonical configuration primitives.

### Existing Sprint41 regression

The existing Sprint41 regression may change only as necessary to provide the new constructor dependency and to prove prior Sprint41 behavior remains unchanged.

### Dedicated Sprint42 regression

The new fixture must prove at least:

- authorized fresh `applied` disablement revokes all active exact-target authorities;
- identity transition, journal, and active-session revocation converge before success;
- actor session remains active;
- another identity same tenant remains active;
- same-text target identity another tenant remains active;
- expired target session is not rewritten;
- already-revoked target session is not rewritten;
- fresh `no_change` still removes active target authorities without changing journal outcome;
- exact replay preserves prior outcome and journal cardinality while enforcing zero active target authorities;
- conflicting replay performs no termination;
- termination storage failure rolls back a fresh enabled-target disablement and journal insert;
- persistence-disabled / runtime-denied / session-control-disabled paths fail closed;
- no self-service session API semantics are widened;
- no new route/payload exists;
- no session-audit event is inserted by Sprint42;
- migrations #1-#15 are unchanged;
- no migration #16 exists;
- Sprint40 still denies disabled identity request-time use;
- prior Sprint41 regression remains green.

### Foundation document

The document must record the published bounded implementation facts only and preserve all lifecycle locks.

## 17. Explicit exclusions

Sprint42 source must not add or mutate:

- identity re-enable/reactivation;
- protected-control identity lifecycle;
- last-administrator/break-glass flow;
- bulk disablement;
- independent administrator session inventory;
- independent administrator revoke-one/revoke-others/revoke-all route;
- cross-tenant/global logout;
- credential epoch;
- factor epoch;
- password/TOTP/recovery material;
- tenant membership;
- organization/outlet/device grants;
- self-service session audit vocabulary;
- background job/queue;
- feature flag/environment variable;
- config;
- dependency manifests/locks;
- migration #16 or later;
- Technical Preview activation;
- Production activation;
- updater wiring;
- deployment;
- release.

If implementation requires an excluded path or semantic, source mutation must stop pending a separately governed envelope revision.

## 18. Exit criteria for this gate stage

This gate stage is complete only when:

- this exact one-document gate is qualified on its exact PR head;
- every materially triggered workflow succeeds;
- queued/cancelled/empty execution is not treated as success;
- governance checks succeed;
- the PR is Ready;
- top-level Product Owner exact-head merge authorization exists;
- `product-owner-merge-authority=SUCCESS`;
- final race-check confirms current main, exact head, exact one-file envelope, and mergeability;
- squash merge uses `expected_head_sha`;
- post-merge canonical commit/tree/signature are verified.

Only after publication of this gate may the separately governed eight-workflow Sprint42 source-preservation predecessor begin.

Publication of this gate does **not** authorize Sprint42 source implementation.
