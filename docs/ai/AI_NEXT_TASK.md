# AI Next Task

## Canonical post-Sprint 28 next task — 2026-08-18

This file is the current-facing next-work checkpoint. Earlier M7.5 and Secure Web Updater next-task checkpoints remain immutable in Git history and are historical provenance only.

Attribution: **Lab | zefry**

## Completed boundary

- Sprint 21 through Sprint 28 governed foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint 28 source publication PR #188 was squash-published as `b012262b0028c21c7662d5a9edec3cbf249bba5e`.
- Post-Sprint28 canonical reconciliation PR #189 was squash-published as `68a9b5736a3fc169b50984857954322b169bc42e`.
- Canonical source migrations are exactly **#1 through #8**.
- Migrations #1 through #7 remain immutable.
- Migration #8 is additive and forward-only for initial password enrollment.
- Sprint 26 first-party credential verification is published for Local/Test/CI only.
- Sprint 27 first-party login/session establishment is published for Local/Test/CI only.
- Sprint 28 first-party initial password enrollment is published for Local/Test/CI only.
- Technical Preview remains **`NO_SCHEMA_CHANGE`**.
- Production remains **`NO-GO / NOT AUTHORIZED`**.
- Updater remains **`DISABLED / UNWIRED`**.
- Durable application persistence remains default-disabled with `ONEQAY_PERSISTENCE_ENABLED=false`.

The detailed factual baseline is `docs/ai/AI_POST_SPRINT_28_CANONICAL_STATE.md`.

## Next governed concern

The canonical next logical identity concern is:

**First-Control-Principal Bootstrap Credential Foundation**

Authority state:

**UNRESOLVED / NOT AUTHORIZED**

Sprint 28 assumes an already-authenticated tenant-control administrator can issue an initial-password enrollment capability for another existing identity. The first protected/control principal therefore still needs a separately governed bootstrap path that does not create a circular dependency on an already-authenticated control administrator.

## Required entry gate before implementation

Before any implementation may begin, a separately published bounded entry gate must at minimum:

1. perform fresh GitHub Minimal Delta Verification against the live canonical repository;
2. define the exact bootstrap authority and trust root without relying on an already-authenticated tenant-control administrator;
3. prevent public self-bootstrap, arbitrary identity creation, arbitrary tenant selection, or protected-control bypass;
4. preserve exact tenant and identity scoping, deny-by-default authorization, and protected-control invariants;
5. preserve secret-minimal handling and ensure no plaintext password, reusable secret, token digest, or credential hash is logged or exposed;
6. define bounded one-time/replay-safe semantics if a bootstrap capability or token is selected;
7. prevent bootstrap scope from silently becoming password change, reset, recovery, rotation, revocation, or credential overwrite;
8. preserve generic failure and anti-enumeration principles where credential state can be observed;
9. preserve Local/Test/CI-only delivery unless a separate runtime authority explicitly says otherwise;
10. keep Technical Preview and Production denied unless separately authorized;
11. determine independently whether any schema addition is actually necessary;
12. define the exact changed-file envelope and dedicated regression/preservation chain before source mutation.

## Explicit non-authority

This checkpoint does **not** authorize:

- Sprint 29 implementation;
- any source-code mutation for first-control-principal bootstrap;
- migration #9;
- any assumption that migration #9 will be needed;
- credential overwrite or administrative password setting;
- authenticated password change;
- forgot-password or password reset/recovery;
- password rotation/revocation/deletion;
- MFA/TOTP/passkey/WebAuthn implementation;
- OAuth/OIDC/SAML/federation implementation;
- API/bearer token authentication implementation;
- Production credential storage or authentication activation;
- Technical Preview credential/login/enrollment activation;
- updater activation or wiring;
- deployment, Release, Phase 0 Exit, Sprint 14, or Production authority.

A future sprint number, exact source envelope, schema/migration authority, workflow authority, and implementation scope exist only after a separately published Product Owner-governed entry gate.

## Live GitHub rule

No hard-coded SHA in this checkpoint is a permanently current live-head claim. The SHAs above are publication provenance. Before any new branch, lifecycle mutation, implementation decision, Ready transition, or Merge transition, obtain the live repository state from GitHub again.
