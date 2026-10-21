<?php
// Author by Lab | zefry. Exact-source additive POS diagnostics successor. Never resets business data.
declare(strict_types=1);
const S_SOURCE='__SOURCE__';
const S_ARTIFACT='__ARTIFACT__';
const S_RUN=__RUN__;
const S_ROOT='/home/pekd7254/oneqay-production-test';
const S_PUBLIC='/home/pekd7254/public_html/oneqaydev.n07.my.id';
const S_PREVIOUS=S_ROOT.'/oneqaydev-ui-update-7e4197b44322';
const S_OLD_SOURCE='7e4197b443225e2022a26af9fd53a966d06c304f';
const S_OLD_RELEASE='production-7e4197b44322';
const S_OLD_ENV='05aca10c97be6cb3cbaeaac2fef2e5d0c4502b14e050292822fc7e7ff00e7299';
const S_OLD_ENTRY='4d53c3759441ac552055f47ef253c72cd6ccbebf2c23dfab8a42ba71a8fbc861';
const S_OLD_ROUTING='3b668015b8fd4c75c391a2eae06518885b41e4f904c88ecdcee62f790f179295';
const S_OLD_INVENTORY='73d8ac633df717409cb0907914296f573353bf8c12c90e253c68c165b7fcbae7';
const S_INDEX_SHA='6870348b43644d61fd80d7dcef4bdc4b9158a1a314ce558d6752dda8ab1c2934';
const S_OLD_RECOVERY='460c6725653a859e325284b8217ec864a9d7b942357843491c47c03aed45fafd';
function sNeed(bool $ok,string $code):void{if(!$ok)throw new RuntimeException($code);}
function uAssert(bool $ok,string $code):void{sNeed($ok,$code);}
function sHash(string $path,string $sha):void{sNeed(is_file($path)&&!is_link($path)&&hash_equals($sha,(string)hash_file('sha256',$path)),'SOURCE_OR_BINDING_CHANGED');}
function sRelease():string{return 'production-'.substr(S_SOURCE,0,12);}
function sPackage():string{return S_ROOT.'/oneqaydev-pos-sync-'.substr(S_SOURCE,0,12);}
function sApp():string{return S_ROOT.'/releases/'.sRelease().'/apps/web';}
function sEnv():string{return S_ROOT.'/shared/production-business-runtime-'.substr(S_SOURCE,0,12).'-pos.env';}
function sEntry():string{return 'oneqay-business-'.substr(S_SOURCE,0,12).'-pos.php';}
function sReport(string $name,array $data):void{recPublish(sPackage().'/'.$name,$data);}
function sRequest():array{
 sNeed(realpath(__DIR__)===sPackage()&&!is_link(__FILE__),'PRIVATE_PACKAGE_LOCATION_REQUIRED');
 sHash(S_PREVIOUS.'/recovery-20261022.php',S_OLD_RECOVERY);require_once S_PREVIOUS.'/recovery-20261022.php';
 $r=recJson(__DIR__.'/authority-request.json');
 foreach(['source'=>S_SOURCE,'artifact'=>S_ARTIFACT,'run'=>S_RUN,'previous_source'=>S_OLD_SOURCE,'domain'=>'oneqaydev.n07.my.id','migration'=>'pos/2026_10_22_000001_create_desktop_sync_diagnostics.php','business_reset_allowed'=>false,'credentials_changed'=>false,'window_seconds'=>3600]as$k=>$v)sNeed(($r[$k]??null)===$v,'REQUEST_SCOPE_CHANGED');
 foreach(['update.php','database-backup-helper.php','release-files.sha256','release.manifest.json','release.tar.gz']as$name)sHash(__DIR__.'/'.$name,$r['sha256'][$name]??'');
 sHash(__DIR__.'/release.tar.gz',S_ARTIFACT);$m=recJson(__DIR__.'/release.manifest.json');sNeed(($m['source']['commit_sha']??null)===S_SOURCE&&($m['artifact']['sha256']??null)===S_ARTIFACT&&($m['runtime']['source_ci_certified']??null)===true&&($m['runtime']['business_runtime_activation_ready']??null)===true,'CERTIFIED_SOURCE_REQUIRED');sNeed(($m['build']['provenance_reference']??null)==='github-actions:'.S_RUN.':1:source-tested','CERTIFIED_RUN_BINDING_REQUIRED');
 return $r;
}
function sInventory(string $file,string $root,string $release,int $count,bool $strict):void{
 $seen=[];foreach(explode("\n",trim((string)file_get_contents($file)))as$line){sNeed(preg_match('~\A([a-f0-9]{64})  ('.preg_quote($release,'~').'/[A-Za-z0-9_.@+/-]+)\z~D',$line,$m)===1&&!str_contains('/'.$m[2].'/','/../')&&!str_contains('/'.$m[2].'/','/./')&&!isset($seen[$m[2]]),'INVENTORY_INVALID');sHash($root.'/'.$m[2],$m[1]);$seen[$m[2]]=true;}
 sNeed(count($seen)===$count,'INVENTORY_COUNT_CHANGED');
 if($strict)foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$release,FilesystemIterator::SKIP_DOTS))as$f){sNeed(!$f->isLink()&&$f->isFile(),'SOURCE_SPECIAL_FILE_DENIED');sNeed(isset($seen[substr($f->getPathname(),strlen($root)+1)]),'UNLISTED_SOURCE_FILE');}
}
function sOldHost():array{
 sNeed(realpath(S_ROOT)===S_ROOT&&realpath(S_PUBLIC)===S_PUBLIC&&realpath(qOldApp())===qOldApp(),'HOST_ROOT_CHANGED');sHash(S_PUBLIC.'/index.php',S_INDEX_SHA);[, $old]=recBindings(S_PREVIOUS);sHash(S_PUBLIC.'/oneqay-business-7e4197b44322-pos.php',S_OLD_ENTRY);sHash(S_PUBLIC.'/.htaccess',S_OLD_ROUTING);sHash(S_PREVIOUS.'/release-files.sha256',S_OLD_INVENTORY);
 sInventory(S_PREVIOUS.'/release-files.sha256',S_ROOT.'/releases',S_OLD_RELEASE,6398,false);
 $runtime=(string)file_get_contents($old.'/.env');sNeed(str_contains($runtime,'ONEQAY_RUNNING_SOURCE_COMMIT='.S_OLD_SOURCE)&&str_contains($runtime,'ONEQAY_RUNNING_ARTIFACT_SHA256=03670693b16f3f1ba0c3b6d64b89445d251780e1bdacc81de523927caec3c49e'),'OLD_SOURCE_RUNTIME_MISMATCH');
 require_once $old.'/vendor/autoload.php';$app=require $old.'/bootstrap/app.php';$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();sNeed(\App\Infrastructure\Runtime\ProductionBusinessRuntimeGate::allows('production'),'OLD_NATIVE_PRODUCTION_GATE_DENIED');
 $db=$app->make('db')->connection();sNeed($db->getDriverName()==='mysql','MYSQL_REQUIRED');return [$app,$db,$runtime];
}
function sSchema($db):array{
 $tables=$db->getSchemaBuilder()->getTableListing($db->getDriverName()==='mysql'?$db->getDatabaseName():null,false);sort($tables);$ddl=[];foreach($tables as$t){sNeed(preg_match('/\A[A-Za-z0-9_]+\z/D',$t)===1,'SCHEMA_NAME_INVALID');$sql=$db->getDriverName()==='mysql'?array_values((array)$db->selectOne('SHOW CREATE TABLE `'.$t.'`'))[1]:(string)$db->selectOne('SELECT sql FROM sqlite_master WHERE type = ? AND name = ?',['table',$t])->sql;$ddl[$t]=hash('sha256',preg_replace('/ AUTO_INCREMENT=\d+/','',$sql));}
 return ['ddl'=>$ddl,'receipts'=>$db->table('migrations')->orderBy('id')->get()->map(fn($r)=>(array)$r)->all()];
}
function sPreflight(array $r):array{
 [$app,$db,$runtime]=sOldHost();$before=sSchema($db);sNeed(count($before['ddl'])===88&&count($before['receipts'])===38&&!isset($before['ddl']['oneqay_desktop_sync_diagnostics']),'EXACT_PREDECESSOR_SCHEMA_REQUIRED');
 $payload=__DIR__.'/payload/'.sRelease().'/apps/web';sInventory(__DIR__.'/release-files.sha256',__DIR__.'/payload',sRelease(),$r['file_count'],true);
 $meta=recJson(__DIR__.'/payload/'.sRelease().'/RELEASE.json');sNeed(($meta['source_commit']??null)===S_SOURCE&&($meta['migration_count']??null)===27,'PAYLOAD_METADATA_INVALID');
 foreach(glob(qOldApp().'/database/migrations/*.php')as$f)sHash($payload.'/database/migrations/'.basename($f),hash_file('sha256',$f));
 $oldModules=glob(qOldApp().'/database/module-migrations/*/*.php');$newModules=glob($payload.'/database/module-migrations/*/*.php');sNeed(count($newModules)===count($oldModules)+1,'ONLY_ONE_ADDITIVE_MODULE_ALLOWED');foreach($oldModules as$f){$rel=substr($f,strlen(qOldApp())+1);sHash($payload.'/'.$rel,hash_file('sha256',$f));}
 sNeed(is_file($payload.'/database/module-migrations/'.$r['migration']),'DIAGNOSTIC_MIGRATION_MISSING');
 foreach([sApp(),sEnv(),S_PUBLIC.'/'.sEntry(),__DIR__.'/rollback']as$p)sNeed(!file_exists($p)&&!is_link($p),'CANDIDATE_ALREADY_PREPARED');
 return [$app,$db,$runtime,$before];
}
function qOldApp():string{return S_ROOT.'/releases/'.S_OLD_RELEASE.'/apps/web';}
function sAuthority(string $id):array{
 sNeed(preg_match('/\A[1-9][0-9]{6,18}\z/D',$id)===1,'OWNER_COMMENT_ID_REQUIRED');$ch=curl_init('https://api.github.com/repos/labzefry/oneQay/issues/comments/'.$id);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>25,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,CURLOPT_HTTPHEADER=>['Accept: application/vnd.github+json','User-Agent: oneQay-Source-Bound-Deployment']]);$raw=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_RESPONSE_CODE);curl_close($ch);sNeed($status===200&&is_string($raw)&&strlen($raw)<30000,'NATIVE_OWNER_FETCH_FAILED');$c=json_decode($raw,true,32,JSON_THROW_ON_ERROR);
 sNeed(($c['user']['login']??null)==='labzefry'&&($c['user']['type']??null)==='User'&&($c['author_association']??null)==='OWNER'&&($c['issue_url']??null)==='https://api.github.com/repos/labzefry/oneQay/issues/981','NATIVE_OWNER_IDENTITY_DENIED');
 $at=strtotime($c['created_at']??'');sNeed(is_int($at)&&($c['updated_at']??null)===($c['created_at']??null)&&time()>=$at&&time()<$at+3600,'FRESH_OWNER_AUTHORITY_REQUIRED');
 $want="ONEQAYDEV POS SYNC DEPLOY AUTHORIZATION\nREQUEST_SHA256: ".hash_file('sha256',__DIR__.'/authority-request.json')."\nSOURCE_COMMIT: ".S_SOURCE."\nARTIFACT_SHA256: ".S_ARTIFACT."\nTARGET: https://oneqaydev.n07.my.id\nADDITIVE_MIGRATION: pos/2026_10_22_000001_create_desktop_sync_diagnostics.php\nPRESERVE_BUSINESS_ON: REQUIRED\nBUSINESS_DATA_RESET: NOT_AUTHORIZED\nCREDENTIAL_CHANGES: NOT_AUTHORIZED\nWINDOW_SECONDS: 3600\nAuthor by Lab | zefry";
 sNeed(hash_equals($want,str_replace("\r\n","\n",$c['body']??'')),'EXACT_OWNER_AUTHORITY_REQUIRED');return ['comment_id'=>(int)$id,'authorized_at'=>$at,'expires_at'=>$at+3600];
}
function sCopy(string $from,string $to,int $mode=0755):void{
 sNeed(is_dir($from)&&!is_link($from)&&realpath($from)===$from,'COPY_SOURCE_INVALID');sNeed(!file_exists($to)&&!is_link($to)&&mkdir($to,$mode),'COPY_TARGET_INVALID');chmod($to,$mode);foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($from,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST)as$f){sNeed(!$f->isLink()&&($f->isFile()||$f->isDir()),'COPY_SPECIAL_FILE_DENIED');$target=$to.'/'.substr($f->getPathname(),strlen($from)+1);if($f->isDir())sNeed(mkdir($target,$mode)&&chmod($target,$mode),'COPY_DIRECTORY_FAILED');else sNeed(copy($f->getPathname(),$target)&&chmod($target,0644),'COPY_FILE_FAILED');}
}
function sHttp(string $path):array{
 $ch=curl_init('https://oneqaydev.n07.my.id'.$path.(str_contains($path,'?')?'&':'?').'oneqay_verify='.bin2hex(random_bytes(6)));curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_ENCODING=>'',CURLOPT_FOLLOWLOCATION=>false,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>30,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,CURLOPT_HTTPHEADER=>['Accept: application/json,text/html,*/*','User-Agent: oneQay-Deployment-Verification']]);$body=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_RESPONSE_CODE);$type=curl_getinfo($ch,CURLINFO_CONTENT_TYPE);curl_close($ch);sNeed(is_string($body)&&strlen($body)<4000000,'HTTPS_READ_FAILED');return ['status'=>$status,'content_type'=>$type,'sha256'=>hash('sha256',$body)];
}
function sProbe():array{
 $out=[];foreach(['/','/owner-login']as$p){$out[$p]=sHttp($p);sNeed($out[$p]['status']===200,'PUBLIC_HTTP_GATE_FAILED');}$out['/pos']=sHttp('/pos');sNeed(in_array($out['/pos']['status'],[401,403],true),'POS_AUTHORIZATION_GATE_FAILED');
 $m=recJson(S_PUBLIC.'/build/manifest.json');$entry=$m['resources/js/app.ts'];foreach([$entry['file'],...($entry['css']??[])]as$p){sNeed(preg_match('~\Aassets/[A-Za-z0-9_.-]+\z~D',$p)===1,'ASSET_PATH_INVALID');$out['/build/'.$p]=sHttp('/build/'.$p);sNeed($out['/build/'.$p]['status']===200&&hash_equals(hash_file('sha256',S_PUBLIC.'/build/'.$p),$out['/build/'.$p]['sha256']),'PUBLIC_ASSET_HTTP_HASH_FAILED');}return $out;
}
function sVerifyActive(array $receipt):void{
 sNeed(($receipt['state']??null)==='POS_SYNC_RELEASE_PUBLISHED_ACCEPTANCE_PENDING'&&($receipt['source']??null)===S_SOURCE,'SOURCE_NOT_ACTIVE');sHash(sEnv(),$receipt['runtime_sha256']);sHash(sApp().'/.env',$receipt['runtime_sha256']);sHash(S_PUBLIC.'/'.sEntry(),$receipt['entry_sha256']);sHash(S_PUBLIC.'/.htaccess',$receipt['routing_sha256']);
}
function sDeploy(array $r,array $grant):array{
 [$app,$db,$oldEnv,$before]=sPreflight($r);sNeed(time()<$grant['expires_at'],'AUTHORITY_EXPIRED');$oldEntry=(string)file_get_contents(S_PUBLIC.'/oneqay-business-7e4197b44322-pos.php');$oldRouting=(string)file_get_contents(S_PUBLIC.'/.htaccess');
 $env=$oldEnv;foreach(['ONEQAY_RUNNING_SOURCE_COMMIT'=>S_SOURCE,'ONEQAY_RUNNING_ARTIFACT_SHA256'=>S_ARTIFACT]as$k=>$v){$env=preg_replace('/^'.$k.'=[^\r\n]*$/m',$k.'='.$v,$env,-1,$n);sNeed($n===1,'RUNTIME_BINDING_REWRITE_FAILED');}$restored=str_replace(['ONEQAY_RUNNING_SOURCE_COMMIT='.S_SOURCE,'ONEQAY_RUNNING_ARTIFACT_SHA256='.S_ARTIFACT],['ONEQAY_RUNNING_SOURCE_COMMIT='.S_OLD_SOURCE,'ONEQAY_RUNNING_ARTIFACT_SHA256=03670693b16f3f1ba0c3b6d64b89445d251780e1bdacc81de523927caec3c49e'],$env);sNeed(hash_equals($oldEnv,$restored),'UNRELATED_RUNTIME_CHANGED');$envSha=hash('sha256',$env);
 $entry=$oldEntry;foreach([var_export(qOldApp(),true)=>var_export(sApp(),true),var_export(S_ROOT.'/shared/production-business-runtime-7e4197b44322-pos.env',true)=>var_export(sEnv(),true),var_export(S_OLD_ENV,true)=>var_export($envSha,true),S_OLD_RELEASE.'-pos'=>sRelease().'-pos']as$from=>$to){sNeed(str_contains($entry,$from),'BRIDGE_BINDING_MISSING');$entry=str_replace($from,$to,$entry);}
 sNeed(str_contains($oldRouting,'oneqay-business-7e4197b44322-pos.php'),'ROUTING_BINDING_MISSING');$routing=str_replace('oneqay-business-7e4197b44322-pos.php',sEntry(),$oldRouting);
 $stage=S_PUBLIC.'/.oneqay-pos-sync-build-'.bin2hex(random_bytes(6));sCopy(S_PUBLIC.'/build',$stage);foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/payload/'.sRelease().'/apps/web/public/build',FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST)as$f){sNeed(!$f->isLink(),'ASSET_LINK_DENIED');$rel=substr($f->getPathname(),strlen(__DIR__.'/payload/'.sRelease().'/apps/web/public/build')+1);$to=$stage.'/'.$rel;if($f->isDir()){if(!is_dir($to))sNeed(mkdir($to,0755)&&chmod($to,0755),'ASSET_DIRECTORY_FAILED');}else{if(is_file($to)&&$rel!=='manifest.json')sNeed(hash_equals(hash_file('sha256',$to),hash_file('sha256',$f->getPathname())),'HASHED_ASSET_COLLISION');sNeed(copy($f->getPathname(),$to)&&chmod($to,0644),'ASSET_OVERLAY_FAILED');}}
 sNeed(mkdir(__DIR__.'/rollback',0700),'ROLLBACK_DIRECTORY_FAILED');recPublish(__DIR__.'/rollback/schema-before.json',$before);file_put_contents(__DIR__.'/rollback/routing.htaccess',$oldRouting);chmod(__DIR__.'/rollback/routing.htaccess',0600);
 require_once __DIR__.'/database-backup-helper.php';$backup=uPrivateDatabaseBackup($db,__DIR__.'/rollback/database.sql.gz');sNeed($backup['tables']===88,'DATABASE_BACKUP_SCOPE_MISMATCH');sReport('database-backup.json',$backup);
 sNeed(rename(__DIR__.'/payload/'.sRelease(),S_ROOT.'/releases/'.sRelease()),'CERTIFIED_RELEASE_MOVE_FAILED');sReport('deployment-stage.json',['state'=>'CANDIDATE_PREPARED_SCHEMA_NOT_STARTED','source'=>S_SOURCE,'at'=>time()]);// Materialize only the exact private runtime bytes below.
 $write=function(string $path,string $bytes,int $mode=0600):void{$tmp=$path.'.tmp-'.bin2hex(random_bytes(6));sNeed(!is_link($path),'WRITE_LINK_DENIED');$mask=umask(0077);try{$h=fopen($tmp,'x');}finally{umask($mask);}sNeed(is_resource($h)&&chmod($tmp,$mode),'WRITE_CREATE_FAILED');try{sNeed(fwrite($h,$bytes)===strlen($bytes)&&fflush($h),'WRITE_FAILED');fclose($h);$h=null;sNeed(rename($tmp,$path),'ATOMIC_WRITE_FAILED');}finally{if(is_resource($h))fclose($h);if(is_file($tmp))unlink($tmp);}};
 $write(sEnv(),$env);$write(sApp().'/.env',$env);$switched=false;$oldBuild=S_PUBLIC.'/.oneqay-pos-sync-previous-build-'.substr(S_SOURCE,0,12);sNeed(!file_exists($oldBuild),'PREVIOUS_BUILD_BACKUP_EXISTS');
 try{
  sReport('deployment-stage.json',['state'=>'ADDITIVE_SCHEMA_STARTED','source'=>S_SOURCE,'at'=>time()]);$app->make('migrator')->run([sApp().'/database/module-migrations/'.$r['migration']],['step'=>true]);$after=sSchema($db);sNeed(count($after['ddl'])===89&&count($after['receipts'])===39&&array_slice($after['receipts'],0,38)===$before['receipts'],'ADDITIVE_SCHEMA_TOTAL_INVALID');foreach($before['ddl']as$t=>$sha)sNeed(($after['ddl'][$t]??null)===$sha,'HISTORICAL_SCHEMA_CHANGED');sNeed(array_values(array_diff(array_keys($after['ddl']),array_keys($before['ddl'])))===['oneqay_desktop_sync_diagnostics'],'UNEXPECTED_ADDITIVE_TABLE');
  sNeed(time()<$grant['expires_at'],'AUTHORITY_EXPIRED_BEFORE_SWITCH');$write(S_PUBLIC.'/'.sEntry(),$entry,0644);sNeed(rename(S_PUBLIC.'/build',$oldBuild),'BUILD_BACKUP_FAILED');if(!rename($stage,S_PUBLIC.'/build')){rename($oldBuild,S_PUBLIC.'/build');throw new RuntimeException('BUILD_PROMOTION_FAILED');}$switched=true;$write(S_PUBLIC.'/.htaccess',$routing,0644);$https=sProbe();$result=['state'=>'POS_SYNC_RELEASE_PUBLISHED_ACCEPTANCE_PENDING','source'=>S_SOURCE,'release'=>sRelease(),'previous_source'=>S_OLD_SOURCE,'artifact'=>S_ARTIFACT,'certification_run'=>S_RUN,'runtime_sha256'=>$envSha,'entry_sha256'=>hash('sha256',$entry),'routing_sha256'=>hash('sha256',$routing),'file_count'=>$r['file_count'],'database'=>['migration_receipts'=>39,'tables'=>89,'added'=>'oneqay_desktop_sync_diagnostics','historical_ddl'=>'UNCHANGED'],'https'=>$https,'business_reset'=>'NOT_PERFORMED','credentials'=>'PRESERVED','smtp'=>'PRESERVED_NOT_SENT','offline_receipts'=>'PRESERVED_NOT_ACCEPTANCE_TESTED','worker'=>'REQUIRES_CRON_REPLACEMENT','old_release'=>'PRESERVED_PENDING_RETENTION','grant'=>$grant,'at'=>time()];sReport('update-result.json',$result);return $result;
 }catch(Throwable $error){if($switched){rename(S_PUBLIC.'/build',$stage);rename($oldBuild,S_PUBLIC.'/build');$write(S_PUBLIC.'/.htaccess',$oldRouting,0644);}sReport('update-result.json',['state'=>'STOPPED_FORWARD_REVIEW_REQUIRED','source'=>S_SOURCE,'code'=>$error instanceof RuntimeException?$error->getMessage():'DEPLOYMENT_ERROR','public_routing'=>$switched?'RESTORED_TO_STOCK_COMPATIBLE_7E':'NOT_SWITCHED','diagnostic_schema'=>'RETAINED_IF_CREATED','business_reset'=>'NOT_PERFORMED','at'=>time()]);throw $error;}
}
function sWork():array{
 $r=recJson(__DIR__.'/update-result.json');sVerifyActive($r);sNeed(!is_link(__DIR__.'/worker.lock')&&!is_dir(__DIR__.'/worker.lock'),'WORKER_LOCK_LINK_DENIED');$mask=umask(0077);try{$h=fopen(__DIR__.'/worker.lock','c');}finally{umask($mask);}sNeed(is_resource($h)&&chmod(__DIR__.'/worker.lock',0600),'WORKER_LOCK_FAILED');if(!flock($h,LOCK_EX|LOCK_NB))return ['state'=>'already_running'];
 try{require sApp().'/vendor/autoload.php';$app=require sApp().'/bootstrap/app.php';$k=$app->make(\Illuminate\Contracts\Console\Kernel::class);$k->bootstrap();sNeed(\App\Infrastructure\Runtime\ProductionBusinessRuntimeGate::allows('production')&&env('ONEQAY_RUNNING_SOURCE_COMMIT')===S_SOURCE,'NEW_NATIVE_PRODUCTION_GATE_DENIED');$o=new \Symfony\Component\Console\Output\BufferedOutput();$exit=$k->call('oneqay:control:work',['--limit'=>20],$o);$v=json_decode(trim($o->fetch()),true,64,JSON_THROW_ON_ERROR);if(($v['state']??null)==='already_running')return $v;sNeed(is_bool($v['failed']??null)&&is_int($v['at']??null)&&abs(time()-$v['at'])<180&&(($exit===0)===($v['failed']===false)),'WORKER_OUTPUT_INVALID');sReport('worker-result.json',$v);return $v;}finally{flock($h,LOCK_UN);fclose($h);}
}
if(PHP_SAPI==='cli'&&realpath($_SERVER['SCRIPT_FILENAME']??'')===__FILE__){
 try{$r=sRequest();$mode=$argv[1]??'';sNeed(in_array($mode,['preflight','update','work'],true),'COMMAND_INVALID');
  if($mode==='work'){$v=sWork();fwrite(STDOUT,"ONEQAY|WORKER_COMPLETE\n");exit(($v['failed']??false)?1:0);}
  sNeed(!is_link(__DIR__.'/deployment.lock')&&!is_dir(__DIR__.'/deployment.lock'),'DEPLOYMENT_LOCK_LINK_DENIED');$h=fopen(__DIR__.'/deployment.lock','c');sNeed(is_resource($h)&&chmod(__DIR__.'/deployment.lock',0600)&&flock($h,LOCK_EX|LOCK_NB),'DEPLOYMENT_BUSY');
  if($mode==='preflight'){sPreflight($r);$v=['state'=>'READY_NO_ACTIVATION','source'=>S_SOURCE,'file_count'=>$r['file_count'],'migration'=>'ONE_ADDITIVE_DIAGNOSTIC_TABLE','business_reset'=>'NOT_PERFORMED','at'=>time()];sReport('preflight-result.json',$v);}else{if(is_file(__DIR__.'/update-result.json')){$v=recJson(__DIR__.'/update-result.json');sVerifyActive($v);}else $v=sDeploy($r,sAuthority($argv[2]??''));}fwrite(STDOUT,'ONEQAY|'.$v['state']."\n");
 }catch(Throwable $error){$code=$error instanceof RuntimeException&&preg_match('/\A[A-Z0-9_]+\z/D',$error->getMessage())?$error->getMessage():'OPERATOR_INTERNAL_ERROR';if(function_exists('recPublish'))sReport('operator-error.json',['state'=>'STOPPED','code'=>$code,'at'=>time()]);fwrite(STDERR,'ONEQAY|'.$code."\n");exit(1);}
}
