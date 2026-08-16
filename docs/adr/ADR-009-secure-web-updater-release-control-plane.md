# ADR-009: Secure Web Updater and Release Control Plane

- Status: Accepted for architecture foundation; implementation remains separately gated
- Date: 2026-08-17
- Owners: Product / Architecture / Security / Operations
- Related tasks: Secure Web Updater Architecture Foundation; governed release artifact v1; privileged update security; updater control plane; updater UI; atomic activation and rollback
- Supersedes: None
- Superseded by: None

## Context

oneQay must eventually support controlled application updates from the product itself while remaining compatible with constrained Stage-1 hosting, including cPanel environments where SSH, symlink management, Composer, Node, or a long-running deployment agent may be unavailable.

The repository already defines non-negotiable updater, release, deployment, security, health, tenant-isolation, and recoverability principles. M7.5 has also published bounded non-Production Technical Preview evidence, including recoverable application publication and isolated backup/restore rehearsal. Those facts are useful architectural inputs but do not create Production readiness, M7.6 authority, migration authority, cPanel mutation authority, or permission to deploy a new updater runtime.

The existing Technical Preview release artifact is a deterministic CI-built package, but its current purpose is qualification evidence rather than a durable product updater contract. The present build format and `RELEASE.json` are therefore treated as predecessor evidence, not as the final updater manifest or final deployment filesystem model.

This ADR defines the secure control-plane architecture that future updater implementation must follow. It does not implement the updater, change workflow YAML, deploy anything, mutate cPanel, change a database/schema, execute a migration, or authorize a later lifecycle stage.

## Decision drivers

- No direct overwrite of the active application code.
- Build once in trusted CI; runtime hosts must not need Composer, npm, Node, source checkout, or `git pull`.
- Trusted, immutable, attributable release identity.
- Fail-closed verification before any activation mutation.
- Recovery when the browser closes, a request times out, or PHP execution is interrupted.
- Safe operation on shared hosting without assuming SSH or symlink support.
- Strong privileged authorization with fresh authentication and TOTP/step-up before installation.
- Protection against SSRF, archive traversal, symlink/hardlink escape, archive bombs, disk exhaustion, secret overwrite, downgrade/replay, and concurrent deployment.
- Atomic or equivalently recoverable application activation.
- Health-gated success with automatic application rollback when a newly activated release is unhealthy.
- Explicit separation between application rollback and database/schema recovery.
- Auditability without logging secrets or Restricted data.
- Compatibility with oneQay Clean Architecture and platform-scoped administration.
- Feature must remain disabled until its security, artifact, control-plane, UI, activation, and recovery gates are separately satisfied.

## Options considered

### Option A — Direct overwrite of live files

The updater downloads an archive and writes files directly over the active application tree.

Benefits: simple to explain and initially small implementation.

Costs and risks: partial deployments are possible, request interruption can leave mixed versions, rollback is unreliable, stale files may survive, secret/config files can be overwritten, and a self-updating process may replace code that is currently executing. This conflicts with the existing deployment rule that direct live overwrite without a recoverable release boundary is unsupported.

Decision: Rejected.

### Option B — Runtime `git pull` plus dependency/build commands

The production/Preview host pulls a Git branch and runs dependency/build commands in place.

Benefits: familiar developer workflow.

Costs and risks: requires source-control credentials and tooling on the host, makes branch state part of deployment authority, increases supply-chain and secret exposure, is incompatible with no-shell/shared-hosting constraints, weakens reproducibility, and can create environment-specific builds.

Decision: Rejected.

### Option C — Governed immutable release artifact plus release control plane

Trusted CI produces an immutable governed release artifact and manifest. The updater downloads only from an allowlisted release source, verifies identity/integrity/compatibility, extracts into an isolated staging area, creates an immutable release directory, applies only separately governed shared runtime configuration, then switches a private active-release pointer. A stable public bootstrap resolves the active private release. Health failure after switch causes automatic pointer rollback to the previous stable release when application/schema compatibility permits.

Benefits: reproducible, auditable, compatible with no-shell hosting, minimizes partial deployment risk, keeps active releases immutable, supports deterministic recovery, and cleanly separates build, verification, activation, shared configuration, and health gates.

Costs and risks: requires more explicit state management, filesystem discipline, disk-space planning, privileged security, release metadata, and recovery testing.

Decision: Selected.

## Decision

oneQay will use a **governed immutable release control plane** for the future Secure Web Updater.

### 1. Trust source and release identity

The initial trusted source is restricted to the canonical repository identity `labzefry/oneQay` and approved immutable release assets. Arbitrary URLs, arbitrary repositories, arbitrary branches, runtime Git checkout, and user-supplied download endpoints are prohibited.

A release is not trusted merely because it was downloaded over HTTPS. The updater must verify the governed repository/release identity, manifest contract, source commit, release/version policy, artifact digest, and later signature/provenance requirements as release maturity increases.

Redirects must remain within an explicit allowlist suitable for GitHub release delivery. The downloader must not become a generic HTTP client or SSRF primitive.

### 2. Build once; deploy trusted artifact

Dependencies and frontend assets are built in trusted CI. The runtime host must not require Composer install, npm install, Vite build, Git checkout, or other source-build commands to activate a release.

The release artifact must contain only runtime-required files and must exclude `.env`, repository metadata, development dependencies, secrets, private keys, and other forbidden material.

### 3. Release Manifest v1

The future governed artifact must carry a machine-readable manifest whose v1 contract includes at minimum:

- schema/manifest version;
- canonical product identity `oneQay`;
- canonical repository identity;
- release ID;
- semantic/product version when the release baseline exists;
- release channel;
- immutable source commit SHA;
- build identity/timestamp and provenance reference;
- artifact filename/type and byte size;
- artifact SHA-256 digest;
- runtime requirements, including PHP compatibility;
- supported current-version range and upgrade constraints;
- deployment compatibility flags;
- migration classification;
- rollback compatibility classification;
- public-surface/bootstrap compatibility;
- required private storage layout version;
- release notes/reference;
- attribution `Lab | zefry`.

The manifest must be schema-validated before extraction or activation. Unknown mandatory schema versions fail closed.

The initial updater implementation supports only `NO_SCHEMA_CHANGE`. Any release declaring schema/database migration work must be rejected until a separate migration-safe updater architecture and authority are approved.

### 4. Private release filesystem model

The target filesystem model is conceptually:

```text
<private-oneqay-root>/
  releases/
    <release-id>/
  staging/
    <operation-id>/
  shared/
    .env
  deployment-state/
    operations/
    locks/
  current-release.json
```

The exact hosting account path is environment-private and must never be committed, logged, or required in user-visible diagnostics.

Release directories become immutable after successful staging/activation. Shared runtime configuration remains outside release directories. Cleanup must never delete the current release, previous stable release, any release referenced by an active/recoverable operation, or any release required by retention/recovery policy.

### 5. Stable public bootstrap

The public web root uses a stable, minimal bootstrap/front controller that resolves the private active-release pointer and loads the selected private application release.

The bootstrap must fail safely if the pointer is missing, malformed, points outside the approved private release root, or references a release that does not satisfy the expected structure.

The updater must not overwrite hosting-managed files such as provider-managed `.htaccess`, `.user.ini`, `php.ini`, `cgi-bin`, or `.well-known` unless a separate, explicit authority and compatibility design exists.

### 6. Active-release pointer

`current-release.json` is the logical activation point. It contains only non-secret deployment identity/state needed to resolve the active immutable release.

Pointer updates must use an atomic write/replace strategy available on the target filesystem. The new release is never considered active until its complete staged release directory exists and the pointer commit succeeds.

The pointer store validates release IDs, canonical paths, expected layout, and operation ownership. Path traversal or arbitrary filesystem paths are prohibited.

### 7. Deployment state machine

Updater execution is a persisted state machine rather than a browser-request transaction. Minimum logical states are:

- `IDLE`;
- `CHECKING`;
- `UPDATE_AVAILABLE`;
- `DOWNLOADING`;
- `VERIFYING`;
- `STAGING`;
- `PREFLIGHT`;
- `READY_TO_ACTIVATE`;
- `ACTIVATING`;
- `VERIFYING_HEALTH`;
- `SUCCEEDED`;
- `ROLLING_BACK`;
- `ROLLED_BACK`;
- `FAILED`.

The persisted operation record is the source of truth for progress. Closing the browser, losing the HTTP connection, or retrying a request must not create a second uncontrolled installation.

Transitions are explicit, validated, idempotent where possible, and auditable. Terminal failures preserve sanitized diagnostic evidence sufficient for operator recovery without exposing secrets.

### 8. Global deployment lock

Only one deployment-changing operation may own the updater at a time. A global lock uses a unique operation ID, owner identity, acquired/renewed timestamps, bounded lease semantics, and safe stale-lock reconciliation.

A second install attempt is denied while a valid lock exists. A stale lock is not silently discarded; the updater reconciles persisted operation state, active pointer state, staging state, and health before deciding whether recovery may continue.

### 9. Download and extraction safety

Before download, the updater checks compatibility, configured limits, available disk headroom, and whether another operation is active.

The downloader enforces allowlisted HTTPS origins/redirects, timeout, bounded retries, expected content type where reliable, maximum package size, and deterministic temporary paths.

Extraction occurs only after manifest and artifact digest verification. Archive handling must reject:

- absolute paths;
- `..` traversal;
- destination escape after normalization;
- symlink escape;
- hardlink escape;
- device/special files;
- unsupported archive entries;
- duplicate/colliding normalized paths;
- unexpected `.env` or secret/key material;
- excessive extracted file count/size/ratio;
- writes outside the operation staging root.

Because current predecessor artifacts use tar-compatible packaging, tar traversal, symlink, and hardlink rules are mandatory. The same equivalent protections apply if ZIP support is later introduced.

### 10. Privileged authorization

Updater installation is a **platform-scoped privileged operation**, not a tenant-selected business operation.

The installation capability requires all of the following before mutation:

- authenticated platform superadmin capability;
- fresh privileged session;
- explicit re-authentication;
- verified TOTP/step-up authentication;
- CSRF protection for browser mutation requests;
- rate limiting/abuse protection;
- current operation/version confirmation;
- immutable audit entry.

Tenant context alone is insufficient to authorize platform release activation. Existing verified platform identity boundaries should be reused rather than inventing a parallel identity source.

Read-only update availability may have a less privileged capability, but it must not leak sensitive installation fingerprints or filesystem details.

### 11. Shared runtime configuration

Release artifacts never contain the live `.env` or secrets. Shared environment configuration remains external to immutable releases.

The updater may only consume a future typed, validated runtime-configuration boundary. Raw secret values must never be returned by updater APIs, shown in history, written into operation logs, or copied into release metadata.

A database connection configuration manager is a separate high-risk sub-milestone. This ADR does not authorize database credential editing, database creation, schema mutation, or migration execution.

### 12. Health-gated activation

After the active pointer switches, the updater verifies the new release through bounded health/readiness checks, including the existing `/health/ready` contract and release identity consistency.

Success is recorded only after the active release is healthy. A pointer switch by itself is not a successful deployment.

Business-smoke checks may be added only when separately authorized and must use synthetic/non-sensitive data in Preview.

### 13. Automatic application rollback

For `NO_SCHEMA_CHANGE` releases, if post-switch health fails, the control plane automatically attempts to restore the pointer to the previous stable release and re-runs health verification.

Rollback success is recorded as `ROLLED_BACK`, not `SUCCEEDED`. Failure to restore a healthy previous release is a terminal high-severity recovery condition and must preserve state/evidence for operator intervention.

Database rollback is never inferred from application-pointer rollback. When future schema-changing releases are supported, their recovery contract requires a separate architecture decision and rehearsal evidence.

### 14. Retention and cleanup

The system retains at minimum the current release and previous known-good release. Additional retention is bounded by policy and available disk.

Cleanup is a distinct stateful action after successful activation/verification. It must be reference-aware, idempotent, and unable to delete the active release, rollback target, staging data needed by a recoverable operation, or shared configuration.

### 15. Audit model

Sanitized audit records include at minimum:

- operation ID;
- actor/platform identity reference;
- action and state transition;
- old and new release IDs/versions;
- source commit/release identity;
- manifest/artifact digest reference;
- timestamps;
- health result;
- rollback result when applicable;
- correlation/request identifiers;
- safe failure code.

Audit must not contain passwords, TOTP secrets/codes, session tokens, API tokens, raw `.env`, database credentials, account-home paths, private backup contents, or other Restricted data.

### 16. Module ownership

Secure Web Updater is a platform/operational capability, not a business Domain module.

Candidate ownership:

```text
apps/web/app/Application/SystemUpdate/
  use cases
  state machine contracts
  ports/interfaces

apps/web/app/Infrastructure/SystemUpdate/
  governed release client
  manifest verifier
  digest verifier
  safe archive extractor
  release filesystem
  active pointer store
  deployment lock/state store
  shared configuration adapter
  health verifier
  audit/history adapter
  retention/cleanup

apps/web/app/Delivery/Http/SystemUpdate/
  privileged admin endpoints
```

UI ownership is under `System → Update & Deployment`. The first UI milestone is read-only/status-oriented; install remains disabled until privileged security, backend control plane, activation, rollback, and recovery gates are satisfied.

### 17. Feature gating

The updater feature flag defaults to **DISABLED**.

Architecture publication alone must not expose an install button, accept artifact uploads, download releases, mutate files, edit runtime configuration, switch the active release, or initiate deployment.

Enabling installation requires separate implementation authority plus successful security/regression/CI gates and a separately authorized Preview rehearsal.

### 18. Lifecycle boundary

This ADR does not repurpose M7.6. Existing M7.6 Preview Deployment / Recovery Rehearsal remains **NOT AUTHORIZED** until separately granted.

This architecture foundation does not authorize M7.7, Phase 0 Exit, Sprint 14, Release, Production, cPanel mutation, database/schema/migration work, restore execution, or Production readiness.

## Consequences

### Positive

- Updates become a governed release operation rather than an in-place file copy.
- Runtime hosts can remain no-shell/no-build environments.
- Active code remains immutable and recoverable.
- Browser interruption does not define deployment correctness.
- Artifact verification, activation, health, and rollback become separate auditable gates.
- Shared secrets/configuration remain outside release artifacts.
- Initial implementation can remain database-safe by enforcing `NO_SCHEMA_CHANGE`.
- Architecture reuses existing identity, health, configuration, release, and deployment boundaries.

### Negative and trade-offs

- More implementation components are required than direct overwrite.
- Disk usage increases because at least two release generations must coexist.
- Stable bootstrap and pointer correctness become critical infrastructure.
- Shared-hosting filesystem semantics must be qualified before activation is enabled.
- Privileged TOTP/step-up is a prerequisite and is not treated as already implemented.
- Schema-changing releases remain unsupported by the first updater version.

## Security, privacy, and tenant impact

The updater is a high-risk remote-code/deployment surface. Threat modeling and negative tests are mandatory. Platform superadmin authorization is deny-by-default, tenant selection does not grant updater authority, and all install-changing actions require fresh re-authentication plus TOTP/step-up.

The control plane stores only sanitized operational metadata. Secrets, credentials, private filesystem identity, raw environment values, and private backup contents are excluded from API responses, operation history, logs, and release metadata.

The updater must preserve tenant isolation by never deriving deployment authorization from tenant-controlled input and by preventing release/config operations from becoming cross-tenant data access mechanisms.

## Migration, rollout, and rollback

Initial implementation sequence is intentionally gated:

1. governed release manifest/artifact v1, with no runtime deployment;
2. privileged update security foundation: platform capability, fresh-session re-authentication, TOTP/step-up, audit;
3. backend updater control plane with feature flag disabled;
4. read-only `System → Update & Deployment` UI;
5. safe staging, active-pointer activation, health gate, automatic application rollback;
6. shared runtime configuration boundary;
7. separately authorized Preview deployment/recovery rehearsal.

The initial updater accepts only `NO_SCHEMA_CHANGE`. Database/schema migration support requires a separate architecture and recovery decision.

## Validation and fitness functions

Before install capability may be enabled, automated tests must cover at minimum:

- valid manifest and trusted release identity;
- unsupported manifest/schema version;
- wrong product/repository/source commit;
- incompatible current version/runtime;
- downgrade/replay rejection;
- checksum mismatch;
- truncated/oversized package;
- forbidden `.env`/secret/key content;
- absolute/traversal path entries;
- symlink/hardlink escape;
- archive bomb/file-count/extracted-size limits;
- insufficient disk;
- release-ID collision;
- concurrent install denial;
- stale-lock reconciliation;
- request/browser interruption and idempotent resume/reconciliation;
- atomic pointer-write failure;
- post-switch health failure;
- automatic rollback success;
- automatic rollback failure preservation;
- cleanup reference safety;
- unauthorized user;
- non-fresh session;
- missing/invalid TOTP step-up;
- CSRF failure;
- feature flag disabled;
- audit completeness and secret redaction.

Architecture fitness functions:

- no arbitrary release URL or branch input;
- no direct overwrite of active release code;
- no runtime Git/Composer/npm build dependency;
- no live `.env` inside release artifacts;
- no release activation before manifest/digest/preflight success;
- no second deployment while the global lock is valid;
- no success state before health verification;
- no database rollback claim from application rollback;
- no schema-changing install in updater v1;
- no install authority derived from tenant context;
- no secret/credential/private-host-path exposure in updater API/log/audit;
- feature flag remains disabled until separately authorized.

## Follow-up tasks

- Define governed Release Manifest v1 JSON schema and CI artifact contract.
- Define durable immutable GitHub Release asset publication only under separate workflow authority.
- Implement privileged platform re-authentication and TOTP/step-up foundation.
- Implement updater operation/lock/state persistence with feature disabled.
- Implement safe downloader, verifier, and extractor.
- Implement private release filesystem and atomic active pointer.
- Implement health-gated activation and automatic application rollback.
- Implement read-only updater admin UI before install controls are enabled.
- Define typed shared runtime configuration boundary without exposing raw secrets.
- Keep database configuration editing and schema migration as separately gated high-risk work.
- Execute Preview deployment/recovery rehearsal only after explicit M7.6/deployment authority.

Attribution: **Lab | zefry**
