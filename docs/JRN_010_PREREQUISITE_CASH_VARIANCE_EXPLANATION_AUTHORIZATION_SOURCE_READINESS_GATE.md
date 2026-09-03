# JRN-010 Prerequisite — Cash Variance Explanation Authorization Source Readiness Gate

Author by Lab | zefry

## Status

`SPRINT72 SOURCE READINESS GATE ONLY / AUTHORIZATION INTEGRATION SOURCE-READY / EXACT AUTHOR PERMISSION PRESERVED / NO DEFAULT GRANT / NO RUNTIME DELIVERY / NO REVIEWER POLICY / NO CLOSE AUTHORITY / MIGRATION #25 SOURCE-PUBLISHED ONLY / JRN-010 SHIFT CLOSE NOT SELECTED`

Sprint72 determines only whether the canonical Sprint71 cash-variance explanation author-authorization policy is sufficiently specified for a later bounded source-envelope freeze and source implementation.

Sprint72 publishes no application source change, permission source change, workflow source change, test source change, provider/config binding, controller, route, API resource, UI, role/default grant, reviewer policy, privileged step-up, close authority, migration, deployment, release, updater activation, Technical Preview activation, Production activation, migration execution/application, rollback, or destructive database authority.

## Canonical basis

The canonical baseline is:

`15848e42e5ea7913ded63d4cc306b1521eb3ef0a`

Canonical Sprint70 publishes durable append-only non-zero cash-variance explanation evidence.

Canonical Sprint71 selects exactly one author permission:

`pos.shift.cash-variance-explanation.record`

Canonical Sprint71 also selects:

- durable scoped authorization;
- no default grant;
- no implicit administrator bypass;
- exact verified organizational-context containment;
- authorization before transaction/persistence;
- author permission is not reviewer authority;
- author permission is not approval;
- author permission is not close authority;
- no runtime delivery.

Sprint72 does not reopen those decisions.

## Readiness conclusion

The cash-variance explanation author-authorization integration is **SOURCE-READY** for a later exact source-envelope freeze.

The source behavior is sufficiently determined because the repository already has:

- canonical `PermissionIdentifier` validation;
- canonical `PosPermission` representation;
- canonical `DurableScopedAuthorizationPolicy`;
- canonical `DurableRolePermissionRepository`;
- verified organizational context;
- canonical POS execution-context derivation;
- canonical `RecordCashVarianceExplanation` application service;
- dedicated durable cash-variance explanation regression;
- historical POS authorization patterns in existing services.

No new authorization architecture is required.

## Selected source behavior

A later authorization source implementation must add exactly one new POS permission capability to the canonical permission family:

`pos.shift.cash-variance-explanation.record`

The application service must require that permission through:

`DurableScopedAuthorizationPolicy`

The authorization check must consume the same verified organizational context that is already used to derive the POS execution context.

No caller-supplied permission, role, tenant, organization, outlet, or actor identity is permitted.

## Required application-service ordering

A later source implementation must preserve this fail-closed ordering:

1. validate correlation identity;
2. validate canonical non-zero variance shape;
3. obtain current verified organizational context;
4. derive canonical `PosExecutionContext`;
5. prove exact tenant/organization/outlet agreement between execution context and variance evidence;
6. require `pos.shift.cash-variance-explanation.record` through `DurableScopedAuthorizationPolicy`;
7. obtain authoritative positive recorded time;
8. enter `PersistenceTransaction`;
9. record or exact-replay durable explanation evidence.

No persistence transaction may begin before authorization succeeds.

No explanation repository call may occur before authorization succeeds.

No clock result may be accepted as authorization evidence.

## Existing context mismatch behavior remains authoritative

Sprint70 already rejects a canonical variance whose tenant, organization, or outlet does not exactly match the current verified execution context.

Sprint72 preserves that behavior.

The later authorization integration must not move or remove the exact context-agreement proof in a way that permits:

- cross-tenant explanation;
- cross-organization explanation;
- cross-outlet explanation;
- actor substitution;
- permission evaluation against a different context than persistence.

Authorization is an additional gate, not a replacement for exact evidence binding.

## Permission source readiness

The canonical `PosPermission` class is the selected permission source location.

A future implementation is expected to add:

- one constant for `pos.shift.cash-variance-explanation.record`;
- one accessor returning a canonical `PermissionIdentifier`.

No other permission identifier is selected.

No reviewer, approval, rejection, waiver, write-off, close, settlement, accounting, or platform permission is source-ready under Sprint72.

## Service dependency readiness

The canonical `RecordCashVarianceExplanation` application service is the selected authorization integration point.

A future implementation is expected to add one dependency:

`DurableScopedAuthorizationPolicy`

No separate explanation-specific authorization service is justified at this stage.

No direct dependency on:

- `LaravelDurableRolePermissionRepository`;
- database connection;
- role identifiers;
- route middleware;
- session controller;
- provider/config state;

is selected for the application service.

The service must depend on the authorization abstraction already used by other canonical POS services.

## No repository widening

The canonical `CashVarianceExplanationRepository` contract does not require authorization parameters.

Sprint72 explicitly rejects widening the explanation repository port with:

- permission identifiers;
- roles;
- grant state;
- authorization booleans;
- reviewer state;
- approval state;
- session state.

Authorization belongs in the application-service boundary before persistence.

The infrastructure adapter remains concerned with durable explanation evidence only.

## Regression readiness

The future authorization source requires executable proof of both allowed and denied behavior.

At minimum, later regression evidence must prove:

- exact permission identifier;
- authorized `OVER` explanation succeeds;
- authorized `SHORT` explanation succeeds;
- absent permission denies;
- wrong permission denies;
- grant in another tenant does not authorize;
- grant in another organization does not authorize;
- grant in another outlet does not authorize;
- role/display label without durable permission does not authorize;
- authorization denial occurs before transaction;
- authorization denial occurs before explanation repository persistence;
- authorization denial creates no explanation evidence;
- exact replay still requires authorization;
- permission cannot authorize `MATCH`;
- permission cannot authorize malformed variance;
- existing actor attribution remains from verified context;
- existing exact tenant/org/outlet evidence binding remains intact;
- existing durable explanation behavior remains append-only;
- no reviewer/approval/close semantics appear.

## Durable test preservation

The existing Sprint70 durable explanation regression must remain executable after the authorization dependency is added.

Because `RecordCashVarianceExplanation` construction will change, the existing durable regression may require bounded adaptation so its successful paths explicitly provide authorized policy evidence.

Such adaptation must not:

- weaken existing durable assertions;
- bypass `DurableScopedAuthorizationPolicy`;
- treat all actors as implicitly authorized;
- remove Production runtime denial;
- remove persistence-disabled denial;
- remove feature-disabled denial;
- remove exact replay assertions;
- remove cross-tenant assertions;
- remove append-only assertions;
- remove migration #25 rollback denial.

The durable regression must remain a preservation regression, not become a fake-green fixture.

## Dedicated authorization regression

A dedicated authorization-focused regression is justified.

It should isolate authorization ordering and permission semantics from the broader durable explanation persistence regression.

The exact test path is not frozen by Sprint72.

Sprint73 should freeze the final path after verifying there is no repository collision and after selecting the smallest exact source envelope.

## Workflow readiness

The canonical Sprint70 source workflow currently contains a source-foundation lock that rejects `DurableScopedAuthorizationPolicy|PosPermission` in the explanation service.

That lock was correct for Sprint70 because authorization source was deliberately absent.

It becomes a historical/source-foundation oracle once Sprint71 authorization semantics are canonical.

Sprint72 therefore concludes that a later authorization source publication must explicitly handle this workflow horizon.

Sprint73 must choose one exact strategy:

- evolve the canonical Sprint70 workflow as an exact successor-aware authorization source gate; or
- publish a separate bounded successor workflow while using a workflow-only compatibility predecessor for the stale Sprint70 oracle.

The chosen strategy must preserve executable Sprint70 durable regression coverage and must not suppress historical business regressions.

Sprint72 does not freeze the strategy or workflow path.

## Candidate source surface

The source-ready candidate surface is bounded to these responsibilities:

1. canonical POS permission source;
2. cash-variance explanation application service;
3. existing durable explanation regression preservation;
4. dedicated explanation authorization regression;
5. exact successor workflow coverage;
6. authorization source-foundation documentation.

This is a responsibility set, not yet a frozen path envelope.

Sprint72 intentionally does not publish a path-count fingerprint.

## Paths explicitly excluded from the future authorization source envelope

Sprint73 must continue to exclude:

- `apps/web/app/Providers/AppServiceProvider.php`;
- `apps/web/config/oneqay.php`;
- `apps/web/routes/web.php`;
- delivery controllers;
- request/form objects;
- API resources;
- UI/resources;
- role/default-grant seeders;
- reviewer/approval source;
- MFA/step-up source;
- shift-close source;
- migration #26;
- deployment/release/updater source.

If one of these paths appears necessary, the scope must be reclassified as a later runtime or adjudication gate rather than silently widened into authorization source.

## No default grant source

Sprint72 does not make role/default-grant source ready.

The later source implementation must add the permission identifier without granting it automatically.

The existence of the permission constant/accessor must not create:

- role assignment;
- permission row;
- bootstrap grant;
- administrator grant;
- tenant-owner grant;
- supervisor grant;
- cashier grant;
- system grant.

Grant policy remains separately bounded.

## Authorization failure semantics

The application layer may continue using the canonical `DurableAuthorizationViolation` failure type.

Sprint72 does not select a delivery error code because no runtime controller is selected.

A later controller/runtime gate must separately map authorization failures to a safe delivery envelope.

No delivery-level HTTP status or route contract is source-ready in Sprint72.

## Replay remains authorization-bound

Exact idempotent replay remains subject to the author permission.

A later source implementation must not:

- bypass authorization when operation identity already exists;
- return durable evidence to an unauthorized actor merely because the operation identifier is known;
- use replay fingerprint equality as permission evidence.

Replay integrity and authorization remain separate controls.

## No privileged step-up requirement selected

Sprint72 does not select privileged MFA or step-up for explanation authoring.

This does not mean privileged step-up is forbidden forever.

It means no evidence currently justifies combining explanation authoring with privileged step-up in the same source change.

Any later step-up requirement must be separately selected and must not be inferred from the existence of a non-zero variance.

## Reviewer and approval separation

The future authorization source must not add methods or fields representing:

- review;
- approval;
- rejection;
- waiver;
- write-off;
- reconciliation decision;
- close eligibility;
- close authority.

The author permission is intentionally narrower than adjudication.

## No lifecycle mutation

Authorization integration must not mutate:

- shift state;
- opening cash evidence;
- closing cash evidence;
- expected cash;
- signed variance;
- variance direction;
- sale/refund state;
- migration state;
- deployment state.

The explanation remains evidence only.

## Historical compatibility rule

If Sprint73/Sprint74 qualification exposes stale workflow/oracle incompatibility rather than an authorization business-source defect:

1. classify the failure before altering source;
2. do not weaken permission semantics;
3. do not add a default grant;
4. do not bypass `DurableScopedAuthorizationPolicy`;
5. freeze exact legitimate source blobs;
6. publish the smallest workflow-only compatibility predecessor;
7. preserve executable historical regressions;
8. fresh-qualify and merge the predecessor;
9. replay the frozen source byte-identically from new canonical main;
10. fresh-qualify again.

No fake-green CI is permitted.

## Source-readiness denial conditions

Authorization source is not ready to expand beyond the candidate surface when any proposal requires:

- new schema;
- migration #26;
- provider binding;
- feature config;
- environment variable;
- route/controller;
- UI;
- default grant;
- reviewer policy;
- privileged step-up;
- close authority;
- Production runtime;
- migration execution.

Such work belongs to a separately bounded later Sprint.

## Next bounded Sprint

After Sprint72 is canonical, Sprint73 should define the **exact cash-variance explanation authorization source envelope**.

Sprint73 must:

- select exact paths;
- freeze exact path count;
- freeze sorted newline-terminated path SHA-256;
- preserve `pos.shift.cash-variance-explanation.record`;
- preserve authorization-before-transaction ordering;
- preserve existing durable explanation regression;
- select exact successor-workflow handling;
- reject provider/config/route/default-grant/reviewer/close widening;
- preserve migration and lifecycle locks.

Sprint73 must remain a gate only.

It must not publish authorization source implementation.

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
