# Workflow Directory

## Canonical post-Sprint40 M7.5 preservation closure — 2026-08-27

This current-facing section supersedes older pre-Sprint40/current-state wording retained below as historical provenance. It records repository state only and creates no new implementation or lifecycle authority.

- Functional canonical baseline reconciled by this docs-only publication: `fe502ee40471633e292606ef203a2f0e90754175`; tree `6b494a9a152539a0e922bb564ff96930ff82d86c`; GitHub signature **verified / valid**.
- Sprint40 **First-Party Session Identity Disablement Revalidation Foundation** source is **IMPLEMENTED / PUBLISHED** through PR #286 as `03e86d4e677632a7516c8f4ed2c34045647b774a`, from qualified source head `c8d0f1ab6477f1c743247a519cbc1e6996365199`.
- The Sprint40 source envelope remains exactly **8 paths** with SHA-256 `a9caf2b68210a38687fee543256aec04dc1e67ee1ef403608f7db69139957ff8`.
- Canonical source migration files are exactly **#1–#14**. Migration #14 exists in source and adds only `first_party_authentication_enabled`; this does **not** authorize or imply schema application in Technical Preview or Production.
- Post-Sprint40 historical-regression preservation is published through PR #295 (Sprint32 horizon) and PR #296 (Sprint39 horizon). The bounded M7.5 seven-workflow correction is published through PR #297 and corrected for canonical-main push behavior through PR #298.
- The governed seven-workflow changed-path fingerprint remains `4784ffca1c940d3fa54a2a3988ead07e2de993bde8d3af2bd41014dbdf905be0`.
- Canonical main-push oracle **M7.5 Technical Preview Release Artifact #307** (run `33040247339`) completed **SUCCESS** on `fe502ee40471633e292606ef203a2f0e90754175`. Full-source tests, historical M7.2/M7.3 fixtures with temporary migration #10–#14 isolation, restoration verification, POS/Preview/background regressions, manifest/checksum validation, deterministic archive reproduction, artifact upload, and tracked-source cleanliness all succeeded.
- The oracle and generated qualification artifact are CI evidence only. **Technical Preview remains `NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED`; Production remains `NO-GO / NOT AUTHORIZED`; updater remains `DISABLED / UNWIRED`; deployment and release remain `NOT AUTHORIZED`.**
- PR #295–#298 changed workflow-governance/preservation behavior only; they did not add application source, apply schema, activate runtime, or grant standing successor authority.
- No post-Sprint40 successor implementation concern is selected or authorized by this reconciliation. Any next concern requires fresh canonical-main verification and separate bounded Product Owner authority.

Attribution: **Lab | zefry**


## Canonical Sprint40 pre-source workflow state — 2026-08-25

For current Sprint40 workflow, preservation, exact-shape, and lifecycle interpretation, this section supersedes older current-facing inventory wording retained below as historical provenance.

- Sprint40 **First-Party Session Identity Disablement Revalidation Foundation** has a published entry gate (#268), schema/source-envelope gate (#270), source-preservation predecessor (#271), supplemental Sprint33/Sprint34 compatibility correction (#272), historical-compatibility bridge (#273), and canonical-documentation synchronization preservation predecessor (#274).
- The published Sprint40 source-preservation lineage recognizes only the exact future eight-path source implementation shape with sorted newline-terminated SHA-256 `a9caf2b68210a38687fee543256aec04dc1e67ee1ef403608f7db69139957ff8`. Sprint40 application source and migration #14 remain **NOT YET IMPLEMENTED / NOT YET PUBLISHED**.
- PR #274 prepared the Sprint35-Sprint39 fail-closed historical workflow horizon to recognize only the exact 13-document canonical synchronization successor when changed-path count is exactly 13 and sorted newline-terminated fingerprint is `b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d`.
- The 13-document synchronization envelope is `README.md`, `PROJECT_MANIFEST.md`, `ROADMAP.md`, `TASKS.md`, `CHANGELOG.md`, `docs/ai/AI_NEXT_TASK.md`, `docs/ai/AI_PROJECT_STATE.md`, `docs/ai/AI_SESSION_STATE.md`, `ARCHITECTURE.md`, `DATABASE.md`, `SECURITY.md`, `DEPLOYMENT.md`, and `.github/workflows/README.md`.
- Unknown successor shapes remain fail-closed. No wildcard successor acceptance, broad `paths-ignore`, workflow/job disablement, source/schema/runtime bypass, or direct-main mutation is authorized.
- Pull-request qualification is exact-head based. Only workflows actually triggered for the exact head are counted; every triggered workflow must complete successfully. A head mutation invalidates previously head-bound Product Owner merge authority.
- `product-owner-merge-authority.yml` remains the repository-native exact-head merge gate. The Product Owner's explicit lifecycle decision must be recorded on the PR in the exact standalone format documented below; the comment is operational evidence of the already-granted Product Owner authority, not a substitute for that authority.
- Technical Preview remains unactivated for Sprint40. Production remains **NO-GO / NOT AUTHORIZED**. Updater remains **DISABLED / UNWIRED**. Deployment and release remain **NOT AUTHORIZED**.

Historical workflow sections below remain preserved as provenance and must not override this section for current-state interpretation.

Attribution: **Lab | zefry**

## Canonical post-Sprint 33 program-state reconciliation — 2026-08-20

For current identity, security, recovery, schema, runtime, workflow, and next-work interpretation, this section supersedes older current-facing post-Sprint32 wording retained below as historical provenance.

- Sprint 21 through Sprint 33 governed control/identity foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint 33 Recovery-Bound Password Reset Completion Foundation is published through source PR #213 as `9eba56d92b4b714225d677990ffed93687b0b2cb` with tree `492e723b6343dab518b43645883976ad20f0054c`, parent `c89baa55318dca230cd0ef792df80e3d54b8165d`, and a verified/valid publication signature after **24/24** exact-head pull-request workflows succeeded.
- The qualified Sprint33 source head was `a7a50644cbe67e6f08138c79cf50a9350e8e220d`; source remained exactly **39 paths** with sorted-path SHA-256 `04a1177c12712183a7dda4ae81be1356c0e41294533336c9f999d376c224712a`.
- Sprint33 entry-gate PR #211 and source-envelope gate PR #212 remain published provenance; their authorities and PR #213 merge authority are consumed and grant no standing successor authority.
- Canonical source migrations remain exactly **#1 through #10** and are unchanged by Sprint33. No migration #11 is authorized.
- `ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=false` remains the source default and recovery execution remains bounded to **Local/Test/CI**.
- Sprint32 proof still establishes only `password_reset_required` restricted state for exactly **600 seconds**; Sprint33 binds the consumed server-owned recovery `code_id` into that restricted evidence and exposes only `POST /auth/recovery/password-reset` inside the same bounded recovery arm.
- Reset accepts only opaque `password` input of **12–4096 bytes**, performs no trim/normalization, hashes with `PASSWORD_DEFAULT`, updates only the existing exact credential row, revokes remaining unused recovery codes, and appends exactly one secret-free `password_reset_completed` audit event atomically.
- Credential epoch is derived without schema change from the durable count of `password_reset_completed` rows. Fresh normal login captures the epoch; stale, malformed, negative, future, or post-reset legacy-missing epoch evidence fails closed as applicable.
- Protected-control principals and identities with confirmed privileged TOTP remain ineligible for recovery completion; TOTP secret material is not read, decrypted, replaced, deleted, or mutated.
- Successful reset invalidates the restricted session and regenerates CSRF but establishes no normal/full login, MFA evidence, step-up evidence, or epoch evidence; fresh normal login remains mandatory.
- Technical Preview remains **`NO_SCHEMA_CHANGE`**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Durable persistence remains source-default-disabled with `ONEQAY_PERSISTENCE_ENABLED=false`.
- Authenticated in-session password change, administrative password overwrite, MFA/TOTP recovery and factor lifecycle, protected-control recovery bypass, support/admin bypass, email/SMS recovery delivery, passkeys/WebAuthn, federation, API-token authentication, Preview/Production auth/schema activation, updater activation, deployment, and release remain separately governed.
- Sprint32 + Sprint33 now form a bounded Local/Test/CI end-to-end recovery sequence for eligible non-protected identities without confirmed privileged TOTP, but this does not activate recovery in Technical Preview or Production.
- This reconciliation selects **no new post-Sprint33 implementation concern** and grants no Sprint34, migration #11, source, Preview, Production, updater, deployment, or release authority.

The detailed factual baseline is `docs/ai/AI_POST_SPRINT_33_CANONICAL_STATE.md`. Historical sections below remain preserved as provenance and must not override this section for current-state interpretation.

Attribution: **Lab | zefry**

## Canonical post-Sprint 32 program-state reconciliation — 2026-08-19

For current identity, security, recovery, schema, runtime, workflow, and next-work interpretation, this section supersedes older current-facing post-Sprint30/post-Sprint31 wording retained below as historical provenance.

- Sprint 21 through Sprint 32 governed control/identity foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint 31 Privileged Reauthentication / Step-Up Session Freshness Foundation remains published with exact **300-second** freshness for the `policy_administration` scope and its source-default-disabled Local/Test/CI boundary.
- Sprint 32 Authentication Recovery / JRN-003 Recovery Proof Foundation is published through source PR #208 as `914f93f8636bbd0901c61d8a8f14ad69c2c8fbfe` with tree `89f8dcea209ea912ba2539f3c6224a3a0519c8f7`, parent `7f2cc64e5a85158fb24cf03b61d2b36ead73190a`, and a verified/valid publication signature after **24/24** exact-head pull-request workflows succeeded.
- Sprint 32 source remained within the exact **32-path** envelope whose sorted-path SHA-256 is `db230ab3b77fff67f0bd12d7d7b615146d9d9df9a0af12014214e1862e9f6867`.
- Canonical source migrations are exactly **#1 through #10**. Migrations #1–#9 remain immutable. Migration #10 creates only `oneqay_identity_recovery_codes` and `oneqay_identity_recovery_audit`. No migration #11 is authorized.
- `ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=false` remains the source default and Sprint 32 recovery execution remains bounded to **Local/Test/CI**.
- Successful recovery-code rotation issues exactly **8** `rq1.<22-char selector>.<43-char secret>` codes, persists no plaintext recovery secret/code, and uses SHA-256 digest verification with `hash_equals` plus secret-free audit evidence.
- Recovery-code rotation and proof are atomic; same-code replay/concurrency is fail-closed with at most one winner.
- Successful recovery proof establishes only the restricted `password_reset_required` session for exactly **600 seconds**. It does **not** establish a normal/full authenticated session, does not populate the five canonical Sprint27 full-session keys, and does not read/decrypt the TOTP secret.
- Technical Preview remains **`NO_SCHEMA_CHANGE`**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Durable persistence remains source-default-disabled with `ONEQAY_PERSISTENCE_ENABLED=false`.
- Password reset/change/overwrite, automatic/full login from recovery proof, MFA/TOTP recovery, factor replacement/deletion, protected-control recovery, support/admin bypass, email/SMS recovery, passkeys, federation, API-token authentication, Preview/Production auth/schema activation, updater activation, deployment, and release authority remain separately governed and **NOT AUTHORIZED** by Sprint 32 or this reconciliation.
- Sprint 32 publishes the JRN-003 **recovery-proof foundation** only; this reconciliation does not claim end-to-end password recovery completion because password reset/change/overwrite remain excluded.
- This reconciliation selects **no new post-Sprint32 implementation concern** and grants no Sprint33, migration #11, source, Preview, Production, updater, deployment, or release authority. Any subsequent source work requires a separately bounded Product Owner entry gate.

The detailed factual baseline is `docs/ai/AI_POST_SPRINT_32_CANONICAL_STATE.md`. Historical sections below remain preserved as provenance and must not override this section for current-state interpretation.

Attribution: **Lab | zefry**

## Canonical post-Sprint 30 program-state reconciliation — 2026-08-19

For current identity, security, schema, runtime, workflow, and next-work interpretation, this section supersedes older current-facing post-Sprint28/post-Sprint29 wording retained below as historical provenance.

- Sprint 21 through Sprint 30 governed control/identity foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint 29 First-Control-Principal Bootstrap Credential Foundation is published through source PR #195 and closes the first protected-control credential circular dependency without credential overwrite, password recovery, or session creation.
- Sprint 30 Privileged TOTP MFA Foundation is published through PR #199 as `6d41755eba4030c2b0b7c4f3b7a5806b761b0ad7` with tree `bf1d56af5524e77919833bd64b585cdca84af55d` after **22/22** exact-head workflows succeeded.
- Sprint 30 source remained within the exact **46-path** envelope whose sorted-path SHA-256 is `95daaf86ba93ae797fccf3825d65d27acd4f71ee58916898a16fbc83d432a5ce`.
- Canonical source migrations are exactly **#1 through #9**. Migration #9 adds one tenant-scoped TOTP-factor row per identity with encrypted secret ciphertext and monotonic accepted-time-step replay state.
- The direct TOTP dependency is pinned to `spomky-labs/otphp` **11.5.0**; oneQay does not implement custom TOTP/HMAC/Base32 cryptography.
- `ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED` remains source-default **false** and Sprint 29–30 delivery remains bounded to **Local/Test/CI**.
- For an armed protected-control principal, password verification alone does not establish the full privileged session. Restricted enrollment/challenge state is used until successful confirmed TOTP challenge establishes full session MFA evidence.
- TOTP secrets are Restricted, encrypted at rest, context-bound to tenant + identity, and never stored as plaintext. Accepted TOTP time steps advance monotonically to deny replay.
- Technical Preview remains **`NO_SCHEMA_CHANGE`**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Durable persistence remains source-default-disabled with `ONEQAY_PERSISTENCE_ENABLED=false`.
- Password change/reset/recovery, MFA recovery, factor replacement/deletion, multiple factors, WebAuthn/passkeys, federation, API-token authentication, Preview auth activation, and Production auth activation remain separately governed.
- **JRN-003 remains UNRESOLVED**; this reconciliation creates no password/MFA recovery path.
- The next logical governed identity/security concern is **Privileged Reauthentication / Step-Up Session Freshness Foundation**. DEC-006 already requires risk-based reauthentication/step-up for sensitive operations. This concern is **CANDIDATE / NOT AUTHORIZED** until a separate bounded entry gate freezes semantics, freshness evidence, session transitions, routes, exact source envelope, schema decision, and preservation tests.

The detailed factual baseline is `docs/ai/AI_POST_SPRINT_30_CANONICAL_STATE.md`. Historical sections below remain preserved as provenance and must not override this section for current-state interpretation.

Attribution: **Lab | zefry**

## Canonical post-Sprint 28 workflow inventory — 2026-08-18

For current workflow/lifecycle interpretation, this section supersedes older inventory text below that stops at M7.4A/M7.5 or treats later governed identity/control regressions as nonexistent.

Current repository workflow evidence includes the established governance/foundation/M7 workflows plus the governed Sprint 21–28 regression chain, including:

- `.github/workflows/sprint21-role-permission-policy-regression.yml`;
- `.github/workflows/sprint22-policy-administration-regression.yml`;
- `.github/workflows/sprint23-initial-tenant-admin-provisioning-regression.yml`;
- `.github/workflows/sprint24-protected-control-admin-lifecycle-regression.yml`;
- `.github/workflows/sprint25-policy-administration-delivery-regression.yml`;
- `.github/workflows/sprint26-identity-credential-verification-regression.yml`;
- `.github/workflows/sprint27-first-party-session-establishment-regression.yml`;
- `.github/workflows/sprint28-initial-password-enrollment-regression.yml`.

Sprint 28 exact-head qualification completed **19/19 triggered workflows successfully** before Product Owner merge authority. The Sprint 28 dedicated workflow enforced the exact 33-path source envelope, migrations #1–#7 immutability plus additive migration #8, Local/Test/CI route boundaries, secret-minimal enrollment state, disposable enrollment regression, Sprint 21–27 preservation, Preview/Production/updater separation, and tracked-source cleanliness.

Current workflow existence does not broaden runtime authority: Technical Preview remains **`NO_SCHEMA_CHANGE`**, Production remains **`NO-GO / NOT AUTHORIZED`**, updater remains **`DISABLED / UNWIRED`**, and persistence remains default-disabled. The next logical concern, First-Control-Principal Bootstrap Credential Foundation, requires its own future workflow only after a separately published bounded entry gate; this synchronization does not authorize such workflow/source work.

The detailed post-Sprint28 qualification record is `docs/ai/AI_POST_SPRINT_28_CANONICAL_STATE.md`. Historical workflow descriptions below remain provenance and continue to describe their own bounded mechanisms unless superseded here for current inventory interpretation.

Attribution: **Lab | zefry**

## Canonical M7.5 lifecycle closure — 2026-08-17

For current workflow/lifecycle interpretation, this section supersedes the older current-facing M7.5 consolidation retained below as historical control-plane provenance.

M7.5 mandatory runtime/engine evidence is now **CLOSED / EVIDENCE_COMPLETE / PUBLISHED** with **29 VERIFIED / 0 BLOCKED** after PR #129, with secure rehearsal cleanup published through PR #130. `lifecycle_authority_created=false` remains true for the evidence package.

The existing bounded M7.5 workflow mechanisms remain historical/current technical mechanisms only. This closure changes no workflow YAML, protected status-check producer, ruleset, deployment mechanism, or merge authority. It does not authorize M7.6, M7.7, Phase 0 Exit, Sprint 14, Release, Production, database/schema/migration, restore, cPanel mutation, or deployment.

The next candidate engineering direction is separately gated Secure Web Updater architecture/release-control-plane work. Any workflow expansion for that capability requires separate authority.

Attribution: **Lab | zefry**

## Canonical program-state consolidation — 2026-08-16

For current workflow-directory interpretation, this section supersedes older statements below that stop at M7.4A or describe all `M7.5+` workflow activity as future/nonexistent. Those statements are retained as historical control-plane provenance.

Current repository workflow inventory also includes bounded M7.5 mechanisms, including:

- `.github/workflows/m7-5-preview-db-qualification-regression.yml`;
- `.github/workflows/m7-5-preview-release-artifact.yml`.

Their existence and prior governed use do not create general deployment, Release, Production, database/schema/migration, restore, M7.6, M7.7, Phase 0 Exit, or Sprint 14 authority. Canonical M7.5 after PR #124 is **26 VERIFIED / 3 BLOCKED**, overall **BLOCKED / INCOMPLETE**, with `lifecycle_authority_created=false`.

This documentation-only consolidation changes no workflow YAML, status-check producer, ruleset, or merge authority.

Application release and deployment workflows remain deferred until the relevant
Product Owner authority and delivery gates are available. M7.1, M7.2, M7.3,
M7.4, and M7.4A each permit only their separately authorized bounded
Local/Test/CI or explicit-Preview validation workflows.

The repository currently permits narrowly scoped governance, foundation, M7.1,
M7.2, M7.3, M7.4, and M7.4A validation workflows:

- `.github/workflows/governance-required-checks.yml`;
- `.github/workflows/php-foundation-regression.yml`;
- `.github/workflows/product-owner-merge-authority.yml`;
- `.github/workflows/m7-1-application-regression.yml`;
- `.github/workflows/m7-2-tenant-isolation-regression.yml`;
- `.github/workflows/m7-3-identity-org-context-regression.yml`;
- `.github/workflows/m7-4-pos-core-synthetic-regression.yml`;
- `.github/workflows/m7-4a-technical-preview-interaction-regression.yml`.

## Stable governance checks

`governance-required-checks.yml` produces:

- `governance-validation`;
- `markdown-lint`;
- `secret-scan`.

It runs for pull requests targeting `main`, uses least-privilege
`contents: read`, pins `actions/checkout` to a full commit SHA, does not access
repository secrets, and does not build, publish, release, migrate, or deploy
oneQay.

## PHP foundation regression

`php-foundation-regression.yml` produces:

- `php-foundation-regression`.

The check:

- checks out the exact pull-request source head rather than the synthetic merge
  ref;
- exposes the PHP version and rejects PHP versions below 8.2;
- exposes the Composer version;
- runs `composer validate --strict --no-check-publish`;
- runs PHP syntax validation across tracked PHP foundation and test files;
- runs the full `composer test` foundation regression;
- uses the GitHub-hosted runner's preinstalled PHP and Composer toolchain;
- adds no third-party setup action;
- accesses no Production credential, Production database, or Production data;
- performs no migration, release, publish, or deployment action.

## Milestone workflow applicability

Standalone M7.2, M7.3, M7.4, and M7.4A workflows are applicable only when their
owned source envelope or their own workflow definition is changed. Unrelated
documentation-only pull requests must not create milestone regression failures.

Workflow applicability and regression preservation are separate controls:

- a non-applicable predecessor workflow may be **NOT RUN**;
- an applicable successor workflow must continue executing predecessor
  behavioral regressions that are part of its governed verification chain;
- path filtering does not authorize modification of predecessor source and does
  not disable tenant, identity/organizational, money, idempotency, audit, or
  other preserved regressions;
- a pull request that changes a milestone workflow remains applicable to that
  workflow so the workflow correction itself is validated.

The historical bounded post-M7.4 lifecycle-stabilization envelope permitted only
the explicit governance/documentation and M7.2/M7.3/M7.4 workflow files needed
to validate that corrective PR. It did not broaden application business-source
authority and is preserved as historical control-plane provenance.

## M7.1 application regression

`m7-1-application-regression.yml` produces:

- `m7-1-application-regression`.

The check is bounded to the separately authorized M7.1 application skeleton. It:

- checks out the exact pull-request source head;
- preserves the root Platform Foundation regression;
- enforces the governed PHP 8.2-8.5 compatibility boundary;
- uses Node.js `24.19.0` for the Local/Test/CI frontend toolchain;
- requires committed `composer.lock` and `package-lock.json` files;
- validates and installs Composer dependencies from the lockfile;
- rejects unresolved High/Critical Composer advisories;
- validates application PHP syntax;
- installs npm dependencies with `npm ci`;
- rejects npm advisories at High or Critical severity;
- type-checks Vue/TypeScript source;
- builds Vite assets;
- runs the M7.1 application regression covering configuration fail-closed,
  health/readiness, correlation/error, tenant-context fail-closed, and
  architecture-boundary behavior;
- uses `contents: read` and receives no repository or Production secret;
- performs no SQL, migration, infrastructure mutation, deployment, release, or
  Production action.

This M7.1 check is source-lifecycle evidence. Its existence does not modify the
protected-branch required-status-check set and does not authorize M7.2 or later
work.

## M7.2 tenant isolation regression

`m7-2-tenant-isolation-regression.yml` produces:

- `m7-2-tenant-isolation-regression`.

The standalone check is bounded to the M7.2 Tenant Kernel & Isolation Foundation
and runs only when the M7.2-owned source envelope or the M7.2 workflow itself is
touched. It:

- checks out the exact pull-request source head;
- enforces the governed M7.2 source envelope plus the explicit lifecycle-
  stabilization governance envelope when the workflow itself is under review;
- preserves the root Platform Foundation regression;
- preserves the M7.1 application regression;
- rejects dependency-manifest or lockfile changes;
- validates and installs the already locked Composer dependencies;
- rejects unresolved High/Critical Composer advisories;
- validates application PHP syntax;
- rejects database/schema/migration/SQL implementation across the bounded source;
- installs already locked npm dependencies with `npm ci`;
- rejects npm advisories at High or Critical severity;
- preserves Vue/TypeScript type-check and Vite build evidence;
- runs deterministic synthetic tenant verification, fail-closed missing/invalid
  context, raw-client-hint rejection, cross-tenant negative verification,
  request-scope clearing, safe-denial, and framework-independence regression;
- uses `contents: read` and receives no repository or Production secret;
- performs no SQL, migration, infrastructure mutation, deployment, release,
  publish, or Production action.

M7.3, M7.4, or M7.4A successor source does not need this standalone predecessor
workflow to run merely because successor paths changed. The applicable successor
workflow preserves the M7.2 tenant-isolation behavioral regression.

The synthetic tenant verifier is Local/Test/CI evidence only and is not
registered as a Production identity or membership implementation.

## M7.3 identity organizational context regression

`m7-3-identity-org-context-regression.yml` produces:

- `m7-3-identity-org-context-regression`.

The standalone check is bounded to the M7.3 Identity / Organization / Outlet /
Device Minimum and runs only when the M7.3-owned source envelope or the M7.3
workflow itself is touched. It:

- checks out the exact pull-request source head;
- rejects changed paths outside the Product Owner-authorized M7.3 source or
  lifecycle-stabilization envelope;
- rejects modification of the root Tenant/Auth Platform Foundation;
- preserves the root Platform Foundation regression;
- preserves the M7.1 application regression;
- preserves the full M7.2 tenant isolation regression;
- rejects Composer/npm manifest or lockfile changes;
- validates and installs only the already locked application dependencies;
- rejects unresolved High/Critical Composer and npm advisories;
- validates application PHP syntax;
- rejects database/schema/migration/SQL implementation;
- preserves Vue/TypeScript type-check and Vite build evidence;
- runs deterministic positive controls for verified identity, organization,
  outlet, and device context;
- runs deny-by-default negative controls for missing/malformed identity,
  missing tenant context, missing membership, cross-tenant identity,
  foreign organization/outlet/device relationships, global identifier
  collisions, raw untrusted organizational hints, and stale request context;
- verifies generic denial behavior without foreign-context payload leakage;
- verifies Domain/Application framework independence;
- constrains relationship evidence to deterministic Local/Test/CI synthetic
  principals;
- uses `contents: read` and receives no repository or Production secret;
- performs no authentication implementation, SQL, migration, infrastructure
  mutation, deployment, release, publish, or Production action.

M7.4 or M7.4A successor source does not need this standalone predecessor
workflow to run merely because successor paths changed. The applicable successor
workflow preserves the full M7.3 identity/organizational-context behavioral
regression.

M7.3 does not implement password login, MFA/TOTP, WebAuthn, token authentication,
OAuth/OIDC/SAML, real tenant membership persistence, or Production organizational
repositories. It does not authorize M7.4 or later work.

## M7.4 POS core synthetic regression

`m7-4-pos-core-synthetic-regression.yml` produces:

- `m7-4-pos-core-synthetic-regression`.

The standalone check is bounded to the M7.4 POS Core Synthetic Vertical Slice and
runs only when the M7.4-owned source envelope or the M7.4 workflow itself is
touched. It:

- checks out the exact pull-request source head;
- enforces the bounded POS source envelope plus the explicit lifecycle-
  stabilization governance envelope when the workflow itself is under review;
- preserves the root Platform Foundation and M7.1 application regressions;
- preserves the full M7.2 tenant-isolation regression;
- preserves the full M7.3 identity/organizational-context regression;
- rejects dependency-manifest or lockfile changes;
- validates locked Composer/npm dependencies and rejects unresolved
  High/Critical advisories;
- validates PHP syntax and Vue/TypeScript/Vite build evidence;
- preserves Domain/Application framework independence;
- rejects database/schema/migration/SQL implementation;
- executes deterministic M7.4 POS core synthetic regression for exact-money,
  idempotency/replay, payment sufficiency, stock causation, tenant/context, and
  audit/correlation behavior;
- uses `contents: read`, receives no Production secret, and performs no
  migration, deployment, release, publish, or Production action.

M7.4 workflow success is regression evidence for the bounded synthetic POS slice;
it is not evidence that a complete end-user POS UI, Production authentication,
durable business persistence, deployment, release, or Production readiness
exists. M7.4A successor paths do not require this standalone predecessor workflow
to run because the applicable M7.4A workflow preserves the M7.4 behavioral
regression.

## M7.4A Technical Preview interaction regression

`m7-4a-technical-preview-interaction-regression.yml` produces:

- `m7-4a-technical-preview-interaction-regression`.

The check is bounded to the M7.4A Technical Preview Interaction Layer. It:

- checks out the exact pull-request source head;
- enforces the bounded M7.4A Preview Application/Infrastructure/Delivery,
  provider/routes/UI/test, and workflow source envelope;
- runs the root Platform Foundation regression and M7.1 application regression;
- preserves the full M7.2 tenant-isolation, M7.3 identity/organizational-context,
  and M7.4 POS-core behavioral regressions;
- requires unchanged Composer/npm manifests and lockfiles;
- validates and installs only already locked dependencies and rejects unresolved
  High/Critical Composer/npm advisories;
- validates PHP syntax, Vue/TypeScript type checking, and Vite build evidence;
- preserves Preview Application framework independence;
- rejects database/SQL/migration implementation and obvious credential material
  within the bounded Preview implementation envelope;
- exercises the synthetic sign-in → server-verified context → catalog → cart →
  `CASH` / `MANUAL_EXTERNAL` → existing M7.4 `CompleteSyntheticSale` → receipt
  journey;
- uses explicit CI Preview configuration with synthetic data only;
- performs no migration, deployment, release, publish, or Production action.

M7.4A workflow success is lifecycle evidence for the already published PR #98
interaction layer. It does not grant M7.5 runtime qualification, durable
Production persistence, deployment, release, Phase 0 Exit, Sprint 14, or
Production authority.

## Product Owner merge authority

`product-owner-merge-authority.yml` evaluates repository-native Product Owner
merge authority and writes the commit-status context:

- `product-owner-merge-authority`.

A valid authority record must be an issue comment on the pull request authored
by the repository owner and contain these exact standalone lines:

```text
PRODUCT OWNER MERGE AUTHORIZATION
PR: #<pull-request-number>
EXACT HEAD: <40-character-head-sha>
MERGE AUTHORITY: GRANTED
```

The evaluator fails closed when no matching authority exists. A new push changes
the exact head, so an authority comment bound to the previous head cannot satisfy
the new commit. Editing or deleting authority comments triggers reevaluation.

The evaluator runs only from the trusted default-branch workflow through
`pull_request_target` and `issue_comment`. It never checks out or executes
pull-request code. Its permissions are limited to metadata reads and
`statuses: write`, preventing an untrusted PR-head workflow edit from
self-authorizing merge.

## Required ruleset activation

The active default-branch lifecycle requires:

- `governance-validation`;
- `markdown-lint`;
- `secret-scan`;
- `php-foundation-regression`;
- `product-owner-merge-authority`.

M7.1, M7.2, M7.3, M7.4, and M7.4A source authority do not grant repository-
ruleset mutation. `m7-1-application-regression`,
`m7-2-tenant-isolation-regression`, `m7-3-identity-org-context-regression`,
`m7-4-pos-core-synthetic-regression`, and
`m7-4a-technical-preview-interaction-regression` remain mandatory milestone-
specific lifecycle evidence for their applicable Draft PRs even though they are
not silently added to the protected required-status set.

Repository protection must preserve strict required-status-check policy,
independent review, stale-review dismissal, latest-push approval, review-thread
resolution, squash-only merge, deletion and non-fast-forward protection, and an
empty bypass list.

## Scope boundary

These workflows are control-plane, foundation-validation, or bounded
M7.1/M7.2/M7.3/M7.4/M7.4A Local/Test/CI/explicit-Preview mechanisms. They do not
authorize Sprint 14, M7.5+, broader application business source, real Production
authentication, SQL/migration execution, Production database changes,
deployment, release, ADR/GD promotion, JRN resolution, Phase 0 Exit, or
Production readiness.

Any workflow added here must:

- use least-privilege `permissions`;
- pin reusable actions to immutable commits;
- avoid untrusted-code secret exposure;
- avoid Production credentials and Production data unless separately
  authorized;
- produce traceable results bound to a commit;
- document its authority boundary and operational activation requirements.

Attribution: Lab | zefry
