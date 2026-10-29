#!/usr/bin/env python3
"""Validate CI fixture isolation and reject false-green PHP results using synthetic files."""
import os, pathlib, subprocess, tempfile
script=pathlib.Path(__file__).resolve().parents[1]/'run-historical-fixture.sh'
with tempfile.TemporaryDirectory(prefix='oneqay-fixture-test-') as directory:
    root=pathlib.Path(directory);repo=root/'repo';temp=root/'runner';binpath=root/'bin'
    migrations=repo/'apps/web/database/migrations';migrations.mkdir(parents=True)
    (repo/'apps/web/tests').mkdir(parents=True);temp.mkdir();binpath.mkdir()
    for i in range(1,28):(migrations/('0000_00_00_%06d_fixture.php'%i)).write_text('<?php\n')
    (binpath/'git').write_text('#!/bin/sh\nprintf "%s\\n" "$FIXTURE_REPO"\n')
    (binpath/'php').write_text('#!/bin/sh\ncount=$(find database/migrations -name "*.php" | wc -l)\n[ "$count" -eq 13 ] || exit 7\n[ "${FIXTURE_SILENT:-0}" = 1 ] || printf "%s\\n" "Sprint36 first-party session inventory/revocation regression passed."\n')
    for name in ['git','php']:(binpath/name).chmod(0o755)
    env=os.environ.copy();env.update({'PATH':str(binpath)+os.pathsep+env['PATH'],'FIXTURE_REPO':str(repo),'RUNNER_TEMP':str(temp),'GITHUB_ACTIONS':'true','GITHUB_REPOSITORY':'labzefry/oneQay'})
    def run(name,extra=None):
        return subprocess.run(['bash',str(script),name],env={**env,**(extra or {})},text=True,capture_output=True)
    assert run('s36').returncode==0
    failed=run('s36',{'FIXTURE_SILENT':'1'});assert failed.returncode!=0 and 'MISSING_PASS_MARKER' in failed.stderr
    assert run('unapproved').returncode!=0
    assert len(list(migrations.glob('*.php')))==27 and not list(temp.iterdir())
    print('HISTORICAL_FIXTURE_PASS assertions=4')
