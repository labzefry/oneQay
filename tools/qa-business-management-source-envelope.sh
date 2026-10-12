#!/usr/bin/env bash
# Author by Lab | zefry. Bounded source compatibility; no execution or deployment authority.
set -euo pipefail
python3 - "$@" <<'PYCODE'
import json,re,subprocess,sys
base,head=sys.argv[1:]
assert base == 'f3de67cd0426fb13cc51c4d88966a8a9923dcf24', 'Unexpected business predecessor'
assert re.fullmatch('[0-9a-f]{40}',head), 'Invalid head'
def git(*args): return subprocess.check_output(['git',*args],text=True)
def source(ref,path): return git('show',ref+':'+path)
allowed=['.github/workflows/integrated-business-management.yml', '.github/workflows/m7-4a-technical-preview-interaction-regression.yml', '.github/workflows/sprint126-final-shift-close-runtime-binding-manifest-control-plane-token-hardening.yml', '.github/workflows/sprint211-durable-staging-operator-artifact-publication-regression.yml', '.github/workflows/sprint212-cpanel-no-ssh-durable-staging-target-qualification-regression.yml', '.github/workflows/sprint213-cpanel-no-ssh-operator-qualification-kit-publication-regression.yml', '.github/workflows/sprint214-cpanel-no-ssh-guarded-deployment-execution-regression.yml', '.github/workflows/sprint218-production-dark-deployment-execution-regression.yml', '.github/workflows/sprint222-cpanel-fixed-public-php-symlink-policy-fallback-regression.yml', '.github/workflows/sprint223-durable-runtime-ingestion-null-boundary-regression.yml', '.github/workflows/sprint253-final-shift-close-post-activation-historical-regression-successor-compatibility.yml', '.github/workflows/sprint268-dependency-security-remediation-regression.yml', 'apps/web/app/Application/Business/BusinessAccess.php', 'apps/web/app/Application/Business/BusinessInput.php', 'apps/web/app/Application/Business/BusinessManagement.php', 'apps/web/app/Application/Business/BusinessRepository.php', 'apps/web/app/Application/Business/BusinessRoleAdministration.php', 'apps/web/app/Application/Business/BusinessViolation.php', 'apps/web/app/Application/Business/FinancialManagement.php', 'apps/web/app/Application/Business/PartnerManagement.php', 'apps/web/app/Application/Business/ProcurementManagement.php', 'apps/web/app/Application/Business/WorkforceManagement.php', 'apps/web/app/Application/BusinessIntelligence/BusinessCommandCenter.php', 'apps/web/app/Delivery/Http/Business/BusinessAccessController.php', 'apps/web/app/Delivery/Http/Business/BusinessWorkspaceController.php', 'apps/web/app/Delivery/Http/Pos/PosOperationsHubController.php', 'apps/web/app/Infrastructure/Business/LaravelBusinessRepository.php', 'apps/web/app/Providers/BusinessManagementServiceProvider.php', 'apps/web/bootstrap/providers.php', 'apps/web/database/module-migrations/business/2026_10_12_000001_create_integrated_business_management.php', 'apps/web/resources/js/app.ts', 'apps/web/resources/js/i18n/catalog.json', 'apps/web/resources/js/layouts/MerchantShell.vue', 'apps/web/resources/js/pages/Business/Access.vue', 'apps/web/resources/js/pages/Business/Workspace.vue', 'apps/web/tests/business-role-administration.php', 'apps/web/tests/integrated-business-management.php', 'docs/operations/ONEQAY_INTEGRATED_BUSINESS_2026-10-12.md', 'tools/qa-business-management-source-envelope.sh', 'tools/qa-integrated-business-ui.mjs']
changes=git('diff','--name-status','--no-renames',base,head).splitlines()
assert changes and all(line.split('\t')[0] in ['A','M'] for line in changes), 'Deletion, renaming or type change denied'
paths={line.split('\t')[1] for line in changes}
assert paths <= set(allowed), 'Unexpected paths: '+repr(paths-set(allowed))
assert 'apps/web/app/Providers/BusinessManagementServiceProvider.php' in paths
providers='apps/web/bootstrap/providers.php'
addition='    App\\Providers\\BusinessManagementServiceProvider::class,\n'
assert source(head,providers).count(addition)==1
assert source(head,providers).replace(addition,'')==source(base,providers), 'Existing providers changed'
catalog='apps/web/resources/js/i18n/catalog.json'
before=json.loads(source(base,catalog));after=json.loads(source(head,catalog))
assert all(after.get(k)==v for k,v in before.items()), 'Existing translations must be preserved'
# Existing workflow triggers, permissions, dependencies and executable regressions stay byte-identical.
guards={'.github/workflows/sprint211-durable-staging-operator-artifact-publication-regression.yml': 'Prove application source remains untouched or bounded successor', '.github/workflows/sprint212-cpanel-no-ssh-durable-staging-target-qualification-regression.yml': 'Prove application source remains untouched or bounded successor', '.github/workflows/sprint213-cpanel-no-ssh-operator-qualification-kit-publication-regression.yml': 'Prove application source remains untouched or bounded successor', '.github/workflows/sprint214-cpanel-no-ssh-guarded-deployment-execution-regression.yml': 'Prove application source remains untouched or bounded successor', '.github/workflows/sprint218-production-dark-deployment-execution-regression.yml': 'Prove application source remains untouched or bounded successor', '.github/workflows/sprint222-cpanel-fixed-public-php-symlink-policy-fallback-regression.yml': 'Confirm no application or operational authority drift', '.github/workflows/sprint223-durable-runtime-ingestion-null-boundary-regression.yml': 'Confirm source-only corrective envelope', '.github/workflows/sprint126-final-shift-close-runtime-binding-manifest-control-plane-token-hardening.yml': 'Prove materialization semantics and operational state remain untouched', '.github/workflows/m7-4a-technical-preview-interaction-regression.yml': 'Preserve canonical migrations and reject Preview database credential session or enrollment implementation', '.github/workflows/sprint268-dependency-security-remediation-regression.yml': 'Verify historical or exact bounded npm-security successor paths', '.github/workflows/sprint253-final-shift-close-post-activation-historical-regression-successor-compatibility.yml': 'Prove bounded current-state successor compatibility'}
def chunks(text):
    return re.split(r'(?m)(?=      - (?:name:|uses:))',text)
for path,guard in guards.items():
    old,new=chunks(source(base,path)),chunks(source(head,path))
    assert len(old)==len(new), 'Workflow steps changed: '+path
    modified=0
    for a,b in zip(old,new):
        if a==b: continue
        assert a.splitlines()[0] == '      - name: '+guard, 'Executable step or metadata changed: '+path
        assert b.splitlines()[0] == a.splitlines()[0]
        assert 'qa-business-management-source-envelope.sh' in b
        modified+=1
    assert modified==1, 'Expected exactly one historical compatibility step: '+path
assert not git('diff','--name-only',base,head,'--','apps/web/database/migrations','apps/web/database/module-migrations/pos','apps/web/config','apps/web/routes','apps/web/bootstrap/app.php','apps/web/app/Application/Identity','apps/web/app/Application/Authorization','apps/web/app/Infrastructure/Identity','apps/web/app/Infrastructure/Authorization','apps/web/composer.json','apps/web/composer.lock','apps/web/package.json','apps/web/package-lock.json','ops/final-shift-close'), 'Protected foundation changed'
print('INTEGRATED_BUSINESS_BOUNDED_SOURCE_AND_EXECUTABLE_REGRESSION_PRESERVATION=PASS')
PYCODE
