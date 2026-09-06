# Sprint93 — Final Shift Close Authorization Acceptance

Author by Lab | zefry

## Purpose

Sprint93 adds a machine-verifiable acceptance mechanism for the still-unselected Final Shift Close authorization posture prepared by Sprint92.

Sprint93 does **not** select, define, or grant Final Shift Close authority. It does not modify `PosPermission`, role grants, application source, infrastructure source, migrations, provider bindings, routes, UI, deployment, updater, Technical Preview, or Production state.

## Canonical starting state

After Sprint92:

- `FINAL_SHIFT_CLOSE_MUTATION_FREEZE = CLOSED_SOURCE_QUALIFIED`;
- `FINAL_SHIFT_CLOSE_TRANSACTION_AWARE_SNAPSHOT = CLOSED_SOURCE_QUALIFIED`;
- `FINAL_SHIFT_CLOSE_PERMISSION = NOT_DEFINED`;
- `FINAL_SHIFT_CLOSE_DEFAULT_ROLE_GRANT = NOT_SELECTED`;
- `FINAL_SHIFT_CLOSE_CLOSER_VS_OPENER_POLICY = NOT_SELECTED`;
- `FINAL_SHIFT_CLOSE_CLOSER_VS_EXPLANATION_AUTHOR_POLICY = NOT_SELECTED`;
- `FINAL_SHIFT_CLOSE_CLOSER_VS_REVIEWER_POLICY = NOT_SELECTED`;
- `FINAL_SHIFT_CLOSE_AUTHORITY = NOT_SELECTED`;
- migration #27 remains source-published only and not executed.

## Exact Product Owner selection phrase

The machine-verifiable selection phrase for the Sprint92 recommended security posture is exactly:

`PRODUCT OWNER AUTHORIZATION: SELECT SPRINT92 RECOMMENDED FINAL SHIFT CLOSE SECURITY POSTURE`

Whitespace surrounding the complete comment may be ignored, but the semantic body must otherwise match exactly. Partial, paraphrased, inferred, generic, or conversational approval must fail closed.

In particular, `Lanjutkan`, standing merge authority, a PR approval, reviewer acceptance, or Product Owner merge authorization are **not** Final Shift Close authorization selection.

## Identity and target binding

The acceptance workflow must require all of the following:

1. the event is a pull-request conversation comment;
2. the comment author is the repository owner;
3. the exact phrase above is present as the complete normalized comment body;
4. the pull request head SHA is resolved from GitHub at evaluation time;
5. the resulting commit status is attached to that exact PR head SHA;
6. success uses context `final-shift-close-authorization-selection`;
7. all nonmatching cases fail closed or produce no authority success.

The workflow does not mutate source or role assignments. It only records whether the exact Product Owner selection statement has been supplied for an exact PR head.

## Selected posture represented by a future successful status

A successful `final-shift-close-authorization-selection` status means the Product Owner has explicitly selected the Sprint92 recommended posture for the bounded authorization-contract PR head:

- dedicated permission identifier candidate: `pos.shift.close`;
- default role grant: `NO_DEFAULT_GRANT`;
- closer must differ from opener;
- for nonzero variance, closer must differ from variance-explanation author;
- for nonzero variance, closer must differ from variance reviewer;
- review acceptance remains evidence precondition only and never becomes close authority.

The status alone does not add the permission or role grants. A later bounded source PR must materialize the selected authorization contract and must itself pass exact-head qualification and normal Product Owner merge authorization.

## Fail-closed boundaries

Sprint93 preserves:

- `FINAL_SHIFT_CLOSE_PERMISSION = NOT_DEFINED`;
- `FINAL_SHIFT_CLOSE_AUTHORITY = NOT_SELECTED` until the exact selection phrase is explicitly supplied;
- `FINAL_SHIFT_CLOSE_APPLICATION_SOURCE = NOT_IMPLEMENTED`;
- `MIGRATION_27_LIVE_EXECUTION = NOT_AUTHORIZED`;
- `TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY = NOT_GRANTED`;
- `DEPLOYMENT_EXECUTION_AUTHORITY = NOT_GRANTED`;
- `PRODUCTION_AUTHORITY = NOT_GRANTED`;
- `UPDATER_ACTIVATION_AUTHORITY = NOT_GRANTED`.

## Exit state

Successful Sprint93 qualification means only:

`FINAL_SHIFT_CLOSE_AUTHORIZATION_ACCEPTANCE_MECHANISM = SOURCE_QUALIFIED`

It does **not** mean:

`FINAL_SHIFT_CLOSE_AUTHORITY = SELECTED`

and it does not authorize `CloseShift` implementation until the explicit Product Owner selection has been received and machine-bound to the relevant exact head.
