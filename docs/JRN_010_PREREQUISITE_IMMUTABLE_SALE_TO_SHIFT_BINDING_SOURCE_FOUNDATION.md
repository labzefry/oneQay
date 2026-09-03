# JRN-010 Prerequisite — Immutable Sale-to-Shift Binding Source Foundation

Author by Lab | zefry

## Status

`SPRINT57 SOURCE FOUNDATION / MIGRATION #24 SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED / EXPECTED-CASH IMPLEMENTATION NOT SELECTED / JRN-010 SHIFT CLOSE NOT SELECTED`

## Bounded purpose

This source foundation implements only the immutable server-derived relationship between a newly completed durable sale and the exact canonical active shift that authorized first completion.

It closes the historical-membership gap frozen by the Sprint56 entry gate and the Sprint57 schema/source-envelope gate without implementing expected-cash arithmetic, variance, or final shift close.

## Canonical schema delta

Migration #24 is published at:

`apps/web/database/migrations/0000_00_00_000024_add_immutable_shift_binding_to_pos_sales.php`

The migration:

- adds the unique shift context key `(tenant_id, shift_id, organization_id, outlet_id, device_id)` to `oneqay_pos_shifts`;
- adds nullable `shift_id` to `oneqay_pos_sales` with no default;
- adds deterministic `(tenant_id, shift_id, completed_at_unix)` lookup support;
- adds a composite foreign key requiring the sale binding to match the referenced shift's tenant, organization, outlet, and device context;
- uses restrictive update/delete semantics;
- performs no historical backfill;
- remains forward-only.

Nullable `shift_id` exists only to preserve historical pre-binding sale rows without inventing membership. New canonical sale completion through this source foundation persists a non-null server-derived shift binding.

## Completion semantics

`LaravelDurablePosSaleRepository::complete()` keeps operation replay first.

For a fresh operation only, it resolves the active shift from server-owned execution context using exact tenant, organization, outlet, device, and `active_slot = 1`, locks that row, validates its canonical `shift_id`, and persists that exact identity in the first durable sale insert.

The caller receives no shift selector and cannot provide or override `shift_id`.

## Replay lock

Existing operations do not resolve a current active shift before returning their durable sale evidence.

Therefore:

- a post-binding sale remains bound to the original shift after a later shift becomes active;
- a historical row with `shift_id = NULL` is not silently repaired during ordinary replay;
- conflicting operation reuse continues to fail closed under the existing payload-fingerprint contract;
- replay does not create a new sale-to-shift relationship.

## Historical evidence posture

Migration #24 contains no data update or backfill.

Historical sale membership is not inferred from outlet/time, device/time, current active shift, nearest shift evidence, mutable audit state, or caller input.

A later reconciliation-authoritative expected-cash implementation must fail closed when required historical sale evidence lacks an independently canonical exact shift binding.

## Preserved semantics

This foundation does not change:

- completed-sale amount calculation;
- integer atomic money rules;
- tender classification;
- stock decrement semantics;
- sale operation idempotency;
- full-sale void semantics;
- full CASH refund semantics;
- shift opening evidence;
- shift opening-cash evidence;
- shift closing-cash observation semantics.

Void and refund evidence do not rebind the original sale to another shift.

## Regression boundary

`apps/web/tests/pos-sale-shift-binding-durable.php` proves the new binding behavior, including exact binding persistence, organization/outlet/device/tenant isolation, replay preservation, historical-null non-repair, database context rejection, and operation conflict denial.

The Sprint57 workflow enforces the exact five-path source envelope and preserves earlier sale completion/void/refund regression horizons without applying migration #24 to those historical fixtures.

## Explicit non-scope

This source foundation does not implement or authorize:

- expected-cash derivation service or persistence;
- historical sale backfill;
- variance calculation;
- variance tolerance or explanation;
- final shift close or state transition;
- reviewer/reopen policy;
- settlement/provider reconciliation;
- denomination or arbitrary cash movement;
- deployment or release;
- updater activation;
- Technical Preview activation;
- Production activation;
- migration execution/application outside bounded test qualification;
- rollback or destructive database operation.

## Next gate

After this source foundation is canonical and post-merge verified, perform a fresh bounded reconciliation of expected-cash source readiness using the immutable sale-to-shift binding as canonical durable evidence.

That reconciliation must not silently select final JRN-010 Shift Close.

## Lifecycle posture

Migration #22: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #23: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #24: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Technical Preview: **INACTIVE**

Production: **NO-GO**

Updater: **INACTIVE**

Deployment/release: **NOT AUTHORIZED**

Migration execution/application: **NOT AUTHORIZED**

Rollback/destructive database operations: **NOT AUTHORIZED**

Attribution: **Lab | zefry**
