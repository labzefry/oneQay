# AI Session State

## Canonical post-Sprint 34 handoff — 2026-08-21

This file is the current session handoff after Sprint34 publication. Older session-state snapshots remain immutable in Git history and are historical provenance only.

- Repository: `labzefry/oneQay`.
- Product attribution: **Lab | zefry**.
- Verified pre-reconciliation canonical main: `4420ad423c27ea30ebe58307a68a547a6115d1bf`.
- Verified pre-reconciliation canonical tree: `d9f133eaa37b1ebf635f6611e70409d7ffa133a3`.
- Sprint34 Authenticated In-Session Password Change Foundation: **COMPLETE / IMPLEMENTED / PUBLISHED** through PR #217.
- Qualified Sprint34 source head: `dc35373a43ce59c59c9e0a71f66b49e4f0aabd9e`.
- Published Sprint34 squash main: `4420ad423c27ea30ebe58307a68a547a6115d1bf`.
- Published Sprint34 tree: `d9f133eaa37b1ebf635f6611e70409d7ffa133a3`.
- PR #215, PR #216, and PR #217 authorities are **CONSUMED** and grant no standing successor authority.
- Canonical migrations are exactly **#1 through #11**; migrations #1–#10 remain immutable.
- Migration #11 adds durable `credential_epoch` to `oneqay_identity_password_credentials` and is the generic credential epoch authority.
- Migration #12 is **NOT SELECTED / DOES NOT EXIST**.
- `POST /auth/password/change` is published for **Local/Test/CI only**.
- Successful authenticated password change is update-only, current-password reverified, same-password rejecting, epoch-incrementing, recovery-code revoking, and fresh-login requiring.
- Protected-control / confirmed privileged-TOTP identities require the existing canonical fresh TOTP challenge; ordinary identities must not submit `totp_code`.
- Sprint33 recovery reset increments the same durable credential epoch exactly once while preserving recovery-specific evidence.
- Normal authenticated password change creates no recovery proof/audit completion event and consumes no recovery code.
- The Sprint34 Technical Preview verifier wiring correction must remain: synthetic Preview verifier only when Preview is explicitly armed; durable verifier otherwise.
- `ONEQAY_PERSISTENCE_ENABLED=false` remains the source default.
- `ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=false` remains the source default.
- Technical Preview remains **`NO_SCHEMA_CHANGE`**.
- Production remains **`NO-GO / NOT AUTHORIZED`**.
- Updater remains **`DISABLED / UNWIRED`**.
- Deployment remains **NOT AUTHORIZED**.
- Release remains **NOT AUTHORIZED**.
- Sprint35 is **NOT SELECTED** by this reconciliation.
- No migration #12, Sprint35 source, Preview schema, Production, updater, deployment, or release authority is created here.

Detailed canonical facts are recorded in `docs/ai/AI_POST_SPRINT_34_CANONICAL_STATE.md`. `docs/ai/AI_PROJECT_STATE.md` is the current project-state pointer and `docs/ai/AI_NEXT_TASK.md` is the current next-work pointer.

Before any future lifecycle mutation or Sprint35 source work, obtain fresh live GitHub main/tree/head evidence and apply a new bounded Product Owner authority. Historical hard-coded SHAs are publication provenance, not permanently current live state.

Attribution: **Lab | zefry**
