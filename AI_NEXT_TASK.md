# AI Next Task

## Immediate continuation

Complete the required-check workflow recovery on Draft PR #38 without starting another milestone.

## Required order

1. Update `CHANGELOG.md` under `[Unreleased]` to record:
   - restoration of `.github/workflows/governance-required-checks.yml`;
   - stable checks `governance-validation`, `markdown-lint`, and `secret-scan`;
   - alignment of `main-protected-governance` required-check contexts;
   - removal of obsolete contexts `actions/checkout-v4` and `pull_request`;
   - governance-only scope with no build, release, deployment, application source code, ADR acceptance, Phase 0 exit, or merge authority;
   - GOV-043 remains Review pending final exact-head evidence and independent review.
2. Refresh the PR #38 body with:
   - final exact head SHA;
   - complete changed-file list;
   - completed documentation status;
   - current required-check and ruleset evidence;
   - unchanged lifecycle restriction requiring Draft state.
3. Perform final Delta Verification only:
   - current `main`;
   - PR #38 Draft state and exact head;
   - PR #38 changed files;
   - latest exact-head checks;
   - `main-protected-governance` required contexts;
   - PR #35 Draft state and unchanged head.
4. Request independent exact-head review only after all final content commits have completed.

## Stop conditions

Do not:

- merge PR #38 or PR #35;
- mark either PR ready;
- enable auto-merge;
- deploy or release;
- force push;
- change ADR or Phase 0 statuses;
- create application source code;
- begin Authentication Foundation or another milestone.

## Completion condition

The recovery task is ready for Product Owner lifecycle decision only when documentation is complete, all three exact-head checks pass, ruleset contexts remain aligned, no review thread is unresolved, and independent review is recorded on the latest exact head.

Attribution: Lab | zefry
