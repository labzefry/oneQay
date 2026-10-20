# Business onboarding, workforce and shift incident recovery

Author by Lab | zefry. Continuation checkpoint: 20 October 2026.

## Baseline and current status

Production before this change: application source `302379bd7e474f082bdfada13d434802edcedc68`, operator/main `13f6ea3fb47231568ddc587fb4d40ab51a2163f8`. Deployment evidence remains in `SHIFT_CLOSURE_DEPLOY_20261020.md`. Do not replay its expired native grant. Staging is out of scope.

Implementation is present on branch `business-onboarding-recovery-20261020`. Local SQLite integration, HTTP controller tests, Vue type checking and production frontend build passed. MySQL CI, exact-head merge and a certified successor hosting update still require completion. This document does not claim production publication or real-cash acceptance.

## Changes and user entry points

- Fixed `POS_SHIFT_CLOSING_CASH_REJECTED`: the controller sorted submitted field names but compared them with an unsorted allowed-field list, rejecting every valid request. HTTP regression now covers actual controller input, ordering, extra-field rejection and retry deduplication. Existing cash-count, independent approval and variance-review rules remain enforced.
- POS settings tab styles now outrank the generic merchant-button rule, with visible text, selected state, hover and keyboard focus.
- People workspace: built-in Cashier, Manager, Accounting, Warehouse, Kitchen and Sales job descriptions; owner can add tenant-specific job descriptions. A job title is descriptive and never grants permissions.
- Employee creation/edit can provision a linked cashier with canonical initial password enrollment, POS role and protected lock PIN. Existing cashiers gain employee-directory links through one additive module migration. No existing identity, password, PIN, role or session is replaced by migration. Deactivating a linked employee disables their cashier account and revokes its server sessions.
- Control plane: incident shift recovery for a selected business and the privately bound initial merchant. Requires platform ownership, existing MFA/step-up middleware, incident reason, reviewed digest and acknowledgment that offline queues were secured. It releases the active register slot, preserves sales/cash evidence, writes a distinct immutable recovery receipt, invalidates old register leases and sessions, and queues an owner notice when the managed business has an email. It does not fabricate cash or canonical final-close evidence. No production shift is reset by deployment.
- Control plane SMTP: test recipient button queues a test through the normal outbox. Three tests/minute route limit, owner step-up and idempotent operation key. Reported state is queued; SMTP acceptance and confirmed delivery remain separate.
- Public registration queues email verification immediately and creates no tenant authority until proof and canonical password enrollment. Successful verification activates directly without manual approval. Resend rotates the token, expires previous links, uses generic responses and rate limits, and cannot reactivate active/suspended/cancelled accounts. Activation replay only accepts the same already-enrolled password during the brief lost-response window and cannot change credentials.
- Suspension invalidates outstanding verification tokens, revokes sessions and queues the stated reason to the business owner atomically. It does not delete business data.
- Public ID/EN copy explains the current free application offer, third-party costs, account setup, role separation, offline versus synced report freshness, copyright, acceptable use, suspension, incident handling and privacy responsibilities. No automatic billing was added.

## Schema and deployment contract

New forward-only module: `database/module-migrations/business/2026_10_20_000001_create_workforce_accounts_and_shift_recovery.php`.

Adds three tables: `oneqay_business_job_titles`, `oneqay_business_staff_accounts`, `oneqay_pos_shift_recoveries`. Existing 27 foundation migrations and financial tables are not altered. Baseline hosting has 35 migration receipts and 84 tables; expected successor has 36 receipts and 87 tables. The deployment successor must explicitly authorize this additive migration and preserve production identities, SMTP secrets, financial records and tenant role assignments. Do not reuse the previous zero-migration verifier unchanged.

Existing owner-authored CMS revisions and drafts must not be silently destroyed. Defaults update naturally when a page has no published override; any deployed content refresh must preserve prior revisions and image references and state what was published. Do not claim a published custom page changed merely because source defaults changed.

## Validation and next actions

Local: `tests/business-onboarding-recovery.php` 143 assertions including inherited suites; `tests/website-cms-registration.php` 71 CMS assertions plus inherited mail suite; `npm run type-check`; `npm run build`. Production build has the existing large-bundle advisory.

CI workflow `business-onboarding-recovery.yml` runs SQLite and MySQL 8.4. Source certification runs the new integration suite and CMS suite before packaging. Exact successor compatibility is bounded by base commit, branch, path set and file digests; historical executable regressions remain enabled.

Before publishing: finish CI, record actual exact merged source and certification artifact/run, build a new additive-module operator against the active 302379bd7e47 predecessor hashes, upload in cPanel, obtain a fresh time-bounded exact-packet native grant, run/update only the application's cron, verify report and runtime, archive previous releases with existing retention. Record concrete outcome in this document or a linked deployment note. Never label failed/queued tests, mail or real-cash pilot as complete.

Follow-up hardening: Windows devices with unsynced old operations after incident recovery require explicit reconciliation; never delete their SQLite queue. Production incident recovery is an owner action, not an automated acceptance test. Permanent account/data deletion is a separate reviewed operation with retention/export considerations; no destructive delete button is introduced here.

## Legal source references

Plain-language notices preserve mandatory rights and provider duties; they are not blanket liability waivers. Applicable factual law sources consulted: [UU 27/2022, official Komdigi text](https://jdih.komdigi.go.id/produk_hukum/view/id/832/t/undangundang%2Bnomor%2B27%2Btahun%2B2022) and [UU 28/2014, official Ministry of Finance JDIH](https://jdih.kemenkeu.go.id/dok/uu-28-tahun-2014). Existing proprietary root LICENSE and third-party licenses remain in force.
