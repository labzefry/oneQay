# AI Project State

## Project

OneQay — The Future of Intelligent Business Management

Developer and Product Engineering Entity: Lab | zefry

GitHub repository `labzefry/oneQay` is the Single Source of Truth.

## Governance state

- Phase 0: In Progress
- Application implementation: Blocked
- Phase 0 preview exit: Not Ready
- P1 hosting: conditional and Unverified
- ADR-001 through ADR-007: Proposed
- GD-007: Proposed
- JRN-003 and JRN-013: unresolved
- Hosting evidence: Pending / Not supplied / Unverified
- No Technical Preview task is Done
- No application source-code authority exists

## Repository protection state

Ruleset `main-protected-governance` is active for the default branch with:

- pull request required;
- one independent approving review;
- stale approvals dismissed after push;
- latest reviewable push approval required;
- review-thread resolution required;
- strict required-status-check policy;
- deletion protection;
- non-fast-forward protection;
- empty bypass list.

Required checks are aligned to:

- `governance-validation`;
- `markdown-lint`;
- `secret-scan`.

## Active recovery work

Draft PR #38 restores the workflow producers for those three required checks on branch `agent/restore-required-check-workflows`.

Current documented scope includes:

- `.github/workflows/governance-required-checks.yml`;
- `.github/workflows/README.md`;
- `DEPLOYMENT.md`;
- `TESTING.md`;
- `TASKS.md`;
- AI checkpoint files created during the recovery session.

`CHANGELOG.md` remains pending before recovery completion.

## Related pull request

Draft PR #35 remains isolated and must not be modified, marked ready, merged, or deployed during the workflow recovery.

## Authority boundaries

Authorized:

- commit files directly to `agent/restore-required-check-workflows` within the required-check recovery scope;
- update PR #38 metadata for accurate recovery documentation;
- perform read-only Delta Verification.

Not authorized:

- merge;
- deploy or release;
- force push;
- enable auto-merge;
- mark PR #38 or PR #35 ready;
- accept ADRs;
- approve Phase 0 exit;
- create application source code.

Attribution: Lab | zefry
