# oneQay Roadmap

**Roadmap checkpoint:** Sprint175 closed canonically
**Canonical engineering baseline:** `6357d883fe04ec0515f9d21c6adc0ee907787cde`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint175 horizon

Sprint175 closed `INSTALLATION_GOVERNED_HOST_PLATFORM_REQUIREMENTS_READINESS`, moving installer Step 2 host/platform requirements into governed release metadata so target readiness cannot silently depend on hardcoded installer assumptions.

The governed release manifest now defines supported OS families and web-server interfaces, minimum memory/execution-time/free-disk requirements, and the canonical capability set for HTTPS, DNS, time synchronization, outbound allowlisting, scheduler, archive support, temporary-directory readiness, and required tools. Deterministic observed target facts fail closed when incomplete, malformed, unsupported, or insufficient.

Engineering PR #775 squash merged at `6357d883fe04ec0515f9d21c6adc0ee907787cde`. Engineering envelope: 3 paths, SHA-256 `3b06f39902fda4e43096b622cffad62ad4308652f760a300c44d5a54616ef8e0`.

Canonical reconciliation envelope: 6 paths, SHA-256 `be033502c6e72d211415751966e6dc48e3453ce6fae6003d57b3da807cee1460`.

## Product progression through Sprint175

The product combines tenant/security/API/POS operational foundations with a canonical installation-readiness owner that now qualifies governed release identity, PHP/runtime requirements, host/platform requirements, release compatibility policy, canonical configuration, filesystem write surfaces, immutable artifact identity/integrity, and deterministic database compatibility prerequisites. These checks remain intentionally separate from real host probing, artifact transport/extraction, database execution, schema mutation, migrations, administrator bootstrap, environment mutation, installer exposure, updater activation, and deployment.

## Operational boundary

Machine-readable operational state under `ops/final-shift-close/` remains authoritative. Selected durable target remains `null`; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; producer dispatch remains not performed; runtime allowlist remains Local/Test/CI; Final Shift Close remains inactive; deployment/Technical Preview/Production remain unauthorized; updater remains inactive.

## Sprint176 selection rule

Begin Sprint176 bounded discovery from fully reconciled Sprint175. Ask what now blocks a real merchant end-to-end or production-ready installation lifecycle, and prioritize the smallest material non-duplicative P0/P1 gap rather than mechanically adding readiness checks or returning to dashboard expansion.

No host probing, artifact transport/extraction, production database execution, administrator creation, environment mutation, migration/seeder execution, updater control-plane mutation, privileged updater UI, deployment, or operational activation objective is pre-authorized. Live repository evidence must prove the next bounded gap first.

Author by Lab | zefry
