#!/usr/bin/env python3
"""Synthetic event/path tests; no repository or network mutation."""
import copy, importlib.util, pathlib
path=pathlib.Path(__file__).resolve().parents[1]/'verify-menu-successor.py'
spec=importlib.util.spec_from_file_location('successor',path);m=importlib.util.module_from_spec(spec);spec.loader.exec_module(m)
event={'number':992,'pull_request':{'base':{'ref':'main','sha':m.BASE,'repo':{'full_name':'labzefry/oneQay'}},'head':{'ref':'feature/menu-membership-20261029','sha':'a'*40,'repo':{'full_name':'labzefry/oneQay'}}}}
changes=[('M',p) for p in sorted(m.EXPECTED)]
m.validate(event,'pull_request','labzefry/oneQay',changes);n=1
cases=[]
v=copy.deepcopy(event);v['number']=993;cases.append((v,'pull_request','labzefry/oneQay',changes))
v=copy.deepcopy(event);v['pull_request']['base']['sha']='b'*40;cases.append((v,'pull_request','labzefry/oneQay',changes))
v=copy.deepcopy(event);v['pull_request']['head']['repo']['full_name']='foreign/fork';cases.append((v,'pull_request','labzefry/oneQay',changes))
cases.append((event,'push','labzefry/oneQay',changes))
cases.append((event,'pull_request','labzefry/oneQay',changes[:-1]))
cases.append((event,'pull_request','labzefry/oneQay',changes+[('A','unapproved.php')]))
cases.append((event,'pull_request','labzefry/oneQay',[('D',changes[0][1])]+changes[1:]))
for args in cases:
    try:m.validate(*args)
    except ValueError:n+=1
    else:raise AssertionError('UNSAFE_SUCCESSOR_ACCEPTED')
fixture='      - name: classify\n        run: echo original\n      - name: next\n        run: true\n'
assert m.step(fixture,'classify').startswith('      - name: classify');n+=1
m.CLASSIFIERS={'workflow.yml':['classify']}
m.subprocess.check_output=lambda args,text:fixture if args[-1].startswith(m.BASE+':') else fixture.replace('original','tampered')
try:m.verify_classifiers('a'*40)
except ValueError as error:
    assert str(error)=='MENU_SUCCESSOR_CLASSIFIER_CHANGED';n+=1
else:raise AssertionError('MODIFIED_CLASSIFIER_ACCEPTED')
required=[
 '0000_00_00_000009_create_identity_totp_factors.php',
 '0000_00_00_000010_create_identity_recovery_codes.php',
 '0000_00_00_000011_add_credential_epoch_to_identity_password_credentials.php',
 '0000_00_00_000012_add_totp_factor_epoch_and_recovery_authority.php',
 '0000_00_00_000013_create_first_party_session_authority.php']
paths=['apps/web/database/migrations/'+name for name in required]+['apps/web/database/migrations/synthetic-%02d.php'%i for i in range(22)]
m.subprocess.check_output=lambda args,text:chr(10).join(paths)
m.subprocess.run=lambda *args,**kwargs:None
m.verify_migrations('a'*40);n+=1
m.subprocess.check_output=lambda args,text:chr(10).join(paths[:-1])
try:m.verify_migrations('a'*40)
except ValueError as error:
    assert str(error)=='MENU_BASE_MIGRATION_COUNT_INVALID';n+=1
else:raise AssertionError('WRONG_MIGRATION_COUNT_ACCEPTED')
# Approved successor inputs must match both the committed tree and working copy.
def pinned_output(args, text):
    path=args[-1].split(':',1)[-1] if args[1]=='rev-parse' else args[-1]
    return m.FROZEN_INPUTS[path]+'\n'
m.subprocess.check_output=pinned_output
m.verify_frozen_inputs('a'*40);n+=1
for changed_command in ['rev-parse','hash-object']:
    def changed_output(args,text):
        return '0'*40 if args[1]==changed_command else pinned_output(args,text)
    m.subprocess.check_output=changed_output
    try:m.verify_frozen_inputs('a'*40)
    except ValueError:n+=1
    else:raise AssertionError('MODIFIED_FROZEN_INPUT_ACCEPTED')
print('MENU_SUCCESSOR_ENVELOPE_PASS assertions='+str(n))
