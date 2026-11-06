#!/usr/bin/env python3
"""Exact source classification for PR #996 (Windows POS v0.2.8); does not authorize deployment."""
import json
import os
from pathlib import Path
import re
import subprocess
import sys

BASE = '5ab9b673437eca9501657f395f329610266ee7bb'
REPO = 'labzefry/oneQay'
BRANCH = 'pos/windows-028-persistent-session-20261106'
SELF = 'tools/ci/verify-windows-pos-successor.py'
MANIFEST = 'tools/ci/windows-pos-successor-files.json'

def git(*args):
    return subprocess.check_output(['git', *args])


def reject_unless(value, reason):
    if not value:
        raise ValueError(reason)


def verify():
    event = json.loads(Path(os.environ['GITHUB_EVENT_PATH']).read_text())
    pr = event['pull_request']
    reject_unless(os.environ.get('GITHUB_EVENT_NAME') == 'pull_request'
                  and os.environ.get('GITHUB_REPOSITORY') == REPO
                  and event['number'] == 996
                  and pr['base']['repo']['full_name'] == REPO
                  and pr['base']['ref'] == 'main'
                  and pr['base']['sha'] == BASE
                  and pr['head']['repo']['full_name'] == REPO
                  and pr['head']['ref'] == BRANCH, 'WINDOWS_POS_SUCCESSOR_EVENT_INVALID')
    reject_unless(os.environ.get('GITHUB_HEAD_REF') == BRANCH, 'WINDOWS_POS_SUCCESSOR_HEAD_REF_INVALID')
    head = pr['head']['sha']
    reject_unless(re.fullmatch(r'[a-f0-9]{40}', head), 'WINDOWS_POS_SUCCESSOR_HEAD_INVALID')
    reject_unless(subprocess.run(['git', 'merge-base', '--is-ancestor', BASE, head], check=False).returncode == 0,
                  'WINDOWS_POS_SUCCESSOR_BASE_NOT_ANCESTOR')
    recorded = json.loads(git('show', f'{head}:{MANIFEST}'))
    raw = git('diff', '--no-renames', '--name-status', '-z', BASE, head).decode().split('\0')
    changes = dict((path, status) for status, path in zip(raw[0::2], raw[1::2]))
    reject_unless(set(changes) == set(recorded) | {SELF, MANIFEST}
                  and set(changes.values()) <= {'A', 'M'}, 'WINDOWS_POS_SUCCESSOR_PATHS_INVALID')
    for path, status in changes.items():
        row = git('ls-tree', head, '--', path).decode().rstrip('\n')
        meta, actual_path = row.split('\t')
        mode, object_type, blob = meta.split()
        reject_unless(Path(path).is_file() and not Path(path).is_symlink(),
                      'WINDOWS_POS_SUCCESSOR_WORKTREE_FILE_INVALID: ' + path)
        working = git('hash-object', '--', path).decode().strip()
        reject_unless(actual_path == path and mode == '100644' and object_type == 'blob'
                      and working == blob, 'WINDOWS_POS_SUCCESSOR_WORKTREE_INVALID: ' + path)
        if path not in (SELF, MANIFEST):
            reject_unless(recorded[path] == [status, blob], 'WINDOWS_POS_SUCCESSOR_BLOB_CHANGED: ' + path)
    # Canonical auth implementations intentionally change and are tested against
    # frozen browser fixtures plus the current SQLite/MySQL native regressions.
    # Root migration history, historical harnesses and operational authority stay frozen.
    frozen = (
        'apps/web/database/migrations', 'apps/web/tests/run.php',
        'apps/web/tests/privileged-totp-mfa.php',
        'apps/web/tests/identity-credential-verification.php',
        'apps/web/tests/policy-administration-delivery.php',
        'apps/web/tests/first-control-principal-credential-bootstrap.php',
        'apps/web/tests/initial-password-enrollment.php',
        'apps/web/tests/initial-password-enrollment-current.php',
        'apps/web/tests/first-party-session-establishment.php',
        'tools/ci/historical',
        'tools/ci/run-historical-fixture.sh', 'apps/web/bootstrap/app.php',
        'apps/web/composer.json', 'apps/web/composer.lock',
        'tools/ci/verify-cashier-qr-successor.py', 'tools/ci/cashier-qr-successor-files.json',
        '.github/CODEOWNERS', 'AI_CONSTITUTION.md', 'apps/web/.env.example', 'apps/web/package.json', 'apps/web/package-lock.json',
        'ops/final-shift-close',
    )
    reject_unless(not git('diff', '--name-only', BASE, head, '--', *frozen),
                  'WINDOWS_POS_SUCCESSOR_HISTORICAL_SOURCE_CHANGED')
    root_paths = git('ls-tree','-r','--name-only',head,'--','apps/web/database/migrations').decode().splitlines()
    root_migrations = [p for p in root_paths if p.endswith('.php')]
    reject_unless(len(root_migrations) == 27 and
                  all(Path(p).is_file() and git('hash-object','--',p).strip() ==
                      git('rev-parse',f'{BASE}:{p}').strip() for p in root_migrations),
                  'WINDOWS_POS_SUCCESSOR_CANONICAL_MIGRATION_CHANGED')
    print('WINDOWS_POS_EXACT_SOURCE_ENVELOPE=PASS')


if __name__ == '__main__':
    try:
        # Preserve PR995's exact validator without modifying its original bytes.
        event = json.loads(Path(os.environ['GITHUB_EVENT_PATH']).read_text())
        if event.get('number') == 995:
            import runpy
            runpy.run_path('tools/ci/verify-cashier-qr-successor.py', run_name='__main__')
        else:
            verify()
    except (ValueError, KeyError, OSError, subprocess.CalledProcessError) as error:
        print(error, file=sys.stderr)
        sys.exit(1)
