# Post-Sprint 32 Canonical State

## Status

Sprint 32 — Authentication Recovery / JRN-003 Recovery Proof Foundation is **COMPLETE / IMPLEMENTED / PUBLISHED** within its exact bounded authority.

This document is a factual post-publication canonical-state record. It creates no new implementation, schema, Preview, Production, updater, deployment, release, password-reset, factor-lifecycle, or post-Sprint32 authority.

## Exact publication lineage

- source PR: **#208** — `feat(sprint32): add authentication recovery proof foundation`;
- authorized exact source head: `2a9ef03384f1a5ba5f095584a9bbb1be098ee1c5`;
- exact source tree: `89f8dcea209ea912ba2539f3c6224a3a0519c8f7`;
- source parent / canonical base: `7f2cc64e5a85158fb24cf03b61d2b36ead73190a`;
- source relation before publication: `ahead 1 / behind 0`, exactly one commit;
- source changed-file envelope: exactly **32 paths**;
- source additions/deletions: `+1901 / -7`;
- sorted-path SHA-256: `db230ab3b77fff67f0bd12d7d7b615146d9d9df9a0af12014214e1862e9f6867`;
- squash publication: `914f93f8636bbd0901c61d8a8f14ad69c2c8fbfe`;
- publication tree: `89f8dcea209ea912ba2539f3c6224a3a0519c8f7`;
- publication parent: `7f2cc64e5a85158fb24cf03b61d2b36ead73190a`;
- publication signature: **verified / valid**.

The Product Owner exact-head merge authority for PR #208 was recorded, the repository-native `product-owner-merge-authority` status became **SUCCESS**, the final race-safe checks remained clean, and the squash merge used `expected_head_sha=2a9ef03384f1a5ba5f095584a9bbb1be098ee1c5`. That authority is **CONSUMED** and cannot be reused.

## Preservation compatibility publication

Before the final Sprint32 source publication, preservation compatibility was corrected through PR #209 — `docs(sprint32): correct preservation compatibility source envelope`.

- authorized exact correction head: `5a548ba477a2b4b5846f3f44e70aeb5c90743d8c`;
- correction publication: `7f2cc64e5a85158fb24cf03b61d2b36ead73190a`;
- correction tree: `588171e03fd76b9cdc585e7ecee93c682e7bcfb7`;
- correction parent: `1d231d6ba9c48cc0dc4391d6111161653eb92c54`;
- signature: **verified / valid**.

PR #209 froze Sprint32 successor recognition to the exact **32-path count AND exact sorted-path fingerprint** above. Branch names, PR numbers, wildcards, prefixes, count-only checks, and commit-message heuristics are not valid recognition mechanisms.

## Exact-head qualification

The final PR #208 exact head completed **24/24 pull-request workflow runs with SUCCESS**. The preserved qualification surfaces include:

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
- M7.1 Application Regression;
- M7.2 Tenant Isolation Regression;
- M7.3 Identity Organizational Context Regression;
- M7.4A Technical Preview Interaction Regression;
- M7.5 Preview Database Qualification Regression;
- M7.5 Technical Preview Release Artifact;
- M7.6 Preview Deployment Recovery Rehearsal Regression;
- PHP Foundation Regression;
- Governance Required Checks;
- Backend Updater Control Plane Regression;
- Read-Only Update Deployment UI Regression;
- Privileged Update Security Regression.

## Canonical identity and security progression

Sprint 21 through Sprint 32 governed identity/control foundations are now published within their bounded authorities:

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
12. Sprint32 — Authentication Recovery / JRN-003 Recovery Proof Foundation.

DEC-006 continues to govern first-party identity with server-side browser sessions. The privileged baseline remains TOTP. Sprint31 step-up freshness remains exactly **300 seconds** for the published `policy_administration` scope.

## Sprint32 recovery proof boundary

The Sprint32 feature arm remains source-default disabled:

`ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=false`

Execution remains bounded to **Local / Test / CI only**.

Each successful recovery-code rotation produces exactly **8 codes**. The exact code shape is:

`rq1.<22-char selector>.<43-char secret>`

The selector uses 128-bit entropy and the secret uses 256-bit entropy generated through `random_bytes`.

Plaintext recovery secrets/codes are not durably persisted. Durable verification uses SHA-256 digest material and `hash_equals`; recovery audit evidence is secret-free.

A successful rotation atomically revokes previous unused codes, writes the new code records, and writes secret-free audit evidence. A recovery proof locks the selected code, revalidates eligible non-privileged identity state, atomically consumes exactly one code, and writes secret-free audit evidence. Same-code replay and concurrency are fail-closed with at most one winner.

A successful recovery proof **does not establish a normal/full authenticated session**. It establishes only the restricted `password_reset_required` session for exactly **600 seconds**. The five canonical Sprint27 full-session context keys remain unchanged and are not populated by recovery proof. The recovery path does not read or decrypt the TOTP secret.

## Canonical migration state

Canonical source migrations are now exactly **#1 through #10**.

Migrations #1 through #9 remain immutable. Migration #10 is the only Sprint32 migration:

`apps/web/database/migrations/0000_00_00_000010_create_identity_recovery_codes.php`

It creates only:

- `oneqay_identity_recovery_codes`;
- `oneqay_identity_recovery_audit`.

No migration #11 is authorized by Sprint32 or by this reconciliation.

## Explicit exclusions that remain in force

Sprint32 and this post-publication reconciliation do **not** implement or authorize:

- password reset, password change, or password overwrite;
- automatic login or full-session establishment from recovery proof;
- MFA/TOTP recovery or TOTP secret recovery;
- factor replacement or deletion;
- protected-control recovery;
- support/admin bypass;
- email or SMS recovery;
- passkeys;
- federation;
- API-token authentication;
- Technical Preview authentication or schema activation;
- Production authentication or schema activation;
- updater activation;
- deployment;
- release authority.

Sprint32 publishes the **JRN-003 recovery-proof foundation** only. This record does not claim end-to-end password recovery completion because password reset/change/overwrite remain explicitly excluded.

## Frozen environment and lifecycle boundaries

- Technical Preview: **`NO_SCHEMA_CHANGE`**;
- Production: **`NO-GO / NOT AUTHORIZED`**;
- updater: **`DISABLED / UNWIRED`**;
- durable persistence source default: **`ONEQAY_PERSISTENCE_ENABLED=false`**.

No Preview/Production recovery, schema mutation, updater activation, deployment, or release authority is implied by the Sprint32 source publication.

## Post-Sprint32 next-work state

This canonical-state record deliberately selects **no new post-Sprint32 implementation concern** and grants no Sprint33 or migration #11 authority.

Any subsequent source work requires a separately bounded Product Owner entry gate and, for any later PR merge, a new exact PR/head Product Owner merge authorization. PR #208 and PR #209 authorities are historical and consumed.

## Reconciliation non-scope

The post-Sprint32 canonical program-state reconciliation is documentation-only. It must not change application source, workflow YAML, dependency manifests/lockfiles, migration files, schema, routes, runtime behavior, Preview behavior, Production behavior, updater behavior, deployment, or release state.

Attribution: **Lab | zefry**
