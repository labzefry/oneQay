# M7.5 P1 Backup / Restore Runtime Evidence — 2026-08-16

Attribution: **Lab | zefry**

## Scope

This record documents one bounded, non-Production, isolated database backup / restore rehearsal performed for the remaining M7.5 P1 controls:

- `RUNTIME:BACKUP_RESTORE`;
- `ENGINE:RESTORE_VERIFIED`.

The rehearsal used synthetic deterministic data only. It did not use customer, BPJS, payment-provider, or Production data and did not exercise a permanent oneQay business schema.

## Governed baseline

Canonical `main` before this reconciliation:

`fb046717af894ac6508bc3a92ec375cf407f6bcd`

The published evaluator before the rehearsal evidence was reconciled remained:

- **27 VERIFIED**;
- **2 BLOCKED**;
- outcome: **BLOCKED**;
- blockers:
  - `ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED`;
  - `RUNTIME:BACKUP_RESTORE:PARTIAL`;
- `lifecycle_authority_created=false`.

The active Technical Preview application release observed during the rehearsal remained:

`m75-preview-dab951519e67`

## Isolation boundary

The restore target was an isolated disposable Technical Preview rehearsal database.

The rehearsal intentionally excluded:

- Production databases;
- customer data;
- BPJS data;
- payment-provider data;
- durable business-schema qualification;
- application migration execution;
- tenant-selective Production restore claims.

No claim is made that the rehearsal proves Production disaster-recovery readiness.

## Synthetic baseline

A rehearsal-only InnoDB table named `m75_restore_probe` was created with a deterministic schema marker and two tenant-scoped sentinel rows.

Observed baseline:

| tenant | sentinel | schema marker |
| --- | --- | --- |
| `tenant-alpha` | `M75-RESTORE-ALPHA` | `m75-v1` |
| `tenant-beta` | `M75-RESTORE-BETA` | `m75-v1` |

Pre-backup record count: **2**.

This fixture exists only to make restore integrity observable. It is not a oneQay business table.

## Backup observation

A database backup of the isolated rehearsal target was acquired through the cPanel database backup mechanism after the two-row baseline was observed.

The raw backup archive is not committed to the repository.

An independent artifact checksum was not recorded during this interactive rehearsal. This limitation is explicitly retained and no checksum claim is made.

## Controlled-loss observation

After the backup was acquired, controlled loss was introduced by dropping only the rehearsal fixture table:

`m75_restore_probe`

This establishes a materially different post-backup state and prevents the restore result from being confused with an unchanged source database.

No other database mutation is claimed by this evidence record.

## Restore observation

The selected rehearsal backup was supplied to the bounded cPanel database restore mechanism.

cPanel reported the isolated rehearsal database restore as **Success**.

The control decision does not rely on the cPanel success message alone; integrity-after-restore was subsequently observed independently through the restored fixture.

## Integrity-after-restore

After restore:

- `m75_restore_probe` existed again;
- total row count was exactly **2**;
- `tenant-alpha` returned with sentinel `M75-RESTORE-ALPHA` and schema marker `m75-v1`;
- `tenant-beta` returned with sentinel `M75-RESTORE-BETA` and schema marker `m75-v1`;
- record reconciliation was **2/2**;
- no unexpected third row was observed in the bounded fixture view.

Therefore the qualifying chain was:

**known synthetic baseline → backup → controlled loss → restore → schema recovery → exact 2/2 record recovery**.

A prior empty-database restore observation is deliberately excluded from the integrity decision because an empty source cannot prove row/schema recovery. The control decision relies on this subsequent fixture-backed rehearsal.

## Post-restore application health

After database restore and fixture reconciliation, the active Technical Preview endpoint `/health/ready` returned:

- status: `ready`;
- service: `oneqay-web`.

The restore rehearsal therefore did not introduce an observed health regression to the active Technical Preview application.

## Recovery metrics

The repository recovery plan requests achieved RPO/RTO to be recorded. Exact wall-clock durations were not independently captured during this interactive rehearsal, so this evidence is intentionally conservative:

- numerical RPO: **not precisely measured**;
- numerical RTO: **not precisely measured**;
- recovery-point interpretation: the known backup restored all **2/2** synthetic records included in that backup;
- recovery-time interpretation: bounded interactive restore completed successfully, but no exact duration is asserted;
- Production SLA: **not claimed**.

This metric limitation does not replace or fabricate a duration. It remains a maturity follow-up for any future Production recovery program.

## Control decision

For the bounded Technical Preview M7.5 evidence catalog, the observed restore rehearsal is sufficient to reclassify:

### `ENGINE:RESTORE_VERIFIED`

**VERIFIED**

Rationale:

- restore was executed against an isolated non-Production database;
- controlled loss existed before restore;
- schema returned after restore;
- deterministic synthetic records returned exactly `2/2`;
- the decision does not rely on capability screenshots alone.

### `RUNTIME:BACKUP_RESTORE`

**VERIFIED**

Rationale:

- bounded database backup acquisition was observed;
- bounded restore execution was observed;
- post-restore fixture integrity was reconciled;
- active Technical Preview health remained `ready` after restore.

## Deterministic evaluator consequence

The runtime qualification catalog contains 20 runtime requirements and 9 engine-profile requirements. The evaluator treats every non-`VERIFIED` item as blocking and returns `EVIDENCE_COMPLETE` only when the blocking list is empty.

With the two decisions above and all previously published controls unchanged, the candidate report becomes:

- **29 VERIFIED**;
- **0 BLOCKED**;
- outcome: **EVIDENCE_COMPLETE**;
- `lifecycle_authority_created=false`.

This is evidence completeness for the M7.5 mandatory runtime/engine catalog only.

It does **not** create:

- M7.6 authority;
- M7.7 authority;
- Phase 0 Exit approval;
- Release authority;
- Production authority;
- Production readiness;
- Production RPO/RTO SLA;
- tenant-selective restore capability;
- permanent business-schema restore certification.

## Security and evidence hygiene

The repository evidence intentionally excludes:

- database/account names and prefixes;
- database usernames/passwords;
- raw `.env` or APP_KEY;
- cPanel account identifiers and home paths;
- raw database backup archives;
- raw screenshots;
- cookies/session identifiers;
- correlation IDs;
- customer, BPJS, payment-provider, or Production records.

Sanitized machine-readable observation:

`docs/evidence/runtime/p1-cpanel-backup-restore-live-runtime-20260816.json`

Reconciled evaluator input:

`docs/evidence/runtime/p1-cpanel-live-runtime-reconciled-20260816-backup-restore.json`

Reconciled evaluator report:

`docs/evidence/runtime/p1-cpanel-live-runtime-reconciled-20260816-backup-restore.report.json`

Attribution: **Lab | zefry**
