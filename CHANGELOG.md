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

### Security

- Established deny-by-default tenant isolation, least privilege, secret handling, credential revocation, supply-chain, plugin, updater, and AI security requirements.

### Documentation

- Declared unresolved technology, licensing, compliance, offline POS, payment, and recovery decisions instead of treating assumptions as approved choices.

## Release policy note

No product release exists yet. This changelog entry describes the handbook baseline only. A dated/tagged version will be added after review and merge through the approved release process.
