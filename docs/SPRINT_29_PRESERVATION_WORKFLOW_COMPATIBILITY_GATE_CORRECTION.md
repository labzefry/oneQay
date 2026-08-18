# Sprint 29 Preservation Workflow Compatibility Gate Correction

## Status

This document is a bounded documentation-only correction to the published Sprint 29 entry gate and its env-file envelope correction.

Canonical predecessor gate publications:

- Sprint 29 entry gate PR #191 → `54bf91d393dcd99dea1ca7402cab932669da99c4`;
- env-file/source-envelope correction PR #192 → `5431a186bb7a87b48841c454db121b632a133549`.

Attribution: **Lab | zefry**

## Why this correction exists

The first Sprint 29 source candidate proved the Sprint 29-specific implementation gates successfully, including:

- exact Sprint 29 source envelope;
- no schema change and migrations exactly #1–#8;
- PHP syntax;
- framework-independent Application boundary;
- fail-closed feature arming;
- runtime-scoped console registration;
- durable target derivation;
- insert-only credential path;
- disposable Sprint 29 bootstrap behavior.

However, ten existing preservation workflows failed before executing their substantive regression logic because their historical preflight guards were intentionally hard-coded to earlier Sprint 28 or earlier changed-file universes. A representative failure was:

`Unauthorized Sprint 28 changed path: .github/workflows/sprint29-first-control-principal-credential-bootstrap-regression.yml`

Those failures are compatibility drift in preservation workflow envelope guards, not authority to bypass or suppress the regressions.

Sprint 29 must therefore update only the affected preservation workflow YAML files so they recognize the exact Sprint 29 envelope while retaining their historical envelope checks for historical/other scopes.

## Core implementation correction also authorized

The first candidate also exposed two bounded implementation/test-harness corrections:

1. preflight credential-bootstrap denials must occur outside the existing `PersistenceTransaction` callback because that historical transaction adapter intentionally wraps unknown callback exceptions as `DURABLE_PERSISTENCE_TRANSACTION_FAILURE`;
2. the dedicated Sprint 29 preservation step must invoke Sprint 21–24 include-oriented regression scripts through the same bootstrapped M7.1 application harness pattern already used by Sprint 28 rather than invoking those include-only scripts as standalone PHP programs.

The repository must still repeat critical eligibility checks inside the transaction immediately before insertion. A transaction-time race/ineligibility may collapse to the generic bounded bootstrap-ineligible failure; it must never enable a credential overwrite or leak a raw database exception.

## Authoritative expanded source envelope

For the Sprint 29 source implementation PR, this section supersedes only the prior corrected 10-path source-envelope list. All other Sprint 29 security, runtime, persistence, behavior, schema, exclusion, and lifecycle requirements remain unchanged.

The exact authorized Sprint 29 source implementation envelope is now these **20 paths**:

### Sprint 29 implementation paths

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

### Preservation-workflow compatibility paths

11. `.github/workflows/m7-2-tenant-isolation-regression.yml`;
12. `.github/workflows/m7-3-identity-org-context-regression.yml`;
13. `.github/workflows/m7-5-preview-db-qualification-regression.yml`;
14. `.github/workflows/sprint22-policy-administration-regression.yml`;
15. `.github/workflows/sprint23-initial-tenant-admin-provisioning-regression.yml`;
16. `.github/workflows/sprint24-protected-control-admin-lifecycle-regression.yml`;
17. `.github/workflows/sprint25-policy-administration-delivery-regression.yml`;
18. `.github/workflows/sprint26-identity-credential-verification-regression.yml`;
19. `.github/workflows/sprint27-first-party-session-establishment-regression.yml`;
20. `.github/workflows/sprint28-initial-password-enrollment-regression.yml`.

No other application, test, workflow, configuration, route, migration, documentation, dependency, release, deployment, updater, `.env`, or Technical Preview file is authorized in the source PR.

## Exact compatibility rule for the ten historical workflows

Each of the ten preservation workflow files above may change only as needed to make its preflight envelope guard successor-aware.

The permitted model is:

- when the PR changed-file set is exactly the Sprint 29 20-path envelope, accept that exact set and continue into the workflow's existing substantive preservation checks;
- otherwise preserve the workflow's previous historical envelope behavior unchanged.

The compatibility branch must remain fail closed:

- no wildcard acceptance of arbitrary `apps/web/**` changes;
- no removal of dependency-lock protection;
- no removal of migration immutability or exact migration-count checks;
- no suppression of regression execution;
- no `continue-on-error` conversion;
- no weakening of Preview/Production/updater boundaries;
- no status-check renaming;
- no workflow disablement.

The ten workflows are compatibility surfaces only. They do not gain Sprint 29 business logic.

## Dedicated Sprint 29 workflow correction

The dedicated Sprint 29 workflow remains the authoritative Sprint 29 envelope/security regression.

It must be updated to:

- enforce the new exact 20-path envelope;
- still reject `.env` / `.env.*` changes;
- still reject dependency manifest/lock changes;
- still enforce no migration changes and exactly migrations #1–#8;
- retain all Sprint 29 static/runtime/disposable checks;
- invoke Sprint 21–24 include-oriented tests through the existing bootstrapped application-harness pattern;
- run standalone Sprint 25–28 and M7 preservation scripts through their existing supported invocation patterns;
- fail if any preservation regression fails.

## Transaction/preflight correction

Sprint 29 may refine its repository contract and service flow within the already-authorized Application/Infrastructure files so that:

1. Application calls a read-only bootstrap eligibility preflight before opening the transaction;
2. the transaction callback invokes an insert operation that repeats all critical eligibility checks;
3. transaction-time stale/race failures are converted to bounded `BOOTSTRAP_INELIGIBLE`, `STORAGE_FAILURE`, or another already-defined generic Sprint 29 violation;
4. no credential update/upsert/delete/overwrite path is introduced;
5. the existing Sprint 26 `(tenant_id, identity_id)` primary key remains the final structural race boundary.

This correction does not authorize changing the shared `PersistenceTransaction` implementation.

## Schema and secret boundaries remain unchanged

Sprint 29 remains **NO_SCHEMA_CHANGE**.

- canonical migrations remain exactly #1–#8;
- no migration #9;
- no bootstrap-token table;
- no `.env` or `.env.*` mutation;
- feature-arm source default remains `false`;
- password remains hidden interactive input only;
- no HTTP bootstrap surface;
- no credential overwrite/reset/change/recovery;
- no Preview credential activation;
- no Production credential activation.

## Lifecycle handling of the first source candidate

PR #193 is qualification evidence but must not be merged with failing preservation checks.

After this correction is published:

1. PR #193 must be closed/superseded without merge;
2. a replacement Sprint 29 source branch must be created from the exact publication commit of this correction;
3. the proven Sprint 29 source content is reapplied with the bounded preflight/test-harness corrections;
4. only the exact 20-path source envelope above may differ from the correction publication baseline;
5. every triggered workflow on the replacement exact head must complete successfully before Product Owner merge authority and merge.

## No authority expansion beyond compatibility

This correction does not authorize:

- migration #9;
- schema mutation;
- password reset/change/recovery;
- emergency protected-control recovery;
- MFA/passkeys/federation/API token work;
- public bootstrap HTTP endpoints;
- Preview or Production activation;
- updater activation;
- deployment;
- GitHub Release;
- Phase 0 Exit;
- Production readiness promotion.

## Security invariant

> Preservation compatibility may teach historical workflow guards to recognize exactly the bounded Sprint 29 20-path successor envelope, but it must not weaken their substantive tests, accept arbitrary future paths, suppress failures, alter schema/runtime authority, or broaden the first-control-principal bootstrap trust model.

Attribution: **Lab | zefry**
