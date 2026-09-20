# oneQay Roadmap

**Roadmap checkpoint:** Sprint213 closed canonically
**Canonical engineering baseline:** `c8998c00e19c177ac535921dbc0ef1fa96b06583`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint213 horizon

Sprint213 closed `CPANEL_NO_SSH_OPERATOR_QUALIFICATION_KIT_PUBLICATION`.

The cPanel no-SSH qualification/governance chain is now published as one deterministic operator-retrievable ZIP from canonical main. The kit includes Sprint212 host qualification, Sprint208 authority tooling, Sprint207 planning, and Sprint209 evidence qualification without embedding application bytes, secrets, or authority.

Engineering PR #861 qualified at 92/92 on final head `4c2ef5d73436e3660d0f6700a9bb3cfe46438289` and squash merged at `c8998c00e19c177ac535921dbc0ef1fa96b06583`.

Publication run `35494805706` produced artifact `10600262872`, with independently verified inner ZIP SHA-256 `de485d82c7960683a90c605f4d220b09f85b6cc2282e18f52c43b82006949011`.

## Production-readiness progression

Sprint203 supplied the merchant-core staging bridge, Sprint204 runtime readiness, Sprint205 reproducible durable artifact, Sprint206 validated handoff, Sprint207 deterministic operator planning, Sprint208 request-bound deployment authority, Sprint209 deployment-execution evidence qualification, Sprint210 provenance continuity, Sprint211 application artifact publication, Sprint212 cPanel no-SSH target qualification, and Sprint213 cPanel no-SSH operator-kit publication.

## Next material horizon

Issue #856 is now operational. Materialize a real isolated target. For cPanel without SSH, retrieve the Sprint213 kit and Sprint211 application bundle, run the fail-closed host qualification through File Manager + one-shot Cron/PHP CLI, then continue only with truthful Sprint208 authority binding and real Sprint207/Sprint209 execution evidence.

Open another source sprint only if that real sequence exposes a concrete repository-side blocker.

Author by Lab | zefry
