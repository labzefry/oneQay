# Sprint44 First-Party Identity Reactivation Fresh Authentication Re-entry — Schema / Source Envelope Gate

Author by Lab | zefry

## Status

`SCHEMA / SOURCE ENVELOPE GATE — SOURCE NOT YET IMPLEMENTED`

This document is the bounded successor to the canonical Sprint44 Entry Gate. It freezes the only source envelope that may be considered for Sprint44 implementation. It does not activate Technical Preview, Production, deployment, release, updater, rollback, or any migration.

## Selected concern

Sprint44 proves that Sprint43 reactivation restores only eligibility to attempt normal first-party authentication. Reactivation itself is not login and cannot resurrect any prior framework session, logical session authority, public handle, credential/factor state, membership, organizational access, outlet/device authority, or other authorization state.

The required cycle is:

1. an ordinary identity has a valid first-party session;
2. Sprint41 disablement changes authentication eligibility from `true` to `false`;
3. Sprint42 terminates active logical sessions for that tenant+identity;
4. Sprint43 reactivation changes eligibility from `false` to `true` without creating a session;
5. every old authority remains revoked or otherwise invalid;
6. the existing canonical login flow must perform fresh credential and context verification;
7. successful fresh authentication issues new session authority;
8. later disablement terminates that newly issued active authority again.

## Architecture inspection decision

Canonical inspection shows that the existing `FirstPartySessionController::login()` already composes credential verification, server-verified tenant context, organizational/outlet/device entry, MFA when enabled, current credential epoch capture, logical authority issuance, and framework-session regeneration. `FirstPartySessionAuthorityService::issue()` generates a fresh random authority ID and public handle and validates current credential/factor epochs. `LaravelFirstPartySessionAuthorityRepository::issue()` inserts a new session record and `session_issued` audit event without clearing or mutating historical `revoked_at_unix` values.

Therefore Sprint44 MUST compose the existing login/session-establishment flow. No special reactivation-login endpoint, restore endpoint, resume endpoint, caller-selected authority, or alternate session establishment path is authorized.

## Schema decision

`NO_SCHEMA_CHANGE`

Migration #16: `NOT SELECTED`.

No new persistent evidence is required. Existing identity eligibility state, first-party session authority table, session audit table, credential epoch state, factor epoch state, tenant membership, and organizational relationship/access persistence are sufficient to prove the bounded concern.

Sprint44 source implementation MUST NOT modify `apps/web/database/migrations` and MUST preserve exactly migrations #1 through #15.

## Source-change decision

A bounded source change IS REQUIRED because the canonical login flow currently verifies password before the Sprint40 identity-eligibility defense is consulted. Sprint44 must make fresh authentication itself fail closed while the identity remains disabled; relying only on request-time session middleware after authority issuance would permit an ineligible identity to create a fresh logical authority before subsequent denial.

The minimal implementation strategy is to compose the existing `FirstPartyIdentityEligibilityVerifier` into the canonical first-party login path and require eligibility before any logical authority is issued. No new route is required.

## Exact authentication composition

The canonical `POST /session` login route remains the only Sprint44 fresh-authentication entry point.

The implementation must preserve the closed login payload:

- `tenant_id`
- `identity_id`
- `password`
- `organization_id`
- optional `outlet_id`
- optional `device_id`

No `reactivate`, `restore`, `resume`, `operation`, `enabled`, authority ID, public handle, role, permission, membership, or session selector may be accepted from the caller.

Before logical authority issuance, the login flow must prove all applicable current state using existing components:

- password credential verification;
- `first_party_authentication_enabled = true` through the existing fail-closed eligibility verifier;
- server-verified tenant context;
- current tenant membership and organizational relationship/access through canonical organizational-context entry;
- current outlet/device relationship when supplied;
- current MFA state when enabled;
- current credential epoch;
- current factor epoch when MFA requires it.

Unknown, missing, malformed, disabled, stale, cross-tenant, or storage-error state fails closed with the existing safe authentication failure semantics.

## Fresh session issuance semantics

A successful fresh authentication after Sprint43 reactivation MUST use `FirstPartySessionAuthorityService::issue()` and the existing repository issuance transaction.

Each new logical session must receive:

- a newly generated 32-hex authority ID;
- a newly generated 43-character URL-safe public handle;
- current tenant+identity ownership;
- current organization/outlet/device context;
- current credential epoch;
- current factor epoch when applicable;
- fresh issue/last-seen/expiry timestamps;
- `revoked_at_unix = null` only on the newly inserted record;
- the existing `session_issued` audit evidence.

The framework session must be invalidated/regenerated by the canonical establishment flow and store only the newly issued authority.

## Non-resurrection proof

Sprint44 tests must capture the pre-disable authority ID and public handle, then prove after disablement and reactivation that:

- the historical session row still has non-null `revoked_at_unix`;
- reactivation does not mutate that row back to active;
- the old authority cannot pass active-current validation;
- the old public handle is not reused;
- expired authority remains expired;
- idle-invalid authority remains invalid;
- credential-epoch-invalid authority remains invalid;
- factor-epoch-invalid authority remains invalid;
- membership/org/outlet/device-invalid authority remains invalid;
- successful fresh login inserts a different authority ID and different public handle;
- no operation clears historical `revoked_at_unix`.

Random issuance collision is already constrained by repository uniqueness and fail-closed storage behavior; tests must assert inequality against the captured historical identifiers.

## Credential and factor epochs

Reactivation MUST NOT mutate credentials, credential epochs, TOTP secrets, recovery state, factor epochs, or MFA enrollment/confirmation state.

Fresh login uses the current password credential. Authority issuance must capture the current credential epoch. When MFA is applicable, full authority may only be issued through the existing MFA completion flow with the current factor epoch. Any stale epoch must fail closed and must never be repaired by reactivation.

## Membership and organizational access

Reactivation restores no tenant membership, organization membership, durable grant, role, permission, outlet relationship, or device relationship. Fresh login must re-enter canonical organizational context from current server-side state. Caller-provided tenant/organization/outlet/device identifiers are selectors only and never authority.

Cross-tenant re-entry is prohibited. Missing or invalid membership/relationship/access fails authentication without issuing authority.

## Protected-control and authorization boundary

Sprint44 applies only to ordinary first-party identities already governed by Sprint41–Sprint43. It does not widen protected-control eligibility or create self-service/admin/support authentication bypass.

The Sprint43 administration reactivation route remains separately authorized by server-derived tenant-scoped policy administration plus `AdministrationPermission::MANAGE`. Sprint44 adds no administrative authority and no new public administration route.

## Failure semantics

All ambiguity fails closed. Authentication failure must continue to use the safe `AUTHENTICATION_FAILED` envelope without disclosing whether password, eligibility, membership, organization, outlet/device, MFA, epoch, or persistence state caused denial.

No authority row may be issued before all pre-issuance eligibility/context requirements for that authentication stage are satisfied.

## Transaction semantics

Sprint43 reactivation and Sprint44 login are separate requests and separate transactions. Reactivation commits only the eligibility transition/journal semantics already frozen by Sprint43. Fresh login uses existing persistence transactions for authority issuance and audit evidence. There is no transaction spanning reactivation and login and no compensating session restoration.

If authority issuance/audit persistence fails, authentication fails closed and no successful framework-session authority may be established.

## Exact Sprint44 source implementation envelope

Only the following sorted, newline-terminated paths are authorized for the subsequent Sprint44 source implementation PR:

```text
.github/workflows/sprint44-first-party-identity-reactivation-fresh-authentication-reentry-regression.yml
apps/web/app/Delivery/Http/Identity/FirstPartySessionController.php
apps/web/tests/first-party-identity-reactivation-fresh-authentication-reentry.php
docs/FIRST_PARTY_IDENTITY_REACTIVATION_FRESH_AUTHENTICATION_REENTRY_FOUNDATION.md
```

No route file, provider binding, repository contract, repository adapter, migration, configuration, dependency lockfile, Sprint41/42/43 source, or protected-control source mutation is authorized by this gate.

The source-envelope workflow must prove that the existing `FirstPartyIdentityEligibilityVerifier` dependency is composed into `FirstPartySessionController` through the already canonical provider/container binding; no provider mutation is selected.

## Source implementation path fingerprint

The exact SHA-256 is computed from the four paths above, lexicographically sorted, each followed by one LF byte and with no additional lines. The implementation workflow MUST freeze and verify that computed value before source qualification. The implementation PR must not widen the path set.

## Required regression proof

The dedicated Sprint44 regression must prove at minimum:

1. disabled identity cannot fresh-login and creates zero new logical authority;
2. Sprint43 reactivation alone creates zero logical authority and zero framework authority;
3. old Sprint42-revoked authority/public handle remain historical and unusable;
4. fresh login after reactivation succeeds only with current credential and current context;
5. new authority/public handle differ from historical values;
6. expired and idle-invalid historical authorities remain invalid;
7. stale credential epoch remains invalid;
8. stale factor epoch remains invalid when MFA is enabled;
9. invalid tenant membership, organization, outlet, or device state prevents new authority issuance;
10. cross-tenant authentication cannot borrow reactivation state;
11. disable -> reactivate -> fresh login -> disable converges to zero active target sessions;
12. the second disablement does not alter unrelated tenant/identity sessions;
13. no historical `revoked_at_unix` is cleared;
14. no generic toggle, restore, resume, automatic login, or special reactivation-login route exists.

The workflow must also preserve materially applicable Sprint27, Sprint30/31, Sprint36, Sprint38, Sprint39, Sprint40, Sprint41, Sprint42, and Sprint43 regressions.

## Historical workflow compatibility

Before publishing the source implementation, historical fail-closed workflows must be inspected against the exact four-path source envelope. If any materially triggered historical workflow rejects that exact successor shape, a minimal exact compatibility predecessor is required first. Compatibility must recognize only the exact successor fingerprint; wildcard path ignores, generic successor toggles, arbitrary fingerprint widening, and bypasses remain prohibited.

This schema/source gate itself changes exactly one documentation path:

```text
docs/SPRINT_44_FIRST_PARTY_IDENTITY_REACTIVATION_FRESH_AUTHENTICATION_REENTRY_SCHEMA_SOURCE_ENVELOPE_GATE.md
```

Sorted newline-terminated gate path fingerprint:

`5005a4ecc48fb122f024e09b3e73ab0ddada7a9d1e6aada6d2c53aae763e5975`

## Local / Test / CI boundary

Sprint44 remains `Local / Test / CI only`. The implementation may not widen runtime classes or activate any preview/production path.

## Lifecycle locks

Technical Preview: `NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED`.

Sprint41 migration #15 is NOT activated/applied in Technical Preview.

Sprint42 source is NOT activated in Technical Preview.

Sprint43 source is NOT activated in Technical Preview.

Sprint44 source is NOT activated in Technical Preview.

Production: `NO-GO / NOT AUTHORIZED`.

Updater: `DISABLED / UNWIRED`.

Deployment and release remain `NOT AUTHORIZED`.

Rollback remains `NOT AUTHORIZED`.

Canonical migrations remain #1–#15. Migration #16 remains `NOT SELECTED`.

## Gate result

Schema decision: `NO_SCHEMA_CHANGE`.

Migration #16: `NOT SELECTED`.

Source implementation: `SELECTED BUT NOT IMPLEMENTED`.

Selected implementation strategy: minimal composition of existing identity-eligibility verification into canonical fresh login plus dedicated integration/regression proof. No new route, no new persistence schema, no new session restoration semantics, and no lifecycle activation.
