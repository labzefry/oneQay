<?php
declare(strict_types=1);
// Author by Lab | zefry. Private, source-bound operator repair; no process execution.
const REC_PACKAGE='/home/pekd7254/oneqay-production-test/oneqaydev-ui-update-7e4197b44322';
const REC_SOURCE='7e4197b443225e2022a26af9fd53a966d06c304f';
const REC_ENV_SHA='05aca10c97be6cb3cbaeaac2fef2e5d0c4502b14e050292822fc7e7ff00e7299';
const REC_WORKER_SHA='fe96490b4a27ad9e589b5ae69a7ff943139903e2e227f450d828e9cd0fe1aab9';
function recNeed(bool $ok,string $code):void{if(!$ok)throw new RuntimeException($code);}
function recJson(string $file):array{recNeed(is_file($file)&&!is_link($file),'INPUT_FILE_INVALID');$v=json_decode((string)file_get_contents($file),true,512,JSON_THROW_ON_ERROR);recNeed(is_array($v),'INPUT_JSON_INVALID');return $v;}
function recPublish(string $target,array $record):void{
 recNeed(!is_link($target)&&!is_dir($target),'RESULT_TARGET_INVALID');$bytes=json_encode($record,JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";$tmp=$target.'.tmp-'.bin2hex(random_bytes(8));$mask=umask(0077);try{$h=fopen($tmp,'x');}finally{umask($mask);}recNeed(is_resource($h),'RESULT_CREATE_FAILED');
 try{recNeed(chmod($tmp,0600)&&fwrite($h,$bytes)===strlen($bytes)&&fflush($h),'RESULT_WRITE_FAILED');fclose($h);$h=null;recNeed(!is_link($target)&&!is_dir($target)&&rename($tmp,$target),'RESULT_RENAME_FAILED');}finally{if(is_resource($h))fclose($h);if(is_file($tmp)&&!is_link($tmp))unlink($tmp);}
}
function recBindings(string $package):array{
 $r=recJson($package.'/update-result.json');recNeed(($r['state']??null)==='UI_SOURCE_PUBLISHED_ACCEPTANCE_PENDING'&&($r['source_commit']??null)===REC_SOURCE&&($r['release_id']??null)==='production-7e4197b44322'&&($r['business_traffic']??null)==='ON_PRESERVED','DEPLOY_RECEIPT_BINDING_DENIED');
 $worker=$package.'/control-worker.php';recNeed(is_file($worker)&&!is_link($worker)&&hash_equals(REC_WORKER_SHA,(string)hash_file('sha256',$worker))&&($r['worker_sha256']??null)===REC_WORKER_SHA,'ORIGINAL_WORKER_CHANGED');
 $app='/home/pekd7254/oneqay-production-test/releases/production-7e4197b44322/apps/web';$env='/home/pekd7254/oneqay-production-test/shared/production-business-runtime-7e4197b44322-pos.env';
 foreach([$env,$app.'/.env'] as $file)recNeed(is_file($file)&&!is_link($file)&&hash_equals(REC_ENV_SHA,(string)hash_file('sha256',$file)),'RUNTIME_BINDING_CHANGED');
 return [$r,$app];
}
function recInspect():array{
 $out=['at'=>time(),'state'=>'READ_ONLY_HTTP_INSPECTION','process_execution_available'=>function_exists('proc_open'),'http'=>[]];
 foreach(['/','/build/assets/app-Cy47fj4C.js','/build/assets/app-DgPj8UAj.css'] as $path){$ch=curl_init('https://oneqaydev.n07.my.id'.$path);$headers=[];curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_CONNECTTIMEOUT=>15,CURLOPT_TIMEOUT=>35,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_HEADERFUNCTION=>static function($ch,string $line)use(&$headers):int{if(preg_match('/^(content-type|content-security-policy|x-content-type-options|content-encoding|cache-control|cf-cache-status):\s*(.*)$/i',trim($line),$m))$headers[strtolower($m[1])]=$m[2];return strlen($line);}]);$body=curl_exec($ch);$out['http'][$path]=['status'=>curl_getinfo($ch,CURLINFO_RESPONSE_CODE),'headers'=>$headers,'bytes'=>is_string($body)?strlen($body):null,'sha256'=>is_string($body)?hash('sha256',$body):null,'curl_error_code'=>curl_errno($ch)];curl_close($ch);if($path!=='/'){$file='/home/pekd7254/public_html/oneqaydev.n07.my.id'.$path;$out['http'][$path]['disk_sha256']=is_file($file)&&!is_link($file)?hash_file('sha256',$file):null;}}
 return $out;
}
function recWork(string $app):array{
 require_once $app.'/vendor/autoload.php';$application=require $app.'/bootstrap/app.php';$kernel=$application->make(\Illuminate\Contracts\Console\Kernel::class);$kernel->bootstrap();
 recNeed(\App\Infrastructure\Runtime\ProductionBusinessRuntimeGate::allows('production')&&env('ONEQAY_RUNNING_SOURCE_COMMIT')===REC_SOURCE,'PRODUCTION_GATE_DENIED');
 $output=new \Symfony\Component\Console\Output\BufferedOutput();$exit=$kernel->call('oneqay:control:work',['--limit'=>20],$output);$raw=$output->fetch();recNeed(strlen($raw)<=131072,'WORKER_OUTPUT_TOO_LARGE');$value=json_decode(trim($raw),true,512,JSON_THROW_ON_ERROR);
 recNeed(is_array($value)&&!array_is_list($value),'WORKER_OUTPUT_INVALID');if(($value['state']??null)==='already_running')return ['at'=>time(),'state'=>'WORKER_ALREADY_RUNNING_RESULT_PRESERVED'];
 recNeed(is_bool($value['failed']??null)&&is_int($value['at']??null)&&abs(time()-$value['at'])<=180,'WORKER_OUTPUT_INVALID');recNeed(($exit===0)===($value['failed']===false),'WORKER_EXIT_STATUS_MISMATCH');return $value;
}
function recAssets(string $package):array{
 $root='/home/pekd7254/public_html/oneqaydev.n07.my.id/build';recNeed(realpath($root)===$root&&!is_link($root),'PUBLIC_BUILD_LOCATION_INVALID');
 foreach(['assets/app-Cy47fj4C.js'=>'cd1eb8923a4ee667ea228415b4c4844d63bf883c6f757e4bbc8ca744617cdb84','assets/app-DgPj8UAj.css'=>'71f123335ae43c356c11ebeb42401a00e49f9afee72384af27093d9214a6a906'] as $name=>$sha)recNeed(is_file($root.'/'.$name)&&!is_link($root.'/'.$name)&&hash_equals($sha,(string)hash_file('sha256',$root.'/'.$name)),'CERTIFIED_PUBLIC_ASSET_CHANGED');
 $before=['.'=>['mode'=>substr(sprintf('%o',fileperms($root)),-4),'kind'=>'directory']];$entries=[$root=>true];$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST);
 foreach($it as $e){$name=substr($e->getPathname(),strlen($root)+1);recNeed(!$e->isLink()&&($e->isDir()||$e->isFile())&&preg_match('~^[A-Za-z0-9_@+./-]+$~D',$name)===1&&!str_contains($name,'..')&&!str_starts_with($name,'.')&&!preg_match('~(^|/)(?:\\.env|artisan|composer\\.(?:json|lock)|package(?:-lock)?\\.json)$~',$name),'PUBLIC_BUILD_SPECIAL_FILE_DENIED');$before[$name]=['mode'=>substr(sprintf('%o',$e->getPerms()),-4),'kind'=>$e->isDir()?'directory':'file'];if($e->isFile())$before[$name]['sha256']=hash_file('sha256',$e->getPathname());$entries[$e->getPathname()]=$e->isDir();}
 $snapshot=$package.'/public-build-permissions-before.json';if(!file_exists($snapshot))recPublish($snapshot,['at'=>time(),'root'=>$root,'entries'=>$before]);
 $changed=0;foreach($entries as $path=>$dir){$mode=$dir?0755:0644;if((fileperms($path)&0777)!==$mode){recNeed(chmod($path,$mode),'PUBLIC_ASSET_MODE_REPAIR_FAILED');$changed++;}}
 clearstatcache();foreach($before as $name=>$entry){$path=$name==='.'?$root:$root.'/'.$name;recNeed((fileperms($path)&0777)===($entry['kind']==='directory'?0755:0644),'PUBLIC_ASSET_MODE_READBACK_FAILED');if($entry['kind']==='file')recNeed(hash_equals($entry['sha256'],(string)hash_file('sha256',$path)),'PUBLIC_ASSET_BYTES_CHANGED');}
 $out=['at'=>time(),'state'=>'PUBLIC_BUILD_PERMISSIONS_REPAIRED','changed_entries'=>$changed,'checked_entries'=>count($entries),'source_commit'=>REC_SOURCE,'asset_bytes'=>'UNCHANGED','private_paths'=>'UNTOUCHED'];recPublish($package.'/public-assets-recovery.json',$out);return $out;
}
if(PHP_SAPI==='cli'&&realpath($_SERVER['SCRIPT_FILENAME']??'')===__FILE__){
 $mode=$argv[1]??'';if(!in_array($mode,['inspect','work','assets'],true)||$argc!==2){fwrite(STDERR,"Usage: php recovery-20261022.php inspect|work|assets\n");exit(64);} $lock=null;$locked=false;
 try{recNeed(realpath(__DIR__)===REC_PACKAGE&&!is_link(__FILE__),'PRIVATE_OPERATOR_LOCATION_DENIED');recBindings(REC_PACKAGE);
  if($mode==='inspect'){$result=recInspect();recPublish(REC_PACKAGE.'/web-recovery-inspect.json',$result);fwrite(STDOUT,"ONEQAYDEV|HTTP_INSPECTION_COMPLETE\n");exit(0);}
  if($mode==='assets'){$result=recAssets(REC_PACKAGE);fwrite(STDOUT,"ONEQAYDEV|PUBLIC_BUILD_PERMISSIONS_REPAIRED\n");exit(0);}
  $path=REC_PACKAGE.'/worker-atomic.lock';recNeed(!is_link($path)&&!is_dir($path),'WORKER_LOCK_INVALID');$mask=umask(0077);try{$lock=fopen($path,'c');}finally{umask($mask);}recNeed(is_resource($lock)&&chmod($path,0600),'WORKER_LOCK_OPEN_FAILED');$locked=flock($lock,LOCK_EX|LOCK_NB);if(!$locked){fwrite(STDOUT,"ONEQAYDEV|WORKER_ALREADY_RUNNING\n");exit(0);}
  [, $app]=recBindings(REC_PACKAGE);$result=recWork($app);if(($result['state']??null)==='WORKER_ALREADY_RUNNING_RESULT_PRESERVED'){fwrite(STDOUT,"ONEQAYDEV|WORKER_ALREADY_RUNNING\n");exit(0);}recPublish(REC_PACKAGE.'/worker-result.json',$result);fwrite(STDOUT,$result['failed']?"ONEQAYDEV|WORKER_REPORTED_FAILURE\n":"ONEQAYDEV|WORKER_HEALTHY_BUFFERED_OUTPUT\n");exit($result['failed']?1:0);
 }catch(Throwable $e){$code=$e instanceof RuntimeException?$e->getMessage():'RECOVERY_INTERNAL_ERROR';if(!preg_match('/^[A-Z0-9_]+$/D',$code))$code='RECOVERY_INTERNAL_ERROR';try{recPublish(REC_PACKAGE.'/recovery-error.json',['at'=>time(),'state'=>$code,'failed'=>true]);}catch(Throwable){}fwrite(STDERR,"ONEQAYDEV|STOPPED|".$code."\n");exit(1);
 }finally{if(is_resource($lock)){if($locked)flock($lock,LOCK_UN);fclose($lock);}}
}
