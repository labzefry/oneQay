# Sprint107 Final Shift Close Preview Runtime Compatibility Entry Gate

Author by Lab | zefry

## Purpose

Sprint107 materializes the bounded entry gate that must be satisfied before any source change may widen Final Shift Close from the canonical `local/test/ci` durable runtime envelope into the Sprint106-selected Synthetic Technical Preview runtime class `preview`.

Sprint107 does **not** modify a runtime allowlist, execute migration #27, provision `pos.shift.close`, activate the Final Shift Close feature, mutate the operational state, deploy, activate Technical Preview, activate Production, release, change DNS, or activate the updater.

## Canonical target inherited from Sprint106

The only selected logical target is:

- environment ID: `oneqay-synthetic-technical-preview`;
- environment class: `technical-preview`;
- runtime class: `preview`;
- selection state: `SELECTED_NOT_AUTHORIZED`;
- shared runtime profile: `PRIVATE_SHARED_DOTENV_V1`.

Selection does not imply Technical Preview activation authority or Final Shift Close feature activation authority.

## Why a one-line provider allowlist change is invalid

Final Shift Close is not isolated behind one runtime guard. The current HTTP and durable execution path crosses multiple independently fail-closed runtime boundaries.

Sprint107 records nine minimum runtime components that currently authorize only `local`, `test`, and `ci`:

1. `FinalShiftCloseServiceProvider` delivery registration;
2. `RequirePosSessionContextMiddleware`;
3. `EnforceActiveFirstPartySessionAuthorityMiddleware`;
4. `LaravelFirstPartySessionAuthorityRepository`;
5. `LaravelFirstPartyIdentityEligibilityVerifier`;
6. `LaravelDurableRolePermissionRepository`;
7. `LaravelPersistenceTransaction`;
8. `LaravelCloseShiftRepository`;
9. `LaravelDurablePosSaleRepository`.

Changing only the Final Shift Close provider would therefore create a partially enabled runtime that is still rejected by middleware, session authority, authorization, transaction, or POS persistence boundaries.

## Synthetic Preview verifiers are not sufficient

The canonical Technical Preview provider supplies `SyntheticTenantMembershipVerifier` and `SyntheticOrganizationalRelationshipVerifier` for the synthetic Preview envelope. Those synthetic verifiers solve only the Preview relationship-verification boundary.

They do not authorize:

- active durable first-party session authority;
- durable first-party identity eligibility;
- durable role-permission lookup;
- durable persistence transactions;
- Final Shift Close repository mutation;
- durable POS sale lifecycle participation.

Therefore their existence must not be interpreted as general durable Preview compatibility.

## Machine-readable compatibility state

`ops/final-shift-close/PREVIEW_RUNTIME_COMPATIBILITY.json` is the canonical Sprint107 dependency inventory.

Every listed component is currently recorded as:

`preview_qualified = false`

and the aggregate state is:

`compatibility_state = BLOCKED`

The entry gate fails if the canonical runtime sources are widened to `preview` before a successor explicitly replaces this blocked matrix with bounded executable qualification.

## Required successor qualification

A future source sprint that proposes Preview compatibility must remain bounded and must qualify the runtime envelope as a unit. At minimum it must prove:

1. Preview inclusion is explicit and exact, never inferred from a generic non-Production class;
2. default/off behavior remains fail-closed;
3. Production remains denied;
4. the Final Shift Close route remains absent unless all feature prerequisites are explicitly armed;
5. `session.active` semantics are preserved or a separately selected Preview-specific session authority contract is introduced;
6. identity eligibility cannot become implicitly permissive;
7. `pos.shift.close` remains durable, tenant-scoped, and `NO_DEFAULT_GRANT`;
8. persistence transactions remain atomic and fail closed;
9. Final Shift Close durable evidence, exact replay, variance review prerequisites, and separation-of-duties remain intact;
10. sale/opening/closing/review evidence used by close belongs to one coherent Preview-compatible persistence model;
11. Synthetic Preview isolation cannot be mixed accidentally with durable Production data;
12. migration #27 live execution remains separately authorized;
13. a dedicated executable Preview regression proves both permitted and denied states across the complete path;
14. source qualification does not itself activate Technical Preview or Final Shift Close.

## Required architectural decision before source widening

Sprint107 deliberately does not choose between these possible future designs:

- extend the existing durable runtime envelope to permit `preview` across all required components;
- create a distinct Preview-specific durable adapter envelope;
- keep Final Shift Close unavailable in Synthetic Technical Preview and select a different non-Production durable target in a separately authorized contract.

That choice must be explicit because the current Synthetic Technical Preview uses synthetic relationship verifiers while Final Shift Close depends on durable transactional evidence.

## Authority boundaries

Preview runtime source compatibility is distinct from:

- migration #27 execution authority;
- `pos.shift.close` permission provisioning authority;
- Final Shift Close feature activation authority;
- Technical Preview activation authority;
- Production authority;
- deployment/release authority;
- DNS/cutover authority;
- updater activation authority.

No one authority implies another.

## Sprint107 disposition

`PREVIEW_RUNTIME_COMPATIBILITY = BLOCKED`

`RUNTIME_DEPENDENCY_INVENTORY = MATERIALIZED`

`RUNTIME_ALLOWLIST_CHANGE = NOT_IMPLEMENTED`

`PREVIEW_RUNTIME_REGRESSION = NOT_IMPLEMENTED`

`FEATURE_ACTIVATION_EVIDENCE_PRODUCER = NOT_IMPLEMENTED`

`FEATURE_ACTIVATION_EXECUTION = NOT_PERFORMED`

`MIGRATION_27_LIVE_EXECUTION = NOT_AUTHORIZED`

`FINAL_SHIFT_CLOSE_PERMISSION_PROVISIONING = NONE`

`FINAL_SHIFT_CLOSE_FEATURE_ACTIVATION = INACTIVE`

`TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY = NOT_GRANTED`

`PRODUCTION_AUTHORITY = NOT_GRANTED`

`UPDATER_ACTIVATION_AUTHORITY = NOT_GRANTED`
