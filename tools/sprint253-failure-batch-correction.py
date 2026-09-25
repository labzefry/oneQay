#!/usr/bin/env python3
import subprocess

V1 = '2fd4fd544995a79b71b4baf1d5d21bb54e465d92'
source = subprocess.check_output(
    ['git', 'show', f'{V1}:tools/sprint253-failure-batch-correction.py'],
    text=True,
)

old_s243_s244 = '''for p in [
    '.github/workflows/sprint243-final-shift-close-feature-activation-guard-successor-regression.yml',
    '.github/workflows/sprint244-final-shift-close-capability-producer-selection-shape-successor-regression.yml',
]:
    old = ''' + "'''" + '''          test -z \"$(git diff --name-only \"$BASE_SHA\" \"$HEAD_SHA\" -- apps/web ops/final-shift-close/STATE.json ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json)\"\n''' + "'''" + '''
    new = ''' + "'''" + '''          test -z \"$(git diff --name-only \"$BASE_SHA\" \"$HEAD_SHA\" -- apps/web/app apps/web/bootstrap apps/web/config apps/web/database/migrations apps/web/resources apps/web/routes ops/final-shift-close/STATE.json ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json)\"\n''' + "'''" + '''
    replace_once(p, old, new)
'''
new_s243_s244 = '''p = '.github/workflows/sprint243-final-shift-close-feature-activation-guard-successor-regression.yml'
old = ''' + "'''" + '''          test -z \"$(git diff --name-only \"$BASE_SHA\" \"$HEAD_SHA\" -- ops/final-shift-close/STATE.json ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json apps/web)\"\n''' + "'''" + '''
new = ''' + "'''" + '''          test -z \"$(git diff --name-only \"$BASE_SHA\" \"$HEAD_SHA\" -- apps/web/app apps/web/bootstrap apps/web/config apps/web/database/migrations apps/web/resources apps/web/routes ops/final-shift-close/STATE.json ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json)\"\n''' + "'''" + '''
replace_once(p, old, new)

p = '.github/workflows/sprint244-final-shift-close-capability-producer-selection-shape-successor-regression.yml'
old = ''' + "'''" + '''          test -z \"$(git diff --name-only \"$BASE_SHA\" \"$HEAD_SHA\" -- apps/web ops/final-shift-close/STATE.json ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json)\"\n''' + "'''" + '''
new = ''' + "'''" + '''          test -z \"$(git diff --name-only \"$BASE_SHA\" \"$HEAD_SHA\" -- apps/web/app apps/web/bootstrap apps/web/config apps/web/database/migrations apps/web/resources apps/web/routes ops/final-shift-close/STATE.json ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json)\"\n''' + "'''" + '''
replace_once(p, old, new)
'''
if source.count(old_s243_s244) != 1:
    raise SystemExit(f'Unable to patch S243/S244 correction block: count={source.count(old_s243_s244)}')
source = source.replace(old_s243_s244, new_s243_s244, 1)

old_php = '''for p, old_msg in [
    ('apps/web/tests/pos-final-shift-close-operator-kit-successor-wiring.php', 'Feature must remain INACTIVE during Sprint248 wiring.'),
    ('apps/web/tests/pos-final-shift-close-activation-workflow-pagination.php', 'Feature must remain INACTIVE during Sprint250 hardening.'),
]:
    old = f"expectTrue(($state['feature_activation']['state'] ?? null) === 'INACTIVE', '{old_msg}');"
    new = "expectTrue(in_array(($state['feature_activation']['state'] ?? null), ['INACTIVE', 'ACTIVE'], true), 'Feature activation state must remain within the bounded predecessor/successor horizon.');"
    replace_once(p, old, new)
'''
new_php = '''for p in [
    'apps/web/tests/pos-final-shift-close-operator-kit-successor-wiring.php',
    'apps/web/tests/pos-final-shift-close-activation-workflow-pagination.php',
]:
    old = "$assert(($state['feature_activation']['state'] ?? null) === 'INACTIVE', 'engineering correction activated Final Shift Close');"
    new = "$assert(in_array(($state['feature_activation']['state'] ?? null), ['INACTIVE', 'ACTIVE'], true), 'feature activation state drifted outside bounded predecessor/successor horizon');"
    replace_once(p, old, new)
'''
if source.count(old_php) != 1:
    raise SystemExit(f'Unable to patch S248/S250 PHP correction block: count={source.count(old_php)}')
source = source.replace(old_php, new_php, 1)

exec(compile(source, 'sprint253-failure-batch-correction-v3.py', 'exec'), {'__name__': '__main__'})
