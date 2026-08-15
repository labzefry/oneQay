# M7.5 P1 Relational Qualification Cleanup — 2026-08-15

Attribution: **Lab | zefry**

## Purpose

This additive closure record documents the secure retirement of the bounded non-Production M7.5 Preview relational qualification capability after the sanitized qualification evidence was published through PR #112.

It records only operational closure facts. It intentionally excludes database names, database usernames, passwords, raw `.env` content, cPanel account identifiers, home-directory paths, tokens, private keys, screenshots, customer data, BPJS data, Production data, and other credential material.

This closure does not authorize permanent schema, migration execution, durable business persistence, M7.6, M7.7, Phase 0 Exit, Release, Production, or `oneqay.com`.

## Governed baseline before cleanup

The cleanup was performed after PR #112 published the sanitized relational qualification reconciliation.

Published `main` immediately before this closure record:

`3e2a310144fd73504b662cabae6a32a0073c592d`

Published tree:

`70de762f254950abdaa6ee519ecd4d88869337eb`

Active non-Production Preview release:

`m75-preview-0edea8cdcc0c`

Historical bounded relational result preserved by PR #112:

- scope: `technical-preview-relational-probe`;
- status: **QUALIFIED**;
- engine profile: MariaDB;
- engine version: `11.4.8`;
- `persistent_schema_created = false`;
- `production_ready = false`.

The historical qualification evidence remains valid as an observation of the authorized probe while it was enabled. Secure retirement after evidence publication does not invalidate that recorded observation.

## Cleanup sequence and observations

The Product Owner explicitly authorized cleanup of the dedicated qualification credential/database boundary after PR #112 was published.

The following bounded sequence was manually verified on the non-Production cPanel Preview target:

1. the qualification feature switch was set to its disabled state;
2. the qualification endpoint returned `404 NOT FOUND`, demonstrating fail-closed behavior;
3. the dedicated qualification database user was detached from the dedicated qualification database;
4. the Technical Preview main page remained healthy after the database-user detach;
5. the dedicated qualification database user account was deleted;
6. the dedicated qualification database was inspected and confirmed to contain no permanent tables before deletion;
7. the dedicated empty qualification database was deleted under explicit Product Owner authority;
8. the qualification database identity, username, and password values were cleared from the private runtime `.env`;
9. the qualification feature switch was confirmed as `false` after one incorrect runtime value was detected and corrected;
10. the qualification endpoint again returned `404 NOT FOUND` after credential cleanup;
11. the Technical Preview main page remained healthy after full cleanup.

No other cPanel database was intentionally modified by this cleanup sequence.

## Important runtime distinction

The current runtime state is intentionally different from the historical qualification snapshot:

- **Historical evidence state:** the authorized bounded relational probe successfully connected to the dedicated MariaDB qualification target and returned `qualified`.
- **Current runtime state:** the qualification capability is retired, its dedicated credential/database boundary has been removed, and the qualification endpoint is expected to remain unavailable with `404`.

Therefore a future absence of live qualification connectivity after this retirement must not be misclassified as a regression of the historical PR #112 evidence. The probe was deliberately decommissioned after its evidence purpose was completed.

Re-enabling the probe, recreating a qualification database/user, or introducing any new relational target requires fresh bounded authority and must not be inferred from this closure record.

## Permanent-schema boundary preserved

Before the dedicated qualification database was deleted, it was manually observed to contain no permanent tables.

This is consistent with the earlier probe result:

`persistent_schema_created = false`

The cleanup did not create or migrate schema, seed business data, introduce durable business persistence, or authorize database migration work.

## Credential retirement

The dedicated qualification account was removed from its database and then deleted. The dedicated qualification database was subsequently removed only after it was confirmed empty.

The private runtime qualification values for database identity, username, and password were cleared. No credential values are included in this repository record.

The non-secret profile/host/port defaults are not treated as credentials by this closure record; the security-critical condition is that the qualification feature switch is disabled and dedicated database identity/username/password material is absent.

## Fail-closed verification

After cleanup, the protected qualification URL returned:

`404 NOT FOUND`

This is the expected secure state when the qualification feature switch is disabled. The main Technical Preview page continued to render normally after the cleanup, providing bounded evidence that retirement of the qualification-only credential/database boundary did not break the current Preview application foundation.

## Machine-readable closure evidence

The sanitized machine-readable closure record is:

`docs/evidence/runtime/p1-cpanel-relational-qualification-cleanup-20260815.json`

It records the secure-retirement facts without database identity, database username, password, raw `.env`, cPanel account identifier, screenshots, or Production/customer data.

## M7.5 status after cleanup

This secure retirement closes the temporary relational-probe lifecycle only. It does **not** complete the broader deterministic M7.5 evaluator.

The last governed evaluator snapshot remains:

- verified mandatory controls: **13**;
- blocking mandatory controls: **16**;
- complete evaluator outcome: **BLOCKED**;
- lifecycle authority created: **false**.

The cleanup itself does not promote any of the 16 remaining controls to `VERIFIED` by inference.

Therefore the current distinction remains:

- M7.5 preparation: **DONE / PUBLISHED**;
- M7.5 live web-runtime evidence: **VERIFIED / MATERIAL PROGRESS**;
- M7.5 bounded MariaDB relational probe historical evidence: **QUALIFIED / VERIFIED**;
- M7.5 relational probe current runtime lifecycle: **RETIRED / FAIL-CLOSED**;
- M7.5 complete 29-control evidence package: **BLOCKED / INCOMPLETE**;
- M7.6: **BLOCKED / NOT AUTHORIZED**;
- M7.7: **BLOCKED / NOT AUTHORIZED**;
- Phase 0 Exit: **NOT APPROVED**;
- Production readiness: **NO-GO**;
- Production authority: **NONE**.

## Security and privacy statement

No raw screenshot, cPanel account identifier, database identity, database username, password, raw `.env`, token, private key, runtime `APP_KEY`, customer data, BPJS data, personal data, Production data, or permanent relational schema is committed by this closure record.

Attribution: **Lab | zefry**
