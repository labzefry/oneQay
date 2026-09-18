# oneQay Tasks

**Current canonical engineering checkpoint:** Sprint177 engineering closed  
**Canonical engineering commit:** `6752af1eb957993a6080206d9a40f7163bd24be6`  
**Latest engineering PR:** #781 — `Sprint177: add guarded merchant context bootstrap delivery`  
**Canonical status authority:** `PROJECT_MANIFEST.md`

## Completed Sprint177 engineering state

Sprint177 materialized `MERCHANT_CONTEXT_GUARDED_BOOTSTRAP_DELIVERY_FOUNDATION`.

- [x] Dedicated auto-discovered merchant-context bootstrap console command added.
- [x] Exact merchant tuple sourced only from configured preauthorization material.
- [x] Command exposes no tenant/identity/organization/outlet/device/provisioning tuple arguments.
- [x] Merchant bootstrap enablement remains default-false.
- [x] First-control credential bootstrap and persistence remain independent required gates.
- [x] Runtime remains Local/Test/CI only.
- [x] Hidden password and confirmation inputs used.
- [x] Disabled, Production-like runtime, persistence-disabled, mismatch, malformed-grant, and replay paths fail closed.
- [x] Success and failure output remain sanitized and redact merchant tuple/secret material.
- [x] Sprint176 atomic/fresh-tenant/rollback semantics preserved.
- [x] No HTTP route, controller, UI onboarding, deployment, or operational activation introduced.
- [x] Exact engineering head completed 59/59 surfaced PR-triggered workflows successfully.
- [x] Repository-native exact-head Product Owner merge authority succeeded.
- [x] Engineering PR #781 squash merged at `6752af1eb957993a6080206d9a40f7163bd24be6`.

Engineering envelope: 4 paths; SHA-256 `de509f025c78e8f2ed7d0335b81b6f423deb621c3f1d6bc54bba4a312635d872`.

Canonical reconciliation envelope: 6 paths; SHA-256 `cf8df3335c6b40d148a86b3b9ad7a565400b727a88c62b26869f8a4a5481e78c`.

## Preserved lifecycle state

Machine-readable operational state under `ops/final-shift-close/` remains authoritative and unchanged: selected target `null`; migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; producer dispatch not performed; feature activation `INACTIVE`; deployment authority `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; runtime allowlist Local/Test/CI only.

## Remaining Sprint177 closure tasks

- [ ] Qualify exact six-path canonical reconciliation envelope.
- [ ] Complete reconciliation PR CI.
- [ ] Verify reconciliation exact-head Product Owner merge authority.
- [ ] Run final race against unchanged main/base/head.
- [ ] Squash merge reconciliation PR.
- [ ] Verify final canonical main and mark Sprint177 CLOSED.

## Next engineering position

After Sprint177 closes canonically, begin **Sprint178 bounded discovery** from the reconciled Sprint177 state. Select only the smallest non-duplicative P0/P1 blocker proven by live repository evidence. No public onboarding, real merchant provisioning, runtime widening, migrations, deployment, Technical Preview, Production, updater activation, durable-target selection, or producer dispatch is pre-authorized.

Author by Lab | zefry
