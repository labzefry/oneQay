# oneQay Testing Strategy

## Objectives

Testing memberi evidence bahwa oneQay benar, aman, terisolasi antar tenant, kompatibel, cepat, accessible, dapat diinstal/diupdate, dan dapat dipulihkan. Coverage percentage bukan satu-satunya ukuran kualitas.

## Test layers

| Layer | Focus |
|---|---|
| Static | Format, lint, type, architecture, secret, dependency |
| Unit | Domain rule dan application decision |
| Integration | Database, cache, queue, storage, vendor adapter |
| Contract | API, event, webhook, consumer/provider compatibility |
| Component | Module melalui public interface |
| End-to-end | Critical user journey |
| Operational | Installer, updater, migration, backup, rollback, DR |

## Mandatory risk suites

### Tenant isolation

Uji object ID lintas tenant, query/filter, cache, file, export, job, event, search, backup/restore, admin impersonation, dan error leakage. Test harus mencoba serangan, bukan hanya happy path.

### Financial and POS correctness

Uji precision, rounding, tax, discount, currency, duplicate request, concurrent sale, partial failure, void/refund, shift close, reconciliation, timezone/business date, dan audit.

### Authentication and authorization

Uji login, MFA, session rotation/expiry/revocation, role change, object/function/property authorization, CSRF, brute force/rate limit, privilege escalation, dan step-up.

### Migration

Uji clean install, upgrade dari versi supported, large dataset rehearsal, expand/contract compatibility, resume after failure, verification, dan recovery.

### Installer/updater

Uji environment failure, permission, invalid config, interrupted process, corrupt package, invalid signature, backup failure, migration failure, health failure, rollback, lock, dan rerun/idempotency.

## Test data

- Default synthetic; production data dilarang.
- Masked data memerlukan approved process dan residual-risk review.
- Fixture deterministic, minimal, tenant-explicit, dan tidak menggunakan real credential/PII.
- Generator mencakup boundary, Unicode, locale, timezone, large value, dan invalid case.

## Environments

Unit test tanpa network/filesystem nyata. Integration test memakai dependency nyata yang relevan dan terisolasi. Staging menyerupai production topology/configuration tanpa data produksi mentah.

## Performance testing

Tetapkan budget per critical flow: p95/p99 latency, throughput, error, database query, payload, memory/CPU, job completion, dan concurrency. Test mencakup baseline, load, stress, spike, soak, large tenant, dan noisy-neighbor isolation.

## Security testing

Gunakan threat-model-driven tests, SAST, dependency/secret scan, API authorization test, injection/fuzzing, upload, SSRF, session, webhook, supply-chain, configuration, dan periodic penetration test sebelum milestone berisiko.

## UI and accessibility testing

Uji keyboard, focus, screen reader semantics, contrast, zoom/reflow, touch target, localization, loading/empty/error/stale state, responsive layout, offline transition, dan supported browser/device matrix.

## AI testing

Bila AI diaktifkan, uji groundedness, cross-tenant leakage, authorization-aware retrieval, prompt injection, unsafe action, human confirmation, refusal, fallback, latency, cost, model/version regression, dan data retention policy.

## Quality gates

Pull request minimal:

- formatter/lint/type/architecture checks;
- unit dan relevant integration tests;
- tenant/security regression sesuai scope;
- contract/migration test bila terdampak;
- docs link/format check;
- no Critical/High unresolved finding.

Release menambahkan full regression, installer/updater, upgrade path, performance/security sesuai risk, staging smoke, backup/restore evidence, dan release checklist.

## Flaky test policy

Flaky test adalah defect. Quarantine hanya sementara dengan issue, owner, reason, expiry, dan continued visibility. Retry tidak boleh menyembunyikan systematic failure.

## Defect severity

| Severity | Contoh | Release impact |
|---|---|---|
| Critical | Data loss, tenant leak, auth/payment compromise | Block |
| High | Critical flow gagal tanpa safe workaround | Block |
| Medium | Fitur penting terganggu dengan workaround | Risk decision |
| Low | Minor/cosmetic | Track |

## Test evidence

Simpan version/commit, environment, suite, result, duration, failure, artifact/log aman, approver, dan exception. Jangan menyimpan secret atau data Restricted.

## Governance required-check validation

The workflow
`.github/workflows/governance-required-checks.yml` provides three stable
job-level checks for pull requests targeting `main`:

- `governance-validation`;
- `markdown-lint`;
- `secret-scan`.

### Governance validation

`governance-validation` inspects the pull-request changed-file set and fails
when restricted environment or private-key filenames, committed dependency
directories, or an empty validation scope are detected.

### Markdown validation

`markdown-lint` validates changed Markdown files for:

- non-empty file content;
- trailing-whitespace absence;
- final newline presence.

This is a structural Markdown guard. It does not replace a future
standards-based Markdown linter selected through the relevant quality-tool ADR.

### Secret-pattern validation

`secret-scan` scans tracked repository content for high-risk private-key,
GitHub-token, and AWS access-key patterns.

This regex-based guard is a baseline control. It does not replace a future
dedicated secret-scanning platform or incident-response process.

### Acceptance criteria

A governance workflow run is acceptable when:

- all three job-level checks complete successfully;
- the results are attached to the exact pull-request head commit;
- no workflow job accesses repository secrets;
- no build, migration, release, publish, or deployment action executes;
- the pull request remains subject to independent review and repository
  protection rules.

Passing these checks does not approve application implementation, merge,
deployment, release, Phase 0 exit, ADR acceptance, or source-code authority.

Attribution: Lab | zefry

## Definition of Done

Acceptance criteria teruji, suites sesuai risk lulus, negative/abuse/boundary cases tersedia, test deterministic, evidence tersimpan, defect ditangani, dan TESTING/TASKS/CHANGELOG diperbarui.
