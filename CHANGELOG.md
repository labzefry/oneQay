# Changelog

## 2026-09-15 — Sprint168 closed

**Sprint168: POS shift history performance workspace**

- **Purpose / Why:** Sprint167 closed live exact-device shift visibility, but operators still lacked durable read-only historical visibility across closed outlet shifts and devices.
- **Objective / Gap:** `POS_SHIFT_HISTORY_PERFORMANCE_WORKSPACE`.
- Added exact tenant + organization + outlet closed-shift history with deliberate same-outlet cross-device scope.
- Eligible history requires `active_slot IS NULL` and exactly one canonical Final Shift Close evidence row.
- Lists the newest 50 closed shifts; selected shift defaults to newest when omitted.
- Validates opener/closer/device/open-close/cutoff identity and expected/observed/variance/review arithmetic.
- Derives selected-shift completed-sale, full-sale-void, and CASH-refund performance by tender + currency + scale.
- Active net value equals gross minus full-sale void; CASH refund remains separate and is not subtracted twice.
- Legacy null-shift sales inside the shift window fail closed.
- Foreign-scope/outside-window evidence, malformed tender/evidence, inconsistent void/refund evidence, numeric overflow, more than 64 buckets, or missing close evidence fail closed.
- Reused existing `pos.reporting.sales-summary.view`; no new permission or provisioning was introduced.
- Added guarded `GET /pos/reporting/shift-history/{shiftId?}` / `pos.reporting.shift-history` behind default-false `ONEQAY_POS_SHIFT_HISTORY_PERFORMANCE_ENABLED`.
- Delivery remains Local/Test/CI + persistence + operational-reporting + exact-session gated and depends on existing `ONEQAY_POS_SHIFT_CLOSE_ENABLED` source/runtime readiness.
- POS Operations Hub discovery remains permission + `Route::has()` guarded.
- Added responsive Vue/Inertia read-only UI and focused SQLite regression.
- Shared permission registry, global routes/providers, schema/migrations, existing mutation owners, Final Shift Close provider, deployment, and updater remained untouched.
- Exact engineering head `05652fc5b11fdad7bb11e9c992f79d6f5436e29d` completed the surfaced PR-triggered matrix successfully.
- Sprint168 regression `34921162121`, M7.1 `34921161920`, Governance `34921162056`, PHP Foundation `34921162098`, POS successor regressions, and surfaced Final Shift Close historical controls succeeded.
- Repository-native exact-head Product Owner merge authority verified successfully.
- Engineering envelope: 12 paths; SHA-256 `d42a5a7d8568766e524ff662107ffdb35452d4f9ac00f26cdc8e419d36bc3c25`.
- Engineering PR #754 squash merged at `d3703a6b18f478acd812e7892c3871ce3aaf7bfa`.
- Canonical reconciliation envelope: six paths; SHA-256 `350fd16d111bb398c34a63e1519cc02206bcc373378a9366768c198ccf8146b5`.
- **Operational boundaries / NO-GO:** selected target remains `null`; migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; feature activation `INACTIVE`; deployment authority `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`.
- **Next position:** Sprint169 bounded discovery from canonical post-Sprint168; no objective or source envelope preselected.

## Recent material progression

- **Sprint167:** read-only current exact-device active-shift performance; engineering squash `dbdf0aa90a6d6127cf10113ec8c1092b8780504f`.
- **Sprint166:** read-only product/currency sales performance; engineering squash `e2758d0170a081953aaae11711ecc1ec3c0f8e78`.
- **Sprint165:** read-only inventory accountability; engineering squash `dc6340c04ac710bd38966d897b27e23fd92c0a41`.
- **Sprint164:** positive-only inventory replenishment; engineering squash `d37ecdfa16d3f024de840871aa202af4fb5ee7d1`.
- **Sprint163:** operational cash-variance reconciliation workspace; engineering squash `a920e63c1a1d2623664b416422c3d5a471e389a6`.
- **Sprint162:** guarded POS operations hub; engineering squash `332bcff11b40307d350c7ce5b3a6c08913c4251c`.
- **Sprint156–Sprint161:** operational reporting, cashier, shift start, sale correction, immutable sale history/receipt detail, and catalog/opening-inventory setup.
- **Sprint88–Sprint155:** Final Shift Close source/readiness chain; operational execution did not occur.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

Author by Lab | zefry
