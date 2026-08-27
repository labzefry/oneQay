# oneQay Deployment Handbook

## Canonical post-Sprint41 source publication reconciliation — 2026-08-27

This current-facing section supersedes older post-Sprint40/current-state wording retained below as historical provenance. It records canonical source and CI evidence only and creates no new implementation or lifecycle authority.

- Functional canonical baseline reconciled by this docs-only publication: `1994a7821846db9f872edb62a984c4248f766c1e`; tree `1eb7a9294eed86c6e3333f0db25ef9e3793aaaf0`; GitHub signature **verified / valid**.
- Sprint41 **First-Party Identity Authentication Eligibility Administration Foundation** is **IMPLEMENTED / PUBLISHED** through PR #315 as `1994a7821846db9f872edb62a984c4248f766c1e`, from qualified source head `fadd0c5bba83e4a2e2e209e1750de2224b7f3b68`.
- Sprint41 source remains exactly **12 paths** with sorted newline-terminated SHA-256 `b2c5fc10a8baa2d56991d6dbd36b0407159d70953654ef322a9a11d23660489b`.
- Canonical source migrations are exactly **#1–#15**. Migration #15 creates only the tenant-scoped `oneqay_identity_authentication_eligibility_mutations` journal; migrations #1–#14 remain immutable.
- Sprint41 implements only server-authorized `first_party_authentication_enabled: true -> false` administration for eligible ordinary same-tenant identities. No enable/reactivation, bulk mutation, protected-control disablement, administrator session revocation, credential mutation, factor mutation, membership mutation, or grant mutation authority exists.
- Sprint40 remains the independent request-time consumer of current authentication eligibility. Sprint41 does not weaken Sprint36–Sprint40 session, lifetime, organizational-access, or eligibility revalidation controls.
- Bounded historical/source compatibility closure required for source publication is merged through PR #316–#323, and the post-Sprint41 canonical-document preservation predecessor is published through PR #325. These PRs changed preservation/governance behavior only where applicable and created no runtime, deployment, updater, Preview, or Production authority.
- Canonical main-push oracle **M7.5 Technical Preview Release Artifact #338** (run `33095155642`) completed **SUCCESS** on `1994a7821846db9f872edb62a984c4248f766c1e`.
- Main-push **Backend Updater Control Plane Regression #121**, **Privileged Update Security Regression #123**, and **Read-Only Update Deployment UI Regression #106** also completed **SUCCESS** on the same canonical commit; these are regression evidence only.
- Technical Preview remains **`NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED`**; Sprint41 source and migration #15 are **not activated/applied** in Technical Preview. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment and release remain **`NOT AUTHORIZED`**.
- No post-Sprint41 successor implementation concern is selected or authorized by this reconciliation. Any next concern must begin with a separately bounded Product Owner entry gate; no Sprint42, migration #16, source, schema, runtime, Preview, Production, updater, deployment, or release authority is implied.

Attribution: **Lab | zefry**


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


## Canonical Sprint40 pre-source deployment state — 2026-08-25

For current Sprint40 runtime, deployment, schema-activation, and release interpretation, this section supersedes older current-facing wording retained below as historical provenance.

- Sprint40 **First-Party Session Identity Disablement Revalidation Foundation** is selected and governed through its published entry gate and schema/source-envelope gate, but source implementation and migration #14 are **NOT YET IMPLEMENTED / NOT YET PUBLISHED**.
- The selected migration #14 is Local/Test/CI source-stage work only and remains **NOT CREATED / NOT APPLIED** at this documentation-synchronization stage. Technical Preview receives no Sprint40 migration or authentication-eligibility activation from this work.
- The future Sprint40 source envelope remains exactly eight paths with sorted newline-terminated SHA-256 `a9caf2b68210a38687fee543256aec04dc1e67ee1ef403608f7db69139957ff8`; publication of preservation workflows does not activate any runtime.
- This canonical documentation synchronization is exactly 13 documentation paths with sorted newline-terminated SHA-256 `b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d`. It performs no build, migration, deployment, release, updater, DNS, cPanel, Preview, or Production mutation.
- `ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false` remains source-default. Sprint40 execution remains bounded to a future separately qualified Local/Test/CI source stage unless later authority explicitly changes that boundary.
- Technical Preview remains unactivated for Sprint40. Production remains **NO-GO / NOT AUTHORIZED**. Updater remains **DISABLED / UNWIRED**. M7.6, M7.7, deployment, release, Production migration, and Production database mutation remain **NOT AUTHORIZED**.

Historical deployment sections below remain preserved as provenance and must not override this section for current-state interpretation.

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

## Canonical post-Sprint 28 deployment/runtime reconciliation — 2026-08-18

For current deployment/runtime interpretation, this section supersedes older current-facing M7.5/updater-next-work wording retained below as historical qualification provenance.

Sprint 28 is **COMPLETE / IMPLEMENTED / PUBLISHED** and adds canonical source schema/identity capability without changing deployment authority. Canonical migrations are exactly #1–#8, but Technical Preview remains **`NO_SCHEMA_CHANGE`** and continues excluding application migrations from its governed release artifact. Sprint 26–28 credential verification, login/session establishment, and initial-password-enrollment routes are absent from Preview and Production and remain Local/Test/CI-only.

Production remains **`NO-GO / NOT AUTHORIZED`**. No Production migration execution, authentication activation, session activation, enrollment activation, real-user rollout, or persistence activation is authorized. Updater remains **`DISABLED / UNWIRED`** and the updater/release-control-plane contracts below remain separate from identity credential work. Durable persistence remains default-disabled with `ONEQAY_PERSISTENCE_ENABLED=false`.

The next logical governed identity concern is **First-Control-Principal Bootstrap Credential Foundation**. It requires a separately published bounded entry gate and does not grant deployment, schema, Preview, Production, updater, or release authority.

The authoritative detailed post-Sprint28 state is `docs/ai/AI_POST_SPRINT_28_CANONICAL_STATE.md`. Historical M7.5 and earlier deployment sections below remain provenance and must not override this section.

Attribution: **Lab | zefry**

## Canonical M7.5 lifecycle closure — 2026-08-17

For current Preview/runtime interpretation, this section supersedes the older current-facing M7.5 consolidation retained below as historical planning and pre-qualification state.

The bounded non-Production Technical Preview runtime qualification is now **CLOSED / EVIDENCE_COMPLETE / PUBLISHED** with **29 VERIFIED / 0 BLOCKED**. PR #129 published the final restore and backup/restore evidence; PR #130 published secure retirement of the disposable rehearsal environment without changing the evaluator.

The M7.5 evidence package verifies the bounded runtime, database connectivity/least privilege/transaction/migration boundary, connection/resource visibility, outbound DNS/HTTPS, environment-secret isolation, security boundary, Database Portability Contract conformance, observability logging, PHP CLI, scheduler/cron, rollback/deployment recovery, background/queue execution, database-backed tenant isolation, and isolated backup/restore rehearsal needed by the M7.5 mandatory catalog.

This closure does **not** authorize a new deployment or upgrade runtime. It does not create Production recoverability, universal migration rollback safety, Production Release authority, M7.6 authority, or Production readiness. `lifecycle_authority_created=false` remains true for the M7.5 evidence package.

M7.6, M7.7, Phase 0 Exit, Sprint 14, Release, and Production remain **NOT AUTHORIZED**; production readiness remains **NO-GO**.

The next candidate engineering direction is separately gated Secure Web Updater / release-control-plane architecture using trusted governed artifacts, immutable release directories, stable public bootstrap, private active-release pointer, shared runtime configuration, health gates, and rollback. No such implementation is authorized by this closure.

Attribution: **Lab | zefry**

## Canonical program-state consolidation — 2026-08-16

For current Preview/runtime interpretation, this section supersedes older M7.5/current-target wording retained below as historical planning and pre-qualification state.

The bounded non-Production Technical Preview on P1/cPanel has been materially exercised through governed publication/evidence up to PR #124. The canonical M7.5 evaluator is **26 VERIFIED / 3 BLOCKED**, outcome **BLOCKED / INCOMPLETE**, with `lifecycle_authority_created=false`.

Verified runtime evidence now includes the bounded web runtime, database connectivity/least privilege/transaction/migration boundary, connection/resource visibility, outbound DNS/HTTPS, environment-secret isolation, security boundary, Database Portability Contract conformance, safe observability logging, PHP CLI, scheduler/cron, bounded release rollback/deployment recovery, and short-lived Preview background/queue execution.

The remaining blockers are only:

- `ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED`;
- `ENGINE:TENANT_ISOLATION:PARTIAL`;
- `RUNTIME:BACKUP_RESTORE:PARTIAL`.

The observed rollback/recovery evidence is limited to the governed no-schema-change Technical Preview release rehearsal and must not be interpreted as database restore, universal rollback safety, Release, or Production readiness. Backup/export evidence is not successful restore evidence. M7.6, M7.7, Phase 0 Exit, Sprint 14, Release, and Production remain **NOT AUTHORIZED**; production readiness remains **NO-GO**.

Historical DEC-009/P1/P2 wording and prior qualification snapshots below remain preserved.

## Goals

Deployment harus reproducible, auditable, secure, recoverable, portable, dan tidak mengubah business logic antar environment atau antar officially qualified relational engine profile. Artifact yang sama dipromosikan; konfigurasi, secret, Infrastructure adapter, dan qualified engine profile membedakan environment tanpa mengubah Domain/Application business rules.

## Environments

| Environment | Purpose | Data |
|---|---|---|
| Local | Development | Synthetic |
| Test / CI | Automated validation | Synthetic by default |
| Preview | Production-like rehearsal | Synthetic or separately approved masked data |
| Production | Tenant operation | Real, classified; only after separate production authority |

Historical documentation may use `Staging` as a human-facing label. Under substantive DEC-009, the canonical Stage-1 runtime classification is `Preview`; `Staging` must be explicitly mapped to `Preview` rather than treated as an additional environment class.

Masked data requires an approved process and residual-risk review. Raw production/customer/credential/payment-sensitive data must not be copied into non-production merely for convenience. DEC-011 governs this privacy/data-handling boundary; it does not itself authorize production data processing.

Production access menggunakan least privilege, MFA, approval, audit, dan break-glass procedure.

## Configuration

- Environment variable atau secret manager sebagai sumber konfigurasi runtime.
- `.env` real tidak boleh di-commit.
- Config schema divalidasi saat startup/install.
- Default harus aman; missing critical config menyebabkan fail-closed.
- Feature flag memiliki owner, scope, expiry, audit, dan removal task.
- Relational engine-profile configuration berada di Infrastructure/Configuration boundary dan tidak boleh mengubah business use case.

## Deployment stages

### Stage 1 — Capability-Based Preview

Stage 1 mengikuti substantive DEC-009 **Capability-Based Staged / Hybrid Portability Model**. Environment dipilih berdasarkan pemenuhan capability, bukan kategori hosting.

DEC-005R merekonsiliasi database dependency DEC-009. Mandatory capability mencakup secure public-only document root, HTTPS, environment separation, externalized secrets, scheduler/cron, safe background-execution model where required, controlled file permission, **authorized and runtime-qualified relational engine profile under DEC-005R**, server-side session/cache capability, persistent private storage where required, backup plus verified restore capability, log/correlation access, health/readiness, resource visibility, trusted versioned release artifact, recoverable publication, dan rollback/recovery.

Engine/profile identity atau driver connectivity sendiri bukan runtime qualification. MariaDB 11.4 family adalah Stage-1 profile direction karena repository telah memiliki engine-family/version evidence tersebut, tetapi actual oneQay connectivity, security, limits, transaction semantics, tenant isolation, backup/restore, migration boundary, dan portability-contract evidence tetap harus dikualifikasi.

Preferred build model adalah **Build Once / Deploy Trusted Artifact**. Composer dan Node/build tooling dapat berada di trusted build environment dan tidak wajib tersedia pada runtime host jika artifact terverifikasi sudah membawa dependencies dan compiled assets yang diperlukan.

P1 Shared Hosting / cPanel tetap **CONDITIONAL / NOT SELECTED** dan hanya eligible jika seluruh mandatory Stage-1 capability terbukti. P2 Managed / Hardened VPS or Server adalah **FALLBACK EXECUTION CLASS** bila P1 gagal atau tetap unverifiable pada satu mandatory requirement. Tidak ada provider yang dipilih oleh DEC-009 atau DEC-005R.

Constraint hosting atau database engine tidak boleh masuk ke Domain/Application layer.

### Stage 2 — VPS / Managed Server Evolution

Tambahkan OS hardening, dedicated service account, firewall, automated provisioning/deploy, reverse proxy, process supervision, centralized logs, monitoring, offsite backup, dan restore rehearsal sesuai kebutuhan environment. Under DEC-009, a managed/hardened VPS/server may also serve as the P2 Stage-1 fallback execution class when P1 cannot satisfy mandatory capabilities; this does not change Domain/Application business logic.

### Stage 3 — Dedicated Server

Tambahkan capacity planning, storage/redundancy design, network segmentation, hardware monitoring, failover/DR decision, dan maintenance lifecycle.

### Stage 4 — Docker

Image immutable, non-root, minimal base, health check, read-only filesystem bila memungkinkan, externalized state, pinned dependency, vulnerability scan, SBOM, resource limit, dan image signing policy.

### Stage 5 — Cloud

Gunakan least-privilege IAM, private networking, managed secrets, managed database/storage sesuai ADR, autoscaling, multi-zone decision, cost guardrail, centralized observability, backup, dan DR.

### Stage 6 — Kubernetes

Hanya setelah ada platform ownership. Wajib resource request/limit, probes, disruption budget, network policy, secret integration, autoscaling criteria, policy enforcement, rollout strategy, cluster backup, dan workload isolation.

## Stage-1 runtime boundaries

Substantive DEC-009, reconciled by DEC-005R for the database-engine dependency, menetapkan boundary provider-neutral berikut:

- PHP baseline `>=8.2`; PHP CLI mandatory; exact supported minor/patch mengikuti authorized Laravel/release compatibility matrix.
- HTTPS, secure front controller, rewrite/routing, public-only document root, bounded request/upload/timeout controls, dan trusted proxy policy where applicable.
- An **authorized and qualified relational engine profile under DEC-005R**; MariaDB, MySQL, and PostgreSQL are profile directions, not automatic qualification.
- MariaDB 11.4 family is Stage-1 profile direction subject to actual runtime qualification.
- Least-privilege database credentials, externalized secrets, known connection limits, appropriate TLS, tenant isolation, backup/verified restore, and controlled migration boundary remain mandatory.
- Cron-equivalent scheduler capability; safe worker/background model where authorized workloads require it.
- Server-side Web/PWA session, application cache, and rate-limit/temporary-state capability; Redis is not mandatory for first bounded Stage 1.
- Persistent private storage, backup coverage, isolated restore, release metadata, health/readiness, logging/correlation, and recoverable rollback.
- Secrets remain environment-specific and externalized; no production `.env` or credential belongs in repository/client/logs.
- Domain/Application remain independent from cPanel, specific VPS/web-server/cache/queue/container/cloud providers, and relational-engine vendor identity.

DEC-005R establishes the **ZERO BUSINESS-CODE CHANGE** target between officially qualified relational engine profiles. It does not assert zero Infrastructure/configuration differences and does not itself implement database adapters, cross-engine CI, or DBME.

DEC-009 defines requirements only. It does not authorize infrastructure provisioning, DNS/certificate mutation, M7.5 execution, database/DBME implementation, migration execution, release, production promotion, or Sprint 14.

## Release artifact

Artifact harus memiliki version, commit SHA, build timestamp, compatibility metadata, checksum/signature, migration set when separately authorized, SBOM sesuai maturity, changelog, dan installation/update instruction. Build sekali, promote artifact yang sama.

Where durable relational persistence is later implemented, release compatibility metadata must identify supported/qualified engine profiles without changing the business-code artifact semantics.

## Deployment pipeline

1. verify clean/tagged source;
2. restore dependencies reproducibly;
3. lint, type, test, scan;
4. build artifact;
5. generate checksum/SBOM;
6. qualify target runtime and selected relational engine profile when separately authorized;
7. deploy to Preview only with deployment authority;
8. migrate and smoke test only when separately authorized;
9. approval;
10. backup and preflight production;
11. deploy/migrate only with separate deployment/migration authority;
12. health and business verification;
13. observe and close/rollback.

## Database migration and mobility

Migration memiliki preflight, compatibility window, lock/load estimate, backup, rehearsal, progress signal, verification, reconciliation, dan recovery. Destructive contract migration dipisahkan dari deploy yang menghapus compatibility.

DEC-005R adds a future **oneQay Database Mobility & Migration Engine — DBME** architecture direction for source/target profile discovery, compatibility analysis, dry-run, proven-equivalent physical adaptation, fail-closed unsafe/lossy conversion, controlled data movement, reconciliation, controlled cutover, source retention, and rollback only where genuinely safe.

Neither DEC-005R nor DEC-009 authorizes executable migration/DBME, SQL/DDL, live database connection, credentials, data movement, or Production database mutation.

## Deployment strategies

- Atomic directory/symlink release untuk compatible hosting.
- Equivalent controlled versioned artifact publication may be used where symlink/SSH is unavailable only when recoverability, path safety, release history, rollback, and auditability are proven.
- Rolling/blue-green/canary dipilih sesuai platform dan risk.
- Maintenance mode hanya bila zero-downtime tidak aman; harus memiliki status page/message dan bypass terkontrol.
- Feature flag bukan pengganti incomplete migration safety.
- Direct overwrite of live application files without a recoverable release boundary is unsupported.

## Health verification

Technical checks: process, config, selected relational engine profile/database, cache/queue, storage, external dependency, scheduler, error rate, latency. Business checks: login, tenant isolation, catalog read, controlled transaction smoke, audit, notification/payment callback sesuai environment and only when those capabilities are authorized.

Engine-profile health alone tidak membuktikan Database Portability Contract atau business correctness; qualification evidence remains separate.

## Rollback

Rollback decision memiliki owner dan threshold. Application rollback hanya dilakukan bila schema masih compatible. Jika data telah berubah, gunakan recovery/forward fix yang direhearsal. Semua rollback dicatat dan diikuti verification.

Cross-engine/DBME rollback hanya boleh dinyatakan tersedia apabila source retention, reverse compatibility, data integrity, and operational safety have been proven; otherwise fail forward/recovery strategy must be explicit before cutover.

## Backup and disaster recovery

- Encrypted offsite backup dan retention where appropriate.
- Restore runbook serta periodic rehearsal.
- Backup success alone is not recoverability evidence; successful restore evidence is required.
- Privacy retention/deletion semantics for backup data remain governed by DEC-011.
- RPO/RTO per capability remain separately governed by DEC-012.
- Selected engine-profile qualification must include applicable backup/restore evidence.
- Dependency inventory dan contact tree.
- DR exercise menghasilkan evidence dan task perbaikan.

## Observability and alerts

Pantau availability, error rate, latency, saturation, job backlog/failure, database/engine-profile dependency, storage, external dependencies, auth anomaly, tenant isolation denial, payment/reconciliation, updater, dan backup. Alert harus actionable, memiliki owner/runbook, serta tidak membocorkan data. Detailed retention remains separately governed where applicable by DEC-011.

## Cloudflare operations

DNS/SSL/wildcard/cache operation menggunakan scoped token, validation, idempotency, audit, retry, quota awareness, dan rollback. Cache purge harus sempit; global purge memerlukan approval sesuai risk. This section defines operational controls only and does not authorize Cloudflare or DNS mutation under DEC-009.

## Deployment Definition of Done

Artifact terversi, quality gate lulus, target runtime dan selected relational engine profile qualified where applicable, migration dan backup direhearsal where applicable, approval tersedia, deployment tercatat, health/business checks lulus, monitoring normal, rollback/recovery siap, dan changelog/release record diperbarui. Deployment Definition of Done does not itself grant deployment authority.

## Governance required-check workflow

The repository uses
`.github/workflows/governance-required-checks.yml` as a narrowly scoped
repository-governance control.

It produces the following protected-branch checks:

- `governance-validation`;
- `markdown-lint`;
- `secret-scan`.

This workflow:

- runs for pull requests targeting `main`;
- may be started manually through `workflow_dispatch` for diagnostics;
- uses read-only repository-content permission;
- does not use deployment environments;
- does not access deployment credentials or repository secrets;
- does not build application artifacts;
- does not publish packages or container images;
- does not execute database migrations;
- does not release or deploy oneQay.

A successful workflow run is governance evidence only. It is not deployment approval, release authority, Phase 0 exit approval, application source-code authority, or merge authority.

Application deployment remains unavailable until the relevant architecture, technology, security, testing, hosting, deployment, release, and lifecycle decisions receive separate Product Owner approval and execution authority. DEC-005R publication alone does not start M7.5 or deployment.

Attribution: Lab | zefry
