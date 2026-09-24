# Sprint244 — Final Shift Close Capability Producer Selection Shape Successor

Author by Lab | zefry

## Objective

Align the protected durable-runtime capability evidence producer with the canonical persisted selected-target shape after Sprint241 same-environment rebind.

The canonical target now stores trusted ingestion provenance as the nested object:

`selected_target.trusted_ingestion.{run_id,run_attempt,ingestion_fingerprint_sha256}`

The capability producer still referenced three historical flat fields. That stale preflight would fail closed before authenticated capability observations were qualified even though the selected runtime itself is valid and already deployment/attestation qualified.

## Bounded correction

Only the three persisted-target validation paths in the capability producer are changed to the canonical nested trusted-ingestion object. The producer workflow identity, protected GitHub Environment, authenticated observation endpoint, evidence model, status context, artifact format, and secret-free rules remain unchanged.

The dependency-envelope producer is intentionally not changed because it consumes the capability artifact and does not rely on the stale flat selected-target fields.

## Security boundary

This sprint changes no application runtime source and performs no operational mutation.

- feature activation remains NOT PERFORMED;
- Final Shift Close remains `INACTIVE`;
- migration #27 is not replayed;
- permission provisioning is not repeated;
- deployment is not performed;
- selected-target generation is not changed;
- Technical Preview remains NOT AUTHORIZED;
- Production remains NOT AUTHORIZED;
- updater remains INACTIVE;
- no capability producer dispatch is performed by this PR;
- no secret or bearer token is added to repository content.

After this engineering correction is merged, the protected capability producer can be dispatched on canonical `main`, followed by the existing dependency-envelope evidence producer.

Author by Lab | zefry
