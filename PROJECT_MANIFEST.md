# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Canonical engineering checkpoint:** Sprint158
**Canonical engineering commit:** `d6eb8f7f359584130deea9ec0ed3572add3c05aa`
**Latest engineering PR:** #734 — `Sprint158: add operational POS shift-start workspace`
**Status date:** 2026-09-14

> This file is the canonical human-readable source of truth for current oneQay project status. Machine-readable operational state remains authoritative for operational gates.

## 1. Current project state

oneQay remains an actively engineered enterprise business-management platform using Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

Sprint158 closed the bounded P1 product-usability gap `POS_SHIFT_START_WORKSPACE`. Sprint157 had already made cashier sale entry operational, but cashier readiness still depended on an active exact-device shift and opening-cash evidence. Canonical shift-opening and opening-cash mutation contracts already existed; Sprint158 added a real operational, resumable start-of-shift workspace over those authorities rather than creating another mutation engine.

The workspace requires both existing `pos.shift.open` and `pos.shift.opening-cash.record` permissions, reads exact tenant + organization + outlet + device state, and presents three states: shift required, opening cash required, and ready for cashier. Existing `OpenShift`, `RecordShiftOpeningCash`, and their Laravel repositories remain authoritative for transaction, idempotency, persistence, Money, active-shift, and evidence semantics.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest completed engineering sprint | Sprint158 |
| Canonical engineering commit | `d6eb8f7f359584130deea9ec0ed3572add3c05aa` |
| Latest engineering PR | #734, squash merged |
| Sprint158 final engineering head | `1e2a0ae959a88870ae4728f46f07b08ae0c08a2c` |
| Sprint158 exact-head CI | Complete PR-triggered matrix successful |
| Sprint158 Product Owner authority | `product-owner-merge-authority=success` for exact engineering head |
| Sprint158 engineering envelope | 11 paths; SHA-256 `3828b5914b64b4862ce4d39ff037261b796c232e5ac7c6fb001913a096ac1666` |
| Post-Sprint158 reconciliation envelope | 6 paths; SHA-256 `0257dde337eee65e156c49f59b54a61506b47866babb2c68a8bcc3a1a2e3321f` |
| POS shift-start workspace | Materialized as guarded Local/Test/CI delivery |
| Shift-start authorization | Existing `pos.shift.open` + `pos.shift.opening-cash.record`, deny-by-default |
| Shift-start scope | Tenant + organization + outlet + device |
| Shift-opening mutation authority | Existing canonical `/pos/shifts/open` / `OpenShift` path |
| Opening-cash mutation authority | Existing canonical `/pos/shifts/opening-cash` / `RecordShiftOpeningCash` path |
| POS cashier workspace | Materialized through Sprint157 |
| POS operational sales reporting | Materialized read-only through Sprint156 |
| Runtime allowlist | `local`, `test`, `ci` only |
| Durable activation target | `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET` |
| Selected activation target | `null` |
| Final Shift Close migration #27 | `NOT_EXECUTED` |
| Permission provisioning | `NONE` |
| Real target-bound capability evidence | `NONE` |
| Real dependency-envelope evidence | `NONE` |
| Feature activation | `INACTIVE` |
| Deployment authority | `NOT_GRANTED` |
| Technical Preview activation | `NOT_AUTHORIZED` |
| Production activation | `NOT_AUTHORIZED` |
| Updater activation | `INACTIVE` |

## 2. Material engineering progress

### Platform, governance, and POS foundation

The repository has established modular-monolith architecture, tenant isolation, deny-by-default authorization, first-party session and privileged-authentication foundations, versioned REST governance, deterministic CI/governance controls, exact-head qualification, and repository-native Product Owner merge authorization.

Bounded POS work now includes shift/register opening, opening-cash evidence, sale completion/payment/receipt evidence, catalog preparation, inventory baseline, durable idempotency, cash variance/adjudication, Final Shift Close source/readiness controls, operational sales reporting, cashier sale entry, and an operational shift-start workspace.

### Final Shift Close engineering chain

Sprint88 through Sprint155 established the source/readiness chain from migration #27 materialization through runtime dependency/readiness, selected-target identity, DB binding/control-plane hardening, capability/dependency evidence foundations, permission-provisioning binding, and source-only feature-activation planning/handoff. Operational execution remains separately gated and has not occurred.

### Product-readiness pivot

- Sprint156 — read-only tenant + organization + outlet scoped POS operational sales reporting.
- Sprint157 — operational POS cashier sale-entry workspace over existing canonical transaction authority.
- Sprint158 — operational POS shift-start workspace over existing canonical shift-opening and opening-cash mutation authorities.

## 3. Sprint158 description and closure evidence

### Purpose / Why

Post-Sprint157 discovery showed cashier sale entry was usable only after an active exact-device shift existed, but no operational page existed to open that shift and record its opening cash. The required backend mutation contracts and permissions already existed, making an operational start-of-shift surface the shortest non-duplicative path to a usable cashier journey.

### Objective / Gap

Bounded objective: `POS_SHIFT_START_WORKSPACE`.

### What changed

- Added `PosShiftStartWorkspaceSnapshot`, read-only repository contract, and authorized query service.
- Added exact-scope Laravel read repository over `oneqay_pos_shifts` and `oneqay_pos_shift_opening_cash_evidence`.
- Required both existing `PosPermission::openShift()` and `PosPermission::recordShiftOpeningCash()` permissions.
- Added dedicated fail-closed GET `/pos/shift-start` delivery and `ONEQAY_POS_SHIFT_START_WORKSPACE_ENABLED`, default false.
- Composed the provider through the existing bounded POS composition root; global provider registry remains unchanged.
- Added Vue/Inertia three-stage operational flow: open shift, record opening cash, enter cashier.
- Reused existing POST `/pos/shifts/open` and POST `/pos/shifts/opening-cash` mutation owners; no composite mutation was introduced.
- Preserved partial completion explicitly: a successful shift opening remains authoritative even if opening-cash submission fails or becomes uncertain.
- Added no-auto-retry guidance so uncertain network outcomes require authoritative status refresh before resubmission.
- Added scale-aware integer Money input conversion with client safe-integer guard while server Money validation remains final.
- Added disposable SQLite regression covering exact-device isolation, no-shift state, active-shift/no-evidence resumability, exact evidence binding, currency/scale preservation, and fail-closed persistence/runtime/capability states.
- Left `routes/web.php`, global provider registry, Composer manifest, migrations, and operational state unchanged.

### Evidence / Qualification

- Engineering PR: #734, squash merged.
- Parent canonical post-Sprint157 checkpoint: `700f2133032047d213d38e2e0317641599831a9f`.
- Final exact engineering head: `1e2a0ae959a88870ae4728f46f07b08ae0c08a2c`.
- Complete exact-head PR-triggered matrix: **successful**.
- Sprint158 shift-start regression run `34816888058`: successful.
- M7.1 Application Regression run `34816888381`: successful.
- Governance Required Checks run `34816888368`: successful.
- PHP Foundation Regression run `34816887952`: successful.
- Sprint96 run `34816888176`, Sprint97 run `34816888300`, Sprint126 run `34816888435`, Sprint148 run `34816888194`, Sprint156 run `34816888289`, and Sprint157 run `34816888205`: successful.
- Repository-native Product Owner authorization: exact PR #734 and exact engineering head.
- `product-owner-merge-authority`: successful — exact-head authority verified.
- Engineering envelope: 11 paths; SHA-256 `3828b5914b64b4862ce4d39ff037261b796c232e5ac7c6fb001913a096ac1666`.
- Canonical engineering squash commit: `d6eb8f7f359584130deea9ec0ed3572add3c05aa`.
- Post-merge verification: exactly one squash commit above `700f2133032047d213d38e2e0317641599831a9f`, with exactly the 11 qualified engineering paths.
- Post-Sprint158 reconciliation envelope: six paths; SHA-256 `0257dde337eee65e156c49f59b54a61506b47866babb2c68a8bcc3a1a2e3321f`.

### Operational boundaries / NO-GO

Sprint158 does not select or persist a durable target, dispatch capability/dependency producers, execute migration #27, provision permissions, produce real target-bound capability/dependency evidence, widen the Final Shift Close runtime allowlist, activate Final Shift Close, grant deployment authority, activate Technical Preview/Production, or activate the updater.

Machine-readable state remains target selection blocked with `selected_target=null`, migration #27 `NOT_EXECUTED`, permission provisioning `NONE`, feature activation `INACTIVE`, deployment authority `NOT_GRANTED`, Technical Preview and Production `NOT_AUTHORIZED`, and updater `INACTIVE`.

### Next position

The next engineering position is **Sprint159 bounded discovery from canonical post-Sprint158**. No Sprint159 objective, implementation, or source envelope is preselected.

## 4. Operational truth — NO-GO remains authoritative

- migration #27: `NOT_EXECUTED`;
- permission provisioning: `NONE`;
- selected target: `null`;
- target selection: `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- capability-evidence producer dispatch: `NOT_PERFORMED`; real capability evidence: `NONE`;
- dependency-evidence producer dispatch: `NOT_PERFORMED`; real dependency evidence: `NONE`;
- Final Shift Close runtime allowlist remains Local/Test/CI only;
- feature activation: `INACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview / Production activation: `NOT_AUTHORIZED`;
- updater activation: `INACTIVE`.

Machine-readable operational authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

## 5. Documentation responsibility model

- `PROJECT_MANIFEST.md` — canonical human-readable current project/lifecycle state;
- `README.md` — concise entry-point summary;
- `CHANGELOG.md` — material chronology;
- `TASKS.md` — current completed/pending workboard;
- `ROADMAP.md` — forward sequencing and lifecycle gates;
- `docs/SPRINT*.md` — detailed historical bounded-sprint evidence where materialized;
- `ops/final-shift-close/*.json` — machine-readable operational authority;
- merged PRs and Git history — immutable implementation provenance.

## 6. Mandatory sprint description and update rule

Every material sprint records **Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position**. Every material closed sprint reconciles this manifest and the four root summary documents while the just-closed sprint preservation workflow remains successor-compatible.

Author by Lab | zefry
