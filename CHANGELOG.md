# Changelog

Semua perubahan penting OneQay dicatat di dokumen ini. Format mengikuti Keep a Changelog dan versioning produk akan mengikuti Semantic Versioning setelah release baseline disetujui.

## [Unreleased]

### Added

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

### Security

- Established deny-by-default tenant isolation, least privilege, secret handling, credential revocation, supply-chain, plugin, updater, and AI security requirements.

### Documentation

- Declared unresolved technology, licensing, compliance, offline POS, payment, and recovery decisions instead of treating assumptions as approved choices.

## Release policy note

No product release exists yet. This changelog entry describes the handbook baseline only. A dated/tagged version will be added after review and merge through the approved release process.
