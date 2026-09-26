# Sprint258 — Selected-Target Generation Successor Compatibility

Author by Lab | zefry

Sprint258 removes historical CI coupling that treated one prior durable-staging selected-target generation as immutable forever.

Stable target identity remains governed and unchanged: environment `oneqay-durable-staging-01`, runtime class `durable-staging`, and selection state `SELECTED_NOT_AUTHORIZED`. Historical workflows keep those stable invariants while generation-specific fields are checked for canonical structural shape. Exact binding of a newly proposed generation remains fail-closed in the active post-activation generation gate.

This compatibility pass performs no target reselection, runtime mutation, feature activation mutation, migration replay, permission reprovisioning, Technical Preview activation, Production deployment or activation, updater activation, or producer dispatch. Historical documents and machine-readable historical contracts remain provenance and are not rewritten.
