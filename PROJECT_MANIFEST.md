# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-15

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint174
**Objective:** `INSTALLATION_RELEASE_COMPATIBILITY_POLICY_READINESS`
**Canonical engineering commit:** `3937cfc56d2615262160c9592d204715eb80ec89`
**Engineering PR:** #773 — `Sprint174: add governed release compatibility policy readiness`
**Final engineering head:** `6db7c94a7eef99f5f1281f11c524a7d7e32d1968`
**Sprint174 regression:** `34996139756` — successful
**Governance Required Checks:** `34996139846` — successful
**PHP Foundation Regression:** `34996138931` — successful
**M7.1 Application Regression:** `34996140085` — successful
**Engineering envelope:** 3 paths — `1f4a5333d32f79e57ac51d8c9ea8b1a1b3b0342d58b6439fbfe127d3a235aa91`
**Canonical reconciliation envelope:** 6 paths — `c620402ddfb186f47fd0994f5751c3453811f08ca870400478442a1f9095075b`
**Previous canonical checkpoint:** Sprint173 reconciliation `34b2c4ddca636c3d36f4697ef4eb3f962cef211e`
**Next position:** Sprint175 bounded discovery from the fully reconciled Sprint174 checkpoint; no objective preselected.

> `3937cfc56d2615262160c9592d204715eb80ec89` is the canonical Sprint174 engineering evidence. The future Sprint174 reconciliation squash must not replace it as the canonical engineering commit.

## 1. Purpose / Why

Sprint174 closes a governed release-contract gap proven from `RELEASE.md`: installation readiness previously validated release identity, runtime requirements, artifact integrity, and migration classification, but did not fail closed on the full compatibility policy required to decide whether an immutable release is safe for an existing installation context.

## 2. What changed

- Reused canonical `App\Infrastructure\Installation\SecureInstallationReadiness`; no parallel release validator or installer owner was introduced.
- Governed Release Manifest v1 now requires a bounded `compatibility_policy`.
- Compatibility policy requires release version, build/provenance reference, supported-current-version range, deployment compatibility, rollback compatibility, public-bootstrap/layout compatibility, and release-notes reference.
- Release version and supported-current-version range use bounded semantic-version validation; inverted ranges fail closed.
- Build provenance and release-notes references use bounded safe-reference validation.
- Deployment and public-bootstrap/layout compatibility values use bounded uppercase policy tokens.
- Rollback compatibility is constrained to `NO_SCHEMA_CHANGE_ROLLBACK_SAFE`, consistent with the current no-schema-change release boundary.
- Missing or malformed policy makes both release-manifest readiness and artifact-integrity readiness fail closed without echoing untrusted policy values.
- Existing PHP/runtime, environment/key/debug/HTTPS, filesystem-write, governed artifact identity/integrity, deterministic database compatibility, and redaction controls remain preserved.
- No artifact download/extraction, network/database connection, environment/schema/configuration mutation, migration/seeder execution, credential or administrator provisioning, installer exposure, release publication, updater activation, deployment, Technical Preview, Production, durable-target selection, or producer dispatch was introduced.

## 3. Evidence / Qualification

- Canonical parent: `34b2c4ddca636c3d36f4697ef4eb3f962cef211e`.
- Exact engineering head: `6db7c94a7eef99f5f1281f11c524a7d7e32d1968`.
- Dedicated Sprint174 run `34996139756`: successful.
- Governance `34996139846`, PHP Foundation `34996138931`, and M7.1 `34996140085`: successful.
- Surfaced Sprint169–Sprint173, POS successor, and Final Shift Close historical controls completed successfully on the exact engineering head.
- Repository-native Product Owner merge authority verified on the exact engineering head.
- Engineering PR #773 squash merged at `3937cfc56d2615262160c9592d204715eb80ec89`.
- Engineering envelope: exactly 3 paths; SHA-256 `1f4a5333d32f79e57ac51d8c9ea8b1a1b3b0342d58b6439fbfe127d3a235aa91`.
- Canonical reconciliation envelope: exactly 6 paths; SHA-256 `c620402ddfb186f47fd0994f5751c3453811f08ca870400478442a1f9095075b`.

## 4. Operational boundaries / NO-GO

Machine-readable operational authority under `ops/final-shift-close/` remains authoritative and unchanged:

- durable activation target selection: `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- selected target: `null`;
- migration #27: `NOT_EXECUTED`;
- permission provisioning: `NONE`;
- real target-bound capability/dependency evidence: absent;
- producer dispatch: `NOT_PERFORMED`;
- runtime allowlist: Local/Test/CI only;
- feature activation: `INACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview / Production: `NOT_AUTHORIZED`;
- updater: `INACTIVE`.

Release compatibility-policy readiness grants no authority to publish, transport, extract, install, migrate, provision, expose, update, deploy, or activate any runtime.

## 5. Next position

Begin Sprint175 bounded discovery only after Sprint174 canonical reconciliation closes. Prioritize the smallest material non-duplicative P0/P1 business-completeness or production-readiness gap. Host/platform capability readiness remains a candidate from installer Step 2, but it is not preselected; live canonical evidence must prove the next objective.

## Documentation responsibility

`PROJECT_MANIFEST.md` is the canonical human-readable state. `README.md`, `CHANGELOG.md`, `TASKS.md`, and `ROADMAP.md` are reconciled summaries. Git history, merged PRs, workflows, tests, and machine-readable contracts preserve detailed provenance.

Author by Lab | zefry
