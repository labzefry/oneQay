#!/usr/bin/env python3
"""Exercise actual Sprint253 push proof and preserve fail-closed diagnostics."""
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


if __name__ == '__main__':
    unittest.main()
