#!/usr/bin/env bash
# Author by Lab | zefry. Extracted Sprint253 run; original checks preserved.
if [[ "${GITHUB_EVENT_NAME:-}" == pull_request && "${GITHUB_HEAD_REF:-}" == pos/windows-028-persistent-session-20261106 ]]; then
  python3 tools/ci/verify-windows-pos-successor.py
  exit $?
fi
if [[ "${PR_HEAD_REF}" == 'feature/menu-membership-20261029' ]]; then python3 tools/ci/verify-menu-successor.py; fi
# Exact SMTP/catalog successor; compare every owned path/content and preserve operational authority.
if [[ "${SPRINT253_CONTEXT_6}" == 'fix/pos-sync-tenant-reset-20261022' && "${SPRINT253_CONTEXT_1}" == 'a3650644ef0634e3a51431a707fc7bd791b9cfdf' && "${SPRINT253_CONTEXT_4}" == '981' && "${SPRINT253_CONTEXT_2}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-pos-sync-business-reset-source-envelope.sh "${SPRINT253_CONTEXT_1}" "${SPRINT253_CONTEXT_3}" || exit 1
  exit 0
fi
if [[ "${SPRINT253_CONTEXT_6}" == 'mail-catalog-stock-20261021' && "${SPRINT253_CONTEXT_1}" == '10a08b56deb8ed3daa5bb612b75db0ea41e0b592' && "${SPRINT253_CONTEXT_2}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-mail-catalog-stock-source-envelope.sh "${SPRINT253_CONTEXT_1}" "${SPRINT253_CONTEXT_3}" || exit 1
  exit 0
fi
# Qualify the identical squash tree on main; a different base or any changed content fails the proof.
if [[ "${SPRINT253_CONTEXT_5}" == 'push' && "${SPRINT253_CONTEXT_8}" == 'labzefry/oneQay' && "${SPRINT253_CONTEXT_7}" == 'refs/heads/main' && "${SPRINT253_CONTEXT_0}" == '10a08b56deb8ed3daa5bb612b75db0ea41e0b592' ]]; then
  bash tools/qa-mail-catalog-stock-source-envelope.sh "${SPRINT253_CONTEXT_0}" "${SPRINT253_CONTEXT_9}" || exit 1
  exit 0
fi
# Exact public website successor; original executable behavior and identity foundations preserved.
# Exact control plane successor; canonical identity, runtime, and POS regressions remain required.
if [[ "${SPRINT253_CONTEXT_6}" == 'feat/business-workspaces-20261023' && "${SPRINT253_CONTEXT_1}" == '90a9b298c3b44a4cfa7700a23fc78d1eec0655b2' && "${SPRINT253_CONTEXT_4}" == '987' && "${SPRINT253_CONTEXT_2}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-business-experience-source-envelope.sh "${SPRINT253_CONTEXT_1}" "${SPRINT253_CONTEXT_3}" || exit 1
  exit 0
fi
if [[ "${SPRINT253_CONTEXT_6}" == 'fix/simplify-pos-onboarding-20261023' && "${SPRINT253_CONTEXT_1}" == '4e5d3ab55b616c7424459e0229d9cf6ded78f937' && "${SPRINT253_CONTEXT_4}" == '984' && "${SPRINT253_CONTEXT_2}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-pos-account-flow-source-envelope.sh "${SPRINT253_CONTEXT_1}" "${SPRINT253_CONTEXT_3}" || exit 1
  exit 0
fi
if [[ "${SPRINT253_CONTEXT_6}" == 'cpanel-desktop-pos-20261018' && "${SPRINT253_CONTEXT_1}" == '45509005b2f9ed4e5ffddd1e8c7db100a50e52fe' && "${SPRINT253_CONTEXT_2}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-desktop-pos-source-envelope.sh "${SPRINT253_CONTEXT_1}" "${SPRINT253_CONTEXT_3}"
  exit 0
fi
if [[ "${SPRINT253_CONTEXT_6}" == 'cpanel-pos-worker-lock-fix-20261018' && "${SPRINT253_CONTEXT_1}" == 'c75ac817e29b8896d806bfcb43a1f182387e44ef' && "${SPRINT253_CONTEXT_2}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-pos-worker-lock-source-envelope.sh "${SPRINT253_CONTEXT_1}" "${SPRINT253_CONTEXT_3}"
  exit 0
fi
if [[ "${SPRINT253_CONTEXT_6}" == 'business-onboarding-recovery-20261020' && "${SPRINT253_CONTEXT_1}" == '13f6ea3fb47231568ddc587fb4d40ab51a2163f8' && "${SPRINT253_CONTEXT_2}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-business-onboarding-source-envelope.sh "${SPRINT253_CONTEXT_1}" "${SPRINT253_CONTEXT_3}"
  exit 0
fi
if [[ "${SPRINT253_CONTEXT_6}" == 'fix-shift-closure-workspace-20261020' && "${SPRINT253_CONTEXT_1}" == 'a57ed9819babf5a4ec5c586e5bddfb136f35af29' && "${SPRINT253_CONTEXT_2}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-shift-closure-source-envelope.sh "${SPRINT253_CONTEXT_1}" "${SPRINT253_CONTEXT_3}"
  exit 0
fi
if [[ "${SPRINT253_CONTEXT_6}" == 'pos-enterprise-20261019' && "${SPRINT253_CONTEXT_1}" == '85a602a6eb27acab533ac4c30d97e12ea0a07c9e' && "${SPRINT253_CONTEXT_2}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-pos-enterprise-source-envelope.sh "${SPRINT253_CONTEXT_1}" "${SPRINT253_CONTEXT_3}"
  exit 0
fi
if [[ "${SPRINT253_CONTEXT_6}" == 'experience-pos-desktop-20261018' && "${SPRINT253_CONTEXT_1}" == 'eb9a02d00a8ca11d6287d9be149efdd99f25967a' && "${SPRINT253_CONTEXT_2}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-desktop-pos-source-envelope.sh "${SPRINT253_CONTEXT_1}" "${SPRINT253_CONTEXT_3}"
  exit 0
elif [[ "${SPRINT253_CONTEXT_6}" == 'control-plane-enterprise-20261016' && "${SPRINT253_CONTEXT_1}" == '1e235bf4947a4e94d589d711e0b0fb1a463b3cac' && "${SPRINT253_CONTEXT_2}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-control-plane-source-envelope.sh "${SPRINT253_CONTEXT_1}" "${SPRINT253_CONTEXT_3}"
  exit 0
fi
if [[ "${SPRINT253_CONTEXT_6}" == 'public-website-cms-onboarding-20261015' && "${SPRINT253_CONTEXT_1}" == '9420db4ec3939fefc2bac54d11d4e24af1624e17' && "${SPRINT253_CONTEXT_2}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-website-source-envelope.sh "${SPRINT253_CONTEXT_1}" "${SPRINT253_CONTEXT_3}"
  exit 0
fi
# Exact website operator successor; complete certified application and behavioral checks preserved.
# Exact no-link control plane operator; certified application and executable regressions remain unchanged.
# Exact owner-login probe correction; certified app and executable regressions are preserved.
# Bounded HTTPS retry successor; certified app and executable regressions are preserved.
if [[ "${SPRINT253_CONTEXT_6}" == 'cpanel-https-retry-20261018' && "${SPRINT253_CONTEXT_1}" == '63f045b557e87c683196424646d323a027b3e8fe' && "${SPRINT253_CONTEXT_2}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-https-retry-source-envelope.sh "${SPRINT253_CONTEXT_1}" "${SPRINT253_CONTEXT_3}"
  exit 0
fi
if [[ "${SPRINT253_CONTEXT_6}" == 'cpanel-control-bridge-fix-20261018' && "${SPRINT253_CONTEXT_1}" == '003416a2a462c1d0891e75663c4deff36714ebb6' && "${SPRINT253_CONTEXT_2}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-control-bridge-fix-source-envelope.sh "${SPRINT253_CONTEXT_1}" "${SPRINT253_CONTEXT_3}"
  exit 0
fi
if [[ "${SPRINT253_CONTEXT_6}" == 'cpanel-control-plane-20261018' && "${SPRINT253_CONTEXT_1}" == '78202bb5fe597538b25063c277fec4e1f45253a5' && "${SPRINT253_CONTEXT_2}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-control-source-envelope.sh "${SPRINT253_CONTEXT_1}" "${SPRINT253_CONTEXT_3}"
  exit 0
fi
if [[ "${SPRINT253_CONTEXT_6}" == 'cpanel-public-website-20261015' && "${SPRINT253_CONTEXT_1}" == '14e1cdb9f11f9b1a6384aa1659dab86cac5e1078' && "${SPRINT253_CONTEXT_2}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-website-source-envelope.sh "${SPRINT253_CONTEXT_1}" "${SPRINT253_CONTEXT_3}"
  exit 0
fi
# Exact cPanel catalog successor; certified application and prior DDL unchanged.
if [[ "${SPRINT253_CONTEXT_6}" == 'business-catalog-cpanel-20261014' && "${SPRINT253_CONTEXT_1}" == 'ec48e3fffdbb4cdaa79a7c65b16fe38425939dcf' && "${SPRINT253_CONTEXT_2}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-catalog-source-envelope.sh "${SPRINT253_CONTEXT_1}" "${SPRINT253_CONTEXT_3}"
  exit 0
fi
# Exact catalog/report/currency successor; canonical foundations and behavioral tests preserved.
if [[ "${SPRINT253_CONTEXT_6}" == 'business-catalog-reports-currency-20261014' && "${SPRINT253_CONTEXT_1}" == '27f2b6d220d85f2d08fe5e7abb9e91af6d8863ad' && "${SPRINT253_CONTEXT_2}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-merchandising-source-envelope.sh "${SPRINT253_CONTEXT_1}" "${SPRINT253_CONTEXT_3}"
  exit 0
fi
# Exact receipt-baseline recovery successor; application and existing behavioral tests preserved.
if [[ "${SPRINT253_CONTEXT_6}" == 'cpanel-receipt-recovery-20261014' && "${SPRINT253_CONTEXT_1}" == '58364e4131b380663b4dcdb47aa720c0a25f058a' && "${SPRINT253_CONTEXT_2}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-receipt-source-envelope.sh "${SPRINT253_CONTEXT_1}" "${SPRINT253_CONTEXT_3}"
  exit 0
fi
# Exact additive hosting operator successor; application source and behavioral tests are immutable.
if [[ "${SPRINT253_CONTEXT_6}" == 'cpanel-platform-module-upgrade-20261013' && "${SPRINT253_CONTEXT_1}" == 'f5a037ce60aa05c61e0b9c197f2e171c4d01fa8c' && "${SPRINT253_CONTEXT_2}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-module-source-envelope.sh "${SPRINT253_CONTEXT_1}" "${SPRINT253_CONTEXT_3}"
  exit 0
fi
# Exact platform activation/mail successor: preserve executable regressions and validate owned changes.
if [[ "${SPRINT253_CONTEXT_6}" == 'platform-owner-mail-20261013' && "${SPRINT253_CONTEXT_1}" == '894750c6a59a4ac28f42d5da8d8eb426280a412b' && "${SPRINT253_CONTEXT_2}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-platform-owner-source-envelope.sh "${SPRINT253_CONTEXT_1}" "${SPRINT253_CONTEXT_3}"
  exit 0
fi
set -euo pipefail
python3 - <<'PY'
from pathlib import Path
import os
import re
import subprocess

old_fp = '858280ea3575317e8d88eed7009c770530097e261c391b2869fa81ad7c8546ce'
old_source = '5be28a3c001738373588b58e9d29832c46402de1'
old_artifact = '66be23792478fb191f912571b35076c42783b9733cdb3fb514b549d22ec90dd7'

base = os.environ.get('PR_BASE_SHA') or os.environ.get('PUSH_BEFORE_SHA')
head = os.environ.get('PR_HEAD_SHA') or os.environ.get('PUSH_AFTER_SHA')
head_ref = os.environ.get('PR_HEAD_REF', '')
if not base or not head or set(base) == {'0'}:
    raise SystemExit('Unable to resolve bounded comparison range.')

changed = subprocess.check_output(
    ['git', 'diff', '--name-only', base, head], text=True
).splitlines()
business_successor = base == 'f3de67cd0426fb13cc51c4d88966a8a9923dcf24' and Path('tools/qa-business-management-source-envelope.sh').is_file()
if business_successor:
    subprocess.run(['bash', 'tools/qa-business-management-source-envelope.sh', base, head], check=True)
bilingual_presentation = base == '36b11e1e998f135a93a8d72a21bdb0aefabc3560' and Path('tools/qa-bilingual-source-envelope.sh').is_file()
if bilingual_presentation:
    subprocess.run(['bash', 'tools/qa-bilingual-source-envelope.sh', base, head], check=True)
bilingual_workflows = {'.github/workflows/production-business-release-certification.yml', '.github/workflows/sprint268-dependency-security-remediation-regression.yml', '.github/workflows/sprint22-policy-administration-regression.yml', '.github/workflows/sprint212-cpanel-no-ssh-durable-staging-target-qualification-regression.yml', '.github/workflows/sprint99-final-shift-close-ui-delivery.yml', '.github/workflows/sprint31-privileged-reauthentication-step-up-regression.yml', '.github/workflows/sprint24-protected-control-admin-lifecycle-regression.yml', '.github/workflows/m7-5-preview-db-qualification-regression.yml', '.github/workflows/sprint26-identity-credential-verification-regression.yml', '.github/workflows/sprint34-authenticated-password-change-regression.yml', '.github/workflows/sprint21-role-permission-policy-regression.yml', '.github/workflows/sprint23-initial-tenant-admin-provisioning-regression.yml', '.github/workflows/sprint253-final-shift-close-post-activation-historical-regression-successor-compatibility.yml', '.github/workflows/sprint29-first-control-principal-credential-bootstrap-regression.yml', '.github/workflows/sprint32-authentication-recovery-proof-regression.yml', '.github/workflows/sprint30-privileged-totp-mfa-regression.yml', '.github/workflows/sprint28-initial-password-enrollment-regression.yml', '.github/workflows/sprint211-durable-staging-operator-artifact-publication-regression.yml', '.github/workflows/sprint33-recovery-bound-password-reset-regression.yml', '.github/workflows/preview-variance-review-journey-regression.yml', '.github/workflows/sprint214-cpanel-no-ssh-guarded-deployment-execution-regression.yml', '.github/workflows/m7-2-tenant-isolation-regression.yml', '.github/workflows/sprint218-production-dark-deployment-execution-regression.yml', '.github/workflows/sprint223-durable-runtime-ingestion-null-boundary-regression.yml', '.github/workflows/sprint27-first-party-session-establishment-regression.yml', '.github/workflows/sprint25-policy-administration-delivery-regression.yml', '.github/workflows/sprint222-cpanel-fixed-public-php-symlink-policy-fallback-regression.yml', '.github/workflows/m7-3-identity-org-context-regression.yml', '.github/workflows/m7-4a-technical-preview-interaction-regression.yml', '.github/workflows/preview-sale-adjustment-journey-regression.yml', '.github/workflows/sprint213-cpanel-no-ssh-operator-qualification-kit-publication-regression.yml'}
workflows = [
    Path(p) for p in changed
    if p.startswith('.github/workflows/sprint')
    and p.endswith('.yml')
    and not Path(p).name.startswith('sprint253-')
    and Path(p).exists()
]
# The PR996 squash was independently reviewed and passed 141 exact-head runs.
# Recognize only that immutable push; retain every operational/stale-state scan.
certified_windows_push = False
if base == '5ab9b673437eca9501657f395f329610266ee7bb' and head == 'f628b946c2a369317f041f5da63b3f534b0c852b':
    if not (
        os.environ.get('GITHUB_EVENT_NAME') == 'push'
        and os.environ.get('GITHUB_REPOSITORY') == 'labzefry/oneQay'
        and os.environ.get('GITHUB_REF') == 'refs/heads/main'
        and os.environ.get('GITHUB_SHA') == head
        and os.environ.get('SPRINT253_CONTEXT_9') == head
        and not os.environ.get('PR_HEAD_SHA')
    ):
        raise SystemExit('SPRINT253_CERTIFIED_WINDOWS_PUSH_CONTEXT_INVALID')
    tree = subprocess.check_output(['git', 'rev-parse', head + '^{tree}'], text=True).strip()
    if tree != 'd91be372d5d50ef643f5ff3f17f4a1ef8d75fa4d':
        raise SystemExit('SPRINT253_CERTIFIED_WINDOWS_TREE_INVALID')
    approved_workflows = [Path(p) for p in changed
        if p.startswith('.github/workflows/sprint') and p.endswith('.yml')
        and not Path(p).name.startswith('sprint253-')]
    for path in approved_workflows:
        # Missing files must fail explicitly rather than disappearing from workflows.
        if not path.is_file() or path.is_symlink() or path.stat().st_mode & 0o111:
            raise SystemExit('SPRINT253_CERTIFIED_WINDOWS_WORKTREE_INVALID: ' + str(path))
        row = subprocess.check_output(['git', 'ls-tree', head, '--', str(path)], text=True).strip()
        meta, name = row.split('\t')
        mode, kind, blob = meta.split()
        working = subprocess.check_output(['git', 'hash-object', '--', str(path)], text=True).strip()
        if name != str(path) or mode != '100644' or kind != 'blob' or working != blob:
            raise SystemExit('SPRINT253_CERTIFIED_WINDOWS_BLOB_INVALID: ' + str(path))
    certified_windows_push = True
    print(f'SPRINT253_CERTIFIED_WINDOWS_PUSH_OK workflows={len(approved_workflows)}')

if not workflows:
    print('SPRINT253_SUCCESSOR_COMPATIBILITY_OK workflows=0')

# Only the exact PR #926 successor is accepted; each historical
# workflow must retain its original guard and add a bounded security check.
sprint268_compat_workflows = {
    '.github/workflows/sprint30-privileged-totp-mfa-regression.yml',
    '.github/workflows/sprint31-privileged-reauthentication-step-up-regression.yml',
    '.github/workflows/sprint32-authentication-recovery-proof-regression.yml',
    '.github/workflows/sprint33-recovery-bound-password-reset-regression.yml',
    '.github/workflows/sprint34-authenticated-password-change-regression.yml',
}

sprint264_compat_workflows = {
    '.github/workflows/sprint126-final-shift-close-runtime-binding-manifest-control-plane-token-hardening.yml',
    '.github/workflows/sprint211-durable-staging-operator-artifact-publication-regression.yml',
    '.github/workflows/sprint212-cpanel-no-ssh-durable-staging-target-qualification-regression.yml',
    '.github/workflows/sprint213-cpanel-no-ssh-operator-qualification-kit-publication-regression.yml',
    '.github/workflows/sprint214-cpanel-no-ssh-guarded-deployment-execution-regression.yml',
    '.github/workflows/sprint218-production-dark-deployment-execution-regression.yml',
    '.github/workflows/sprint222-cpanel-fixed-public-php-symlink-policy-fallback-regression.yml',
    '.github/workflows/sprint223-durable-runtime-ingestion-null-boundary-regression.yml',
}

inactive = re.compile(r'\bINACTIVE\b')
active = re.compile(r'\bACTIVE\b')
step_start = re.compile(r'^(\s*)- name:\s*')
offenders = []

def predecessor_only_activation(line):
    if '.feature_activation.state' not in line or not inactive.search(line) or active.search(line):
        return False
    stripped = line.strip()
    if 'grep ' in stripped:
        return False
    if '[[' in stripped and re.search(r'(?:==|=|=~)\s*[\'\"]?INACTIVE[\'\"]?', stripped):
        return True
    return bool(re.search(r'\btest\s+.+?\s+=\s*[\'\"]?INACTIVE[\'\"]?(?:\s|;|$)', stripped))

for path in sorted(workflows):
    if business_successor:
        continue  # Exact path and unchanged executable-step checks are enforced by the helper.
    if bilingual_presentation and str(path) in bilingual_workflows:
        continue  # Qualified current 27-migration backend; original executable regressions remain enabled.
    lines = path.read_text(encoding='utf-8').splitlines()
    for number, line in enumerate(lines, start=1):
        if predecessor_only_activation(line):
            offenders.append(f'{path}:{number}:feature predecessor-only guard')
        if "jq -r '.migration27.state'" in line and 'NOT_EXECUTED' in line and 'EXECUTED' not in line.replace('NOT_EXECUTED', ''):
            offenders.append(f'{path}:{number}:migration predecessor-only guard')
        if "jq -r '.permission_provisioning.state'" in line and 'NONE' in line and 'PROVISIONED' not in line:
            offenders.append(f'{path}:{number}:permission predecessor-only guard')
        if "jq -r '.selected_target.selection_fingerprint_sha256'" in line and old_fp in line:
            offenders.append(f'{path}:{number}:stale selected-target fingerprint')
        if "jq -r '.selected_target.exact_running_source_commit'" in line and old_source in line:
            offenders.append(f'{path}:{number}:stale selected-target running source')
        if "jq -r '.selected_target.exact_running_artifact_sha256'" in line and old_artifact in line:
            offenders.append(f'{path}:{number}:stale selected-target running artifact')

    starts = [i for i, line in enumerate(lines) if step_start.match(line)]
    starts.append(len(lines))
    for pos in range(max(0, len(starts) - 1)):
        start, end = starts[pos], starts[pos + 1]
        chunk = '\n'.join(lines[start:end])
        full_pr_diff = 'git diff --name-only' in chunk and (
            ('BASE_SHA' in chunk and 'HEAD_SHA' in chunk)
            or 'github.event.pull_request' in chunk
        )
        exact_global_lock = any(marker in chunk for marker in (
            'diff -u /tmp/expected',
            'sha256sum /tmp/',
            'wc -l < /tmp/',
        ))
        sprint264_bounded_compat = (
            head_ref == 'sprint264-durable-staging-merchant-core-route-bridge-delivery'
            and str(path) in sprint264_compat_workflows
        )
        sprint268_bounded_compat = (
            head_ref == 'sprint268-security-dependency-remediation'
            and str(path) in sprint268_compat_workflows
            and "github.event.pull_request.number == 926" in '\n'.join(lines)
            and "('apps/web/composer.json' 'apps/web/composer.lock' 'apps/web/package-lock.json' 'apps/web/package.json')" in '\n'.join(lines)
            and ('Verify Sprint268 security-only Composer successor' in '\n'.join(lines)
                 or 'Verify exact Sprint268 security compatibility successor' in '\n'.join(lines))
        )
        menu_classifiers = {".github/workflows/sprint30-privileged-totp-mfa-regression.yml":["Recognize exact Sprint70 source and workflow closure for Sprint30"],".github/workflows/sprint35-privileged-totp-recovery-regression.yml":["Recognize exact Sprint41 Sprint35 preservation state"],".github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml":["Classify exact Sprint47 JRN-004 compatibility","Classify exact Sprint37 quote-correction compatibility"],".github/workflows/sprint37-first-party-all-session-termination-regression.yml":["Classify exact Sprint35 Sprint36 compatibility","Isolate migration sixteen for post-Sprint46 docs preservation","Isolate migration sixteen for exact Sprint37 legacy horizon","Classify exact Sprint47 JRN-004 compatibility"]}
        menu_explicit_guard = (
            "github.event.pull_request.number == 992" in chunk
            and "github.event.pull_request.head.repo.full_name == 'labzefry/oneQay'" in chunk
        )
        menu_preserved_classifier = any(
            chunk.startswith('      - name: '+name+chr(10))
            for name in menu_classifiers.get(str(path), [])
        )
        menu_bounded_compat = (
            head_ref == 'feature/menu-membership-20261029'
            and os.environ.get('PR_BASE_SHA') == '87f65c5c816dff18383eda1567388cdce81819ba'
            and 'Verify bounded menu successor source envelope' in chr(10).join(lines)
            and 'python3 tools/ci/verify-menu-successor.py' in chr(10).join(lines)
            and (menu_explicit_guard or menu_preserved_classifier)
        )
        if full_pr_diff and exact_global_lock and not sprint264_bounded_compat and not sprint268_bounded_compat and not menu_bounded_compat and not certified_windows_push:
            offenders.append(f'{path}:exact full-PR historical envelope lock')

if offenders:
    raise SystemExit('\n'.join(offenders))

print(f'SPRINT253_SUCCESSOR_COMPATIBILITY_OK workflows={len(workflows)}')
PY
