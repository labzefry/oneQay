# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Canonical entry for Sprint252: `2dd17971532fabf52ace576fb7cb4aff5556bac9`. PR #903 is squash merged and Final Shift Close is canonically `ACTIVE`.

Trusted activation evidence:
- runtime activation run `36121219541`: SUCCESS;
- trusted activation evidence run `36121950330`, attempt 2: SUCCESS;
- canonical state transition: `INACTIVE -> ACTIVE`;
- migration #27 remains `EXECUTED`;
- `pos.shift.close` remains `PROVISIONED` with `default_grant = NONE`.

## Durable staging baseline

Selected target: `oneqay-durable-staging-01` (`durable-staging`, `SELECTED_NOT_AUTHORIZED`).

Running identity:
- source `59b42137eed717aacc82080a46753440bb4d8537`;
- artifact SHA-256 `8ad4a33196eabd6248c9dfb35eb118e97759e5c17b32384a12b120258bce615c`;
- readiness attestation SHA-256 `4a0772838f25d7ee3a7d57153f76b3b23f5d4722c644a76c287b5005e9c328b2`;
- selection fingerprint `6ecc487cb144e634455de69cc43aa39b1f6880a2256c4b1ab33387096513772b`.

## Current operational boundary

- Final Shift Close runtime flag: `ACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview: `NOT_AUTHORIZED`;
- Production: `NOT_AUTHORIZED`;
- updater: `INACTIVE`;
- no migration #27 replay;
- no permission reprovisioning;
- no runtime allowlist widening.

## Next material production-readiness blocker

Post-activation source promotion must preserve the already-active Final Shift Close state. Sprint252 introduces a governed successor for current-main durable-staging publication, deployment planning, cPanel execution, and deployment evidence qualification. Real deployment remains separately authorized. After a future authorized same-target source promotion, fresh staging re-attestation and selected-target generation reconciliation are required before dark-production readiness can advance.

Author by Lab | zefry
