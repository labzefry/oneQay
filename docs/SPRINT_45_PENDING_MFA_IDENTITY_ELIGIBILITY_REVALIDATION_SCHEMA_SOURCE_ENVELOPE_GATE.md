# Sprint45 Pending MFA Identity Eligibility Revalidation Schema / Source Envelope Gate

Author by Lab | zefry

## Status

`SCHEMA / SOURCE ENVELOPE GATE ONLY / SOURCE NOT YET AUTHORIZED / NO_SCHEMA_CHANGE`

## Canonical predecessor

This bounded gate starts from canonical main `d6d7b1175af94118c9748188f89474077a5a4303`, tree `fb3999fc9a057979e8ef5fab9800528cd813504e`, after the qualified Sprint45 entry gate and its exact schema-gate compatibility predecessor.

The selected concern remains exactly **First-Party Pending MFA Identity Eligibility Revalidation Foundation**.

## Schema classification

Sprint45 remains **NO_SCHEMA_CHANGE**.

The required security behavior can be composed from existing canonical primitives:

- `FirstPartyIdentityEligibilityVerifier` already provides fail-closed current tenant+identity eligibility verification;
- `PrivilegedTotpMfaController` already owns the canonical pending privileged MFA enrollment / confirmation / challenge transition boundary;
- `FirstPartySessionKeys::pending()` already defines the pending-state key family;
- Laravel session invalidation already exists in the controller and can be used to destroy stale pending authentication state without adding persistence or schema;
- the canonical session-authority service remains the only logical authority issuance boundary after successful challenge.

No new table, column, index, foreign key, repository contract, persisted lifecycle state, migration, route, configuration flag, or caller-supplied authority field is required.

Canonical source migrations therefore remain exactly **#1–#15**. Migration #16 remains **NOT SELECTED**.

## Required implementation semantics

When canonical session control is enabled, the pending privileged MFA boundary must verify current authentication eligibility for the exact pending tenant+identity before any pending MFA operation may advance authentication state.

The implementation must cover all three canonical pending-MFA state-advancing operations owned by `PrivilegedTotpMfaController`:

1. enrollment start;
2. enrollment confirmation;
3. challenge completion / full authority issuance.

Current ineligibility must fail closed before TOTP enrollment material is issued, before enrollment confirmation mutates factor state, and before challenge verification may lead to credential/factor epoch capture, logical authority issuance, or framework full-session establishment.

On an ineligibility result, the exact pending framework session must be invalidated and its CSRF token regenerated before returning the existing safe MFA failure envelope. This prevents a pending state created before disablement from becoming resumable after a later reactivation. Reactivation remains eligibility-only; a new pending MFA flow after reactivation requires canonical fresh primary-credential authentication.

The implementation must not clear historical logical-session revocation evidence, restore old session or authority identifiers, weaken organizational-context checks, bypass current TOTP factor semantics, or create any generic restore/resume/login-after-reactivate path.

## Frozen source envelope

The eventual Sprint45 source implementation is bounded to exactly these four paths:

```text
.github/workflows/sprint45-pending-mfa-identity-eligibility-revalidation-regression.yml
apps/web/app/Delivery/Http/Identity/PrivilegedTotpMfaController.php
apps/web/tests/first-party-pending-mfa-identity-eligibility-revalidation.php
docs/FIRST_PARTY_PENDING_MFA_IDENTITY_ELIGIBILITY_REVALIDATION_FOUNDATION.md
```

Sorted newline-terminated SHA-256:

`5dfaecf9be5c584b431606a7253515ab623ad9a11b4ff74062e794a1f40917c7`

No source path outside this exact envelope is authorized by this gate.

In particular, this gate does not authorize mutation of `FirstPartySessionKeys`, the eligibility verifier interface or implementation, service-provider bindings, routes, configuration, migrations, credential/factor repositories, organizational access primitives, session-authority services, recovery services, or previous Sprint workflow/source files except through a separately qualified compatibility predecessor when repository-native CI requires exact successor recognition.

## Required regression proof

The bounded source regression must prove at minimum:

- exact four-path source envelope and fingerprint;
- `NO_SCHEMA_CHANGE`, migrations #1–#15 preserved, migration #16 absent;
- all three pending MFA state-advancing controller operations are guarded by current eligibility when session control is enabled;
- eligible pending enrollment/challenge behavior remains canonical;
- identity disablement after pending-state creation makes subsequent enrollment start, enrollment confirmation, and challenge completion fail closed;
- ineligible encounter invalidates the pending framework session so later eligibility reactivation cannot resume the old pending flow;
- reactivation itself creates no pending/full framework session, logical authority, or public session handle;
- a new canonical fresh primary-credential authentication after valid reactivation may establish a new pending MFA flow and, after valid current TOTP proof, issue only new authority;
- no historical revoked/terminated authority is resurrected;
- cross-tenant pending identity evidence, invalid organizational access, stale credential/factor epoch behavior, and unrelated active sessions remain protected;
- Sprint41 disablement, Sprint42 termination, Sprint43 reactivation, and Sprint44 fresh-authentication re-entry semantics remain preserved;
- full application regression remains green under its canonical legacy/default feature environment;
- lifecycle locks remain unchanged.

## Explicit non-goals

This gate does **not** authorize:

- schema mutation or migration #16;
- new routes or externally selectable operations;
- generic authentication, reactivation, restore, resume, or session resurrection APIs;
- self-service or protected-control reactivation bypass;
- caller-selected tenant, identity, role, permission, grant, factor epoch, credential epoch, authority ID, public session handle, or eligibility state;
- bulk, cross-tenant, automatic, timed, or background reactivation/login;
- Technical Preview activation;
- Production activation;
- updater wiring;
- deployment, release, migration execution, or rollback.

## Gate envelope

This gate itself changes exactly one path:

```text
docs/SPRINT_45_PENDING_MFA_IDENTITY_ELIGIBILITY_REVALIDATION_SCHEMA_SOURCE_ENVELOPE_GATE.md
```

Sorted newline-terminated SHA-256:

`94430119e646c8ab2fca48233276b8155c8f8769965532b3c739479510f10017`

## Lifecycle locks

Technical Preview remains **NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED**.

Sprint41 migration #15 remains **NOT ACTIVATED / NOT APPLIED in Technical Preview**.

Sprint42, Sprint43, Sprint44, and Sprint45 source remain **NOT ACTIVATED in Technical Preview**.

Production remains **NO-GO / NOT AUTHORIZED**.

Updater remains **DISABLED / UNWIRED**.

Deployment, release, migration execution, and rollback remain **NOT AUTHORIZED**.

## Result

This gate freezes a four-path, no-schema-change Sprint45 source envelope and creates no source or runtime authority by itself.
