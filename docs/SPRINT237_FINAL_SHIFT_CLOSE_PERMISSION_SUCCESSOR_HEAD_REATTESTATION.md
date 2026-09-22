# Sprint237 — Final Shift Close Permission Successor-Head Reattestation

Author by Lab | zefry

## Purpose

PR #889 reached 100/100 CI and exact permission provisioning evidence on head `645c845d997d2d615d7bbaa1bb66c1beb999455c`, but repository rules require the PR branch to be fully up to date with `main` before merge. Updating the branch necessarily changes the PR head while the durable permission mutation already exists and must not be replayed.

Sprint237 adds a bounded successor-head reattestation path.

## Contract

- mutation origin PR: 889
- mutation origin head: `645c845d997d2d615d7bbaa1bb66c1beb999455c`
- successor target head must descend from the mutation origin head;
- successor target must retain the exact same `ops/final-shift-close/STATE.json` blob as the origin head;
- the existing signed evidence HMAC must validate;
- the existing deterministic policy mutation journal and exact permission grant must still exist;
- role-assignment boundaries must remain unchanged;
- Final Shift Close must remain inactive;
- fresh successor evidence is HMAC-signed and TTL-bounded to 900 seconds;
- no permission mutation, DDL, or DML is allowed.

## Exact envelope

1. `.github/workflows/final-shift-close-permission-provisioning.yml`
2. `.github/workflows/sprint236-final-shift-close-permission-evidence-refresh-compatibility.yml`
3. `.github/workflows/sprint237-final-shift-close-permission-successor-head-reattestation.yml`
4. `docs/SPRINT237_FINAL_SHIFT_CLOSE_PERMISSION_SUCCESSOR_HEAD_REATTESTATION.md`
5. `ops/final-shift-close/PERMISSION_PROVISIONING_SELECTED_TARGET_BINDING_CONTRACT.json`
6. `ops/final-shift-close/cpanel-permission-provisioning-successor-evidence-refresh.php`
7. `ops/final-shift-close/verify-cpanel-permission-provisioning-evidence.php`

Path-set SHA-256:

`68e4239ac8c337de0656f3b36bfa337705086ff03aa201b75750f3e6e7c79cbf`

## NO-GO preserved

- `STATE.json` unchanged;
- migration #27 remains EXECUTED;
- permission provisioning is not replayed;
- default grant remains NONE;
- Final Shift Close remains INACTIVE;
- Technical Preview remains NOT_AUTHORIZED;
- Production remains NOT_AUTHORIZED;
- deployment authority remains NOT_GRANTED;
- updater remains INACTIVE;
- Remote MySQL remains unnecessary.

Author by Lab | zefry
