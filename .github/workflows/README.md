# Workflow Directory

Application release and deployment workflows remain deferred until the relevant
Product Owner authority and delivery gates are available. M7.1 separately
authorizes one bounded Local/Test/CI application-regression workflow only.

The repository currently permits narrowly scoped governance, foundation, and
M7.1 Local/Test/CI validation workflows:

- `.github/workflows/governance-required-checks.yml`;
- `.github/workflows/php-foundation-regression.yml`;
- `.github/workflows/product-owner-merge-authority.yml`;
- `.github/workflows/m7-1-application-regression.yml`.

## Stable governance checks

`governance-required-checks.yml` produces:

- `governance-validation`;
- `markdown-lint`;
- `secret-scan`.

It runs for pull requests targeting `main`, uses least-privilege
`contents: read`, pins `actions/checkout` to a full commit SHA, does not access
repository secrets, and does not build, publish, release, migrate, or deploy
oneQay.

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
- accesses no Production credential, Production database, or Production data;
- performs no migration, release, publish, or deployment action.

## M7.1 application regression

`m7-1-application-regression.yml` produces:

- `m7-1-application-regression`.

The check is bounded to the separately authorized M7.1 application skeleton. It:

- checks out the exact pull-request source head;
- preserves the root Platform Foundation regression;
- enforces the governed PHP 8.2-8.5 compatibility boundary;
- uses Node.js `24.19.0` for the Local/Test/CI frontend toolchain;
- requires committed `composer.lock` and `package-lock.json` files;
- validates and installs Composer dependencies from the lockfile;
- rejects unresolved High/Critical Composer advisories;
- validates application PHP syntax;
- installs npm dependencies with `npm ci`;
- rejects npm advisories at High or Critical severity;
- type-checks Vue/TypeScript source;
- builds Vite assets;
- runs the M7.1 application regression covering configuration fail-closed,
  health/readiness, correlation/error, tenant-context fail-closed, and
  architecture-boundary behavior;
- uses `contents: read` and receives no repository or Production secret;
- performs no SQL, migration, infrastructure mutation, deployment, release, or
  Production action.

This M7.1 check is source-lifecycle evidence. Its existence does not modify the
protected-branch required-status-check set and does not authorize M7.2 or later
work.

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

The active default-branch lifecycle requires:

- `governance-validation`;
- `markdown-lint`;
- `secret-scan`;
- `php-foundation-regression`;
- `product-owner-merge-authority`.

The M7.1 source authority does not grant repository-ruleset mutation. The new
`m7-1-application-regression` check remains mandatory M7.1 lifecycle evidence
for its Draft PR even though it is not silently added to the protected required
status set.

Repository protection must preserve strict required-status-check policy,
independent review, stale-review dismissal, latest-push approval, review-thread
resolution, squash-only merge, deletion and non-fast-forward protection, and an
empty bypass list.

## Scope boundary

These workflows are control-plane, foundation-validation, or bounded M7.1
Local/Test/CI mechanisms. They do not authorize Sprint 14, broader application
business source, SQL/migration execution, Production database changes,
deployment, release, ADR/GD promotion, JRN resolution, Phase 0 Exit, or
Production readiness.

Any workflow added here must:

- use least-privilege `permissions`;
- pin reusable actions to immutable commits;
- avoid untrusted-code secret exposure;
- avoid Production credentials and Production data unless separately
  authorized;
- produce traceable results bound to a commit;
- document its authority boundary and operational activation requirements.

Attribution: Lab | zefry
