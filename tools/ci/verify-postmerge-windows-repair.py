#!/usr/bin/env python3
"""Bound PR997 and its squash to a reviewed CI-only change; never grants deployment."""
# Author by Lab | zefry
import json
import os
from pathlib import Path
import re
import subprocess
import sys

BASE = 'f628b946c2a369317f041f5da63b3f534b0c852b'
TREE = 'd91be372d5d50ef643f5ff3f17f4a1ef8d75fa4d'
REPO = 'labzefry/oneQay'
BRANCH = 'fix/postmerge-windows-release-20261106'
SELF = 'tools/ci/verify-postmerge-windows-repair.py'
MANIFEST = 'tools/ci/postmerge-windows-repair-files.json'
ALLOWED = {
    SELF, MANIFEST, 'CHANGELOG.md', 'PROJECT_MANIFEST.md', 'TASKS.md', 'TESTING.md',
    'docs/operations/WINDOWS_POS_PERSISTENT_SESSION_2026-11-05.md',
    'tools/ci/sprint253-successor-compatibility.sh',
    'tools/ci/test-sprint253-certified-merge.py',
    '.github/workflows/sprint253-final-shift-close-post-activation-historical-regression-successor-compatibility.yml',
    '.github/workflows/sprint35-privileged-totp-recovery-regression.yml',
    '.github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml',
    '.github/workflows/sprint37-first-party-all-session-termination-regression.yml',
    '.github/workflows/sprint268-dependency-security-remediation-regression.yml',
}


def git(*args):
    return subprocess.check_output(['git', *args])


def require(value, reason):
    if not value:
        raise ValueError('POSTMERGE_WINDOWS_REPAIR_' + reason)


def verify():
    event = json.loads(Path(os.environ['GITHUB_EVENT_PATH']).read_text())
    require(os.environ.get('GITHUB_REPOSITORY') == REPO, 'REPOSITORY_INVALID')
    kind = os.environ.get('GITHUB_EVENT_NAME')
    if kind == 'pull_request':
        pr = event['pull_request']
        require(event['number'] == 997 and pr['base']['repo']['full_name'] == REPO
                and pr['base']['ref'] == 'main' and pr['base']['sha'] == BASE
                and pr['head']['repo']['full_name'] == REPO
                and pr['head']['ref'] == BRANCH and os.environ.get('GITHUB_HEAD_REF') == BRANCH,
                'PR_CONTEXT_INVALID')
        head = pr['head']['sha']
    elif kind == 'push':
        head = event['after']
        require(event['before'] == BASE and event['ref'] == 'refs/heads/main'
                and event['repository']['full_name'] == REPO
                and os.environ.get('GITHUB_REF') == 'refs/heads/main'
                and os.environ.get('GITHUB_SHA') == head
                and not event.get('deleted') and not event.get('forced'), 'PUSH_CONTEXT_INVALID')
    else:
        raise ValueError('POSTMERGE_WINDOWS_REPAIR_EVENT_INVALID')
    require(isinstance(head, str) and re.fullmatch(r'[a-f0-9]{40}', head), 'HEAD_INVALID')
    require(git('rev-parse', 'HEAD').decode().strip() == head, 'CHECKOUT_HEAD_INVALID')
    if kind == 'push' and os.environ.get('SPRINT253_CONTEXT_9'):
        require(os.environ['SPRINT253_CONTEXT_9'] == head, 'PUSH_SOURCE_CONTEXT_INVALID')
    require(git('rev-parse', BASE + '^{tree}').decode().strip() == TREE, 'BASE_TREE_INVALID')
    require(subprocess.run(['git', 'merge-base', '--is-ancestor', BASE, head], check=False).returncode == 0,
            'BASE_ANCESTRY_INVALID')
    entries = git('diff', '--no-renames', '--name-status', '-z', BASE, head).decode().split('\0')
    changes = dict((path, status) for status, path in zip(entries[0::2], entries[1::2]))
    require(set(changes) == ALLOWED and set(changes.values()) <= {'A', 'M'}, 'PATHS_INVALID')
    added = {SELF, MANIFEST, 'tools/ci/test-sprint253-certified-merge.py'}
    require(all(status == ('A' if path in added else 'M') for path, status in changes.items()),
            'STATUS_INVALID')
    recorded = json.loads(git('show', head + ':' + MANIFEST))
    require(set(recorded) == ALLOWED - {SELF, MANIFEST}, 'MANIFEST_PATHS_INVALID')
    for path, status in changes.items():
        file = Path(path)
        require(file.is_file() and not file.is_symlink() and not file.stat().st_mode & 0o111,
                'WORKTREE_FILE_INVALID: ' + path)
        meta, name = git('ls-tree', head, '--', path).decode().strip().split('\t')
        mode, object_kind, blob = meta.split()
        require(name == path and mode == '100644' and object_kind == 'blob'
                and git('hash-object', '--', path).decode().strip() == blob, 'BLOB_INVALID: ' + path)
        if path not in (SELF, MANIFEST):
            require(recorded[path] == [status, blob], 'MANIFEST_BLOB_INVALID: ' + path)
    # The whitelist already freezes every other committed path; enforce runtime worktree too.
    frozen = ('apps', 'packages', 'src', 'ops', 'tests', 'composer.json', 'composer.lock')
    require(not git('diff', '--name-only', BASE, head, '--', *frozen), 'RUNTIME_CHANGED')
    flags = git('ls-files', '-v', '--', *frozen).decode().splitlines()
    require(all(line.startswith('H ') for line in flags), 'RUNTIME_INDEX_INVALID')
    require(not git('diff', '--name-only', head, '--', *frozen), 'RUNTIME_WORKTREE_CHANGED')
    print('POSTMERGE_WINDOWS_REPAIR_SOURCE=PASS')


if __name__ == '__main__':
    try:
        verify()
    except (ValueError, KeyError, OSError, subprocess.CalledProcessError) as error:
        print(error, file=sys.stderr)
        sys.exit(1)
