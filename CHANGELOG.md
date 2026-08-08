# Changelog

Semua perubahan penting oneQay dicatat di dokumen ini. Format mengikuti Keep a Changelog dan versioning produk akan mengikuti Semantic Versioning setelah release baseline disetujui.

## [Unreleased]

### Added

- Added `docs/handbook/DEC_001_DECISION_RECORD.md` as the bounded repository record for the Product Owner substantive DEC-001 MVP Scope and Non-Scope decision, including the approved POS Core Transaction & Outlet Operations slice, actors, journeys, bounded dependencies, explicit non-scope, outcomes, open items, and no-implementation authority boundary.
- Added `docs/handbook/DEC_000_DECISION_RECORD.md` as the bounded repository record for the Product Owner substantive DEC-000 Product Vision and Decision Rights decision, including D-000-01 through D-000-06 dispositions, Product Owner identity, PV-001 through PV-006 disposition, Issue #2 closure semantics, provenance, supersession path, and explicit no-implementation boundary.
- Added M6 canonical Enterprise Vision candidate at `docs/handbook/ENTERPRISE_VISION.md`, defining oneQay directionally as an **Enterprise Intelligent Business Management Platform** while explicitly separating Product Vision, Capability Map, Architecture Direction, Delivery Roadmap, and Implementation Authority.
- Added the high-level Enterprise Capability Map covering Core Business Platform, Platform Capabilities, Extensibility, AI Platform, and Channels without promoting any capability to implementation authority.
- Added conceptual product evolution stages E0 Foundation, E1 Core Transaction Platform, E2 Business Management, E3 Enterprise Management, E4 Intelligence, and E5 Ecosystem; these are directional stages, not release commitments.
- Added canonical brand rule requiring current/future product identity to use exactly **oneQay**, while preserving immutable repository identifiers and historical evidence.
- Added `.github/workflows/governance-required-checks.yml` as the narrowly scoped producer for the stable `governance-validation`, `markdown-lint`, and `secret-scan` protected-branch checks; recovery was later published through PR #38 as `a59521ad31d8153198bb80dd7985142cb21e3775`.
- Recorded GOV-043 as Done after required-check recovery publication and subsequent protected-branch use.
- Recorded the active `main-protected-governance` ruleset alignment to the three stable job-level checks and removal of obsolete `actions/checkout-v4` and `pull_request` contexts.
- Recorded PR #38 required-check recovery and PR #35 conflict recovery as published repository facts; neither publication grants ADR acceptance, Phase 0 exit, final/business application authority, deployment, or release authority.
- Added seven **Proposed** Technical Preview ADRs for B1 Laravel/PHP, F1 Vue/Inertia/Vite, D1 MySQL-compatible shared tenancy, A1 first-party session/TOTP, PAY-1 synthetic cash-only, OFF-1 online-only, and conditional P1 deployment with P2 fallback hypothesis.
- Added Proposed synthetic-data classification, threat model, recovery plan, incomplete shared-hosting capability assessment, and Not Ready Phase 0 preview exit evidence under Issue #23.
- Recorded TEN-1, REC-1, SLO-1, and DATA-1 as Technical Preview selections without accepting ADRs or granting source-code authority.
- Preserved Phase 0 as In Progress, final/business application implementation as Blocked, P1 as Unverified, GD-007 as Proposed, and JRN-003/JRN-013 as unresolved.
- Recorded that PR #24 technical merge and Issue #23 closure do not constitute substantive approval, ADR acceptance, Phase 0 exit, or general application-skeleton authority.
- AI Engineering Handbook version 1.0 baseline.
- Project orientation, identity, target platforms, and GitHub SSOT governance.
- Canonical project manifest with decision states and initial risk register.
- Permanent AI constitution and stop conditions.
- Modular Monolith, Clean Architecture, DDD, API-first, multi-tenant, event-ready architecture baseline.
- Product and infrastructure roadmap from shared hosting through Kubernetes.
- Cross-platform coding standards.
- Database, API, security, deployment, testing, and UI/UX governance.
- Secure Installer Wizard and Auto Updater specifications.
- Contribution, release, task, and change-management processes.
- GitHub issue and pull-request templates.
- ADR template and documentation-area guidance.
- Per-document completion report and continuation/review prompts.
- Phase 0 governance and discovery kickoff plan with explicit no-code gate.
- Proposed Product Vision and Decision Rights for Phase 0, including human approval boundaries, decision lifecycle, escalation rules, measurable outcome directions, and workshop acceptance gate.
- Proposed Stakeholder and Actor Map covering platform/tenant boundaries, human and system actors, separation of duties, engagement hypotheses, validation methods, and downstream discovery handoff.
- Proposed Current Process and User Journeys covering discovery evidence, critical operational journeys, actor/system handoffs, pain-point hypotheses, invariants, exception/recovery needs, and validation backlog.
- Proposed Domain Event Storming covering event/command hypotheses, business policies, tenant-safe envelopes, critical invariants, aggregate/context candidates, consistency questions, hotspots, and workshop validation.

### Changed

- Recorded Product Owner substantive **DEC-001 — MVP Scope and Non-Scope: APPROVED** on baseline `17f156b9861972b4924a5ed01bfabd5a1a79461a`, verified tree `33241c18a1b7da2efc7dd2889c13c25c6e8526d5`, with the first bounded delivery slice **POS CORE TRANSACTION & OUTLET OPERATIONS**.
- Recorded DEC-001 actors, MVP journeys JRN-004/JRN-005/JRN-006/JRN-007/JRN-010, bounded dependencies JRN-001/JRN-002/JRN-003/JRN-008/JRN-011/JRN-012, explicit deferred/non-scope items, outcomes Transaction Trust / Operational Efficiency / Inventory Accuracy, Secure Tenant Isolation guardrail, and Recoverability release/reliability gate.
- Preserved JRN-003 and JRN-013 as unresolved, GD-005/GD-006/GD-007 as Proposed, Phase 0 as In Progress, Sprint 14 as Not Authorized, production readiness as NO-GO, DEC-002 through DEC-012 as independently gated, ADR-001 through ADR-007 as unaccepted, and all application/business implementation, SQL/schema/migration, production-DB, deployment, and release authority as NOT GRANTED by DEC-001.
- Recorded Product Owner substantive **DEC-000 — Product Vision and Decision Rights: APPROVED** on baseline `792b2dc30636bc53baa7d66b43cf2dab4a348dd4`, tree `08f03b895d5e2ae7ca402e9866384990e126add3`, canonical owner artifact blob `843544b9e31dd4c47638b88dd204f4e594295df4`, and readiness artifact blob `b493a5d66edc1bbffab0126bdacf2ca1ce14fa8f`.
- Promoted **GD-003 Product vision and decision rights** from Proposed to Approved only as the repository representation of DEC-000; this approval is product/discovery governance and not implementation authority.
- Recorded D-000-01 Product Vision and Mission as Approved, D-000-02 Product Principles and Outcome Direction as Approved, D-000-03 segment/actor/problem/scope/deferred/non-goal hypotheses as accepted Phase 0 discovery hypotheses and constraints, D-000-04 decision-rights matrix as Confirmed, D-000-05 open-decision disposition as Approved, and D-000-06 implementation-authority boundary as Confirmed.
- Recorded `labzefry` as accountable Product Owner for DEC-000, with no Product Owner delegates currently recorded; PV-001 is satisfied for DEC-000 identity while PV-002 through PV-006 remain **OPEN / NOT RESOLVED**.
- Marked GOV-024 and DEC-000 Done only as representation of the completed substantive decision and its bounded record; preserved Phase 0 In Progress, Sprint 14 Not Authorized, ADR-001 through ADR-007 and GD-007 Proposed, JRN-003/JRN-013 unresolved, final/business/production implementation blocked, and production readiness NO-GO.
- Recorded GOV-051 Product Owner substantive decision as **APPROVED**: oneQay's binding long-term Enterprise Vision is **Enterprise Intelligent Business Management Platform**, approved on verified repository baseline `762149757e4bc1fa79cc16bc4761f4147be0f7ea` against canonical artifact blob `bb1cace72a6fdb359e15e22467443d9f3916c336`.
- Promoted only the Enterprise Vision substantive decision from Proposed to Approved; capability-map presence, E0–E5 stages, and the approval itself remain product direction rather than implementation authority.
- Historical GOV-051 boundary at its decision point preserved Phase 0 as In Progress, Sprint 14 as Not Authorized, production readiness as NO-GO, GD-003/GD-007 and ADR-001 through ADR-007 as Proposed, JRN-003/JRN-013 as unresolved, and all SQL/migration/deployment/release/production-DB boundaries unchanged; GD-003 was later separately Approved through DEC-000.
- Corrected current-state wording so PR #70 post-publication reconciliation and PR #71 checkpoint-semantics closure are treated as already published rather than pending future work.
- Recorded M6 — Enterprise Vision Canonicalization as **PUBLISHED / PUBLICATION COMPLETE** through PR #69: exact source head `e6a3345b09a6b270ac7e09abd78c6356f426e363`, source tree `567df997bae70090b19465c75e4cc3b1e23b6579`, published commit `0b7b28028966ac38af0f32960054210c3a083916`, published tree `567df997bae70090b19465c75e4cc3b1e23b6579`, with source tree equal to published tree.
- Recorded PR #69 lifecycle evidence: independent reviewer `zefriansyah` APPROVED the exact source head; required technical checks passed; Product Owner READY and MERGE authorities were separately recorded; `product-owner-merge-authority` passed before squash merge.
- Reconciled A-09 at the canonical representation/publication level through PR #69 while preserving the substantive Enterprise Vision decision as **Proposed** pending separate explicit Product Owner approval.
- Reconciled A-10 for current/future-facing product identity: canonical product name is **oneQay**; immutable GitHub identifiers and historical quoted evidence remain preserved.
- Clarified that M6 publication does not authorize Sprint 14, application/business source implementation, database/schema implementation, SQL/migration execution, deployment, release, ADR/GD promotion, JRN resolution, or production-readiness transition.
- Recorded M5.3 — Governance & Program State Synchronization as **PUBLISHED / COMPLETE** through PR #68, source head `aa799e657070a7d3283110a73a411f54a73b972c`, published commit `e45f5b4c0f143abc6e255e4e8550bf3504348aae`, and identical source/published tree `e2bc0505f5abd98a7283b3cd3cd2c4c02ef23ece`.
- Recorded A-06, A-07, and A-08 as reconciled through M5.3 publication and advanced the active program to M6 — Enterprise Vision Canonicalization.
- Historical pre-publication state recorded that A-09 began as an M6 bounded candidate; PR #69 later completed the representation/publication lifecycle without promoting the substantive Enterprise Vision decision from Proposed to Approved.
- Historical pre-publication state recorded A-10 product-name capitalization reconciliation; PR #69 later completed current/future-facing canonical normalization to `oneQay` while preserving immutable historical evidence.
- Recorded M5.1 — Canonical State Reconciliation as **PUBLISHED / COMPLETE** through PR #66 and published commit `153a33a4a2b5edb4a31285eca7d3491f9589b778`.
- Recorded M5.2 — CI & Lifecycle Control Hardening as **PUBLISHED / ENFORCEMENT COMPLETE** through PR #67, published commit `512344d0497787c729242cb1fd2d7d02ecfc40c2`, and published tree `0f0af1c1acab208c704fbdf05b19014127abddbb`.
- Recorded A-03 and A-05 as resolved and the protected default-branch required contexts as `governance-validation`, `markdown-lint`, `secret-scan`, `php-foundation-regression`, and `product-owner-merge-authority`.
- Started M5.3 — Governance & Program State Synchronization to reconcile A-06 Phase 0 semantics, A-07 ROADMAP/TASKS state, and A-08 product metadata/attribution only; A-09 Enterprise Vision remained reserved for M6 at that historical point.
- Clarified that **Phase 0 — In Progress** is a governance/discovery program state and does not negate published bounded Platform Foundation source through Sprint 12 and Sprint 13.
- Clarified that **application implementation Blocked** now means final/business/production application implementation; the clarification grants no new source-code authority and Sprint 14 remains Not Authorized.
- Separated canonical product/development attribution **Lab | zefry** from AI engineering-tooling metadata; collaboration tooling remains governed by `AI_CONSTITUTION.md` and is not product authorship attribution.
- Preserved historical lifecycle discrepancies without retroactive normalization, while keeping production readiness NO-GO and prohibiting M5.3 deployment, release, SQL execution, migration execution, or production database modification.
- Standardized the oneQay engineering workflow to ChatGPT and GitHub only.
- Replaced external-AI review prompts with independent ChatGPT review prompts.
- Clarified that product AI Assistant decisions remain separate from the engineering collaboration model.
- Reconciled Handbook 1.0 task and delivery-gate statuses with the approved and merged PR #1 evidence.
- Started Phase 0 Product Vision and Decision Rights under GitHub Issue #2 while keeping application implementation blocked.
- Started Phase 0 Stakeholder and Actor Map under GitHub Issue #4 without promoting Product Vision and Decision Rights from Proposed.
- Started Phase 0 Current Process and User Journeys under GitHub Issue #6 without promoting upstream discovery hypotheses.
- Started Phase 0 Domain Event Storming under GitHub Issue #8 while preserving unresolved JRN-003/JRN-013 review findings as blockers.
- Corrected the Proposed Domain Event Storming under Issue #10 after Product Owner approval of five PR #9 review findings: payment finality, goods-receipt/stock causation, support-access revocation, sale cancellation, and subscription/entitlement coverage.
- Kept GD-007, all domain hypotheses, JRN-003, JRN-013, technology decisions, and application implementation gates unchanged by the correction approval.
- Corrected four approved PR #11 audit findings under Issue #12: sale-level payment sufficiency, stable receipt-line idempotency across versions, support closure after proven access termination, and cancelled-sale payment recovery.
- Recorded that PR #11 merge and Issue #10 closure do not constitute substantive approval; GD-007 and application implementation remain Proposed/Blocked respectively.
- Recorded the Product Owner's limited post-merge ratification of the four corrections at exact PR #13 head `e4a3b7ba9f94b429b6e50e2856a11b953a336ac0` without approving GD-007.
- Opened governance reconciliation under Issue #14 after PR #13 was merged and Issue #12 was closed before approval evidence was recorded; Issue #12 was reopened and Phase 0/application gates remain unchanged.
- Recorded recurrence when PR #15 head `4ad28a4e8ad5740e6f55f4563a32d09e7bba631a` was merged as `b34f99ea3c5471cfcd6ae82bc6abeb9a3e78441a` and Issue #12/#14 were closed before exact-head approval and completion evidence; both issues were reopened.
- Proposed governance-control hardening under Issue #16: exact-head approval evidence, changed-head invalidation, separate merge authority, pre-merge state re-fetch, issue closure gates, PR checklist, and repository-protection verification without changing GD-007 or application gates.
- Recorded the PR #17 recurrence at exact head `aaa7510759925c0c62ba5424c93e2356d18c9d3d`, merged as `82b45820a67c274bd96866bb048f3f320d6cbe70` without exact-head approval, review, checks/deferral, protection evidence/risk acceptance, or separate merge authority; Issue #12/#14/#16 were reopened and Issue #18 now tracks reconciliation.
- Made direct repository-protection/ruleset evidence a blocking precondition before ready transition, merge, and governance issue closure for High/Critical governance changes, with a fully specified formal risk-acceptance path when direct evidence is unavailable.
- Preserved PR #17 as unratified, GOV-029/GOV-030/GOV-031 as Review, GD-007 and all Domain Event Storming hypotheses as Proposed, Phase 0 as In Progress, application implementation as Blocked, and JRN-003/JRN-013 as unresolved blockers.
- Recorded recurrence when PR #19 head `483fcf3dbe2c5a418ea7aad97bcfcbf26124b631` was merged as `f68c01e85660409fac6c4e85f2f6545dca08f1d7` without required evidence or lifecycle authority and Issue #12/#14/#16/#18 were closed without completion evidence; all four issues were reopened and Issue #20 tracks reconciliation.
- Required a separate Product Owner formal-risk-acceptance approval URL and exact-head decision statement; general content/reviewer approval cannot serve as risk acceptance.
- Clarified that formal risk acceptance only substitutes for the scoped direct protection-evidence requirement and never supplies ready, merge, release, status-promotion, or issue-closure authority.
- Recorded that PR #25 exact head `ca2157096b310b114203d919cb8182e55a6fa5f9` was changed from draft and merged as `93c8b8d4d8dae399c0d3f758c50460cf086e2322` without available separate exact-head lifecycle authority.
- Recorded that Issue #23 was closed as completed before its evidence, acceptance, hosting, ADR, recovery, and Phase 0 preview-exit gates were complete.
- Recorded that PR #25 exact head had no published commit status or GitHub Actions workflow run; its documented local validation remains distinct from independent GitHub check evidence.
- Clarified that the PR #25 technical merge and Issue #23 closure do not constitute substantive approval, ADR acceptance, Phase 0 exit, source-code authority, ratification, or completion evidence.
- Preserved Phase 0 as In Progress, application implementation as Blocked, Phase 0 preview exit as Not Ready, P1 as conditional and Unverified, ADR-001 through ADR-007 and GD-007 as Proposed, JRN-003/JRN-013 as unresolved, and all missing hosting evidence as Pending/Not supplied/Unverified.
- Recorded that PR #26 original base `93c8b8d4d8dae399c0d3f758c50460cf086e2322` and exact head `63223b9b856bd67e739651a1e23cc071971998c3` were technically merged as `294fe24381e88b61701868567cda4be532640ab0` after the PR changed from draft despite its body limiting authority to draft creation.
- Recorded that no separate exact-head ready or merge authority, review submission, PR comment, published commit status, or GitHub Actions workflow run was available for PR #26.
- Recorded the Product Owner post-merge decision approving only the accuracy of PR #26 corrective content, without retrospective lifecycle authority or ratification of PR #26 lifecycle action.
- Clarified that the PR #26 technical merge does not ratify PR #25, validate Issue #23 closure, accept any ADR, approve Phase 0 exit, grant source-code authority, complete GOV-034/GOV-035, or provide completion evidence.
- Preserved Phase 0 as In Progress, application implementation as Blocked, Phase 0 preview exit as Not Ready, P1 as conditional and Unverified, ADR-001 through ADR-007 and GD-007 as Proposed, JRN-003/JRN-013 as unresolved, PAY-1/OFF-1/TEN-1/REC-1/SLO-1/DATA-1 as Proposed, and hosting evidence as Pending/Not supplied/Unverified.
- Recorded that PR #27 original base `294fe24381e88b61701868567cda4be532640ab0` and exact head `c6adb55a9a6cd2ebedd78668ccaf5fd64c041d94` were technically merged as `3c4bcfe9797a3ae7f4deb124568ef361d74125e5` after the PR changed from draft despite its body limiting authority to draft creation and requiring the PR to remain draft.
- Recorded that no separate exact-head ready authority, separate exact-head merge authority, review submission, PR comment, published commit status, or GitHub Actions workflow run was available for PR #27.
- Recorded the Product Owner post-merge exact-head decision approving only the accuracy of PR #27 three-file corrective content, without retrospective lifecycle authority or ratification of PR #27 lifecycle action.
- Clarified that the PR #27 technical merge does not ratify PR #26 or PR #25, validate Issue #23 closure, accept any ADR, approve Phase 0 exit, grant source-code authority, complete GOV-034/GOV-035/GOV-036, or provide substantive approval or completion evidence.
- Preserved Phase 0 as In Progress, application implementation as Blocked, Phase 0 preview exit as Not Ready, P1 as conditional and Unverified, ADR-001 through ADR-007 and GD-007 as Proposed, GOV-034/GOV-035/GOV-036 as Review, JRN-003/JRN-013 as unresolved, PAY-1/OFF-1/TEN-1/REC-1/SLO-1/DATA-1 as Proposed, and hosting evidence as Pending/Not supplied/Unverified.
- Recorded that PR #28 original base `3c4bcfe9797a3ae7f4deb124568ef361d74125e5` and exact head `0597d784f63cf6d5967cedae17ca8d0b5a2e4dc9` were technically merged as `1009af84ec0ee7d7731890e379dde25279280c3a` after the PR changed from draft despite its body limiting authority to draft creation and requiring the PR to remain draft.
- Recorded that no separate exact-head ready authority, separate exact-head merge authority, review submission, PR comment, published commit status, or GitHub Actions workflow run was available for PR #28.
- Recorded the Product Owner post-merge exact-head decision approving only the accuracy of PR #28 three-file corrective content, without retrospective lifecycle authority or ratification of PR #28 lifecycle action.
- Clarified that the PR #28 technical merge does not ratify PR #27, PR #26, or PR #25, validate Issue #23 closure, accept any ADR, approve Phase 0 exit, grant source-code authority, complete GOV-034/GOV-035/GOV-036/GOV-037, or provide substantive approval or completion evidence.
- Preserved Phase 0 as In Progress, application implementation as Blocked, Phase 0 preview exit as Not Ready, P1 as conditional and Unverified, ADR-001 through ADR-007 and GD-007 as Proposed, GOV-034/GOV-035/GOV-036/GOV-037 as Review, JRN-003/JRN-013 as unresolved, PAY-1/OFF-1/TEN-1/REC-1/SLO-1/DATA-1 as Proposed, and hosting evidence as Pending/Not supplied/Unverified.
- Recorded that PR #29 original base `1009af84ec0ee7d7731890e379dde25279280c3a` and exact head `54a5773c3ab65a33e35ef2646089727490a0ff8d` were technically merged as `f55d86f1a3d89a6bcbbbcf7800851b9c61f8c047` after the PR changed from draft despite its body requiring the PR to remain draft.
- Recorded that the repository-native operational authority comment for PR #29 explicitly excluded draft-to-ready, merge, auto-merge, ADR acceptance, Phase 0 exit, source-code implementation, release, deployment, and status promotion.
- Recorded that no separate exact-head ready authority, separate exact-head merge authority, review submission, published commit status, or GitHub Actions workflow run was available for PR #29.
- Recorded the Product Owner post-merge exact-head decision approving only the accuracy of PR #29 three-file corrective content, without retrospective lifecycle authority or ratification of PR #29 lifecycle action.
- Clarified that the PR #29 technical merge does not ratify PR #28, PR #27, PR #26, or PR #25, validate Issue #23 closure, accept any ADR, approve Phase 0 exit, grant source-code authority, complete GOV-034/GOV-035/GOV-036/GOV-037/GOV-038, or provide substantive approval or completion evidence; all substantive governance statuses remain unchanged.
- Recorded that PR #30 original base `f55d86f1a3d89a6bcbbbcf7800851b9c61f8c047` and exact head `f3703650f98e5d6abfdb21d9b67ac7c5567ea9f6` were technically merged as `54bc51a7a150394748dcc5f6a2fb8e376206feba` after the PR changed from draft despite its body requiring the PR to remain draft.
- Recorded that the repository-native operational authority comment for PR #30 explicitly excluded draft-to-ready, merge, auto-merge, approval review, ADR acceptance, Phase 0 exit, source-code implementation, Issue #23 state change, governance-task completion, release, deployment, and status promotion.
- Recorded that no separate exact-head ready authority, separate exact-head merge authority, review submission, review thread, published commit status, or GitHub Actions workflow run was available for PR #30.
- Recorded the Product Owner post-merge exact-head decision approving only the accuracy of PR #30 three-file corrective content, without retrospective lifecycle authority or ratification of PR #30 lifecycle action.
- Clarified that the PR #30 technical merge does not ratify PR #29, PR #28, PR #27, PR #26, or PR #25, validate Issue #23 closure, accept any ADR, approve Phase 0 exit, grant source-code authority, complete GOV-034/GOV-035/GOV-036/GOV-037/GOV-038/GOV-039, or provide substantive approval or completion evidence; all substantive governance statuses remain unchanged.
- Recorded that PR #31 original base `54bc51a7a150394748dcc5f6a2fb8e376206feba` and exact head `10b5179b16c104e1877153b066e96a937ece9c9b` were technically merged as `67059e563de26cee26cefd64cf9e7d5c4436ffc6` after the PR changed from draft despite its body requiring the PR to remain draft.
- Recorded that the repository-native operational authority comment for PR #31 explicitly excluded draft-to-ready, merge, auto-merge, approval review, ADR acceptance, Phase 0 exit, source-code implementation, Issue #23 state change, hosting-evidence completion, governance-task completion, ratification, release, deployment, and status promotion.
- Recorded that no separate exact-head ready authority, separate exact-head merge authority, review submission, review thread, published commit status, or GitHub Actions workflow run was available for PR #31.
- Recorded the Product Owner post-merge exact-head decision approving only the accuracy of PR #31 three-file corrective content, without retrospective lifecycle authority or ratification of PR #31 lifecycle action.
- Clarified that the PR #31 technical merge does not ratify PR #30, PR #29, PR #28, PR #27, PR #26, or PR #25, validate Issue #23 closure, accept any ADR, approve Phase 0 exit, grant source-code authority, complete GOV-034/GOV-035/GOV-036/GOV-037/GOV-038/GOV-039/GOV-040, or provide substantive approval or completion evidence; all substantive governance statuses remain unchanged.
- Recorded that PR #32 original base `67059e563de26cee26cefd64cf9e7d5c4436ffc6` and exact head `beb7b35aa718a746ad5dad9d5574c2293bd0ab40` were technically merged as `d1a6160b37250bda691e906fc4ee06e37dd0c847` after the PR changed from draft despite its body requiring the PR to remain draft.
- Recorded that the repository-native operational authority comment for PR #32 explicitly excluded draft-to-ready, merge, auto-merge, approval review and branch-protection/ruleset changes, ADR acceptance, Phase 0 exit, source-code implementation, Issue #23 state change, hosting-evidence completion, governance-task completion, ratification, release, deployment, and status promotion.
- Recorded that no separate exact-head ready authority, separate exact-head merge authority, review submission, review thread, published commit status, or GitHub Actions workflow run was available for PR #32.
- Recorded the Product Owner post-merge exact-head decision approving only the accuracy of PR #32 three-file corrective content, without retrospective lifecycle authority or ratification of PR #32 lifecycle action.
- Clarified that the PR #32 technical merge does not ratify PR #31, PR #30, PR #29, PR #28, PR #27, PR #26, or PR #25, validate Issue #23 closure, accept any ADR, approve Phase 0 exit, grant source-code authority, complete GOV-034/GOV-035/GOV-036/GOV-037/GOV-038/GOV-039/GOV-040/GOV-041, or provide substantive approval or completion evidence; all substantive governance statuses remain unchanged.
- Recorded that PR #33 exact head `28c776abf6ab7832dbdf61ea49203c6e9c13a55c` was technically merged as `68df196efdf38919d73a6b6345b973d2c3698b29` after changing from draft despite its body requiring the PR to remain draft.
- Recorded the Product Owner post-merge content decision approving only PR #33 corrective-content accuracy, without retrospective ready or merge authority.
- Recorded the repository-control incident investigation, Git author `labzefry` / committer `web-flow` attribution, and the unavailable account-security-log and credential-level evidence.
- Recorded containment through active ruleset `main-protected-governance`, an empty bypass list, independent approval, stale-review dismissal, latest-push approval, conversation resolution, required checks, deletion restriction, and force-push blocking.
- Recorded sentinel PR #34 exact head `be4182a7f918da043e71fe9af3626a1bb027372b`, automatic dismissal of its stale approval, independent latest-head approval by `@zefriansyah`, successful `governance-validation`, `markdown-lint`, and `secret-scan` checks, closure without merge, and unchanged `main`.
- Added GOV-042 as Review and preserved Phase 0, application implementation, preview exit, P1, ADR, GD-007, unresolved journal, hosting, Technical Preview, and source-code gates.

### Security

- Established deny-by-default tenant isolation, least privilege, secret handling, credential revocation, supply-chain, plugin, updater, and AI security requirements.

### Documentation

- Declared unresolved technology, licensing, compliance, offline POS, payment, and recovery decisions instead of treating assumptions as approved choices.

## Release policy note

No product release exists yet. This changelog entry describes the handbook baseline and subsequent governance/foundation/vision-canonicalization work only. A dated/tagged product version will be added after review and merge through the approved release process.

Attribution: Lab | zefry
