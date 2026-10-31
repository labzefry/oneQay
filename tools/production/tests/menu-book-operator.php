<?php
// Author by Lab | zefry. Disposable files only; never connects to hosting.
declare(strict_types=1);
$root=sys_get_temp_dir().'/oneqay-menu-op-'.bin2hex(random_bytes(6));mkdir($root,0700);
$source=file_get_contents(__DIR__.'/../cpanel/update-oneqaydev-menu-book.php');
file_put_contents($root.'/operator.php',str_replace(['__RUN__','__PREVIOUS_FILE_COUNT__'],['1','6426'],$source));require $root.'/operator.php';
$n=0;$assert=static function(bool $condition)use(&$n){if(!$condition)throw new RuntimeException('MENU_OPERATOR_ASSERTION_FAILED');$n++;};
$assert(str_contains($source,"N_OLD_SOURCE='95d9286f4698b168f7c419b67ac3c6583f844910'"));
$assert(str_contains($source,"'schema_change'=>true")&&str_contains($source,'ADDITIVE_3_MODULE_MIGRATIONS'));
$assert(str_contains($source,"'database-backup-helper.php'")&&str_contains($source,'uPrivateDatabaseBackup'));
$assert(str_contains($source,'mbApplyMigrations($app,$db,nApp(),$before)')&&str_contains($source,'mbVerifySchema($before,nSchema($db))'));
$assert(str_contains($source,'menuViteInventory(realpath(nApp().')&&str_contains($source,'VITE_ASSET_HTTP_HASH_FAILED'));
$assert(str_contains($source,'PHP_GD_REQUIRED')&&str_contains($source,'issues/992'));
nMenuRuntime(['JPEG Support'=>true,'PNG Support'=>true,'WebP Support'=>true],8*1024*1024);$assert(true);
try{nMenuRuntime(['JPEG Support'=>true,'PNG Support'=>true],8*1024*1024);throw new Exception('GD_FORMAT_ACCEPTED');}catch(RuntimeException $e){$assert($e->getMessage()==='PHP_GD_FORMAT_REQUIRED');}
try{nMenuRuntime(['JPEG Support'=>true,'PNG Support'=>true,'WebP Support'=>true],1048576);throw new Exception('PACKET_ACCEPTED');}catch(RuntimeException $e){$assert($e->getMessage()==='MYSQL_PACKET_TOO_SMALL');}
$assert(str_contains($source,'ADDITIVE_TABLES_RETAINED_FORWARD_REVIEW_REQUIRED'));
foreach(['current','stage'] as $d)mkdir($root.'/'.$d);file_put_contents($root.'/current/asset','old');file_put_contents($root.'/stage/asset','new');file_put_contents($root.'/routing','old-route');
try{nPromote($root.'/stage',$root.'/current',$root.'/backup',$root.'/routing','new-route',fn()=>throw new RuntimeException('PROBE_REJECTED'));throw new Exception('UNEXPECTED_SUCCESS');}catch(RuntimeException $e){$assert($e->getMessage()==='PROBE_REJECTED');}
$assert(file_get_contents($root.'/current/asset')==='old'&&file_get_contents($root.'/routing')==='old-route'&&file_get_contents($root.'/stage/asset')==='new');
foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST)as$f){$f->isDir()?rmdir($f->getPathname()):unlink($f->getPathname());}rmdir($root);
echo 'MENU_OPERATOR_DRAFT_PASS assertions='.$n.PHP_EOL;
