# OneQay API Specification and Governance

## Scope

Dokumen ini menetapkan governance untuk REST API internal, public API, webhook, dan integration contract. Endpoint detail akan dikelola sebagai machine-readable API specification setelah stack dipilih.

## API principles

- Contract first dan backward compatible by default.
- Authentication tidak menggantikan authorization.
- Tenant context harus tervalidasi pada setiap tenant-scoped operation.
- Resource-oriented, predictable, bounded, observable, dan secure.
- Internal implementation detail tidak bocor ke contract.

## Base contract

Versi major ditempatkan pada path atau media type sesuai ADR. Baseline yang diusulkan:

```text
/api/v1/{resource}
```

Public dan internal API memiliki hostname/audience, credential, quota, documentation, serta lifecycle terpisah.

## Request context

Setiap request terautentikasi menghasilkan actor ID, tenant ID, permissions, outlet scope bila relevan, locale/timezone, correlation ID, dan client/version metadata. Header tenant dari client tidak pernah dipercaya tanpa membership validation.

## Authentication and authorization

- Authentication mechanism ditetapkan melalui security ADR.
- Authorization menggunakan policy/capability dan resource context.
- Privileged operation menggunakan MFA/step-up bila risikonya tinggi.
- API key memiliki scope, tenant, expiry, rotation, last-used, dan revocation.
- Public API menerapkan rate limit, quota, abuse detection, dan audit.

## Methods and semantics

| Method | Semantics |
|---|---|
| GET | Safe, tidak mengubah state bisnis |
| POST | Create/command; gunakan idempotency untuk retry-prone operation |
| PUT | Full replacement hanya bila contract mendukung |
| PATCH | Partial update dengan field-level authorization |
| DELETE | Lifecycle operation; destructive behavior memerlukan guardrail |

HTTP status harus sesuai outcome contract; business error tidak selalu dipetakan menjadi 200.

## Standard response

Resource response mengikuti schema endpoint. Metadata seperti correlation ID dapat dikirim melalui header. Collection memiliki bounded pagination dan link/cursor.

Error envelope:

```json
{
  "error": {
    "code": "STABLE_MACHINE_CODE",
    "message": "Safe actionable message",
    "correlation_id": "opaque-id",
    "details": []
  }
}
```

Stack trace, SQL, file path, credential, provider secret, dan data tenant lain dilarang pada response.

## Validation

- Unknown field policy harus konsisten.
- Field memiliki type, format, length, allowed value, nullable, default, dan semantic rule.
- Server selalu menghitung field yang authoritative.
- Monetary request menyertakan currency dan fixed precision.
- Time menggunakan ISO 8601 dengan timezone/offset; business date dibedakan dari instant.

## Pagination, filter, and sort

- Default dan maximum page size wajib.
- Cursor pagination digunakan untuk collection besar/berubah cepat.
- Filter/sort field menggunakan allowlist.
- Query complexity dan response size dibatasi.
- Total count opsional jika mahal dan contract menjelaskan semantics.

## Idempotency

Idempotency wajib untuk payment, sale/order creation, refund, subscription mutation, DNS automation, webhook processing, dan operasi lain yang dapat diulang. Scope key mencakup tenant, actor/client, operation, dan expiry. Request yang sama menghasilkan outcome sama; payload berbeda dengan key sama ditolak.

## Concurrency

Gunakan resource version/ETag atau expected version pada conflicting update. Lost update tidak boleh diselesaikan dengan last-write-wins tanpa keputusan domain eksplisit.

## Versioning

- Additive optional field dapat masuk pada versi aktif setelah compatibility review.
- Removal, rename, semantic change, required field baru, dan enum narrowing adalah breaking change.
- Breaking change membutuhkan major version, migration guide, parallel support, deprecation notice, dan sunset approval.
- Consumer contract test memblokir incompatible change.

## Deprecation lifecycle

1. announce dan dokumentasikan replacement;
2. ukur consumer usage;
3. beri migration window;
4. kirim deprecation/sunset signal;
5. verifikasi migration;
6. remove hanya setelah approval dan changelog.

## Webhooks

- HTTPS only;
- signed payload dengan timestamp dan key rotation;
- unique event ID dan replay window;
- at-least-once delivery; consumer harus idempotent;
- bounded retry dan dead-letter handling;
- delivery log aman, redelivery control, dan tenant isolation;
- payload schema versioned.

## Rate limiting and quotas

Limit dapat ditetapkan per tenant, credential, endpoint, plan, dan risk. Response memberi safe retry guidance. Limit tidak boleh memungkinkan noisy tenant menghabiskan resource tenant lain.

## Observability

Catat method, route template, status, latency, tenant-safe dimension, client type, correlation ID, dan error code. Jangan mencatat raw token, sensitive query, full payload, atau high-cardinality uncontrolled value.

## API security tests

- broken object/property/function authorization;
- tenant identifier tampering;
- injection dan mass assignment;
- rate-limit/credential abuse;
- SSRF pada URL-taking endpoints;
- replay/idempotency;
- schema fuzzing dan oversized payload;
- information leakage;
- webhook signature and replay.

## Definition of Done

Endpoint selesai jika contract, example, auth policy, tenant behavior, error, idempotency, rate limit, audit, tests, observability, migration/deprecation impact, dan changelog tersedia serta direview.
