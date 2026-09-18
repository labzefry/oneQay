# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, module-owned schema, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed engineering sprint is **Sprint184 — Preboot Installation Configuration Preparation**.

- Canonical engineering commit: `dfb65d2782a580108d8ccd9a6f9203720fa036b7`
- Engineering PR: #797
- Final engineering head: `a22d98f18618be3ccf5ff8274ebf5528b33f01d7`
- Exact-head qualification: 72/72 successful
- Canonical main-push M7.5 run `35378584615`: SUCCESS
- Engineering envelope: 9 paths, SHA-256 `e2537851052c57b1ec3b7d2e99e1b5d0208fb3c54da207d5280682eb85c686a0`
- Reconciliation envelope: 8 paths, SHA-256 `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`

For the full project state, use [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md) as the canonical human-readable source of truth.

## Sprint184 — Secure pre-boot installation preparation

Sprint184 adds the first governed installation step that can operate before the Laravel runtime is configured.

The governed release now carries an operator-facing `install.php` bound to the exact immutable release. A private one-time authority gates configuration preparation, runtime values are validated, a fresh application key is generated, and only a private `.env.pending` file can be created. Replay is denied after pending or active configuration exists.

The prepared environment keeps persistence, Technical Preview, and update-control activation disabled. No migration execution or deployment activation is performed.

## Product progression

oneQay now has a continuous governed path from deterministic release artifact → installation-readiness evidence → operator-visible preflight → secure pre-boot runtime configuration preparation, while preserving merchant bootstrap/sign-in/POS progression and all activation boundaries.

## Operational status remains intentionally gated

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; durable activation target `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview and Production `NOT_AUTHORIZED`; updater `INACTIVE`.

## Next engineering position

Sprint185 begins from the fully reconciled Sprint184 checkpoint. Select the smallest material P0/P1 blocker that advances the installation/onboarding journey beyond pending configuration without implicitly granting operational activation.

Author by Lab | zefry