# Physical Schema Plan Representation and Change Classification Foundation

## Scope

Sprint 12 menyediakan foundation framework-agnostic untuk membandingkan dua `PhysicalMappingManifest` yang telah melewati published vendor compatibility boundary, menghasilkan fingerprint deterministik, merepresentasikan perubahan secara immutable, mengklasifikasikan risiko secara konservatif, dan menghasilkan safe JSON review artifact.

Foundation ini tidak menghasilkan executable SQL, tidak membuat migration artifact, tidak membuka database, tidak melakukan metadata introspection, tidak membuat production table, dan tidak menetapkan final tenant atau business schema.

## Published authorization basis

- Sprint 12 Entry Gate dipublikasikan melalui PR #52.
- Approved source head PR #52: `f9c74ce798ef1095e03164ad1424cefbdabc9474`.
- Approved dan published tree PR #52: `4f6d49c4dcf894f78f40764940da21b821ffb315`.
- Publication closure dipublikasikan melalui PR #53.
- Published commit PR #53: `15999b34fa223fe8e7fcc33cab7427de316f76c2`.
- Product Owner mengotorisasi source implementation Sprint 12 pada 2026-08-06 hanya untuk scope yang dipublikasikan melalui PR #52.

## Implementation boundary

- `src/SchemaPlanning/Foundation.php` memuat dependency Physical Mapping dan file module Sprint 12.
- `src/SchemaPlanning/ValueObjects.php` menyediakan stable error codes, correlation ID, fingerprints, disposition, risk, dan change-kind vocabulary.
- `src/SchemaPlanning/Contracts.php` menyediakan planner contract, immutable change representation, dan immutable plan representation.
- `src/SchemaPlanning/Planning.php` menyediakan canonicalizer dan deterministic planner.
- `tests/schema-planning.php` menggunakan synthetic manifests tanpa network atau database.

## Deterministic representation

Canonicalization mengurutkan entity berdasarkan logical identifier, attribute berdasarkan logical identifier, unique index berdasarkan physical index identifier, reference berdasarkan physical reference identifier, dan reference attribute map berdasarkan source logical identifier.

Primary-index attribute order tetap dipertahankan karena urutan index adalah bagian dari physical intent. Fingerprint menggunakan SHA-256 terhadap canonical JSON dengan stable key ordering. Stable change identifier diturunkan hanya dari kind, risk, safe entity/component identifiers, serta before/after fingerprints; runtime state dan correlation ID tidak memengaruhi change identifier.

## Change classification

| Change category | Disposition contribution | Risk |
| --- | --- | --- |
| Identical manifests | `NO_CHANGES` | None |
| Entity creation | `REVIEW_REQUIRED` | Additive review |
| Attribute addition | `REVIEW_REQUIRED` | Additive review |
| Unique index addition | `REVIEW_REQUIRED` | Additive review |
| Reference addition | `REVIEW_REQUIRED` | Additive review |
| Entity removal | `BLOCKED` | Destructive |
| Entity physical identifier change | `BLOCKED` | Rename or replacement risk |
| Attribute removal | `BLOCKED` | Destructive |
| Attribute logical scalar change | `BLOCKED` | Conversion risk |
| Attribute physical mapping change | `BLOCKED` | Conversion or compatibility risk |
| Primary index change | `BLOCKED` | Identity and lock risk |
| Unique index removal or mutation | `BLOCKED` | Integrity risk |
| Reference removal or mutation | `BLOCKED` | Referential integrity risk |
| Tenant scope or tenant key change | `BLOCKED` | Cross-tenant exposure risk |
| Vendor change | `BLOCKED` | Compatibility and migration semantics unknown |

Any `BLOCKED` change makes the whole plan `BLOCKED`. A non-empty plan containing only additive changes is `REVIEW_REQUIRED`. `REVIEW_REQUIRED` is not migration approval and does not authorize execution.

## Safe plan output

Plan output contains only:

- baseline and target SHA-256 fingerprints;
- `NO_CHANGES`, `REVIEW_REQUIRED`, or `BLOCKED` disposition;
- validated correlation ID;
- stable change identifiers;
- change kind and risk;
- safe entity and component identifiers;
- before and after fingerprints.

Plan output does not contain raw manifests, SQL, database endpoint, credential, production path, tenant data, arbitrary exception text, atau migration instructions.

## Validation evidence

Candidate implementation evidence:

```text
Schema Planning tests passed: 55 assertions.
```

PHP syntax validation lulus untuk:

- `src/SchemaPlanning/Foundation.php`;
- `src/SchemaPlanning/ValueObjects.php`;
- `src/SchemaPlanning/Contracts.php`;
- `src/SchemaPlanning/Planning.php`;
- `tests/schema-planning.php`.

Tests mencakup deterministic ordering, ordering-independent fingerprints, stable change IDs, no-change disposition, additive review classifications, destructive blocking classifications, physical/scalar changes, primary index, unique index, reference, tenant scope, tenant key, invalid manifest rejection, correlation ID rejection, plan-disposition invariants, duplicate change-ID rejection, fingerprint consistency, safe JSON, no SQL, no network, no database, dan vendor-change deny path.

Full historical `composer test` tetap menjadi pre-Ready gate. Exact repository snapshot tidak dapat dimaterialisasi ke local no-clone workspace pada sesi implementasi ini, dan repository tidak memiliki PHP test workflow terpisah. Tidak ada klaim bahwa seluruh historical foundation regressions telah dieksekusi pada candidate head.

## Governance preservation

- Canonical Phase 0 tetap `In Progress`.
- ADR-001 sampai ADR-007 tetap Proposed.
- GD-007 tetap Proposed.
- JRN-003 dan JRN-013 tetap unresolved.
- Final tenant data model tetap Not Started.
- Final business schema tetap Not Started.
- Production migration tetap Not Performed.
- Production database usage tetap None.
- Production table tetap None.
- Production readiness tetap NO-GO.
- POS dan business modules tetap Not Started.
- Deployment dan release tetap None.
- Sprint 13 tetap Not Authorized.

## Explicit exclusions

Sprint 12 tidak membuat executable SQL, DDL, DML, SQL renderer, migration executor, migration artifact, database adapter, database connection, production metadata inspection, production table, final schema, backfill behavior, online schema change behavior, rollback execution, deployment, release, POS, business module, workflow change, ruleset change, atau Sprint 13 work.

Attribution: Lab | zefry
