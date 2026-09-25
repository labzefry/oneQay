# Sprint253 — Final Shift Close Post-Activation Historical Regression Successor Compatibility

Author by Lab | zefry

## Objective

Reconcile historical Sprint regression workflows with the canonical Final Shift Close transition to `ACTIVE` after PR #903 and the post-activation durable-staging promotion successor merged by PR #910.

## Compatibility rule

Historical workflows that previously asserted canonical `feature_activation.state = INACTIVE` now accept the bounded predecessor/successor horizon `INACTIVE|ACTIVE`. Fixture literals and source-shape assertions remain historical and unchanged. All migration, permission, deployment-authority, target-selection, Technical Preview, Production, updater, and runtime-boundary assertions remain unchanged.

This sprint changes CI compatibility only. It does not execute migration #27, reprovision permissions, mutate feature activation, deploy a release, reselect a target, widen runtime allowlists, activate Technical Preview, activate Production traffic, or activate the updater.

## Canonical base

- main: `94392c003db080e2585a6580e9bf9ac7b042766b`
- Sprint252 / PR #910: merged
- Final Shift Close: `ACTIVE`

## Acceptance

- every modified historical Sprint workflow preserves all non-activation assertions;
- only canonical activation predecessor guards become successor-compatible;
- fixture/source-shape literals remain unchanged;
- dedicated Sprint253 regression proves no recognized Sprint canonical activation guard still requires only predecessor `INACTIVE`.

Author by Lab | zefry
