# Sprint87 — Final Shift Close Schema Readiness

Author by Lab | zefry

## 1. Purpose

Sprint87 selects the bounded persistence contract required before a future durable Final Shift Close source envelope can be considered.

Sprint87 is schema-readiness only. It does **not** create migration #27, execute a migration, implement `CloseShift`, define or grant a Final Shift Close permission, bind runtime services, activate Technical Preview, deploy a release, activate Production, or select Final Shift Close authority.

## 2. Canonical starting point

Sprint86 established that the canonical repository has durable shift opening, opening/closing cash evidence, expected cash and variance derivation, nonzero-variance explanation evidence, and maker-checker review-decision evidence, but no durable Final Shift Close source or persistence model.

The current `oneqay_pos_shifts` lifecycle uses nullable `active_slot` with value `1` to enforce one active shift per tenant/outlet/device context. It has no immutable final-close evidence, closer identity, close operation fingerprint, or close timestamp.

## 3. Selected future evidence table

A later migration-source sprint may materialize the selected table:

`oneqay_pos_shift_close_evidence`

The table is append-only evidence. It must not replace or rewrite historical opening, closing-cash, explanation, or review evidence.

Selected columns for the future migration contract:

- `tenant_id` — string(64), non-null;
- `evidence_id` — string(32), non-null;
- `operation_id` — string(128), non-null;
- `payload_fingerprint` — char(64), non-null;
- `shift_id` — char(32), non-null;
- `opening_cash_evidence_id` — string(32), non-null;
- `closing_cash_evidence_id` — string(32), non-null;
- `closer_actor_identity_id` — string(96), non-null;
- `organization_id` — string(64), non-null;
- `outlet_id` — string(64), non-null;
- `device_id` — string(64), non-null;
- `cutoff_at_unix` — unsigned bigint, non-null;
- `expected_cash_atomic` — unsigned bigint, non-null;
- `observed_closing_cash_atomic` — unsigned bigint, non-null;
- `variance_atomic` — signed bigint, non-null;
- `variance_direction` — string(8), non-null;
- `currency` — char(3), non-null;
- `currency_scale` — unsigned tinyint, non-null;
- `review_evidence_id` — string(32), nullable;
- `review_outcome` — string(24), nullable;
- `correlation_id` — string(128), non-null;
- `closed_at_unix` — unsigned bigint, non-null.

The future source must derive these values from authoritative canonical state. The caller must not be allowed to choose monetary reconciliation values, evidence bindings, review outcome, or close timestamp as authority.

## 4. Selected keys and foreign-key bindings

The future migration contract must provide:

- primary key: (`tenant_id`, `evidence_id`);
- unique key: (`tenant_id`, `operation_id`) for idempotent operation identity;
- unique key: (`tenant_id`, `shift_id`) so a shift can have only one final-close evidence row;
- index: (`tenant_id`, `outlet_id`, `closed_at_unix`) for bounded operational/audit lookup.

Selected foreign-key bindings:

- (`tenant_id`, `shift_id`) → `oneqay_pos_shifts`;
- (`tenant_id`, `opening_cash_evidence_id`) → `oneqay_pos_shift_opening_cash_evidence`;
- (`tenant_id`, `closing_cash_evidence_id`) → `oneqay_pos_shift_closing_cash_evidence`;
- (`tenant_id`, `closer_actor_identity_id`) → `oneqay_identities`;
- (`tenant_id`, `organization_id`) → `oneqay_organizations`;
- (`tenant_id`, `outlet_id`) → `oneqay_outlets`;
- (`tenant_id`, `device_id`) → `oneqay_devices`;
- nullable (`tenant_id`, `review_evidence_id`) → `oneqay_pos_cash_variance_review_decision_evidence`.

All selected foreign keys are restrict-on-delete/restrict-on-update. Historical close evidence must not cascade away.

## 5. Selected variance/review integrity rule

The future persistence contract must enforce the following semantic branches in addition to application-level exact binding validation:

### MATCH

- `variance_direction = 'MATCH'`;
- `variance_atomic = 0`;
- `review_evidence_id IS NULL`;
- `review_outcome IS NULL`.

### OVER

- `variance_direction = 'OVER'`;
- `variance_atomic > 0`;
- `review_evidence_id IS NOT NULL`;
- `review_outcome = 'REVIEW_ACCEPTED'`.

### SHORT

- `variance_direction = 'SHORT'`;
- `variance_atomic < 0`;
- `review_evidence_id IS NOT NULL`;
- `review_outcome = 'REVIEW_ACCEPTED'`.

`REVIEW_REJECTED` is never a valid value in final-close evidence. A future source must resolve the referenced immutable review row and prove that its tenant/organization/outlet/shift/opening/closing/cutoff/expected/observed/variance/direction/currency/scale state exactly matches the close candidate before insert.

The future migration must preserve MySQL-compatible and isolated SQLite CI parity using a governed CHECK/trigger strategy consistent with canonical migration policy.

## 6. Selected active-slot transition contract

Final close requires two persistence effects that must be atomic:

1. append exactly one immutable `oneqay_pos_shift_close_evidence` row;
2. release the canonical shift active marker by updating `oneqay_pos_shifts.active_slot` from `1` to `NULL` for the exact tenant/shift being closed.

These effects must occur inside the same `PersistenceTransaction` boundary.

The future repository/service must:

- lock the exact canonical shift row before finalization;
- require `active_slot = 1` for a first close attempt;
- verify tenant/organization/outlet/device and shift identity from authoritative state;
- resolve and validate canonical opening-cash and closing-cash evidence;
- derive/validate authoritative expected cash and variance;
- resolve accepted review evidence for nonzero variance;
- insert final-close evidence;
- update exactly one active shift row from `1` to `NULL`;
- treat an affected-row count other than exactly one as a fail-closed transaction failure;
- roll back both effects on any failure.

No path may release `active_slot` before the evidence insert is guaranteed inside the same transaction.

## 7. Idempotency and race contract

The future source must use stable `operation_id` + semantic `payload_fingerprint` behavior consistent with existing durable POS operations.

Required behavior:

- exact replay of an already successful operation returns the same immutable close evidence without re-releasing the active slot;
- same operation ID with a different fingerprint fails closed;
- a competing operation ID for an already closed shift fails closed;
- concurrent close attempts cannot both succeed;
- a failed close must leave the shift active and must not persist partial final-close evidence.

A future implementation may resolve an existing exact operation before requiring `active_slot = 1` so legitimate replay remains possible after the successful transaction has already set `active_slot = NULL`.

## 8. Authority and actor policy remain separate

Sprint87 selects persistence shape only.

It does not select:

- the Final Shift Close permission identifier;
- who receives that permission;
- whether the closer may equal the original shift actor;
- whether the closer may equal the explanation author;
- whether the closer may equal the variance reviewer;
- any default role grant.

The existing reviewer permission remains review-only and must never be interpreted as close authority.

## 9. Future migration-source envelope

If a later bounded sprint is authorized to materialize this schema, the selected candidate migration path is:

`apps/web/database/migrations/0000_00_00_000027_create_pos_shift_close_evidence_foundation.php`

That future sprint must still be source-only unless migration execution receives separate authority. The migration must be forward-only and its `down()` path must not authorize rollback.

Sprint87 itself does not create that file.

## 10. Sprint87 decision

Subject to successful exact-head Sprint87 CI:

- `FINAL_SHIFT_CLOSE_SCHEMA_READINESS = PASS_TO_MIGRATION_SOURCE_ENVELOPE`
- `FINAL_SHIFT_CLOSE_EVIDENCE_TABLE = oneqay_pos_shift_close_evidence`
- `ACTIVE_SLOT_RELEASE_CONTRACT = SELECTED_ATOMIC_WITH_EVIDENCE_INSERT`
- `NONZERO_VARIANCE_REQUIRES_ACCEPTED_REVIEW = TRUE`
- `FINAL_SHIFT_CLOSE_AUTHORITY = NOT_SELECTED`
- `FINAL_SHIFT_CLOSE_PERMISSION = NOT_DEFINED`
- `SHIFT_CLOSE_EXECUTION = NOT_IMPLEMENTED`
- `MIGRATION_27_SOURCE = NOT_CREATED`
- `MIGRATION_EXECUTION_AUTHORITY = NOT_GRANTED`
- `TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY = NOT_GRANTED`
- `DEPLOYMENT_EXECUTION_AUTHORITY = NOT_GRANTED`
- `PRODUCTION_AUTHORITY = NOT_GRANTED`

`PASS_TO_MIGRATION_SOURCE_ENVELOPE` means a later bounded sprint may propose the selected migration #27 source for qualification. It does not authorize migration execution, Final Shift Close application source, permission grants, deployment, or activation.

## 11. Current NO-GO boundaries

Until separately selected and qualified:

- Final durable Shift Close authority remains **NOT SELECTED**;
- Final Shift Close execution remains **NOT IMPLEMENTED**;
- migration #27 source remains **NOT CREATED**;
- migration execution remains **NOT AUTHORIZED**;
- Technical Preview remains **NOT ACTIVATED / NO-GO**;
- Production remains **NO-GO**.
