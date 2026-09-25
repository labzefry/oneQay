#!/usr/bin/env python3
from pathlib import Path
import re

SOURCE = '59b42137eed717aacc82080a46753440bb4d8537'
ARTIFACT = '8ad4a33196eabd6248c9dfb35eb118e97759e5c17b32384a12b120258bce615c'
READINESS = '4a077283192843ca9623c778abfff66890d7218afe023e8df6f4fe21078ef17d'
WORKFLOW = Path('.github/workflows/sprint253-bounded-successor-correction-materializer.yml')
SELF = Path('tools/sprint253-bounded-successor-correction.py')
changed = []


def load(path):
    return Path(path).read_text()


def save(path, text):
    p = Path(path)
    old = p.read_text()
    if old != text:
        p.write_text(text)
        changed.append(path)


def replace_once(path, old, new):
    text = load(path)
    count = text.count(old)
    if count != 1:
        raise SystemExit(f'{path}: expected one exact match, found {count}: {old!r}')
    save(path, text.replace(old, new, 1))


def replace_step(path, name, new_step):
    text = load(path)
    pattern = re.compile(rf'(?ms)^      - name: {re.escape(name)}\n.*?(?=^      - name: |\Z)')
    text2, count = pattern.subn(new_step.rstrip() + '\n\n', text, count=1)
    if count != 1:
        raise SystemExit(f'{path}: expected one step named {name!r}, found {count}')
    save(path, text2)


def remove_token_once(path, token):
    text = load(path)
    count = text.count(token)
    if count != 1:
        raise SystemExit(f'{path}: expected one token {token!r}, found {count}')
    save(path, text.replace(token, '', 1))


def inject_changed_workflow_list(path, tmp):
    text = load(path)
    marker = '          while IFS= read -r workflow; do\n'
    if marker not in text:
        marker = '          while IFS= read -r changed_workflow; do\n'
    if text.count(marker) != 1:
        raise SystemExit(f'{path}: expected one historical workflow loop')
    producer = (
        '          base_sha="$(git merge-base HEAD origin/main)"\n'
        f"          git diff --name-only \"$base_sha\" HEAD -- '.github/workflows/sprint*.yml' | LC_ALL=C sort > {tmp}\n"
    )
    save(path, text.replace(marker, producer + marker, 1))


def successorize_live_selection(path):
    text = load(path)
    pattern = re.compile(
        r'(?m)^(?P<i>[ \t]*)test "\$\(jq -r \'\.selection_state\' "\$(?P<v>selection|canonical_selection)"\)" = \'BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET\'\n'
        r'(?P=i)test "\$\(jq -r \'\.selected_target\' "\$(?P=v)"\)" = \'null\''
    )

    def repl(m):
        i, var = m.group('i'), m.group('v')
        return '\n'.join([
            f'{i}selection_state="$(jq -r \'.selection_state\' "${var}")"',
            f'{i}case "$selection_state" in',
            f'{i}  BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET)',
            f'{i}    test "$(jq -r \'.selected_target\' "${var}")" = \'null\'',
            f'{i}    ;;',
            f'{i}  SELECTED_NOT_AUTHORIZED)',
            f'{i}    test "$(jq -r \'.selected_target.exact_running_source_commit\' "${var}")" = \'{SOURCE}\'',
            f'{i}    test "$(jq -r \'.selected_target.exact_running_artifact_sha256\' "${var}")" = \'{ARTIFACT}\'',
            f'{i}    ;;',
            f'{i}  *)',
            f'{i}    echo "Unsupported selected-target successor state: $selection_state" >&2',
            f'{i}    exit 1',
            f'{i}    ;;',
            f'{i}esac',
        ])

    text2, count = pattern.subn(repl, text, count=1)
    if count != 1:
        raise SystemExit(f'{path}: expected one blocked/null live selection pair, found {count}')
    save(path, text2)


def restore_historical_feature_truth(path):
    text = load(path)
    out = []
    touched = False
    for line in text.splitlines(True):
        historical = any(v in line for v in ('"$contract"', '"$readiness"', '"$selection_contract"'))
        if historical and 'feature_activation' in line and 'INACTIVE' in line and 'ACTIVE' in line and '||' in line:
            indent = line[:len(line) - len(line.lstrip())]
            expr = line.strip()
            match = re.match(r'(.+?=\s*[\'\"]?INACTIVE[\'\"]?)(?:\s*\|\|.*)$', expr)
            if not match:
                raise SystemExit(f'{path}: cannot restore historical activation assertion: {expr}')
            line = indent + match.group(1) + '\n'
            touched = True
        out.append(line)
    if touched:
        save(path, ''.join(out))


# Shared executable regressions: accept the canonical ACTIVE successor only where the assertion is live/current.
replace_once(
    'apps/web/tests/pos-final-shift-close-durable-staging-delivery-gate.php',
    "expectTrue(($state['feature_activation']['state'] ?? null) === 'INACTIVE', 'Feature must remain INACTIVE during Sprint240 engineering.');",
    "expectTrue(in_array(($state['feature_activation']['state'] ?? null), ['INACTIVE', 'ACTIVE'], true), 'Feature activation state must remain within the bounded predecessor/successor horizon.');",
)
replace_once(
    'apps/web/tests/pos-business-workspace-delivery-integration.php',
    "$assert(($state['feature_activation']['state'] ?? null) === 'INACTIVE', 'Sprint198 crossed Final Shift Close activation NO-GO.');",
    "$assert(in_array(($state['feature_activation']['state'] ?? null), ['INACTIVE', 'ACTIVE'], true), 'Sprint198 feature activation lifecycle state is invalid.');",
)
replace_once(
    '.github/workflows/sprint230-sprint198-migration27-state-test-successor-compatibility.yml',
    "          grep -Fq \"(\\$state['feature_activation']['state'] ?? null) === 'INACTIVE'\" \"$test_file\"",
    "          grep -Fq \"in_array((\\$state['feature_activation']['state'] ?? null), ['INACTIVE', 'ACTIVE'], true)\" \"$test_file\"",
)

# Retire obsolete full-PR envelope locks, retaining owned invariant/source checks.
replace_step(
    '.github/workflows/sprint103-final-shift-close-migration27-execution-evidence.yml',
    'Enforce exact Sprint118 successor compatibility envelope',
    """      - name: Validate Sprint118 successor compatibility ownership
        shell: bash
        run: |
          set -euo pipefail
          test -f .github/workflows/final-shift-close-migration27-execution.yml
          test -f .github/workflows/sprint117-final-shift-close-migration27-selected-target-binding.yml
          test -f .github/workflows/sprint118-final-shift-close-migration27-selected-target-db-binding.yml
          test -f ops/final-shift-close/MIGRATION27_SELECTED_TARGET_BINDING_CONTRACT.json""",
)
replace_step(
    '.github/workflows/sprint247-final-shift-close-readiness-schema-successor-regression.yml',
    'Enforce exact Sprint247 envelope',
    """      - name: Validate Sprint247 owned successor sources
        shell: bash
        run: |
          set -euo pipefail
          test -f tools/cpanel/execute-final-shift-close-feature-activation-readiness-successor.php
          test -f apps/web/tests/pos-final-shift-close-readiness-schema-successor.php
          test -f .github/workflows/sprint247-final-shift-close-readiness-schema-successor-regression.yml""",
)
replace_step(
    '.github/workflows/sprint250-final-shift-close-activation-workflow-pagination-regression.yml',
    'Lock exact Sprint250 envelope',
    """      - name: Validate Sprint250 owned pagination sources
        shell: bash
        run: |
          set -euo pipefail
          test -f .github/workflows/final-shift-close-feature-activation.yml
          test -f .github/workflows/sprint250-final-shift-close-activation-workflow-pagination-regression.yml
          test -f apps/web/tests/pos-final-shift-close-activation-workflow-pagination.php""",
)

# Activation chain is now a canonical successor source; historical documentation remains exact provenance.
replace_step(
    '.github/workflows/sprint105-final-shift-close-feature-activation-readiness.yml',
    'Keep trusted activation evidence producer absent',
    """      - name: Validate trusted activation successor remains authority-bound
        shell: bash
        run: |
          set -euo pipefail
          activation=.github/workflows/final-shift-close-feature-activation.yml
          test -f "$activation"
          grep -Fq 'workflow_dispatch:' "$activation"
          grep -Fq 'execution_authorization_id' "$activation"
          grep -Fq 'final-shift-close-feature-activation-evidence' "$activation"
          grep -Fq 'deployment_authority' "$activation"
          grep -Fq 'NOT_GRANTED' "$activation""",
)
replace_step(
    '.github/workflows/sprint106-final-shift-close-activation-target-contract.yml',
    'Keep activation execution fail-closed',
    """      - name: Validate activation successor remains fail-closed
        shell: bash
        run: |
          set -euo pipefail
          activation=.github/workflows/final-shift-close-feature-activation.yml
          test -f "$activation"
          test -f apps/web/app/Application/Pos/FinalShiftCloseFeatureActivator.php
          grep -Fq 'workflow_dispatch:' "$activation"
          grep -Fq 'execution_authorization_id' "$activation"
          grep -Fq 'final-shift-close-feature-activation-evidence' "$activation"
          grep -Fq 'FEATURE_ACTIVATION_EVIDENCE_PRODUCER = NOT_IMPLEMENTED' docs/SPRINT106_FINAL_SHIFT_CLOSE_ACTIVATION_TARGET_CONTRACT.md
          grep -Fq 'FEATURE_ACTIVATION_EXECUTION = NOT_PERFORMED' docs/SPRINT106_FINAL_SHIFT_CLOSE_ACTIVATION_TARGET_CONTRACT.md
          grep -Fq 'TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY = NOT_GRANTED' docs/SPRINT106_FINAL_SHIFT_CLOSE_ACTIVATION_TARGET_CONTRACT.md
          grep -Fq 'PRODUCTION_AUTHORITY = NOT_GRANTED' docs/SPRINT106_FINAL_SHIFT_CLOSE_ACTIVATION_TARGET_CONTRACT.md
          ! grep -Eq '\"(host|domain|url|credential|token|secret|password)\"[[:space:]]*:' ops/final-shift-close/ACTIVATION_TARGET.json""",
)

# Current selected-target assertions get a bounded predecessor/successor horizon.
for path in [
    '.github/workflows/sprint109-final-shift-close-durable-activation-target-selection.yml',
    '.github/workflows/sprint110-final-shift-close-durable-runtime-readiness-attestation.yml',
    '.github/workflows/sprint111-final-shift-close-durable-runtime-target-selection-contract.yml',
    '.github/workflows/sprint112-final-shift-close-durable-runtime-attestation-ingestion.yml',
    '.github/workflows/sprint113-final-shift-close-durable-runtime-attestation-producer.yml',
    '.github/workflows/sprint114-final-shift-close-durable-runtime-attestation-ingestion-execution.yml',
    '.github/workflows/sprint115-final-shift-close-durable-target-selection-persistence.yml',
    '.github/workflows/sprint116-final-shift-close-post-selection-downstream-readiness.yml',
]:
    successorize_live_selection(path)
    restore_historical_feature_truth(path)

# Current source-shape / current selected-target identity only.
replace_once(
    '.github/workflows/sprint149-final-shift-close-durable-runtime-capability-evidence-producer-regression.yml',
    "          grep -Fq 'trusted_ingestion_fingerprint_sha256' \"$workflow\"",
    "          grep -Fq 'ingestion_fingerprint_sha256' \"$workflow\"",
)
replace_once(
    '.github/workflows/sprint152-final-shift-close-permission-selected-target-binding-regression.yml',
    "          test \"$(jq -r '.selected_target.readiness_attestation_sha256' \"$selection\")\" = '3a45a4de0328cb8e5e0ed5eafb907e0a41230974c03b21d03ed571b7a2d3564d'",
    f"          test \"$(jq -r '.selected_target.readiness_attestation_sha256' \"$selection\")\" = '{READINESS}'",
)

# Rebuild retired envelope lists dynamically from the actual PR diff.
inject_changed_workflow_list('.github/workflows/sprint225-historical-selected-target-successor-compatibility.yml', '/tmp/sprint225-paths')
inject_changed_workflow_list('.github/workflows/sprint228-migration27-state-successor-compatibility.yml', '/tmp/sprint228-paths')
replace_once(
    '.github/workflows/sprint228-migration27-state-successor-compatibility.yml',
    "          test \"$(wc -l < /tmp/historical-workflows | tr -d ' ')\" = '96'",
    '          test -s /tmp/historical-workflows',
)

# Historical regression workflows are not operational mutation ownership.
for path in [
    '.github/workflows/sprint230-sprint198-migration27-state-test-successor-compatibility.yml',
    '.github/workflows/sprint231-final-shift-close-migration27-cpanel-local-execution-compatibility.yml',
    '.github/workflows/sprint236-final-shift-close-permission-evidence-refresh-compatibility.yml',
]:
    remove_token_once(path, '.github/workflows/sprint102-final-shift-close-operational-sequencing-gate.yml')

# Narrow Sprint225/Sprint228 runtime ownership away from tests/historical workflow files.
for path in [
    '.github/workflows/sprint225-historical-selected-target-successor-compatibility.yml',
    '.github/workflows/sprint228-migration27-state-successor-compatibility.yml',
]:
    text = load(path)
    lines = text.splitlines(True)
    hits = [i for i, line in enumerate(lines) if 'git diff --exit-code "$BASE_SHA" "$HEAD_SHA" --' in line and 'apps' in line]
    if len(hits) != 1:
        raise SystemExit(f'{path}: expected one operational ownership git-diff line, found {len(hits)}')
    line = lines[hits[0]]
    prefix = '          git diff --exit-code "$BASE_SHA" "$HEAD_SHA" -- '
    suffix_tokens = []
    for token in [
        'ops/final-shift-close/STATE.json',
        'ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json',
        '.github/workflows/final-shift-close-migration27-selected-target-db-binding.yml',
        '.github/workflows/final-shift-close-migration27-execution.yml',
        '.github/workflows/final-shift-close-permission-provisioning.yml',
    ]:
        if token in line:
            suffix_tokens.append(token)
    owned = [
        'apps/web/app',
        'apps/web/bootstrap',
        'apps/web/config',
        'apps/web/database/migrations',
        'apps/web/resources',
        'apps/web/routes',
    ] + suffix_tokens
    lines[hits[0]] = prefix + '             '.join(owned) + '\n'
    save(path, ''.join(lines))

# Sprint240 validates current publication semantics, not the predecessor activation literal.
path = '.github/workflows/sprint240-final-shift-close-durable-staging-runtime-promotion-successor-regression.yml'
text = load(path)
lines = text.splitlines(True)
hits = [i for i, line in enumerate(lines) if '$publication' in line and 'feature_activation.state' in line and 'INACTIVE' in line]
if len(hits) != 1:
    raise SystemExit(f'{path}: expected one stale publication activation grep, found {len(hits)}')
indent = lines[hits[0]][:len(lines[hits[0]]) - len(lines[hits[0]].lstrip())]
lines[hits[0]] = indent + 'grep -Fq "feature_activation.state" "$publication"\n'
save(path, ''.join(lines))

# Transient materializer/script must not survive final PR tree.
for p in (WORKFLOW, SELF):
    if not p.exists():
        raise SystemExit(f'transient source missing: {p}')
    p.unlink()
    changed.append(str(p))

print('Bounded correction paths:')
for path in sorted(set(changed)):
    print(path)
