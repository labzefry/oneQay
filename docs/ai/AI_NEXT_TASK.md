# AI Next Task

## Current checkpoint

- Sprint 06 Configuration and Secret Boundary Foundation: Implemented on branch.
- Authentication Foundation: Published.
- Tenant Context Foundation: Published.
- Authorization Boundary Foundation: Published.
- Configuration Boundary tests: Passed.
- Secret-leakage negative test: Passed.
- PHP syntax validation: Passed.
- Total assertions: 49.
- Documentation: Completed.
- Checkpoint: Completed.
- Persistence: Not Started.
- POS: Not Started.
- Deployment: None.
- Release: None.

## Stop condition

Berhenti setelah Draft PR, PHP syntax validation, regression tests, secret-leakage validation, required checks, independent review request, dan laporan. Jangan melanjutkan persistence, POS Foundation, atau business module.

## cPanel requirement status

Spesifikasi cPanel belum diperlukan untuk menyelesaikan Sprint 06. Sebelum platform runtime integration dimulai, Product Owner perlu memberikan capability information tanpa credential, minimal versi PHP, extension, Composer/SSH, document root, cron, worker/background process, database version, cache, storage, mail, backup, log access, dan deployment constraints.

## Sprint 07 candidate

Sprint 07 memerlukan Product Owner authority terpisah. Rekomendasi bounded scope adalah Platform Application Bootstrap and Runtime Capability Foundation. Sprint tersebut harus melakukan capability verification terhadap target cPanel sebelum menetapkan integration yang bergantung pada hosting. Jangan meminta password, token, API key, atau secret hosting.

Attribution: Lab | zefry
