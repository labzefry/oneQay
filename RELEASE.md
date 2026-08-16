# oneQay Release Management

## Secure updater release-contract alignment — 2026-08-17

ADR-009 defines the future updater-facing release-control-plane contract. Release packaging for that path follows **Build Once / Deploy Trusted Artifact** and must not require runtime Git checkout, Composer install, npm install, or frontend build on the target host.

The durable updater source must be an approved immutable release asset associated with the canonical `labzefry/oneQay` release identity. Existing short-retention GitHub Actions artifacts remain predecessor Technical Preview evidence and must not be silently treated as the final durable updater channel. Any workflow/release-asset publication change requires separate authority.

Governed Release Manifest v1 must bind product/repository identity, release ID/version/channel, immutable source commit, build/provenance reference, artifact filename/type/size, artifact SHA-256, runtime requirements, supported current-version range, deployment compatibility, migration classification, rollback compatibility, public-bootstrap/layout compatibility, release notes/reference, and attribution `Lab | zefry`.

The initial updater contract accepts only releases classified **NO_SCHEMA_CHANGE**. Schema-changing artifacts fail closed until a separate migration-safe updater architecture, recovery contract, regression evidence, and Product Owner authority exist.

Artifact trust is not established by transport alone. Manifest schema, governed source/release identity, immutable commit identity, artifact digest, compatibility policy, and later signature/provenance maturity must be verified before staging or activation.

This alignment does not create Release authority, Production authority, artifact-publication workflow authority, deployment authority, M7.6 authority, database/schema/migration authority, or cPanel mutation authority. The updater remains default **DISABLED** until separately implemented and qualified.

Attribution: **Lab | zefry**

## Release principles

Release harus reproducible, traceable, signed/verified sesuai maturity, backward-aware, recoverable, dan didukung evidence. `main` mencerminkan kondisi stabil; tag dan release record tidak boleh dipindahkan diam-diam.

## Versioning

Gunakan Semantic Versioning setelah produk memiliki release baseline:

- MAJOR: breaking contract atau incompatible operational change;
- MINOR: backward-compatible capability;
- PATCH: backward-compatible fix/security/performance improvement.

Sebelum 1.0, compatibility tetap wajib dijelaskan; versi 0.x bukan izin untuk breaking change tanpa migration notice.

## Release channels

| Channel | Audience | Stability |
|---|---|---|
| Internal | Engineering/controlled test | Experimental |
| Preview | Selected non-critical evaluation | Pre-release |
| Stable | Production tenants | Supported |

Security hotfix dapat menggunakan expedited process tanpa melewati tenant isolation, migration, backup, dan release verification.

## Release lifecycle

1. scope freeze dan release branch bila diperlukan;
2. version/changelog/manifest update;
3. dependency lock dan build reproducible;
4. full quality/security gate;
5. migration/installer/updater/rollback rehearsal;
6. staging deployment dan acceptance;
7. release approval;
8. tag dan immutable artifact;
9. staged production rollout;
10. observation dan reconciliation;
11. close atau rollback/recovery;
12. retrospective dan follow-up.

## Release contents

- version/tag dan commit SHA;
- release notes dan changelog;
- artifact plus checksum/signature;
- compatibility/runtime/database matrix;
- migration list dan downtime estimate;
- installer/updater instructions;
- backup/rollback/recovery;
- known issues;
- SBOM/license notice sesuai maturity;
- support and monitoring plan.

## Quality gates

No unresolved Critical/High defect/security finding tanpa approved exception. Required test suite, supported upgrade path, clean install, tenant isolation, migration, backup/restore, updater recovery, staging smoke, performance budget, accessibility scope, dan docs harus lulus sesuai release risk.

## Approval matrix

Product Owner menyetujui scope/user impact; Engineering menyetujui implementation/compatibility; Security menyetujui high-risk surface; QA menyetujui evidence; Operations menyetujui deploy/monitor/recovery; Release Manager memverifikasi readiness. Satu orang dapat memegang beberapa role pada tim kecil, tetapi conflict dan separation untuk critical action harus dinilai.

## Rollout

Gunakan cohort/canary bila platform mendukung. Stop conditions mencakup error/latency spike, tenant isolation signal, payment/reconciliation mismatch, migration anomaly, data integrity failure, dan support incident. Feature flag memiliki rollback semantics dan expiry.

## Hotfix

Hotfix berasal dari stable baseline, memiliki narrow scope, regression test, security/tenant review, version patch, changelog, rollout/rollback, dan setelah release disinkronkan ke active development branch. Urgency tidak membenarkan direct untracked production edit.

## Deprecation and EOL

Deprecation mencakup replacement, notice, usage measurement, migration guide, support window, sunset date, dan approval. EOL version tidak menerima feature; security/support policy diumumkan sebelum cutoff.

## Release notes

Tulis untuk pengguna dan operator: added, changed, fixed, security, deprecated, removed, migration, known issues. Jangan membuka detail exploit sebelum coordinated disclosure aman.

## Failed release

Declare incident, hentikan rollout, lindungi data, rollback/recover sesuai rehearsal, komunikasikan status, verifikasi tenant/financial integrity, dan buat postmortem tanpa menyalahkan individu. Tag/artifact bermasalah tidak ditimpa; rilis pengganti memakai versi baru.

## Release Definition of Done

Artifact immutable dan verified, approvals/evidence lengkap, deployment serta health checks lulus, observation window normal, reconciliation valid, docs/changelog/release record tersedia, dan follow-up memiliki owner.
