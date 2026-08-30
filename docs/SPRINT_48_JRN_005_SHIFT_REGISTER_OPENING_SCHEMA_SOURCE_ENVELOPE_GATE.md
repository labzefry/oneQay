# Sprint48 JRN-005 Shift/Register Opening Schema / Source Envelope Gate

Author by Lab | zefry

## Status

**SELECTED / SCHEMA-SOURCE ENVELOPE GATE / SOURCE NOT YET IMPLEMENTED / MIGRATION_18_SELECTED_NOT_EXECUTED**

## Canonical predecessor

This gate starts from canonical `main`:

- commit: `35b622084ccc89213dac5b95ec2d8f7068777ac9`;
- tree: `eed9ede9416c7cdbf6a2bfb5969fab7011174a1f`;
- Sprint48 JRN-005 entry gate: PR #454;
- exact historical compatibility predecessor for this gate: PR #456;
- Sprint47 JRN-004 source remains canonical from PR #440;
- canonical migrations are exactly #1 through #17.

Migrations #16 and #17 remain **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**. Technical Preview remains **NO_SCHEMA_CHANGE / NOT ACTIVATED**. Production remains **NO-GO**.

## Targeted source finding

Canonical `PosExecutionContext` already contains verified actor, tenant, organization, outlet, and device context and fails closed without outlet/device evidence. The foundational context graph binds each device to its exact tenant, organization, and outlet.

Canonical source has no durable shift or register lifecycle state in migrations #1 through #17. Therefore **NO_SCHEMA_CHANGE is rejected** for this bounded JRN-005 concern.

For this smallest Sprint48 foundation, the verified `device_id` is selected as the bounded **register execution context**. No separate register-administration model is selected and no caller-provided register identifier is accepted.

## Schema decision

Sprint48 selects exactly one future source migration:

`apps/web/database/migrations/0000_00_00_000018_create_pos_shift_opening_foundation.php`

Migration #18 is **SELECTED IN SOURCE DESIGN ONLY / NOT CREATED BY THIS GATE / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**.

The future migration may create exactly one table:

`oneqay_pos_shifts`

Required columns:

- `tenant_id` string(64);
- `shift_id` char(32), server-owned;
- `operation_id` string(128);
- `payload_fingerprint` char(64);
- `actor_identity_id` string(96);
- `organization_id` string(64);
- `outlet_id` string(64);
- `device_id` string(64);
- `active_slot` nullable unsigned tiny integer;
- `correlation_id` string(128);
- `opened_at_unix` unsigned bigint.

Required constraints:

- primary: `tenant_id + shift_id`;
- unique idempotency: `tenant_id + operation_id`;
- unique active occupancy: `tenant_id + outlet_id + device_id + active_slot`;
- index: `tenant_id + outlet_id + opened_at_unix`;
- tenant-bound foreign keys to canonical identity, organization, outlet, and device;
- forward-only `down()` that fails closed.

Sprint48 opening writes server-owned `active_slot = 1`. No caller may supply or modify it. The database unique constraint is the final concurrency arbiter for one active shift in an exact device-backed register context. A future separately authorized close concern may decide how occupancy is released while preserving opening evidence.

No migration #1 through #17 may be changed.

## Authorization and command boundary

The future source adds exactly one permission:

`pos.shift.open`

It is deny-by-default and receives no default grant. Existing `pos.sale.complete` and `pos.catalog.prepare` do not imply it.

The future command may accept only:

- `operation_id`.

The caller must not submit tenant, organization, outlet, device, register, actor, role, permission, session authority, shift id, active state, correlation id, opened-at time, or opening cash.

All authority and context remain server-derived.

## Transaction and replay semantics

The future `OpenShift` service must execute under canonical `PersistenceTransaction`:

1. obtain verified organizational context;
2. build exact `PosExecutionContext`;
3. require `pos.shift.open`;
4. validate the closed command;
5. obtain server-owned time;
6. lock/look up exact `tenant_id + operation_id`;
7. exact replay with the same semantic fingerprint returns the original opening result without a second row;
8. conflicting replay fails closed;
9. first operation derives a server-owned deterministic `shift_id` and inserts one row with `active_slot = 1`;
10. a second active opening for the same tenant/outlet/device is rejected by the database uniqueness boundary.

The semantic fingerprint must bind actor, tenant, organization, outlet, device-backed register context, and command semantics.

Different verified devices in one outlet may have independent active shifts. Cross-tenant, cross-outlet, unknown, stale, or caller-selected context fails closed.

## Opening evidence

The bounded result may expose only shift id, operation id, tenant id, outlet id, device-backed register context, opened-at time, correlation identity, and resulting active state.

Replay must not rewrite opening evidence. Opening cash amount, close state, cash reconciliation, and cash movement are not part of this foundation.

## JRN-006 relationship

This source envelope intentionally does **not** modify JRN-006 sale-completion files.

The current JRN-006 sale foundation remains unchanged while durable shift opening is published. A later separately bounded gate must decide how active-shift evidence becomes a fail-closed precondition for sale completion.

Completed sales and sale-line price snapshots remain immutable.

## Runtime posture

Future feature flag:

`ONEQAY_POS_SHIFT_OPENING_ENABLED`

Canonical config key:

`oneqay.pos_shift_opening.enabled`

It must default to `false`.

The future HTTP boundary may expose only:

`POST /pos/shifts/open`

It remains Local/Test/CI only, requires active first-party session control, reuses `RequirePosSessionContextMiddleware`, uses bounded mutation throttling, and remains unavailable in Technical Preview and Production by default.

## Explicit non-scope

This gate does not authorize shift close, JRN-010 reconciliation, opening cash, cash count/variance, cash ledger, register CRUD/administration, device enrollment, outlet administration, JRN-006 active-shift enforcement in this source envelope, sale mutation, void, cancellation, return, refund, stock administration, purchasing, provider integration, accounting, offline POS, deployment, release, updater activation, migration execution, rollback, Technical Preview activation, or Production activation.

## Frozen future source envelope

The next bounded source implementation is frozen to exactly these 15 paths:

1. `.github/workflows/sprint48-jrn005-shift-register-opening-regression.yml`
2. `apps/web/app/Application/Authorization/PosPermission.php`
3. `apps/web/app/Application/Pos/ShiftOpeningClock.php`
4. `apps/web/app/Application/Pos/ShiftOpeningCommand.php`
5. `apps/web/app/Application/Pos/ShiftOpeningRepository.php`
6. `apps/web/app/Application/Pos/ShiftOpeningResult.php`
7. `apps/web/app/Application/Pos/OpenShift.php`
8. `apps/web/app/Delivery/Http/Pos/PosShiftOpeningController.php`
9. `apps/web/app/Infrastructure/Pos/LaravelShiftOpeningRepository.php`
10. `apps/web/app/Providers/AppServiceProvider.php`
11. `apps/web/config/oneqay.php`
12. `apps/web/database/migrations/0000_00_00_000018_create_pos_shift_opening_foundation.php`
13. `apps/web/routes/web.php`
14. `apps/web/tests/pos-shift-opening-durable.php`
15. `docs/JRN_005_POS_SHIFT_REGISTER_OPENING_FOUNDATION.md`

Sorted newline-terminated path SHA-256:

`5e19664988cabba0030f9927d26b0702370414be4cd6b424d585925b634ca2b8`

No source path outside this envelope is authorized.

## Required source regression proof

The future dedicated regression must prove:

- exact 15-path envelope and fingerprint;
- migrations #1 through #17 unchanged and exactly one source migration #18 added;
- migration #18 is forward-only and not executed/applied/activated;
- source-default feature disabled and Local/Test/CI-only;
- active first-party session plus exact verified tenant/organization/outlet/device context;
- deny-by-default `pos.shift.open`;
- rejection of caller-selected authority;
- tenant/outlet/device isolation;
- exact replay and conflicting replay;
- one-active-shift enforcement under concurrent attempts;
- immutable opening evidence on replay;
- no JRN-004 regression;
- no JRN-006 source/evidence mutation;
- historical regressions remain executable;
- tracked-source cleanliness;
- no `jobs=[]` accepted as success.

Compatibility corrections, if terminal evidence requires them, must remain separate workflow-only predecessor PRs. Migration #18 may only be temporarily isolated from historical executable fixtures for exact governed fingerprints and must never be treated as executed or activated.

## Gate envelope

This PR changes exactly:

`docs/SPRINT_48_JRN_005_SHIFT_REGISTER_OPENING_SCHEMA_SOURCE_ENVELOPE_GATE.md`

Sorted newline-terminated path SHA-256:

`94fbf396830524ff1314ec1d60042a64b4307ba20d81d5f034d2da3f713efa22`

Unknown shapes remain fail closed.

## Lifecycle locks

- migration #16: **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**;
- migration #17: **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**;
- migration #18: **SELECTED IN SOURCE DESIGN ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**;
- Technical Preview: **NO_SCHEMA_CHANGE / NOT ACTIVATED**;
- Production: **NO-GO**;
- updater: **DISABLED / UNWIRED**;
- deployment/release/migration execution/rollback: **NOT AUTHORIZED**.

After canonical publication of this gate, only the frozen 15-path JRN-005 source implementation may proceed under the existing bounded repository authority.

Attribution: **Lab | zefry**
