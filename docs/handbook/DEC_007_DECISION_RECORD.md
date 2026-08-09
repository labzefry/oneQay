# DEC-007 — Payment Provider and Compliance Boundary Decision Record

- Status: Approved / Substantive Decision Complete
- Decision owner: Product Owner `labzefry`
- Product: oneQay
- Developer & Product Engineering Entity: Lab | zefry
- Repository: `labzefry/oneQay`
- Decision baseline: `50955d101c455c6af7356197d9e06d6d76e753bb`
- Decision baseline tree: `2987eccc6bf4ba8ece23ee2343b178e518a454b3`
- Published predecessor: DEC-006 — Authentication / MFA / Session Architecture
- ADR representation: `docs/adr/ADR-005-technical-preview-payment-boundary.md`
- ADR disposition: Materially Revise Then Publish

GitHub adalah Single Source of Truth. Baseline di atas adalah provenance keputusan substantif dan bukan klaim live-state permanen. Fresh Minimal Delta Verification wajib dilakukan sebelum mutation, lifecycle transition, atau publication action berikutnya.

## Decision scope

DEC-007 menetapkan bounded payment architecture dan compliance boundary oneQay. Keputusan ini tidak memilih payment provider tertentu, tidak mengotorisasi real-money provider processing, dan tidak memberikan source-code, package, schema, SQL/migration, deployment, release, atau production authority.

Arah first bounded MVP adalah:

**CASH-FIRST + CONFIGURABLE MANUAL / EXTERNAL RECORDED TENDERS**.

Integrated electronic payment provider tidak diperlukan untuk first bounded MVP. Future electronic integration tetap provider-abstracted dan future-compatible.

## D-007-01 — Canonical payment domain boundary

Approved.

oneQay membedakan secara konseptual:

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

Konsep tersebut tidak boleh dikolaps menjadi satu boolean `PAID / UNPAID`.

Payment Obligation adalah kewajiban moneter outstanding terkait sale. Payment Intent adalah intent/instruction untuk memenuhi seluruh atau sebagian obligation melalui tender/payment mechanism yang eligible. Satu sale dapat memiliki lebih dari satu attempt atau evidence tanpa mengharuskan premature multi-provider orchestration.

Exact aggregate, entity, state machine, identifier, table, schema, atau API tetap separately gated.

## D-007-02 — First bounded MVP tender scope

Approved.

First bounded MVP mendukung secara arsitektural:

1. `CASH` sebagai canonical first-class system tender; dan
2. configurable `MANUAL_EXTERNAL` tender yang dicatat oleh operator terotorisasi.

Contoh display label manual/external dapat berupa transfer bank, external card terminal, manual QR payment, atau label tenant lain. Label tersebut hanya presentation/configuration metadata dan tidak memilih bank, acquirer, provider, QRIS implementation, card processor, wallet provider, atau electronic-payment integration.

Canonical tender categories:

- `CASH`;
- `MANUAL_EXTERNAL`;
- future `PROVIDER_ELECTRONIC`.

Canonical evidence/verification modes:

- `CASH_COUNTED`;
- `OPERATOR_RECORDED`;
- future `PROVIDER_VERIFIED`.

Display label tidak boleh menjadi sole canonical domain identity dan tidak boleh mengubah security/verification semantics. Misalnya label `Transfer BCA` tidak berarti `BCA API VERIFIED`; label tersebut hanya berarti operator oneQay mencatat tender manual/external dengan label itu. Label `QRIS` atau `Manual QR Payment` juga tidak membuktikan provider/QRIS verification.

Manual/external tender configuration secara konseptual dapat memiliki stable internal identity, display label, active/inactive state, tenant ownership, optional outlet availability, reference requirement, optional operator note/evidence requirement, dan ordering/display metadata. Exact field, schema, authorization model, UI, migration, dan implementation tetap separately gated.

Label manual juga tidak boleh digunakan untuk menyelundupkan domain lain seperti credit, receivable, pay-later, customer debt, installment financing, stored value, gift-card, atau voucher liability. Semantics tersebut memerlukan keputusan/domain rules terpisah bila relevan.

## Cash tender principle

Cash adalah first-class tender dengan evidence auditable yang secara konseptual mencakup:

- tendered amount;
- accepted/applied amount;
- change;
- currency;
- tenant;
- outlet;
- register;
- shift;
- cashier/operator;
- sale;
- operation identity;
- correction/refund evidence;
- reconciliation capability.

Cash-first tidak berarti permanent cash-only product direction.

## Manual / external tender evidence principle

`OPERATOR_RECORDED` evidence dapat berkontribusi pada Sale Payment Sufficiency hanya ketika tender aktif dan permitted untuk tenant/outlet yang benar, operator terotorisasi mencatat payment, amount/currency valid, required reference/evidence tersedia, binding tenant/outlet/register/shift/operator/sale/payment-operation benar, state transition valid, dan audit evidence dipertahankan.

Manual/external tender acceptance adalah operational record oneQay dan bukan provider-confirmed evidence. Ia tidak boleh direpresentasikan sebagai `PROVIDER_VERIFIED` kecuali independent provider verification benar-benar tersedia.

## D-007-03 — Electronic provider architecture direction

Approved.

Canonical future direction:

**CASH + MANUAL/EXTERNAL RECORDED TENDERS + PROVIDER-ABSTRACTED ELECTRONIC PAYMENT ARCHITECTURE**.

Domain/Application payment contracts tetap provider-neutral. Provider-specific API, credential, callback, webhook, SDK, request/response format, protocol vocabulary, merchant identifier, amount representation, signing mechanism, dan integration-specific retry behavior berada di replaceable Infrastructure boundary.

Payment provider tidak boleh menjadi dependency Domain/Application business logic. Multi-provider routing/orchestration tidak diperlukan untuk first bounded MVP.

## D-007-04 — Provider selection and deferral

Approved.

Tidak ada provider elektronik tertentu yang dipilih oleh DEC-007. Provider selection deferred sampai tersedia evidence yang cukup mengenai:

- approved launch jurisdiction;
- target-customer dan required-payment-method needs;
- regulatory applicability;
- provider security/compliance capability;
- hosted/tokenized payment capability;
- callback/webhook security;
- settlement/reconciliation support;
- refund/reversal capability;
- merchant-account dan funds-flow model;
- merchant-of-record implication bila relevan;
- availability, resilience, dan supportability;
- transaction fee, MDR, payout/settlement fee, dan commercial/procurement terms;
- contractual constraints;
- paid SDK/runtime requirement;
- third-party licensing implication.

Architecture approval dan provider/vendor procurement/commercial approval adalah keputusan terpisah.

## D-007-05 — Payment state and evidence authority

Approved.

Server-side validated oneQay payment evidence adalah authoritative.

Browser/PWA/Android result, customer statement, screenshot, uploaded receipt, displayed QR, redirect query parameter, arbitrary administrator/manual label, unverified provider response, atau unverified callback/webhook tidak pernah sendiri membuktikan provider-verified payment success.

Manual/external tender diklasifikasikan sebagai `OPERATOR_RECORDED`. Ia dapat memenuhi approved operational payment requirement sesuai rules yang berlaku, tetapi tidak boleh disebut `PROVIDER_VERIFIED` tanpa independent provider verification.

Payment uncertainty harus direpresentasikan eksplisit. Provider authorization, accepted/captured evidence, settlement, refund, reconciliation, dan Sale completion adalah fakta berbeda.

## D-007-06 — Sale-level payment sufficiency

Approved.

Sale completion bergantung pada **SALE-LEVEL PAYMENT SUFFICIENCY**, bukan hanya status satu Payment Attempt.

Hanya eligible validated evidence yang terikat pada tenant, sale, amount, currency, tender, actor/context, dan payment operation yang benar yang boleh berkontribusi pada sufficiency.

Untuk first MVP, evidence eligible dapat berupa accepted `CASH` evidence atau eligible `OPERATOR_RECORDED` manual/external tender sesuai configured dan authorized rules.

Pending, failed, cancelled, expired, uncertain, authorization-only, invalid, atau unverified provider claim tidak secara independen memenuhi electronic-provider payment obligation.

## D-007-07 — Idempotency, retry, and duplication

Approved.

Payment correctness adalah Critical domain. Retry-prone payment operation harus mengikuti prinsip:

- stable operation identity;
- bounded idempotency scope;
- deterministic replay outcome;
- immutable external/provider references bila berlaku;
- duplicate detection;
- callback/event deduplication;
- replay protection;
- state-transition validation;
- auditability.

Prinsip ini berlaku konseptual untuk sale completion, manual payment recording, payment-intent creation, payment attempts, provider callbacks/events, refunds, reversals, dan retry-prone financial operations lain. Exact key, algorithm, schema, table, dan implementation tetap separately gated.

## D-007-08 — Refund, reversal, and dispute boundary

Approved.

Cancellation, void/reversal, refund, partial refund, dispute, dan chargeback adalah konsep berbeda. Refund/reversal harus authorization-controlled, idempotent, auditable, bound ke original eligible payment evidence dan tenant/context yang benar, serta mampu merepresentasikan asynchronous provider outcome bila berlaku.

Untuk manual/external tender, refund semantics dapat memerlukan operator-recorded external evidence ketika tidak ada provider integration. oneQay tidak boleh mengklaim memindahkan external funds jika sebenarnya hanya mencatat evidence operasional.

Exact return/refund journeys tetap separately gated.

## D-007-09 — Settlement and reconciliation boundary

Approved.

Payment acceptance dan Sale Payment Sufficiency tidak berarti external settlement.

Untuk cash, operational reconciliation dapat membandingkan oneQay records terhadap physical cash/register/shift evidence. Untuk `MANUAL_EXTERNAL`, reconciliation dapat menggunakan separately available external evidence. Future `PROVIDER_ELECTRONIC` dapat direkonsiliasi terhadap provider evidence, settlement, payout, refund, reversal, dispute, fee, dan authoritative external evidence lain.

Provider fee/MDR, gross/net amount, payout reference, dan settlement evidence dapat menjadi payment/reconciliation concern bila diperlukan. DEC-007 tidak menetapkan complete accounting/general-ledger implementation.

## D-007-10 — PCI, payment credential, and secret boundary

Approved.

oneQay meminimalkan payment-account-data exposure dan secara default menghindari raw restricted card credential handling.

Sensitive Authentication Data tidak boleh sengaja disimpan setelah authorization, termasuk CVV/CVC/CID atau equivalent card-verification value, PIN/PIN block, dan equivalent restricted authentication data.

Raw PAN handling/storage tidak approved by default. Setiap future architecture yang mengharuskan raw PAN memerlukan Product Owner architecture authority terpisah, security/compliance review, applicable PCI evidence, data-flow review, dan implementation authority.

Provider-hosted checkout, redirect flow, provider-controlled secure component, tokenization, opaque reference, atau equivalent approach lebih disukai bila materially mengurangi exposure.

Penggunaan third-party provider tidak otomatis menghilangkan PCI DSS atau merchant-compliance obligation. Actual PCI scope harus direassess terhadap provider, payment flow, merchant architecture, data flow, processing/storage/transmission behavior, launch jurisdiction yang telah approved, dan then-current applicable requirements sebelum card processing diotorisasi.

Provider API credential dan integration secret bukan ordinary payment-domain business record. Future provider secret harus server-side only melalui approved configuration/secret boundary dan tidak boleh berada dalam browser/PWA public asset, Android client, source control, ordinary business record, normal API response, log, audit payload, error, atau unauthorized tenant exposure.

Exact secret-storage technology tetap separately gated oleh implementation dan DEC-009.

## D-007-11 — QR, QRIS, wallet, card, and payment-method compatibility

Approved.

QR, QRIS, card, wallet, payment link, bank-transfer integration, dan electronic payment method lain adalah future-compatible capabilities. Tidak ada QR/QRIS provider, bank, card acquirer, wallet, gateway, atau processor yang dipilih.

Manual label `QR Payment` atau `QRIS` tidak berarti actual QRIS/provider integration. Bila Indonesia kelak secara eksplisit menjadi launch jurisdiction dan QR payment diimplementasikan, then-current applicable Bank Indonesia/QRIS evidence harus direview pada saat itu. DEC-007 tidak memilih Indonesia sebagai launch jurisdiction dan tidak mengotorisasi QRIS implementation, merchant onboarding, atau production payment processing.

## D-007-12 — Tenant, outlet, and merchant context

Approved.

Payment operation mempertahankan server-authoritative context across tenant, legal merchant/entity bila relevan, outlet, register/device, shift bila relevan, cashier/operator, sale, Payment Obligation, Payment Intent, Payment Attempt, Payment Evidence, serta provider merchant/reference context bila ada.

Provider merchant/payment ID, references, callbacks, headers, client input, atau external metadata tidak dapat secara independen membentuk atau mengganti oneQay tenant authorization. DEC-005 tenant-isolation boundary tetap berlaku.

## D-007-13 — Offline boundary with DEC-008

Approved.

DEC-008 tetap exclusive owner untuk offline POS transaction authority, disconnected semantics, synchronization, replay, conflict resolution, dan reconciliation semantics setelah reconnection.

DEC-007 tidak mengotorisasi offline electronic-payment acceptance, store-and-forward card processing, offline provider authorization, offline QR confirmation, locally authoritative provider-electronic success, atau offline electronic-payment sync/conflict semantics.

Cash dan manual/external tender diakui oleh DEC-007, tetapi apakah disconnected client boleh membuat atau memfinalisasi transaksi tersebut tetap eksklusif milik DEC-008.

## D-007-14 — Compliance and jurisdiction boundary

Approved.

DEC-007 jurisdiction-neutral. Tidak ada launch jurisdiction yang dipilih atau diinferensikan.

Payment regulation, licensing, QR/QRIS rules, card/acquirer rules, merchant onboarding, fiscal/tax requirements, privacy/data-residency implications, consumer protection, legal classification, dan funds-flow requirements tetap deferred sampai launch jurisdiction serta actual product/payment flow ditetapkan secara canonical.

DEC-011 mempertahankan privacy/retention/jurisdiction ownership. DEC-007 mengonsumsi applicable legal/jurisdiction evidence dan tidak secara diam-diam menetapkan policy di luar boundary.

## D-007-15 — ADR-005 disposition and PAY-1 provenance

Approved.

ADR-005 harus **MATERIALLY REVISED THEN PUBLISHED**.

Historical Technical Preview PAY-1 tetap preserved sebagai provenance:

- synthetic cash-only tender;
- no real-money processing;
- no provider API;
- no production payment credentials;
- no authorization/capture;
- no settlement;
- no refund;
- no chargeback;
- no provider reconciliation;
- no QR implementation;
- historical one-currency-per-sale boundary;
- historical integer-minor-unit representation;
- sale-level payment sufficiency;
- idempotency consideration.

PAY-1 tidak boleh ditulis ulang seolah-olah sejak awal memuat current DEC-007 architecture. Substantive DEC-007 supersedes PAY-1 hanya sebagai current bounded payment architecture direction.

## Money representation principle

Approved.

Domain/Application memakai exact monetary representation dengan explicit currency. Floating-point monetary correctness tidak diotorisasi.

Provider-specific integer minor units, decimal formatting, atau representation lain adalah Infrastructure concern. Conversion harus exact, validated, currency-aware, dan isolated pada provider integration boundary.

Zero-decimal/non-two-decimal currency, rounding, tax/discount allocation, change, refund, fee, dan settlement rules membutuhkan explicit business/currency evidence dan tidak boleh diasumsikan diam-diam.

## Split / partial tender

Split tender, partial payment, cash + manual combination, cash + electronic combination, multiple manual tenders untuk satu sale, dan simultaneous multi-provider combination tidak approved sebagai first-MVP business requirements.

Architecture boleh future-compatible dengan multiple Payment Attempts / Payment Evidence tanpa premature orchestration. Exact split/partial-tender behavior tetap separately gated.

## Provider / merchant ownership principle

Bila future evidence memungkinkan, preferred direction adalah regulated/external payment provider dan applicable tenant/legal merchant memiliki regulated payment-rail processing dan merchant settlement, bukan oneQay secara default menjadi holder/intermediary customer/merchant funds.

Ini bukan legal determination. Final legal/regulatory classification bergantung pada actual jurisdiction, payment flow, contractual model, merchant-of-record model bila berlaku, funds-flow model, provider model, dan legal/compliance assessment.

## Dependency and license guardrail

DEC-010 tetap canonical owner Product License and Third-Party Notice Policy.

DEC-007 tidak menyetujui mandatory commercial payment SDK, paid application library, premium runtime, paid developer framework, premium SaaS technical dependency, atau dependency komersial lain untuk application operation. Mandatory paid application/runtime dependency membutuhkan Product Owner approval terpisah dan harus sesuai DEC-010.

Ini tidak berarti payment rails harus gratis. Future transaction fees, MDR, settlement fees, merchant fees, acquiring costs, dan provider commercial terms adalah provider/commercial concerns yang dievaluasi sebelum provider selection.

First MVP cash + manual/external recorded tender tidak memerlukan electronic payment provider.

Apache ECharts adalah outside DEC-007. DEC-007 tidak memasang atau memilih visualization dependency. Preferred future visualization direction tersebut tetap harus ditangani di DEC-010 dan/atau separately authorized frontend dependency workflow.

## Audit and security

Payment-sensitive activity harus traceable melalui correlation/operation identity, actor, tenant, outlet, register, shift bila relevan, sale, tender/payment identity, manual-method identity bila relevan, external/provider reference bila relevan, state transition, callback/evidence receipt, refund/reversal, reconciliation, dan configuration-change history.

Audit/logging tidak boleh memuat raw card secret, CVV/CVC/CID, PIN/PIN block, provider secret, authentication secret, raw access token, atau equivalent restricted credential.

## Preserved boundaries

- DEC-001 — POS CORE TRANSACTION & OUTLET OPERATIONS tetap first bounded MVP slice.
- DEC-002 — PHP + Laravel / Modular Monolith First / Clean Architecture tidak berubah.
- DEC-003 — first-party Web/PWA architecture tidak berubah.
- DEC-004 — Android dan explicit API/mobile boundary tidak berubah.
- DEC-005 — MySQL Server, physical tenancy, dan tenant isolation tidak berubah.
- DEC-006 — identity, MFA, session, Android/API authentication, privileged-security, dan recovery boundary tidak berubah; JRN-003 tetap unresolved.
- DEC-008 — exclusive owner offline POS semantics.
- DEC-009 — Stage-1 runtime/hosting owner.
- DEC-010 — product licensing/third-party notice owner.
- DEC-011 — privacy/retention/jurisdiction owner.
- DEC-012 — final RPO/RTO/support-objective owner.

Phase 0 tetap **IN PROGRESS**. Sprint 14 tetap **NOT AUTHORIZED**. Final/business/production application implementation tetap **BLOCKED / SEPARATELY GATED**. Production readiness tetap **NO-GO**.

## Explicitly deferred / not authorized

DEC-007 tidak memilih atau mengotorisasi specific provider, provider account/contract, commercial commitment, merchant onboarding, production credential, exact provider API/signing/SDK, QR/QRIS/bank/card/wallet integration, provider adapter, manual-tender implementation/UI, payment physical schema, table/column, SQL/DDL/migration, controller/API endpoint, webhook/callback handler, settlement/reconciliation job, refund/cashier UI, accounting ledger, accounts receivable, credit/pay-later, installment financing, stored value, taxation configuration, production secret, real-money provider processing, Apache ECharts installation, frontend visualization implementation/dependency change, roadmap/checkpoint mutation, deployment, release, atau production.

Implementation Authority, READY Authority, MERGE Authority, Deployment Authority, Release Authority, Production Authority, provider/vendor procurement authority, dan DEC-008 offline authority tetap terpisah.

## Publication lifecycle boundary

Substantive DEC-007 sudah APPROVED pada baseline di atas. Repository representation ini menjadi canonical melalui governed publication lifecycle.

Publication candidate harus:

1. mempertahankan historical PAY-1 provenance;
2. merekonsiliasi ADR-005 secara material;
3. menjalankan required repository-native checks;
4. memperoleh independent exact-head review;
5. memperoleh separate Product Owner READY Authorization bound ke exact PR/head;
6. memperoleh later separate Product Owner MERGE Authorization bound ke exact PR/head;
7. melewati `product-owner-merge-authority` sebelum merge.

Perubahan head setelah review/authority membatalkan exact-head review/authority yang terdampak.

Attribution: **Lab | zefry**
