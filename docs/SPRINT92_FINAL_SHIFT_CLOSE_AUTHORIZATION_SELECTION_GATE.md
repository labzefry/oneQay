# Sprint92 — Final Shift Close Authorization Selection Gate

Author by Lab | zefry

## 1. Purpose

Sprint92 converts the remaining Final Shift Close authorization ambiguity into an explicit Product Owner decision gate without selecting or implementing durable close authority.

Sprint90 already qualified the post-close sale mutation freeze. Sprint91 already qualified the transaction-aware expected-cash snapshot. The remaining blocker before `CloseShift` source may be materialized is authorization policy selection.

## 2. Canonical facts

Canonical source currently has no `pos.shift.close` permission and no Final Shift Close application source.

`PosPermission` contains existing POS authorities including sale completion, shift opening, opening-cash recording, closing-cash recording, variance-explanation recording, void, refund, and inventory-baseline authorities. None is implicitly Final Shift Close authority.

Canonical cash-variance review uses the separate string `pos.shift.cash-variance-review-decision.record`. Review acceptance is evidence required for nonzero variance and is not authority to finalize a shift. Canonical review also rejects the explanation author as reviewer.

## 3. Authorization invariants that are already fixed

Any future Final Shift Close authorization selection must preserve all of these invariants:

1. A dedicated Final Shift Close permission is required; no existing permission may be reused implicitly.
2. Review acceptance cannot authorize close by itself.
3. Closing-cash recording cannot authorize close by itself.
4. A caller cannot supply authoritative reconciliation amounts, variance direction, accepted-review outcome, or close timestamp.
5. Tenant, organization, outlet, device, and shift scope remain derived from verified context and durable evidence.
6. Exact successful replay must remain possible without granting authority to create a second close mutation.
7. Default-role grants must be explicit; absence of an explicit grant is deny-by-default.
8. No deployment, migration execution, release, updater activation, Technical Preview activation, or Production activation follows from selecting an application permission.

## 4. Product Owner decision dimensions

The Product Owner must explicitly select each dimension before Final Shift Close application source is created.

### A. Dedicated permission identifier

Candidate identifier:

`pos.shift.close`

Sprint92 does **not** define this identifier in source. It is only the bounded candidate aligned with the existing POS permission namespace.

### B. Default role grant policy

Choose one explicitly:

- **NO_DEFAULT_GRANT** — permission exists but no default role receives it; assignment is explicit through durable authorization administration.
- **EXPLICIT_PRIVILEGED_ROLE_GRANT** — permission is granted only to a separately named privileged operational role selected by the Product Owner.

No implicit inheritance from shift-opening, closing-cash, explanation, review, void, refund, or sale-completion permission is allowed.

### C. Closer vs opener separation

Choose one explicitly:

- **SEPARATION_REQUIRED** — the final closer must not be the actor that opened the shift.
- **SAME_ACTOR_ALLOWED** — the opener may close if independently authorized by the dedicated Final Shift Close permission.

No choice is made by Sprint92.

### D. Closer vs variance-explanation author separation

Choose one explicitly:

- **SEPARATION_REQUIRED_FOR_NONZERO_VARIANCE** — the closer must differ from the explanation author when variance is OVER or SHORT.
- **SAME_ACTOR_ALLOWED_AFTER_INDEPENDENT_REVIEW** — the explanation author may close after an independent reviewer has accepted the explanation, provided the closer independently holds Final Shift Close authority.

No choice is made by Sprint92.

### E. Closer vs variance reviewer separation

Choose one explicitly:

- **SEPARATION_REQUIRED_FOR_NONZERO_VARIANCE** — the actor who accepted/rejected the variance may not perform Final Shift Close.
- **REVIEWER_MAY_CLOSE_AFTER_ACCEPTANCE** — the reviewer may close after REVIEW_ACCEPTED if the reviewer independently holds Final Shift Close authority.

No choice is made by Sprint92.

## 5. Recommended security posture for Product Owner consideration

The strongest separation-of-duties posture is:

- dedicated `pos.shift.close` permission;
- no default role grant;
- closer distinct from explanation author for nonzero variance;
- closer distinct from reviewer for nonzero variance;
- closer distinct from opener unless the Product Owner explicitly accepts same-actor operational close.

This is a recommendation for decision support only. It is **not** selected authority and must not be interpreted by source or CI as authorization.

## 6. Source gate

Until the Product Owner explicitly selects the authorization policy:

- `FINAL_SHIFT_CLOSE_PERMISSION = NOT_DEFINED`
- `FINAL_SHIFT_CLOSE_DEFAULT_ROLE_GRANT = NOT_SELECTED`
- `FINAL_SHIFT_CLOSE_CLOSER_VS_OPENER = NOT_SELECTED`
- `FINAL_SHIFT_CLOSE_CLOSER_VS_EXPLANATION_AUTHOR = NOT_SELECTED`
- `FINAL_SHIFT_CLOSE_CLOSER_VS_REVIEWER = NOT_SELECTED`
- `FINAL_SHIFT_CLOSE_AUTHORITY = NOT_SELECTED`
- `FINAL_SHIFT_CLOSE_APPLICATION_SOURCE = BLOCKED_BY_AUTHORIZATION_SELECTION`

`CloseShift.php`, `CloseShiftCommand.php`, `CloseShiftRepository.php`, `CloseShiftResult.php`, and `LaravelCloseShiftRepository.php` remain absent.

## 7. Already-closed technical prerequisites

Subject to their canonical merged qualification:

- `FINAL_SHIFT_CLOSE_MUTATION_FREEZE = QUALIFIED_CANONICAL`
- `FINAL_SHIFT_CLOSE_TRANSACTION_AWARE_SNAPSHOT = QUALIFIED_CANONICAL`

These technical prerequisite closures do not imply authorization selection.

## 8. Current lifecycle boundaries

- Migration #27: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED**
- Technical Preview: **NOT ACTIVATED / NO-GO**
- Production: **NO-GO**
- Updater: **INACTIVE**
- Final Shift Close runtime: **NOT IMPLEMENTED**

Sprint92 is complete only when exact-head qualification proves this decision gate changed documentation/workflow evidence only and did not introduce Final Shift Close source or authority.
