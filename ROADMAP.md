# oneQay Roadmap

**Roadmap checkpoint:** Sprint182 closed canonically
**Canonical engineering baseline:** `ceb8fd7839a5077d59247b2cbdd2ba30559c4d25`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint182 horizon

Sprint182 closed `GOVERNED_RELEASE_INSTALLATION_PREFLIGHT_BRIDGE_FOUNDATION`, converting the governed M7.5 release artifact into installer-facing evidence that the Sprint181 readiness engine can validate without inventing runtime target facts.

The shared trusted-build tool binds source and artifact identity, emits deterministic runtime/host/compatibility policy, validates through `SecureInstallationReadiness`, rejects digest tampering, and reproduces deterministically.

Engineering PR #791 squash merged at `ceb8fd7839a5077d59247b2cbdd2ba30559c4d25`. Engineering envelope: 6 paths, SHA-256 `4968d9fcf35b7a17d67b1909cfd1dad93f2b08b7d82b8c6d30be4dc09f067be3`.

Canonical reconciliation envelope: 6 paths, SHA-256 `a78e4871e0852ac6c4f466b7f3278efde846a4ae84f5fe589123672e568aea35`.

## Product progression through Sprint182

The product now combines governed release artifacts, trusted installer evidence, operator-visible installation preflight, and the secure merchant bootstrap/sign-in/POS progression delivered through Sprint180.

## Known release-automation blocker

The legacy M7.5 push-event workflow startup failure predates Sprint182 and remains separate canonical debt. Sprint182's dedicated workflow proves the bridge itself is executable; Sprint183 should first assess restoration of the canonical M7.5 automated release path as a material installation/release blocker.

## Operational boundary

Machine-readable operational state under `ops/final-shift-close/` remains authoritative. Selected durable target remains `null`; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; producer dispatch remains not performed; runtime allowlist remains Local/Test/CI; Final Shift Close remains inactive; deployment/Technical Preview/Production remain unauthorized; updater remains inactive.

## Sprint183 selection rule

Begin Sprint183 bounded discovery from fully reconciled Sprint182. Prefer the smallest end-to-end correction that restores governed release automation and closes a real installation journey blocker without granting operational activation.

No environment writes, migration execution, real permission provisioning, updater activation, deployment, Technical Preview, Production, durable-target selection, or producer dispatch is pre-authorized.

Author by Lab | zefry
