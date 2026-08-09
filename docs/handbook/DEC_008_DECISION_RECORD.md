# DEC-008 — Offline POS Semantics and Conflict Resolution Decision Record

- Status: Approved / Substantive Decision Complete
- Decision owner: Product Owner `labzefry`
- Product: oneQay
- Developer & Product Engineering Entity: Lab | zefry
- Repository: `labzefry/oneQay`
- Decision baseline: `21f1b2e938d9f6a42b1dd2ff717c4e049b5119d7`
- Decision baseline tree: `8cf993f0c82c84bdc46a18aa70c4cb5425b89ac6`
- Published predecessor: DEC-007 — Payment Provider and Compliance Boundary
- ADR representation: `docs/adr/ADR-006-technical-preview-offline-boundary.md`
- ADR disposition: Materially Revise Then Publish

GitHub adalah Single Source of Truth. Baseline di atas adalah provenance keputusan substantif dan bukan klaim live-state permanen. Fresh Minimal Delta Verification wajib dilakukan sebelum mutation, lifecycle transition, atau publication action berikutnya.

## Decision scope

DEC-008 menetapkan bounded architecture untuk offline POS semantics, disconnected operation authority, replay, synchronization, conflict resolution, local-data security, channel capability, dan reconciliation setelah reconnect.

DEC-008 tidak memberikan source-code, Android implementation, PWA offline implementation, physical schema, SQL/DDL/migration, package/dependency, payment-provider, deployment, release, Sprint 14, atau production authority.

Arah canonical adalah:

**STAGED / HYBRID OFFLINE ARCHITECTURE**

First bounded MVP tetap menggunakan:

**ONLINE-AUTHORITATIVE TRANSACTIONS**.

Future disconnected mutations, bila kelak separately authorized, tetap berupa **PROVISIONAL CLIENT OPERATIONS** sampai **SERVER VALIDATION AND ACCEPTANCE**.

## D-008-01 — First MVP offline posture

Approved.

Architecture menggunakan staged capability:

- `O0` — online-authoritative transactional baseline;
- `O1` — bounded degraded/read-only offline capability sebagai architecture direction only;
- `O2` — future bounded offline transaction capability sebagai future-compatible direction only.

Pada first bounded MVP, sale, payment recording, stock mutation, authoritative shift mutation, void/refund/correction, dan final authoritative transaction acceptance membutuhkan server acknowledgement.

Broad disconnected/local-first transactional POS tidak approved untuk first bounded MVP.

## D-008-02 — Offline authority model

Approved.

Canonical authority model membedakan:

- locally recorded intent;
- provisional local operation;
- server accepted operation;
- server rejected operation;
- conflicted operation requiring resolution.

Disconnected client tidak menjadi authoritative hanya karena operation tersimpan secara lokal.

Server tetap authoritative untuk transaction correctness, tenant authorization, inventory acceptance, payment sufficiency, shift/register validity, user/device authorization, dan financial correctness.

## D-008-03 — Stable operation identity

Approved.

Retry-prone dan future provisional offline operation membutuhkan stable operation identity yang ditetapkan sebelum first submission dan dipertahankan sepanjang retry, reconnect, synchronization, duplicate detection, crash recovery, correlation, dan audit.

Exact identifier format, persistence, table, column, key, dan implementation tetap separately gated.

## D-008-04 — Replay and idempotency

Approved.

Required principles:

- deterministic replay;
- bounded idempotency;
- duplicate suppression;
- already-applied detection;
- same operation identity dengan conflicting semantic payload ditolak;
- retry tidak menciptakan duplicate business effects;
- partial synchronization menghasilkan explicit per-operation outcomes;
- replay evidence auditable.

Exact implementation tetap separately gated.

## D-008-05 — Conflict classification

Approved.

Conceptual conflict classes mencakup:

- duplicate / already applied;
- stale price or catalog;
- insufficient or depleted stock;
- invalid or closed shift;
- revoked or expired user authority;
- revoked or compromised device context;
- tenant/outlet authorization mismatch;
- cancelled/voided/reversed business state;
- payment evidence conflict;
- receipt/reference/sequence collision;
- changed server-side rule or invariant;
- causal/dependency failure.

Exact error code, schema, persistence, dan UI tetap separately gated.

## D-008-06 — Conflict resolution authority

Approved.

Silent last-write-wins bukan default yang dapat diterima untuk transactional facts.

Automatic conflict resolution hanya diperbolehkan jika deterministic dan proven safe, misalnya exact duplicate yang sudah applied.

Conflict lain dapat memerlukan operator acknowledgement, supervisor decision, rejection/re-entry, atau support/escalation.

Financial, payment, authorization, tenant-isolation, dan transaction-integrity conflict harus fail safely dan tidak boleh diam-diam mengubah fakta hanya agar synchronization berhasil.

## D-008-07 — Inventory semantics

Approved.

Server tetap authoritative untuk inventory acceptance.

Cached/local stock bersifat advisory dan dapat stale. Offline state tidak menciptakan implicit inventory reservation.

Future provisional offline sale dapat menghasilkan explicit stock conflict saat disubmit ke server. Oversell risk harus direpresentasikan dan ditangani secara eksplisit.

Final inventory model dan physical schema tetap separately gated.

## D-008-08 — Price and catalog semantics

Approved.

Classified approved catalog/price information dapat mendukung bounded read-only offline/degraded display setelah security dan implementation gate terpenuhi.

Cached information membutuhkan explicit freshness/version semantics.

Jika future O2 operation menggunakan cached price, harga tersebut adalah provisional transaction evidence dan tunduk pada authoritative server acceptance rules.

Exact freshness duration dan stale-price business policy tetap separately gated.

## D-008-09 — Payment offline boundary

Approved.

DEC-007 tetap binding.

Canonical distinctions tetap:

- `CASH / CASH_COUNTED`;
- `MANUAL_EXTERNAL / OPERATOR_RECORDED`;
- future `PROVIDER_ELECTRONIC / PROVIDER_VERIFIED`.

First bounded MVP tidak mengotorisasi offline payment mutation.

Future O2 dapat remain compatible dengan provisional `CASH` dan `MANUAL_EXTERNAL` operations hanya di bawah separately authorized implementation dan server acceptance.

Offline state tidak boleh menghasilkan `PROVIDER_VERIFIED` evidence.

DEC-008 tidak mengotorisasi offline electronic-provider acceptance, store-and-forward card processing, offline provider authorization, offline QR/QRIS confirmation, locally authoritative provider-electronic success, atau real-money provider integration.

## D-008-10 — Register and shift boundary

Approved.

Future offline capability hanya dapat menggunakan previously established and bounded tenant/outlet/register/shift context.

First bounded offline direction tidak menciptakan authoritative disconnected shift opening atau closing.

Jika server-side shift menjadi closed, invalid, atau incompatible selama disconnection, affected provisional operations masuk explicit conflict processing setelah reconnect.

## D-008-11 — Authentication and device trust

Approved.

DEC-006 tetap binding.

Future transactional offline capability membutuhkan previously server-authorized bounded context yang terikat, sesuai kebutuhan, pada identity, tenant, outlet, device/register, permitted capability, dan bounded validity.

Possession of cached credentials atau old client state tidak memberikan unlimited offline authority.

Privileged operations tidak memperoleh broad disconnected authority untuk convenience.

Karena immediate server revocation tidak dapat diamati ketika disconnected, offline exposure harus bounded dan business operations tetap provisional sampai server evaluation.

Exact token/grant representation dan numerical expiry tetap separately gated. JRN-003 tetap unresolved.

## D-008-12 — Tenant and outlet context

Approved.

DEC-005 server-authoritative tenant isolation tetap binding.

Offline local state harus scoped ke validated tenant/outlet context. Missing atau ambiguous tenant context fails closed.

Offline mode tidak boleh mengizinkan silent cross-tenant mutation, cross-outlet mutation, atau tenant switching hanya berdasarkan cached/client-provided state.

## D-008-13 — PWA versus Native Android

Approved: staged channel capability.

Untuk first bounded MVP:

- PWA offline transactional mutation: not approved;
- Android offline transactional mutation: not approved.

O1 read-only/degraded capability dapat kelak diimplementasikan pada salah satu channel melalui separate security dan implementation authority.

Jika O2 bounded offline transactional capability kelak justified dan authorized, **Native Android adalah preferred initial transactional offline channel**.

PWA transactional offline mutation tetap separately gated sampai security, lifecycle, storage, dan reliability evidence cukup.

Arah ini tidak memberikan Android source implementation authority.

## D-008-14 — Local data security

Approved.

Future local persistence harus minimal, classified, tenant scoped, user/device/session scoped bila applicable, protected menurut classification, bounded in retention, excluded dari unsafe logs/analytics, dan invalidated atau isolated ketika tenant/user/session/security context berubah bila applicable.

Restricted payment/authentication secrets bukan ordinary offline business cache.

Exact local database, encryption, secure-storage, keystore, library, dan key-management implementation tetap separately gated.

## D-008-15 — Synchronization ordering and reference allocation

Approved: bounded causal/dependency ordering.

DEC-008 tidak membutuhkan global total ordering seluruh oneQay operations.

Synchronization mempertahankan ordering/dependency hanya saat dibutuhkan untuk correctness, termasuk bounded relationship antara shift/register context, sale, payment evidence, dependent correction, cancellation, dan reversal.

Client wall-clock ordering sendiri tidak cukup.

Disconnected clients tidak memperoleh unrestricted authority untuk canonical global receipt/reference sequence.

Preferred future-compatible direction:

**PROVISIONAL LOCAL REFERENCE → AUTHORITATIVE SERVER REFERENCE / SEQUENCE AFTER ACCEPTANCE**.

Preallocated sequence ranges atau fiscal/reference mechanism lain tetap separately gated bila future evidence memerlukannya.

## D-008-16 — Offline reconciliation

Approved.

Offline synchronization reconciliation menentukan apakah local operations telah converged dengan server knowledge dan merekam outcome accepted, rejected, conflicted, atau pending resolution.

Offline synchronization reconciliation berbeda dari payment/provider settlement, payment-provider reconciliation, accounting/general ledger, dan physical inventory count.

## D-008-17 — Failure recovery

Approved.

Future synchronization architecture harus mendukung resumable synchronization, safe duplicate retry, explicit partial-batch results, ambiguous timeout handling tanpa mengasumsikan success/failure, crash recovery dengan stable operation identity, bounded reconnect retry, explicit invalid-entry handling, dan visible unrecoverable conflicts.

Invalid atau conflicted operation tidak boleh silently discarded hanya untuk mengosongkan queue.

## D-008-18 — Observability and audit

Approved.

Offline-related audit evidence secara konseptual mendukung correlation untuk operation identity, origin/channel, device, operator, tenant, outlet, register, shift bila applicable, local-recorded time, server-received time, replay attempts, accepted/rejected/conflicted outcome, conflict reason, conflict-resolution actor/action, dan correlation identity.

Audit tidak boleh memuat Restricted secrets atau prohibited sensitive payment credentials.

## D-008-19 — Time and client-clock boundary

Approved.

Client wall-clock time adalah metadata/evidence, bukan authoritative global ordering truth.

Client clock tidak secara independen menentukan final transaction precedence, payment truth, authorization truth, settlement, tenant authority, atau server-state precedence.

Server evidence, stable operation identity, dan bounded causal/dependency metadata menjadi authoritative coordination inputs.

## D-008-20 — Explicit scope boundary

Approved.

DEC-008 tidak memilih atau mengotorisasi:

- physical schema, table, column, index, SQL, DDL, atau migration;
- exact operation-ID representation;
- exact queue implementation, Redis, broker, atau message queue technology;
- exact local database technology;
- exact encryption, secure-storage, atau keystore library;
- exact PWA storage mechanism atau Background Sync implementation;
- exact synchronization transport;
- exact retry/backoff values;
- exact offline-retention duration;
- exact offline authorization expiry;
- exact conflict UI atau technical permission matrix;
- payment provider, payment SDK, atau offline electronic-provider processing;
- final inventory architecture;
- accounting/general ledger;
- fiscal/jurisdiction-specific receipt implementation;
- Sprint 14;
- deployment;
- release;
- production.

## Historical OFF-1 provenance

Sebelum substantive DEC-008, ADR-006 merekam Proposed Technical Preview v0.0.1 / Issue #23 / OFF-1 dengan arah:

**ONLINE-ONLY FOR TECHNICAL PREVIEW**.

Historical OFF-1 menyatakan tidak ada transactional mutation ketika connectivity unavailable, PWA dapat menyimpan static assets dan menampilkan offline state, authenticated mutation responses tidak di-service-worker-cache, tidak ada offline sale/stock mutation/background replay, dan queue/conflict/device-trust/sequence/reconciliation masih unresolved.

Historical OFF-1 tetap preserved sebagai provenance dan tidak boleh ditulis ulang seolah-olah sejak awal telah memuat complete DEC-008 architecture.

## ADR-006 disposition

Approved substantive direction:

**MATERIALLY REVISE ADR-006 THEN PUBLISH THROUGH A SEPARATE GOVERNED LIFECYCLE**.

Setelah separately authorized governed publication, ADR-006 dapat menjadi Accepted hanya sebagai repository representation dari exact substantive DEC-008 boundary ini.

## Security and architecture guardrails

- Tenant isolation tetap server-authoritative dan fail closed.
- Authentication/session/revocation tetap mengikuti DEC-006.
- Payment evidence dan sufficiency tetap mengikuti DEC-007.
- Background Sync/service-worker availability tidak menjadi transaction-correctness authority.
- Local data diminimalkan dan diklasifikasikan.
- Restricted secrets tidak menjadi ordinary offline cache.
- Replay/duplicate protection dan auditability wajib.
- Business invariants tetap berada pada server-side Domain/Application authority.

## Program and lifecycle boundary

Phase 0 tetap **IN PROGRESS**.

Sprint 14 tetap **NOT AUTHORIZED**.

Final/business/production application implementation tetap **BLOCKED / SEPARATELY GATED**.

Production readiness tetap **NO-GO**.

DEC-009, DEC-010, DEC-011, dan DEC-012 tetap separately governed.

Publication Preparation Authority, Independent Exact-Head Review, Product Owner READY Authority, Product Owner MERGE Authority, Implementation Authority, Deployment Authority, Release Authority, dan Production Authority tetap terpisah.

Attribution: **Lab | zefry**
