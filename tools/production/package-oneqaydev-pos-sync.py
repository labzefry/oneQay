#!/usr/bin/env python3
"""Author by Lab | zefry. Package a certified additive successor, never activate hosting."""
import argparse, hashlib, json, pathlib, tarfile, zipfile, shutil

def sha(raw):return hashlib.sha256(raw).hexdigest()
def main():
 p=argparse.ArgumentParser();p.add_argument('--certified-zip',type=pathlib.Path,required=True);p.add_argument('--source',required=True);p.add_argument('--run',type=int,required=True);p.add_argument('--output',type=pathlib.Path,required=True);a=p.parse_args()
 assert len(a.source)==40 and all(c in 'abcdef0123456789' for c in a.source) and a.run>0
 release='production-'+a.source[:12];name='oneqaydev-pos-sync-'+a.source[:12];folder=a.output/name;assert not folder.exists()
 repo=pathlib.Path(__file__).resolve().parents[2];operator=(repo/'tools/production/cpanel/update-oneqaydev-pos-sync.php').read_text();assert all(x in operator for x in ['__SOURCE__','__ARTIFACT__','__RUN__'])
 with zipfile.ZipFile(a.certified_zip) as z:
  entries={}
  for info in z.infolist():
   if info.is_dir():continue
   assert '/' not in info.filename and '\\' not in info.filename and info.filename not in entries and info.file_size<300_000_000
   entries[info.filename]=z.read(info)
 tarname=release+'.tar.gz';manifest=json.loads(entries[release+'.manifest.json']);artifact=sha(entries[tarname]);assert set(entries)=={tarname,release+'.manifest.json',tarname+'.sha256'}
 assert manifest['source']['commit_sha']==a.source and manifest['artifact']['sha256']==artifact and manifest['artifact']['size_bytes']==len(entries[tarname])
 assert manifest['runtime']['source_ci_certified'] is True and manifest['runtime']['business_runtime_activation_ready'] is True
 assert manifest['build']['provenance_reference']==f'github-actions:{a.run}:1:source-tested'
 assert entries[tarname+'.sha256'].decode().split()[0]==artifact
 folder.mkdir(parents=True,mode=0o700);(folder/'release.tar.gz').write_bytes(entries[tarname]);(folder/'release.manifest.json').write_bytes(entries[release+'.manifest.json'])
 payload=folder/'payload';payload.mkdir(mode=0o750);inventory=[];seen=set();total=0
 with tarfile.open(folder/'release.tar.gz','r:gz') as t:
  for item in t:
   path=pathlib.PurePosixPath(item.name);assert path.parts and path.parts[0]==release and all(x not in ('.','..') for x in path.parts) and not path.is_absolute()
   assert all(c.isalnum() or c in '_.@+/-' for c in item.name) and item.name not in seen and (item.isdir() or item.isfile())
   assert not item.name.endswith('/apps/web/.env');seen.add(item.name);target=payload.joinpath(*path.parts)
   if item.isdir():target.mkdir(parents=True,exist_ok=True,mode=0o750);continue
   total+=item.size;assert total<500_000_000 and len(seen)<20000
   raw=t.extractfile(item).read();assert len(raw)==item.size;target.parent.mkdir(parents=True,exist_ok=True,mode=0o750);target.write_bytes(raw);target.chmod(0o640);inventory.append(sha(raw)+'  '+item.name)
 metadata=json.loads((payload/release/'RELEASE.json').read_text());assert metadata['source_commit']==a.source and metadata['migration_count']==27 and 6300<=len(inventory)<=7000
 (folder/'release-files.sha256').write_text('\n'.join(sorted(inventory))+'\n')
 operator=operator.replace('__SOURCE__',a.source).replace('__ARTIFACT__',artifact).replace('__RUN__',str(a.run));(folder/'update.php').write_text(operator)
 shutil.copyfile(repo/'tools/production/cpanel/install-oneqaydev-mail-stock.php',folder/'database-backup-helper.php')
 request={'source':a.source,'artifact':artifact,'run':a.run,'previous_source':'7e4197b443225e2022a26af9fd53a966d06c304f','domain':'oneqaydev.n07.my.id','migration':'pos/2026_10_22_000001_create_desktop_sync_diagnostics.php','business_reset_allowed':False,'credentials_changed':False,'window_seconds':3600,'file_count':len(inventory),'sha256':{n:sha((folder/n).read_bytes()) for n in ['update.php','database-backup-helper.php','release-files.sha256','release.manifest.json','release.tar.gz']}}
 (folder/'authority-request.json').write_text(json.dumps(request,indent=2)+'\n');request_sha=sha((folder/'authority-request.json').read_bytes())
 authorization=f'ONEQAYDEV POS SYNC DEPLOY AUTHORIZATION\nREQUEST_SHA256: {request_sha}\nSOURCE_COMMIT: {a.source}\nARTIFACT_SHA256: {artifact}\nTARGET: https://oneqaydev.n07.my.id\nADDITIVE_MIGRATION: pos/2026_10_22_000001_create_desktop_sync_diagnostics.php\nPRESERVE_BUSINESS_ON: REQUIRED\nBUSINESS_DATA_RESET: NOT_AUTHORIZED\nCREDENTIAL_CHANGES: NOT_AUTHORIZED\nWINDOW_SECONDS: 3600\nAuthor by Lab | zefry'
 (folder/'authorization-body.txt').write_text(authorization)
 command='/usr/local/bin/php /home/pekd7254/oneqay-production-test/'+name+'/update.php'
 (folder/'PETUNJUK.txt').write_text('Private cPanel deployment. Extract only inside /home/pekd7254/oneqay-production-test.\n1. '+command+' preflight > /home/pekd7254/oneqay-production-test/'+name+'/preflight.log 2>&1\n2. Verify preflight-result.json READY_NO_ACTIVATION. Publish exact authorization-body.txt as a new owner comment on PR981 only after upload. Never reuse an expired grant.\n3. '+command+' update COMMENT_ID > /home/pekd7254/oneqay-production-test/'+name+'/deployment.log 2>&1\n4. After verified update-result.json, remove temporary deploy cron and replace only the existing oneQay worker cron with: '+command+' work > /home/pekd7254/oneqay-production-test/'+name+'/worker.log 2>&1\nNo database reset occurs during deployment. Preserve all other crons, previous compatible release, SQL backup and shared files.\n')
 for f in folder.iterdir():
  if f.is_file():f.chmod(0o600)
 dest=a.output/(name+'.zip')
 with zipfile.ZipFile(dest,'x',compression=zipfile.ZIP_DEFLATED,compresslevel=6) as z:
  for f in sorted(folder.rglob('*')):z.write(f,pathlib.Path(name)/f.relative_to(folder))
 print(json.dumps({'package':str(dest),'sha256':sha(dest.read_bytes()),'bytes':dest.stat().st_size,'files':len(inventory),'source':a.source,'artifact':artifact,'request_sha256':request_sha}))
if __name__=='__main__':main()
