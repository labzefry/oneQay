# JRN-010 Prerequisite — Immutable Sale-to-Shift Binding Schema / Source Envelope Gate

Author by Lab | zefry

## Status

`SPRINT57 SCHEMA/SOURCE-ENVELOPE GATE ONLY / MIGRATION #24 SELECTED SOURCE-PUBLISHED ONLY / NO MIGRATION EXECUTION / NO EXPECTED-CASH IMPLEMENTATION / JRN-010 SHIFT CLOSE NOT SELECTED`

## Canonical input

Sprint56 established that historical expected-cash replay must not infer shift membership from outlet, device, and wall-clock time. A reconciliation-authoritative completed sale therefore requires an immutable server-derived binding to the exact canonical shift that authorized first completion.

Fresh canonical source reconciliation at Sprint57 confirms:

- durable completed sales are created by `0000_00_00_000016_create_pos_sale_completion_foundation.php` in `oneqay_pos_sales`;
- the current sale row contains tenant, organization, outlet, device, immutable monetary evidence, and completion time, but no `shift_id`;
- canonical shifts are created by `0000_00_00_000018_create_pos_shift_opening_foundation.php` in `oneqay_pos_shifts`;
- `oneqay_pos_shifts` has primary key `(tenant_id, shift_id)` and immutable organization/outlet/device ownership columns;
- `LaravelDurablePosSaleRepository::complete()` resolves and locks one active shift using tenant/outlet/device before first completion, but does not currently persist the resolved `shift_id` and does not currently include organization in that active-shift lookup.

This gate freezes the smallest safe schema/source envelope needed to close that specific gap. It does not implement the envelope.

## Selected migration

Migration **#24 is selected** for this bounded prerequisite only.

Canonical future migration path:

`apps/web/database/migrations/0000_00_00_000024_add_immutable_shift_binding_to_pos_sales.php`

Posture:

`SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED`

Sequence number selection does not authorize execution, deployment, updater activation, Technical Preview activation, or Production activation.

## Frozen schema shape

Migration #24 must make only the following bounded schema changes.

### `oneqay_pos_shifts`

Add one unique context key covering:

`tenant_id, shift_id, organization_id, outlet_id, device_id`

The purpose is not to redefine shift identity. Canonical shift identity remains `(tenant_id, shift_id)`. The additional key exists only so the sale binding can be constrained to the same immutable organization/outlet/device context at the database boundary.

### `oneqay_pos_sales`

Add:

- `shift_id` as nullable fixed-width canonical shift identity compatible with `oneqay_pos_shifts.shift_id`;
- no default value;
- an index supporting deterministic lookup by `tenant_id, shift_id, completed_at_unix`;
- a composite foreign key from `tenant_id, shift_id, organization_id, outlet_id, device_id` to the corresponding unique context key on `oneqay_pos_shifts`;
- `restrictOnDelete()` and `restrictOnUpdate()` semantics.

`shift_id` is nullable only to preserve pre-binding historical rows without inventing a relationship. New canonical sale completion after the source envelope is implemented must persist a non-null server-derived binding.

No migration-time backfill is selected.

## Historical rows

Existing sale rows with `shift_id = NULL` remain historical pre-binding evidence.

Migration #24 must not derive or backfill a shift from:

- outlet plus completion time;
- device plus completion time;
- current active shift;
- nearest opening or closing evidence;
- mutable audit state;
- caller input.

Ordinary legacy replay behavior must not silently repair a null binding. A later reconciliation-authoritative expected-cash reader must fail closed when a required sale has no independently canonical exact shift binding.

## Frozen application-source change

Only this existing application source is selected for mutation:

`apps/web/app/Infrastructure/Pos/LaravelDurablePosSaleRepository.php`

The bounded implementation must:

1. keep active-shift resolution server-owned;
2. add exact organization ownership to the active-shift lookup;
3. validate that the locked shift exposes one canonical non-empty `shift_id`;
4. persist that exact locked `shift_id` in `oneqay_pos_sales` during first durable completion;
5. never accept a caller-supplied shift identity;
6. never resolve a current/later active shift when replaying an already existing sale operation;
7. never rewrite an existing binding;
8. preserve existing sale money, inventory, tender, idempotency, void, and refund semantics.

No delivery/controller/request/command field for `shift_id` is selected.

## Replay compatibility

For a post-binding sale, replay must leave the original stored shift binding unchanged even if another shift later becomes active.

For a pre-binding historical sale whose stored binding is null, ordinary compatible replay must not assign the current shift merely to repair the row. This gate does not broaden replay into historical data migration.

Conflicting reuse of an operation remains fail closed under the existing payload-fingerprint contract.

## Frozen implementation envelope

A later source implementation PR for this selected gate is limited to exactly these five paths unless a fresh compatibility failure proves a separately bounded workflow-only correction is required:

1. `.github/workflows/sprint57-jrn010-prerequisite-sale-shift-binding-regression.yml`
2. `apps/web/app/Infrastructure/Pos/LaravelDurablePosSaleRepository.php`
3. `apps/web/database/migrations/0000_00_00_000024_add_immutable_shift_binding_to_pos_sales.php`
4. `apps/web/tests/pos-sale-shift-binding-durable.php`
5. `docs/JRN_010_PREREQUISITE_IMMUTABLE_SALE_TO_SHIFT_BINDING_SOURCE_FOUNDATION.md`

No interface, delivery, route, permission, runtime feature flag, void/refund schema, expected-cash source, or shift-close source is selected by this envelope.

## Required regression proof

The selected regression must prove at minimum:

- first completion persists the exact locked active `shift_id`;
- tenant mismatch fails closed;
- organization mismatch fails closed;
- outlet mismatch fails closed;
- device mismatch fails closed;
- caller cannot provide or override shift identity;
- replay preserves the original binding after active shift changes;
- replay of a historical null-bound row does not repair/rebind it;
- the database rejects a sale binding whose tenant/organization/outlet/device context does not match the referenced shift;
- no heuristic historical backfill occurs;
- existing JRN-006/JRN-007 sale completion, idempotency, money, void, and refund semantics remain compatible.

## Transaction and concurrency lock

The binding must be captured from the same row that is locked as the active shift before first sale persistence.

This gate does not authorize a second independent shift lookup after the sale insert and does not authorize asynchronous repair.

A later implementation qualification must demonstrate that a shift transition racing sale completion cannot cause a committed sale to bind to a different shift from the one authorizing that completion.

## Explicit non-scope

Sprint57 does not implement or authorize:

- migration execution/application;
- migration-time or runtime historical backfill;
- expected-cash service/repository/query/persistence;
- variance or tolerance semantics;
- final shift close/state transition;
- close authority, reviewer, reopen, settlement, denomination, or arbitrary cash movement;
- caller-provided shift identity;
- void/refund shift rebinding;
- deployment or release;
- updater activation;
- Technical Preview activation;
- Production activation;
- rollback or destructive database operations.

## Next action after this gate is canonical

Create the exact five-path implementation envelope above from fresh canonical `main`, qualify only the exact PR head, and preserve migration #24 as source-published only.

Expected-cash implementation remains unselected until this immutable binding source foundation is canonical and post-merge verified.

## Lifecycle posture

Migration #22: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #23: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #24: **SELECTED FOR SOURCE PUBLICATION ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Technical Preview: **INACTIVE**

Production: **NO-GO**

Updater: **INACTIVE**

Deployment/release: **NOT AUTHORIZED**

Migration execution/application: **NOT AUTHORIZED**

Rollback/destructive database operations: **NOT AUTHORIZED**

Attribution: **Lab | zefry**
