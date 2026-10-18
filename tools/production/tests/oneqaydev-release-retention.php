<?php
declare(strict_types=1);
// Author by Lab | zefry. Actual compressed ZIP round trips and failure boundaries, never a hosting directory.
require __DIR__.'/../cpanel/release-retention.php';
$tmp=sys_get_temp_dir().'/oneqay-retention-'.bin2hex(random_bytes(8)); mkdir($tmp,0700); mkdir($tmp.'/releases');mkdir($tmp.'/shared');
$count=0;
function rt(bool $ok,string $label): void {global $count; if(!$ok)throw new RuntimeException($label);$count++;}
function rtDeny(callable $f,string $code): void {try{$f();throw new RuntimeException('expected '.$code);}catch(ReleaseRetentionStop $e){rt($e->getMessage()===$code,$code);}}
function rtRelease(string $path,string $commit): void {
    mkdir($path.'/apps/web/empty',0750,true);file_put_contents($path.'/apps/web/app.php',str_repeat('oneQay code ',1000));chmod($path.'/apps/web/app.php',0640);
    file_put_contents($path.'/apps/web/.env','PRIVATE_DONT_PRINT');chmod($path.'/apps/web/.env',0600);
    file_put_contents($path.'/RELEASE.json',json_encode(['release_id'=>basename($path),'source_commit'=>$commit,'source_ci_certified'=>true]));
}
function rtClean(string $dir):void {foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST)as$e){if($e->isDir()&&!$e->isLink())rmdir($e->getPathname());else unlink($e->getPathname());}rmdir($dir);}
try {
    $old='production-'.str_repeat('a',12);$new='production-'.str_repeat('b',12);$older='production-'.str_repeat('c',12);
    rtRelease($tmp.'/releases/'.$old,str_repeat('a',40));rtRelease($tmp.'/releases/'.$new,str_repeat('b',40));
    $initial=rrInventory($tmp.'/releases/'.$old,$tmp);
    $payload=$tmp.'/oneqaydev-control-update-'.str_repeat('b',12).'-r2/payload/'.$new;rtRelease($payload,str_repeat('b',40));
    mkdir($tmp.'/staging');file_put_contents($tmp.'/staging/keep','staging');file_put_contents($tmp.'/shared/session','session');file_put_contents($tmp.'/oneqaydev-control-update-'.str_repeat('b',12).'-r2/keep-helper.php','helper');
    $pending=rrMaintain($tmp,$new,1000,1119);rt($pending['state']==='WAITING_GRACE'&&is_dir($tmp.'/releases/'.$old),'GRACE_PRESERVES_OLD');
    $done=rrMaintain($tmp,$new,1000,1120);rt($done['state']==='COMPLETE'&&count($done['archived'])===2&&$done['freed_inodes']>10,'MAINTENANCE_RELEASE_AND_PAYLOAD');
    rt(is_dir($tmp.'/releases/'.$new)&&is_file($tmp.'/releases/'.$new.'/apps/web/app.php'),'ACTIVE_RETAINED');
    rt(!file_exists($tmp.'/releases/'.$old)&&!file_exists($payload),'EXPANDED_OLD_REMOVED');
    rt(file_get_contents($tmp.'/staging/keep')==='staging'&&file_get_contents($tmp.'/shared/session')==='session'&&is_file(dirname(dirname($payload)).'/keep-helper.php'),'STAGING_SHARED_HELPERS_RETAINED');
    $archiveDir=$tmp.'/release-archives';$receipt=$archiveDir.'/'.hash('sha256','releases/'.$old).'.json';$saved=rrRead($receipt);$zip=$archiveDir.'/'.$saved['zip'];
    rt((fileperms($archiveDir)&0777)===0700&&(fileperms($zip)&0777)===0600&&(fileperms($receipt)&0777)===0600,'PRIVATE_ARCHIVE_MODES');
    $phar=new PharData($zip);rt($phar['apps/web/app.php']->isCompressed(Phar::GZ)&&filesize($zip)<6000,'ACTUAL_ZIP_COMPRESSION');unset($phar);
    rrRestore($tmp,$old);rt(rrInventory($tmp.'/releases/'.$old,$tmp)===$initial,'ROLLBACK_ALL_BYTES_MODES_AND_EMPTY_DIRECTORIES');
    rrRestore($tmp,$old);rt(rrInventory($tmp.'/releases/'.$old,$tmp)===$initial,'RESTORE_IDEMPOTENT');
    // The restored previous version can become active while the next release is archived.
    $back=rrMaintain($tmp,$old,1000,1120);rt(is_dir($tmp.'/releases/'.$old)&&!file_exists($tmp.'/releases/'.$new)&&$back['state']==='COMPLETE','ROLLBACK_ACTIVE_PROTECTED');
    rrRestore($tmp,$new);rt(is_file($tmp.'/releases/'.$new.'/apps/web/app.php'),'NEXT_VERSION_RESTORABLE');
    rrArchive($tmp,$tmp.'/releases/'.$old);
    // Tampered ZIP cannot be used even if Phar cached the original directory index.
    $raw=file_get_contents($zip);file_put_contents($zip,$raw.'corruption');rtDeny(fn()=>rrRestore($tmp,$old),'RETENTION_ARCHIVE_CHANGED');rt(!file_exists($tmp.'/releases/'.$old),'CORRUPT_ARCHIVE_NO_PARTIAL_RESTORE');file_put_contents($zip,$raw);
    rtDeny(fn()=>rrArchive($tmp,$tmp.'/staging'),'RETENTION_SOURCE_SCOPE');rtDeny(fn()=>rrRestore($tmp,'../staging'),'RETENTION_RESTORE_SCOPE');
    rt(!rrRelative('../x')&&!rrRelative('/absolute')&&!rrRelative('a//b')&&!rrRelative('a/./b')&&!rrRelative('a\\b'),'TRAVERSAL_PATHS_DENIED');
    rtRelease($tmp.'/releases/'.$older,str_repeat('c',40));symlink($tmp.'/shared',$tmp.'/releases/'.$older.'/outside');
    $warn=rrMaintain($tmp,$new,1000,1120);rt($warn['state']==='RETRY_REQUIRED_FILES_PRESERVED'&&is_link($tmp.'/releases/'.$older.'/outside')&&file_get_contents($tmp.'/shared/session')==='session','ARBITRARY_LINK_KEEP_FILES');unlink($tmp.'/releases/'.$older.'/outside');
    // Only historical app .env alias into private shared is allowed; restored as an independent private file.
    file_put_contents($tmp.'/shared/old.env','PRIVATE_ALIAS');unlink($tmp.'/releases/'.$older.'/apps/web/.env');symlink($tmp.'/shared/old.env',$tmp.'/releases/'.$older.'/apps/web/.env');
    $aliased=rrArchive($tmp,$tmp.'/releases/'.$older);rrRestore($tmp,$older);
    rt(!is_link($tmp.'/releases/'.$older.'/apps/web/.env')&&file_get_contents($tmp.'/releases/'.$older.'/apps/web/.env')==='PRIVATE_ALIAS'&&(fileperms($tmp.'/releases/'.$older.'/apps/web/.env')&0777)===0600&&is_file($tmp.'/shared/old.env'),'HISTORICAL_ENV_ALIAS_PRIVATE_REGULAR_RESTORE');
    $bad=rrRead($archiveDir.'/'.hash('sha256','releases/'.$older).'.json');$bad['entries']['../escape']=['kind'=>'file'];
    rrWrite($archiveDir.'/'.hash('sha256','releases/'.$older).'.json',$bad);rtClean($tmp.'/releases/'.$older);rtDeny(fn()=>rrRestore($tmp,$older),'RETENTION_RECEIPT_PATH');
    // Partial deletion resumes from the verified full archive instead of archiving an incomplete version.
    rrRestore($tmp,$old);$quarantine=$archiveDir.'/deleting-'.hash('sha256','releases/'.$old);rename($tmp.'/releases/'.$old,$quarantine);unlink($quarantine.'/apps/web/app.php');
    $again=rrMaintain($tmp,$new,1000,1120);rt(!is_dir($quarantine)&&!is_dir($tmp.'/releases/'.$old),'PARTIAL_REMOVAL_RESUMED');
    // A concurrent source modification leaves the directory available and preserves the previously verified archive.
    rrRestore($tmp,$old);file_put_contents($tmp.'/releases/'.$old.'/apps/web/app.php','changed');
    rtDeny(fn()=>rrArchive($tmp,$tmp.'/releases/'.$old),'RETENTION_SOURCE_CHANGED_KEEP_FILES');rt(file_get_contents($tmp.'/releases/'.$old.'/apps/web/app.php')==='changed','CHANGED_SOURCE_NOT_DELETED');
    // Locks make overlapping maintenance a deferred retry, and every new deployment invokes the same policy.
    $lock=fopen($archiveDir.'/retention.lock','c');flock($lock,LOCK_EX);$busy=rrMaintain($tmp,$new,1000,1120);flock($lock,LOCK_UN);fclose($lock);rt($busy['state']==='BUSY_RETRY_NEXT_CRON','OVERLAPPING_CRON_DEFERRED');
    echo 'RELEASE_RETENTION_PASS assertions='.$count."\n";
} finally {rtClean($tmp);}
