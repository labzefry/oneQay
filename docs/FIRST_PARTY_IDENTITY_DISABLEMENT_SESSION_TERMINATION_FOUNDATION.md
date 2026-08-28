# First-Party Identity Disablement Session Termination Foundation

## Status

**SPRINT42 SOURCE IMPLEMENTATION / NO_SCHEMA_CHANGE / NOT ACTIVATED**

Attribution: **Lab | zefry**

## Published concern

Sprint42 composes the published Sprint41 disable-only first-party identity authentication eligibility administration with the durable first-party logical session registry.

Before a successful Sprint41 administrative disablement invocation returns, the exact authorized tenant + target identity is disabled and no active logical first-party session authority for that target remains at the server-owned termination timestamp.

Sprint40 request-time eligibility revalidation remains mandatory and independent.

## Schema and evidence

Sprint42 is **NO_SCHEMA_CHANGE**.

- migrations #1 through #15 remain immutable;
- migration #16 is not selected and does not exist;
- no new table, column, index, trigger, outbox, scheduler, queue, or journal is introduced;
- rollback remains unauthorized.

Durable security evidence remains the composition of the existing Sprint41 mutation journal, the target identity disabled authentication-eligibility state, and monotonic revoked_at_unix values on target logical sessions that were active at termination time.

Sprint42 inserts no self-service session audit event and does not repurpose all_sessions_revoked.

## Dedicated application contract

The dedicated contract is App\Application\Identity\FirstPartyIdentityDisablementSessionTerminationRepository.

It exposes one operation only:

revokeActiveForIdentityDisablement(TenantId $tenantId, PlatformIdentityId $targetIdentityId, int $revokedAtUnix): int

The contract accepts no public handle, authority ID, organization/outlet/device selector, caller-selected tenant, actor-selected session, reactivation flag, reason text, or bulk target.

The infrastructure adapter is App\Infrastructure\Identity\LaravelFirstPartyIdentityDisablementSessionTerminationRepository.

It reuses the canonical database connection, durable persistence enabled boundary, existing runtime class, and existing first-party session-control feature arm.

Runtime is **Local / Test / CI only**.

## Exact termination set

The adapter may update only oneqay_identity_first_party_sessions rows satisfying exact tenant_id, exact identity_id, revoked_at_unix IS NULL, and expires_at_unix >= revokedAtUnix.

The only written column is revoked_at_unix = revokedAtUnix.

Expired authorities and already-revoked authorities remain unchanged. The administrator actor authority remains unchanged. Another identity in the same tenant remains unchanged. A same-text identity in another tenant remains unchanged.

The adapter verifies that no matching active target authority remains before returning success.

## Transaction ordering

The existing PersistenceTransaction remains the only application transaction abstraction.

For a fresh mutation:

1. Sprint41 authorization and target preflight run unchanged;
2. the canonical PolicyAdministrationClock produces a positive Unix timestamp;
3. one PersistenceTransaction::run(...) encloses the fresh mutation;
4. Sprint41 applyFresh(...) performs the deterministic identity eligibility + journal transition;
5. Sprint42 revokes every active exact-target logical session at the same server timestamp;
6. the transaction succeeds only after the zero-active-target-session postcondition is satisfied.

A termination storage/runtime failure rolls back the fresh enabled-target identity transition and Sprint41 journal insert.

No second transaction manager, queue, delayed job, worker, or asynchronous convergence is introduced.

## Outcome and replay composition

Sprint41 outcome vocabulary remains exactly applied and no_change. Sprint42 adds no outcome.

For fresh applied, identity disablement, Sprint41 journal evidence, and active target session revocation converge inside one transaction before success.

For fresh no_change, an already-disabled target keeps outcome no_change while the same invocation removes any stale active target logical sessions.

For exact replay, the same mutation ID + exact fingerprint returns the prior Sprint41 outcome without a duplicate journal row and idempotently enforces zero active target logical sessions before success.

For conflicting replay, the same mutation ID + different fingerprint fails closed before any Sprint42 termination authority is exercised.

## Public contract preservation

Sprint42 adds no public route.

The only public operation remains POST /administration/identities/{identity_id}/authentication-disablement with route name identity.authentication-eligibility.disable.

Request payload remains exactly mutation_id.

The HTTP controller, generic rejection response, middleware chain, route vocabulary, and disable-only semantics remain unchanged.

Sprint42 does not create an administrator-targeted session inventory, revoke-one, revoke-others, revoke-all, or cross-tenant/global logout API.

## Preserved prior foundations

Sprint42 preserves Sprint36 inventory/opaque-handle/revocation/logout semantics; Sprint37 self-scoped revoke-all; Sprint38 idle TTL **7200 seconds** and absolute TTL **43200 seconds**; Sprint39 tenant membership and exact organization/outlet/device revalidation; Sprint40 request-time identity eligibility revalidation; and Sprint41 authorization, protected/self target exclusion, mutation ID binding, replay/conflict behavior, disable-only identity state, journal, route, payload, and safe response semantics.

No credential epoch, factor epoch, password, TOTP, recovery material, membership, role/permission, or organizational-access mutation is introduced.

## Qualification

The Sprint42 source envelope is exactly:

1. .github/workflows/sprint42-first-party-identity-disablement-session-termination-regression.yml
2. apps/web/app/Application/Identity/FirstPartyIdentityDisablementSessionTerminationRepository.php
3. apps/web/app/Application/Identity/FirstPartyIdentityEligibilityAdministrationService.php
4. apps/web/app/Infrastructure/Identity/LaravelFirstPartyIdentityDisablementSessionTerminationRepository.php
5. apps/web/app/Providers/AppServiceProvider.php
6. apps/web/tests/first-party-identity-authentication-eligibility-administration.php
7. apps/web/tests/first-party-identity-disablement-session-termination.php
8. docs/FIRST_PARTY_IDENTITY_DISABLEMENT_SESSION_TERMINATION_FOUNDATION.md

Sorted newline-terminated path SHA-256:

6315890d318c3cdfca549bfacef6cb8d1ca66a4421416b49b4978095a98b6729

Unknown successor shapes remain fail-closed.

Regression qualification proves fresh applied/no-change convergence, exact replay, conflicting replay, target ownership isolation, expired/already-revoked preservation, transaction rollback on termination failure, disabled persistence/runtime/session-control failure, zero session-audit insertion, no schema change, no migration #16, and preserved Sprint40/Sprint41 behavior.

## Lifecycle locks

Technical Preview remains:

**NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED**

Sprint41 migration #15 remains not applied/activated in Technical Preview.

Production remains:

**NO-GO / NOT AUTHORIZED**

Updater remains:

**DISABLED / UNWIRED**

Deployment and release remain:

**NOT AUTHORIZED**

Sprint42 publication does not authorize re-enable/reactivation, Technical Preview activation, Production activation, updater wiring, deployment, or release.
