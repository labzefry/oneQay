# First-Party Pending MFA Identity Eligibility Revalidation Foundation

Author by Lab | zefry

## Status

`SPRINT45 SOURCE IMPLEMENTED / LOCAL-TEST-CI ONLY / NOT ACTIVATED`

## Canonical predecessor

Sprint45 source starts from the qualified schema/source-envelope publication on canonical main `6b669d46690a42ebdcd8a2b7ab6ff2434ecbefeb`, tree `13c7c984494059e2ca1eb6bdf00e2a3a67473ad9`, published through PR #378.

The selected concern remains exactly **First-Party Pending MFA Identity Eligibility Revalidation Foundation**.

## Security invariant

Sprint44 already requires current identity authentication eligibility before the canonical primary-credential path may establish pending MFA state or full authority. Sprint45 closes the later race in which that previously eligible identity becomes ineligible after pending MFA state was created but before enrollment or challenge completion.

When canonical session control is enabled, the shared pending-MFA context boundary revalidates current eligibility for the exact pending tenant+identity before any pending operation may advance authentication state.

The three guarded operations are:

1. privileged TOTP enrollment start;
2. privileged TOTP enrollment confirmation;
3. privileged TOTP challenge completion and any resulting full authority issuance.

## Fail-closed behavior

The canonical `PrivilegedTotpMfaController` composes the existing `FirstPartyIdentityEligibilityVerifier`. The eligibility check occurs after pending-state integrity validation and before server-verified tenant/organizational-context advancement or MFA service execution.

If current eligibility cannot be proven true while session control is enabled, the controller:

- invalidates the exact pending framework session;
- regenerates the CSRF token;
- advances no TOTP enrollment or confirmation state;
- performs no successful challenge transition;
- captures no credential or factor epoch for authority issuance;
- issues no logical session authority;
- establishes no framework full-authentication session;
- returns the existing safe `MFA_VERIFICATION_FAILED` error envelope through the operation's canonical response path.

This destruction of the stale pending framework session is intentional. If Sprint43 reactivation later restores eligibility, that old pending flow remains unusable. A new canonical primary-credential authentication is required to establish any new pending MFA flow.

Reactivation therefore remains eligibility-only. It creates no pending session, full framework session, logical authority, public session handle, or restored authority.

## Preservation boundaries

Sprint45 does not change routes, service-provider bindings, configuration shape, session-key definitions, repository contracts, authorization policy, credential/factor persistence contracts, session-authority persistence contracts, or schema.

It does not clear historical logical-session revocation evidence, reuse authority IDs or public session handles, restore terminated/expired/idle/epoch-invalid/organizationally-invalid sessions, or add restore, resume, login-after-reactivate, automatic-login, self-service reactivation-login, protected-control bypass, break-glass, bulk, cross-tenant, timed, or caller-selected tenant/identity/role/permission/session authority.

When canonical session control is disabled, the pre-existing pending MFA behavior remains unchanged by the new eligibility gate.

## Schema boundary

Sprint45 is **NO_SCHEMA_CHANGE**.

Canonical source migrations remain exactly **#1–#15**. Migration #16 is **NOT SELECTED** and does not exist.

## Exact source envelope

Sprint45 source changes exactly these four paths:

```text
.github/workflows/sprint45-pending-mfa-identity-eligibility-revalidation-regression.yml
apps/web/app/Delivery/Http/Identity/PrivilegedTotpMfaController.php
apps/web/tests/first-party-pending-mfa-identity-eligibility-revalidation.php
docs/FIRST_PARTY_PENDING_MFA_IDENTITY_ELIGIBILITY_REVALIDATION_FOUNDATION.md
```

Sorted newline-terminated SHA-256:

`5dfaecf9be5c584b431606a7253515ab623ad9a11b4ff74062e794a1f40917c7`

No other source path is part of Sprint45 implementation authority.

## Regression proof

The dedicated Sprint45 regression proves the exact controller composition and shared three-operation guard, fail-closed invalidation of pending enrollment and challenge state, CSRF regeneration, no creation of full authority evidence, and the no-resume property after later eligibility restoration. It also locks canonical migrations to #1–#15 with migration #16 absent.

The Sprint45 workflow additionally preserves recent canonical identity/session semantics and the full application regression under its canonical legacy/default feature environment.

## Lifecycle locks

Technical Preview remains **NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED**.

Sprint41 migration #15 remains **NOT ACTIVATED / NOT APPLIED in Technical Preview**.

Sprint42, Sprint43, Sprint44, and Sprint45 source remain **NOT ACTIVATED in Technical Preview**.

Production remains **NO-GO / NOT AUTHORIZED**.

Updater remains **DISABLED / UNWIRED**.

Deployment, release, migration execution, and rollback remain **NOT AUTHORIZED**.

## Result

Sprint45 source closes the pending-MFA eligibility race without widening authority: only a currently eligible exact tenant+identity may advance a canonical pending privileged MFA flow, and an ineligibility encounter burns that pending flow so later reactivation still requires fresh primary authentication.
