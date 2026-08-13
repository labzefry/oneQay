# Workflow Directory

Application release and deployment workflows remain deferred until the relevant
Product Owner authority and delivery gates are available. M7.1, M7.2, and M7.3
each permit only their separately authorized bounded Local/Test/CI validation
workflows.

The repository currently permits narrowly scoped governance, foundation, M7.1,
M7.2, and M7.3 Local/Test/CI validation workflows:

- `.github/workflows/governance-required-checks.yml`;
- `.github/workflows/php-foundation-regression.yml`;
- `.github/workflows/product-owner-merge-authority.yml`;
- `.github/workflows/m7-1-application-regression.yml`;
- `.github/workflows/m7-2-tenant-isolation-regression.yml`;
- `.github/workflows/m7-3-identity-org-context-regression.yml`.

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

## M7.2 tenant isolation regression

`m7-2-tenant-isolation-regression.yml` produces:

- `m7-2-tenant-isolation-regression`.

The check preserves the separately authorized M7.2 Tenant Kernel & Isolation
Foundation while remaining active for the bounded M7.3 successor source. It:

- checks out the exact pull-request source head;
- permits only the governed M7.2 source envelope plus the separately authorized
  bounded M7.3 identity/organization/outlet/device successor envelope;
- preserves the root Platform Foundation regression;
- preserves the M7.1 application regression;
- rejects dependency-manifest or lockfile changes;
- validates and installs the already locked Composer dependencies;
- rejects unresolved High/Critical Composer advisories;
- validates application PHP syntax;
- rejects database/schema/migration/SQL implementation across the bounded M7.2
  and M7.3 source;
- installs already locked npm dependencies with `npm ci`;
- rejects npm advisories at High or Critical severity;
- preserves Vue/TypeScript type-check and Vite build evidence;
- runs deterministic synthetic tenant verification, fail-closed missing/invalid
  context, raw-client-hint rejection, cross-tenant negative verification,
  request-scope clearing, safe-denial, and framework-independence regression;
- uses `contents: read` and receives no repository or Production secret;
- performs no SQL, migration, infrastructure mutation, deployment, release,
  publish, or Production action.

The M7.3 successor allowlist does not disable, skip, or weaken the M7.2 tenant
isolation regression. It only allows the separately authorized bounded M7.3
source to coexist while the M7.2 regression continues to execute.

The synthetic tenant verifier is Local/Test/CI evidence only and is not
registered as a Production identity or membership implementation.

## M7.3 identity organizational context regression

`m7-3-identity-org-context-regression.yml` produces:

- `m7-3-identity-org-context-regression`.

The check is bounded to the separately authorized M7.3 Identity / Organization /
Outlet / Device Minimum. It:

- checks out the exact pull-request source head;
- rejects changed paths outside the Product Owner-authorized M7.3 source
  envelope;
- rejects modification of the root Tenant/Auth Platform Foundation;
- preserves the root Platform Foundation regression;
- preserves the M7.1 application regression;
- preserves the full M7.2 tenant isolation regression;
- rejects Composer/npm manifest or lockfile changes;
- validates and installs only the already locked application dependencies;
- rejects unresolved High/Critical Composer and npm advisories;
- validates application PHP syntax;
- rejects database/schema/migration/SQL implementation;
- preserves Vue/TypeScript type-check and Vite build evidence;
- runs deterministic positive controls for verified identity, organization,
  outlet, and device context;
- runs deny-by-default negative controls for missing/malformed identity,
  missing tenant context, missing membership, cross-tenant identity,
  foreign organization/outlet/device relationships, global identifier
  collisions, raw untrusted organizational hints, and stale request context;
- verifies generic denial behavior without foreign-context payload leakage;
- verifies Domain/Application framework independence;
- constrains relationship evidence to deterministic Local/Test/CI synthetic
  principals;
- uses `contents: read` and receives no repository or Production secret;
- performs no authentication implementation, SQL, migration, infrastructure
  mutation, deployment, release, publish, or Production action.

M7.3 does not implement password login, MFA/TOTP, WebAuthn, token authentication,
OAuth/OIDC/SAML, real tenant membership persistence, or Production organizational
repositories. It does not authorize M7.4 or later work.

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

M7.1, M7.2, and M7.3 source authority do not grant repository-ruleset mutation.
`m7-1-application-regression`, `m7-2-tenant-isolation-regression`, and
`m7-3-identity-org-context-regression` remain mandatory milestone-specific
lifecycle evidence for their applicable Draft PRs even though they are not
silently added to the protected required-status set.

Repository protection must preserve strict required-status-check policy,
independent review, stale-review dismissal, latest-push approval, review-thread
resolution, squash-only merge, deletion and non-fast-forward protection, and an
empty bypass list.

## Scope boundary

These workflows are control-plane, foundation-validation, or bounded
M7.1/M7.2/M7.3 Local/Test/CI mechanisms. They do not authorize Sprint 14, M7.4+,
broader application business source, real authentication, SQL/migration
execution, Production database changes, deployment, release, ADR/GD promotion,
JRN resolution, Phase 0 Exit, or Production readiness.

Any workflow added here must:

- use least-privilege `permissions`;
- pin reusable actions to immutable commits;
- avoid untrusted-code secret exposure;
- avoid Production credentials and Production data unless separately
  authorized;
- produce traceable results bound to a commit;
- document its authority boundary and operational activation requirements.

Attribution: Lab | zefry
