# Sprint108 Final Shift Close Preview Runtime Strategy Selection

Author by Lab | zefry

## Purpose

Sprint108 resolves the strategy decision opened by Sprint107. It does not widen any runtime allowlist, activate Final Shift Close, execute migration #27, provision `pos.shift.close`, change operational state, deploy, release, activate Technical Preview or Production, alter DNS, or activate the updater.

## Canonical finding

Synthetic Technical Preview is intentionally a synthetic fixture runtime. `TechnicalPreviewServiceProvider` replaces tenant membership and organizational relationship verification with synthetic verifiers and binds `SyntheticPosStore` to the deterministic Preview fixture.

Final Shift Close is a durable transactional lifecycle. Sprint107 proved that its required runtime path crosses durable session authority, identity eligibility, role-permission authorization, persistence transaction, Final Shift Close repository, POS session context, and durable POS sale lifecycle. Those components remain `local/test/ci` only.

Expanding all durable runtime guards to `preview` would therefore change the nature of Synthetic Technical Preview from an isolated synthetic runtime into a mixed synthetic/durable runtime. Expanding only a subset would create a partial runtime and fail closed at another dependency.

## Selected strategy

`PREVIEW_RUNTIME_STRATEGY = KEEP_DURABLE_FINAL_SHIFT_CLOSE_UNAVAILABLE_ON_SYNTHETIC_PREVIEW`

The Synthetic Technical Preview remains useful for deterministic UX and synthetic behavior rehearsal, but it is not eligible to produce trusted durable Final Shift Close activation evidence.

A future synthetic Final Shift Close rehearsal may be materialized separately only if it is clearly labeled synthetic, does not write durable operational state, does not execute migration #27, does not provision durable permission, and cannot satisfy `final-shift-close-feature-activation-evidence`.

## Rejected strategy: widen the durable envelope to Synthetic Preview

`WIDEN_DURABLE_RUNTIME_ENVELOPE_TO_SYNTHETIC_PREVIEW = REJECTED`

Reason: it would mix synthetic tenant/organization/POS fixtures with durable session, authorization, transaction, and database mutation semantics. The resulting environment would no longer be a clean synthetic Technical Preview and would create unnecessary coupling between demonstration runtime and operational runtime.

## Rejected strategy: use Synthetic Preview as durable activation evidence

`SYNTHETIC_PREVIEW_DURABLE_ACTIVATION_EVIDENCE = REJECTED`

A successful synthetic route or UI rehearsal cannot prove that the real durable migration, permission, transaction, session, and persistence chain is operating on a non-synthetic target. Treating such a result as operational activation evidence would be fake-green.

## Consequence for the Sprint106 activation target

Sprint106 selected Synthetic Technical Preview as a logical target while explicitly marking it `SELECTED_NOT_AUTHORIZED`. Sprint107 then proved that target is not durable-runtime compatible.

Sprint108 does not edit `ACTIVATION_TARGET.json`, because changing the target contract is a separate bounded decision. Instead, Sprint108 records that the current selected logical target is **not eligible for durable Final Shift Close activation execution** under the selected strategy.

Before an operational feature-activation evidence producer may be implemented, a successor must select a **non-synthetic durable runtime target** with:

1. stable environment/runtime identity;
2. exact running build provenance;
3. durable persistence enabled under an explicitly qualified runtime class;
4. durable first-party session authority;
5. durable identity eligibility and organizational access;
6. durable role-permission authorization;
7. durable persistence transaction support;
8. durable POS sale and Final Shift Close lifecycle support;
9. authenticated configuration mutation scoped only to `ONEQAY_POS_SHIFT_CLOSE_ENABLED`;
10. read-before/write/read-after verification;
11. non-mutating route/health attestation;
12. verified rollback to `false`.

## Authority separation

This strategy selection does not grant:

- migration #27 execution authority;
- `pos.shift.close` provisioning authority;
- feature activation authority;
- deployment authority;
- Technical Preview activation authority;
- Production authority;
- release authority;
- DNS/cutover authority;
- updater activation authority.

## Sprint108 disposition

`PREVIEW_RUNTIME_STRATEGY = KEEP_DURABLE_FINAL_SHIFT_CLOSE_UNAVAILABLE_ON_SYNTHETIC_PREVIEW`

`SYNTHETIC_PREVIEW_DURABLE_ACTIVATION_EVIDENCE = REJECTED`

`FUTURE_DURABLE_ACTIVATION_TARGET = RESELECTION_REQUIRED`

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
