# M7.6 Preview Deployment / Recovery Rehearsal

## Purpose

This runbook defines the bounded execution contract for M7.6 on an already qualified Preview target. It does not itself grant deployment authority and does not contain hosting credentials, private filesystem identities, raw environment values, database credentials, tokens, or production data.

Attribution: **Lab | zefry**

## Current authority state

- M7.5 Preview Runtime Qualification: **CLOSED / EVIDENCE_COMPLETE / PUBLISHED**.
- M7.6 rehearsal package: **PREPARATION ONLY until separately authorized for target execution**.
- Production: **NOT AUTHORIZED**.
- Release: **NOT AUTHORIZED**.
- Database/schema migration: **NOT AUTHORIZED**.
- Runtime updater installation remains hard-disabled and unwired.

## Real-target corrective observation — 2026-08-17

A separately authorized Preview-target rehearsal activated governed candidate `m75-preview-9761ad293e9a` over healthy baseline `m75-preview-dab951519e67`. Candidate `/health/live` returned HTTP 500. Sanitized candidate logging identified `Illuminate\Encryption\MissingAppKeyException` with no application encryption key specified. The public front controller was immediately restored to the exact baseline, after which `/health/live` returned `ok` and `/health/ready` returned `ready`.

This outcome is classified **`candidate_unhealthy_recovered`**. It does not close M7.6. The proven corrective requirement is **`SHARED_RUNTIME_APP_KEY_NOT_BOUND`**: a governed immutable release must load runtime secrets from an approved private shared environment boundary before candidate activation.

No raw `.env` value, application key, database credential, account-home path, correlation ID, session material, or hosting credential is part of this record.

## Mandatory entry gates

All of the following must be true before a real Preview-target rehearsal begins:

1. Fresh GitHub Minimal Delta Verification identifies the exact source and governed release artifact.
2. The target remains qualified under M7.5 / DEC-009 and the qualification evidence fingerprint is supplied without sensitive values.
3. The rehearsal is bound to runtime class `preview` and synthetic-only data.
4. A separate Product Owner deployment/recovery rehearsal authorization is bound to the exact target qualification fingerprint.
5. Candidate and baseline release identities are different and both are immutable governed release identities.
6. Candidate migration classification is exactly `NO_SCHEMA_CHANGE`.
7. Backup/restore and recovery checkpoint evidence required by the qualified target remains valid.
8. The public/private deployment boundary, active release pointer, health endpoint, rollback target, and log/evidence storage are available under the qualified target model.
9. Shared runtime environment profile `PRIVATE_SHARED_DOTENV_V1` is available outside immutable releases, its directory/file permissions are private, it is not symlink-backed, `APP_KEY` presence is verified without exposing its value, and the candidate public bootstrap is explicitly bound to that shared environment.

Any missing gate is fail-closed.

## Shared runtime environment boundary

For the cPanel-compatible Preview layout, runtime secret state belongs under the private account boundary at the stable relative location:

`oneqay-preview/shared/runtime/.env`

The shared and runtime directories must be private to the account and the environment file must not be group/world readable. The file must never be committed to Git, embedded in the governed release archive, copied into `releases/<release_id>`, shown in updater status, or persisted in sanitized evidence.

The governed public bootstrap selects the private shared environment through Laravel's environment-path boundary before the request kernel bootstraps. If the shared path/file is missing, symlink-backed, or unreadable, the public bootstrap must fail closed instead of booting a candidate without runtime secrets.

A cached `bootstrap/cache/config.php` is prohibited in the governed release input because cached configuration could bypass the expected shared environment loading contract.

## Execution sequence

The rehearsal runner uses an infrastructure driver supplied only by a separately authorized execution adapter. The canonical order is:

1. **PREFLIGHT** — verify exact target, artifact, runtime, permissions, storage, recovery prerequisites, and `PRIVATE_SHARED_DOTENV_V1` binding without exposing secrets.
2. **RECOVERY_CHECKPOINT_VERIFIED** — verify the authorized recovery checkpoint is usable before application mutation.
3. **CANDIDATE_STAGED** — stage the governed immutable candidate release outside the active public surface.
4. **CANDIDATE_ACTIVATED** — atomically/equivalently recoverably activate the candidate through the approved active-release boundary.
5. **CANDIDATE_HEALTH_VERIFIED** — verify release-specific application readiness for the exact candidate identity.
6. **ROLLBACK_EXERCISED** — deliberately restore the previous known-good application release pointer as the recovery drill.
7. **BASELINE_HEALTH_VERIFIED** — verify the exact baseline release is healthy after rollback.
8. **COMPLETED** — persist sanitized evidence only after baseline health succeeds.

If candidate health fails, the rehearsal still exercises rollback immediately. A healthy baseline after that path records `candidate_unhealthy_recovered`. If the baseline is not healthy after rollback, the rehearsal is terminal `FAILED` and must not be represented as successful recovery evidence.

## Evidence contract

Persisted M7.6 evidence may contain only bounded operational metadata such as:

- rehearsal operation ID;
- safe target ID;
- M7.5 qualification evidence ID;
- target qualification fingerprint;
- baseline release ID and source commit;
- candidate release ID and source commit;
- `NO_SCHEMA_CHANGE` classification;
- shared runtime environment profile and presence-only `APP_KEY` state;
- completed phase names;
- timestamps;
- sanitized outcome code.

Evidence must not contain passwords, application keys, database credentials, tokens, session material, raw `.env` content, account-home paths, `public_html` identity, private backup archive names, correlation IDs, or host-control credentials.

## Recovery success criteria

A rehearsal is successful only when:

- target/authority binding passed;
- shared runtime environment binding passed before mutation;
- recovery checkpoint passed before mutation;
- candidate staging and activation followed the approved immutable release boundary;
- candidate health result was recorded;
- rollback was exercised;
- the exact baseline release identity was restored;
- baseline health verification passed;
- sanitized evidence was persisted.

Successful M7.6 rehearsal evidence does not authorize M7.7, Release, Production, schema migration, real customer data, or payment-provider processing.

## Execution adapter boundary

This preparation deliberately ships **no cPanel API, SSH, SFTP, FTP, arbitrary HTTP, shell, or provider-specific deployment adapter**. The private shared runtime environment guard is a local filesystem safety boundary only; it does not discover credentials, authenticate to hosting control planes, or mutate a live target remotely.

A real target adapter may be introduced only under separate target execution authority and must preserve DEC-009 portability, the already-published updater safety boundaries, and the qualified target evidence.

Until that adapter/execution authority exists, CI uses synthetic filesystem fixtures and the synthetic rehearsal driver to prove orchestration, evidence redaction, fail-closed target binding, shared-runtime binding, health behavior, and rollback semantics.
