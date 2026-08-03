# Changelog

Semua perubahan penting OneQay dicatat di dokumen ini. Format mengikuti Keep a Changelog dan versioning produk akan mengikuti Semantic Versioning setelah release baseline disetujui.

## [Unreleased]

### Added

- Added seven **Proposed** Technical Preview ADRs for B1 Laravel/PHP, F1 Vue/Inertia/Vite, D1 MySQL-compatible shared tenancy, A1 first-party session/TOTP, PAY-1 synthetic cash-only, OFF-1 online-only, and conditional P1 deployment with P2 fallback hypothesis.
- Added Proposed synthetic-data classification, threat model, recovery plan, incomplete shared-hosting capability assessment, and Not Ready Phase 0 preview exit evidence under Issue #23.
- Recorded TEN-1, REC-1, SLO-1, and DATA-1 as Technical Preview selections without accepting ADRs or granting source-code authority.
- Preserved Phase 0 as In Progress, application implementation as Blocked, P1 as Unverified, GD-007 as Proposed, and JRN-003/JRN-013 as unresolved.
- Recorded that PR #24 technical merge and Issue #23 closure do not constitute substantive approval, ADR acceptance, Phase 0 exit, or application-skeleton authority.
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

- Standardized the OneQay engineering workflow to ChatGPT and GitHub only.
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
- Recorded that the repository-native operational authority comment for PR #32 explicitly excluded draft-to-ready, merge, auto-merge, approval review, branch-protection or ruleset changes, ADR acceptance, Phase 0 exit, source-code implementation, Issue #23 state change, hosting-evidence completion, governance-task completion, ratification, release, deployment, and status promotion.
- Recorded that no separate exact-head ready authority, separate exact-head merge authority, review submission, review thread, published commit status, or GitHub Actions workflow run was available for PR #32.
- Recorded the Product Owner post-merge exact-head decision approving only the accuracy of PR #32 three-file corrective content, without retrospective lifecycle authority or ratification of PR #32 lifecycle action.
- Clarified that the PR #32 technical merge does not ratify PR #31, PR #30, PR #29, PR #28, PR #27, PR #26, or PR #25, validate Issue #23 closure, accept any ADR, approve Phase 0 exit, grant source-code authority, complete GOV-034/GOV-035/GOV-036/GOV-037/GOV-038/GOV-039/GOV-040/GOV-041, or provide substantive approval or completion evidence; all substantive governance statuses remain unchanged.

### Security

- Established deny-by-default tenant isolation, least privilege, secret handling, credential revocation, supply-chain, plugin, updater, and AI security requirements.

### Documentation

- Declared unresolved technology, licensing, compliance, offline POS, payment, and recovery decisions instead of treating assumptions as approved choices.

## Release policy note

No product release exists yet. This changelog entry describes the handbook baseline only. A dated/tagged version will be added after review and merge through the approved release process.
