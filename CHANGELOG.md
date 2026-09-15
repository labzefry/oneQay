# Changelog

## 2026-09-15 — Sprint166 closed

**Sprint166: POS product sales performance workspace**

- **Purpose / Why:** operational sales reporting was outlet-aggregate only; operators lacked product-level quantity/value visibility and an explicit view of how full-sale voids affected active product performance.
- **Objective / Gap:** `POS_PRODUCT_SALES_PERFORMANCE_WORKSPACE`.
- Added read-only exact tenant + organization + outlet product/currency performance derived from immutable completed sale lines.
- Added gross quantity/value, full-sale-void quantity/value, and net active quantity/value.
- CASH refund is intentionally not subtracted a second time because canonical CASH refund follows a full-sale void.
- Preserved historical currency/scale boundaries as separate buckets and retained inactive catalog products when immutable sales history exists.
- Added fail-closed handling for orphaned, malformed, overflowing, negative, and inconsistent aggregate evidence.
- Bounded output to 250 product/currency buckets with explicit truncation.
- Reused existing `pos.reporting.sales-summary.view`; no new permission identifier or provisioning was introduced.
- Added guarded `GET /pos/reporting/product-performance` / `pos.reporting.product-performance`, default-false `ONEQAY_POS_PRODUCT_SALES_PERFORMANCE_ENABLED`, child provider registration through the POS Operations Hub, and `Route::has()` discovery.
- Added responsive Vue/Inertia read-only UI and focused SQLite regression.
- Existing Sales Summary owner, global routes/providers, schema/migrations, sale/stock mutation, stocktake, arbitrary adjustment, supplier/purchasing, and transfer remained untouched.
- Exact engineering head `7f8de2dc2639938a02f5413a1a1edbbb0b7e925f` completed the surfaced PR-triggered matrix successfully.
- Sprint166 regression `34917171015`, M7.1 `34917171092`, Governance `34917171084`, PHP Foundation `34917171083`, POS successor regressions, and surfaced Final Shift Close historical controls succeeded.
- Repository-native exact-head Product Owner merge authority verified successfully.
- Engineering envelope: 12 paths; SHA-256 `3687acbd962b494a354c705c43eda5a71c1de426b5691d9b0f13b1620af2e228`.
- Engineering PR #750 squash merged at `e2758d0170a081953aaae11711ecc1ec3c0f8e78`.
- Canonical reconciliation envelope: six paths; SHA-256 `d887f8bcf393ad56a74d481e64aa3963c8678012f87025dcbf93ef8bde5ea4c8`.
- **Operational boundaries / NO-GO:** selected target remains `null`; migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; feature activation `INACTIVE`; deployment authority `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`.
- **Next position:** Sprint167 bounded discovery from canonical post-Sprint166; no objective or source envelope preselected.

## Recent material progression

- **Sprint165:** read-only inventory accountability over opening baseline + replenishment + full-sale-void restoration − completed sale quantity; engineering squash `dc6340c04ac710bd38966d897b27e23fd92c0a41`.
- **Sprint164:** positive-only inventory replenishment with immutable before/received/after evidence; engineering squash `d37ecdfa16d3f024de840871aa202af4fb5ee7d1`.
- **Sprint163:** operational cash-variance reconciliation workspace; engineering squash `a920e63c1a1d2623664b416422c3d5a471e389a6`.
- **Sprint162:** guarded POS operations hub; engineering squash `332bcff11b40307d350c7ce5b3a6c08913c4251c`.
- **Sprint156–Sprint161:** operational reporting, cashier, shift start, sale correction, immutable sale history/receipt detail, and catalog/opening-inventory setup.
- **Sprint88–Sprint155:** Final Shift Close source/readiness chain through migration #27 materialization, runtime readiness, selected-target controls, capability/dependency evidence foundations, permission-provisioning binding, and source-only activation planning. Operational execution did not occur.

This changelog records material canonical progress; detailed provenance remains in merged pull requests, Git history, workflows, tests, and machine-readable contracts.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

Author by Lab | zefry
