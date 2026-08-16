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

Any missing gate is fail-closed.

## Execution sequence

The rehearsal runner uses an infrastructure driver supplied only by a separately authorized execution adapter. The canonical order is:

1. **PREFLIGHT** — verify exact target, artifact, runtime, permissions, storage and recovery prerequisites without exposing secrets.
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
- completed phase names;
- timestamps;
- sanitized outcome code.

Evidence must not contain passwords, application keys, database credentials, tokens, session material, raw `.env` content, account-home paths, `public_html` identity, private backup archive names, or host-control credentials.

## Recovery success criteria

A rehearsal is successful only when:

- target/authority binding passed;
- recovery checkpoint passed before mutation;
- candidate staging and activation followed the approved immutable release boundary;
- candidate health result was recorded;
- rollback was exercised;
- the exact baseline release identity was restored;
- baseline health verification passed;
- sanitized evidence was persisted.

Successful M7.6 rehearsal evidence does not authorize M7.7, Release, Production, schema migration, real customer data, or payment-provider processing.

## Execution adapter boundary

This preparation deliberately ships **no cPanel, SSH, SFTP, FTP, arbitrary HTTP, shell, or provider-specific deployment adapter**. A real target adapter may be introduced only under separate target execution authority and must preserve DEC-009 portability, the already-published updater safety boundaries, and the qualified target evidence.

Until that adapter/execution authority exists, CI uses a synthetic driver only to prove orchestration, evidence redaction, fail-closed target binding, health behavior, and rollback semantics.
