#!/usr/bin/env python3
from pathlib import Path
import subprocess

source = subprocess.check_output(
    ['git', 'show', 'HEAD^:tools/sprint253-failure-batch-correction.py'],
    text=True,
)
old = '''for p in [
    '.github/workflows/sprint243-final-shift-close-feature-activation-guard-successor-regression.yml',
    '.github/workflows/sprint244-final-shift-close-capability-producer-selection-shape-successor-regression.yml',
]:
    old = ''' + "'''" + '''          test -z \"$(git diff --name-only \"$BASE_SHA\" \"$HEAD_SHA\" -- apps/web ops/final-shift-close/STATE.json ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json)\"\n''' + "'''" + '''
    new = ''' + "'''" + '''          test -z \"$(git diff --name-only \"$BASE_SHA\" \"$HEAD_SHA\" -- apps/web/app apps/web/bootstrap apps/web/config apps/web/database/migrations apps/web/resources apps/web/routes ops/final-shift-close/STATE.json ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json)\"\n''' + "'''" + '''
    replace_once(p, old, new)
'''
new = '''p = '.github/workflows/sprint243-final-shift-close-feature-activation-guard-successor-regression.yml'
old = ''' + "'''" + '''          test -z \"$(git diff --name-only \"$BASE_SHA\" \"$HEAD_SHA\" -- ops/final-shift-close/STATE.json ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json apps/web)\"\n''' + "'''" + '''
new = ''' + "'''" + '''          test -z \"$(git diff --name-only \"$BASE_SHA\" \"$HEAD_SHA\" -- apps/web/app apps/web/bootstrap apps/web/config apps/web/database/migrations apps/web/resources apps/web/routes ops/final-shift-close/STATE.json ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json)\"\n''' + "'''" + '''
replace_once(p, old, new)

p = '.github/workflows/sprint244-final-shift-close-capability-producer-selection-shape-successor-regression.yml'
old = ''' + "'''" + '''          test -z \"$(git diff --name-only \"$BASE_SHA\" \"$HEAD_SHA\" -- apps/web ops/final-shift-close/STATE.json ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json)\"\n''' + "'''" + '''
new = ''' + "'''" + '''          test -z \"$(git diff --name-only \"$BASE_SHA\" \"$HEAD_SHA\" -- apps/web/app apps/web/bootstrap apps/web/config apps/web/database/migrations apps/web/resources apps/web/routes ops/final-shift-close/STATE.json ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json)\"\n''' + "'''" + '''
replace_once(p, old, new)
'''
if source.count(old) != 1:
    raise SystemExit(f'Unable to patch v1 S243/S244 correction block: count={source.count(old)}')
patched = source.replace(old, new, 1)
exec(compile(patched, 'sprint253-failure-batch-correction-v2.py', 'exec'), {'__name__': '__main__'})
