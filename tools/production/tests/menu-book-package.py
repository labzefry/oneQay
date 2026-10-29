#!/usr/bin/env python3
"""Synthetic cPanel package test; never accesses hosting or live credentials."""
import hashlib, io, json, pathlib, shutil, subprocess, tarfile, tempfile, zipfile
root=pathlib.Path(__file__).resolve().parents[3]
sha=lambda b:hashlib.sha256(b).hexdigest()
source='a'*40
release='production-'+source[:12]
with tempfile.TemporaryDirectory(prefix='oneqay-menu-package-') as temp:
    tmp=pathlib.Path(temp); raw=io.BytesIO()
    with tarfile.open(fileobj=raw,mode='w:gz') as archive:
        metadata=json.dumps({'source_commit':source,'migration_count':27}).encode()
        for name,content in [(release+'/RELEASE.json',metadata),*((release+'/apps/web/filler/file%04d'%i,b'x') for i in range(6299))]:
            item=tarfile.TarInfo(name); item.size=len(content); archive.addfile(item,io.BytesIO(content))
    artifact=raw.getvalue(); digest=sha(artifact)
    manifest={'source':{'commit_sha':source},'artifact':{'sha256':digest,'size_bytes':len(artifact)},'runtime':{'source_ci_certified':True,'business_runtime_activation_ready':True},'build':{'provenance_reference':'github-actions:123:1:source-tested'}}
    cert=tmp/'certified.zip'
    with zipfile.ZipFile(cert,'w') as z:
        z.writestr(release+'.tar.gz',artifact);z.writestr(release+'.manifest.json',json.dumps(manifest));z.writestr(release+'.tar.gz.sha256',digest+'  archive\n')
    bind={'source':'95d9286f4698b168f7c419b67ac3c6583f844910','file_count':6426,**{k:'b'*64 for k in ['artifact','env','entry','routing','inventory','index']}}
    (tmp/'binding.json').write_text(json.dumps(bind))
    subprocess.run(['python3',str(root/'tools/production/package-oneqaydev-menu-book.py'),'--predecessor-binding',str(tmp/'binding.json'),'--certified-zip',str(cert),'--source',source,'--run','123','--output',str(tmp/'out')],check=True,capture_output=True,text=True)
    folder=tmp/'out'/('oneqaydev-menu-release-'+source[:12]); request=json.loads((folder/'authority-request.json').read_text())
    assert request['schema_change'] is True and request['file_count']==6300
    for name in ['update.php','database-backup-helper.php','menu-book-migrations.php','menu-vite-assets.php','release-files.sha256','release.manifest.json','release.tar.gz']:
        assert request['sha256'][name]==sha((folder/name).read_bytes())
    op=(folder/'update.php').read_text();assert '__SOURCE__' not in op and '__PREVIOUS_' not in op and 'ADDITIVE_2_MODULE_MIGRATIONS' in op
    assert (tmp/'out'/('oneqaydev-menu-release-'+source[:12]+'.zip')).is_file()
    print('MENU_PACKAGE_SYNTHETIC_PASS assertions=11')
