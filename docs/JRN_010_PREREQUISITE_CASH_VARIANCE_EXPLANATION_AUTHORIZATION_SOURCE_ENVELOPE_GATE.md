# JRN-010 Prerequisite — Cash Variance Explanation Authorization Source Envelope Gate

Author by Lab | zefry

## Status

`SPRINT73 SOURCE ENVELOPE GATE ONLY / EXACT SIX-PATH AUTHORIZATION SOURCE ENVELOPE FROZEN / NO SOURCE IMPLEMENTATION / NO DEFAULT GRANT / NO RUNTIME DELIVERY / NO REVIEWER POLICY / NO CLOSE AUTHORITY / MIGRATION #25 SOURCE-PUBLISHED ONLY / JRN-010 SHIFT CLOSE NOT SELECTED`

Sprint73 freezes the exact future source envelope for integrating the canonical Sprint71/Sprint72 explanation-author authorization policy into the canonical Sprint70 durable cash-variance explanation source.

Sprint73 publishes no authorization source implementation.

It does not modify application source, workflow source, tests, permissions, provider/config, routes, controllers, UI, role/default grants, reviewer/approval policy, privileged step-up, close authority, schema, migration, deployment, release, updater, Technical Preview, or Production runtime.

## Canonical basis

The canonical baseline is:

`33f1a78062bc428dc905841009261dc34de37319`

Canonical Sprint71 selects:

`pos.shift.cash-variance-explanation.record`

Canonical Sprint72 concludes authorization integration is source-ready and selects:

- canonical `PosPermission` as the permission representation;
- canonical `DurableScopedAuthorizationPolicy`;
- `RecordCashVarianceExplanation` as the integration point;
- exact context agreement before authorization;
- authorization before clock/transaction/persistence;
- durable regression preservation;
- dedicated authorization regression;
- explicit successor handling for the Sprint70 source workflow;
- no default grant;
- no runtime delivery.

Sprint73 does not reopen those decisions.

## Frozen future source envelope

The next authorization source publication is restricted to exactly six paths:

1. `.github/workflows/sprint69-jrn010-prerequisite-cash-variance-explanation-source-regression.yml`
2. `apps/web/app/Application/Authorization/PosPermission.php`
3. `apps/web/app/Application/Pos/RecordCashVarianceExplanation.php`
4. `apps/web/tests/pos-cash-variance-explanation-authorization.php`
5. `apps/web/tests/pos-cash-variance-explanation-durable.php`
6. `docs/JRN_010_PREREQUISITE_CASH_VARIANCE_EXPLANATION_AUTHORIZATION_SOURCE_FOUNDATION.md`

Sorted newline-terminated path SHA-256:

`cc40f08beda780e4aa4795f29aa7533bbde1029d11855d43f180812502c4d80b`

Any unknown path, path-count change, rename, or fingerprint mismatch fails closed.

## Why the existing Sprint70 workflow is inside the envelope

The canonical Sprint70 workflow currently enforces the correct source-foundation rule that authorization source must be absent.

That rule becomes a legitimate successor boundary after Sprint71/Sprint72 become canonical.

Therefore the future source publication must evolve the same workflow in a bounded, successor-aware way rather than:

- silently bypassing it;
- disabling it;
- deleting its durable regressions;
- weakening its migration locks;
- allowing arbitrary source shapes.

The evolved workflow must continue to recognize and preserve the canonical Sprint70 source-foundation horizon while exact-qualifying only the frozen six-path authorization successor shape.

## Permission source path

`apps/web/app/Application/Authorization/PosPermission.php` is included only to add the canonical explanation-author permission representation.

The future source may add:

- one constant with exact value `pos.shift.cash-variance-explanation.record`;
- one accessor returning canonical `PermissionIdentifier`.

No other permission is authorized by the envelope.

No default grant is authorized.

No reviewer, approval, waiver, close, settlement, accounting, platform, or privileged permission is authorized.

## Application-service path

`apps/web/app/Application/Pos/RecordCashVarianceExplanation.php` is included only to integrate the canonical durable scoped authorization dependency and require call.

The future source must preserve this ordering:

1. correlation validation;
2. canonical non-zero variance validation;
3. verified organizational context acquisition;
4. canonical POS execution-context derivation;
5. exact tenant/organization/outlet agreement;
6. require `pos.shift.cash-variance-explanation.record`;
7. authoritative positive clock;
8. persistence transaction;
9. durable record or exact replay.

The source must not move authorization after transaction entry or repository invocation.

## Dedicated authorization regression path

`apps/web/tests/pos-cash-variance-explanation-authorization.php` is selected as the dedicated authorization regression.

It must prove at minimum:

- exact permission identifier;
- authorized `OVER` succeeds;
- authorized `SHORT` succeeds;
- absent permission denies;
- wrong permission denies;
- cross-tenant grant denies;
- cross-organization grant denies;
- cross-outlet grant denies;
- role/display label without durable permission denies;
- authorization denial occurs before clock/transaction/repository persistence;
- denial creates no explanation evidence;
- exact replay requires authorization;
- permission cannot authorize `MATCH`;
- permission cannot authorize malformed variance;
- canonical actor attribution remains from verified context;
- no reviewer/approval/close semantics are introduced.

## Durable regression preservation path

`apps/web/tests/pos-cash-variance-explanation-durable.php` remains inside the envelope because constructor-level authorization integration requires its successful durable cases to provide explicit authorization evidence.

The future source must preserve all Sprint70 durable assertions.

It must not replace durable authorization with an always-allow bypass.

It must not remove:

- deterministic replay;
- conflicting replay denial;
- one authoritative explanation rule;
- cross-tenant isolation;
- context mismatch denial;
- Production runtime denial;
- persistence-disabled denial;
- source-feature-disabled denial;
- append-only behavior;
- opening/closing evidence immutability;
- no sale/refund mutation;
- migration #25 rollback denial;
- absence of runtime repository binding.

## Authorization source-foundation document path

`docs/JRN_010_PREREQUISITE_CASH_VARIANCE_EXPLANATION_AUTHORIZATION_SOURCE_FOUNDATION.md` must document final source behavior after implementation.

It must prove that:

- exact permission is `pos.shift.cash-variance-explanation.record`;
- authorization uses `DurableScopedAuthorizationPolicy`;
- exact context agreement precedes permission evaluation;
- authorization precedes clock/transaction/persistence;
- replay remains authorization-bound;
- no default grant exists;
- no runtime binding exists;
- no route/controller/UI exists;
- explanation is not approval;
- explanation is not close authority;
- migration #25 remains source-published only;
- migration #26 remains not selected.

## Explicitly excluded paths

The six-path envelope excludes:

- `apps/web/app/Providers/AppServiceProvider.php`;
- `apps/web/config/oneqay.php`;
- `apps/web/routes/web.php`;
- delivery controllers;
- middleware;
- request/form classes;
- response resources;
- UI/resources;
- role/default-grant seeders;
- authorization repository implementation changes;
- schema/migrations;
- reviewer/approval source;
- privileged MFA/step-up source;
- shift-close source;
- deployment/release/updater source.

These exclusions are part of the gate.

## No infrastructure authorization changes

The canonical `DurableRolePermissionRepository`, `LaravelDurableRolePermissionRepository`, and `DurableScopedAuthorizationPolicy` are already sufficient.

They are deliberately excluded from the future source envelope.

The source implementation must consume those canonical abstractions without changing their behavior.

## No explanation repository change

The canonical `CashVarianceExplanationRepository` and `LaravelCashVarianceExplanationRepository` are deliberately excluded.

Authorization must remain an application-service gate.

The future source must not push authorization booleans, roles, permission identifiers, or session state into the explanation repository contract.

## No config/provider binding

The envelope does not publish runtime wiring.

The future source implementation must not add:

- config keys;
- environment variables;
- DI provider binding for `CashVarianceExplanationRepository`;
- explanation service/controller binding;
- route registration.

The source remains non-delivered and non-Production.

## No default grant

Adding the permission identifier does not authorize any identity.

No role assignment or permission row may be created by the source publication.

An absent durable grant remains denied.

## No reviewer or close semantics

The six-path envelope must not add:

- reviewer permission;
- supervisor permission;
- approval/rejection state;
- waiver/write-off;
- maker-checker;
- privileged step-up;
- final close permission;
- shift state mutation;
- reconciliation decision.

Explanation authoring remains separate from adjudication.

## Workflow successor requirements

The evolved workflow must:

- recognize exactly the six-path fingerprint;
- preserve exact migration set through #25;
- reject migration #26;
- preserve Sprint70 durable explanation regression;
- run the dedicated authorization regression;
- validate exact permission source lock;
- validate authorization-before-transaction source ordering;
- reject provider/config/routes/default-grant widening;
- reject reviewer/approval/close terms in newly authorized source surface;
- preserve historical cash-variance, expected-cash, sale-to-shift, opening-cash, and closing-cash regressions;
- preserve lifecycle locks.

No executable regression may be skipped solely to manufacture green status.

## Historical compatibility rule

Fresh qualification may still expose older workflows that do not recognize legitimate changes to `PosPermission`, `RecordCashVarianceExplanation`, or the evolved Sprint70 workflow.

If a failure is a historical workflow/oracle incompatibility rather than a business-source defect:

1. do not merge the source PR;
2. freeze all six source blob identities;
3. classify the exact stale workflow horizon;
4. publish the smallest workflow-only compatibility predecessor;
5. preserve executable historical regressions;
6. fresh-qualify and merge the predecessor;
7. replay all six frozen source blobs byte-identically from new canonical main;
8. fresh-qualify again.

Do not weaken authorization semantics or add default grants to satisfy stale CI.

## Source implementation non-scope

The next source Sprint must not expand into:

- provider/config runtime wiring;
- permission default grants;
- role mapping;
- route/controller/API/UI;
- reviewer/approval/rejection/waiver;
- privileged MFA/step-up;
- close authority;
- final close concurrency/idempotency;
- final evidence freshness;
- final shift-state transition;
- explanation amendment/supersession;
- migration #26;
- migration execution;
- Technical Preview activation;
- Production activation;
- deployment/release;
- updater activation;
- rollback/destructive database work.

## Next bounded Sprint

After Sprint73 is canonical, the next bounded Sprint may publish exactly the frozen six-path authorization source foundation.

That source publication must:

- preserve the exact six-path fingerprint;
- remain byte-bounded to those paths;
- implement only the selected author permission integration;
- preserve canonical durable explanation semantics;
- keep runtime delivery absent;
- fresh-qualify current and historical material regression horizons;
- use workflow-only compatibility correction if stale historical oracles reject the legitimate source shape.

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
