# Worker lock recognition and guarded preflight restart

Author by Lab | zefry.

The first desktop POS deployment stopped in preflight with `UNEXPECTED_SOURCE_FILE` and `public_switch: NOT_PERFORMED`. Subsequent scheduled `update` calls correctly refused to retry automatically. No successful hosting promotion is evidenced by these reports.

The runtime integrity checker omitted `apps/web/storage/framework/oneqay-control-worker.lock`. The already-installed `ControlPlaneWorkCommand` creates and leaves this empty file when it acquires its normal application lock. A filesystem regression using the same open/flock/unlock/close lifecycle reproduces the reported error on the prior operator.

The successor recognizes only that exact regular, non-symlink, empty lock file, and only in deployed runtime trees. Certified installer payloads remain strict. Non-empty lock files, differently named lock files, symlinks and modified certified source still fail. Existing source-manifest, configuration, routing, authority and database checks are unchanged.

The explicit `retry` command now also accepts the exact untouched five-field `UNEXPECTED_SOURCE_FILE` preflight stop. It still requires fresh native authority bound to the corrected operator/request, no candidate release/environment/bridge, no rollback directory, no module checkpoint and no candidate worker. It preserves the original stopped report before retrying all preconditions. Reports indicating mutations or other unknown error states are denied. The operator does not erase or recreate schema or overwrite business configuration.

Ship a small correction archive into the existing `oneqaydev-pos-update-45509005b2f9` private directory, containing the corrected operator, newly hash-bound authority request and new cron instructions. The application tarball, 6,378-file source manifest, module helper and retention helper remain identical to the already certified release. A fresh native grant is issued only after the correction passes CI. The user runs the explicit `retry` cron once, then replaces the old worker cron after successful installation.

Validation: 30 new filesystem/restart boundary assertions, plus the existing 56 operator assertions, migration preservation checks on SQLite/MySQL, HTTPS behavior, fixed no-link bridge and private ZIP retention tests. Physical hosting and cash/hardware pilot acceptance remain separate.
