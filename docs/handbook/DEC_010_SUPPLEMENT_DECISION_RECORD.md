# DEC-010 Supplement — Dependency Cost Baseline, Open-Source Preference, and Visualization Technology Direction

- Status: Approved / Substantive Decision Complete
- Decision owner: Product Owner `labzefry`
- Product: oneQay
- Developer & Product Engineering Entity: Lab | zefry
- Repository: `labzefry/oneQay`
- Decision baseline: `4a56cad359ab5e4e59b7a5b3e342869339c8f6a8`
- Verified baseline tree: `205bcbdbb746e9959a3d18b90b5992c303033192`
- Parent canonical decision: DEC-010 — Product License and Third-Party Notice Policy
- Relevant frontend decision: DEC-003 — Frontend / PWA Stack
- Gap provenance: DEC-012 separate post-DEC-012 gaps
- Scope: product/dependency-selection policy only
- Package/dependency adoption authority: **NOT GRANTED**
- Implementation authority: **NOT GRANTED**

GitHub adalah Single Source of Truth. Baseline di atas adalah provenance keputusan substantif dan bukan klaim live-state permanen. Fresh verification tetap wajib sebelum mutation atau lifecycle transition berikutnya.

## Decision

DEC-010 Supplement establishes four bounded directions.

### D10S-01 — Core license-cost baseline

Approved: **ZERO MANDATORY COMMERCIAL SOFTWARE-LICENSE COST — CORE BASELINE**.

The default oneQay core technology baseline must retain a credible path to build, test, and operate the core product without requiring mandatory recurring commercial software-license fees.

This policy **does not mean zero total cost**. Hosting/infrastructure, network/domain services, managed services, security/compliance, professional services, qualified legal review, optional commercial support, optional enterprise tooling, external SaaS/provider usage, and personnel/staffing/operations may still incur cost.

Optional commercial or proprietary technology remains possible through separate bounded authority. It must not silently become a mandatory core dependency contrary to this baseline.

A material future exception requires separate Product Owner authority.

### D10S-02 — Free / Open-Source First

Approved: **FREE / OPEN-SOURCE FIRST PREFERENCE — NOT FOSS-ONLY**.

When materially equivalent technologies satisfy functional, security, reliability, maintainability, legal/compliance, and operational requirements, oneQay should prefer technology that:

- avoids mandatory recurring commercial software-license fees;
- has known provenance;
- has acceptable licensing;
- supports sustainable maintenance;
- reduces unnecessary vendor lock-in.

Open-source status alone does not override DEC-010. Existing DEC-010 license classes and pre-adoption controls remain binding.

Commercial/proprietary exceptions remain possible where separately evidenced, including for material security, reliability, legal/compliance, enterprise capability, maintainability, absence of a credible alternative, or materially better lifecycle total cost of ownership.

This supplement does not create an absolute FOSS-only prohibition.

### D10S-03 — Apache ECharts direction

Approved: **APACHE ECHARTS — DEFAULT WEB/PWA VISUALIZATION TECHNOLOGY CANDIDATE / APPROVED TECHNOLOGY DIRECTION**.

Intended future use may include:

- dashboards;
- operational analytics;
- business-intelligence visualization;
- reporting visualization;
- responsive Web/PWA charts.

This direction is compatible with DEC-003 Vue 3, Composition API, TypeScript-first, Inertia, Vite, and accessible/responsive/component-oriented Web/PWA UI direction.

Apache ECharts is not declared mandatory, exclusive, permanently selected regardless of future evidence, automatically installed, or an exact-version selection.

No exact ECharts version is approved by this supplement. Upstream/version facts observed during readiness are provenance only and are not a package pin. Future adoption must fresh-verify the exact package/version/upstream state.

Approval of Apache ECharts does not automatically approve:

- `vue-echarts`;
- another Vue wrapper;
- ECharts GL;
- extensions;
- map/provider integrations;
- themes/plugins;
- CDN delivery;
- SSR implementation;
- other ECharts ecosystem packages.

Each additional component remains independently gated.

### D10S-04 — Dependency adoption boundary

Approved:

**TECHNOLOGY POLICY APPROVAL ≠ DEPENDENCY ADOPTION AUTHORITY ≠ IMPLEMENTATION AUTHORITY**.

Before an actual frontend dependency is adopted, a separately authorized exact-state evaluation must identify and verify where applicable:

1. exact package name;
2. exact package version;
3. canonical registry/upstream source;
4. exact license;
5. direct dependencies;
6. transitive dependencies;
7. provenance;
8. security status;
9. relevant upstream security guidance;
10. maintenance/release health;
11. required notices and attribution;
12. `THIRD_PARTY_NOTICES` impact;
13. SBOM impact;
14. browser/runtime compatibility;
15. TypeScript/frontend compatibility;
16. bundle-size impact;
17. tree-shaking strategy where applicable;
18. renderer strategy where applicable;
19. measured performance;
20. accessibility acceptance;
21. CSP/security implications;
22. safe handling of untrusted data/configuration;
23. rollback/removal strategy;
24. viable replacement path.

For future Apache ECharts adoption, implementation review must also consider bounded/tree-shakable imports where practical, Canvas versus SVG according to measured workload, DEC-003 frontend performance budgets, sanitization or prohibition of untrusted raw HTML/URL use, unsafe chart-configuration boundaries, regex/ReDoS risk where applicable, and separation of tenant-controlled chart data from tenant-controlled executable or security-sensitive configuration.

## Preserved DEC-010 policy

DEC-010 remains canonical owner of:

- proprietary oneQay product/source licensing;
- dependency-license classification;
- exact-version pre-adoption review;
- provenance;
- direct/transitive review;
- license compatibility;
- notice/attribution governance;
- SBOM relationship;
- commercial-right verification;
- dependency exception governance;
- qualified legal-review boundaries.

oneQay product/source remains **PROPRIETARY / ALL RIGHTS RESERVED**.

This supplement does not convert oneQay into an open-source product, establish open-core, establish dual licensing, alter `LICENSE` legal text, or provide final lawyer-grade legal terms.

## Preserved DEC-003 policy

DEC-003 remains canonical owner of the primary Web/PWA frontend architecture:

- Vue 3;
- Composition API;
- TypeScript-first;
- Inertia;
- Vite;
- Modern Monolith Web Delivery + Explicit API Boundaries;
- accessible/responsive frontend direction;
- frontend performance-budget direction.

Visualization libraries remain Presentation/UI concerns. ECharts, Vue, browser APIs, and UI libraries must not become dependencies of Domain or Application layers. Business invariants and authoritative business state remain server-side.

## Current package-state boundary

This supplement adopts no package and selects no package manager.

It does not authorize creation of `package.json`, a frontend lockfile, `npm install`, `pnpm add`, `yarn add`, `echarts`, `vue-echarts`, Composer dependency changes, or lockfile changes.

## Explicit non-scope

This supplement does not authorize:

- dependency/package installation or adoption;
- exact ECharts version selection;
- package-manager selection;
- frontend/chart/dashboard implementation;
- CSS/UI framework selection;
- API implementation;
- schema/SQL/DDL/migration work;
- infrastructure provisioning;
- deployment;
- release;
- Production;
- Phase 0 exit;
- Sprint 14.

## Program state preserved

- Phase 0: **IN PROGRESS**.
- Sprint 12: **PUBLISHED**.
- Sprint 13: **PUBLISHED**.
- Sprint 14: **NOT AUTHORIZED**.
- Final/business/production implementation: **BLOCKED / SEPARATELY GATED**.
- Deployment: **NOT AUTHORIZED**.
- Release: **NOT AUTHORIZED**.
- Production: **NOT AUTHORIZED**.
- Production readiness: **NO-GO**.
- DEC-010: **PUBLISHED / COMPLETE**.
- DEC-011: **PUBLISHED / COMPLETE**.
- DEC-012: **PUBLISHED / COMPLETE**.

## Authority boundary

DEC-010 Supplement is an approved product/dependency-selection policy only. It does not itself authorize publication beyond the governed documentation lifecycle, package adoption, implementation, infrastructure, deployment, release, Production, Phase 0 exit, or Sprint 14.

Attribution: **Lab | zefry**
