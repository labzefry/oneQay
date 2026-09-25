# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository / Product Owner attribution:** Lab | zefry

## Canonical state

- canonical entry for Sprint252: `2dd17971532fabf52ace576fb7cb4aff5556bac9`
- latest operational closure: PR #903 — Final Shift Close activation
- Final Shift Close: `ACTIVE`
- migration #27: `EXECUTED` / no replay
- permission `pos.shift.close`: `PROVISIONED`, `default_grant = NONE`
- deployment authority: `NOT_GRANTED`
- Technical Preview: `NOT_AUTHORIZED`
- Production: `NOT_AUTHORIZED`
- updater: `INACTIVE`

## Trusted activation evidence

- activation execution run: `36121219541` — SUCCESS
- trusted activation evidence run: `36121950330`, attempt 2 — SUCCESS
- evidence artifact: `10859140684`
- evidence artifact digest: `sha256:9e137b235bc36ff0c7989fd2fdca228878562aef69c2686aecce85d0e1643c97`

## Selected durable target

- environment: `oneqay-durable-staging-01`
- runtime class: `durable-staging`
- selection state: `SELECTED_NOT_AUTHORIZED`
- running source: `59b42137eed717aacc82080a46753440bb4d8537`
- running artifact SHA-256: `8ad4a33196eabd6248c9dfb35eb118e97759e5c17b32384a12b120258bce615c`
- readiness attestation SHA-256: `4a0772838f25d7ee3a7d57153f76b3b23f5d4722c644a76c287b5005e9c328b2`
- selection fingerprint SHA-256: `6ecc487cb144e634455de69cc43aa39b1f6880a2256c4b1ab33387096513772b`

## Next material engineering blocker

The pre-activation promotion chain historically required `feature_activation = INACTIVE`. That assumption is no longer canonical. Sprint252 establishes post-activation durable-staging publication/promotion adapters that require and preserve `ACTIVE` without performing activation or any other operational mutation.

After source readiness is merged, any real cPanel source promotion requires separate short-lived deployment authority. Fresh runtime re-attestation and selected-target generation reconciliation are mandatory after that deployment.

Author by Lab | zefry
