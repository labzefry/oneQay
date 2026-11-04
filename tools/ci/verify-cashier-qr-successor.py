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
    print('CASHIER_QR_EXACT_SOURCE_ENVELOPE=PASS')


if __name__ == '__main__':
    try:
        verify()
    except (ValueError, KeyError, OSError, subprocess.CalledProcessError) as error:
        print(error, file=sys.stderr)
        sys.exit(1)
