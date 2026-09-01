# Sprint49 JRN-006 Active Shift Sale-Completion Precondition Schema / Source Envelope Gate

Author by Lab | zefry

## Status

**SELECTED / SCHEMA-SOURCE ENVELOPE GATE / SOURCE NOT YET IMPLEMENTED / NO_SCHEMA_CHANGE**

## Canonical predecessor

This gate starts from canonical `main`:

- commit: `19dfc061b831ffa8eade784d6dc2d8748102cb12`;
- tree: `49a43586ffe1b2187456ad2165476acc6fb73285`;
- Sprint49 JRN-006 active-shift precondition entry gate is source-published through PR #482;
- Sprint49 entry-gate historical compatibility predecessor is source-published through PR #483;
- Sprint48 JRN-005 shift/register opening source remains canonical from PR #458;
- Sprint46 JRN-006 sale/payment/receipt source remains canonical;
- canonical migrations are exactly #1 through #18.

Migrations #16, #17, and #18 remain **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**.

Technical Preview remains **NO_SCHEMA_CHANGE / NOT ACTIVATED**.

Production remains **NO-GO / NOT AUTHORIZED**.

Updater remains **NOT ACTIVATED**.

No deployment, release, migration execution/application/activation, rollback, force update, protected-main bypass, or Production authority is created here.

## Targeted canonical source findings

Exact canonical source establishes all persistence needed for the bounded Sprint49 concern.

Canonical JRN-006:

- reconstructs `PosExecutionContext` from verified server-owned organizational context;
- requires `pos.sale.complete` before durable completion;
- executes durable completion under canonical `PersistenceTransaction`;
- uses `tenant_id + operation_id` as the durable idempotency boundary;
- checks existing operation state before any fresh catalog/stock/sale mutation;
- exact replay hydrates and returns the original sale receipt;
- conflicting replay fails closed;
- fresh sale catalog and stock work is tenant + outlet scoped;
- completed sale evidence already records server-owned actor, tenant, organization, outlet, and device context.

Canonical JRN-005 migration #18 already defines durable `oneqay_pos_shifts` with:

- `tenant_id`;
- `shift_id`;
- `actor_identity_id`;
- `organization_id`;
- `outlet_id`;
- `device_id`;
- `active_slot`;
- durable operation/correlation/opened-at evidence;
- unique active occupancy on `tenant_id + outlet_id + device_id + active_slot`.

Sprint48 writes server-owned `active_slot = 1`.

Therefore the exact active-shift precondition can be resolved from existing durable state inside the existing JRN-006 transaction boundary.

## Schema decision

**NO_SCHEMA_CHANGE is selected.**

Migration #19 is **NOT SELECTED**.

No sale column, shift column, linkage table, journal table, foreign key, or new migration is required for this bounded foundation.

This gate deliberately does **not** add `shift_id` to `oneqay_pos_sales`.

The selected Sprint49 concern is only a fail-closed execution precondition. Immutable sale-to-shift linkage evidence, if later required for JRN-010, reporting, audit expansion, or shift-close reconciliation, must be separately gated and must not be smuggled into this foundation.

Migrations #1 through #18 must remain byte-preserved by the future source implementation.

## Exact active-shift lookup boundary

For a fresh JRN-006 operation only, canonical source must require one row equivalent to:

- `tenant_id = current server-derived tenant`;
- `outlet_id = current server-derived outlet`;
- `device_id = current server-derived device-backed register context`;
- `active_slot = 1`.

The lookup must occur inside the same persistence transaction used by sale completion.

The row must be read with a database lock suitable for preserving a deterministic current active-state decision within that transaction.

No caller-provided shift id or register id may participate in authority resolution.

An active shift from:

- another tenant;
- another outlet;
- another device in the same outlet;
- a non-active slot;

must never satisfy the precondition.

Missing exact active-shift evidence fails closed with the existing bounded POS transaction failure behavior.

## Replay ordering decision

The exact canonical JRN-006 operation lookup remains first.

The future source ordering is frozen as:

1. preserve the existing operational/runtime guard;
2. compute the existing JRN-006 semantic fingerprint;
3. lock and resolve existing `tenant_id + operation_id`;
4. if an existing sale exists:
   - preserve existing fingerprint conflict denial;
   - exact replay returns existing sale/receipt evidence;
   - do **not** require a currently active shift again;
5. if no existing sale exists:
   - require exact current active shift for tenant + outlet + device-backed register context;
   - only after that requirement passes may catalog resolution, stock mutation, sale insert, line insert, and completion-event evidence proceed.

This ordering is required because idempotent replay represents an already-completed immutable business operation and must not become dependent on later operational shift state.

A fresh operation never bypasses active-shift enforcement.

## Transaction and locking posture

No new transaction service is selected.

Canonical `CompleteSale` already wraps repository completion in `PersistenceTransaction`.

The active-shift read therefore belongs inside the existing durable repository transaction path before fresh sale mutation.

The future implementation must not:

- perform an unlocked application-only precheck outside the transaction;
- open a nested independent transaction;
- create or close a shift as a sale side effect;
- modify `active_slot`;
- rewrite JRN-005 opening evidence.

## Authorization posture

Canonical permission remains:

`pos.sale.complete`

No new permission is selected.

No default grant is created.

Active shift is an additional operational precondition and never replaces:

- first-party session authority;
- verified organizational context;
- canonical scoped authorization;
- existing JRN-006 command validation.

## Caller-input posture

The future source does not expand `SaleCommand`.

Caller-controlled:

- tenant;
- organization;
- outlet;
- device;
- register;
- shift;
- actor;
- role;
- permission;
- session authority;
- active-state assertion;

remain forbidden.

No `shift_id` input is selected.

## Runtime posture

No new feature flag is selected.

The canonical JRN-006 runtime boundary remains governed by:

`ONEQAY_POS_SALE_COMPLETION_ENABLED`

The canonical JRN-005 shift-opening flag remains separately governed by:

`ONEQAY_POS_SHIFT_OPENING_ENABLED`

Sprint49 does not couple feature activation or grant runtime authority.

The source implementation remains Local/Test/CI only under existing JRN-006 delivery locks.

Technical Preview and Production remain unactivated.

## Frozen future source envelope

The next bounded Sprint49 source implementation is frozen to exactly these four paths:

1. `.github/workflows/sprint49-jrn006-active-shift-precondition-regression.yml`
2. `apps/web/app/Infrastructure/Pos/LaravelDurablePosSaleRepository.php`
3. `apps/web/tests/pos-sale-completion-active-shift.php`
4. `docs/JRN_006_POS_SALE_COMPLETION_PAYMENT_RECEIPT_FOUNDATION.md`

Sorted newline-terminated path SHA-256:

`154e4e0f6fb898b2c5c677764571a5a9a57c31371c852f16e89a74f3b104adec`

No application/source path outside this envelope is authorized by this gate.

In particular, the future source must not modify:

- `CompleteSale.php`;
- `DurablePosSaleRepository.php`;
- `SaleCommand.php`;
- `SaleReceipt.php`;
- `PosExecutionContext.php`;
- `AppServiceProvider.php`;
- route/config files;
- authorization permission definitions;
- migration #16;
- migration #18;
- any migration #1 through #18.

## Regression strategy

The future source creates a new dedicated Sprint49 test rather than mutating the historical Sprint46 durable sale test.

This preserves historical JRN-006 regression semantics while allowing Sprint49 to execute the full canonical migration horizon through #18.

The dedicated Sprint49 regression must prove at least:

- exact four-path source envelope and fingerprint;
- migrations exactly #1 through #18 and byte-preserved;
- migration #19 absent;
- exact replay succeeds without requiring active-shift revalidation;
- conflicting replay still fails closed;
- fresh sale denied when no active shift exists;
- fresh sale denied when only another tenant has an active shift;
- fresh sale denied when only another outlet has an active shift;
- fresh sale denied when only another device at the same outlet has an active shift;
- fresh sale succeeds with the exact tenant/outlet/device active shift;
- `pos.sale.complete` authorization remains mandatory;
- caller cannot select shift/register authority;
- stock decrement occurs only after active-shift precondition succeeds;
- failed active-shift precondition leaves sale/payment/stock/event state unchanged;
- JRN-005 opening evidence remains unchanged by sale completion;
- existing JRN-006 idempotency remains `tenant_id + operation_id`;
- historical Sprint46 JRN-006 durable regression remains executable;
- Sprint48 JRN-005 durable regression remains executable;
- JRN-004 catalog regression remains executable;
- M7.4 synthetic POS regression remains executable;
- tracked-source cleanliness;
- no material workflow with `jobs=[]` qualifies as success.

## Dedicated workflow posture

The future dedicated workflow:

`.github/workflows/sprint49-jrn006-active-shift-precondition-regression.yml`

must:

- trigger only for the exact four source paths;
- enforce source fingerprint `154e4e0f6fb898b2c5c677764571a5a9a57c31371c852f16e89a74f3b104adec`;
- reject migration changes;
- require exactly migrations #1 through #18;
- run PHP syntax validation;
- install locked dependencies;
- reject High/Critical Composer advisories;
- run the dedicated Sprint49 active-shift sale regression;
- preserve relevant JRN-004, JRN-005, JRN-006, and M7.4 regressions;
- enforce tracked-source cleanliness.

Historical compatibility corrections, if exact terminal evidence requires them, must remain separate workflow-only predecessor PRs.

Unknown changed-file shapes remain fail closed.

## JRN-005 immutability

Sprint49 consumes existing active-shift state as a precondition only.

It does not authorize:

- shift open;
- shift close;
- active-slot mutation;
- shift reassignment;
- shift handoff;
- shift reopen;
- changing one-active-shift uniqueness;
- rewriting `opened_at_unix`, opening correlation, operation identity, or actor evidence.

## JRN-010 boundary

JRN-010 remains **NOT SELECTED**.

No expected-vs-actual reconciliation, cash count, variance, reviewer approval, provider settlement, close timestamp, close state, or operational report semantics are created here.

A future shift-close/reconciliation concern may separately decide whether sales require immutable shift linkage for reconciliation. That future concern must not retroactively expand this gate.

## Explicit non-scope

This gate does not authorize:

- migration #19;
- schema changes;
- shift close;
- opening/closing cash;
- cash ledger;
- JRN-010;
- void/refund/return;
- purchasing;
- supplier lifecycle;
- broad inventory administration;
- register administration;
- external provider integration;
- accounting;
- offline POS;
- Technical Preview activation;
- Production activation;
- deployment;
- release;
- updater activation;
- migration execution/application;
- rollback;
- protected-main bypass;
- force update.

## Gate envelope

This PR changes exactly:

`docs/SPRINT_49_JRN_006_ACTIVE_SHIFT_SALE_COMPLETION_PRECONDITION_SCHEMA_SOURCE_ENVELOPE_GATE.md`

Sorted newline-terminated path SHA-256:

`41fd19359942f3d271740e81609eb786fd3b02b701f7cb2fb7a4380d84425054`

Unknown shapes remain fail closed.

## Lifecycle locks

- migration #16: **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**;
- migration #17: **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**;
- migration #18: **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**;
- migration #19: **NOT SELECTED**;
- Technical Preview: **NO_SCHEMA_CHANGE / NOT ACTIVATED**;
- Production: **NO-GO / NOT AUTHORIZED**;
- updater: **NOT ACTIVATED**;
- deployment/release/migration execution/rollback: **NOT AUTHORIZED**.

After canonical publication of this gate, only the frozen four-path Sprint49 source implementation may proceed under the existing bounded repository authority.

Attribution: **Lab | zefry**
