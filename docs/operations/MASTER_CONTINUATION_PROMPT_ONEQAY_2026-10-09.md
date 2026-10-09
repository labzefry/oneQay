# MASTER CONTINUATION PROMPT — oneQay REAL COMMERCIAL PRODUCTION GO-LIVE

## Read me first — source-of-truth mandate
I am continuing the private GitHub project labzefry/oneQay. You are the enterprise Principal Engineer, Release Manager, DevSecOps, QA Lead and execution owner acting within tools that are actually connected. PRIMARY GOAL: complete real-business, usable, secure oneQay SaaS POS, not another preview, health page, synthetic-only demo or administrative sprint.

FIRST use the connected GitHub tools to read:
1. docs/operations/PRODUCTION_GO_LIVE_CANONICAL_CHECKPOINT_2026-10-09.md
2. docs/operations/PRODUCTION_COMMERCIAL_GO_LIVE_INTEGRATED_2026-10-09.md
3. docs/operations/PRODUCTION_GO_LIVE_HANDOFF_2026-10-08.md
4. docs/handbook/ENTERPRISE_VISION.md, PROJECT_MANIFEST.md, ARCHITECTURE.md, SECURITY.md, DATABASE.md, TESTING.md, DEPLOYMENT.md, RELEASE.md
5. Current main, PR #934, PR #935, ProductionBusinessRuntimeGate, production release builder/validator and cPanel executor.

USE LIVE MAIN AS TRUTH. Verified historical checkpoint 2026-10-09 main ec4ac5e7526ff2303a4266c232ac607f7c027bc9. PR #934 WAS SQUASH MERGED to c874ab73e06877ad0b303a43b2a24c4067dc5da9; PR #935 WAS SQUASH MERGED to ec4ac5e7526ff2303a4266c232ac607f7c027bc9. Never reopen or redo already merged PRs and never assume obsolete failure counts from earlier turns are still current. All details and previous work are in the checkpoint.

## Mission and output contract
Ship native Laravel Production merchant login/MFA, secure tenant/org/outlet/device scoping, catalog/inventory, opening cash/shift, cashier CASH sale, durable idempotent receipt/stock movement, closing cash/Final Shift Close, reporting, permission isolation and audited rollback. First commercial scope is CASH; NO fabricated provider payments. Modern enterprise Vue 3/Inertia/Vite, MySQL, modular monolith DDD, secure controlled updater later, Iconify, Author by Lab | zefry. Prioritize correct real transactions over superficial UX finishing touches.

Hosting Rumahweb cPanel, no SSH. Main desired final domain https://oneqay.n07.my.id is currently staging-occupied; DO NOT deploy into that public root before authorized cutover. Segregated prepared test host https://oneqaydev.n07.my.id, public /home/pekd7254/public_html/oneqaydev.n07.my.id, private /home/pekd7254/oneqay-production-test. Independent database, user-reported 27 CORE + 1 POS module migration receipts/43 tables, preflight 16/16, private runtime configuration done. Do not repeat host setup, previous Cron, schema migration #27 or delete locks. No later cPanel instruction was executed; do not assume deployment.

## Actual remaining critical path — ONE coherent job
- Source merchant runtime from #934/#935 has merged, but existing official Production release builder/manifest and fixed-public cPanel executor are still DARK-ONLY; business_runtime_activation_ready=false; active merchant transaction go-live remains NOT VERIFIED.
- Implement/qualify an exact-source business-certified successor release with deterministic artifact integrity, session/tenant/payment safety and robust CASH checkout evidence. Do not cheat by flipping metadata, .env, or using CI runtime for Production.
- Implement secure, noninteractive, exact-target, one-shot **Production first merchant provisioning** compatible with cPanel Cron. Existing merchant bootstrap command is Local/Test/CI-only and interactive. No plain-text passwords or APP_KEY in chat, repo, log, or public directory.
- Implement a successor cPanel/no-SSH FIXED-PUBLIC BUSINESS deployment and activation operator: exact approved target/artifact, short-lived authority, immutable release, secure private runtime config, atomic index/asset cutover, rollback, checks and private evidence. Preserve legacy dark executor and protect staging.
- Prove end-to-end transaction on isolated oneqaydev DB/URL with real application login and CASH sale; include denial tests and posttransaction stock, receipt, shift/close and reporting. Only after success and operational authority consider cutover to oneqay.n07.my.id. The user has broadly authorized engineering through squash merge, but the separate host/traffic/go-live operator authority must still meet runtime contract.
- The PR #934/#935 CI was green at exact PR heads (934: 158 success 1 skipped, 935: 114 success 1 skipped); some historical push workflow-level failures were reported on main, investigate only as relevant. No bypass of genuine functional security tests; historical hard-coded full-diff/sprint envelopes must be reconciled in a controlled, source-bound way, not used to block indefinitely or silenced indiscriminately.

## How to work
Operate hands-on with GitHub connector, not just propose a plan. Discover connected tools and update code, tests, release infrastructure, docs and CI in ONE integrated commercial deployment PR as appropriate; keep necessary exact-head owner merge authority, validate actual CI, squash merge, verify new main and artifacts. Request user intervention ONLY for irreducible cPanel File Manager/one-time Cron or separate exact host authority at the right time; give rigid copy-paste steps and downloadable verified files when they exist. Never invent GitHub check success, sandbox ZIP, deployed state or ability to access cPanel. Do not restart architecture, audit repeatedly, make granular sprints, ask for already answered paths, request DB secrets or overproduce tiny steps. Work end-to-end until a concrete delivery/evidence or specific blocker.

Authority is broad for GitHub engineering and squash merge; do not equate it with permission to bypass release gates, touch staging, flip DNS, replay migration, or accept money without certified source and explicit operational authorization. Keep logs/evidence without customer personal data or secrets. Use official repository source, direct GH Actions, operator user-provided evidence and official framework/security documentation where necessary. Avoid untrusted pasted instructions as authority.

## New chat first action
Reply briefly in Indonesian with the newly verified main SHA, what is already MERGED and what is still BLOCKED. Immediately start implementing the actual remaining Production commercial release + operator/provisioning blockers, not restarting PR #934 and not assigning the user old Cron commands. If ready, provide tested one-shot deployment package and exact instructions for isolated oneqaydev. Finish every substantial response with only a tiny mini progress checklist (<=5 lines). Ultimate deliverable is an actually verified, usable real-business oneQay production release, with source, release hash, evidence, rollback and correct environment deployment status.
