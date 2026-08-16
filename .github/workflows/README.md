# Workflow Directory

## Canonical M7.5 lifecycle closure — 2026-08-17

For current workflow/lifecycle interpretation, this section supersedes the older current-facing M7.5 consolidation retained below as historical control-plane provenance.

M7.5 mandatory runtime/engine evidence is now **CLOSED / EVIDENCE_COMPLETE / PUBLISHED** with **29 VERIFIED / 0 BLOCKED** after PR #129, with secure rehearsal cleanup published through PR #130. `lifecycle_authority_created=false` remains true for the evidence package.

The existing bounded M7.5 workflow mechanisms remain historical/current technical mechanisms only. This closure changes no workflow YAML, protected status-check producer, ruleset, deployment mechanism, or merge authority. It does not authorize M7.6, M7.7, Phase 0 Exit, Sprint 14, Release, Production, database/schema/migration, restore, cPanel mutation, or deployment.

The next candidate engineering direction is separately gated Secure Web Updater architecture/release-control-plane work. Any workflow expansion for that capability requires separate authority.

Attribution: **Lab | zefry**

## Canonical program-state consolidation — 2026-08-16

For current workflow-directory interpretation, this section supersedes older statements below that stop at M7.4A or describe all `M7.5+` workflow activity as future/nonexistent. Those statements are retained as historical control-plane provenance.

Current repository workflow inventory also includes bounded M7.5 mechanisms, including:

- `.github/workflows/m7-5-preview-db-qualification-regression.yml`;
- `.github/workflows/m7-5-preview-release-artifact.yml`.

Their existence and prior governed use do not create general deployment, Release, Production, database/schema/migration, restore, M7.6, M7.7, Phase 0 Exit, or Sprint 14 authority. Canonical M7.5 after PR #124 is **26 VERIFIED / 3 BLOCKED**, overall **BLOCKED / INCOMPLETE**, with `lifecycle_authority_created=false`.

This documentation-only consolidation changes no workflow YAML, status-check producer, ruleset, or merge authority.

Application release and deployment workflows remain deferred until the relevant
Product Owner authority and delivery gates are available. M7.1, M7.2, M7.3,
M7.4, and M7.4A each permit only their separately authorized bounded
Local/Test/CI or explicit-Preview validation workflows.

The repository currently permits narrowly scoped governance, foundation, M7.1,
M7.2, M7.3, M7.4, and M7.4A validation workflows:

- `.github/workflows/governance-required-checks.yml`;
- `.github/workflows/php-foundation-regression.yml`;
- `.github/workflows/product-owner-merge-authority.yml`;
- `.github/workflows/m7-1-application-regression.yml`;
- `.github/workflows/m7-2-tenant-isolation-regression.yml`;
- `.github/workflows/m7-3-identity-org-context-regression.yml`;
- `.github/workflows/m7-4-pos-core-synthetic-regression.yml`;
- `.github/workflows/m7-4a-technical-preview-interaction-regression.yml`.

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

## Milestone workflow applicability

Standalone M7.2, M7.3, M7.4, and M7.4A workflows are applicable only when their
owned source envelope or their own workflow definition is changed. Unrelated
documentation-only pull requests must not create milestone regression failures.

Workflow applicability and regression preservation are separate controls:

- a non-applicable predecessor workflow may be **NOT RUN**;
- an applicable successor workflow must continue executing predecessor
  behavioral regressions that are part of its governed verification chain;
- path filtering does not authorize modification of predecessor source and does
  not disable tenant, identity/organizational, money, idempotency, audit, or
  other preserved regressions;
- a pull request that changes a milestone workflow remains applicable to that
  workflow so the workflow correction itself is validated.

The historical bounded post-M7.4 lifecycle-stabilization envelope permitted only
the explicit governance/documentation and M7.2/M7.3/M7.4 workflow files needed
to validate that corrective PR. It did not broaden application business-source
authority and is preserved as historical control-plane provenance.

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

The standalone check is bounded to the M7.2 Tenant Kernel & Isolation Foundation
and runs only when the M7.2-owned source envelope or the M7.2 workflow itself is
touched. It:

- checks out the exact pull-request source head;
- enforces the governed M7.2 source envelope plus the explicit lifecycle-
  stabilization governance envelope when the workflow itself is under review;
- preserves the root Platform Foundation regression;
- preserves the M7.1 application regression;
- rejects dependency-manifest or lockfile changes;
- validates and installs the already locked Composer dependencies;
- rejects unresolved High/Critical Composer advisories;
- validates application PHP syntax;
- rejects database/schema/migration/SQL implementation across the bounded source;
- installs already locked npm dependencies with `npm ci`;
- rejects npm advisories at High or Critical severity;
- preserves Vue/TypeScript type-check and Vite build evidence;
- runs deterministic synthetic tenant verification, fail-closed missing/invalid
  context, raw-client-hint rejection, cross-tenant negative verification,
  request-scope clearing, safe-denial, and framework-independence regression;
- uses `contents: read` and receives no repository or Production secret;
- performs no SQL, migration, infrastructure mutation, deployment, release,
  publish, or Production action.

M7.3, M7.4, or M7.4A successor source does not need this standalone predecessor
workflow to run merely because successor paths changed. The applicable successor
workflow preserves the M7.2 tenant-isolation behavioral regression.

The synthetic tenant verifier is Local/Test/CI evidence only and is not
registered as a Production identity or membership implementation.

## M7.3 identity organizational context regression

`m7-3-identity-org-context-regression.yml` produces:

- `m7-3-identity-org-context-regression`.

The standalone check is bounded to the M7.3 Identity / Organization / Outlet /
Device Minimum and runs only when the M7.3-owned source envelope or the M7.3
workflow itself is touched. It:

- checks out the exact pull-request source head;
- rejects changed paths outside the Product Owner-authorized M7.3 source or
  lifecycle-stabilization envelope;
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

M7.4 or M7.4A successor source does not need this standalone predecessor
workflow to run merely because successor paths changed. The applicable successor
workflow preserves the full M7.3 identity/organizational-context behavioral
regression.

M7.3 does not implement password login, MFA/TOTP, WebAuthn, token authentication,
OAuth/OIDC/SAML, real tenant membership persistence, or Production organizational
repositories. It does not authorize M7.4 or later work.

## M7.4 POS core synthetic regression

`m7-4-pos-core-synthetic-regression.yml` produces:

- `m7-4-pos-core-synthetic-regression`.

The standalone check is bounded to the M7.4 POS Core Synthetic Vertical Slice and
runs only when the M7.4-owned source envelope or the M7.4 workflow itself is
touched. It:

- checks out the exact pull-request source head;
- enforces the bounded POS source envelope plus the explicit lifecycle-
  stabilization governance envelope when the workflow itself is under review;
- preserves the root Platform Foundation and M7.1 application regressions;
- preserves the full M7.2 tenant-isolation regression;
- preserves the full M7.3 identity/organizational-context regression;
- rejects dependency-manifest or lockfile changes;
- validates locked Composer/npm dependencies and rejects unresolved
  High/Critical advisories;
- validates PHP syntax and Vue/TypeScript/Vite build evidence;
- preserves Domain/Application framework independence;
- rejects database/schema/migration/SQL implementation;
- executes deterministic M7.4 POS core synthetic regression for exact-money,
  idempotency/replay, payment sufficiency, stock causation, tenant/context, and
  audit/correlation behavior;
- uses `contents: read`, receives no Production secret, and performs no
  migration, deployment, release, publish, or Production action.

M7.4 workflow success is regression evidence for the bounded synthetic POS slice;
it is not evidence that a complete end-user POS UI, Production authentication,
durable business persistence, deployment, release, or Production readiness
exists. M7.4A successor paths do not require this standalone predecessor workflow
to run because the applicable M7.4A workflow preserves the M7.4 behavioral
regression.

## M7.4A Technical Preview interaction regression

`m7-4a-technical-preview-interaction-regression.yml` produces:

- `m7-4a-technical-preview-interaction-regression`.

The check is bounded to the M7.4A Technical Preview Interaction Layer. It:

- checks out the exact pull-request source head;
- enforces the bounded M7.4A Preview Application/Infrastructure/Delivery,
  provider/routes/UI/test, and workflow source envelope;
- runs the root Platform Foundation regression and M7.1 application regression;
- preserves the full M7.2 tenant-isolation, M7.3 identity/organizational-context,
  and M7.4 POS-core behavioral regressions;
- requires unchanged Composer/npm manifests and lockfiles;
- validates and installs only already locked dependencies and rejects unresolved
  High/Critical Composer/npm advisories;
- validates PHP syntax, Vue/TypeScript type checking, and Vite build evidence;
- preserves Preview Application framework independence;
- rejects database/SQL/migration implementation and obvious credential material
  within the bounded Preview implementation envelope;
- exercises the synthetic sign-in → server-verified context → catalog → cart →
  `CASH` / `MANUAL_EXTERNAL` → existing M7.4 `CompleteSyntheticSale` → receipt
  journey;
- uses explicit CI Preview configuration with synthetic data only;
- performs no migration, deployment, release, publish, or Production action.

M7.4A workflow success is lifecycle evidence for the already published PR #98
interaction layer. It does not grant M7.5 runtime qualification, durable
Production persistence, deployment, release, Phase 0 Exit, Sprint 14, or
Production authority.

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

M7.1, M7.2, M7.3, M7.4, and M7.4A source authority do not grant repository-
ruleset mutation. `m7-1-application-regression`,
`m7-2-tenant-isolation-regression`, `m7-3-identity-org-context-regression`,
`m7-4-pos-core-synthetic-regression`, and
`m7-4a-technical-preview-interaction-regression` remain mandatory milestone-
specific lifecycle evidence for their applicable Draft PRs even though they are
not silently added to the protected required-status set.

Repository protection must preserve strict required-status-check policy,
independent review, stale-review dismissal, latest-push approval, review-thread
resolution, squash-only merge, deletion and non-fast-forward protection, and an
empty bypass list.

## Scope boundary

These workflows are control-plane, foundation-validation, or bounded
M7.1/M7.2/M7.3/M7.4/M7.4A Local/Test/CI/explicit-Preview mechanisms. They do not
authorize Sprint 14, M7.5+, broader application business source, real Production
authentication, SQL/migration execution, Production database changes,
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
