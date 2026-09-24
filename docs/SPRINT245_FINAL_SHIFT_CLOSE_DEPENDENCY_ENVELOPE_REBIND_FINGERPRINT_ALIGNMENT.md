# Sprint245 — Final Shift Close Dependency Envelope Rebind Fingerprint Alignment

Author by Lab | zefry

## Purpose / Why

The first protected dependency-envelope evidence dispatch after the qualified same-environment target generation rebind reached the producer qualification step and failed before evidence publication because the workflow still read `selection_fingerprint_sha256` from the historical top-level location.

The canonical persisted target now stores that fingerprint under `selected_target.selection_fingerprint_sha256`, alongside the target identity and trusted-ingestion generation binding.

## Bounded correction

Sprint245 changes only the dependency-envelope evidence producer workflow to read the canonical nested fingerprint location before comparing it with the deterministic Sprint111 recomputation.

The comparison itself remains fail-closed. Environment ID, runtime class, running source, running artifact, readiness attestation SHA-256, capability producer provenance, protected environment, authenticated dependency observations, and all downstream dependency qualification remain unchanged.

## Operational boundaries / NO-GO

This correction does not mutate the selected target, runtime configuration, deployment, migration #27, permission provisioning, Final Shift Close feature state, Technical Preview, Production, or updater state. It grants no activation authority and publishes no evidence by source merge alone.

The existing protected dependency evidence dispatch must be rerun after merge. The capability evidence run remains `36022905748` attempt `2` and does not need to be repeated.

Author by Lab | zefry
