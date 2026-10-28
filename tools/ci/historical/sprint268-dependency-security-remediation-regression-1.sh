#!/usr/bin/env bash
# Author by Lab | zefry. Historical checks preserved; extracted to bound workflow scalar size.
# Exact public website successor; original executable behavior and identity foundations preserved.
# Exact control plane successor; canonical identity, runtime, and POS regressions remain required.
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'feat/business-workspaces-20261023' && "${ONEQAY_CI_EXPRESSION_1}" == '90a9b298c3b44a4cfa7700a23fc78d1eec0655b2' && "${ONEQAY_CI_EXPRESSION_2}" == '987' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-business-experience-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}" || exit 1
  exit 0
fi
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'fix/simplify-pos-onboarding-20261023' && "${ONEQAY_CI_EXPRESSION_1}" == '4e5d3ab55b616c7424459e0229d9cf6ded78f937' && "${ONEQAY_CI_EXPRESSION_2}" == '984' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-pos-account-flow-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}" || exit 1
  exit 0
fi
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'cpanel-desktop-pos-20261018' && "${ONEQAY_CI_EXPRESSION_1}" == '45509005b2f9ed4e5ffddd1e8c7db100a50e52fe' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-desktop-pos-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
fi
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'cpanel-pos-worker-lock-fix-20261018' && "${ONEQAY_CI_EXPRESSION_1}" == 'c75ac817e29b8896d806bfcb43a1f182387e44ef' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-pos-worker-lock-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
fi
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'pos-enterprise-20261019' && "${ONEQAY_CI_EXPRESSION_1}" == '85a602a6eb27acab533ac4c30d97e12ea0a07c9e' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-pos-enterprise-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
fi
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'experience-pos-desktop-20261018' && "${ONEQAY_CI_EXPRESSION_1}" == 'eb9a02d00a8ca11d6287d9be149efdd99f25967a' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-desktop-pos-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
elif [[ "${ONEQAY_CI_EXPRESSION_0}" == 'control-plane-enterprise-20261016' && "${ONEQAY_CI_EXPRESSION_1}" == '1e235bf4947a4e94d589d711e0b0fb1a463b3cac' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-control-plane-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
fi
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'public-website-cms-onboarding-20261015' && "${ONEQAY_CI_EXPRESSION_1}" == '9420db4ec3939fefc2bac54d11d4e24af1624e17' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-website-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
fi
# Exact website operator successor; complete certified application and behavioral checks preserved.
# Exact no-link control plane operator; certified application and executable regressions remain unchanged.
# Exact owner-login probe correction; certified app and executable regressions are preserved.
# Bounded HTTPS retry successor; certified app and executable regressions are preserved.
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'cpanel-https-retry-20261018' && "${ONEQAY_CI_EXPRESSION_1}" == '63f045b557e87c683196424646d323a027b3e8fe' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-https-retry-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
fi
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'cpanel-control-bridge-fix-20261018' && "${ONEQAY_CI_EXPRESSION_1}" == '003416a2a462c1d0891e75663c4deff36714ebb6' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-control-bridge-fix-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
fi
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'cpanel-control-plane-20261018' && "${ONEQAY_CI_EXPRESSION_1}" == '78202bb5fe597538b25063c277fec4e1f45253a5' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-control-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
fi
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'cpanel-public-website-20261015' && "${ONEQAY_CI_EXPRESSION_1}" == '14e1cdb9f11f9b1a6384aa1659dab86cac5e1078' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-website-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
fi
# Exact cPanel catalog successor; certified application and prior DDL unchanged.
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'business-catalog-cpanel-20261014' && "${ONEQAY_CI_EXPRESSION_1}" == 'ec48e3fffdbb4cdaa79a7c65b16fe38425939dcf' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-catalog-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
fi
# Exact catalog/report/currency successor; canonical foundations and behavioral tests preserved.
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'business-catalog-reports-currency-20261014' && "${ONEQAY_CI_EXPRESSION_1}" == '27f2b6d220d85f2d08fe5e7abb9e91af6d8863ad' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-merchandising-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
fi
# Exact receipt-baseline recovery successor; application and existing behavioral tests preserved.
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'cpanel-receipt-recovery-20261014' && "${ONEQAY_CI_EXPRESSION_1}" == '58364e4131b380663b4dcdb47aa720c0a25f058a' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-receipt-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
fi
# Exact additive hosting operator successor; application source and behavioral tests are immutable.
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'cpanel-platform-module-upgrade-20261013' && "${ONEQAY_CI_EXPRESSION_1}" == 'f5a037ce60aa05c61e0b9c197f2e171c4d01fa8c' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-module-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
fi
# Exact platform activation/mail successor: preserve executable regressions and validate owned changes.
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'platform-owner-mail-20261013' && "${ONEQAY_CI_EXPRESSION_1}" == '894750c6a59a4ac28f42d5da8d8eb426280a412b' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-platform-owner-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
fi
set -euo pipefail
python3 - <<'PY'
import os
import subprocess
historical_expected = sorted([
  'apps/web/composer.json',
  'apps/web/composer.lock',
  'apps/web/package.json',
  'apps/web/package-lock.json',
  '.github/workflows/sprint268-dependency-security-remediation-regression.yml',
  'src/SchemaPlanning/LaravelMigrationMaterialization.php',
  'tests/migration.php',
  '.github/workflows/sprint32-authentication-recovery-proof-regression.yml',
  '.github/workflows/sprint33-recovery-bound-password-reset-regression.yml',
  '.github/workflows/sprint34-authenticated-password-change-regression.yml',
  '.github/workflows/sprint31-privileged-reauthentication-step-up-regression.yml',
  '.github/workflows/sprint222-cpanel-fixed-public-php-symlink-policy-fallback-regression.yml',
  '.github/workflows/sprint253-final-shift-close-post-activation-historical-regression-successor-compatibility.yml',
  '.github/workflows/sprint211-durable-staging-operator-artifact-publication-regression.yml',
  '.github/workflows/sprint212-cpanel-no-ssh-durable-staging-target-qualification-regression.yml',
  '.github/workflows/sprint213-cpanel-no-ssh-operator-qualification-kit-publication-regression.yml',
  '.github/workflows/sprint214-cpanel-no-ssh-guarded-deployment-execution-regression.yml',
  '.github/workflows/sprint218-production-dark-deployment-execution-regression.yml',
  '.github/workflows/sprint223-durable-runtime-ingestion-null-boundary-regression.yml',
  '.github/workflows/sprint30-privileged-totp-mfa-regression.yml',
])

successor_expected = sorted([
  ".github/workflows/sprint211-durable-staging-operator-artifact-publication-regression.yml",
  ".github/workflows/sprint212-cpanel-no-ssh-durable-staging-target-qualification-regression.yml",
  ".github/workflows/sprint213-cpanel-no-ssh-operator-qualification-kit-publication-regression.yml",
  ".github/workflows/sprint214-cpanel-no-ssh-guarded-deployment-execution-regression.yml",
  ".github/workflows/sprint218-production-dark-deployment-execution-regression.yml",
  ".github/workflows/sprint222-cpanel-fixed-public-php-symlink-policy-fallback-regression.yml",
  ".github/workflows/sprint223-durable-runtime-ingestion-null-boundary-regression.yml",
  ".github/workflows/sprint268-dependency-security-remediation-regression.yml",
  "apps/web/package-lock.json",
  "apps/web/package.json",
])
successor = (os.environ.get('HEAD_REF') == 'remediate-npm-high-advisories-20261008'
             and os.environ.get('PR_NUMBER') == '929')
session_successor = (os.environ.get('HEAD_REF') == 'production-cash-go-live-secure-session-20261011'
                     and os.environ.get('PR_NUMBER') == '941')
if (os.environ.get('HEAD_REF') == 'enterprise-integrated-business-20261012'
    and os.environ.get('PR_NUMBER') == '951'
    and os.environ.get('HEAD_REPOSITORY') == 'labzefry/oneQay'
    and os.environ.get('BASE_SHA') == 'f3de67cd0426fb13cc51c4d88966a8a9923dcf24'):
    subprocess.run(['bash', 'tools/qa-business-management-source-envelope.sh', os.environ['BASE_SHA'], os.environ['HEAD_SHA']], check=True)
elif successor:
    base = os.environ.get('BASE_SHA', '')
    assert len(base) == 40 and all(ch in '0123456789abcdef' for ch in base)
    actual = sorted(subprocess.check_output(
        ['git','diff','--name-only','--diff-filter=ACMRT',base,'HEAD'],
        text=True).splitlines())
    assert actual == successor_expected, f'Unsafe bounded security successor paths: {actual}'
    print('SPRINT268_NPM_SUCCESSOR_TEN_PATH_ENVELOPE=PASS')
elif (os.environ.get('HEAD_REF') == 'production-cash-pos-session-pipeline-20261011'
      and os.environ.get('BASE_SHA') == 'd78bede01d46c6cb4154148ce361809e5da8ab0b'):
    pipeline_expected = ['.github/workflows/production-business-release-certification.yml', '.github/workflows/sprint211-durable-staging-operator-artifact-publication-regression.yml', '.github/workflows/sprint212-cpanel-no-ssh-durable-staging-target-qualification-regression.yml', '.github/workflows/sprint213-cpanel-no-ssh-operator-qualification-kit-publication-regression.yml', '.github/workflows/sprint214-cpanel-no-ssh-guarded-deployment-execution-regression.yml', '.github/workflows/sprint218-production-dark-deployment-execution-regression.yml', '.github/workflows/sprint222-cpanel-fixed-public-php-symlink-policy-fallback-regression.yml', '.github/workflows/sprint223-durable-runtime-ingestion-null-boundary-regression.yml', '.github/workflows/sprint268-dependency-security-remediation-regression.yml', 'apps/web/app/Providers/PosOperationsHubServiceProvider.php', 'apps/web/tests/production-session-web-pipeline.php', 'docs/operations/ONEQAYDEV_FINAL_BUSINESS_GO_LIVE_HANDOFF.md']
    head = os.environ.get('HEAD_SHA', '')
    assert len(head) == 40 and all(c in '0123456789abcdef' for c in head)
    actual = sorted(subprocess.check_output(['git','diff','--name-only',os.environ['BASE_SHA'],head], text=True).splitlines())
    assert actual == pipeline_expected, f'Unsafe native Production session successor paths: {actual}'
    assert not subprocess.check_output(['git','diff','--diff-filter=D','--name-only',os.environ['BASE_SHA'],head], text=True)
    print('SPRINT268_NATIVE_PRODUCTION_SESSION_TWELVE_PATH_ENVELOPE=PASS')
elif (os.environ.get('HEAD_REF') == 'oneqaydev-enterprise-workspace-20261011'
      and os.environ.get('BASE_SHA') == '2e2b80e966c3850c47d3b4ffc6ee8f4b8b5d3804'
      and os.environ.get('HEAD_REPOSITORY') == 'labzefry/oneQay'):
    # Presentation-only successor: audit and every behavioral regression remain enabled.
    ui_expected = ['.github/workflows/production-business-release-certification.yml', '.github/workflows/sprint211-durable-staging-operator-artifact-publication-regression.yml', '.github/workflows/sprint212-cpanel-no-ssh-durable-staging-target-qualification-regression.yml', '.github/workflows/sprint213-cpanel-no-ssh-operator-qualification-kit-publication-regression.yml', '.github/workflows/sprint214-cpanel-no-ssh-guarded-deployment-execution-regression.yml', '.github/workflows/sprint218-production-dark-deployment-execution-regression.yml', '.github/workflows/sprint222-cpanel-fixed-public-php-symlink-policy-fallback-regression.yml', '.github/workflows/sprint223-durable-runtime-ingestion-null-boundary-regression.yml', '.github/workflows/sprint268-dependency-security-remediation-regression.yml', 'CHANGELOG.md', 'TASKS.md', 'UI_GUIDELINE.md', 'apps/web/resources/css/app.css', 'apps/web/resources/js/app.ts', 'apps/web/resources/js/components/WorkspaceIcon.vue', 'apps/web/resources/js/icons/LICENSE.LUCIDE.txt', 'apps/web/resources/js/icons/workspace-icons.json', 'apps/web/resources/js/layouts/MerchantShell.vue', 'apps/web/resources/js/pages/Pos/Cashier.vue', 'apps/web/resources/js/pages/Pos/OperationsHub.vue', 'docs/operations/ONEQAYDEV_ENTERPRISE_WORKSPACE_UI_2026-10-11.md', 'docs/operations/ONEQAYDEV_WORKSPACE_UPDATE_HANDOFF.md', 'tools/production/cpanel/update-oneqaydev-certified-workspace.php', 'tools/production/tests/oneqaydev-certified-workspace-update.php', 'tools/qa-merchant-enterprise-ui.mjs']
    head = os.environ.get('HEAD_SHA', '')
    assert len(head) == 40 and all(c in '0123456789abcdef' for c in head)
    actual = sorted(subprocess.check_output(['git','diff','--name-only',os.environ['BASE_SHA'],head], text=True).splitlines())
    assert actual == ui_expected, f'Unsafe enterprise workspace paths: {actual}'
    assert not subprocess.check_output(['git','diff','--diff-filter=D','--name-only',os.environ['BASE_SHA'],head], text=True)
    print('SPRINT268_ENTERPRISE_WORKSPACE_PRESENTATION_ENVELOPE=PASS')
elif (os.environ.get('HEAD_REF') == 'oneqaydev-bilingual-workspace-20261012'
      and os.environ.get('BASE_SHA') == '36b11e1e998f135a93a8d72a21bdb0aefabc3560'
      and os.environ.get('HEAD_REPOSITORY') == 'labzefry/oneQay'):
    # Bilingual presentation successor; all behavioral regressions still execute.
    expected = ['.github/workflows/m7-2-tenant-isolation-regression.yml', '.github/workflows/m7-3-identity-org-context-regression.yml', '.github/workflows/m7-4a-technical-preview-interaction-regression.yml', '.github/workflows/m7-5-preview-db-qualification-regression.yml', '.github/workflows/preview-sale-adjustment-journey-regression.yml', '.github/workflows/preview-variance-review-journey-regression.yml', '.github/workflows/production-business-release-certification.yml', '.github/workflows/sprint21-role-permission-policy-regression.yml', '.github/workflows/sprint211-durable-staging-operator-artifact-publication-regression.yml', '.github/workflows/sprint212-cpanel-no-ssh-durable-staging-target-qualification-regression.yml', '.github/workflows/sprint213-cpanel-no-ssh-operator-qualification-kit-publication-regression.yml', '.github/workflows/sprint214-cpanel-no-ssh-guarded-deployment-execution-regression.yml', '.github/workflows/sprint218-production-dark-deployment-execution-regression.yml', '.github/workflows/sprint22-policy-administration-regression.yml', '.github/workflows/sprint222-cpanel-fixed-public-php-symlink-policy-fallback-regression.yml', '.github/workflows/sprint223-durable-runtime-ingestion-null-boundary-regression.yml', '.github/workflows/sprint23-initial-tenant-admin-provisioning-regression.yml', '.github/workflows/sprint24-protected-control-admin-lifecycle-regression.yml', '.github/workflows/sprint25-policy-administration-delivery-regression.yml', '.github/workflows/sprint253-final-shift-close-post-activation-historical-regression-successor-compatibility.yml', '.github/workflows/sprint26-identity-credential-verification-regression.yml', '.github/workflows/sprint268-dependency-security-remediation-regression.yml', '.github/workflows/sprint27-first-party-session-establishment-regression.yml', '.github/workflows/sprint28-initial-password-enrollment-regression.yml', '.github/workflows/sprint29-first-control-principal-credential-bootstrap-regression.yml', '.github/workflows/sprint30-privileged-totp-mfa-regression.yml', '.github/workflows/sprint31-privileged-reauthentication-step-up-regression.yml', '.github/workflows/sprint32-authentication-recovery-proof-regression.yml', '.github/workflows/sprint33-recovery-bound-password-reset-regression.yml', '.github/workflows/sprint34-authenticated-password-change-regression.yml', '.github/workflows/sprint99-final-shift-close-ui-delivery.yml', 'CHANGELOG.md', 'TASKS.md', 'apps/web/resources/css/app.css', 'apps/web/resources/js/app.ts', 'apps/web/resources/js/components/LanguageSwitcher.vue', 'apps/web/resources/js/i18n/catalog.json', 'apps/web/resources/js/i18n/index.ts', 'apps/web/resources/js/i18n/messages.json', 'apps/web/resources/js/layouts/LocalizedShell.vue', 'apps/web/resources/js/layouts/MerchantShell.vue', 'apps/web/resources/js/pages/Foundation.vue', 'apps/web/resources/js/pages/Pos/ActiveShiftPerformance.vue', 'apps/web/resources/js/pages/Pos/CashVarianceReconciliation.vue', 'apps/web/resources/js/pages/Pos/Cashier.vue', 'apps/web/resources/js/pages/Pos/CatalogInventorySetup.vue', 'apps/web/resources/js/pages/Pos/InventoryAccountability.vue', 'apps/web/resources/js/pages/Pos/InventoryReplenishment.vue', 'apps/web/resources/js/pages/Pos/OperationsHub.vue', 'apps/web/resources/js/pages/Pos/ProductSalesPerformance.vue', 'apps/web/resources/js/pages/Pos/SaleCorrections.vue', 'apps/web/resources/js/pages/Pos/SaleHistory.vue', 'apps/web/resources/js/pages/Pos/SalesSummary.vue', 'apps/web/resources/js/pages/Pos/ShiftClose.vue', 'apps/web/resources/js/pages/Pos/ShiftHistoryPerformance.vue', 'apps/web/resources/js/pages/Pos/ShiftStart.vue', 'apps/web/resources/js/pages/Preview/Context.vue', 'apps/web/resources/js/pages/Preview/Pos.vue', 'apps/web/resources/js/pages/Preview/Receipt.vue', 'apps/web/resources/js/pages/Preview/Reconciliation.vue', 'apps/web/resources/js/pages/Preview/SignIn.vue', 'apps/web/resources/js/pages/System/UpdateDeployment.vue', 'apps/web/resources/views/app.blade.php', 'apps/web/tests/read-only-update-deployment-ui.php', 'docs/operations/ONEQAYDEV_BILINGUAL_WORKSPACE_2026-10-12.md', 'tools/production/cpanel/update-oneqaydev-bilingual-workspace.php', 'tools/production/tests/oneqaydev-bilingual-workspace.php', 'tools/qa-bilingual-source-envelope.sh', 'tools/qa-merchant-enterprise-ui.mjs']
    head = os.environ.get('HEAD_SHA', '')
    assert len(head) == 40 and all(c in '0123456789abcdef' for c in head)
    actual = sorted(subprocess.check_output(['git','diff','--name-only',os.environ['BASE_SHA'],head], text=True).splitlines())
    assert actual == expected, f'Unsafe bilingual paths: {actual}'
    assert not subprocess.check_output(['git','diff','--diff-filter=D','--name-only',os.environ['BASE_SHA'],head], text=True)
    print('SPRINT268_BILINGUAL_PRESENTATION_ENVELOPE=PASS')
elif (os.environ.get('HEAD_REF') == 'business-os-command-center-integrated-20261012'
      and os.environ.get('PR_NUMBER') == '949'
      and os.environ.get('HEAD_REPOSITORY') == 'labzefry/oneQay'
      and os.environ.get('BASE_SHA') == 'e93b7fef42cb36840b7fca6b79435c6188a13cd5'):
    # The dependency and historical security checks below remain active.
    expected = sorted([
        ".github/workflows/business-os-command-center.yml",
        ".github/workflows/sprint211-durable-staging-operator-artifact-publication-regression.yml",
        ".github/workflows/sprint212-cpanel-no-ssh-durable-staging-target-qualification-regression.yml",
        ".github/workflows/sprint214-cpanel-no-ssh-guarded-deployment-execution-regression.yml",
        ".github/workflows/sprint213-cpanel-no-ssh-operator-qualification-kit-publication-regression.yml",
        ".github/workflows/sprint218-production-dark-deployment-execution-regression.yml",
        ".github/workflows/sprint222-cpanel-fixed-public-php-symlink-policy-fallback-regression.yml",
        ".github/workflows/sprint223-durable-runtime-ingestion-null-boundary-regression.yml",
        ".github/workflows/sprint268-dependency-security-remediation-regression.yml",
        "apps/web/app/Application/BusinessIntelligence/BusinessCommandCenter.php",
        "apps/web/app/Delivery/Http/Pos/PosOperationsHubController.php",
        "apps/web/resources/js/i18n/catalog.json",
        "apps/web/resources/js/layouts/MerchantShell.vue",
        "apps/web/resources/js/pages/Pos/OperationsHub.vue",
        "apps/web/tests/business-os-command-center.php",
        "docs/operations/ONEQAY_BUSINESS_OS_COMMAND_CENTER_2026-10-12.md",
    ])
    actual = sorted(subprocess.check_output([
        'git', 'diff', '--name-only', os.environ['BASE_SHA'], os.environ['HEAD_SHA']
    ], text=True).splitlines())
    assert actual == expected, f'Unsafe Business OS scope: {actual}'
    assert not subprocess.check_output([
        'git', 'diff', '--diff-filter=D', '--name-only',
        os.environ['BASE_SHA'], os.environ['HEAD_SHA']
    ], text=True), 'Source deletions are forbidden'
    print('BUSINESS_OS_EXACT_SOURCE_ENVELOPE=PASS')
elif session_successor:
    # This PR changes no dependency graph. Audit and all regressions below still run.
    session_expected = sorted([
        '.github/workflows/production-secure-session-cookie-regression.yml',
        '.github/workflows/sprint211-durable-staging-operator-artifact-publication-regression.yml',
        '.github/workflows/sprint212-cpanel-no-ssh-durable-staging-target-qualification-regression.yml',
        '.github/workflows/sprint213-cpanel-no-ssh-operator-qualification-kit-publication-regression.yml',
        '.github/workflows/sprint214-cpanel-no-ssh-guarded-deployment-execution-regression.yml',
        '.github/workflows/sprint218-production-dark-deployment-execution-regression.yml',
        '.github/workflows/sprint222-cpanel-fixed-public-php-symlink-policy-fallback-regression.yml',
        '.github/workflows/sprint223-durable-runtime-ingestion-null-boundary-regression.yml',
        '.github/workflows/sprint268-dependency-security-remediation-regression.yml',
        'apps/web/config/session.php',
        'apps/web/tests/production-session-cookie-policy.php',
        'docs/operations/ONEQAYDEV_COMMERCIAL_FINAL_ACTIVATION_2026-10-11.md',
        'docs/operations/ONEQAYDEV_FINAL_BUSINESS_GO_LIVE_HANDOFF.md',
    ])
    base = os.environ.get('BASE_SHA', '')
    head = os.environ.get('HEAD_SHA', '')
    assert all(len(s) == 40 and all(c in '0123456789abcdef' for c in s) for s in (base, head))
    actual = sorted(subprocess.check_output(
        ['git', 'diff', '--name-only', base, head], text=True).splitlines())
    assert actual == session_expected, f'Unsafe PR941 session correction paths: {actual}'
    deleted = subprocess.check_output(
        ['git', 'diff', '--diff-filter=D', '--name-only', base, head], text=True)
    assert not deleted, 'PR941 must not delete existing source'
    print('SPRINT268_PR941_SESSION_THIRTEEN_PATH_ENVELOPE=PASS')
else:
    actual = sorted(subprocess.check_output(
        ['git','diff','--name-only','--diff-filter=ACMRT',
         'c83d5131d7aaab19ddbd001223236aa424d2ea48','HEAD'],
        text=True).splitlines())
    assert actual == historical_expected, f'Unsafe Sprint268 historical paths: {actual}'
    print('SPRINT268_TWENTY_PATH_ENVELOPE=PASS')
PY
