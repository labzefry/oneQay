# Sprint54 JRN-010 Prerequisite Shift Closing Cash Evidence Schema & Source Envelope Gate

Author by Lab | zefry

## 1. Gate classification

`SCHEMA + SOURCE ENVELOPE SELECTED / SOURCE IMPLEMENTATION NOT YET PUBLISHED / JRN-010 NOT SELECTED / LIFECYCLE NOT AUTHORIZED`

Canonical base:

`4c16528834b4ce620e71c813b3451b6c205c4e18`

The canonical predecessor is the published Sprint54 bounded Shift Closing Cash Observation Evidence entry gate.

This gate freezes only the minimum schema and source design for one immutable operator-observed closing-cash fact per canonical shift. It does not execute or apply migration #23 and creates no shift-close, expected-cash, variance, settlement, Technical Preview, Production, deployment, release, updater, rollback, or destructive lifecycle authority.

## 2. Selected concern retained

The selected concern remains exactly:

**JRN-010 prerequisite — Bounded Shift Closing Cash Observation Evidence Foundation**

A future operation records one explicit closing cash observation for the exact current active shift derived from verified server context.

Fresh establishment requires canonical opening-cash evidence for the same shift and exact currency/scale compatibility with that opening observation.

Zero is valid only when explicitly supplied as the observation. Missing closing cash is never converted to zero and the observation is never derived from expected system cash.

The record remains operator-observed evidence and is not independent proof of denomination composition, physical custody, acceptable variance, settlement, or accounting balance.

## 3. Why dedicated schema is required

`NO_SCHEMA_CHANGE` is rejected.

The current `oneqay_pos_shifts` row has no closing cash amount, closing observation operation identity, closing observation fingerprint, separate closing observer evidence, or replay boundary.

The Sprint53 `oneqay_pos_shift_opening_cash_evidence` table represents a different immutable fact and must not be mutated or overloaded with closing data.

Therefore one additive immutable table is required.

## 4. Selected migration

Exact future migration path:

`apps/web/database/migrations/0000_00_00_000023_create_pos_shift_closing_cash_evidence_foundation.php`

Migration #23 posture:

`SOURCE DESIGN SELECTED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED`

No migration #24 or other schema change is selected.

The migration remains forward-only. Its `down()` path must throw:

`LogicException('Forward-only generated migration; rollback is not authorized.')`

## 5. Selected durable table

Migration #23 creates exactly:

`oneqay_pos_shift_closing_cash_evidence`

The table is an immutable closing-cash observation and durable replay/uniqueness boundary.

It is not a shift-close table, cash-movement ledger, denomination table, drawer ledger, variance table, settlement table, reviewer journal, reopen journal, or accounting journal.

## 6. Exact migration #23 columns

The selected columns are:

- `tenant_id VARCHAR(64)`;
- `evidence_id VARCHAR(32)`;
- `operation_id VARCHAR(128)`;
- `payload_fingerprint CHAR(64)`;
- `shift_id CHAR(32)`;
- `opening_cash_evidence_id VARCHAR(32)`;
- `actor_identity_id VARCHAR(96)`;
- `organization_id VARCHAR(64)`;
- `outlet_id VARCHAR(64)`;
- `device_id VARCHAR(64)`;
- `closing_cash_atomic UNSIGNED BIGINT`;
- `currency CHAR(3)`;
- `currency_scale UNSIGNED TINYINT`;
- `evidence_mode VARCHAR(40)`;
- `correlation_id VARCHAR(128)`;
- `recorded_at_unix UNSIGNED BIGINT`.

No mutable status, expected cash, variance, tolerance, explanation, reviewer, denomination, settlement, reopen, or accounting column is selected.

## 7. Exact keys and constraints

Primary key:

`tenant_id + evidence_id`

Unique replay key:

`tenant_id + operation_id`

Unique one-closing-cash-observation-per-shift key:

`tenant_id + shift_id`

Required index:

`tenant_id + outlet_id + recorded_at_unix`

Required restrictive foreign keys:

- `tenant_id + shift_id` -> `oneqay_pos_shifts(tenant_id, shift_id)`;
- `tenant_id + opening_cash_evidence_id` -> `oneqay_pos_shift_opening_cash_evidence(tenant_id, evidence_id)`;
- `tenant_id + actor_identity_id` -> canonical identities;
- `tenant_id + organization_id` -> canonical organizations;
- `tenant_id + outlet_id` -> canonical outlets;
- `tenant_id + device_id` -> canonical devices.

All update/delete behavior remains restrictive.

## 8. Exact permission

Exact permission:

`pos.shift.closing-cash.record`

`PosPermission` must expose a dedicated constant and typed helper.

No default role grant is included.

No existing shift-open, opening-cash, sale, void, refund, catalog, inventory, or administration permission implies this authority.

## 9. Exact command shape

Application command:

`ShiftClosingCashCommand`

Exact constructor inputs:

1. `operationId`;
2. canonical `Money closingCash`.

The delivery layer builds `Money` only from exact caller fields:

- `closing_cash_atomic`;
- `currency`;
- `currency_scale`.

The command semantic fingerprint part must bind exactly:

`CLOSING_CASH|<currency>:<scale>:<atomic_units>`

Shift id, opening-cash evidence id, tenant, organization, outlet, device, actor, correlation identity, and time are not command inputs.

## 10. Exact result shape

Application result:

`ShiftClosingCashResult`

It exposes:

- evidence id;
- opening-cash evidence id;
- shift id;
- operation id;
- tenant id;
- outlet id;
- device id;
- closing cash `Money`;
- evidence mode;
- correlation identity;
- recorded-at Unix time.

It exposes no mutable shift state, expected cash, variance, reviewer/approval, settlement, reopen, or accounting state.

## 11. Exact Application service

Application service:

`RecordShiftClosingCash`

Dependencies:

- `ShiftClosingCashRepository`;
- `OrganizationalContextStore`;
- `DurableScopedAuthorizationPolicy`;
- `PersistenceTransaction`;
- existing `ShiftOpeningClock`.

The service must:

1. validate canonical correlation identity;
2. derive `PosExecutionContext` from current verified organizational context;
3. require `PosPermission::recordShiftClosingCash()`;
4. obtain positive server time from `ShiftOpeningClock`;
5. execute repository recording inside canonical `PersistenceTransaction`.

Application remains framework independent.

## 12. Exact repository abstraction

Interface:

`ShiftClosingCashRepository`

One bounded method equivalent to:

`record(PosExecutionContext, ShiftClosingCashCommand, correlationId, recordedAtUnix): ShiftClosingCashResult`

No CRUD/list/update/delete, close, expected-cash, variance, settlement, reopen, or arbitrary cash movement method is selected.

## 13. Dedicated infrastructure adapter

Adapter:

`LaravelShiftClosingCashRepository`

It is separate from `LaravelShiftOpeningRepository` and `LaravelShiftOpeningCashRepository` so exact replay can return immutable historical closing observation evidence independently of future shift lifecycle state.

Dependencies are limited to:

- canonical database connection;
- persistence-enabled state;
- runtime class;
- exact closing-cash feature flag.

## 14. Exact persistence ordering

### A. Operational guard

Deny unless:

- persistence is enabled;
- exact closing-cash feature flag is enabled;
- runtime is Local/Test/CI.

### B. Deterministic fingerprint

Fingerprint binds:

- current actor;
- tenant;
- organization;
- outlet;
- device;
- command semantic fingerprint.

### C. Operation replay before mutable-state validation

Lock `oneqay_pos_shift_closing_cash_evidence` by exact tenant + operation id first.

If found:

- fingerprint must match exactly;
- evidence mode must match exactly;
- return original result;
- do not require the shift to still be active;
- do not create another write.

Conflicting operation reuse fails closed.

### D. Fresh exact active-shift resolution

For a new operation, lock the exact current active shift by:

- tenant;
- outlet;
- device;
- `active_slot = 1`.

Organization must match verified context.

Missing, inactive, wrong-organization, wrong-device, ambiguous, or cross-context shift state fails closed.

### E. Same-shift opening-cash prerequisite

Lock `oneqay_pos_shift_opening_cash_evidence` for the same tenant + shift.

Fresh establishment fails closed unless exactly one canonical opening-cash row exists.

The opening evidence must have:

- valid evidence id;
- exact same shift id;
- currency equal to the closing observation currency;
- currency scale equal to the closing observation scale.

No conversion, rounding, fallback currency, or hidden money profile is selected.

### F. One-closing-observation-per-shift check

Lock any existing closing-cash evidence for tenant + shift.

If one already exists under a different operation id, fail closed.

Database uniqueness remains the final concurrency arbiter.

### G. Insert only immutable closing observation evidence

The adapter inserts one row only into `oneqay_pos_shift_closing_cash_evidence`.

It does not mutate:

- `oneqay_pos_shifts.active_slot`;
- opening-cash evidence;
- sale/payment/receipt evidence;
- void/refund evidence;
- catalog/inventory state;
- expected cash;
- variance;
- settlement;
- accounting state.

## 15. Evidence identity and mode

Evidence id must be deterministic from tenant + operation id with prefix:

`cashclose-`

The bounded identifier remains at most 32 characters.

Exact evidence mode:

`OPERATOR_OBSERVED_CLOSING_CASH`

No calculated, imported, provider-derived, settlement-derived, or manager-adjusted evidence mode is selected.

## 16. Exact HTTP delivery boundary

Controller:

`PosShiftClosingCashController`

Exact future route:

`POST /pos/shifts/closing-cash`

Exact route name:

`pos.shifts.closing-cash`

Required existing middleware:

- active first-party session;
- bounded throttles equivalent to other shift money mutations;
- `RequirePosSessionContextMiddleware`.

Exact accepted request keys:

- `operation_id`;
- `closing_cash_atomic`;
- `currency`;
- `currency_scale`.

Unknown request keys fail closed.

The caller may not submit shift id, opening-cash evidence id, expected cash, variance, tolerance, reviewer, settlement, actor, tenant, outlet, device, correlation id, or event time.

## 17. Exact safe failure envelope

Authorization denial:

`POS_SHIFT_CLOSING_CASH_AUTHORIZATION_DENIED`

Validation, state, replay conflict, missing opening-cash prerequisite, money incompatibility, duplicate-per-shift, persistence, runtime, or storage rejection:

`POS_SHIFT_CLOSING_CASH_REJECTED`

No storage/database exception detail may escape.

## 18. Exact feature flag

Configuration key:

`oneqay.pos_shift_closing_cash_evidence.enabled`

Environment binding:

`ONEQAY_POS_SHIFT_CLOSING_CASH_EVIDENCE_ENABLED`

Default:

`false`

Runtime activation remains limited to Local/Test/CI even when explicitly armed.

Technical Preview and Production remain unavailable.

## 19. Exact source envelope

Future source implementation is frozen to exactly these 14 paths:

1. `.github/workflows/sprint54-jrn010-prerequisite-shift-closing-cash-evidence-regression.yml`
2. `apps/web/app/Application/Authorization/PosPermission.php`
3. `apps/web/app/Application/Pos/RecordShiftClosingCash.php`
4. `apps/web/app/Application/Pos/ShiftClosingCashCommand.php`
5. `apps/web/app/Application/Pos/ShiftClosingCashRepository.php`
6. `apps/web/app/Application/Pos/ShiftClosingCashResult.php`
7. `apps/web/app/Delivery/Http/Pos/PosShiftClosingCashController.php`
8. `apps/web/app/Infrastructure/Pos/LaravelShiftClosingCashRepository.php`
9. `apps/web/app/Providers/AppServiceProvider.php`
10. `apps/web/config/oneqay.php`
11. `apps/web/database/migrations/0000_00_00_000023_create_pos_shift_closing_cash_evidence_foundation.php`
12. `apps/web/routes/web.php`
13. `apps/web/tests/pos-shift-closing-cash-durable.php`
14. `docs/JRN_010_PREREQUISITE_POS_BOUNDED_SHIFT_CLOSING_CASH_EVIDENCE_FOUNDATION.md`

Sorted newline-terminated source-path SHA-256:

`a0914d7db5c1636e909e331e8b72653bc9814eb74e3a3823d7db36bd3b73b624`

No other source, dependency, environment, workflow, schema, release, deployment, updater, or runtime path is selected.

## 20. Dedicated regression requirements

The dedicated future regression must prove at minimum:

- exact 14-path source envelope and fingerprint;
- migration #23 is the only migration path in the source diff;
- migrations #1–#22 remain byte-preserved;
- migration #23 table/columns/keys/FKs/forward-only down lock;
- dedicated permission and no default grant;
- exact request key closure;
- explicit non-negative atomic money validation;
- same-shift opening-cash prerequisite;
- exact opening/closing currency + scale compatibility;
- missing opening evidence denied;
- money mismatch denied;
- server-derived active shift and context;
- exact replay before mutable active-shift validation;
- operation-id conflict denied;
- second closing observation per shift denied;
- concurrency uniqueness expectation;
- no shift state mutation;
- no opening-cash, sale, refund, catalog, inventory, expected-cash, variance, settlement, or accounting mutation;
- feature default false and Local/Test/CI-only runtime;
- Technical Preview and Production route absence;
- full preserved POS/application regression coverage;
- tracked source cleanliness.

## 21. JRN-010 selection lock

JRN-010 remains **NOT SELECTED**.

This gate still does not define:

- expected-cash derivation;
- full currency/scale eligibility across all CASH sale/refund evidence;
- event cutoff and late-event handling;
- expected-vs-observed variance;
- tolerance or explanation;
- close/reviewer/step-up authority;
- final shift state transition;
- controlled reopen;
- arbitrary cash movement;
- settlement;
- accounting.

Those require later separately bounded gates.

## 22. Lifecycle posture

Migration #22 remains:

`SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED`

Migration #23 becomes only:

`SOURCE DESIGN SELECTED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED`

Technical Preview remains **INACTIVE**.

Production remains **NO-GO / NOT AUTHORIZED**.

Updater remains **INACTIVE**.

Deployment, release, migration execution/application, rollback, and destructive database operations remain **NOT AUTHORIZED**.

## 23. Exact gate envelope

Exact path:

`docs/SPRINT_54_JRN_010_PREREQUISITE_SHIFT_CLOSING_CASH_EVIDENCE_SCHEMA_SOURCE_ENVELOPE_GATE.md`

Sorted newline-terminated path SHA-256:

`fc083122688b957ec94cfaaf67a205413f4a3ead43f5d4ccc756fb180812b852`

No application source, migration content, workflow, dependency, environment, runtime activation, deployment, release, rollback, Production, or migration-execution authority is created by this documentation gate.

Attribution: **Lab | zefry**
