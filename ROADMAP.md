# oneQay Roadmap

**Roadmap checkpoint:** Sprint217 closed canonically
**Canonical engineering baseline:** `afb048c9b7edc53d13ad8f5fc1197a8874966450`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint217 horizon

Sprint217 closed `CPANEL_SAME_SOURCE_STAGING_REBIND`.

The cPanel no-SSH operator path is now aligned with Sprint216:
- staging artifact `10603323419`;
- cPanel kit `10603569410`;
- application source `d0b5becbf945c5192e797d512a704eb5aecc6eaa`;
- Production candidate `10603358335` from the same source.

This removes the stale Sprint211 release binding that previously prevented the current cPanel operator path from producing same-source staging evidence for Production promotion.

## Production-readiness progression

Sprint203–Sprint217 now cover durable-staging readiness, deterministic release, authority-bound planning, deployment evidence, cPanel no-SSH target qualification/execution tooling, current-release operator-kit delivery, and same-source staging→Production promotion governance.

## Next material horizon

A concrete Production source gap remains: Production has release, target, authority, plan, and evidence tooling, but no execution tool.

Next bounded engineering:
`PRODUCTION_DARK_DEPLOYMENT_EXECUTION_FOUNDATION`

Target outcome:
1. exact Production plan/authority/artifact revalidation;
2. immutable dark deployment;
3. non-mutating `/health/live` verification;
4. rollback rehearsal and reactivation;
5. exact runtime provenance readback where available;
6. evidence only as `PRODUCTION_DEPLOYED_VERIFIED_NOT_ACTIVATED`;
7. no migration #27 and no Production traffic activation.

Real staging and Production host execution remains under separate operational authority.

Author by Lab | zefry
