# JRN-010 Prerequisite — Cash Variance Explanation Authorization Source Foundation

Author by Lab | zefry

## Status

`SPRINT74 SOURCE-PUBLISHED AUTHORIZATION FOUNDATION / EXACT AUTHOR PERMISSION ENFORCED BEFORE PERSISTENCE / NO DEFAULT GRANT / NO RUNTIME DELIVERY / NO REVIEWER POLICY / NO CLOSE AUTHORITY / MIGRATION #25 SOURCE-PUBLISHED ONLY / JRN-010 SHIFT CLOSE NOT SELECTED`

Sprint74 publishes exactly the six-path authorization source envelope frozen by canonical Sprint73.

It integrates the canonical Sprint71 author permission into the canonical Sprint70 durable cash-variance explanation application service without publishing runtime delivery.

## Exact source envelope

The source publication is restricted to exactly six paths:

1. `.github/workflows/sprint69-jrn010-prerequisite-cash-variance-explanation-source-regression.yml`
2. `apps/web/app/Application/Authorization/PosPermission.php`
3. `apps/web/app/Application/Pos/RecordCashVarianceExplanation.php`
4. `apps/web/tests/pos-cash-variance-explanation-authorization.php`
5. `apps/web/tests/pos-cash-variance-explanation-durable.php`
6. `docs/JRN_010_PREREQUISITE_CASH_VARIANCE_EXPLANATION_AUTHORIZATION_SOURCE_FOUNDATION.md`

Sorted newline-terminated path SHA-256:

`cc40f08beda780e4aa4795f29aa7533bbde1029d11855d43f180812502c4d80b`

Unknown paths, path-count changes, renames, migration changes, provider/config/route widening, or fingerprint mismatch fail closed.

## Exact permission

Sprint74 publishes exactly one new POS permission identifier:

`pos.shift.cash-variance-explanation.record`

The permission is represented by canonical `PosPermission` through:

- `RECORD_CASH_VARIANCE_EXPLANATION`;
- `recordCashVarianceExplanation()`.

No other permission is added.

No role/default grant is published.

## Application authorization

`RecordCashVarianceExplanation` now depends on:

`DurableScopedAuthorizationPolicy`

The service requires:

`PosPermission::recordCashVarianceExplanation()`

The canonical durable scoped authorization policy remains the only authorization abstraction used by the service.

The service does not depend directly on:

- `DurableRolePermissionRepository`;
- `LaravelDurableRolePermissionRepository`;
- roles;
- database connection;
- route middleware;
- session controller;
- config;
- provider state.

## Fail-closed ordering

Sprint74 preserves this exact authorization ordering:

1. validate correlation identity;
2. validate canonical non-zero variance;
3. obtain verified organizational context;
4. derive exact `PosExecutionContext`;
5. prove tenant/organization/outlet agreement with canonical variance evidence;
6. require `pos.shift.cash-variance-explanation.record`;
7. obtain authoritative positive recorded time;
8. enter `PersistenceTransaction`;
9. call the durable explanation repository.

Authorization therefore occurs before clock-dependent persistence execution and before repository access.

An unauthorized request must not enter the persistence transaction.

## Context containment remains independent

Authorization does not replace evidence binding.

The service still rejects any variance whose:

- tenant;
- organization;
- outlet;

does not exactly match the current verified POS execution context.

A permission grant in one context does not authorize another context.

## Replay remains authorization-bound

Exact operation replay is still subject to authorization.

Knowing a prior operation identifier is not permission evidence.

Repository replay fingerprint equality is not permission evidence.

An actor whose permission is removed must not reach the repository merely because a prior identical operation exists.

## No default grant

Sprint74 publishes no:

- role assignment;
- permission row;
- seed;
- bootstrap grant;
- administrator grant;
- supervisor grant;
- cashier grant;
- tenant-owner grant;
- system grant.

The permission constant/accessor is capability identity only.

Absent durable grant remains denied.

## No implicit role bypass

Display labels do not authorize explanation recording.

The source contains no bypass for labels such as:

- administrator;
- super administrator;
- supervisor;
- manager;
- owner;
- cashier;
- control principal;
- platform operator.

Durable scoped permission evidence remains required.

## Authorized domain remains non-zero only

Authorization does not widen the Sprint70 evidence domain.

Explanation remains valid only for canonical:

- `OVER` with positive signed variance;
- `SHORT` with negative signed variance.

`MATCH` and zero variance remain rejected before authorization.

Malformed variance arithmetic remains rejected before authorization.

Automatic tolerance remains exactly `0` atomic units.

## Durable explanation semantics preserved

Sprint74 does not change:

- explanation canonicalization;
- 4096-byte explanation bound;
- stable operation identity;
- deterministic evidence identity;
- payload fingerprint;
- exact replay behavior;
- conflicting replay denial;
- one authoritative explanation per closing evidence;
- actor attribution;
- cutoff binding;
- expected cash snapshot;
- observed cash snapshot;
- signed variance;
- direction;
- currency;
- scale;
- append-only posture;
- restrictive relationships;
- rollback denial.

## Durable preservation fixture

The canonical Sprint70 durable regression is adapted to provide explicit authorization evidence.

The fixture grants only the exact Sprint74 permission to:

- `explainer-alpha` in tenant-alpha / organization-alpha / outlet-alpha;
- `explainer-beta` in tenant-beta / organization-beta / outlet-beta.

It is not an always-allow authorization bypass.

Existing durable assertions remain preserved.

## Dedicated authorization regression

Sprint74 publishes:

`apps/web/tests/pos-cash-variance-explanation-authorization.php`

The regression proves:

- exact permission identifier;
- authorized `OVER`;
- authorized `SHORT`;
- exact ordering `authorization -> clock -> transaction -> repository`;
- absent permission denial;
- wrong-permission denial;
- cross-tenant grant denial;
- cross-organization grant denial;
- cross-outlet grant denial;
- administrator-like label does not bypass permission;
- denial occurs before clock;
- denial occurs before transaction;
- denial occurs before repository;
- replay requires authorization again;
- `MATCH` rejected before authorization;
- malformed variance rejected before authorization;
- exact context mismatch rejected before authorization;
- canonical actor attribution from verified context.

## Workflow evolution

The existing Sprint70 source workflow path is evolved into the Sprint74 authorization successor gate.

The workflow now:

- enforces the exact six-path fingerprint;
- requires no migration delta;
- proves exact migration set through #25;
- rejects migration #26;
- validates exact permission source;
- validates authorization dependency;
- validates source ordering;
- rejects direct infrastructure authorization dependency;
- rejects runtime binding;
- rejects config/route delivery;
- rejects database/default-grant source;
- validates PHP syntax;
- installs locked Composer dependencies;
- rejects High or Critical Composer advisories;
- runs the dedicated Sprint74 authorization regression;
- preserves Sprint70 durable explanation regression;
- preserves Sprint64 cash-variance regression;
- preserves Sprint60 expected-cash regression at migration #24 horizon;
- preserves Sprint57 sale-to-shift binding at migration #24 horizon;
- preserves Sprint54 closing-cash regression at migration #23 horizon;
- preserves Sprint53 opening-cash regression at migration #22 horizon;
- asserts lifecycle locks.

No executable regression is skipped to manufacture green status.

## No runtime repository binding

Sprint74 does not bind `CashVarianceExplanationRepository` in `AppServiceProvider`.

The canonical infrastructure adapter remains source-foundation-only and explicitly constructible for tests.

No application config key or environment variable is added for runtime delivery.

## No route or controller

Sprint74 publishes no:

- controller;
- route;
- request object;
- response resource;
- middleware;
- throttle contract;
- UI;
- queue;
- scheduled job;
- webhook.

The authorization source foundation is not a delivered Product feature.

## No reviewer or adjudication semantics

The author permission means only:

`may request durable recording of explanation evidence`

It does not mean:

- reviewed;
- approved;
- rejected;
- waived;
- written off;
- reconciled;
- remediated;
- close-eligible;
- close-authorized.

No reviewer or supervisor permission is published.

No maker-checker policy is published.

## No privileged step-up

Sprint74 selects no MFA or privileged step-up requirement for explanation authoring.

No privileged authentication source changes are included.

## No lifecycle mutation

Authorization integration does not mutate:

- shift state;
- opening cash evidence;
- closing cash evidence;
- expected cash;
- observed closing cash;
- signed variance;
- direction;
- sales;
- refunds;
- accounting state;
- deployment state.

## Historical compatibility rule

Fresh qualification may expose historical workflow/oracle incompatibility caused by the legitimate six-path authorization source shape.

If such a failure is not a Sprint74 business-source defect:

1. do not merge the source PR;
2. freeze all six Sprint74 source blob identities;
3. classify the exact stale workflow/oracle horizon;
4. publish the smallest workflow-only compatibility predecessor;
5. preserve executable historical regressions;
6. fresh-qualify and merge the compatibility predecessor;
7. replay all six frozen source blobs byte-identically from new canonical main;
8. fresh-qualify again.

Authorization semantics, tests, deny-by-default behavior, or permission requirements must not be weakened to satisfy stale CI.

## Explicit non-scope

Sprint74 does not select or implement:

- default grants;
- role mapping;
- provider/config runtime wiring;
- controller/route/API/UI;
- reviewer/supervisor policy;
- approval/rejection/waiver;
- maker-checker;
- privileged MFA/step-up;
- close authority;
- final close concurrency/idempotency;
- final evidence freshness;
- final shift-state transition;
- explanation amendment/supersession;
- reason-code catalog;
- attachments;
- arbitrary cash movement;
- denomination counting;
- settlement/provider reconciliation;
- accounting/general ledger;
- migration #26;
- migration execution/application;
- Technical Preview activation;
- Production activation;
- deployment/release;
- updater activation;
- rollback/destructive database operations.

## Migration and lifecycle lock

Migration #22: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #23: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #24: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #25: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #26: **NOT SELECTED**

Technical Preview: **INACTIVE**

Production: **NO-GO**

Updater: **INACTIVE**

Deployment/release: **NOT AUTHORIZED**

Migration execution/application: **NOT AUTHORIZED**

Rollback/destructive database operations: **NOT AUTHORIZED**

JRN-010 Shift Close: **NOT SELECTED**

Attribution: **Lab | zefry**
