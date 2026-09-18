# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-18

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint180
**Objective:** `MERCHANT_INITIAL_CONTEXT_ASSISTED_SIGN_IN_FOUNDATION`
**Canonical engineering commit:** `8451470f2eb37b45df53ac0d6f30e73c1e9cb5ad`
**Engineering PR:** #787 — `Sprint180: add initial merchant context-assisted sign-in`
**Final engineering head:** `e4844d47cc99c8f655c09e358b224f45c29513e1`
**Exact-head surfaced qualification:** 62/62 successful
**Engineering envelope:** 4 paths — `2c122014511daaeb8ca1d5cbd2ee4bb184733ed1154e08c8e0000f3b758c9c6c`
**Canonical reconciliation envelope:** 6 paths — `eb9b36e214455c09714f9d03f034e85aec06506d43b64be314b2b95b00b4f41b`
**Previous canonical checkpoint:** Sprint179 reconciliation `9bbcd1e2b94dc24654fe397416b74f3ae3a249b1`
**Next position:** Sprint181 bounded discovery from the fully reconciled Sprint180 checkpoint.

> `8451470f2eb37b45df53ac0d6f30e73c1e9cb5ad` is the canonical Sprint180 engineering evidence. The Sprint180 reconciliation squash must not replace it as the canonical engineering commit.

## 1. Purpose / Why

Sprint180 removes the proven usability barrier where the initial merchant had to copy opaque internal tenant, identity, organization, outlet, and device identifiers into the login form even though the exact installation context was already available server-side.

## 2. What changed

- Reused the existing first-party login controller, route, MFA flow, and session-authority contract unchanged.
- Built a server-assisted initial login context from the existing exact merchant installation grant.
- Serialized only `tenant_id`, `identity_id`, `organization_id`, `outlet_id`, and `device_id` into the guarded merchant-entry document.
- Explicitly excluded `provisioning_id` from browser serialization.
- Merchant entry remains enabled only for Local/Test/CI with persistence, session control, and a complete exact context.
- Removed manual editing of tenant, identity, organization, outlet, and device IDs from the initial merchant sign-in UI.
- Initial merchant sign-in now requires password followed by existing MFA semantics when applicable.
- Invalid, incomplete, or malformed server-assisted context fails closed to the Foundation posture.
- Public registration, authentication architecture replacement, runtime widening, and operational activation were not introduced.

## 3. Evidence / Qualification

- Canonical parent before engineering: `9bbcd1e2b94dc24654fe397416b74f3ae3a249b1`.
- Exact engineering head: `e4844d47cc99c8f655c09e358b224f45c29513e1`.
- All 62 surfaced PR-triggered workflow runs completed successfully on the exact engineering head.
- Sprint178 and Sprint179 predecessor preservation regressions remained successful.
- Governance, PHP Foundation, M7.1, and dedicated Sprint180 qualification succeeded.
- Repository-native Product Owner merge authority succeeded for the exact engineering head.
- Engineering PR #787 squash merged at `8451470f2eb37b45df53ac0d6f30e73c1e9cb5ad`.
- Engineering envelope: exactly 4 paths; SHA-256 `2c122014511daaeb8ca1d5cbd2ee4bb184733ed1154e08c8e0000f3b758c9c6c`.
- Canonical reconciliation envelope: exactly 6 paths; SHA-256 `eb9b36e214455c09714f9d03f034e85aec06506d43b64be314b2b95b00b4f41b`.

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

Sprint180 grants no authority to provision a real merchant, widen runtime authorization, execute migrations, deploy, activate Technical Preview/Production, select a durable target, dispatch producers, or perform operational permission provisioning.

## 5. Next position

Begin Sprint181 bounded discovery from the fully reconciled Sprint180 state. Identify the smallest material P0/P1 blocker remaining in the real merchant end-to-end journey from live canonical evidence; do not preselect the objective or widen operational authority.

## Documentation responsibility

`PROJECT_MANIFEST.md` is the canonical human-readable state. `README.md`, `CHANGELOG.md`, `TASKS.md`, and `ROADMAP.md` are reconciled summaries. Git history, merged PRs, workflows, tests, and machine-readable contracts preserve detailed provenance.

Author by Lab | zefry
