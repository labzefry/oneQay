# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management  
**Repository owner / attribution:** Lab | zefry  
**Default branch:** `main`  
**Status date:** 2026-09-18

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint177  
**Objective:** `MERCHANT_CONTEXT_GUARDED_BOOTSTRAP_DELIVERY_FOUNDATION`  
**Canonical engineering commit:** `6752af1eb957993a6080206d9a40f7163bd24be6`  
**Engineering PR:** #781 — `Sprint177: add guarded merchant context bootstrap delivery`  
**Final engineering head:** `5a1b790414e2616ad6337224dc06392ee1154ec2`  
**Exact-head surfaced qualification:** 59/59 successful  
**Engineering envelope:** 4 paths — `de509f025c78e8f2ed7d0335b81b6f423deb621c3f1d6bc54bba4a312635d872`  
**Canonical reconciliation envelope:** 6 paths — `cf8df3335c6b40d148a86b3b9ad7a565400b727a88c62b26869f8a4a5481e78c`  
**Previous canonical checkpoint:** Sprint176 reconciliation `9330e223a85a2e258fcdbb5001a40d8520e99472`  
**Next position:** Sprint178 bounded discovery only after Sprint177 canonical reconciliation closes.

> `6752af1eb957993a6080206d9a40f7163bd24be6` is the canonical Sprint177 engineering evidence. The later Sprint177 reconciliation squash must not replace it as the canonical engineering commit.

## 1. Purpose / Why

Sprint177 closes the proven delivery gap after Sprint176 atomic merchant-context bootstrap by providing a deliberately guarded console execution surface without creating public onboarding or operational authority.

## 2. What changed

- Added the auto-discovered `oneqay:merchant-context:bootstrap` console command.
- Added dedicated default-deny merchant bootstrap configuration and exact preauthorized grant material.
- The command accepts no tenant, identity, organization, outlet, device, or provisioning tuple arguments.
- Merchant bootstrap, first-control credential bootstrap, and persistence must each be explicitly armed.
- Runtime remains restricted to Local/Test/CI.
- Password and confirmation use hidden console inputs; plaintext secrets and merchant tuple material are not emitted.
- Existing Sprint176 fresh-tenant, atomic transaction, protected administrator, credential, and rollback semantics remain authoritative.
- Disabled delivery, Production-like runtime, disabled persistence, password mismatch, malformed grant, replay, and output-redaction behavior are regression-tested.
- No HTTP route, controller, UI onboarding surface, installer exposure, production runtime widening, migration execution, deployment, updater activation, durable-target selection, or producer dispatch was introduced.

## 3. Evidence / Qualification

- Canonical parent before engineering: `9330e223a85a2e258fcdbb5001a40d8520e99472`.
- Exact engineering head: `5a1b790414e2616ad6337224dc06392ee1154ec2`.
- All 59 surfaced PR-triggered workflow runs completed successfully on the exact engineering head.
- Repository-native Product Owner merge authority succeeded for the exact engineering head.
- Engineering PR #781 squash merged at `6752af1eb957993a6080206d9a40f7163bd24be6`.
- Engineering envelope: exactly 4 paths; SHA-256 `de509f025c78e8f2ed7d0335b81b6f423deb621c3f1d6bc54bba4a312635d872`.
- Canonical reconciliation envelope: exactly 6 paths; SHA-256 `cf8df3335c6b40d148a86b3b9ad7a565400b727a88c62b26869f8a4a5481e78c`.

## 4. Operational boundaries / NO-GO

Machine-readable operational authority under `ops/final-shift-close/` remains authoritative and unchanged:

- durable activation target selection: `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- selected target: `null`;
- migration #27: `NOT_EXECUTED`;
- permission provisioning: `NONE`;
- producer dispatch: `NOT_PERFORMED`;
- runtime allowlist: Local/Test/CI only;
- feature activation: `INACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview / Production: `NOT_AUTHORIZED`;
- updater: `INACTIVE`.

Sprint177 grants no authority to provision a real merchant, expose public onboarding, widen runtime authorization, execute migrations, deploy, activate Technical Preview/Production, select a durable target, or dispatch producers.

## 5. Next position

After canonical reconciliation closes, begin Sprint178 bounded discovery from Sprint177. Identify the smallest material P0/P1 blocker remaining in the real merchant end-to-end journey from live canonical evidence; do not preselect an objective or widen operational authority.

## Documentation responsibility

`PROJECT_MANIFEST.md` is the canonical human-readable state. `README.md`, `CHANGELOG.md`, `TASKS.md`, and `ROADMAP.md` are reconciled summaries. Git history, merged PRs, workflows, tests, and machine-readable contracts preserve detailed provenance.

Author by Lab | zefry
