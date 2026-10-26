#!/usr/bin/env python3
"""Author by Lab | zefry. Package a certified additive cPanel successor; never activate hosting."""
import argparse,hashlib,json,pathlib,tarfile,zipfile,shutil

def sha(raw):return hashlib.sha256(raw).hexdigest()
def main():
 p=argparse.ArgumentParser();p.add_argument('--certified-zip',type=pathlib.Path,required=True);p.add_argument('--source',required=True);p.add_argument('--run',type=int,required=True);p.add_argument('--output',type=pathlib.Path,required=True);a=p.parse_args()
 assert len(a.source)==40 and all(c in 'abcdef0123456789' for c in a.source) and a.run>0
 repo=pathlib.Path(__file__).resolve().parents[2];release='production-'+a.source[:12];name='oneqaydev-business-experience-'+a.source[:12];folder=a.output/name;assert not folder.exists()
 with zipfile.ZipFile(a.certified_zip)as z:
  files={}
  for i in z.infolist():
   if i.is_dir():continue
   assert '/' not in i.filename and '\\' not in i.filename and i.filename not in files and i.file_size<300000000
   files[i.filename]=z.read(i)
 tarname=release+'.tar.gz';assert set(files)=={tarname,release+'.manifest.json',tarname+'.sha256'}
 m=json.loads(files[release+'.manifest.json']);artifact=sha(files[tarname]);assert m['source']['commit_sha']==a.source and m['artifact']['sha256']==artifact and m['artifact']['size_bytes']==len(files[tarname]);assert m['runtime']['source_ci_certified'] is True and m['runtime']['business_runtime_activation_ready'] is True and m['build']['provenance_reference']==f'github-actions:{a.run}:1:source-tested';assert files[tarname+'.sha256'].decode().split()[0]==artifact
 folder.mkdir(parents=True,mode=0o700);(folder/'release.tar.gz').write_bytes(files[tarname]);(folder/'release.manifest.json').write_bytes(files[release+'.manifest.json']);payload=folder/'payload';payload.mkdir(mode=0o750);inventory=[];seen=set();total=0
 with tarfile.open(folder/'release.tar.gz','r:gz')as t:
  for i in t:
   path=pathlib.PurePosixPath(i.name);assert path.parts and path.parts[0]==release and not path.is_absolute() and all(x not in ('.','..')for x in path.parts);assert all(c.isalnum()or c in '_.@+/-'for c in i.name)and i.name not in seen and(i.isdir()or i.isfile());assert not i.name.endswith('/apps/web/.env');seen.add(i.name);target=payload.joinpath(*path.parts)
   if i.isdir():target.mkdir(parents=True,exist_ok=True,mode=0o750);continue
   total+=i.size;assert total<500000000 and len(seen)<20000;raw=t.extractfile(i).read();assert len(raw)==i.size;target.parent.mkdir(parents=True,exist_ok=True,mode=0o750);target.write_bytes(raw);target.chmod(0o640);inventory.append(sha(raw)+'  '+i.name)
 meta=json.loads((payload/release/'RELEASE.json').read_text());assert meta['source_commit']==a.source and meta['migration_count']==27 and 6300<=len(inventory)<=7000
 (folder/'release-files.sha256').write_text('\n'.join(sorted(inventory))+'\n')
 operator=(repo/'tools/production/cpanel/update-oneqaydev-business-experience.php').read_text();assert all(x in operator for x in ['__SOURCE__','__ARTIFACT__','__RUN__']);operator=operator.replace('__SOURCE__',a.source).replace('__ARTIFACT__',artifact).replace('__RUN__',str(a.run));(folder/'update.php').write_text(operator);shutil.copyfile(repo/'tools/production/cpanel/install-oneqaydev-mail-stock.php',folder/'database-backup-helper.php')
 shutil.copyfile(repo/'tools/production/cpanel/business-experience-migrations.php',folder/'business-experience-migrations.php')
 request={'source':a.source,'artifact':artifact,'run':a.run,'previous_source':'d63cf4dd44af7e32198166c9521dd6516114de53','domain':'oneqaydev.n07.my.id','schema_change':True,'business_reset_allowed':False,'credentials_changed':False,'window_seconds':3600,'file_count':len(inventory),'retention':'verified_private_zip_after_healthy_worker_and_180s','sha256':{n:sha((folder/n).read_bytes())for n in ['update.php','database-backup-helper.php','business-experience-migrations.php','release-files.sha256','release.manifest.json','release.tar.gz']}}
 (folder/'authority-request.json').write_text(json.dumps(request,indent=2)+'\n');requestsha=sha((folder/'authority-request.json').read_bytes())
 (folder/'authorization-body.txt').write_text(f'ONEQAYDEV BUSINESS EXPERIENCE DEPLOY AUTHORIZATION\nREQUEST_SHA256: {requestsha}\nSOURCE_COMMIT: {a.source}\nARTIFACT_SHA256: {artifact}\nTARGET: https://oneqaydev.n07.my.id\nSCHEMA_CHANGE: ADDITIVE_2_MODULE_MIGRATIONS\nPRESERVE_BUSINESS_ON: REQUIRED\nBUSINESS_DATA_RESET: NOT_AUTHORIZED\nCREDENTIAL_CHANGES: NOT_AUTHORIZED\nWINDOW_SECONDS: 3600\nAuthor by Lab | zefry')
 command='/usr/local/bin/php /home/pekd7254/oneqay-production-test/'+name+'/update.php'
 (folder/'PETUNJUK.txt').write_text('Extract only inside /home/pekd7254/oneqay-production-test. Never public_html.\n1. '+command+' preflight > /home/pekd7254/oneqay-production-test/'+name+'/preflight.log 2>&1\n2. Verify READY_NO_ACTIVATION. Publish authorization-body.txt as a fresh owner comment on PR987.\n3. '+command+' update OWNER_COMMENT_ID > /home/pekd7254/oneqay-production-test/'+name+'/update.log 2>&1\n4. Verify update-result.json. Replace only the previous oneQay worker cron with every-minute:\n'+command+' work > /home/pekd7254/oneqay-production-test/'+name+'/worker.log 2>&1\n5. Old expanded release is privately archived after a healthy current worker and a 180-second drain window. Verify archive-result.json. Never delete an unverified archive or current release.\nWindows signed publication is separate and requires the original Actions signing key.\n')
 zipfile_path=a.output/(name+'.zip')
 with zipfile.ZipFile(zipfile_path,'w',zipfile.ZIP_DEFLATED,compresslevel=1)as z:
  for f in sorted(folder.rglob('*')):
   if f.is_file():z.write(f,f.relative_to(a.output))
 print(json.dumps({'package':str(zipfile_path),'package_sha256':sha(zipfile_path.read_bytes()),'request_sha256':requestsha,'artifact_sha256':artifact,'file_count':len(inventory),'schema_change':True}))
if __name__=='__main__':main()
