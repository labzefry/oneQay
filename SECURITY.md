# oneQay Security Handbook

## Secure Web Updater threat-model baseline — 2026-08-17

ADR-009 defines the updater/release-control-plane security architecture. The updater is treated as a high-risk remote-code/deployment surface and must remain **DISABLED** until the required privileged identity, verification, extraction, state-machine, activation, rollback, audit, and recovery controls are separately implemented and qualified.

Minimum privileged mutation controls are platform superadmin capability, fresh privileged session, explicit re-authentication, verified TOTP/step-up, CSRF protection, rate limiting, operation/version confirmation, deny-by-default authorization, and sanitized immutable audit. Tenant context alone never authorizes platform release activation.

The updater threat model must explicitly cover unauthorized deployment, session theft, CSRF, arbitrary source/repository/branch input, downgrade/replay, artifact tampering, SSRF/redirect abuse, archive path traversal, symlink/hardlink escape, archive bombs, disk exhaustion, forbidden secret/config overwrite, concurrent installation, stale locks, browser/request interruption, atomic-pointer failure, post-switch health failure, rollback failure, audit bypass, and secret/private-host-path leakage.

Download endpoints and redirects are allowlisted to the governed canonical release source. The updater is not a generic HTTP fetcher. Extraction is isolated and fail-closed; absolute paths, `..` traversal, destination escape, symlink/hardlink escape, special files, duplicate normalized paths, unexpected `.env`/key material, unsupported entries, and excessive extraction size/count/ratio are rejected.

Live `.env`, passwords, database credentials, API tokens, session tokens, TOTP secrets/codes, private account-home paths, and private backup contents must never appear in release artifacts, updater API responses, operation history, audit details, logs, screenshots, or diagnostics.

Updater v1 supports **NO_SCHEMA_CHANGE** only. Database/schema migration support is a separately gated high-risk capability and database rollback must never be inferred from application release rollback.

This security baseline does not authorize implementation, workflow YAML mutation, deployment, cPanel mutation, database/schema/migration work, restore execution, M7.6, M7.7, Phase 0 Exit, Sprint 14, Release, or Production. Production readiness remains **NO-GO**.

Attribution: **Lab | zefry**

## Security objectives

oneQay melindungi confidentiality, integrity, availability, privacy, tenant isolation, financial correctness, dan auditability. Security berlaku sepanjang design, coding, testing, deployment, operation, update, dan decommission.

## Security governance

- Security owner ditetapkan sebelum production.
- Flow auth, payment, POS transaction, tenant admin, data export/delete, installer, updater, plugin, dan AI membutuhkan threat model.
- Critical/High finding memblokir release kecuali risk acceptance tertulis, owner, mitigation, dan expiry.
- Security exception tidak boleh permanen atau tersimpan hanya di percakapan.

## Data classification

| Class | Contoh | Minimum control |
|---|---|---|
| Public | Konten landing yang disetujui | Integrity dan publication control |
| Internal | Dokumentasi operasional non-sensitif | Authenticated access |
| Confidential | Data tenant, laporan bisnis, kontrak | Encryption, least privilege, audit |
| Restricted | Credential, auth secret, payment-sensitive data, key | Strong encryption, minimal access, rotation, no logging |

Setiap dataset memiliki owner, purpose, retention, export, deletion, dan sharing policy.

## Identity and access management

- Unique identity; shared account dilarang.
- MFA wajib untuk platform admin, tenant owner, finance privileged role, support impersonation, release, dan secret access.
- Authorization deny-by-default menggunakan roles dan fine-grained permissions/capabilities.
- Separation of duties diterapkan pada refund/void, financial close, release, secret, dan destructive operation.
- Joiner/mover/leaver process serta periodic access review wajib.
- Impersonation membutuhkan reason, approval policy, visible banner, limited duration, dan audit.

## Session security

Session identifier kuat, rotated setelah authentication/privilege change, menggunakan Secure/HttpOnly/SameSite cookie untuk browser, idle dan absolute timeout, logout/revocation, device/session listing untuk privileged user, dan CSRF protection pada state-changing request.

## Tenant isolation

Tenant membership diverifikasi server-side. Tenant ID dari host/header/token/request tidak dipercaya sendiri. Enforcement mencakup query, cache, queue, file, search, export, backup, log, metric, dan event. Automated isolation test adalah release gate.

## Secret management

- Secret hanya melalui environment atau secret manager.
- Repository, issue, PR, chat, test fixture, screenshot, build log, analytics, dan client bundle dilarang memuat secret.
- Gunakan least-scope, environment-specific credential dengan expiry dan rotation.
- Credential yang terekspos segera direvoke, dirotasi, ditelusuri penggunaannya, dan dicatat sebagai incident.
- Contoh konfigurasi hanya menggunakan placeholder.

## Cryptography

Gunakan library/protocol mapan; custom cryptography dilarang. TLS wajib untuk transport. Encryption at rest diterapkan berdasarkan classification. Key dipisahkan dari ciphertext, memiliki rotation/version, backup, access audit, dan destruction policy. Password menggunakan adaptive password hashing yang disetujui pada ADR keamanan.

## Application security controls

- Input validation pada trust boundary dan domain invariant.
- Context-aware output encoding.
- Parameterized query dan mass-assignment protection.
- CSRF, XSS, injection, SSRF, path traversal, open redirect, deserialization, template injection, dan request smuggling controls sesuai surface.
- File upload: allowlist, content inspection, size limit, safe filename, isolated storage, scanning, dan controlled retrieval.
- Security headers/CSP ditetapkan per client architecture.
- Error response aman dengan correlation ID.

## API security

API menerapkan authenticated identity, object/function/property authorization, tenant enforcement, bounded payload, schema validation, rate limit, quota, idempotency, replay protection, audit, dan version lifecycle. Webhook wajib signed dan timestamped.

## Payment and financial controls

Provider integration harus meminimalkan payment data yang diproses oneQay. Callback diverifikasi signature, amount, currency, merchant/tenant, status, timestamp, dan replay. Reconciliation tidak bergantung pada redirect browser. Refund/void memiliki authorization, reason, audit, dan idempotency.

## Supply-chain security

- Dependency dan action harus pinned sesuai policy.
- License, provenance, vulnerability, maintenance, dan transitive risk direview.
- Build reproducible dan artifact memiliki checksum/signature sesuai release maturity.
- SBOM dibuat untuk release produksi setelah build stack tersedia.
- Secret scan, dependency scan, static analysis, dan artifact scan menjadi quality gate.

## Cloudflare integration

Gunakan scoped API token per environment/zone. Operation DNS, SSL, wildcard, cache purge, dan zone validation memiliki allowlist, audit, idempotency, dry-run bila relevan, rate-limit handling, dan rollback. Token tidak pernah dikirim ke client.

## Plugin and marketplace security

Plugin tidak boleh mengakses database atau secret secara langsung. Sebelum activation diperlukan signed manifest, publisher verification, capability grant, tenant consent, compatibility check, sandbox/isolation, quota, network policy, update/rollback, audit, review, revocation, dan platform kill switch.

## AI security and privacy

- Provider dan data residency disetujui sebelum data tenant dikirim.
- Prompt injection dianggap untrusted input.
- Retrieval mengikuti authorization sumber, bukan hanya similarity.
- Restricted data disaring; raw secret tidak boleh masuk model context.
- Tool/action menggunakan allowlist, scoped credential, confirmation, idempotency, dan audit.
- Model output tidak menjadi otorisasi atau source of truth.
- Evaluation mencakup leakage, cross-tenant retrieval, jailbreak, unsafe action, hallucination, dan cost abuse.

## Logging and audit

Security log mencakup auth, authorization denial, privileged action, tenant switch, impersonation, credential lifecycle, export/delete, payment, installer/updater, plugin, release, dan configuration change. Log terlindungi dari perubahan dan tidak berisi secret/Restricted data.

## Vulnerability management

1. intake dan confidential triage;
2. severity dan affected scope;
3. containment;
4. fix serta regression test;
5. coordinated release;
6. verification;
7. post-incident/root-cause;
8. documentation dan prevention.

Target remediation ditetapkan sebelum production berdasarkan severity dan exploitability.

## Incident response

Incident plan mencakup detect, declare, contain, preserve evidence, eradicate, recover, communicate, tenant impact assessment, legal/compliance escalation, postmortem, dan action tracking. Token exposure diperlakukan sebagai incident meskipun belum ada bukti penyalahgunaan.

## Security Definition of Done

- Threat model/abuse case sesuai risk.
- Auth, authorization, tenant, validation, secret, audit, dan privacy controls tersedia.
- Security tests dan scan lulus.
- Finding ditutup atau exception formal aktif.
- Monitoring, incident signal, rollback, dan docs tersedia.