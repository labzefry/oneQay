# ADR-005: Payment Provider and Compliance Boundary

- Status: Accepted — substantive DEC-007 representation, canonical after governed publication
- Date: 2026-08-09
- Decision owner: Product Owner oneQay (`labzefry`)
- Substantive authority: DEC-007 — Payment Provider and Compliance Boundary
- Decision baseline: `50955d101c455c6af7356197d9e06d6d76e753bb`
- Decision baseline tree: `2987eccc6bf4ba8ece23ee2343b178e518a454b3`
- Historical evidence: Issue #23, Technical Preview boundary PAY-1
- Decision record: `docs/handbook/DEC_007_DECISION_RECORD.md`

## Context

ADR-005 awalnya dibentuk sebagai **Proposed Technical Preview v0.0.1 payment boundary** untuk PAY-1. Historical candidate tersebut hanya membutuhkan synthetic cash-sale happy path tanpa real-money processing atau payment-provider selection.

Substantive Product Owner DEC-007 kemudian menetapkan bounded payment architecture yang lebih luas tanpa menghapus historical PAY-1 provenance. Current payment direction harus mendukung first bounded MVP yang sederhana, dapat diaudit, tenant-safe, dan tidak bergantung provider, sambil tetap future-compatible dengan later regulated/external electronic-payment integration.

DEC-007 tidak memberikan payment implementation authority, provider-selection authority, schema/SQL authority, atau production-payment authority.

## Historical PAY-1 provenance

Historical PAY-1 tetap dicatat sebagai fakta Technical Preview:

- synthetic cash-only tender;
- no real-money processing;
- no provider API;
- no production payment credential;
- no authorization/capture;
- no settlement;
- no refund;
- no chargeback;
- no provider reconciliation;
- no QR implementation;
- historical one-currency-per-sale boundary;
- historical integer-minor-unit money representation;
- sale-level payment-sufficiency principle;
- idempotency consideration untuk retry-prone sale completion.

PAY-1 adalah historical input dan **bukan permanent cash-only product architecture**. Substantive DEC-007 supersedes PAY-1 hanya sebagai current bounded payment-architecture direction. Historical wording tidak ditulis ulang seolah-olah PAY-1 sejak awal telah memuat current DEC-007 design.

## Decision

### Canonical payment concepts

oneQay membedakan:

- Sale;
- Payment Obligation;
- Payment Intent;
- Payment Attempt;
- Payment Evidence;
- Sale Payment Sufficiency;
- Settlement;
- Refund;
- Reversal / Void;
- Dispute / Chargeback;
- Reconciliation.

Konsep tersebut tidak boleh dikolaps menjadi satu flag `paid`.

Payment Obligation merepresentasikan outstanding monetary obligation terkait sale. Payment Intent merepresentasikan intent/instruction untuk memenuhi semua atau sebagian obligation melalui eligible tender/payment mechanism. Multiple attempts/evidence dapat direpresentasikan tanpa mewajibkan multi-provider orchestration.

Exact aggregate, entity, state machine, ID, storage, API, dan schema tetap separately gated.

### First bounded MVP tender direction

First bounded MVP direction adalah:

**CASH-FIRST + CONFIGURABLE MANUAL / EXTERNAL RECORDED TENDERS**.

Integrated electronic provider tidak required untuk first bounded MVP.

Canonical tender categories:

- `CASH`;
- `MANUAL_EXTERNAL`;
- future `PROVIDER_ELECTRONIC`.

Canonical evidence/verification modes:

- `CASH_COUNTED`;
- `OPERATOR_RECORDED`;
- future `PROVIDER_VERIFIED`.

Cash adalah fixed semantic tender. Manual/external tender menggunakan stable internal identity terpisah dari editable display label. Label tenant seperti transfer bank, external card terminal, atau manual QR hanyalah presentation/configuration metadata dan tidak memilih provider atau membuktikan provider verification.

Configurable label tidak boleh dipakai untuk menyelundupkan business domain terpisah seperti credit, receivable, pay-later, financing, stored value, atau voucher liability.

### Cash evidence

Cash evidence secara konseptual harus mampu menjaga:

- tendered amount;
- accepted/applied amount;
- change;
- currency;
- tenant/outlet/register/shift context;
- cashier/operator;
- sale;
- operation identity;
- correction/refund evidence;
- reconciliation capability.

Cash-first tidak berarti permanent cash-only direction.

### Manual / external recorded tender

Manual/external payment adalah `OPERATOR_RECORDED` evidence.

Ia dapat berkontribusi pada Sale Payment Sufficiency hanya ketika tender active/permitted, operator authorized, amount/currency valid, required reference/evidence tersedia, context binding benar, state transition valid, dan audit evidence dipertahankan.

Manual record tidak boleh diklaim `PROVIDER_VERIFIED` tanpa independent provider evidence. oneQay juga tidak boleh mengklaim telah memindahkan external funds hanya karena operator mencatat external/manual payment atau refund evidence.

### Future electronic-provider boundary

Future electronic direction adalah **provider-abstracted**.

Domain/Application payment contracts tetap provider-neutral. Provider API, credential, webhook/callback, SDK, request/response format, protocol vocabulary, merchant identifier, amount representation, signing mechanism, dan integration-specific retry behavior berada pada replaceable Infrastructure boundary.

Tidak ada provider tertentu yang dipilih. Multi-provider routing/orchestration tidak diperlukan untuk first bounded MVP.

Architecture approval dan provider procurement/commercial approval tetap terpisah.

### Evidence authority and uncertainty

oneQay server-side validated payment evidence adalah authoritative.

Browser, PWA, Android, customer statement, screenshot, uploaded receipt, displayed QR, redirect parameter, arbitrary manual label, unverified provider response, dan unverified callback tidak pernah sendiri membuktikan provider-verified payment success.

Payment uncertainty harus direpresentasikan eksplisit. Provider authorization, accepted/captured evidence, settlement, refund, reconciliation, dan Sale completion adalah fakta berbeda.

### Sale-level payment sufficiency

Sale completion menggunakan **SALE-LEVEL PAYMENT SUFFICIENCY**.

Eligible evidence harus terikat pada tenant, sale, amount, currency, tender, actor/context, dan payment operation yang benar.

First-MVP evidence dapat berupa accepted cash evidence atau eligible `OPERATOR_RECORDED` manual/external evidence. Pending, failed, cancelled, expired, uncertain, authorization-only, invalid, atau unverified provider claim tidak secara independen memenuhi electronic-provider obligation.

### Idempotency and replay

Retry-prone payment operation mengikuti prinsip:

- stable operation identity;
- bounded idempotency scope;
- deterministic replay outcome;
- immutable external/provider reference bila berlaku;
- duplicate detection;
- callback/event deduplication;
- replay protection;
- state-transition validation;
- auditability.

Prinsip ini berlaku secara konseptual pada sale completion, manual-payment recording, payment-intent/attempt handling, callbacks/events, refund, reversal, dan retry-prone financial operations lain.

### Refund, reversal, and dispute

Cancellation, void/reversal, refund, partial refund, dispute, dan chargeback adalah konsep berbeda.

Refund/reversal harus authorization-controlled, idempotent, auditable, bound ke original eligible evidence dan correct tenant/context, serta dapat merepresentasikan asynchronous provider outcome bila berlaku.

Exact return/refund journeys tetap separately gated.

### Settlement and reconciliation

Payment acceptance atau Sale Payment Sufficiency tidak membuktikan external settlement.

- Cash reconciliation dapat menggunakan physical cash/register/shift evidence.
- `MANUAL_EXTERNAL` reconciliation dapat menggunakan separately available external evidence.
- Future `PROVIDER_ELECTRONIC` reconciliation dapat menggunakan provider, settlement, payout, refund, reversal, dispute, fee, dan external authoritative evidence.

Provider fee/MDR, gross/net amount, payout reference, atau settlement evidence dapat menjadi payment/reconciliation concern bila diperlukan. Complete accounting/general-ledger implementation tidak diputuskan oleh ADR ini.

### PCI and restricted-payment-data boundary

oneQay meminimalkan payment-account-data exposure.

Sensitive Authentication Data tidak boleh sengaja disimpan setelah authorization, termasuk CVV/CVC/CID atau equivalent verification data, PIN/PIN block, dan equivalent restricted authentication data.

Raw PAN handling/storage tidak approved by default. Future raw-PAN architecture memerlukan architecture authority, security/compliance review, applicable PCI evidence, data-flow review, dan implementation authority terpisah.

Provider-hosted checkout, redirect flow, provider-controlled secure component, tokenization, opaque reference, atau equivalent approach lebih disukai bila materially mengurangi exposure.

Third-party provider usage tidak otomatis menghilangkan PCI DSS atau merchant compliance obligations. Actual scope harus direassess terhadap selected provider, payment flow, merchant architecture, data flow, processing/storage/transmission behavior, approved launch jurisdiction, dan then-current applicable requirements sebelum card processing diotorisasi.

Provider secret harus server-side only melalui approved configuration/secret boundary dan tidak boleh diletakkan pada browser/PWA public asset, Android client, source control, ordinary business-domain record, normal API response, logs, audit payloads, errors, atau unauthorized tenant exposure.

### QR, QRIS, card, wallet, and bank compatibility

QR, QRIS, card, wallet, payment link, bank-transfer integration, dan electronic payment method lain future-compatible only.

Tidak ada provider, bank, acquirer, processor, gateway, atau wallet yang dipilih. Manual label `QRIS` atau `QR Payment` tidak berarti actual QRIS/provider integration.

Launch jurisdiction tidak dipilih oleh DEC-007. Bila Indonesia kelak secara eksplisit menjadi launch jurisdiction dan QR payment diimplementasikan, then-current applicable Bank Indonesia/QRIS evidence harus direview melalui authority yang relevan.

### Tenant, outlet, and merchant context

Payment operation mempertahankan server-authoritative tenant, legal merchant/entity bila relevan, outlet, register/device, shift, operator, sale, obligation/intent/attempt/evidence, dan external/provider context bila ada.

Provider merchant/payment ID, references, callback, header, client input, atau external metadata tidak dapat menggantikan tenant authorization. DEC-005 tetap authoritative untuk tenant isolation.

### Offline boundary

DEC-008 tetap exclusive owner untuk offline POS transaction authority, disconnected semantics, synchronization, replay, conflict resolution, dan post-reconnection reconciliation semantics.

ADR-005 tidak mengotorisasi offline electronic-payment acceptance, store-and-forward card processing, offline provider authorization, offline QR confirmation, locally authoritative provider-electronic success, atau offline electronic-payment sync/conflict semantics.

Cash dan manual/external tender recognition tidak memberikan disconnected transaction authority.

### Compliance and jurisdiction

Architecture ini jurisdiction-neutral. Payment regulation, licensing, QR/QRIS rules, acquirer/card rules, merchant onboarding, fiscal/tax requirements, consumer protection, privacy/data residency, legal classification, dan funds-flow requirements tetap deferred sampai jurisdiction dan actual payment flow ditetapkan.

DEC-011 tetap owner privacy/retention/jurisdiction policy.

### Money representation

Domain/Application menggunakan exact monetary representation dengan explicit currency. Floating-point monetary correctness tidak diotorisasi.

Historical PAY-1 integer-minor-unit representation tetap provenance saja. Provider-required minor units, decimal format, atau representation lain adalah Infrastructure concerns dan conversion harus exact, validated, currency-aware, serta isolated pada provider adapter boundary.

Zero-decimal/non-two-decimal currency, rounding, tax/discount allocation, change, refund, fee, dan settlement rules membutuhkan explicit applicable evidence.

### Split / partial tender

Split tender, partial payment, cash + manual combination, cash + electronic combination, multiple manual tenders untuk satu sale, dan simultaneous multi-provider combinations tidak approved sebagai first-MVP business requirements.

Architecture boleh future-compatible dengan multiple Payment Attempts/Evidence tanpa premature orchestration.

### Provider / merchant ownership

Preferred future direction, bila evidence memungkinkan, adalah regulated/external provider dan applicable tenant/legal merchant memiliki regulated payment-rail processing dan settlement, bukan oneQay secara default menjadi holder/intermediary funds.

Ini bukan legal determination. Final regulatory classification bergantung pada actual jurisdiction, payment/funds flow, contracts, merchant-of-record model bila relevan, provider model, dan legal/compliance assessment.

### Dependency and licensing boundary

DEC-010 tetap canonical owner Product License and Third-Party Notice Policy.

ADR ini tidak mengotorisasi mandatory commercial payment SDK, paid library/framework/runtime, premium SaaS technical dependency, atau Apache ECharts/frontend visualization dependency. Payment transaction/MDR/settlement/merchant fees adalah commercial/provider concern yang dinilai terpisah sebelum provider selection.

## Alternatives considered

### Permanent cash-only architecture

Tidak dipilih. Cash-first sesuai first bounded MVP, tetapi permanent cash-only architecture akan menciptakan unnecessary future migration dead-end.

### Provider-first architecture

Tidak dipilih saat ini. Provider dan launch jurisdiction belum selected, sehingga provider-first design akan menciptakan premature vendor and compliance coupling.

### Multi-provider orchestration from first MVP

Ditunda. Belum ada evidence yang membenarkan routing/orchestration complexity, multi-provider operational state, atau duplicated compliance surface.

### Full card-data handling inside oneQay

Tidak dipilih. Default architecture meminimalkan restricted payment data dan raw PAN exposure.

## Consequences

### Positive

- first bounded MVP dapat berjalan tanpa electronic-provider dependency;
- manual/external payment recording dapat tersedia tanpa falsely claiming provider verification;
- future provider integrations tidak mengubah Domain/Application payment invariants;
- tenant/outlet/payment evidence dan reconciliation remain explicit;
- restricted-card-data exposure diminimalkan;
- DEC-008 offline authority tetap terpisah;
- provider/vendor procurement tetap dapat dilakukan berdasarkan actual evidence.

### Costs and constraints

- manual/external tender memerlukan explicit evidence labeling dan operator accountability;
- eventual provider integration membutuhkan adapter, security/compliance review, callback verification, reconciliation, secret management, dan provider-specific contract tests;
- refund, dispute, split/partial tender, merchant-of-record, dan jurisdiction-specific requirements tetap memiliki later decision work;
- first MVP sengaja tidak mendapatkan provider-verified electronic payments melalui ADR ini.

## Fitness functions for later implementation

Saat implementation authority kelak diberikan, architecture harus dapat diverifikasi melalui tests/evidence yang menunjukkan:

- tenant/outlet/payment context tidak dapat dipindahkan lintas tenant;
- manual/external evidence tidak dapat berubah menjadi provider-verified evidence hanya melalui label/client input;
- duplicate retry tidak menggandakan payment/refund effect;
- timeout/uncertainty tidak silently menjadi success/failure;
- sale completion mengikuti sale-level payment sufficiency;
- raw provider/card secrets tidak bocor ke client, log, audit, atau repository;
- provider callback/event bila ada diverifikasi dan deduplicated;
- reconciliation dapat membedakan acceptance dari settlement;
- exact-money conversion tidak menggunakan floating-point correctness;
- offline behavior tidak diaktifkan tanpa DEC-008 authority.

Fitness functions ini bukan implementation authority.

## Preserved boundaries

- DEC-001 bounded MVP scope tetap berlaku.
- DEC-002 backend architecture tetap berlaku.
- DEC-003 Web/PWA architecture tetap berlaku.
- DEC-004 Android/API boundary tetap berlaku.
- DEC-005 tenant/database boundary tetap berlaku.
- DEC-006 identity/authentication boundary tetap berlaku dan JRN-003 tetap unresolved.
- DEC-008 offline semantics tetap separately gated dan exclusive.
- DEC-009 runtime/hosting tetap separately gated.
- DEC-010 licensing/third-party notices tetap separately gated.
- DEC-011 retention/privacy/jurisdiction tetap separately gated.
- DEC-012 final RPO/RTO/support objectives tetap separately gated.

Phase 0 tetap **In Progress**. Sprint 14 tetap **Not Authorized**. Final/business/production application implementation tetap **Blocked / separately gated**. Production readiness tetap **NO-GO**.

## Implementation authority

ADR-005 tidak mengotorisasi payment source code, provider adapter, provider account/contract, manual-tender UI, controller/API endpoint, webhook/callback handler, physical schema, SQL/DDL/migration, package/dependency installation, real-money processing, QR/QRIS/card/wallet processing, refund implementation, settlement/reconciliation job, offline behavior, deployment, release, atau production.

Attribution: **Lab | zefry**
