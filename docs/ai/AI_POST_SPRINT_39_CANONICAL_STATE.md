# AI Post-Sprint 39 Canonical State

## Status

**CANONICAL / POST-SPRINT39 / RECONCILIATION BASELINE**

Date: **2026-08-25**

This document records the factual program state after publication of Sprint39 **First-Party Session Organizational Access Revalidation Foundation** and its bounded reconciliation-preservation predecessor.

It does not authorize Sprint40 implementation, schema change, runtime activation, deployment, or release.

## Canonical publication lineage

Sprint39 source is published through PR #262.

Canonical Sprint39 source publication:

- merge commit: `14dc1433300047060be5554d46f87840c9250d06`;
- tree: `b47b4dfefb064aa4c359ffe305578015a7547337`;
- parent: `7024482555fcf7ea882340503b387f053032368d`;
- exact qualified source head: `f3d9e3d91d4b1c83971ddff16795f73a55168159`;
- signature: **verified / valid**;
- source changed-file count: exactly **8 paths**;
- sorted newline-terminated source-path SHA-256: `2cfc92c34f46375b11bf3fe92f9094cefa598d234133847fdd6629be211f12c4`;
- exact-head pull-request qualification: **9/9 triggered workflows SUCCESS** before publication.

Sprint39 governed provenance includes:

- PR #257 — Sprint39 entry-gate preservation predecessor;
- PR #258 — Sprint39 First-Party Session Organizational Access Revalidation entry gate;
- PR #259 — Sprint39 schema/source-envelope preservation predecessor;
- PR #260 — Sprint39 schema/source-envelope gate;
- PR #261 — Sprint39 source-successor preservation predecessor;
- PR #263 — supplemental M7.5/historical preservation correction required for source qualification;
- PR #264 — auxiliary integration-only synchronization into a temporary non-main base, used to synchronize the Sprint39 source branch without force-push and without targeting `main`;
- PR #262 — Sprint39 source implementation publication;
- PR #265 — post-Sprint39 reconciliation preservation predecessor.

All lifecycle authorities consumed by those publications grant no standing successor authority.

## Reconciliation preservation predecessor

Post-Sprint39 reconciliation preservation is published through PR #265.

Canonical predecessor publication:

- merge commit: `8966c4265206f661d2f88480c1c39a73c42ffca1`;
- tree: `e6201f137c88b74b2627a68461ba4806f41e822b`;
- parent: `14dc1433300047060be5554d46f87840c9250d06`;
- exact qualified predecessor head: `25c44ec55f60e0d410c781241186a04b9ced0ff3`;
- signature: **verified / valid**;
- predecessor changed-file count: exactly **5 workflow paths**;
- sorted newline-terminated predecessor-path SHA-256: `ab09e044925ae98740a49c46a70ff387e5f5445b245d3a556d85ad6a354305ec`;
- exact-head predecessor qualification: **8/8 triggered workflows SUCCESS** before publication.

The predecessor changes only these fail-closed preservation workflows:

1. `.github/workflows/sprint35-privileged-totp-recovery-regression.yml`;
2. `.github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml`;
3. `.github/workflows/sprint37-first-party-all-session-termination-regression.yml`;
4. `.github/workflows/sprint38-first-party-session-absolute-lifetime-regression.yml`;
5. `.github/workflows/sprint39-first-party-session-organizational-access-revalidation-regression.yml`.

Qualification of the initial four-workflow predecessor showed that the dedicated Sprint39 workflow is itself triggered by those historical workflow mutations. The predecessor was therefore boundedly refined to the exact five-workflow shape above rather than bypassing the Sprint39 guard or introducing wildcard recognition.

Historical four-workflow predecessor fingerprint `f6064e6563ec987fc487162fc29285870fab4869509a626c1b9a9280dc6ebafe` remains preserved as provenance.

The predecessor recognizes the exact post-Sprint39 reconciliation successor consisting of:

1. `docs/ai/AI_NEXT_TASK.md`;
2. `docs/ai/AI_POST_SPRINT_39_CANONICAL_STATE.md`.

Its sorted newline-terminated successor-path SHA-256 is:

`f38b1a2dea2bd5a55a5cd166db0ea7b2d4ae5186cefe2cb5db27f18f9639e866`

Unknown successor shapes remain fail-closed.

## Sprint39 delivered concern

Sprint39 publishes **First-Party Session Organizational Access Revalidation Foundation** for Local/Test/CI only.

For an otherwise-valid first-party logical session authority, request-time enforcement now revalidates the current durable organizational access before the request may continue.

The enforcement sequence preserves the existing logical session authority checks first and only then revalidates organizational access:

1. preserve existing malformed/restricted-session checks;
2. derive current logical session authority coordinates from server-owned session state;
3. execute the existing `FirstPartySessionAuthorityService::assertActiveCurrent(...)` validation;
4. only after successful logical-authority validation, parse the current server-derived tenant, identity, organization, outlet, and device coordinates into the existing domain identifiers;
5. verify current durable tenant membership through the existing `TenantMembershipVerifier` contract;
6. require the verified membership tenant to equal the authority tenant;
7. verify the exact organization/outlet/device relationship through the existing `OrganizationalRelationshipVerifier` contract;
8. deny absent, malformed, contradictory, removed, mismatched, or denied evidence;
9. continue the request only after all required current access evidence succeeds.

## Organizational authority invariants

All authoritative coordinates remain server-derived.

Caller-provided tenant, organization, outlet, device, owner, or authority selectors do not become authorization authority.

An organization-bound authority must still have current access to that exact organization.

An outlet-bound authority must still have current access to that exact outlet and cannot silently fall back to organization-only access.

A device-bound authority must still have current access to that exact device and requires an outlet. Device-without-outlet evidence fails closed.

Domain-identifier parsing and canonical-value comparison prevent normalization from silently changing the stored authority coordinates.

Removed tenant membership fails closed even if the logical session authority would otherwise remain valid.

Removed organization, outlet, or device relationship fails closed even if the logical session authority would otherwise remain valid.

Missing, malformed, contradictory, cross-tenant, cross-identity, or otherwise inconsistent current durable evidence fails closed.

Sprint39 does not recreate a removed grant, auto-switch organization/outlet/device context, widen a narrower authority to a broader scope, or issue a replacement logical session authority.

Replay, retry, Laravel session rotation, inventory access, revocation operations, and privileged step-up do not resurrect organizational access that no longer exists.

Another tenant or identity remains untouched by denial of the current authority.

## Preserved first-party session semantics

Sprint36 durable logical authority, inventory, revoke-one, revoke-others, and canonical logout remain preserved.

Sprint37 tenant-scoped revoke-all remains preserved.

Sprint38 idle and absolute lifetime semantics remain preserved.

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

The fixed idle authority lifetime remains **7200 seconds / 2 hours**.

The fixed absolute logical authority lifetime remains **43200 seconds / 12 hours** from durable server-owned `issued_at_unix`.

Privileged session-control mutation continues to reuse canonical `session_control` step-up with **300-second** freshness.

Sprint39 changes none of these lifetime, inventory, revocation, logout, or step-up semantics.

## Repository, route, and audit preservation

Sprint39 does not change `FirstPartySessionAuthorityService`.

Sprint39 does not change `FirstPartySessionAuthorityRepository` or its Laravel implementation.

Sprint39 does not change the existing tenant-membership or organizational-relationship verifier interfaces or their durable Laravel implementations.

Sprint39 does not change `DurableOrganizationalAccessRepository` or its implementation.

Sprint39 does not change `AppServiceProvider`, bootstrap wiring, configuration, or routes.

Sprint39 adds no new public route, API endpoint, request payload, feature arm, or environment variable.

Sprint39 adds no new audit event or revocation reason.

Existing session audit vocabulary remains preserved, including:

- `session_issued`;
- `session_revoked`;
- `other_sessions_revoked`;
- `all_sessions_revoked`;
- `session_logout`.

Organizational-access loss uses the existing generic session-authority denial boundary. Sprint39 introduces no dedicated durable session-row transition solely for organizational-access loss.

No secret, credential material, factor material, or opaque session authority is newly exposed by Sprint39.

## Schema and migration state

Canonical application migrations remain exactly **#1 through #13**.

Migrations #1 through #13 are immutable for this reconciliation.

Migration #14 is **NOT REQUIRED / NOT SELECTED / NOT AUTHORIZED**.

Sprint39 is **NO_SCHEMA_CHANGE**.

No table, index, column, migration artifact, or rollback authority is introduced by Sprint39 or this reconciliation.

## Workflow and governance evidence

Sprint39 source PR #262 completed **9/9 triggered workflows successfully** on exact head `f3d9e3d91d4b1c83971ddff16795f73a55168159` before squash publication.

Those triggered workflows were:

- Governance Required Checks;
- PHP Foundation Regression;
- M7.1 Application Regression;
- M7.5 Technical Preview Release Artifact;
- Sprint35 Privileged TOTP Recovery Regression;
- Sprint36 First-Party Session Inventory Revocation Regression;
- Sprint37 First-Party All-Session Termination Regression;
- Sprint38 First-Party Session Absolute Lifetime Regression;
- Sprint39 First-Party Session Organizational Access Revalidation Regression.

The dedicated Sprint39 regression completed successfully on the final exact source head.

Supplemental preservation PR #263 corrected historical/M7.5 recognition without changing application source, schema, runtime activation, deployment, or release boundaries.

Auxiliary PR #264 synchronized the source branch with the then-current canonical `main` through a temporary non-main base and did not directly target or mutate `main`.

Post-Sprint39 reconciliation predecessor PR #265 completed **8/8 triggered workflows successfully** before squash publication, including Sprint35 through Sprint39 preservation, M7.1, PHP Foundation, and Governance Required Checks.

Required governance validation remained fail-closed; no failing workflow was bypassed.

No direct mutation to `main` or force-push authority was used for the canonical Sprint39 publication sequence.

## Runtime and activation boundaries

`ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false` remains the source default.

Sprint39 delivery remains **Local/Test/CI only**.

Technical Preview remains **`NO_SCHEMA_CHANGE`** and is not activated by Sprint39.

Production remains **`NO-GO / NOT AUTHORIZED`**.

Updater remains **`DISABLED / UNWIRED`**.

Deployment and release remain **NOT AUTHORIZED**.

Sprint39 publication does not arm Technical Preview, Production, updater, deployment, or release behavior.

## Explicit exclusions and non-authority

This reconciliation does not select a Sprint40 implementation concern.

It grants no Sprint40 source authority.

It grants no Sprint40 schema or migration authority.

It grants no migration #14 authority.

It grants no new session route, API, request payload, audit event, trusted-device, IP/browser fingerprint, risk-scoring, account-disablement, support impersonation, API/mobile token, WebAuthn/passkey, federation, break-glass, organization auto-switch, grant recreation, or replacement-session scope.

It grants no Preview activation authority.

It grants no Production activation authority.

It grants no updater, deployment, release, or Phase-exit authority.

## Next governed boundary

The next governed action is Product Owner selection of a future concern followed by a separately authorized bounded entry gate.

Any future Sprint40 concern, exact source envelope, schema decision, migration decision, workflow authority, runtime activation, Ready authority, or Merge authority must be established separately against fresh canonical GitHub state.

No authority from Sprint39 or this reconciliation is inherited by a future PR or head.

Attribution: **Lab | zefry**
