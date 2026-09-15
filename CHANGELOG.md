# Changelog

## 2026-09-15 — Sprint167 closed

**Sprint167: POS active shift performance workspace**

- **Purpose / Why:** outlet-level reporting did not explain live exact-device shift performance, while cash-variance reconciliation focused only on closing-cash control.
- **Objective / Gap:** `POS_ACTIVE_SHIFT_PERFORMANCE_WORKSPACE`.
- Added read-only exact tenant + organization + outlet + device active-shift performance.
- Reads only the current exact-device `active_slot=1` shift; no active shift is an explicit valid empty state.
- Derives performance from immutable sales bound to the exact active shift and buckets by tender category + currency + scale.
- Added completed, full-void, active, and cash-refund counts plus gross, full-void, active-net, and cash-refund values.
- Active net equals gross minus full-sale void; CASH refund remains separate and is not subtracted twice.
- Legacy null-shift sale evidence on the same device at/after shift opening fails closed.
- Scope mismatch, malformed tender/evidence mode, inconsistent void/refund evidence, overflow, or more than 64 buckets fails closed.
- Reused existing `pos.reporting.sales-summary.view`; no new permission identifier or provisioning was introduced.
- Added guarded `GET /pos/reporting/active-shift-performance` / `pos.reporting.active-shift-performance`, default-false `ONEQAY_POS_ACTIVE_SHIFT_PERFORMANCE_ENABLED`, child provider registration through the POS Operations Hub, and `Route::has()` discovery.
- Added responsive Vue/Inertia read-only UI and focused SQLite regression.
- Shared permission registry, global routes/providers, schema/migrations, existing sale/shift/stock mutation owners, Final Shift Close operational state, deployment, and updater remained untouched.
- Exact engineering head `05047a7f04650b52c4035e4ca40171609fa8db10` completed the surfaced PR-triggered matrix successfully.
- Sprint167 regression `34919045197`, M7.1 `34919045660`, Governance `34919045650`, PHP Foundation `34919045018`, POS successor regressions, and surfaced Final Shift Close historical controls succeeded.
- Repository-native exact-head Product Owner merge authority verified successfully.
- Engineering envelope: 12 paths; SHA-256 `e9ca990e36ce896428bc749d19cf47025ce0525fd707f5e330d5d1cfbbfc8b48`.
- Engineering PR #752 squash merged at `dbdf0aa90a6d6127cf10113ec8c1092b8780504f`.
- Canonical reconciliation envelope: six paths; SHA-256 `2fd60a53d8996d41452cb10036d350a14a4d95a2943e897c233d29f3eac32b14`.
- **Operational boundaries / NO-GO:** selected target remains `null`; migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; feature activation `INACTIVE`; deployment authority `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`.
- **Next position:** Sprint168 bounded discovery from canonical post-Sprint167; no objective or source envelope preselected.

## Recent material progression

- **Sprint166:** read-only product/currency sales performance over immutable completed-sale and full-sale-void evidence; engineering squash `e2758d0170a081953aaae11711ecc1ec3c0f8e78`.
- **Sprint165:** read-only inventory accountability over opening baseline + replenishment + full-sale-void restoration − completed sale quantity; engineering squash `dc6340c04ac710bd38966d897b27e23fd92c0a41`.
- **Sprint164:** positive-only inventory replenishment with immutable before/received/after evidence; engineering squash `d37ecdfa16d3f024de840871aa202af4fb5ee7d1`.
- **Sprint163:** operational cash-variance reconciliation workspace; engineering squash `a920e63c1a1d2623664b416422c3d5a471e389a6`.
- **Sprint162:** guarded POS operations hub; engineering squash `332bcff11b40307d350c7ce5b3a6c08913c4251c`.
- **Sprint156–Sprint161:** operational reporting, cashier, shift start, sale correction, immutable sale history/receipt detail, and catalog/opening-inventory setup.
- **Sprint88–Sprint155:** Final Shift Close source/readiness chain through migration #27 materialization, runtime readiness, selected-target controls, capability/dependency evidence foundations, permission-provisioning binding, and source-only activation planning. Operational execution did not occur.

This changelog records material canonical progress; detailed provenance remains in merged pull requests, Git history, workflows, tests, and machine-readable contracts.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

Author by Lab | zefry
