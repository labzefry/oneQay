# Sprint149 — Final Shift Close Durable Runtime Capability Evidence Producer

Author by Lab | zefry

## Purpose / Why

Sprint148 established the fail-closed identity contract for capability-specific evidence bound to the exact durable runtime target, but intentionally left real capability evidence absent and the trusted producer unimplemented. Sprint149 closes only that source-readiness gap.

## Objective / Gap

Bounded objective: `DURABLE_RUNTIME_TARGET_BOUND_CAPABILITY_EVIDENCE_PRODUCER`.

Sprint110 readiness booleans remain readiness claims, not target-bound evidence. The trusted producer must obtain capability-specific observation digests from an authenticated protected-environment endpoint, recompute the Sprint111 target identity from a qualified Sprint110 attestation, match that identity to the persisted `SELECTED_NOT_AUTHORIZED` target, and produce evidence accepted by the Sprint148 qualifier.

## What changed

Sprint149 materializes a protected-environment `workflow_dispatch` producer source, a pure application producer, executable regression coverage, and a machine-readable producer contract. The producer accepts no caller-supplied runtime target identity. It derives evidence identities deterministically from the exact target binding plus per-capability evidence payload digests, rejects unexpected fields, and emits only secret-free artifacts.

The exact frozen seven-path source envelope SHA-256 is `51f9cc22db6b1d6b072a1ee649ce2d474fad2f457346d949a1aece67f6a1eb6b`.

## Evidence / Qualification

The producer requires all four Sprint148 capability evidence kinds, exact selected-target identity, lowercase SHA-256 observation payload identities, protected-environment identity agreement, recomputed Sprint110/Sprint111 qualification, and final Sprint148 evidence qualification. Key-order-independent canonical hashing prevents object-order drift from changing producer identity.

The producer workflow publishes evidence only after qualification and uses pending/success commit status around artifact publication. Source materialization alone does not create real evidence.

## Operational boundaries / NO-GO

Sprint149 does not dispatch the producer. It does not persist a target, execute migration #27, provision permissions, change the runtime allowlist, implement or execute feature activation, deploy, release, activate Technical Preview or Production, or activate the updater. `real_capability_evidence` remains `NONE` until a separately authorized future protected-environment execution succeeds against a qualified selected target.

## Next position

After engineering merge and canonical reconciliation, the next bounded discovery must re-evaluate the remaining production-readiness prerequisites from canonical main. No downstream operational stage becomes authorized by Sprint149 source materialization.
