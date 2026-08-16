# M7.5 P1 Rollback & Deployment Recovery Runtime Evidence — 2026-08-16

Attribution: **Lab | zefry**

## Scope

This handbook entry records the Product Owner-authorized bounded non-Production Technical Preview rollback and forward-recovery rehearsal executed on 2026-08-16.

The rehearsal changed only the live Technical Preview release pointer between two already-retained versioned releases and then restored the original active release.

No database operation, migration, schema change, queue execution, backup restore, cleanup, Production action, M7.6, M7.7, Phase 0 Exit, or Release authority is created by this evidence.

## Governed baseline

Canonical repository baseline when this evidence branch was created:

- `main`: `de859fa310af7e14f58a8f891171784b92163814`;
- tree: `3e01b900a8afcdacd3d1235e52d7cfae3db89650`;
- canonical M7.5: **22 VERIFIED / 7 BLOCKED**;
- outcome: **BLOCKED**;
- lifecycle authority created: **false**.

The live Technical Preview was still intentionally running the previously deployed application release:

`m75-preview-c7159770381e`

The difference between canonical repository `main` and the live application release is expected here because the intervening PR #121 was evidence-only and did not authorize or perform another deployment.

## Recovery model under test

The canonical Technical Preview release model retains private versioned releases under the non-public release directory and uses the public entry point only as a release selector.

The rehearsal therefore tested the existing recoverable-publication design without overwriting application payloads:

`m75-preview-c7159770381e -> m75-preview-c9d2d45c4c11 -> m75-preview-c7159770381e`

The hosting-managed `.htaccess` was not changed. The private runtime `.env` was not opened or edited. Neither release was deleted.

## Initial-state verification

Before mutation, the public `index.php` release selector was visually verified to reference:

`m75-preview-c7159770381e`

No other entry-point line was intentionally changed.

## Rollback execution

Only the release identifier in the public entry point was changed from:

`m75-preview-c7159770381e`

to:

`m75-preview-c9d2d45c4c11`

After saving the pointer change, the Technical Preview sign-in surface rendered normally and continued to display the explicit non-Production boundary.

No login, sale, payment, database, queue, or business mutation was performed as part of this rehearsal.

### Rollback readiness

The rolled-back release was then checked through `/health/ready`.

Sanitized observed payload semantics:

- `status`: `ready`;
- `service`: `oneqay-web`.

The per-request correlation identifier shown by the live endpoint is intentionally omitted from repository evidence.

This demonstrates that the retained previous release could become the active Technical Preview release and remain ready after the rollback pointer switch.

## Forward-recovery execution

Only the same release identifier was then changed back from:

`m75-preview-c9d2d45c4c11`

to:

`m75-preview-c7159770381e`

After saving the pointer, the Technical Preview sign-in surface again rendered normally with the explicit non-Production boundary.

### Forward-recovery readiness

The restored current release was checked again through `/health/ready`.

Sanitized observed payload semantics:

- `status`: `ready`;
- `service`: `oneqay-web`.

The final active release pointer therefore returned to the same release that was active before rehearsal:

`m75-preview-c7159770381e`

## Rollback qualification

The bounded evidence supports:

`RUNTIME:ROLLBACK = VERIFIED`

for this Technical Preview release pair because:

- the initial active release identity was verified before mutation;
- a retained previous stable release was activated using the governed release-selector boundary;
- the Technical Preview surface remained healthy after rollback;
- readiness returned `ready` on the rolled-back release;
- no release payload was destructively overwritten;
- no schema or migration change occurred during the rehearsal.

This qualification is deliberately narrow. It does not assert that every future release can be rolled back across arbitrary schema, data, dependency, or configuration changes. Canonical deployment policy still requires compatibility to be evaluated for each release.

## Deployment recovery qualification

The bounded evidence supports:

`RUNTIME:DEPLOYMENT_RECOVERY = VERIFIED`

for the same Technical Preview recovery boundary because:

- the current release could be recovered after deliberate rollback;
- the same controlled release selector was returned to the original release;
- the Technical Preview surface remained healthy after forward recovery;
- readiness returned `ready` after recovery;
- the final active pointer matched the initial active release;
- the previous retained release remained available rather than being destroyed during the cycle.

This does not qualify database restore, data recovery, disaster recovery, cross-engine recovery, Production recovery, or rollback across incompatible schema changes.

## Deterministic evaluator reconciliation

The sanitized evaluator input added with this evidence is:

`docs/evidence/runtime/p1-cpanel-live-runtime-reconciled-20260816-recovery.json`

The candidate reconciliation promotes only:

- `RUNTIME:DEPLOYMENT_RECOVERY`: `PARTIAL -> VERIFIED`;
- `RUNTIME:ROLLBACK`: `NOT_SUPPLIED -> VERIFIED`.

Proposed deterministic snapshot after governed publication:

- **24 VERIFIED**;
- **5 BLOCKED**;
- outcome: **BLOCKED**;
- lifecycle authority created: **false**.

## Remaining blockers

1. `ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED`
2. `ENGINE:TENANT_ISOLATION:PARTIAL`
3. `RUNTIME:BACKGROUND_EXECUTION:PARTIAL`
4. `RUNTIME:BACKUP_RESTORE:PARTIAL`
5. `RUNTIME:QUEUE_EXECUTION:UNVERIFIED`

The rehearsal does not alter or infer any of these controls.

## Security and privacy handling

Raw cPanel and browser screenshots are intentionally not committed. Some source screenshots contained hosting-account path information, and readiness responses contained request correlation identifiers; those details are unnecessary for qualification and are omitted.

No hosting account identifier, cookie, session value, credential, token, raw `.env`, APP_KEY, database identity, customer data, BPJS data, payment data, or Production data is committed.

The release identifiers and repository SHAs recorded here are governed non-secret provenance metadata.

## Authority boundary

This evidence-only Draft PR performs no additional deployment and does not authorize Ready, merge, cleanup, database/migration/schema work, queue execution, restore, Production, M7.6, M7.7, Phase 0 Exit, or Release.

Production readiness remains **NO-GO**.

Attribution: **Lab | zefry**
