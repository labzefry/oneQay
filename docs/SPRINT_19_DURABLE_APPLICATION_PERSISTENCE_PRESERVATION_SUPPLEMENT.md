# Sprint 19 Supplement — Durable Persistence Preservation Reconciliation

## Identity

- Product: `oneQay`
- Developer and Product Engineering Entity: **Lab | zefry**
- Repository: `labzefry/oneQay`
- Decision date: 2026-08-17
- Exact supplement base: `41234e1b05455673ca32aa76572c23630c55c999`
- Sprint 19 primary entry gate: `docs/SPRINT_19_DURABLE_APPLICATION_PERSISTENCE_ENTRY_GATE.md`
- Production readiness: **NO-GO**

Attribution: **Lab | zefry**

## Why this supplement is required

Sprint 19 implementation passed its new durable persistence regression and the reconciled M7.2 tenant-isolation regression, but fresh CI exposed two historical preservation assumptions that predate the governed migration program:

1. M7.5 Preview Database Qualification still globally requires `apps/web/database/migrations` not to exist;
2. M7.3 Identity/Organizational Context regression still globally requires the migration directory not to exist, and M7.5 Technical Preview Release Artifact preserves M7.3 as part of its first-party Web regression suite.

Those checks were valid before Sprint 16–19, but they are no longer the correct security invariant once canonical durable application persistence exists.

The correct invariants are now:

- Identity/Organization Domain and Application source remains persistence-framework independent;
- Preview qualification itself must not create or mutate permanent schema;
- Preview runtime must not gain Sprint 19 durable persistence authority;
- the canonical Sprint 19 migration may exist in the repository;
- the governed Technical Preview release must remain `NO_SCHEMA_CHANGE` and must not package the canonical migration directory.

This supplement authorizes only the minimum preservation reconciliation required to encode those invariants.

## Product Owner continuation

The Product Owner has already authorized continuation through Sprint 19 and ordinary Ready/Merge lifecycle after exact-head checks succeed.

Independent review is not an additional requirement.

This supplement does not grant Preview, cPanel, live, or Production persistence authority.

## Additional authorized implementation paths

In addition to the 16 paths authorized by the primary Sprint 19 entry gate, this supplement authorizes exactly five additional implementation paths:

1. `apps/web/tests/identity-org-context.php`;
2. `.github/workflows/m7-3-identity-org-context-regression.yml`;
3. `.github/workflows/m7-5-preview-db-qualification-regression.yml`;
4. `tools/build-m7-5-preview-release.sh`;
5. `.github/workflows/m7-5-preview-release-artifact.yml`.

No other path is authorized by this supplement.

## M7.3 reconciliation contract

`apps/web/tests/identity-org-context.php` must preserve all existing M7.3 identity and organizational-context behavioral assertions.

Only the obsolete global assertion that no migration directory may exist is superseded.

M7.3 must continue to prove that these areas contain no database/schema implementation:

- Domain Identity;
- Domain Organization;
- Domain Outlet;
- Domain Device;
- Application Identity;
- Application Organization;
- Infrastructure Identity;
- Infrastructure Organization.

The regression may additionally confirm that the repository migration set is the exact single Sprint 19 foundational migration rather than rejecting all migrations.

The M7.3 workflow must retain a bounded changed-file envelope. It may be widened only to the exact Sprint 19 primary paths plus these preservation paths so that the reconciled M7.3 test can run on the Sprint 19 source PR.

It must not broadly authorize `apps/web/**`.

## Preview database qualification preservation contract

M7.5 Preview Database Qualification remains a non-mutating qualification surface.

Its workflow must stop asserting that the repository contains no canonical migration directory.

Instead it must prove:

- `PreviewDatabaseQualification.php` contains no permanent DDL behavior;
- dependency manifests/locks remain unchanged;
- no credential/private-key material is committed;
- verified tenant context remains required;
- Sprint 19 durable persistence remains disabled by default;
- Preview runtime receives no durable persistence authority;
- established application regressions, including Sprint 19 durable persistence tests in CI mode, continue to pass.

The workflow must not execute the Sprint 19 canonical migration against a Preview database target.

## Technical Preview release preservation contract

The existing Technical Preview release metadata states:

`migration_classification = NO_SCHEMA_CHANGE`

That claim must remain true after Sprint 19.

The Technical Preview build process currently copies the whole `apps/web` tree. Sprint 19 therefore requires one explicit packaging exclusion:

`apps/web/database/migrations/**`

The build script must remove the canonical migration directory from the staged Preview release payload after copying the private application tree.

This is a packaging boundary only. The canonical repository migration remains unchanged and continues to be exercised in Local/Test/CI regression.

The Preview release workflow must verify that the resulting archive does not contain:

- `apps/web/database/migrations/`;
- a canonical application migration PHP file beneath that path.

The workflow must continue to verify release manifest binding, checksums, deterministic reproduction, private/public boundary, and no `.env`/`.git`/`node_modules` leakage.

## Why database configuration is not excluded from the Preview artifact

Sprint 19 repository and transaction adapters independently deny runtime class `preview`.

The Preview artifact may therefore retain the ordinary lazy application configuration and class files without gaining durable persistence authority.

The critical schema-mutation surface is the canonical migration directory; that surface is excluded from the Technical Preview payload until a future explicit Preview persistence gate authorizes otherwise.

No automated `artisan migrate` execution is authorized.

## Required CI outcome

After the supplement is merged and Sprint 19 source is rebuilt on the new canonical main:

- Governance Required Checks must pass;
- PHP Foundation Regression must pass;
- M7.1 Application Regression must pass;
- M7.2 Tenant Isolation Regression must pass;
- M7.3 Identity Organizational Context Regression, when triggered, must pass;
- M7.5 Preview Database Qualification Regression must pass;
- M7.5 Technical Preview Release Artifact must pass;
- exact Product Owner merge authority must succeed for the final source head.

If any newly exposed historical preservation check fails for another obsolete global no-persistence assumption, implementation must stop and the authorization envelope must be reassessed before changing another path.

## Non-scope

This supplement does not authorize:

- Preview database migration execution;
- Preview durable business data;
- cPanel/live schema mutation;
- Production persistence;
- POS business persistence;
- updater activation;
- deployment changes;
- GitHub Release;
- Production readiness.

Production readiness remains **NO-GO**.
