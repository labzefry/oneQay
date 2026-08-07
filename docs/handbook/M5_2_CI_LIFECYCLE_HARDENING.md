# M5.2 — CI & Lifecycle Control Hardening

## Identity

- Project: OneQay
- Developer and Product Engineering Entity: Lab | zefry
- Repository: `labzefry/oneQay`
- Program: M5 — Engineering State, CI & Governance Stabilization
- Micro-milestone: M5.2 — CI & Lifecycle Control Hardening
- Implementation base: `153a33a4a2b5edb4a31285eca7d3491f9589b778`
- Implementation base tree: `e41b1b8cad32997d073c20fb5960d17376f16043`

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

1. PHP version visibility and minimum PHP 8.2 enforcement.
2. Composer version visibility.
3. `composer validate --strict --no-check-publish`.
4. PHP syntax validation for PHP files under `src/` and `tests/`.
5. Full `composer test`.

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

Editing or deleting the matching authority comment also causes reevaluation once
the workflow is present on the default branch.

The evaluator does not check out or execute pull-request code. Its permissions
are limited to metadata reads and `statuses: write`.

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

The PHP regression workflow can run on the M5.2 candidate PR immediately.

For the same-repository M5.2 candidate PR, the authority evaluator also runs on
pull-request events and initially writes `product-owner-merge-authority` as
failure because merge authority has not yet been granted. This is expected
fail-closed behavior, not a technical defect.

After an explicit Product Owner merge-authorization comment is recorded for the
exact candidate head, rerunning the evaluator can produce the success status.
After publication, `issue_comment` events automatically reevaluate authority
comments for subsequent pull requests.

## Lifecycle boundary

Implementation and technical validation do not grant Ready or Merge authority.

M5.2 must follow:

implementation → validation → Draft PR → checks → independent review → Product
Owner lifecycle decision → publication → ruleset verification.

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
