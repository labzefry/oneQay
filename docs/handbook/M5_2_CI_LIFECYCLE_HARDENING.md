# M5.2 — CI & Lifecycle Control Hardening

## Identity

- Project: OneQay
- Developer and Product Engineering Entity: Lab | zefry
- Repository: `labzefry/oneQay`
- Program: M5 — Engineering State, CI & Governance Stabilization
- Micro-milestone: M5.2 — CI & Lifecycle Control Hardening
- Implementation base: `153a33a4a2b5edb4a31285eca7d3491f9589b778`
- Implementation base tree: `e41b1b8cad32997d073c20fb5960d17376f16043`

## Published enforcement state

M5.2 is now **PUBLISHED / ENFORCEMENT COMPLETE**.

Published identity:

- Pull request: #67
- Published commit: `512344d0497787c729242cb1fd2d7d02ecfc40c2`
- Published tree: `0f0af1c1acab208c704fbdf05b19014127abddbb`

Resolved anomalies:

- A-03 — Lifecycle Authority Not Enforced: **Resolved**.
- A-05 — PHP Regression Not in GitHub CI: **Resolved**.

The active protected default-branch contexts are:

1. `governance-validation`
2. `markdown-lint`
3. `secret-scan`
4. `php-foundation-regression`
5. `product-owner-merge-authority`

The verified ruleset posture includes strict status checks, one approving review,
stale-review dismissal after push, latest-push approval, review-thread
resolution, squash-only merge, deletion protection, non-fast-forward protection,
and an empty bypass list.

The remaining sections preserve the M5.2 implementation, bootstrap, activation,
and lifecycle design as historical provenance. Language below that describes a
"candidate", "manual action required", or a condition that had to be satisfied
before declaring enforcement complete refers to the pre-publication/pre-activation
sequence. Those activation conditions are now satisfied by the published and
verified state above; they are not current pending actions.

## Objective

M5.2 closes two remaining high-priority stabilization gaps without starting
Sprint 14 or business-module implementation:

1. A-05 — PHP foundation regression is not represented in GitHub CI.
2. A-03 — independent reviewer approval is not technically separated from
   exact-head Product Owner merge authority.

## Part A — PHP foundation regression

The candidate adds `.github/workflows/php-foundation-regression.yml`.

Protected check target:

`php-foundation-regression`

The check performs:

1. Exact pull-request source-head checkout.
2. PHP version visibility and minimum PHP 8.2 enforcement.
3. Composer version visibility.
4. `composer validate --strict --no-check-publish`.
5. PHP syntax validation for PHP files under `src/` and `tests/`.
6. Full `composer test`.

The workflow uses the GitHub-hosted runner toolchain and the same immutable
`actions/checkout` commit already used by repository governance checks. It does
not add a third-party PHP setup action.

It does not use production credentials, production database state, production
data, migration execution, deployment, release, or network dependencies inside
the application regression suite.

## Part B — exact-head Product Owner merge authority

The candidate adds
`.github/workflows/product-owner-merge-authority.yml`.

Protected status target:

`product-owner-merge-authority`

A valid authorization must be a pull-request issue comment authored by the
repository owner and must contain these standalone lines:

```text
PRODUCT OWNER MERGE AUTHORIZATION
PR: #<pull-request-number>
EXACT HEAD: <40-character-head-sha>
MERGE AUTHORITY: GRANTED
```

The evaluator reads the current pull-request head from GitHub, reads pull-request
issue comments, and writes the resulting commit status directly to that exact
head.

Invariant:

**Reviewer APPROVED does not equal Product Owner merge authority.**

If the exact head changes, an authority record naming the previous SHA no longer
matches. The new head therefore has no successful authority evidence until a
new exact-head Product Owner authorization is recorded and evaluated.

Editing or deleting the matching authority comment also causes reevaluation.

### Trusted execution boundary

The authority evaluator uses `pull_request_target` and `issue_comment`, so the
workflow definition is loaded from the trusted default branch. It never checks
out or executes pull-request code.

This prevents a pull-request author from changing the evaluator in the same PR
and using that modified workflow to self-authorize merge.

Permissions are limited to:

- repository content read;
- issue metadata read;
- pull-request metadata read;
- commit-status write.

No repository secret is read.

## Ruleset activation requirement

The currently active `main-protected-governance` ruleset already requires:

- `governance-validation`;
- `markdown-lint`;
- `secret-scan`;
- one approving review;
- stale-review dismissal after push;
- latest-push approval;
- review-thread resolution;
- strict required-status-check policy;
- squash-only merge;
- deletion protection;
- non-fast-forward protection;
- no bypass actor.

M5.2 enforcement requires adding these required contexts without removing or
weakening any existing protection:

- `php-foundation-regression`;
- `product-owner-merge-authority`.

The available GitHub connector can read the ruleset but does not expose a safe
ruleset-update action. Therefore this ruleset mutation is explicitly classified:

**MANUAL ACTION REQUIRED**

M5.2 must not be declared enforcement-complete until the Product Owner performs
that repository-administration action and the resulting ruleset is verified
read-only from GitHub.

## Bootstrap behavior for the M5.2 pull request

`php-foundation-regression` runs on the candidate PR and validates its exact
source head.

The trusted authority evaluator is intentionally loaded from the default branch.
Because it does not yet exist on `main`, the new `product-owner-merge-authority`
status is not relied on to authorize publication of the M5.2 bootstrap PR.

The M5.2 PR therefore remains governed by the pre-existing repository rules and
the explicit Product Owner lifecycle process. This bootstrap exception must not
be generalized to later pull requests.

After M5.2 publication:

1. the trusted evaluator becomes available from `main`;
2. the Product Owner adds `php-foundation-regression` and
   `product-owner-merge-authority` to the active ruleset;
3. the ruleset is verified read-only from GitHub;
4. subsequent pull requests fail closed unless current exact-head Product Owner
   merge authority is recorded.

## Lifecycle boundary

Implementation and technical validation do not grant Ready or Merge authority.

M5.2 must follow:

implementation → validation → Draft PR → checks → independent review → Product
Owner lifecycle decision → publication → ruleset activation → ruleset
verification.

Do not start M5.3 or Sprint 14 merely because the candidate checks pass.

## Prohibited effects

M5.2 does not:

- implement application business source;
- start POS, ERP, WMS, CRM, Business Network, or Enterprise Vision;
- execute SQL or migrations;
- modify production database state;
- deploy or release;
- promote ADR/GD state;
- resolve JRN-003 or JRN-013;
- promote production readiness.

Production readiness remains **NO-GO**.

Attribution: Lab | zefry
