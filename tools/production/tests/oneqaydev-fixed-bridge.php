<?php
// Author by Lab | zefry. Isolated regular-file binding with both link capabilities disabled.
declare(strict_types=1);
require getenv('CONTROL_OPERATOR')?:__DIR__.'/../cpanel/update-oneqaydev-control-plane.php';
$n=0;$ok=static function(bool $value,string $label)use(&$n):void{if(!$value)throw new RuntimeException($label);++$n;};
$deny=static function(callable $work,string $code)use($ok):void{try{$work();throw new RuntimeException('Expected '.$code);}catch(WorkspaceUpdateStop $e){$ok($e->getMessage()===$code,$code);}};
if(($argv[1]??'')==='no-links')$ok(!function_exists('link')&&!function_exists('symlink'),'test must disable both link functions');
$dir=sys_get_temp_dir().'/oneqay-fixed-bridge-'.bin2hex(random_bytes(8));mkdir($dir,0700);
try{
 $env=$dir.'/private.env';$target=$dir.'/.env';$raw="APP_KEY=synthetic-private-secret\nDB_PASSWORD=synthetic-not-logged\n";file_put_contents($env,$raw);chmod($env,0600);
 uBindExistingRuntime($env,$target);$ok(is_file($target)&&!is_link($target),'runtime is a regular file');$ok(file_get_contents($target)===$raw,'exact byte copy');$ok(((int)fileperms($target)&0777)===0600,'runtime is private 0600');
 $ok(file_get_contents($env)===$raw,'shared source unchanged');$ok(fileinode($env)!==fileinode($target),'runtime does not depend on a hard link');
 $deny(fn()=>uBindExistingRuntime($env,$target),'RUNTIME_TARGET_ALREADY_EXISTS');$ok(file_get_contents($target)===$raw,'existing runtime never overwritten');
 chmod($env,0644);clearstatcache(true,$env);$deny(fn()=>uBindExistingRuntime($env,$dir.'/unsafe.env'),'PRIVATE_RUNTIME_INVALID');$ok(!file_exists($dir.'/unsafe.env'),'unsafe source creates no target');chmod($env,0600);
 $deny(fn()=>uBindExistingRuntime($dir.'/missing',$dir.'/missing-copy'),'PRIVATE_RUNTIME_INVALID');
 $worker=uWorker(['source_commit'=>U_SOURCE,'release_id'=>'production-'.substr(U_SOURCE,0,12)]);token_get_all($worker,TOKEN_PARSE);$ok(true,'generated worker parses');
 foreach(['WORKER_RELEASE_NOT_ACTIVE','WORKER_ACTIVE_BINDING_CHANGED','WORKER_PRIVATE_RUNTIME_CHANGED','WORKER_PRODUCTION_GATE_DENIED','worker_sha256','oneqay:control:work','--limit=20']as$needle)$ok(str_contains($worker,$needle),'worker enforces '.$needle);
 $ok(!str_contains($worker,'shell_exec')&&!str_contains($worker,'exec(')&&!str_contains($worker,'passthru'),'worker invokes no shell');
 echo 'FIXED_PUBLIC_BRIDGE_'.(($argv[1]??'')==='no-links'?'NO_LINKS':'STANDARD').'_PASS assertions='.$n.PHP_EOL;
}finally{foreach(glob($dir.'/*')as$file)unlink($file);if(is_file($dir.'/.env'))unlink($dir.'/.env');rmdir($dir);}
