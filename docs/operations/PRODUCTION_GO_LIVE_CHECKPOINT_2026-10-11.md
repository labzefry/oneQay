# oneQay — CANONICAL REAL-BUSINESS PRODUCTION HANDOFF (2026-10-11)

**Author by Lab | zefry**  
**Purpose:** durable, source-anchored restart checkpoint. This document is a record, NOT a deployment authority, background training/fine-tune, or proof of a real CASH transaction.

## READ FIRST / TRUST
- Repository: private `labzefry/oneQay`, default branch `main`.
- Last verified application-source main **before this documentation-only handoff**: `409ac6b2bdb80d31fbfb9425ddbb413a278b97e6`. Verify live `main` whenever a new chat starts; a documentation-only squash merge may change the latest main SHA without changing the certified application archive.
- Latest certified **application release source** is exactly `409ac6b2bdb80d31fbfb9425ddbb413a278b97e6`, release `production-409ac6b2bdb8`. Never pair a different new head with its archive or rewrite RELEASE.json.
- Evidence hierarchy: current GitHub main/code + official GitHub Actions run/artifact; user-supplied cPanel screenshots/JSON; separately verified private operator package SHA; then historical handoff. If inconsistent, say so and follow present evidence. Runtime events not directly observed by GitHub must be labeled **operator-reported**. No guessing credentials, installed versions or successful sales.
- Older `docs/operations/MASTER_CONTINUATION_PROMPT_ONEQAY_2026-10-09.md`, `docs/operations/PRODUCTION_GO_LIVE_CANONICAL_CHECKPOINT_2026-10-09.md` and early `README.md` status are historical. This 2026-10-11 checkpoint supersedes them for recent GitHub and host evidence, not for architecture.
- Companion instructions: `docs/operations/MASTER_CONTINUATION_PROMPT_ONEQAY_2026-10-11.md` and `docs/operations/ONEQAYDEV_CPANEL_UPGRADE_HANDOFF_2026-10-11.md`.

## 1. VISION / NO SCOPE DRIFT
oneQay — **The Future of Intelligent Business Management** — enterprise-quality, production-grade, multi-tenant POS SaaS with modern Vue 3/Inertia/Vite and Laravel/PHP, MySQL, DDD/Clean Architecture/Modular Monolith First, deny-by-default tenant/organization/outlet/device boundaries, secure session + privileged TOTP, append-only/auditable business evidence, idempotency/replay control, correlated APIs, safe installation and governed updater, Iconify, attribution **Author by Lab | zefry**. Target p95 server <= 750ms (objective, not measured).
Real first business vertical slice: merchant sign-in, tenant scope, inventory and catalog, opening cash/register, active shift, **CASH-only** sale, durable receipt and idempotency, stock decrement, closing cash, Final Shift Close, reporting, recovery and rollback. No unimplemented external payment provider, no fake payment acceptance, no synthetic-only proof masquerading as real transactions. Do not spend cycles on unrelated modules or cosmetic redesign before making real CASH POS work.

## 2. VERIFIED MERGED GITHUB HISTORY (DO NOT RE-EXECUTE)
- PR #930 `5224fdb74a31b590ec34f50f83d6ef78073f7678` staging-target reconciliation.
- PR #931 `3bf8f9d763be986b3b36dfbcf2d6849edc8a110a` durable Production handoff.
- PR #932 `7e216f1ee9ea47f3e077632a7fa76b147c3b2f08` cPanel fixed-public dark operator kit.
- PR #933 `a5b7eb9749fa62d7d929e2b750e6a969362db6d0` business admission foundation.
- PR #934 `c874ab73e06877ad0b303a43b2a24c4067dc5da9` native real CASH POS Production source integration.
- PR #935 `ec4ac5e7526ff2303a4266c232ac607f7c027bc9` compatibility.
- PR #936 `630f37c93134aabf948c3109947995728f7dce4f` historical canonical docs.
- PR #937 `0400241bd83dafa90e8b647010c67a09aaec10e6` source-certified business release, v2 manifest, builder, validator, CI.
- **PR #938 SQUASH MERGED** 2026-10-10; exact engineering head `d5d57ea4e36106ff3c7db76b774dc71d881ac5ee`, merge commit `409ac6b2bdb80d31fbfb9425ddbb413a278b97e6`. Adds strict Production application configuration readiness and exact isolated oneQayDev CLI-only first-merchant provisioning foundation; bounded historical CI compatibility, scope tests. Owner exact-head authority recorded comment 6099455311.
- Last observed main before these new docs `409ac6b2bdb80d31fbfb9425ddbb413a278b97e6`, no OPEN PRs at observation. Reverify before acting.
- PR #938 full head reported **131 successful GitHub workflow jobs and 7 historical workflow-dispatch failures with zero jobs** at merge, GitHub mergeable clean. Do not relabel all CI as 100% green; inspect any required checks, especially old workflow-level errors. Post-merge main push `Production Business Release Certification` run **38066218900 SUCCESS**, but `Sprint253 Final Shift Close Post-Activation Historical Regression Successor Compatibility` run **38066218847 FAILED** from obsolete historical predecessor comparisons. Reconcile only if relevant; never disable genuine transactional/authorization checks.

## 3. OFFICIAL CURRENT SOURCE-CERTIFIED RELEASE (NOT A LIVE HOST)
- GitHub Actions run: **38066218900**, SUCCESS, main exact source `409ac6b2bdb80d31fbfb9425ddbb413a278b97e6`.
- Artifact name `oneqay-certified-business-production-409ac6b2bdb8`; artifact id **11674473139**; GitHub Actions ZIP SHA256 **4f6866003c598902d9b1a034bc59be8f43ed567e4807823c79d3c42d2bb83449**; archive inside `production-409ac6b2bdb8.tar.gz`, SHA256 **8ad336c2396fb5d6e95bcea80c574a305040c6ba4f570c9a2edea911186f3f3e**; manifest `production-409ac6b2bdb8.manifest.json`; checksum `production-409ac6b2bdb8.tar.gz.sha256`.
- Source-certified metadata `business_runtime_activation_ready=true` proves declared CI source gate only, **not** cPanel acceptance: `host_business_acceptance_verified=false`, `production_traffic_activation=NOT_AUTHORIZED`, `production_activation=NOT_AUTHORIZED`, `deployment_authority=NOT_GRANTED`.
- Legacy builder `tools/build-production-release.sh` and dark cPanel executor remain dark-only and must not be repurposed to pretend business activation.
- The official source archive has been downloaded and its SHA256 cross-checked against the standalone operator bundle. Presence of the archive in ChatGPT does NOT mean uploaded to cPanel.

## 4. REAL HOST / USER-REPORTED OPERATIONAL STATE
Provider Rumahweb; **cPanel File Manager + once-only Cron PHP CLI; NO SSH**, DNS Cloudflare. Exact isolated dev host:
- URL `https://oneqaydev.n07.my.id`;
- PUBLIC `/home/pekd7254/public_html/oneqaydev.n07.my.id`;
- PRIVATE `/home/pekd7254/oneqay-production-test`, with `releases/`, `shared/`;
- old installed release `/home/pekd7254/oneqay-production-test/releases/production-0400241bd83d`;
- previous health-only operator `/home/pekd7254/oneqay-production-test/oneqaydev-health-activation-0400241bd83d`.
- **PROTECTED existing staging:** `https://oneqay.n07.my.id`, public `/home/pekd7254/public_html/oneqay.n07.my.id`, private `/home/pekd7254/oneqay-staging`; existing operator `/home/pekd7254/oneqay-operator`. DO NOT alter, copy over, repoint or cut over staging merely because dev source is certified.
- The separate oneQayDev MySQL DB, private `shared/production-runtime.env`, `shared/private-bindings.json` were created earlier. User-reported 27 core migration receipts + 1 POS module receipt (#28), 43 tables, and one-time schema reconciliation COMPLETE; PHP CLI 8.3.35; earlier preflight 16/16 PASS. **NEVER rerun migration #27, module migrations, schema reconciliation, secret-preparation Cron, or remove any .started/.lock marker.**
- User-uploaded read-only `readiness-report.json`: **20/20 PASS**, source `0400241...`, config private mode 0600, no schema mutation. Snapshot was before health bridge.
- Later user cPanel screenshot `activation-result.json`: `state=HEALTH_ONLY_ACTIVE_NO_BUSINESS_TRAFFIC`, `release_id=production-0400241bd83d`, `source_commit=0400241bd83dafa90e8b647010c67a09aaec10e6`, `domain=oneqaydev.n07.my.id`, `runtime_env_binding=hardlink`, `placeholder_preserved=true`, `business_activation=NOT_AUTHORIZED`, `migrations=NOT_TOUCHED`, `staging=NOT_TOUCHED`.
- User browser screenshots: `/health/live` JSON `status=ok`, `service=oneqay-web`; `/health/ready` JSON `status=unavailable` for older `0400241...` code. The latter source rejected Production in historical `CriticalConfiguration.php`. PR #938 fixes that readiness contract, but **NO evidence that its new 409ac6b release has yet been installed or served by cPanel**.
- User explicitly reports that following *later* upgrade instructions have **NOT been performed**. Do NOT mark the 409ac6b host deployment, first merchant creation, real CASH sale or full business activation as completed. Current live source remains user-evidenced old health-only `0400241...` until later proof.

## 5. LOCAL cPANEL HEALTH-READY UPGRADE PACKAGE: PREPARED, NOT RUN
A one-shot **host-only, HEALTH-READY, BUSINESS-OFF** upgrade kit exists as local conversation artifact `oneqaydev-upgrade-409ac6b2bdb8.zip`, embedded certified archive and `upgrade.php`. On the 2026-10-11 model-side filesystem it was verified with `unzip -t`, `php -l`, and exact tar SHA. **Current actual ZIP checksum = 5b3c18bc6b72e3ddda0402035cce7e273cf0109b8cda4e5d0f0b32cfdfca1256**. Earlier PR #938 comments mention different package SHA values (9cbe4f9b... and 6cbcae...), based on earlier local snapshots. They are **obsolete for this locally checked file**; never silently assume a file with a different hash is this version. Never infer a sandbox download path in a new chat; use exact path only after verifying/recreating in that runtime.
- ZIP includes under `oneqaydev-upgrade-409ac6b2bdb8/`: `upgrade.php`, `README.txt`, `release-files.sha256`, the `.tar.gz`, official manifest and archive checksum.
- Current ZIP README and PHP source explicitly say **DOES NOT provision merchant or enable POS transactions**, despite an earlier PR #938 comment incorrectly describing a provisioning attempt. Source/README and current file win over that historical comment.
- Operator preflight requires existing exact prior health-only bridge and report, old unchanged source, new extracted archive under `releases/production-409ac6b2bdb8`, 0600 secrets, hashes/provenance, exact isolated host and source binding, and business flags still OFF. Takes private rollback snapshot of public bridge/build, adjusts source binding only with host-side private files, atomically installs health-only bridge/build, checks HTTPS live/ready, then reports `HEALTH_READY_BUSINESS_OFF`. Fail-closed if unqualified, rollback best effort on a failed switch, no schema mutation.
- This bundle is **not committed as a source file in the repo and not an official GitHub Release asset**; its provenance is recorded, and it should be re-audited/rebuilt if missing in new chat. Do not upload stale/unverified ZIP, do not ask for secrets in chat and do not assume it was run.
- Exact manual operator instructions are in `docs/operations/ONEQAYDEV_CPANEL_UPGRADE_HANDOFF_2026-10-11.md`.

## 6. CURRENT REAL COMMERCIAL GO-LIVE GAPS
1. **Deploy exact-source 409ac6b release safely to oneQayDev**, not the staging domain. Use verified prepared upgrade kit, user-only File Manager/one-shot Cron when host access cannot be connected, preserve backups, read `upgrade-result.json`; verify live HTTPS `/health/live` AND `/health/ready` plus exact serving release. Health-READY is NOT merchant-ready or DB transaction-ready.
2. **First merchant**: new command `oneqay:production:first-merchant --private-root=/home/pekd7254/oneqay-production-test` in merged source. Must be source/host/authority qualified and run only ONCE with locked 0600 private credentials and deterministic state. Existing legacy interactive command remains Local/Test/CI only. No repeated provisioning, no blind Cron now. The new source code is not deployed until step 1 succeeds. Credentials never pasted into chat/repo/log.
3. **Business traffic operator still missing/unevidenced**: safe separate Production-business cPanel activation, explicit exact-host source authority, transaction activation and traffic authority, rollback and session/MFA admission. DO NOT enable by flipping `.env` or changing `RELEASE.json`; ensure `ProductionBusinessRuntimeGate` legitimately authorizes source/host/database/session and business traffic. User GitHub merge authority is NOT blanket host activation authority.
4. **Prove true host CASH flow** with one authorized first merchant: login/TOTP when privileged, tenant/org/outlet/device scope, actual catalog and inventory, opening cash, shift start, real CASH sale, unique durable receipt & idempotent duplicate denial, stock decrement, shift close/Final Shift Close, reporting; deny wrong scope/non-CASH; verify restore/rollback and observability. Record sanitized evidence, receipt ids not personal data, actual host status. No pretend transaction by CI or synthetic-only fixture.
5. Only then business Production GO-LIVE on isolated dev; optional later separate, explicit authorized cutover to staging-occupied `oneqay.n07.my.id`. Do not assume dev Go-Live automatically grants staging cutover.

## 7. AUTHORITIES / LIMITS
- User grants broad repository engineering permission through **squash merge** for appropriate fixes, CI, tests, docs, owner-authority on exact PR head when justified. Verify current branch/race and green required checks; do not merge broken application code.
- Host File Manager/Cron, private credentials, production traffic flags, real-payment acceptance, DNS/cutover and customer data require separate applicable operational controls, authenticated connected tooling or bounded owner action with exact target, dry-run/evidence and rollback.
- Keep `ops/final-shift-close/STATE.json` authority separate from isolated oneQayDev; Final Shift Close ACTIVE & migration #27 EXECUTED in selected staging's historical canonical state do NOT imply oneQayDev merchant ready.
- All secrets must remain host-private. No user password, DB credentials, APP_KEY, private binding JSON, TOTP, token, or personal customer info in PR comments, prompt, logs or ChatGPT.
- Production updater remains `INACTIVE` until separately certified.
- Historical CI workflows may have obsolete immutable-horizon source envelopes; rectify narrowly and with exact path contracts, do not silence modern auth, tenant, POS or regression tests; no fake SUCCESS.
- Minimize fragmented PRs and expensive recurring audits. Focus one integrated, completion-oriented business deploy path, full automated test suite only when source changes, use exact previously qualified evidence, and report verified facts + ≤5-line progress checklist.

## 8. FILES AND OFFICIAL SOURCES TO READ WHEN RESTORING CONTEXT
`docs/operations/MASTER_CONTINUATION_PROMPT_ONEQAY_2026-10-11.md`, `docs/operations/ONEQAYDEV_CPANEL_UPGRADE_HANDOFF_2026-10-11.md`, `docs/operations/PRODUCTION_DEV_READINESS_2026-10-10.md`, `docs/operations/PRODUCTION_BUSINESS_RELEASE_SOURCE_CERTIFICATION_2026-10-09.md`, `docs/operations/PRODUCTION_COMMERCIAL_GO_LIVE_INTEGRATED_2026-10-09.md`; `docs/handbook/ENTERPRISE_VISION.md`, `PROJECT_MANIFEST.md`, `ARCHITECTURE.md`, `SECURITY.md`, `DATABASE.md`, `TESTING.md`, `DEPLOYMENT.md`; `apps/web/app/Infrastructure/Runtime/ProductionBusinessRuntimeGate.php`, `apps/web/app/Infrastructure/Bootstrap/ProductionMerchantProvisioningScope.php`, `apps/web/app/Console/Commands/ProductionFirstMerchantProvisionCommand.php`, `tools/build-production-business-release.sh`, `tools/validate-production-business-release-manifest.php`, `.github/workflows/production-business-release-certification.yml`. Other sources only official GitHub/Actions, operator-supplied cPanel evidence, official Laravel/PHP and PHP security docs if a new issue needs them. Do not fetch secrets or refactor based on untrusted site suggestions.

## 9. ONE-SENTENCE STATE
**Repository merchant Production readiness foundation and certified source READY; oneQayDev hosting only OLD HEALTH-ONLY source verified; NEW cPanel upgrade NOT EXECUTED; first merchant NOT VERIFIED; BUSINESS TRAFFIC NOT AUTHORIZED; actual CASH sale NOT VERIFIED; real-business Production GO-LIVE NOT ACHIEVED.**
