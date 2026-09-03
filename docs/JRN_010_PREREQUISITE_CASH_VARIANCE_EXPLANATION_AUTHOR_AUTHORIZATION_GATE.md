# JRN-010 Prerequisite — Cash Variance Explanation Author Authorization Gate

Author by Lab | zefry

## Status

`SPRINT71 AUTHORIZATION POLICY GATE ONLY / DEDICATED TENANT-SCOPED AUTHOR PERMISSION SELECTED / NO DEFAULT GRANT / NO REVIEWER POLICY / NO RUNTIME DELIVERY / NO CLOSE AUTHORITY / MIGRATION #25 SOURCE-PUBLISHED ONLY / JRN-010 SHIFT CLOSE NOT SELECTED`

Sprint71 selects only the minimum authorization policy required before canonical Sprint70 cash-variance explanation evidence may ever be exposed through a runtime invocation surface.

It does not publish application source changes, permission source changes, provider/config wiring, controller, route, API resource, UI, default grants, reviewer/approval workflow, privileged step-up, close authority, final shift transition, deployment, release, updater activation, Technical Preview activation, Production activation, migration execution/application, rollback, or destructive database authority.

## Canonical basis

The canonical baseline is `a373b30e5706b79ca6efbc28d016292b880f61ee`.

Canonical Sprint70 publishes durable, append-only explanation source for exact non-zero `CashVarianceResult` evidence.

Canonical Sprint70 remains deliberately non-runtime:

- `CashVarianceExplanationRepository` is not bound in `AppServiceProvider`;
- no explanation feature config key or environment variable exists;
- no explanation controller or route exists;
- no explanation permission exists;
- no default grant exists;
- Production runtime remains denied by the explicit source-foundation adapter;
- explanation evidence is not reviewer approval;
- explanation evidence is not close authority.

Sprint71 does not reopen the canonical tolerance, durability, schema, or source-foundation decisions.

## Selected author permission

A future runtime invocation that records authoritative cash-variance explanation evidence must require one dedicated scoped permission:

`pos.shift.cash-variance-explanation.record`

The future source representation is expected to remain consistent with the existing POS authorization model through `PosPermission` and `PermissionIdentifier`.

The permission grants only the ability to request recording of one canonical non-zero cash-variance explanation under the current verified organizational context.

It does not grant any other POS or JRN-010 authority.

## Permission identity requirements

The selected permission identifier is:

- lower-case;
- bounded by the existing `PermissionIdentifier` grammar;
- free of tenant/user identity segments;
- not a `platform.*` permission;
- capability-specific;
- separate from shift-opening, opening-cash, closing-cash, sale, refund, inventory, reviewer, approval, and close permissions.

A future source gate must fail closed if a different permission identifier is substituted silently.

## Authorization mechanism

A future authorization source implementation must use the canonical durable scoped authorization path:

- obtain the current verified `VerifiedOrganizationalContext`;
- derive the exact POS execution context from that verified context;
- require the dedicated explanation-record permission through `DurableScopedAuthorizationPolicy`;
- deny before persistence when the current context is absent or unauthorized.

Authorization must occur before authoritative explanation persistence.

The explanation repository must never treat successful construction, route reachability, config enablement, authenticated session presence, role display name, or caller-supplied identity as permission.

## No default grant

Sprint71 selects no default grant for `pos.shift.cash-variance-explanation.record`.

No role, tenant, organization, outlet, identity, bootstrap principal, administrator label, or system actor receives this permission automatically from this gate.

Future role-to-permission grant policy, if needed, must be separately bounded.

An absent durable grant means denied.

## No implicit administrator bypass

A future runtime implementation must not infer explanation-author authority from labels such as:

- administrator;
- super administrator;
- manager;
- supervisor;
- cashier;
- owner;
- control principal;
- platform operator.

Authorization remains evidence-driven through the canonical scoped permission repository.

Sprint71 does not select any bypass role.

## Exact context containment

The author permission must be evaluated against the same verified organizational context that the explanation application service uses to prove tenant, organization, and outlet agreement with the canonical variance.

A permission granted in one tenant, organization, or outlet context must not authorize explanation recording in another context.

A future runtime caller must not be allowed to supply or override:

- tenant identity;
- organization identity;
- outlet identity;
- authoritative actor identity;
- shift identity;
- opening cash evidence identity;
- closing cash evidence identity;
- variance cutoff;
- expected cash amount;
- observed closing cash amount;
- signed variance;
- variance direction;
- currency;
- currency scale.

Those values must remain canonical or derived from trusted context/evidence.

## Author attribution

The authoritative actor identity persisted in explanation evidence must continue to come from the verified organizational context.

The selected permission does not permit caller-supplied actor impersonation.

Display names, role labels, request metadata, headers, free text, or client-provided actor identifiers must not replace the canonical actor identity.

## Non-zero variance restriction remains unchanged

The permission does not widen the Sprint70 explanation domain.

Explanation recording remains valid only for canonical:

- `OVER` with positive signed variance;
- `SHORT` with negative signed variance.

It does not authorize explanation recording for `MATCH` or zero variance.

It cannot manufacture a non-zero tolerance.

Automatic tolerance remains exactly `0` atomic units.

## Permission is not adjudication

The selected author permission means only:

`may request durable recording of explanation evidence`

It does not mean:

- explanation accepted as resolved;
- variance approved;
- variance rejected;
- variance waived;
- variance written off;
- variance remediated;
- shift eligible for final close;
- shift authorized to close;
- reviewer authority granted;
- supervisor authority granted;
- privileged step-up satisfied;
- settlement/accounting treatment selected.

Any later reviewer or close policy must remain separate.

## Reviewer separation

Sprint71 selects no reviewer permission.

It selects no:

- reviewer role;
- supervisor role;
- maker-checker policy;
- approval state;
- rejection state;
- waiver state;
- quorum;
- escalation;
- privileged MFA/step-up requirement;
- reviewer persistence;
- close authority.

A future reviewer gate must consume exact immutable variance evidence plus exact durable explanation evidence without changing either.

The explanation author permission must not be reused as reviewer permission.

## Transaction ordering

A future source implementation must preserve fail-closed ordering:

1. validate trusted correlation and canonical variance shape;
2. obtain current verified organizational context;
3. derive exact POS execution context;
4. require `pos.shift.cash-variance-explanation.record`;
5. obtain authoritative recorded time;
6. enter the persistence transaction;
7. record or replay exact explanation evidence.

Unauthorized requests must not enter the explanation persistence transaction.

Authorization failure must not create, update, or delete explanation evidence.

## Replay behavior

Authorization is required for a runtime request even when the requested operation would be an exact idempotent replay.

A caller must not gain access to existing explanation evidence merely by knowing a prior operation identifier.

The canonical repository's replay fingerprint and tenant-scoped uniqueness remain evidence-integrity controls, not authorization substitutes.

## Runtime delivery remains unselected

Sprint71 does not select or publish:

- `AppServiceProvider` binding;
- config key;
- environment variable;
- feature flag;
- controller;
- request DTO/form request;
- route;
- response resource;
- route name;
- middleware chain;
- throttle rate;
- UI;
- queue;
- scheduled job;
- webhook.

The author permission policy is therefore not a delivered feature.

A later bounded runtime-readiness gate must decide the minimum non-Production delivery envelope separately.

## Production remains denied

Sprint71 grants no Production authority.

The canonical Sprint70 infrastructure adapter remains source-foundation-only and Production runtime denial remains intact.

No permission grant can override:

- runtime-class denial;
- persistence-disabled denial;
- feature-disabled denial;
- migration-not-applied state;
- missing provider binding;
- missing runtime route.

Permission is necessary for any future runtime use but never sufficient by itself.

## Migration posture

Sprint71 adds no migration and changes no schema.

Migration #25 remains:

`SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED`

Migration #26 remains:

`NOT SELECTED`

Permission policy selection does not authorize migration execution.

## Fail-closed requirements

Any future explanation authorization implementation must fail closed when:

- verified organizational context is absent;
- durable permission evidence is absent;
- the dedicated permission is not granted;
- the permission belongs to another tenant/context;
- a caller supplies tenant/organization/outlet/actor overrides;
- canonical variance evidence is absent or malformed;
- an explanation targets `MATCH` or zero variance;
- direction/sign consistency fails;
- explanation is empty or invalid;
- operation replay conflicts with existing evidence;
- authorization is attempted after persistence;
- role labels are used as authorization evidence;
- route/config presence is treated as permission;
- the caller attempts reviewer/approval/close semantics through the author permission.

Unknown authorization states remain denied by default.

## Source-readiness implications

Sprint71 selects authorization semantics only.

It does not yet authorize source implementation.

The next bounded gate should determine the exact authorization-source envelope required to integrate the selected permission into the existing Sprint70 application service without runtime delivery.

That future source-readiness envelope should consider only the minimum paths necessary for:

- `PosPermission` permission constant/accessor;
- `RecordCashVarianceExplanation` durable authorization dependency and pre-persistence require call;
- dedicated authorization regression coverage;
- dedicated workflow/documentation preservation.

It must not combine provider binding, config, controller/route, default grants, reviewer policy, close authority, UI, Production enablement, or migration execution.

## Historical compatibility rule

If later authorization-source qualification exposes stale workflow/oracle incompatibility rather than a business-source defect:

1. do not weaken authorization semantics;
2. do not add a default grant to manufacture green CI;
3. freeze the exact legitimate source blobs;
4. publish the smallest workflow-only compatibility predecessor;
5. preserve executable historical regressions;
6. merge the compatibility predecessor only after fresh exact-head qualification;
7. replay the frozen source byte-identically from new canonical main;
8. fresh-qualify again.

No fake-green CI is permitted.

## Explicit non-scope

Sprint71 does not select or implement:

- authorization source changes;
- default grants;
- role mapping;
- reviewer/supervisor policy;
- approval/rejection/waiver;
- maker-checker;
- privileged MFA/step-up;
- close authority;
- final close concurrency/idempotency;
- final evidence freshness window;
- final shift-state transition;
- explanation amendment/supersession;
- reason-code catalog;
- attachments;
- arbitrary cash movement;
- denomination counting;
- settlement/provider reconciliation;
- accounting/general ledger;
- provider/config runtime wiring;
- controller/route/API/UI;
- Technical Preview activation;
- Production activation;
- deployment/release;
- updater activation;
- migration execution/application;
- rollback/destructive database operations.

## Next bounded Sprint

After Sprint71 is canonical, the next bounded Sprint should define **cash-variance explanation authorization source readiness only**.

It must preserve:

- exact dedicated permission `pos.shift.cash-variance-explanation.record`;
- deny-by-default durable scoped authorization;
- no default grant;
- authorization before transaction/persistence;
- exact canonical variance/context binding;
- no reviewer/approval/close semantics;
- no runtime delivery.

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
