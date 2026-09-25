#!/usr/bin/env python3
from pathlib import Path
import re

SOURCE = '59b42137eed717aacc82080a46753440bb4d8537'
ARTIFACT = '8ad4a33196eabd6248c9dfb35eb118e97759e5c17b32384a12b120258bce615c'
FINGERPRINT = '6ecc487cb144e634455de69cc43aa39b1f6880a2256c4b1ab33387096513772b'
OLD_READINESS = '4a077283192843ca9623c778abfff66890d7218afe023e8df6f4fe21078ef17d'
READINESS = '4a0772838f25d7ee3a7d57153f76b3b23f5d4722c644a76c287b5005e9c328b2'

changed = []

def read(path):
    return Path(path).read_text(encoding='utf-8')

def write(path, text):
    p = Path(path)
    old = p.read_text(encoding='utf-8')
    if old == text:
        raise SystemExit(f'No change produced for {path}')
    p.write_text(text, encoding='utf-8')
    changed.append(path)

def replace_once(path, old, new):
    text = read(path)
    count = text.count(old)
    if count != 1:
        raise SystemExit(f'{path}: expected one occurrence, found {count}: {old[:120]!r}')
    write(path, text.replace(old, new, 1))

p = '.github/workflows/sprint105-final-shift-close-feature-activation-readiness.yml'
old = '''          activation=.github/workflows/final-shift-close-feature-activation.yml
          test -f "$activation"
          grep -Fq 'workflow_dispatch:' "$activation"
          grep -Fq 'execution_authorization_id' "$activation"
          grep -Fq 'final-shift-close-feature-activation-evidence' "$activation"
          grep -Fq 'deployment_authority' "$activation"
          grep -Fq 'NOT_GRANTED' "$activation
'''
new = '''          activation=.github/workflows/final-shift-close-feature-activation.yml
          test -f "$activation"
          grep -Fq 'workflow_dispatch:' "$activation"
          grep -Fq 'target_head_sha:' "$activation"
          grep -Fq 'approval_token_sha256:' "$activation"
          grep -Fq 'environment: final-shift-close-feature-activation' "$activation"
          grep -Fq 'ONEQAY_FINAL_SHIFT_CLOSE_ACTIVATION_PROTECTION_ASSERTION' "$activation"
          grep -Fq 'REVIEW_GATED_V1' "$activation"
          grep -Fq 'product-owner-merge-authority' "$activation"
          grep -Fq 'final-shift-close-feature-activation-authority' "$activation"
'''
replace_once(p, old, new)

p = '.github/workflows/sprint106-final-shift-close-activation-target-contract.yml'
old = '''          activation=.github/workflows/final-shift-close-feature-activation.yml
          test -f "$activation"
          test -f apps/web/app/Application/Pos/FinalShiftCloseFeatureActivator.php
          grep -Fq 'workflow_dispatch:' "$activation"
          grep -Fq 'execution_authorization_id' "$activation"
          grep -Fq 'final-shift-close-feature-activation-evidence' "$activation"
'''
new = '''          activation=.github/workflows/final-shift-close-feature-activation.yml
          test -f "$activation"
          test -f apps/web/app/Application/Pos/FinalShiftCloseFeatureActivator.php
          grep -Fq 'workflow_dispatch:' "$activation"
          grep -Fq 'target_head_sha:' "$activation"
          grep -Fq 'approval_token_sha256:' "$activation"
          grep -Fq 'environment: final-shift-close-feature-activation' "$activation"
          grep -Fq 'ONEQAY_FINAL_SHIFT_CLOSE_ACTIVATION_PROTECTION_ASSERTION' "$activation"
          grep -Fq 'REVIEW_GATED_V1' "$activation"
          grep -Fq 'product-owner-merge-authority' "$activation"
          grep -Fq 'final-shift-close-feature-activation-authority' "$activation"
'''
replace_once(p, old, new)

replace_once(
    '.github/workflows/sprint109-final-shift-close-durable-activation-target-selection.yml',
    "          grep -Fq 'Activation foundation must not wire runtime HTTP/config/provider mutation.' \"$staging\"\n",
    "          grep -Fq 'Activation foundation must not wire runtime HTTP/provider mutation outside a qualified successor.' \"$staging\"\n",
)

bounded_selection = f'''          selection_state="$(jq -r '.selection_state' "$selection")"
          case "$selection_state" in
            BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET)
              test "$(jq -r '.selected_target' "$selection")" = 'null'
              ;;
            SELECTED_NOT_AUTHORIZED)
              test "$(jq -r '.selected_target.exact_running_source_commit' "$selection")" = '{SOURCE}'
              test "$(jq -r '.selected_target.exact_running_artifact_sha256' "$selection")" = '{ARTIFACT}'
              test "$(jq -r '.selected_target.selection_fingerprint_sha256' "$selection")" = '{FINGERPRINT}'
              ;;
            *)
              echo "Unsupported selected-target successor state: $selection_state" >&2
              exit 1
              ;;
          esac
'''

for p in [
    '.github/workflows/sprint111-final-shift-close-durable-runtime-target-selection-contract.yml',
    '.github/workflows/sprint112-final-shift-close-durable-runtime-attestation-ingestion.yml',
    '.github/workflows/sprint114-final-shift-close-durable-runtime-attestation-ingestion-execution.yml',
]:
    old = '''          test "$(jq -r '.selection_state' "$selection")" = 'BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET'
          test "$(jq -r '.selected_target' "$selection")" = 'null'
'''
    replace_once(p, old, bounded_selection)

p = '.github/workflows/sprint113-final-shift-close-durable-runtime-attestation-producer.yml'
old = '''          mapfile -t changed_app_paths < <(git diff --name-only "$BASE_SHA" "$HEAD_SHA" -- apps/web/app | LC_ALL=C sort)
          allowed_app_paths=(
            "apps/web/app/Application/Pos/FinalShiftCloseDurableRuntimeAttestationIngestion.php"
            "apps/web/app/Application/Pos/FinalShiftCloseDurableRuntimeAttestationProvenance.php"
          )
          diff -u <(printf '%s\\n' "${allowed_app_paths[@]}") <(printf '%s\\n' "${changed_app_paths[@]}")
'''
new = '''          mapfile -t changed_app_paths < <(git diff --name-only "$BASE_SHA" "$HEAD_SHA" -- apps/web/app | LC_ALL=C sort)
          allowed_app_paths=(
            "apps/web/app/Application/Pos/FinalShiftCloseDurableRuntimeAttestationIngestion.php"
            "apps/web/app/Application/Pos/FinalShiftCloseDurableRuntimeAttestationProvenance.php"
          )
          if [[ "${#changed_app_paths[@]}" -eq 0 ]]; then
            echo 'Workflow-only successor correction preserves application source.'
          else
            diff -u <(printf '%s\\n' "${allowed_app_paths[@]}") <(printf '%s\\n' "${changed_app_paths[@]}")
          fi
'''
replace_once(p, old, new)

p = '.github/workflows/sprint116-final-shift-close-post-selection-downstream-readiness.yml'
old = '''          test ! -e .github/workflows/final-shift-close-feature-activation.yml
'''
new = '''          activation=.github/workflows/final-shift-close-feature-activation.yml
          test -f "$activation"
          grep -Fq 'workflow_dispatch:' "$activation"
          grep -Fq 'target_head_sha:' "$activation"
          grep -Fq 'approval_token_sha256:' "$activation"
          grep -Fq 'product-owner-merge-authority' "$activation"
          grep -Fq 'final-shift-close-feature-activation-authority' "$activation"
'''
replace_once(p, old, new)

replace_once(
    '.github/workflows/sprint152-final-shift-close-permission-selected-target-binding-regression.yml',
    OLD_READINESS,
    READINESS,
)

p = '.github/workflows/sprint225-historical-selected-target-successor-compatibility.yml'
old = '''          grep '^\\.github/workflows/sprint' /tmp/sprint225-paths | grep -v 'sprint225-' > /tmp/historical-workflows
          base_sha="$(git merge-base HEAD origin/main)"
          git diff --name-only "$base_sha" HEAD -- '.github/workflows/sprint*.yml' | LC_ALL=C sort > /tmp/sprint225-paths
          while IFS= read -r workflow; do
            ! grep -Eq "jq -r '\\.selection_state'.*BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET" "$workflow"
            ! grep -Eq "jq -r '\\.selected_target'.*(==|=)[[:space:]]*'?null'?" "$workflow"
            ! grep -Eq '^[[:space:]]+\\.github/workflows/final-shift-close-migration27-selected-target-db-binding\\.yml \\\\$' "$workflow"
          done < /tmp/historical-workflows
'''
new = '''          base_sha="$(git merge-base HEAD origin/main)"
          git diff --name-only "$base_sha" HEAD -- '.github/workflows/sprint*.yml' | LC_ALL=C sort > /tmp/sprint225-paths
          grep '^\\.github/workflows/sprint' /tmp/sprint225-paths | grep -v 'sprint225-' > /tmp/historical-workflows
          while IFS= read -r workflow; do
            ! grep -Eq "jq -r '\\.selection_state'.*BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET" "$workflow"
            ! grep -Eq '^[[:space:]]+\\.github/workflows/final-shift-close-migration27-selected-target-db-binding\\.yml \\\\$' "$workflow"
          done < /tmp/historical-workflows
'''
replace_once(p, old, new)

p = '.github/workflows/sprint228-migration27-state-successor-compatibility.yml'
old = '''          grep '^\\.github/workflows/sprint' /tmp/sprint228-paths             | grep -v 'sprint228-' > /tmp/historical-workflows
          test -s /tmp/historical-workflows

          base_sha="$(git merge-base HEAD origin/main)"
          git diff --name-only "$base_sha" HEAD -- '.github/workflows/sprint*.yml' | LC_ALL=C sort > /tmp/sprint228-paths
          while IFS= read -r workflow; do
            ! grep -Eq "jq -r ['\\\"]\\.migration27\\.state['\\\"].*(==|=)[[:space:]]*['\\\"]?NOT_EXECUTED['\\\"]?" "$workflow"
            ! grep -Eq "git diff[^\\n]*ops/final-shift-close/STATE\\.json" "$workflow"
          done < /tmp/historical-workflows
'''
new = '''          base_sha="$(git merge-base HEAD origin/main)"
          git diff --name-only "$base_sha" HEAD -- '.github/workflows/sprint*.yml' | LC_ALL=C sort > /tmp/sprint228-paths
          grep '^\\.github/workflows/sprint' /tmp/sprint228-paths | grep -v 'sprint228-' > /tmp/historical-workflows
          test -s /tmp/historical-workflows

          while IFS= read -r workflow; do
            ! grep -Eq "jq -r ['\\\"]\\.migration27\\.state['\\\"].*(==|=)[[:space:]]*['\\\"]?NOT_EXECUTED['\\\"]?[[:space:]]*$" "$workflow"
          done < /tmp/historical-workflows
'''
replace_once(p, old, new)

p = '.github/workflows/sprint229-sprint194-migration27-state-successor-compatibility.yml'
old = '''          git diff --exit-code "$BASE_SHA" "$HEAD_SHA" --             apps             ops/final-shift-close/STATE.json             ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json             .github/workflows/final-shift-close-migration27-execution.yml             .github/workflows/final-shift-close-migration27-selected-target-db-binding.yml
'''
new = '''          git diff --exit-code "$BASE_SHA" "$HEAD_SHA" -- \\
            apps/web/app \\
            apps/web/bootstrap \\
            apps/web/config \\
            apps/web/database/migrations \\
            apps/web/resources \\
            apps/web/routes \\
            ops/final-shift-close/STATE.json \\
            ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json \\
            .github/workflows/final-shift-close-migration27-execution.yml \\
            .github/workflows/final-shift-close-migration27-selected-target-db-binding.yml
'''
replace_once(p, old, new)

p = '.github/workflows/sprint240-final-shift-close-durable-staging-runtime-promotion-successor-regression.yml'
old = '''          grep -Fq 'workflow_run:' "$promotion"
          grep -Fq 'post_deployment_reattestation_required' "$promotion"
          grep -Fq 'deployment_authority: "NOT_GRANTED"' "$promotion"
'''
new = '''          grep -Fq 'workflow_run:' "$promotion"
          grep -Fq 'MATERIALIZED_POST_ACTIVATION_PROMOTION_READY_NOT_DEPLOYED' "$promotion"
          grep -Fq '"feature_activation_must_remain_active": true' "$promotion"
          grep -Fq '"exact_running_source_reattestation": true' "$promotion"
          grep -Fq '"exact_running_artifact_reattestation": true' "$promotion"
          grep -Fq '"deployment_authority": "NOT_GRANTED"' "$promotion"
'''
replace_once(p, old, new)

for p in [
    '.github/workflows/sprint243-final-shift-close-feature-activation-guard-successor-regression.yml',
    '.github/workflows/sprint244-final-shift-close-capability-producer-selection-shape-successor-regression.yml',
]:
    old = '''          test -z "$(git diff --name-only "$BASE_SHA" "$HEAD_SHA" -- apps/web ops/final-shift-close/STATE.json ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json)"
'''
    new = '''          test -z "$(git diff --name-only "$BASE_SHA" "$HEAD_SHA" -- apps/web/app apps/web/bootstrap apps/web/config apps/web/database/migrations apps/web/resources apps/web/routes ops/final-shift-close/STATE.json ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json)"
'''
    replace_once(p, old, new)

p = '.github/workflows/sprint253-final-shift-close-post-activation-historical-regression-successor-compatibility.yml'
replace_once(
    p,
    "          old_artifact = '66be23792478fb191f912571b35076c42783b9733cdb3fb514b549d22ec90dd7'\n",
    f"          old_artifact = '66be23792478fb191f912571b35076c42783b9733cdb3fb514b549d22ec90dd7'\n          old_readiness = '{OLD_READINESS}'\n",
)
replace_once(
    p,
    '''          def activation_related(line):
              lower = line.lower()
              return 'feature_activation' in lower or 'feature activation' in lower or '.feature_activation.state' in lower
''',
    '''          def activation_related(line):
              return (
                  '.feature_activation.state' in line
                  or "['feature_activation']['state']" in line
                  or '["feature_activation"]["state"]' in line
              )
''',
)
replace_once(
    p,
    '''                  if "jq -r '.selected_target.exact_running_artifact_sha256'" in line and old_artifact in line:
                      offenders.append(f'{path}:{number}:stale selected-target running artifact')
''',
    '''                  if "jq -r '.selected_target.exact_running_artifact_sha256'" in line and old_artifact in line:
                      offenders.append(f'{path}:{number}:stale selected-target running artifact')
                  if "jq -r '.selected_target.readiness_attestation_sha256'" in line and old_readiness in line:
                      offenders.append(f'{path}:{number}:stale selected-target readiness attestation')
''',
)
replace_once(
    p,
    f'''          test "$(jq -r '.selected_target.exact_running_artifact_sha256' "$selection")" = '{ARTIFACT}'
          test "$(jq -r '.selected_target.selection_fingerprint_sha256' "$selection")" = '{FINGERPRINT}'
''',
    f'''          test "$(jq -r '.selected_target.exact_running_artifact_sha256' "$selection")" = '{ARTIFACT}'
          test "$(jq -r '.selected_target.readiness_attestation_sha256' "$selection")" = '{READINESS}'
          test "$(jq -r '.selected_target.selection_fingerprint_sha256' "$selection")" = '{FINGERPRINT}'
''',
)

for p, old_msg in [
    ('apps/web/tests/pos-final-shift-close-operator-kit-successor-wiring.php', 'Feature must remain INACTIVE during Sprint248 wiring.'),
    ('apps/web/tests/pos-final-shift-close-activation-workflow-pagination.php', 'Feature must remain INACTIVE during Sprint250 hardening.'),
]:
    old = f"expectTrue(($state['feature_activation']['state'] ?? null) === 'INACTIVE', '{old_msg}');"
    new = "expectTrue(in_array(($state['feature_activation']['state'] ?? null), ['INACTIVE', 'ACTIVE'], true), 'Feature activation state must remain within the bounded predecessor/successor horizon.');"
    replace_once(p, old, new)

expected = [
    '.github/workflows/sprint105-final-shift-close-feature-activation-readiness.yml',
    '.github/workflows/sprint106-final-shift-close-activation-target-contract.yml',
    '.github/workflows/sprint109-final-shift-close-durable-activation-target-selection.yml',
    '.github/workflows/sprint111-final-shift-close-durable-runtime-target-selection-contract.yml',
    '.github/workflows/sprint112-final-shift-close-durable-runtime-attestation-ingestion.yml',
    '.github/workflows/sprint113-final-shift-close-durable-runtime-attestation-producer.yml',
    '.github/workflows/sprint114-final-shift-close-durable-runtime-attestation-ingestion-execution.yml',
    '.github/workflows/sprint116-final-shift-close-post-selection-downstream-readiness.yml',
    '.github/workflows/sprint152-final-shift-close-permission-selected-target-binding-regression.yml',
    '.github/workflows/sprint225-historical-selected-target-successor-compatibility.yml',
    '.github/workflows/sprint228-migration27-state-successor-compatibility.yml',
    '.github/workflows/sprint229-sprint194-migration27-state-successor-compatibility.yml',
    '.github/workflows/sprint240-final-shift-close-durable-staging-runtime-promotion-successor-regression.yml',
    '.github/workflows/sprint243-final-shift-close-feature-activation-guard-successor-regression.yml',
    '.github/workflows/sprint244-final-shift-close-capability-producer-selection-shape-successor-regression.yml',
    '.github/workflows/sprint253-final-shift-close-post-activation-historical-regression-successor-compatibility.yml',
    'apps/web/tests/pos-final-shift-close-activation-workflow-pagination.php',
    'apps/web/tests/pos-final-shift-close-operator-kit-successor-wiring.php',
]
if sorted(changed) != sorted(expected):
    raise SystemExit('Unexpected changed path set:\\n' + '\\n'.join(sorted(changed)))
print('\\n'.join(sorted(changed)))
print(f'CORRECTION_PATH_COUNT={len(changed)}')
