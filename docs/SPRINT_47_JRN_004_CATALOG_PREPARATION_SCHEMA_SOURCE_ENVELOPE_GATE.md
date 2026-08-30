# Sprint47 JRN-004 Catalog Preparation Schema / Source Envelope Gate

> Status: SELECTED / SCHEMA-SOURCE ENVELOPE GATE
>
> Product: oneQay — The Future of Intelligent Business Management
>
> Repository: `labzefry/oneQay`
>
> Product Owner: Lab | zefry
>
> Attribution: Lab | zefry

## Canonical predecessor

This gate begins only after canonical publication of the Sprint47 JRN-004 entry gate and its exact compatibility predecessor.

Canonical predecessor:

- main: `0b941aa2f9493c0c20ca08202670f83c449f554e`;
- tree: `c220878899abd6d182e2a47421ccda433879ba97`;
- Sprint47 JRN-004 entry gate: PR #425 / `235e8a2e3f644ab319f3615aa64f5cc069e397d8`;
- schema-gate compatibility predecessor: PR #427 / `0b941aa2f9493c0c20ca08202670f83c449f554e`;
- Sprint46 JRN-006 durable sale source remains canonical from PR #419 / `437e463d4e862b1a1ba26cd500ea7ec23e352878`.

Closed, superseded, stale, or unmerged probes are not canonical authority.

## Targeted live evidence

Canonical migration #16 already provides current tenant+outlet catalog state through
`oneqay_pos_sale_catalog_items` with:

- `tenant_id`;
- `outlet_id`;
- `product_id`;
- `display_name`;
- `unit_price_atomic`;
- `currency`;
- `currency_scale`;
- server-owned `available_quantity`;
- `active` as the current bounded sellability state.

Canonical JRN-006 reads price and sellability from that server-owned row and decrements only server-owned stock quantity during sale completion.

However, the current representation does not provide durable JRN-004 mutation evidence for:

- deterministic mutation / operation identity;
- semantic payload fingerprinting;
- exact replay vs conflicting replay;
- actor identity;
- organization / outlet / device context;
- immutable before-state evidence;
- immutable after-state evidence;
- correlation identity;
- event time.

The existing `oneqay_pos_sale_events` table is sale-specific and requires a
`sale_id`; reusing it for catalog administration would conflate sale evidence
with catalog mutation evidence and is not authorized.

Therefore **NO_SCHEMA_CHANGE is rejected** for this bounded concern.

## Schema decision

Sprint47 selects:

**MIGRATION #17 SELECTED IN SOURCE DESIGN ONLY**

Migration #17 is not executed, applied, activated, or published to Technical Preview or Production by this gate.

The only schema addition authorized for the future bounded source implementation is one forward-only durable journal table:

`oneqay_pos_catalog_preparation_journal`

No mutation of migration #16 is authorized.

### Minimal journal contract

The future migration #17 may create exactly the bounded catalog preparation journal needed for idempotency and audit evidence.

Required business columns:

- `tenant_id` — string, 64;
- `mutation_id` — deterministic bounded identifier, 32;
- `operation_id` — caller business mutation idempotency key, 128;
- `payload_fingerprint` — SHA-256 semantic fingerprint, 64;
- `actor_identity_id` — verified current actor, 96;
- `organization_id` — verified current organization, 64;
- `outlet_id` — verified current outlet, 64;
- `device_id` — verified current device, 64;
- `product_id` — bounded catalog product reference, 64;
- `before_exists` — whether a current row existed before mutation;
- nullable before-state fields for:
  - display name;
  - unit price atomic units;
  - currency;
  - currency scale;
  - sellability;
- after-state fields for:
  - display name;
  - unit price atomic units;
  - currency;
  - currency scale;
  - sellability;
- `correlation_id` — server correlation identity, 128;
- `occurred_at_unix` — server mutation time.

Required keys / constraints:

- primary key: `tenant_id + mutation_id`;
- unique idempotency key: `tenant_id + operation_id`;
- bounded query index: `tenant_id + outlet_id + product_id + occurred_at_unix`;
- tenant-bound foreign keys for actor identity, organization, outlet, and device where the canonical schema supports them;
- forward-only `down()` behavior must remain fail-closed and must not authorize rollback.

The journal must not store credentials, session handles, payment secrets, or unrelated personal data.

## Current catalog row contract

The future source implementation must continue using canonical
`oneqay_pos_sale_catalog_items` as the current bounded catalog state.

Allowed JRN-004 mutation fields are exactly:

- `display_name`;
- `unit_price_atomic`;
- `currency`;
- `currency_scale`;
- `active` as bounded sellability intent.

`available_quantity` is **not** a JRN-004 caller input.

For creation of a previously absent tenant+outlet+product catalog row:

- source must write `available_quantity = 0` as a server-owned deterministic initial value;
- the caller must not supply or override that value;
- sellability and stock availability remain distinct;
- a sellable item with zero stock remains unavailable to JRN-006 sale completion until a separately authorized inventory concern changes stock.

For mutation of an existing row:

- existing `available_quantity` must be preserved exactly;
- JRN-004 must never increment, decrement, replace, or otherwise administer stock quantity.

## Bounded command contract

The future source command may accept only:

- `operation_id`;
- `product_id`;
- `display_name`;
- `unit_price_atomic`;
- `currency`;
- `currency_scale`;
- `sellable`.

The caller must not provide:

- tenant id;
- organization id;
- outlet id;
- actor identity;
- device id;
- role;
- permission;
- session identity or handle;
- stock / `available_quantity`;
- mutation id;
- correlation id;
- event time.

Tenant, identity, organization, outlet, device, current session authority, and
correlation identity must be reconstructed from canonical server-side evidence.

Unknown, malformed, stale, unauthorized, cross-tenant, or cross-outlet context must fail closed.

## Permission boundary

The future source must introduce exactly one new bounded permission:

`pos.catalog.prepare`

It must be evaluated through the canonical durable scoped authorization policy.

No default permission grant is authorized.

The existing `pos.sale.complete` permission must not imply catalog preparation authority.

## Transaction and replay semantics

The future application service must execute under the canonical
`PersistenceTransaction`.

The exact operation flow must be bounded as follows:

1. derive verified POS execution context from current server-side organizational/session evidence;
2. require `pos.catalog.prepare`;
3. validate the closed command payload;
4. obtain server time from a bounded catalog preparation clock;
5. look up `tenant_id + operation_id` in the journal under transaction/locking semantics;
6. when an existing journal row has the same semantic fingerprint:
   - return the originally applied after-state;
   - do not rewrite current catalog state;
   - do not create a second journal record;
7. when an existing journal row has a different semantic fingerprint:
   - fail closed;
   - create no business mutation;
8. for a first operation:
   - lock the exact tenant+outlet+product current catalog row when present;
   - snapshot before-state;
   - update only the allowed current-state fields, or create the row with server-owned `available_quantity = 0`;
   - preserve existing stock quantity exactly on update;
   - insert one immutable journal row;
   - return the applied after-state.

The semantic fingerprint must bind the verified execution context plus the exact
business mutation fields. A later catalog state change must not cause replay of
an earlier operation to restore stale state.

## Historical sale integrity

JRN-004 catalog mutation must not rewrite completed JRN-006 sales.

Canonical sale lines retain their immutable:

- product reference;
- quantity;
- unit-price snapshot;
- line total;
- currency;
- currency scale.

No historical sale recalculation or retrospective price mutation is authorized.

## Delivery boundary

The future HTTP boundary remains:

- Local/Test/CI only;
- source-default disabled;
- first-party session control required;
- `RequirePosSessionContextMiddleware` reused for exact verified context;
- deny-by-default throttled mutation route;
- no Technical Preview route activation;
- no Production activation.

The future route may expose one bounded catalog preparation mutation operation only.

## Feature flag

The future source may add:

`ONEQAY_POS_CATALOG_PREPARATION_ENABLED`

Canonical config key:

`oneqay.pos_catalog_preparation.enabled`

It must default to `false`.

No environment setting may activate Technical Preview or Production through this gate.

## Explicit non-scope

This gate does not authorize:

- arbitrary stock quantity input;
- stock receiving;
- stock movement;
- stock transfer;
- stock count;
- stock adjustment;
- reservation;
- negative-stock policy;
- warehouse management;
- scheduled/future pricing;
- price books;
- promotions;
- discounts;
- customer-specific pricing;
- tax-inclusive/exclusive policy;
- fiscal pricing rules;
- cross-outlet inheritance;
- external API publication;
- broad catalog CRUD;
- item deletion;
- migration execution;
- Technical Preview activation;
- Production activation;
- deployment;
- release;
- updater activation or wiring;
- rollback.

## Frozen future source envelope

The next bounded source implementation is frozen to exactly these 15 paths:

1. `.github/workflows/sprint47-jrn004-catalog-preparation-regression.yml`
2. `apps/web/app/Application/Authorization/PosPermission.php`
3. `apps/web/app/Application/Pos/CatalogPreparationClock.php`
4. `apps/web/app/Application/Pos/CatalogPreparationCommand.php`
5. `apps/web/app/Application/Pos/CatalogPreparationRepository.php`
6. `apps/web/app/Application/Pos/CatalogPreparationResult.php`
7. `apps/web/app/Application/Pos/PrepareCatalogItem.php`
8. `apps/web/app/Delivery/Http/Pos/PosCatalogPreparationController.php`
9. `apps/web/app/Infrastructure/Pos/LaravelCatalogPreparationRepository.php`
10. `apps/web/app/Providers/AppServiceProvider.php`
11. `apps/web/config/oneqay.php`
12. `apps/web/database/migrations/0000_00_00_000017_create_pos_catalog_preparation_journal.php`
13. `apps/web/routes/web.php`
14. `apps/web/tests/pos-catalog-preparation-durable.php`
15. `docs/JRN_004_POS_CATALOG_PRICE_AVAILABILITY_PREPARATION_FOUNDATION.md`

Sorted newline-terminated path SHA-256:

`c1ac6d5130c9d78ff99ed0accf5d11e3f08debc575c0d5152ef5b24e6f82ce02`

No source path outside this envelope is authorized by this gate.

## Gate path

This gate changes exactly one path:

`docs/SPRINT_47_JRN_004_CATALOG_PREPARATION_SCHEMA_SOURCE_ENVELOPE_GATE.md`

Sorted newline-terminated path SHA-256:

`f2b8728a2dfb5f36aad996777170ca65e23ff28be81ec235a95d338287779d5f`

Unknown changed-file shapes remain fail closed.

## Lifecycle locks

The following remain unchanged except for migration #17 selection in source design:

- Technical Preview: **NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED**;
- migration #15: **NOT APPLIED / NOT ACTIVATED IN TECHNICAL PREVIEW**;
- migration #16: **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**;
- migration #17: **SELECTED IN SOURCE DESIGN / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**;
- Production: **NO-GO / NOT AUTHORIZED**;
- updater: **DISABLED / UNWIRED**;
- deployment: **NOT AUTHORIZED**;
- release: **NOT AUTHORIZED**;
- migration execution: **NOT AUTHORIZED**;
- rollback: **NOT AUTHORIZED**.

## Completion criterion

This schema/source-envelope gate is complete only after publication through the
normal exact-head qualification, Product Owner authorization, repository-native
evaluator, final race check, squash merge, and canonical post-merge verification.

After canonical publication, only the frozen 15-path JRN-004 source
implementation may proceed under the existing bounded authority.

Attribution: **Lab | zefry**
