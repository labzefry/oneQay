# Changelog

## 2026-09-16 — Sprint176 closed

**Sprint176: Merchant Context Atomic Bootstrap Foundation**

- **Objective:** `MERCHANT_CONTEXT_ATOMIC_BOOTSTRAP_FOUNDATION`.
- Closed the proven merchant-context orchestration gap by composing existing durable graph, initial tenant administrator, and first control credential primitives.
- Added exact-tuple bootstrap authorization for tenant, identity, organization, outlet, device, and provisioning identity.
- Added a fresh-tenant guard; bootstrap fails closed for pre-existing tenant state.
- Graph creation, protected initial administrator provisioning, and first credential creation execute inside one outer durable transaction.
- Focused regression proves rollback of all merchant-context state when the downstream credential stage fails.
- Invalid password is rejected before mutation; Preview runtime is denied; plaintext password material is not persisted.
- Application bootstrap contracts remain framework-independent.
- No service-provider binding, public route, controller, UI, installer exposure, production runtime widening, migration execution, deployment, Technical Preview, Production, updater activation, durable-target selection, or producer dispatch was introduced.
- Exact engineering head `775343389659754d85f870eca55f808a0b28eea5` completed surfaced PR-triggered qualification successfully.
- Sprint176 regression `35043179125`, Governance `35043179113`, PHP Foundation `35043179183`, and M7.1 `35043178985` succeeded.
- Repository-native Product Owner merge authorization verified on the exact engineering head.
- Engineering envelope: 8 paths; SHA-256 `f1b48efc2a25623ae55c72b95407a19ef60b63f8dcb933f9d7f137f09a34b974`.
- Engineering PR #777 squash merged at `af2ed4db8e49c4a75f1e1b743986cc20f3e3b0ff` with verified signature.
- Canonical reconciliation envelope: 6 paths; SHA-256 `4cc815fdb1c6489ab34334a14033acfe1452b50874f48c9e867e6f6da3858b03`.
- **Operational NO-GO preserved:** migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; selected durable target `null`; feature activation `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`.
- **Next position:** Sprint177 bounded discovery from canonical post-Sprint176 with no objective preselected.

## Recent material progression

- **Sprint175:** governed host/platform requirements readiness; engineering squash `6357d883fe04ec0515f9d21c6adc0ee907787cde`.
- **Sprint174:** governed release compatibility-policy readiness; engineering squash `3937cfc56d2615262160c9592d204715eb80ec89`.
- **Sprint173:** governed release runtime-requirements readiness; engineering squash `933b06d0790834fb830ca9a55b443db69eca65f0`.
- **Sprint172:** database configuration compatibility readiness; engineering squash `636a07130650f2d3119d450f35cfcfaf2868898e`.
- **Sprint171:** governed release artifact installation readiness; engineering squash `cbf26a53c1a784b8e6eda65cc1d047c90ea590e8`.
- **Sprint170:** installation filesystem readiness; engineering squash `aa46ad0752f8ae2da145eb0c0f6324a3c5be6f25`.
- **Sprint169:** secure installation readiness foundation; engineering squash `2a53d9db9547340bd4d791b34fabb80ec600fc8b`.
- **Sprint162–Sprint168:** guarded POS operations, cash variance, replenishment, inventory accountability, product performance, and shift performance.
- **Sprint88–Sprint155:** Final Shift Close source/readiness chain; operational execution did not occur.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

Author by Lab | zefry
