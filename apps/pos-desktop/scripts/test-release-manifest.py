"""Author by Lab | zefry. Reproduce GitHub's space-to-dot asset normalization."""
import base64
import copy
import hashlib
import importlib.util
import pathlib
import unittest

spec = importlib.util.spec_from_file_location("release_manifest", pathlib.Path(__file__).with_name("release-manifest.py"))
module = importlib.util.module_from_spec(spec)
spec.loader.exec_module(module)


class PublishedAssets(unittest.TestCase):
    def setUp(self):
        self.exe, self.sig = b"synthetic signed installer", base64.b64encode(b"synthetic signature")
        self.name = "oneQay.POS_0.2.7_x64-setup.exe"
        self.release = {"tag_name": "pos-windows-v0.2.7", "draft": False, "prerelease": False, "assets": [
            {"name": self.name, "state": "uploaded", "size": len(self.exe), "digest": "sha256:" + hashlib.sha256(self.exe).hexdigest(),
             "browser_download_url": "https://github.com/labzefry/oneQay/releases/download/pos-windows-v0.2.7/" + self.name},
            {"name": self.name + ".sig", "state": "uploaded", "size": len(self.sig), "digest": "sha256:" + hashlib.sha256(self.sig).hexdigest()}]}

    def test_uses_actual_published_dots_filename(self):
        actual = module.manifest(self.release, "0.2.7", self.exe, self.sig)
        self.assertTrue(actual["platforms"]["windows-x86_64"]["url"].endswith("/" + self.name))
        self.assertEqual(actual["platforms"]["windows-x86_64"]["signature"], self.sig.decode())

    def test_rejects_wrong_url_digest_version_or_missing_signature(self):
        for mutation in [lambda r: r["assets"][0].update(browser_download_url="https://evil.test/setup.exe"),
                         lambda r: r["assets"][0].update(digest="sha256:" + "0" * 64),
                         lambda r: r.update(tag_name="pos-windows-v0.2.6"),
                         lambda r: r["assets"].pop(), lambda r: r.update(draft=True)]:
            r = copy.deepcopy(self.release)
            mutation(r)
            with self.assertRaises(ValueError):
                module.manifest(r, "0.2.7", self.exe, self.sig)

    def test_rejects_downloaded_tamper(self):
        for exe, sig in [(self.exe + b"changed", self.sig), (self.exe, self.sig + b"changed")]:
            with self.assertRaises(ValueError):
                module.manifest(self.release, "0.2.7", exe, sig)


unittest.main()
