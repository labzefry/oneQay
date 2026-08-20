# Post-Sprint 33 Canonical State

## Status

Sprint 33 — Recovery-Bound Password Reset Completion Foundation is **COMPLETE / IMPLEMENTED / PUBLISHED** within its exact bounded authority.

This document is a factual post-publication canonical-state record. It creates no new implementation, schema, Preview, Production, updater, deployment, release, authenticated-password-change, MFA/TOTP-recovery, factor-lifecycle, or post-Sprint33 authority.

## Exact publication lineage

- Sprint 33 entry-gate PR: **#211** — `docs(sprint33): establish recovery-bound password reset completion entry gate`;
- entry-gate publication: `42d6105749620edb307fd12e7d116798f71cdd9e`;
- Sprint 33 source-envelope gate PR: **#212** — `docs(sprint33): freeze recovery-bound password reset source envelope`;
- source-envelope publication / canonical source base: `c89baa55318dca230cd0ef792df80e3d54b8165d`;
- source-envelope publication tree: `64ca0cffc6067ccd03632b15af1786d21d00e463`;
- source PR: **#213** — `Sprint 33: recovery-bound password reset completion`;
- authorized exact source head: `a7a50644cbe67e6f08138c79cf50a9350e8e220d`;
- exact source tree: `492e723b6343dab518b43645883976ad20f0054c`;
- source parent / canonical base: `c89baa55318dca230cd0ef792df80e3d54b8165d`;
- source relation before publication: `ahead 1 / behind 0`, exactly one commit;
- source changed-file envelope: exactly **39 paths**;
- source additions/deletions: `+2065 / -45`;
- sorted-path SHA-256: `04a1177c12712183a7dda4ae81be1356c0e41294533336c9f999d376c224712a`;
- squash publication: `9eba56d92b4b714225d677990ffed93687b0b2cb`;
- publication tree: `492e723b6343dab518b43645883976ad20f0054c`;
- publication parent: `c89baa55318dca230cd0ef792df80e3d54b8165d`;
- publication signature: **verified / valid**.

The Product Owner exact-head merge authority for PR #213 was recorded in the PR conversation for exact head `a7a50644cbe67e6f08138c79cf50a9350e8e220d`; the repository-native `product-owner-merge-authority` status became **SUCCESS** before publication. GitHub repository policy rejected merge-commit and rebase attempts, so the one-commit qualified PR was published through squash merge while preserving the exact qualified source tree. That authority is **CONSUMED** and cannot be reused.

## Exact-head qualification

The final PR #213 exact head completed **24/24 pull-request workflow runs with SUCCESS**. The qualified surfaces included:

- Sprint 21 Role Permission Policy Regression;
- Sprint 22 Policy Administration Regression;
- Sprint 23 Initial Tenant Administrator Provisioning Regression;
- Sprint 24 Protected Control Administrator Lifecycle Regression;
- Sprint 25 Policy Administration Delivery Regression;
- Sprint 26 Identity Credential Verification Regression;
- Sprint 27 First-Party Session Establishment Regression;
- Sprint 28 Initial Password Enrollment Regression;
- Sprint 29 First Control Principal Credential Bootstrap Regression;
- Sprint 30 Privileged TOTP MFA Regression;
- Sprint 31 Privileged Reauthentication Step-Up Regression;
- Sprint 32 Authentication Recovery Proof Regression;
- Sprint 33 Recovery-Bound Password Reset Regression;
- M7.1 Application Regression;
- M7.2 Tenant Isolation Regression;
- M7.3 Identity Organizational Context Regression;
- M7.4A Technical Preview Interaction Regression;
- M7.5 Preview Database Qualification Regression;
- M7.5 Technical Preview Release Artifact;
- PHP Foundation Regression;
- Governance Required Checks;
- Backend Updater Control Plane Regression;
- Read-Only Update Deployment UI Regression;
- Privileged Update Security Regression.

The dedicated Sprint 33 regression proved the exact 39-path envelope and fingerprint, canonical migration preservation, framework-independent Application boundaries, server-bound recovery proof and credential epoch behavior, update-only transactional reset completion, executable password-reset behavior, Sprint32 preservation, historical executable regressions, runtime separation, and tracked-source cleanliness.

## Canonical identity and security progression

Sprint 21 through Sprint 33 governed identity/control foundations are now published within their bounded authorities:

1. Sprint21 — Role / Permission / Policy;
2. Sprint22 — Policy Administration;
3. Sprint23 — Initial Tenant Administrator Provisioning;
4. Sprint24 — Protected Control Administrator Lifecycle;
5. Sprint25 — Policy Administration Delivery;
6. Sprint26 — Identity Credential Verification;
7. Sprint27 — First-Party Login / Server-Side Session Establishment;
8. Sprint28 — Initial Password Enrollment;
9. Sprint29 — First-Control Principal Credential Bootstrap;
10. Sprint30 — Privileged TOTP MFA Foundation;
11. Sprint31 — Privileged Reauthentication / Step-Up Session Freshness;
12. Sprint32 — Authentication Recovery / JRN-003 Recovery Proof Foundation;
13. Sprint33 — Recovery-Bound Password Reset Completion Foundation.

DEC-006 continues to govern first-party identity with server-side browser sessions. The privileged baseline remains TOTP. Sprint31 step-up freshness remains exactly **300 seconds** for the published `policy_administration` scope.

## Sprint33 recovery completion boundary

The existing recovery feature arm remains source-default disabled:

`ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=false`

Execution remains bounded to **Local / Test / CI only**.

Sprint32 rotation and proof behavior remains intact. Successful recovery proof establishes only the restricted `password_reset_required` session for exactly **600 seconds**. Sprint33 additionally binds the server-owned consumed recovery row through non-secret `code_id` evidence in `oneqay.auth.recovery.code_id`; caller-selected tenant, identity, code id, proof time, or expiry remain forbidden.

The exact completion endpoint is:

`POST /auth/recovery/password-reset`

It accepts exactly one business field, `password`, after normal CSRF handling. Replacement passwords are opaque byte input with inclusive length **12 through 4096 bytes**. Sprint33 performs no trim or normalization and hashes the replacement with PHP `PASSWORD_DEFAULT`.

Credential mutation is update-only against the exact existing `(tenant_id, identity_id)` credential row. A missing credential fails closed; Sprint33 does not insert, upsert, recreate, delete, or truncate credentials as a fallback.

Completion is one atomic durable transaction. It re-locks and revalidates the consumed recovery proof, requires matching secret-free `proof_succeeded` evidence, rejects prior `password_reset_completed` evidence for the same proof, revalidates identity and credential eligibility, updates the exact password hash, revokes remaining unused recovery codes for the same tenant and identity, and appends exactly one secret-free `password_reset_completed` audit event.

Same-proof replay/concurrency remains fail-closed with at most one winner.

Protected-control principals and identities with confirmed privileged TOTP remain ineligible for this recovery completion path. TOTP secret material is not read, decrypted, exposed, replaced, deleted, or mutated.

On successful reset the restricted recovery session is invalidated and CSRF is regenerated. Sprint33 does **not** establish normal/full authentication, privileged MFA evidence, privileged step-up evidence, or credential-epoch evidence. A fresh normal login with the replacement password is mandatory.

## Credential epoch and pre-reset session invalidation

Sprint33 publishes a no-schema credential epoch derived from the count of durable `password_reset_completed` recovery-audit rows for the exact tenant and identity.

- initial durable epoch is `0`;
- fresh normal login captures the current durable epoch in separate session evidence `oneqay.auth.credential_epoch`;
- the credential-epoch key remains outside the five canonical Sprint27 full-context session keys;
- a legacy authenticated session with no epoch is accepted only while durable epoch is also `0`;
- stale, malformed, negative, or invented future epoch evidence fails closed;
- each later successful recovery-bound reset advances durable epoch through another completion audit row;
- an older authenticated session therefore cannot continue to exercise recovery rotation after the target credential has been reset;
- credential-epoch lookup is independent of the recovery feature arm, so later feature disablement cannot make stale authenticated sessions authoritative again.

No credential-version column, session-version table, new cache authority, migration #11, or new dependency is introduced.

## Canonical migration state

Canonical source migrations remain exactly **#1 through #10**.

Migrations #1 through #10 are unchanged by Sprint33. Migration #10 remains the Sprint32 recovery-state migration and creates only:

- `oneqay_identity_recovery_codes`;
- `oneqay_identity_recovery_audit`.

No migration #11 is authorized by Sprint33 or by this reconciliation.

## Recovery status after Sprint33

Within the published **Local/Test/CI-only**, feature-disabled-by-default boundary, Sprint32 + Sprint33 now provide a bounded end-to-end recovery sequence for eligible non-protected identities without confirmed privileged TOTP:

1. an already authenticated eligible identity rotates its recovery codes;
2. one recovery code can later be proved exactly once;
3. successful proof establishes only the 600-second restricted `password_reset_required` session;
4. Sprint33 completes an update-only password reset bound to that exact consumed proof;
5. completion invalidates the restricted session and advances durable credential epoch;
6. a fresh normal login with the replacement password is required.

This is not Production recovery activation and is not a support/admin recovery bypass.

## Explicit exclusions that remain in force

Sprint33 and this post-publication reconciliation do **not** implement or authorize:

- authenticated in-session password change outside recovery;
- administrative password overwrite;
- MFA/TOTP recovery or TOTP secret recovery;
- TOTP factor replacement or deletion;
- protected-control recovery bypass;
- support/admin bypass;
- email or SMS recovery delivery;
- passkeys or WebAuthn implementation;
- federation;
- API-token authentication;
- Technical Preview authentication or schema activation;
- Production authentication or schema activation;
- updater activation;
- deployment;
- release authority.

## Frozen environment and lifecycle boundaries

- Technical Preview: **`NO_SCHEMA_CHANGE`**;
- Production: **`NO-GO / NOT AUTHORIZED`**;
- updater: **`DISABLED / UNWIRED`**;
- durable persistence source default: **`ONEQAY_PERSISTENCE_ENABLED=false`**;
- recovery source default: **`ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=false`**.

No Preview/Production recovery, schema mutation, updater activation, deployment, or release authority is implied by the Sprint33 source publication.

## Post-Sprint33 next-work state

This canonical-state record deliberately selects **no new post-Sprint33 implementation concern** and grants no Sprint34 or migration #11 authority.

Any subsequent source work requires a separately bounded Product Owner entry gate. Any later Ready or Merge transition requires new exact PR/head Product Owner authority. PR #211, PR #212, and PR #213 authorities are historical and consumed.

## Reconciliation non-scope

The post-Sprint33 canonical program-state reconciliation is documentation-only. It must not change application source, workflow YAML, dependency manifests/lockfiles, migration files, schema, routes, runtime behavior, Preview behavior, Production behavior, updater behavior, deployment, or release state.

Attribution: **Lab | zefry**
