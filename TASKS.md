# oneQay Tasks

**Current canonical engineering checkpoint:** Sprint176 closed
**Canonical engineering commit:** `af2ed4db8e49c4a75f1e1b743986cc20f3e3b0ff`
**Latest engineering PR:** #777 — `Sprint176: add atomic merchant context bootstrap foundation`
**Canonical status authority:** `PROJECT_MANIFEST.md`

## Completed Sprint176 state

Sprint176 materialized `MERCHANT_CONTEXT_ATOMIC_BOOTSTRAP_FOUNDATION` as a bounded P0/P1 merchant-completeness foundation.

- [x] Existing durable tenant/identity/organization/outlet/device persistence reused.
- [x] Existing initial tenant administrator provisioning reused.
- [x] Existing first control principal credential bootstrap reused.
- [x] Exact tenant/identity/organization/outlet/device/provisioning tuple authorization added.
- [x] Fresh-tenant state guard added.
- [x] Graph + protected administrator + credential creation wrapped in one outer durable transaction.
- [x] Downstream credential failure proven to roll back the complete bootstrap state.
- [x] Invalid-password denial proven before mutation.
- [x] Preview-runtime denial preserved; Local/Test/CI remains the only runtime allowlist.
- [x] Plaintext password persistence prohibited and regression-tested.
- [x] No provider, route, controller, UI, config activation, deployment, or operational exposure introduced.
- [x] Dedicated exact-envelope workflow successful.
- [x] Full surfaced CI matrix successful.
- [x] Repository-native Product Owner merge authority verified.
- [x] Engineering PR #777 squash merged at `af2ed4db8e49c4a75f1e1b743986cc20f3e3b0ff`.

Engineering envelope: 8 paths; SHA-256 `f1b48efc2a25623ae55c72b95407a19ef60b63f8dcb933f9d7f137f09a34b974`.

Canonical reconciliation envelope: 6 paths; SHA-256 `4cc815fdb1c6489ab34334a14033acfe1452b50874f48c9e867e6f6da3858b03`.

## Preserved lifecycle state

Machine-readable operational state under `ops/final-shift-close/` remains authoritative and unchanged: selected target `null`; migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; real target-bound capability/dependency evidence absent; producer dispatch not performed; feature activation `INACTIVE`; deployment authority `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`.

## Next engineering position

Begin **Sprint177 bounded discovery** only from fully reconciled Sprint176. Ask what materially blocks a real merchant end-to-end now that atomic merchant-context creation exists. Prioritize the smallest non-duplicative P0/P1 business gap proven by live repository evidence. Do not pre-authorize public onboarding, real merchant provisioning, runtime widening, migrations, deployment, Technical Preview, Production, updater activation, durable-target selection, or producer dispatch.

Author by Lab | zefry
