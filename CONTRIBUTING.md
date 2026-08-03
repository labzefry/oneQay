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

Setiap PR juga wajib menyediakan governance evidence berikut:

- risk class dan decision owner;
- full exact head SHA yang direview;
- URL approval Product Owner atau reviewer yang menyebut PR dan exact head;
- status review, unresolved thread, CI/check, atau approved deferral;
- issue yang harus tetap open dan closure criteria;
- status keputusan sebelum/sesudah perubahan, termasuk **Proposed**, **Under Review**, **Approved**, atau **Blocked**;
- konfirmasi apakah scope hanya dokumentasi dan apakah application source code tidak berubah.

Approval konten tidak otomatis mengotorisasi ready-for-review, merge, issue closure, release, atau status promotion. Perubahan head membatalkan approval sebelumnya.

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

Untuk setiap merge yang diotorisasi, lakukan pre-merge fetch ulang dan cocokkan:

1. nomor PR, state, draft/ready state, base, dan full exact head SHA;
2. approval evidence pada exact head terbaru;
3. required review dan unresolved conversation;
4. status checks atau documented deferral yang disetujui;
5. mergeability/conflict serta changed-file scope;
6. acceptance checklist dan issue closure gate;
7. status keputusan pada dokumen kanonis.

Mismatch membatalkan authority merge dan pekerjaan harus berhenti untuk keputusan Product Owner. Merge teknis tanpa decision statement tidak menjadi approval substantif.

## Issue closure policy

Issue hanya dapat ditutup setelah acceptance checklist dan evidence lengkap, termasuk reviewed head, validation result, approval URL, serta final merge commit bila merge memang menjadi deliverable. Issue yang menjadi blocker tetap open sampai dependency selesai. Auto-close keyword tidak boleh digunakan untuk melewati closure review.

Jika merge atau closure terjadi prematur, buka kembali issue terkait, audit exact head, catat recurrence/root cause, dan minta keputusan pasca-merge terpisah. Jangan meratifikasi atau mempromosikan status secara otomatis.

## Repository protection verification

Repository Owner memverifikasi required review, stale-approval dismissal, conversation resolution, required status checks, dan bypass restriction. Bila tool tidak mengekspos settings, laporkan sebagai limitation dan gunakan merge history hanya sebagai effective evidence, bukan bukti konfigurasi. Perubahan settings membutuhkan approval terpisah.

Untuk perubahan governance berisiko **High** atau **Critical**, direct repository-protection/ruleset evidence merupakan blocking precondition sebelum ready transition dan merge. Evidence harus menyediakan URL atau rekaman GitHub yang dapat diaudit untuk required PR review, stale-approval dismissal, conversation resolution, required status checks, dan bypass restriction.

Jika direct evidence tidak tersedia atau tidak dapat diverifikasi, lifecycle action tetap diblokir kecuali terdapat formal risk acceptance dari Product Owner. Risk acceptance wajib mencantumkan owner, scope, alasan, compensating controls, evidence URL, masa berlaku, nomor PR, dan full exact head SHA. Evidence atau acceptance yang pending, tidak lengkap, kedaluwarsa, atau tidak mengikat head terbaru membatalkan authority. Perubahan head membatalkan approval dan risk acceptance sebelumnya kecuali keputusan baru secara eksplisit mengikat head terbaru.

Formal risk acceptance wajib memiliki Product Owner approval URL tersendiri dan decision statement yang mengikat risk scope, nomor PR, serta full exact head SHA. General content approval atau reviewer approval bukan formal risk acceptance. Risk acceptance hanya menggantikan direct protection-evidence requirement yang disebut dalam scope; ready transition, merge, status promotion, release, dan issue closure tetap membutuhkan evidence, gate, serta authority masing-masing.

Direct protection evidence atau formal risk acceptance yang valid juga diperlukan sebelum issue governance terkait ditutup. Effective merge history tidak membuktikan configured protection. PR yang sudah merged tidak dapat memperoleh merge authority secara retrospektif dan hanya dapat diproses melalui audit exact-head serta keputusan pasca-merge terpisah tanpa automatic ratification.

## After merge

Pantau deployment/release bila berlaku, validasi outcome, rekonsiliasi dokumen dan issue, catat follow-up, dan update changelog/roadmap. Tutup task hanya setelah issue closure policy terpenuhi. Defect baru tidak disembunyikan dalam PR yang sudah selesai.
