#!/usr/bin/env python3
"""Author by Lab | zefry. Bind updates to GitHub's published asset names and bytes."""
import argparse
import base64
import hashlib
import json
import pathlib
import re
from datetime import datetime, timezone
from urllib.parse import quote


def manifest(release, version, installer, signature):
    if not re.fullmatch(r"[0-9]+\.[0-9]+\.[0-9]+", version):
        raise ValueError("Invalid stable version")
    tag = f"pos-windows-v{version}"
    if release.get("tag_name") != tag or release.get("draft") or release.get("prerelease"):
        raise ValueError("Published stable release required")
    assets = release["assets"]
    candidates = [a for a in assets if a["name"].endswith("-setup.exe")]
    if len(candidates) != 1:
        raise ValueError("Exactly one published installer required")
    asset = candidates[0]
    # GitHub can normalize spaces to dots during upload. Never construct the URL
    # from the local installer filename: use and constrain the published metadata.
    expected = f"https://github.com/labzefry/oneQay/releases/download/{tag}/{quote(asset['name'], safe='')}"
    if asset["browser_download_url"] != expected or not re.fullmatch(r"oneQay[. ]POS_" + re.escape(version) + r"_x64-setup\.exe", asset["name"]):
        raise ValueError("Unexpected published installer URL")
    digest = "sha256:" + hashlib.sha256(installer).hexdigest()
    if asset.get("state") != "uploaded" or asset["size"] != len(installer) or asset.get("digest") != digest:
        raise ValueError("Published installer bytes do not match")
    signatures = [a for a in assets if a["name"] == asset["name"] + ".sig"]
    raw_signature = signature.strip()
    base64.b64decode(raw_signature, validate=True)
    if len(signatures) != 1 or signatures[0].get("state") != "uploaded" or signatures[0]["size"] != len(signature) or signatures[0].get("digest") != "sha256:" + hashlib.sha256(signature).hexdigest():
        raise ValueError("Published signature bytes do not match")
    return {"version": version, "notes": f"oneQay POS {version}", "pub_date": datetime.now(timezone.utc).isoformat(),
            "platforms": {"windows-x86_64": {"signature": raw_signature.decode("ascii"), "url": asset["browser_download_url"]}}}


if __name__ == "__main__":
    p = argparse.ArgumentParser()
    p.add_argument("--release", type=pathlib.Path, required=True)
    p.add_argument("--version", required=True)
    p.add_argument("--installer", type=pathlib.Path, required=True)
    p.add_argument("--output", type=pathlib.Path, required=True)
    a = p.parse_args()
    result = manifest(json.loads(a.release.read_text(encoding="utf-8-sig")), a.version,
                      a.installer.read_bytes(), pathlib.Path(str(a.installer) + ".sig").read_bytes())
    a.output.write_text(json.dumps(result, indent=2) + "\n", encoding="utf-8")
