# oneQay GitHub Actions: path-scoped historical regression optimisation

Author by Lab | zefry · 30 October 2026

## Problem observed
PR #992 had 76 commits and 34 modified workflow files as of the audit. GitHub Actions contains many historical sprint regression workflows that run on **all** pull requests to main, even when the workflow's owned feature and contracts are unaffected. This multiplies queued and canceled runs, increases runner minutes, and hides genuinely relevant failure signals. The visible first page of Actions is not proof of the full count, and canceled stale attempts should not be interpreted as fresh failures.

## Bounded change
Sprint134–147 final-shift-close canonical control-plane regressions now use path-scoped `pull_request` triggers while retaining each workflow's original steps, checks, concurrency policy, permissions, and job identity. Each workflow is triggered on: its own workflow or immediate predecessor change; relevant FinalShiftClose control-plane code/routes/providers/tests/contracts; and its own Sprint documentation. No application logic or security scanner was changed.

Each of the 14 workflows also provides `workflow_dispatch` so the historical check is available explicitly at any time. A full compatibility / release gate must still run before a high-risk release when cross-module impact is suspected. `paths` selection is not a substitute for threat modeling or regression verification.

## Governance
The active `main-protected-governance` ruleset (ID 20309642) was read on 30 Oct 2026. Its required status checks are `governance-validation`, `markdown-lint`, `secret-scan`, `php-foundation-regression`, and `product-owner-merge-authority`; **none** are renamed, path-filtered or modified here. Keep the PR in Draft until its YAML parsing, workflow trigger result, and merge checks are reviewed. A future ruleset change must be re-evaluated before merge; never filter a required status check without an equivalent always-running check.

## Verification and operational evidence
1. Diff each edited file against `main`: only the `on.pull_request.paths` selector and `on.workflow_dispatch` have changed. No test steps or job definitions are altered.
2. Validate the 14 YAML documents and ensure all 14 owned workflow paths, predecessor workflow paths, and canonical source patterns are included.
3. Open one draft pull request containing the batch and review Actions for generated runs. GitHub `paths` changes cannot suppress workflow triggers on existing feature branches until this PR is merged.
4. Compare a subsequent unrelated menu-only PR to a FinalShiftClose code/contract PR: the first should skip these historical workflows; the second should run all appropriate ones. Observe actual counts; do not claim percentage savings without a measured before/after sample.
5. Roll back using a normal revert PR if valid FinalShiftClose changes fail to trigger. Never bypass mandatory branch protection.

This PR intentionally does not convert unrelated legacy workflows, disable secret scans, modify payment logic, or deploy to hosting. Further refactoring should be incremental and evidence-driven.

## Second-batch expansion — Sprint 148–168
A second single-commit change adds `on.pull_request.paths` to 18 more historically broad workflows: Sprint 148, 149, 150, 151, 153 and Sprint 156–168. Sprint 152 and 154 already had path filters, while Sprint 155 is a broader source-contract guard and remains unchanged. All 18 preserve original job identity, test steps, permissions and concurrency; the five durable-runtime workflows also acquire manual `workflow_dispatch`, while POS workflow manual triggers are retained unchanged.

The path selectors are sourced from each workflow's own referenced source, owned tests, route/Vue pages, relevant FinalShiftClose JSON contracts and Sprint docs; shared dependency manifest/lock updates are included, so critical dependency remediation does not silently skip these regressions. No catch-all `apps/web/**` selector was added, because doing so would reproduce the unrelated menu-change load.

**Count:** 32 historical workflows filtered across two changes (14 first batch + 18 second batch); 281 definitions existed in `main` at initial inventory. This is still a controlled partial optimization, not a claim that the backlog or unresolved Symfony Mime CVE is fixed. Re-check required checks and verify YAML on the PR's newest exact head before merging.
