# oneQay Roadmap

**Roadmap checkpoint:** Sprint170 closed canonically  
**Canonical engineering baseline:** `aa46ad0752f8ae2da145eb0c0f6324a3c5be6f25`  
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint170 horizon

Sprint170 closed `INSTALLATION_FILESYSTEM_READINESS`, extending the secure installer-readiness path with deterministic, read-only, fail-closed verification of the minimum Laravel runtime write surface required by oneQay.

The capability qualifies `bootstrap/cache`, `storage/framework/cache`, `storage/framework/sessions`, `storage/framework/views`, and `storage/logs`. Missing or non-writable required paths prevent readiness. It does not perform `chmod`, `chown`, directory creation, environment mutation, migration execution, administrator bootstrap, or installer exposure.

Engineering PR #758 squash merged at `aa46ad0752f8ae2da145eb0c0f6324a3c5be6f25`. Engineering envelope: 3 paths, SHA-256 `7a86c9fbe8d4e87bbcdc3bf72ad618649d9d2cebfe20e2eba51f841ef685b877`.

Canonical reconciliation envelope: 6 paths, SHA-256 `76de10f095cf53b630f96da884ebf6c1f4e581c4dc0f773312415c7788669b98`.

## Product progression through Sprint170

The product now combines the existing tenant/security/API/POS operational foundations with a canonical installation-readiness owner that qualifies runtime/configuration/security posture and the exact runtime filesystem write surface needed by the Laravel application. This is still intentionally not a web wizard, deployment mechanism, environment writer, migration runner, or administrator bootstrapper.

## Operational boundary

Machine-readable operational state under `ops/final-shift-close/` remains authoritative. Selected durable target remains `null`; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; producer dispatch remains not performed; runtime allowlist remains Local/Test/CI; Final Shift Close remains inactive; deployment/Technical Preview/Production remain unauthorized; updater remains inactive.

## Sprint171 selection rule

Begin Sprint171 bounded discovery from fully reconciled Sprint170. Prioritize the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap rather than returning to mechanical dashboard expansion.

For installer progression, package/application prerequisite qualification is a reasonable candidate only if live repository discovery proves it is the next canonical gap. No deployment, migration execution, environment mutation, administrator bootstrap, installer exposure, or operational activation is authorized by this roadmap position.

Author by Lab | zefry
