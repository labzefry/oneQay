<?php
declare(strict_types=1);
// Author by Lab | zefry. Real filesystem fixtures for the worker lock and untouched preflight restart.
$source=file_get_contents(__DIR__.'/../cpanel/update-oneqaydev-pos-enterprise.php');
$tmp=sys_get_temp_dir().'/oneqay-pos-lock-'.bin2hex(random_bytes(8));mkdir($tmp,0700);$op=$tmp.'/operator';mkdir($op,0700);
$id='production-45509005b2f9';$release=$tmp.'/private/releases/'.$id;
mkdir($release.'/apps/web/storage/framework',0700,true);file_put_contents($release.'/source.php','certified-source');
$manifest=hash('sha256','certified-source').'  '.$id."/source.php\n";
$manifestPath=$tmp.'/previous-files.sha256';file_put_contents($manifestPath,$manifest);
foreach(['U_ROOT'=>var_export($tmp.'/private',true),'U_PUBLIC'=>var_export($tmp.'/public',true),'L_PREVIOUS_FILES'=>var_export($manifestPath,true),'L_PREVIOUS_FILES_SHA'=>var_export(hash('sha256',$manifest),true)]as$key=>$value){
 $source=preg_replace('~^const '.$key.' = [^;]+;~m','const '.$key.' = '.$value.';',$source,1,$n);if($n!==1)throw new RuntimeException('Fixture binding failed');
}
$source=str_replace("'release_file_count'=>6378","'release_file_count'=>1",$source);
$source=str_replace('function uAuthority(', 'function fixtureUnusedAuthority(', $source);
file_put_contents($op.'/update.php',$source);require $op.'/update.php';
$n=0;$grantValid=true;$grantCalls=0;
function uAuthority(string $locator,array $r,?int $at=null):array {global $grantValid,$grantCalls;$grantCalls++;uAssert($grantValid,'EXACT_WORKSPACE_GRANT_MISSING');return [];}
function lk(bool $yes,string $why):void {global $n;if(!$yes)throw new RuntimeException($why);$n++;}
function lkDeny(callable $work,string $code):void {try{$work();throw new RuntimeException('Expected '.$code);}catch(WorkspaceUpdateStop $e){lk($e->getMessage()===$code,$code);}}
try{
 file_put_contents($op.'/release-files.sha256',$manifest);
 $r=['release_id'=>$id,'release_file_count'=>1,'release_files_sha256'=>hash('sha256',$manifest)];
 lk(lPreviousIntegrity()===1&&uSourceFiles($r,$tmp.'/private/releases',true)===1,'clean predecessor and runtime');
 $lock=$release.'/apps/web/storage/framework/oneqay-control-worker.lock';
 // Use the same open/lock/unlock/close behavior as ControlPlaneWorkCommand, which leaves an empty file.
 $h=fopen($lock,'c');flock($h,LOCK_EX);flock($h,LOCK_UN);fclose($h);chmod($lock,0600);
 lk(lPreviousIntegrity()===1,'actual worker lock accepted in predecessor');
 lk(uSourceFiles($r,$tmp.'/private/releases',true)===1,'actual worker lock accepted in candidate runtime');
 lkDeny(fn()=>uSourceFiles($r,$tmp.'/private/releases',false),'UNEXPECTED_SOURCE_FILE');
 file_put_contents($lock,'unexpected bytes');clearstatcache();
 lkDeny(fn()=>lPreviousIntegrity(),'UNEXPECTED_SOURCE_FILE');lkDeny(fn()=>uSourceFiles($r,$tmp.'/private/releases',true),'UNEXPECTED_SOURCE_FILE');
 unlink($lock);symlink($release.'/source.php',$lock);
 lkDeny(fn()=>lPreviousIntegrity(),'SOURCE_SYMLINK_DENIED');lkDeny(fn()=>uSourceFiles($r,$tmp.'/private/releases',true),'SOURCE_SYMLINK_DENIED');unlink($lock);
 $unknown=$release.'/apps/web/storage/framework/other-worker.lock';file_put_contents($unknown,'');
 lkDeny(fn()=>lPreviousIntegrity(),'UNEXPECTED_SOURCE_FILE');lkDeny(fn()=>uSourceFiles($r,$tmp.'/private/releases',true),'UNEXPECTED_SOURCE_FILE');unlink($unknown);
 file_put_contents($lock,'');file_put_contents($release.'/source.php','tampered');
 lkDeny(fn()=>lPreviousIntegrity(),'CERTIFIED_SOURCE_CHANGED');lkDeny(fn()=>uSourceFiles($r,$tmp.'/private/releases',true),'CERTIFIED_SOURCE_CHANGED');file_put_contents($release.'/source.php','certified-source');
 $r=['source_commit'=>U_SOURCE,'release_id'=>'production-'.substr(U_SOURCE,0,12)];$p=uPaths($r);
 mkdir(dirname($p['env']),0700,true);mkdir(U_PUBLIC,0700,true);
 $failed=['state'=>'STOPPED_FAIL_CLOSED','error_code'=>'UNEXPECTED_SOURCE_FILE','public_switch'=>'NOT_PERFORMED','host_business_acceptance'=>'NOT_VERIFIED','attribution'=>'Lab | zefry'];
 $write=static fn($v)=>uWrite(U_REPORT,json_encode($v,JSON_THROW_ON_ERROR));$write($failed);
 foreach([$p['release'],$p['env'],$p['entry'],U_BACKUP,$op.'/desktop-pos-installation.json',$op.'/control-worker.php']as$path){
  file_put_contents($path,'checkpoint');lkDeny(fn()=>uRetryStoppedPreflight('123',$r),'RETRY_MUTATION_PRESENT_NO_AUTOMATIC_RECOVERY');unlink($path);lk(uJson(U_REPORT)===$failed,'mutation denied without erasing evidence');
 }
 foreach(['public_switch'=>'PERFORMED','error_code'=>'INTERNAL_ERROR','extra'=>'unknown']as$key=>$value){$bad=$failed;$bad[$key]=$value;$write($bad);lkDeny(fn()=>uRetryStoppedPreflight('123',$r),'RETRY_REPORT_NOT_PREFLIGHT_ONLY');}
 $write($failed);$grantValid=false;lkDeny(fn()=>uRetryStoppedPreflight('123',$r),'EXACT_WORKSPACE_GRANT_MISSING');lk(uJson(U_REPORT)===$failed,'expired authority preserves evidence');$grantValid=true;
 uRetryStoppedPreflight('123',$r);$saved=glob($op.'/update-result-preflight-stopped-*.json');
 lk(!file_exists(U_REPORT)&&count($saved)===1&&uJson($saved[0])===$failed&&$grantCalls===2,'authorized untouched preflight restart preserves original report');
 echo 'POS_ENTERPRISE_RUNTIME_LOCK_PASS assertions='.$n.PHP_EOL;
}finally{
 foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmp,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST)as$e){if($e->isDir()&&!$e->isLink())rmdir($e->getPathname());else unlink($e->getPathname());}rmdir($tmp);
}
