# Sprint246 — Final Shift Close Feature Activation Evidence Successor

Author by Lab | zefry

## Purpose

Sprint246 closes the final trusted-evidence gap between the already-governed cPanel one-shot Final Shift Close activation executor and the canonical `INACTIVE -> ACTIVE` state transition.

The Sprint239 executor intentionally emits `FLAG_TRUE_VERIFIED_HEALTHY_AWAITING_RUNTIME_ALLOWLIST` and does not itself publish `final-shift-close-feature-activation-evidence`. That historical boundary remains unchanged.

The selected durable-staging source already contains the Sprint203/Sprint240 bounded runtime compatibility allowlist successor. The successor is armed only for the already-qualified staging/durable-staging runtime when `ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED=true`. Sprint246 therefore verifies that the existing allowlist is already armed after executor success; it does not widen or mutate the runtime allowlist.

## Trusted evidence successor

The workflow `.github/workflows/final-shift-close-feature-activation-evidence.yml` is a protected, read-only evidence producer. It requires:

- current canonical `main`;
- exact open state-transition PR/head;
- exact one-path `ops/final-shift-close/STATE.json` transition envelope;
- exact-head `product-owner-merge-authority=success`;
- exact-head `final-shift-close-feature-activation-authority=success`;
- exact-head `final-shift-close-feature-activation-execution-ready=success`;
- exact successful activation operator-kit run;
- exact successful dependency-envelope evidence run;
- authenticated HTTPS retrieval of the cPanel execution receipt and live runtime context from the selected target;
- runtime flag readback `ONEQAY_POS_SHIFT_CLOSE_ENABLED=true`;
- selected-source identity and artifact identity matching the persisted target;
- prequalified runtime allowlist already armed with no allowlist mutation;
- no migration, permission provisioning, deployment, Technical Preview, Production, or updater action.

Only after all checks pass may the workflow publish `final-shift-close-feature-activation-evidence=success` on the exact state-transition PR head.

## PR #903 continuity

PR #903 remains the exact one-file operational state-transition proposal. Sprint246 does not edit its branch, head commit, or `ops/final-shift-close/STATE.json`.

The Product Owner activation authority already bound to PR #903 remains exact-head scoped as long as PR #903 head remains unchanged. Sprint246 itself is engineering-only and does not grant any new operational authority.

## Operational boundaries / NO-GO

Sprint246 does not mutate the runtime, run migration #27, perform permission provisioning, deploy a release, reselect the target, activate Technical Preview, activate Production, activate the updater, or modify the runtime allowlist.

The runtime flag mutation remains exclusively owned by the existing separately authorized cPanel one-shot executor. The evidence workflow is read-only and may only observe, verify, upload secret-safe evidence, and publish the exact trusted commit-status context after successful verification.

Author by Lab | zefry
