# Technical Preview Recovery Plan

- Status: Proposed
- Scope: OneQay Technical Preview v0.0.1
- Source: Issue #23, REC-1

## Objectives

- Provisional RPO: 24 hours.
- Provisional RTO: 4 hours.
- These objectives apply only to synthetic sandbox data and do not define production commitments.

## Backup scope

- Database schema version and synthetic data.
- Tenant-scoped files, if file scope is approved.
- Runtime configuration template without secrets.
- Release manifest, dependency lockfiles, migration version, and integrity hashes.
- Audit records required to explain the recovery event.

## Controls

- Backup artifacts encrypted in transit and at rest where supported.
- Backup access separated from ordinary tenant roles.
- Every artifact has creation time, source release, schema version, checksum, expiry, and owner.
- Secrets are recovered from the approved secret boundary, not embedded in backup archives.
- Retention target is seven days for preview unless hosting capability requires a documented alternative.

## Restore rehearsal

1. Select a known backup and verify checksum.
2. Restore to an isolated sandbox target.
3. Verify schema/migration compatibility.
4. Run health and two-tenant isolation tests.
5. Reconcile record counts and critical sale/stock invariants.
6. Record start/end time, RPO achieved, RTO achieved, operator, outcome, and safe failure details.
7. Destroy rehearsal data after evidence retention needs are met.

## Deployment rollback

- Prefer versioned release directories with an atomic pointer/switch or equivalent recoverable mechanism.
- Migration uses expand/migrate/contract principles; destructive contract is outside preview.
- Rollback decision identifies application release, schema compatibility, backup point, and responsible owner.
- Failed rollback escalates to restore procedure and maintenance state.

## Tenant recovery

Tenant-selective restore is a requirement to investigate, not a capability claimed by this preview. A full restore must never overwrite another tenant silently. JRN-013 remains unresolved for suspension/export/restore/termination semantics.

## Exit evidence

- Hosting backup/export and restore capabilities verified.
- One restore and one deployment rollback rehearsal pass.
- Achieved RPO/RTO recorded.
- Tenant isolation and audit evidence remain intact.
- Product Owner approves exact head.
