# Sprint52 JRN-007 Bounded Full CASH Refund Evidence Schema & Source Envelope Gate

Author by Lab | zefry

## 1. Gate classification

`SCHEMA + SOURCE ENVELOPE SELECTED / SOURCE IMPLEMENTATION NOT YET PUBLISHED / LIFECYCLE NOT AUTHORIZED`

This bounded Sprint52 gate starts from canonical main:

`bdac6408b4ea3767004844ca5ac531f3f3ad74bb`

The canonical predecessor is the published Sprint52 full CASH refund evidence entry gate.

This gate freezes the minimum schema and source design required for one auditable full CASH refund evidence operation after canonical JRN-007 full-sale void. It does not execute or apply migration #21 and creates no Technical Preview, Production, deployment, release, updater, rollback, or destructive lifecycle authority.

## 2. Entry-gate semantic retained

The selected concern remains exactly:

**JRN-007 continuation — Bounded Full CASH Refund Evidence Foundation**

A future operation records one full CASH refund evidence fact for one canonical CASH sale only when exact JRN-007 full-sale void evidence already exists.

The amount is server-derived from immutable original payment evidence.

The refund operation performs no stock restoration because JRN-007 already restored stock exactly once.

## 3. Why dedicated schema is required

`NO_SCHEMA_CHANGE` is rejected.

The current sale and void rows cannot independently prove all future refund requirements:

- refund operation identity;
- refund semantic fingerprint;
- refund actor/device distinct from historical sale/void actor/device;
- one-refund-per-sale uniqueness;
- exact replay identity;
- refund-specific occurrence time and correlation identity;
- durable binding from refund to exact void evidence;
- refund-specific evidence mode.

Reusing or mutating the void row would destroy the separation between void/reversal and refund required by DEC-007 and would make historical void evidence mutable.

Therefore one dedicated source-only migration is required.

## 4. Selected migration

Sprint52 selects exactly:

`apps/web/database/migrations/0000_00_00_000021_create_pos_sale_cash_refund_foundation.php`

Migration #21 is:

`SOURCE DESIGN SELECTED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED`

No migration #22 or additional schema change is selected.

Migration #21 must remain forward-only and its `down()` path must throw:

`LogicException('Forward-only generated migration; rollback is not authorized.')`

## 5. Selected durable table

Migration #21 must create exactly one table:

`oneqay_pos_sale_cash_refunds`

The table is immutable full CASH refund evidence and the durable replay/uniqueness boundary.

It is not a generic refund ledger, cash-drawer ledger, settlement ledger, accounting journal, return ledger, or provider-refund table.

## 6. Exact migration #21 columns

The selected columns are:

- `tenant_id VARCHAR(64)`;
- `refund_id VARCHAR(32)`;
- `operation_id VARCHAR(128)`;
- `payload_fingerprint CHAR(64)`;
- `sale_id VARCHAR(32)`;
- `void_id VARCHAR(32)`;
- `actor_identity_id VARCHAR(96)`;
- `organization_id VARCHAR(64)`;
- `outlet_id VARCHAR(64)`;
- `device_id VARCHAR(64)`;
- `refunded_atomic UNSIGNED BIGINT`;
- `currency CHAR(3)`;
- `currency_scale UNSIGNED TINYINT`;
- `tender_category VARCHAR(32)`;
- `evidence_mode VARCHAR(32)`;
- `correlation_id VARCHAR(128)`;
- `refunded_at_unix UNSIGNED BIGINT`.

No caller-controlled reason, provider reference, cash-count, settlement, fee, or mutable status column is selected.

## 7. Exact migration #21 keys and constraints

Primary key:

`tenant_id + refund_id`

Unique replay key:

`tenant_id + operation_id`

Unique one-refund-per-sale key:

`tenant_id + sale_id`

Unique one-refund-per-void key:

`tenant_id + void_id`

Required index:

`tenant_id + outlet_id + refunded_at_unix`

Required restrictive foreign keys:

- `tenant_id + sale_id` -> `oneqay_pos_sales(tenant_id, sale_id)`;
- `tenant_id + void_id` -> `oneqay_pos_sale_voids(tenant_id, void_id)`;
- `tenant_id + actor_identity_id` -> canonical identities;
- `tenant_id + organization_id` -> canonical organizations;
- `tenant_id + outlet_id` -> canonical outlets;
- `tenant_id + device_id` -> canonical devices.

All selected foreign-key update/delete behavior remains restrictive.

## 8. Exact permission

The exact permission identifier is frozen as:

`pos.sale.refund`

`PosPermission` must expose a dedicated constant and typed helper.

No default role grant is included.

No existing POS permission implies refund authority.

## 9. Exact command shape

The Application command is:

`SaleCashRefundCommand`

Exact caller-owned constructor inputs:

1. `operationId`;
2. `saleId`.

The command must validate the canonical stable mutation-operation identifier vocabulary and canonical `sale-[a-f0-9]{24}` identifier.

Its semantic fingerprint part is exactly bound to:

`FULL_CASH_REFUND|<sale_id>`

Refund amount, tender category, void id, tenant, organization, outlet, device, actor, correlation identity, and time are not command inputs.

## 10. Exact result shape

The Application result is:

`SaleCashRefundResult`

The bounded result exposes:

- refund id;
- sale id;
- void id;
- operation id;
- tenant id;
- outlet id;
- refunded Money;
- tender category;
- evidence mode;
- correlation identity;
- refunded-at Unix time.

The result does not expose mutable stock, drawer balance, settlement, accounting, provider, or shift state.

## 11. Exact Application service

The selected Application service is:

`RecordCashRefund`

Dependencies:

- `SaleCashRefundRepository`;
- `OrganizationalContextStore`;
- `DurableScopedAuthorizationPolicy`;
- `PersistenceTransaction`;
- existing `PosSaleClock`.

The service must:

1. validate server-owned correlation identity;
2. derive `PosExecutionContext` from current verified organizational context;
3. require `PosPermission::refundSale()`;
4. obtain positive server time from `PosSaleClock`;
5. run the repository mutation inside canonical `PersistenceTransaction`.

The Application layer remains framework independent.

## 12. Exact repository abstraction

The selected interface is:

`SaleCashRefundRepository`

It exposes one bounded mutation equivalent to:

`record(PosExecutionContext, SaleCashRefundCommand, correlationId, occurredAtUnix): SaleCashRefundResult`

No broad refund CRUD, refund listing, refund update, refund delete, stock mutation, cash-drawer method, or settlement method is selected.

## 13. Dedicated infrastructure adapter

The selected adapter is:

`LaravelSaleCashRefundRepository`

It is intentionally separate from `LaravelDurablePosSaleRepository`.

This keeps the refund feature independently fail-closed and avoids making an exact replay depend on whether the current sale-completion or void HTTP mutation flags remain armed after the historical sale/void evidence already exists.

The adapter depends only on:

- canonical database connection;
- persistence-enabled state;
- runtime class;
- exact refund feature flag.

## 14. Exact persistence ordering

The Laravel adapter must preserve the following fail-closed ordering.

### A. Operational guard

Deny unless:

- persistence is enabled;
- exact CASH refund feature flag is enabled;
- runtime class is exactly Local/Test/CI.

### B. Deterministic fingerprint

Compute the semantic payload fingerprint from:

- current actor id;
- tenant id;
- organization id;
- outlet id;
- device id;
- `FULL_CASH_REFUND|sale_id`.

### C. Exact replay lookup first

Lock/read `oneqay_pos_sale_cash_refunds` by exact:

`tenant_id + operation_id`

If found:

- payload fingerprint must match exactly;
- return the original durable refund result;
- do not revalidate current mutable runtime state;
- do not insert another refund;
- do not insert another event;
- do not mutate catalog/stock;
- do not mutate sale or void evidence.

Conflicting operation-id reuse fails closed.

### D. Target sale lock

For a fresh operation, lock the exact canonical sale by:

`tenant_id + sale_id`

Fail unless:

- sale exists;
- sale organization matches current server-derived organization;
- sale outlet matches current server-derived outlet;
- sale tender category is exactly `CASH`.

### E. Exact canonical void lock

Lock the exact JRN-007 void by:

`tenant_id + sale_id`

Fail unless exactly one void exists and:

- its sale id matches;
- its organization/outlet match the canonical target sale;
- its tender category is exactly `CASH`;
- its evidence mode is exactly `FULL_SALE_VOID`;
- its reversed amount equals original sale `applied_atomic`;
- its currency and scale exactly match the sale.

The future refund binds to this durable `void_id`.

### F. One-refund guard

Check for existing refund evidence by exact:

`tenant_id + sale_id`

Any existing refund under another operation fails closed.

The target sale lock serializes competing fresh refund operations; database uniqueness remains the final defensive arbiter.

### G. Exact amount derivation

Construct refunded Money only from original sale:

- `applied_atomic`;
- currency;
- currency scale.

Never include `change_atomic`.

Never accept caller-provided amount.

### H. Atomic immutable evidence

Insert exactly one `oneqay_pos_sale_cash_refunds` row using:

- deterministic refund id;
- exact target sale id;
- exact canonical void id;
- current authorized server context;
- exact derived full refund amount;
- `CASH`;
- exact evidence mode `FULL_CASH_REFUND`;
- server correlation id;
- server occurrence time.

No original sale, sale line, void, receipt, catalog, baseline, shift, or payment row is updated.

### I. Refund event

Insert exactly one immutable sale event:

`REFUNDED`

The event references the target sale and refund operation with the current authorized actor, server correlation identity, and occurrence time.

Exact replay never adds another `REFUNDED` event.

If event insertion fails, the entire transaction fails and refund evidence is not partially committed.

## 15. Refund identifier

The deterministic refund identifier is:

`refund-` + first 24 hexadecimal characters of SHA-256 over:

`tenant_id|operation_id`

The resulting identifier fits the selected 32-character column.

The identifier is not caller-controlled.

## 16. CASH amount contract

The original canonical sale remains authoritative for the applied amount.

For an eligible target:

`refunded_atomic == sale.applied_atomic == void.reversed_atomic`

Currency and scale must also match across sale, void, and refund.

`sale.change_atomic` remains immutable original tender-change evidence and is never added to the refund amount.

Unknown, malformed, overflowing, or inconsistent monetary evidence fails closed.

## 17. Exact feature flag

Environment:

`ONEQAY_POS_SALE_CASH_REFUND_ENABLED`

Configuration:

`oneqay.pos_sale_cash_refund.enabled`

Default:

`false`

The adapter and route independently remain fail-closed when not armed.

No Technical Preview or Production activation is selected.

## 18. Exact delivery boundary

The selected future endpoint is:

`POST /pos/sales/cash-refund`

Controller:

`PosSaleCashRefundController`

The route is created only when:

- runtime class is Local/Test/CI;
- canonical session control is enabled;
- `oneqay.pos_sale_cash_refund.enabled` is true.

It does not require the current sale-completion or sale-void feature flag to remain armed because eligibility derives from existing durable canonical sale/void evidence.

Required middleware:

- `session.active`;
- bounded mutation throttling;
- `RequirePosSessionContextMiddleware`.

## 19. Exact closed HTTP payload

The controller accepts exactly:

- `operation_id`;
- `sale_id`.

Both must be strings.

Unknown or additional request keys fail closed.

The controller never accepts:

- refund amount;
- tender category;
- currency/scale;
- void id;
- tenant/outlet/device/actor/role/permission;
- stock/return quantity;
- cash count;
- settlement/provider state;
- timestamp;
- correlation id.

## 20. Exact HTTP response posture

First success and exact replay may return:

- status `cash_refund_recorded`;
- refund id;
- sale id;
- void id;
- operation id;
- tenant id;
- outlet id;
- refunded amount in bounded existing Money representation;
- tender category;
- evidence mode;
- server-owned correlation id;
- refunded-at Unix time.

Response caching remains:

`Cache-Control: no-store, private`

Authorization denial remains safe 403.

Invalid/conflicting target state remains bounded safe 422.

No internal exception, SQL, or database detail is exposed.

## 21. JRN-006 compatibility contract

JRN-006 remains unchanged:

- original sale/payment/receipt evidence stays immutable;
- `applied_atomic` remains the authoritative sale-applied amount;
- `change_atomic` remains original cash-change evidence;
- exact sale replay still returns the original receipt;
- refund does not modify sale lines or stock.

## 22. JRN-007 void compatibility contract

JRN-007 remains unchanged:

- stock restoration occurs only in first successful full void;
- refund requires existing exact full-void evidence;
- refund never performs another restoration;
- void evidence remains immutable;
- exact void replay remains safe and returns original void evidence;
- existing `VOIDED` event remains unchanged.

## 23. JRN-008 compatibility contract

Inventory baseline evidence remains immutable.

Refund never mutates:

- baseline row;
- catalog quantity;
- catalog price/currency/scale/sellability.

The quantity after a void remains exactly the quantity already established by the existing sale/void transaction history.

## 24. Shift compatibility contract

A refund evidence record does not require an active shift.

It does not create, close, reopen, reassign, or mutate a shift/register record.

It does not create opening cash, cash count, drawer balance, variance, or settlement evidence.

JRN-010 remains separately gated.

## 25. Exact future source envelope

The future Sprint52 source implementation is frozen to exactly these 14 paths:

1. `.github/workflows/sprint52-jrn007-bounded-full-cash-refund-regression.yml`
2. `apps/web/app/Application/Authorization/PosPermission.php`
3. `apps/web/app/Application/Pos/RecordCashRefund.php`
4. `apps/web/app/Application/Pos/SaleCashRefundCommand.php`
5. `apps/web/app/Application/Pos/SaleCashRefundRepository.php`
6. `apps/web/app/Application/Pos/SaleCashRefundResult.php`
7. `apps/web/app/Delivery/Http/Pos/PosSaleCashRefundController.php`
8. `apps/web/app/Infrastructure/Pos/LaravelSaleCashRefundRepository.php`
9. `apps/web/app/Providers/AppServiceProvider.php`
10. `apps/web/config/oneqay.php`
11. `apps/web/database/migrations/0000_00_00_000021_create_pos_sale_cash_refund_foundation.php`
12. `apps/web/routes/web.php`
13. `apps/web/tests/pos-sale-cash-refund-durable.php`
14. `docs/JRN_007_POS_BOUNDED_FULL_CASH_REFUND_EVIDENCE_FOUNDATION.md`

Sorted newline-terminated path SHA-256:

`ad4f6c62c820bdc59d7c1c8550ffeec0a4a0718ba4388a516418d50468d7e9d5`

Any other changed-file shape fails closed.

The entry-gate and this schema/source-envelope document are predecessor evidence and are not part of the future source envelope.

## 26. Dedicated workflow contract

The new Sprint52 workflow must at minimum enforce:

- exact 14-path source envelope and fingerprint;
- exactly one migration delta and exact migration #21 path;
- migrations #1 through #20 preserved byte-for-byte relative to source base;
- migration #21 exact selected schema shape;
- forward-only rollback lock;
- no dependency lock/environment-file change;
- dedicated permission and no default grant;
- Application framework independence;
- exact feature flag default false;
- exact Local/Test/CI route lock;
- strict closed HTTP payload;
- PHP syntax;
- locked Composer install;
- High/Critical Composer advisory rejection;
- dedicated CASH refund durable regression;
- JRN-006 sale regression preservation;
- JRN-007 void regression preservation;
- JRN-008 baseline regression preservation;
- Sprint49 active-shift sale preservation;
- M7.4 synthetic POS preservation;
- tracked-source cleanliness;
- lifecycle locks.

Historical compatibility corrections, if fresh exact-head failures require them, remain separate bounded workflow-only predecessors.

## 27. Required dedicated refund regression

The dedicated Sprint52 regression must prove all of the following:

- refund permission denied by default;
- authorized eligible CASH refund succeeds;
- exact amount equals original `applied_atomic`;
- original `change_atomic` is excluded;
- refund result binds exact sale and void;
- non-voided sale denied;
- MANUAL_EXTERNAL sale denied;
- inconsistent void amount/currency/scale/tender/evidence mode denied;
- cross-tenant target denied;
- cross-organization target denied;
- cross-outlet target denied;
- exact replay returns original refund;
- exact replay does not add another event;
- conflicting operation id reuse denied;
- second operation for already-refunded sale denied;
- deterministic double-refund/concurrency safety;
- original sale/line/payment/receipt rows unchanged;
- original void row unchanged;
- stock/catalog quantity unchanged by refund;
- exact JRN-007 replay still does not restore stock twice;
- no shift requirement/mutation;
- no cash-count/drawer/variance/settlement state;
- unknown HTTP field rejected;
- caller amount/context/time injection rejected;
- feature defaults false;
- runtime outside Local/Test/CI fails closed;
- migration #21 forward-only;
- migrations #1 through #20 preserved;
- tracked-source cleanliness.

## 28. Explicit non-scope

The frozen source envelope may not add:

- partial refund;
- partial/item return;
- exchange;
- MANUAL_EXTERNAL refund;
- provider refund/reversal;
- dispute/chargeback;
- asynchronous provider state;
- cash drawer movement;
- cash counting;
- denomination capture;
- variance;
- settlement/payout;
- accounting/general ledger;
- additional stock movement;
- stock adjustment;
- purchasing;
- supplier lifecycle;
- transfer;
- stocktake;
- JRN-010 shift close/reconciliation;
- external payment integration;
- offline mutation;
- Technical Preview activation;
- Production activation;
- deployment;
- release;
- updater activation;
- migration execution/application;
- rollback;
- destructive database operation.

## 29. Lifecycle locks

After this gate, canonical source migration horizon remains #1 through #20 until future source publication.

- Migration #20: **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**.
- Migration #21: **SELECTED IN SOURCE DESIGN / NOT CREATED BY THIS GATE / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**.
- Technical Preview: **NOT ACTIVATED**.
- Production: **NO-GO / NOT AUTHORIZED**.
- Updater activation: **NOT AUTHORIZED**.
- Deployment: **NOT AUTHORIZED**.
- Release: **NOT AUTHORIZED**.
- Migration execution/application: **NOT AUTHORIZED**.
- Rollback: **NOT AUTHORIZED**.
- Destructive database operations: **NOT AUTHORIZED**.

## 30. Exact schema/source-envelope gate path

This gate changes exactly:

`docs/SPRINT_52_JRN_007_BOUNDED_FULL_CASH_REFUND_EVIDENCE_SCHEMA_SOURCE_ENVELOPE_GATE.md`

Sorted newline-terminated path SHA-256:

`ede979f77d49e621c8270a48c2361d03be12bc763386018babaacc4ad4b065bb`

No application source, workflow, configuration, migration, dependency, updater, deployment, release, or runtime file belongs to this gate PR.

## 31. Required next bounded task

If this gate is published, the next Sprint52 task is:

**Sprint52 JRN-007 Bounded Full CASH Refund Evidence Source Implementation**

That task must start from exact then-current canonical main, create only the frozen 14-path envelope, and independently qualify its exact head.

Migration #21 may be created as source in that future task but must not be executed/applied/activated.

## 32. Gate decision

Sprint52 freezes:

- exact semantic: one full CASH refund evidence record after exact JRN-007 full void;
- exact permission: `pos.sale.refund`;
- exact closed payload: `operation_id + sale_id`;
- exact amount derivation: original `applied_atomic`;
- exact event: one `REFUNDED` event on first success;
- exact feature: `ONEQAY_POS_SALE_CASH_REFUND_ENABLED`, default false;
- exact endpoint: `POST /pos/sales/cash-refund`;
- exact dedicated refund table and migration #21 source design;
- exact 14-path future source envelope.

No lifecycle activation is created.

Attribution: **Lab | zefry**
