# Sprint239 — Final Shift Close cPanel Activation Transport Readiness

Author by Lab | zefry

## 1. Purpose

Sprint239 closes the remaining **transport/readiness source gap** after the canonical post-PR889 state reconciliation. It does not activate Final Shift Close. The selected target is already `oneqay-durable-staging-01` / `durable-staging`, migration #27 is already `EXECUTED`, and `pos.shift.close` provisioning is already `PROVISIONED`, while canonical feature state remains `INACTIVE`.

Repository discovery also proved that the Sprint154 execution-plan qualifier and the target-bound feature-activation transport envelope already exist. Both intentionally stop at source-only boundaries (`NOT_IMPLEMENTED` / `NOT_PERFORMED`). Sprint239 therefore does not duplicate them. It materializes the concrete operator-side adapter and the governed workflow that can prepare an exact-target-bound operator kit after all future activation gates are satisfied.

## 2. Bounded objective

`FINAL_SHIFT_CLOSE_CPANEL_ACTIVATION_TRANSPORT_READINESS`

The concrete adapter is:

`CPANEL_PRIVATE_FILE_ONE_SHOT_CRON_V1`

It reuses the repository's established cPanel no-SSH operational pattern: a private File Manager workspace outside the public document root plus a one-shot cPanel Cron Jobs PHP CLI invocation. No public privileged activation route is introduced.

## 3. Concrete mutation ceremony

The executor is limited to `ONEQAY_POS_SHIFT_CLOSE_ENABLED` and preserves the Sprint154 ceremony:

1. read the current runtime flag and require fail-closed `false`;
2. atomically write `true` while preserving unrelated runtime configuration bytes;
3. read the runtime file again and require `true`;
4. perform authenticated, non-mutating HTTPS readiness attestation bound to the exact selected environment/runtime/source/artifact;
5. on any post-write failure, restore the exact pre-write runtime configuration bytes;
6. verify the rollback digest and flag readback are exactly back to `false`.

The authority is target-bound, plan-bound, transport-envelope-bound, state-transition-PR-bound, one-time-token-hash-bound, and limited to a maximum 900-second window. Plaintext approval tokens and target readiness credentials are never embedded in repository source, the operator kit, or execution evidence.

## 4. Dispatch workflow remains fail-closed today

`.github/workflows/final-shift-close-feature-activation.yml` is now materialized as the governed preparation workflow, but it **does not directly mutate the selected target**. It validates current canonical state, exact future state-transition PR/head, Product Owner authority, exact feature-activation authority, required CI, and exact trusted capability/dependency evidence. When valid, it prepares a short-lived target-bound operator kit and publishes only the `final-shift-close-feature-activation-execution-ready` status.

Current `FinalShiftCloseServiceProvider` delivery remains restricted to `local`, `test`, and `ci`. Sprint239 deliberately does not change that provider because widening only this one gate without a separately reviewed successor would violate the historical runtime compatibility chain. Accordingly the workflow contains an explicit fail-closed preflight requiring a future durable-staging allowlist successor before an operator kit may be produced.

Therefore Sprint239 cannot activate Final Shift Close on the current canonical main.

## 5. Security and rollback properties

The cPanel executor:

- is PHP CLI only and has no HTTP/public entry point;
- requires private regular non-symlink runtime, authority, and approval-token files;
- rejects malformed, duplicated, already-true, or target-drifted feature flags;
- validates selected environment, runtime class, running source commit, running artifact SHA-256, readiness attestation identity, selection fingerprint, target binding, dependency envelope, activation plan, and transport envelope;
- accepts only HTTPS readiness with TLS peer/host verification;
- records only digests and non-secret evidence;
- performs no migration, permission provisioning, release deployment, target selection, runtime allowlist modification, Technical Preview activation, Production activation, or updater activation;
- restores exact original runtime bytes if health/readback fails after mutation.

Successful executor evidence intentionally records `FLAG_TRUE_VERIFIED_HEALTHY_AWAITING_RUNTIME_ALLOWLIST` and `BLOCKED_BY_RUNTIME_ALLOWLIST`; it is not itself canonical `STATE.json` activation evidence and does not authorize a state transition.

## 6. Exact engineering envelope

Exactly six paths belong to Sprint239:

1. `.github/workflows/final-shift-close-feature-activation.yml`
2. `.github/workflows/sprint239-final-shift-close-cpanel-activation-transport-readiness-regression.yml`
3. `apps/web/tests/pos-final-shift-close-cpanel-activation-transport.php`
4. `docs/SPRINT239_FINAL_SHIFT_CLOSE_CPANEL_ACTIVATION_TRANSPORT_READINESS.md`
5. `ops/final-shift-close/FINAL_SHIFT_CLOSE_FEATURE_ACTIVATION_CPANEL_TRANSPORT_READINESS_CONTRACT.json`
6. `tools/cpanel/execute-final-shift-close-feature-activation.php`

Sorted newline-terminated path-set SHA-256:

`fef6c595dd1c26345a8d0cea97dfdea0dace26bde37a9a64743c3dedf48a3e3a`

No `STATE.json`, provider, migration, permission, deployment, Technical Preview, Production, or updater path belongs to this envelope.

## 7. Operational NO-GO after Sprint239

The canonical operational boundaries remain:

- migration #27: already `EXECUTED`; no replay;
- permission `pos.shift.close`: already `PROVISIONED`; no second grant;
- Final Shift Close: `INACTIVE`;
- runtime allowlist change: `NOT_PERFORMED`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview: `NOT_AUTHORIZED`;
- Production: `NOT_AUTHORIZED`;
- updater: `INACTIVE`.

## 8. Next gate

The next bounded engineering step is **not another transport foundation**. It is a separately reviewed Final Shift Close durable-staging runtime allowlist successor that proves the complete delivery dependency chain for `durable-staging` without broadening Production or Technical Preview authority. Only after that source is qualified and merged may the Sprint239 dispatch workflow become eligible to prepare an operator kit; actual feature activation still requires the separate exact-target/exact-head activation authority and execution evidence ceremony.
