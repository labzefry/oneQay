#!/usr/bin/env python3
"""Exercise actual Sprint253 push proof and preserve fail-closed diagnostics."""
# Author by Lab | zefry
import os
from pathlib import Path
import stat
import subprocess
import tempfile
import unittest

ROOT = Path(__file__).resolve().parents[2]
SCRIPT = ROOT / 'tools/ci/sprint253-successor-compatibility.sh'
BASE = '5ab9b673437eca9501657f395f329610266ee7bb'
MERGE = 'f628b946c2a369317f041f5da63b3f534b0c852b'
WORKFLOW = ROOT / '.github/workflows/sprint38-first-party-session-absolute-lifetime-regression.yml'


def environment():
    values = dict(os.environ)
    values.update({f'SPRINT253_CONTEXT_{i}': '' for i in range(10)})
    values.update(GITHUB_EVENT_NAME='push', GITHUB_REPOSITORY='labzefry/oneQay',
                  GITHUB_REF='refs/heads/main', GITHUB_SHA=MERGE,
                  PR_HEAD_REF='', PR_HEAD_SHA='', PR_BASE_SHA='',
                  PUSH_BEFORE_SHA=BASE, PUSH_AFTER_SHA=MERGE,
                  SPRINT253_CONTEXT_9=MERGE)
    return values


def run(values, command=None):
    return subprocess.run(command or ['bash', str(SCRIPT)], cwd=ROOT, env=values,
                          text=True, capture_output=True, timeout=45)


class CertifiedMergeTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        # Replay the immutable merge in a disposable worktree, so legitimate
        # future workflow edits cannot invalidate this historical regression.
        global ROOT, SCRIPT, WORKFLOW
        cls.source_root = ROOT
        cls.scratch = tempfile.TemporaryDirectory(prefix='oneqay-s253-fixture-')
        fixture = Path(cls.scratch.name) / 'repo'
        subprocess.run(['git', 'worktree', 'add', '--quiet', '--detach', str(fixture), MERGE],
                       cwd=cls.source_root, check=True, capture_output=True)
        original_script = SCRIPT.read_bytes()
        ROOT = fixture
        SCRIPT = ROOT / 'tools/ci/sprint253-successor-compatibility.sh'
        SCRIPT.write_bytes(original_script)
        WORKFLOW = ROOT / '.github/workflows/sprint38-first-party-session-absolute-lifetime-regression.yml'

    @classmethod
    def tearDownClass(cls):
        try:
            subprocess.run(['git', 'worktree', 'remove', '--force', str(ROOT)],
                           cwd=cls.source_root, check=True, capture_output=True)
        finally:
            cls.scratch.cleanup()

    def test_exact_merged_source_passes_and_scans_workflows(self):
        result = run(environment())
        self.assertEqual(result.returncode, 0, result.stderr)
        self.assertRegex(result.stdout, r'SPRINT253_CERTIFIED_WINDOWS_PUSH_OK workflows=[1-9][0-9]*')
        self.assertIn('SPRINT253_SUCCESSOR_COMPATIBILITY_OK', result.stdout)

    def test_wrong_event_repository_ref_and_sha_fail(self):
        for key, value in [('GITHUB_EVENT_NAME', 'pull_request'),
                           ('GITHUB_REPOSITORY', 'foreign/oneQay'),
                           ('GITHUB_REF', 'refs/heads/unrelated'),
                           ('GITHUB_SHA', '0' * 40),
                           ('SPRINT253_CONTEXT_9', '0' * 40),
                           ('PR_HEAD_SHA', MERGE),
                           ('PUSH_BEFORE_SHA', '0' * 40),
                           ('PUSH_AFTER_SHA', '0' * 40)]:
            with self.subTest(key=key):
                values = environment(); values[key] = value
                self.assertNotEqual(run(values).returncode, 0)

    def test_modified_missing_symlink_and_executable_workflow_fail(self):
        original = WORKFLOW.read_bytes()
        mode = stat.S_IMODE(WORKFLOW.stat().st_mode)
        with tempfile.TemporaryDirectory(prefix='oneqay-s253-') as scratch:
            backup = Path(scratch) / 'original.yml'
            for mutation in ['modified', 'missing', 'symlink', 'executable']:
                with self.subTest(mutation=mutation):
                    try:
                        if mutation == 'modified': WORKFLOW.write_bytes(original + b'\n# changed\n')
                        elif mutation == 'missing': WORKFLOW.rename(backup)
                        elif mutation == 'symlink':
                            WORKFLOW.rename(backup); WORKFLOW.symlink_to(backup)
                        else: WORKFLOW.chmod(mode | stat.S_IXUSR)
                        self.assertNotEqual(run(environment()).returncode, 0)
                    finally:
                        if WORKFLOW.is_symlink(): WORKFLOW.unlink()
                        if backup.exists(): backup.replace(WORKFLOW)
                        WORKFLOW.write_bytes(original); WORKFLOW.chmod(mode)

    def test_wrong_immutable_tree_fails(self):
        real_git = subprocess.check_output(['which', 'git'], text=True).strip()
        with tempfile.TemporaryDirectory(prefix='oneqay-s253-git-') as scratch:
            wrapper = Path(scratch) / 'git'
            wrapper.write_text('#!/usr/bin/env python3\nimport os,sys\n'
                + "if sys.argv[1:]==['rev-parse', '" + MERGE + "^{tree}']:\n"
                + " print('0'*40)\nelse:\n os.execv(" + repr(real_git)
                + ', [' + repr(real_git) + '] + sys.argv[1:])\n')
            wrapper.chmod(0o700)
            values = environment(); values['PATH'] = scratch + os.pathsep + values['PATH']
            result = run(values)
            self.assertNotEqual(result.returncode, 0)
            self.assertIn('CERTIFIED_WINDOWS_TREE_INVALID', result.stderr)

    def test_stale_operational_checks_still_run_after_source_proof(self):
        # Inject parser input after the immutable file proof, without weakening it.
        body = SCRIPT.read_text().split("python3 - <<'PY'\n", 1)[1].rsplit('\nPY', 1)[0]
        for line, diagnostic in [
            ("test \"$(jq -r '.feature_activation.state' state.json)\" = INACTIVE", 'feature predecessor-only guard'),
            ("test \"$(jq -r '.permission_provisioning.state' state.json)\" = NONE", 'permission predecessor-only guard'),
        ]:
            with self.subTest(diagnostic=diagnostic):
                code = ('from pathlib import Path\noriginal=Path.read_text\n'
                        'def injected(self,*a,**kw):\n value=original(self,*a,**kw)\n'
                        ' return value+"\\n"+' + repr(line) + ' if self.name=='
                        + repr(WORKFLOW.name) + ' else value\n'
                        'Path.read_text=injected\nexec(compile(' + repr(body) + ', "sprint253", "exec"))')
                result = run(environment(), ['python3', '-c', code])
                self.assertNotEqual(result.returncode, 0)
                self.assertIn('CERTIFIED_WINDOWS_PUSH_OK', result.stdout)
                self.assertIn(diagnostic, result.stderr)

    def test_unrelated_zero_workflow_range_uses_normal_scanner(self):
        values = environment()
        values['PUSH_BEFORE_SHA'] = MERGE
        values['PUSH_AFTER_SHA'] = subprocess.check_output(['git', 'rev-parse', 'HEAD'], cwd=ROOT, text=True).strip()
        values['GITHUB_SHA'] = values['PUSH_AFTER_SHA']
        result = run(values)
        self.assertEqual(result.returncode, 0, result.stderr)
        self.assertNotIn('CERTIFIED_WINDOWS_PUSH_OK', result.stdout)
        self.assertIn('SPRINT253_SUCCESSOR_COMPATIBILITY_OK workflows=0', result.stdout)


class PostmergeRepairTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        import importlib.util
        source = Path(__file__).resolve().parents[2]
        spec = importlib.util.spec_from_file_location('repair', source / 'tools/ci/verify-postmerge-windows-repair.py')
        cls.proof = importlib.util.module_from_spec(spec); spec.loader.exec_module(cls.proof)
        cls.source = source
        cls.scratch = tempfile.TemporaryDirectory(prefix='oneqay-ci-repair-')
        cls.repo = Path(cls.scratch.name) / 'repo'
        subprocess.run(['git', 'worktree', 'add', '--quiet', '--detach', str(cls.repo), cls.proof.BASE],
                       cwd=source, check=True, capture_output=True)
        # Construct a real, runtime-frozen CI-only candidate from the current
        # owned inputs. No future unrelated application edits enter the fixture.
        for path in cls.proof.ALLOWED - {cls.proof.MANIFEST}:
            target = cls.repo / path; target.parent.mkdir(parents=True, exist_ok=True)
            content = (source / path).read_bytes()
            if target.exists() and target.read_bytes() == content: content += b'\n'
            target.write_bytes(content); target.chmod(0o644)
        (cls.repo / cls.proof.MANIFEST).write_text('{}\n')
        cls.git('add', '--', *sorted(cls.proof.ALLOWED))
        fields = cls.git('diff', '--cached', '--no-renames', '--name-status', '-z', cls.proof.BASE).split('\0')
        manifest = {p: [status, cls.git('rev-parse', ':' + p).strip()]
                    for status, p in zip(fields[0::2], fields[1::2])
                    if p not in (cls.proof.SELF, cls.proof.MANIFEST)}
        (cls.repo / cls.proof.MANIFEST).write_text(__import__('json').dumps(manifest, sort_keys=True) + '\n')
        cls.git('add', '--', cls.proof.MANIFEST)
        cls.head = cls.git('commit-tree', cls.git('write-tree').strip(), '-p', cls.proof.BASE,
                           '-m', 'Synthetic CI source proof').strip()
        cls.git('checkout', '--quiet', '--detach', cls.head)

    @classmethod
    def git(cls, *args):
        return subprocess.check_output(['git', '-c', 'user.name=oneQay Regression',
                                        '-c', 'user.email=fixture@oneqay.invalid', *args],
                                       cwd=cls.repo, text=True)

    @classmethod
    def tearDownClass(cls):
        try:
            subprocess.run(['git', 'worktree', 'remove', '--force', str(cls.repo)],
                           cwd=cls.source, check=True, capture_output=True)
        finally: cls.scratch.cleanup()

    def values(self, push=False):
        import json
        values = environment()
        values.update(GITHUB_EVENT_NAME='push' if push else 'pull_request',
                      GITHUB_HEAD_REF=self.proof.BRANCH, GITHUB_REF='refs/heads/main',
                      GITHUB_SHA=self.head, SPRINT253_CONTEXT_9=self.head,
                      PR_HEAD_REF=self.proof.BRANCH, PR_BASE_SHA=self.proof.BASE,
                      PR_HEAD_SHA='' if push else self.head,
                      PUSH_BEFORE_SHA=self.proof.BASE, PUSH_AFTER_SHA=self.head)
        if push:
            event = {'before': self.proof.BASE, 'after': self.head, 'ref': 'refs/heads/main',
                     'repository': {'full_name': self.proof.REPO}, 'forced': False, 'deleted': False}
        else:
            event = {'number': 997, 'pull_request': {
                'base': {'sha': self.proof.BASE, 'ref': 'main', 'repo': {'full_name': self.proof.REPO}},
                'head': {'sha': self.head, 'ref': self.proof.BRANCH, 'repo': {'full_name': self.proof.REPO}}}}
        path = Path(self.scratch.name) / 'event.json'; path.write_text(json.dumps(event))
        values['GITHUB_EVENT_PATH'] = str(path)
        return values, event, path

    def invoke(self, values):
        return subprocess.run(['python3', self.proof.SELF], cwd=self.repo, env=values,
                              text=True, capture_output=True, timeout=30)

    def test_actual_pr_and_squash_push_proofs_pass(self):
        for push in (False, True):
            with self.subTest(push=push):
                values, _, _ = self.values(push)
                result = self.invoke(values)
                self.assertEqual(result.returncode, 0, result.stderr)
                self.assertIn('POSTMERGE_WINDOWS_REPAIR_SOURCE=PASS', result.stdout)

    def test_wrong_pr_fork_base_and_push_contexts_fail(self):
        import json
        for scenario in ('pr_number', 'fork', 'base', 'branch', 'push_before', 'push_sha', 'forced'):
            with self.subTest(scenario=scenario):
                values, event, path = self.values(scenario.startswith('push') or scenario == 'forced')
                if scenario == 'pr_number': event['number'] = 998
                elif scenario == 'fork': event['pull_request']['head']['repo']['full_name'] = 'foreign/oneQay'
                elif scenario == 'base': event['pull_request']['base']['sha'] = '0' * 40
                elif scenario == 'branch': values['GITHUB_HEAD_REF'] = 'unrelated'
                elif scenario == 'push_before': event['before'] = '0' * 40
                elif scenario == 'push_sha': values['GITHUB_SHA'] = '0' * 40
                else: event['forced'] = True
                path.write_text(json.dumps(event))
                self.assertNotEqual(self.invoke(values).returncode, 0)

    def test_owned_file_and_uncommitted_runtime_drift_fail(self):
        for relative, scenario in [('TASKS.md', 'owned_blob'), ('TASKS.md', 'owned_mode'),
                                  ('apps/web/routes/console.php', 'runtime'),
                                  ('apps/web/routes/console.php', 'hidden_runtime')]:
            path = self.repo / relative; original = path.read_bytes(); mode = stat.S_IMODE(path.stat().st_mode)
            with self.subTest(scenario=scenario):
                try:
                    if scenario == 'owned_mode': path.chmod(mode | stat.S_IXUSR)
                    else: path.write_bytes(original + b'\n')
                    if scenario == 'hidden_runtime': self.git('update-index', '--assume-unchanged', relative)
                    values, _, _ = self.values()
                    self.assertNotEqual(self.invoke(values).returncode, 0)
                finally:
                    if scenario == 'hidden_runtime': self.git('update-index', '--no-assume-unchanged', relative)
                    path.write_bytes(original); path.chmod(mode)

    def test_committed_runtime_only_push_is_rejected_before_zero_workflow_scan(self):
        # A separate real app-only commit must not pass merely because no Sprint
        # workflow changed. Execute the current scanner against that event.
        import json
        with tempfile.TemporaryDirectory(prefix='oneqay-ci-runtime-negative-') as scratch:
            repo = Path(scratch) / 'repo'
            subprocess.run(['git', 'worktree', 'add', '--quiet', '--detach', str(repo), self.proof.BASE],
                           cwd=self.source, check=True, capture_output=True)
            try:
                runtime = repo / 'apps/web/routes/console.php'; runtime.write_bytes(runtime.read_bytes() + b'\n')
                def git(*args):
                    return subprocess.check_output(['git', '-c', 'user.name=oneQay Regression',
                        '-c', 'user.email=fixture@oneqay.invalid', *args], cwd=repo, text=True).strip()
                git('add', 'apps/web/routes/console.php')
                head = git('commit-tree', git('write-tree'), '-p', self.proof.BASE, '-m', 'Synthetic runtime drift')
                git('checkout', '--quiet', '--detach', head)
                helper = repo / self.proof.SELF; helper.write_bytes((self.source / self.proof.SELF).read_bytes())
                values, event, path = self.values(True)
                event['after'] = head; path.write_text(json.dumps(event))
                values.update(GITHUB_SHA=head, SPRINT253_CONTEXT_9=head, PUSH_AFTER_SHA=head)
                result = subprocess.run(['bash', str(self.source / 'tools/ci/sprint253-successor-compatibility.sh')],
                                        cwd=repo, env=values, text=True, capture_output=True, timeout=30)
                self.assertNotEqual(result.returncode, 0)
                self.assertIn('POSTMERGE_WINDOWS_REPAIR_PATHS_INVALID', result.stderr)
            finally:
                subprocess.run(['git', 'worktree', 'remove', '--force', str(repo)],
                               cwd=self.source, check=True, capture_output=True)


if __name__ == '__main__':
    unittest.main()
