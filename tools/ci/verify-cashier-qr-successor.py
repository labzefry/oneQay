#!/usr/bin/env python3
"""Exact source classification for PR #995; does not authorize deployment."""
import json
import os
from pathlib import Path
import re
import subprocess
import sys

BASE = '430cbbb469bc5afee4e8c67444b827943811e06f'
REPO = 'labzefry/oneQay'
BRANCH = 'feature/cashier-qr-membership-20261104'
SELF = 'tools/ci/verify-cashier-qr-successor.py'
MANIFEST = 'tools/ci/cashier-qr-successor-files.json'
COMPOSER_LOCK = 'apps/web/composer.lock'
SYMFONY_PATCHES = {
    'symfony/http-foundation': '7fb73cffa06e2bcc2cb523fcccb1e8174106b206',
    'symfony/http-kernel': '94c57f4c5c45a311d244a0aeadb72b14ee41d25d',
    'symfony/routing': '0000ade604b7bc798cb1e75edf6f17df48185f73',
}


def git(*args):
    return subprocess.check_output(['git', *args])


def reject_unless(value, reason):
    if not value:
        raise ValueError(reason)


def verify_composer_security_patch(head):
    old, new = (json.loads(git('show', f'{ref}:{COMPOSER_LOCK}')) for ref in (BASE, head))
    old_packages, new_packages = (lock.pop('packages') for lock in (old, new))
    reject_unless(old.pop('plugin-api-version') == '2.9.0'
                  and new.pop('plugin-api-version') == '2.11.0'
                  and old == new, 'CASHIER_SUCCESSOR_COMPOSER_METADATA_CHANGED')
    reject_unless([package['name'] for package in old_packages]
                  == [package['name'] for package in new_packages],
                  'CASHIER_SUCCESSOR_COMPOSER_PACKAGE_SET_CHANGED')
    for before, after in zip(old_packages, new_packages):
        ref = SYMFONY_PATCHES.get(after['name'])
        if ref is None:
            reject_unless(before == after, 'CASHIER_SUCCESSOR_COMPOSER_UNEXPECTED_CHANGE: ' + after['name'])
        else:
            reject_unless(before['version'] == 'v7.4.20' and after['version'] == 'v7.4.23'
                          and after['source']['reference'] == ref and after['dist']['reference'] == ref,
                          'CASHIER_SUCCESSOR_COMPOSER_PATCH_INVALID: ' + after['name'])


def verify():
    event = json.loads(Path(os.environ['GITHUB_EVENT_PATH']).read_text())
    pr = event['pull_request']
    reject_unless(os.environ.get('GITHUB_EVENT_NAME') == 'pull_request'
                  and os.environ.get('GITHUB_REPOSITORY') == REPO
                  and event['number'] == 995
                  and pr['base']['repo']['full_name'] == REPO
                  and pr['base']['ref'] == 'main'
                  and pr['base']['sha'] == BASE
                  and pr['head']['repo']['full_name'] == REPO
                  and pr['head']['ref'] == BRANCH, 'CASHIER_SUCCESSOR_EVENT_INVALID')
    head = pr['head']['sha']
    reject_unless(re.fullmatch(r'[a-f0-9]{40}', head), 'CASHIER_SUCCESSOR_HEAD_INVALID')
    recorded = json.loads(git('show', f'{head}:{MANIFEST}'))
    raw = git('diff', '--no-renames', '--name-status', '-z', BASE, head).decode().split('\0')
    changes = dict((path, status) for status, path in zip(raw[0::2], raw[1::2]))
    reject_unless(set(changes) == set(recorded) | {SELF, MANIFEST}
                  and set(changes.values()) <= {'A', 'M'}, 'CASHIER_SUCCESSOR_PATHS_INVALID')
    for path, status in changes.items():
        row = git('ls-tree', head, '--', path).decode().rstrip('\n')
        meta, actual_path = row.split('\t')
        mode, object_type, blob = meta.split()
        working = git('hash-object', '--', path).decode().strip()
        reject_unless(actual_path == path and mode == '100644' and object_type == 'blob'
                      and working == blob, 'CASHIER_SUCCESSOR_WORKTREE_INVALID: ' + path)
        if path not in (SELF, MANIFEST):
            reject_unless(recorded[path] == [status, blob], 'CASHIER_SUCCESSOR_BLOB_CHANGED: ' + path)
    # Legacy jobs expect their original migration horizon. No canonical
    # root migration, frozen fixture or bootstrap byte is part of this
    # successor. Composer can change only for the three audited patches.
    frozen = (
        'apps/web/database/migrations', 'apps/web/tests/run.php',
        'apps/web/tests/privileged-totp-mfa.php', 'tools/ci/historical',
        'tools/ci/run-historical-fixture.sh', 'apps/web/bootstrap/app.php',
        'apps/web/composer.json', 'apps/web/package.json', 'apps/web/package-lock.json',
        'ops/final-shift-close',
    )
    reject_unless(not git('diff', '--name-only', BASE, head, '--', *frozen),
                  'CASHIER_SUCCESSOR_HISTORICAL_SOURCE_CHANGED')
    verify_composer_security_patch(head)
    root_paths = git('ls-tree','-r','--name-only',head,'--','apps/web/database/migrations').decode().splitlines()
    root_migrations = [p for p in root_paths if p.endswith('.php')]
    reject_unless(len(root_migrations) == 27 and
                  all(Path(p).is_file() and git('hash-object','--',p).strip() ==
                      git('rev-parse',f'{BASE}:{p}').strip() for p in root_migrations),
                  'CASHIER_SUCCESSOR_CANONICAL_MIGRATION_CHANGED')
    print('CASHIER_QR_EXACT_SOURCE_ENVELOPE=PASS')


if __name__ == '__main__':
    try:
        verify()
    except (ValueError, KeyError, OSError, subprocess.CalledProcessError) as error:
        print(error, file=sys.stderr)
        sys.exit(1)
