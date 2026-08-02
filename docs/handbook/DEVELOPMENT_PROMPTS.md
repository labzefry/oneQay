# Systematic AI Continuation and Review Prompts

## Usage

Before using any prompt, provide the latest repository context and require the agent to read the mandatory canonical documents. Agents must not convert Proposed/Under Review decisions into Approved decisions.

## 1. README.md

**ChatGPT - Lanjutan:** Review the latest OneQay repository and improve README.md as the project orientation without inventing technology choices. Preserve product identity, GitHub SSOT, handbook-first status, and links. Update manifest/tasks/changelog if status changes.

**ChatGPT - Review Independen:** Audit README.md for contradictions, missing stakeholder orientation, broken governance links, ambiguous commitments, and drift from the canonical manifest. Classify findings Critical/High/Medium/Low.

## 2. PROJECT_MANIFEST.md

**ChatGPT - Lanjutan:** Update OneQay PROJECT_MANIFEST.md from accepted evidence and ADRs. Maintain explicit Approved, Proposed, Under Review, Deferred, and Deprecated states; do not silently promote decisions.

**ChatGPT - Review Independen:** Review the manifest for hidden assumptions, inconsistent statuses, missing risks/owners, stale capability states, and conflicts with ADRs or root documents.

## 3. AI_CONSTITUTION.md

**ChatGPT - Lanjutan:** Improve AI_CONSTITUTION.md only where a new recurring failure mode or governance requirement is evidenced. Keep rules testable, enforceable, and consistent with repository protections.

**ChatGPT - Review Independen:** Red-team the constitution for loopholes allowing destructive, insecure, undocumented, untested, or cross-tenant changes.

## 4. ARCHITECTURE.md

**ChatGPT - Lanjutan:** Refine OneQay architecture after domain discovery. Preserve inward dependencies, module ownership, tenant isolation, infrastructure independence, and Accepted ADRs; create new ADRs for consequential decisions.

**ChatGPT - Review Independen:** Review architecture for coupling, distributed-monolith risk, cross-module data access, tenant leakage, failure semantics, operational gaps, and unjustified complexity.

## 5. ROADMAP.md

**ChatGPT - Lanjutan:** Update roadmap outcomes, entry/exit criteria, dependencies, and risks from approved product evidence. Avoid dates without capacity and avoid bringing deferred complexity forward.

**ChatGPT - Review Independen:** Challenge roadmap sequencing, missing foundations, optimistic dependencies, unverifiable outcomes, and premature cloud/plugin/AI work.

## 6. CODING_STANDARDS.md

**ChatGPT - Lanjutan:** Add stack-specific standards only after the relevant ADR is Accepted. Include enforceable formatter, linter, type, architecture, testing, security, and dependency rules.

**ChatGPT - Review Independen:** Review standards for ambiguity, unenforceable wording, duplicated rules, framework leakage into domain logic, and missing tenant/security practices.

## 7. DATABASE.md

**ChatGPT - Lanjutan:** Extend database governance from the accepted engine and tenancy ADR. Add naming, migration, indexing, backup, retention, and isolation details without weakening compatibility.

**ChatGPT - Review Independen:** Red-team schema governance for cross-tenant references, destructive migrations, money/time errors, weak constraints, unbounded queries, and untested restore.

## 8. API_SPEC.md

**ChatGPT - Lanjutan:** Produce or update machine-readable contracts from approved use cases. Preserve versioning, authorization, tenant context, idempotency, error, pagination, webhook, and deprecation rules.

**ChatGPT - Review Independen:** Test the API design for broken object/function/property authorization, ambiguous semantics, replay, mass assignment, compatibility breaks, and data leakage.

## 9. SECURITY.md

**ChatGPT - Lanjutan:** Update security controls using current threat models and evidence. Define owner, verification, monitoring, incident, and exception expiry for each material risk.

**ChatGPT - Review Independen:** Perform adversarial review across IAM, tenant isolation, payment, secrets, supply chain, uploads, plugins, updater, Cloudflare, and AI tools.

## 10. DEPLOYMENT.md

**ChatGPT - Lanjutan:** Add deployment details for the approved environment while keeping business logic infrastructure-independent. Include artifact, config, migration, health, observation, backup, and recovery.

**ChatGPT - Review Independen:** Review for non-reproducible builds, hidden state, unsafe migration order, weak rollback, missing restore proof, secret leakage, and environment drift.

## 11. TESTING.md

**ChatGPT - Lanjutan:** Convert approved risks and acceptance criteria into deterministic automated and operational tests, with clear gates and evidence ownership.

**ChatGPT - Review Independen:** Identify untested tenant, financial, concurrency, migration, installer/updater, security, accessibility, performance, and recovery failure modes.

## 12. UI_GUIDELINE.md

**ChatGPT - Lanjutan:** Develop the OneQay design system from validated research and brand decisions. Preserve accessibility, locale, POS speed, context visibility, error/offline states, privacy, and performance.

**ChatGPT - Review Independen:** Review UX for ambiguous tenant/outlet context, unsafe financial actions, inaccessible controls, misleading states/charts, mobile gaps, and localization failures.

## 13. INSTALLER.md

**ChatGPT - Lanjutan:** Convert the installer specification into stack-specific acceptance criteria only after runtime ADR approval. Keep it idempotent, resumable, locked after finish, and free of secret leakage.

**ChatGPT - Review Independen:** Threat-model clean install and interrupted/failing steps, including permission, database, archive, migration, default admin, report redaction, and installer re-entry.

## 14. UPDATER.md

**ChatGPT - Lanjutan:** Refine updater details for signed release artifacts and all supported version paths. Preserve backup, compatibility, atomicity, health gates, staged rollout, and recovery.

**ChatGPT - Review Independen:** Attack the updater trust chain, signature/key lifecycle, archive handling, concurrent update, migration failure, rollback assumptions, and maintenance bypass.

## 15. CONTRIBUTING.md

**ChatGPT - Lanjutan:** Update contribution workflow to match repository protections, CI, ownership, and release strategy while preserving atomic, reviewed, documented changes.

**ChatGPT - Review Independen:** Check for routes that bypass issue scope, required review, security reporting, quality gates, documentation, or protected branches.

## 16. RELEASE.md

**ChatGPT - Lanjutan:** Prepare the next OneQay release process from approved scope and evidence. Include version, artifact, compatibility, migration, rollout, monitoring, recovery, and communication.

**ChatGPT - Review Independen:** Audit release readiness for missing evidence, unsupported upgrade paths, unresolved severe risks, weak stop conditions, mutable artifacts, and poor EOL planning.

## 17. TASKS.md

**ChatGPT - Lanjutan:** Reconcile TASKS.md against issues, PRs, ADRs, roadmap, manifest, and changelog. Every active task needs scope, dependency, owner, risk, acceptance evidence, and honest status.

**ChatGPT - Review Independen:** Find stale, duplicated, orphaned, blocked-without-action, done-without-evidence, or source-code tasks that violate Phase 0 entry criteria.

## 18. CHANGELOG.md

**ChatGPT - Lanjutan:** Update CHANGELOG.md from merged user-visible and engineering-baseline changes only. Keep Unreleased accurate and never invent release dates, versions, or security details.

**ChatGPT - Review Independen:** Verify changelog completeness and truth against repository history, manifest, tasks, migrations, API changes, deprecations, and release records.

## Master cross-document review prompt

Review all OneQay handbook documents as one enterprise governance system. Build a contradiction matrix across identity, status, architecture, tenancy, API, database, security, deployment, testing, UI, installer, updater, contribution, release, tasks, and changelog. Report severity, evidence, affected files, recommended correction, and whether the handbook is ready for Product Owner approval. Do not create application source code or approve unresolved technology/vendor decisions.
