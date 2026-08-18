# Sprint 29 Entry Gate Envelope Correction

## Status

This document is a bounded documentation-only correction to the published Sprint 29 entry gate:

`docs/SPRINT_29_GOVERNED_FIRST_CONTROL_PRINCIPAL_BOOTSTRAP_CREDENTIAL_ENTRY_GATE.md`

Attribution: **Lab | zefry**

## Why this correction exists

The published gate listed `apps/web/.env.example` inside the authorized source envelope as a possible place to document the new feature-arm environment variable.

The repository's existing `Governance Required Checks` intentionally reject changes to tracked `.env` and `.env.*` paths. That global protection is stricter than the gate's proposed documentation path and must not be weakened merely to publish Sprint 29.

Sprint 29 does not require an environment example file for correctness. The security requirement is that the application configuration reads `ONEQAY_FIRST_CONTROL_CREDENTIAL_BOOTSTRAP_ENABLED` with a fail-closed default of `false`. That requirement is fully enforceable in `apps/web/config/oneqay.php` and the dedicated regression workflow.

Therefore this correction removes `apps/web/.env.example` from the Sprint 29 changed-file envelope and preserves the existing global env-file governance unchanged.

## Authoritative corrected source envelope

For Sprint 29 source implementation, this section supersedes only the 11-path source-envelope list in the original entry gate. Every other security, runtime, persistence, behavior, test, exclusion, and lifecycle requirement in the original gate remains unchanged.

The exact authorized Sprint 29 source implementation envelope is now these **10 paths**:

1. `apps/web/app/Application/Identity/FirstControlPrincipalCredentialBootstrapRepository.php` — new;
2. `apps/web/app/Application/Identity/FirstControlPrincipalCredentialBootstrapService.php` — new;
3. `apps/web/app/Application/Identity/FirstControlPrincipalCredentialBootstrapViolation.php` — new;
4. `apps/web/app/Infrastructure/Identity/LaravelFirstControlPrincipalCredentialBootstrapRepository.php` — new;
5. `apps/web/app/Providers/AppServiceProvider.php`;
6. `apps/web/config/oneqay.php`;
7. `apps/web/routes/console.php`;
8. `apps/web/tests/first-control-principal-credential-bootstrap.php` — new;
9. `.github/workflows/sprint29-first-control-principal-credential-bootstrap-regression.yml` — new;
10. `docs/FIRST_CONTROL_PRINCIPAL_CREDENTIAL_BOOTSTRAP_FOUNDATION.md` — new.

No `.env` or `.env.*` file is authorized for mutation.

No other application, test, workflow, configuration, route, migration, documentation, dependency, release, deployment, updater, or Technical Preview file is authorized in the Sprint 29 source PR.

## Feature-arm configuration remains fail closed

Sprint 29 still authorizes configuration:

`oneqay.first_control_principal_credential_bootstrap.enabled`

backed by:

`ONEQAY_FIRST_CONTROL_CREDENTIAL_BOOTSTRAP_ENABLED`

The source-level default remains exactly `false`.

Absence of the environment variable must therefore be equivalent to the feature being disabled.

The dedicated Sprint 29 regression must prove the configuration default is false and must prove the command is unavailable unless the feature is explicitly armed in Local/Test/CI.

## No change to security model

This correction does not broaden authority and does not change the selected trust model.

Sprint 29 remains:

- Local/Test/CI only;
- explicitly feature-armed;
- interactive-console only;
- hidden password + confirmation only;
- target identity derived from immutable Sprint 23 evidence;
- protected-control state independently revalidated;
- exactly one insert into the existing Sprint 26 credential table;
- no HTTP bootstrap surface;
- no credential overwrite;
- no password reset/change/recovery;
- **NO_SCHEMA_CHANGE / no migration #9**;
- Technical Preview denied;
- Production denied;
- updater `DISABLED / UNWIRED`.

## Lifecycle effect

This correction must be published before Sprint 29 source mutation.

After publication, the corrected 10-path envelope is the authoritative source boundary for Sprint 29. The original gate remains authoritative for every requirement not explicitly changed here.

Attribution: **Lab | zefry**
