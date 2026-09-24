# Sprint240 — Final Shift Close Durable-Staging Runtime Promotion Successor

Author by Lab | zefry

## Purpose / Why

Sprint239 materialized a guarded cPanel activation transport, but the selected durable target still runs historical source `5be28a3c001738373588b58e9d29832c46402de1`. Opening the Final Shift Close delivery gate in repository source alone would therefore not make the selected runtime ready. Sprint240 closes this gap as one bounded promotion-readiness delivery rather than a chain of granular compatibility sprints.

## Objective / Gap

Materialize a fail-closed `durable-staging` delivery successor, restore current-main durable artifact publication against the already-completed migration/permission state, and automatically publish a target-promotion operator kit after a successful artifact publication.

Sprint240 does **not** deploy the application, replay migration #27, grant `pos.shift.close` again, mutate selected-target identity, activate Final Shift Close, activate Technical Preview/Production, or activate the updater.

## What changed

The historical `FinalShiftCloseServiceProvider` and historical `bootstrap/app.php` remain unchanged. A dedicated durable-staging Final Shift Close child provider is registered through the already-existing `PosOperationsHubServiceProvider` durable-staging aggregate rather than by widening a historical bootstrap boundary.

The successor reuses the same existing enterprise Final Shift Close route/UI delivery and only becomes eligible when all of the following are simultaneously true:

- the existing durable-staging bridge is explicitly armed;
- runtime class is exactly `durable-staging`;
- packaged `RELEASE.json` identifies a non-production, non-synthetic durable-staging release;
- runtime source identity matches that packaged release exactly;
- runtime artifact identity is present as a 64-character SHA-256 binding;
- durable persistence, active first-party session control, and POS sale completion are enabled;
- `ONEQAY_POS_SHIFT_CLOSE_ENABLED=true`.

The existing staging compatibility bridge gains only the exact `GET /pos/shifts/close` and `POST /pos/shifts/close` requests required by this already-governed Final Shift Close surface. Unknown, preview, production, stale-source, malformed-release, and feature-disabled conditions remain fail-closed.

The durable-staging publication workflow now validates the **current** canonical state: migration #27 `EXECUTED`, permission `PROVISIONED`, feature `INACTIVE`, and selected target `SELECTED_NOT_AUTHORIZED`. It publishes a reproducible successor candidate from exact `main` without deployment.

A separate `workflow_run` successor packages the exact upstream artifact, manifest, checksum, deployment handoff, current cPanel deployment/evidence tooling, and Sprint239 activation executor into a secret-free promotion kit. The kit explicitly records `deployment_authority=NOT_GRANTED` and requires fresh post-deployment runtime re-attestation plus selected-target generation update.

## Evidence / Qualification

Engineering base: `fd799f3c3f78d70f7c8428579e0f2ba364d61e17`

Exact 19-path sorted-newline SHA-256:

`95f5d2f4ad04d6c1813fcaf0f95a80b612f780d3f67ebd9d3d11122531d6864b`

Regression proves the durable-staging gate positive path, fail-closed negative paths, unchanged legacy Final Shift Close provider, unchanged bootstrap boundary, existing POS aggregate registration, unchanged canonical operational state, selected-target identity preservation, current-main artifact publication semantics, and non-executing promotion-kit semantics.

## Enterprise UI/UX continuity

No separate staging-only UI is introduced. The successor reuses the existing Final Shift Close enterprise page and HTTP delivery so staging qualification exercises the same product UX intended for later governed promotion. No UI route is exposed unless all delivery gates and the explicit feature flag qualify. This avoids UX divergence between staging and the later governed Production promotion path.

## Operational boundaries / NO-GO

Current selected target remains `oneqay-durable-staging-01` running `5be28a3c001738373588b58e9d29832c46402de1` until a separately authorized deployment actually occurs and fresh attestation proves the successor source/artifact. Final Shift Close remains `INACTIVE`. Deployment authority remains `NOT_GRANTED`; Technical Preview and Production remain `NOT_AUTHORIZED`; updater remains `INACTIVE`.

## Next position

After merge, the main-branch artifact publication and chained promotion-kit publication are safe to run automatically because they perform no target mutation. The next material step is a single separately authorized durable-staging deployment using the newly published exact-main candidate, immediately followed by fresh runtime attestation and selected-target generation update. Feature activation remains a distinct exact-head authority/evidence gate.

Author by Lab | zefry
