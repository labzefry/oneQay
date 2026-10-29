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
print('MENU_SUCCESSOR_ENVELOPE_PASS assertions='+str(n))
