# oneQay Roadmap

**Roadmap checkpoint:** Sprint176 closed canonically
**Canonical engineering baseline:** `af2ed4db8e49c4a75f1e1b743986cc20f3e3b0ff`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint176 horizon

Sprint176 closed `MERCHANT_CONTEXT_ATOMIC_BOOTSTRAP_FOUNDATION`, addressing the proven orchestration gap between already-existing secure durable primitives needed to establish a foundational merchant context.

The bounded bootstrap foundation now requires exact preauthorization of the tenant/identity/organization/outlet/device/provisioning tuple and a fresh tenant. It atomically materializes the context graph, protected initial tenant administrator, and first control credential. A downstream credential failure rolls back the entire merchant bootstrap state.

Engineering PR #777 squash merged at `af2ed4db8e49c4a75f1e1b743986cc20f3e3b0ff`. Engineering envelope: 8 paths, SHA-256 `f1b48efc2a25623ae55c72b95407a19ef60b63f8dcb933f9d7f137f09a34b974`.

Canonical reconciliation envelope: 6 paths, SHA-256 `4cc815fdb1c6489ab34334a14033acfe1452b50874f48c9e867e6f6da3858b03`.

## Product progression through Sprint176

The product now combines tenant/security/API/POS operational foundations, governed installation-readiness controls, and an atomic merchant-context bootstrap foundation. The bootstrap remains source-only and deliberately unexposed: no provider binding, public route, controller, UI, real merchant provisioning, runtime widening, or operational activation is part of Sprint176.

## Operational boundary

Machine-readable operational state under `ops/final-shift-close/` remains authoritative. Selected durable target remains `null`; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; producer dispatch remains not performed; runtime allowlist remains Local/Test/CI; Final Shift Close remains inactive; deployment/Technical Preview/Production remain unauthorized; updater remains inactive.

## Sprint177 selection rule

Begin Sprint177 bounded discovery from fully reconciled Sprint176. Ask what now blocks a real merchant end-to-end after the atomic merchant context can be formed in a guarded source-only path. Prioritize the smallest material non-duplicative P0/P1 gap. Business metadata, delivery/onboarding surface, or another blocker may only be selected if live canonical evidence proves it.

No real merchant provisioning, public onboarding exposure, runtime widening, migration execution, updater activation, deployment, Technical Preview, Production, durable-target selection, or producer dispatch is pre-authorized.

Author by Lab | zefry
