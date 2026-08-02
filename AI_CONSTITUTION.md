# OneQay AI Constitution

## Purpose

Dokumen ini adalah aturan permanen bagi ChatGPT Pro, Gemini Pro, AI coding agent, automation agent, dan alat generatif lain yang bekerja pada OneQay. Instruksi task tidak boleh menurunkan standar keamanan, kualitas, dokumentasi, atau persetujuan yang ditetapkan di sini.

## Rule hierarchy

1. Hukum, lisensi, dan kebijakan keamanan yang berlaku.
2. Keputusan Product Owner dan repository protection.
3. AI_CONSTITUTION.md.
4. PROJECT_MANIFEST.md dan ADR berstatus Accepted.
5. Dokumen domain terkait.
6. Issue, task, dan prompt pekerjaan.

Jika terjadi konflik, AI wajib berhenti pada bagian yang konflik, menyajikan bukti, dan meminta keputusan. AI tidak boleh memilih aturan yang paling mudah.

## Mandatory preflight

Sebelum mengubah repository, AI wajib membaca versi terbaru:

- README.md
- PROJECT_MANIFEST.md
- ARCHITECTURE.md
- TASKS.md
- CHANGELOG.md
- ROADMAP.md

Kemudian baca dokumen yang terkait dengan scope: API_SPEC, DATABASE, SECURITY, DEPLOYMENT, TESTING, CODING_STANDARDS, UI_GUIDELINE, INSTALLER, UPDATER, CONTRIBUTING, dan RELEASE.

AI wajib memeriksa branch, diff, status repository, issue/PR terkait, dan perubahan pengguna yang belum selesai. Perubahan pengguna tidak boleh ditimpa.

## Operating principles

AI wajib:

1. memahami tujuan bisnis, actor, data, risiko, dan acceptance criteria;
2. membuat perubahan terkecil yang lengkap dan koheren;
3. mempertahankan backward compatibility kecuali breaking change disetujui;
4. menerapkan tenant isolation, least privilege, secure defaults, dan auditability;
5. memisahkan domain, application, interface, dan infrastructure concerns;
6. menggunakan migration untuk setiap perubahan database;
7. menggunakan API versioning dan deprecation policy;
8. menambahkan atau memperbarui test yang relevan;
9. menjalankan quality gate dan melaporkan hasil sebenarnya;
10. memperbarui living documentation;
11. menggunakan Conventional Commits;
12. menyebut asumsi, risiko, keterbatasan, dan pekerjaan tersisa secara jujur.

## Prohibited actions

AI dilarang:

- membuat breaking change tanpa persetujuan eksplisit dan migration path;
- menghapus modul, data, test, dokumentasi, atau kontrol keamanan tanpa impact analysis;
- mengubah database tanpa migration, compatibility plan, backup, dan rollback;
- mengubah API publik tanpa versioning, contract test, dan deprecation notice;
- membuat duplicate business logic;
- hardcode secret, credential, URL lingkungan, tenant identifier, atau konfigurasi operasional;
- mengakses data tenant tanpa tenant context yang tervalidasi;
- menganggap subdomain sebagai bukti otorisasi;
- menonaktifkan security check, lint, test, branch protection, atau audit log agar pipeline lulus;
- menyembunyikan error, membuat test palsu, atau mengklaim validasi yang tidak dijalankan;
- memasukkan data produksi, personal data, token, atau credential ke prompt, log, fixture, commit, atau issue;
- menggunakan dependency tanpa review license, security, maintenance, dan necessity;
- melakukan force-push, rewrite history bersama, atau merge langsung ke branch terlindungi;
- menambahkan atribusi yang menyatakan kode dibuat oleh AI.

## Change classification

| Class | Contoh | Minimum control |
|---|---|---|
| Documentation | Klarifikasi tanpa perubahan perilaku | Review dokumentasi |
| Low risk | Refactor internal tanpa contract change | Unit test + review |
| Medium risk | Fitur tenant-scoped baru | Test lengkap + security review |
| High risk | Auth, payment, update, plugin, data migration | Threat model + staged rollout + approval |
| Critical | Breaking API, destructive migration, cross-tenant operation | Explicit approval + rehearsal + rollback + audit |

AI harus mengklasifikasikan perubahan sebelum implementasi.

## Required change record

Setiap PR harus menjelaskan:

- masalah dan tujuan bisnis;
- scope dan non-scope;
- keputusan desain dan alternatif;
- dampak tenant, API, database, security, performance, UI/UX, deployment, dan compatibility;
- test serta hasil quality gate;
- migration, rollout, monitoring, rollback, dan recovery;
- dokumen yang diperbarui.

## Database rules

- Migration bersifat versioned, deterministic, idempotent bila relevan, dan dapat direhearsal.
- Destructive change menggunakan expand-migrate-contract; contract dilakukan setelah kompatibilitas terverifikasi.
- Query tenant wajib menyertakan tenant scope pada enforcement layer, bukan bergantung pada disiplin pemanggil.
- Data migration besar harus resumable, observable, dan memiliki batas beban.

## API rules

- Contract lebih dahulu daripada implementation.
- Perubahan additive diprioritaskan.
- Idempotency wajib untuk operasi finansial dan retry-prone.
- Error tidak boleh membocorkan stack trace, secret, atau detail tenant lain.
- Public API membutuhkan authentication, authorization, rate limit, quota, audit, dan lifecycle policy.

## Security and privacy rules

- Gunakan data minimization, purpose limitation, encryption, retention, dan deletion policy.
- Secret hanya melalui environment atau secret manager; contoh menggunakan placeholder.
- Credential yang terekspos harus segera direvoke, tidak boleh digunakan kembali.
- AI feature harus memiliki data boundary, provider policy, redaction, consent, dan human override.
- Security finding Critical/High memblokir release kecuali exception formal yang kedaluwarsa.

## Performance rules

- Hindari N+1 query, unbounded collection, synchronous long task, dan cache tanpa tenant-aware key.
- Tetapkan budget latency, throughput, resource, dan payload pada flow kritis.
- Optimasi harus berbasis evidence; perubahan performance tidak boleh mengorbankan correctness atau security.

## Testing rules

AI wajib memilih kombinasi unit, integration, contract, tenant-isolation, security, performance, migration, end-to-end, installer, updater, dan rollback test sesuai risiko. Test yang flaky harus diperbaiki atau dikarantina dengan owner dan expiry, bukan diabaikan.

## Documentation rules

Minimal per perubahan:

- PROJECT_MANIFEST.md bila status/kapabilitas berubah;
- TASKS.md untuk progres dan pekerjaan tersisa;
- CHANGELOG.md untuk perubahan yang terlihat atau mengubah engineering baseline.

Dokumen arsitektur/API/database/security/deployment/testing/UI harus ikut berubah bila domainnya terdampak. Dokumentasi lama tidak boleh dihapus; gunakan status superseded dan tautkan penggantinya.

## GitHub rules

- Satu branch untuk satu scope koheren.
- Tidak melakukan perubahan langsung ke main.
- PR draft digunakan sampai acceptance criteria dan quality gate terpenuhi.
- Review wajib independen untuk auth, payment, tenant isolation, migration, installer, updater, plugin, dan release.
- Commit harus atomik dan mengikuti Conventional Commits.

## Stop conditions

AI wajib berhenti dan meminta keputusan bila:

- requirement ambigu mengubah hasil secara material;
- tindakan membutuhkan credential atau izin baru;
- ditemukan potensi data loss, cross-tenant exposure, atau irreversible action;
- baseline dokumentasi saling bertentangan;
- test penting tidak dapat dijalankan;
- perubahan melampaui scope yang disetujui;
- legal, license, privacy, atau compliance decision belum tersedia.

## Completion protocol

Saat selesai, AI melaporkan outcome, file yang berubah, test yang dijalankan, hasilnya, risiko tersisa, migration/rollback, dan next action. Status tidak boleh disebut selesai bila perubahan belum tersimpan pada SSOT atau quality gate wajib belum lulus.
