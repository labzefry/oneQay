# OneQay Architecture

## Architecture goals

OneQay menggunakan **Modular Monolith First** dengan Clean Architecture dan Domain-Driven Design. Tujuannya adalah menyediakan sistem yang sederhana untuk dioperasikan pada tahap awal, namun memiliki boundary yang cukup kuat untuk berkembang tanpa menulis ulang business logic.

## Context

```mermaid
flowchart TD
    U["Business Users"] --> C["Web, PWA, Android"]
    A["Platform & Tenant Admin"] --> C
    C --> P["OneQay Platform"]
    P --> X["Payment, Cloudflare, Messaging"]
    P --> D["Tenant Data & Files"]
    P --> O["Observability & Audit"]
```

## Logical layers

| Layer | Responsibility | Allowed dependency |
|---|---|---|
| Domain | Entity, value object, invariant, domain service/event | Domain only |
| Application | Use case, orchestration, port, transaction boundary | Domain |
| Interface | HTTP, CLI, jobs, UI adapter, serialization | Application |
| Infrastructure | Database, cache, queue, storage, vendor integration | Application ports |

Dependency mengarah ke dalam. Domain dan application tidak boleh bergantung pada framework atau vendor.

## Modular topology

```mermaid
flowchart TD
    E["Experience Layer"] --> G["Application Gateway"]
    G --> C["Core Commerce Modules"]
    G --> B["Business Management Modules"]
    G --> P["Platform Modules"]
    C --> I["Integration Ports"]
    B --> I
    P --> I
```

### Core commerce modules

- Organization, Outlet & Device
- Catalog & Pricing
- Inventory & Warehousing
- Sales & Point of Sale
- Purchasing & Supplier
- Customer & Loyalty

### Business management modules

- Finance & Accounting
- Reporting & Analytics
- Content Management

### Platform modules

- Tenant & Subscription
- Identity & Access Management
- Audit & Platform Operations
- Integration Hub
- Marketplace & Plugin Management
- AI Assistance

Daftar ini berstatus Proposed sampai domain discovery dan ADR menyetujuinya.

## Module contract

Setiap modul harus memiliki:

- bounded context dan ubiquitous language;
- public application interface;
- owned schema/table namespace;
- authorization policy;
- domain events;
- failure semantics;
- observability signals;
- test boundary;
- owner dan lifecycle status.

Modul tidak boleh membaca atau menulis tabel milik modul lain secara langsung. Interaksi sinkron melalui application contract; interaksi asinkron melalui event/outbox.

## Request flow

```mermaid
sequenceDiagram
    participant Client
    participant Gateway
    participant TenantContext
    participant UseCase
    participant Repository
    Client->>Gateway: Authenticated request
    Gateway->>TenantContext: Resolve and authorize tenant
    TenantContext->>UseCase: Validated actor + tenant
    UseCase->>Repository: Tenant-scoped operation
    Repository-->>UseCase: Domain result
    UseCase-->>Client: Contract response + correlation ID
```

## Multi-tenant architecture

Tenant context terdiri dari immutable tenant ID, actor, roles/permissions, outlet scope, timezone, currency, locale, subscription entitlement, dan correlation ID.

Enforcement wajib terjadi pada:

- authentication dan tenant membership;
- authorization policy;
- repository/query boundary;
- cache key dan lock;
- queue/job payload;
- object storage path;
- search index;
- event envelope;
- audit log dan metrics dimension.

Cross-tenant operation hanya tersedia pada platform administration yang eksplisit, diaudit, menggunakan step-up authentication, dan memiliki purpose limitation.

## Data architecture

- Relational database adalah default system of record yang diusulkan.
- Transaksi tidak boleh melintasi boundary secara implisit.
- Outbox pattern disiapkan untuk reliable domain event publication.
- Cache bukan source of truth dan harus tenant-aware.
- File/object storage menggunakan generated identifier, content validation, malware scanning, dan signed access.
- Analytics workload dipisahkan saat beban membenarkan; OLTP tidak boleh menjadi reporting warehouse tanpa kontrol.

## API architecture

- REST API menggunakan versioned contract.
- Internal dan public API dipisahkan secara policy dan lifecycle.
- Error menggunakan stable code, correlation ID, dan safe message.
- Operasi finansial menggunakan idempotency key.
- Pagination wajib cursor-based untuk collection besar.
- Webhook ditandatangani, replay-protected, retryable, dan dapat diaudit.

## Event-driven readiness

Domain event menggunakan envelope minimum: event ID, type, version, occurred at, tenant ID, actor/correlation/causation ID, dan payload. Event bersifat immutable. Consumer harus idempotent dan mendukung dead-letter/replay policy.

Event bus eksternal belum diwajibkan pada shared hosting. Implementasi awal dapat menggunakan transactional outbox dan worker terjadwal, selama application contract tetap sama.

## Integration architecture

Semua vendor ditempatkan di adapter melalui port. Adapter wajib memiliki timeout, bounded retry, circuit breaker bila tersedia, idempotency, rate limit awareness, audit, metric, dan failure mapping.

Cloudflare integration mencakup DNS create/update/delete, wildcard, SSL support, cache purge, dan zone validation. Token hanya melalui environment/secret manager dan tidak boleh tampil pada log.

## Plugin architecture

Plugin system berstatus Deferred sampai trust model disetujui. Sebelum aktif, harus tersedia:

- signed manifest/package;
- compatibility dan capability declaration;
- tenant-scoped installation;
- permission grant dan revocation;
- resource quota;
- isolation/sandbox strategy;
- lifecycle, upgrade, rollback, dan kill switch;
- marketplace review serta audit.

Plugin tidak boleh memperoleh akses database langsung.

## AI architecture

AI Assistant wajib melalui AI Gateway internal yang menangani provider abstraction, policy, redaction, tenant isolation, prompt/version registry, retrieval authorization, budget, rate limit, observability, human confirmation, dan safe fallback.

AI tidak boleh menjadi source of truth untuk transaksi, otorisasi, accounting posting, inventory mutation, atau keputusan irreversible. Output berisiko tinggi memerlukan deterministic validation dan human approval.

## Deployment architecture

Business logic dan module contract harus identik pada seluruh stage:

1. Shared Hosting / cPanel
2. VPS
3. Dedicated Server
4. Docker
5. Cloud
6. Kubernetes

Perbedaan stage ditangani oleh configuration dan infrastructure adapter. Session, cache, file, job, dan scheduler harus dapat dieksternalisasi tanpa mengubah use case.

## Reliability

- Request memiliki timeout dan correlation ID.
- Retry hanya untuk kegagalan transient dan operasi idempotent.
- Long-running task dipindahkan ke background job.
- Health check dibagi menjadi liveness, readiness, dan dependency diagnostics sesuai kemampuan environment.
- Backup tidak dianggap valid sebelum restore test lulus.
- Recovery objective ditetapkan per capability sebelum production launch.

## Observability

Log terstruktur tidak boleh memuat secret atau payload sensitif. Metrics minimum mencakup request rate/error/duration, job status, database saturation, cache, external dependency, tenant isolation denial, authentication, dan business critical events. Distributed tracing diperkenalkan saat arsitektur mendukungnya.

## Security architecture

Gunakan deny-by-default authorization, least privilege, MFA untuk privileged roles, secure session, CSRF protection, input validation, output encoding, encryption in transit/at rest, secret rotation, audit log immutable, dependency scanning, dan threat modeling untuk flow kritis.

## Architecture fitness functions

- Domain layer bebas import infrastructure/framework.
- Tidak ada query data tenant tanpa enforced tenant scope.
- Tidak ada akses tabel lintas module.
- Public contract memiliki version dan test.
- Semua migration terurut serta tervalidasi.
- Dependency cycle memblokir build.
- Secret scan dan high-severity security gate memblokir release.

## Decision process

Keputusan signifikan dicatat di `docs/adr/ADR-NNN-title.md` dengan status Proposed, Accepted, Superseded, atau Rejected. ADR wajib untuk technology stack, tenancy model, auth, database, payment, event transport, plugin isolation, AI provider, dan perubahan deployment architecture.

## Open decisions

- MVP bounded contexts dan feature scope
- backend/frontend/mobile stack
- database engine dan tenancy physical model
- authentication provider/protocol
- payment and fiscal requirements
- offline POS conflict model
- audit retention dan regulatory boundary
- recovery objectives
- plugin trust model
- AI data and provider policy

## Technical Preview candidate architecture

The following profile is a **Proposed** Technical Preview candidate recorded through Issue #23. It does not replace Accepted architecture decisions and does not grant implementation authority.

- Delivery shape: Laravel/PHP modular monolith with domain/application boundaries independent of framework and infrastructure.
- Web client: Vue 3, Inertia, and Vite in one preview deployment unit.
- Data: MySQL-compatible shared schema with mandatory validated tenant identity and composite integrity strategy.
- Identity: first-party revocable session, CSRF protection, and privileged-role TOTP baseline.
- Payment: synthetic cash-only; no provider, callback, settlement, refund, or real-money processing.
- Connectivity: online-only transactional mutation.
- Deployment: P1 cPanel only if every mandatory capability passes; P2 hardened VPS remains an undecided fallback.
- Recovery: provisional RPO 24 hours and RTO 4 hours for synthetic sandbox data.
- SLO: zero cross-tenant exposure, 99% scheduled demo-window availability, and proposed p95 server response at or below 750 ms for the agreed preview load.

Architectural fitness for this preview requires two-tenant negative isolation tests, server-side deny-by-default authorization, integer minor-unit money, idempotent retry boundaries, tenant-aware cache/job/file/audit behavior, deterministic migration/seeder rehearsal, secret isolation, versioned deployment, and backup/restore/rollback evidence.

All ADR-001 through ADR-007 remain **Proposed**. Hosting engine/version, worker, HTTPS, storage, backup, restore, rollback, and quota remain unverified. JRN-003 and JRN-013 are not resolved by this candidate profile.
