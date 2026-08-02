# Contributing to OneQay

## Before starting

Semua kontributor—manusia maupun AI—wajib membaca README, PROJECT_MANIFEST, AI_CONSTITUTION, ARCHITECTURE, ROADMAP, TASKS, dan CHANGELOG, lalu dokumen domain terkait. Jangan mulai implementasi jika scope, acceptance criteria, tenant/security impact, atau decision owner belum jelas.

## Work item

Setiap perubahan memiliki issue/task dengan:

- problem dan desired outcome;
- scope/non-scope;
- actor/tenant/data impact;
- acceptance criteria;
- dependency dan risk class;
- test/evidence;
- migration/rollout/rollback bila relevan;
- documentation impact.

## Branch strategy

| Pattern | Purpose |
|---|---|
| `main` | Stable/releasable |
| `develop` | Integration bila diaktifkan oleh release policy |
| `feature/*` | Feature |
| `bugfix/*` | Non-emergency defect |
| `hotfix/*` | Production critical fix |
| `release/*` | Release stabilization |
| `experiment/*` | Time-boxed, non-production experiment |
| `agent/*` | ChatGPT-assisted scoped work |

Branch berasal dari approved base terbaru. Jangan commit langsung ke branch terlindungi atau force-push shared branch.

## Commits

Gunakan Conventional Commits:

```text
type(optional-scope): imperative summary
```

Allowed types: feat, fix, docs, refactor, perf, test, build, ci, security, chore. Commit atomik, dapat direview, tidak mengandung generated noise, secret, credential, atau unrelated changes.

## Pull requests

PR dimulai sebagai draft dan memuat:

- what/why;
- issue/task reference;
- scope/non-scope;
- architecture/API/database/security/performance/UI/deployment impact;
- test dan evidence;
- migration/rollout/monitoring/rollback;
- documentation/checklist;
- screenshots untuk UI tanpa data sensitif.

PR besar harus dipecah berdasarkan vertical slice atau dependency yang dapat divalidasi.

## Required reviews

Independent review wajib untuk auth, tenant isolation, payment/finance, migration, public API, installer/updater, plugin, AI action, release, dan security control. Author tidak menyetujui perubahan sendiri bila policy membutuhkan separation.

## Documentation update matrix

Minimal PROJECT_MANIFEST, TASKS, CHANGELOG diperiksa setiap perubahan. Perbarui ARCHITECTURE/ADR, API_SPEC, DATABASE, SECURITY, DEPLOYMENT, TESTING, UI_GUIDELINE, INSTALLER, UPDATER, atau RELEASE sesuai dampak.

## Quality checks

Jalankan formatter, lint, type/architecture checks, unit/integration/contract/security/tenant/migration tests sesuai scope, docs link/format check, secret/dependency scan, dan build. Laporkan check yang tidak dapat dijalankan; jangan mengklaim lulus.

## Review etiquette

Komentar fokus pada correctness, risk, evidence, maintainability, dan user outcome. Actionable feedback diberi severity/reason. Thread ditutup setelah fix atau keputusan terdokumentasi. Perubahan tambahan di luar scope dibuat sebagai task terpisah.

## Sensitive security reports

Jangan membuka public issue berisi exploit, credential, atau tenant data. Gunakan private security reporting/channel yang ditetapkan. Credential yang terlanjur terekspos segera direvoke.

## Merge policy

Merge hanya setelah acceptance criteria, required review, CI/quality gate, documentation, migration/rollback, dan conflict resolution selesai. Squash/rebase/merge strategy ditetapkan pada RELEASE.md/repository settings dan harus menjaga traceability.

## After merge

Pantau deployment/release bila berlaku, validasi outcome, tutup task, catat follow-up, dan update changelog/roadmap. Defect baru tidak disembunyikan dalam PR yang sudah selesai.
