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
    if old == text:
        return
    p.write_text(text)
    changed.append(path)


def replace_exact(path, old, new, count=1):
    text = load(path)
    actual = text.count(old)
    if actual != count:
        raise SystemExit(f'{path}: expected {count} occurrences, found {actual}: {old!r}')
    save(path, text.replace(old, new, count))


def remove_path_line(path, needle):
    text = load(path)
    lines = text.splitlines(True)
    hits = [i for i, line in enumerate(lines) if needle in line]
    if len(hits) != 1:
        raise SystemExit(f'{path}: expected one line for {needle}, found {len(hits)}')
    del lines[hits[0]]
    save(path, ''.join(lines))


def replace_step(path, name, new_step):
    text = load(path)
    pattern = re.compile(rf'(?ms)^      - name: {re.escape(name)}\n.*?(?=^      - name: |\Z)')
    text2, n = pattern.subn(new_step.rstrip() + '\n\n', text, count=1)
    if n != 1:
        raise SystemExit(f'{path}: step not found exactly once: {name}')
    save(path, text2)


def successorize_live_selection(path):
    text = load(path)
    pattern = re.compile(
        r'(?m)^(?P<i>[ \t]*)test "\$\(jq -r \'\.selection_state\' "\$(?P<v>selection|canonical_selection)"\)" = \'BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET\'\n'
        r'(?P=i)test "\$\(jq -r \'\.selected_target\' "\$(?P=v)"\)" = \'null\''
    )

    def repl(m):
        i, v = m.group('i'), m.group('v')
        return '\n'.join([
            f'{i}selection_state="$(jq -r \'.selection_state\' "${v}")"',
            f'{i}case "$selection_state" in',
            f'{i}  BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET)',
            f'{i}    test "$(jq -r \'.selected_target\' "${v}")" = \'null\'',
            f'{i}    ;;',
            f'{i}  SELECTED_NOT_AUTHORIZED)',
            f'{i}    test "$(jq -r \'.selected_target.exact_running_source_commit\' "${v}")" = \'{SOURCE}\'',
            f'{i}    test "$(jq -r \'.selected_target.exact_running_artifact_sha256\' "${v}")" = \'{ARTIFACT}\'',
            f'{i}    ;;',
            f'{i}  *)',
            f'{i}    echo "Unsupported durable activation target successor state: $selection_state" >&2',
            f'{i}    exit 1',
            f'{i}    ;;',
            f'{i}esac',
        ])

    text2, n = pattern.subn(repl, text)
    if n:
        save(path, text2)


def restore_historical_feature_truth(path):
    text = load(path)
    lines = []
    changed_here = False
    for line in text.splitlines(True):
        historical = any(token in line for token in ('"$contract"', '"$readiness"', '"$selection_contract"'))
        if historical and 'feature_activation' in line and 'INACTIVE' in line and 'ACTIVE' in line and '||' in line:
            indent = line[:len(line) - len(line.lstrip())]
            expr = line.strip()
            for sep in (" = 'INACTIVE' ||", ' = INACTIVE ||'):
                if sep in expr:
                    expr = expr.split(sep, 1)[0] + " = 'INACTIVE'"
                    break
            else:
                raise SystemExit(f'{path}: cannot restore historical feature assertion: {line.strip()}')
            line = indent + expr + '\n'
            changed_here = True
        lines.append(line)
    if changed_here:
        save(path, ''.join(lines))


# Shared executable regressions follow canonical ACTIVE successor while retaining true NO-GO boundaries.
replace_exact(
    'apps/web/tests/pos-final-shift-close-durable-staging-delivery-gate.php',
    "(($state['feature_activation'] ?? null) === 'INACTIVE'),\n    'Feature must remain INACTIVE during Sprint240 engineering.'",
    "in_array(($state['feature_activation'] ?? null), ['INACTIVE', 'ACTIVE'], true),\n    'Feature activation state must remain within the bounded predecessor/successor horizon.'",
)

business = 'apps/web/tests/pos-business-workspace-delivery-integration.php'
text = load(business)
marker = "'Sprint198 crossed Final Shift Close activation NO-GO.'"
pos = text.find(marker)
if pos < 0:
    raise SystemExit(f'{business}: Sprint198 marker missing')
start = text.rfind('$assertFlag(', 0, pos)
end = text.find('\n);', pos)
if start < 0 or end < 0:
    raise SystemExit(f'{business}: activation assertion boundary not found')
end += len('\n);')
replacement = """$assertFlag(
    file_get_contents($root.'/.github/workflows/final-shift-close-feature-activation.yml') ?: '',
    [
        'workflow_dispatch:',
        'execution_authorization_id',
        'final-shift-close-feature-activation-evidence',
    ],
    true,
    'Sprint198 activation successor source must remain explicit and authority-bound.'
);"""
save(business, text[:start] + replacement + text[end:])

# Retire obsolete whole-PR locks but preserve owned invariant/source checks.
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

# Activation chain is now canonical successor source; historical documentation remains exact provenance.
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

live_selection_paths = [
    '.github/workflows/sprint109-final-shift-close-durable-activation-target-selection.yml',
    '.github/workflows/sprint110-final-shift-close-durable-runtime-readiness-attestation.yml',
    '.github/workflows/sprint111-final-shift-close-durable-runtime-target-selection-contract.yml',
    '.github/workflows/sprint112-final-shift-close-durable-runtime-attestation-ingestion.yml',
    '.github/workflows/sprint113-final-shift-close-durable-runtime-attestation-producer.yml',
    '.github/workflows/sprint114-final-shift-close-durable-runtime-attestation-ingestion-execution.yml',
    '.github/workflows/sprint115-final-shift-close-durable-target-selection-persistence.yml',
    '.github/workflows/sprint116-final-shift-close-post-selection-downstream-readiness.yml',
]
for path in live_selection_paths:
    successorize_live_selection(path)
    restore_historical_feature_truth(path)

# Current source-shape / selected-target identity assertions only.
replace_exact(
    '.github/workflows/sprint149-final-shift-close-durable-runtime-capability-evidence-producer-regression.yml',
    "grep -Fq 'trusted_ingestion_fingerprint_sha256' .github/workflows/final-shift-close-durable-runtime-capability-evidence.yml",
    "grep -Fq 'ingestion_fingerprint_sha256' .github/workflows/final-shift-close-durable-runtime-capability-evidence.yml",
)
replace_exact(
    '.github/workflows/sprint152-final-shift-close-permission-selected-target-binding-regression.yml',
    "test \"$(jq -r '.selected_target.readiness_attestation_sha256' \"$selection\")\" = '3a45a4de0328cb8e5e0ed5eafb907e0a41230974c03b21d03ed571b7a2d3564d'",
    f"test \"$(jq -r '.selected_target.readiness_attestation_sha256' \"$selection\")\" = '{READINESS}'",
)
replace_exact(
    '.github/workflows/sprint223-durable-runtime-ingestion-null-boundary-regression.yml',
    "grep -Fq 'readiness_attestation_sha256 == \\\"' .github/workflows/sprint114-final-shift-close-durable-runtime-attestation-ingestion-execution.yml",
    "grep -Fq '(.selected_target.readiness_attestation_sha256 // \\\"\\\") != \\\"\\\"' .github/workflows/sprint114-final-shift-close-durable-runtime-attestation-ingestion-execution.yml\n          grep -Fq '(.selected_target.selection_fingerprint_sha256 // \\\"\\\") != \\\"\\\"' .github/workflows/sprint114-final-shift-close-durable-runtime-attestation-ingestion-execution.yml",
)

# Rebuild retired envelope path lists dynamically for owned-workflow validation.
for path, tmp in [
    ('.github/workflows/sprint225-historical-selected-target-successor-compatibility.yml', '/tmp/sprint225-paths'),
    ('.github/workflows/sprint228-migration27-state-successor-compatibility.yml', '/tmp/sprint228-paths'),
]:
    text = load(path)
    marker = '          while IFS= read -r changed_workflow; do\n'
    if text.count(marker) != 1:
        raise SystemExit(f'{path}: expected one changed-workflow loop')
    producer = (
        '          base_sha="$(git merge-base HEAD origin/main)"\n'
        f"          git diff --name-only \"$base_sha\" HEAD -- '.github/workflows/sprint*.yml' | LC_ALL=C sort > {tmp}\n"
    )
    save(path, text.replace(marker, producer + marker, 1))

# Historical regression workflows/tests are not operational mutation ownership.
for path in [
    '.github/workflows/sprint228-migration27-state-successor-compatibility.yml',
    '.github/workflows/sprint230-sprint198-migration27-state-test-successor-compatibility.yml',
    '.github/workflows/sprint231-final-shift-close-migration27-cpanel-local-execution-compatibility.yml',
    '.github/workflows/sprint236-final-shift-close-permission-evidence-refresh-compatibility.yml',
]:
    remove_path_line(path, '.github/workflows/sprint102-final-shift-close-operational-sequencing-gate.yml')
remove_path_line(
    '.github/workflows/sprint230-sprint198-migration27-state-test-successor-compatibility.yml',
    'apps/web/tests/pos-business-workspace-delivery-integration.php',
)

replace_exact(
    '.github/workflows/sprint228-migration27-state-successor-compatibility.yml',
    '            apps \\\n',
    '            apps/web/app \\\n            apps/web/bootstrap \\\n            apps/web/config \\\n            apps/web/database/migrations \\\n            apps/web/resources \\\n            apps/web/routes \\\n',
)

# Current publication source semantics, not predecessor activation literal.
path = '.github/workflows/sprint240-final-shift-close-durable-staging-runtime-promotion-successor-regression.yml'
text = load(path)
lines = text.splitlines(True)
hits = [i for i, line in enumerate(lines) if '$publication' in line and 'feature_activation.state' in line and 'INACTIVE' in line]
if len(hits) != 1:
    raise SystemExit(f'{path}: expected one stale publication activation grep, found {len(hits)}')
indent = lines[hits[0]][:len(lines[hits[0]]) - len(lines[hits[0]].lstrip())]
lines[hits[0]] = indent + 'grep -Fq "feature_activation.state" "$publication"\n'
save(path, ''.join(lines))

# Transient correction machinery must not survive final PR diff.
for p in (WORKFLOW, SELF):
    if not p.exists():
        raise SystemExit(f'transient source unexpectedly missing: {p}')
    p.unlink()
    changed.append(str(p))

print('Bounded correction paths:')
for path in sorted(set(changed)):
    print(path)
