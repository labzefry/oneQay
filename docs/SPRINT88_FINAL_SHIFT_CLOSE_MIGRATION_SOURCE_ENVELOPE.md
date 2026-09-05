# Sprint88 — Final Shift Close Migration Source Envelope

Author by Lab | zefry

## 1. Purpose

Sprint88 materializes the bounded **source-only** migration selected by Sprint87 for future durable Final Shift Close evidence.

This sprint creates migration #27 source and an isolated SQLite qualification regression. It does **not** execute migration #27 against Preview, Production, shared hosting, or any live database. It does not implement Final Shift Close application/repository source, define or grant a Final Shift Close permission, publish runtime bindings, activate Technical Preview, deploy, publish a release, or activate Production/updater behavior.

## 2. Canonical starting point

Canonical `main` before Sprint88 is:

`d0eae906ad9294a510cf9839470488d212ba8b30`

Sprint87 selected:

- append-only table `oneqay_pos_shift_close_evidence`;
- exactly one immutable final-close evidence row per tenant/shift;
- stable operation identity and semantic payload fingerprint;
- exact opening/closing cash, scope, money, variance, review, correlation, and close-time evidence fields;
- `MATCH` with zero variance and no review reference;
- `OVER` / `SHORT` only with an accepted review reference;
- `REVIEW_REJECTED` as a finalization blocker;
- future atomic evidence insert + `active_slot` release in the same `PersistenceTransaction` boundary.

Sprint88 implements only the schema artifact needed to support that future transaction.

## 3. Exact Sprint88 source envelope

Sprint88 is limited to exactly four paths:

1. `.github/workflows/sprint88-final-shift-close-migration-source-gate.yml`
2. `apps/web/database/migrations/0000_00_00_000027_create_pos_shift_close_evidence_foundation.php`
3. `apps/web/tests/pos-shift-close-migration-foundation.php`
4. `docs/SPRINT88_FINAL_SHIFT_CLOSE_MIGRATION_SOURCE_ENVELOPE.md`

Sorted newline-terminated path SHA-256:

`6f4e1a3efe6e2dc8bcc5e213b88d968ead955d64074f159f52971e16a8a9abf8`

No Application, Infrastructure repository, provider, route, config, permission, Preview UI, or deployment source belongs to this envelope.

## 4. Migration #27 source

Selected path:

`apps/web/database/migrations/0000_00_00_000027_create_pos_shift_close_evidence_foundation.php`

Selected table:

`oneqay_pos_shift_close_evidence`

Columns:

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

## 5. Keys and immutable evidence bindings

Migration #27 provides:

- primary key (`tenant_id`, `evidence_id`);
- unique (`tenant_id`, `operation_id`);
- unique (`tenant_id`, `shift_id`);
- index (`tenant_id`, `outlet_id`, `closed_at_unix`).

Foreign keys are tenant-scoped and restrict-on-delete/restrict-on-update:

- shift → `oneqay_pos_shifts`;
- opening cash → `oneqay_pos_shift_opening_cash_evidence`;
- closing cash → `oneqay_pos_shift_closing_cash_evidence`;
- closer identity → `oneqay_identities`;
- organization → `oneqay_organizations`;
- outlet → `oneqay_outlets`;
- device → `oneqay_devices`;
- nullable review evidence → `oneqay_pos_cash_variance_review_decision_evidence`.

The migration does not create cascades that can erase historical close evidence.

## 6. Database-level variance/review invariant

Migration #27 enforces exactly these branches:

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

`REVIEW_REJECTED` is not a valid final-close outcome.

Canonical MySQL-compatible targets use a governed CHECK constraint. Isolated SQLite CI uses equivalent insert/update triggers so the same invariant is executable during qualification.

This database rule is a last-line integrity guard. A future application repository must still resolve and prove the referenced review row exactly matches the close candidate's tenant, organization, outlet, shift, opening/closing evidence, cutoff, expected/observed cash, variance, direction, currency, and scale.

## 7. Active-slot boundary remains runtime-only

Migration #27 **does not** update `oneqay_pos_shifts.active_slot`.

A future Final Shift Close repository/service must perform, inside one `PersistenceTransaction`:

1. authoritative validation and row locking;
2. insert of exactly one final-close evidence row;
3. update of exactly one canonical shift row from `active_slot = 1` to `active_slot = NULL`.

Any failure must roll back both persistence effects. Schema materialization alone does not close any shift.

## 8. Isolated CI migration qualification

`apps/web/tests/pos-shift-close-migration-foundation.php` creates a process-local temporary SQLite database, applies the canonical migration sequence through #27, and exercises migration #27 constraints.

The regression proves, at minimum:

- exact migration horizon through #27;
- expected table/columns are materialized;
- valid `MATCH` evidence is accepted;
- one final-close evidence row per shift is enforced;
- invalid variance/review transitions fail closed;
- valid `OVER` evidence with accepted review is accepted;
- `SHORT` without accepted review is rejected;
- cross-tenant review evidence is rejected;
- `REVIEW_REJECTED` finalization is rejected;
- `down()` remains forward-only/no-rollback;
- schema qualification does not mutate the canonical shift `active_slot`.

This ephemeral SQLite execution is CI schema qualification only. It is **not** Preview/Production migration execution authority.

## 9. Explicitly absent source

Sprint88 must not add:

- `CloseShift.php`;
- `CloseShiftCommand.php`;
- `CloseShiftRepository.php`;
- `CloseShiftResult.php`;
- `LaravelCloseShiftRepository.php`;
- `pos.shift.close` permission;
- provider binding;
- route/controller/UI;
- deployment/release activation logic.

The reviewer permission remains review-only and does not imply close authority.

## 10. Sprint88 decision target

Subject to successful exact-head Sprint88 qualification:

- `FINAL_SHIFT_CLOSE_MIGRATION_SOURCE = QUALIFIED_SOURCE_ONLY`
- `MIGRATION_27_SOURCE = CREATED`
- `MIGRATION_27_LIVE_EXECUTION = NOT_AUTHORIZED`
- `FINAL_SHIFT_CLOSE_APPLICATION_SOURCE = NOT_IMPLEMENTED`
- `FINAL_SHIFT_CLOSE_AUTHORITY = NOT_SELECTED`
- `FINAL_SHIFT_CLOSE_PERMISSION = NOT_DEFINED`
- `ACTIVE_SLOT_RELEASE_EXECUTION = NOT_IMPLEMENTED`
- `MIGRATION_EXECUTION_AUTHORITY = NOT_GRANTED`
- `TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY = NOT_GRANTED`
- `DEPLOYMENT_EXECUTION_AUTHORITY = NOT_GRANTED`
- `PRODUCTION_AUTHORITY = NOT_GRANTED`
- `UPDATER_ACTIVATION_AUTHORITY = NOT_GRANTED`

A successful Sprint88 permits only a later bounded source-readiness step for the Final Shift Close application/repository contract. It does not authorize runtime close execution or migration execution.

## 11. Current NO-GO boundaries

Until separately selected and qualified:

- migration #27 live execution remains **NOT AUTHORIZED**;
- Final durable Shift Close authority remains **NOT SELECTED**;
- Final Shift Close execution remains **NOT IMPLEMENTED**;
- Technical Preview remains **NOT ACTIVATED / NO-GO**;
- Production remains **NO-GO**;
- updater remains **INACTIVE**.
