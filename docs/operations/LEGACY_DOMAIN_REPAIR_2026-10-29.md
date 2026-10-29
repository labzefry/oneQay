# oneQayDev live deployment and legacy domain completion

Author by Lab | zefry.

## Actual deployment evidence

On-host result observed again on 29 October 2026 confirms application
`95d9286f4698b168f7c419b67ac3c6583f844910` (PR #990), certified run
40732174597, artifact SHA256
`e2f043d6f7b883d6a1d9f4f66d4f1099eb383499a475105dab196520a9768a5f`.
The release has 6,426 certified files, 108 tables and 41 migration receipts.
No schema migration or business reset occurred. Canonical host remains
`https://oneqaydev.n07.my.id`; business domain base is `https://n07.my.id`.
Wildcard routing and HTTPS/certified asset checks passed. Unknown business host
returns404. Central home, owner login, business login and registration returned200.

The current every-minute cPanel cron points to the private95d operator `work`.
Prior observed worker result: failed=false, mail/health/campaign completed.
Expanded predecessor d32 was removed only after verified private ZIP creation;
archive SHA256 `131e9499a4bda99e4370e765c3374f6c348cf37acce1112282d50df2024860e0`.
Current cPanel inode use observed29October:154,817/250,000 (61.93%).

Windows0.2.7 was signed and published by workflow40700126251. Installer asset
`oneQay.POS_0.2.7_x64-setup.exe`, bytes4286504, SHA256
`fff742be6f462e2073c000211dcee2feb4ba4ed9759e1c251a0ab4488c537576`.
Trust key and stable-v2 channel are unchanged. Check for updates again before
installing, to replace cached0.2.6 metadata. Actual installed-device update and
printer/offline cash acceptance remain unverified.

## Remaining legacy account gap

A private count-only host report found one active account, one pending account,
one existing business profile, zero domain history rows and no active account
without a profile. No business ID, email, credentials or transaction data is
published here. Thus deployment correctly reported no active business host to
probe. New registration already claims a domain; a pre-domain legacy profile
needs one create-only data repair. Pending registration must stay pending.

## Versioned repair contract: legacy-business-domain-v1

Risk: High (tenant routing/data repair), owner: repository Product Owner.
Scope: exactly one active canonical business with an existing matching tenant
profile and absolutely no domain history, active or retired. Do not manufacture
an authenticated platform-owner session or weaken TOTP/RBAC. Preserve all
business/identity/credential/permission rows, profile business type and MFA policy.
Only insert one domain and increment that profile's optimistic version; enqueue
one bilingual owner notice and one clearly attributed operator audit atomically.

The maintenance CLI is separate from the deployed application and exposes no
HTTP endpoint. `binding.json` supplies explicit private deployment/package hashes
and PR number; its canonical hash is required on each CLI invocation. The
installed operator is hashed before inclusion, validates its certification
request, active environment/bridge/routing, and all6,426 certified source files.
Exact source and runtime hashes bind the target. `inspect` writes a private0600
plan and summary; repeated inspection preserves an unchanged plan. A deterministic
name-derived slug skips all reserved/claimed/retired names. Multiple eligible
accounts, stale versions, mismatch, suspension or domain history stop execution.

`apply` additionally requires a fresh, unedited native repository-owner GitHub
comment on the configured PR, binding the complete plan hash and operator hash.
The shared account registry lock and existing durable PlatformStore::once provide
serialization and crash-safe replay. A private0600 scoped preimage is written and
read-verified before mutation. Transaction failure rolls back domain, profile,
mail and audit; exact replay returns the durable receipt without duplicates.
Later retirement or suspension is never undone by replay. This is a versioned
data repair, not a DDL migration; existing schema and receipt set remain unchanged.

After commit, separately probe actual business login200, tenant owner route404,
and unknown-host404 with verified TLS. If HTTPS acceptance fails, retain the
committed receipt and use `verify`; do not rerun provisioning, reset data or
restore the whole database over later business transactions. If operational
rollback is necessary, use the normal MFA-protected platform domain management
to rename/retire the address; never delete its historical reservation. The
preimage provides forensic scope, not permission to bypass normal management.

## Validation and completion

Disposable SQLite/MySQL rehearsal covers reserved names, stale plans, history,
pending status, collision, failed preimage, failure after insertion, exact replay,
mail/audit uniqueness, account/credential preservation, and no domain resurrection.
Independent review, exact-head owner merge evidence and fresh on-host repair grant
are required before application. Append actual CI/review/merge/inspect/apply/HTTPS
results as PR evidence. Do not claim authenticated login or SMTP inbox delivery
from anonymous HTTPS checks or a queued email.

At source preparation time, legacy repair is NOT YET APPLIED. The application
release remains95d; this tools-only change does not require replacing web assets
or republishing the already signed Windows release.
