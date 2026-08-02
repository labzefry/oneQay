# OneQay Domain Event Storming

> **Status:** Proposed — workshop hypothesis, bukan domain model final  
> **Phase:** 0 — Governance & Discovery  
> **Owner:** Product Owner OneQay  
> **Tracking:** GitHub Issue #8; approved corrections tracked in Issue #10  
> **Dependencies:** Upstream discovery documents berstatus Proposed; temuan JRN-003 dan JRN-013 masih terbuka

## Purpose

Dokumen ini menyediakan event-storming hypothesis untuk menemukan bahasa domain, alur sebab-akibat, actor, command, policy, invariant, ownership candidate, system boundary, dan hotspot OneQay. Peta ini digunakan untuk workshop dan validasi sebelum MVP slicing, context map, data classification, threat modeling, API design, atau ADR teknologi.

Event, command, aggregate candidate, dan bounded-context candidate di dokumen ini belum menjadi kontrak implementasi. Tidak ada message schema, database schema, API, framework, atau source code yang disetujui melalui dokumen ini.

## Evidence and notation

| Element | Notation | Meaning |
|---|---|---|
| Domain event | Past tense, contoh `SaleCompleted` | Fakta domain yang telah terjadi dan tidak diubah di tempat |
| Command | Intent/imperative, contoh `CompleteSale` | Permintaan untuk mencoba mengubah state; dapat ditolak |
| Actor | Human atau system actor | Pihak yang memulai command atau menerima outcome |
| Policy | `When event, then command` | Reaksi bisnis yang masih memerlukan owner, condition, dan failure handling |
| Invariant | Kalimat yang harus selalu benar | Constraint yang melindungi tenant, uang, stock, akses, atau audit |
| Aggregate candidate | Boundary consistency hypothesis | Candidate owner state; bukan class/table/schema final |
| Read-model candidate | Informasi untuk keputusan actor | Tidak menentukan storage atau query implementation |
| External system | Boundary di luar kontrol domain | Membutuhkan contract, timeout, retry, reconciliation, dan ownership |
| Hotspot | Pertanyaan/konflik berisiko | Tidak boleh diselesaikan diam-diam |
| Evidence label | Approved, Observed, Assumption, Proposed, Under Review | Menyatakan kekuatan bukti dan approval |

Seluruh event, command, policy, aggregate, dan context di bawah berstatus **Proposed** kecuali approved constraint yang dirujuk secara eksplisit.

## Event envelope requirements

Setiap event kritis yang kelak menjadi contract harus mempertimbangkan field konseptual berikut tanpa menetapkan format teknis:

- immutable event identity;
- event type dan contract version;
- occurred-at time dan timezone semantics yang benar;
- Tenant ID atau explicit platform scope;
- organization/outlet/warehouse/device/register/shift scope bila relevan;
- actor atau system principal dan authorization context;
- correlation dan causation identity;
- entity identity/version atau concurrency evidence;
- idempotency/replay reference bila operation dapat diulang;
- source command/result dan audit classification;
- data-minimized payload dengan classification dan retention rule.

Event tidak boleh memuat secret, credential, data tenant lain, atau data pribadi yang tidak diperlukan. Metadata tracing tidak boleh digunakan sebagai pengganti authorization.

## Workshop lanes

| Lane | Domain focus | Primary upstream journeys |
|---|---|---|
| L-01 | Tenant, subscription, ownership, lifecycle | JRN-001, JRN-013 |
| L-02 | Identity, membership, access, recovery | JRN-002, JRN-003 |
| L-03 | Organization, outlet, warehouse, device, register | JRN-002, JRN-005 |
| L-04 | Catalog, price, tax, availability | JRN-004 |
| L-05 | Shift, sale, payment, receipt | JRN-005, JRN-006 |
| L-06 | Void, return, refund, correction | JRN-007 |
| L-07 | Inventory movement and stock count | JRN-008 |
| L-08 | Purchasing, supplier, receiving, invoice | JRN-009 |
| L-09 | Reconciliation, finance evidence, reporting | JRN-010, JRN-011 |
| L-10 | Support, incident, recovery, platform operations | JRN-012, JRN-013 |

Lane membantu workshop; lane tidak otomatis menjadi bounded context atau module.

## L-01 — Tenant and lifecycle event hypotheses

| Command hypothesis | Event hypothesis | Actor/system | Invariant or hotspot |
|---|---|---|---|
| `RequestTenant` | `TenantRequested` | Prospective Tenant Owner/Platform Admin | Request tidak boleh menghasilkan duplicate tenant yang tidak terdeteksi |
| `ProvisionTenant` | `TenantProvisioned` | Platform operation | Tenant ID immutable; partial provisioning memiliki state/recovery eksplisit |
| `StartTenantTrial` | `TenantTrialStarted` | Authorized platform/commercial policy | Trial scope, plan reference, entitlement, effective time, dan expiry membutuhkan evidence |
| `AssignSubscriptionPlan` | `SubscriptionPlanAssigned` | Authorized platform/commercial role | Assignment tidak otomatis membuktikan payment atau mengaktifkan tenant |
| `ActivateSubscription` | `SubscriptionActivated` | Authorized commercial policy | Activation memiliki tenant, plan, effective time, dan evidence yang dapat diaudit |
| `ChangeSubscriptionPlan` | `SubscriptionPlanChanged` | Verified Tenant Owner/delegate and authorized policy | Upgrade/downgrade timing, proration, quota, dan rollback belum diputuskan |
| `ChangeTenantEntitlement` | `TenantEntitlementChanged` | Authorized platform policy | Before/after capability, source subscription decision, tenant, dan effective time dapat ditelusuri |
| `SuspendSubscription` | `SubscriptionSuspended` | Authorized human/policy | Suspension reason dan hubungannya dengan tenant access/data retention belum diputuskan |
| `EndSubscription` | `SubscriptionEnded` | Authorized human/policy | Ending subscription tidak otomatis menghapus tenant atau data |
| `InviteTenantOwner` | `TenantOwnerInvited` | Platform Admin/system policy | Owner invitation terikat pada tenant dan purpose |
| `AcceptTenantOwnership` | `TenantOwnershipAccepted` | Verified human owner | Ownership tidak berasal dari hostname atau unverified email saja |
| `ActivateTenant` | `TenantActivated` | Authorized platform policy | Entitlement dan readiness harus dapat dibuktikan |
| `ChangeTenantConfiguration` | `TenantConfigurationChanged` | Tenant Owner/Admin | Before/after value, actor, tenant, dan effective time diaudit |
| `SuspendTenant` | `TenantSuspended` | Authorized human/policy | Suspension tidak boleh menyamarkan deletion atau kehilangan data |
| `ResumeTenant` | `TenantResumed` | Authorized human/policy | Resume hanya pada tenant yang sama dan state yang dapat dipulihkan |
| `RequestTenantDataExport` | `TenantDataExportRequested` | Verified Tenant Owner/delegate | Scope, requester, purpose, retention, dan delivery authorization belum diputuskan |
| `CompleteTenantDataExport` | `TenantDataExportCompleted` | Platform operation | Export tidak bocor lintas tenant dan delivery dapat diaudit |
| `RequestTenantRestore` | `TenantRestoreRequested` | Authorized human | Restore point, target tenant, overwrite/merge semantics adalah hotspot |
| `CompleteTenantRestore` | `TenantRestored` | Platform operation | Cross-tenant restore dilarang; consistency dan audit harus diverifikasi |
| `RequestTenantTermination` | `TenantTerminationRequested` | Verified Tenant Owner/authorized platform role | Cooling-off, legal hold, billing, retention, dan reversibility belum diputuskan |
| `TerminateTenant` | `TenantTerminated` | Named human approval + platform operation | Destructive behavior tidak boleh silent; evidence dan recovery limit wajib jelas |

Subscription events adalah dependency hypothesis minimum antara Phase 1 tenant activation dan Phase 4 commercial platform. Mereka tidak menetapkan plan catalog, quota, price, invoice, collection, grace period, billing provider, atau coupling final antara subscription, entitlement, dan tenant access.

JRN-013 belum memiliki journey detail yang disetujui. Event lifecycle ini hanya hotspot map dan tidak menyelesaikan retention, deletion, export, restore, atau legal obligations.

## L-02 — Identity and access event hypotheses

| Command hypothesis | Event hypothesis | Actor/system | Invariant or hotspot |
|---|---|---|---|
| `InviteUser` | `UserInvited` | Tenant Owner/Admin | Invitation terikat tenant, role proposal, expiry, dan intended identity |
| `AcceptInvitation` | `InvitationAccepted` | Invited human | Replay/identity substitution harus ditolak |
| `AddTenantMembership` | `TenantMembershipAdded` | Authorized tenant role | User identity tidak otomatis memberi membership pada tenant lain |
| `AssignRole` | `RoleAssigned` | Authorized approver | Assignment mempertahankan tenant/object scope dan approval evidence |
| `ChangeRoleScope` | `RoleScopeChanged` | Authorized approver | Scope escalation dapat memerlukan step-up/independent review |
| `RevokeRole` | `RoleRevoked` | Authorized approver/policy | Revocation effect terhadap active session adalah hotspot |
| `SuspendUserAccess` | `UserAccessSuspended` | Tenant Owner/Admin/security policy | Suspension dan account deletion berbeda |
| `RequestAccessRecovery` | `AccessRecoveryRequested` | User/support | Recovery channel, anti-enumeration, rate limit, dan identity proof belum diputuskan |
| `VerifyRecoveryIdentity` | `RecoveryIdentityVerified` | Human/system policy | Verification tidak boleh bergantung pada satu weak factor |
| `CompleteAccessRecovery` | `AccessRecovered` | Verified user/system policy | Credential/session revocation dan notification harus dipertimbangkan |
| `RevokeSessions` | `SessionsRevoked` | User/Admin/security policy | Revocation harus memiliki coverage dan observable result |
| `TransferTenantOwnership` | `TenantOwnershipTransferred` | Current/new verified owners | Dual verification, dispute, orphan ownership, dan recovery adalah hotspot |

JRN-003 belum memiliki journey detail yang disetujui. Event recovery tidak memilih authentication protocol, MFA method, identity provider, session technology, atau recovery factor.

## L-03 — Organization, outlet, device, and register events

| Command hypothesis | Event hypothesis | Actor/system | Invariant or hotspot |
|---|---|---|---|
| `CreateOrganizationUnit` | `OrganizationUnitCreated` | Tenant Owner/Admin | Entity selalu berada dalam satu Tenant ID |
| `OpenOutlet` | `OutletOpened` | Tenant Owner/Admin | Timezone, currency, locale, fiscal identity perlu evidence |
| `ChangeOutletConfiguration` | `OutletConfigurationChanged` | Authorized tenant role | Effective time dan audit diperlukan |
| `CreateStockLocation` | `StockLocationCreated` | Inventory/Admin role | Location ownership dan transfer rules belum final |
| `RegisterDevice` | `DeviceRegistered` | Tenant Admin/Manager | Device tidak menjadi authorization utama |
| `RevokeDevice` | `DeviceRevoked` | Authorized tenant role/security policy | Offline/session effect dan lost-device recovery adalah hotspot |
| `ActivateRegister` | `RegisterActivated` | Outlet Manager | Satu register memiliki outlet/device context yang jelas |
| `DeactivateRegister` | `RegisterDeactivated` | Outlet Manager | Active shift atau pending transaction harus ditangani eksplisit |

## L-04 — Catalog, pricing, tax, and availability events

| Command hypothesis | Event hypothesis | Actor/system | Invariant or hotspot |
|---|---|---|---|
| `CreateCatalogItem` | `CatalogItemCreated` | Catalog role | Identity tenant-scoped dan immutable reference diperlukan |
| `ChangeCatalogItem` | `CatalogItemChanged` | Catalog role | History/effective behavior untuk transaction lama harus jelas |
| `SchedulePrice` | `PriceScheduled` | Authorized pricing role | Currency, effective time, scope, rounding, dan overlap policy |
| `ActivatePrice` | `PriceActivated` | Time policy/authorized role | Sale memakai price yang dapat dibuktikan pada waktu transaksi |
| `AssociateTaxRule` | `TaxRuleAssociated` | Authorized finance/catalog role | Jurisdiction dan fiscal requirement masih Under Review |
| `ChangeItemAvailability` | `ItemAvailabilityChanged` | Catalog/Inventory role | Availability dan on-hand stock tidak boleh disamakan tanpa keputusan |
| `AuthorizeDiscount` | `DiscountAuthorized` | Manager/policy | Threshold, stacking, reason, dan actor attribution adalah hotspot |

## L-05 — Shift, sale, payment, and receipt events

| Command hypothesis | Event hypothesis | Actor/system | Invariant or hotspot |
|---|---|---|---|
| `OpenShift` | `ShiftOpened` | Cashier/Manager | Satu active shift per context sesuai policy; opening evidence tercatat |
| `StartSale` | `SaleStarted` | Cashier | Sale terikat tenant/outlet/register/shift/cashier |
| `AddSaleLine` | `SaleLineAdded` | Cashier | Item, quantity, price source, currency, tax context jelas |
| `RecalculateSale` | `SaleCalculated` | Sales policy | Server-authoritative total, precision, rounding, dan deterministic result |
| `ApplyDiscount` | `DiscountApplied` | Cashier/Manager policy | Authorization dan reason dapat diaudit |
| `InitiatePayment` | `PaymentInitiated` | Cashier/Customer | Payment reference, amount, currency, tenant, sale, idempotency binding |
| `RecordProviderPaymentAuthorization` | `ProviderPaymentAuthorized` | Verified payment-provider boundary | Authorization adalah fakta non-final dan tidak membuktikan capture, settlement, atau sale completion eligibility |
| `RecordCashPayment` | `CashPaymentRecorded` | Cashier/authorized cash policy | Cash evidence mempertahankan amount, currency, tenant, sale, shift, actor, dan reconciliation scope |
| `RecordVerifiedPaymentSuccess` | `PaymentSucceeded` | Verified method-specific payment policy | Outcome memenuhi completion criterion yang masih Proposed dan memiliki source evidence serta amount/currency/tenant/sale binding |
| `RecordPaymentDecline` | `PaymentDeclined` | Verified payment boundary/system | Decline berbeda dari technical failure dan uncertainty |
| `RecordPaymentFailure` | `PaymentFailed` | Payment boundary/system | Technical failure tidak boleh disamakan dengan provider decline atau customer-visible final outcome |
| `MarkPaymentUncertain` | `PaymentStatusBecameUncertain` | Payment boundary/system | Timeout bukan otomatis gagal; operator harus melihat uncertainty |
| `CompleteSale` | `SaleCompleted` | Cashier/sales policy | Sale completed sekali; money dan stock effect tidak ambigu |
| `IssueReceipt` | `ReceiptIssued` | Sales system | Receipt merepresentasikan final state dan tidak membocorkan data berlebih |
| `CloseShift` | `ShiftClosed` | Cashier/Manager policy | Close membutuhkan reconciliation state sesuai policy |

Provider authorization, cash evidence, verified business outcome, settlement, dan reconciliation adalah fakta berbeda.

Critical hotspot: completion criterion per payment method serta hubungan atomicity antara Sale, Payment, dan Inventory belum diputuskan. Event-storming workshop harus membedakan strong consistency need, external-provider eventual result, retry, compensation, dan reconciliation.

## L-06 — Void, return, and refund events

| Command hypothesis | Event hypothesis | Actor/system | Invariant or hotspot |
|---|---|---|---|
| `CancelSale` | `SaleCancelled` | Cashier/authorized sales policy | Hanya sebelum completion; reason, actor, idempotency, active/uncertain payment, dan stock reservation effect harus eksplisit |
| `RequestSaleVoid` | `SaleVoidRequested` | Cashier/Manager | Void hanya pada lifecycle state yang diperbolehkan |
| `ApproveSaleVoid` | `SaleVoidApproved` | Independent/authorized approver | Threshold dan separation of duties belum final |
| `VoidSale` | `SaleVoided` | Sales policy | Original sale tidak dihapus; effect dan reason dapat ditelusuri |
| `RequestReturn` | `ReturnRequested` | Customer/Cashier | Original sale, item, quantity, condition, window, tenant scope diverifikasi |
| `AcceptReturn` | `ReturnAccepted` | Authorized role | Stock disposition terpisah dari financial refund bila relevan |
| `RejectReturn` | `ReturnRejected` | Authorized role | Reason dan appeal/escalation terlihat |
| `RequestRefund` | `RefundRequested` | Cashier/Manager/Customer workflow | Amount tidak melebihi eligible remainder dan tenant/payment binding |
| `ApproveRefund` | `RefundApproved` | Independent/authorized approver | Approval tidak sama dengan provider success |
| `ExecuteRefund` | `RefundInitiated` | Payment boundary/cash handler | Idempotency dan original payment reference wajib |
| `ConfirmRefund` | `RefundCompleted` | Provider/cash evidence | Repeated refund dan partial result dicegah/direkonsiliasi |
| `RecordRefundFailure` | `RefundFailed` | Provider/system | Retry/compensation/operator action eksplisit |

Cancellation menghentikan sale sebelum completion dan tidak boleh digunakan untuk mengubah completed sale. Reservation release dan active/uncertain payment resolution tetap memerlukan policy, causation, serta observable outcome yang terpisah.

Void, cancellation, return, dan refund adalah semantics berbeda. Kesamaan UI atau workflow tidak boleh menyatukan domain event secara prematur.

## L-07 — Inventory movement events

| Command hypothesis | Event hypothesis | Actor/system | Invariant or hotspot |
|---|---|---|---|
| `PostReceiptStockMovement` | `ReceiptStockMovementPosted` | Authorized Inventory policy | Tenant, goods-receipt/line identity, receipt version, item, accepted quantity/unit, location, dan causation mencegah receipt yang sama diposting dua kali |
| `ReserveStock` | `StockReserved` | Sales/Inventory policy | Reservation need dan oversell policy belum diputuskan |
| `ReleaseStockReservation` | `StockReservationReleased` | Sales/Inventory policy | Release idempotent dan terkait reservation yang benar |
| `CommitSaleStockMovement` | `SaleStockCommitted` | Sales/Inventory policy | Satu completed sale tidak double-decrement stock |
| `InitiateStockTransfer` | `StockTransferInitiated` | Inventory Operator | Source/destination satu tenant; in-transit semantics terbuka |
| `ReceiveStockTransfer` | `StockTransferReceived` | Destination Inventory role | Partial/lost/damaged transfer memiliki discrepancy path |
| `StartStockCount` | `StockCountStarted` | Inventory/Manager | Count snapshot/concurrency behavior adalah hotspot |
| `CompleteStockCount` | `StockCountCompleted` | Inventory Operator | Observed quantity bukan otomatis adjustment |
| `ProposeStockAdjustment` | `StockAdjustmentProposed` | Inventory Operator | Reason, evidence, delta, value impact tercatat |
| `ApproveStockAdjustment` | `StockAdjustmentApproved` | Authorized independent role | Threshold dan small-tenant compensating control diperlukan |
| `ApplyStockAdjustment` | `StockAdjusted` | Inventory policy | Movement ledger tidak dapat disamarkan atau ditulis ulang |

## L-08 — Purchasing and supplier events

| Command hypothesis | Event hypothesis | Actor/system | Invariant or hotspot |
|---|---|---|---|
| `CreateSupplier` | `SupplierCreated` | Purchasing role | Identity, tenant scope, duplicate, sensitive payment detail control |
| `ChangeSupplierPaymentDetail` | `SupplierPaymentDetailChanged` | Authorized role | Out-of-band verification dan independent approval hypothesis |
| `SubmitPurchaseRequest` | `PurchaseRequested` | Requesting/Purchasing role | Need, item, quantity, location, budget context dapat ditelusuri |
| `ApprovePurchaseRequest` | `PurchaseApproved` | Manager/authorized approver | Threshold dan conflict-of-interest control |
| `IssuePurchaseOrder` | `PurchaseOrderIssued` | Purchasing role | Terms/version dan supplier acknowledgement hotspot |
| `ReceiveGoods` | `GoodsReceived` | Inventory role | Delivery/receipt evidence mencatat partial, over, damaged, dan wrong-unit discrepancy; event ini sendiri tidak menyatakan on-hand stock telah berubah |
| `RecordSupplierInvoice` | `SupplierInvoiceRecorded` | Finance/Purchasing | Duplicate invoice dan supplier identity diverifikasi |
| `MatchPurchaseEvidence` | `PurchaseMatchCompleted` | Finance policy | Order, receipt, invoice, variance policy belum final |
| `RecordPurchaseMismatch` | `PurchaseMismatchDetected` | Finance policy | Mismatch tidak boleh auto-paid atau disembunyikan |
| `ApproveSupplierPayment` | `SupplierPaymentApproved` | Finance/authorized approver | Initiator/approver separation dan banking-change control |
| `RecordSupplierPayment` | `SupplierPaymentRecorded` | Finance/payment system | Payment evidence, amount, currency, invoice allocation dapat direkonsiliasi |

## L-09 — Reconciliation and reporting events

| Command hypothesis | Event hypothesis | Actor/system | Invariant or hotspot |
|---|---|---|---|
| `RecordCashCount` | `CashCountRecorded` | Cashier/Manager | Actual value, actor, shift, time, denomination need terbuka |
| `ImportProviderSettlement` | `ProviderSettlementImported` | Payment provider/integration | File/API provenance, duplicate import, tenant mapping, integrity |
| `StartReconciliation` | `ReconciliationStarted` | Finance/Manager/system policy | Scope dan cutoff/timezone eksplisit |
| `DetectVariance` | `ReconciliationVarianceDetected` | Reconciliation policy | Expected/actual/source classification tersedia |
| `ExplainVariance` | `ReconciliationVarianceExplained` | Cashier/Manager/Finance | Explanation bukan approval atau write-off otomatis |
| `ResolveVariance` | `ReconciliationVarianceResolved` | Authorized reviewer | Adjustment source dan approval evidence dapat ditelusuri |
| `CompleteReconciliation` | `ReconciliationCompleted` | Finance/Manager policy | Completion hanya bila required evidence dan unresolved exception sesuai policy |
| `ReopenPeriodOrShift` | `PeriodOrShiftReopened` | Authorized approver | Reopen reason, scope, prior state, follow-up audit wajib |
| `PublishOperationalReport` | `OperationalReportPublished` | Reporting policy | Metric definition, source lineage, cutoff, correction semantics |

Read models seperti sales summary, stock position, payment status, shift variance, dan tenant health adalah candidate projections. Mereka tidak menjadi source of truth tanpa keputusan ownership dan consistency.

## L-10 — Support, incident, and recovery events

| Command hypothesis | Event hypothesis | Actor/system | Invariant or hotspot |
|---|---|---|---|
| `OpenSupportCase` | `SupportCaseOpened` | Tenant user/support | Requester, tenant, purpose, severity, correlation reference diverifikasi |
| `CollectDiagnosticEvidence` | `DiagnosticEvidenceCollected` | Support/system | Redaction, necessity, retention, tenant boundary diterapkan |
| `RequestSupportAccess` | `SupportAccessRequested` | Support | Access purpose, scope, duration, consent/approval sesuai policy |
| `GrantSupportAccess` | `SupportAccessGranted` | Authorized human/policy | Time-bound, least privilege, actor attribution, no silent impersonation |
| `RevokeSupportAccess` | `SupportAccessRevoked` | Authorized human/security policy | Manual revocation, consent/approval withdrawal, atau containment memiliki reason, actor, time, scope, dan observable result |
| `ExpireSupportAccess` | `SupportAccessExpired` | System policy | Expiry/revocation dapat dibuktikan |
| `CloseSupportCase` | `SupportCaseClosed` | Support/authorized case owner | Closure membutuhkan outcome, requester communication, active-access check, dan unresolved follow-up visibility |
| `DeclareIncident` | `IncidentDeclared` | Operations/Security | Severity, affected tenants, commander, communication owner |
| `ContainIncident` | `IncidentContained` | Incident responder | Containment tidak menghapus evidence atau memperluas exposure |
| `RestoreService` | `ServiceRestored` | Operations | Health, data integrity, tenant isolation, reconciliation diverifikasi |
| `CompleteRecoveryReview` | `RecoveryReviewCompleted` | Incident Commander/Product Owner | Root cause, action, owner, expiry, evidence tercatat |

## Policy hypotheses

| Trigger event | Policy condition | Follow-up command | Failure/recovery question |
|---|---|---|---|
| `TenantProvisioned` | Owner invitation required | `InviteTenantOwner` | Bagaimana incomplete provisioning dan expired invitation ditangani? |
| `SubscriptionActivated` atau `SubscriptionPlanChanged` | Entitlement change approved | `ChangeTenantEntitlement` | Bagaimana effective time, delayed billing evidence, downgrade, suspension, dan rollback ditangani? |
| `RoleScopeChanged` | Privilege increased | `RevokeSessions` atau request step-up review | Apakah session lama tetap valid dan bagaimana coverage dibuktikan? |
| `PriceActivated` | Outlet/catalog scope applicable | `RecalculateSale` untuk sale yang belum final bila policy mengizinkan | Price lock moment belum diputuskan |
| `ProviderPaymentAuthorized` | Further method-specific confirmation required | `VerifyPaymentOutcome` | Authorization tidak boleh otomatis menyelesaikan sale; capture/settlement semantics belum diputuskan |
| `CashPaymentRecorded` | Approved cash evidence tersedia untuk dinilai | `VerifyPaymentOutcome` | Cash count, correction, fraud, shift close, dan reconciliation tetap terpisah |
| `PaymentStatusBecameUncertain` | Outcome dapat diverifikasi atau direkonsiliasi | `VerifyPaymentOutcome` | Verification menghasilkan success, decline, failure, atau tetap uncertain; timeout, retry budget, escalation, dan customer communication belum diputuskan |
| `PaymentSucceeded` | Sale eligible for completion under approved method policy | `CompleteSale` | Atomicity dengan inventory dan late callback adalah hotspot |
| `SaleCompleted` | Receipt required | `IssueReceipt` | Printer/delivery failure tidak boleh membatalkan sale secara diam-diam |
| `SaleCompleted` | Stock-tracked items exist | `CommitSaleStockMovement` | Exactly-once effect dicapai secara semantik, bukan klaim transport |
| `SaleCancelled` | Active stock reservation exists | `ReleaseStockReservation` | Payment authorization/uncertainty tidak boleh diselesaikan atau di-refund secara diam-diam |
| `ReturnAccepted` | Refund eligible | `RequestRefund` | Return tanpa refund atau refund tanpa stock return mungkin valid sesuai policy |
| `GoodsReceived` | Accepted quantity requires stock effect | `PostReceiptStockMovement` | Tenant + receipt/line identity/version harus mencegah duplicate posting dan menyediakan correction path |
| `StockCountCompleted` | Variance exceeds zero/policy | `ProposeStockAdjustment` | Concurrent movement dan snapshot semantics |
| `GoodsReceived` + `SupplierInvoiceRecorded` | Match evidence sufficient | `MatchPurchaseEvidence` | Partial receipt/invoice dan tolerance policy |
| `ReconciliationVarianceDetected` | Human explanation required | `ExplainVariance` | Unresolved variance dan shift/period close behavior |
| `SupportAccessGranted` | Consent/approval withdrawn atau incident containment requires removal | `RevokeSupportAccess` | Bagaimana revocation failure, active session, dan evidence ditangani? |
| `SupportAccessGranted` | Approved duration elapsed | `ExpireSupportAccess` | Emergency extension membutuhkan approval baru dan review pasca-kejadian |
| `SupportCaseClosed` | Active support access remains | `RevokeSupportAccess` | Closure harus gagal atau tetap visible bila access belum berakhir |
| `IncidentContained` | Recovery criteria met | `RestoreService` | Data integrity dan tenant-specific recovery proof |

Policy table tidak menentukan synchronous/asynchronous implementation, queue, transaction, retry library, atau workflow engine.

## Candidate aggregate boundaries

| Candidate | State/invariant responsibility hypothesis | Must not imply |
|---|---|---|
| Tenant | Tenant identity, lifecycle state, configuration reference | Satu tabel global tanpa isolation control |
| Subscription/Entitlement | Trial/plan reference, subscription lifecycle, effective entitlement evidence | Billing provider, price, invoice, quota, atau tenant-access policy final |
| Tenant Membership | User-to-tenant membership dan delegated scope | Identity sama dengan authorization |
| Organization/Outlet | Operational hierarchy dan local configuration | Final topology untuk semua customer segment |
| Device/Register | Device/register enrollment dan active operational state | Device sebagai satu-satunya authorization factor |
| Catalog Item | Sellable identity dan lifecycle | Price, tax, inventory, atau sales ownership otomatis |
| Price | Currency/scope/effective price rules | Framework money type atau schema |
| Shift | Register/cashier operational period dan close state | Final cash-management policy |
| Sale | Sale lines, calculation, lifecycle, correction eligibility | Payment provider atau stock ledger ownership |
| Payment Attempt | Method-scoped attempt, amount, state, idempotency, dan outcome evidence | Authorization, success, settlement, dan reconciliation sebagai fakta yang sama |
| Cash Payment Evidence | Cash amount, shift, actor, correction, dan reconciliation reference | Cash evidence sebagai provider authorization atau settlement |
| Return/Refund | Eligibility, approval, provider/cash outcome | Return dan refund sebagai event yang sama |
| Stock Movement | Immutable movement source, location, quantity/unit | Cached stock position sebagai source of truth |
| Stock Count | Count session, evidence, variance proposal | Count langsung mengubah stock tanpa approval |
| Purchase Order | Approved supplier commitment dan version | Supplier invoice/payment ownership otomatis |
| Goods Receipt | Delivery evidence dan discrepancy | Purchase order selalu fully received |
| Reconciliation | Scope, expected/actual evidence, variance, completion | Reporting projection sebagai financial ledger |
| Support Case | Requester, purpose, diagnostic/access lifecycle | Unbounded support access |

Aggregate candidate adalah consistency discussion aid. Aggregate final membutuhkan load/concurrency evidence, invariant analysis, transaction boundary, failure semantics, dan ADR bila consequential.

## Candidate bounded contexts

| Candidate context | Likely ownership | Upstream/downstream question | Status |
|---|---|---|---|
| Tenant & Subscription | Tenant lifecycle, entitlement, configuration | IAM dan Billing boundary | Proposed |
| Identity & Access | Identity, membership, session/recovery policy | Tenant ownership dan platform admin separation | Proposed |
| Organization & Operations | Organization, outlet, device, register | Inventory location dan sales context | Proposed |
| Catalog & Pricing | Item, price, tax association, availability intent | Inventory availability dan sales snapshot | Proposed |
| Sales & POS | Shift, sale, calculation, correction lifecycle | Payment, Inventory, Customer, Reporting | Proposed |
| Payment & Reconciliation | Payment attempt/status, settlement, refund outcome, reconciliation | Provider, Sales, Finance boundary | Proposed/Under Review |
| Inventory | Movement, transfer, count, adjustment | Sales commitment dan Purchasing receipt | Proposed |
| Purchasing & Supplier | Request, order, supplier, receiving coordination | Inventory receipt dan Finance invoice/payment | Proposed |
| Finance & Reporting | Financial evidence, reconciliation, management reporting | Accounting scope dan fiscal boundary | Proposed/Under Review |
| Platform Operations & Audit | Support, incident, recovery, audit access | Cross-tenant control dan tenant consent | Proposed |

Context candidate tidak mengizinkan akses tabel lintas context. Komunikasi hanya melalui application contract/domain event yang nantinya disetujui; ownership dan consistency masih harus divalidasi.

## Cross-context consistency questions

| ID | Question | Risk if guessed | Required evidence/decision |
|---|---|---|---|
| ES-001 | Kapan sale dianggap final terhadap payment dan stock? | Duplicate money, oversell, unreconciled state | Payment/offline ADR, failure scenarios, journey evidence |
| ES-002 | Apakah stock reservation diperlukan? | Oversell atau unnecessary complexity | Concurrency/load/business policy evidence |
| ES-003 | Bagaimana late payment callback memengaruhi cancelled/expired sale? | Double sale/refund dan customer dispute | Provider semantics dan reconciliation policy |
| ES-004 | Apa source of truth stock position? | Drift dan unsafe correction | Movement invariant dan projection/rebuild test |
| ES-005 | Bagaimana effective price dipilih dan disimpan pada sale? | Historical inconsistency | Pricing/timezone/discount policy |
| ES-006 | Bagaimana role revocation memengaruhi session/device/offline state? | Continued unauthorized access | IAM/session/offline ADR dan threat model |
| ES-007 | Bagaimana tenant export/restore/termination menjaga tenant binding? | Cross-tenant leak atau data loss | JRN-013 correction, retention/legal decision, recovery plan |
| ES-008 | Kapan finance/accounting ledger dibutuhkan dibanding operational evidence? | Scope explosion atau inaccurate financial claims | MVP/accounting discovery dan compliance boundary |
| ES-009 | Event mana memerlukan strong consistency dan mana dapat eventual? | Lost update, latency, distributed complexity | Invariant and failure-semantics analysis |
| ES-010 | Siapa owner event schema dan compatibility? | Breaking consumers dan duplicated truth | Context ownership dan API/event governance |
| ES-011 | Bagaimana subscription/plan decision menghasilkan entitlement dan memengaruhi tenant activation/access? | Unauthorized capability, premature suspension, atau hidden commercial coupling | Phase 1 activation need, Phase 4 commercial policy, lifecycle workshop |

## Critical invariants

1. Tenant-scoped command dan event tidak dapat memengaruhi tenant lain.
2. Hostname, UI visibility, client-supplied scope, correlation ID, atau device identity bukan authorization tunggal.
3. Completed sale, payment, refund, dan stock movement memiliki identity serta final/uncertain state yang tidak ambigu.
4. Duplicate/retry tidak menggandakan financial atau stock effect.
5. Money mempertahankan currency, precision, rounding, amount source, dan reconciliation evidence.
6. Original transaction tidak dihapus untuk menyamarkan void, return, refund, adjustment, atau correction.
7. Stock movement mempertahankan item, unit, quantity, source/destination, source event, tenant, actor/system, dan time.
8. Privilege, support access, lifecycle, refund, stock adjustment, reconciliation, close/reopen, dan incident action dapat diaudit.
9. Partial failure memiliki explicit state, retry/compensation/reconciliation path, dan operator visibility.
10. Projection/read model dapat dibangun ulang atau direkonsiliasi dari source yang disetujui sesuai recovery objective.
11. Event evolution mempertahankan backward compatibility atau versioned migration/deprecation path.
12. Destructive tenant/data lifecycle action membutuhkan human authority, data policy, evidence, dan recovery limitation yang jelas.
13. Provider authorization tidak menjadi payment success tanpa method-specific verification dan amount/currency/tenant/sale binding.
14. Satu goods-receipt line/version tidak dapat menghasilkan lebih dari satu stock effect tanpa explicit correction event.
15. Support access berakhir melalui revocation atau expiry yang observable ketika purpose, approval, atau duration berakhir.
16. Cancellation sebelum sale completion tidak digunakan sebagai void, return, atau refund atas completed sale.

## Hotspot register

| ID | Hotspot | Severity | Owner role | Blocking effect |
|---|---|---|---|---|
| HS-001 | Product Owner/delegate identity belum tercatat | High | Product Owner | Approval substantif tetap terbuka |
| HS-002 | JRN-003 access-recovery detail belum diterapkan | High | Product/Security | IAM/session ADR tidak boleh dianggap siap |
| HS-003 | JRN-013 lifecycle detail belum diterapkan | Critical | Product/Data/Security/Operations | Export/restore/termination design diblokir |
| HS-004 | Payment provider/state/compliance boundary belum diketahui | Critical | Product/Security/Finance | Payment implementation dan final-state semantics diblokir |
| HS-005 | Offline POS need dan conflict semantics belum diketahui | Critical | Product/Architecture/Security | Offline implementation diblokir |
| HS-006 | Inventory reservation/negative-stock/concurrency policy belum diketahui | High | Product/Domain | Stock consistency design terbuka |
| HS-007 | Tax/fiscal/receipt jurisdiction belum ditetapkan | High | Product/Legal/Finance | Compliance-sensitive scope diblokir |
| HS-008 | Accounting depth dan valuation method belum ditetapkan | High | Product/Finance/Domain | Finance/Inventory boundary terbuka |
| HS-009 | Support access, consent, impersonation, break-glass policy belum disetujui | Critical | Security/Operations/Product | Production support-access design diblokir |
| HS-010 | RPO/RTO dan restore semantics belum disetujui | Critical | Operations/Data/Product | Recovery and updater readiness diblokir |
| HS-011 | Phase 1 tenant activation dan Phase 4 subscription/entitlement boundary belum disetujui | High | Product/Finance/Architecture | Activation, suspension, dan commercial lifecycle design terbuka |

## Workshop protocol

1. Pilih satu lane dan satu end-to-end scenario; hindari membahas seluruh ERP sekaligus.
2. Tempel event past tense tanpa mengurutkan solusi teknis.
3. Tambahkan command, actor, policy, external system, read need, dan hotspot.
4. Untuk setiap event kritis, tanyakan tenant scope, actor, money/stock effect, duplicate, concurrency, timeout, partial failure, correction, dan recovery.
5. Tandai evidence: observed, assumed, disputed, proposed, atau approved.
6. Pecah istilah ambigu seperti payment success, void, refund, stock available, close, restore, dan delete.
7. Identifikasi invariant dan candidate consistency boundary.
8. Kelompokkan event sebagai context candidate hanya setelah language/ownership tension terlihat.
9. Catat dissent dan open question; jangan memaksa consensus palsu.
10. Product Owner atau delegate mereview hasil pada head GitHub yang jelas.

Workshop tidak boleh menggunakan data produksi. Gunakan scenario sintetis dan artifact yang telah disanitasi.

## Validation backlog

| ID | Activity | Output | Dependency |
|---|---|---|---|
| EV-001 | Identity/access/recovery storming | Corrected JRN-003 events, invariants, abuse/recovery cases | Approval koreksi PR #7 |
| EV-002 | Tenant lifecycle/data storming | Suspension/export/restore/termination semantics | Approval koreksi PR #7, data/legal input |
| EV-003 | Sale/payment/inventory failure workshop | Final/uncertain states, consistency and compensation questions | Payment/offline evidence |
| EV-004 | Return/refund workshop | Eligibility, approval, money/stock effects, provider outcomes | Customer/finance evidence |
| EV-005 | Inventory/purchasing workshop | Movement, count, discrepancy, matching, concurrency | Actor/workflow evidence |
| EV-006 | Reconciliation/finance workshop | Expected/actual, settlement, variance, period control | Finance and provider evidence |
| EV-007 | Support/incident/recovery workshop | Access, audit, severity, containment, restore evidence | Operations/security participation |
| EV-008 | Context ownership review | Ubiquitous language, owner, relationship, unresolved boundary | EV-001–007 dan EV-009 |
| EV-009 | Subscription/entitlement boundary workshop | Trial/plan/subscription facts, entitlement causation, Phase 1/Phase 4 boundary | Product/commercial evidence |

## Acceptance gate

- [ ] Product Owner atau delegate manusia tercatat di GitHub.
- [ ] Event/command/policy telah direview oleh actor/domain reviewer yang relevan.
- [ ] Observed evidence, assumption, hypothesis, invariant, dan hotspot dibedakan.
- [ ] Tenant, actor, context, causation, money, stock, payment, refund, reconciliation, access, lifecycle, dan recovery tercakup.
- [ ] Critical flow memiliki duplicate, concurrency, timeout, partial failure, correction, dan recovery analysis.
- [ ] Provider authorization, cash evidence, payment success, settlement, dan reconciliation tidak disamakan.
- [ ] Goods receipt menyebabkan paling banyak satu stock effect per receipt line/version kecuali melalui explicit correction.
- [ ] Support case closure, manual access revocation, dan timed expiry memiliki lifecycle terpisah.
- [ ] Cancellation sebelum completion dibedakan dari void, return, dan refund.
- [ ] Subscription/entitlement dependency minimum dicatat tanpa memfinalkan commercial policy atau provider.
- [ ] JRN-003 dan JRN-013 diselesaikan melalui correction/approval terpisah atau tetap dinyatakan blocker.
- [ ] Aggregate/context candidate memiliki trade-off dan tidak dianggap final.
- [ ] Cross-context interaction tidak mengizinkan shared mutable ownership atau direct table access.
- [ ] Tidak ada schema, API implementation, framework, vendor, atau runtime choice tersirat.
- [ ] Application source code tetap Blocked.

## ChatGPT — Lanjutan

Gunakan repository `labzefry/oneQay` sebagai SSOT. Review Issue #8, correction Issue #10, dan draft PR Domain Event Storming pada head terbaru. Terapkan hanya koreksi yang disetujui Product Owner. Jangan mengubah event hypothesis menjadi observed fact, memfinalkan aggregate/bounded context, memilih technology/provider, mempromosikan status Proposed, atau membuat source code tanpa evidence dan approval eksplisit. Setelah correction PR disetujui dan workshop evidence direview, siapkan Ubiquitous Language and Context Map sebagai issue dan draft PR terpisah.

## ChatGPT — Review Independen

Audit `DOMAIN_EVENT_STORMING.md` terhadap Product Vision, Stakeholder and Actor Map, Current Process and User Journeys, `PROJECT_MANIFEST.md`, `AI_CONSTITUTION.md`, `ARCHITECTURE.md`, `DATABASE.md`, `API_SPEC.md`, `SECURITY.md`, `ROADMAP.md`, `TASKS.md`, dan Phase 0 Kickoff. Cari event yang bukan past-tense fact, command/event ambiguity, tenant leakage, missing actor/causation, double financial/stock effects, invalid final state, weak idempotency/concurrency/recovery, lifecycle data loss, premature aggregate/context ownership, direct table coupling, atau pilihan teknologi tersirat. Klasifikasikan temuan Critical, High, Medium, atau Low dan berikan perbaikan minimal.
