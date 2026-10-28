<?php
// Author by Lab | zefry. Disposable files only; no hosting, credentials or database access.
declare(strict_types=1);
$source=file_get_contents(__DIR__.'/../cpanel/update-oneqaydev-workspace-successor.php');
$root=sys_get_temp_dir().'/oneqay-successor-'.bin2hex(random_bytes(6));mkdir($root,0700);
file_put_contents($root.'/operator.php',str_replace(['__RUN__','__PREVIOUS_FILE_COUNT__'],['1','6406'],$source));
require $root.'/operator.php';$n=0;
$assert=function(bool $condition,string $message)use(&$n){if(!$condition)throw new RuntimeException($message);$n++;};
$denied=function(callable $action,string $code)use($assert){try{$action();throw new RuntimeException('EXPECTED_DENIAL');}catch(RuntimeException $e){$assert($e->getMessage()===$code,'Unexpected rejection '.$e->getMessage());}};
foreach(['old','new']as$tree){mkdir($root.'/'.$tree.'/database/migrations',0700,true);mkdir($root.'/'.$tree.'/database/module-migrations/business',0700,true);file_put_contents($root.'/'.$tree.'/database/migrations/base.php','historical');file_put_contents($root.'/'.$tree.'/database/module-migrations/business/module.php','module');}
sVerifyMigrationSources($root.'/old',$root.'/new');$assert(true,'same schema accepted');
file_put_contents($root.'/new/database/migrations/base.php','mutated');$denied(fn()=>sVerifyMigrationSources($root.'/old',$root.'/new'),'SOURCE_OR_BINDING_CHANGED');file_put_contents($root.'/new/database/migrations/base.php','historical');
file_put_contents($root.'/new/database/migrations/extra.php','new');$denied(fn()=>sVerifyMigrationSources($root.'/old',$root.'/new'),'SCHEMA_SOURCE_SET_CHANGED');unlink($root.'/new/database/migrations/extra.php');
$assert(!str_contains($source,'beApplyMigrations')&&!str_contains($source,'make(\'migrator\')'),'no migration execution path');
$assert(str_contains($source,"'schema_change'=>false")&&str_contains($source,'SCHEMA_CHANGE: NONE'),'no schema authority requested');
$assert(str_contains($source,'issues/988')&&str_contains($source,"N_OLD_SOURCE='275e1b293eea9ba5658259b0bfe8a543b1984723'"),'exact target and owner authority pinned');
mkdir($root.'/current');mkdir($root.'/stage');file_put_contents($root.'/current/asset','old');file_put_contents($root.'/stage/asset','new');file_put_contents($root.'/routing','old-route');
$denied(fn()=>nPromote($root.'/stage',$root.'/current',$root.'/backup',$root.'/routing','new-route',fn()=>throw new RuntimeException('PROBE_REJECTED')),'PROBE_REJECTED');
$assert(file_get_contents($root.'/current/asset')==='old'&&file_get_contents($root.'/routing')==='old-route','failed promotion restores old assets and route');
foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST)as$f){$f->isDir()?rmdir($f->getPathname()):unlink($f->getPathname());}rmdir($root);
echo 'WORKSPACE_SUCCESSOR_OPERATOR_PASS assertions='.$n.PHP_EOL;
