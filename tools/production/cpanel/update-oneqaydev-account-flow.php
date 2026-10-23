<?php
// Author by Lab | zefry. Certified source-only successor; no schema or business-data mutation.
declare(strict_types=1);
const N_SOURCE='__SOURCE__';const N_ARTIFACT='__ARTIFACT__';const N_RUN=__RUN__;
const N_ROOT='/home/pekd7254/oneqay-production-test';const N_PUBLIC='/home/pekd7254/public_html/oneqaydev.n07.my.id';
const N_OLD_SOURCE='938d9c32d8782c8b54fde8ba9a5143e87c01463a';
const N_OLD_ARTIFACT='fbd6c8d41182ec9810894ae655f890a552d8fb93eef2b3699cc762c403c19024';
const N_OLD_ENV='41cc19d0fa1df0953ff63047dccb35586050ddc3c56fe2cfb06ad17db92da51f';
const N_OLD_ENTRY='fa3773c72bfc0460ef029722caf5b5340de6983d2c33efa71d29fa9aa4dd25e8';
const N_OLD_ROUTING='af3eb6591a871efaa8ab78a026a92b2f331db1a8fc585a926d4c402debe5040d';
const N_OLD_INVENTORY='2fbe13714957fd52625339af6ed589b9ebd64653c08269aacd256a6ef97e98bd';
const N_INDEX='6870348b43644d61fd80d7dcef4bdc4b9158a1a314ce558d6752dda8ab1c2934';
function nNeed(bool $ok,string $code):void{if(!$ok)throw new RuntimeException($code);}
function uAssert(bool $ok,string $code):void{nNeed($ok,$code);}
function nRelease():string{return 'production-'.substr(N_SOURCE,0,12);}
function nPackage():string{return N_ROOT.'/oneqaydev-account-flow-'.substr(N_SOURCE,0,12);}
function nApp():string{return N_ROOT.'/releases/'.nRelease().'/apps/web';}
function nOldApp():string{return N_ROOT.'/releases/production-938d9c32d878/apps/web';}
function nEnv():string{return N_ROOT.'/shared/production-business-runtime-'.substr(N_SOURCE,0,12).'-pos.env';}
function nEntry():string{return 'oneqay-business-'.substr(N_SOURCE,0,12).'-pos.php';}
function nHash(string $file,string $sha):void{nNeed(preg_match('/\A[a-f0-9]{64}\z/D',$sha)===1&&is_file($file)&&!is_link($file)&&hash_equals($sha,(string)hash_file('sha256',$file)),'SOURCE_OR_BINDING_CHANGED');}
function nJson(string $file):array{nNeed(is_file($file)&&!is_link($file)&&filesize($file)>0&&filesize($file)<2000000,'JSON_FILE_INVALID');$v=json_decode(file_get_contents($file),true,64,JSON_THROW_ON_ERROR);nNeed(is_array($v)&&!array_is_list($v),'JSON_OBJECT_REQUIRED');return $v;}
function nWrite(string $file,string $bytes,int $mode=0600):void{
 nNeed(!is_link($file),'WRITE_LINK_DENIED');$tmp=$file.'.stage-'.bin2hex(random_bytes(6));$mask=umask(0077);try{$h=fopen($tmp,'x');}finally{umask($mask);}nNeed(is_resource($h)&&chmod($tmp,$mode),'WRITE_CREATE_FAILED');
 try{nNeed(fwrite($h,$bytes)===strlen($bytes)&&fflush($h),'WRITE_FAILED');fclose($h);$h=null;nNeed(rename($tmp,$file),'ATOMIC_WRITE_FAILED');}finally{if(is_resource($h))fclose($h);if(is_file($tmp))unlink($tmp);}
}
function nReport(string $name,array $data):void{nWrite(__DIR__.'/'.$name,json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");}
function nInventory(string $inventory,string $root,string $release,int $count,bool $strict):void{
 $seen=[];foreach(file($inventory,FILE_IGNORE_NEW_LINES)as$line){nNeed(preg_match('~\A([a-f0-9]{64})  ('.preg_quote($release,'~').'/[A-Za-z0-9_.@+/-]+)\z~D',$line,$m)===1&&!str_contains($m[2],'..')&&!isset($seen[$m[2]]),'INVENTORY_INVALID');nHash($root.'/'.$m[2],$m[1]);$seen[$m[2]]=true;}
 nNeed(count($seen)===$count,'INVENTORY_COUNT_CHANGED');if($strict)foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$release,FilesystemIterator::SKIP_DOTS))as$f){nNeed($f->isFile()&&!$f->isLink(),'PAYLOAD_SPECIAL_FILE_DENIED');nNeed(isset($seen[substr($f->getPathname(),strlen($root)+1)]),'UNLISTED_SOURCE_FILE');}
}
function nCopyPublic(string $from,string $to):void{
 nNeed(realpath($from)===$from&&is_dir($from)&&!is_link($from)&&!file_exists($to)&&!is_link($to)&&mkdir($to,0755)&&chmod($to,0755),'PUBLIC_COPY_PATH_INVALID');
 foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($from,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST)as$f){nNeed(!$f->isLink()&&($f->isFile()||$f->isDir()),'PUBLIC_SPECIAL_FILE_DENIED');$target=$to.'/'.substr($f->getPathname(),strlen($from)+1);if($f->isDir())nNeed(mkdir($target,0755)&&chmod($target,0755),'PUBLIC_DIRECTORY_FAILED');else nNeed(copy($f->getPathname(),$target)&&chmod($target,0644),'PUBLIC_FILE_FAILED');}
}
function nCarryPublicAssets(string $old,string $stage):void{
 $m=nJson($old.'/manifest.json');$entry=$m['resources/js/app.ts'];foreach([$entry['file'],...($entry['css']??[])]as$f){nNeed(preg_match('~\Aassets/[A-Za-z0-9_.-]+\.(?:js|css)\z~D',$f)===1,'OLD_ASSET_PATH_INVALID');$hash=hash_file('sha256',$old.'/'.$f);if(is_file($stage.'/'.$f)){nHash($stage.'/'.$f,$hash);continue;}nNeed(!is_link($stage.'/'.$f)&&copy($old.'/'.$f,$stage.'/'.$f)&&chmod($stage.'/'.$f,0644),'OLD_ASSET_GRACE_COPY_FAILED');nHash($stage.'/'.$f,$hash);}
}
function nSchema($db):array{
 $tables=$db->getSchemaBuilder()->getTableListing($db->getDriverName()==='mysql'?$db->getDatabaseName():null,false);sort($tables);$ddl=[];
 foreach($tables as$t){nNeed(preg_match('/\A[A-Za-z0-9_]+\z/D',$t)===1,'SCHEMA_NAME_INVALID');$sql=$db->getDriverName()==='mysql'?array_values((array)$db->selectOne('SHOW CREATE TABLE `'.$t.'`'))[1]:(string)$db->selectOne('SELECT sql FROM sqlite_master WHERE type = ? AND name = ?',['table',$t])->sql;$ddl[$t]=hash('sha256',preg_replace('/ AUTO_INCREMENT=\d+/','',$sql));}
 return ['ddl'=>$ddl,'receipts'=>$db->table('migrations')->orderBy('id')->get()->map(fn($r)=>(array)$r)->all()];
}
function nRequest():array{
 nNeed(realpath(__DIR__)===nPackage()&&!is_link(__FILE__),'PRIVATE_PACKAGE_LOCATION_REQUIRED');$r=nJson(__DIR__.'/authority-request.json');
 foreach(['source'=>N_SOURCE,'artifact'=>N_ARTIFACT,'run'=>N_RUN,'previous_source'=>N_OLD_SOURCE,'domain'=>'oneqaydev.n07.my.id','schema_change'=>false,'business_reset_allowed'=>false,'credentials_changed'=>false,'window_seconds'=>3600,'retention'=>'verified_private_zip_after_healthy_worker_and_180s']as$k=>$v)nNeed(($r[$k]??null)===$v,'REQUEST_SCOPE_CHANGED');
 foreach(['update.php','database-backup-helper.php','release-files.sha256','release.manifest.json','release.tar.gz']as$f)nHash(__DIR__.'/'.$f,$r['sha256'][$f]??'');nHash(__DIR__.'/release.tar.gz',N_ARTIFACT);$m=nJson(__DIR__.'/release.manifest.json');
 nNeed(($m['source']['commit_sha']??null)===N_SOURCE&&($m['artifact']['sha256']??null)===N_ARTIFACT&&($m['runtime']['source_ci_certified']??null)===true&&($m['runtime']['business_runtime_activation_ready']??null)===true&&($m['build']['provenance_reference']??null)==='github-actions:'.N_RUN.':1:source-tested','CERTIFIED_SOURCE_REQUIRED');return $r;
}
function nOld():array{
 nNeed(realpath(N_ROOT)===N_ROOT&&realpath(N_PUBLIC)===N_PUBLIC&&realpath(nOldApp())===nOldApp(),'HOST_ROOT_CHANGED');nHash(N_PUBLIC.'/index.php',N_INDEX);nHash(N_PUBLIC.'/.htaccess',N_OLD_ROUTING);nHash(N_PUBLIC.'/oneqay-business-938d9c32d878-pos.php',N_OLD_ENTRY);
 $env=N_ROOT.'/shared/production-business-runtime-938d9c32d878-pos.env';nHash($env,N_OLD_ENV);nHash(nOldApp().'/.env',N_OLD_ENV);$inventory=N_ROOT.'/oneqaydev-pos-sync-938d9c32d878/release-files.sha256';nHash($inventory,N_OLD_INVENTORY);nInventory($inventory,N_ROOT.'/releases','production-938d9c32d878',6404,false);
 require_once nOldApp().'/vendor/autoload.php';$app=require nOldApp().'/bootstrap/app.php';$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();nNeed(\App\Infrastructure\Runtime\ProductionBusinessRuntimeGate::allows('production')&&env('ONEQAY_RUNNING_SOURCE_COMMIT')===N_OLD_SOURCE&&env('ONEQAY_RUNNING_ARTIFACT_SHA256')===N_OLD_ARTIFACT,'PREDECESSOR_NATIVE_GATE_DENIED');$db=$app->make('db')->connection();nNeed($db->getDriverName()==='mysql','MYSQL_REQUIRED');return [$app,$db,file_get_contents($env)];
}
function nPreflight(array $r):array{
 [$app,$db,$env]=nOld();$before=nSchema($db);nNeed(count($before['ddl'])===89&&count($before['receipts'])===39,'EXACT_PREDECESSOR_SCHEMA_REQUIRED');nInventory(__DIR__.'/release-files.sha256',__DIR__.'/payload',nRelease(),$r['file_count'],true);
 $payload=__DIR__.'/payload/'.nRelease().'/apps/web';foreach(['database/migrations/*.php','database/module-migrations/*/*.php']as$glob){$old=glob(nOldApp().'/'.$glob);$new=glob($payload.'/'.$glob);nNeed(count($old)===count($new),'SCHEMA_SOURCE_SET_CHANGED');foreach($old as$f)nHash($payload.'/'.substr($f,strlen(nOldApp())+1),hash_file('sha256',$f));}
 foreach([nApp(),nEnv(),N_PUBLIC.'/'.nEntry(),__DIR__.'/rollback']as$p)nNeed(!file_exists($p)&&!is_link($p),'CANDIDATE_ALREADY_PREPARED');return [$app,$db,$env,$before];
}
function nAuthority(string $id):array{
 nNeed(preg_match('/\A[1-9][0-9]{6,18}\z/D',$id)===1,'OWNER_COMMENT_REQUIRED');$ch=curl_init('https://api.github.com/repos/labzefry/oneQay/issues/comments/'.$id);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>25,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,CURLOPT_HTTPHEADER=>['Accept: application/vnd.github+json','User-Agent: oneQay-Source-Bound-Deployment']]);$raw=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_RESPONSE_CODE);curl_close($ch);nNeed($status===200&&is_string($raw)&&strlen($raw)<30000,'NATIVE_OWNER_FETCH_FAILED');$c=json_decode($raw,true,32,JSON_THROW_ON_ERROR);
 nNeed(($c['user']['login']??null)==='labzefry'&&($c['user']['type']??null)==='User'&&($c['author_association']??null)==='OWNER'&&($c['issue_url']??null)==='https://api.github.com/repos/labzefry/oneQay/issues/984','NATIVE_OWNER_IDENTITY_DENIED');$at=strtotime($c['created_at']??'');nNeed(is_int($at)&&($c['created_at']??null)===($c['updated_at']??null)&&time()>=$at&&time()<$at+3600,'FRESH_OWNER_AUTHORITY_REQUIRED');
 $want="ONEQAYDEV ACCOUNT FLOW DEPLOY AUTHORIZATION\nREQUEST_SHA256: ".hash_file('sha256',__DIR__.'/authority-request.json')."\nSOURCE_COMMIT: ".N_SOURCE."\nARTIFACT_SHA256: ".N_ARTIFACT."\nTARGET: https://oneqaydev.n07.my.id\nSCHEMA_CHANGE: NONE\nPRESERVE_BUSINESS_ON: REQUIRED\nBUSINESS_DATA_RESET: NOT_AUTHORIZED\nCREDENTIAL_CHANGES: NOT_AUTHORIZED\nWINDOW_SECONDS: 3600\nAuthor by Lab | zefry";nNeed(hash_equals($want,str_replace("\r\n","\n",$c['body']??'')),'EXACT_OWNER_AUTHORITY_REQUIRED');return ['comment_id'=>(int)$id,'authorized_at'=>$at,'expires_at'=>$at+3600];
}
function nHttp(string $path):array{
 $ch=curl_init('https://oneqaydev.n07.my.id'.$path.(str_contains($path,'?')?'&':'?').'oneqay_verify='.bin2hex(random_bytes(6)));curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_ENCODING=>'',CURLOPT_FOLLOWLOCATION=>false,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>30,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,CURLOPT_HTTPHEADER=>['Accept: application/json,text/html,*/*','User-Agent: oneQay-Deployment-Verification']]);$body=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_RESPONSE_CODE);curl_close($ch);nNeed(is_string($body)&&strlen($body)<4000000,'HTTPS_READ_FAILED');return ['status'=>$status,'sha256'=>hash('sha256',$body)];
}
function nProbe():array{
 $out=[];foreach(['/','/owner-login','/business-login','/register']as$p){$out[$p]=nHttp($p);nNeed($out[$p]['status']===200,'PUBLIC_HTTP_GATE_FAILED');}$out['/pos']=nHttp('/pos');nNeed(in_array($out['/pos']['status'],[401,403],true),'POS_AUTHORIZATION_GATE_FAILED');$m=nJson(N_PUBLIC.'/build/manifest.json');$entry=$m['resources/js/app.ts'];foreach([$entry['file'],...($entry['css']??[])]as$f){nNeed(preg_match('~\Aassets/[A-Za-z0-9_.-]+\z~D',$f)===1,'ASSET_PATH_INVALID');$out['/build/'.$f]=nHttp('/build/'.$f);nNeed($out['/build/'.$f]['status']===200&&hash_equals(hash_file('sha256',N_PUBLIC.'/build/'.$f),$out['/build/'.$f]['sha256']),'PUBLIC_ASSET_HTTP_HASH_FAILED');}return $out;
}
/** Filesystem-only promotion primitive; callback failure restores the prior business-on routing/assets. */
function nPromote(string $stage,string $build,string $backup,string $routing,string $nextRouting,callable $probe,?string $privateRoutingBackup=null):array{
 $old=file_get_contents($routing);$saved=$privateRoutingBackup??($backup.'.routing');$next=$saved.'.next';nNeed(is_string($old)&&!is_link($routing)&&!file_exists($saved)&&!is_link($saved)&&!file_exists($next)&&!is_link($next)&&!file_exists($backup)&&!is_link($backup),'PROMOTION_PATH_INVALID');
 nNeed(stat(dirname($saved))['dev']===stat(dirname($routing))['dev'],'ATOMIC_ROUTING_FILESYSTEM_REQUIRED');nWrite($saved,$old);nWrite($next,$nextRouting,0644);nNeed(rename($build,$backup),'BUILD_BACKUP_FAILED');$switched=false;
 try{nNeed(rename($stage,$build),'BUILD_PROMOTION_FAILED');$switched=true;nNeed(rename($next,$routing),'ROUTING_PROMOTION_FAILED');return $probe();}
 catch(Throwable $e){nNeed(rename($saved,$routing),'OLD_ROUTING_RESTORE_FAILED');if($switched)nNeed(rename($build,$stage),'CANDIDATE_BUILD_RESTORE_FAILED');nNeed(rename($backup,$build),'OLD_BUILD_RESTORE_FAILED');throw $e;}
 finally{if(is_file($next))unlink($next);}
}
function nVerifyActive(array $r):void{
 nNeed(($r['state']??null)==='ACCOUNT_FLOW_RELEASE_PUBLISHED_ACCEPTANCE_PENDING'&&($r['source']??null)===N_SOURCE,'SOURCE_NOT_ACTIVE');nHash(nEnv(),$r['runtime_sha256']);nHash(nApp().'/.env',$r['runtime_sha256']);nHash(N_PUBLIC.'/'.nEntry(),$r['entry_sha256']);nHash(N_PUBLIC.'/.htaccess',$r['routing_sha256']);nHash(N_PUBLIC.'/index.php',N_INDEX);
}
function nDeploy(array $r,array $grant):array{
 [$app,$db,$oldEnv,$before]=nPreflight($r);nNeed(time()<$grant['expires_at'],'AUTHORITY_EXPIRED');$oldEntry=file_get_contents(N_PUBLIC.'/oneqay-business-938d9c32d878-pos.php');$oldRouting=file_get_contents(N_PUBLIC.'/.htaccess');$env=$oldEnv;
 foreach(['ONEQAY_RUNNING_SOURCE_COMMIT'=>N_SOURCE,'ONEQAY_RUNNING_ARTIFACT_SHA256'=>N_ARTIFACT]as$k=>$v){$env=preg_replace('/^'.$k.'=[^\r\n]*$/m',$k.'='.$v,$env,-1,$n);nNeed($n===1,'RUNTIME_BINDING_REWRITE_FAILED');}
 nNeed(hash_equals($oldEnv,str_replace(['ONEQAY_RUNNING_SOURCE_COMMIT='.N_SOURCE,'ONEQAY_RUNNING_ARTIFACT_SHA256='.N_ARTIFACT],['ONEQAY_RUNNING_SOURCE_COMMIT='.N_OLD_SOURCE,'ONEQAY_RUNNING_ARTIFACT_SHA256='.N_OLD_ARTIFACT],$env)),'UNRELATED_RUNTIME_CHANGED');$envSha=hash('sha256',$env);$entry=$oldEntry;
 foreach([var_export(nOldApp(),true)=>var_export(nApp(),true),var_export(N_ROOT.'/shared/production-business-runtime-938d9c32d878-pos.env',true)=>var_export(nEnv(),true),var_export(N_OLD_ENV,true)=>var_export($envSha,true),'production-938d9c32d878-pos'=>nRelease().'-pos']as$from=>$to){nNeed(str_contains($entry,$from),'BRIDGE_BINDING_MISSING');$entry=str_replace($from,$to,$entry);}
 nNeed(str_contains($oldRouting,'oneqay-business-938d9c32d878-pos.php'),'ROUTING_BINDING_MISSING');$routing=str_replace('oneqay-business-938d9c32d878-pos.php',nEntry(),$oldRouting);$stage=N_PUBLIC.'/.oneqay-account-flow-build-'.substr(N_SOURCE,0,12);nCopyPublic(__DIR__.'/payload/'.nRelease().'/apps/web/public/build',$stage);nCarryPublicAssets(nOldApp().'/public/build',$stage);
 nNeed(mkdir(__DIR__.'/rollback',0700)&&chmod(__DIR__.'/rollback',0700),'ROLLBACK_DIRECTORY_FAILED');nWrite(__DIR__.'/rollback/runtime.env',$oldEnv);nWrite(__DIR__.'/rollback/entry.php',$oldEntry);nWrite(__DIR__.'/rollback/routing.htaccess',$oldRouting);
 require_once __DIR__.'/database-backup-helper.php';$backup=uPrivateDatabaseBackup($db,__DIR__.'/rollback/database.sql.gz');nNeed($backup['tables']===89,'DATABASE_BACKUP_SCOPE_CHANGED');nReport('database-backup.json',$backup);
 nNeed(rename(__DIR__.'/payload/'.nRelease(),N_ROOT.'/releases/'.nRelease()),'CERTIFIED_RELEASE_MOVE_FAILED');nWrite(nEnv(),$env);nWrite(nApp().'/.env',$env);nWrite(N_PUBLIC.'/'.nEntry(),$entry,0644);
 $promoted=false;try{
  nNeed(time()<$grant['expires_at'],'AUTHORITY_EXPIRED_BEFORE_SWITCH');$https=nPromote($stage,N_PUBLIC.'/build',N_PUBLIC.'/.oneqay-account-flow-previous-build-'.substr(N_SOURCE,0,12),N_PUBLIC.'/.htaccess',$routing,function()use($db,$before){$https=nProbe();nNeed(nSchema($db)===$before,'DATABASE_SCHEMA_CHANGED');return $https;},__DIR__.'/rollback/routing.restore');$promoted=true;
  $result=['state'=>'ACCOUNT_FLOW_RELEASE_PUBLISHED_ACCEPTANCE_PENDING','source'=>N_SOURCE,'release'=>nRelease(),'previous_source'=>N_OLD_SOURCE,'artifact'=>N_ARTIFACT,'certification_run'=>N_RUN,'file_count'=>$r['file_count'],'runtime_sha256'=>$envSha,'entry_sha256'=>hash('sha256',$entry),'routing_sha256'=>hash('sha256',$routing),'database'=>['migration_receipts'=>39,'tables'=>89,'ddl'=>'UNCHANGED','migration'=>'NOT_RUN'],'https'=>$https,'asset_grace'=>'PREDECESSOR_HASHED_ASSETS_PRESERVED','business_reset'=>'NOT_PERFORMED','credentials'=>'PRESERVED','smtp'=>'PRESERVED_NOT_SENT','offline_receipts'=>'NOT_ACCEPTANCE_TESTED','worker'=>'REQUIRES_CRON_REPLACEMENT','old_release'=>'PRESERVED_UNTIL_VERIFIED_ARCHIVE','grant'=>$grant,'at'=>time()];nReport('update-result.json',$result);return $result;
 }catch(Throwable $e){if($promoted){nNeed(rename(__DIR__.'/rollback/routing.restore',N_PUBLIC.'/.htaccess'),'OLD_ROUTING_RESTORE_FAILED');nNeed(rename(N_PUBLIC.'/build',$stage)&&rename(N_PUBLIC.'/.oneqay-account-flow-previous-build-'.substr(N_SOURCE,0,12),N_PUBLIC.'/build'),'OLD_BUILD_RESTORE_FAILED');}nReport('update-result.json',['state'=>'STOPPED_FORWARD_REVIEW_REQUIRED','source'=>N_SOURCE,'code'=>'DEPLOYMENT_VERIFICATION_FAILED','database_schema'=>'NO_MIGRATION_RUN','business_reset'=>'NOT_PERFORMED','at'=>time()]);throw $e;}
}
/** Verify the complete private archive before removing any expanded application file. */
function nArchiveTree(string $root,string $file):array{
 nNeed(class_exists(ZipArchive::class),'ZIP_EXTENSION_REQUIRED');nNeed(is_dir($root)&&realpath($root)===$root&&!is_link($root)&&!file_exists($file)&&!is_link($file),'ARCHIVE_PATH_INVALID');$plan=[];$bytes=0;
 foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS))as$f){nNeed($f->isFile()&&!$f->isLink(),'ARCHIVE_SPECIAL_FILE_DENIED');nNeed($f->getSize()<200000000,'ARCHIVE_FILE_TOO_LARGE');$name=substr($f->getPathname(),strlen($root)+1);$plan[$name]=['sha256'=>hash_file('sha256',$f->getPathname()),'bytes'=>$f->getSize()];$bytes+=$f->getSize();nNeed(count($plan)<20000&&$bytes<500000000,'ARCHIVE_SCOPE_LIMIT');}
 $stage=$file.'.stage-'.bin2hex(random_bytes(6));$zip=new ZipArchive();$mask=umask(0077);try{$opened=$zip->open($stage,ZipArchive::CREATE|ZipArchive::EXCL);}finally{umask($mask);}nNeed($opened===true,'ARCHIVE_CREATE_FAILED');
 try{foreach($plan as$name=>$row)nNeed($zip->addFile($root.'/'.$name,$name),'ARCHIVE_ADD_FAILED');nNeed($zip->close()&&chmod($stage,0600),'ARCHIVE_CLOSE_FAILED');$zip=null;$check=new ZipArchive();nNeed($check->open($stage,ZipArchive::CHECKCONS)===true&&$check->numFiles===count($plan),'ARCHIVE_VERIFY_OPEN_FAILED');
  try{foreach($plan as$name=>$row){$stream=$check->getStream($name);nNeed(is_resource($stream),'ARCHIVE_STREAM_INVALID');$hash=hash_init('sha256');$length=0;try{while(!feof($stream)){$raw=fread($stream,1048576);nNeed(is_string($raw),'ARCHIVE_STREAM_READ_FAILED');$length+=strlen($raw);hash_update($hash,$raw);}}finally{fclose($stream);}nNeed($length===$row['bytes']&&hash_equals($row['sha256'],hash_final($hash)),'ARCHIVE_CONTENT_MISMATCH');nHash($root.'/'.$name,$row['sha256']);}}finally{$check->close();}
  nNeed(rename($stage,$file),'ARCHIVE_PROMOTION_FAILED');$sha=hash_file('sha256',$file);
  foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST)as$f){nNeed(!$f->isLink(),'ARCHIVE_DELETE_LINK_DENIED');if($f->isDir())nNeed(rmdir($f->getPathname()),'OLD_DIRECTORY_REMOVE_FAILED');else{$name=substr($f->getPathname(),strlen($root)+1);nNeed(isset($plan[$name]),'OLD_FILE_SET_CHANGED');nHash($f->getPathname(),$plan[$name]['sha256']);nNeed(unlink($f->getPathname()),'OLD_FILE_REMOVE_FAILED');}}
  nNeed(rmdir($root),'OLD_RELEASE_REMOVE_FAILED');return ['state'=>'VERIFIED_PRIVATE_ARCHIVE_EXPANDED_RELEASE_REMOVED','file'=>basename($file),'sha256'=>$sha,'files'=>count($plan),'bytes_before'=>$bytes,'mode'=>'0600','at'=>time()];
 }finally{if($zip instanceof ZipArchive)$zip->close();if(is_file($stage))unlink($stage);}
}
function nArchive():array{
 $r=nJson(__DIR__.'/update-result.json');nVerifyActive($r);$worker=nJson(__DIR__.'/worker-result.json');nNeed(($worker['source']??null)===N_SOURCE&&($worker['failed']??null)===false&&time()-($worker['at']??0)<180,'HEALTHY_CURRENT_WORKER_REQUIRED');nNeed(time()-$r['at']>=180,'OLD_REQUEST_DRAIN_WINDOW_REQUIRED');
 if(is_file(__DIR__.'/archive-result.json'))return nJson(__DIR__.'/archive-result.json');$old=N_ROOT.'/releases/production-938d9c32d878';nNeed(realpath($old)===$old&&$old!==dirname(nApp(),2),'OLD_RELEASE_ARCHIVE_BOUNDARY');$inventory=N_ROOT.'/oneqaydev-pos-sync-938d9c32d878/release-files.sha256';nHash($inventory,N_OLD_INVENTORY);nInventory($inventory,N_ROOT.'/releases','production-938d9c32d878',6404,false);nHash(nOldApp().'/.env',N_OLD_ENV);
 $dir=N_ROOT.'/release-archives';nNeed(!is_link($dir)&&(is_dir($dir)||mkdir($dir,0700))&&chmod($dir,0700),'PRIVATE_ARCHIVE_DIRECTORY_FAILED');$result=nArchiveTree($old,$dir.'/production-938d9c32d878-before-'.substr(N_SOURCE,0,12).'.zip');nReport('archive-result.json',$result);return $result;
}
function nWork():array{
 $r=nJson(__DIR__.'/update-result.json');nVerifyActive($r);nNeed(!is_link(__DIR__.'/worker.lock')&&!is_dir(__DIR__.'/worker.lock'),'WORKER_LOCK_INVALID');$mask=umask(0077);try{$h=fopen(__DIR__.'/worker.lock','c');}finally{umask($mask);}nNeed(is_resource($h)&&chmod(__DIR__.'/worker.lock',0600),'WORKER_LOCK_FAILED');if(!flock($h,LOCK_EX|LOCK_NB))return ['state'=>'already_running'];
 try{require nApp().'/vendor/autoload.php';$app=require nApp().'/bootstrap/app.php';$kernel=$app->make(\Illuminate\Contracts\Console\Kernel::class);$kernel->bootstrap();nNeed(\App\Infrastructure\Runtime\ProductionBusinessRuntimeGate::allows('production')&&env('ONEQAY_RUNNING_SOURCE_COMMIT')===N_SOURCE,'NEW_NATIVE_PRODUCTION_GATE_DENIED');$output=new \Symfony\Component\Console\Output\BufferedOutput();$exit=$kernel->call('oneqay:control:work',['--limit'=>20],$output);$v=json_decode(trim($output->fetch()),true,64,JSON_THROW_ON_ERROR);if(($v['state']??null)==='already_running')return $v;nNeed(is_bool($v['failed']??null)&&is_int($v['at']??null)&&abs(time()-$v['at'])<180&&(($exit===0)===($v['failed']===false)),'WORKER_OUTPUT_INVALID');$v['source']=N_SOURCE;nReport('worker-result.json',$v);if($v['failed']===false&&time()-$r['at']>=180&&!is_file(__DIR__.'/archive-result.json')){try{nArchive();}catch(Throwable){nReport('archive-result.json',['state'=>'REVIEW_REQUIRED_OLD_RELEASE_RETAINED_OR_VERIFIED_ARCHIVE_AVAILABLE','at'=>time()]);}}return $v;}finally{flock($h,LOCK_UN);fclose($h);}
}
if(PHP_SAPI==='cli'&&realpath($_SERVER['SCRIPT_FILENAME']??'')===__FILE__){
 try{$r=nRequest();$mode=$argv[1]??'';nNeed(in_array($mode,['preflight','update','work','archive'],true),'COMMAND_INVALID');if($mode==='archive'){$v=nArchive();fwrite(STDOUT,'ONEQAY|'.$v['state']."\n");exit(0);}if($mode==='work'){$v=nWork();fwrite(STDOUT,"ONEQAY|WORKER_COMPLETE\n");exit(($v['failed']??false)?1:0);}
  nNeed(!is_link(__DIR__.'/deployment.lock')&&!is_dir(__DIR__.'/deployment.lock'),'DEPLOYMENT_LOCK_INVALID');$mask=umask(0077);try{$h=fopen(__DIR__.'/deployment.lock','c');}finally{umask($mask);}nNeed(is_resource($h)&&chmod(__DIR__.'/deployment.lock',0600)&&flock($h,LOCK_EX|LOCK_NB),'DEPLOYMENT_BUSY');
  if($mode==='preflight'){nPreflight($r);$v=['state'=>'READY_NO_ACTIVATION','source'=>N_SOURCE,'schema_change'=>false,'at'=>time()];nReport('preflight-result.json',$v);}else{if(is_file(__DIR__.'/update-result.json')){$v=nJson(__DIR__.'/update-result.json');nVerifyActive($v);}else $v=nDeploy($r,nAuthority($argv[2]??''));}fwrite(STDOUT,'ONEQAY|'.$v['state']."\n");
 }catch(Throwable $e){$code=$e instanceof RuntimeException&&preg_match('/\A[A-Z0-9_]+\z/D',$e->getMessage())?$e->getMessage():'OPERATOR_INTERNAL_ERROR';nReport('operator-error.json',['state'=>'STOPPED','code'=>$code,'at'=>time()]);fwrite(STDERR,'ONEQAY|'.$code."\n");exit(1);}
}
