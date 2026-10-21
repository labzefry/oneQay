<?php
declare(strict_types=1);
// Author by Lab | zefry. Synthetic filesystem and real Laravel console-output regression.
$vendor=$argv[1]??'';$certified=$argv[2]??'';
if(!is_file($vendor)||!is_dir($certified)){fwrite(STDERR,"Usage: php test.php VENDOR_AUTOLOAD CERTIFIED_APP_DIRECTORY\n");exit(64);}
require $vendor;
$temp=sys_get_temp_dir().'/oq-ui-recovery-'.bin2hex(random_bytes(8));mkdir($temp,0700);
$production=dirname(__DIR__).'/cpanel/recover-oneqaydev-ui-20261022.php';$source=file_get_contents($production);
$source=str_replace("'/home/pekd7254/public_html/oneqaydev.n07.my.id/build'",var_export($temp.'/build',true),$source);
file_put_contents($temp.'/operator.php',$source);require $temp.'/operator.php';
$checks=0;$assert=static function(bool $ok,string $label)use(&$checks):void{if(!$ok)throw new RuntimeException('FAIL: '.$label);$checks++;};
try{
 mkdir($temp.'/build',0750);mkdir($temp.'/build/assets',0750);mkdir($temp.'/package',0700);
 foreach(['app-Cy47fj4C.js','app-DgPj8UAj.css'] as $name){copy($certified.'/public/build/assets/'.$name,$temp.'/build/assets/'.$name);chmod($temp.'/build/assets/'.$name,0640);}
 $old=hash_file('sha256',$temp.'/build/assets/app-Cy47fj4C.js');$result=recAssets($temp.'/package');clearstatcache();
 $assert($result['state']==='PUBLIC_BUILD_PERMISSIONS_REPAIRED'&&$result['changed_entries']===4,'repair exact restricted public entries');
 $assert((fileperms($temp.'/build')&0777)===0755&&(fileperms($temp.'/build/assets')&0777)===0755,'public traversal restored');
 $assert((fileperms($temp.'/build/assets/app-Cy47fj4C.js')&0777)===0644&&hash_file('sha256',$temp.'/build/assets/app-Cy47fj4C.js')===$old,'bytes unchanged and static read restored');
 $assert((fileperms($temp.'/package')&0777)===0700,'private directory untouched');
 $snapshot=file_get_contents($temp.'/package/public-build-permissions-before.json');$again=recAssets($temp.'/package');
 $assert($again['changed_entries']===0&&file_get_contents($temp.'/package/public-build-permissions-before.json')===$snapshot,'replay retains original recovery evidence');
 file_put_contents($temp.'/private.env','synthetic');symlink($temp.'/private.env',$temp.'/build/assets/secret');
 try{recAssets($temp.'/package');throw new LogicException('unexpected symlink acceptance');}catch(RuntimeException $e){$assert($e->getMessage()==='PUBLIC_BUILD_SPECIAL_FILE_DENIED','symlink excluded before chmod');}unlink($temp.'/build/assets/secret');
 file_put_contents($temp.'/build/assets/app-Cy47fj4C.js','tampered');
 try{recAssets($temp.'/package');throw new LogicException('unexpected byte acceptance');}catch(RuntimeException $e){$assert($e->getMessage()==='CERTIFIED_PUBLIC_ASSET_CHANGED','wrong certified bytes denied');}
 recPublish($temp.'/package/result.json',['failed'=>false,'at'=>123]);$record=recJson($temp.'/package/result.json');
 $assert($record===['failed'=>false,'at'=>123]&&(fileperms($temp.'/package/result.json')&0777)===0600,'valid private atomic JSON');
 symlink($temp.'/private.env',$temp.'/package/result-link.json');
 try{recPublish($temp.'/package/result-link.json',['failed'=>false]);throw new LogicException('unexpected result symlink acceptance');}catch(RuntimeException $e){$assert($e->getMessage()==='RESULT_TARGET_INVALID','atomic writer rejects symlink');}
 // Run a real Illuminate command through the same Kernel::call output-buffer path.
 $application=new \Illuminate\Foundation\Application($temp);$events=new \Illuminate\Events\Dispatcher($application);
 $artisan=new \Illuminate\Console\Application($application,$events,'test');
 $command=new class extends \Illuminate\Console\Command{protected $signature='oneqay:output-regression';public function handle():int{$this->line('{"failed":false,"at":123,"mail":{"state":"completed"}}');return 0;}};
 $artisan->add($command);
 $kernel=new class($artisan) extends \Illuminate\Foundation\Console\Kernel{private $testArtisan;public function __construct($artisan){$this->testArtisan=$artisan;}public function bootstrap(){}protected function getArtisan(){return $this->testArtisan;}};
 $buffer=new \Symfony\Component\Console\Output\BufferedOutput();ob_start();$exit=$kernel->call('oneqay:output-regression',[],$buffer);$leak=ob_get_clean();$json=json_decode(trim($buffer->fetch()),true,512,JSON_THROW_ON_ERROR);
 $assert($exit===0&&$leak===''&&$json['failed']===false&&$json['mail']['state']==='completed','real Laravel output captured without proc_open or STDOUT redirection');
 $assert(!preg_match('/\b(?:proc_open|exec|shell_exec|system|passthru|popen)\s*\(/',$source),'no external process dependency');
 echo json_encode(['state'=>'PASS','checks'=>$checks]),"\n";
}finally{
 foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($temp,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST) as $e){if($e->isDir()&&!$e->isLink())rmdir($e->getPathname());else unlink($e->getPathname());}rmdir($temp);
}
