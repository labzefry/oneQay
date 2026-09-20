# oneQay Roadmap

**Roadmap checkpoint:** Sprint216 closed canonically
**Canonical engineering baseline:** `d0b5becbf945c5192e797d512a704eb5aecc6eaa`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint216 horizon

Sprint216 closed `PRODUCTION_RELEASE_DEPLOYMENT_GOVERNANCE_FOUNDATION`.

It adds a Production candidate artifact and Production deployment governance without modifying application runtime behavior or activating Production traffic.

The canonical merge publishes:
- same-source durable-staging artifact `10603323419`;
- same-source Production candidate `10603358335`.

Both are source-bound to `d0b5becbf945c5192e797d512a704eb5aecc6eaa`. Independent verification confirms byte-identical application payload files.

Production promotion fails closed unless it receives verified durable-staging evidence from the same source, a real Production target candidate, and separate short-lived authority. The strongest accepted post-deployment state remains `PRODUCTION_DEPLOYED_VERIFIED_NOT_ACTIVATED`.

## Production-readiness progression

Sprint203–Sprint215 established durable-staging readiness, governed artifacts, deployment planning/authority/evidence, cPanel no-SSH qualification/execution, and retention controls. Sprint216 adds a same-source staging→Production promotion boundary and dark Production deployment governance.

## Next material horizon

1. Qualify a real durable-staging target.
2. Deploy exact Sprint216 staging artifact `10603323419` under separate authority.
3. Obtain verified deployment evidence for source `d0b5becbf945c5192e797d512a704eb5aecc6eaa`.
4. Only then prepare the Production promotion request against artifact `10603358335`.
5. Keep business/traffic activation separate until its source and authority requirements are explicitly closed.

Issue #856 remains the single operational handoff.

Author by Lab | zefry
