# Sprint53 JRN-010 Prerequisite Shift Opening Cash Evidence Schema & Source Envelope Gate

Author by Lab | zefry

## 1. Gate classification

`SCHEMA + SOURCE ENVELOPE SELECTED / SOURCE IMPLEMENTATION NOT YET PUBLISHED / JRN-010 NOT SELECTED / LIFECYCLE NOT AUTHORIZED`

Canonical base:

`7cc158dc21bd3f580aa8f18dc107cc5982ccaeac`

The canonical predecessor is the published Sprint53 bounded Shift Opening Cash Observation Evidence entry gate.

This gate freezes only the minimum schema and source design for one immutable operator-observed opening-cash fact per canonical shift. It does not execute or apply migration #22 and creates no shift-close, variance, settlement, Technical Preview, Production, deployment, release, updater, rollback, or destructive lifecycle authority.

## 2. Selected concern retained

The selected concern remains exactly:

**JRN-010 prerequisite — Bounded Shift Opening Cash Observation Evidence Foundation**

A future operation records one explicit opening cash observation for the exact current active shift derived from verified server context.

Zero is valid only when explicitly supplied as the observation. Unknown opening cash is never converted to zero.

The record remains operator-observed evidence and is not independent proof of physical custody, denomination composition, settlement, or accounting balance.

## 3. Why dedicated schema is required

`NO_SCHEMA_CHANGE` is rejected.

The current canonical `oneqay_pos_shifts` row has no opening-cash amount, currency, scale, observation operation identity, observation fingerprint, separately authorized observer identity, or replay evidence.

Mutating the original shift-opening row would make historical JRN-005 opening evidence mutable and collapse two separately authorized facts.

Therefore one additive immutable table is required.

## 4. Selected migration

Exact future migration path:

`apps/web/database/migrations/0000_00_00_000022_create_pos_shift_opening_cash_evidence_foundation.php`

Migration #22 posture:

`SOURCE DESIGN SELECTED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED`

No migration #23 or other schema change is selected.

The migration remains forward-only. Its `down()` path must throw:

`LogicException('Forward-only generated migration; rollback is not authorized.')`

## 5. Selected durable table

Migration #22 creates exactly:

`oneqay_pos_shift_opening_cash_evidence`

The table is an immutable opening-cash observation and durable replay/uniqueness boundary.

It is not a shift-close table, cash-movement ledger, denomination table, drawer ledger, variance table, settlement table, or accounting journal.

## 6. Exact migration #22 columns

The selected columns are:

- `tenant_id VARCHAR(64)`;
- `evidence_id VARCHAR(32)`;
- `operation_id VARCHAR(128)`;
- `payload_fingerprint CHAR(64)`;
- `shift_id CHAR(32)`;
- `actor_identity_id VARCHAR(96)`;
- `organization_id VARCHAR(64)`;
- `outlet_id VARCHAR(64)`;
- `device_id VARCHAR(64)`;
- `opening_cash_atomic UNSIGNED BIGINT`;
- `currency CHAR(3)`;
- `currency_scale UNSIGNED TINYINT`;
- `evidence_mode VARCHAR(40)`;
- `correlation_id VARCHAR(128)`;
- `recorded_at_unix UNSIGNED BIGINT`.

No mutable status, closing count, expected cash, variance, denomination, free-text note, settlement, or accounting column is selected.

## 7. Exact keys and constraints

Primary key:

`tenant_id + evidence_id`

Unique replay key:

`tenant_id + operation_id`

Unique one-opening-cash-observation-per-shift key:

`tenant_id + shift_id`

Required index:

`tenant_id + outlet_id + recorded_at_unix`

Required restrictive foreign keys:

- `tenant_id + shift_id` -> `oneqay_pos_shifts(tenant_id, shift_id)`;
- `tenant_id + actor_identity_id` -> canonical identities;
- `tenant_id + organization_id` -> canonical organizations;
- `tenant_id + outlet_id` -> canonical outlets;
- `tenant_id + device_id` -> canonical devices.

All update/delete behavior remains restrictive.

## 8. Exact permission

Exact permission:

`pos.shift.opening-cash.record`

`PosPermission` must expose a dedicated constant and typed helper.

No default role grant is included.

No existing shift, sale, void, refund, catalog, or inventory permission implies this authority.

## 9. Exact command shape

Application command:

`ShiftOpeningCashCommand`

Exact constructor inputs:

1. `operationId`;
2. canonical `Money openingCash`.

The delivery layer builds `Money` only from exact caller fields:

- `opening_cash_atomic`;
- `currency`;
- `currency_scale`.

The command semantic fingerprint part must bind exactly:

`OPENING_CASH|<currency>:<scale>:<atomic_units>`

Shift id, tenant, organization, outlet, device, actor, correlation identity, and time are not command inputs.

## 10. Exact result shape

Application result:

`ShiftOpeningCashResult`

It exposes:

- evidence id;
- shift id;
- operation id;
- tenant id;
- outlet id;
- device id;
- opening cash `Money`;
- evidence mode;
- correlation identity;
- recorded-at Unix time.

It exposes no mutable shift state, closing count, expected cash, variance, settlement, or accounting state.

## 11. Exact Application service

Application service:

`RecordShiftOpeningCash`

Dependencies:

- `ShiftOpeningCashRepository`;
- `OrganizationalContextStore`;
- `DurableScopedAuthorizationPolicy`;
- `PersistenceTransaction`;
- existing `ShiftOpeningClock`.

The service must:

1. validate canonical correlation identity;
2. derive `PosExecutionContext` from current verified organizational context;
3. require `PosPermission::recordShiftOpeningCash()`;
4. obtain positive server time from `ShiftOpeningClock`;
5. execute repository recording inside canonical `PersistenceTransaction`.

Application remains framework independent.

## 12. Exact repository abstraction

Interface:

`ShiftOpeningCashRepository`

One bounded method equivalent to:

`record(PosExecutionContext, ShiftOpeningCashCommand, correlationId, recordedAtUnix): ShiftOpeningCashResult`

No CRUD/list/update/delete, close, variance, settlement, or arbitrary cash movement method is selected.

## 13. Dedicated infrastructure adapter

Adapter:

`LaravelShiftOpeningCashRepository`

It is separate from `LaravelShiftOpeningRepository` so exact replay can return immutable historical evidence even if the original shift is no longer active in a future lifecycle.

Dependencies are limited to:

- canonical database connection;
- persistence-enabled state;
- runtime class;
- exact opening-cash feature flag.

## 14. Exact persistence ordering

### A. Operational guard

Deny unless:

- persistence is enabled;
- exact opening-cash feature flag is enabled;
- runtime is Local/Test/CI.

### B. Deterministic fingerprint

Fingerprint binds:

- current actor;
- tenant;
- organization;
- outlet;
- device;
- `OPENING_CASH|currency:scale:atomic_units`.

### C. Replay lookup first

Lock/read `oneqay_pos_shift_opening_cash_evidence` by exact:

`tenant_id + operation_id`

If found:

- fingerprint must match exactly;
- return original durable evidence;
- do not require the historical shift to remain active;
- do not create another record;
- do not mutate shift, sale, refund, catalog, or inventory state.

Conflicting operation reuse fails closed.

### D. Fresh active shift lock

Resolve and lock exact active shift by server-derived:

`tenant_id + outlet_id + device_id + active_slot = 1`

Fail unless the row exists and its organization matches current server-derived organization.

The caller never selects `shift_id`.

### E. One-observation guard

Lock/read any opening-cash evidence for:

`tenant_id + shift_id`

Any existing evidence under another operation fails closed.

The active shift lock serializes competing fresh attempts. Database unique constraints remain the final defensive arbiter.

### F. Exact money evidence

Use only canonical `Money` from command input.

Requirements:

- atomic units non-negative;
- currency exactly canonical three-letter code;
- scale 0..6;
- zero allowed only because caller explicitly supplied zero.

No currency conversion, rounding, inferred defaults, or catalog/sale-derived substitute is permitted.

### G. Atomic immutable insert

Insert exactly one evidence row with:

- deterministic evidence id;
- exact server-resolved shift id;
- current authorized context;
- exact opening money observation;
- evidence mode `OPERATOR_OBSERVED_OPENING_CASH`;
- server correlation identity;
- server time.

No source row is updated.

## 15. Deterministic evidence id

Exact evidence id:

`cashopen-` + first 23 hexadecimal characters of SHA-256 over:

`tenant_id|operation_id`

The result is exactly 32 characters and is not caller-controlled.

## 16. Exact feature flag

Environment:

`ONEQAY_POS_SHIFT_OPENING_CASH_EVIDENCE_ENABLED`

Configuration:

`oneqay.pos_shift_opening_cash_evidence.enabled`

Default:

`false`

Route and adapter independently fail closed when unarmed.

No Technical Preview or Production activation is selected.

## 17. Exact delivery boundary

Endpoint:

`POST /pos/shifts/opening-cash`

Controller:

`PosShiftOpeningCashController`

Route exists only when:

- runtime is Local/Test/CI;
- canonical session control is enabled;
- opening-cash evidence feature is explicitly armed.

Required middleware:

- `session.active`;
- bounded throttling;
- canonical `RequirePosSessionContextMiddleware`.

Exact request fields:

- `operation_id` string;
- `opening_cash_atomic` integer >= 0;
- `currency` string;
- `currency_scale` integer 0..6.

Unknown request keys fail closed.

The route does not require the JRN-005 shift-opening feature flag to remain armed because fresh target eligibility derives from durable canonical active shift state.

## 18. Exact success/error semantics

Successful response:

- status `recorded`;
- evidence id;
- shift id;
- operation id;
- tenant id;
- outlet id;
- register-context device id;
- opening cash atomic/currency/scale;
- evidence mode;
- recorded-at Unix time;
- original correlation id.

Authorization denial uses a bounded safe 403 envelope.

Invalid, missing, duplicate, incompatible, or conflicting state uses a bounded safe 422 envelope.

Responses are no-store/private.

## 19. Regression obligations

Dedicated regression must prove at least:

- permission denied by default/no implicit grant;
- explicit zero observation succeeds;
- positive observation succeeds;
- exact active shift is server-resolved;
- caller cannot provide shift id or authority fields;
- no active shift fails closed;
- wrong organization/outlet/device context fails closed;
- exact replay returns original evidence even after test-only deactivation of historical shift;
- conflicting operation reuse fails closed;
- second observation for same shift fails closed;
- tenant isolation;
- amount/currency/scale preserved exactly;
- invalid money input rejected;
- no shift row mutation;
- no sale/refund/catalog/inventory mutation;
- feature disabled denies;
- Production runtime denies;
- route and middleware boundary;
- migration #22 forward-only;
- migrations #1-#21 preserved.

## 20. Exact future source envelope

Future implementation is frozen to exactly 14 paths:

1. `.github/workflows/sprint53-jrn010-prerequisite-shift-opening-cash-evidence-regression.yml`
2. `apps/web/app/Application/Authorization/PosPermission.php`
3. `apps/web/app/Application/Pos/RecordShiftOpeningCash.php`
4. `apps/web/app/Application/Pos/ShiftOpeningCashCommand.php`
5. `apps/web/app/Application/Pos/ShiftOpeningCashRepository.php`
6. `apps/web/app/Application/Pos/ShiftOpeningCashResult.php`
7. `apps/web/app/Delivery/Http/Pos/PosShiftOpeningCashController.php`
8. `apps/web/app/Infrastructure/Pos/LaravelShiftOpeningCashRepository.php`
9. `apps/web/app/Providers/AppServiceProvider.php`
10. `apps/web/config/oneqay.php`
11. `apps/web/database/migrations/0000_00_00_000022_create_pos_shift_opening_cash_evidence_foundation.php`
12. `apps/web/routes/web.php`
13. `apps/web/tests/pos-shift-opening-cash-durable.php`
14. `docs/JRN_010_PREREQUISITE_POS_BOUNDED_SHIFT_OPENING_CASH_EVIDENCE_FOUNDATION.md`

Sorted newline-terminated source-path SHA-256:

`1d75fe26236e2bb56c113713e878e81d3df72cee9aa647b3f7759b9d1bec9164`

No extra path is authorized in the source implementation.

## 21. Explicit non-scope

Excluded:

- shift close;
- closing cash count;
- denomination counting;
- expected cash calculation;
- variance;
- arbitrary cash-in/cash-out;
- drawer administration;
- settlement;
- accounting;
- purchasing/suppliers;
- stocktake/transfer/adjustment;
- partial return/refund;
- provider integration;
- offline mutation;
- deployment;
- release;
- updater activation;
- Technical Preview activation;
- Production activation;
- migration execution/application;
- rollback;
- destructive database operations.

## 22. JRN-010 remains locked

JRN-010 Shift Close and Operational Reconciliation remains **NOT SELECTED**.

This schema gate does not define closing-count, expected-cash, variance, close authority, settlement, or accounting semantics.

## 23. Lifecycle posture

Migration #22:

`SOURCE DESIGN SELECTED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED`

Migration #21 and earlier source migrations remain source-published only.

Technical Preview remains **INACTIVE**.

Production remains **NO-GO**.

Updater remains **INACTIVE**.

Deployment, release, migration execution, rollback, and destructive database operations remain **NOT AUTHORIZED**.

## 24. Exact gate envelope

This schema/source gate changes exactly one path:

`docs/SPRINT_53_JRN_010_PREREQUISITE_SHIFT_OPENING_CASH_EVIDENCE_SCHEMA_SOURCE_ENVELOPE_GATE.md`

Sorted newline-terminated path SHA-256:

`a4203b0a3a9e5c22ce824a2bb9fdf90d2a90f8293f84b465d6721d82c86d1f22`

Attribution: **Lab | zefry**
