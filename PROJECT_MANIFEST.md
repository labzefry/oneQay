# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository / Product Owner attribution:** Lab | zefry

## Canonical state

**Canonical engineering checkpoint:** Sprint213
**Objective:** `CPANEL_NO_SSH_OPERATOR_QUALIFICATION_KIT_PUBLICATION`
**Canonical engineering commit:** `c8998c00e19c177ac535921dbc0ef1fa96b06583`
**Engineering PR:** #861 — `Sprint213: publish cPanel no-SSH operator qualification kit`
**Final engineering head:** `4c2ef5d73436e3660d0f6700a9bb3cfe46438289`
**Exact-head qualification:** 92/92 successful
**Sprint213 qualification:** run `35494619337` — SUCCESS
**Sprint212 preservation:** run `35494619310` — SUCCESS
**Sprint211 preservation:** run `35494619279` — SUCCESS
**Sprint209 preservation:** run `35494619409` — SUCCESS
**Sprint208 preservation:** run `35494618989` — SUCCESS
**Sprint207 preservation:** run `35494619256` — SUCCESS
**M7.1 qualification:** run `35494619181` — SUCCESS
**Governance qualification:** run `35494619736` — SUCCESS
**PHP Foundation qualification:** run `35494619251` — SUCCESS
**Product Owner merge authority:** comment `5748175502`; `product-owner-merge-authority` — SUCCESS
**Engineering envelope:** 6 paths — `7ccb0d202041e8968e7d526d6ee3fc7d76fd68048fc062993a8172b53a95e6be`
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`
**Previous canonical checkpoint:** Sprint212 reconciliation `ab99cbb0852d8570480f155ca452dda0794ca989`

> `c8998c00e19c177ac535921dbc0ef1fa96b06583` is the permanent canonical Sprint213 engineering evidence. The reconciliation squash must not replace it.

## Published cPanel no-SSH operator qualification kit

**Publication run:** `35494805706` — SUCCESS
**Actions artifact ID:** `10600262872`
**Artifact name:** `oneqay-cpanel-no-ssh-operator-kit-c8998c00e19c`
**Kit source commit:** `c8998c00e19c177ac535921dbc0ef1fa96b06583`
**Actions artifact size:** 50,102 bytes
**Actions artifact digest:** `sha256:834f5be1f3aee3d89396312eb15e348f8a85b6e89eb9a5a2fcdbeb223978cd5e`
**Inner operator ZIP SHA-256:** `de485d82c7960683a90c605f4d220b09f85b6cc2282e18f52c43b82006949011`
**Kit manifest SHA-256:** `d9a0300c24e260d3d87533af6808185a269471cfdeea0e69b2310188b30f3fde`
**Internal file count:** 20
**Expiry:** 2026-10-20T06:39:49Z

Independent downloaded-artifact verification confirmed the outer Actions digest, inner ZIP sidecar, ZIP integrity, 20-file manifest, absence of `apps/web` application bytes, absence of secret-bearing file shapes, `deployment_authority = NOT_GRANTED`, `migration27_execution = NOT_PERFORMED`, `selected_target = null`, and `producer_dispatch = NOT_PERFORMED`.

The kit includes the Sprint212 cPanel host inspector/candidate bridge plus Sprint208 authority tooling, Sprint207 planning, Sprint209 evidence qualification, schemas, non-secret templates, README, and checksums.

## Current application release binding

Sprint213 does not replace the governed application release. The cPanel kit remains bound to the Sprint211 application bundle:

**Application Actions artifact ID:** `10597712890`
**Release ID:** `durable-staging-e37300d5d1be`
**Application source:** `e37300d5d1be6727cdb5d818b6365c6429f2af9d`
**Application artifact SHA-256:** `faf6b4799648c0fe1d5ddcf4a55e0a39ba8bd0506e3e2ef3f4bb33f38495d079`
**Application manifest SHA-256:** `ef968562f76801e425d02c33d3ecfc13556f271f05bff1ff9441c5a147b588f0`
**Deployment handoff state:** `VALIDATED_FOR_EXTERNAL_DEPLOYMENT_NOT_AUTHORIZED`

## Delivered capability

- A cPanel operator without SSH no longer needs to collect raw repository files manually.
- Canonical main publishes one deterministic, secret-free ZIP carrying the operator-side qualification/governance chain.
- The ZIP is rebuilt twice and must be byte-identical before publication.
- The ZIP deliberately excludes application runtime bytes and granted authority.
- Real host facts, secrets, short-lived approval token, deployment execution, runtime evidence, and target selection remain external operational concerns.

## Operational NO-GO

Machine-readable state remains authoritative and unchanged: migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target selection `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; producer dispatch `NOT_PERFORMED`.

## Next position

Issue #856 remains the single operational handoff. For cPanel without SSH, retrieve the Sprint213 qualification kit plus the Sprint211 application bundle, upload the kit into a private File Manager workspace, run the one-shot Cron/PHP CLI qualification steps, and produce a truthful Sprint208 target candidate only if the real host passes. VM/VPS remains a valid alternative target class.

Open another engineering sprint only if real qualification or real execution proves a concrete source-side defect or missing capability.

Author by Lab | zefry
