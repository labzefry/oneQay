# Sprint242 — Final Shift Close Durable Runtime Target Rebind Persistence

Author by Lab | zefry

## Objective

Materialize a dedicated, fail-closed persistence gate for a **same-environment generation rebind** of the already selected durable-staging target.

This successor exists because the original target-selection persistence workflow was intentionally written for the first transition from `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET / selected_target=null` to `SELECTED_NOT_AUTHORIZED / selected_target=object`. After the target had already been selected, Sprint240 legitimately deployed a newer exact source/artifact generation to the same environment and refreshed protected runtime attestation + trusted ingestion.

The attempted persistence run `36014328179` correctly failed before mutation because the original workflow still required the historical initial-selection base state. The failure occurred in the one-file transition precondition before Product Owner authority, ingestion retrieval, selector recomputation, persistence evidence publication, or merge.

## Exact current rebind context

- target PR: `#898`
- target exact head: `993484b0b5a476e8c42c4964691112f6f1bf96e4`
- environment remains: `oneqay-durable-staging-01`
- runtime class remains: `durable-staging`
- target source generation: `59b42137eed717aacc82080a46753440bb4d8537`
- target artifact SHA-256: `8ad4a33196eabd6248c9dfb35eb118e97759e5c17b32384a12b120258bce615c`
- readiness attestation SHA-256: `4a0772838f25d7ee3a7d57153f76b3b23f5d4722c644a76c287b5005e9c328b2`
- selection fingerprint SHA-256: `6ecc487cb144e634455de69cc43aa39b1f6880a2256c4b1ab33387096513772b`
- trusted ingestion run: `36008213090`, attempt `1`
- trusted ingestion fingerprint SHA-256: `a007a97b0b822895ffcdc3453594dae7d4a3f5dab1358390f030c7793b7c79fb`

## Successor semantics

The new workflow `.github/workflows/final-shift-close-durable-runtime-target-rebind-persistence.yml` is manual-only and main-only. It requires:

1. current canonical main at dispatch time;
2. an open, non-draft, exact-head PR whose only changed path is `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`;
3. canonical base state already `SELECTED_NOT_AUTHORIZED` with a selected target object;
4. candidate state still `SELECTED_NOT_AUTHORIZED`;
5. exact preservation of `environment_id=oneqay-durable-staging-01` and `runtime_class=durable-staging`;
6. a genuine generation change rather than a no-op;
7. exact-head Product Owner merge authority on the target PR;
8. a successful trusted ingestion run and unexpired exact evidence artifact;
9. exact trusted-ingestion success status bound to the candidate running source;
10. canonical recomputation of ingestion and target selection using application classes already owned by oneQay;
11. exact equality between the trusted selector output and the target PR selected-target object;
12. no top-level mutation outside selected-target generation evidence;
13. secret-free evidence publication and an exact-head success status.

The original initial-selection persistence workflow is preserved unchanged as historical and initial-selection provenance.

## Engineering envelope

Exactly three paths:

1. `.github/workflows/final-shift-close-durable-runtime-target-rebind-persistence.yml`
2. `.github/workflows/sprint242-final-shift-close-durable-runtime-target-rebind-persistence-regression.yml`
3. `docs/SPRINT242_FINAL_SHIFT_CLOSE_DURABLE_RUNTIME_TARGET_REBIND_PERSISTENCE.md`

Sorted-newline path-set SHA-256:

`1d9aba779dcb80f3874a151db4fc0a865043808c1f7b23335b9975d697f9c815`

No application source, migration source, database, route, runtime configuration, canonical `STATE.json`, or selected-target JSON is changed by Sprint242.

## Operational boundary

Sprint242 creates a source-level qualification capability only.

- Final Shift Close remains INACTIVE.
- Migration #27 is not replayed.
- Permission provisioning is not replayed.
- Technical Preview remains NOT_AUTHORIZED.
- Production remains NOT_AUTHORIZED.
- Updater remains INACTIVE.
- No target environment reselection is authorized.
- No producer dispatch is performed.
- No deployment is performed.

The successor only permits persistence evidence for a same-environment target-generation rebind that is independently backed by trusted runtime ingestion and exact-head Product Owner authority.
