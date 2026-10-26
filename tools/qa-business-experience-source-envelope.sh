#!/usr/bin/env bash
# Author by Lab | zefry. Exact successor source guard; all executable regression steps remain.
set -euo pipefail
python3 - "$1" "$2" <<'PYCODE'
import json,re,subprocess,sys
base,head=sys.argv[1:]
def git(*args):return subprocess.check_output(['git',*args])
assert base=='90a9b298c3b44a4cfa7700a23fc78d1eec0655b2','Predecessor changed; recertification required'
assert re.fullmatch('[a-f0-9]{40}',head),'Exact source head required'
manifest=json.loads(git('show',head+':docs/business-experience-source-manifest.json'))
changed=set(git('diff','--name-only',base,head).decode().splitlines())
assert changed==set(manifest['paths']),'Unexpected or missing paths: '+repr(changed^set(manifest['paths']))
assert not git('diff','--diff-filter=D','--name-only',base,head),'Deletions denied'
assert not git('diff','--name-only',base,head,'--','src','ops','apps/web/database/migrations','apps/web/composer.json','apps/web/composer.lock','apps/web/package.json','apps/web/package-lock.json','apps/web/app/Domain','apps/web/app/Application/Identity','apps/web/app/Delivery/Http/Identity','apps/web/app/Infrastructure/Runtime','apps/web/config/oneqay.php','apps/web/app/Providers/AppServiceProvider.php','apps/web/app/Providers/FinalShiftCloseServiceProvider.php','apps/web/routes/pos-final-shift-close.php'),'Canonical foundations, dependencies or runtime changed'
hook='          if [[ "${{ github.head_ref }}" == \'feat/business-workspaces-20261023\' && "${{ github.event.pull_request.base.sha }}" == \'90a9b298c3b44a4cfa7700a23fc78d1eec0655b2\' && "${{ github.event.pull_request.number }}" == \'987\' && "${{ github.event.pull_request.head.repo.full_name }}" == \'labzefry/oneQay\' ]]; then\n            bash tools/qa-business-experience-source-envelope.sh "${{ github.event.pull_request.base.sha }}" "${{ github.event.pull_request.head.sha }}" || exit 1\n            exit 0\n          fi\n'
for path in manifest['historical_workflows']:
 before=git('show',base+':'+path).decode();after=git('show',head+':'+path).decode()
 assert hook in after and after.replace(hook,'')==before,'Historical executable steps changed: '+path

for path, additions in {
 'apps/web/bootstrap/providers.php':['    App\\Providers\\BusinessExperienceServiceProvider::class,\n'],
 'apps/web/bootstrap/app.php':['            \\App\\Delivery\\Http\\Middleware\\BusinessHostMiddleware::class,\n','            \\App\\Delivery\\Http\\Middleware\\BusinessWorkflowMiddleware::class,\n'],
}.items():
 before=git('show',base+':'+path).decode();after=git('show',head+':'+path).decode()
 for addition in additions:
  assert after.count(addition)==1,'Additive runtime hook missing or duplicated'
  after=after.replace(addition,'')
 assert after==before,'Existing runtime provider/middleware registration changed'
p='apps/pos-desktop/src-tauri/tauri.conf.json'
before=json.loads(git('show',base+':'+p));after=json.loads(git('show',head+':'+p))
assert before['plugins']['updater']==after['plugins']['updater'],'Updater trust or endpoint changed'
assert before['identifier']==after['identifier'],'Windows application identity changed'
assert after['version']=='0.2.5','Unexpected Windows candidate version'
assert before['bundle']['windows']['nsis']['installMode']==after['bundle']['windows']['nsis']['installMode']=='perMachine','Installation mode changed'
assert set(git('diff','--name-only',base,head,'--','apps/web/database').decode().splitlines())=={'apps/web/database/module-migrations/platform/2026_10_23_000001_create_business_experience.php','apps/web/database/module-migrations/business/2026_10_23_000002_create_isolated_service_pos.php'},'Unexpected module schema changes'
print('BUSINESS_EXPERIENCE_EXACT_SOURCE_ENVELOPE_PASS paths='+str(len(changed)))
PYCODE
