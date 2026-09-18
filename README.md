# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, module-owned schema, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed engineering sprint is **Sprint182 — Governed Release Installation Preflight Bridge Foundation**.

- Canonical engineering commit: `ceb8fd7839a5077d59247b2cbdd2ba30559c4d25`
- Engineering PR: #791
- Final engineering head: `cc6eba43ccd9dfd316f652aaf133a7372a86380b`
- Exact-head pull-request qualification: 68/68 successful
- Engineering envelope: 6 paths, SHA-256 `4968d9fcf35b7a17d67b1909cfd1dad93f2b08b7d82b8c6d30be4dc09f067be3`
- Reconciliation envelope: 6 paths, SHA-256 `a78e4871e0852ac6c4f466b7f3278efde846a4ae84f5fe589123672e568aea35`

For the full project state, use [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md) as the canonical human-readable source of truth.

## Sprint182 — Governed artifact to installer preflight

Sprint182 connects the deterministic M7.5 release artifact to the Sprint181 installer-readiness contract through a shared trusted-build manifest builder.

The generated installer manifest is bound to the exact source commit and artifact identity, validated by `SecureInstallationReadiness`, rejects digest tampering, and is reproduced deterministically in the dedicated Sprint182 qualification.

No runtime host capability is fabricated. Host requirements remain policy until a target host can truthfully prove the required evidence.

## Product progression

oneQay now combines governed release artifacts, an executable artifact-to-installer evidence bridge, operator-visible installation preflight, atomic POS-ready merchant bootstrap, context-assisted first-party sign-in, and permission-filtered POS operations.

## Known pre-existing CI debt

The legacy M7.5 push-event workflow startup failure predates Sprint182 and is not claimed as remediated. Dedicated Sprint182 qualification is the active executable proof of the release-to-installer bridge. Sprint183 bounded discovery should assess this release-automation blocker first.

## Operational status remains intentionally gated

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; durable activation target `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview and Production `NOT_AUTHORIZED`; updater `INACTIVE`.

## Next engineering position

Sprint183 begins from the fully reconciled Sprint182 checkpoint and should prioritize the smallest material correction that restores canonical release automation without crossing operational activation boundaries.

Author by Lab | zefry
