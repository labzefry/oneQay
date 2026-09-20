# oneQay Roadmap

**Roadmap checkpoint:** Sprint214 closed canonically
**Canonical engineering baseline:** `270e8e954e389f61d32f49b89b87bed571866867`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint214 horizon

Sprint214 closed `CPANEL_NO_SSH_GUARDED_DEPLOYMENT_EXECUTION`.

The cPanel no-SSH path now has a governed executor that validates current request-bound authority and the exact Sprint207 plan, checks the governed Sprint211 archive, performs immutable release extraction and atomic activation, verifies authenticated HTTPS readiness, rehearses rollback, reactivates the new release, verifies readiness again, and emits Sprint209-compatible evidence only after all checks pass.

Engineering PR #863 qualified at 93/93 on final head `d8822e6f2e3aee3b8550424c2e36c342b09fe101` and squash merged at `270e8e954e389f61d32f49b89b87bed571866867`.

Publication run `35497396424` produced post-Sprint214 kit artifact `10601606508`, with independently verified inner ZIP SHA-256 `e572c93f1a8fc55b1c67f0b1f8744a2b6dd3f3c811d0c83a91767fef8df1ae82`.

## Production-readiness progression

Sprint203 supplied the merchant-core staging bridge, Sprint204 runtime readiness, Sprint205 reproducible durable artifact, Sprint206 validated handoff, Sprint207 deterministic operator planning, Sprint208 request-bound deployment authority, Sprint209 deployment-execution evidence qualification, Sprint210 provenance continuity, Sprint211 application artifact publication, Sprint212 cPanel no-SSH target qualification, Sprint213 cPanel operator-kit publication, and Sprint214 guarded cPanel deployment execution.

## Next material horizon

The repository-side execution chain is now sufficient for a real target attempt. Issue #856 remains the operational handoff. Materialize and qualify a real isolated non-production target, then use separately issued authority for actual execution. If the target is cPanel without SSH, use the post-Sprint214 kit artifact `10601606508`; the stale Sprint213 kit is no longer the final kit for execution.

Do not create Sprint215 merely to continue activity. Open another source sprint only if real qualification/execution proves a concrete repository-side blocker.

Author by Lab | zefry
