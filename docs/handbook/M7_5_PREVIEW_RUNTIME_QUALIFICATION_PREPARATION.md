# M7.5 Preview Runtime Qualification Preparation

Attribution: **Lab | zefry**

## Status

- Work item: M7.5 qualification preparation only.
- Lifecycle: **M7.5 BLOCKED / WAITING FOR REQUIRED EXTERNAL RUNTIME EVIDENCE**.
- Deployment: **NOT AUTHORIZED**.
- M7.6 / M7.7: **BLOCKED / NOT AUTHORIZED**.
- Production: **NOT AUTHORIZED / NO-GO**.

This preparation creates no hosting selection, deployment authority, database credential, live database connection, schema, SQL, migration, release, or Production authority.

## Governing decisions

- DEC-009 remains the owner of Stage-1 Preview runtime capability requirements.
- DEC-005R requires an authorized **and runtime-qualified** relational engine profile while keeping Domain/Application database-engine-neutral.
- MariaDB 11.4 family is the current Stage-1 profile direction because existing repository evidence observes MariaDB 11.4.8, but engine family/version evidence is **not runtime qualification**.
- P1 Shared Hosting/cPanel remains **CONDITIONAL / NOT SELECTED**.
- P2 Managed/Hardened VPS or Server remains the fallback execution class; no actual P2 target is currently supplied in the repository.

## Current evidence classification

Existing P1/cPanel evidence records PHP 8.3.26, Apache 2.4.63, required historical PHP extensions, resource/request limits, cPanel Cron/Backup/SSL/File Manager/Git tooling, MariaDB 11.4.8, and no SSH.

The following remain incomplete or unverified for an actual Preview target:

- PHP CLI on target;
- safe document root exactly to the public application surface;
- effective rewrite/front-controller routing;
- safe background execution and queue model;
- scheduler cadence;
- private filesystem/storage isolation;
- target-side environment/secret isolation;
- effective TLS redirect and secure-cookie behavior;
- actual oneQay database connectivity and least-privilege credential behavior;
- connection-limit visibility;
- database transaction semantics and tenant-isolation evidence;
- backup coverage plus verified isolated restore;
- application-level correlation/log lookup;
- complete resource/quota visibility;
- recoverable/versioned deployment model;
- rollback rehearsal;
- outbound DNS/HTTPS evidence;
- explicit Preview-only isolation evidence.

Therefore the current repository evidence must not produce a M7.5 Pass.

## Fail-closed evidence contract

`src/Runtime/Qualification.php` defines a deterministic, provider-neutral evidence evaluator. It deliberately performs **no network, database, shell, deployment, or infrastructure mutation**.

Evidence input is intentionally narrow and sanitized:

```json
{
  "target_id": "preview-p2-sanitized",
  "observed_at": "2026-08-15T00:00:00+07:00",
  "engine": {
    "family": "MARIADB",
    "version": "11.4.8"
  },
  "capabilities": {
    "PHP_RUNTIME": {
      "status": "VERIFIED",
      "reference": "evidence:runtime/php"
    }
  },
  "engine_checks": {
    "APPLICATION_CONNECTIVITY": {
      "status": "UNVERIFIED",
      "reference": "evidence:engine/application-connectivity"
    }
  }
}
```

Only the following evidence states are accepted:

- `VERIFIED`
- `PARTIAL`
- `UNVERIFIED`
- `NOT_SUPPLIED`
- `UNAVAILABLE`

A mandatory control is satisfied only by `VERIFIED`. Missing evidence is classified as `NOT_SUPPLIED`. `PARTIAL`, `UNVERIFIED`, `NOT_SUPPLIED`, and `UNAVAILABLE` all block evidence completion.

A `VERIFIED` control must have a sanitized evidence reference. Unknown fields and unauthorized relational engine families are rejected.

## Runtime qualification controls

The preparation evaluator requires explicit evidence for:

1. PHP runtime.
2. PHP CLI.
3. Web-server/request runtime.
4. Safe public-only document root.
5. Effective URL rewrite/front-controller routing.
6. Background execution.
7. Queue execution model.
8. Scheduler/cron.
9. Filesystem/private storage.
10. Environment/secrets boundary.
11. TLS/HTTPS behavior.
12. Database connectivity.
13. Backup and verified restore.
14. Observability/logging and correlation lookup.
15. Resource/quota visibility.
16. Deployment/recovery boundary.
17. Rollback capability.
18. Security boundary.
19. Preview-only isolation.
20. Outbound DNS/HTTPS capability.

## Relational engine-profile qualification controls

Engine family/version identity is never enough. The selected DEC-005R engine profile also requires explicit `VERIFIED` evidence for:

1. application connectivity;
2. least-privilege behavior;
3. connection-limit visibility;
4. transaction semantics;
5. tenant-isolation semantics;
6. backup/export capability;
7. successful restore evidence;
8. controlled migration boundary;
9. DEC-005R portability-contract conformance.

Authorized family identifiers for the evidence model are:

- `MARIADB`
- `MYSQL`
- `POSTGRESQL`

This list represents architecture directions only. It does not claim those profiles are implemented or runtime-qualified.

## Deterministic offline evaluator

Run only against a sanitized non-secret evidence JSON file:

```bash
php tools/runtime-qualification.php --evidence=/path/to/sanitized-evidence.json
```

Exit behavior:

- `0`: evidence package is complete for the evaluator's mandatory controls;
- `2`: evidence is valid but incomplete/blocked;
- `64`: evidence schema/input is rejected.

`EVIDENCE_COMPLETE` is a necessary technical precondition only. It does **not** create Product Owner lifecycle authority and does not itself authorize M7.5 completion, deployment, M7.6, M7.7, release, or Production.

## Validation

The bounded synthetic regression is:

```bash
php -l src/Runtime/Qualification.php
php -l tests/runtime-qualification.php
php -l tools/runtime-qualification.php
php tests/runtime-qualification.php
composer test
```

The tests verify fail-closed missing/partial evidence behavior, strict input schema, engine-family allow-listing, evidence-reference requirements, deterministic output, no lifecycle authority creation, and absence of network/database/process execution from the evaluator.

## Next evidence gate

Before M7.5 can move beyond blocked state, supply an actual **sanitized Preview target evidence package** (P1 or P2 as applicable) tied to an observation time and evidence source, with credentials/tokens/passwords excluded.

The target must then be evaluated against DEC-009 and the selected DEC-005R relational engine profile. Any mandatory `PARTIAL`, `UNVERIFIED`, `NOT_SUPPLIED`, or `UNAVAILABLE` result remains blocking.
