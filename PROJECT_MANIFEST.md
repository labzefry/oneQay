# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository / Product Owner attribution:** Lab | zefry

## Canonical state

**Canonical engineering checkpoint:** Sprint212
**Objective:** `CPANEL_NO_SSH_DURABLE_STAGING_TARGET_QUALIFICATION`
**Canonical engineering commit:** `7adc0f34bbf1646400c344e7c6d1f89324db61d1`
**Engineering PR:** #859 — `Sprint212: qualify cPanel no-SSH durable staging targets`
**Final engineering head:** `d3f771daabcf9263069e6b7b23af6302b06dab45`
**Exact-head qualification:** 91/91 successful
**Sprint212 qualification:** run `35493028325` — SUCCESS
**Sprint208 preservation:** run `35493028291` — SUCCESS
**Sprint207 preservation:** run `35493028394` — SUCCESS
**Sprint211 preservation:** run `35493028528` — SUCCESS
**M7.1 qualification:** run `35493028546` — SUCCESS
**Governance qualification:** run `35493028434` — SUCCESS
**PHP Foundation qualification:** run `35493028515` — SUCCESS
**Product Owner merge authority:** comment `5748011184`; `product-owner-merge-authority` — SUCCESS
**Engineering envelope:** 7 paths — `73f2978a1a748f8314c58078b62653ee331a8b21dd08bef971952a795ed9550e`
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`
**Previous canonical checkpoint:** Sprint211 reconciliation `7b7f8704c67c4f163220cd365cdda2d91f5e233f`

> `7adc0f34bbf1646400c344e7c6d1f89324db61d1` is the permanent canonical Sprint212 engineering evidence. The reconciliation squash must not replace it.

## cPanel no-SSH qualification capability

Sprint212 closes the proven source gap for shared-hosting/cPanel targets that do not expose SSH.

- Supported operator channel: cPanel File Manager + one-shot Cron Jobs + PHP CLI.
- The inspector verifies PHP >= 8.2, required extensions, private `0600` binding material, writable deployment roots, atomic rename, PHP symlink support, exact runtime identity, and document-root shape.
- Secret values are read only from private host material and are never emitted into the observed profile or target candidate.
- The candidate bridge emits the existing Sprint208 `OPERATOR_TARGET_CANDIDATE`; no alternate authority model is introduced.
- Operator capability assertions remain provisional until Sprint209 deployment evidence and runtime readiness/capability evidence prove them against the real deployed runtime.
- If Cron/PHP CLI/symlink/document-root isolation requirements cannot be met, the cPanel host fails closed and is not a qualified target.

## Published durable-staging operator bundle

The currently published deployable application bundle remains the Sprint211 bundle because Sprint212 changes qualification tooling and documentation, not application runtime source.

**Publication run:** `35487670967` — SUCCESS
**Actions artifact ID:** `10597712890`
**Artifact name:** `oneqay-durable-staging-e37300d5d1be-operator-bundle`
**Release ID:** `durable-staging-e37300d5d1be`
**Source commit:** `e37300d5d1be6727cdb5d818b6365c6429f2af9d`
**Durable artifact SHA-256:** `faf6b4799648c0fe1d5ddcf4a55e0a39ba8bd0506e3e2ef3f4bb33f38495d079`
**Durable manifest SHA-256:** `ef968562f76801e425d02c33d3ecfc13556f271f05bff1ff9441c5a147b588f0`
**Deployment handoff state:** `VALIDATED_FOR_EXTERNAL_DEPLOYMENT_NOT_AUTHORIZED`

## Operational NO-GO

Machine-readable state remains authoritative and unchanged: migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target selection `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; producer dispatch `NOT_PERFORMED`.

## Next position

Issue #856 remains the single operational handoff. A real target may now follow either the existing operator-managed POSIX path or the Sprint212 cPanel no-SSH qualification path. For cPanel, real observed host facts must be collected through the Sprint212 inspector and translated into the canonical Sprint208 candidate before separate short-lived deployment authority can be requested.

Open another engineering sprint only if a real host qualification or real execution exposes a concrete repository-side defect or missing capability.

Author by Lab | zefry
