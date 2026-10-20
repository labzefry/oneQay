# Incoming POS payload retention race

Author by Lab | zefry. Target: oneqaydev.n07.my.id only.

The first enterprise deployment stopped before the public switch with `CERTIFIED_SOURCE_CHANGED`. Owner-supplied diagnostics verified all 6378 predecessor files, while the first five candidate files were missing. The signed candidate archive and operator files were present. The active predecessor worker had archived `oneqaydev-pos-update-5f2acf1aec6c/payload/production-5f2acf1aec6c` at 09:15 WIB; its archive receipt is keyed by source-path hash `96433897ead9b47e5ff212415aa11d6fe55cc73a76bdb6eb9711a83e034f6ae5`.

Automatic retention previously treated every non-active release and every operator payload as obsolete. It now requires a successful publication receipt no later than the current active release. Prepared, failed and future candidates are preserved. Published older releases and the active operator's payload remain eligible for verified private ZIP archival.

The enterprise deployment holds the same global `release-archives/retention.lock` used by the old worker throughout recovery, source checks, additive migration, copy and promotion. The old worker skips only archival while that lock is held; its mail/NOC work can continue. A missing candidate payload is restored only from its verified private receipt/ZIP and then passes the unchanged certified source checks.

An exact five-field stopped preflight with `CERTIFIED_SOURCE_CHANGED` can be retried with fresh native authority only when no candidate release, runtime, entry, rollback, migration checkpoint or successor worker exists. The original failure JSON is preserved before retry. No partially applied deployment is automatically retried.

The fix changes operator tooling and retention only. The certified application remains `5f2acf1aec6c6cc9a5cbab128c312141fe0301c4`, certification run `39356620709`. New operator/retention hashes require a newly bound request and native deployment grant before execution.
