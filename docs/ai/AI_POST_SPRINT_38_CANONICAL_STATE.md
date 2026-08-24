# AI Post-Sprint 38 Canonical State

## Status

**CANONICAL / POST-SPRINT38 / RECONCILIATION BASELINE**

Date: **2026-08-25**

This document records the factual program state after publication of Sprint38 **First-Party Session Absolute Lifetime Foundation** and its bounded reconciliation-preservation predecessor.

It does not authorize Sprint39 implementation, schema change, runtime activation, deployment, or release.

## Canonical publication lineage

Sprint38 source is published through PR #254.

Canonical Sprint38 source publication:

- merge commit: `1443749c9c69b6a2f9bcfa37534d51b92b6a8de6`;
- tree: `1562f95d6ae05f2dea432365bdbb87748e2718e2`;
- parent: `99095a16f881084fef5937dfbcc0ec9ee6ac97ee`;
- exact qualified source head: `1aad005fc52fd2d7160c73b81fcdadb38ea4a499`;
- signature: **verified / valid**;
- source changed-file count: exactly **10 paths**;
- sorted newline-terminated source-path SHA-256: `411950d5602dc7160668c88e08a3941ebccc8bdc82d20bee77ce4004f039d216`;
- exact-head pull-request qualification: **29/29 workflows SUCCESS** before publication.

Sprint38 governed provenance includes:

- PR #248 — Sprint38 entry-gate preservation compatibility;
- PR #249 — Sprint38 First-Party Session Absolute Lifetime entry gate;
- PR #250 — Sprint38 schema/source-gate preservation predecessor;
- PR #251 — Sprint38 schema/source-envelope gate;
- PR #252 — Sprint38 source preservation bridge;
- PR #253 — Sprint38 historical source-successor preservation predecessor;
- PR #254 — Sprint38 source implementation publication;
- PR #255 — post-Sprint38 reconciliation preservation predecessor.

All lifecycle authorities consumed by those publications grant no standing successor authority.

## Reconciliation preservation predecessor

Post-Sprint38 reconciliation preservation is published through PR #255.

Canonical predecessor publication:

- merge commit: `767a762701ad6dccb53428ff9976e9c22631bc16`;
- tree: `ad78349c5313d930166625f79987b032ce136c00`;
- parent: `1443749c9c69b6a2f9bcfa37534d51b92b6a8de6`;
- signature: **verified / valid**;
- predecessor changed-file count: exactly **4 workflow paths**;
- sorted newline-terminated predecessor-path SHA-256: `f6064e6563ec987fc487162fc29285870fab4869509a626c1b9a9280dc6ebafe`;
- exact-head predecessor qualification: **7/7 workflows SUCCESS** before publication.

The predecessor changes only these fail-closed preservation workflows:

1. `.github/workflows/sprint35-privileged-totp-recovery-regression.yml`;
2. `.github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml`;
3. `.github/workflows/sprint37-first-party-all-session-termination-regression.yml`;
4. `.github/workflows/sprint38-first-party-session-absolute-lifetime-regression.yml`.

The predecessor recognizes the exact post-Sprint38 reconciliation successor consisting of:

1. `docs/ai/AI_NEXT_TASK.md`;
2. `docs/ai/AI_POST_SPRINT_38_CANONICAL_STATE.md`.

Its sorted newline-terminated successor-path SHA-256 is:

`e1bd5f1acb858e3b8e91f3b57dfe8505c4142564142bf9a32b98240c1dd9c472`

Unknown successor shapes remain fail-closed.

## Sprint38 delivered concern

Sprint38 publishes **First-Party Session Absolute Lifetime Foundation** for Local/Test/CI only.

The existing idle lifetime remains fixed at **7200 seconds / 2 hours**.

The new absolute logical session lifetime is fixed at **43200 seconds / 12 hours** from durable server-owned `issued_at_unix`.

Effective authority expiry is bounded by:

`min(now + 7200, issued_at_unix + 43200)`

Activity cannot move the logical authority beyond the absolute deadline.

Laravel session rotation, session inventory access, privileged step-up activity, and ordinary request activity do not reset `issued_at_unix` and do not create a new absolute lifetime for the same logical authority.

The exact absolute deadline remains a valid equality boundary.

At `issued_at_unix + 43200`, the authority may still pass freshness evaluation, but the service performs no non-advancing repository touch when the effective expiry is no longer greater than the current time.

At `issued_at_unix + 43201`, the authority fails closed.

## Runtime invariants

Durable `issued_at_unix` remains the server-owned origin for absolute lifetime calculation.

A persisted `expires_at_unix` value cannot extend effective runtime authority beyond `issued_at_unix + 43200`.

Inventory reports the effective capped expiry rather than treating an inflated historical persistence value as additional authority.

Authorities past the absolute deadline are denied or omitted from active inventory even if persistence contains a later `expires_at_unix` value.

Clock rollback before durable issuance fails closed.

Malformed or impossible timestamp state fails closed.

Idle and absolute TTL configuration mismatch fails closed.

The source-controlled canonical configuration remains:

- `idle_ttl_seconds => 7200`;
- `absolute_ttl_seconds => 43200`.

The absolute TTL is not controlled by a new environment variable.

## Preserved first-party session semantics

Sprint36 session inventory and revocation remain preserved.

Sprint37 tenant-scoped revoke-all remains preserved.

Published session-control operations remain:

- `GET /auth/sessions`;
- `DELETE /auth/sessions/{public_handle}`;
- `POST /auth/sessions/revoke-others`;
- `POST /auth/sessions/revoke-all`;
- `POST /auth/reauthenticate/session-control`;
- canonical `POST /auth/logout`.

Exact tenant + identity ownership remains derived server-side.

Internal `authority_id` remains non-public.

Opaque `public_handle` remains inventory/revocation addressing evidence and is not authentication authority.

Credential epoch and, where applicable, factor epoch checks remain fail-closed request-time authority evidence.

Privileged session-control mutation continues to reuse canonical `session_control` step-up with **300-second** freshness.

Sprint38 adds no public route, API, request payload, middleware scope, or new privileged challenge.

## Repository and audit preservation

Sprint38 does not add or change the `FirstPartySessionAuthorityRepository` contract.

Sprint38 does not mutate the Laravel repository interface boundary.

Sprint38 does not add a new audit event.

Existing session audit vocabulary remains preserved, including:

- `session_issued`;
- `session_revoked`;
- `other_sessions_revoked`;
- `all_sessions_revoked`;
- `session_logout`.

No secret or opaque session authority material is newly exposed by Sprint38.

## Schema and migration state

Canonical application migrations remain exactly **#1 through #13**.

Migrations #1 through #13 are immutable for this reconciliation.

Migration #14 is **NOT REQUIRED / NOT SELECTED / NOT AUTHORIZED**.

Sprint38 is **NO_SCHEMA_CHANGE**.

No table, index, column, migration artifact, or rollback authority is introduced by Sprint38 or this reconciliation.

## Workflow and governance evidence

Sprint38 source PR #254 completed **29/29** exact-head pull-request workflows successfully before squash publication.

The dedicated `Sprint38 First-Party Session Absolute Lifetime Regression` completed successfully on the final exact source head.

Sprint35, Sprint36, Sprint37, historical identity/application regressions, Governance Required Checks, PHP Foundation, updater/security preservation, and relevant M7 preservation checks also completed successfully on the final exact source head.

Required `governance-validation`, `markdown-lint`, and `secret-scan` completed successfully before source publication.

Post-Sprint38 reconciliation predecessor PR #255 completed **7/7** workflows successfully before squash publication, including Sprint35 through Sprint38 preservation, M7.1, PHP Foundation, and Governance Required Checks.

No failing workflow was bypassed.

No direct mutation to `main` or force-push authority was used.

## Runtime and activation boundaries

`ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false` remains the source default.

Sprint38 delivery remains **Local/Test/CI only**.

Technical Preview remains **`NO_SCHEMA_CHANGE`**.

Production remains **`NO-GO / NOT AUTHORIZED`**.

Updater remains **`DISABLED / UNWIRED`**.

Deployment and release remain **NOT AUTHORIZED**.

Sprint38 publication does not arm Technical Preview, Production, updater, deployment, or release behavior.

## Explicit exclusions and non-authority

This reconciliation does not select a Sprint39 implementation concern.

It grants no Sprint39 source authority.

It grants no Sprint39 schema or migration authority.

It grants no migration #14 authority.

It grants no new session route, API, audit event, trusted-device, IP/browser fingerprint, risk-scoring, account-disablement, support impersonation, API/mobile token, WebAuthn/passkey, federation, or break-glass scope.

It grants no Preview activation authority.

It grants no Production activation authority.

It grants no updater, deployment, release, or Phase-exit authority.

## Next governed boundary

The next governed action is Product Owner selection of a future concern followed by a separately authorized bounded entry gate.

Any future Sprint39 concern, exact source envelope, schema decision, migration decision, workflow authority, runtime activation, Ready authority, or Merge authority must be established separately against fresh canonical GitHub state.

No authority from Sprint38 or this reconciliation is inherited by a future PR or head.

Attribution: **Lab | zefry**
