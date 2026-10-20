#!/usr/bin/env python3
"""Author by Lab | zefry. Package an exact certified release; never activate hosting."""
import argparse
import hashlib
import json
import pathlib
import re
import shutil
import tarfile
import zipfile


def digest(data):
    return hashlib.sha256(data).hexdigest()


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--certified-zip', type=pathlib.Path, required=True)
    parser.add_argument('--source-sha', required=True)
    parser.add_argument('--run-id', type=int, required=True)
    parser.add_argument('--output-directory', type=pathlib.Path, required=True)
    args = parser.parse_args()
    repo = pathlib.Path(__file__).resolve().parents[2]
    cpanel = repo / 'tools/production/cpanel'
    source = (cpanel / 'update-oneqaydev-onboarding-recovery.php').read_text()
    const = dict(re.findall(r"^const ([A-Z_]+) = '([^']*)';", source, re.M))
    assert re.fullmatch('[a-f0-9]{40}', args.source_sha)
    assert const['U_SOURCE'] == args.source_sha, 'Operator is not bound to this source'
    assert args.run_id > 0
    short = args.source_sha[:12]
    release = 'production-' + short
    name = 'oneqaydev-pos-update-' + short
    args.output_directory.mkdir(parents=True, exist_ok=True)
    folder = args.output_directory / name
    assert not folder.exists(), 'Refusing to overwrite an existing package'
    with zipfile.ZipFile(args.certified_zip) as archive:
        files = {}
        for item in archive.infolist():
            if item.is_dir():
                continue
            assert '/' not in item.filename and '\\' not in item.filename
            assert item.filename not in files and item.file_size < 300_000_000
            files[item.filename] = archive.read(item)
    tar_name = release + '.tar.gz'
    manifest_name = release + '.manifest.json'
    assert set(files) == {tar_name, manifest_name, tar_name + '.sha256'}
    manifest = json.loads(files[manifest_name])
    assert manifest['source']['commit_sha'] == args.source_sha
    assert manifest['release']['id'] == release
    assert manifest['artifact']['sha256'] == digest(files[tar_name])
    assert manifest['artifact']['size_bytes'] == len(files[tar_name])
    assert manifest['build']['provenance_reference'] == f'github-actions:{args.run_id}:1:source-tested'
    assert manifest['runtime']['source_ci_certified'] is True
    assert manifest['runtime']['business_runtime_activation_ready'] is True
    assert manifest['runtime']['host_business_acceptance_verified'] is False
    assert files[tar_name + '.sha256'].decode().split()[0] == digest(files[tar_name])
    folder.mkdir(mode=0o700)
    for filename, raw in files.items():
        (folder / filename).write_bytes(raw)
    for original, target in [
        ('update-oneqaydev-onboarding-recovery.php', 'update.php'),
        ('install-oneqaydev-onboarding-recovery.php', 'install-modules.php'),
        ('release-retention.php', 'release-retention.php'),
    ]:
        shutil.copyfile(cpanel / original, folder / target)
    payload = folder / 'payload'
    payload.mkdir(mode=0o700)
    lines, seen, total = [], set(), 0
    with tarfile.open(folder / tar_name, 'r:gz') as archive:
        for item in archive:
            path = pathlib.PurePosixPath(item.name)
            assert path.parts and path.parts[0] == release
            assert all(part not in ('..', '.') for part in path.parts)
            assert re.fullmatch(r'[A-Za-z0-9._+@/-]+', item.name)
            assert item.name not in seen and (item.isdir() or item.isfile())
            assert item.name != release + '/apps/web/.env'
            seen.add(item.name)
            destination = payload.joinpath(*path.parts)
            if item.isdir():
                destination.mkdir(parents=True, exist_ok=True, mode=0o750)
                continue
            total += item.size
            assert len(seen) < 20_000 and total < 500_000_000
            raw = archive.extractfile(item).read()
            assert len(raw) == item.size
            destination.parent.mkdir(parents=True, exist_ok=True, mode=0o750)
            destination.write_bytes(raw)
            destination.chmod(0o640)
            lines.append(digest(raw) + '  ' + item.name)
    metadata = json.loads((payload / release / 'RELEASE.json').read_text())
    assert metadata['source_commit'] == args.source_sha
    assert metadata['release_id'] == release and metadata['migration_count'] == 27
    assert metadata['source_ci_certified'] is True
    assert metadata['business_runtime_activation_ready'] is True
    assert 6252 <= len(lines) <= 7000
    inventory = ('\n'.join(sorted(lines)) + '\n').encode()
    (folder / 'release-files.sha256').write_bytes(inventory)
    request = {
        'schema_version': 1,
        'action': 'CERTIFIED_ONBOARDING_ADDITIVE_UPDATE_PRESERVE_BUSINESS_ON',
        'domain': 'oneqaydev.n07.my.id',
        'private_root': const['U_ROOT'], 'public_root': const['U_PUBLIC'],
        'previous_source_commit': const['L_SOURCE'],
        'previous_runtime_sha256': const['U_OLD_ENV_SHA'],
        'previous_business_entry_sha256': const['U_OLD_ENTRY_SHA'],
        'previous_routing_sha256': const['U_OLD_ROUTING_SHA'],
        'merchant_receipt_sha256': re.search(r"'merchant_receipt_sha256'=>'([a-f0-9]{64})'", source)[1],
        'operator_sha256': digest((folder / 'update.php').read_bytes()),
        'module_helper_sha256': digest((folder / 'install-modules.php').read_bytes()),
        'rollback_target_state': 'BUSINESS_ON_PREVIOUS_CERTIFIED_RELEASE',
        'authority_window_seconds': 3600,
        'retention_helper_sha256': digest((folder / 'release-retention.php').read_bytes()),
        'release_retention_policy': 'VERIFIED_PRIVATE_ZIP_KEEP_ACTIVE',
        'module_migrations_allowed': True, 'module_migration_paths': ['business/2026_10_20_000001_create_workforce_accounts_and_shift_recovery.php'], 'historical_migrations_allowed': False,
        'owner_business_role_allowed': False, 'merchant_reprovision_allowed': False,
        'staging_allowed': False, 'updater_allowed': False, 'external_payments_allowed': False,
        'runtime_config_delta': [], 'website_content_refresh': ['home','pricing','guides','terms','privacy'],
        'source_commit': args.source_sha, 'release_id': release,
        'archive_sha256': digest(files[tar_name]), 'manifest_sha256': digest(files[manifest_name]),
        'release_files_sha256': digest(inventory), 'release_file_count': len(lines),
        'source_certification_run_id': args.run_id,
    }
    raw_request = (json.dumps(request, indent=2, ensure_ascii=False) + '\n').encode()
    (folder / 'authority-request.json').write_bytes(raw_request)
    body = (
        'ONEQAYDEV PRODUCTION ONBOARDING RECOVERY UPDATE AUTHORIZATION\n'
        f'REQUEST_SHA256: {digest(raw_request)}\nTARGET: https://oneqaydev.n07.my.id\n'
        f'RELEASE: {release}\nSOURCE_COMMIT: {args.source_sha}\n'
        f'ARTIFACT_SHA256: {request["archive_sha256"]}\n'
        'BACKEND_CODE_UPDATE: GRANTED\nVERIFIED_SELF_REGISTRATION: ACTIVATE_AFTER_EMAIL_PROOF\nPUBLIC_COPY: HOME_PRICING_GUIDES_TERMS_PRIVACY_PUBLISH_PRESERVE_REVISIONS_IMAGES_DRAFTS\nPRESERVE_BUSINESS_ON: REQUIRED\nROLLBACK: GRANTED\n'
        'MODULE_MIGRATIONS: ONE_ADDITIVE_WORKFORCE_RECOVERY_MODULE_ONLY\nHISTORICAL_MIGRATIONS: NOT_ALLOWED\n'
        'OWNER_BUSINESS_ROLE: PRESERVE_ONLY\nMERCHANT_REPROVISION: NOT_ALLOWED\nSTAGING: NOT_ALLOWED\n'
        'UPDATER: NOT_ALLOWED\nEXTERNAL_PAYMENTS: NOT_ALLOWED\nWINDOW_SECONDS: 3600\nAuthor by Lab | zefry'
    )
    (folder / 'authorization-body.txt').write_text(body)
    base = const['U_ROOT'] + '/' + name
    (folder / 'PETUNJUK-DEPLOY.txt').write_text(
        f'oneQay enterprise POS — {release}\n\n'
        f'1. Ekstrak ZIP di {const["U_ROOT"]}, sehingga terdapat {base}/update.php.\n'
        '2. Sesudah upload selesai, minta ID otorisasi baru. Jangan menjalankan placeholder OWNER_COMMENT_ID.\n'
        '3. Pasang cron satu kali dengan ID otorisasi tersebut:\n'
        f'/usr/local/bin/php {base}/update.php update OWNER_COMMENT_ID > {base}/cron-output.log 2>&1\n\n'
        f'4. Buka {base}/update-result.json. Jika state BUSINESS_WORKSPACE_UPDATED_ACCEPTANCE_PENDING dan business_traffic ON_PRESERVED, hapus cron update satu kali.\n'
        '5. Ganti cron worker lama dengan cron berikut, setiap menit:\n'
        f'/usr/local/bin/php {base}/control-worker.php > {base}/worker-result.json 2>&1\n\n'
        'Periksa worker-result.json. Pengarsipan ZIP privat untuk rilis lama dan payload berjalan setelah 120 detik. Jika gagal, folder sumber dipertahankan.\n'
        'Jika hasil STOPPED, simpan JSON dan log. Jangan menghapus release aktif, runtime, sesi, atau jurnal migrasi.\n'
        'Backend harus berhasil diperbarui sebelum login kasir melalui installer Windows 0.2.\n'
    )
    shutil.copyfile(repo / 'docs/implementation/POS_ENTERPRISE_OPERATOR_HANDOFF.md', folder / 'PANDUAN-POS.md')
    output = args.output_directory / (name + '.zip')
    assert not output.exists()
    with zipfile.ZipFile(output, 'x', compression=zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
        for item in sorted(folder.rglob('*')):
            archive.write(item, item.relative_to(args.output_directory))
    checksum = digest(output.read_bytes())
    (args.output_directory / (output.name + '.sha256')).write_text(checksum + '  ' + output.name + '\n')
    print(json.dumps({'package': str(output), 'sha256': checksum, 'source_files': len(lines),
                      'request_sha256': digest(raw_request), 'release': release}))


if __name__ == '__main__':
    main()
