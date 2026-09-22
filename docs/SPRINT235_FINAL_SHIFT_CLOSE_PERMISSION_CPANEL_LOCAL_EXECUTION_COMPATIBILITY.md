# Sprint235 — Final Shift Close Permission Provisioning cPanel Local Execution Compatibility

Author by Lab | zefry

## Purpose / Why

The selected durable target is a real cPanel no-SSH runtime whose MySQL endpoint is local to the hosting environment. The existing Final Shift Close permission provisioning workflow performs the mutation from a GitHub-hosted runner. The same network shape already proved unsuitable for the migration #27 direct path when the GitHub runner attempted the cPanel-local database endpoint.

Sprint235 adds an additive cPanel-local execution adapter without opening Remote MySQL and without weakening the existing direct GitHub execution path for targets that are legitimately remotely reachable.

## Bounded objective

Provide one fail-closed permission provisioning path:

1. execute the exact `pos.shift.close` permission grant on the selected cPanel runtime;
2. verify the canonical selected-target database binding before mutation;
3. require migration #27 to be exactly EXECUTED;
4. require an existing same-tenant protected control actor;
5. require an existing non-protected target role;
6. write the canonical policy mutation journal and permission grant in one transaction;
7. prove all role-assignment counts remain unchanged;
8. publish short-lived signed evidence;
9. verify the evidence on GitHub against the exact PR/head/binding/tenant/actor/organization/role;
10. publish the existing `final-shift-close-permission-provisioning-evidence` status only after verification succeeds.

## Source envelope

- `.github/workflows/final-shift-close-permission-provisioning.yml`
- `.github/workflows/sprint152-final-shift-close-permission-selected-target-binding-regression.yml`
- `.github/workflows/sprint235-final-shift-close-permission-cpanel-local-execution-compatibility.yml`
- `docs/SPRINT235_FINAL_SHIFT_CLOSE_PERMISSION_CPANEL_LOCAL_EXECUTION_COMPATIBILITY.md`
- `ops/final-shift-close/PERMISSION_PROVISIONING_SELECTED_TARGET_BINDING_CONTRACT.json`
- `ops/final-shift-close/cpanel-permission-provisioning-local-execution.php`
- `ops/final-shift-close/verify-cpanel-permission-provisioning-evidence.php`

Path-set SHA-256:

`bbc26f659fb298712363d36d7968fbc06a098c1a4df73bc5dff64130622bf523`

Sprint152 remains historical provenance for its original source-only contract, while its operational-state assertions are successor-qualified to the exact currently selected durable target, migration #27 `EXECUTED`, permission provisioning `NONE`, and feature activation `INACTIVE`.

## Adapter contract

Direct mode remains:

`GITHUB_HOSTED_LARAVEL_MYSQL_PRIMARY`

Additive fallback:

`CPANEL_LOCAL_SIGNED_PERMISSION_PROVISIONING_AND_POST_VERIFICATION`

Protected environment secret:

`ONEQAY_PERMISSION_LOCAL_PROVISIONING_B64`

Evidence type:

`CPANEL_LOCAL_PERMISSION_PROVISIONING`

Attestation mode:

`LOCAL_PERMISSION_PROVISIONING_AND_POST_VERIFICATION`

Signing context:

`oneqay-permission-cpanel-local-provisioning-v1`

Evidence TTL is at most 900 seconds. HMAC-SHA256 uses a key derived from the selected database password and the fixed signing context. Raw database credentials are never embedded in evidence.

## Preserved semantics

The local executor preserves the exact existing provisioning semantics:

- permission: `pos.shift.close`;
- deterministic mutation id bound to exact target head, tenant, and role;
- target role must already exist;
- protected control role cannot receive the permission;
- actor must already carry tenant-scoped `authorization.policy.manage`;
- existing grant or existing deterministic journal fails closed;
- role assignments are measured before and after and must remain unchanged;
- no `insertOrIgnore`, replay repair, retrospective evidence, or default grant is introduced.

## Operational boundaries / NO-GO

Sprint235 does not:

- change `ops/final-shift-close/STATE.json`;
- grant permission provisioning authority;
- grant feature activation authority;
- activate Final Shift Close;
- change `default_grant = NONE`;
- change migration #27;
- change application runtime source;
- open Remote MySQL;
- authorize Technical Preview;
- authorize Production;
- grant deployment authority;
- activate the updater.

Current canonical state remains:

- migration27 = EXECUTED;
- permission_provisioning = NONE until separately authorized evidence execution completes;
- feature_activation = INACTIVE;
- Technical Preview = NOT_AUTHORIZED;
- Production = NOT_AUTHORIZED;
- updater = INACTIVE.

## Next position

After Sprint235 is merged and the state-transition PR is synchronized to the new canonical main, an exact-head-authorized operator may execute the local cPanel permission provisioning source, transfer only its short-lived Base64 evidence into the protected GitHub Environment secret, and immediately dispatch the canonical permission provisioning workflow.

Author by Lab | zefry
