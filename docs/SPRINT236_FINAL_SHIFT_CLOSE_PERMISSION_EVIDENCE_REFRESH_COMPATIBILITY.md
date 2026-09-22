# Sprint236 — Final Shift Close Permission Evidence Refresh Compatibility

Author by Lab | zefry

## Purpose

The canonical selected-target binding artifact from run 35685845647 is valid and still has a SUCCESS status, but it predates the later `database_binding_algorithm` field. The current permission provisioning verifier therefore rejects the legacy artifact before it can verify cPanel-local signed evidence.

Sprint236 adds narrowly scoped successor compatibility for that exact historical artifact and a read-only evidence refresh path so the already executed permission grant is not repeated after the original 900-second evidence TTL expires.

## Exact envelope

1. .github/workflows/final-shift-close-permission-provisioning.yml
2. .github/workflows/sprint235-final-shift-close-permission-cpanel-local-execution-compatibility.yml
3. .github/workflows/sprint236-final-shift-close-permission-evidence-refresh-compatibility.yml
4. docs/SPRINT236_FINAL_SHIFT_CLOSE_PERMISSION_EVIDENCE_REFRESH_COMPATIBILITY.md
5. ops/final-shift-close/PERMISSION_PROVISIONING_SELECTED_TARGET_BINDING_CONTRACT.json
6. ops/final-shift-close/cpanel-permission-provisioning-local-evidence-refresh.php

Path-set SHA-256:

`7d4b9f75ef984d5184dc11ca6d6058fb9f06b26c1dcc9a8db9222c7fc574087c`

## Legacy binding compatibility

A missing `database_binding_algorithm` field is accepted only when all of these match exactly:

- run ID: 35685845647
- run attempt: 1
- artifact ID: 10676756501
- artifact digest: sha256:8f2cd339664d20a40a565543d520ab389b242a4690010def71d1bed7b3eac421
- database binding SHA-256: c9247f4200c8b55eb2d8e109185337a44b663dc44961d67cac51eded7ef54e0d

All other binding artifacts must carry the explicit algorithm field.

## Evidence refresh

The refresh operator is read-only. It requires the prior signed evidence file, verifies its HMAC, exact PR/head/binding/tenant/actor/organization/role/mutation identity, re-reads the durable permission grant and policy journal, reattests assignment boundaries, verifies Final Shift Close remains inactive, and emits a fresh 900-second envelope bound to the new canonical main.

It performs no DDL/DML and does not grant the permission a second time.

## Preserved boundaries

- target PR head remains unchanged;
- permission grant is not replayed;
- STATE.json is unchanged;
- migration #27 remains EXECUTED;
- default grant remains NONE;
- Final Shift Close remains INACTIVE;
- Technical Preview remains NOT_AUTHORIZED;
- Production remains NOT_AUTHORIZED;
- deployment authority remains NOT_GRANTED;
- updater remains INACTIVE;
- Remote MySQL remains unnecessary.

Author by Lab | zefry
