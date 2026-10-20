<?php
declare(strict_types=1);
// Author by Lab | zefry. Reproduce incoming-payload cleanup and verify locked recovery in disposable files only.
$tmp=sys_get_temp_dir().'/oneqay-retention-race-'.bin2hex(random_bytes(8));mkdir($tmp,0700);mkdir($tmp.'/releases');mkdir($tmp.'/public');mkdir($tmp.'/shared');
$a=str_repeat('a',40);$b=str_repeat('b',40);$c=str_repeat('c',40);$d=str_repeat('d',40);
$op=$tmp.'/oneqaydev-pos-update-'.substr($b,0,12);mkdir($op,0700);mkdir($op.'/payload');
$operator=file_get_contents(__DIR__.'/../cpanel/update-oneqaydev-pos-enterprise.php');
foreach(['U_ROOT'=>$tmp,'U_PUBLIC'=>$tmp.'/public']as$key=>$value)$operator=preg_replace("~^const ".$key." = '[^']+';~m",'const '.$key.' = '.var_export($value,true).';',$operator,1);
$operator=str_replace('function uAuthority(', 'function fixtureUnusedAuthority(', $operator);
$operator=str_replace('function uApplyLocked(', 'function fixtureUnusedApplyLocked(', $operator);
file_put_contents($op.'/update.php',$operator);copy(__DIR__.'/../cpanel/release-retention.php',$op.'/release-retention.php');
require $op.'/update.php';require $op.'/release-retention.php';
$n=0;$grant=true;$grantCalls=0;$engineCalls=0;
function raceOk(bool $v,string $why):void {global $n;if(!$v)throw new RuntimeException($why);$n++;}
function raceDeny(callable $fn,string $code):void {try{$fn();throw new RuntimeException('Expected '.$code);}catch(WorkspaceUpdateStop|ReleaseRetentionStop $e){raceOk($e->getMessage()===$code,$code);}}
function raceRelease(string $path,string $sha):void {mkdir($path.'/apps/web',0700,true);file_put_contents($path.'/apps/web/source.php','certified-fixture');file_put_contents($path.'/RELEASE.json',json_encode(['release_id'=>basename($path),'source_commit'=>$sha,'source_ci_certified'=>true]));}
function raceReceipt(string $root,string $sha,int $at,string $state='BUSINESS_WORKSPACE_UPDATED_ACCEPTANCE_PENDING'):void {$op=$root.'/oneqaydev-pos-update-'.substr($sha,0,12);if(!is_dir($op))mkdir($op,0700);file_put_contents($op.'/update-result.json',json_encode(['state'=>$state,'business_traffic'=>'ON_PRESERVED','source_commit'=>$sha,'release_id'=>'production-'.substr($sha,0,12),'published_at_unix'=>$at]));}
function raceClean(string $path):void {foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST)as$f){$f->isDir()&&!$f->isLink()?rmdir($f->getPathname()):unlink($f->getPathname());}rmdir($path);}
function uAuthority(string $locator,array $r,?int $at=null):array {global $grant,$grantCalls;$grantCalls++;uAssert($grant,'EXACT_WORKSPACE_GRANT_MISSING');return [];}
function uApplyLocked(string $locator,array $r,bool $recover=false):array {
 global $tmp,$a,$op,$engineCalls;$engineCalls++;
 $h=fopen($tmp.'/release-archives/retention.lock','c');raceOk(!flock($h,LOCK_EX|LOCK_NB),'deployment owns the same lock as the old retention worker');fclose($h);
 raceOk(rrMaintain($tmp,'production-'.substr($a,0,12),1000,1120)['state']==='BUSY_RETRY_NEXT_CRON','old worker defers cleanup during restore/deploy');
 raceOk(file_get_contents($op.'/payload/'.$r['release_id'].'/apps/web/source.php')==='certified-fixture','restored payload available before normal preconditions');
 return ['state'=>'FIXTURE_DEPLOY_COMPLETED'];
}
try{
 $active=$tmp.'/releases/production-'.substr($a,0,12);$older=$tmp.'/releases/production-'.substr($d,0,12);$future=$tmp.'/releases/production-'.substr($c,0,12);$payload=$op.'/payload/production-'.substr($b,0,12);
 raceRelease($active,$a);raceRelease($older,$d);raceRelease($future,$c);raceRelease($payload,$b);
 raceReceipt($tmp,$d,900);raceReceipt($tmp,$b,1000,'STOPPED_FAIL_CLOSED');raceReceipt($tmp,$c,1100);
 $kept=rrMaintain($tmp,basename($active),1000,1120);
 raceOk(is_dir($payload),'prepared/failed payload survives active predecessor cleanup');
 raceOk(is_dir($future),'release published after the active horizon is preserved');
 raceOk(!is_dir($older)&&$kept['freed_inodes']>0,'previously published older release still archived');
 $original=rrInventory($payload,$tmp);rrArchive($tmp,$payload);raceOk(!file_exists($payload),'reproduce the deployed old worker archiving the candidate');
 $r=['source_commit'=>$b,'release_id'=>basename($payload),'retention_helper_sha256'=>hash_file('sha256',$op.'/release-retention.php')];
 $result=uApply('123',$r);raceOk($result['candidate_payload_recovered_from_verified_archive']===true&&rrInventory($payload,$tmp)===$original,'verified recovery preserves every byte and mode');
 $h=fopen($tmp.'/release-archives/retention.lock','c');raceOk(flock($h,LOCK_EX|LOCK_NB),'deploy releases retention lock on success');flock($h,LOCK_UN);fclose($h);
 rrArchive($tmp,$payload);$grant=false;$before=$engineCalls;raceDeny(fn()=>uApply('123',$r),'EXACT_WORKSPACE_GRANT_MISSING');raceOk(!file_exists($payload)&&$engineCalls===$before,'invalid authority cannot restore or enter deployment');$grant=true;
 $prior=['state'=>'STOPPED_FAIL_CLOSED','error_code'=>'CERTIFIED_SOURCE_CHANGED','public_switch'=>'NOT_PERFORMED','host_business_acceptance'=>'NOT_VERIFIED','attribution'=>'Lab | zefry'];uWrite(U_REPORT,json_encode($prior));
 uRetryStoppedPreflight('123',$r);$saved=glob($op.'/update-result-preflight-stopped-*.json');raceOk(!is_file(U_REPORT)&&count($saved)===1&&uJson($saved[0])===$prior,'untouched certified-source stop is preserved before authorized retry');
 $receipt=rrRead($tmp.'/release-archives/'.hash('sha256',basename($op).'/payload/'.$r['release_id']).'.json');$zip=$tmp.'/release-archives/'.$receipt['zip'];file_put_contents($zip,'tampered',FILE_APPEND);
 raceDeny(fn()=>rrRestoreSource($tmp,basename($op).'/payload/'.$r['release_id']),'RETENTION_ARCHIVE_CHANGED');raceOk(!file_exists($payload),'tampered archive creates no candidate payload');
 raceDeny(fn()=>rrRestoreSource($tmp,'../outside'),'RETENTION_RESTORE_SCOPE');
 echo 'POS_RETENTION_RACE_PASS assertions='.$n.PHP_EOL;
}finally{raceClean($tmp);}
