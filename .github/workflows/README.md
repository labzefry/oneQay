# Workflow Directory

Application build, release, and deployment workflows remain deferred until the
relevant Product Owner authority and delivery gates are available.

The repository currently permits narrowly scoped governance and foundation
validation workflows:

- `.github/workflows/governance-required-checks.yml`;
- `.github/workflows/php-foundation-regression.yml`;
- `.github/workflows/product-owner-merge-authority.yml`.

## Stable governance checks

`governance-required-checks.yml` produces:

- `governance-validation`;
- `markdown-lint`;
- `secret-scan`.

It runs for pull requests targeting `main`, uses least-privilege
`contents: read`, pins `actions/checkout` to a full commit SHA, does not access
repository secrets, and does not build, publish, release, migrate, or deploy
OneQay.

## PHP foundation regression

`php-foundation-regression.yml` produces:

- `php-foundation-regression`.

The check:

- checks out the exact pull-request source head rather than the synthetic merge
  ref;
- exposes the PHP version and rejects PHP versions below 8.2;
- exposes the Composer version;
- runs `composer validate --strict --no-check-publish`;
- runs PHP syntax validation across tracked PHP foundation and test files;
- runs the full `composer test` foundation regression;
- uses the GitHub-hosted runner's preinstalled PHP and Composer toolchain;
- adds no third-party setup action;
- accesses no production credential, production database, or production data;
- performs no migration, release, publish, or deployment action.

## Product Owner merge authority

`product-owner-merge-authority.yml` evaluates repository-native Product Owner
merge authority and writes the commit-status context:

- `product-owner-merge-authority`.

A valid authority record must be an issue comment on the pull request authored
by the repository owner and contain these exact standalone lines:

```text
PRODUCT OWNER MERGE AUTHORIZATION
PR: #<pull-request-number>
EXACT HEAD: <40-character-head-sha>
MERGE AUTHORITY: GRANTED
```

The evaluator fails closed when no matching authority exists. A new push changes
the exact head, so an authority comment bound to the previous head cannot satisfy
the new commit. Editing or deleting authority comments triggers reevaluation.

The evaluator runs only from the trusted default-branch workflow through
`pull_request_target` and `issue_comment`. It never checks out or executes
pull-request code. Its permissions are limited to metadata reads and
`statuses: write`, preventing an untrusted PR-head workflow edit from
self-authorizing merge.

## Required ruleset activation

M5.2 is not enforcement-complete until the active default-branch ruleset
requires both additional contexts:

- `php-foundation-regression`;
- `product-owner-merge-authority`.

The existing required contexts remain:

- `governance-validation`;
- `markdown-lint`;
- `secret-scan`.

Ruleset activation is a repository-administration action and must preserve
strict required-status-check policy, independent review, stale-review dismissal,
latest-push approval, review-thread resolution, squash-only merge, deletion and
non-fast-forward protection, and an empty bypass list.

Because the Product Owner authority evaluator is intentionally trusted from the
default branch, its automatic status production begins only after the workflow
is published. The M5.2 publication is therefore the bootstrap change; ruleset
activation and read-only verification follow publication before M5.2 can be
declared enforcement-complete.

## Scope boundary

These workflows are control-plane and foundation-validation mechanisms. They do
not authorize Sprint 14, application business source, migration execution,
production database changes, deployment, release, ADR/GD promotion, JRN
resolution, or production readiness.

The detailed M5.2 implementation and activation record is maintained in
`docs/handbook/M5_2_CI_LIFECYCLE_HARDENING.md`. Broader ROADMAP/TASKS/CHANGELOG
synchronization remains a separate M5.3 concern and is not silently folded into
this remediation.

Any workflow added here must:

- use least-privilege `permissions`;
- pin reusable actions to immutable commits;
- avoid untrusted-code secret exposure;
- avoid production credentials and production data unless separately
  authorized;
- produce traceable results bound to a commit;
- document its authority boundary and operational activation requirements.

Attribution: Lab | zefry
