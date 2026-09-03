# JRN-010 Prerequisite — Cash Variance Explanation Source Envelope Gate

Author by Lab | zefry

## Status

`SPRINT69 SOURCE-ENVELOPE GATE ONLY / EXACT NINE-PATH FUTURE SOURCE SHAPE / MIGRATION #25 INCLUDED IN FUTURE ENVELOPE / NO SOURCE IMPLEMENTATION / NO RUNTIME WIRING / NO REVIEWER POLICY / NO CLOSE AUTHORITY / JRN-010 SHIFT CLOSE NOT SELECTED`

Sprint69 freezes only the exact future source envelope for the durable, append-only, non-zero cash-variance explanation evidence foundation selected by canonical Sprint68.

It does not publish migration #25 source, application source, repository source, infrastructure source, runtime configuration, provider binding, controller, route, API resource, UI, permission, reviewer workflow, privileged step-up, close authority, final shift transition, deployment, release, updater activation, Technical Preview activation, Production activation, migration execution, rollback, or destructive database authority.

## Canonical basis

The canonical baseline is `ce3c7ca5b26c21798e650667dbec8eaae953aaab`.

Canonical Sprint64 publishes immutable `CashVarianceResult`.

Canonical Sprint65 keeps `OVER` and `SHORT` unresolved and non-authorizing.

Canonical Sprint66 selects:

- automatic tolerance exactly `0` atomic units;
- explanation required for every `OVER` and `SHORT`;
- no explanation requirement for exact `MATCH` under this policy.

Canonical Sprint67 requires authoritative non-zero explanation evidence to be durable and auditable.

Canonical Sprint68 selects migration #25 semantically for one append-only durable explanation-evidence structure and reserves the table name:

`oneqay_pos_cash_variance_explanation_evidence`

Sprint69 does not reopen those semantics.

## Collision conclusion

All nine selected future paths are absent from canonical main at this gate.

No class, repository port, infrastructure adapter, migration #25 file, dedicated regression, dedicated workflow, or source-foundation document collision exists for the selected names.

The future source implementation must not rename or widen this envelope merely for convenience.

## Frozen future source envelope

The next bounded source publication, if separately authorized and fresh-qualified, is restricted to exactly these nine paths:

1. `.github/workflows/sprint69-jrn010-prerequisite-cash-variance-explanation-source-regression.yml`
2. `apps/web/app/Application/Pos/CashVarianceExplanationCommand.php`
3. `apps/web/app/Application/Pos/CashVarianceExplanationRepository.php`
4. `apps/web/app/Application/Pos/CashVarianceExplanationResult.php`
5. `apps/web/app/Application/Pos/RecordCashVarianceExplanation.php`
6. `apps/web/app/Infrastructure/Pos/LaravelCashVarianceExplanationRepository.php`
7. `apps/web/database/migrations/0000_00_00_000025_create_pos_cash_variance_explanation_evidence_foundation.php`
8. `apps/web/tests/pos-cash-variance-explanation-durable.php`
9. `docs/JRN_010_PREREQUISITE_CASH_VARIANCE_EXPLANATION_SOURCE_FOUNDATION.md`

The sorted newline-terminated path SHA-256 fingerprint is:

`c2a575ec728249a8a4b26c173229b26455eee92c7bd4c59026a1d4c064e2c442`

Unknown path count, unknown path, rename, additional binding/config file, or fingerprint mismatch must fail closed.

## Deliberately excluded future paths

The frozen envelope intentionally excludes:

- `apps/web/app/Providers/AppServiceProvider.php`;
- `apps/web/config/oneqay.php`;
- permission-definition files;
- default-grant files;
- controller files;
- route files;
- API resource files;
- UI components;
- feature-flag/config files;
- reviewer/approval source;
- privileged MFA/step-up source;
- shift-close source;
- updater/deployment/release files.

The future source foundation must remain non-runtime and non-authorizing.

The dedicated regression may construct the application service and infrastructure adapter explicitly without publishing a container/provider binding.

## Migration #25 path lock

The future migration path is frozen exactly as:

`apps/web/database/migrations/0000_00_00_000025_create_pos_cash_variance_explanation_evidence_foundation.php`

The future migration must create exactly one dedicated append-only explanation-evidence table:

`oneqay_pos_cash_variance_explanation_evidence`

No second adjudication/reviewer/approval table is selected.

No mutation of opening-cash, closing-cash, shift, sale, refund, or catalog schemas is selected.

## Migration #25 lifecycle distinction

Sprint69 freezes a future source path only.

Migration #25 status remains:

**SELECTED SEMANTICALLY / SOURCE NOT PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

A later source publication may change that status only to:

**SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Source publication must never be interpreted as migration execution authority.

## Selected future application command

`CashVarianceExplanationCommand.php` is selected as an immutable caller-intent contract.

It may contain only the minimum caller-supplied explanation intent:

- stable operation identity;
- non-empty explanation text.

It must not accept caller-supplied:

- tenant identity;
- organization identity;
- outlet identity;
- shift identity;
- opening evidence identity;
- closing evidence identity;
- cutoff;
- expected cash;
- observed closing cash;
- signed variance;
- variance direction;
- currency;
- scale;
- actor identity;
- reviewer identity;
- approval/rejection state;
- close state.

The operation identity must use the existing stable-operation identifier posture.

The command semantic fingerprint must include the explanation content in canonical deterministic form and must not include mutable presentation-only values.

Sprint69 does not select reason codes, attachments, or structured reviewer metadata.

## Selected future application result

`CashVarianceExplanationResult.php` is selected as immutable durable evidence output.

It must expose the minimum durable evidence needed to prove exactly what was persisted:

- explanation evidence identity;
- operation identity;
- tenant identity;
- organization identity;
- outlet identity;
- shift identity;
- opening cash evidence identity;
- closing cash evidence identity;
- authoritative actor identity;
- canonical cutoff;
- expected cash atomic value;
- observed closing cash atomic value;
- signed variance atomic value;
- variance direction;
- currency;
- currency scale;
- explanation text;
- correlation identity;
- authoritative recorded timestamp.

The result must not expose:

- reviewer approval;
- rejection state;
- waiver state;
- write-off state;
- close authority;
- final shift state.

The result is durable explanation evidence only.

## Selected future repository port

`CashVarianceExplanationRepository.php` is selected as the application-layer persistence port.

Its recording operation must receive only trusted/canonical inputs required to persist one explanation:

- verified `PosExecutionContext`;
- canonical non-zero `CashVarianceResult`;
- immutable `CashVarianceExplanationCommand`;
- correlation identity;
- authoritative recorded timestamp.

It must return `CashVarianceExplanationResult`.

It must not accept separate variance snapshot fields that could override `CashVarianceResult`.

It must not accept reviewer, approval, rejection, close-state, or permission inputs.

## Selected future application service

`RecordCashVarianceExplanation.php` is selected as the application service.

The future service must:

- accept one canonical `CashVarianceResult`;
- accept one `CashVarianceExplanationCommand`;
- accept correlation identity;
- resolve authoritative verified organizational context;
- derive `PosExecutionContext` from that verified context;
- obtain one authoritative positive recorded timestamp from the existing clock boundary;
- execute repository persistence under the existing persistence transaction boundary;
- return the immutable durable explanation result.

The service must fail closed for malformed correlation identity, missing verified context, invalid recorded time, malformed variance, or repository conflict.

## Authorization separation

Sprint69 does not select a new permission.

Therefore the future source foundation must not invent:

- `RECORD_CASH_VARIANCE_EXPLANATION`;
- a role;
- a default grant;
- a reviewer privilege;
- a privileged step-up requirement.

The source foundation remains non-runtime and may be explicitly constructed only in dedicated regression.

A later separately bounded authorization gate must decide who may invoke explanation recording in a delivery surface.

The absence of a selected permission is not permission to call the service in Production.

## Existing clock reuse

No new clock path is selected.

The future application service may reuse the existing canonical POS clock boundary already used for durable opening/closing evidence if its semantics remain only "authoritative current recorded timestamp".

No new wall-clock abstraction is required merely for this envelope.

The future regression must prove non-positive recorded timestamps fail closed.

## Infrastructure adapter

`LaravelCashVarianceExplanationRepository.php` is selected as the infrastructure adapter.

The adapter must preserve the existing durable POS evidence posture:

- persistence-enabled guard;
- runtime-class guard;
- source-foundation feature guard supplied explicitly to the adapter constructor for regression;
- exact tenant isolation;
- transaction-safe operation replay;
- fail-closed conflict handling;
- restrictive relationship validation;
- append-only insert;
- no update;
- no delete.

Because AppServiceProvider/config are excluded from this envelope, the feature-enabled constructor input is test/source-foundation evidence only and does not create runtime activation.

## Runtime-class posture

The future adapter must reject Production runtime.

The minimum source-foundation runtime posture may allow only canonical local/test/ci classes consistent with existing durable POS evidence foundations.

A source-foundation test flag does not authorize Technical Preview or Production.

No environment variable or config key is selected by Sprint69.

## Exact replay semantics

The future source implementation must preserve stable replay semantics:

- same tenant + same operation identity + same exact canonical payload -> return original evidence;
- same tenant + same operation identity + conflicting payload -> fail closed;
- cross-tenant operation identity reuse -> isolated;
- a second independent authoritative explanation for the same exact canonical non-zero variance -> fail closed under the initial foundation.

Replay must return the original:

- evidence identity;
- canonical variance snapshot;
- explanation text;
- actor attribution;
- correlation identity;
- recorded timestamp.

Replay must not replace correlation or recorded time with values from the retry.

## Initial one-explanation rule

Sprint68 selected no amendment/supersession model.

Therefore the future source foundation must enforce exactly one authoritative explanation for one canonical selected non-zero variance under the initial foundation.

The database uniqueness design must minimally prevent competing explanation evidence for the same canonical closing/variance context.

The future source implementation must not silently:

- overwrite explanation text;
- append a second authoritative explanation;
- convert a conflict into replay;
- invent amendment/supersession.

A later immutable supersession design requires a separate Sprint.

## Exact variance binding

The future repository must persist the exact canonical variance snapshot from `CashVarianceResult`.

It must validate exact agreement with durable database evidence for all relationships that can be independently proven from canonical storage, including at least:

- tenant;
- organization;
- outlet;
- shift;
- opening cash evidence identity;
- closing cash evidence identity;
- canonical cutoff;
- expected/observed currency and scale context where applicable.

It must not recompute the signed variance from caller input.

It must not query "current active shift" as a substitute for the shift represented by the canonical variance.

It must not substitute another opening or closing cash evidence record.

## Non-zero/sign lock

The future source must accept only:

- `OVER` with signed variance greater than zero;
- `SHORT` with signed variance less than zero.

It must reject:

- `MATCH`;
- zero variance with `OVER` or `SHORT`;
- positive variance labeled `SHORT`;
- negative variance labeled `OVER`;
- unknown direction.

Explanation must never normalize non-zero variance into `MATCH`.

## Explanation-content lock

The future command/service must reject explanation text that is empty after canonical normalization.

The source must not silently substitute:

- a default reason;
- "N/A";
- a reviewer comment;
- a generic tolerance label.

Sprint69 does not select:

- maximum text length;
- reason-code catalog;
- attachment support;
- localization rules.

Any implementation-specific bounded storage limit must be explicit in the later source-foundation document and regression and must not truncate silently.

## Migration #25 minimum schema lock

The future migration must implement the minimum semantic columns selected by Sprint68:

- `tenant_id`;
- `evidence_id`;
- `operation_id`;
- `payload_fingerprint`;
- `shift_id`;
- `opening_cash_evidence_id`;
- `closing_cash_evidence_id`;
- `actor_identity_id`;
- `organization_id`;
- `outlet_id`;
- `cutoff_at_unix`;
- `expected_cash_atomic`;
- `observed_closing_cash_atomic`;
- `variance_atomic`;
- `variance_direction`;
- `currency`;
- `currency_scale`;
- `explanation_text`;
- `correlation_id`;
- `recorded_at_unix`.

No reviewer/approval/close fields may be added.

The migration must be forward-only and its `down()` must fail closed because rollback is not authorized.

## Key and relationship lock

The future migration must use tenant-scoped restrictive relationships consistent with canonical durable evidence patterns.

At minimum:

- primary key: `tenant_id + evidence_id`;
- unique operation replay key: `tenant_id + operation_id`;
- one-authoritative-explanation uniqueness for the initial foundation;
- restrictive foreign key to exact shift;
- restrictive foreign key to exact opening cash evidence;
- restrictive foreign key to exact closing cash evidence;
- restrictive foreign key to authoritative actor identity;
- restrictive foreign key to organization;
- restrictive foreign key to outlet.

No cascade delete/update is selected.

The exact database uniqueness column combination for the one-explanation rule must be the smallest deterministic shape that proves one authoritative explanation per canonical selected variance without widening to amendment semantics.

## Signed integer storage lock

Expected and observed atomic values remain non-negative.

Signed variance must use a signed integer representation capable of preserving the canonical PHP integer range used by Sprint64.

No unsigned variance column is permitted.

No float, double, decimal coercion, tolerance epsilon, or implicit rounding is selected.

## Dedicated regression path

`apps/web/tests/pos-cash-variance-explanation-durable.php` must prove at least:

- successful durable explanation for `OVER`;
- successful durable explanation for `SHORT`;
- `MATCH` denial;
- sign/direction mismatch denial;
- empty explanation denial;
- exact canonical snapshot persistence;
- actor attribution from trusted context;
- tenant isolation;
- organization/outlet/shift exact binding;
- opening evidence identity binding;
- closing evidence identity binding;
- cutoff preservation;
- currency/scale preservation;
- exact replay returns original evidence;
- conflicting operation reuse denial;
- second authoritative explanation for same variance denial;
- cross-tenant operation reuse isolation;
- no update mutation;
- no delete mutation;
- no sale mutation;
- no refund mutation;
- no opening/closing evidence mutation;
- persistence-disabled denial;
- source-feature-disabled denial;
- Production-runtime denial;
- migration #25 forward-only rollback denial;
- no reviewer/approval/close state.

The regression must explicitly construct source-foundation dependencies and must not require runtime route/controller/provider wiring.

## Dedicated workflow path

`.github/workflows/sprint69-jrn010-prerequisite-cash-variance-explanation-source-regression.yml` must:

- enforce exact nine-path count;
- enforce fingerprint `c2a575ec728249a8a4b26c173229b26455eee92c7bd4c59026a1d4c064e2c442`;
- prove migrations #1-#24 are unchanged;
- require exactly one new migration #25 path;
- reject migration #26;
- validate PHP syntax;
- install locked dependencies;
- reject High or Critical Composer advisories;
- run the dedicated durable explanation regression;
- preserve Sprint64 cash-variance regression;
- preserve Sprint60 expected-cash regression;
- preserve Sprint57 sale-to-shift binding regression;
- preserve relevant historical opening/closing cash evidence regression horizons;
- assert migration/lifecycle locks from the future source-foundation document;
- reject provider/config/controller/route/UI/permission/reviewer/close-state widening.

No executable business regression may be skipped merely to produce a green result.

## Historical compatibility warning

Canonical historical workflows currently include material source-shape and migration-count horizons through migration #24.

A legitimate future source publication of migration #25 may therefore expose stale historical oracle incompatibilities.

If fresh qualification fails because an older workflow does not recognize the exact frozen nine-path source shape or migration #25 horizon, the failure must be classified before changing business source.

If the failure is historical workflow/oracle incompatibility rather than a source defect:

1. close or do not merge the affected source PR;
2. freeze source blob identity;
3. publish the smallest workflow-only compatibility predecessor;
4. preserve exact business regression execution;
5. fresh-qualify and merge that predecessor;
6. replay the exact frozen nine-path source byte-identically from new canonical main;
7. rerun fresh exact-head qualification.

Business semantics, tests, migration #25, or fail-closed behavior must not be weakened to satisfy stale CI.

## No provider/config widening

The source envelope excludes `AppServiceProvider.php` and config because no runtime delivery is selected.

A later runtime-enablement Sprint, if ever authorized, must separately decide:

- dependency injection binding;
- config/feature flag;
- permission;
- controller/route;
- request validation;
- UI;
- rollout posture.

Sprint69 must not be interpreted as pre-authorizing any of them.

## Source-foundation document path

`docs/JRN_010_PREREQUISITE_CASH_VARIANCE_EXPLANATION_SOURCE_FOUNDATION.md` must record the final source behavior and verify that:

- explanation evidence is durable;
- explanation is append-only;
- exact replay is deterministic;
- only `OVER`/`SHORT` are supported;
- automatic tolerance remains exactly zero;
- explanation is not approval;
- explanation is not close authority;
- no runtime delivery is wired;
- migration #25 is source-published only;
- migration execution/application remains unauthorized.

## Explicit non-scope

Sprint69 does not select or implement:

- migration #25 source;
- application source;
- repository source;
- infrastructure adapter source;
- dedicated regression source;
- dedicated workflow source;
- provider binding;
- config/feature flag;
- permission/default grant;
- controller/route/API/UI;
- reason-code catalog;
- attachment support;
- explanation amendment/supersession;
- reviewer/supervisor policy;
- approval/rejection policy;
- maker-checker;
- privileged MFA/step-up;
- close authority;
- final close concurrency/idempotency;
- final evidence freshness window;
- final shift-state transition;
- controlled reopen;
- late-event remediation;
- arbitrary cash movement;
- denomination counting;
- settlement/provider reconciliation;
- accounting/general ledger;
- Technical Preview activation;
- Production activation;
- deployment/release;
- updater activation;
- migration execution/application;
- rollback/destructive database operations.

## Next bounded Sprint

After Sprint69 is canonical, the next bounded Sprint may publish exactly the frozen nine-path durable explanation source foundation.

That source publication must:

- preserve the exact path fingerprint;
- remain byte-bounded to the nine paths;
- publish migration #25 source only, not execute it;
- remain non-runtime and non-authorizing;
- fresh-qualify current and historical material regression horizons;
- use separate workflow-only compatibility correction if stale historical oracles reject the legitimate frozen source shape.

It must not expand into reviewer approval, permission/runtime delivery, privileged step-up, close authority, final shift transition, deployment, or migration execution.

## Migration and lifecycle lock

Migration #22: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #23: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #24: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #25: **SELECTED SEMANTICALLY / SOURCE NOT PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #26: **NOT SELECTED**

Technical Preview: **INACTIVE**

Production: **NO-GO**

Updater: **INACTIVE**

Deployment/release: **NOT AUTHORIZED**

Migration execution/application: **NOT AUTHORIZED**

Rollback/destructive database operations: **NOT AUTHORIZED**

JRN-010 Shift Close: **NOT SELECTED**

Attribution: **Lab | zefry**
