# oneQay Security Handbook

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
