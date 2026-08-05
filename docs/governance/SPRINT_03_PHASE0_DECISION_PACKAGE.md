# Paket Keputusan Sprint 03 — Penutupan Phase 0 dan Kesiapan Implementasi

## Identitas

- Proyek: oneQay
- Entitas engineering: Lab | zefry
- Sprint: Sprint 03
- Milestone: Penutupan Keputusan Phase 0 dan Kesiapan Implementasi
- Modul: Bukti Keluar Phase 0 dan Kesiapan Teknis
- Exact base: `ce68e711dcc8bdb00b54cd4446db198ed4ab9eec`
- Jenis pekerjaan: dokumentasi, bukti, analisis keputusan, dan governance

Dokumen ini merupakan paket rekomendasi. Dokumen ini tidak menerima ADR, tidak menyelesaikan JRN, tidak mempromosikan GD-007, tidak menyetujui keluar Phase 0, dan tidak memberikan kewenangan source code, merge, deployment, atau release.

## Delta Verification

- PR #40 telah dipublikasikan melalui squash merge sebagai `ce68e711dcc8bdb00b54cd4446db198ed4ab9eec`.
- Phase 0 tetap **In Progress**.
- Application implementation tetap **Blocked**.
- Phase 0 preview exit tetap **Not Ready**.
- ADR-001 sampai ADR-007 tetap **Proposed**.
- GD-007 tetap **Proposed**.
- JRN-003 dan JRN-013 tetap unresolved.
- Hosting evidence tetap **Unverified**.
- Required contexts tetap `governance-validation`, `markdown-lint`, dan `secret-scan`.
- Issue #23 tidak diubah.

## Disposition JRN-003

### Masalah

JRN-003 mencakup undangan pengguna, delegasi role, dan pemulihan akses. Semantik final untuk password reset, MFA recovery, recovery code, session revocation, orphan owner, support-assisted recovery, serta abuse prevention belum disetujui.

### Dampak

- Dampak Phase 0: blocker Critical untuk menerima ADR-004 secara substantif.
- Dampak Technical Preview: Authentication Foundation tidak boleh dimulai sebelum recovery boundary dan authorization matrix disetujui.

### Dependency

- daftar privileged role;
- authorization matrix deny-by-default;
- session inventory dan revocation;
- audit event untuk invitation, delegation, reset, recovery, dan revocation;
- rate limiting dan step-up authentication;
- support-access boundary.

### Risiko jika ditunda

Account takeover, privilege escalation, recovery abuse, session yang tidak dapat direvoke, akses lintas tenant, dan hilangnya audit trail.

### Pilihan

1. First-party recovery penuh dengan TOTP, hashed one-time recovery code, session revocation, dan audited owner-assisted recovery.
2. Recovery terbatas untuk Technical Preview: administrator reset terkontrol, tidak ada self-service MFA recovery, seluruh tindakan privileged dan diaudit.
3. External identity provider; ditunda karena dependency dan hosting belum diputuskan.

### Rekomendasi

Gunakan pilihan 2 untuk Technical Preview, dengan desain yang tidak menutup jalur pilihan 1. Tidak ada recovery otomatis berdasarkan tenant selector, subdomain, atau klaim pengguna tanpa verifikasi terpisah.

### Evidence yang diperlukan

- threat cases dan abuse cases;
- authorization matrix;
- session dan recovery test plan;
- audit event matrix;
- emergency owner recovery procedure;
- exact-head Product Owner decision.

### Decision owner

Product Owner OneQay.

### Acceptance criteria

- recovery actor, precondition, approval, scope, expiry, revocation, notification, dan audit dinyatakan eksplisit;
- raw password, session token, TOTP secret, dan recovery code tidak masuk log;
- semua existing session dapat direvoke setelah high-risk recovery;
- negative tenant isolation dan privilege tests didefinisikan.

### Stop condition

Stop bila recovery masih bergantung pada identity proof yang belum tersedia, authorization matrix belum disetujui, atau support dapat mengambil alih akun tanpa kontrol independen.

## Disposition JRN-013

### Masalah

JRN-013 mencakup tenant suspension, export, restore, dan termination. Lifecycle final, retention, legal hold, entitlement, ownership, encryption, deletion, restore scope, dan cross-tenant recovery belum disetujui.

### Dampak

- Dampak Phase 0: blocker Critical untuk menyatakan recovery, tenant lifecycle, dan data boundary siap.
- Dampak Technical Preview: hanya dua tenant sintetis; suspension/termination produksi tidak boleh diimplementasikan.

### Dependency

- immutable Tenant ID;
- tenant-owned data inventory;
- export format dan authorization;
- backup/restore capability;
- retention dan deletion policy;
- subscription/entitlement boundary;
- audit dan error correlation.

### Risiko jika ditunda

Kehilangan data, restore ke tenant yang salah, akses setelah suspension, export tidak lengkap, termination prematur, dan kebocoran lintas tenant.

### Pilihan

1. Lifecycle produksi lengkap; tidak layak untuk Technical Preview.
2. Boundary sintetis: status tenant aktif/suspended untuk demonstrasi, export sintetis terotorisasi, restore rehearsal terisolasi, tanpa destructive termination.
3. Menunda seluruh lifecycle; mengurangi nilai recovery evidence.

### Rekomendasi

Gunakan pilihan 2. Termination bersifat non-destructive dan hanya berupa skenario terdokumentasi sampai retention, legal, billing, dan deletion policy disetujui.

### Evidence yang diperlukan

- tenant data inventory;
- export authorization dan completeness checklist;
- backup/restore rehearsal dua tenant sintetis;
- restore target binding dan negative isolation tests;
- retention/deletion decision package;
- exact-head Product Owner decision.

### Decision owner

Product Owner OneQay.

### Acceptance criteria

- suspension mencabut akses efektif tanpa menghapus data;
- export tenant-bound, diaudit, dan tidak memuat tenant lain;
- restore menggunakan target Tenant ID eksplisit dan rehearsal evidence;
- termination tidak dieksekusi pada Technical Preview;
- rollback dan escalation path tersedia.

### Stop condition

Stop bila backup tidak dapat dipulihkan, restore scope tidak dapat dibuktikan tenant-bound, atau hosting tidak menyediakan release history dan persistent storage yang aman.

## Paket Verifikasi Hosting

Target P1 cPanel/shared hosting tetap conditional. Seluruh item berikut harus memiliki bukti URL, timestamp, owner, hasil Pass/Fail/Unverifiable, dan catatan risiko:

| Area | Bukti minimum | Status saat ini |
| --- | --- | --- |
| Runtime | versi PHP, extensions, process model | Unverified |
| Database | engine/version, migration access, backup/export | Unverified |
| Scheduler | cron granularity dan reliability | Unverified |
| Queue | worker capability atau safe synchronous fallback | Unverified |
| Storage | persistent path, permission, non-public sensitive storage | Unverified |
| Secret | environment secret handling dan akses terbatas | Unverified |
| TLS/DNS | HTTPS, certificate, domain/subdomain control | Unverified |
| Email | SMTP capability, rate limit, secret handling | Unverified |
| Backup/restore | schedule, retention, restore rehearsal | Unverified |
| Logging | application log, correlation ID, access control | Unverified |
| Deployment | atomic artifact/release directory, no direct overwrite | Unverified |
| Rollback | previous release retention dan procedure | Unverified |
| Resource | CPU, memory, disk, process, request timeout | Unverified |
| Security | document root, SSH/Git, WAF/firewall limitations | Unverified |

Satu mandatory Fail atau Unverifiable pada security, database, deployment, backup/restore, rollback, atau process model menghasilkan rekomendasi evaluasi P2 hardened VPS. Status hosting tetap **Unverified** sampai evidence lengkap.

## Ringkasan Keputusan ADR

| ADR | State | Rekomendasi | Evidence/gate utama | Dampak implementasi |
| --- | --- | --- | --- | --- |
| ADR-001 Backend | Proposed | B1 Laravel/PHP modular monolith | hosting runtime, dependency review, architecture test plan | framework sebagai delivery layer, bukan domain owner |
| ADR-002 Frontend/PWA | Proposed | F1 Vue 3 + Inertia + Vite, online-only | build environment, browser/accessibility, cache threat tests | satu deployment unit; tidak ada business rule di browser |
| ADR-003 Database/tenancy | Proposed | D1 MySQL-compatible shared schema | engine/version, tenant ownership map, restore rehearsal | immutable Tenant ID dan mandatory tenant predicate |
| ADR-004 Authentication | Proposed | A1 first-party session dengan privileged TOTP | JRN-003 disposition, authorization matrix, recovery tests | belum boleh dimulai sebelum recovery boundary disetujui |
| ADR-005 Payment | Proposed | PAY-1 synthetic cash-only | no credential/network call, money/idempotency/audit tests | tidak ada provider atau uang nyata |
| ADR-006 Offline | Proposed | OFF-1 online-only | offline UX dan reconnect tests | tidak ada offline mutation atau replay queue |
| ADR-007 Deployment | Proposed | P1 conditional, P2 fallback | hosting assessment dan recovery rehearsal | tidak ada deployment sebelum capability Pass |

Decision owner seluruh ADR adalah Product Owner OneQay. State tidak berubah melalui dokumen ini.

## Disposition GD-007

- Current state: **Proposed**.
- Masalah: domain event, policy, aggregate, dan bounded context masih hypothesis dan memiliki correction/governance history.
- Hubungan dengan Technical Preview: hanya boundary minimum yang diperlukan untuk synthetic vertical slice dapat dipakai sebagai hipotesis implementasi setelah authority diberikan.
- Dependency: JRN disposition, ADR decisions, tenant/data boundary, payment/offline boundary, dan exact-head approval.
- Risiko: memperlakukan hypothesis sebagai domain final, coupling dini, dan scope expansion.
- Opsi: approve penuh; approve terbatas untuk preview; tetap Proposed.
- Rekomendasi: tetap Proposed, dengan penggunaan terbatas sebagai discovery reference. Jangan promote sebelum independent domain review dan Product Owner decision.
- Acceptance evidence: event glossary, invariant map, aggregate responsibility, bounded-context interaction, unresolved dissent, dan traceability ke journey.

## Checklist Keluar Preview Phase 0

| Kriteria | Status |
| --- | --- |
| Konsistensi handbook dan governance | Sebagian terpenuhi |
| Tidak ada unresolved Critical decision | Belum terpenuhi |
| ADR-001–ADR-007 siap diputuskan | Belum terpenuhi |
| JRN-003 disposition disetujui | Belum terpenuhi |
| JRN-013 disposition disetujui | Belum terpenuhi |
| Hosting evidence lengkap | Belum terpenuhi |
| Tenant isolation expectations | Terdokumentasi, belum diterima |
| Authentication/authorization boundary | Belum disetujui |
| Audit dan error correlation | Baseline ada, evidence belum ada |
| Backup/restore/rollback | Belum terverifikasi |
| Secret handling dan environment constraints | Baseline ada, hosting belum terverifikasi |
| Scope Technical Preview dan exclusion produksi | Terdokumentasi |
| Implementation allowlist | Direkomendasikan, belum disetujui |
| Source-code authority | Tidak diberikan |
| Independent exact-head review | Belum ada untuk paket ini |
| Product Owner exact-head decision | Belum ada untuk paket ini |

Kesimpulan: **NO-GO untuk keluar Phase 0**.

## Entry Criteria Technical Preview

- seluruh ADR minimum memiliki exact-head Product Owner decision;
- JRN-003 dan JRN-013 memiliki disposition yang disetujui;
- hosting P1 Pass atau P2 dipilih secara eksplisit;
- DATA-1 synthetic-only, TEN-1 two synthetic tenants, PAY-1, dan OFF-1 diterima;
- implementation allowlist dan prohibited paths disetujui;
- source-code authority menyebut exact base, branch, module, dan tests;
- required checks dan independent latest-head review tersedia;
- deployment dan release tetap terpisah.

## Rekomendasi Modul Implementasi Pertama

Urutan yang direkomendasikan setelah seluruh gate terpenuhi:

1. **Configuration and Secret Boundary** — membangun konfigurasi tervalidasi, secret-free repository, environment classification, dan safe failure.
2. **Platform Foundation Skeleton** — composition root dan boundary modular tanpa business feature.
3. **Tenant Context Foundation** — immutable tenant context, deny-by-default, dan isolation test harness.
4. **Observability and Error Correlation** — correlation ID, safe error, structured audit/log boundary.
5. **Migration and Seeder Foundation** — migration deterministic dan dua tenant sintetis.
6. **Authentication Foundation** — hanya setelah JRN-003 dan authorization matrix disetujui.

Modul pertama yang direkomendasikan adalah **Configuration and Secret Boundary**, karena menjadi dependency security untuk seluruh modul lain dan dapat dibatasi tanpa memulai authentication atau domain transaction.

## Rekomendasi Batas Kewenangan Implementasi

### Allowed paths yang diusulkan untuk sprint implementasi pertama

- file manifest dependency dan lockfile yang disetujui;
- configuration bootstrap;
- environment example tanpa secret;
- application composition root minimum;
- health/readiness endpoint minimum;
- configuration tests dan secret-scan fixtures;
- dokumentasi pemilik yang terdampak.

### Prohibited paths/actions

- authentication dan user management;
- schema bisnis, payment, inventory, sales, tenant lifecycle produksi;
- provider credential atau production data;
- workflow/ruleset tanpa authority terpisah;
- deployment/release;
- artifact atau source PR #37;
- destructive migration.

## Authority Matrix

| Authority | Status |
| --- | --- |
| Recommendation authority | Tersedia untuk paket ini |
| Product Owner decision | Belum diberikan untuk keputusan substansif paket ini |
| Governance-document authority | Terbatas pada branch dan allowlist Sprint 03 |
| Source-code authority | Tidak diberikan |
| Ready authority | Tidak diberikan |
| Merge authority | Tidak diberikan |
| Deployment authority | Tidak diberikan |
| Release authority | Tidak diberikan |
| Phase 0 exit authority | Tidak diberikan |

## Technical Debt

- JRN-003 dan JRN-013 unresolved;
- hosting evidence Unverified;
- ADR minimum belum Accepted;
- GD-007 tetap Proposed;
- root checkpoint lama masih berdampingan dengan checkpoint `docs/ai/`;
- PR #36/#37 memerlukan archival disposition bila belum ditutup;
- implementation allowlist belum disetujui.

## Open Risks

- premature implementation;
- salah tafsir check success sebagai acceptance;
- shared-hosting limitation;
- cross-tenant data leakage;
- recovery dan restore yang tidak tenant-bound;
- authentication dimulai sebelum JRN-003 selesai;
- scope Technical Preview berkembang menjadi production capability.

## Keputusan Product Owner yang Diperlukan

1. menerima atau mengubah rekomendasi disposition JRN-003;
2. menerima atau mengubah rekomendasi disposition JRN-013;
3. menunjuk owner dan bukti hosting assessment;
4. memutus ADR-001 sampai ADR-007 pada exact head terpisah;
5. mempertahankan atau mempromosikan GD-007 melalui keputusan terpisah;
6. menyetujui atau menolak checklist keluar Phase 0;
7. menyetujui modul implementasi pertama;
8. memberikan atau menolak source-code authority dengan exact base dan allowlist;
9. menetapkan Ready, merge, deployment, dan release authority secara terpisah.

## Keputusan Gate

- Phase 0 exit: **NO-GO**.
- Implementation authority: **NO-GO**.
- Governance decision package review: **GO**, setelah checks dan independent exact-head review.

Attribution: Lab | zefry
