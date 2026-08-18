# Sprint 25 Preservation Supplement — M7.4A Technical Preview Interaction Regression

## Identity and purpose

- Product: `oneQay`
- Engineering entity: **Lab | zefry**
- Repository: `labzefry/oneQay`
- Canonical branch: `main`
- Sprint 25 entry-gate publication base: `3941d4e44cb66eb46728c5a5053f209e9b56935d`
- Active Sprint 25 source PR: `#178`
- Observed Sprint 25 source candidate: `c157f401f03c72a3d19eca5444542dc2a2409383`

GitHub remains the Single Source of Truth.

Attribution: **Lab | zefry**

## Preservation finding

Sprint 25 legitimately modifies `apps/web/routes/web.php` to add the one authorized ordinary policy-administration POST route. That path triggers the pre-existing M7.4A Technical Preview Interaction Regression.

The M7.4A workflow currently contains the historical assertion:

`test ! -d apps/web/database/migrations`

That assertion is no longer compatible with canonical repository state because the repository already contains the Product Owner-authorized forward-only migrations #1–#6 from prior published sprints.

The failure does **not** demonstrate a Sprint 25 schema change and does **not** demonstrate database access by Technical Preview code. Sprint 25 changes zero migration files, and the existing M7.4A checks against database mechanics inside `Application/Preview`, `Infrastructure/Preview`, and `Delivery/Preview` remain valid and mandatory.

## Authorized preservation correction

This supplement authorizes exactly one additional implementation path for Sprint 25:

`.github/workflows/m7-4a-technical-preview-interaction-regression.yml`

No other previously unauthorized path is added.

The Sprint 25 implementation envelope therefore expands from exactly **17 paths** to exactly **18 paths**.

## Required M7.4A correction

The M7.4A workflow may be changed only to reconcile its migration assertion with canonical state while preserving its Technical Preview security purpose.

The correction must:

1. remove the stale requirement that the migration directory not exist;
2. require that Sprint 25's PR diff contains **no changed path under `apps/web/database/migrations/`**;
3. require the canonical migration directory to contain exactly migrations #1–#6 and no others;
4. preserve the existing prohibition on database implementation inside Technical Preview Application/Infrastructure/Delivery source;
5. preserve the existing production-secret scan;
6. preserve dependency-lock checks;
7. preserve Application/Preview framework independence;
8. preserve M7.1, M7.2, M7.3, POS-core, and Technical Preview interaction regressions;
9. keep Technical Preview `NO_SCHEMA_CHANGE`;
10. keep Production `NO-GO / NOT AUTHORIZED`;
11. keep updater authority separate and unchanged.

The M7.4A workflow may also add the Sprint 25 delivery regression as an additional preservation proof if useful, but it may not remove or weaken any prior M7.4A assertion merely to obtain a green check.

## Sprint 25 envelope reconciliation

After this supplement is published, exact-envelope enforcement inside already-authorized Sprint 25 workflows may be updated from 17 paths to 18 paths and include the newly authorized M7.4A workflow path.

The original 17 authorized paths remain unchanged. The only additive path is:

18. `.github/workflows/m7-4a-technical-preview-interaction-regression.yml`

No migration, dependency, Preview source, updater source, or protected-control source becomes authorized by this supplement.

## Explicit exclusions

This supplement does not authorize:

- any migration change;
- migration #7;
- schema application to Technical Preview;
- database access from Technical Preview Application/Infrastructure/Delivery code;
- Sprint 23 bootstrap delivery;
- Sprint 24 protected-control delivery;
- emergency recovery;
- Production activation;
- updater activation;
- dependency changes;
- weakening CSRF/session/tenant-isolation controls;
- any new source path other than the single M7.4A workflow named above.

## Publication requirement

This documentation-only supplement must itself be published before the M7.4A workflow is modified.

Publication requires:

- exactly one changed documentation file;
- canonical base lineage preserved;
- required documentation CI green;
- exact-head Product Owner merge authority success;
- expected-head protected squash merge.

After publication, the active Sprint 25 source branch must incorporate the canonical supplement non-destructively, remain behind 0, and keep the supplement document out of the source PR diff against the updated canonical `main`.

## Declaration

Upon publication of this supplement:

- the Sprint 25 source envelope becomes exactly **18 paths**;
- M7.4A migration preservation may be reconciled to canonical migrations #1–#6;
- Sprint 25 still authorizes zero migration changes;
- Technical Preview remains `NO_SCHEMA_CHANGE`;
- Production remains `NO-GO / NOT AUTHORIZED`;
- updater remains `DISABLED / UNWIRED`.

GitHub remains the Single Source of Truth.

Attribution: **Lab | zefry**
